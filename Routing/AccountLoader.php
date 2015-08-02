<?php

namespace Ekyna\Bundle\SubscriptionBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class AccountLoader
 * @package Ekyna\Bundle\SubscriptionBundle\Routing
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AccountLoader extends Loader
{
    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var array
     */
    private $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "account" routes loader twice.');
        }

        $collection = new RouteCollection();

        // Base account
        if ($this->config['account']['enable']) {

            $prefix = '/my-account/subscriptions';

            $resource = '@EkynaSubscriptionBundle/Resources/config/routing/front/account.yml';
            $type     = 'yaml';
            $routes = $this->import($resource, $type);
            $routes->addPrefix($prefix);
            $collection->addCollection($routes);
        }

        $this->loaded = true;

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return 'subscription' === $type;
    }
}
