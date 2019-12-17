<?php

namespace Rlustosa\LaravelGenerator\Commands;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Rlustosa\LaravelGenerator\Html\HtmlView;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CodeMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'rlustosa:make-code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a code for the specified model.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Code';

    protected $mapping = [
        'DummyProviderNamespace' => 'Modules\ModuleName\Providers',
        'DummyServiceProviderClass' => 'ModuleNameServiceProvider',
        'DummyRouteServiceProviderClass' => 'RouteServiceProvider',
        'DummyModuleLowerCase' => 'ConfigNameFileName',
        'DummyModule' => 'ModuleName',
    ];

    protected $viewsPath = '';
    protected $skeletonPath = '';
    protected $jsPath = '';
    protected $componentsPath = '';
    protected $componentsSharedPath = '';
    private $sm;
    private $tableDetails;
    private $tableForeignKeys;
    private $columns;
    private $table;

    private $ignoreInListing = [];
    private $ignoreInForm = [];
    private $ignoreInFill = [];

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {

        $this->viewsPath = $this->getViewsPath();
        $this->skeletonPath = $this->viewsPath . '/.skeleton.json';
        $this->jsPath = $this->viewsPath . '/js';
        $this->componentsPath = $this->viewsPath . '/components';
        $this->componentsSharedPath = $this->viewsPath . '/components/shared';

        $this->startDoctrineConnection();

        if (!$this->initTableDetails()) {

            $this->error('A table "' . $this->table . '" not exists in database!');
            return false;
        }

        if (!$this->option('skeleton') && !$this->option('code')) {

            $this->error('Você deve especificar se deseja criar um skeleton ou o code');

            return false;
        }

        if ($this->option('skeleton')) {

            $this->createSkeleton();
        } elseif ($this->option('code')) {

            if (!$this->files->exists($this->skeletonPath)) {

                $this->error('Skeleton file missing');

                return false;
            }

            $this->createVueBar();
            $this->createVueList();
            $this->createVueForm();
            $this->createVueRoute();
        }

    }

    protected function startDoctrineConnection()
    {

        $defaultMappings = [
            'enum' => 'string',
            'json' => 'text',
            'bit' => 'boolean',
        ];

        $schemaManager = DB::getDoctrineSchemaManager();
        $platform = $schemaManager->getDatabasePlatform();

        foreach ($defaultMappings as $dbType => $doctrineType) {

            $platform->registerDoctrineTypeMapping($dbType, $doctrineType);
        }

        $this->sm = $schemaManager;
    }

    protected function initTableDetails()
    {

        $replace['ModuleName'] = $this->qualifyClass($this->getModuleInput());
        $replace['ClassName'] = $this->qualifyClass($this->argument('model'));

        $modelClass = 'Modules\ModuleName\Models\ClassName';
        $modelClass = str_replace(
            array_keys($replace), array_values($replace), $modelClass
        );

        if (!class_exists($modelClass)) {

            $this->error("A {$modelClass} model does not exist.", true);
            return false;
        }

        $myModel = new $modelClass();
        $this->table = $myModel->getTable();

        try {

            $this->tableDetails = $this->sm->listTableDetails($this->table);
            $this->tableForeignKeys = $this->sm->listTableForeignKeys($this->table);
            $this->columns = $this->sm->listTableColumns($this->table);

            if (count($this->columns) == 0) {

                return false;

            }

            /*$primaryKey = $this->table->getPrimaryKey();
            if ($primaryKey) {

                $primaryKey = $primaryKey->getColumns()[0];
                $this->ignoreInListing[] = $primaryKey;
            }*/

            $this->ignoreInListing = [
                $myModel->getKeyName(),
                $myModel::CREATED_AT,
                $myModel::UPDATED_AT,
                $myModel->getDeletedAtColumn(),
                'email_verified_at',
                'remember_token',
                'password',
                'user_creator_id',
                'user_updater_id',
                'user_eraser_id',
            ];

            $this->ignoreInForm = [
                $myModel->getKeyName(),
                $myModel::CREATED_AT,
                $myModel::UPDATED_AT,
                $myModel->getDeletedAtColumn(),
                'email_verified_at',
                'remember_token',
                'user_creator_id',
                'user_updater_id',
                'user_eraser_id',
            ];

            $this->ignoreInFill = [
                $myModel->getKeyName(),
                $myModel::CREATED_AT,
                $myModel::UPDATED_AT,
                $myModel->getDeletedAtColumn(),
                'email_verified_at',
                'remember_token',
                'password',
                'user_creator_id',
                'user_updater_id',
                'user_eraser_id',
            ];

        } catch (Exception $exception) {

            dd($exception->getMessage());
            return false;
        }

        return true;
    }

    protected function createSkeleton()
    {

        $path = $this->skeletonPath;

        if ((!$this->hasOption('force') || !$this->option('force')) && $this->files->exists($path)) {

            $this->error('Skeleton already exists!');

            return false;
        }

        $this->makeDirectory($path);
        $stub = $this->files->get(__DIR__ . '/stubs/skeleton.stub');
        $index = 0;
        $inListing = [];
        $inForm = [];
        $inSearch = [];
        $fill = [];
        $names = [];

        foreach ($this->columns as $column) {

            $columnName = $column->getName();
            $label = Str::upper(str_replace('_', ' ', $columnName));

            if (!in_array($columnName, $this->ignoreInListing)) {

                $inListing[] = $columnName;
            }

            if (!in_array($columnName, $this->ignoreInForm)) {

                $inForm[] = $columnName;
            }

            if (!in_array($columnName, array_merge($this->ignoreInListing, $this->ignoreInForm))) {

                $names[] = [
                    'id' => $columnName,
                    'label' => $label,
                ];
            }


            if (!in_array($columnName, $this->ignoreInFill)) {

                $fill[] = $columnName;
            }
        }

        $stub = null;
        $stub['names'] = $names;
        $stub['fill'] = $fill;
        $stub['search'] = $inListing;
        $stub['listing'] = $inListing;
        $stub['form'] = $inForm;

        $this->files->put($path, json_encode($stub, JSON_PRETTY_PRINT));
        $this->info('Skeleton created successfully.');
    }

    protected function createVueList()
    {

        $htmlView = new HtmlView();

        $mapping = json_decode($this->files->get($this->skeletonPath));
        $mappingNames = collect($mapping->names);

        $dummySearch = collect($mapping->search)->map(function ($field) use ($htmlView, $mappingNames) {

            $field = $mappingNames->where('id', $field)->first();
            return $htmlView->generateHtmlSearch($field);
        });

        $headBody = collect($mapping->listing)->map(function ($field) use ($htmlView, $mappingNames) {

            $fields = $mappingNames->whereIn('id', is_array($field) ? $field : (array)$field);

            return [
                'head' => $htmlView->generateTableListTh($fields),
                'body' => $htmlView->generateTableListTd($fields),
            ];
        });

        $replaces = $this->getDefaultsForClasses($this->argument('model'))['model'];
        $path = $this->componentsPath . '/' . $replaces['DummyModelClass'] . 'ListComponent.vue';
        $this->makeDirectory($path);
        $stub = $this->files->get(__DIR__ . '/stubs/components/list.vue.stub');

        $replaces['DummyHead'] = implode("\r\n", $headBody->pluck('head')->toArray());
        $replaces['DummyBody'] = implode("\r\n", $headBody->pluck('body')->toArray());
        $replaces['DummySearch'] = implode("", $dummySearch->toArray());
        $replaces['DummyModulePlural'] = Str::snake(Str::pluralStudly($this->argument('model')));

        $this->files->put($path, str_replace(array_keys($replaces), array_values($replaces), $stub));
        $this->info('VueList created successfully.');
    }

    protected function createVueForm()
    {

        $this->info('VueForm created successfully.');
    }

    protected function createVueRoute()
    {

        $this->info('VueRoute created successfully.');
    }

    protected function createVueBar()
    {

        $replaces = $this->getDefaultsForClasses($this->argument('model'))['model'];
        $path = $this->componentsPath . '/' . $replaces['DummyModelClass'] . 'NavBarComponent.vue';
        $this->makeDirectory($path);
        $stub = $this->files->get(__DIR__ . '/stubs/components/navbar.vue.stub');

        $replaces['DummyModulePlural'] = Str::snake(Str::pluralStudly($this->argument('model')));

        $this->files->put($path, str_replace(array_keys($replaces), array_values($replaces), $stub));
        $this->info('VueBar created successfully.');
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
            ['skeleton', 's', InputOption::VALUE_NONE, 'Create the Skeleton'],
            ['code', 'c', InputOption::VALUE_NONE, 'Create the Skeleton'],
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
            ['module', InputArgument::REQUIRED, 'The name of module will be used.'],
            ['model', InputArgument::REQUIRED, 'The name of model will be used.'],
        ];
    }
}
