<?php
use Neph\Core\URL;
use Neph\Core\Console;
use Neph\Core\Response;
use Xinix\Neph\Message\Message;
?><!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title></title>

	<link href="<?php echo URL::theme('css/bootstrap.css') ?>" rel="stylesheet" />
    <link href="<?php echo URL::theme('css/bootstrap-responsive.css') ?>" rel="stylesheet" />

    <style>
        body { box-sizing: border-box;}
        body #body { padding: 10px 0; }

        select, textarea, input[type="text"], input[type="password"], input[type="datetime"], input[type="datetime-local"], input[type="date"], input[type="month"], input[type="time"], input[type="week"], input[type="number"], input[type="email"], input[type="url"], input[type="search"], input[type="tel"], input[type="color"], .uneditable-input {
            box-sizing: border-box;
            height: inherit;
        }

        .grid-action {
            text-indent: 10000px;
            overflow: hidden;
            border: 1px solid #ccc;
            /*background-position: -360px -144px;*/
        }

        .error-list {
            list-style: none;
            text-align: center;
        }

        .error-list li {
            display: inline;
        }

        .error-list li:after {
            content: ". ";
        }

        @media (min-width: 979px) {
    	   body { padding-top: 41px; }
        }
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
				<a class="brand" href="<?php echo URL::site() ?>">/</a>
				<div class="nav-collapse collapse">
					<ul class="nav">
						<li><a href="<?php echo URL::site('/user') ?>">User</a></li>
                        <li><a href="<?php echo URL::site('/module') ?>">Module</a></li>
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