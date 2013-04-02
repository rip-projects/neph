<?php
use \Neph\Core\String;
use \Neph\Core\URL;
use \Neph\Core\Console;
use \Xinix\Neph\Grid\Grid;
?>

<div class="row-fluid">
    <div class="span6">
        <ul class="breadcrumb">
            <li><a href="<?php echo URL::site('/') ?>">Home</a> <span class="divider">/</span></li>
            <li class="active"><?php echo String::humanize($_response->uri->segments[1]) ?></li>
        </ul>
    </div>
    <div class="span6">
        <div class="pull-right">
            <a href="<?php echo URL::site('/'.$_response->uri->segments[1].'/add') ?>" class="btn">Add</a>
        </div>
    </div>
</div>

<?php echo $crud->grid(isset($publish['entries']) ? $publish['entries'] : '') ?>
