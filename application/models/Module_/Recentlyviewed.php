<?php
class Model_Module_Recentlyviewed{
	public $data;
	public $_array;
	public function __construct($var=null) {
		$this->customer=new Model_Customer();
		$this->tax=new Model_Tax();
		$this->currency=new Model_Currencies();
		$module=$var[2];
    //$front = Zend_Controller_Front::getInstance();
      	$this->data['heading_title_bestseller'] = 'heading_title_module_bestseller';

		$this->data['button_cart'] = 'button_cart';

		$this->data['products'] = array();
		$prodObj=new Model_Products();
		$results = $prodObj->getRecentlyViewedProducts(@constant('recentlyviewed_' . $module . '_limit'));
		
		foreach ($results as $result) {
			if ($result['image']) {
				//$image = $this->model_tool_image->resize($result['image'], $this->config->get('bestseller_' . $module . '_image_width'), $this->config->get('bestseller_' . $module . '_image_height'));

				$image_avail=strpbrk($result['image'],'.');

				$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".constant('recentlyviewed_' . $module . '_image_width')."&h=".constant('recentlyviewed_' . $module . '_image_height')."&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=".constant('recentlyviewed_' . $module . '_image_width')."&h=".constant('recentlyviewed_' . $module . '_image_height')."&zc=1";

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
		$setobj->getConstant('recentlyviewed');
	$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting('bestseller', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam('recentlyviewed_module');
		if (isset($ifam)) {
	 		$modules = explode(',', $ifam);
		} elseif (@constant('recentlyviewed_module') != '') {
	 		$modules = explode(',',@constant('recentlyviewed_module'));
		} else {
	 		$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		//echo $this->config->get('recentlyviewed_module');
	
		foreach ($modules as $module) {
			$ifamil=$front->getRequest()->getParam('recentlyviewed_'.$module.'_limit');
			if (isset($ifamil)) {
				$this->data['recentlyviewed_' . $module . '_limit'] = $ifamil;
			} else {
				$this->data['recentlyviewed_' . $module . '_limit'] = @constant('recentlyviewed_' . $module . '_limit');
			}

			$ifamit=$front->getRequest()->getParam('recentlyviewed_module_title');
			if (isset($ifamit)) {
				$this->data['recentlyviewed_module_title'] = $ifamit;
			} else {
				$this->data['recentlyviewed_module_title'] = @constant('recentlyviewed_module_title');
			}

			$ifamiw=$front->getRequest()->getParam('recentlyviewed_'.$module.'_image_width');
			if (isset($ifamiw)) {
				$this->data['recentlyviewed_' . $module . '_image_width'] = $ifamiw;
			} else {
				$this->data['recentlyviewed_' . $module . '_image_width'] = @constant('recentlyviewed_' . $module . '_image_width');
			}

			$ifamih=$front->getRequest()->getParam('recentlyviewed_'.$module.'_image_height');
			if (isset($ifamih)) {
				$this->data['recentlyviewed_' . $module . '_image_height'] = $ifamih;
			} else {
				$this->data['recentlyviewed_' . $module . '_image_height'] = @constant('recentlyviewed_' . $module . '_image_height');
			}

			$ifamlid=$front->getRequest()->getParam('recentlyviewed_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['recentlyviewed_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['recentlyviewed_' . $module . '_layout_id'] = @constant('recentlyviewed_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('recentlyviewed_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['recentlyviewed_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['recentlyviewed_' . $module . '_position'] = @constant('recentlyviewed_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('recentlyviewed_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['recentlyviewed_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['recentlyviewed_' . $module . '_status'] = @constant('recentlyviewed_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('recentlyviewed_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['recentlyviewed_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['recentlyviewed_' . $module . '_sort_order'] = @constant('recentlyviewed_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('recentlyviewed_module');
		if (isset($ifam)) {
			$this->data['recentlyviewed_module'] =$ifam;
		} else {
			$this->data['recentlyviewed_module'] = @constant('recentlyviewed_module');
		}
		return $this->data;
	}
}
?>