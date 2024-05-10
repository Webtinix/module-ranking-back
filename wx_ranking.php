<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once dirname(__FILE__) . '/autoload.php';

(new WxCustomRankingLoader())->getLoader();


define("_DB_PREFIX_WEBTINIX_RANKING_",     "wx_ranking_");

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Wx_Ranking extends Module implements WidgetInterface
{
    private $default_score_ranking        = 0;
    private $default_initial_ranking      = 0;
    private $default_position_ranking     = 0;
    private $default_category_ranking     = 0;
    private $default_id_product_attribute = 0;

    public function __construct()
    {
        $this->name = 'wx_ranking';
        $this->tab = 'quick_bulk_update';
        $this->version = '1.0.3';
        $this->author = 'Webtinix';
        $this->need_instance = 0;
        $this->display_warnings = null;
        $this->display_messages = null;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('WX Ranking');
        $this->description = $this->l('WX Ranking');
        $this->confirmUninstall = $this->l('Uninstall ?');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        
        $this->default_score_ranking        = 
        Configuration::get('WX_RANKING_DEFAULT_SCORE_RANKING') 
        ? Configuration::get('WX_RANKING_DEFAULT_SCORE_RANKING') : 
        $this->default_score_ranking;

        $this->default_initial_ranking      = 
        Configuration::get('WX_RANKING_DEFAULT_INITIAL_RANKING') 
        ? Configuration::get('WX_RANKING_DEFAULT_INITIAL_RANKING') : 
        $this->default_initial_ranking;

        $this->default_position_ranking     = 
        Configuration::get('WX_RANKING_DEFAULT_POSITION_RANKING') 
        ? Configuration::get('WX_RANKING_DEFAULT_POSITION_RANKING') : 
        $this->default_position_ranking;

        $this->default_category_ranking     = 
        Configuration::get('WX_RANKING_DEFAULT_CATEGORY_RANKING') 
        ? Configuration::get('WX_RANKING_DEFAULT_CATEGORY_RANKING') : 
        $this->default_category_ranking;

        $this->default_id_product_attribute = 
        Configuration::get('WX_RANKING_DEFAULT_ID_PRODUCT_ATTRIBUTE') 
        ? Configuration::get('WX_RANKING_DEFAULT_ID_PRODUCT_ATTRIBUTE') : 
        $this->default_id_product_attribute;
    }  

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include dirname(__FILE__) . '/sql/install.php';
        
        // Configuration
        Configuration::updateValue('WX_RANKING_DEFAULT_SCORE_RANKING',        $this->default_score_ranking);
        Configuration::updateValue('WX_RANKING_DEFAULT_INITIAL_RANKING',      CalculateRanking::CODE_PRODUCT_NO_STOCK_NO_REAPPRO);
        Configuration::updateValue('WX_RANKING_DEFAULT_POSITION_RANKING',     $this->default_position_ranking);
        Configuration::updateValue('WX_RANKING_DEFAULT_CATEGORY_RANKING',     $this->default_category_ranking);
        Configuration::updateValue('WX_RANKING_DEFAULT_ID_PRODUCT_ATTRIBUTE', $this->default_id_product_attribute);

        return parent::install();
            // &&
            // $this->registerHook('header') &&
            // $this->registerHook('backOfficeHeader') &&
            // $this->registerHook('displayHome') &&
            // $this->registerHook('displayWxProductButtons') &&
            // $this->registerHook('displayCartModalFooter') &&
            // $this->registerHook('displayBrandPage') &&
            // $this->registerHook('displayCategoryPage') && 
            // $this->registerHook('displayRecoFPMobile') && 
            // $this->registerHook('displayShoppingCartFooter');
    }

    public function uninstall()
    {
        include dirname(__FILE__) . '/sql/uninstall.php';
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    // public function hookBackOfficeHeader()
    // {
        
    // }

    // /**
    //  * Add the CSS & JavaScript files you want to be added on the FO.
    //  */
    // public function hookHeader()
    // {
        
    // }

    // /* 1.7 */
    // public function hookDisplayWxProductButtons()
    // {
        
        
    // }

    // public function hookDisplayRecoFPMobile()
    // {
        
    // }


    // public function hookDisplayCartModalFooter()
    // {
        
    // }

    // public function hookDisplayShoppingCartFooter()
    // {
        
    // }

    public function renderWidget($hookName = null, array $configuration = [],$params = [])
    {   
        // echo '<pre>'; var_dump($hookName); echo '</pre>';
        return true;
        $variables = $this->getWidgetVariables($hookName, $configuration,$params);
        if (empty($variables)) {
            return false;
        }
        $templateFile = $hookName.'.tpl';
        if (file_exists(__DIR__.'/views/templates/hook/' . $templateFile)) {
            $this->smarty->assign($variables);
            return $this->fetch('module:' . $this->name . '/views/templates/hook/' . $templateFile);
        }
        return false;
    }

    public function getWidgetVariables($hookName = null, array $configuration = [], array $params=[])
    {
        // echo '<pre>'; var_dump($hookName); echo '</pre>';
        // die();
        if (isset($hookName) && in_array($hookName, ['displayRecoFPMobile','displayHome', 'displayWxProductButtons', 'displayModalCartCrosseling', 'displayShoppingCartFooter','displayBrandPage', 'displayCategoryPage'])) {
            if ($hookName == 'displayWxProductButtons' OR $hookName == 'displayRecoFPMobile' ) {
                $configHookName = 'displayFooterProduct';
            }elseif ($hookName == 'displayModalCartCrosseling') {
                $configHookName = 'displayPopUpShoppingCart';
            }else{
                $configHookName = $hookName;
            }    
                
            // $this->iw_tools->loadConfig();
            // if ($zoneID = $this->iw_tools->getConfigValue('NUUKIK_'.strtoupper($configHookName).'_ZONE')) {
            //     $itemID = "";
            //     if ($dataID = $this->iw_tools->getConfigValue('NUUKIK_'.strtoupper($configHookName).'_DATA')) {
                   
            //         $itemID = $this->getSourceItems($hookName, $zoneID, $dataID, $configuration);
                    
            //     }
            //     $products = $this->getProducts($zoneID, $itemID, true);
            //     if (!empty($products)) {
            //         return ['products' => $products, 'wx_customshop' => Module::getInstanceByName('wx_customshop')];
            //     }  
            // }
            
            $products = $this->getProducts($zoneID, $itemID, true);
            if (!empty($products)) {
                return ['products' => $products, 'wx_customshop' => Module::getInstanceByName('wx_customshop')];
            } 
        }
        return false;
    }

}
