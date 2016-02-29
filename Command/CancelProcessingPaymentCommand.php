<?php

namespace Ekyna\Bundle\SubscriptionBundle\Command;

use Doctrine\DBAL\Types\Type;
use Ekyna\Component\Sale\Payment\PaymentStates;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CancelProcessingPaymentCommand
 * @package Ekyna\Bundle\SubscriptionBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CancelProcessingPaymentCommand  extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:subscription:cancel-payment')
            ->setDescription('Cancel the processing payment not updated since day -1.')
            ->setHelp(<<<EOT
The <info>ekyna:subscription:cancel-payment</info> cancels the processing payment not updated since day -1.
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Cancelling processing payments...</info>'));

        $qb = $this->getContainer()
            ->get('ekyna_subscription.payment.repository')
            ->createQueryBuilder('p');

        /** @var \Ekyna\Bundle\SubscriptionBundle\Entity\Payment[] $payments */
        $payments = $this->getContainer()
            ->get('ekyna_subscription.payment.repository')

            ->createQueryBuilder('p')
            ->join('p.method', 'm')
            ->andWhere($qb->expr()->eq('p.state', ':state'))
            ->andWhere($qb->expr()->neq('m.factoryName', ':not_factory'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->isNotNull('p.updatedAt'),
                        $qb->expr()->lt('p.updatedAt', ':date')
                    ),
                    $qb->expr()->andX(
                        $qb->expr()->isNull('p.updatedAt'),
                        $qb->expr()->lt('p.createdAt', ':date')
                    )
                )
            )

            ->getQuery()
            ->setParameter('state', PaymentStates::STATE_PROCESSING)
            ->setParameter('not_factory', 'offline')
            ->setParameter('date', new \DateTime('-1 day'), Type::DATETIME)

            ->getResult();

        if (!empty($payments)) {
            $factory = $this->getContainer()->get('sm.factory');
            foreach ($payments as $payment) {
                $stateMachine = $factory->get($payment);
                $stateMachine->apply('cancel');
                $output->writeln(sprintf(' - payement #%d.', $payment->getId()));
            }
        }

        $this->getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->flush();
    }
}
