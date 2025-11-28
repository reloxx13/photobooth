<?php

namespace Photobooth\Service;

use Photobooth\Utility\PathUtility;

class ThemeService
{
    private string $themeDirectory;
    private string $legacyThemeDirectory;

    public function __construct()
    {
        $this->themeDirectory = PathUtility::getAbsolutePath('private/themes');
        $this->legacyThemeDirectory = PathUtility::getAbsolutePath('config/themes');

        if (!is_dir($this->themeDirectory)) {
            @mkdir($this->themeDirectory, 0775, true);
        }
    }

    public static function getInstance(): self
    {
        if (!isset($GLOBALS[self::class])) {
            $GLOBALS[self::class] = new self();
        }

        return $GLOBALS[self::class];
    }

    /**
     * @return array<string,array<string,mixed>>
     */
    public function getAll(): array
    {
        $directory = $this->themeDirectory;
        $themes = [];
        if (!is_dir($directory)) {
            return $themes;
        }

        $files = glob($directory . DIRECTORY_SEPARATOR . '*.theme.config.json');
        if ($files === false) {
            return $themes;
        }

        foreach ($files as $file) {
            $name = basename($file, '.theme.config.json');
            if (isset($themes[$name])) {
                continue;
            }

            $raw = @file_get_contents($file);
            if ($raw === false) {
                continue;
            }

            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                continue;
            }

            $themes[$name] = $decoded;
        }

        return $themes;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function get(string $name): ?array
    {
        if ($name === '') {
            return null;
        }

        // New location
        $file = $this->getFilePath($name);
        if (is_file($file)) {
            $raw = @file_get_contents($file);
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        // Older per-file location
        $legacyFile = $this->getLegacyFilePath($name);
        if (is_file($legacyFile)) {
            $raw = @file_get_contents($legacyFile);
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        // Fallback to legacy JSON file if theme is not stored as a separate file.
        $legacyJsonFile = PathUtility::getAbsolutePath('private/themes.json');
        if (is_file($legacyJsonFile)) {
            $legacyRaw = @file_get_contents($legacyJsonFile);
            if ($legacyRaw !== false) {
                $legacyDecoded = json_decode($legacyRaw, true);
                if (is_array($legacyDecoded) && isset($legacyDecoded[$name]) && is_array($legacyDecoded[$name])) {
                    return $legacyDecoded[$name];
                }
            }
        }

        return null;
    }

    /**
     * @param array<string,mixed> $data
     */
    public function save(string $name, array $data): void
    {
        if ($name === '') {
            return;
        }

        $file = $this->getFilePath($name);

        if (!is_dir($this->themeDirectory)) {
            @mkdir($this->themeDirectory, 0775, true);
        }

        @file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function delete(string $name): void
    {
        if ($name === '') {
            return;
        }

        $file = $this->getFilePath($name);
        if (is_file($file)) {
            @unlink($file);
        }

        $legacyFile = $this->getLegacyFilePath($name);
        if (is_file($legacyFile)) {
            @unlink($legacyFile);
        }
    }

    private function getFilePath(string $name): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
        if ($safeName === '') {
            $safeName = 'theme';
        }

        return $this->themeDirectory . DIRECTORY_SEPARATOR . $safeName . '.theme.config.json';
    }

    private function getLegacyFilePath(string $name): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
        if ($safeName === '') {
            $safeName = 'theme';
        }

        return $this->legacyThemeDirectory . DIRECTORY_SEPARATOR . $safeName . '.theme.config.json';
    }

    /**
     * @param array<string,array<string,mixed>> $themes
     * @return array<string,array<string,mixed>>
     */
    private function loadThemesFromDirectory(string $directory, array $themes): array
    {
        if (!is_dir($directory)) {
            return $themes;
        }

        $files = glob($directory . DIRECTORY_SEPARATOR . '*.theme.config.json');
        if ($files === false) {
            return $themes;
        }

        foreach ($files as $file) {
            $name = basename($file, '.theme.config.json');
            if (isset($themes[$name])) {
                continue;
            }

            $raw = @file_get_contents($file);
            if ($raw === false) {
                continue;
            }

            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                continue;
            }

            $themes[$name] = $decoded;
        }

        return $themes;
    }
}
