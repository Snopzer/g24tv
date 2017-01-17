<?php
class Model_Module_Affiliate{
	public $data;
	public $_array;
	public function __construct($var=null) {
     

		$this->data['text_register'] = 'text_register_module_affiliate';
    	$this->data['text_login'] = 'text_login_module_affiliate';
		$this->data['text_logout'] = 'text_logout_module_affiliate';
		$this->data['text_forgotten'] = 'text_forgotten_module_affiliate';
		$this->data['text_account'] = 'text_account_module_affiliate';
		$this->data['text_edit'] = 'text_edit_module_affiliate';
		$this->data['text_password'] = 'text_password_module_affiliate';
		$this->data['text_payment'] = 'text_payment_module_affiliate';
		$this->data['text_tracking'] = 'text_tracking_module_affiliate';
		$this->data['text_transaction'] = 'text_transaction_module_affiliate';
		$affObj=new Model_Affinfo();
		$this->data['logged'] = $affObj->isLogged();
		$this->data['register'] = Model_url::getLink(array("controller"=>"affiliate","action"=>"register"),'',SERVER_SSL);
    	$this->data['login'] = Model_url::getLink(array("controller"=>"affiliate","action"=>"login"),'',SERVER_SSL);
		$this->data['logout'] = Model_url::getLink(array("controller"=>"affiliate","action"=>"logout"),'',SERVER_SSL);
		$this->data['forgotten'] = Model_url::getLink(array("controller"=>"affiliate","action"=>"forgotten"),'',SERVER_SSL);
		$this->data['account'] = Model_url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL);
		$this->data['edit'] = Model_url::getLink(array("controller"=>"affiliate","action"=>"edit"),'',SERVER_SSL);
		$this->data['password'] = Model_url::getLink(array("controller"=>"affiliate","action"=>"password"),'',SERVER_SSL);
		$this->data['payment'] = Model_url::getLink(array("controller"=>"affiliate","action"=>"payment"),'',SERVER_SSL);
		$this->data['tracking'] = Model_url::getLink(array("controller"=>"affiliate","action"=>"tracking"),'',SERVER_SSL);
		$this->data['transaction']=Model_url::getLink(array("controller"=>"affiliate","action"=>"transaction"),'',SERVER_SSL);

		/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/affiliate.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/affiliate.tpl';
		} else {
			$this->template = SITE_DEFAULT_TEMPLATE.'/template/module/affiliate.tpl';
		}*/
			$this->_array['data']=$this->data;
	}

	public function updateModule()
	{
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant('affiliate');
	$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting('affiliate', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam('affiliate_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (affiliate_module != '') {
			$modules = explode(',',@constant('affiliate_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		//echo $this->config->get('affiliate_module');
		/*echo "<pre>";
		print_r($modules);
		echo "</pre>";
		exit;*/
		foreach ($modules as $module) {
			$ifamlid=$front->getRequest()->getParam('affiliate_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['affiliate_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['affiliate_' . $module . '_layout_id'] = @constant('affiliate_' . $module . '_layout_id');
			}

			$ifamlititle=$front->getRequest()->getParam('affiliate_title');
			if (isset($ifamlititle)) {
				$this->data['affiliate_module_title'] = $ifamlititle;
			} else {
				$this->data['affiliate_module_title'] = @constant('affiliate_module_title');
			}

			$ifampos=$front->getRequest()->getParam('affiliate_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['affiliate_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['affiliate_' . $module . '_position'] = @constant('affiliate_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('affiliate_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['affiliate_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['affiliate_' . $module . '_status'] = @constant('affiliate_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('affiliate_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['affiliate_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['affiliate_' . $module . '_sort_order'] = @constant('affiliate_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('affiliate_module');
		if (isset($ifam)) {
			$this->data['affiliate_module'] =$ifam;
		} else {
			$this->data['affiliate_module'] = @constant('affiliate_module');
		}
		return $this->data;
	}
}
?>