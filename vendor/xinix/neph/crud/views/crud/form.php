<?php
use \Neph\Core\String;
?>

<form method="POST">
    <div>
        <?php foreach($self->columns as $column): ?>
        <div class="row-fluid">
            <label class="span2"><?php echo String::humanize($column) ?></label>
            <input type="text" class="span10" name="<?php echo $column ?>" />
        </div>
        <?php endforeach ?>
    </div>
    <div>
        <input type="submit" class="btn" />
    </div>
</form>