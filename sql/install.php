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

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . 'groupe` (
        `id_groupe` int(11) NOT NULL AUTO_INCREMENT,
        `id_groupe_parent` int(11), 
        `id_type_groupe` int(11), 
        `level` int(11),
        `note` int(11),
        `active` int(11) NOT NULL DEFAULT 1,
        `position` int(11),
        `date_begin` timestamp NOT NULL,
        `date_end` timestamp NOT NULL,
        `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_upd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (`id_groupe`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;',    

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . 'type_groupe` (
        `id_type_groupe` int(11) NOT NULL AUTO_INCREMENT,
        `code` text NOT NULL,
        `position` int(11),
        PRIMARY KEY  (`id_type_groupe`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;',  
    
    "DELETE FROM `" . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . "type_groupe`;
    INSERT INTO `" . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . "type_groupe` (`id_type_groupe`, `code`, `position`) VALUES
    (1, '12_last_months', 0),
    (2, 'promo', 1),
    (3, 'id_product', 2),
    (4, 'popularity', 3),
    (5, 'custom_filter_product_criteria', 4);",

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . 'operator_groupe` (
        `id_operator_groupe` int(11) NOT NULL AUTO_INCREMENT,
        `id_groupe_left` int(11), 
        `id_groupe_right` int(11),
        `operator` varchar(255) NOT NULL,
        PRIMARY KEY  (`id_operator_groupe`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;',    

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . 'operator_criteria` (
        `id_operator_criteria` int(11) NOT NULL AUTO_INCREMENT,
        `id_criteria_left` int(11), 
        `id_criteria_right` int(11),
        `operator` varchar(255) NOT NULL,
        PRIMARY KEY  (`id_operator_criteria`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;',  

    'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . 'criteria` (
        `id_criteria` int(11) NOT NULL AUTO_INCREMENT,
        `criteria_product` int(11), 
        `comparison_operator` int(11), 
        `values` text NOT NULL,
        `active` int(11) NOT NULL DEFAULT 1,
        `position` int(11),
        `date_add` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `date_upd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (`id_criteria`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;',    
);
    
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
