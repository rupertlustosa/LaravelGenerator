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
    private $myModel;

    private $ignoreInNames = [];
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
        $this->jsPath = $this->viewsPath . '/js';
        $this->componentsPath = $this->viewsPath . '/components';
        $this->componentsSharedPath = $this->viewsPath . '/components/shared';

        $this->startDoctrineConnection();

        if (!$this->initTableDetails()) {

            $this->error('A table "' . $this->table . '" not exists in database!');
            return false;
        }

        $this->skeletonPath = $this->viewsPath . '/.skeleton.' . $this->table . '.json';

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
            $this->updateRules();
            $this->updateResource();
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

        $this->myModel = new $modelClass();
        $this->table = $this->myModel->getTable();

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

            $this->ignoreInNames = [
                $this->myModel::CREATED_AT,
                $this->myModel::UPDATED_AT,
                $this->myModel->getDeletedAtColumn(),
                'email_verified_at',
                'remember_token',
                'password',
                'user_creator_id',
                'user_updater_id',
                'user_eraser_id',
            ];

            $this->ignoreInListing = [
                $this->myModel->getKeyName(),
                $this->myModel::CREATED_AT,
                $this->myModel::UPDATED_AT,
                $this->myModel->getDeletedAtColumn(),
                'email_verified_at',
                'remember_token',
                'password',
                'user_creator_id',
                'user_updater_id',
                'user_eraser_id',
            ];

            $this->ignoreInForm = [
                $this->myModel->getKeyName(),
                $this->myModel::CREATED_AT,
                $this->myModel::UPDATED_AT,
                $this->myModel->getDeletedAtColumn(),
                'email_verified_at',
                'remember_token',
                'user_creator_id',
                'user_updater_id',
                'user_eraser_id',
            ];

            $this->ignoreInFill = [
                $this->myModel->getKeyName(),
                $this->myModel::CREATED_AT,
                $this->myModel::UPDATED_AT,
                $this->myModel->getDeletedAtColumn(),
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
        $inRules = [];

        foreach ($this->columns as $column) {

            $columnName = $column->getName();
            $label = Str::upper(str_replace('_', ' ', $columnName));

            if (!in_array($columnName, $this->ignoreInListing)) {

                $inListing[] = $columnName;
            }

            if (!in_array($columnName, $this->ignoreInForm)) {

                $inForm[] = $columnName;
            }

            if (!in_array($columnName, array_merge($this->ignoreInNames, $this->ignoreInForm))) {

                $names[] = [
                    'id' => $columnName,
                    'label' => $label,
                ];
            }

            if (!in_array($columnName, $this->ignoreInFill)) {

                $fill[] = $columnName;
            }
        }

        $inRules = $this->generateRules($this->myModel);

        $stub = null;
        $stub['names'] = $names;
        $stub['fill'] = $fill;
        $stub['rules'] = $inRules;
        $stub['search'] = $inListing;
        $stub['listing'] = $inListing;
        $stub['form'] = $inForm;

        $this->files->put($path, json_encode($stub, JSON_PRETTY_PRINT));
        $this->info('Skeleton created successfully.');
    }

    private function generateRules($model)
    {

        $table = $model->getTable();
        $structure = rl_load_table_structure($table);

        $columns = [];
        foreach ($structure['columns'] as $column) {

            if (!in_array($column['name'],
                [
                    $model::CREATED_AT,
                    $model::UPDATED_AT,
                    $model->getDeletedAtColumn(),
                    'remember_token',
                    'email_verified_at',
                ]
            )) {

                $nullableOrRequired = $column['notnull'] ? 'required' : 'nullable';

                if ($column['name'] == $model->getKeyName()) {

                    $columns[$column['name']] = 'required|integer|exists:' . $model->getTable() . ',id,deleted_at,NULL';
                } elseif (in_array($column['name'], array_keys($structure['foreignKeys']))) {

                    $referenceTable = $structure['foreignKeys'][$column['name']];

                    $columns[$column['name']] = $nullableOrRequired . '|integer|exists:' . $referenceTable . ',id,deleted_at,NULL';
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'password')) {

                    $columns[$column['name']] = $nullableOrRequired . '|string|min:8|confirmed';
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'email')) {

                    $columns[$column['name']] = $nullableOrRequired . '|email|max:' . $column['length'];
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'image')) {

                    $columns[$column['name']] = $nullableOrRequired . '|image|mimes:jpeg,png,jpg,gif,svg|max:4096';
                } elseif ($column['type'] == 'string' && $this->contains($column['name'], 'phone')) {

                    $columns[$column['name']] = $nullableOrRequired . '|max:' . $column['length'] . '|regex:/^\(?\d{2}\)?[\s-]?\d{4,5}-?\d{4}$/i';
                } elseif ($column['type'] == 'string') {

                    $columns[$column['name']] = $nullableOrRequired . '|max:' . $column['length'];
                } elseif ($column['type'] == 'boolean') {

                    $columns[$column['name']] = $nullableOrRequired . '|boolean';
                } elseif (in_array($column['type'], ['bigint', 'integer', 'smallint'])) {

                    $columns[$column['name']] = $nullableOrRequired . '|numeric';
                } elseif ($column['type'] == 'datetime') {

                    $columns[$column['name']] = $nullableOrRequired . '|date_format:d/m/Y H:i';
                } elseif ($column['type'] == 'date') {

                    $columns[$column['name']] = $nullableOrRequired . '|date_format:d/m/Y';
                } elseif ($column['type'] == 'time') {

                    $columns[$column['name']] = $nullableOrRequired . '|date_format:H:i';
                } elseif ($column['type'] == 'text') {

                    $columns[$column['name']] = $nullableOrRequired;
                } else {

                    $columns[$column['name']] = $nullableOrRequired;
                }
            }
        }

        $data = [];
        $rules = [];

        foreach ($columns as $key => $rule) {

            $rules['definition'][] = "'{$key}' => '{$rule}',";
            $rules['this'][] = "'{$key}' => self::\$rules['{$key}'],";
        }

        return $rules;
    }

    private function contains($string, $needles)
    {

        return Str::contains(strtolower($string), $needles);
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

        $htmlView = new HtmlView();

        $mapping = json_decode($this->files->get($this->skeletonPath));
        $mappingNames = collect($mapping->names);

        $headBody = collect($mapping->form)->map(function ($field) use ($htmlView, $mappingNames) {

            $fields = $mappingNames->whereIn('id', is_array($field) ? $field : (array)$field);

            return $htmlView->generateFormHtml($fields);
        });

        $replaces = $this->getDefaultsForClasses($this->argument('model'))['model'];
        $path = $this->componentsPath . '/' . $replaces['DummyModelClass'] . 'FormComponent.vue';
        $this->makeDirectory($path);
        $stub = $this->files->get(__DIR__ . '/stubs/components/form.vue.stub');

        $replaces['DummyHtml'] = implode("\r\n", $headBody->toArray());
        $replaces['DummyModulePlural'] = Str::snake(Str::pluralStudly($this->argument('model')));

        $this->files->put($path, str_replace(array_keys($replaces), array_values($replaces), $stub));
        $this->info('VueForm created successfully.');
    }

    protected function createVueRoute()
    {

        $replaces = $this->getDefaultsForClasses($this->argument('model'))['model'];
        $nameFile = $replaces['DummyModelVariable'];
        $path = $this->jsPath . '/' . $nameFile . 'Router.js';
        $this->makeDirectory($path);
        $stub = $this->files->get(__DIR__ . '/stubs/js/module.router.stub');

        $replaces['DummyModulePlural'] = Str::snake(Str::pluralStudly($this->argument('model')));

        $this->files->put($path, str_replace(array_keys($replaces), array_values($replaces), $stub));
        $this->info('VueRoute created successfully.');
    }

    protected function updateRules()
    {

        $ruleReplaces = $this->getDefaultsForClasses($this->argument('model'))['rule'];
        $storeRequestReplaces = $this->getDefaultsForClasses($this->argument('model'))['storeRequest'];
        $updateRequestReplaces = $this->getDefaultsForClasses($this->argument('model'))['updateRequest'];

        $pathRule = $this->getPathFromNamespace($ruleReplaces['DummyRuleFullNamed']);
        $pathStoreRequest = $this->getPathFromNamespace($storeRequestReplaces['DummyStoreRequestFullNamed']);
        $pathUpdateRequest = $this->getPathFromNamespace($updateRequestReplaces['DummyUpdateRequestFullNamed']);

        $mapping = json_decode($this->files->get($this->skeletonPath));
        $mappingRules = collect($mapping->rules);

        $definition = $mappingRules['definition'];
        $thisRules = $mappingRules['this'];
        //dd($definition, $thisRules);

        if (!$this->files->exists($pathRule)) {

            $this->error($pathRule . ' missing!');

            return false;
        } else {

            $stubRule = $this->files->get($pathRule);
            $replaces['DummyRules'] = implode("\r\n	    ", $definition);
            $replaces['DummyStaticRules'] = implode("\r\n            ", $thisRules);
            $this->files->put($pathRule, str_replace(array_keys($replaces), array_values($replaces), $stubRule));
        }

        /*

        //$replaces['DummyModulePlural'] = Str::snake(Str::pluralStudly($this->argument('model')));

        */
        $this->info('Rules successfully.');
    }

    private function getPathFromNamespace($fullClassNamespaced)
    {

        return base_path() . '/' . str_replace('\\', '/', $fullClassNamespaced) . '.php';
    }

    protected function updateResource()
    {

        $replaces = $this->getDefaultsForClasses($this->argument('model'))['resource'];

        $path = $this->getPathFromNamespace($replaces['DummyResourceFullNamed']);

        $mapping = json_decode($this->files->get($this->skeletonPath));
        $mapping = collect($mapping->names);

        if (!$this->files->exists($path)) {

            $this->error($path . ' missing!');

            return false;
        } else {

            $toArray = $mapping->pluck('id')->map(function ($column) {

                return "'{$column}' => \$this->{$column},";
            });
            $toArray->prepend('            ');

            $stub = $this->files->get($path);
            $replaces['//DummyResourceToArray'] = implode("\r\n            ", $toArray->toArray());
            $this->files->put($path, str_replace(array_keys($replaces), array_values($replaces), $stub));
        }

        /*

        //$replaces['DummyModulePlural'] = Str::snake(Str::pluralStudly($this->argument('model')));

        */
        $this->info('Rules successfully.');
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
