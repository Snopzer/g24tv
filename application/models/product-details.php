<?
public function productDetailsAction()
	{
                echo "<pre>";
                print_r($this->_getAllParams());               
                echo "</pre>";
                exit;
            
                $url=new Model_Url('','');
                $ret=$url->getUrlParams($this->_getAllParams());
		
		$this->page='2';
		$currObj=new Model_currencies();
 		$currObj->setCurrency($this->_getParam('curr'));
 		$this->view->curr=$currObj;
		$catObj=new Model_Categories();

		/*start modules*/
		$moduleObj=new Model_Module();
		$this->view->pos=$moduleObj->getModules(array('page'=>'2')); //refers to category page as per r_layout
		/*end modules*/


		$ifpath=$this->_getParam('path');
		if (isset($ifpath)) {
			$path = '';

			foreach (explode('_', $this->_getParam('path')) as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category_info = $catObj->getCategory($path_id);
				if ($category_info) {
					$this->view->breadcrumbs[] = array(
						'text'      => $category_info['categories_name'],
						'href'      => HTTP_SERVER."product/category/path/".$path,
						'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
					);
				}
			}
		}
		$ifmanu=$this->_getParam('manufacturer_id');
		if (isset($ifmanu)) {
			$this->view->breadcrumbs[] = array(
				'text'      => $_SESSION['OBJ']['tr']->translate('text_brand'),
				'href'      => HTTP_SERVER."product/manufacturer",
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
			);
			$manuObj=new Model_Manufacturer();
			$manufacturer_info = $manuObj->getManufacturer($this->_getParam('manufacturer_id'));

			if ($manufacturer_info) {
				$this->view->breadcrumbs[] = array(
					'text'	    => $manufacturer_info['name'],
					'href'	    => HTTP_SERVER."product/manufacturer/product/manufacturer_id/'" . $this->_getParam('manufacturer_id'),
					'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
				);
			}
		}
		$iffilter_name=$this->_getParam('filter_name');

		$iffilter_tag=$this->_getParam('filter_tag');
		if (isset($iffilter_name) || isset($iffilter_tag)) {
			$url = '';

			$iffilter_name=$this->_getParam('filter_name');
			if (isset($iffilter_name)) {
				$url .= '/filter_name/' . $this->_getParam('filter_name');
			}

			$iffilter_tag=$this->_getParam('filter_tag');
			if (isset($iffilter_tag)) {
				$url .= '/filter_tag/' . $this->_getParam('filter_tag');
			}
			$iffilter_description=$this->_getParam('filter_description');
			if (isset($iffilter_description)) {
				$url .= '/filter_description/' . $this->_getParam('filter_description');
			}

			$iffilter_category_id=$this->_getParam('filter_description');
			if (isset($iffilter_category_id)) {
				$url .= '/filter_category_id/' . $this->_getParam('filter_description');
			}

			$this->view->breadcrumbs[] = array(
				'text'      => $_SESSION['OBJ']['tr']->translate('text_search'),
				'href'      => HTTP_SERVER."product/search",
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
			);
		}

		$ifproduct_id=$this->_getParam('product_id');
		if (isset($ifproduct_id)) {
			$product_id = $ifproduct_id;
		} else {
			$product_id = 0;
		}
		$prodObj=new Model_Products();
		$product_info = $prodObj->getProduct($product_id);
		/*echo "<pre>";
		print_r($product_info);
		echo "</pre>";*/
		//exit;
		$this->getMetaTags(array('meta_title'=>$product_info['products_name'],'meta_keywords'=>$product_info['meta_keyword'],'meta_description'=>$product_info['meta_description']));
		$this->view->product_info = $product_info;

		if ($product_info) {
			$url = '';

			$ifpath=$this->_getParam('path');
			if (isset($ifpath)) {
				$url .= '/path/' . $ifpath;
			}

			$ifmanu=$this->_getParam('manufacturer_id');
			if (isset($ifmanu)) {
				$url .= '/manufacturer_id/' . $ifmanu;
			}

			$iffilter_name=$this->_getParam('filter_name');
			if (isset($iffilter_name)) {
				$url .= '/filter_name/' . $iffilter_name;
			}

			$iffilter_tag=$this->_getParam('filter_tag');
			if (isset($iffilter_tag)) {
				$url .= '/filter_tag/' . $iffilter_tag;
			}

			$iffilter_description=$this->_getParam('filter_description');
			if (isset($iffilter_description)) {
				$url .= '/filter_description/' . $iffilter_description;
			}
			$iffilter_category_id=$this->_getParam('filter_category_id');
			if (isset($iffilter_category_id)) {
				$url .= '/filter_category_id/' . $iffilter_category_id;
			}

			$this->view->breadcrumbs[] = array(
				'text'      => $product_info['products_name'],
				'href'      => HTTP_SERVER."product/product".$url . '/product_id/' . $this->_getParam('product_id'),
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
			);

	  		/*$this->view->meta_title=$product_info['products_name'];
			$this->view->meta_description=$product_info['meta_description'];
			$this->view->meta_keyword=$product_info['meta_keyword'];*/
			$this->view->heading_title = $product_info['products_name'];
			$this->view->thumb = $product_info['image'];

			$reviewObj=new Model_Review();
			$this->view->tab_review = sprintf($_SESSION['OBJ']['tr']->translate('tab_review'), $reviewObj->getTotalReviewsByProductId($this->_getParam('product_id')));

			$this->view->product_id = $this->_getParam('product_id');
			$this->view->manufacturer = $product_info['manufacturers_name'];
			$this->view->manufacturers = HTTP_SERVER.'product/manufacturer/product/manufacturer_id/' . $product_info['manufacturers_id'];
			$this->view->model = $product_info['model'];
			$this->view->reward = $product_info['reward'];
			//echo "value of ".$product_info['reward'];
			$this->view->points = $product_info['points'];

			$DISPLAY_STOCK=STOCK_DISPLAY=="false"?"0":"1";
			//echo "value of display stock".$DISPLAY_STOCK;
			if ($product_info['products_quantity'] <= 0) {
				$this->view->stock = $product_info['stock_status_id'];
			} elseif ($DISPLAY_STOCK) {
				$this->view->stock = $product_info['products_quantity'];
			} else {
				$this->view->stock = $_SESSION['OBJ']['tr']->translate('text_instock');
			}

			//$this->load->model('tool/image');
			//$imageObj=new Model_Image();
			//echo $this->getRequest()->getParam('path');//print_r($this->_request->getQuery());


			if ($product_info['image']) {
				$this->view->popup=PATH_TO_UPLOADS."products/".$product_info['image'];
			} else
			{
				$this->view->popup = '';
			}

			if ($product_info['image']) {

				$image_avail=strpbrk($product_info['image'],'.');
					//echo "value of ".$res."<br/>";
				$imgTSize=explode("*",IMAGE_P_THUMB_SIZE);
					$this->view->thumb=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgTSize[0]."&h=".$imgTSize[1]."&zc=1":PATH_TO_UPLOADS."products/".$product_info['image']."&w=".$imgTSize[0]."&h=".$imgTSize[1]."&zc=1";

				/*$imgTSize=explode("*",IMAGE_P_THUMB_SIZE);
				$this->view->thumb =PATH_TO_UPLOADS."products/".$product_info['image']."&w=".$imgTSize[0]."&h=".$imgTSize[1]."&zc=1";*/

			} else {
				  $imgSize=explode("*",IMAGE_P_THUMB_SIZE);
					$this->view->thumb=PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
			}
//echo $this->view->thumb;
//exit;
			$this->view->images = array();

			$results = $prodObj->getProductImages($this->_getParam('product_id'));
			$imgPAddSize=explode("*",IMAGE_P_ADDITIONAL_SIZE);
			foreach($results as $result) {


				//echo "here";
			$this->view->images[] = array(
					'popup' => PATH_TO_UPLOADS."products/".$result['image'],
					'popup_resized' => PATH_TO_UPLOADS."products/".$result['image']."&w=".$imgPSize[0]."&h=".$imgPSize[1]."&zc=1",
					'htmlcontent' => $result[htmlcontent],
					'thumb' => PATH_TO_UPLOADS."products/".$result['image']."&w=".$imgPAddSize[0]."&h=".$imgPAddSize[1]."&zc=1"
				);
			}
			$taxObj=new Model_Tax();
			$LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=="false"?"0":"1";
			if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
			/*if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {*/
 				$this->view->price = $currObj->format($taxObj->calculate($product_info['price'], $product_info['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
			} else {
				$this->view->price = false;
			}

			if ((float)$product_info['special']) {
				$this->view->special =$currObj->format($taxObj->calculate($product_info['special'], $product_info['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));

			} else {
				$this->view->special = false;
			}

			if (constant(DISPLAY_PRICE_WITH_TAX)) {
				//$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);

				$this->view->tax = $currObj->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price']);

				} else {
					$this->view->tax = false;
				}


			$discounts = $prodObj->getProductDiscounts($this->_getParam('product_id'));
			/*echo "<pre>";
			print_r($discounts);
			echo "</pre>";
			exit;*/
			$this->view->discounts = array();

			foreach ($discounts as $discount) {
				$this->view->discounts[] = array(
					'quantity' => $discount['quantity'],
					'price'    =>
				$currObj->format($taxObj->calculate($discount['price'], $product_info['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX)));
			}

			$this->view->options = array();

			foreach ($prodObj->getProductOptions($this->_getParam('product_id')) as $option) {
				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox') {
					$option_value_data = array();

					foreach ($option['option_value'] as $option_value) {
						if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
							$option_value_data[] = array(
								'product_option_value_id' => $option_value['product_option_value_id'],
								'option_value_id'         => $option_value['option_value_id'],
								'name'                    => $option_value['name'],
								'price'                   => (float)$option_value['price'] ?
								$currObj->format($taxObj->calculate($option_value['price'], $product_info['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX)) : false,
								'price_prefix'            => $option_value['price_prefix']
							);
						}
					}

					$this->view->options[] = array(
						'product_option_id' => $option['product_option_id'],
						'option_id'         => $option['option_id'],
						'name'              => $option['name'],
						'type'              => $option['type'],
						'option_value'      => $option_value_data,
						'required'          => $option['required']
					);
				} elseif ($option['type'] == 'text' || $option['type'] == 'textarea' || $option['type'] == 'file' || $option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
					$this->view->options[] = array(
						'product_option_id' => $option['product_option_id'],
						'option_id'         => $option['option_id'],
						'name'              => $option['name'],
						'type'              => $option['type'],
						'option_value'      => $option['option_value'],
						'required'          => $option['required']
					);
				}
			}

			if ($product_info['products_minimum_quantity']) {
				$this->view->minimum = $product_info['products_minimum_quantity'];
			} else {
				$this->view->minimum = 1;
			}

			$this->view->text_minimum = sprintf($_SESSION['OBJ']['tr']->translate('text_minimum'), $product_info['products_minimum_quantity']);
 			$this->view->review_status = constant(ALLOW_REVIEWS)==false?'0':'1';
 			$this->view->reviews = sprintf($_SESSION['OBJ']['tr']->translate('text_reviews'), (int)$product_info['reviews']);
			$this->view->rating = (int)$product_info['rating'];
			$this->view->description = html_entity_decode($product_info['products_description'], ENT_QUOTES, 'UTF-8');
			$this->view->attribute_groups = $prodObj->getProductAttributes($this->_getParam('product_id'));

			$this->view->products = array();

			$results = $prodObj->getProductRelated($this->_getParam('product_id'));

			foreach ($results as $result) {
				if ($result['image']) {
					$imgRSize=explode("*",IMAGE_P_RELATED_SIZE);
					//$image =PATH_TO_UPLOADS."products/".$product_info['image']."&w=".$imgRSize[0]."&h=".$imgRSize[1]."&zc=1";
					$image_avail=strpbrk($result['image'],'.');
					//echo "value of ".$res."<br/>";
					$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgRSize[0]."&h=".$imgRSize[1]."&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=".$imgRSize[0]."&h=".$imgRSize[1]."&zc=1";
				} else {
					//$image = false;
					$imgSize=explode("*",IMAGE_P_RELATED_SIZE);
					$image=PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgRSize[0]."&h=".$imgRSize[1]."&zc=1";
					//$image =PATH_TO_UPLOADS."products/".constant(STORE_NO_IMAGE_ICON)."&w=".$imgRSize[0]."&h=".$imgRSize[1]."&zc=1";
				}
				//echo "value of ".$image;
					$LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=="false"?"0":"1";
				if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
			//echo "here inside";
					$price = $currObj->format($taxObj->calculate($result['price'], $result['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
				} else {
				//	echo "in false";
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $currObj->format($taxObj->calculate($result['special'], $result['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
				} else {
					$special = false;
				}

				if (ALLOW_REVIEWS) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

 				$this->view->products[] = array(
					'product_id' => $result['products_id'],
					'thumb'   	 => $image,
					'name'    	 => $result['products_name'],
					'price'   	 => $price,
					'special' 	 => $special,
					'rating'     => $rating,
					'reviews'    => sprintf($_SESSION['OBJ']['tr']->translate('text_reviews'), (int)$result['reviews']),
					'href'    	 => HTTP_SERVER."product/product-details/product_id/".$result['products_id']);
			}

			$this->view->tags = array();

			$results = $prodObj->getProductTags($this->_getParam('product_id'));

			foreach ($results as $result) {
				$this->view->tags[] = array(
					'tag'  => $result['tag'],
					'href' => HTTP_SERVER."product/search/filter_tag/".$result['tag']);
			}

			$prodObj->updateViewed($this->_getParam('product_id'));

		} else {
			$url = '';

			$ifpath=$this->_getParam('path');
			if (isset($ifpath)) {
				$url .= '/path/' . $ifpath;
			}

			$ifmanu=$this->_getParam('manufacturer_id');
			if (isset($ifmanu)) {
				$url .= '/manufacturer_id/'.$ifmanu;
			}

			$iffilter_name=$this->_getParam('filter_name');
			if (isset($iffilter_name)) {
				$url .= '/filter_name/' . $iffilter_name;
			}

			$iffilter_tag=$this->_getParam('filter_tag');
			if (isset($this->request->get['filter_tag'])) {
				$url .= '/filter_tag/' . $iffilter_tag;
			}

			$iffilter_description=$this->_getParam('filter_description');
			if (isset($iffilter_description)) {
				$url .= '/filter_description/' . $iffilter_description;
			}

			$iffilter_category_id=$this->_getParam('filter_category_id');
			if (isset($iffilter_category_id)) {
				$url .= '&filter_category_id=' . $iffilter_category_id;
			}

      		$this->data['breadcrumbs'][] = array(
        		'text'      => $_SESSION['OBJ']['tr']->translate('text_error'),
				'href'      => HTTP_SERVER."product/product".$url."/product_id/" . $product_id,
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator'));

      		//$this->document->setTitle($this->language->get('text_error'));
    	}
	}
?>