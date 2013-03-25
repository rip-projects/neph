<?php
use \Neph\String;
?>
<h1><?php echo String::humanize($_response->uri->segments[1]) ?></h1>

<table class="table table-striped table-bordered">
	<thead>
		<tr>
			<th>ID</th>
			<th>Name</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($publish['entries'] as $entry): ?>
		<tr>
			<td><?php echo $entry->id ?></td>
			<td><?php echo $entry->name ?></td>
		</tr>
		<?php endforeach ?>
	</tbody>
</table>