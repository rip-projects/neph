<?php 
use Neph\Core\URL;
use Neph\Core\Console;
use Neph\Core\Response;
?><!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title></title>

	<link href="<?php echo URL::theme('css/bootstrap.css') ?>" rel="stylesheet" />
    <link href="<?php echo URL::theme('css/bootstrap-responsive.css') ?>" rel="stylesheet" />

    <style>
    	body { padding-top: 41px; box-sizing: border-box;}
    	body #body { padding: 10px 0; }
    </style>

    <script type="text/javascript" src="<?php echo URL::theme('js/jquery-1.9.1.min.js') ?>"></script>
	<script type="text/javascript" src="<?php echo URL::theme('js/bootstrap.min.js') ?>"></script>
	<script type="text/javascript" src="<?php echo URL::theme('js/underscore-min.js') ?>"></script>
</head>
<body>
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="brand" href="<?php echo URL::base() ?>">NEPH</a>
				<div class="nav-collapse collapse">
					<ul class="nav">
						<li class=""><a href="<?php echo URL::site('/module') ?>">Module</a></li>
					</ul>
				</div>
			</div>
		</div>
    </div>

    <div class="container" id="body">
		<?php echo $content ?>
    </div>

</body>
</html>