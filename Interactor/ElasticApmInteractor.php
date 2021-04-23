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
use Elastic\Apm\ElasticApm;
use Elastic\Apm\SpanInterface;
use Elastic\Apm\TransactionInterface;

class ElasticApmInteractor implements ElasticApmInteractorInterface
{
    public function setTransactionName(string $name): bool
    {
        $transaction = ElasticApm::getCurrentTransaction();
        $transaction->setName($name);

        return true;
    }

    public function addLabel(string $name, $value): bool
    {
        // limited to 1024 bytes in label key/value
        ElasticApm::getCurrentTransaction()->context()->setLabel(
            mb_substr($name, 0, 1024),
            mb_substr((string) $value, 0, 1024)
        );

        return true;
    }

    public function addCustomContext(string $name, $value): bool
    {
        return $this->addLabel($name, $value);
    }

    public function noticeThrowable(\Throwable $e, string $message = null): void
    {
        ElasticApm::createErrorFromThrowable($e);
    }

    public function beginTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface
    {
        return ElasticApm::beginTransaction($name, $type, $timestamp, $distributedTracingData);
    }

    public function beginCurrentTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface
    {
        return ElasticApm::beginCurrentTransaction($name, $type, $timestamp, $distributedTracingData);
    }

    public function endCurrentTransaction(?float $duration = null): bool
    {
        ElasticApm::getCurrentTransaction()->end($duration);

        return true;
    }

    public function getCurrentTransaction(): ?TransactionInterface
    {
        return ElasticApm::getCurrentTransaction();
    }

    public function beginCurrentSpan(string $name, string $type, ?string $subtype = null, ?string $action = null, ?float $timestamp = null): ?SpanInterface
    {
        return ElasticApm::getCurrentTransaction()->beginCurrentSpan($name, $type, $subtype, $action, $timestamp);
    }

    public function endCurrentSpan(?float $duration = null): bool
    {
        ElasticApm::getCurrentTransaction()->getCurrentSpan()->end($duration);

        return true;
    }

    public function captureCurrentSpan(string $name, string $type, Closure $callback, ?string $subtype = null, ?string $action = null, ?float $timestamp = null)
    {
        return ElasticApm::getCurrentTransaction()->captureCurrentSpan($name, $type, $callback, $subtype, $action, $timestamp);
    }

    public function setUserAttributes(?string $id, ?string $email, ?string $username): bool
    {
        if (null !== $id) {
            $this->addLabel('user_id', $id);
        }

        if (null !== $email) {
            $this->addLabel('user_email', $email);
        }

        if (null !== $username) {
            $this->addLabel('user_username', $username);
        }

        return true;
    }
}
