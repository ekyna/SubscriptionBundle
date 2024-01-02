<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Table\Column;

use DateTime;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionStates;
use Ekyna\Bundle\SubscriptionBundle\Service\SubscriptionUtils;
use Ekyna\Component\Commerce\Common\Util\FormatterAwareTrait;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Table\Column\AbstractColumnType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\Core\Type\Column\PropertyType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class SubscriptionExpiresAtType
 * @package Ekyna\Bundle\SubscriptionBundle\Table\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionExpiresAtType extends AbstractColumnType
{
    use FormatterAwareTrait;

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $subscription = $row->getData(null);

        if (!$subscription instanceof SubscriptionInterface) {
            throw new UnexpectedTypeException($subscription, SubscriptionInterface::class);
        }

        if (null === $expiresAt = $subscription->getExpiresAt()) {
            return;
        }

        if (SubscriptionStates::STATE_CANCELLED === $subscription->getState()) {
            $view->vars['value'] = $this->getFormatter()->date($expiresAt);

            return;
        }

        if (new DateTime() < $expiresAt) {
            $theme = 'success';
        } elseif (SubscriptionUtils::shouldBeReminded($subscription)) {
            $theme = 'danger';
        } else {
            $theme = 'warning';
        }

        $view->vars['value'] = sprintf(
            '<span class="label label-%s">%s</span>',
            $theme,
            $this->getFormatter()->date($expiresAt)
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('label', t('field.expires_at', [], 'EkynaUi'));
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
