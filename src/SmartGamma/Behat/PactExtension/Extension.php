<?php

namespace SmartGamma\Behat\PactExtension;

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * Class MailerExtension.
 */
class Extension implements ExtensionInterface
{
    const PARAMETER_NAME_PACT_PROVIDERS = 'pact.providers.config';

    const PARAMETER_NAME_PACT_COMMON_CONFIG = 'pact.common.config';

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
            ->arrayNode('common')
            ->useAttributeAsKey('key')
            ->prototype('variable')->end()
            ->end()
            ->arrayNode('providers')
            ->prototype('variable')->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter(self::PARAMETER_NAME_PACT_PROVIDERS, $this->normalizeProvidersConfig($config['providers']));
        $container->setParameter(self::PARAMETER_NAME_PACT_COMMON_CONFIG, $config['common']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/ServiceContainer/config'));
        $loader->load('services.yml');
    }

    /**
     * @param array $originalConfig
     *
     * @return array
     */
    private function normalizeProvidersConfig(array $originalConfig): array
    {
        $config = [];

        foreach ($originalConfig as $one) {
            foreach ($one as $key => $val) {
                $config[$key]                          = [];
                $config[$key]['PACT_PROVIDER_NAME']    = $key;
                $parts                                 = explode(':', $val);
                $config[$key]['PACT_MOCK_SERVER_HOST'] = $parts[0];
                $config[$key]['PACT_MOCK_SERVER_PORT'] = $parts[1];
            }
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigKey()
    {
        return 'pact';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
    }
}
