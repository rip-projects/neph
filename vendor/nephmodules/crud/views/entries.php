<?php
use \Neph\Core\String;
use \Neph\Core\Console;
use \Xinix\Neph\Grid\Grid;
?>
<h1><?php echo String::humanize($_response->uri->segments[1]) ?></h1>

<?php echo Grid::instance($grid_config)->show($publish['entries']) ?>
