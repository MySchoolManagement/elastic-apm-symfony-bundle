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

use ElasticApmBundle\Interactor\Config;
use ElasticApmBundle\Interactor\ElasticApmInteractorInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommandListener implements EventSubscriberInterface
{
    private $interactor;
    private $config;

    public function __construct(Config $config, ElasticApmInteractorInterface $interactor)
    {
        $this->config = $config;
        $this->interactor = $interactor;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => ['onConsoleCommand', 0],
            ConsoleEvents::ERROR => ['onConsoleError', 0],
        ];
    }

    public function onConsoleCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        $input = $event->getInput();

        $this->interactor->setTransactionName($command->getName());

        foreach ($input->getOptions() as $key => $value) {
            $key = '--'.$key;
            if (\is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->interactor->addCustomContext($key.'['.$k.']', $v);
                }
            } else {
                $this->interactor->addCustomContext($key, $value);
            }
        }

        foreach ($input->getArguments() as $key => $value) {
            if (\is_array($value)) {
                foreach ($value as $k => $v) {
                    $this->interactor->addCustomContext($key.'['.$k.']', $v);
                }
            } else {
                $this->interactor->addCustomContext($key, $value);
            }
        }
    }

    public function onConsoleError(ConsoleErrorEvent $event): void
    {
        $this->interactor->noticeThrowable($event->getError());
    }
}
