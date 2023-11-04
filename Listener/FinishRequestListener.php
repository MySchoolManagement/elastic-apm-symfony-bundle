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
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class FinishRequestListener implements EventSubscriberInterface
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
            // We set the transaction name at the end to be sure the listener gets called.
            // When using KernelEvents::REQUEST it could be skipped due to the stop propagation.
            // We need to be called before the RouterListener to be able to set the transaction name.
            // We also use the finish request as this service is not relevant for the request and
            // in system not using php-fpm this could be done after the request was sent to the client.
            KernelEvents::FINISH_REQUEST => [
                ['onFinishRequest', 255],
            ],
        ];
    }

    public function onFinishRequest(FinishRequestEvent $event): void
    {
        if (! $this->isMainRequest($event)) {
            return;
        }

        $this->setTransactionName($event);
        $this->interactor->addContextFromConfig();
    }

    private function setTransactionName(FinishRequestEvent $event): void
    {
        $transactionName = $this->transactionNamingStrategy->getTransactionName($event->getRequest());

        $this->interactor->setTransactionName($transactionName);
    }

    private function isMainRequest(FinishRequestEvent $event): bool
    {
        return method_exists($event, 'isMainRequest') ? $event->isMainRequest() : $event->isMasterRequest();
    }
}
