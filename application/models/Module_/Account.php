<?php
class Model_Module_Account {
	public $data;
	public $_array;
	public $type;
	public function __construct($var=null) {
		$this->data['heading_title_account'] = 'heading_title_module_account';
    	$this->data['text_register'] = 'text_register_module_account';
    	$this->data['text_login'] = 'text_login_module_account';
		$this->data['text_logout'] = 'text_logout_module_account';
		$this->data['text_forgotten'] = 'text_forgotten_module_account';
		$this->data['text_account'] = 'heading_title_module_account';
		$this->data['text_edit'] = 'text_edit_module_account';
		$this->data['text_password'] = 'text_password_module_account';
		$this->data['text_wishlist'] = 'text_wishlist_module_account';
		$this->data['text_order'] = 'text_order_module_account';
		$this->data['text_download'] = 'text_download_module_account';
		$this->data['text_return'] = 'text_return_module_account';
		$this->data['text_transaction'] = 'text_transaction_module_account';
		$this->data['text_newsletter'] = 'text_newsletter_module_account';
		$custObj=new Model_Customer();
		$this->data['logged'] = $custObj->isLogged();
		$this->data['register'] =Model_url::getLink(array("controller"=>"account","action"=>"register"),'',SERVER_SSL);
    	$this->data['login'] = Model_url::getLink(array("controller"=>"account","action"=>"login"),'',SERVER_SSL);
		$this->data['logout'] = Model_url::getLink(array("controller"=>"account","action"=>"logout"),'',SERVER_SSL);
		$this->data['forgotten'] = Model_url::getLink(array("controller"=>"account","action"=>"forgotten"),'',SERVER_SSL);
		$this->data['account'] = Model_url::getLink(array("controller"=>"account","action"=>"account"),'',SERVER_SSL);
		$this->data['edit'] =Model_url::getLink(array("controller"=>"account","action"=>"edit"),'',SERVER_SSL);
		$this->data['password'] = Model_url::getLink(array("controller"=>"account","action"=>"password"),'',SERVER_SSL);
		$this->data['wishlist'] = HTTP_SERVER.'account/wishlist';
		$this->data['order'] = Model_url::getLink(array("controller"=>"account","action"=>"order"),'',SERVER_SSL);
		$this->data['download'] = Model_url::getLink(array("controller"=>"account","action"=>"download"),'',SERVER_SSL);
		$this->data['return'] = Model_url::getLink(array("controller"=>"account","action"=>"return"),'',SERVER_SSL);
		$this->data['transaction'] = Model_url::getLink(array("controller"=>"account","action"=>"transaction"),'',SERVER_SSL);
		$this->data['newsletter'] = Model_url::getLink(array("controller"=>"account","action"=>"newsletter"),'',SERVER_SSL);
		$this->_array['data']=$this->data;
	}


	public function updateModule()
	{
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant('account');
		$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting('account', $this->_getAllParams());
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam('account_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (@constant('account_module') != '') {
			$modules = explode(',',@constant('account_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		foreach ($modules as $module) {

			$ifamlid=$front->getRequest()->getParam('account_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['account_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['account_' . $module . '_layout_id'] = @constant('account_' . $module . '_layout_id');
			}

			$ifamlititle=$front->getRequest()->getParam('account_module_title');
			if (isset($ifamlititle)) {
				$this->data['account_module_title'] = $ifamlititle;
			} else {
				$this->data['account_module_title'] = @constant('account_module_title');
			}

			$ifampos=$front->getRequest()->getParam('account_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['account_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['account_' . $module . '_position'] = @constant('account_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('account_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['account_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['account_' . $module . '_status'] = @constant('account_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('account_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['account_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['account_' . $module . '_sort_order'] = @constant('account_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('account_module');
		if (isset($ifam)) {
			$this->data['account_module'] =$ifam;
		} else {
			$this->data['account_module'] = @constant('account_module');
		}
		return $this->data;
	}

}
?>