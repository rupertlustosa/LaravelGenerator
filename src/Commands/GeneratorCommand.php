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
     * @var Filesystem
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
     * @param Filesystem $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $missingDependencies = $this->missingDependencies();
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

        if (!$missingDependencies) {

            // Next, we will generate the path to the location where this class' file should get
            // written. Then, we will build the class and make the proper replacements on the
            // stub files so that it gets the correctly formatted namespace and class name.
            $this->makeDirectory($path);

            $this->files->put($path, $this->sortImports($this->buildClass()));

            //$this->createdSuccessfully();
            $this->info($this->type . ' created successfully.');
        } else {

            $this->error($path . ' não pode ser criado devido a ausência das dependências acima. Para corrigir execute os comandos:');
            $this->info(implode(' && ', $missingDependencies));
        }

    }

    abstract protected function missingDependencies();

    /**
     * Get the destination file path.
     *
     * @return string
     */
    abstract protected function getDestinationFilePath();

    abstract protected function alreadyExists();

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
     * Get the stub file for the generator.
     *
     * @return string
     */
    abstract protected function getStub();

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
     * Get the default namespace for the class.
     *
     * @return string
     */
    abstract protected function getDefaultNamespace();

    /**
     * @return array|string
     */
    protected function getModelName()
    {

        return Str::studly($this->getNameInput());
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
    protected function getServiceProviderName()
    {

        return Str::studly($this->getModuleName()) . 'ServiceProvider';
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
    protected function getModuleInput()
    {
        return trim($this->argument('module'));
    }

    /**
     * @return array|string
     */
    protected function getRouteServiceProviderName()
    {

        //return Str::studly($this->getModuleName()).'RouteServiceProvider';
        return 'RouteServiceProvider';
    }

    /**
     * @return array|string
     */
    protected function getRouteApiName()
    {

        return 'api';
    }

    /**
     * Build the model replacement values.
     *
     * @param array $replace
     * @param bool $createIfNotExists
     * @return array
     */
    protected function buildModelReplacements(array $replace, $createIfNotExists = true)
    {

        $model = $this->option('model');
        $modelClass = $this->parseModel($model);
        //dd('buildModelReplacements');
        if (!class_exists($modelClass)) {

            if ($createIfNotExists) {

                if ($this->confirm("A {$modelClass} model does not exist. Do you want to generate it?", true)) {

                    $this->call('rlustosa:make-model', ['module' => $this->getModuleInput(), 'name' => $model]);
                }
            } else {

                $this->warn("A {$modelClass} model does not exist. Please, create it first!", true);
                exit;
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
     * @throws InvalidArgumentException
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
     * Get the default namespace for the model class.
     *
     * @return string
     */
    protected function getDefaultModelNamespace()
    {

        return trim($this->rootNamespace() . '\\' . $this->getModuleName() . '\Models');
    }

    /**
     * Get the default namespace for the model class.
     *
     * @return string
     */
    protected function getDefaultControllerNamespace()
    {

        return trim($this->rootNamespace() . '\\' . $this->getModuleName() . '\Http\Controllers');
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
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultServiceNamespace()
    {

        return trim($this->rootNamespace() . '\\' . $this->getModuleName() . '\Services');
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
     * Build the service replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildPolicyReplacements(array $replace)
    {

        $model = $this->option('model');

        $policyNamespace = $this->getDefaultPolicyNamespace();
        $policyClass = $policyNamespace . '\\' . $this->getPolicyName();

        if (!class_exists($policyClass)) {

            if ($this->confirm("A {$policyClass} policy does not exist. Do you want to generate it?", true)) {
                $this->call('rlustosa:make-policy', ['module' => $this->getModuleInput(), 'name' => $model, '--model' => $model]);
            }
        }

        return $replace;
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
     * Build the service replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildValidatorRuleReplacements(array $replace)
    {

        $model = $this->option('model');

        $validatorNamespace = $this->getDefaultValidatorsNamespace();
        $validatorClass = $validatorNamespace . '\\' . $this->getValidatorRuleName();

        if (!class_exists($validatorClass)) {

            if ($this->confirm("A {$validatorClass} validator does not exist. Do you want to generate it?", true)) {
                $this->call('rlustosa:make-rule', ['module' => $this->getModuleInput(), 'name' => $model, '--model' => $model]);
            }
        }

        return $replace;
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultValidatorsNamespace()
    {

        return trim($this->rootNamespace() . '\\' . $this->getModuleName() . '\Validators');
    }

    /**
     * @return array|string
     */
    protected function getValidatorRuleName()
    {

        /*$validator = Str::studly($this->option('model'));

        if (Str::contains(strtolower($validator), 'rule') === false) {
            $validator .= 'Rule';
        }

        return $validator;*/
        $validatorRule = Str::studly($this->getNameInput());

        if (Str::contains(strtolower($validatorRule), 'rule') === false) {
            $validatorRule .= 'Rule';
        }

        return $validatorRule;
    }

    /**
     * Build the service replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildValidatorStoreRequestReplacements(array $replace)
    {

        $model = $this->option('model');

        $validatorNamespace = $this->getDefaultValidatorsNamespace();
        $validatorStoreRequestClass = $validatorNamespace . '\\' . $this->getValidatorStoreRequestName();

        if (!class_exists($validatorStoreRequestClass)) {

            if ($this->confirm("A {$validatorStoreRequestClass} validator store request does not exist. Do you want to generate it?", true)) {
                $this->call('rlustosa:make-store-request', ['module' => $this->getModuleInput(), 'name' => $model, '--model' => $model]);
            }
        }

        return array_merge($replace, [
            'DummyFullStoreRequestClass' => $validatorStoreRequestClass,
            'DummyValidatorNamespace' => $validatorNamespace,
            'DummyStoreRequestClass' => $this->getValidatorStoreRequestName(),
            'DummyStoreRequestVariable' => lcfirst($this->getValidatorStoreRequestName()),
        ]);
    }

    /**
     * @return array|string
     */
    protected function getValidatorStoreRequestName()
    {

        $validator = Str::studly($this->option('model'));

        if (Str::contains(strtolower($validator), 'storerequest') === false) {
            $validator .= 'StoreRequest';
        }

        return $validator;
    }

    /**
     * Build the service replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildValidatorUpdateRequestReplacements(array $replace)
    {

        $model = $this->option('model');

        $validatorNamespace = $this->getDefaultValidatorsNamespace();
        $validatorUpdateRequestClass = $validatorNamespace . '\\' . $this->getValidatorUpdateRequestName();

        if (!class_exists($validatorUpdateRequestClass)) {

            if ($this->confirm("A {$validatorUpdateRequestClass} validator update request does not exist. Do you want to generate it?", true)) {
                $this->call('rlustosa:make-update-request', ['module' => $this->getModuleInput(), 'name' => $model, '--model' => $model]);
            }
        }

        return array_merge($replace, [
            'DummyFullUpdateRequestClass' => $validatorUpdateRequestClass,
            'DummyValidatorNamespace' => $validatorNamespace,
            'DummyUpdateRequestClass' => $this->getValidatorUpdateRequestName(),
            'DummyUpdateRequestVariable' => lcfirst($this->getValidatorUpdateRequestName()),
        ]);
    }

    /**
     * @return array|string
     */
    protected function getValidatorUpdateRequestName()
    {

        $validator = Str::studly($this->option('model'));

        if (Str::contains(strtolower($validator), 'updaterequest') === false) {
            $validator .= 'UpdateRequest';
        }

        return $validator;
    }

    /*
     * Determine if the class already exists.
     *
     * @return bool
     */

    /**
     * Build the service replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildResourceReplacements(array $replace)
    {

        $model = $this->option('model');

        $resourceNamespace = $this->getDefaultResourceNamespace();
        $resourceClass = $resourceNamespace . '\\' . $this->getResourceName();

        if (!class_exists($resourceClass)) {

            if ($this->confirm("A {$resourceClass} resource does not exist. Do you want to generate it?", true)) {
                $this->call('rlustosa:make-resource', ['module' => $this->getModuleInput(), 'name' => $model, '--model' => $model]);
            }
        }

        return array_merge($replace, [
            'DummyResourceNamespace' => $resourceNamespace,
            'DummyFullResourceClass' => $resourceClass,
            'DummyResourceClass' => $this->getResourceName(),
        ]);
    }

    /**
     * Get the default namespace for the class.
     *
     * @return string
     */
    protected function getDefaultResourceNamespace()
    {

        return trim($this->rootNamespace() . '\\' . $this->getModuleName() . '\Resources');
    }

    /**
     * @return array|string
     */
    protected function getResourceName()
    {

        $resource = Str::studly($this->getNameInput());

        if (Str::contains(strtolower($resource), 'resource') === false) {
            $resource .= 'Resource';
        }

        return $resource;
    }

    //abstract protected function createdSuccessfully();

    /**
     * Build the service replacement values.
     *
     * @param array $replace
     * @return array
     */
    protected function buildCollectionReplacements(array $replace)
    {

        $model = $this->option('model');

        $resourceNamespace = $this->getDefaultResourceNamespace();
        $collectionClass = $resourceNamespace . '\\' . $this->getCollectionName();

        if (!class_exists($collectionClass)) {

            if ($this->confirm("A {$collectionClass} collection does not exist. Do you want to generate it?", true)) {
                $this->call('rlustosa:make-collection', ['module' => $this->getModuleInput(), 'name' => $model, '--model' => $model]);
            }
        }

        return array_merge($replace, [
            'DummyCollectionNamespace' => $resourceNamespace,
            'DummyFullCollectionClass' => $collectionClass,
            'DummyCollectionClass' => $this->getCollectionName(),
        ]);
    }

    /**
     * @return array|string
     */
    protected function getCollectionName()
    {

        $collection = Str::studly($this->getNameInput());

        if (Str::contains(strtolower($collection), 'collection') === false) {
            $collection .= 'Collection';
        }

        return $collection;
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
    protected function getDefaultProvidersNamespace()
    {

        return trim($this->rootNamespace() . '\\' . $this->getModuleName() . '\Providers');
    }
}