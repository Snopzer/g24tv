<?php
class Model_Module_Manufacturer {
	public $data;
	public $_array;
	public function __construct($var=null) {
		$module=$var[2];
		$this->data['title'] = @constant('manufacturer_' . $module . '_title');
		$this->data['limit'] = @constant('manufacturer_' . $module . '_limit');
		$this->data['scroll'] = @constant('manufacturer_' . $module . '_scroll');
		$this->data['axis'] = @constant('manufacturer_' . $module . '_axis');

		$this->data['manufacturers'] = array();
		$manObj=new Model_Manufacturer();
		$results = $manObj->getManufacturers();
		/*echo "<pre>";
		print_r($results);
		echo "</pre>";
		exit;*/
		
		foreach ($results as $result) {
			if ($result['manufacturers_image']) {
				$this->data['manufacturers'][] = array(
					'manufacturer_id' => $result['manufacturers_id'],
					'name'            => $result['manufacturers_name'],
					'href'            => Model_Url::getLink(array("controller"=>"product","action"=>"manufacturer"),'manu_filter/'.$result['manufacturers_id'],SERVER_SSL),
					'image'           =>PATH_TO_UPLOADS."image/".$result['manufacturers_image']."&w=".@constant('manufacturer_' . $module . '_image_width')."&h=".@constant('manufacturer_' . $module . '_image_height')."&zc=1");
			}
		}
		$this->_array['data']=$this->data;
		//$this->data['module'] = $module;
	}

	public function css() {
		if (isset($this->request->get['module'])) {
			$module = (int)$this->request->get['module'];
		} else {
			$module = 0;
		}

		// Horizontal
		$output  = '#manufacturer' . $module . ' .jcarousel-container-horizontal {';
		$output .= '	width: ' . @constant('manufacturer_' . $module . '_width') . 'px;';
		$output .= '	height: ' . @constant('manufacturer_' . $module . '_height') . 'px;';
		$output .= '}' . "\n";

		$output .= '#manufacturer' . $module . ' .jcarousel-clip-horizontal {';
		$output .= '	width: ' . (@constant('manufacturer_' . $module . '_width') - 36) . 'px;';
		$output .= '	height: ' . @constant('manufacturer_' . $module . '_height') . 'px;';
		$output .= '}' . "\n";

		$output .= '#manufacturer' . $module . ' .jcarousel-item-horizontal {';
		$output .= '	width: ' . @constant('manufacturer_' . $module . '_image_width') . 'px;';
		$output .= '	height: ' . @constant('manufacturer_' . $module . '_image_height') . 'px;';
		$output .= '}' . "\n";

		// Vertical
		$output .= '#manufacturer' . $module . ' .jcarousel-container-vertical {';
		$output .= '	width: ' . @constant('manufacturer_' . $module . '_width') . 'px;';
		$output .= '	height: ' . @constant('manufacturer_' . $module . '_height') . 'px;';
		$output .= '}' . "\n";

		$output .= '#manufacturer' . $module . ' .jcarousel-clip-vertical {';
		$output .= '	width: ' . @constant('manufacturer_' . $module . '_width') . 'px;';
		$output .= '	height: ' . (@constant('manufacturer_' . $module . '_height') - 36) . 'px;';
		$output .= '}' . "\n";

		$output .= '#manufacturer' . $module . ' .jcarousel-item-vertical {';
		$output .= '	width: 100%;';
		$output .= '	height: ' . @constant('manufacturer_' . $module . '_image_height') . 'px;';
		$output .= '}' . "\n";

		$this->response->addHeader('Content-type: text/css');
		$this->response->setOutput($output);
	}

	public function updateModule()
	{
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant('manufacturer');
	$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting('manufacturer', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam('manufacturer_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (manufacturer_module != '') {
			$modules = explode(',',@constant('manufacturer_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		//echo @constant('manufacturer_module');
		/*echo "<pre>";
		print_r($modules);
		echo "</pre>";
		exit;*/
		foreach ($modules as $module) {
			$ifamil=$front->getRequest()->getParam('manufacturer_'.$module.'_limit');
			if (isset($ifamil)) {
				$this->data['manufacturer_' . $module . '_limit'] = $ifamil;
			} else {
				$this->data['manufacturer_' . $module . '_limit'] = @constant('manufacturer_' . $module . '_limit');
			}

			$ifamw=$front->getRequest()->getParam('manufacturer_'.$module.'_width');
			if (isset($ifamw)) {
				$this->data['manufacturer_' . $module . '_width'] = $ifamw;
			} else {
				$this->data['manufacturer_' . $module . '_width'] = @constant('manufacturer_' . $module . '_width');
			}

			$ifamh=$front->getRequest()->getParam('manufacturer_'.$module.'_height');
			if (isset($ifamh)) {
				$this->data['manufacturer_' . $module . '_height'] = $ifamh;
			} else {
				$this->data['manufacturer_' . $module . '_height'] = @constant('manufacturer_' . $module . '_height');
			}

			$ifams=$front->getRequest()->getParam('manufacturer_'.$module.'_scroll');
			if (isset($ifams)) {
				$this->data['manufacturer_' . $module . '_scroll'] = $ifams;
			} elseif (@constant('manufacturer_' . $module . '_scroll')) {
				$this->data['manufacturer_' . $module . '_scroll'] = @constant('manufacturer_' . $module . '_scroll');
			} else {
				$this->data['manufacturer_' . $module . '_scroll'] = 5;
			}

			$ifamiw=$front->getRequest()->getParam('manufacturer_'.$module.'_image_width');
			if (isset($ifamiw)) {
				$this->data['manufacturer_' . $module . '_image_width'] = $ifamiw;
			} else {
				$this->data['manufacturer_' . $module . '_image_width'] = @constant('manufacturer_' . $module . '_image_width');
			}

			$ifamih=$front->getRequest()->getParam('manufacturer_'.$module.'_image_height');
			if (isset($ifamih)) {
				$this->data['manufacturer_' . $module . '_image_height'] = $ifamih;
			} else {
				$this->data['manufacturer_' . $module . '_image_height'] = @constant('manufacturer_' . $module . '_image_height');
			}

			$ifamx=$front->getRequest()->getParam('manufacturer_'.$module.'_axis');
			if (isset($ifamx)) {
				$this->data['manufacturer_' . $module . '_axis'] = $ifamx;
			} else {
				$this->data['manufacturer_' . $module . '_axis'] = @constant('manufacturer_' . $module . '_axis');
			}

			$ifamlid=$front->getRequest()->getParam('manufacturer_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['manufacturer_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['manufacturer_' . $module . '_layout_id'] = @constant('manufacturer_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('manufacturer_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['manufacturer_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['manufacturer_' . $module . '_position'] = @constant('manufacturer_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('manufacturer_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['manufacturer_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['manufacturer_' . $module . '_status'] = @constant('manufacturer_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('manufacturer_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['manufacturer_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['manufacturer_' . $module . '_sort_order'] = @constant('manufacturer_' . $module . '_sort_order');
			}

			$ifamtitle=$front->getRequest()->getParam('manufacturer_'.$module.'_title');
			if (isset($ifamtitle)) {
				$this->data['manufacturer_' . $module . '_title'] = $ifamtitle;
			} else {
				$this->data['manufacturer_' . $module . '_title'] = @constant('manufacturer_' . $module . '_title');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('manufacturer_module');
		if (isset($ifam)) {
			$this->data['manufacturer_module'] =$ifam;
		} else {
			$this->data['manufacturer_module'] = @constant('manufacturer_module');
		}
		return $this->data;
	}
}
?>