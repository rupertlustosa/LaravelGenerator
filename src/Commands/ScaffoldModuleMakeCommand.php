<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Support\Str;
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

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        //dd(__CLASS__);
        /*if (parent::handle() === false && !$this->option('force')) {
            return false;
        }*/

        $data = $this->getDefaultForCommand();

        $namespace = $data['DummyProviderNamespace'];
        $pathServiceProvider = base_path() . '/' . str_replace('\\', '/', $namespace . '\\' . $data['DummyServiceProviderClass']) . '.php';
        $pathRouteServiceProvider = base_path() . '/' . str_replace('\\', '/', $namespace . '\\' . $data['DummyRouteServiceProviderClass']) . '.php';

        $stubServiceProvider = $this->files->get(__DIR__ . '/stubs/service-provider.stub');
        $stubRouteServiceProvider = $this->files->get(__DIR__ . '/stubs/route-service-provider.stub');

        $this->makeDirectory($pathServiceProvider);

        $this->files->put($pathServiceProvider, $this->replaceClass($stubServiceProvider));
        $this->info('ServiceProvider created successfully.');

        $this->files->put($pathRouteServiceProvider, $this->replaceClass($stubRouteServiceProvider));
        $this->info('RouteServiceProvider created successfully.');

        /////////////////////
        $pathRouteApi = $this->getRouteApiPath();
        $stubRouteApi = $this->files->get(__DIR__ . '/stubs/route-api.stub');

        $this->makeDirectory($pathRouteApi);

        preg_match('/(.+?)\}\)(.+?)/', $stubRouteApi, $match);

        $endTag = $match[0];

        $replaces[$endTag] = $this->getRoute($endTag);
        $replaces['DummyModulePlural'] = Str::snake(Str::pluralStudly($this->getNameInput()));

        $stubRouteApi = str_replace(array_keys($replaces), array_values($replaces), $stubRouteApi);

        $this->files->put($pathRouteApi, $this->replaceClass($stubRouteApi));
        $this->info('Api route created successfully.');
    }

    protected function getRoute($endTag)
    {

        return "
        \$api->resource('DummyModulePlural', 'DummyControllerClass')->except([
            'create', 'edit'
        ]);
        
    " . trim($endTag);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        if ($this->option('resource')) {

            $this->createRule();
            $stub = '/stubs/update-request.model.stub';
        } else {

            $stub = '/stubs/update-request.plain.stub';
        }
        return __DIR__ . $stub;
    }

    /**
     * Create a model.
     *
     * @return void
     */
    protected function createRule()
    {

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('rlustosa:make-rule', [
            'module' => $this->getModuleInput(),
            'name' => $modelName,
        ]);
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
}
