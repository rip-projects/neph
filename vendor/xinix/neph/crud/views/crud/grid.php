<?php
use \Neph\Core\URL;
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
					<?php if (!in_array($column, $self->excluded_columns)): ?>
						<th><?php echo \Neph\Core\String::humanize($column) ?></th>
					<?php endif ?>
				<?php endforeach ?>
				<th>&nbsp;</th>
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
					<tr class="grid-row">
						<?php if ($self->show_checkbox): ?>
		                	<td class="grid-row-checkbox"><input type="checkbox" name="row[<?php echo $entry->id ?>]" value="<?php echo $entry->id ?>" /></td>
		                <?php endif ?>
						<?php foreach($self->columns as $column): ?>
							<?php if (!in_array($column, $self->excluded_columns)): ?>
								<td><?php echo (isset($entry->$column)) ? $entry->$column : '' ?></td>
							<?php endif ?>
						<?php endforeach ?>
						<td>
							<?php foreach($self->actions as $key => $action): ?>
							<a class="grid-row-action icon icon-<?php echo $key ?>" href="<?php echo URL::site($action.'/'.$entry->id) ?>" title="<?php echo l(String::humanize($key)) ?>"><?php echo l(String::humanize($key)) ?></a>
							<?php endforeach ?>
						</td>
					</tr>
				<?php endforeach ?>
			<?php else: ?>
				<tr>
					<td colspan="1000" style="text-align: center">No record</td>
				</tr>
			<?php endif ?>
		</tbody>
	</table>
<!-- </form>