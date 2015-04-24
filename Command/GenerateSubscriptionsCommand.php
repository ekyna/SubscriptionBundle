<?php

namespace Ekyna\Bundle\SubscriptionBundle\Command;

use Ekyna\Bundle\SubscriptionBundle\Util\Year;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateSubscriptionsCommand
 * @package Ekyna\Bundle\SubscriptionBundle\Command
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class GenerateSubscriptionsCommand  extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:subscription:generate')
            ->setDescription('Creates a super admin user.')
            ->addArgument('year', InputArgument::OPTIONAL, 'The year for which subscriptions must be generated.', date('Y'))
            ->addOption('notify', null, InputOption::VALUE_NONE, 'Whether to notify the users or not.')
            ->setHelp(<<<EOT
The <info>ekyna:subscription:generate</info> generates subscriptions for the given year, and notifies the users:

  <info>php app/console ekyna:subscription:generate 2015 --notify</info>
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $year = Year::validate($input->getArgument('year'));

        $output->writeln(sprintf('<info>Generating subscription for year %s</info>', $year));

        $count = $this->getContainer()
            ->get('ekyna_subscription.generator')
            ->generateByYear($year)
        ;

        $output->writeln(sprintf('%d subscriptions generated.', $count));
    }
}
