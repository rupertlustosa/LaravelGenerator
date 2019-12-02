<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('rl_load_table_structure')) {
    /**
     * Get the path to the public folder.
     *
     * @param string $tableName
     * @return array
     */
    function rl_load_table_structure($tableName): array
    {

        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        $sm = DB::getDoctrineSchemaManager();

        $mapping = [];
        $mappingForeignKeys = [];

        $table = $sm->listTableDetails($tableName);
        $foreignKeys = $sm->listTableForeignKeys($tableName);

        foreach ($foreignKeys as $foreignKey) {
            //dd($foreignKey->getLocalColumns(), $foreignKey->getColumns(), $foreignKey->getForeignTableName(), $foreignKey->getForeignColumns());

            if (count($foreignKey->getColumns()) == 1 && count($foreignKey->getForeignColumns()) == 1) {

                $mappingForeignKeys[$foreignKey->getColumns()[0]] = $foreignKey->getForeignTableName();
            }
        }

        $mapping['foreignKeys'] = $mappingForeignKeys;
        $mapping['columns'] = [];

        foreach ($table->getColumns() as $column) {

            $mapping['columns'][$column->getName()] = [
                'name' => $column->getName(),
                //'doctrine_type' => $column->getType(),
                'type' => $column->getType()->getName(),
                'autoincrement' => $column->getAutoincrement(),
                //'getTypeRegistry' => $column->getType()->getTypeRegistry(),
                'length' => $column->getLength(),
                'notnull' => $column->getNotnull(),
                //'array' => $column->toArray(),
            ];
        }
        /*$tables = $sm->listTables();
        foreach ($tables as $table) {
            //echo $table->getName() . " columns:\n\n";
            $mapping[$table->getName()] = [];
            foreach ($table->getColumns() as $column) {
                //echo ' - ' . $column->getName() . "\n";
                $mapping[$table->getName()][$column->getName()] = [
                    'name' => $column->getName(),
                    'doctrine_type' => $column->getType(),
                    'type' => $column->getType()->getName(),
                    'autoincrement' => $column->getAutoincrement(),
                    'getTypeRegistry' => $column->getType()->getTypeRegistry(),
                    'length' => $column->getLength(),
                    'notnull' => $column->getNotnull(),
                ];
            }
            //dd($mapping);
        }*/

        // bigint               Doctrine\DBAL\Types\BigIntType
        // integer              Doctrine\DBAL\Types\IntegerType
        // smallint             Doctrine\DBAL\Types\SmallIntType
        // array
        // binary
        // blob
        // boolean              Doctrine\DBAL\Types\BooleanType
        // date                 Doctrine\DBAL\Types\DateType
        // datetime             Doctrine\DBAL\Types\DateTimeType
        // decimal              Doctrine\DBAL\Types\DecimalType
        // float                Doctrine\DBAL\Types\FloatType
        // json                 Doctrine\DBAL\Types\JsonType
        // string               Doctrine\DBAL\Types\StringType
        // text                 Doctrine\DBAL\Types\TextType
        // time                 Doctrine\DBAL\Types\TimeType


        //hasTable
        //$details = $sm->listTableDetails($table);
        //$sequences = $sm->listSequences('dbname');

        return $mapping;
    }
}