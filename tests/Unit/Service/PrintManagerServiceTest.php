<?php

namespace Photobooth\Tests\Unit\Service;

use Photobooth\Configuration\Section\PrintConfiguration;
use Photobooth\Service\PrintManagerService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

final class PrintManagerServiceTest extends TestCase
{
    public function testPrinterSelectionDefaultsToExistingCommand(): void
    {
        $config = PrintConfiguration::getNode()->getNode(true)->finalize([]);
        $this->assertSame('', $config['printer']);
        $command = 'custom-printer --copies %d --file %s';
        $this->assertSame($command, PrintManagerService::getPrintCommand('', $command, true));
        $this->assertSame('custom %s', PrintManagerService::getPrintCommand('', 'custom %s', false));
    }

    public function testPresetSelectsQueueAndPreservesFilenameEscaping(): void
    {
        $file = "/tmp/photo's test.jpg";
        foreach (['Canon', 'DNP'] as $printer) {
            $config = PrintConfiguration::getNode()->getNode(true)->finalize(['printer' => $printer]);
            $this->assertSame($printer, $config['printer']);
            $command = PrintManagerService::getPrintCommand($printer, 'lp -d Old %s', false);
            $this->assertSame('lp -d ' . escapeshellarg($printer) . ' -o landscape ' . escapeshellarg($file), sprintf($command, escapeshellarg($file)));
            $command = PrintManagerService::getPrintCommand($printer, 'lp -d Old %s', true);
            $this->assertSame('lp -d ' . escapeshellarg($printer) . ' -o landscape -n 3 ' . escapeshellarg($file), sprintf($command, 3, escapeshellarg($file)));
        }
    }

    public function testConfigurationRejectsUnknownPrinter(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        PrintConfiguration::getNode()->getNode(true)->finalize(['printer' => 'DNP; touch /tmp/invalid']);
    }

    public function testCommandRejectsUnknownPrinter(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PrintManagerService::getPrintCommand('DNP; touch /tmp/invalid', 'lp %s', false);
    }
}
