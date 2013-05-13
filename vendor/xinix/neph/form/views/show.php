<?php
use \Neph\Core\String;
use \Neph\Core\Request;
use \Neph\Core\URL;
?>

<form method="POST">
    <div>
        <?php foreach($self->columns as $column): ?>
        <div class="row-fluid">
            <label class="span2"><?php echo String::humanize($column) ?></label>
            <?php if ($readonly): ?>
                <?php echo $self->text($column, get($entry, $column), array('class' => 'span10'), $readonly) ?>
            <?php else: ?>
                <?php echo $self->input($column, get($entry, $column), array('class' => 'span10'), $readonly) ?>
            <?php endif ?>
        </div>
        <?php endforeach ?>
    </div>
    <div>
        <?php if (!$readonly): ?>
        <input type="submit" class="btn btn-primary" value="<?php echo l('Save') ?>" />
        <?php endif ?>
        <a href="<?php echo URL::site('/'.Request::instance()->uri->segments[1].'/entries') ?>" class="btn">Back</a>
    </div>
</form>