<?php

use \Neph\Core\String;

echo "<?php\n";

?>

/**
 * <?php echo str_replace(' ', '_', String::humanize('migration_'.$name))."\n" ?>
 * Created at: <?php echo $timestamp."\n" ?>
 */
class <?php echo str_replace(' ', '_', String::humanize('migration_'.$name)) ?> {

    /**
     * When upgrade.
     *
     * @return void
     */
    public function up() {
        // TODO implement codes here to upgrade
    }

    /**
     * When downgrade.
     *
     * @return void
     */
    public function down() {
        // TODO implement codes here to downgrade
    }

}