<?php
use \Neph\Core\URL;
use \Neph\Core\String;
use \Neph\Core\Request;
use \Neph\Core\Helpers\Page;
?>

<div class="row-fluid">
    <?php echo Page::breadcrumb(array(
        String::humanize(Request::instance()->uri->segments[1]) => '/'.Request::instance()->uri->segments[1],
        l('Edit') => Request::instance()->uri->pathinfo,
    )) ?>
</div>

<?php echo $form->show($data) ?>
