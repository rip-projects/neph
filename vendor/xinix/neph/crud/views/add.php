<?php
use \Neph\Core\URL;
use \Neph\Core\String;
?>

<div class="row-fluid">
    <?php echo $crud->breadcrumb(array(
        'User' => '/user',
        'Add' => '/user/add',
    )) ?>
</div>

<?php echo $crud->form() ?>
