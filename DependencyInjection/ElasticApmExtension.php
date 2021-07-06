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

namespace ElasticApmBundle\DependencyInjection;

use ElasticApmBundle\Interactor\AdaptiveInteractor;
use ElasticApmBundle\Interactor\BlackholeInteractor;
use ElasticApmBundle\Interactor\Config;
use ElasticApmBundle\Interactor\ElasticApmInteractor;
use ElasticApmBundle\Interactor\ElasticApmInteractorInterface;
use ElasticApmBundle\Listener\ExceptionListener;
use ElasticApmBundle\Listener\ResponseListener;
use ElasticApmBundle\TransactionNamingStrategy\ControllerNamingStrategy;
use ElasticApmBundle\TransactionNamingStrategy\RouteNamingStrategy;
use ElasticApmBundle\TransactionNamingStrategy\TransactionNamingStrategyInterface;
use ElasticApmBundle\TransactionNamingStrategy\UriNamingStrategy;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ElasticApmExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setAlias(ElasticApmInteractorInterface::class, $this->getInteractorServiceId($config))->setPublic(false);
        $container->setAlias(TransactionNamingStrategyInterface::class, $this->getTransactionNamingServiceId($config))->setPublic(false);

        $container->getDefinition(Config::class)
            ->setArguments(
                [
                    '$customLabels' => $config['custom_labels'],
                    '$customContext' => $config['custom_context'],
                    '$shouldCollectMemoryUsage' => $config['track_memory_usage'],
                    '$memoryUsageLabelName' => $config['memory_usage_label'],
                ]
            );

        if ($config['http']['enabled']) {
            $loader->load('http_listener.xml');
        }

        if ($config['commands']['enabled']) {
            $loader->load('command_listener.xml');
        }

        if ($config['exceptions']['enabled']) {
            $loader->load('exception_listener.xml');

            $container->getDefinition(ExceptionListener::class)
                ->setArguments(
                    [
                        '$ignoredExceptions' => $config['exceptions']['ignored_exceptions'],
                    ]
                );
        }

        if ($config['deprecations']['enabled']) {
            $loader->load('deprecation_listener.xml');
        }

        if ($config['warnings']['enabled']) {
            $loader->load('warning_listener.xml');
        }
    }

    private function getInteractorServiceId(array $config): string
    {
        if (! $config['enabled']) {
            return BlackholeInteractor::class;
        }

        if (! isset($config['interactor'])) {
            // Fallback on AdaptiveInteractor.
            return AdaptiveInteractor::class;
        }

        if ('auto' === $config['interactor']) {
            // Check if the extension is loaded or not
            return \extension_loaded('elastic_apm') ? ElasticApmInteractor::class : BlackholeInteractor::class;
        }

        return $config['interactor'];
    }

    private function getTransactionNamingServiceId(array $config): string
    {
        switch ($config['http']['transaction_naming']) {
            case 'controller':
                return ControllerNamingStrategy::class;
            case 'route':
                return RouteNamingStrategy::class;
            case 'uri':
                return UriNamingStrategy::class;
            case 'service':
                if (! isset($config['http']['transaction_naming_service'])) {
                    throw new \LogicException('When using the "service", transaction naming scheme, the "transaction_naming_service" config parameter must be set.');
                }

                return $config['http']['transaction_naming_service'];
            default:
                throw new \InvalidArgumentException(\sprintf('Invalid transaction naming scheme "%s", must be "route", "controller" or "service".', $config['http']['transaction_naming']));
        }
    }
}
