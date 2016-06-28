<?php
/**
 * Created by PhpStorm.
 * User: whitezo
 * Date: 2016. 06. 24.
 * Time: 16:07
 */

namespace WebtownPhp\DeployerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class TemplateCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('deployer.templates')) {
            return;
        }

        $taggedServiceHolder = $container->getDefinition('deployer.templates');

        foreach ($container->findTaggedServiceIds('deployer.template') as $id => $attributes) {
            $taggedServiceHolder->addMethodCall('push', array(new Reference($id)));
        }
    }
}
