<?php
class Model_Module_Twitterconnect {
	public $data;
	public $_array;
	
	public function __construct($var=null) {
		$module=$var[2];
		
		$this->data['twitterconnect'] = array();
		$this->_array['data']=$this->data;
	}

	public function updateModule()
	{
		$prefix="twitterconnect";
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
		
		$ifamkey=$front->getRequest()->getParam($prefix.'_apikey');
		if (isset($ifamkey)) {
			$this->data[$prefix.'_apikey'] = $ifamkey;
		} else {
			$this->data[$prefix.'_apikey'] = @constant($prefix.'_apikey');
		}

		$ifamsec=$front->getRequest()->getParam($prefix.'_apisecret');
		if (isset($ifamsec)) {
			$this->data[$prefix.'_apisecret'] = $ifamsec;
		} else {
			$this->data[$prefix.'_apisecret'] = @constant($prefix.'_apisecret');
		}

		$ifambut=$front->getRequest()->getParam($prefix.'_button');
		if (isset($ifambut)) {
			$this->data[$prefix.'_button'] = $ifambut;
		} else {
			$this->data[$prefix.'_button'] = @constant($prefix.'_button');
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