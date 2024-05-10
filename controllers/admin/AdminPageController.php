<?php

class AdminPageController extends AdminController {

    public function __construct()
	{
	 	$this->table = 'Admin page';
		$this->className = 'Admin page';
		$this->module = 'wx_customshop';
	 	$this->lang = false;
		parent::__construct();
	}

	public function initContent()
	{
		$this->setTemplate(_PS_THEME_DIR_.'/views/templates/admin/config.tpl');
 
		return parent::initContent();
	}

	public function getContent()
	{
		return $this->display(__FILE__, '/views/templates/admin/config.tpl');
	}
}