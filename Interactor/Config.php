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

/**
 * This value object contains data and configuration that should be passed to the interactors.
 */
class Config
{
    private $customLabels;
    private $customContext;

    public function __construct(array $customLabels, array $customContext)
    {
        $this->customLabels = $customLabels;
        $this->customContext = $customContext;
    }

    public function setCustomLabels(array $customLabels): void
    {
        $this->customLabels = $customLabels;
    }

    /**
     * @param string|int|float $value or any scalar value
     */
    public function addCustomLabels(string $name, $value): void
    {
        $this->customLabels[$name] = $value;
    }

    public function getCustomLabels(): array
    {
        return $this->customLabels;
    }

    public function setCustomContext(array $customContext): void
    {
        $this->customContext = $customContext;
    }

    /**
     * @param string|int|float $value or any scalar value
     */
    public function addCustomContext(string $name, $value): void
    {
        $this->customContext[$name] = $value;
    }

    public function getCustomContext(): array
    {
        return $this->customContext;
    }
}
