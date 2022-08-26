<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Ekyna\Bundle\CommerceBundle\Model\OrderInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionGenerator;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionUtils;
use Ekyna\Component\Commerce\Order\Repository\OrderRepositoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_map;
use function implode;

/**
 * Class GenerateSubscriptionCommand
 * @package Ekyna\Bundle\SubscriptionBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class GenerateSubscriptionCommand extends Command
{
    protected static $defaultName = 'ekyna:subscription:generate';

    private OrderRepositoryInterface $repository;
    private SubscriptionGenerator    $generator;
    private ResourceManagerInterface $manager;
    private Connection               $connection;

    private InputInterface  $input;
    private OutputInterface $output;

    private ?Statement $query = null;

    public function __construct(
        OrderRepositoryInterface $repository,
        SubscriptionGenerator    $generator,
        ResourceManagerInterface $manager,
        Connection               $connection
    ) {
        parent::__construct();

        $this->repository = $repository;
        $this->generator = $generator;
        $this->manager = $manager;
        $this->connection = $connection;
    }

    protected function configure(): void
    {
        $this
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'The order to generate subscription for.')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'The number of orders to generate subscription for.', 20);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        /*if (!$input->getOption('no-debug')) {
            $this->generator->setOnCreateRenewal([$this, 'onRenewalCreate']);
        }*/

        if (0 < $id = (int)$input->getOption('id')) {
            $order = $this->repository->find($id);

            $this->generateForOrder($order);

            return Command::SUCCESS;
        }

        $count = 0;
        $limit = (int)$input->getOption('limit');
        while (null !== $id = $this->getNextOrderId()) {
            $order = $this->repository->find($id);

            $this->generateForOrder($order);

            $count++;
            if (0 < $limit && $limit <= $count) {
                break;
            }
        }

        return Command::SUCCESS;
    }

    public function onRenewalCreate(RenewalInterface $renewal): void
    {
        $order = $renewal->getOrder();
        if ($order->getAcceptedAt()->format('Y') === $renewal->getStartsAt()->format('Y')) {
            return;
        }

        $io = new SymfonyStyle($this->input, $this->output);
        $io->definitionList(
            ['Order id' => $renewal->getOrder()->getId()],
            ['Order number' => $renewal->getOrder()->getNumber()],
            ['Accepted at' => $renewal->getOrder()->getAcceptedAt()->format('Y-m-d')],
            ['Starts at' => $renewal->getStartsAt()->format('Y-m-d')],
            ['Ends at' => $renewal->getEndsAt()->format('Y-m-d')],
            ['Count' => $renewal->getCount()],
        );

        $previous = SubscriptionUtils::getRenewals($renewal->getSubscription(), $renewal);
        if (!empty($previous)) {
            $table = new Table($this->output);
            $table->setHeaders(['Order id', 'Order number', 'Accepted at', 'Starts at', 'Ends at', 'Count']);
            foreach ($previous as $r) {
                $table->addRow([
                    $r->getOrder()->getId(),
                    $r->getOrder()->getNumber(),
                    $r->getOrder()->getAcceptedAt()->format('Y-m-d'),
                    $r->getStartsAt()->format('Y-m-d'),
                    $r->getEndsAt()->format('Y-m-d'),
                    $r->getCount(),
                ]);
            }
            $table->render();
        }

        $helper = $this->getHelper('question');
        $confirm = new ConfirmationQuestion('Edit this renewal ? (n)', false);
        if (!$helper->ask($this->input, $this->output, $confirm)) {
            $this->output->writeln("\n\n");

            return;
        }

        $this->output->writeln("\n\n");

        throw new Exception('Wait');
    }

    private function generateForOrder(OrderInterface $order): void
    {
        $subscriptions = $this->generator->generateFromOrder($order);

        foreach ($subscriptions as $subscription) {
            $this->manager->persist($subscription);
        }

        $this->manager->flush();
        $this->manager->clear();
    }

    private function getNextOrderId(): ?int
    {
        $id = $this
            ->getQuery()
            ->executeQuery()
            ->fetchOne();

        return $id ? (int)$id : null;
    }

    private function getQuery(): Statement
    {
        if (null !== $this->query) {
            return $this->query;
        }

        $statesToParameter = function (array $states): string {
            return '(' . implode(', ', array_map(fn(string $val): string => "'$val'", $states)) . ')';
        };

        $paymentStates = $statesToParameter(SubscriptionGenerator::RENEW_PAYMENT_STATES);
        $shipmentStates = $statesToParameter(SubscriptionGenerator::RENEW_SHIPMENT_STATES);

        $sql = <<<SQL
SELECT o.id
FROM (
    SELECT IFNULL(i.order_id, IFNULL(i1.order_id, IFNULL(i2.order_id, IFNULL(i3.order_id, IFNULL(i4.order_id, i5.order_id))))) as order_id
    FROM commerce_order_item i
    LEFT JOIN subscription_renewal r ON r.order_item_id = i.id
    LEFT JOIN commerce_order_item i1 ON i1.id = i.parent_id
    LEFT JOIN commerce_order_item i2 ON i2.id = i1.parent_id
    LEFT JOIN commerce_order_item i3 ON i3.id = i2.parent_id
    LEFT JOIN commerce_order_item i4 ON i4.id = i3.parent_id
    LEFT JOIN commerce_order_item i5 ON i5.id = i4.parent_id
    WHERE r.id IS NULL
    AND i.subject_provider = 'product'
    AND i.subject_identifier IN (
        SELECT DISTINCT s.product_id FROM subscription_plan s
    )
) as items
JOIN commerce_order o ON o.id=items.order_id
WHERE o.is_sample = 0
  AND o.payment_state IN $paymentStates
  AND o.shipment_state IN $shipmentStates
ORDER BY o.accepted_at
LIMIT 1
SQL;

        return $this->query = $this->connection->prepare($sql);
    }
}
