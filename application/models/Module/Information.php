<?php
class Model_Module_Information {
	public $data;
	public $_array;

	public function __construct() {
    	$this->data['heading_title'] = 'heading_title';

		$this->data['text_contact'] = 'text_contact';
    	$this->data['text_sitemap'] = 'text_sitemap';


		$this->data['informations'] = array();
		$infoObj=new Model_Information();
		foreach ($infoObj->getInformations() as $result) {
      		$this->data['informations'][] = array(
        		'title' => $result['title'],
	    		'href'  => HTTP_SERVER.'information/information/information_id/'.$result['page_id']);//$this->url->link('information/information', 'information_id=' . $result['information_id'])

    	}

		$this->data['contact'] = HTTP_SERVER.'information/contact';//$this->url->link('information/contact');
    	$this->data['sitemap'] = HTTP_SERVER.'information/sitemap';//$this->url->link('information/sitemap');
		$this->_array['data']=$this->data;
	}

	public function updateModule()
	{
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant('information');
	$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting('information', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam('information_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (information_module != '') {
			$modules = explode(',',@constant('information_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		//echo $this->config->get('information_module');
		/*echo "<pre>";
		print_r($modules);
		echo "</pre>";
		exit;*/
		$ifamlititle=$front->getRequest()->getParam('information_module_title');
			if (isset($ifamlititle)) {
				$this->data['information_module_title'] = $ifamlititle;
			} else {
				$this->data['information_module_title'] = @constant('information_module_title');
			}
		foreach ($modules as $module) {
			$ifamlid=$front->getRequest()->getParam('information_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['information_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['information_' . $module . '_layout_id'] = @constant('information_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('information_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['information_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['information_' . $module . '_position'] = @constant('information_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('information_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['information_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['information_' . $module . '_status'] = @constant('information_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('information_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['information_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['information_' . $module . '_sort_order'] = @constant('information_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('information_module');
		if (isset($ifam)) {
			$this->data['information_module'] =$ifam;
		} else {
			$this->data['information_module'] = @constant('information_module');
		}
		return $this->data;
	}
}
?>