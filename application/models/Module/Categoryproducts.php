<?php
	class Model_Module_Categoryproducts{
		public $data;
		public $_array;
		public function __construct($var=null) {
			$this->customer=new Model_Customer();
			$this->tax=new Model_Tax();
			$this->currency=new Model_Currencies();
			$module=$var[2];
			$this->data['heading_title_bestseller'] = 'heading_title_module_bestseller';
			
			$this->data['button_cart'] = 'button_cart';
			
			$this->data['products'] = array();
			$prodObj=new Model_Products();
			$results = $prodObj->getLatestCategoryProducts(array("limit"=>constant('categoryproducts_' . $module . '_limit'),"categories_id"=>constant('categoryproducts_product')));
			//echo "<pre>";
			//print_r($results);
			//echo "</pre>--------------------------------<br/>";
			//exit;

			foreach ($results as $category=>$product) {
				foreach($product as $result){
					
					if ($result['image']) {
						$image_avail=strpbrk($result['image'],'.');
						$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".constant('categoryproducts_' . $module . '_image_width')."&h=".constant('categoryproducts_' . $module . '_image_height')."&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=".constant('categoryproducts_' . $module . '_image_width')."&h=".constant('categoryproducts_' . $module . '_image_height')."&zc=1";
						
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
					
					if(strlen($result['meta_description']) >255)
					{
					$description =  substr ($result['meta_description'],0,255); 
					}else{
					$description  = $result['meta_description'];
					}
					
					$urlObj=new Model_Url('','');
					$path= $urlObj->getProductPathforModule($result['products_id']);
				
					$this->data['category'][$category][] = array(
					'product_id' => $result['products_id'],
					'thumb'   	 => $image,
					'name'    	 => $result['products_name'],
					// display short description about the article Title & Date Added
					'description' => $description,
					'date_added' => $result['products_date_added'],
					'price'   	 => $price,
					'special' 	 => $special,
					'rating'     => $rating,
					'reviews'    => (int)$result['reviews'],//sprintf($this->trans->_('text_reviews'), (int)$result['reviews']),
					'href' => $urlObj->getLink(array("controller"=>"product","action"=>"product-details","path"=>$path,"product_id"=>$result['products_id']), "path/".$path."/product_id/".$result['products_id'])
					//'href'    	 => HTTP_SERVER.'product/product-details/product_id/'.$result['products_id'],//$this->url->link('product/product', 'product_id=' . $result['product_id']),
					);
				}
			}
			/*echo "<pre>";
				print_r($this->data);
				echo "</pre>";
			exit;*/
			$this->_array['data']=$this->data;
		}
		
		public function updateModule()
		{
			$setobj=new Model_AdminModuleSetting();
			$setobj->getConstant('categoryproducts');
			$front = Zend_Controller_Front::getInstance();
			
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				$setobj->editSetting('categoryproducts', $this->_getAllParams());
				$this->_redirect('module.phtml?msg=update successfull');
			}
			
			$ifafp=$front->getRequest()->getParam('categoryproduct');
			if (isset($ifafp)) {
				$products = explode(',', $ifafp);
				} else {
				if(@constant('categoryproducts_product')!="")
				{
					$products = explode(',', @constant('categoryproducts_product'));
				}else
				{
					$products = array();
				}
			}
			$this->data['categoryproducts']=$products;
			
			$this->data['products'] = array();
			$prodObj=new Model_Categories();
			foreach ($products as $product_id) {
				$product_info = $prodObj->getCategory($product_id);
				
				if ($product_info) {
					$this->data['products'][] = array(
					'product_id' => $product_info['categories_id'],
					'name'       => $product_info['categories_name']
					);
				}
			}
			
			$ifam=$front->getRequest()->getParam('categoryproducts_module');
			if (isset($ifam)) {
				$modules = explode(',', $ifam);
				} elseif (@constant('categoryproducts_module') != '') {
				$modules = explode(',',@constant('categoryproducts_module'));
				} else {
				
				$modules = array();
			}
			$this->data['layouts']=$setobj->getLayouts();
			foreach ($modules as $module) {
				$ifamil=$front->getRequest()->getParam('categoryproducts_'.$module.'_limit');
				if (isset($ifamil)) {
					$this->data['categoryproducts_' . $module . '_limit'] = $ifamil;
					} else {
					$this->data['categoryproducts_' . $module . '_limit'] = @constant('categoryproducts_' . $module . '_limit');
				}
				
				$ifamiw=$front->getRequest()->getParam('categoryproducts_'.$module.'_image_width');
				if (isset($ifamiw)) {
					$this->data['categoryproducts_' . $module . '_image_width'] = $ifamiw;
					} else {
					$this->data['categoryproducts_' . $module . '_image_width'] = @constant('categoryproducts_' . $module . '_image_width');
				}
				
				$ifamih=$front->getRequest()->getParam('categoryproducts_'.$module.'_image_height');
				if (isset($ifamih)) {
					$this->data['categoryproducts_' . $module . '_image_height'] = $ifamih;
					} else {
					$this->data['categoryproducts_' . $module . '_image_height'] = @constant('categoryproducts_' . $module . '_image_height');
				}
				
				$ifamlid=$front->getRequest()->getParam('categoryproducts_'.$module.'_layout_id');
				if (isset($ifamlid)) {
					$this->data['categoryproducts_' . $module . '_layout_id'] = $ifamlid;
					} else {
					$this->data['categoryproducts_' . $module . '_layout_id'] = @constant('categoryproducts_' . $module . '_layout_id');
				}
				
				$ifampos=$front->getRequest()->getParam('categoryproducts_'.$module.'_position');
				if (isset($ifampos)) {
					$this->data['categoryproducts_' . $module . '_position'] = $ifampos;
					} else {
					$this->data['categoryproducts_' . $module . '_position'] = @constant('categoryproducts_' . $module . '_position');
				}
				
				$ifamstatus=$front->getRequest()->getParam('categoryproducts_'.$module.'_status');
				if (isset($ifamstatus)) {
					$this->data['categoryproducts_' . $module . '_status'] = $ifamstatus;
					} else {
					$this->data['categoryproducts_' . $module . '_status'] = @constant('categoryproducts_' . $module . '_status');
				}
				
				$ifamsort=$front->getRequest()->getParam('categoryproducts_'.$module.'_sort_order');
				if (isset($ifamsort)) {
					$this->data['categoryproducts_' . $module . '_sort_order'] = $ifamsort;
					} else {
					$this->data['categoryproducts_' . $module . '_sort_order'] = @constant('categoryproducts_' . $module . '_sort_order');
				}
			}
			
			$this->data['modules'] = $modules;
			$ifam=$front->getRequest()->getParam('categoryproducts_module');
			if (isset($ifam)) {
				$this->data['categoryproducts_module'] =$ifam;
				} else {
				$this->data['categoryproducts_module'] = @constant('categoryproducts_module');
			}
			return $this->data;
		}
	}
?>