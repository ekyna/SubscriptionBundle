<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\EventListener;

use DateTime;
use Ekyna\Bundle\SubscriptionBundle\Event\SubscriptionEvents;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\RenewalUpdater;
use Ekyna\Component\Commerce\Common\Util\FormatterFactory;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function is_null;
use function preg_replace;

/**
 * Class RenewalListener
 * @package Ekyna\Bundle\SubscriptionBundle\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class RenewalListener
{
    public function __construct(
        private readonly PersistenceHelperInterface $persistenceHelper,
        private readonly RenewalUpdater             $renewalUpdater,
        private readonly FormatterFactory           $formatterFactory,
        private readonly TranslatorInterface        $translator
    ) {
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $renewal = $this->getRenewalFromEvent($event);

        if ($this->renewalUpdater->update($renewal)) {
            $this->persistenceHelper->persistAndRecompute($renewal, false);
        }

        $this->updateItemDescription($renewal);

        if ($this->persistenceHelper->isScheduledForInsert($renewal->getSubscription())) {
            return;
        }

        $this->scheduleSubscriptionRenewalChange($renewal->getSubscription());
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $renewal = $this->getRenewalFromEvent($event);

        if ($this->renewalUpdater->update($renewal)) {
            $this->persistenceHelper->persistAndRecompute($renewal, false);
        }

        if ($this->persistenceHelper->isChanged($renewal, ['startsAt', 'endsAt', 'paid'])) {
            $this->updateItemDescription($renewal);

            $this->scheduleSubscriptionRenewalChange($renewal->getSubscription());
        }
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $renewal = $this->getRenewalFromEvent($event);

        if (null === $subscription = $renewal->getSubscription()) {
            $cs = $this->persistenceHelper->getChangeSet($renewal, 'subscription');
            if (empty($cs) || is_null($cs[0])) {
                throw new RuntimeException('Failed to retrieve subscription');
            }
            $subscription = $cs[0];
        }

        if ($this->persistenceHelper->isScheduledForRemove($subscription)) {
            return;
        }

        $this->scheduleSubscriptionRenewalChange($subscription);
    }

    protected function scheduleSubscriptionRenewalChange(SubscriptionInterface $subscription): void
    {
        $this->persistenceHelper->scheduleEvent($subscription, SubscriptionEvents::RENEWAL_CHANGE);
    }

    protected function updateItemDescription(RenewalInterface $renewal): void
    {
        if (null === $item = $renewal->getOrderItem()) {
            return;
        }

        while ($item->isPrivate()) {
            $item = $item->getParent();
        }

        $formatter = $this->formatterFactory->create($item->getRootSale()->getLocale());

        $date = strtr($formatter->date(new DateTime('2000-01-02')), [
            '2000' => '\d{4}',
            '01'   => '\d{2}',
            '02'   => '\d{2}',
        ]);

        $pattern = $this->translator->trans('field.date_range', [
            '{from}' => $date,
            '{to}'   => $date,
        ], 'EkynaUi');

        $description = trim(preg_replace("~$pattern~", '', (string)$item->getDescription()), " \t\n\r\0\x0B.");

        if (!empty($description)) {
            $description .= '. ';
        }

        $description .= $this->translator->trans('field.date_range', [
            '{from}' => $formatter->date($renewal->getStartsAt()),
            '{to}'   => $formatter->date($renewal->getEndsAt()),
        ], 'EkynaUi');

        $item->setDescription($description);

        $this->persistenceHelper->persistAndRecompute($item, false);
    }

    protected function getRenewalFromEvent(ResourceEventInterface $event): RenewalInterface
    {
        $renewal = $event->getResource();

        if (!$renewal instanceof RenewalInterface) {
            throw new UnexpectedTypeException($renewal, RenewalInterface::class);
        }

        return $renewal;
    }
}
