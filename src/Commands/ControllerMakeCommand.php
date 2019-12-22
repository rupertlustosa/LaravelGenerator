<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Support\Str;
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
    protected $description = 'Create a new controller class for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Controller';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if (parent::handle() === false && !$this->option('force')) {
            return false;
        }

        if ($this->option('model')) {

            $this->createService();
            $this->createPolicy();
            $this->createResource();
            $this->createCollection();
            $this->createRule();
        }
        $this->createApiRoute();
    }

    protected function createService()
    {

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('rlustosa:make-service', [
            'module' => $this->getModuleInput(),
            'name' => $this->getNameInput(),
            '--model' => $this->option('model') ? $modelName : null,
            '--force' => $this->option('force') ? true : null,
        ]);
    }

    protected function createPolicy()
    {

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('rlustosa:make-policy', [
            'module' => $this->getModuleInput(),
            'name' => $this->getNameInput(),
            '--model' => $this->option('model') ? $modelName : null,
            '--force' => $this->option('force') ? true : null,
        ]);
    }

    protected function createResource()
    {

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('rlustosa:make-resource', [
            'module' => $this->getModuleInput(),
            'name' => $this->getNameInput(),
            '--model' => $this->option('model') ? $modelName : null,
            '--force' => $this->option('force') ? true : null,
        ]);
    }

    protected function createCollection()
    {

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('rlustosa:make-collection', [
            'module' => $this->getModuleInput(),
            'name' => $this->getNameInput(),
            '--model' => $this->option('model') ? $modelName : null,
            '--force' => $this->option('force') ? true : null,
        ]);
    }

    protected function createRule()
    {

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('rlustosa:make-rule', [
            'module' => $this->getModuleInput(),
            'name' => $this->getNameInput(),
            '--resource' => $this->option('model') ? $modelName : null,
            '--force' => $this->option('force') ? true : null,
        ]);
    }

    protected function createApiRoute()
    {

        $pathRouteApi = $this->getRouteApiPath();

        if ($this->files->exists($pathRouteApi)) {

            $originalFile = $pathRouteApi;
            $originalFileContent = $this->files->get($originalFile);

            $replaces = $this->getDefaultForCommand();
            $replaces['DummyModulePlural'] = Str::snake(Str::pluralStudly($this->getNameInput()));

            $findMe = '$api->resource(\'' . $replaces['DummyModulePlural'] . '\', \'' . $replaces['DummyControllerClass'] . '\')';
            if (strpos($originalFileContent, $findMe)) {

                $this->info('Route already exists');
                return true;
            }

            preg_match('/(.+?)\}\)(.+?)/', $originalFileContent, $match);
            $endTag = $match[0];

            $newApiCode = str_replace(array_keys($replaces), array_values($replaces), $this->getCodeRoute($endTag));
            //dd($newApiCode);
            // Backup route file
            $this->files->put(str_replace('.php', date('_Ymd-His') . '.php', $pathRouteApi), $originalFileContent);

            // Update a route file
            $stubRouteApi = str_replace($endTag, $newApiCode, $originalFileContent);
            //dd($replaces, $stubRouteApi);
            $this->files->put($pathRouteApi, $stubRouteApi);

            $this->info('Api Route to Module ' . $this->qualifyClass($this->getModuleInput()) . ' backup was performed!');

            return false;
        }
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        $stub = null;

        if ($this->option('model')) {
            $stub = '/stubs/controller.model.stub';
        } elseif ($this->option('web')) {
            $stub = '/stubs/controller.web.stub';
        } elseif ($this->option('invokable')) {
            $stub = '/stubs/controller.invokable.stub';
        }

        if ($this->option('api') && is_null($stub)) {
            $stub = '/stubs/controller.api.stub';
        } elseif ($this->option('api') && !is_null($stub) && !$this->option('invokable')) {
            $stub = str_replace('.stub', '.api.stub', $stub);
        }

        $stub = $stub ?? '/stubs/controller.plain.stub';

        return __DIR__ . $stub;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the controller already exists'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given model.'],
            ['web', 'w', InputOption::VALUE_NONE, 'Generate a resource controller class for WEB.'],
            ['api', 'a', InputOption::VALUE_NONE, 'Generate a resource controller class for API.'],
            ['invokable', 'i', InputOption::VALUE_NONE, 'Generate a single method, invokable controller class.'],
        ];
    }
}
