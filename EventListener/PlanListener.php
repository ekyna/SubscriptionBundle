<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use Doctrine\Common\Cache\Psr6\DoctrineProvider;
use Ekyna\Bundle\SubscriptionBundle\Exception\ForbiddenOperationException;
use Ekyna\Bundle\SubscriptionBundle\Model\PlanInterface;
use Ekyna\Bundle\SubscriptionBundle\Repository\PlanRepository;
use Ekyna\Bundle\SubscriptionBundle\Repository\SubscriptionRepositoryInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Exception;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class PlanListener
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PlanListener
{
    public function __construct(
        private readonly PersistenceHelperInterface $persistenceHelper,
        private readonly SubscriptionRepositoryInterface $subscriptionRepository,
        private readonly ?CacheItemPoolInterface $resultCache
    ) {
    }

    public function onInsert(): void
    {
        // Purge plans identifiers cache
        $this->clearCache();
    }

    /**
     * @throws Exception
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        $plan = $event->getResource();

        if (!$plan instanceof PlanInterface) {
            throw new UnexpectedTypeException($plan, PlanInterface::class);
        }

        // Prevent product change if any subscription use this plan
        if ($this->persistenceHelper->isChanged($plan, 'product')) {
            if ($this->subscriptionRepository->existsWithPlan($plan)) {
                throw new ForbiddenOperationException(
                    'Plan product cannot be changed as some subscription already use it.'
                );
            }
        }

        // Purge plans identifiers cache
        $this->clearCache();
    }

    private function clearCache(): void
    {
        if (null === $this->resultCache) {
            return;
        }

        $cache = DoctrineProvider::wrap($this->resultCache);

        $cache->delete(PlanRepository::IDENTIFIERS_CACHE_KEY);
    }
}
