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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LoggingInteractorDecorator implements ElasticApmInteractorInterface
{
    private $interactor;
    private $logger;

    public function __construct(ElasticApmInteractorInterface $interactor, LoggerInterface $logger = null)
    {
        $this->interactor = $interactor;
        $this->logger = $logger ?? new NullLogger();
    }

    public function setApplicationName(string $name): bool
    {
        $this->logger->debug('Setting Elastic APM Application name to {name}', ['name' => $name]);

        return $this->interactor->setApplicationName($name);
    }

    public function setTransactionName(string $name): bool
    {
        $this->logger->debug('Setting Elastic APM transaction name to {name}', ['name' => $name]);

        return $this->interactor->setTransactionName($name);
    }

    public function addLabel(string $name, $value): bool
    {
        $this->logger->debug('Adding Elastic APM label {name}: {value}', ['name' => $name, 'value' => $value]);

        return $this->interactor->addLabel($name, $value);
    }

    public function addCustomContext(string $name, $value): bool
    {
        $this->logger->debug('Adding Elastic APM custom context {name}: {value}', ['name' => $name, 'value' => $value]);

        return $this->interactor->addCustomContext($name, $value);
    }

    public function noticeThrowable(\Throwable $e, string $message = null): void
    {
        $this->logger->debug('Sending exception to Elastic APM', [
            'message' => $message,
            'exception' => $e,
        ]);
        $this->interactor->noticeThrowable($e, $message);
    }

    public function endCurrentTransaction(?float $duration = null): bool
    {
        $this->logger->debug('Ending the current Elastic APM transaction');

        return $this->interactor->endCurrentTransaction($duration);
    }

    public function beginTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface
    {
        $this->logger->debug('Starting a new Elastic APM transaction for app {name}', ['name' => $name]);

        return $this->interactor->beginTransaction($name, $type, $timestamp, $distributedTracingData);
    }

    public function beginCurrentTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface
    {
        $this->logger->debug('Starting a new Elastic APM transaction and setting to current for app {name}', ['name' => $name]);

        return $this->interactor->beginCurrentTransaction($name, $type, $timestamp, $distributedTracingData);
    }

    public function getCurrentTransaction(): ?TransactionInterface
    {
        $this->logger->debug('Getting active transaction');

        return $this->interactor->getCurrentTransaction();
    }

    public function beginCurrentSpan(string $name, string $type, ?string $subtype = null, ?string $action = null, ?float $timestamp = null): ?SpanInterface
    {
        $this->logger->debug('Starting new span on current transaction and making it current');

        return $this->interactor->beginCurrentSpan($name, $type, $subtype, $action, $timestamp);
    }

    public function endCurrentSpan(?float $duration = null): bool
    {
        $this->logger->debug('Ending current span on active transaction');

        return $this->interactor->endCurrentSpan($duration);
    }

    public function captureCurrentSpan(string $name, string $type, Closure $callback, ?string $subtype = null, ?string $action = null, ?float $timestamp = null)
    {
        $this->logger->debug('Starting new span capture');

        return $this->interactor->captureCurrentSpan($name, $type, $callback, $subtype, $action, $timestamp);
    }

    public function setUserAttributes(?string $id, ?string $email, ?string $username): bool
    {
        $this->logger->debug('Setting Elastic APM user attributes');

        return $this->interactor->setUserAttributes($id, $email, $username);
    }
}
