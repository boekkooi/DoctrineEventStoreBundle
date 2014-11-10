<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;


class BoekkooiDoctrineEventStoreExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $configs);

        $loader->load('services.xml');

        if ($config['jms']['uuid']) {
            $loader->load('jms_serializer_uuid.xml');
        }

        $container->setParameter('boekkooi.doctrine_event_store.backend_type_' . $config['doctrine']['driver'], true);
        $container->setParameter('boekkooi.doctrine_event_store.model_manager_name', $config['doctrine']['model_manager_name']);

        if ($config['doctrine']['driver'] == 'orm') {
            $loader->load('doctrine_orm.xml');
        }
        $loader->load('doctrine.xml');
    }
}
