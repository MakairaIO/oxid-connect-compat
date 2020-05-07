<?php

namespace Makaira\ConnectCompat;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;

class ContainerCompat
{
    /**
     * @return \OxidEsales\EshopCommunity\Internal\Container\ContainerFactory
     */
    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new static();
        }
        return $instance;
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        static $container = null;

        if ($container === null) {
            $config = \oxRegistry::get('oxConfigFile');
            if (!is_dir($config->sCompileDir)) {
                throw new \Exception("sCompileDir no found");
            }

            $filename = $config->sCompileDir . '/MarmaladeConnectCompatServiceContainer.php';
            if (!file_exists($filename) ) {
                $container = new ContainerBuilder();

                $yamlFileLoader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../..'));
                $yamlFileLoader->load('services.yaml');

                $finder = new Finder();
                $finder->files()
                    ->in(OX_BASE_PATH . "modules")
                    ->depth('< 3')
                    ->name('services.yaml');

                foreach ($finder as $file) {
                    $yamlFileLoader = new YamlFileLoader($container, new FileLocator(dirname($file->getRealPath())));
                    $yamlFileLoader->load(basename($file->getRealPath()));
                }

                $container->addCompilerPass(new Compiler\MergeExtensionConfigurationPass());
                $container->addCompilerPass(new Compiler\AnalyzeServiceReferencesPass());
                $container->addCompilerPass(new Compiler\AutowirePass());
                $container->addCompilerPass(new Compiler\CheckCircularReferencesPass());
                $container->addCompilerPass(new Compiler\ResolveParameterPlaceHoldersPass());
                $container->addCompilerPass(new Compiler\ResolveReferencesToAliasesPass());
                $container->addCompilerPass(new Compiler\ExtensionCompilerPass());

                $container->addCompilerPass(new RegisterListenersPass(EventDispatcherInterface::class));

                $container->compile();

                $dumper = new PhpDumper($container);
                file_put_contents($filename, $dumper->dump(['namespace' => 'Makaira\ConnectCompat']));
            }

            include_once $filename;
            $container = new ProjectServiceContainer();

            $container->set(EventDispatcherInterface::class, new ContainerAwareEventDispatcher($container));
        }

        return $container;
    }

    public static function getConnection()
    {
        $config = \oxRegistry::getConfig();
        return \Doctrine\DBAL\DriverManager::getConnection([
            'host' => $config->getConfigParam('dbHost'),
            'port' => $config->getConfigParam('dbPort'),
            'dbname' => $config->getConfigParam('dbName'),
            'user' => $config->getConfigParam('dbUser'),
            'password' => $config->getConfigParam('dbPwd'),
            'driver' => 'pdo_mysql',
            'charset' => 'utf8'
        ]);
    }

}