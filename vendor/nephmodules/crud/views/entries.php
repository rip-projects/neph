<?php
use \Neph\String;
use \Neph\Console;
?>
<h1><?php echo String::humanize($_response->uri->segments[1]) ?></h1>

<table class="table table-striped table-bordered" data-type="grid"></table>

<script type="text/javascript">
	$(function() {
		console.log(CRUD);
		var html = _.template($('#template-grid').html() || '')(CRUD);
		// console.log(html);
		$('[data-type=grid]').append(html);
	});
</script>

<style type="text/css">
	.template { display: none; }
</style>
<script type="text/template" id="template-grid">
	<thead class="btn-inverse">
		<tr>
			<% for(var i in columns) { %>
			<th><%= columns[i].field %></th>
			<% } %>
		</tr>
	</thead>
	<tbody>
		<% for(var j in publish.entries) { %>
		<tr>
			<% for(var i in columns) { %>
			<td><%= publish.entries[j][columns[i].field] %></td>
			<% } %>
		</tr>
		<% } %>
	</tbody>
</script>
