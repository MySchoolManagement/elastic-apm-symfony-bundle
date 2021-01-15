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

use Elastic\Apm\DistributedTracingData;
use Elastic\Apm\TransactionInterface;

/**
 * This is the service that talks to APM.
 */
interface ElasticApmInteractorInterface
{
    /**
     * Set custom name for current transaction.
     *
     * {@link https://www.elastic.co/guide/en/apm/agent/php/current/public-api.html#api-transaction-interface-set-name}
     */
    public function setTransactionName(string $name): bool;

    /**
     * {@link https://www.elastic.co/guide/en/apm/agent/php/current/public-api.html#api-transaction-interface-set-label}.
     *
     * @param string|int|float $value should be a scalar
     */
    public function addLabel(string $name, $value): bool;

    /**
     * WARNING: The agent does not allow setting of a custom context yet. These are attached as labels which can have
     * an impact on the size and performance of your indexes.
     *
     * @param string|int|float $value should be a scalar
     */
    public function addCustomContext(string $name, $value): bool;

    /**
     * Use these calls to collect errors that the PHP agent does not collect automatically and to set the callback for
     * your own error and exception handler.
     */
    public function noticeThrowable(\Throwable $e, string $message = null): void;

    /**
     * Starts a new transaction.
     */
    public function beginTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface;

    /**
     * Starts a new transaction and makes it current.
     *
     * {@link https://www.elastic.co/guide/en/apm/agent/php/current/public-api.html#api-elasticapm-class-begin-current-transaction}
     */
    public function beginCurrentTransaction(string $name, string $type, ?float $timestamp = null, ?DistributedTracingData $distributedTracingData = null): ?TransactionInterface;

    /**
     * Stop instrumenting the current transaction immediately.
     *
     * {@link https://www.elastic.co/guide/en/apm/agent/php/current/public-api.html#api-transaction-interface-end}
     */
    public function endCurrentTransaction(?float $duration = null): bool;

    /**
     * WARNING: The agent does not allow setting of user attributes yet. These are attached as labels which can have an
     * impact on the size and performance of your indexes.
     */
    public function setUserAttributes(?string $id, ?string $email, ?string $username): bool;
}
