<?php

declare(strict_types=1);

/*
 * This file is part of Ekino New Relic bundle.
 *
 * (c) Ekino - Thomas Rabaix <thomas.rabaix@ekino.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ElasticApmBundle;

use ElasticApmBundle\Listener\DeprecationListener;
use ElasticApmBundle\Listener\WarningListener;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ElasticApmBundle extends Bundle
{
    /**
     * @return void
     */
    public function boot()
    {
        parent::boot();

        if ($this->container->has(DeprecationListener::class)) {
            $this->container->get(DeprecationListener::class)->register();
        }

        if ($this->container->has(WarningListener::class)) {
            $this->container->get(WarningListener::class)->register();
        }
    }

    /**
     * @return void
     */
    public function shutdown()
    {
        if ($this->container->has(DeprecationListener::class)) {
            $this->container->get(DeprecationListener::class)->unregister();
        }

        if ($this->container->has(WarningListener::class)) {
            $this->container->get(WarningListener::class)->unregister();
        }

        parent::shutdown();
    }
}
