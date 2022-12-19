<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Service;

use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\SubscriptionBundle\Action\Subscription\CreateAction;
use Ekyna\Bundle\SubscriptionBundle\Form\Type\SubscribeType;
use Ekyna\Bundle\SubscriptionBundle\Model\Subscribe;
use Ekyna\Bundle\SubscriptionBundle\Model\SubscriptionInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class SubscriptionHelper
 * @package Ekyna\Bundle\SubscriptionBundle\Service
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionHelper
{
    public function __construct(
        private readonly ResourceHelper       $resourceHelper,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function getSubscribeForm(CustomerInterface $customer = null): FormInterface
    {
        $action = $this
            ->resourceHelper
            ->generateResourcePath(
                SubscriptionInterface::class,
                CreateAction::class
            );

        $data = new Subscribe();
        $data->setCustomer($customer);

        return $this->formFactory->create(SubscribeType::class, $data, [
            'action'          => $action,
            'csrf_protection' => false,
        ]);
    }
}
