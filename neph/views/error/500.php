<!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Error 500</title>
	<meta name="viewport" content="width=device-width">
</head>
<body>
	<h1>500</h1>

	<hr>

	<h3><?php echo $exception->getMessage() ?></h3>

	<pre>
<?php echo $exception->getTraceAsString() ?>
	</pre>

</body>
</html>