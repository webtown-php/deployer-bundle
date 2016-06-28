<?php

namespace WebtownPhp\DeployerBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use WebtownPhp\DeployerBundle\DependencyInjection\TemplateCompilerPass;

class WebtownPhpDeployerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TemplateCompilerPass());
    }
}
