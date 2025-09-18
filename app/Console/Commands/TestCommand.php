<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use NunoMaduro\Collision\Adapters\Laravel\Commands\TestCommand as BaseTestCommand;

class TestCommand extends BaseTestCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test
        {--without-tty : Disable output to TTY}
        {--compact : Indicates whether the compact printer should be used}
        {--coverage : Indicates whether code coverage information should be collected}
        {--min= : Indicates the minimum threshold enforcement for code coverage}
        {--p|parallel : Indicates if the tests should run in parallel}
        {--profile : Lists top 10 slowest tests}
        {--recreate-databases : Indicates if the test databases should be re-created}
        {--drop-databases : Indicates if the test databases should be dropped}
        {--without-databases : Indicates if database configuration should be performed}
        {--module= : Run tests for a specific module}
    ';

    /**
     * Get the array of arguments for running PHPUnit.
     *
     * @param  array  $options
     * @return array
     */
    protected function phpunitArguments($options)
    {
        $options = array_values(array_filter($options, function ($option) {
            return ! Str::startsWith($option, '--module');
        }));

        $arguments = parent::phpunitArguments($options);

        if ($this->option('module')) {
            $module = $this->option('module');
            $moduleTestDir = base_path("app-modules/{$module}/tests");
            if (is_dir($moduleTestDir)) {
                $arguments[] = $moduleTestDir;
            }
        }

        return $arguments;
    }

    /**
     * Get the configuration file.
     *
     * @return string
     */
    protected function getConfigurationFile()
    {
        if ($this->option('module')) {
            $module = $this->option('module');
            $moduleTestDir = base_path("app-modules/{$module}/tests");

            if (is_dir($moduleTestDir)) {
                $tempConfig = tempnam(sys_get_temp_dir(), 'phpunit_module_').'.xml';

                $originalConfig = file_get_contents(parent::getConfigurationFile());

                // Add the module Unit tests directory
                $modifiedConfig = str_replace(
                    '<testsuite name="Unit">',
                    '<testsuite name="Unit">'."\n".'            <directory>'.$moduleTestDir.'/Unit</directory>',
                    $originalConfig
                );

                file_put_contents($tempConfig, $modifiedConfig);

                return $tempConfig;
            }
        }

        return parent::getConfigurationFile();
    }
}
