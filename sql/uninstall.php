<?php
/**
* IW
*/

/**
* In some cases you should not drop the tables.
* Maybe the merchant will just try to reset the module
* but does not want to loose all of the data associated to the module.
*/

$sql = array(
    // 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . '_message`',
);

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
