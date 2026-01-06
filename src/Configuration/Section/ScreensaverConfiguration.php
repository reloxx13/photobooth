<?php

namespace Photobooth\Configuration\Section;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class ScreensaverConfiguration
{
    public static function getNode(): NodeDefinition
    {
        // keeps class name for backward compatibility but config node is now "screensaver"
        return (new TreeBuilder('screensaver'))->getRootNode()->addDefaultsIfNotSet()
            ->ignoreExtraKeys()
            ->children()
                ->booleanNode('enabled')->defaultFalse()->end()
                ->enumNode('mode')->values(['image', 'video', 'folder', 'gallery'])->defaultValue('image')->end()
                ->scalarNode('image_source')->defaultValue('')->end()
                ->scalarNode('video_source')->defaultValue('')->end()
                ->scalarNode('text')->defaultValue('')->end()
                ->enumNode('text_position')
                    ->values(['top-center', 'center', 'bottom-center'])
                    ->defaultValue('center')
                ->end()
                ->integerNode('switch_minutes')->min(1)->defaultValue(1)->end()
                ->integerNode('timeout_minutes')->min(0)->defaultValue(3)->end()
            ->end();
    }
}
