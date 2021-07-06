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

use ElasticApmBundle\Interactor\ElasticApmInteractorInterface;
use ElasticApmBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestListener implements EventSubscriberInterface
{
    private $interactor;
    private $transactionNamingStrategy;

    public function __construct(
        ElasticApmInteractorInterface $interactor,
        TransactionNamingStrategyInterface $transactionNamingStrategy
    ) {
        $this->interactor = $interactor;
        $this->transactionNamingStrategy = $transactionNamingStrategy;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['setTransactionName', -10],
            ],
        ];
    }

    public function setTransactionName(KernelRequestEvent $event): void
    {
        if (! $this->isEventValid($event)) {
            return;
        }

        $transactionName = $this->transactionNamingStrategy->getTransactionName($event->getRequest());

        $this->interactor->setTransactionName($transactionName);
    }

    /**
     * Make sure we should consider this event. Example: make sure it is a master request.
     */
    private function isEventValid(KernelRequestEvent $event): bool
    {
        return HttpKernelInterface::MASTER_REQUEST === $event->getRequestType();
    }
}

if (! \class_exists(KernelRequestEvent::class)) {
    if (\class_exists(RequestEvent::class)) {
        \class_alias(RequestEvent::class, KernelRequestEvent::class);
    } else {
        \class_alias(GetResponseEvent::class, KernelRequestEvent::class);
    }
}
