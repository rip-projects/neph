<?php
use \Neph\Core\String;
?>
<!-- <form> -->
	<table class="table table-bordered table-striped grid" id="<?php echo $self->id ?>">
		<thead>
			<tr>
				<?php if ($self->show_checkbox): ?>
					<th style="width: 1px"><input type="checkbox" /></th>
				<?php endif ?>
				<?php foreach($self->columns as $column): ?>
					<th><?php echo String::humanize($column) ?></th>
				<?php endforeach ?>
				<?php if (!empty($self->actions)): ?>
				<th class="grid-row-actions">&nbsp;</th>
				<?php endif ?>
			</tr>

		</thead>
		<tbody>
			<?php if (!empty($entries)): ?>
				<?php foreach($entries as $entry): ?>
					<?php echo $self->row($entry) ?>
				<?php endforeach ?>
			<?php else: ?>
				<tr>
					<td colspan="1000" style="text-align: center">No record</td>
				</tr>
			<?php endif ?>
		</tbody>
	</table>
<!-- </form>