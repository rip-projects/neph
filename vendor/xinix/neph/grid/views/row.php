<?php
use \Neph\Core\URL;
use \Neph\Core\String;

$orig_entry = $entry;
$children = (is_a($entry, '\\Neph\\Core\\DB\\ORM\\Model')) ? $entry->children() : (array) ((empty($entry['children'])) ? array() : $entry['children']);
$entry = (is_a($entry, '\\Neph\\Core\\DB\\ORM\\Model')) ? $entry->to_array() : (array) $entry;
?>
<tr class="grid-row" data-level="<?php echo $level ?>">
    <?php if ($self->show_checkbox): ?>
        <td class="grid-row-checkbox"><input type="checkbox" name="row[<?php echo $entry['id'] ?>]" value="<?php echo $entry['id'] ?>" /></td>
    <?php endif ?>
    <?php $i = 0; ?>
    <?php foreach($self->columns as $column): ?>
        <td>
            <?php $meta = $self->meta[$column] ?: $self->meta[$column] ?>
            <div class="<?php echo (isset($meta) && ($meta['alignment'] == 'right' || $meta['type'] == 'integer' || $self->meta[$column]['type'] == 'decimal')) ? 'pull-right' : '' ?>">
                <?php if ($self->show_tree && $i++ == 0): ?>
                    <span class="level-<?php echo $level ?> <?php echo (empty($children)) ? 'level-leaf' : '' ?>"></span>
                <?php endif ?>
                <?php echo $self->format($entry[$column], $column, $entry) ?>
            </div>
        </td>
    <?php endforeach ?>
    <?php if (!empty($self->actions)): ?>
    <td class="grid-row-actions">
        <?php foreach($self->actions as $key => $action): ?>
        <a class="grid-row-action icon icon-<?php echo $key ?>" href="<?php echo URL::site($action.'/'.$entry['id']) ?>" title="<?php echo l(String::humanize($key)) ?>"><?php echo l(String::humanize($key)) ?></a>
        <?php endforeach ?>
    </td>
    <?php endif ?>
</tr>

<?php foreach($children as $child): ?>
    <?php echo $self->row($child, $level+1) ?>
<?php endforeach ?>