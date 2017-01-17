<?php
	class Model_Module_Banner {
		public $data;
		public $_array;
		public function __construct($var=null) {
			$module=$var[2];
			//echo "in banners";
			$this->data['side_banners'] = array();
			$banObj=new Model_Banner();
			//echo 'banner_' . $module . '_banner_id';
			$b=$banObj->getBanner(@constant('banner_' . $module . '_banner_id'));
			$results = $banObj->getSlideShow(@constant('banner_' . $module . '_banner_id'));
			/*echo "<pre>";
				//print_r($results);
				print_r($b);
			echo "</pre>";*/
			$this->data[title]=$b[0]['name'];
			foreach ($results as $result) {
				if (file_exists(PATH_TO_UPLOADS_DIR."banners/".$result['image']))
				{
					//exit("in");
					$image_avail=strpbrk($result['image'],'.');
					$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".constant('banner_' . $module . '_width')."&h=".constant('banner_' . $module . '_height')."&zc=1":PATH_TO_UPLOADS."banners/".$result['image']."&w=".constant('banner_' . $module . '_width')."&h=".constant('banner_' . $module . '_height')."&zc=1";
					
					$this->data['side_banners'][] = array(
					'title' => $result['title'],
					'link'  => $result['link'],
					'width'  => constant('banner_' . $module . '_width'),
					'height'  => constant('banner_' . $module . '_height'),
					'image' => $image
					
					);
				}
			}
			
			$this->data['module'] = $module;
			
			/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/banner.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/module/banner.tpl';
				} else {
				$this->template = SITE_DEFAULT_TEMPLATE.'/template/module/banner.tpl';
			}*/
			
			$this->_array['data']=$this->data;
		}
		
		public function updateModule()
		{
			$setobj=new Model_AdminModuleSetting();
			$setobj->getConstant('banner');
			$front = Zend_Controller_Front::getInstance();
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				
				$setobj->editSetting('banner', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}
			$ifam=$front->getRequest()->getParam('banner_module');
			if (isset($ifam)) {
				$modules = explode(',', $ifam);
				} elseif (banner_module != '') {
				$modules = explode(',',@constant('banner_module'));
				} else {
				$modules = array();
			}
			$this->data['layouts']=$setobj->getLayouts();
			$banObj=new Model_Banner();
			$this->data['banners'] = $banObj->getBanners();
			
			foreach ($modules as $module) {
				$ifambid=$front->getRequest()->getParam('banner_'.$module.'_banner_id');
				if (isset($ifambid)) {
					$this->data['banner_' . $module . '_banner_id'] = $ifambid;
					} else {
					$this->data['banner_' . $module . '_banner_id'] = @constant('banner_' . $module . '_banner_id');
				}
				
				$ifamiw=$front->getRequest()->getParam('banner_'.$module.'_width');
				if (isset($ifamiw)) {
					$this->data['banner_' . $module . '_width'] = $ifamiw;
					} else {
					$this->data['banner_' . $module . '_width'] = @constant('banner_' . $module . '_width');
				}
				
				$ifamih=$front->getRequest()->getParam('banner_'.$module.'_height');
				if (isset($ifamih)) {
					$this->data['banner_' . $module . '_height'] = $ifamih;
					} else {
					$this->data['banner_' . $module . '_height'] = @constant('banner_' . $module . '_height');
				}
				
				$ifamlid=$front->getRequest()->getParam('banner_'.$module.'_layout_id');
				if (isset($ifamlid)) {
					$this->data['banner_' . $module . '_layout_id'] = $ifamlid;
					} else {
					$this->data['banner_' . $module . '_layout_id'] = @constant('banner_' . $module . '_layout_id');
				}
				
				$ifampos=$front->getRequest()->getParam('banner_'.$module.'_position');
				if (isset($ifampos)) {
					$this->data['banner_' . $module . '_position'] = $ifampos;
					} else {
					$this->data['banner_' . $module . '_position'] = @constant('banner_' . $module . '_position');
				}
				
				$ifamstatus=$front->getRequest()->getParam('banner_'.$module.'_status');
				if (isset($ifamstatus)) {
					$this->data['banner_' . $module . '_status'] = $ifamstatus;
					} else {
					$this->data['banner_' . $module . '_status'] = @constant('banner_' . $module . '_status');
				}
				
				$ifamsort=$front->getRequest()->getParam('banner_'.$module.'_sort_order');
				if (isset($ifamsort)) {
					$this->data['banner_' . $module . '_sort_order'] = $ifamsort;
					} else {
					$this->data['banner_' . $module . '_sort_order'] = @constant('banner_' . $module . '_sort_order');
				}
			}
			
			$this->data['modules'] = $modules;
			$ifam=$front->getRequest()->getParam('banner_module');
			if (isset($ifam)) {
				$this->data['banner_module'] =$ifam;
				} else {
				$this->data['banner_module'] = @constant('banner_module');
			}
			return $this->data;
		}
	}
?>