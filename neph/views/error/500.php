<?php 
use Neph\URL;
?><!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Error 500</title>
	<meta name="viewport" content="width=device-width">

	<link href="<?php echo URL::theme('css/bootstrap.css') ?>" rel="stylesheet" />
    <link href="<?php echo URL::theme('css/bootstrap-responsive.css') ?>" rel="stylesheet" />

</head>
<body>
	<div class="container">
		<h1>500</h1>

		<hr>
		<h3><?php echo isset($data['exception']) ? $data['exception']->getMessage() : $message ?></h3>

		<pre><b>Error:</b>
<?php echo isset($data['error']) ? $data['error']['message'].' ('.$data['error']['type'].")\n    at ".$data['error']['file'].':'.$data['error']['line'] : '' ?></pre>
		<pre><b>Exception: </b>
<?php echo isset($data['exception']) ? $data['exception']->getTraceAsString() : 'No stack trace' ?></pre>
	</div>
</body>
</html>