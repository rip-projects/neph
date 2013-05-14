<?php

namespace Xinix\Neph\Modules\Migration;

use \Neph\Core\Controller;
use \Neph\Core\DB;
use \Neph\Core\DB\Schema;
use \Neph\Core\Neph;
use \Neph\Core\View;

class Migration_Controller extends Controller {
    protected $migration_dir = '';

    function __construct() {
        parent::__construct();

        $this->migration_dir = Neph::path('site').Neph::site().'/migrations/';

        if (!is_readable($this->migration_dir)) {
            mkdir($this->migration_dir, 0644, true);
        }
    }

    function status() {
        if (!DB::check('migration')) {
            return false;
        } else {
            $row = DB::table('migration')->where('site', '=', Neph::site())->order_by('name', 'desc')->take(1)->get();
            return (empty($row)) ? true : $row[0];
        }
    }

    function get_index() {

    }

    function cli_rollback() {
        if ($status = $this->status()) {
            if ($status !== true) {

                $migrations = DB::table('migration')->where('batch', '=', $status->batch)->order_by('name', 'desc')->get();
                foreach ($migrations as $key => $migration) {
                    require_once $this->migration_dir.$migration->name.'.php';

                    try {
                        $class = 'migration_'.$migration->name;

                        $obj = new $class;
                        $obj->down();
                        echo "Downgraded ".$migration->name.".\n";
                    } catch(\Exception $e) {
                        echo "Something wrong happens on rollback.\n";
                        \Console::error($e->getMessage(), $e->getTraceAsString());
                        echo "\n";
                    }
                }

                DB::table('migration')->where('batch', '=', $status->batch)->delete();
            }
        }
    }

    function cli_migrate($act = '') {
        if ($status = $this->status()) {
            $current_batch = get($status, 'batch', 0);
            $current = get($status, 'name', '');

            $files = array();

            $dh = opendir($this->migration_dir);
            while (($file = readdir($dh)) !== false) {
                $pinfo = pathinfo($this->migration_dir . $file);
                if (filetype($this->migration_dir . $file) == 'file' && $pinfo['extension'] == 'php') {
                    $files[] = $pinfo['filename'];
                }
            }
            closedir($dh);

            natsort($files);

            $success = true;
            $migrations = array();
            foreach($files as $file) {
                if ($current && strcmp($current, $file) <= 0) {
                    continue;
                }

                try {
                    require_once $this->migration_dir.$file.'.php';
                    $class = 'migration_'.$file;

                    $obj = new $class;
                    $migrations[$file] = $obj;
                    $obj->up();
                    echo "Upgraded to $file.\n";
                } catch(\Exception $e) {
                    array_reverse($migrations);

                    echo "Something wrong happens on migrate.\n";
                    \Console::error($e->getMessage(), $e->getTraceAsString());
                    echo "\n";

                    foreach ($migrations as $name => $obj) {
                        try {
                            $obj->down();
                            echo "Downgraded $name.\n";
                        } catch(\Exception $e) {
                            echo "Something wrong happens on rollback.\n";
                            \Console::error($e->getMessage(), $e->getTraceAsString());
                            echo "\n";
                        }
                    }
                    $success = false;
                    break;
                }
            }

            if ($success && $act != 'test') {
                if (empty($migrations)) {
                    echo 'No upgrades available.'.PHP_EOL;
                } else {
                    foreach ($migrations as $key => $value) {
                        $row = array(
                            'site' => Neph::site(),
                            'name' => $key,
                            'batch' => $current_batch + 1,
                        );
                        DB::table('migration')->insert($row);
                    }
                }
            } elseif ($act == 'test') {
                array_reverse($migrations);
                foreach ($migrations as $obj) {
                    try {
                        $obj->down();
                    } catch(\Exception $e) {
                        \Console::error($e->getMessage(), $e->getTraceAsString());
                    }
                }
            }

        } else {
            echo "Migration is not installed yet.\n";
            echo "Run 'SITE=[yoursite] php index.php migration install' to install.\n";
        }

    }

    function cli_install() {
        if ($status = $this->status()) {
            echo "Migration already installed.".PHP_EOL;
            return;
        }

        Schema::create('migration', function($table) {
            $table->string('site', 255);
            $table->string('name', 255);
            $table->integer('batch');

            $table->engine = 'InnoDB';
        });

        echo "Migration installed.".PHP_EOL;
    }

    function cli_status() {
        if ($status = $this->status()) {
            echo "Migration is up. Last Update: ".get($status, 'name').PHP_EOL;
        } else {
            echo "Migration is not installed yet.\n";
            echo "Run 'SITE=[yoursite] php index.php migration install' to install.\n";
        }
    }

    function cli_create($name, $time = '') {
        $name = strtolower($name);

        if (empty($time)) {
            $time = date('YmdHis');
        }

        $t = strtotime(substr($time, 0, 4).'-'.substr($time, 4, 2).'-'.substr($time, 6, 2).' '.substr($time, 8, 2).':'.substr($time, 10, 2).':'.substr($time, 12, 2));
        $time = date('YmdHis', $t);

        $filename = $time.'_'.$name.'.php';

        $f = fopen($this->migration_dir.$filename, 'w');
        $content = View::instance('file://'.__DIR__.'/views/_content.php')->render(array(
            'name' => $time.'_'.$name,
            'timestamp' => date('Y-m-d H:i:s', $t),
        ));
        fputs($f, $content);
        fclose($f);

        echo $filename." created.\n";
    }
}