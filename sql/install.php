<?php

$sql = array (
    // wx_ranking_product
    // `id_ranking_product` clé primaire
    // `id_category` catégorie 
    // `id_product` l'id du produit
    // `id_product_attribute` déclinaison de produit
    // `initial_ranking` c'est le ranking de base, il va se baser sur les stocks
    // `score` Le score du produit
    // `position` La position du produit
    // `updated` La dernière modification
    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . 'product` (
        `id_ranking_product` int(11) NOT NULL AUTO_INCREMENT,
        `id_category` int(11), 
        `id_product` int(11),
        `id_product_attribute` int(11),
        `initial_ranking` int(11),
        `score` int(11),
        `position` int(11),
        `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_upd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (`id_ranking_product`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;',    
);
    
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
