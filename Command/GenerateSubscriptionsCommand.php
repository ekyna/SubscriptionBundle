<?php

namespace Ekyna\Bundle\SubscriptionBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GenerateSubscriptionsCommand
 * @package Ekyna\Bundle\SubscriptionBundle\Command
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class GenerateSubscriptionsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:subscription:generate')
            ->setDescription('Generates users subscriptions.')
            ->addArgument('years', InputArgument::IS_ARRAY|InputArgument::OPTIONAL, 'The years for which subscriptions must be generated.', array(date('Y')))
            ->addOption('notify', null, InputOption::VALUE_NONE, 'Whether to notify the user after subscription generation or not.')
            ->setHelp(<<<EOT
The <info>ekyna:subscription:generate</info> generates subscriptions for the given years:

  <info>php app/console ekyna:subscription:generate 2015 2014</info>
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $debug = !$input->getOption('no-debug');
        $years = $input->getArgument('years');

        $generator = $this->getContainer()->get('ekyna_subscription.subscription.generator');

        foreach ($years as $year) {
            try {
                $output->writeln(sprintf('<info>Generating subscription for year %s ...</info>', $year));
                $count = $generator->generateByYear($year);
                $output->writeln(sprintf('%d subscriptions generated.', $count));
            } catch(\Exception $e) {
                if ($debug) {
                    $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
                }
            }
        }

        if ($input->getOption('notify')) {
            $command = $this->getApplication()->find('ekyna:subscription:notify');
            $i = new ArrayInput(array(
                'command' => 'ekyna:subscription:notify',
                '--env'   => $input->getOption('env'),
            ));
            $command->run($i, $output);
        }

        // TODO notify admin about result
    }
}
