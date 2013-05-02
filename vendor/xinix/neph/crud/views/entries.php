<?php
use \Neph\Core\String;
use \Neph\Core\URL;
use \Neph\Core\Helpers\Page;
use \Neph\Core\Console;
use \Neph\Core\Request;
use \Xinix\Neph\Grid\Grid;
?>

<?php echo Page::breadcrumb(array(
    String::humanize(Request::instance()->uri->segments[1]) => '/'.Request::instance()->uri->segments[1],
)) ?>

<div class="row-fluid grid-action">
    <div class="span6 pull-right">
        <div class="pull-right">
            <a href="<?php echo URL::site('/'.$_response->uri->segments[1].'/add') ?>" class="btn"><?php echo l('Add') ?></a>
        </div>
    </div>
    <div class="span6">
        <a href="<?php echo URL::site('/'.$_response->uri->segments[1].'/delete') ?>" class="btn" data-action="delete" data-grid="#<?php echo $grid->id ?>"><?php echo l('Delete') ?></a>
    </div>
</div>

<?php echo $grid->show(isset($publish['entries']) ? $publish['entries'] : '') ?>
