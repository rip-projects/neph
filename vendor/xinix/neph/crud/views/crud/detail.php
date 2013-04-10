<?php
use \Neph\Core\String;
use \Neph\Core\Request;
use \Neph\Core\URL;
?>

<form method="POST">
    <div>
        <?php foreach($self->columns as $column): ?>
        <?php if (!in_array($column, $self->excluded_columns)): ?>
        <div class="row-fluid">
            <label class="span2"><?php echo String::humanize($column) ?></label>
            <span class="span10"><?php echo $data->{$column} ?></span>
        </div>
        <?php endif ?>
        <?php endforeach ?>
    </div>
    <div>
        <a href="<?php echo URL::site('/'.Request::instance()->uri->segments[1].'/entries') ?>" class="btn">Back</a>
    </div>
</form>