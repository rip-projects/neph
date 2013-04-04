<?php
use \Neph\Core\String;
?>

<form method="POST">
    <div>
        <?php foreach($self->columns as $column): ?>
        <?php if (!in_array($column, $self->excluded_columns)): ?>
        <div class="row-fluid">
            <label class="span2"><?php echo String::humanize($column) ?></label>
            <?php echo $self->input($column, array('class' => 'span10')) ?>
        </div>
        <?php endif ?>
        <?php endforeach ?>
    </div>
    <div>
        <input type="submit" class="btn" value="<?php echo l('Save') ?>" />
    </div>
</form>