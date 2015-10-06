<?php

namespace Ekyna\Bundle\SubscriptionBundle\Command;

use Doctrine\DBAL\Types\Type;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class NotifySubscriptionsCommand
 * @package Ekyna\Bundle\SubscriptionBundle\Command
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class NotifySubscriptionsCommand extends ContainerAwareCommand
{
    const USERS_WITH_SUBSCRIPTION_PAYMENT_REQUIRED_DQL = <<<DQL
SELECT s, u
FROM %s s
JOIN s.user u
WHERE s.state = :state
  AND (s.notifiedAt IS NULL OR s.notifiedAt < :date)
GROUP BY u.id
DQL;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:subscription:notify')
            ->setDescription('Notify users about subscriptions payments.')
            ->setHelp(<<<EOT
The <info>ekyna:subscription:notify</info> Notifies users about subscriptions payments.
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Sending "Call for subscription payment" emails...</info>'));

        $notifier = $this->getContainer()->get('ekyna_subscription.subscription.notifier');

        $users = $this->findUsersWithSubscriptionPaymentRequired();

        $count = 0;
        foreach ($users as $user) {
            if (0 < $sent = $notifier->sendCallUserForPayment($user)) {
                $count += $sent;
            }
        }

        $output->writeln(sprintf('%d emails sent.', $count));
    }

    /**
     * Returns the users which need to be called fir a subscription payment.
     *
     * @return \Ekyna\Bundle\UserBundle\Model\UserInterface[]
     */
    protected function findUsersWithSubscriptionPaymentRequired()
    {
        $subscriptionClass = $this->getContainer()->getParameter('ekyna_subscription.subscription.class');

        $dql = sprintf(self::USERS_WITH_SUBSCRIPTION_PAYMENT_REQUIRED_DQL, $subscriptionClass);

        $interval = $this->getContainer()->getParameter('ekyna_subscription.config')['interval'];

        $date = new \DateTime();
        $date->modify(sprintf('-%d days', $interval));

        $results = $this
            ->getContainer()
            ->get('doctrine.orm.default_entity_manager')
            ->createQuery($dql)
            ->setParameter('state', SubscriptionStates::STATE_NEW)
            ->setParameter('date', $date, Type::DATETIME)
            ->getResult()
        ;

        return array_map(function($s) {
            /** @var SubscriptionInterface $s */
            return $s->getUser();
        }, $results);
    }
}
