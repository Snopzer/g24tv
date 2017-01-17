<?php
class Model_Module_Content {
	public $data;
	public $_array;
	public function __construct($var=null) {
		$module=$var[2];
		$banObj=new Model_Information();
		$results = $banObj->getInformation(@constant('content_' . $module . '_content_id'));
		

		$this->data['content'] = $results;
		$this->_array['data']=$this->data;
	}

	public function updateModule()
	{
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant('content');
		$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting('content', $this->_getAllParams());
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam('content_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (@constant('content_module')!= '') {
			$modules = explode(',',@constant('content_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		$banObj=new Model_Information();
		$this->data['banners'] = $banObj->getContents();

		foreach ($modules as $module) {
			$ifambid=$front->getRequest()->getParam('content_'.$module.'_content_id');
			if (isset($ifambid)) {
				$this->data['content_' . $module . '_content_id'] = $ifambid;
			} else {
				$this->data['content_' . $module . '_content_id'] = @constant('content_' . $module . '_content_id');
			}

			/*$ifamiw=$front->getRequest()->getParam('content_'.$module.'_width');
			if (isset($ifamiw)) {
				$this->data['content_' . $module . '_width'] = $ifamiw;
			} else {
				$this->data['content_' . $module . '_width'] = @constant('content_' . $module . '_width');
			}

			$ifamih=$front->getRequest()->getParam('content_'.$module.'_height');
			if (isset($ifamih)) {
				$this->data['content_' . $module . '_height'] = $ifamih;
			} else {
				$this->data['content_' . $module . '_height'] = @constant('content_' . $module . '_height');
			}*/

			$ifamlid=$front->getRequest()->getParam('content_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['content_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['content_' . $module . '_layout_id'] = @constant('content_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('content_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['content_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['content_' . $module . '_position'] = @constant('content_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('content_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['content_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['content_' . $module . '_status'] = @constant('content_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('content_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['content_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['content_' . $module . '_sort_order'] = @constant('content_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('content_module');
		if (isset($ifam)) {
			$this->data['content_module'] =$ifam;
		} else {
			$this->data['content_module'] = @constant('content_module');
		}
		return $this->data;
	}
}
?>