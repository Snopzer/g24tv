<?php
class Model_Module_Compare {
	public $data;
	public $_array;
	public function __construct($var=null) {
	 
	}


	public function updateModule()
	{
		$prefix="compare";
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

			$ifamlititle=$front->getRequest()->getParam($prefix.'_module_title');
			if (isset($ifamlititle)) {
				$this->data[$prefix.'_module_title'] = $ifamlititle;
			} else {
				$this->data[$prefix.'_module_title'] = @constant($prefix.'_module_title');
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