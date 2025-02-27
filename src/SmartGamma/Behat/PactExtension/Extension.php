<?php

namespace SmartGamma\Behat\PactExtension;

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Exception;
use ReflectionClassConstant;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * @phpstan-type CommonConfiguration array{
 *     PACT_CONSUMER_VERSION: string,
 *     PACT_CONSUMER_NAME: string,
 *     PACT_OUTPUT_DIR: string,
 * }
 *
 * @phpstan-type ProviderConfiguration array{
 *     array{
 *         PACT_PROVIDER_NAME: string,
 *         PACT_MOCK_SERVER_HOST: string,
 *         PACT_MOCK_SERVER_PORT: string,
 *     },
 * }
 *
 * @phpstan-type ExtensionConfiguration array{
 *     common: CommonConfiguration,
 *     providers: array{
 *         string[],
 *     },
 * }
 */
class Extension implements ExtensionInterface
{
    const PARAMETER_NAME_PACT_PROVIDERS = 'pact.providers.config';

    const PARAMETER_NAME_PACT_COMMON_CONFIG = 'pact.common.config';

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->arrayNode('common')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('providers')
                    ->prototype('variable')->end()
                ->end()
           ->end();
    }

    /**
     * @param ContainerBuilder       $container
     * @param ExtensionConfiguration $config
     * @return void
     * @throws Exception
     */
    public function load(ContainerBuilder $container, array $config): void
    {
        $this->resolveConsumerVersion($config);
        $container->setParameter(
            self::PARAMETER_NAME_PACT_PROVIDERS,
            $this->normalizeProvidersConfig($config['providers'])
        );
        $container->setParameter(self::PARAMETER_NAME_PACT_COMMON_CONFIG, $config['common']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/ServiceContainer/config'));
        $loader->load('services.yml');
    }

    /**
     * @param ExtensionConfiguration $config
     * @return void
     * @throws Exception
     */
    private function resolveConsumerVersion(array &$config): void
    {
        try {
            $reflex = new ReflectionClassConstant('\App\Kernel', 'PACT_CONSUMER_VERSION');
            $config['common']['PACT_CONSUMER_VERSION'] = (string) $reflex->getValue();
        } catch (Exception $e) {
            if (false === isset($config['common']['PACT_CONSUMER_VERSION'])) {
                throw new Exception('You should define PACT_CONSUMER_VERSION', 0, $e);
            }
        }
    }

    /**
     * @param array<string[]> $originalConfig
     * @return ProviderConfiguration
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

    public function getConfigKey(): string
    {
        return 'pact';
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }

    public function process(ContainerBuilder $container): void
    {
    }
}
