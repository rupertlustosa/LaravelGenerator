<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MigrationMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file for the specified module.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Migration';

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        /*if (parent::handle() === false && !$this->option('force')) {
            return false;
        }*/

        $module = $this->qualifyClass($this->getModuleInput());
        $table = Str::snake(trim($this->option('table')));

        if (empty($table)) {

            $this->error('--table is must be a valid required table name');

            return false;
        }

        $fileName = $this->getMigrationFileName($table);
        $className = Str::studly($fileName);

        $path = $this->getMigrationsPath() . '/' . date('Y_m_d_His_') . $fileName . '.php';

        $this->makeDirectory($path);
        $stub = $this->files->get($this->getStub());

        $replaces['DummyTable'] = $table;
        $replaces['DummyClass'] = $className;
        $this->files->put($path, str_replace(array_keys($replaces), array_values($replaces), $stub));

        $this->info('Migration created successfully.');

        // Seed
        $path = $this->getSeedsPath() . '/' . $className . 'TableSeeder.php';

        $this->makeDirectory($path);
        $stub = $this->files->get($this->getStub());
        $this->files->put($path, str_replace(array_keys($replaces), array_values($replaces), $stub));

        $this->composer->dumpAutoloads();

        $this->info('Seed created successfully.');

    }

    protected function getMigrationFileName($table)
    {

        $update = $this->input->getOption('update') ?: false;

        if ($update) {

            return 'update_' . $table . '_table';
        }

        return 'create_' . $table . '_table';
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {

        $create = $this->input->getOption('create') ?: false;
        $update = $this->input->getOption('update') ?: false;

        if (!$create && !$update) {

            return __DIR__ . '/stubs/migration.blank.stub';
        } else if ($create) {

            return __DIR__ . '/stubs/migration.create.stub';
        }

        return __DIR__ . '/stubs/migration.update.stub';
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


    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['table', null, InputOption::VALUE_REQUIRED, 'Table name'],
            ['create', 'c', InputOption::VALUE_NONE, 'Create create migration'],
            ['update', 'u', InputOption::VALUE_NONE, 'Create update migration'],
            ['force', null, InputOption::VALUE_NONE, 'Create migration even if already exists'],
        ];
    }
}
