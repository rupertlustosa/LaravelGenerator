<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Support\Str;
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
    protected $description = 'Create a new Eloquent model class for the specified module.';

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

        if ($this->option('all')) {
            $this->input->setOption('migration', true);
            $this->input->setOption('controller', true);
            $this->input->setOption('resource', true);
        }

        if ($this->option('migration')) {
            $this->createMigration();
        }

        if ($this->option('controller') || $this->option('resource')) {
            $this->createController();
        }
    }

    /**
     * Create a migration file for the model.
     *
     * @return void
     */
    protected function createMigration()
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->argument('name'))));

        if ($this->option('pivot')) {
            $table = Str::singular($table);
        }

        $this->call('rlustosa:make-migration', [
            'module' => $this->getModuleInput(),
            '--table' => $table,
            '--create' => true,
            '--update' => false,
        ]);
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createController()
    {
        //$controller = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('rlustosa:make-controller', [
            'module' => $this->getModuleInput(),
            'name' => $this->getNameInput(),
            '--model' => $this->option('resource') ? $modelName : null,
            '--force' => $this->option('force') ? true : null,
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
            ['pivot', 'p', InputOption::VALUE_NONE, 'Indicates if the generated model should be a custom intermediate table model'],

            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
        ];
    }
}
