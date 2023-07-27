<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Action\Renewal;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\RepositoryTrait;
use Ekyna\Bundle\ResourceBundle\Action\RoutingActionInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\ReminderInterface;
use Ekyna\Bundle\SubscriptionBundle\Model\RenewalInterface;
use Ekyna\Bundle\SubscriptionBundle\Service\Notifier;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;

/**
 * Class Notify
 * @package Ekyna\Bundle\SubscriptionBundle\Action\Renewal
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotifyAction extends AbstractAction implements AdminActionInterface, RoutingActionInterface
{
    use HelperTrait;
    use RepositoryTrait;

    public function __construct(
        private readonly Notifier $notifier
    ) {
    }

    public function __invoke(): Response
    {
        $renewal = $this->context->getResource();

        if (!$renewal instanceof RenewalInterface) {
            throw new UnexpectedTypeException($renewal, RenewalInterface::class);
        }

        $reminder = $this
            ->getRepository(ReminderInterface::class)
            ->find($this->request->attributes->getInt('reminderId'));

        if (null === $reminder) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        // TODO Flash result (send or not)
        $this->notifier->notify($renewal, $reminder);

        return $this->redirect(
            $this->generateResourcePath($renewal)
        );
    }

    public static function configureAction(): array
    {
        return [
            'name'       => 'subscription_renewal_notify',
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_notify',
                'path'     => '/notify/{reminderId}',
                'resource' => true,
                'methods'  => ['GET'],
            ],
            'button'     => [
                'label'        => 'button.notify',
                'trans_domain' => 'EkynaUi',
                'icon'         => 'envelope',
            ],
        ];
    }

    public static function buildRoute(Route $route, array $options): void
    {
        $route->addRequirements([
            'reminderId' => '\d+',
        ]);
    }
}
