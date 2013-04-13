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
            <?php echo $self->input($column, (empty($entry[$column])) ? '' : $entry[$column], array('class' => 'span10')) ?>
        </div>
        <?php endif ?>
        <?php endforeach ?>
    </div>
    <div>
        <input type="submit" class="btn btn-primary" value="<?php echo l('Save') ?>" />
        <a href="<?php echo URL::site('/'.Request::instance()->uri->segments[1].'/entries') ?>" class="btn">Back</a>
    </div>
</form>