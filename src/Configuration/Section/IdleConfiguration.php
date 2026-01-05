<?php

namespace Photobooth\Configuration\Section;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class IdleConfiguration
{
    public static function getNode(): NodeDefinition
    {
        return (new TreeBuilder('idle'))->getRootNode()->addDefaultsIfNotSet()
            ->ignoreExtraKeys()
            ->children()
                ->booleanNode('enabled')->defaultFalse()->end()
                ->enumNode('mode')->values(['image', 'video', 'folder', 'gallery'])->defaultValue('image')->end()
                ->scalarNode('image_source')->defaultValue('')->end()
                ->scalarNode('video_source')->defaultValue('')->end()
                ->scalarNode('gallery_text')->defaultValue('')->end()
                ->integerNode('switch_minutes')->min(1)->defaultValue(1)->end()
                ->integerNode('timeout_minutes')->min(0)->defaultValue(3)->end()
            ->end();
    }
}
