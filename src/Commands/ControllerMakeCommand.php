<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ControllerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-controller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new restful controller for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        $stub = '/stubs/controller.api.stub';

        return __DIR__ . $stub;
    }

    /**
     * Get controller name.
     *
     * @return string
     */
    public function getDestinationFilePath()
    {

        return base_path($this->rootNamespace() . '/' . $this->getModuleName() . '/Http/Controllers/' . $this->getControllerName() . '.php');
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultNamespace()
    {

        return trim($this->rootNamespace() . '\\' . $this->getModuleName() . '\Http\Controllers');
    }

    /**
     * Get the default namespace for the model class.
     *
     * @return string
     */
    protected function getDefaultModelNamespace()
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

        $controllerNamespace = $this->getDefaultNamespace();
        //dd($controllerNamespace);

        $replace = [];
        $replace['DummyNamespace'] = $controllerNamespace;
        $replace['RootController'] = 'Rlustosa\LaravelGenerator\BaseModule\BaseModuleController';
        $replace['DummyClass'] = $this->getControllerName();

        /*if ($this->option('parent')) {
            $replace = $this->buildParentReplacements();
        }*/

        //if ($this->option('model')) {
        $replace = $this->buildModelReplacements($replace);
        //}

        $replace["use {$controllerNamespace}\Controller;\n"] = '';

        $stub = $this->files->get($this->getStub());

        return str_replace(
            array_keys($replace), array_values($replace), $stub
        );
    }

    /**
     * Build the replacements for a parent controller.
     *
     * @return array
     */
    /*protected function buildParentReplacements()
    {

        $model = $this->option('model') ?? $this->argument('module');
        $parentModelClass = $this->parseModel($model);

        if (!class_exists($parentModelClass)) {
            if ($this->confirm("A {$parentModelClass} model does not exist. Do you want to generate it?", true)) {
                $this->call('make:model', ['name' => $parentModelClass]);
            }
        }

        return [
            'ParentDummyFullModelClass' => $parentModelClass,
            'ParentDummyModelClass' => class_basename($parentModelClass),
            'ParentDummyModelVariable' => lcfirst(class_basename($parentModelClass)),
        ];
    }*/

    /**
     * Build the model replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {

        $model = $this->option('model') ?? $this->argument('module');
        $modelClass = $this->parseModel($model);

        if (!class_exists($modelClass)) {
            if ($this->confirm("A {$modelClass} model does not exist. Do you want to generate it?", true)) {
                $this->call('make:model', ['name' => $modelClass]);
            }
        }

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
        ]);
    }

    /**
     * Get the fully-qualified model class name.
     *
     * @param string $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseModel($model)
    {

        $model = Str::studly($model);

        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $model = trim(str_replace('/', '\\', $model), '\\');


        if (!Str::startsWith($model, $this->getDefaultModelNamespace())) {
            $model = $this->getDefaultModelNamespace() . '\\' . $model;
        }

        return $model;
    }

    /**
     * @return array|string
     */
    protected function getControllerName()
    {
        $controller = Str::studly($this->getNameInput());

        if (Str::contains(strtolower($controller), 'controller') === false) {
            $controller .= 'Controller';
        }

        return $controller;
    }

    /**
     * @return array|string
     */
    protected function getModuleName()
    {
        return Str::studly($this->getModuleInput());
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the controller class.'],
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
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Generate a resource controller class.'],
            ['invokable', 'i', InputOption::VALUE_NONE, 'Generate a single method, invokable controller class.'],
            ['parent', 'p', InputOption::VALUE_OPTIONAL, 'Generate a nested resource controller class.'],
            ['api', null, InputOption::VALUE_NONE, 'Exclude the create and edit methods from the controller.'],
        ];
    }
}