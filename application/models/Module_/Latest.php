<?php
class Model_Module_Latest{
	public $data;
	public $_array;
	public function __construct($var=null) {
		$this->customer=new Model_Customer();
		$this->tax=new Model_Tax();
		$this->currency=new Model_currencies();
		$this->products=new Model_Products();
		$module=$var[2];

     	$this->data['heading_title_latest'] = 'heading_title_module_latest';

		$this->data['button_cart'] = 'button_cart';

		$this->data['products'] = array();

		$data = array(
			'sort'  => 'p.products_date_added',
			'order' => 'DESC',
			'start' => 0,
			'limit' => constant('latest_' . $module . '_limit')
		);

		$results = $this->products->getProducts($data);

		foreach ($results as $result) {
			if ($result['image']) {
			//	$image = $this->model_tool_image->resize($result['image'], $this->config->get('latest' . $module . '_image_width'), $this->config->get('latest' . $module . '_image_height'));
			$image_avail=strpbrk($result['image'],'.');

				$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".constant('latest_' . $module . '_image_width')."&h=".constant('latest_' . $module . '_image_height')."&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=".constant('latest_' . $module . '_image_width')."&h=".constant('latest_' . $module . '_image_height')."&zc=1";
			} else {
				$image = false;
			}

			$LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=="false"?"0":"1";
			if (($LOGIN_DISPLAY_PRICE && $this->customer->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], constant('DISPLAY_PRICE_WITH_TAX')));
			} else {
				$price = false;
			}

			if ((float)$result['special']) {
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], constant('DISPLAY_PRICE_WITH_TAX')));
			} else {
				$special = false;
			}

			if (constant('ALLOW_REVIEWS')) {
				$rating = $result['rating'];
			} else {
				$rating = false;
			}

			$this->data['products'][] = array(
				'product_id' => $result['products_id'],
				'thumb'   	 => $image,
				'name'    	 => $result['products_name'],
				'price'   	 => $price,
				'special' 	 => $special,
				'rating'     => $rating,
				'reviews'    => (int)$result['reviews'],//sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
				'href'    	 => HTTP_SERVER.'product/product-details/product_id/'.$result['products_id'],//$this->url->link('product/product', 'product_id=' . $result['product_id']),
			);
		}
		$this->_array['data']=$this->data;
	}

	public function updateModule()
	{
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant('latest');
	$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting('latest', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam('latest_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (@constant('latest_module') != '') {
			$modules = explode(',',@constant('latest_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		$ifamititle=$front->getRequest()->getParam('latest_module_title');
			if (isset($ifamititle)) {
				$this->data['latest_module_title'] = $ifamititle;
			} else {
				$this->data['latest_module_title'] = @constant('latest_module_title');
			}
		foreach ($modules as $module) {
			$ifamil=$front->getRequest()->getParam('latest_'.$module.'_limit');
			if (isset($ifamil)) {
				$this->data['latest_' . $module . '_limit'] = $ifamil;
			} else {
				$this->data['latest_' . $module . '_limit'] = @constant('latest_' . $module . '_limit');
			}

			$ifamiw=$front->getRequest()->getParam('latest_'.$module.'_image_width');
			if (isset($ifamiw)) {
				$this->data['latest_' . $module . '_image_width'] = $ifamiw;
			} else {
				$this->data['latest_' . $module . '_image_width'] = @constant('latest_' . $module . '_image_width');
			}

			$ifamih=$front->getRequest()->getParam('latest_'.$module.'_image_height');
			if (isset($ifamih)) {
				$this->data['latest_' . $module . '_image_height'] = $ifamih;
			} else {
				$this->data['latest_' . $module . '_image_height'] = @constant('latest_' . $module . '_image_height');
			}

			$ifamlid=$front->getRequest()->getParam('latest'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['latest_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['latest_' . $module . '_layout_id'] = @constant('latest_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('latest_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['latest_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['latest_' . $module . '_position'] = @constant('latest_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('latest_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['latest_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['latest_' . $module . '_status'] = @constant('latest_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('latest_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['latest_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['latest_' . $module . '_sort_order'] = @constant('latest_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('latest_module');
		if (isset($ifam)) {
			$this->data['latest_module'] =$ifam;
		} else {
			$this->data['latest_module'] = @constant('latest_module');
		}
		return $this->data;
	}
}
?>