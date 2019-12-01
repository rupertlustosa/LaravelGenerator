<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class UpdateRequestMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-update-request';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a store request for the specified model.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'UpdateRequest';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        $stub = '/stubs/update-request.stub';

        return __DIR__ . $stub;
    }

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getDestinationFilePath()
    {

        return base_path($this->rootNamespace() . '/' . $this->getModuleName() . '/Validators/' . $this->getValidatorUpdateRequestName() . '.php');
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {

        return $this->getDefaultValidatorsNamespace();
    }


    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass()
    {

        $validatorNamespace = $this->getDefaultValidatorsNamespace();

        $replace = [];
        $replace['DummyValidatorNamespace'] = $validatorNamespace;
        $replace['DummyRuleClass'] = $this->getValidatorRuleName();
        $replace['DummyValidatorRuleNamespace'] = $validatorNamespace . '\\' . $this->getValidatorRuleName();

        //$replace = $this->buildModelReplacements($replace);

        $stub = $this->files->get($this->getStub());

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
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

    protected function alreadyExists()
    {

        return $this->files->exists($this->getDestinationFilePath());
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
}