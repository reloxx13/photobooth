<?php

namespace Photobooth\Tests\Unit\Service;

use Photobooth\Configuration\Section\PrintConfiguration;
use Photobooth\Service\PrintManagerService;
use PHPUnit\Framework\TestCase;

final class PrintManagerServiceTest extends TestCase
{
    public function testExistingCommandsAndDefaultSelectionRemainUnchanged(): void
    {
        $config = PrintConfiguration::getNode()->getNode(true)->finalize([]);
        $this->assertSame('', $config['printer']);
        foreach (['custom %s', 'custom --copies %d %s'] as $command) {
            $this->assertSame($command, PrintManagerService::getPrintCommand('', $command));
            $this->assertSame($command, PrintManagerService::getPrintCommand('DNP', $command));
        }
    }

    public function testSelectedPrinterReplacesOnlyItsPlaceholder(): void
    {
        $file = "/tmp/photo's test.jpg";
        foreach (['Canon', 'DNP', 'Other_Queue', "quote';&<%s>"] as $printer) {
            $config = PrintConfiguration::getNode()->getNode(true)->finalize(['printer' => $printer]);
            $this->assertSame($printer, $config['printer']);
            $command = PrintManagerService::getPrintCommand($printer, 'lp -d {printer} -o landscape %s');
            $this->assertSame('lp -d ' . escapeshellarg($printer) . ' -o landscape ' . escapeshellarg($file), sprintf($command, escapeshellarg($file)));
            $command = PrintManagerService::getPrintCommand($printer, 'lp -d {printer} -o landscape -n %d %s');
            $this->assertSame('lp -d ' . escapeshellarg($printer) . ' -o landscape -n 3 ' . escapeshellarg($file), sprintf($command, 3, escapeshellarg($file)));
        }
    }

    public function testPlaceholderRequiresPrinterSelection(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        PrintManagerService::getPrintCommand('', 'lp -d {printer} %s');
    }

    public function testPrinterDiscoveryUsesInstalledQueuesAndHandlesFailure(): void
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            $this->markTestSkipped('CUPS discovery is Linux-only.');
        }

        $directory = sys_get_temp_dir() . '/photobooth-lpstat-' . bin2hex(random_bytes(6));
        mkdir($directory, 0700);
        $path = getenv('PATH');
        try {
            file_put_contents($directory . '/lpstat', "#!/bin/sh\nprintf '%s\\n' 'printer DNP is idle.' 'printer Canon disabled since today' 'printer 123 is idle.'\n");
            chmod($directory . '/lpstat', 0700);
            putenv('PATH=' . $directory);
            $this->assertSame(['DNP' => 'DNP', 'Canon' => 'Canon', 123 => '123'], PrintManagerService::getPrinters());
            file_put_contents($directory . '/lpstat', "#!/bin/sh\nexit 1\n");
            $this->assertSame([], PrintManagerService::getPrinters());
        } finally {
            putenv($path === false ? 'PATH' : 'PATH=' . $path);
            unlink($directory . '/lpstat');
            rmdir($directory);
        }
    }
}
