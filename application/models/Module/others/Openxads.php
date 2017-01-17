<?php
class Model_Module_Openxads{
	public $data;
	public $_array;
	public function __construct($var=null) {
		$this->customer=new Model_Customer();
		$this->tax=new Model_Tax();
		$this->currency=new Model_Currencies();
		$module=$var[2];
    //$front = Zend_Controller_Front::getInstance();
      	$this->data['heading_title_openxads'] = 'heading_title_module_openxads';

		$this->data['button_cart'] = 'button_cart';

		$this->data['products'] = array();
		$prodObj=new Model_Products();
		$results = $prodObj->getopenxadsProducts(constant('openxads_' . $module . '_script'));

		foreach ($results as $result) {
			if ($result['image']) {
				//$image = $this->model_tool_image->resize($result['image'], $this->config->get('openxads_' . $module . '_image_width'), $this->config->get('openxads_' . $module . '_image_height'));

				$image_avail=strpbrk($result['image'],'.');

				$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".constant('openxads_' . $module . '_image_width')."&h=".constant('openxads_' . $module . '_image_height')."&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=".constant('openxads_' . $module . '_image_width')."&h=".constant('openxads_' . $module . '_image_height')."&zc=1";

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
				'reviews'    => (int)$result['reviews'],//sprintf($this->trans->_('text_reviews'), (int)$result['reviews']),
				'href'    	 => HTTP_SERVER.'product/product-details/product_id/'.$result['products_id'],//$this->url->link('product/product', 'product_id=' . $result['product_id']),
			);
		}
$this->_array['data']=$this->data;
	}

	public function updateModule()
	{
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant('openxads');
	$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting('openxads', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam('openxads_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (@constant('openxads_module') != '') {
			$modules = explode(',',@constant('openxads_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
	
		foreach ($modules as $module) {
			$ifamil=$front->getRequest()->getParam('openxads_'.$module.'_script');
			if (isset($ifamil)) {
				$this->data['openxads_' . $module . '_script'] = $ifamil;
			} else {
				$this->data['openxads_' . $module . '_script'] = @constant('openxads_' . $module . '_script');
			}

			$ifamiw=$front->getRequest()->getParam('openxads_'.$module.'_image_width');
			if (isset($ifamiw)) {
				$this->data['openxads_' . $module . '_image_width'] = $ifamiw;
			} else {
				$this->data['openxads_' . $module . '_image_width'] = @constant('openxads_' . $module . '_image_width');
			}

			$ifamih=$front->getRequest()->getParam('openxads_'.$module.'_image_height');
			if (isset($ifamih)) {
				$this->data['openxads_' . $module . '_image_height'] = $ifamih;
			} else {
				$this->data['openxads_' . $module . '_image_height'] = @constant('openxads_' . $module . '_image_height');
			}

			$ifamlid=$front->getRequest()->getParam('openxads_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['openxads_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['openxads_' . $module . '_layout_id'] = @constant('openxads_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('openxads_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['openxads_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['openxads_' . $module . '_position'] = @constant('openxads_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('openxads_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['openxads_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['openxads_' . $module . '_status'] = @constant('openxads_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('openxads_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['openxads_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['openxads_' . $module . '_sort_order'] = @constant('openxads_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('openxads_module');
		if (isset($ifam)) {
			$this->data['openxads_module'] =$ifam;
		} else {
			$this->data['openxads_module'] = @constant('openxads_module');
		}
		return $this->data;
	}
}
?>