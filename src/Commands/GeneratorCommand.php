<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

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
     *
     * @return bool|null
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $name = $this->qualifyClass($this->getNameInput());

        $path = $this->getPath($name);
        //dd($name, $path);
        // First we will check to see if the class already exists. If it does, we don't want
        // to create the class and overwrite the user's code. So, we will bail out so the
        // code is untouched. Otherwise, we will continue generating this class' files.
        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->alreadyExists($this->getNameInput())) {
            $this->error($this->type . ' already exists!');

            return false;
        }

        // Next, we will generate the path to the location where this class' file should get
        // written. Then, we will build the class and make the proper replacements on the
        // stub files so that it gets the correctly formatted namespace and class name.
        $this->makeDirectory($path);

        $this->files->put($path, $this->sortImports($this->buildClass($name)));

        $this->info($this->type . ' created successfully.');
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
        $name = Str::studly($name);
        return $name;
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
     * Get the destination class path.
     *
     * @return string
     */
    protected function getPath()
    {

        $namespace = null;
        $class = null;

        $data = $this->getDefaultForCommand();

        foreach ($data as $key => $value) {

            if (Str::contains($key, 'Namespace')) {

                $namespace = $value;
            } elseif (Str::contains($key, 'Class')) {

                $class = $value;
            }
        }

        if (empty($class)) {

            throw new Exception('Invalid mapping to Namespace:' . $this->type);
        }

        $fullClassNamespaced = $namespace . '\\' . $class;

        return base_path() . '/' . str_replace('\\', '/', $fullClassNamespaced) . '.php';
    }

    protected function getDefaultForCommand()
    {

        if (array_key_exists(Str::camel($this->type), $this->getDefaultsForClasses())) {
            return $this->getDefaultsForClasses()[Str::camel($this->type)];
        } else {
            throw new Exception('Invalid param $type ' . $this->type);
        }

        return [];
    }

    protected function getDefaultsForClasses($class = null)
    {

        $data = [
            'controller' => [
                'DummyControllerNamespace' => 'Modules\ModuleName\Http\Controllers',
                'DummyControllerClass' => 'ClassNameController',
            ],
            'model' => [
                'DummyModelNamespace' => 'Modules\ModuleName\Models',
                'DummyModelClass' => 'ClassName',
                'DummyModelFullNamed' => 'Modules\ModuleName\Models\ClassName',
                'DummyModelVariable' => 'ModelVariable',
            ],
            'policy' => [
                'DummyPolicyNamespace' => 'Modules\ModuleName\Policies',
                'DummyPolicyClass' => 'ClassNamePolicy',
                'DummyPolicyFullNamed' => 'Modules\ModuleName\Policies\ClassNamePolicy',
            ],
            'scaffold' => [
                'DummyProviderNamespace' => 'Modules\ModuleName\Providers',
                'DummyServiceProviderClass' => 'ModuleNameServiceProvider',
                'DummyRouteServiceProviderClass' => 'RouteServiceProvider',
            ],
            'collection' => [
                'DummyResourceNamespace' => 'Modules\ModuleName\Resources',
                'DummyCollectionClass' => 'ClassNameCollection',
                'DummyCollectionFullNamed' => 'Modules\ModuleName\Resources\ClassNameCollection',
            ],
            'resource' => [
                'DummyResourceNamespace' => 'Modules\ModuleName\Resources',
                'DummyResourceClass' => 'ClassNameResource',
                'DummyResourceFullNamed' => 'Modules\ModuleName\Resources\ClassNameResource',
            ],
            'service' => [
                'DummyServiceNamespace' => 'Modules\ModuleName\Services',
                'DummyServiceClass' => 'ClassNameService',
                'DummyServiceFullNamed' => 'Modules\ModuleName\Services\ClassNameService',
            ],
            'rule' => [
                'DummyValidatorsNamespace' => 'Modules\ModuleName\Validators',
                'DummyRuleClass' => 'ClassNameRule',
                'DummyRuleFullNamed' => 'Modules\ModuleName\Validators\ClassNameRule',
            ],
            'storeRequest' => [
                'DummyValidatorsNamespace' => 'Modules\ModuleName\Validators',
                'DummyStoreRequestClass' => 'ClassNameStoreRequest',
                'DummyStoreRequestFullNamed' => 'ClassNameStoreRequest',
            ],
            'updateRequest' => [
                'DummyValidatorsNamespace' => 'Modules\ModuleName\Validators',
                'DummyUpdateRequestClass' => 'ClassNameUpdateRequest',
                'DummyUpdateRequestFullNamed' => 'Modules\ModuleName\Validators\ClassNameUpdateRequest',
            ],
            'others' => [
                'DummyDefaultApiControllerNamespace' => 'Modules\Http\ApiController',
            ],
        ];

        $classBase = $class ?? $this->getNameInput();
        $replace = [];
        $replace['ModuleName'] = $this->qualifyClass($this->getModuleInput());
        $replace['ClassName'] = $this->qualifyClass($classBase);
        $replace['ModelVariable'] = Str::camel($this->qualifyClass($classBase));
        //dd(Str::snake($this->qualifyClass($classBase)));
        foreach ($data as $key => $values) {

            foreach ($values as $internalKey => $value) {

                $data[$key][$internalKey] = str_replace(
                    array_keys($replace), array_values($replace), $value
                );
            }
        }

        return $data;
    }

    /**
     * Get the desired module name from the input.
     *
     * @return string
     */
    protected function getModuleInput()
    {
        return trim($this->argument('module'));
    }

    /**
     * Determine if the class already exists.
     *
     * @param string $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return $this->files->exists($this->getPath($this->qualifyClass($rawName)));
    }

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
     * Build the class with the given name.
     *
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass()
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceClass($stub);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    abstract protected function getStub();

    /**
     * Replace the class name for the given stub.
     *
     * @param string $stub
     * @return string
     */
    protected function replaceClass($stub)
    {

        $replaces = [];

        foreach ($this->getDefaultsForClasses() as $key => $values) {

            foreach ($values as $internalKey => $value) {

                $replaces[$internalKey] = $value;
            }
        }

        return str_replace(array_keys($replaces), array_values($replaces), $stub);
    }

    protected function getRouteApiPath()
    {

        return base_path() . '/' . str_replace('\\', '/', $this->rootModuleNamespace()) . $this->qualifyClass($this->getModuleInput()) . '/routes/api.php';
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootModuleNamespace()
    {
        return 'Modules\\';
    }

    protected function classExists($type, $name)
    {

        $x = null;

        if (array_key_exists(Str::lower($type), $this->getDefaultsForClasses())) {

            $mapping = $this->getDefaultsForClasses($name)[Str::lower($type)];
            $classString = 'Dummy' . Str::studly($type) . 'FullNamed';

            return class_exists($mapping[$classString]);
        } else {

            throw new Exception('Invalid param $type ' . $type);
        }
        dd($name, $type, $x, $class);
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param string $stub
     * @param string $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name)
    {
        $stub = str_replace(
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel'],
            [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel()],
            $stub
        );

        return $this;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param string $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace()
    {
        return $this->laravel->getNamespace();
    }

    /**
     * Get the model for the default guard's user provider.
     *
     * @return string|null
     */
    protected function userProviderModel()
    {
        $guard = config('auth.defaults.guard');

        $provider = config("auth.guards.{$guard}.provider");

        return config("auth.providers.{$provider}.model");
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The name of the module'],
            ['name', InputArgument::REQUIRED, 'The name of the class'],
        ];
    }
}
