<?php
class Model_Module_Youtubevideos{
	public $data;
	public $_array;
	public function __construct($var=null) {
		$module=$var[2];
		
		$this->data['youtubevideos'] = array();
		$this->data['youtubevideos'][value]=@constant('youtubevideos_'.$module.'_value');
		$this->data['youtubevideos'][title]=@constant('youtubevideos_'.$module.'_title');
		$this->data['youtubevideos'][width]=@constant('youtubevideos_'.$module.'_video_width');
		$this->data['youtubevideos'][height]=@constant('youtubevideos_'.$module.'_video_height');
		$this->_array['data']=$this->data;
	}

	public function updateModule()
	{
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant('youtubevideos');
		$front = Zend_Controller_Front::getInstance();

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$setobj->editSetting('youtubevideos', $this->_getAllParams());
				$this->_redirect('module.phtml?msg='.base64_encode('update successfull'));
			}

 
		$ifam=$front->getRequest()->getParam('youtubevideos_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (@constant('youtubevideos_module') != '') {
			$modules = explode(',',@constant('youtubevideos_module'));
		} else {
			$modules = array();
		}
		$this->data['youtubevideos']=$products;

		$ifam=$front->getRequest()->getParam('youtubevideos_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (@constant('youtubevideos_module') != '') {
			$modules = explode(',',@constant('youtubevideos_module'));
		} else {

			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		foreach ($modules as $module) {
			
			$ifamiw=$front->getRequest()->getParam('youtubevideos_'.$module.'_video_width');
			if (isset($ifamiw)) {
				$this->data['youtubevideos_' . $module . '_video_width'] = $ifamiw;
			} else {
				$this->data['youtubevideos_' . $module . '_video_width'] = @constant('youtubevideos_' . $module . '_video_width');
			}

			$ifamitit=$front->getRequest()->getParam('youtubevideos_'.$module.'_title');
			if (isset($ifamitit)) {
				$this->data['youtubevideos_' . $module . '_title'] = $ifamitit;
			} else {
				$this->data['youtubevideos_' . $module . '_title'] = @constant('youtubevideos_' . $module . '_title');
			}

			$ifamipar=$front->getRequest()->getParam('youtubevideos_'.$module.'_value');
			if (isset($ifamipar)) {
				$this->data['youtubevideos_' . $module . '_value'] = $ifamipar;
			} else {
				$this->data['youtubevideos_' . $module . '_value'] = @constant('youtubevideos_' . $module . '_value');
			}

			$ifamih=$front->getRequest()->getParam('youtubevideos_'.$module.'_video_height');
			if (isset($ifamih)) {
				$this->data['youtubevideos_' . $module . '_video_height'] = $ifamih;
			} else {
				$this->data['youtubevideos_' . $module . '_video_height'] = @constant('youtubevideos_' . $module . '_video_height');
			}

			$ifamlid=$front->getRequest()->getParam('youtubevideos_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['youtubevideos_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['youtubevideos_' . $module . '_layout_id'] = @constant('youtubevideos_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('youtubevideos_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['youtubevideos_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['youtubevideos_' . $module . '_position'] = @constant('youtubevideos_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('youtubevideos_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['youtubevideos_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['youtubevideos_' . $module . '_status'] = @constant('youtubevideos_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('youtubevideos_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['youtubevideos_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['youtubevideos_' . $module . '_sort_order'] = @constant('youtubevideos_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('youtubevideos_module');
		if (isset($ifam)) {
			$this->data['youtubevideos_module'] =$ifam;
		} else {
			$this->data['youtubevideos_module'] = @constant('youtubevideos_module');
		}
		return $this->data;
	}
}
?>