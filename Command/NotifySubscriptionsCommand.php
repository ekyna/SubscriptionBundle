<?php

namespace Ekyna\Bundle\SubscriptionBundle\Command;

use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Component\Sale\Payment\PaymentStates;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class NotifySubscriptionsCommand
 * @package Ekyna\Bundle\SubscriptionBundle\Command
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class NotifySubscriptionsCommand  extends ContainerAwareCommand
{
    const USERS_WITH_SUBSCRIPTION_PAYMENT_REQUIRED_DQL = <<<DQL
SELECT s, u
FROM %s s
JOIN s.user u
WHERE s.state = %s
    AND s.notifiedAt IS NULL
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
            if ($notifier->sendCallUserForPayment($user)) {
                $count++;
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
        $subscriptionState = sprintf("'%s'", SubscriptionStates::STATE_NEW);

        $query = $this
            ->getContainer()
            ->get('doctrine.orm.default_entity_manager')->createQuery(
                sprintf(
                    self::USERS_WITH_SUBSCRIPTION_PAYMENT_REQUIRED_DQL,
                    $subscriptionClass,
                    $subscriptionState
                )
            )
        ;

        $results = $query->getResult();

        return array_map(function($s) {
            /** @var SubscriptionInterface $s */
            return $s->getUser();
        }, $results);
    }
}
