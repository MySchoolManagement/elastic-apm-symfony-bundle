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

namespace ElasticApmBundle\Listener;

use ElasticApmBundle\Interactor\Config;
use ElasticApmBundle\Interactor\ElasticApmInteractorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseListener implements EventSubscriberInterface
{
    private $config;
    private $interactor;

    public function __construct(
        Config $config,
        ElasticApmInteractorInterface $interactor
    ) {
        $this->config = $config;
        $this->interactor = $interactor;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => [
                ['onKernelResponse', -255],
            ],
        ];
    }

    public function onKernelResponse(KernelResponseEvent $event): void
    {
        if (! $event->isMasterRequest()) {
            return;
        }

        foreach ($this->config->getCustomLabels() as $name => $value) {
            $this->interactor->addLabel((string) $name, $value);
        }

        foreach ($this->config->getCustomContext() as $name => $value) {
            $this->interactor->addCustomContext((string) $name, $value);
        }
    }
}

if (! \class_exists(KernelResponseEvent::class)) {
    if (\class_exists(ResponseEvent::class)) {
        \class_alias(ResponseEvent::class, KernelResponseEvent::class);
    } else {
        \class_alias(FilterResponseEvent::class, KernelResponseEvent::class);
    }
}
