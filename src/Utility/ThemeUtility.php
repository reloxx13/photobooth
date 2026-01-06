<?php

namespace Photobooth\Utility;

class ThemeUtility
{
    public static function renderCustomUserStyle(array $config): string
    {
        $backgroundDefault = $config['background']['defaults'] ?? '';
        $backgroundChroma  = $config['background']['chroma'] ?? '';

        $backgroundDefaultCss = self::buildBackgroundCssValue($backgroundDefault);
        $backgroundChromaCss  = self::buildBackgroundCssValue($backgroundChroma);

        $properties = [
            '--ui-scale' => $config['ui']['scale'] ? $config['ui']['scale'] . '%' : '__UNSET__',
            '--primary-color' => $config['colors']['primary'] ?? '__UNSET__',
            '--primary-light-color' => $config['colors']['primary_light'] ?? '__UNSET__',
            '--secondary-color' => $config['colors']['secondary'] ?? '__UNSET__',
            '--highlight-color' => $config['colors']['highlight'] ?? '__UNSET__',
            '--secondary-font-color' => $config['colors']['font_secondary'] ?? '__UNSET__',
            '--countdown-color' => $config['colors']['countdown'] ?? '__UNSET__',
            '--background-countdown-color' => $config['colors']['background_countdown'] ?? '__UNSET__',
            '--cheese-color' => $config['colors']['cheese'] ?? '__UNSET__',
            '--panel-color' => $config['colors']['panel'] ?? '__UNSET__',
            '--border-color' => $config['colors']['border'] ?? '__UNSET__',
            '--box-color' => $config['colors']['box'] ?? '__UNSET__',
            '--gallery-button-color' => $config['colors']['gallery_button'] ?? '__UNSET__',
            '--background-default' => $backgroundDefaultCss,
            '--background-chroma'  => $backgroundChromaCss,
            '--background-preview' => $config['preview']['url'] ?? '__UNSET__',
            '--preview-rotation' => $config['preview']['rotation'] ?? '__UNSET__',
            '--ui-scale-result' => $config['ui']['scale_resultImage'] ? 'auto ' . $config['ui']['scale_resultImage'] . '%' : '__UNSET__',
        ];

        $fontCss = '';

        // Default font
        if (!empty($config['fonts']['default'])) {
            $fontCss .= "@font-face {font-family:'DefaultFont';src:url('" . PathUtility::getPublicPath($config['fonts']['default']) . "') format('truetype');font-display:swap;}\n";
        }
        if (!empty($config['fonts']['default_color'])) {
            $properties['--font-color'] = $config['fonts']['default_color'];
        }
        $properties['--font-weight'] = !empty($config['fonts']['default_bold']) ? '700' : '400';
        $properties['--font-style']  = !empty($config['fonts']['default_italic']) ? 'italic' : 'normal';

        // Start screen font
        if (!empty($config['fonts']['start_screen_title'])) {
            $fontCss .= "@font-face {font-family:'StartScreenFont';src:url('" . PathUtility::getPublicPath($config['fonts']['start_screen_title']) . "') format('truetype');font-display:swap;}\n";
        }
        $properties['--start-text-color']  = $config['fonts']['start_screen_title_color'] ?? '__UNSET__';
        $properties['--start-text-weight'] = !empty($config['fonts']['start_screen_title_bold']) ? '700' : '400';
        $properties['--start-text-style']  = !empty($config['fonts']['start_screen_title_italic']) ? 'italic' : 'normal';

        // Event font
        if (!empty($config['fonts']['event_text'])) {
            $fontCss .= "@font-face {font-family:'EventFont';src:url('" . PathUtility::getPublicPath($config['fonts']['event_text']) . "') format('truetype');font-display:swap;}\n";
        }
        $properties['--event-text-color']  = $config['fonts']['event_text_color'] ?? '__UNSET__';
        $properties['--event-text-weight'] = !empty($config['fonts']['event_text_bold']) ? '700' : '400';
        $properties['--event-text-style']  = !empty($config['fonts']['event_text_italic']) ? 'italic' : 'normal';

        // Gallery title font
        if (!empty($config['fonts']['gallery_title'])) {
            $fontCss .= "@font-face {font-family:'GalleryFont';src:url('" . PathUtility::getPublicPath($config['fonts']['gallery_title']) . "') format('truetype');font-display:swap;}\n";
        }
        $properties['--gallery-title-color']  = $config['fonts']['gallery_title_color'] ?? '__UNSET__';
        $properties['--gallery-title-weight'] = !empty($config['fonts']['gallery_title_bold']) ? '700' : '400';
        $properties['--gallery-title-style']  = !empty($config['fonts']['gallery_title_italic']) ? 'italic' : 'normal';

        // Screensaver font
        if (!empty($config['fonts']['screensaver_text'])) {
            $fontCss .= "@font-face {font-family:'ScreensaverFont';src:url('" . PathUtility::getPublicPath($config['fonts']['screensaver_text']) . "') format('truetype');font-display:swap;}\n";
        } elseif (!empty($config['screensaver']['text_font'])) {
            // fallback to legacy location
            $fontCss .= "@font-face {font-family:'ScreensaverFont';src:url('" . PathUtility::getPublicPath($config['screensaver']['text_font']) . "') format('truetype');font-display:swap;}\n";
        }
        $properties['--screensaver-text-color']  = $config['fonts']['screensaver_text_color']
                                                   ?? ($config['screensaver']['text_color'] ?? '#ffffff');
        $properties['--screensaver-text-weight'] = !empty($config['fonts']['screensaver_text_bold']) ? '700' : '400';
        $properties['--screensaver-text-style']  = !empty($config['fonts']['screensaver_text_italic']) ? 'italic' : 'normal';

        // Font variables (button)
        if (!empty($config['fonts']['button_font'])) {
            $fontCss .= "@font-face {font-family:'ButtonFont';src:url('" . PathUtility::getPublicPath($config['fonts']['button_font']) . "') format('truetype');font-display:swap;}\n";
        }
        $properties['--button-font-color']  = $config['fonts']['button_font_color'] ?? '__UNSET__';
        $properties['--button-font-weight'] = !empty($config['fonts']['button_font_bold']) ? '700' : '400';
        $properties['--button-font-style']  = !empty($config['fonts']['button_font_italic']) ? 'italic' : 'normal';

        // Font variables (buzzer message)
        if (!empty($config['fonts']['button_buzzer_message_font'])) {
            $fontCss .= "@font-face {font-family:'BuzzerMessageFont';src:url('" . PathUtility::getPublicPath($config['fonts']['button_buzzer_message_font']) . "') format('truetype');font-display:swap;}\n";
        }
        $properties['--buzzer-message-font-color']  = $config['fonts']['button_buzzer_message_font_color'] ?? '__UNSET__';
        $properties['--buzzer-message-font-weight'] = !empty($config['fonts']['button_buzzer_message_font_bold']) ? '700' : '400';
        $properties['--buzzer-message-font-style']  = !empty($config['fonts']['button_buzzer_message_font_italic']) ? 'italic' : 'normal';

        $output = '';
        $output .= '<style>' . PHP_EOL;
        if ($fontCss !== '') {
            $output .= $fontCss;
        }
        $output .= ':root {' . PHP_EOL;
        foreach ($properties as $key => $value) {
            $value = trim($value);
            if ($value === '__UNSET__' || $value === '') {
                continue;
            }
            $output .= '  ' . $key . ': ' . $value . ';' . PHP_EOL;
        }
        $output .= '}' . PHP_EOL;
        $output .= '</style>' . PHP_EOL;

        return $output;
    }

    protected static function buildBackgroundCssValue(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '__UNSET__';
        }

        // Keep already wrapped values for backwards compatibility
        if (str_starts_with($value, 'url(')) {
            return $value;
        }

        // Build a full public URL from a relative or absolute path
        $publicPath = PathUtility::getPublicPath($value);

        return 'url(' . $publicPath . ')';
    }
}
