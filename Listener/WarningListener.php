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

use ElasticApmBundle\Exception\WarningException;
use ElasticApmBundle\Interactor\Config;
use ElasticApmBundle\Interactor\ElasticApmInteractorInterface;

class WarningListener
{
    private $isRegistered = false;
    private $interactor;
    private $config;

    public function __construct(ElasticApmInteractorInterface $interactor, Config $config)
    {
        $this->interactor = $interactor;
        $this->config = $config;
    }

    public function register(): void
    {
        if ($this->isRegistered) {
            return;
        }
        $this->isRegistered = true;

        $prevErrorHandler = \set_error_handler(function ($type, $msg, $file, $line, $context = []) use (&$prevErrorHandler) {
            switch($type) {
                case E_WARNING:
                case E_USER_WARNING:
                    foreach ($this->config->getCustomLabels() as $name => $value) {
                        $this->interactor->addLabel((string) $name, $value);
                    }
    
                    foreach ($this->config->getCustomContext() as $name => $value) {
                        $this->interactor->addCustomContext((string) $name, $value);
                    }

                    $this->interactor->noticeThrowable(new WarningException($msg, 0, $type, $file, $line));
            }

            return $prevErrorHandler ? $prevErrorHandler($type, $msg, $file, $line, $context) : false;
        });
    }

    public function unregister(): void
    {
        if (! $this->isRegistered) {
            return;
        }
        $this->isRegistered = false;
        \restore_error_handler();
    }
}
