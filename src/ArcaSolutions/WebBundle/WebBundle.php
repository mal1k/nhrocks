<?php

namespace ArcaSolutions\WebBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Liip\ImagineBundle\DependencyInjection\LiipImagineExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ArcaSolutions\WebBundle\Imagine\Cache\CustomWebPathResolverFactory;

class WebBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        /** @var $liipExtension LiipImagineExtension */
        $liipExtension = $container->getExtension('liip_imagine');
        if ($liipExtension) {
            $liipExtension->addResolverFactory(new CustomWebPathResolverFactory());
            $liipConfiguration = $liipExtension->getConfiguration([], $container);
        }

    }
}
