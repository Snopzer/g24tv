<?php
class Model_Module_Fblike {
	public $data;
	public $_array;
	
	public function __construct($var=null) {
		$module=$var[2];
		
		$this->data['fblike'] = array();
		$this->data['fblike']['link']=@constant('fblike_'.$module.'_link');
		$this->data['fblike']['width']=@constant('fblike_'.$module.'_width');
		$this->data['fblike']['height']=@constant('fblike_'.$module.'_height');
		$this->data['fblike']['face']=@constant('fblike_'.$module.'_face');
		$this->data['fblike']['header']=@constant('fblike_'.$module.'_header');
		$this->data['fblike']['stream']=@constant('fblike_'.$module.'_stream');

		$this->_array['data']=$this->data;
	}

	public function updateModule()
	{
		$prefix="fblike";
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

			$ifamlw=$front->getRequest()->getParam($prefix.'_'.$module.'_width');
			if (isset($ifamlw)) {
				$this->data[$prefix.'_' . $module . '_width'] = $ifamlw;
			} else {
				$this->data[$prefix.'_' . $module . '_width'] = @constant($prefix.'_' . $module . '_width');
			}

			$ifamlh=$front->getRequest()->getParam($prefix.'_'.$module.'_height');
			if (isset($ifamlh)) {
				$this->data[$prefix.'_' . $module . '_height'] = $ifamlh;
			} else {
				$this->data[$prefix.'_' . $module . '_height'] = @constant($prefix.'_' . $module . '_height');
			}

			$ifamllink=$front->getRequest()->getParam($prefix.'_'.$module.'_link');
			if (isset($ifamllink)) {
				$this->data[$prefix.'_' . $module . '_link'] = $ifamllink;
			} else {
				$this->data[$prefix.'_' . $module . '_link'] = @constant($prefix.'_' . $module . '_link');
			}

			$ifamlface=$front->getRequest()->getParam($prefix.'_'.$module.'_face');
			if (isset($ifamlface)) {
				$this->data[$prefix.'_' . $module . '_face'] = $ifamlface;
			} else {
				$this->data[$prefix.'_' . $module . '_face'] = @constant($prefix.'_' . $module . '_face');
			}

			$ifamlstream=$front->getRequest()->getParam($prefix.'_'.$module.'_stream');
			if (isset($ifamlstream)) {
				$this->data[$prefix.'_' . $module . '_stream'] = $ifamlstream;
			} else {
				$this->data[$prefix.'_' . $module . '_stream'] = @constant($prefix.'_' . $module . '_stream');
			}

			$ifamlheader=$front->getRequest()->getParam($prefix.'_'.$module.'_header');
			if (isset($ifamlheader)) {
				$this->data[$prefix.'_' . $module . '_header'] = $ifamlheader;
			} else {
				$this->data[$prefix.'_' . $module . '_header'] = @constant($prefix.'_' . $module . '_header');
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