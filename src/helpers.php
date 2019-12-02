<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('load_table_structure')) {
    /**
     * Get the path to the public folder.
     *
     * @param string $table
     * @return string
     */
    function load_table_structure($table)
    {

        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $sm = DB::getDoctrineSchemaManager();

        $tables = $sm->listTables();

        foreach ($tables as $table) {
            echo $table->getName() . " columns:\n\n";
            foreach ($table->getColumns() as $column) {
                echo ' - ' . $column->getName() . "\n";
            }
        }
    }
}