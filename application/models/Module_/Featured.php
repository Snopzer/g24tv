<?php
class Model_Module_Featured{
		public function __construct($var=null) {
		$this->customer=new Model_Customer();
		$this->tax=new Model_Tax();
		$this->currency=new Model_Currencies();
		$module=$var[2];
    //$front = Zend_Controller_Front::getInstance();
      	$this->data['heading_title_featured'] = 'heading_title_module_featured';

		$this->data['button_cart'] = 'button_cart';

		$this->data['products'] = array();
		$prodObj=new Model_Products();
		$results = $prodObj->getFeaturedProducts(constant('featured_product'),'5');

		foreach ($results as $result) {
			if ($result['image']) {
				 $image_avail=strpbrk($result['image'],'.');

				$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".constant('featured_' . $module . '_image_width')."&h=".constant('featured_' . $module . '_image_height')."&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=".constant('featured_' . $module . '_image_width')."&h=".constant('featured_' . $module . '_image_height')."&zc=1";

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
		$setobj->getConstant('featured');
		$front = Zend_Controller_Front::getInstance();


		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		/*echo "<pre>";
		print_r($this->_getAllParams());
		echo "</pre>";
		exit;*/

				$setobj->editSetting('featured', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}
$products=array();
		$ifafp=$front->getRequest()->getParam('featured_product');
		if (isset($ifafp)) {
			$products = explode(',', $ifafp);
		} else {
			if(@constant('featured_product')!="")
			$products = explode(',', @constant('featured_product'));
		}
$this->data['featured_product']=$products;

		$this->data['products'] = array();
		$prodObj=new Model_Products();
		foreach ($products as $product_id) {
			$product_info = $prodObj->getProduct($product_id);
			if ($product_info) {
				$this->data['products'][] = array(
					'product_id' => $product_info['products_id'],
					'name'       => $product_info['products_name_full']
				);
			}
		}

		$ifam=$front->getRequest()->getParam('featured_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (@constant('featured_module') != '') {
			$modules = explode(',',@constant('featured_module'));
		} else {
			$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		//echo $this->config->get('featured_module');
		/*echo "<pre>";
		print_r($modules);
		echo "</pre>";
		exit;*/
			$ifamititle=$front->getRequest()->getParam('featured_module_title');
			if (isset($ifamititle)) {
				$this->data['featured_module_title'] = $ifamititle;
			} else {
				$this->data['featured_module_title'] = @constant('featured_module_title');
			}
		foreach ($modules as $module) {
			$ifamil=$front->getRequest()->getParam('featured_'.$module.'_limit');
			if (isset($ifamil)) {
				$this->data['featured_' . $module . '_limit'] = $ifamil;
			} else {
				$this->data['featured_' . $module . '_limit'] = @constant('featured_' . $module . '_limit');
			}

			$ifamiw=$front->getRequest()->getParam('featured_'.$module.'_image_width');
			if (isset($ifamiw)) {
				$this->data['featured_' . $module . '_image_width'] = $ifamiw;
			} else {
				$this->data['featured_' . $module . '_image_width'] = @constant('featured_' . $module . '_image_width');
			}

			$ifamih=$front->getRequest()->getParam('featured_'.$module.'_image_height');
			if (isset($ifamih)) {
				$this->data['featured_' . $module . '_image_height'] = $ifamih;
			} else {
				$this->data['featured_' . $module . '_image_height'] = @constant('featured_' . $module . '_image_height');
			}

			$ifamlid=$front->getRequest()->getParam('featured_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['featured_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['featured_' . $module . '_layout_id'] = @constant('featured_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('featured_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['featured_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['featured_' . $module . '_position'] = @constant('featured_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('featured_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['featured_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['featured_' . $module . '_status'] = @constant('featured_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('featured_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['featured_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['featured_' . $module . '_sort_order'] = @constant('featured_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('featured_module');
		if (isset($ifam)) {
			$this->data['featured_module'] =$ifam;
		} else {
			$this->data['featured_module'] = @constant('featured_module');
		}
		return $this->data;
	}
}
?>