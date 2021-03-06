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

use ElasticApmBundle\Exception\DeprecationException;
use ElasticApmBundle\Interactor\ElasticApmInteractorInterface;

class DeprecationListener
{
    private $isRegistered = false;
    private $interactor;

    public function __construct(ElasticApmInteractorInterface $interactor)
    {
        $this->interactor = $interactor;
    }

    public function register(): void
    {
        if ($this->isRegistered) {
            return;
        }
        $this->isRegistered = true;

        $prevErrorHandler = \set_error_handler(function ($type, $msg, $file, $line, $context = []) use (&$prevErrorHandler) {
            if (E_USER_DEPRECATED === $type) {
                $this->interactor->addContextFromConfig();
                $this->interactor->noticeThrowable(new DeprecationException($msg, 0, $type, $file, $line));
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
