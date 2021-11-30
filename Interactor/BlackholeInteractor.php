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
 * This interactor throw away any call.
 *
 * It can be used to avoid conditional log calls.
 */
class BlackholeInteractor implements ElasticApmInteractorInterface
{
    public function setTransactionName(string $name): bool
    {
        return true;
    }

    public function addLabel(string $name, $value): bool
    {
        return true;
    }

    public function addCustomContext(string $name, $value): bool
    {
        return true;
    }

    public function noticeThrowable(\Throwable $e): void
    {
    }

    public function beginTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface
    {
        return null;
    }

    public function beginCurrentTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface
    {
        return null;
    }

    public function endCurrentTransaction(?float $duration = null): bool
    {
        return true;
    }

    public function getCurrentTransaction(): ?TransactionInterface
    {
        return null;
    }

    public function beginCurrentSpan(string $name, string $type, ?string $subtype = null, ?string $action = null, ?float $timestamp = null): ?SpanInterface
    {
        return null;
    }

    public function endCurrentSpan(?float $duration = null): bool
    {
        return true;
    }

    public function captureCurrentSpan(string $name, string $type, Closure $callback, ?string $subtype = null, ?string $action = null, ?float $timestamp = null)
    {
        return $callback(null);
    }

    public function setUserAttributes(?string $id, ?string $email, ?string $username): bool
    {
        return true;
    }

    public function addContextFromConfig(): void
    {
    }
}
