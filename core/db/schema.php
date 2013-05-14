<?php

namespace Neph\Core\DB;

use \Neph\Core\DB;

class Schema {
    public static function create($table, $callback) {
        $table = new Schema\Table($table);

        // To indicate that the table is new and needs to be created, we'll run
        // the "create" command on the table instance. This tells schema it is
        // not simply a column modification operation.
        $table->create();

        call_user_func($callback, $table);

        return static::execute($table);
    }

    public static function execute($table) {
        // The implications method is responsible for finding any fluently
        // defined indexes on the schema table and adding the explicit
        // commands that are needed for the schema instance.
        static::implications($table);

        foreach ($table->commands as $command) {
            $connection = DB::connection($table->connection);

            $grammar = static::grammar($connection);

            // Each grammar has a function that corresponds to the command type and
            // is for building that command's SQL. This lets the SQL syntax builds
            // stay granular across various database systems.
            if (method_exists($grammar, $method = get($command, 'type')))
            {
                $statements = $grammar->$method($table, $command);

                // Once we have the statements, we will cast them to an array even
                // though not all of the commands return an array just in case it
                // needs multiple queries to complete.
                foreach ((array) $statements as $statement)
                {
                    $connection->query($statement);
                }
            } else {
                \Console::error('Method "'.$method.'" not found on grammar');
            }
        }
    }

    public static function grammar(Connection $connection) {
        return $connection->grammar();
    }

    /**
     * Add any implicit commands to the schema table operation.
     *
     * @param   Schema\Table  $table
     * @return  void
     */
    protected static function implications($table)
    {
        // If the developer has specified columns for the table and the table is
        // not being created, we'll assume they simply want to add the columns
        // to the table and generate the add command.
        if (count($table->columns) > 0 and ! $table->creating())
        {
            $command = array('type' => 'add');

            array_unshift($table->commands, $command);
        }

        // For some extra syntax sugar, we'll check for any implicit indexes
        // on the table since the developer may specify the index type on
        // the fluent column declaration for convenience.
        foreach ($table->columns as $column)
        {
            foreach (array('primary', 'unique', 'fulltext', 'index') as $key)
            {
                if (isset($column->$key))
                {
                    if ($column->$key === true)
                    {
                        $table->$key($column->name);
                    }
                    else
                    {
                        $table->$key($column->name, $column->$key);
                    }
                }
            }
        }
    }
}