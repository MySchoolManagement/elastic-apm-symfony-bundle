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
use Elastic\Apm\TransactionBuilderInterface;
use Elastic\Apm\TransactionInterface;

class ElasticApmInteractor implements ElasticApmInteractorInterface
{
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

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
            is_string($value) ? mb_substr($value, 0, 1024) : $value
        );

        return true;
    }

    public function addCustomContext(string $name, $value): bool
    {
        return $this->addLabel($name, $value);
    }

    public function noticeThrowable(\Throwable $e): void
    {
        ElasticApm::createErrorFromThrowable($e);

        if ($this->config->shouldUnwrapExceptions() && null !== $e->getPrevious()) {
            ElasticApm::createErrorFromThrowable($e->getPrevious());
        }
    }

    public function beginTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface
    {
        if (version_compare(ElasticApm::VERSION, '1.3.0', '<')) {
            return ElasticApm::beginTransaction($name, $type, $timestamp, $distributedTracingData ? $distributedTracingData->serializeToString() : null);
        }

        return $this->createTransactionBuilder($name, $type, $timestamp, $distributedTracingData)
            ->begin();
    }

    public function beginCurrentTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface
    {
        if (version_compare(ElasticApm::VERSION, '1.3.0', '<')) {
            return ElasticApm::beginCurrentTransaction($name, $type, $timestamp, $distributedTracingData ? $distributedTracingData->serializeToString() : null);
        }

        return $this->createTransactionBuilder($name, $type, $timestamp, $distributedTracingData)
            ->asCurrent()
            ->begin();
    }

    private function createTransactionBuilder(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): TransactionBuilderInterface
    {
        $t = ElasticApm::newTransaction($name, $type);

        if (null !== $timestamp) {
            $t->timestamp($timestamp);
        }

        if (null !== $distributedTracingData) {
            $t->distributedTracingHeaderExtractor(
                function ($a) use ($distributedTracingData) {
                    if ('traceparent' === $a) {
                        return $distributedTracingData->serializeToString();
                    }

                    return null;
                }
            );
        }

        return $t;
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

    public function addContextFromConfig(): void
    {
        if ($this->config->shouldCollectMemoryUsage()) {
            $this->addLabel($this->config->getMemoryUsageLabelName(), memory_get_peak_usage(true));
        }

        foreach ($this->config->getCustomLabels() as $name => $value) {
            $this->addLabel((string) $name, $value);
        }

        foreach ($this->config->getCustomContext() as $name => $value) {
            $this->addCustomContext((string) $name, $value);
        }
    }
}
