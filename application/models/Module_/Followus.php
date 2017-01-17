<?php
class Model_Module_Followus {
	public $data;
	public $_array;
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
		$prefix="followus";
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant($prefix);
		$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting($prefix, $this->_getAllParams());
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam($prefix.'_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (@constant($prefix.'_module') != '') {
			$modules = explode(',',@constant($prefix.'_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		foreach ($modules as $module) {
			$ifamlid=$front->getRequest()->getParam($prefix.'_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data[$prefix.'_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data[$prefix.'_' . $module . '_layout_id'] = @constant($prefix.'_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam($prefix.'_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data[$prefix.'_' . $module . '_position'] = $ifampos;
			} else {
				$this->data[$prefix.'_' . $module . '_position'] = @constant($prefix.'_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam($prefix.'_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data[$prefix.'_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data[$prefix.'_' . $module . '_status'] = @constant($prefix.'_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam($prefix.'_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data[$prefix.'_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data[$prefix.'_' . $module . '_sort_order'] = @constant($prefix.'_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam($prefix.'_module');
		if (isset($ifam)) {
			$this->data[$prefix.'_module'] =$ifam;
		} else {
			$this->data[$prefix.'_module'] = @constant($prefix.'_module');
		}
		return $this->data;
	}

}
?>