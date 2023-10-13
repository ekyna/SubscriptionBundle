<?php

declare(strict_types=1);

namespace Ekyna\Bundle\SubscriptionBundle\Controller\Admin\Notification;

use Ekyna\Bundle\AdminBundle\Service\Menu\MenuBuilder;
use Ekyna\Bundle\SubscriptionBundle\Repository\NotificationRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Class IndexController
 * @package Ekyna\Bundle\SubscriptionBundle\Controller\Admin\Notification
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class IndexController
{
    public function __construct(
        private readonly MenuBuilder            $menuBuilder,
        private readonly NotificationRepository $repository,
        private readonly Environment            $twig,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this
            ->menuBuilder
            ->breadcrumbAppend([
                'name'         => 'subscription_notifications',
                'label'        => 'notification.label.plural',
                'route'        => false,
                'trans_domain' => 'EkynaSubscription',
            ]);

        $query = $this
            ->repository
            ->createQueryBuilder('n')
            ->orderBy('n.notifiedAt', 'DESC');

        $page = $request->query->getInt('page', 1);

        $notifications = new Pagerfanta(new QueryAdapter($query));
        $notifications
            ->setNormalizeOutOfRangePages(true)
            ->setMaxPerPage(15)
            ->setCurrentPage($page);

        $content = $this
            ->twig
            ->render('@EkynaSubscription/Admin/Notification/notification.html.twig', [
                'notifications' => $notifications,
            ]);

        return new Response($content);
    }
}