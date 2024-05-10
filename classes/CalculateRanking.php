<?php 

use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductType;

class CalculateRanking
{
    private $default_score_ranking        = 0;
    private $default_initial_ranking      = 0;
    private $default_position_ranking     = 0;
    private $default_category_ranking     = 0;
    private $default_id_product_attribute = 0;

    public const CODE_PRODUCT_STOCK = 5;
    public const CODE_PRODUCT_NO_STOCK_REAPPRO_STOCK_SUPPLIER = 4;
    public const CODE_PRODUCT_NO_STOCK_REAPPRO_UNDEFINED_STOCK_SUPPLIER = 3;
    public const CODE_PRODUCT_NO_STOCK_REAPPRO_NO_STOCK_SUPPLIER = 2;
    public const CODE_PRODUCT_NO_STOCK_NO_REAPPRO = 1;

    private $list_initial_ranking = [
        'stock' => [
            'yes' => self::CODE_PRODUCT_STOCK,
            'no' => [
                'reappro' => [
                    'yes' => [
                        'stock_fournisseur' => [
                            'yes' => self::CODE_PRODUCT_NO_STOCK_REAPPRO_STOCK_SUPPLIER,
                            'undefined' => self::CODE_PRODUCT_NO_STOCK_REAPPRO_UNDEFINED_STOCK_SUPPLIER,
                            'no' => self::CODE_PRODUCT_NO_STOCK_REAPPRO_NO_STOCK_SUPPLIER,
                        ]
                    ],
                    'no' => self::CODE_PRODUCT_NO_STOCK_NO_REAPPRO,
                ],
            ],
        ],
    ];

    
    public function __construct() {
        
        // var_dump($this->list_initial_ranking);
        $this->default_score_ranking        = Configuration::get('WX_RANKING_DEFAULT_SCORE_RANKING');
        $this->default_initial_ranking      = Configuration::get('WX_RANKING_DEFAULT_INITIAL_RANKING');
        $this->default_position_ranking     = Configuration::get('WX_RANKING_DEFAULT_POSITION_RANKING');
        $this->default_category_ranking     = Configuration::get('WX_RANKING_DEFAULT_CATEGORY_RANKING');
        $this->default_id_product_attribute = Configuration::get('WX_RANKING_DEFAULT_ID_PRODUCT_ATTRIBUTE');
    }

    public function rePopulateRankingProduct($all = false) {
        
        if ($all) {
            # code...
            // On vide la table ranking_product
            \DB::getInstance()->Execute("TRUNCATE TABLE `" . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . "product`" );
        }

        // Dernière date d'insertion 
        $last_insert_date_ranking = \Db::getInstance()->getValue(
            "SELECT date_add FROM `" ._DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . "product` ORDER BY date_add DESC",
            $use_cache = false
        );

        $where = "";
        if ($last_insert_date_ranking && !$all) {
            # code...
            $where = "WHERE id_product IN (SELECT id_product FROM `" . _DB_PREFIX_ . "product` WHERE date_add >= '$last_insert_date_ranking')";
        }

        // On récupère l'ancien livraison_id
        \DB::getInstance()->Execute('
        INSERT INTO `' . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . 'product` 
        (`id_category`, `id_product`, `id_product_attribute`, `initial_ranking`, `score`, `position`)
        SELECT id_category, id_product, ' 
        . $this->default_id_product_attribute . ', ' . 
        $this->default_initial_ranking . ', ' . 
        $this->default_score_ranking . ', position FROM `' . _DB_PREFIX_ . "category_product` $where;" );
    }

    public function getInitialRanking($stock = 0, $reappro = false, $stock_fournisseur = '') {

        $initial_ranking = $this->list_initial_ranking['stock'][(!empty($stock) && $stock > 0) ? 'yes' : 'no'];

        if (is_array($initial_ranking)) {
            # code...
            $initial_ranking = $initial_ranking['reappro'][!empty($reappro) ? 'yes' : 'no'];
            
            if (is_array($initial_ranking)) {
                # code...
                
                // Le stock fournisseur n'est pas vide et a une valeur > 0
                // On va dire qu'il vaut 0 donc on n'est sur la valeur yes stock fournisseur
                if (strlen(trim($stock_fournisseur)) && !empty($stock_fournisseur) && $stock_fournisseur > 0) {
                    # code...
                    $initial_ranking = $initial_ranking['stock_fournisseur']['yes'];
                }
                // Le stock fournisseur n'est pas vide et a une valeur comme 0 ou false
                // On va dire qu'il vaut 0 donc on n'est sur la valeur no stock fournisseur
                elseif (strlen(trim($stock_fournisseur)) && (empty($stock_fournisseur) || $stock_fournisseur <= 0)) {
                    # code...
                    $initial_ranking = $initial_ranking['stock_fournisseur']['no'];
                }
                // Le stock fournisseur est vide
                // On va dire qu'il vaut undefined
                elseif (!strlen(trim($stock_fournisseur))) {
                    # code...
                    $initial_ranking = $initial_ranking['stock_fournisseur']['undefined'];
                }
                else {
                    # code...
                    $initial_ranking = $initial_ranking['stock_fournisseur']['no'];
                }

                if (is_array($initial_ranking)) {
                    # code...
                    // On sait pas quoi faire
                }
                else {
                    return $initial_ranking;
                }
            }
            else{
                return $initial_ranking;
            }
        }
        else {
            # code...
            return $initial_ranking;
        }
    }

    public function calculateInitialRanking() {
        $results = Db::getInstance()->executeS("
        SELECT p.id_product, IF(pa.id_product_attribute > 0, pa.id_product_attribute, 0) as id_product_attribute FROM " . _DB_PREFIX_ . "product p
        LEFT JOIN " . _DB_PREFIX_ . "product_attribute pa ON pa.id_product = p.id_product ORDER BY p.id_product ASC",
            $array = true, $use_cache = false
        );

        $reappro_acf = [];
        $stock_fournisseur_acf = [];
        if (class_exists('AdvancedCustomFieldsModel') && class_exists('AdvancedCustomFieldsContentModel')) {
            # code...
            if (AdvancedCustomFieldsModel::checkIfTechnicalNameExists($technical_name = 'REAPRO')) {
                # code...
                $reappro_acf = AdvancedCustomFieldsModel::getCustomFieldFromTechnicalName($technical_name, \Context::getContext()->language->id);
            }
            if (AdvancedCustomFieldsModel::checkIfTechnicalNameExists($technical_name = 'stock_fournisseur')) {
                # code...
                $stock_fournisseur_acf = AdvancedCustomFieldsModel::getCustomFieldFromTechnicalName($technical_name, \Context::getContext()->language->id);
            }
        }

        // On met les valeurs par défaut pour le score et le liste des initial_ranking
        \Db::getInstance()->execute("
        UPDATE `" . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . "product` p
        SET initial_ranking = " . $this->default_initial_ranking . ", score = " . $this->default_score_ranking . "");

        // Le precédent id_product
        $previous_product_id = 0;
        $stock = 0;
        $stock_current_combination = 0;

        foreach ($results as $key => $result) {
            # code...

            $product = new Product($result['id_product']);
            $reappro = false;
            $stock_fournisseur = '';

            // var_dump(ProductType::TYPE_COMBINATIONS);
            // die();
            switch ($product->getDynamicProductType()) {
                case ProductType::TYPE_COMBINATIONS:
                    # code...
                    // Si le produit précédent est égal au produit courant, on retient le stock le plus grand
                    if ($result['id_product'] == $previous_product_id) {
                        # code...
                        $stock_current_combination = \StockAvailable::getQuantityAvailableByProduct($result['id_product'], $result['id_product_attribute']);
                        if ($stock_current_combination > $stock) {
                            # code...
                            $stock = $stock_current_combination;
                        }
                    }else{
                        $stock   = \StockAvailable::getQuantityAvailableByProduct($result['id_product'], $result['id_product_attribute']);
                        $stock_current_combination = $stock;
                    }
                    break;
                case ProductType::TYPE_VIRTUAL:
                    # code...
                    $stock   = \StockAvailable::getQuantityAvailableByProduct($result['id_product'], $result['id_product_attribute']);
                    break;
                case ProductType::TYPE_PACK:
                    # code...
                    $stock   = \StockAvailable::getQuantityAvailableByProduct($result['id_product'], $result['id_product_attribute']);
                    break;
                case ProductType::TYPE_STANDARD:
                    # code...
                    $stock   = \StockAvailable::getQuantityAvailableByProduct($result['id_product'], $result['id_product_attribute']);
                    break;
                
                default:
                    # code...
                    break;
            }

            if (!empty($reappro_acf)) {
                # code...
                if ($reappro_acf['id_custom_field']) {
                    # code...
                    $reappro_acfc_id = AdvancedCustomFieldsContentModel::getContentID(\Context::getContext()->shop->id, $reappro_acf['id_custom_field'], $result['id_product']);
                    if ($reappro_acfc_id) {
                        # code...
                        $reappro = new AdvancedCustomFieldsContentModel($reappro_acfc_id);
                        if ($reappro_acf['translatable']) {
                            # code...
                            $reappro = $reappro->lang_value;
                        }
                        else {
                            # code...
                            $reappro = $reappro->value;
                        }
                    }
                }
            }

            if (!empty($stock_fournisseur_acf)) {
                # code...
                if ($stock_fournisseur_acf['id_custom_field']) {
                    # code...
                    $stock_fournisseur_acfc_id = AdvancedCustomFieldsContentModel::getContentID(\Context::getContext()->shop->id, $stock_fournisseur_acf['id_custom_field'], $result['id_product']);
                    if ($stock_fournisseur_acfc_id) {
                        # code...
                        $stock_fournisseur = new AdvancedCustomFieldsContentModel($stock_fournisseur_acfc_id);
                        if ($stock_fournisseur_acf['translatable']) {
                            # code...
                            $stock_fournisseur = $stock_fournisseur->lang_value;
                        }
                        else {
                            # code...
                            $stock_fournisseur = $stock_fournisseur->value;
                        }
                    }
                }
            }

            
            // Ranking des combinations
            if (!empty($result['id_product_attribute'])) {
                # code...
                $initial_ranking_combination = $this->getInitialRanking($stock_current_combination, $reappro, $stock_fournisseur);
                \Db::getInstance()->execute("
                UPDATE `" . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . "product` p
                SET initial_ranking = $initial_ranking_combination
                WHERE 
                p.id_product = " . $result['id_product'] . " AND p.id_product_attribute = " . $result['id_product_attribute'] .  "
                ");
            }

            $initial_ranking = $this->getInitialRanking($stock, $reappro, $stock_fournisseur);
            \Db::getInstance()->execute("
            UPDATE `" . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . "product` p
            SET initial_ranking = $initial_ranking
            WHERE 
            p.id_product = " . $result['id_product'] . " AND p.id_product_attribute = " . $this->default_id_product_attribute);

            // Le précédent id_product n'est devient celui produit courant
            $previous_product_id = $result['id_product'];

            // echo "<pre>";
            // var_dump( "product_type: " . $product->getDynamicProductType() . "id_product: " . $result['id_product'] . " id_product_attribute: " . $result['id_product_attribute'] . " stock: " . $stock . " stock_current_combination: " . $stock_current_combination . " reappro: " . $reappro  . " stock_fournisseur: " . $stock_fournisseur ."");
            // echo "</pre>";
        }
        // SELECT p.id_product, IF(pa.id_product_attribute > 0, pa.id_product_attribute, 0) as id_product_attribute FROM ps_product p
        // LEFT JOIN ps_product_attribute pa ON pa.id_product = p.id_product
    }

    public function calculatePosition() {
        
        // Pour PS quand Configuration::get('PS_PRODUCTS_ORDER_WAY') == 0 =>
        // Les produit sont classe dans l'ordre ASC
        // Dans ce cas, le produit qui a la plus petite position sera le premier
        // et le produit qui a la plus grande sera le dernier
        // Pour PS quand Configuration::get('PS_PRODUCTS_ORDER_WAY') == 1 =>
        // Les produit sont classe dans l'ordre DESC
        // Dans ce cas, le produit qui a la plus grande position sera le premier
        // et le produit qui a la plus petite sera le dernier
        // Nous on va faire l'inverse
        // Quand PS ordonne dans l'ordre croissant, on va ordonner dans l'ordre decroissant
        // Quand PS ordonne dans l'ordre decroissant, on va ordonner dans l'ordre croissant
        $order_way = trim(Configuration::get('PS_PRODUCTS_ORDER_WAY')) == 0 ? 'DESC' : 'ASC';

        $results = Db::getInstance()->executeS("
        SELECT p.id_product, p.id_product_attribute FROM `" . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . "product` p
        GROUP BY p.id_product ORDER BY p.initial_ranking $order_way, p.score $order_way",
        $array = true, $use_cache = false
        );

        foreach ($results as $key => $result) {
            # code...
            $position = $key + 1;
            // On met à jour la position du produit dans la table de ranking
            \Db::getInstance()->execute("
            UPDATE `" . _DB_PREFIX_ . _DB_PREFIX_WEBTINIX_RANKING_ . "product` p
            SET position = $position
            WHERE 
            p.id_product = " . $result['id_product']);

            // On met à jour la position du produit dans la table category_product
            \Db::getInstance()->execute("
            UPDATE `" . _DB_PREFIX_ . "category_product` p
            SET position = $position
            WHERE 
            p.id_product = " . $result['id_product']);
        }
        
    }

    public function calculateRanting() {
        // On va d'abord repeupler la table ranking_product au cas où un nouveau produit se serait ajouter.
        // On va repeupler la table ranking_product
        // Au cas où un nouveau produit est ajouter
        $this->rePopulateRankingProduct();
        // On va calculer le ranking initail (basé sur les stocks) de tous les produits
        $this->calculateInitialRanking();
        // On va recalculer le score de tous les produits
        // ...
        // On va recalculer la position de tous les produits
        $this->calculatePosition();
    }
}
