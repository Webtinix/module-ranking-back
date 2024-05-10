<?php

class WxCustomRankingLoader
{
    private $directpry = '';
    private $module;

    public function __construct(){
        
        $this->directpry = dirname(__FILE__);
    }
  
    public function getLoader()
    {
        spl_autoload_register(array($this, 'WxCustomRankingLoadClassLoader'), true, false);
    }

    public function WxCustomRankingLoadClassLoader($class_name)
    {
        # code...
        // Classes base
        if (file_exists($this->directpry . '/classes/' . $class_name . '.php')) {
            $class_file = $this->directpry . '/classes/' . $class_name . '.php';
        }
        // Type d'export
        else if (file_exists($this->directpry . '/classes/TypeExport/' . $class_name . '.php')) {
            $class_file = $this->directpry . '/classes/TypeExport/' . $class_name . '.php';
        }
        // Api
        else if (file_exists($this->directpry . '/classes/Api/Erp/BasicApi' . $class_name . '.php')) {
            $class_file = $this->directpry . '/classes/Api/Erp/BasicApi' . $class_name . '.php';
        }
        // Reponse API
        else if (file_exists($this->directpry . '/classes/Api/Response/' . $class_name . '.php')) {
            $class_file = $this->directpry . '/classes/Api/Response/' . $class_name . '.php';
        }
        // Basic API
        else if (file_exists($this->directpry . '/classes/Api/Erp/BasicApi/' . $class_name . '.php')) {
            $class_file = $this->directpry . '/classes/Api/Erp/BasicApi/' . $class_name . '.php';
        }
        // Administration
        else if (file_exists($this->directpry . '/classes/Administration/' . $class_name . '.php')) {
            $class_file = $this->directpry . '/classes/Administration/' . $class_name . '.php';
        }
        // Cleaner
        else if (file_exists($this->directpry . '/classes/Cleaner/' . $class_name . '.php')) {
            $class_file = $this->directpry . '/classes/Cleaner/' . $class_name . '.php';
        }
        // Front Controller Module
        else if (file_exists($this->directpry . '/controllers/front/' . $class_name . '.php')) {
            $class_file = $this->directpry . '/controllers/front/' . $class_name . '.php';
        }
        // EverPsGiftClass
        else if (file_exists(_PS_MODULE_DIR_ . 'everpsgift/models/' . $class_name . '.php')) {
            $class_file = _PS_MODULE_DIR_ . 'everpsgift/models/' . $class_name . '.php';
        }
        // Get Class By namespace
        else {
            $class_name_array_by_antislash = explode('\\', $class_name);

            $new_class_name = '';
            foreach($class_name_array_by_antislash as $key => $value){
                $new_class_name_pre = $this->getFolderOfPartNameSpace($value, $key, count($class_name_array_by_antislash));
                $new_class_name .= ($new_class_name_pre != '' ? '/' : '') . $new_class_name_pre;
            }

            if (file_exists($this->directpry . $new_class_name . '.php')) {
                $class_file = $this->directpry . $new_class_name . '.php';
            }
        }
        // echo '<pre>';
        // var_dump(($this->directpry . $new_class_name . '.php'));
        // echo '</pre>';
        // die();

        if (isset($class_file)) {
            require_once $class_file;
        }
        // else{
        //     (new ApiResponse($this->type_return))
        //     ->setData(ApiMessage::getErrorApiMessage())
        //     ->response();
        // }
    }

    public function getFolderOfPartNameSpace($partial_of_namespace, $position, $nbr_partial_namespace)
    {
        # code...
        $directory_array_by_antislash = explode('/', $this->directpry);

        $partial_of_namespace_pre = $partial_of_namespace;
        switch (strtolower($partial_of_namespace_pre)) {
            case 'module':
                # code...
                $partial_of_namespace_pre = 'modules';
                break;
            
            default:
                # code...
                $partial_of_namespace_pre = strtolower($partial_of_namespace_pre);
                break;
        }

        // echo '<pre>';
        // var_dump($partial_of_namespace_pre, in_array($partial_of_namespace_pre , $directory_array_by_antislash), $directory_array_by_antislash);
        // echo '</pre>';

        if (in_array($partial_of_namespace_pre , $directory_array_by_antislash)) {
            # code...
            return '';
        }

        switch ($partial_of_namespace) {
            case 'TypeExport':
                # code...
                return $partial_of_namespace;
                break;
            case 'Api':
                # code...
                return $partial_of_namespace;
                break;
            case 'Erp':
                # code...
                return $partial_of_namespace;
                break;
            case 'BasicApi':
                # code...
                return $partial_of_namespace;
                break;
            case 'Response':
                # code...
                return $partial_of_namespace;
                break;
            
            default:
                # code...
                return $position < ($nbr_partial_namespace - 1) ? strtolower($partial_of_namespace) : $partial_of_namespace;
                break;
        }
    }
}