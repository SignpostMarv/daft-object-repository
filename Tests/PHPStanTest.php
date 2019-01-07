<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests;

use Jean85\PrettyVersions;
use OutOfBoundsException;
use PHPStan\Command\AnalyseCommand;
use PHPStan\Command\DumpDependenciesCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class PHPStanTest extends TestCase
{
    public function testPHPStan() : void
    {
        $version = 'Version unknown';
        try {
            $version = PrettyVersions::getVersion('phpstan/phpstan')->getPrettyVersion();
        } catch (OutOfBoundsException $e) {
        }

        $application = new Application('PHPStan Checking', $version);
        $application->add(new AnalyseCommand());
        $application->add(new DumpDependenciesCommand());

        $command = $application->find('analyse');

        static::assertInstanceOf(AnalyseCommand::class, $command);

        $commandTester = new CommandTester($command);

        $commandTester->execute(
            [
                'paths' => [
                    __DIR__ . '/../.php_cs.dist',
                    __DIR__ . '/../src/',
                    __DIR__ . '/../PHPStan/',
                    __DIR__ . '/../Tests/',
                ],
            ],
            [
                'configuration' => __DIR__ . '/../phpstan.neon',
            ]
        );

        $firstLine = trim(current(explode("\n", $commandTester->getDisplay())));

        static::assertSame(
            'Note: Using configuration file ' . realpath(__DIR__ . '/../phpstan.neon') . '.',
            $firstLine
        );
    }
}
