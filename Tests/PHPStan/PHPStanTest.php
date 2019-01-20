<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftObject\Tests\PHPStan;

use Jean85\PrettyVersions;
use OutOfBoundsException;
use PHPStan\Command\AnalyseCommand;
use SignpostMarv\DaftObject\Tests\TestCase;
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

        $command = $application->find('analyse');

        static::assertInstanceOf(AnalyseCommand::class, $command);

        $commandTester = new CommandTester($command);

        $commandTester->execute(
            [
                'paths' => [
                    __DIR__ . '/../../.php_cs.dist',
                    __DIR__ . '/../../src/',
                    __DIR__ . '/../../PHPStan/',
                    __DIR__ . '/../../Tests/',
                    __DIR__ . '/../../tests-src/',
                    __DIR__ . '/../../quick-checks/',
                ],
            ],
            [
                'configuration' => static::ObtainConfiguration(),
            ]
        );

        $firstLine = trim(current(explode("\n", $commandTester->getDisplay())));

        static::assertTrue(in_array(
            $firstLine,
            [
                (
                    'Note: Using configuration file ' .
                    realpath(__DIR__ . '/../../phpstan.neon') .
                    '.'
                ),
                (
                    'Note: Using configuration file ' .
                    realpath(__DIR__ . '/../../../../../phpstan.neon') .
                    '.'
                ),
            ],
            true
        ));
    }

    protected static function ObtainConfiguration() : string
    {
        return  __DIR__ . '/../../phpstan.neon';
    }
}
