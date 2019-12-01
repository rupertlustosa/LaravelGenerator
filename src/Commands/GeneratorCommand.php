<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use InvalidArgumentException;

abstract class GeneratorCommand extends Command
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type;

    /**
     * Create a new controller creator command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    abstract protected function getStub();

    /**
     * Get the destination file path.
     *
     * @return string
     */
    abstract protected function getDestinationFilePath();

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $path = str_replace('\\', '/', $this->getDestinationFilePath());
        //dd($path, $this->getDestinationFilePath());
        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if (
            (!$this->hasOption('force') || !$this->option('force')) &&
            $this->alreadyExists()
        ) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass()));

        //$this->createdSuccessfully();
        $this->info($this->type . ' created successfully.');
    }

    /**
     * @return array|string
     */
    protected function getModuleName()
    {
        return Str::studly($this->getModuleInput());
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        return trim($this->argument('name'));
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getModuleInput()
    {
        return trim($this->argument('module'));
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
     * Parse the class name and format according to the root namespace.
     *
     * @param string $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = ltrim($name, '\\/');

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        $name = str_replace('/', '\\', $name);

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')) . '\\' . $name
        );
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return 'Modules';
    }

    /**
     * @return array|string
     */
    protected function getModelName()
    {

        return Str::studly($this->getNameInput());
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
    protected function getServiceName()
    {

        $service = Str::studly($this->getNameInput());

        if (Str::contains(strtolower($service), 'service') === false) {
            $service .= 'Service';
        }

        return $service;
    }

    /**
     * @return array|string
     */
    protected function getPolicyName()
    {

        $policy = Str::studly($this->getNameInput());

        if (Str::contains(strtolower($policy), 'policy') === false) {
            $policy .= 'Policy';
        }

        return $policy;
    }

    /**
     * Build the model replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {

        $model = $this->option('model');
        $modelClass = $this->parseModel($model);
//dd($modelClass == 'Modules\User\Models\User', class_exists($modelClass));

        if (!class_exists($modelClass)) {
            if ($this->confirm("A {$modelClass} model does not exist XXX. Do you want to generate it?", true)) {
                $this->call('rlustosa:make-model', ['module' => $this->getModuleInput(), 'name' => $model]);
            }
        }

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
        ]);
    }

    /**
     * Build the service replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildServiceReplacements(array $replace)
    {

        $model = $this->option('model');

        $serviceNamespace = $this->getDefaultServiceNamespace();

        $serviceClass = $serviceNamespace . '\\' . $this->getServiceName();
        //$serviceClass = $this->getFullyQualifiedServiceClassName($model);

        if (!class_exists($serviceClass)) {
            if ($this->confirm("A {$serviceClass} service does not exist. Do you want to generate it?", true)) {
                $this->call('rlustosa:make-service', ['module' => $this->getModuleInput(), 'name' => $model, '--model' => $model]);
            }
        }

        return array_merge($replace, [
            'DummyFullServiceClass' => $serviceClass,
            'DummyServiceNamespace' => $serviceNamespace,
            'DummyServiceClass' => $this->getServiceName(),
            'DummyServiceVariable' => lcfirst($this->getServiceName()),
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
     * Get the default namespace for the class.
     *
     * @param string $model
     * @return string
     */
    protected function getFullyQualifiedServiceClassName($model)
    {

        $model = Str::studly($model);

        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $model = trim(str_replace('/', '\\', $model), '\\');

        return trim($this->getDefaultServiceNamespace() . '\\' . $model);
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultServiceNamespace()
    {

        return trim($this->rootNamespace() . '\\' . $this->getModuleName() . '\Services');
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultPolicyNamespace()
    {

        return trim($this->rootNamespace() . '\\' . $this->getModuleName() . '\Policies');
    }

    /**
     * Alphabetically sorts the imports for the given stub.
     *
     * @param string $stub
     * @return string
     */
    protected function sortImports($stub)
    {
        if (preg_match('/(?P<imports>(?:use [^;]+;$\n?)+)/m', $stub, $match)) {
            $imports = explode("\n", trim($match['imports']));

            sort($imports);

            return str_replace(trim($match['imports']), implode("\n", $imports), $stub);
        }

        return $stub;
    }

    /*
     * Determine if the class already exists.
     *
     * @return bool
     */
    abstract protected function alreadyExists();

    //abstract protected function createdSuccessfully();


    /**
     * Get class name.
     *
     * @return string
     */
    public function getClass()
    {
        return class_basename($this->argument($this->argumentName));
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    abstract protected function getDefaultNamespace();

    /**
     * Build the directory for the class if necessary.
     *
     * @param string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }

        return $path;
    }
}