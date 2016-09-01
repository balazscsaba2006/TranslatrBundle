<?php

namespace Evozon\TranslatrBundle\DependencyInjection;

use Onesky\Api\Client;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class TranslatrExtension
 *
 * @package   Evozon\TranslatrBundle\DependencyInjection
 * @author    Balazs Csaba <csaba.balazs@evozon.com>
 * @copyright 2016 Evozon (https://www.evozon.com)
 */
class EvozonTranslatrExtension extends Extension
{
    /**
     * @const string
     */
    const DEFAULT_OUTPUT = '[filename].[locale].[extension]';
    /**
     * @const string
     */
    const DOWNLOAD_MAPPING_FILENAME_POSTFIX = '.tmp_translatr';

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->addDefinitions([
            'evozon_translatr_client' => $this->getClientDefinition($config),
            'evozon_translatr_downloader' => $this->getDownloaderDefinition($config),
            'evozon_translatr_uploader' => $this->getUploaderDefinition($config),
        ]);
    }

    /**
     * @param $config
     *
     * @return Definition
     */
    private function getClientDefinition($config)
    {
        $client = new Definition('Onesky\Api\Client');
        $client->addMethodCall('setApiKey', [$config['api_key']]);
        $client->addMethodCall('setSecret', [$config['secret']]);

        return $client;
    }

    /**
     * @param $config
     *
     * @return Definition
     */
    private function getDownloaderDefinition($config)
    {
        return $this->getServiceDefinition(
            'Evozon\TranslatrBundle\OneSky\Downloader',
            $config,
            self::DOWNLOAD_MAPPING_FILENAME_POSTFIX
        );
    }

    /**
     * @param $config
     *
     * @return Definition
     */
    private function getUploaderDefinition($config)
    {
        return $this->getServiceDefinition('Evozon\TranslatrBundle\OneSky\Uploader', $config);
    }

    /**
     * @param string $class
     * @param array  $config
     * @param mixed  $postfix
     *
     * @return Definition
     */
    private function getServiceDefinition($class, $config, $postfix = null)
    {
        $service = new Definition($class, [
            new Reference('evozon_translatr_client'),
            $config['project'],
            $config['locale_format'],
        ]);

        foreach ($config['mappings'] as $mappingConfig) {
            $mappingConfig['locales'] = array_map(
                function ($locale) {
                    return strtolower(substr($locale, 0, 2));
                },
                $mappingConfig['locales']
            );

            $service->addMethodCall('addMapping', [
                new Definition(
                    'Evozon\TranslatrBundle\OneSky\Mapping',
                    [
                        isset($mappingConfig['sources']) ? $mappingConfig['sources'] : [],
                        isset($mappingConfig['locales']) ? $mappingConfig['locales'] : [],
                        isset($mappingConfig['output']) ? $mappingConfig['output'] : self::DEFAULT_OUTPUT,
                        $postfix,
                    ]
                ),
            ]);
        }

        return $service;
    }
}
