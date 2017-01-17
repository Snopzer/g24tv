<?php
class Model_Module_Googletalk {

	public $data;
	public $_array;
	public function __construct($var=null) {


		$this->data['code'] = @constant('googletalk_code');

		/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/google_talk.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/google_talk.tpl';
		} else {
			$this->template = SITE_DEFAULT_TEMPLATE.'/template/module/google_talk.tpl';
		}*/
		$this->_array['data']=$this->data;
	}

	public function updateModule()
	{
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant('googletalk');
	$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting('googletalk', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}

		$ifgtc=$front->getRequest()->getParam('googletalk_code');
		if (isset($ifgtc)) {
			$this->data['googletalk_code'] = $ifgtc;
		} else {

			$this->data['googletalk_code'] = @constant('googletalk_code');
		}

//echo "value of ".@constant('googletalk_code');
		$ifam=$front->getRequest()->getParam('googletalk_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (googletalk_module != '') {
			$modules = explode(',',@constant('googletalk_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		//echo $this->config->get('googletalk_module');
		/*echo "<pre>";
		print_r($modules);
		echo "</pre>";
		exit;*/
		foreach ($modules as $module) {
			$ifamlid=$front->getRequest()->getParam('googletalk_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['googletalk_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['googletalk_' . $module . '_layout_id'] = @constant('googletalk_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('googletalk_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['googletalk_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['googletalk_' . $module . '_position'] = @constant('googletalk_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('googletalk_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['googletalk_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['googletalk_' . $module . '_status'] = @constant('googletalk_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('googletalk_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['googletalk_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['googletalk_' . $module . '_sort_order'] = @constant('googletalk_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('googletalk_module');
		if (isset($ifam)) {
			$this->data['googletalk_module'] =$ifam;
		} else {
			$this->data['googletalk_module'] = @constant('googletalk_module');
		}
		return $this->data;
	}
}
?>