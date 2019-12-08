<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ValidatorsMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-validators';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new validators for the specified module.';


    public function handle()
    {

        $this->buildValidatorRuleReplacements([]);
        $this->buildValidatorStoreRequestReplacements([]);
        $this->buildValidatorUpdateRequestReplacements([]);
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of module will be used.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_REQUIRED, 'Generate a resource policy for the given model.'],
        ];
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        // TODO: Implement getStub() method.
    }

    /**
     * Get the destination file path.
     *
     * @return string
     */
    protected function getDestinationFilePath()
    {
        // TODO: Implement getDestinationFilePath() method.
    }

    protected function alreadyExists()
    {
        // TODO: Implement alreadyExists() method.
    }

    protected function missingDependencies()
    {

        $missing = [];

        return $missing;
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {
        // TODO: Implement getDefaultNamespace() method.
    }
}