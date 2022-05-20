<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Factory;

use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalUpdater;
use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\ResourceFactory;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class RenewalFactory
 * @package Ekyna\Bundle\SubscriptionBundle\Factory
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalFactory extends ResourceFactory implements RenewalFactoryInterface
{
    private RenewalUpdater $renewalUpdater;

    public function __construct(RenewalUpdater $renewalUpdater)
    {
        $this->renewalUpdater = $renewalUpdater;
    }

    public function createFromContext(Context $context): ResourceInterface
    {
        /** @var RenewalInterface $renewal */
        $renewal = parent::createFromContext($context);

        $this->renewalUpdater->update($renewal);

        return $renewal;
    }
}
