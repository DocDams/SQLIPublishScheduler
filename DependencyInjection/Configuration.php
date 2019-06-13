<?php

namespace SQLI\PublishSchedulerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sqli_publish_scheduler');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->scalarNode('ezdatetime_field_publish')->defaultValue('publish_date')->end()
                ->scalarNode('ezdatetime_field_unpublish')->defaultValue('unpublish_date')->end()
                ->variableNode('publish_scheduler_handler')->defaultValue('@sqli_publish_scheduler.handler.visibility')->end()
            ->end();

        return $treeBuilder;
    }
}
