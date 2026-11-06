<?php

namespace Photobooth\Tests\Unit\Utility;

use Photobooth\Service\LanguageService;
use Photobooth\Utility\AdminInput;
use PHPUnit\Framework\TestCase;

final class AdminInputTest extends TestCase
{
    public function testSelectEscapesDynamicPrinterNamesAndSelectsNumericQueue(): void
    {
        $previous = $GLOBALS[LanguageService::class] ?? null;
        $language = $this->createStub(LanguageService::class);
        $language->method('translate')->willReturnArgument(0);
        $GLOBALS[LanguageService::class] = $language;
        try {
            $name = '\"><script>alert(1)</script>';
            $html = AdminInput::renderSelect([
                'type' => 'select',
                'name' => 'print[printer]',
                'value' => '123',
                'options' => [$name => $name, 123 => '123'],
            ], 'print:printer');
            $this->assertStringNotContainsString('<script>', $html);
            $this->assertStringContainsString(htmlspecialchars($name, ENT_QUOTES), $html);
            $this->assertStringContainsString('selected="selected" value="123"', $html);
        } finally {
            if ($previous === null) {
                unset($GLOBALS[LanguageService::class]);
            } else {
                $GLOBALS[LanguageService::class] = $previous;
            }
        }
    }
}
