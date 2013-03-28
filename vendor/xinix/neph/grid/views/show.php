
<table class="table table-bordered">
	<thead>
		<tr>
			<?php foreach($grid['config']['columns'] as $column): ?>
			<th><?php echo $column ?></th>
			<?php endforeach ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach($entries as $entry): ?>
		<tr>
			<?php foreach($grid['config']['columns'] as $column): ?>
			<td><?php echo $entry->$column ?></td>
			<?php endforeach ?>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>
