<!-- <form> -->
	<table class="table table-bordered table-striped" id="<?php echo $self->id ?>">
		<thead>
			<tr>
				<?php if ($self->show_checkbox): ?>
				<th style="width: 1px"><input type="checkbox" /></th>
				<?php endif ?>
				<?php foreach($self->columns as $column): ?>
				<?php if (!in_array($column, $self->excluded_columns)): ?>
				<th><?php echo \Neph\Core\String::humanize($column) ?></th>
				<?php endif ?>
				<?php endforeach ?>
			</tr>

			<!--
			<tr class="filter">
				<?php foreach($self->columns as $column): ?>
				<?php if (!in_array($column, $self->excluded_columns)): ?>
				<th><input type="text" name="<?php echo $column ?>" style="width: 100%" /></th>
				<?php endif ?>
				<?php endforeach ?>
			</tr>
			-->

		</thead>
		<tbody>
			<?php if (!empty($entries)): ?>
			<?php foreach($entries as $entry): ?>
			<tr>
				<?php if ($self->show_checkbox): ?>
                <td><input type="checkbox" name="row[<?php echo $entry->id ?>]" value="<?php echo $entry->id ?>" /></td>
                <?php endif ?>
				<?php foreach($self->columns as $column): ?>
				<?php if (!in_array($column, $self->excluded_columns)): ?>
				<td><?php echo (isset($entry->$column)) ? $entry->$column : '' ?></td>
				<?php endif ?>
				<?php endforeach ?>
			</tr>
			<?php endforeach ?>
			<?php else: ?>
			<tr>
				<td colspan="<?php echo count($self->columns) ?>" style="text-align: center">No record</td>
			</tr>
			<?php endif ?>
		</tbody>
	</table>
<!-- </form>