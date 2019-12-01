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

        $validatorNamespace = $this->getDefaultValidatorsNamespace();
        $model = $this->option('model');

        $validatorClass = $validatorNamespace . '\\' . $this->getValidatorRuleName();
        if (!class_exists($validatorClass)) {
            if ($this->confirm("A {$validatorClass} validator does not exist. Do you want to generate it?", true)) {
                $this->call('rlustosa:make-rule', ['module' => $this->getModuleInput(), '--model' => $model]);
            }
        }

        $validatorStoreRequestClass = $validatorNamespace . '\\' . $this->getValidatorStoreRequestName();
        if (!class_exists($validatorStoreRequestClass)) {
            if ($this->confirm("A {$validatorStoreRequestClass} validator store request does not exist. Do you want to generate it?", true)) {
                $this->call('rlustosa:make-store-request', ['module' => $this->getModuleInput(), '--model' => $model]);
            }
        }

        $validatorUpdateRequestClass = $validatorNamespace . '\\' . $this->getValidatorUpdateRequestName();
        if (!class_exists($validatorUpdateRequestClass)) {
            if ($this->confirm("A {$validatorUpdateRequestClass} validator update request does not exist. Do you want to generate it?", true)) {
                $this->call('rlustosa:make-update-request', ['module' => $this->getModuleInput(), '--model' => $model]);
            }
        }
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