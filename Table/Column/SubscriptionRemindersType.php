<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Table\Column;

use DateTime;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Bundle\SubscriptionBundle\Service\NotificationHelper;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function reset;
use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class SubscriptionRemindersType
 * @package Ekyna\Bundle\SubscriptionBundle\Table\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionRemindersType extends AbstractColumnType
{
    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $subscription = $row->getData(null);

        if (!$subscription instanceof SubscriptionInterface) {
            throw new UnexpectedTypeException($subscription, SubscriptionInterface::class);
        }

        $view->vars['value'] = $this->generate($subscription);
    }

    private function generate(SubscriptionInterface $subscription): string
    {
        if (SubscriptionStates::STATE_CANCELLED === $subscription->getState()) {
            return '';
        }

        $renewal = null;

        // Find renewal posterior to expiration date
        $expiresAt = $subscription->getExpiresAt();
        foreach ($subscription->getRenewals() as $subRenewal) {
            if ($expiresAt > $subRenewal->getStartsAt()) {
                continue;
            }

            // (Should never happen) Abort if posterior renewal is paid
            if ($subRenewal->isPaid()) {
                continue;
            }

            $renewal = $subRenewal;
        }

        $reminders = $subscription->getPlan()->getReminders()->toArray();

        if (empty($reminders)) {
            return '<span class="label label-warning"><span class="fa fa-question-circle"></span></span>';
        }

        if (null === $renewal) {
            if ($subscription->getExpiresAt() > (new DateTime(sprintf('+%d days', (reset($reminders)->getDays()))))) {
                return '';
            }

            return '<span class="label label-danger"><span class="fa fa-exclamation-circle"></span></span>';
        }

        $result = '';
        foreach ($reminders as $reminder) {
            $notification = NotificationHelper::findRenewalNotificationByReminder($renewal, $reminder);

            $theme = null !== $notification ? 'success' : 'danger';

            $result .= sprintf(
                '<strong class="label label-%s">%s</strong> ',
                $theme,
                $reminder->getDays()
            );
        }

        return $result;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', t('reminder.label.plural', [], 'EkynaSubscription'));
    }

    /**
     * @inheritDoc
     */
    public function getBlockPrefix(): string
    {
        return 'text';
    }

    /**
     * @inheritDoc
     */
    public function getParent(): ?string
    {
        return PropertyType::class;
    }
}
