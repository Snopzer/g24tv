<?php
class Model_Module_Filter{
	public $data;
	public $_array;
	public function __construct() {
			$this->_array['data']=$this->data;
	}


	public function updateModule()
	{
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant('filter');
		$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting('filter', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam('filter_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (filter_module != '') {
			$modules = explode(',',@constant('filter_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		//echo $this->config->get('filter_module');
		/*echo "<pre>";
		print_r($modules);
		echo "</pre>";
		exit;*/
		foreach ($modules as $module) {
			$ifamlid=$front->getRequest()->getParam('filter_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['filter_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['filter_' . $module . '_layout_id'] = @constant('filter_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('filter_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['filter_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['filter_' . $module . '_position'] = @constant('filter_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('filter_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['filter_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['filter_' . $module . '_status'] = @constant('filter_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('filter_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['filter_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['filter_' . $module . '_sort_order'] = @constant('filter_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('filter_module');
		if (isset($ifam)) {
			$this->data['filter_module'] =$ifam;
		} else {
			$this->data['filter_module'] = @constant('filter_module');
		}
		return $this->data;
	}
}
?>