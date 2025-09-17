<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ClassMakeCommand;
use InterNACHI\Modular\Console\Commands\Make\Modularize;
use Symfony\Component\Console\Input\InputOption;

class MakeClass extends ClassMakeCommand
{
    use Modularize;

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['module', null, InputOption::VALUE_REQUIRED, 'Run inside an application module'],
        ]);
    }
}
