<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ScaffoldModuleMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-scaffold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a scaffold (ServiceProvider, RouteServiceProvider, ApiRoute) for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Scaffold';

    protected $mapping = [
        'DummyProviderNamespace' => 'Modules\ModuleName\Providers',
        'DummyServiceProviderClass' => 'ModuleNameServiceProvider',
        'DummyRouteServiceProviderClass' => 'RouteServiceProvider',
        'DummyModuleLowerCase' => 'ConfigNameFileName',
        'DummyModule' => 'ModuleName',
    ];

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {

        $this->createServiceProvider();
        $this->createRouteServiceProvider();
        $this->createRouteApi();
        $this->createApiController();
        $this->createConfig();
    }

    /**
     * Create a ServiceProvider.
     *
     */
    protected function createServiceProvider()
    {

        $mapping = $this->translateMapping();

        $namespace = $mapping['DummyProviderNamespace'];
        $path = base_path() . '/' . str_replace('\\', '/', $namespace . '\\' . $mapping['DummyServiceProviderClass']) . '.php';

        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->files->exists($path)
        ) {

            $this->error('ServiceProvider to Module' . $this->qualifyClass($this->getModuleInput()) . ' already exists!');

            return false;
        }

        $this->makeDirectory($path);
        $stub = $this->files->get(__DIR__ . '/stubs/service-provider.stub');
        $replaces = $mapping->toArray();
        $this->files->put($path, str_replace(array_keys($replaces), array_values($replaces), $stub));
        $this->info('ServiceProvider created successfully.');
    }

    protected function translateMapping()
    {

        $replaces = [];
        $replaces['ModuleName'] = $this->qualifyClass($this->getModuleInput());
        $replaces['ConfigNameFileName'] = Str::lower($replaces['ModuleName']);
        $controller = $this->getDefaultsForClasses($replaces['ModuleName'])['controller'];
        //dd($replaces);
        $mapping = collect($this->mapping + $controller);
        //dd($mapping);

        return $mapping->map(function ($i) use ($replaces) {
//dd($i);
            return str_replace(array_keys($replaces), array_values($replaces), $i);
        });
    }

    /**
     * Create a ServiceProvider.
     *
     * @return void
     */
    protected function createRouteServiceProvider()
    {

        $mapping = $this->translateMapping();

        $namespace = $mapping['DummyProviderNamespace'];
        $path = base_path() . '/' . str_replace('\\', '/', $namespace . '\\' . $mapping['DummyRouteServiceProviderClass']) . '.php';

        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->files->exists($path)
        ) {

            $this->error('RouteServiceProvider to Module' . $this->qualifyClass($this->getModuleInput()) . ' already exists!');

            return false;
        }

        $this->makeDirectory($path);
        $stub = $this->files->get(__DIR__ . '/stubs/route-service-provider.stub');
        $replaces = $mapping->toArray();
        $this->files->put($path, str_replace(array_keys($replaces), array_values($replaces), $stub));
        $this->info('RouteServiceProvider created successfully.');
    }

    /**
     * Create a RouteApi.
     *
     * @return void
     */
    protected function createRouteApi()
    {

        $pathRouteApi = $this->getRouteApiPath();

        if (
            //(!$this->hasOption('force') || !$this->option('force')) &&
        $this->files->exists($pathRouteApi)
        ) {

            $this->error('Api Route to Module' . $this->qualifyClass($this->getModuleInput()) . ' already exists!');

            return false;
        }

        $this->makeDirectory($pathRouteApi);
        $stubRouteApi = $this->files->get(__DIR__ . '/stubs/route-api.stub');
        $this->files->put($pathRouteApi, $stubRouteApi);
        $this->info('Api route created successfully.');
    }

    /**
     * Create a ApiController.
     *
     * @return void
     */
    protected function createApiController()
    {

        $path = $this->getApiControllerPath();

        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->files->exists($path)
        ) {

            $this->error('ApiController already exists!');

            return false;
        }

        $this->makeDirectory($path);
        $stub = $this->files->get(__DIR__ . '/stubs/api-controller.stub');
        $replaces = [];
        $replaces['DummyRootNamespaceHttp'] = app()->getNamespace() . 'Http';
        $this->files->put($path, str_replace(array_keys($replaces), array_values($replaces), $stub));
        $this->info('ApiController created successfully.');
    }


    protected function createConfig()
    {

        $mapping = $this->translateMapping();

        //$moduleLowerCase = $mapping['DummyModuleLowerCase'];

        $path = $this->getConfigPath() . '/config.php';

        if ((!$this->hasOption('force') ||
                !$this->option('force')) &&
            $this->files->exists($path)
        ) {

            $this->error('Config already exists!');

            return false;
        }

        $this->makeDirectory($path);
        $stub = $this->files->get(__DIR__ . '/stubs/config.stub');
        $this->files->put($path, $stub);
        $this->info('Config created successfully.');
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['resource', 'r', InputOption::VALUE_NONE, 'Indicates if the generated rule should be a resource "rule"'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
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
        ];
    }
}
