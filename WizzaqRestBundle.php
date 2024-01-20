<?php

declare(strict_types=1);

namespace Wizzaq\RestBundle;

use Wizzaq\RestBundle\DependencyInjection\Compiler\ProtocolCompiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class WizzaqRestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ProtocolCompiler());
    }
}
