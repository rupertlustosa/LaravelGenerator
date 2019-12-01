<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModelMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new model for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        $stub = '/stubs/model.stub';

        return __DIR__ . $stub;
    }

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getDestinationFilePath()
    {

        return base_path($this->rootNamespace() . '/' . $this->getModuleName() . '/Models/' . $this->getModelName() . '.php');
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {

        return trim($this->rootNamespace() . '\\' . $this->getModuleName() . '\Models');
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

        $modelNamespace = $this->getDefaultNamespace();

        $replace = [];
        $replace['DummyNamespace'] = $modelNamespace;
        $replace['DummyClass'] = $this->getModelName();

        $stub = $this->files->get($this->getStub());

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
    }

    /**
     * @return array|string
     */
    protected function getModelName()
    {

        return Str::studly($this->getNameInput());
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
            ['name', InputArgument::REQUIRED, 'The name of the controller class.'],
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
            ['migration', 'm', InputOption::VALUE_NONE, 'Flag to create associated migrations', null],
        ];
    }
}