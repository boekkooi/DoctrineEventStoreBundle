<?php

namespace Boekkooi\Bundle\DoctrineEventStoreBundle;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BoekkooiDoctrineEventStoreBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(DoctrineOrmMappingsPass::createXmlMappingDriver(
            array(
                realpath(__DIR__ . '/Resources/config/doctrine/model') => __NAMESPACE__ . '\\Model',
            ),
            array('boekkooi.doctrine_event_store.model_manager_name'),
            'boekkooi.doctrine_event_store.backend_type_orm',
            array(
                'BoekkooiDoctrineEventStoreBundle' => __NAMESPACE__ . '\\Model'
            )
        ));

        $container->addCompilerPass(
            new RegisterListenersPass('boekkooi.doctrine_event_store.event_dispatcher', 'domain.event_listener', 'domain.event_subscriber'),
            PassConfig::TYPE_BEFORE_REMOVING
        );
    }
}
