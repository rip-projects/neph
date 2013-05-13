<?php
use \Neph\Core\URL;
use \Neph\Core\String;
?>
<tr class="grid-row" data-level="<?php echo $level ?>">
    <?php if ($self->show_checkbox): ?>
        <td class="grid-row-checkbox"><input type="checkbox" name="row[<?php echo get($entry, 'id') ?>]" value="<?php echo get($entry, 'id') ?>" /></td>
    <?php endif ?>
    <?php $i = 0; ?>
    <?php foreach($self->columns as $column): ?>
        <td>
            <?php $meta = get($self, 'meta.'.$column) ?>
            <div class="<?php echo (get($meta, 'alignment') || get($meta, 'type') == 'integer' || get($meta, 'type') == 'decimal') ? 'pull-right' : '' ?>">
                <?php if ($self->show_tree && $i++ == 0): ?>
                    <span class="level-<?php echo $level ?> <?php echo (empty($children)) ? 'level-leaf' : '' ?>"></span>
                <?php endif ?>
                <?php echo $self->format(get($entry, $column), $column, $entry) ?>
            </div>
        </td>
    <?php endforeach ?>
    <?php if (!empty($self->actions)): ?>
    <td class="grid-row-actions">
        <?php foreach($self->actions as $key => $action): ?>
        <a class="grid-row-action icon icon-<?php echo $key ?>" href="<?php echo URL::site($action.'/'.get($entry, 'id')) ?>" title="<?php echo l(String::humanize($key)) ?>"><?php echo l(String::humanize($key)) ?></a>
        <?php endforeach ?>
    </td>
    <?php endif ?>
</tr>

<?php if ($self->show_tree): ?>
<?php foreach(get($entry, 'children', array()) as $child): ?>
    <?php echo $self->row($child, $level+1) ?>
<?php endforeach ?>
<?php endif ?>