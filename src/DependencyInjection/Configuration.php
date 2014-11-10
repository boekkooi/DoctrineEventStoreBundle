<?php
namespace Boekkooi\Bundle\DoctrineEventStoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('boekkooi_doctrine_event_store');
        $rootNode->append($this->loadJmsNode());
        $rootNode->append($this->loadDoctrine());

        return $treeBuilder;
    }

    private function loadJmsNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('jms');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('uuid')
                    ->defaultTrue()
                    ->info('Should a jms type handler for \Rhumsaa\Uuid\Uuid be registered')
                ->end()
            ->end();

        return $node;
    }

    private function loadDoctrine()
    {
        $supportedDrivers = array('orm');

        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('doctrine');
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('driver')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($supportedDrivers))
                    ->end()
                    ->defaultValue('orm')
                    ->cannotBeOverwritten()
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('model_manager_name')->defaultNull()->end()
            ->end();

        return $node;
    }
}
