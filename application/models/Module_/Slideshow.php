<?php
class Model_Module_Slideshow{
	public $data;
	public $_array;
	public function __construct($var=null) {
		//$this->document->addScript('catalog/view/javascript/jquery/nivo-slider/jquery.nivo.slider.pack.js');
		//$this->document->addStyle('catalog/view/theme/' . $this->config->get('config_template') . '/stylesheet/slideshow.css');
		$module=$var[2];
		$this->data['width'] = @constant('slideshow_' . $module . '_width');
		$this->data['height'] = @constant('slideshow_' . $module . '_height');

		$this->data['banners'] = array();
		$banObj=new Model_Banner();
		$results = $banObj->getSlideShow(@constant('slideshow_' . $module . '_banner_id'));
		/*echo "<pre>";
		print_r($results);
		echo "</pre>";*/
		foreach ($results as $result)
		{
 			if (file_exists(PATH_TO_UPLOADS_DIR."banners/".$result['image']))
			{

 				$image_avail=strpbrk($result['image'],'.');

				$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".constant('slideshow_' . $module . '_width')."&h=".constant('slideshow_' . $module . '_height')."&zc=1":PATH_TO_UPLOADS."banners/".$result['image']."&w=".constant('slideshow_' . $module . '_width')."&h=".constant('slideshow_' . $module . '_height')."&zc=1";

				$this->data['banners'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'image' => $image);
			}
		}
		//print_r($this->data['banners']);
		//echo "</pre>";
		$this->data['module'] = $module;

		/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/slideshow.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/slideshow.tpl';
		} else {
			$this->template = SITE_DEFAULT_TEMPLATE.'/template/module/slideshow.tpl';
		}*/

		//$this->render();


		$this->_array['data']=$this->data;
	}

	public function updateModule()
	{
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant('slideshow');
		$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting('slideshow', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam('slideshow_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (slideshow_module != '') {
			$modules = explode(',',@constant('slideshow_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		$banObj=new Model_Banner();
		$this->data['banners'] = $banObj->getBanners();

		foreach ($modules as $module) {
			$ifambid=$front->getRequest()->getParam('slideshow_'.$module.'_banner_id');
			if (isset($ifambid)) {
				$this->data['slideshow_' . $module . '_banner_id'] = $ifambid;
			} else {
				$this->data['slideshow_' . $module . '_banner_id'] = @constant('slideshow_' . $module . '_banner_id');
			}

			$ifamiw=$front->getRequest()->getParam('slideshow_'.$module.'_width');
			if (isset($ifamiw)) {
				$this->data['slideshow_' . $module . '_width'] = $ifamiw;
			} else {
				$this->data['slideshow_' . $module . '_width'] = @constant('slideshow_' . $module . '_width');
			}

			$ifamih=$front->getRequest()->getParam('slideshow_'.$module.'_height');
			if (isset($ifamih)) {
				$this->data['slideshow_' . $module . '_height'] = $ifamih;
			} else {
				$this->data['slideshow_' . $module . '_height'] = @constant('slideshow_' . $module . '_height');
			}

			$ifamlid=$front->getRequest()->getParam('slideshow_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['slideshow_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['slideshow_' . $module . '_layout_id'] = @constant('slideshow_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('slideshow_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['slideshow_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['slideshow_' . $module . '_position'] = @constant('slideshow_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('slideshow_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['slideshow_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['slideshow_' . $module . '_status'] = @constant('slideshow_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('slideshow_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['slideshow_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['slideshow_' . $module . '_sort_order'] = @constant('slideshow_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('slideshow_module');
		if (isset($ifam)) {
			$this->data['slideshow_module'] =$ifam;
		} else {
			$this->data['slideshow_module'] = @constant('slideshow_module');
		}
		return $this->data;
	}


}
?>