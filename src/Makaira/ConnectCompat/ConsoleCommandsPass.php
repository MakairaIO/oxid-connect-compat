<?php

namespace Makaira\ConnectCompat;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ConsoleCommandsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('makaira.console.application')) {
            return;
        }

        $definition = $container->findDefinition('makaira.console.application');

        $taggedServices = $container->findTaggedServiceIds('console.command');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('add', [new Reference($id)]);
        }
    }

}
