<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Symfony\Component\Console\Input\InputOption;

class ModuleMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

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

        $this->createScaffold();
        $this->createModel();
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createScaffold()
    {

        $this->call('rlustosa:make-scaffold', [
            'module' => $this->getModuleInput(),
            '--force' => $this->option('force') ? true : null,
        ]);
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createModel()
    {

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('rlustosa:make-model', [
            'module' => $this->getModuleInput(),
            'name' => $this->getNameInput(),
            //'--model' => $this->option('resource') ? $modelName : null,
            '--force' => $this->option('force') ? true : null,
            '--resource' => true,
        ]);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        return __DIR__ . '/stubs/model.stub';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['all', 'a', InputOption::VALUE_NONE, 'Generate a migration and resource controller for the model'],

            ['controller', 'c', InputOption::VALUE_NONE, 'Create a new controller for the model'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Indicates if the generated controller should be a resource controller'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model'],

            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
    }
}
