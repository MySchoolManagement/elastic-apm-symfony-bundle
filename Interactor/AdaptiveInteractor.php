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

namespace ElasticApmBundle\Interactor;

use Closure;
use Elastic\Apm\DistributedTracingData;
use Elastic\Apm\SpanInterface;
use Elastic\Apm\TransactionInterface;

/**
 * This interactor does never assume that the extension is installed. It will check for the existence of the extension
 * every time this is class is instantiated. This is a good interactor to use when you want to enable and disable the
 * extension without rebuilding your container.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class AdaptiveInteractor implements ElasticApmInteractorInterface
{
    /**
     * @var ElasticApmInteractorInterface
     */
    private $interactor;

    public function __construct(ElasticApmInteractorInterface $real, ElasticApmInteractorInterface $fake)
    {
        $this->interactor = \extension_loaded('elastic_apm') && \class_exists('Elastic\\Apm\\ElasticApm') ? $real : $fake;
    }

    public function setTransactionName(string $name): bool
    {
        return $this->interactor->setTransactionName($name);
    }

    public function addLabel(string $name, $value): bool
    {
        return $this->interactor->addLabel($name, $value);
    }

    public function addCustomContext(string $name, $value): bool
    {
        return $this->interactor->addCustomContext($name, $value);
    }

    public function noticeThrowable(\Throwable $e): void
    {
        $this->interactor->noticeThrowable($e);
    }

    public function beginTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface
    {
        return $this->interactor->beginTransaction($name, $type, $timestamp, $distributedTracingData);
    }

    public function beginCurrentTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface
    {
        return $this->interactor->beginCurrentTransaction($name, $type, $timestamp, $distributedTracingData);
    }

    public function endCurrentTransaction(?float $duration = null): bool
    {
        return $this->interactor->endCurrentTransaction($duration);
    }

    public function getCurrentTransaction(): ?TransactionInterface
    {
        return $this->interactor->getCurrentTransaction();
    }

    public function beginCurrentSpan(string $name, string $type, ?string $subtype = null, ?string $action = null, ?float $timestamp = null): ?SpanInterface
    {
        $current = $this->interactor->getCurrentTransaction();

        if (null !== $current) {
            return $current->beginCurrentSpan($name, $type, $subtype, $action, $timestamp);
        }

        return null;
    }

    public function endCurrentSpan(?float $duration = null): bool
    {
        return $this->interactor->endCurrentSpan($duration);
    }

    public function captureCurrentSpan(string $name, string $type, Closure $callback, ?string $subtype = null, ?string $action = null, ?float $timestamp = null)
    {
        return $this->interactor->captureCurrentSpan($name, $type, $callback, $subtype, $action, $timestamp);
    }

    public function setUserAttributes(?string $id, ?string $email, ?string $username): bool
    {
        return $this->interactor->setUserAttributes($id, $email, $username);
    }

    public function addContextFromConfig(): void
    {
        $this->interactor->addContextFromConfig();
    }
}
