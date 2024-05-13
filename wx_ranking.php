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
    private $admin_url = '';

    public function __construct()
    {
        $this->name = 'wx_ranking';
        $this->tab = 'quick_bulk_update';
        $this->version = '1.0.3';
        $this->author = 'Webtinix';
        $this->need_instance = 0;
        $this->display_warnings = null;
        $this->display_messages = null;
        $this->admin_url = AdminController::$currentIndex.
        '&token='.Tools::getAdminTokenLite('AdminModules').
        '&configure='.$this->name.
        '&tab_module='.$this->tab.
        '&module_name='.$this->name;

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

        return parent::install() &&
            $this->registerHook('wxHandlerListingAdminMenu') &&
            $this->registerHook('wxHandlerListingAdminMenuAddOptionRankingAfter');
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
        $output = '';

        // get controller
        $controller = Tools::getValue('moduleController');

        // baseAdminModuleUrl
        $baseAdminModuleUrl = AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;

        // display any message, then the form
        if ($controller=='carrierConfiguration'){

            // if (Tools::getIsset('deletecustom_field') && Tools::getValue('id_custom_field')!=''){
                
            // } else {
            //     $output = $this->FieldForm();
            // }

            $carrier_admin = new CarrierAdministration($this);
            $output = $carrier_admin->renderView();

        } else if ($controller=='getCustomFields'){
            // get customer custom fields
            $output = $this->LocationFieldsList(Tools::getValue('location'));
        }  
        else if ($controller=='bannerProgConfiguration'){
            // get customer custom fields
            $banner_admin = new BannerProgAdministration($this);
            $output = $banner_admin->renderView();
        }   
        else if ($controller=='bannerAnyWhereConfiguration'){
            // get customer custom fields
            $banner_admin = new BannerAnyWhereAdministration($this);
            $output = $banner_admin->renderView();
        } 
        else if ($controller=='seeInfosCustomerOrderConfiguration' && Tools::getValue('id_customer')){
            // get customer custom fields
            $banner_admin = new InfosCustomerOrderAdministration($this);
            $banner_admin->setCustomer(Tools::getValue('id_customer'));
            $output = $banner_admin->renderView();
        } 
        else {
            // get module dashboard page
            $output = $this->Dashboard();
        }

        
        // gestion des actions
        if (Tools::getValue('moduleAction')) {
            # code...
            $action = Tools::getValue('moduleAction');

            if ($action == 'loginAsCustomer' && Tools::getValue('id_customer')) {
                # code...
                $id_customer = Tools::getValue('id_customer');
                // $customer = new Customer($id_customer);
                // if (!Validate::isLoadedObject($cart)) {
                //     $this->context->updateCustomer($customer);
                // }
                $uuid = uniqid();
                // die(Tools::getValue('id_customer'));
                \DB::getInstance()->insert(_DB_PREFIX_WEBTINIX_ . 'login_as_custom', [
                    'token' => Tools::getAdminTokenLite('AdminModules'),
                    'id_customer' => $id_customer,
                    'id_employee' => $context->employee->id,
                    'uuid' => $uuid,
                ]);

                // echo '<pre>';
                // var_dump($uuid);
                // echo '</pre>';
                // die();
                $my_account = $this->context->link->getPageLink('my-account', true);
                Tools::redirect($my_account . '?moduleAction=loginAsCustomer&uuid=' . $uuid);
            }
            // elseif ($action == 'loginAsCustomer' && Tools::getValue('uuid')) {
            //     # code...
            //     $uuid = Tools::getValue('uuid');
            //     $uuid = htmlspecialchars($uuid);

            //     $id_customers = \Db::getInstance()->executeS(
            //         "SELECT id_webservice FROM `" . _DB_PREFIX_WEBTINIX_ . "login_as_custom` WHERE `uuid` = $uuid ",
            //         $array = true, $use_cache = false
            //     );

            //     echo '<pre>';
            //     var_dump($id_customers);
            //     echo '</pre>';
            //     die();

            //     if (!empty($id_customers)) {
            //         # code...
            //         $id_customer = Tools::getValue('id_customer');
            //         $customer = new Customer($id_customer);
            //         if (!Validate::isLoadedObject($cart)) {
            //             $this->context->updateCustomer($customer);
            //         }
            //         $my_account = $this->context->link->getPageLink('my-account', true);
            //         Tools::redirect($my_account);
            //     }else{
            //         Tools::redirect('/');
            //     }
            // }
        }
        
        // return
        return $this->getHeader().$output.$this->getFooter();
    }
    
    /*
    * Dashboard
    */
    public function Dashboard()
    {
        // assign to smarty
        $this->smarty->assign(array(
                                    'uri' => $this->admin_url
                                    ));
        // return template
        return $this->display(__FILE__, '/views/templates/admin/dashboard.tpl');
    }

    /*
    * getHeader in admin
    */
    public function getHeader()
    {
        // get controller
        // $controller = Tools::getValue('moduleController');
        // // check if dashboard
        // if ($controller==''){
        //     $dashboard = true;
        // } else {
        //     $dashboard = false;
        // }
        // show current store only if multistore is enabled
        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE')) {
            $displayCurrentStore = true;
        } else {
            $displayCurrentStore = false;
        }

        // Menu des modules Ranking, Recommandations, et les autres modules qui seront liés au listing
        $menu = [];
        $menu = Hook::exec('wxHandlerListingAdminMenu', ['menu' => $menu], 
            $moduleId = null, 
            $returnArray = false, 
            $checkExceptions = true, 
            $usePush = false, 
            $shopId = null, 
            $chain = true
        );

        // assign to smarty
        $this->smarty->assign(array(
                                    'shop'                => $this->context->shop->id,
                                    'storeName'           => $this->context->shop->name ,
                                    'logoSrc'             => _PS_BASE_URL_.$this->_path.'/views/img/logo.png',
                                    'moduleImageDir'      => _PS_BASE_URL_.$this->_path.'/views/img/',
                                    'psVersion'           => $this->psVersion(),
                                    'displayCurrentStore' => $displayCurrentStore,
                                    'menu'                => $menu,
                                    'moduleVersion'       => $this->version,
                                    // 'dashboard'           => $dashboard,
                                    // 'controller'          => $controller,
                                    // 'uri'                 => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules').'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.''
                                    ));
        // return
        return $this->display(__FILE__, '/views/templates/admin/header.tpl');
    }

    /*
    * getFooter in Admin
    */
    public function getFooter()
    {
        $this->smarty->assign(array(
                                    'moduleVersion'       => $this->version,
                                    ));
        return $this->display(__FILE__, '/views/templates/admin/footer.tpl');
    }

    // On rajoute des éléments au header
    public function hookWxHandlerListingAdminMenu(array $params)
    {
        // Le tableau de bord vient en premier
        $params['menu'][] = $this->getOptionMenu($option = 'dashboard');

        // On peut rajouter une nouvelle option avant le ranking
        $option = [];
        $option = Hook::exec('wxHandlerListingAdminMenuAddOptionRankingBefore', 
            ['option' => $option], 
            $moduleId = null, 
            $returnArray = false, 
            $checkExceptions = true, 
            $usePush = false, 
            $shopId = null, 
            $chain = true
        );

        if (!empty($option) && !empty($option['option'])) {
            # code...
            $params['menu'][] = $option['option'];
        }

        // On ajoute le Ranking
        $params['menu'][] = $this->getOptionMenu($option = 'ranking');
        
        // On peut rajouter une nouvelle option après le ranking
        $option = [];
        $option = Hook::exec('wxHandlerListingAdminMenuAddOptionRankingAfter', 
            ['option' => $option], 
            $moduleId = null, 
            $returnArray = false, 
            $checkExceptions = true, 
            $usePush = false, 
            $shopId = null, 
            $chain = true
        );

        if (!empty($option) && !empty($option['option'])) {
            # code...
            $params['menu'][] = $option['option'];
        }

        // L'aide vient en dernier
        $params['menu'][] = $this->getOptionMenu($option = 'help');

        return $params;
    }


    public function getOptionMenu($option = null) {
        
        $title = '';
        $url = $this->admin_url;

        switch ($option) {
            case 'dashboard':
                # code...
                $title = $this->l('Tableau de bord');
                $url .= '';
                break;
            case 'ranking':
                # code...
                $title = $this->l('Ranking');
                $url .= '';
                break;
            case 'help':
                # code...
                $title = $this->l('Aide');
                $url .= '';
                break;
            case 'reco':
                # code...
                $title = $this->l('Recommandation');
                $url .= '';
                break;
            
            default:
                # code...
                $title = $this->l('...');
                break;
        }

        $option_menu = array(
            'title' => $title,
            'url'   => $url,
        ); 

        return $option_menu;
    }

    public function hookWxHandlerListingAdminMenuAddOptionRankingAfter(array $params) {
        
        // Le tableau de bord vient en premier
        $params['option'][] = $this->getOptionMenu($option = 'reco');

        return $params;
    }

    /*
    * psVersion
    */
    public static function psVersion()
    {
        $version = _PS_VERSION_;
        $exp = explode('.', $version);
        return $exp[0].'.'.$exp[1];
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        
    }

    /* 1.7 */
    public function hookDisplayWxProductButtons()
    {
        
        
    }

    public function hookDisplayRecoFPMobile()
    {
        
    }


    public function hookDisplayCartModalFooter()
    {
        
    }

    public function hookDisplayShoppingCartFooter()
    {
        
    }

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
        echo '<pre>'; var_dump($hookName); echo '</pre>';
        die();
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
