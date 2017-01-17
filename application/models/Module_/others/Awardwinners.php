<?php
class Model_Module_Awardwinners{
		public function __construct($var=null) {
		$this->customer=new Model_Customer();
		$this->tax=new Model_Tax();
		$this->currency=new Model_Currencies();
		$module=$var[2];
    //$front = Zend_Controller_Front::getInstance();
      	$this->data['heading_title_awardwinners'] = 'Award Winners';

		$this->data['button_cart'] = 'button_cart';

		$this->data['products'] = array();
		$prodObj=new Model_Products();
		$results = $prodObj->getFeaturedProducts(constant('awardwinners_product'),'5');//it can be used for award winners aswell

		foreach ($results as $result) {
			if ($result['image']) {
				 $image_avail=strpbrk($result['image'],'.');

				$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".constant('awardwinners_' . $module . '_image_width')."&h=".constant('awardwinners_' . $module . '_image_height')."&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=".constant('awardwinners_' . $module . '_image_width')."&h=".constant('awardwinners_' . $module . '_image_height')."&zc=1";

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
		$setobj->getConstant('awardwinners');
		$front = Zend_Controller_Front::getInstance();


		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

		/*echo "<pre>";
		print_r($this->_getAllParams());
		echo "</pre>";
		exit;*/

				$setobj->editSetting('awardwinners', $this->_getAllParams());
				//$this->session->data['success'] = $this->language->get('text_success');
				//$this->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
				$this->_redirect('module.phtml?msg=update successfull');
			}

		$ifafp=$front->getRequest()->getParam('awardwinners_product');
		$products=array();
		if (isset($ifafp)) {
			$products = explode(',', $ifafp);
		} else {
			if(@constant('awardwinners_product')!="")
			$products = explode(',', @constant('awardwinners_product'));
		}

$this->data['awardwinners_product']=$products;

		$this->data['products'] = array();
		$prodObj=new Model_Products();
		foreach ($products as $product_id) {
			$product_info = $prodObj->getProduct($product_id);

			if ($product_info) {
				$this->data['products'][] = array(
					'product_id' => $product_info['products_id'],
					'name'       => $product_info['products_name']
				);
			}
		}

		
		$modules = array();
		$ifam=$front->getRequest()->getParam('awardwinners_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (@constant('awardwinners_module') != '') {
			$modules = explode(',',@constant('awardwinners_module'));
		} else {
			//$modules = array();
		}
		$this->data['layouts']=$setobj->getLayouts();
		//echo $this->config->get('awardwinners_module');
		foreach ($modules as $module) {
			$ifamil=$front->getRequest()->getParam('awardwinners_'.$module.'_limit');
			if (isset($ifamil)) {
				$this->data['awardwinners_' . $module . '_limit'] = $ifamil;
			} else {
				$this->data['awardwinners_' . $module . '_limit'] = @constant('awardwinners_' . $module . '_limit');
			}

			$ifamiw=$front->getRequest()->getParam('awardwinners_'.$module.'_image_width');
			if (isset($ifamiw)) {
				$this->data['awardwinners_' . $module . '_image_width'] = $ifamiw;
			} else {
				$this->data['awardwinners_' . $module . '_image_width'] = @constant('awardwinners_' . $module . '_image_width');
			}

			$ifamih=$front->getRequest()->getParam('awardwinners_'.$module.'_image_height');
			if (isset($ifamih)) {
				$this->data['awardwinners_' . $module . '_image_height'] = $ifamih;
			} else {
				$this->data['awardwinners_' . $module . '_image_height'] = @constant('awardwinners_' . $module . '_image_height');
			}

			$ifamlid=$front->getRequest()->getParam('awardwinners_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data['awardwinners_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data['awardwinners_' . $module . '_layout_id'] = @constant('awardwinners_' . $module . '_layout_id');
			}

			$ifampos=$front->getRequest()->getParam('awardwinners_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data['awardwinners_' . $module . '_position'] = $ifampos;
			} else {
				$this->data['awardwinners_' . $module . '_position'] = @constant('awardwinners_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam('awardwinners_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data['awardwinners_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data['awardwinners_' . $module . '_status'] = @constant('awardwinners_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam('awardwinners_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data['awardwinners_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data['awardwinners_' . $module . '_sort_order'] = @constant('awardwinners_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam('awardwinners_module');
		if (isset($ifam)) {
			$this->data['awardwinners_module'] =$ifam;
		} else {
			$this->data['awardwinners_module'] = @constant('awardwinners_module');
		}
		return $this->data;
	}
}
?>