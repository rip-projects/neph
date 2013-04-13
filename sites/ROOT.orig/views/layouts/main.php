<?php
use Neph\Core\URL;
use Neph\Core\Console;
use Neph\Core\Response;
use Xinix\Neph\Message\Message;
?><!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>NEPH</title>

    <link href="<?php echo URL::theme('css/bootstrap.css') ?>" rel="stylesheet" />
	<link href="<?php echo URL::theme('css/global.css') ?>" rel="stylesheet" />
    <link href="<?php echo URL::theme('css/bootstrap-responsive.css') ?>" rel="stylesheet" />
    <link href="<?php echo URL::theme('css/global-responsive.css') ?>" rel="stylesheet" />

    <script type="text/javascript" src="<?php echo URL::theme('js/jquery-1.9.1.min.js') ?>"></script>
	<script type="text/javascript" src="<?php echo URL::theme('js/bootstrap.min.js') ?>"></script>
	<script type="text/javascript" src="<?php echo URL::theme('js/underscore-min.js') ?>"></script>

    <script type="text/javascript" src="<?php echo URL::theme('js/global.js') ?>"></script>
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
				<a class="brand" href="<?php echo URL::site() ?>">/</a>
				<div class="nav-collapse collapse">
					<ul class="nav">
						<li class="dropdown">
                            <a href="#" data-toggle="dropdown">Admin</a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo URL::site('/user') ?>">User</a></li>
                            </ul>
                        </li>
                        <li><a href="<?php echo URL::site('/module') ?>">Module</a></li>
                        <li><a href="<?php echo URL::site('/issue') ?>">Issue</a></li>
					</ul>
				</div>
			</div>
		</div>
    </div>

    <div class="container" id="body">
        <?php echo Message::show() ?>

		<?php echo $content ?>
    </div>

</body>
</html>