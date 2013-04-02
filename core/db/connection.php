<?php namespace Neph\Core\DB;

use \PDO;
use \Neph\Core\Config;

abstract class Connection {

    var $config;
    protected $connection;

    protected $options = array(
            PDO::ATTR_CASE => PDO::CASE_LOWER,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
    );

    function __construct($config) {
        $this->config = $config;
    }

    abstract public function connect();
    abstract public function check($table);
    abstract public function columns($table);

    protected function options($config) {
        $options = (isset($config['options'])) ? $config['options'] : array();
        return $options + $this->options;
    }

    public function table($table) {
        return new Query($this, $this->grammar(), $table);
    }

    protected function grammar() {
        if (isset($this->grammar)) return $this->grammar;

        $grammar_class = Config::get('db/drivers/'.$this->config['driver'], '\\Neph\\Core\\DB\\'.$this->config['driver']).'\\Grammar';
        return $this->grammar = new $grammar_class($this);
    }

    public function query($sql, $bindings = array()) {
        $sql = trim($sql);

        list($statement, $result) = $this->execute($sql, $bindings);

        if (stripos($sql, 'select') === 0 || stripos($sql, 'show') === 0) {
            return $this->fetch($statement, Config::get('db/fetch'));
        } elseif (stripos($sql, 'update') === 0 or stripos($sql, 'delete') === 0) {
            return $statement->rowCount();
        } elseif (stripos($sql, 'insert') === 0 and stripos($sql, 'returning') !== false) {
            return $this->fetch($statement, Config::get('db/fetch'));
        } else {
            return $result;
        }
    }

    protected function execute($sql, $bindings = array())
    {
        $bindings = (array) $bindings;

        // Since expressions are injected into the query as strings, we need to
        // remove them from the array of bindings. After we have removed them,
        // we'll reset the array so there are not gaps within the keys.
        $bindings = array_filter($bindings, function($binding)
        {
            return ! $binding instanceof Expression;
        });

        $bindings = array_values($bindings);

        $sql = $this->grammar()->shortcut($sql, $bindings);

        // Next we need to translate all DateTime bindings to their date-time
        // strings that are compatible with the database. Each grammar may
        // define it's own date-time format according to its needs.
        $datetime = $this->grammar()->datetime;

        for ($i = 0; $i < count($bindings); $i++)
        {
            if ($bindings[$i] instanceof \DateTime)
            {
                $bindings[$i] = $bindings[$i]->format($datetime);
            }
        }

        // Each database operation is wrapped in a try / catch so we can wrap
        // any database exceptions in our custom exception class, which will
        // set the message to include the SQL and query bindings.
        try {
            $statement = $this->connection->prepare($sql);

            $start = microtime(true);

            $result = $statement->execute($bindings);
        }
        // If an exception occurs, we'll pass it into our custom exception
        // and set the message to include the SQL and query bindings so
        // debugging is much easier on the developer.
        catch (\Exception $exception)
        {
            $exception = new Exception($sql, $bindings, $exception);

            throw $exception;
        }

        // Once we have executed the query, we log the SQL, bindings, and
        // execution time in a static array that is accessed by all of
        // the connections actively being used by the application.
        if (Config::get('db/profile'))
        {
            $this->log($sql, $bindings, $start);
        }

        return array($statement, $result);
    }

    protected function fetch($statement, $style) {
        // If the fetch style is "class", we'll hydrate an array of PHP
        // stdClass objects as generic containers for the query rows,
        // otherwise we'll just use the fetch style value.
        if ($style === PDO::FETCH_CLASS) {
            return $statement->fetchAll(PDO::FETCH_CLASS, 'stdClass');
        } else {
            return $statement->fetchAll($style);
        }
    }
}