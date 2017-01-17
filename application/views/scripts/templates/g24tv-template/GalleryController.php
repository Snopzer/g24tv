<?php
	ob_start();
	/**
		* Handling Errors in the application
		*
		* @category   Zend
		* @package    GallaryController
	*/
	//class ProductController extends Zend_Controller_Action {
	class GalleryController extends My_Controller_Main {
		public $db=null;
		//public $page;
		public $path;
		public function init()
		{
			Zend_Session::start();
			$this->db=Zend_Db_Table::getDefaultAdapter();
			$this->getConstants();
			//$this->view->vaction=$this->getRequest()->getActionName();
			$this->setLangSession();
			$this->isAffiliateTrackingSet();
			$this->globalKeywords();
			$this->getHeader();
			$this->getFlashCart();
			
			$this->view->breadcrumbs = array();
			$this->view->breadcrumbs[] = array(
       		'text'      => $_SESSION['OBJ']['tr']->translate('text_home'),
			'href'      => HTTP_SERVER."index/index",
       		'separator' => false
			);
			//echo $this->getRequest->getParam('id');
		}
		
		public function getUrl($arr)
		{
			$url = '';
			if(in_array('sort',$arr))
			{
				$ifsort=$this->_getParam('sort');
				if (isset($ifsort)) {
					$url .= '/sort/' . $this->_getParam('sort');
				}
			}
			
			if(in_array('order',$arr))
			{
				$iforder=$this->_getParam('order');
				if (isset($iforder)) {
					$url .= '/order/' . $this->_getParam('order');
				}
			}
			
			if(in_array('limit',$arr))
			{
				$iflimit=$this->_getParam('limit');
				if (isset($iflimit)) {
					$url .= '/limit/' . $this->_getParam('limit');
				}
			}
			
			if(in_array('price_filter',$arr))
			{
				$ifprice_filter=$this->_getParam('price_filter');
				if (isset($ifprice_filter)) {
					$url .= '/price_filter/' . $ifprice_filter."/";
				}
			}
			
			if(in_array('manu_filter',$arr))
			{
				$ifmanu_filter=$this->_getParam('manu_filter');
				if (isset($ifmanu_filter)) {
					$url .= '/manu_filter/' . $ifmanu_filter."/";
				}
			}
			
			if(in_array('option_filter',$arr))
			{
				$ifoption_filter=$this->_getParam('option_filter');
				if (isset($ifoption_filter)) {
					$url .= '/option_filter/' . $ifoption_filter."/";
				}
			}
			return $url;
		}
		
		public function postDispatch()
		{
			$currObj=new Model_currencies();
			$currObj->setCurrency($this->_getParam('curr'));
		}
		
		
        //public function categoryAction()
        public function ListAction()
		{
			$urlObj=new Model_Url('','');
			$urlParam=$urlObj->getUrlParams($this->_getAllParams());
			$this->path=$urlParam['path'];
			$this->psort=$urlParam['sort'];
			$this->porder=$urlParam['order'];
			$this->plimit=$urlParam['limit'];
			$this->ppage=$urlParam['page'];
			$this->poption_filter=$urlParam['option_filter'];
			$this->pmanu_filter=$urlParam['manu_filter'];
			$this->pprice_filter=$urlParam['price_filter'];
			
			//echo "value of ".$this->path;
			//exit;
			
			$this->view->qpopup=explode("*",@constant('PRODUCT_LISTING_QUICK_LINK_POPUP_WH'));
			
			//$this->page=3;
			/*start modules*/
			$moduleObj=new Model_Module();
			$this->view->pos=$moduleObj->getModules(array('page'=>3)); 
			/*end modules*/
			
			$catObj=new Model_Categories();
			$currObj=new Model_currencies();
			$currObj->setCurrency($this->_getParam('curr'));
			$this->view->curr=$currObj;
			//echo "value of ".$currObj->currencies[$_SESSION['Curr']['currency']]['value'];
			/*echo "<pre>";
                print_r($currObj);
                print_r($_SESSION['Curr']['currency']);
			echo "</pre>";*/
			$custObj=new Model_Customer();
			$this->view->logged = $custObj->isLogged();
			
			//$this->view->button_continue = $_SESSION['OBJ']['tr']->translate('button_continue');
			//$this->view->continue = HTTP_SERVER.'index/index';//$this->url->link('common/home');
			
			
			/*start view*/
			if(!isset($_SESSION['PRODUCT_LIST_VIEW']))
			{
				$_SESSION['PRODUCT_LIST_VIEW']=PRODUCT_LIST_DEFAULT_VIEW;
			}else if($this->_getParam('view_prod')!="")
			{
				$_SESSION['PRODUCT_LIST_VIEW']=$this->_getParam('view_prod');
				header("location:".$_SERVER['HTTP_REFERER']);
			}
			/*end view*/
			
			$ifsort=$this->psort;
			$sort=isset($ifsort)?$ifsort:'p.sort_order';
			
			$iforder=$this->porder;
			$order=isset($iforder)?$iforder:'ASC';
			
			
			$ifpage=$this->ppage;
			$page=isset($ifpage)?$ifpage:'1';
			
			$iflimit=$this->plimit;
			$limit=isset($iflimit)?$iflimit:MAX_ITEMS_PER_PAGE_CATALOG;
			
			
			$ifpath=$this->path;
			if (isset($ifpath)) {
				$path = '';
				$parts = explode('_', (string)$this->path);
				foreach ($parts as $path_id) {
					if (!$path) {
						$path = $path_id;
						} else {
						$path .= '_' . $path_id;
					}
					$category_info = $catObj->getCategory($path_id);
					if ($category_info) {
						$this->view->breadcrumbs[] = array(
   	    				'text'      => $category_info['categories_name'],
						//'href'      => HTTP_SERVER."product/category/path/".$path,
						'href'      => $urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$path), 'path/'.$path),
        				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
						);
					}
				}
				$category_id = array_pop($parts);
				} else {
				$category_id = 0;
			}
			
			$category_info = $catObj->getCategory($category_id);
			$this->getMetaTags(array('meta_title'=>'','meta_keywords'=>$category_info['meta_keywords'],'meta_description'=>$category_info['meta_description']));
			
			if ($category_info) {
				
				$this->view->cat_heading_title = $category_info['categories_name'];
				$imgSize=explode("*",IMAGE_C_LIST_SIZE);
				if($category_info['categories_image']!="")
				{
					$this->view->cat_thumb = PATH_TO_UPLOADS."categories/".$category_info['categories_image']."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
				}    
				/*$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));*/
				
				$this->view->cat_description = html_entity_decode($category_info['categories_description'], ENT_QUOTES, 'UTF-8');
				//$this->data['compare'] = $this->url->link('product/compare');
				
				$url=$this->getUrl(array('sort','order','limit','price_filter','manu_filter'));
				
				$this->view->refineCategories = array();
				
				$results = $catObj->getCategories($category_id);
				$prodObj=new Model_Products();
				
				foreach ($results as $result)
				{
					$cat_thumb=$result['categories_image']==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1":PATH_TO_UPLOADS."categories/".$result['categories_image']."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
					$product_total = $prodObj->getTotalProducts(array('filter_category_id' => $result['categories_id']));
					$this->view->refineCategories[] = array(
					'name'  => $result['categories_name'] . ' (' . $product_total . ')',
					'thumb'=>$cat_thumb,
					//'href'  => $this->url->link('product/category', 'path=' . $this->request->get['path'] . '_' . $result['category_id'] . $url)
					//'href'=> HTTP_SERVER."product/category/path/".$this->path.'_'.$result['categories_id'].$url
					//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.'_'.$result['categories_id'].$url)
					'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path.'_'.$result['categories_id']),"path/".$this->path.'_'.$result['categories_id'].$url)
					);
				}
				
				$this->view->products = array();
				
				$data = array(
				//'filter_name'=>'black',
				'option_filter'=>$this->poption_filter,
				'manu_filter'=>$this->pmanu_filter,
				'filter_category_id' => $category_id,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
				);
				
				$data['price_filter']=$this->pprice_filter;
				
				$product_total = $prodObj->getTotalProducts($data);
				$results = $prodObj->getProducts($data);
				/*echo "<pre>";
					print_r($results);
				exit;*/
				
				//echo "<pre>";
				foreach ($results as $result) {
					if ($result['image']) {
						//echo PATH_TO_UPLOADS."products/".$result['image'];
						//echo PATH_TO_UPLOADS_DIR."products\\".$result['image'];
						$imgSize=explode("*",IMAGE_P_LIST_SIZE);
						//echo "image size".$imgSize[0].",".$imgSize[1];
						//$imageObj=new Model_Image(PATH_TO_UPLOADS."products/".$result['image']);
						//$imageObj=new Model_Image('uploads/products/image_5_5023_prod_12.jpg');
						//$image = $imageObj->resize('uploads/products/image_5_5023_prod_12.jpg', '200', '200');
						//echo "start ".$image."final ";
						$image_avail=strpbrk($result['image'],'.');
						//echo "value of ".$res."<br/>";
						$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
						//$image=PATH_TO_UPLOADS."products/".$result['image']."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
						} else {
						//	$image = false;
						$imgSize=explode("*",IMAGE_P_LIST_SIZE);
						$image=PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
						//					$image =PATH_TO_UPLOADS."products/".constant(STORE_NO_IMAGE_ICON)."&w=".$imgRSize[0]."&h=".$imgRSize[1]."&zc=1";
					}
					//	echo "value of ".$image;
					//}
					$taxObj=new Model_Tax();
					
					$LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=="false"?"0":"1";
					if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
						//echo "value of ".$taxObj->calculate($result['price'], $result['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX)."<br>";
						$price = $currObj->format($taxObj->calculate($result['price'], $result['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
						//echo "value of ".$price;
						//exit;
						} else {
						
						$price = false;
					}
					
					//echo "value of ".$price;
					//exit;
					
					if ((float)$result['special']) {
						$special = $currObj->format($taxObj->calculate($result['special'], $result['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
						} else {
						$special = false;
					}
					
					if (constant(DISPLAY_PRICE_WITH_TAX)) {
						//$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);
						$tax = $currObj->format((float)$result['special'] ? $result['special'] : $result['price']);
						} else {
						$tax = false;
					}
					if (ALLOW_REVIEWS) {
						$rating = (int)$result['rating'];
						//$this->view->captcha=$this->captcha();
						} else {
						$rating = false;
					}
					
					//print_r($result);
					// echo "value of ".@constant('PRODUCT_LISTING_DESCRIPTION_LENGTH')."<br/>";        
					$attributes=@constant('PRODUCT_LISTING_ATTRIBUTES')=='true'?$prodObj->getProductAttributes($result['products_id']):'';
					$options=@constant('PRODUCT_LISTING_OPTIONS')=='true'?$prodObj->getProductOptions($result['products_id']):'';
					
					
					$this->view->products[] = array(
					'product_id'  => $result['products_id'],
					'thumb'       => $image,
					'name'        => $result['products_name'],
					'model'        => $result['products_model'],
					'attributes'=>$attributes,
					'options'=>$options,
					'quantity'        => $result['products_quantity'],
					'stock_status'        => $result['stock_status_id'],
					'manufacturer'        => $result['manufacturers_name'],
					'reward'        => $result['reward'],
					'points'        => $result['points'],
					'products_minimum_quantity'        => $result['products_minimum_quantity'],
					'products_viewed'        => $result['products_viewed'],
					'name_full'        => $result['products_name_full'],
					'description' => substr(strip_tags(html_entity_decode($result['products_description'], ENT_QUOTES, 'UTF-8')), 0, @constant('PRODUCT_LISTING_DESCRIPTION_LENGTH')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $result['rating'],
					'reviews'     => sprintf($_SESSION['OBJ']['tr']->translate('text_reviews_product_product'), (int)$result['reviews']),
					//'href'        => HTTP_SERVER."product/product-details/path/".$this->path."/product_id/".$result['products_id'] //$this->url->link('product/product', 'path=' . $this->path . '&product_id=' . $result['product_id'])
					'href' => $urlObj->getLink(array("controller"=>"product","action"=>"product-details","path"=>$this->path,"product_id"=>$result['products_id']), "path/".$this->path."/product_id/".$result['products_id'])
					);
				}
				//echo "</pre>";
				
				
				$url=$this->getUrl(array('limit','price_filter','manu_filter','option_filter'));
				
				$this->view->sorts = array();
				
				$this->view->sorts[] = array(
				'text'  => $_SESSION['OBJ']['tr']->translate('text_default_product_category'),
				'value' => 'p.sort_order-ASC',
				//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/p.sort_order/order/ASC/".$url)
				'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/p.sort_order/order/ASC/".$url)
				);
				
				$this->view->sorts[] = array(
				'text'  => $_SESSION['OBJ']['tr']->translate('text_name_asc_product_category'),
				'value' => 'pd.products_name-ASC',
				//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/pd.products_name/order/ASC/".$url)
				'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/pd.products_name/order/ASC/".$url)
				);
				
				$this->view->sorts[] = array(
				'text'  => $_SESSION['OBJ']['tr']->translate('text_name_desc_product_category'),
				'value' => 'pd.products_name-DESC',
				//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/pd.products_name/order/DESC/".$url)
				'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/pd.products_name/order/DESC/".$url)
				);
				
				$this->view->sorts[] = array(
				'text'  => $_SESSION['OBJ']['tr']->translate('text_price_asc_product_category'),
				'value' => 'p.products_price-ASC',
				//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/p.products_price/order/ASC/".$url)
				'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/p.products_price/order/ASC/".$url)
				);
				
				$this->view->sorts[] = array(
				'text'  => $_SESSION['OBJ']['tr']->translate('text_price_desc_product_category'),
				'value' => 'p.products_price-DESC',
				//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/p.products_price/order/DESC/".$url)
				'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/p.products_price/order/DESC/".$url)
				);
				
				$this->view->sorts[] = array(
				'text'  => $_SESSION['OBJ']['tr']->translate('text_rating_desc_product_category'),
				'value' => 'rating-DESC',
				//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/rating/order/DESC/".$url)
				'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/rating/order/DESC/".$url)
				
				);
				
				$this->view->sorts[] = array(
				'text'  => $_SESSION['OBJ']['tr']->translate('text_rating_asc_product_category'),
				'value' => 'rating-ASC',
				
				//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/rating/order/ASC/".$url)
				'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/rating/order/ASC/".$url)
				);
				
				$this->view->sorts[] = array(
				'text'  => $_SESSION['OBJ']['tr']->translate('text_model_asc_product_category'),
				'value' => 'p.products_model-ASC',
				//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/p.products_model/order/ASC/".$url)
				'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/p.products_model/order/ASC/".$url)
				);
				
				$this->view->sorts[] = array(
				'text'  => $_SESSION['OBJ']['tr']->translate('text_model_desc_product_category'),
				'value' => 'p.products_model-DESC',
				//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/p.products_model/order/DESC/".$url)
				'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/p.products_model/order/DESC/".$url)
				//				'href'  => $this->url->link('product/category', 'path=' . $this->path. '&sort=p.model&order=DESC' . $url)
				);
				
				
				$url=$this->getUrl(array('sort','order','price_filter','manu_filter','option_filter'));
				
				
				$this->view->limits = array();
				
				$this->view->limits[] = array(
				'text'  => MAX_ITEMS_PER_PAGE_CATALOG,
				'value' => MAX_ITEMS_PER_PAGE_CATALOG,
				//			'href'=>HTTP_SERVER."product/category/path/".$this->path.$url."/limit/".MAX_ITEMS_PER_PAGE_CATALOG
				//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.$url."/limit/".MAX_ITEMS_PER_PAGE_CATALOG)
				'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url."/limit/".MAX_ITEMS_PER_PAGE_CATALOG)
				
				);
				
				
				//echo "value of url ".$url;
				if(PRODUCT_LIST_SHOW_LIMIT!="")
				{
					$show_exp=explode(",",PRODUCT_LIST_SHOW_LIMIT);
					foreach($show_exp as $k=>$v)
					{
						$this->view->limits[] = array(
						'text'  => $v,
						'value' => $v,
						//	'href'=>HTTP_SERVER."product/category/path/".$this->path.$url."/limit/".$v
						//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.$url."/limit/".$v)
						'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url."/limit/".$v)
						);
						//echo 	"<br/>".HTTP_SERVER."product/category/path/".$this->path.$url."/limit/".$v;
					}
				}
				
				/*start filters*/
				if($category_info[filters]!="")
				{
					$selected_array=array();
					$this->view->global_filters=array();
					$exp_filter=explode("&",$category_info[filters]);
					
					$arr_filters=array();
					$filterContainer=array();
					sizeof($exp_filter);
					foreach($exp_filter as $k)
					{
						$exp=explode("#",$k);
						//$arr_filters[$exp[0]]=$exp[1];
						$arr_filters_sort[$exp[1]."-".$exp[0]]=$exp[0];
					}
					
					ksort($arr_filters_sort);//sorts an array by key
					foreach($arr_filters_sort as $k=>$v)
					{
						if($v=='p')
						{
							$ifprice_filter=$this->pprice_filter;
							//$this->view->price_filter_selected=$this->pprice_filter;
							
							$this->view->filter_option_selected=$ifprice_filter==""?array("-1"=>'price'):array("-1"=>$ifprice_filter);
							$price_limit = $prodObj->getProductsPriceLimits($data);
							
							if($price_limit['max_price']!="") //if no products then display null price block
							{
								$diff_price=round($price_limit['max_price']-$price_limit['min_price'])/5;
								$arrprice=array();
								$arrprice[]=$price_limit['min_price'];
								$between_price=$price_limit['min_price'];
								for($i=0;$i<5;$i++)
								{
									$between_price=$between_price+$diff_price;
									$arrprice[]=floor($between_price);
								}
								
								$url=$this->getUrl(array('sort','order','manu_filter','limit','option_filter'));
								
								$this->view->price_filter = array();
								/*$this->view->price_filter[] = array(
									'text'  => 'Select Price',
									'value' => '',
									//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.$url)
									'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url)
								);*/
								for($i=0;$i<5;$i++)
								{
									$this->view->price_filter[] = array(
									//'text'  => $arrprice[$i].'-'.$arrprice[$i+1],
									'text'  => ($arrprice[$i]*$currObj->currencies[$_SESSION['Curr']['currency']]['value']).'-'.($currObj->currencies[$_SESSION['Curr']['currency']]['value']*$arrprice[$i+1]),
									
									'value' => 'between '.$arrprice[$i].' and '.$arrprice[$i+1],
									//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/price_filter/between ".$arrprice[$i]." and ".$arrprice[$i+1].$url)
									'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/price_filter/between ".$arrprice[$i]." and ".$arrprice[$i+1].$url)
									);
									
								}
								$this->view->price_filter[] = array(
								'text'  => 'Clear',
								'value' => '',
								
								//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.$url)
								'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url)
								);
								$this->view->global_filters['Prices']=$this->view->price_filter;
							}else
							{
								$this->view->global_filters['Prices']="";
							}
							
							
						}else if($v=='m')
						{
							$manuObj=new Model_Manufacturer();
							$manu_info=$manuObj->getManufacturersByCategory(array("path"=>$path_id));
							if(sizeof($manu_info)!=0)
							{
								$url=$this->getUrl(array('sort','order','price_filter','limit','option_filter'));
								
								$this->view->manu_filter=array();
								$ifmanu_filter=$this->pmanu_filter;
								
								//$this->view->filter_option_selected=array("-2"=>$ifmanu_filter);
								$this->view->filter_option_selected=$ifmanu_filter==""?array("-2"=>'manu'):array("-2"=>$ifmanu_filter);
								
								
								/*$this->view->manu_filter[]= array(
									'text'  => $_SESSION['OBJ']['tr']->translate('text_select'),
									'value' => '',
									//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/".$url)
									'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url)
								);*/
								foreach($manu_info as $manu)
								{
									$this->view->manu_filter[]= array(
									'text'  => $manu['manufacturers_name'],
									'value' => $manu['manufacturers_id'],
									//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/manu_filter/".$manu['manufacturers_id'].$url)
									'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/manu_filter/".$manu['manufacturers_id'].$url)
									);
								}
								$this->view->manu_filter[] = array(
								'text'  => 'Clear',
								'value' => '',
								//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.$url)
								'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url)
								);
								$this->view->global_filters['Manufacturers']=$this->view->manu_filter;
							}else
							{
								$this->view->global_filters['Manufacturers']="";
							}
						}else
						{
							//	echo "value of option ".$v;
							$filter_option_name=$prodObj->getOption($v);
							$url=$this->getUrl(array('sort','order','price_filter','limit','manu_filter'));
							$this->view->filter_option_value=array();
							$this->view->filter_option_value=$prodObj->getOptionValue($v,array("path"=>$ifpath,"option_filter"=>$this->poption_filter,"url"=>$url));
							$this->view->global_filters[$filter_option_name]=$this->view->filter_option_value;
							$ifoption_filter=$this->poption_filter;
							if(isset($ifoption_filter))
							{
								$res=strstr($ifoption_filter,",");
								if($res!="")
								{
									$this->view->filter_option_selected=explode(",",$ifoption_filter);
									
								}else
								{
									$this->view->filter_option_selected=array($ifoption_filter);
								}
								
							}
							
						}
						$selected_array=@array_merge($selected_array,$this->view->filter_option_selected); //got warnings while manu and price or not present so ketp @
						
						
					}
					$this->view->filter_option_selected=@array_unique($selected_array);//got warnings while manu and price or not present so ketp @
				}
				
				/*end filters*/
				
				$url=$this->getUrl(array('sort','order','manu_filter','limit','price_filter','option_filter'));//option_fitler added extra
				
				//echo "<br/>url ".$url;
				/*start pagination*/
				$pagination = new Model_FrontPagination();
				$pagination->total = $product_total;
				$pagination->page = $page;
				$pagination->limit = $limit;
				$pagination->text =$_SESSION['OBJ']['tr']->translate('text_pagination');
				// $this->language->get('text_pagination');
				$UrlObj=new Model_Url('','');
				//$pagination->url = $UrlObj->link('product/category', 'path/' . $this->path . $url . '/page/{page}');
				//$pagination->url =HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.$url. '/page/{page}');
				$pagination->url=$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url. '/page/{page}');
				//HTTP_SERVER.'product/category/path/' . $this->path."/". $url . 'page/{page}';
				$this->view->pagination = $pagination->render();
				/*end pagination*/
				
				$this->view->sort = $sort;
				$this->view->order = $order;
				$this->view->limit = $limit;
				
				//$this->view->continue = $this->url->link('common/home');
				$this->view->continue = HTTP_SERVER."index/index";
				
			} else
			{
				//echo "here in else";
				$url = '';
				
				$ifpath=$this->path;
				if (isset($ifpath)) {
					$url .= '&path=' . $this->path;
				}
				
				$ifsort=$this->psort;
				if (isset($ifsort)) {
					$url .= '&sort=' . $this->psort;
				}
				$iforder=$this->porder;
				if (isset($iforder)) {
					$url .= '&order=' . $this->porder;
				}
				
				$ifpage=$this->ppage;
				if (isset($ifpage)) {
					$url .= '&page=' . $this->ppage;
				}
				
				$iflimit=$this->plimit;
				if (isset($iflimit)) {
					$url .= '&limit=' . $this->plimit;
				}
				
				$this->data['breadcrumbs'][] = array(
				'text'      => $_SESSION['OBJ']['tr']->translate('text_error'),
				'href'      => HTTP_SERVER."product/category",
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
				);
				
				//	$this->document->setTitle($_SESSION['OBJ']['tr']->translate('text_error'));
				
				//$this->view->heading_title = $_SESSION['OBJ']['tr']->translate('text_error_product');
				
				//$this->view->text_error = $_SESSION['OBJ']['tr']->translate('text_error_product');
				
				//$this->view->button_continue =$_SESSION['OBJ']['tr']->translate('button_continue');
				
				//$this->view->continue = HTTP_SERVER."index/index";
				
			}
			/*$view=new Zend_View();
			$view->setScriptPath(APPLICATION_PATH . '/views/scripts/templates/maroon/product')->render('category.phtml');*/
			
			/*$views = new Zend_View;
				$views->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/maroon/product/');
			$views->render('category.phtml');*/
			//$view->render("generic.phtml");
			//$this->_helper->viewRenderer('category', null, true);
			
			//$view1->render('category');
			/*$view=new Zend_View();
				$view->setScriptPath ('../../maroon/product/');
			$this->render('category');*/
			//echo "vale of".$this->view->getScriptPath('category');
			/*		$views = new Zend_View();
				$views->setViewScriptPathNoControllerSpec(APPLICATION_PATH . '/views/scripts/templates/maroon/product/');
			$views->render('category.phtml');*/
			//$this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/maroon/product/category');
			//$this->render('category');
			//$this->_helper->viewRenderer->setRender(APPLICATION_PATH . '/views/scripts/templates/maroon/product/category');
			//$view = $bootstrap->bootstrap('View')->getResource('View');
			//$view->setBasePath(APPLICATION_PATH . '/views/' . $theme->foldername);
			//echo $view->getBasePath();
			/**ADD THIS BELOW HERE **/
			///$view = new Zend_View();
			//$this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/maroon/product/');
			//$this->renderScript('category.phtml');
			if (file_exists(PATH_TO_FILES.'product/category.phtml'))
			{
				$this->view->addScriptPath(PATH_TO_FILES.'product/');
				$this->renderScript('category.phtml');
			}else 
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/product/category.phtml'))
            {
                //$this->render('category');
			} else
            {   
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/product/');
                $this->renderScript('category.phtml');
			}
		}
		
		public function specialAction()
		{
			//$this->page=2;
     		/*start modules*/
			$moduleObj=new Model_Module();
			$this->view->pos=$moduleObj->getModules(array('page'=>2));
			/*end modules*/
			$currObj=new Model_currencies();
			$currObj->setCurrency($this->_getParam('curr'));
			$this->view->curr=$currObj;
			$this->getMetaTags(array());
			//$this->view->button_continue = $_SESSION['OBJ']['tr']->translate('button_continue');
			//$this->view->continue = HTTP_SERVER.'index/index';//$this->url->link('common/home');
			
			/*start view*/
			if(!isset($_SESSION['PRODUCT_LIST_VIEW']))
			{
				$_SESSION['PRODUCT_LIST_VIEW']=PRODUCT_LIST_DEFAULT_VIEW;
			}else if($this->_getParam('view_prod')!="")
			{
				$_SESSION['PRODUCT_LIST_VIEW']=$this->_getParam('view_prod');
				header("location:".$_SERVER['HTTP_REFERER']);
			}
			/*end view*/
			
			
			$ifsort=$this->_getParam('sort');
			$sort=isset($ifsort)?$ifsort:'p.sort_order';
			
			$iforder=$this->_getParam('order');
			$order=isset($iforder)?$iforder:'ASC';
			
			
			$ifpage=$this->_getParam('page');
			$page=isset($ifpage)?$ifpage:'1';
			
			$iflimit=$this->_getParam('limit');
			$limit=isset($iflimit)?$iflimit:MAX_ITEMS_PER_PAGE_CATALOG;
			
			
			$this->view->breadcrumbs[] = array(
       		'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_product_special'),
			'href'      => HTTP_SERVER."product/special",
       		'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
			);
			
			
			$url=$this->getUrl(array('sort','order','limit','price_filter','manu_filter'));
			
			$prodObj=new Model_Products();
			
			$this->view->products = array();
			
			$data = array(
			//'filter_name'=>'black',
			'option_filter'=>$this->_getParam('option_filter'),
			'manu_filter'=>$this->_getParam('manu_filter'),
			'filter_category_id' => $category_id,
			'sort'               => $sort,
			'order'              => $order,
			'start'              => ($page - 1) * $limit,
			'limit'              => $limit
			);
			
			$data['price_filter']=$this->_getParam('price_filter');
			$product_total = $prodObj->getTotalProductSpecials($data);
			$results = $prodObj->getProductSpecials($data);
			
			foreach ($results as $result) {
				if ($result['image']) {
					$imgSize=explode("*",IMAGE_P_LIST_SIZE);
					$image_avail=strpbrk($result['image'],'.');
					$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
					} else {
					$image = false;
				}
				
				$taxObj=new Model_Tax();
				
				$LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=="false"?"0":"1";
				if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
 					$price = $currObj->format($taxObj->calculate($result['price'], $result['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
					} else {
 					$price = false;
				}
				
				if ((float)$result['special']) {
					$special = $currObj->format($taxObj->calculate($result['special'], $result['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
					} else {
					$special = false;
				}
				
				if (constant(DISPLAY_PRICE_WITH_TAX)) {
					//$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);
 					$tax = $currObj->format((float)$result['special'] ? $result['special'] : $result['price']);
					} else {
					$tax = false;
				}
 				if (ALLOW_REVIEWS) {
					$rating = (int)$result['rating'];
					//$this->view->captcha=$this->captcha();
					} else {
					$rating = false;
				}
				
				$this->view->products[] = array(
				'product_id'  => $result['products_id'],
				'thumb'       => $image,
				'name'        => $result['products_name'],
				'description' => substr(strip_tags(html_entity_decode($result['products_description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
				'price'       => $price,
				'special'     => $special,
				'tax'         => $tax,
				'rating'      => $result['rating'],
				'reviews'     => sprintf($_SESSION['OBJ']['tr']->translate('text_reviews'), (int)$result['reviews']),
				'href'        => HTTP_SERVER."product/product-details/path/".$this->_getParam('path')."/product_id/".$result['products_id'] //$this->url->link('product/product', 'path=' . $this->_getParam('path') . '&product_id=' . $result['product_id'])
				);
			}
			
			
			$url=$this->getUrl(array('limit','price_filter','manu_filter','option_filter'));
			
			$this->view->sorts = array();
			
			$this->view->sorts[] = array(
			'text'  => $_SESSION['OBJ']['tr']->translate('text_default_product_category'),
			'value' => 'p.sort_order-ASC',
			'href'  =>HTTP_SERVER.str_replace("//","/","product/special/sort/p.sort_order/order/ASC/".$url)
			
			);
			
			$this->view->sorts[] = array(
			'text'  => $_SESSION['OBJ']['tr']->translate('text_name_asc_product_category'),
			'value' => 'pd.products_name-ASC',
			'href'  =>HTTP_SERVER.str_replace("//","/","product/special/sort/pd.products_name/order/ASC/".$url)
			);
			
			$this->view->sorts[] = array(
			'text'  => $_SESSION['OBJ']['tr']->translate('text_name_desc_product_category'),
			'value' => 'pd.products_name-DESC',
			'href'  =>HTTP_SERVER.str_replace("//","/","product/special/sort/pd.products_name/order/DESC/".$url)
			);
			
			$this->view->sorts[] = array(
			'text'  => $_SESSION['OBJ']['tr']->translate('text_price_asc_product_category'),
			'value' => 'ps.specials_new_products_price-ASC',
			'href'  =>HTTP_SERVER.str_replace("//","/","product/special/sort/ps.specials_new_products_price/order/ASC/".$url)
			);
			
			$this->view->sorts[] = array(
			'text'  => $_SESSION['OBJ']['tr']->translate('text_price_desc_product_category'),
			'value' => 'ps.specials_new_products_price-DESC',
			'href'  =>HTTP_SERVER.str_replace("//","/","product/special/sort/ps.specials_new_products_price/order/DESC/".$url)
			);
			
			$this->view->sorts[] = array(
			'text'  => $_SESSION['OBJ']['tr']->translate('text_rating_desc_product_category'),
			'value' => 'rating-DESC',
			'href'  =>HTTP_SERVER.str_replace("//","/","product/special/sort/rating/order/DESC/".$url)
			);
			
			$this->view->sorts[] = array(
			'text'  => $_SESSION['OBJ']['tr']->translate('text_rating_asc_product_category'),
			'value' => 'rating-ASC',
			
			'href'  =>HTTP_SERVER.str_replace("//","/","product/special/sort/rating/order/ASC/".$url)
			);
			
			$this->view->sorts[] = array(
			'text'  => $_SESSION['OBJ']['tr']->translate('text_model_asc_product_category'),
			'value' => 'p.products_model-ASC',
			'href'  =>HTTP_SERVER.str_replace("//","/","product/special/sort/p.products_model/order/ASC/".$url)
			);
			
			$this->view->sorts[] = array(
			'text'  => $_SESSION['OBJ']['tr']->translate('text_model_desc_product_category'),
			'value' => 'p.products_model-DESC',
			'href'  =>HTTP_SERVER.str_replace("//","/","product/special/sort/p.products_model/order/DESC/".$url)
			//				'href'  => $this->url->link('product/category', 'path=' . $this->_getParam('path'). '&sort=p.model&order=DESC' . $url)
			);
			
			
			$url=$this->getUrl(array('sort','order','price_filter','manu_filter','option_filter'));
			
			
			$this->view->limits = array();
			
			$this->view->limits[] = array(
			'text'  => MAX_ITEMS_PER_PAGE_CATALOG,
			'value' => MAX_ITEMS_PER_PAGE_CATALOG,
			//			'href'=>HTTP_SERVER."product/category/path/".$this->_getParam('path').$url."/limit/".MAX_ITEMS_PER_PAGE_CATALOG
			'href'  =>HTTP_SERVER.str_replace("//","/","product/special/".$url."/limit/".MAX_ITEMS_PER_PAGE_CATALOG)
			);
			
			
			//echo "value of url ".$url;
			if(PRODUCT_LIST_SHOW_LIMIT!="")
			{
				$show_exp=explode(",",PRODUCT_LIST_SHOW_LIMIT);
				foreach($show_exp as $k=>$v)
				{
					$this->view->limits[] = array(
					'text'  => $v,
					'value' => $v,
					//	'href'=>HTTP_SERVER."product/category/path/".$this->_getParam('path').$url."/limit/".$v
					'href'  =>HTTP_SERVER.str_replace("//","/","product/special/".$url."/limit/".$v)
					);
					//echo 	"<br/>".HTTP_SERVER."product/category/path/".$this->_getParam('path').$url."/limit/".$v;
				}
			}
			
			/*start filters*/
 			if($category_info[filters]!="")
			{
				$selected_array=array();
				$this->view->global_filters=array();
				$exp_filter=explode("&",$category_info[filters]);
				
				$arr_filters=array();
				$filterContainer=array();
				sizeof($exp_filter);
				foreach($exp_filter as $k)
				{
					$exp=explode("#",$k);
					//$arr_filters[$exp[0]]=$exp[1];
					$arr_filters_sort[$exp[1]."-".$exp[0]]=$exp[0];
				}
				
				ksort($arr_filters_sort);//sorts an array by key
				foreach($arr_filters_sort as $k=>$v)
				{
					if($v=='p')
					{
						$ifprice_filter=$this->_getParam('price_filter');
						//$this->view->price_filter_selected=$this->_getParam('price_filter');
						
						$this->view->filter_option_selected=$ifprice_filter==""?array("-1"=>'price'):array("-1"=>$ifprice_filter);
						$price_limit = $prodObj->getProductsPriceLimits($data);
						
						if($price_limit['max_price']!="") //if no products then display null price block
						{
							$diff_price=round($price_limit['max_price']-$price_limit['min_price'])/5;
							$arrprice=array();
							$arrprice[]=$price_limit['min_price'];
							$between_price=$price_limit['min_price'];
							for($i=0;$i<5;$i++)
							{
								$between_price=$between_price+$diff_price;
								$arrprice[]=$between_price;
							}
							
							$url=$this->getUrl(array('sort','order','manu_filter','limit','option_filter'));
							
							$this->view->price_filter = array();
							/*$this->view->price_filter[] = array(
								'text'  => 'Select Price',
								'value' => '',
								'href'  =>HTTP_SERVER.str_replace("//","/","product/special/".$url)
							);*/
							for($i=0;$i<5;$i++)
							{
								$this->view->price_filter[] = array(
								'text'  => $arrprice[$i].'-'.$arrprice[$i+1],
								'value' => 'between '.$arrprice[$i].' and '.$arrprice[$i+1],
								'href'  =>HTTP_SERVER.str_replace("//","/","product/special/price_filter/between ".$arrprice[$i]." and ".$arrprice[$i+1].$url)
								);
							}
							$this->view->price_filter[] = array(
							'text'  => 'Clear',
							'value' => '',
							
							'href'  =>HTTP_SERVER.str_replace("//","/","product/special/".$url)
							);
							$this->view->global_filters['Prices']=$this->view->price_filter;
						}else
						{
							$this->view->global_filters['Prices']="";
						}
						
						
					}else if($v=='m')
					{
						$manuObj=new Model_Manufacturer();
						$manu_info=$manuObj->getManufacturersByCategory(array("path"=>$path_id));
						if(sizeof($manu_info)!=0)
						{
							$url=$this->getUrl(array('sort','order','price_filter','limit','option_filter'));
							
							$this->view->manu_filter=array();
							$ifmanu_filter=$this->_getParam('manu_filter');
							
							//$this->view->filter_option_selected=array("-2"=>$ifmanu_filter);
							$this->view->filter_option_selected=$ifmanu_filter==""?array("-2"=>'manu'):array("-2"=>$ifmanu_filter);
							
							
							/*$this->view->manu_filter[]= array(
								'text'  => $_SESSION['OBJ']['tr']->translate('text_select'),
								'value' => '',
								'href'  =>HTTP_SERVER.str_replace("//","/","product/special/".$url)
							);*/
							foreach($manu_info as $manu)
							{
								$this->view->manu_filter[]= array(
								'text'  => $manu['manufacturers_name'],
								'value' => $manu['manufacturers_id'],
								'href'  =>HTTP_SERVER.str_replace("//","/","product/special/manu_filter/".$manu['manufacturers_id'].$url)
								);
							}
							$this->view->manu_filter[] = array(
							'text'  => 'Clear',
							'value' => '',
							'href'  =>HTTP_SERVER.str_replace("//","/","product/special/".$url)
							);
							$this->view->global_filters['Manufacturers']=$this->view->manu_filter;
						}else
						{
							$this->view->global_filters['Manufacturers']="";
						}
					}else
					{
						//	echo "value of option ".$v;
						$filter_option_name=$prodObj->getOption($v);
						$url=$this->getUrl(array('sort','order','price_filter','limit','manu_filter'));
						$this->view->filter_option_value=array();
						$this->view->filter_option_value=$prodObj->getOptionValue($v,array("path"=>$ifpath,"option_filter"=>$this->_getParam('option_filter'),"url"=>$url));
						$this->view->global_filters[$filter_option_name]=$this->view->filter_option_value;
						$ifoption_filter=$this->_getParam('option_filter');
						if(isset($ifoption_filter))
						{
							$res=strstr($ifoption_filter,",");
							if($res!="")
							{
								$this->view->filter_option_selected=explode(",",$ifoption_filter);
								
							}else
							{
								$this->view->filter_option_selected=array($ifoption_filter);
							}
						}
					}
					$selected_array=@array_merge($selected_array,$this->view->filter_option_selected); //got warnings while manu and price or not present so ketp @
				}
				$this->view->filter_option_selected=@array_unique($selected_array);//got warnings while manu and price or not present so ketp @
			}
 			/*end filters*/
			$url=$this->getUrl(array('sort','order','manu_filter','limit','price_filter','option_filter'));//option_fitler added extra
			$this->view->qpopup=explode("*",@constant('PRODUCT_LISTING_QUICK_LINK_POPUP_WH'));
			//echo "<br/>url ".$url;
			/*start pagination*/
			$pagination = new Model_FrontPagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->text =$_SESSION['OBJ']['tr']->translate('text_pagination');
			// $this->language->get('text_pagination');
			$UrlObj=new Model_Url('','');
			//$pagination->url = $UrlObj->link('product/category', 'path/' . $this->_getParam('path') . $url . '/page/{page}');
			$pagination->url =HTTP_SERVER.str_replace("//","/","product/special/".$url. '/page/{page}');
			//HTTP_SERVER.'product/category/path/' . $this->_getParam('path')."/". $url . 'page/{page}';
			$this->view->pagination = $pagination->render();
			/*end pagination*/
			
			$this->view->sort = $sort;
			$this->view->order = $order;
			$this->view->limit = $limit;
			
			//$this->view->continue = $this->url->link('common/home');
			//$this->view->continue = HTTP_SERVER."index/index";
			//	$this->render('category');
			if (file_exists(PATH_TO_FILES.'product/special.phtml'))
			{
				$this->view->addScriptPath(PATH_TO_FILES.'product/');
				$this->renderScript('special.phtml');
			}else 
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/product/special.phtml'))
            {
                $this->render('special');
			} else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/product/');
                $this->renderScript('special.phtml');
			}
		}
		
        public function productDetailsAction()
		{
			$urlObj=new Model_Url('','');
			
			$urlParam=$urlObj->getUrlParams($this->_getAllParams());
			$this->path=$urlParam['path'];
			$this->product_id=$urlParam['product_id'];
			
			//$this->page='2';
			/*start modules*/
			$moduleObj=new Model_Module();
			$this->view->pos=$moduleObj->getModules(array('page'=>2));
			/*end modules*/
			$currObj=new Model_currencies();
			$currObj->setCurrency($this->_getParam('curr'));
			$this->view->curr=$currObj;
			$catObj=new Model_Categories();
			
			/*start modules*/
			$moduleObj=new Model_Module();
			$this->view->pos=$moduleObj->getModules(array('page'=>'2')); //refers to category page as per r_layout
			/*end modules*/
			
			$ifpath=$this->path;
			if (isset($ifpath)) {
				$path = '';
				
				foreach (explode('_', $this->path) as $path_id) {
					if (!$path) {
						$path = $path_id;
						} else {
						$path .= '_' . $path_id;
					}
					
					$category_info = $catObj->getCategory($path_id);
					if ($category_info) {
						$this->view->breadcrumbs[] = array(
						'text'      => $category_info['categories_name'],
						//'href'      => HTTP_SERVER."product/category/path/".$path,
						'href'      => $urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$path), 'path/'.$path),
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
			
			$ifproduct_id=$this->product_id;
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
			/*echo "<pre>";
                print_r($product_info);
			echo "</pre>";*/    
			if ($product_info[products_id]) {
				$url = '';
				
				$ifpath=$this->path;
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
				
				//echo "<br/>url".$url."<br/>";
				$this->view->breadcrumbs[] = array(
				'text'      => $product_info['products_name_full'],
				//'href'      => HTTP_SERVER."product/product".$url . '/product_id/' . $this->product_id,
				//'href'      => $urlObj->getLink(array("controller"=>"product","action"=>"product-details","path"=>$path,"product_id"=>$this->product_id), $url . '/product_id/' . $this->product_id),
				'href' => $urlObj->getLink(array("controller"=>"product","action"=>"product-details","path"=>$this->path,"product_id"=>$this->product_id), $url . '/product_id/' . $this->product_id),
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
				);
				
				/*$this->view->meta_title=$product_info['products_name'];
					$this->view->meta_description=$product_info['meta_description'];
				$this->view->meta_keyword=$product_info['meta_keyword'];*/
				$this->view->heading_title = $product_info['products_name_full'];
				$this->view->thumb = $product_info['image'];
				$this->view->products_date_added = $product_info['products_date_added'];
				$this->view->products_viewed = $product_info['products_viewed'];
				
				$reviewObj=new Model_Review();
				$this->view->tab_review = sprintf($_SESSION['OBJ']['tr']->translate('tab_review_product_product'), $reviewObj->getTotalReviewsByProductId($this->product_id));
				
				$this->view->product_id = $this->product_id;
				$this->view->manufacturer = $product_info['manufacturers_name'];
				$this->view->manufacturers = HTTP_SERVER.'product/manufacturer/product/manufacturer_id/' . $product_info['manufacturers_id'];
				$this->view->model = $product_info['products_model'];
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
					
					$this->view->stock = $prodObj->getStockStatus(constant('DEFAULT_AVAILABILITY_STOCK_STATUS_ID'));
					
					//$this->view->stock = $_SESSION['OBJ']['tr']->translate('text_instock');
				}
				
				//$this->load->model('tool/image');
				//$imageObj=new Model_Image();
				//echo $this->getRequest()->getParam('path');//print_r($this->_request->getQuery());
				
				
				if ($product_info['image']) {
					$imgPSize=explode("*",IMAGE_P_POPUP_SIZE);
					//$this->view->popup=PATH_TO_UPLOADS."products/".$product_info['image']."&w=".$imgPSize[0]."&h=".$imgPSize[1]."&zc=1";
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
				
				$results = $prodObj->getProductImages($this->product_id);
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
				$custObj=new Model_Customer();
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
				
				
				$discounts = $prodObj->getProductDiscounts($this->product_id);
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
				
				foreach ($prodObj->getProductOptions($this->product_id) as $option) {
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
						'dependent_option'         => $option['dependent_option'], //for dependent options sep 12 2012
						'child'         => $option['child'],  //for dependent options sep 12 2012		
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
				
				$this->view->text_minimum = sprintf($_SESSION['OBJ']['tr']->translate('text_minimum_product_product'), $product_info['products_minimum_quantity']);
				$this->view->review_status = constant(ALLOW_REVIEWS)==false?'0':'1';
				$this->view->reviews = sprintf($_SESSION['OBJ']['tr']->translate('text_reviews_product_product'), (int)$product_info['reviews']);
				$this->view->rating = (int)$product_info['rating'];
				$this->view->description = html_entity_decode($product_info['products_description'], ENT_QUOTES, 'UTF-8');
				$this->view->attribute_groups = $prodObj->getProductAttributes($this->product_id);
				
				$this->view->products = array();
				
				$results = $prodObj->getProductRelated($this->product_id);
				
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
				
				$results = $prodObj->getProductTags($this->product_id);
				
				foreach ($results as $result) {
					$this->view->tags[] = array(
					'tag'  => $result['tag'],
					'href' => HTTP_SERVER."product/search/filter_tag/".$result['tag']);
				}
				
				$prodObj->updateViewed($this->product_id);
				if (file_exists(PATH_TO_FILES.'product/product-details.phtml'))
				{
					$this->view->addScriptPath(PATH_TO_FILES.'product/');
					$this->renderScript('product-details.phtml');
				}else 
				if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/product/product-details.phtml'))
				{
					
					$this->render('product-details');
				} else
				{
					
					$this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/product/');
					$this->renderScript('product-details.phtml');
				}
				
				} else {
				
				$this->data['breadcrumbs'][] = array(
        		'text'      => $_SESSION['OBJ']['tr']->translate('text_home_common_header'),
				'href'      => HTTP_SERVER,
				'separator' =>'' );
                
				$this->data['breadcrumbs'][] = array(
        		'text'      => $_SESSION['OBJ']['tr']->translate('text_error_product_product'),
				'href'      => "#",
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator'));
				$this->data['text_error']=$_SESSION['OBJ']['tr']->translate('text_error_product_product');               
				//$this->document->setTitle($this->language->get('text_error'));
                $this->view->data=$this->data;
				if (file_exists(PATH_TO_FILES.'account/not-found.phtml'))
				{
                    $this->view->addScriptPath(PATH_TO_FILES.'account/');
                    $this->renderScript('not-found.phtml');
				}else 
				if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/not-found.phtml'))
				{
					$this->renderScript('account/not-found.phtml');
				} else
				{
					$this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
					$this->renderScript('not-found.phtml');
				}
			}
			Model_Cart::recentlyViewedProducts($this->product_id);
		}
        public function manufacturerAction()
        {
            $urlParam=$this->_getAllParams();
            $manObj=new Model_Manufacturer();
            if($urlParam['manu_filter']=="") //list of brands
            {
                $results=$manObj->getManufacturers();
                
                $this->data['categories'] = array();
                
                $this->view->breadcrumbs[] = array(
                'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_module_manufacturer'),
                'href'=>$this->view->url_to_site.'product/manufacturer',
                'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
                );
                
				foreach ($results as $result) 
                {
					if (is_numeric(substr($result['manufacturers_name'], 0, 1))) {
						$key = '0 - 9';
						} else {
						$key = substr(strtoupper($result['manufacturers_name']), 0, 1);
					}
					
					if (!isset($this->data['manufacturers'][$key])) {
						$this->data['categories'][$key]['name'] = $key;
					}
					
					$this->data['categories'][$key]['manufacturer'][] = array(
					'name' => $result['manufacturers_name'],
					//'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $result['manufacturer_id'])
					'href'=>$this->view->url_to_site."product/manufacturer/manu_filter/".$result['manufacturers_id']
					);
				}
                $this->view->data=$this->data;
                /*echo "<pre>";
					print_r($this->data);
				exit;*/
                if (file_exists(PATH_TO_FILES.'product/manufacturer-list.phtml'))
                {
					$this->view->addScriptPath(PATH_TO_FILES.'product/');
					$this->renderScript('manufacturer-list.phtml');
				}else 
                if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/product/manufacturer-list.phtml'))
                {
					$this->render('manufacturer-list');
				} else
                {
					$this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/product/');
					$this->renderScript('manufacturer-list.phtml');
				}
			}else
            {
				$urlParam['category']="product/manufacturer";
				$this->view->qpopup=explode("*",@constant('PRODUCT_LISTING_QUICK_LINK_POPUP_WH'));
				$urlObj=new Model_Url('','');
				
				$this->psort=$urlParam['sort'];
				$this->porder=$urlParam['order'];
				$this->plimit=$urlParam['limit'];
				$this->ppage=$urlParam['page'];
				$this->poption_filter=$urlParam['option_filter'];
				$this->pmanu_filter=$urlParam['manu_filter'];
				$this->pprice_filter=$urlParam['price_filter'];
				
				//$this->page=3;
				/*start modules*/
				$moduleObj=new Model_Module();
				$this->view->pos=$moduleObj->getModules(array('page'=>3));
				/*end modules*/
				$currObj=new Model_currencies();
				$currObj->setCurrency($this->_getParam('curr'));
				$this->view->curr=$currObj;
				
				$custObj=new Model_Customer();
				$this->view->logged = $custObj->isLogged();
				
				$this->view->button_continue = $_SESSION['OBJ']['tr']->translate('button_continue');
				$this->view->continue = HTTP_SERVER.'index/index';//$this->url->link('common/home');
				
				
				/*start view*/
				if(!isset($_SESSION['PRODUCT_LIST_VIEW']))
				{
                    $_SESSION['PRODUCT_LIST_VIEW']=PRODUCT_LIST_DEFAULT_VIEW;
				}else if($this->_getParam('view_prod')!="")
				{
                    $_SESSION['PRODUCT_LIST_VIEW']=$this->_getParam('view_prod');
                    header("location:".$_SERVER['HTTP_REFERER']);
				}
				/*end view*/
				
				$ifsort=$this->psort;
				$sort=isset($ifsort)?$ifsort:'p.sort_order';
				
				$iforder=$this->porder;
				$order=isset($iforder)?$iforder:'ASC';
				
				
				$ifpage=$this->ppage;
				$page=isset($ifpage)?$ifpage:'1';
				
				$iflimit=$this->plimit;
				$limit=isset($iflimit)?$iflimit:MAX_ITEMS_PER_PAGE_CATALOG;
				
				
				$manufacturer_info = $manObj->getManufacturer($this->pmanu_filter);
				$this->getMetaTags(array('meta_title'=>$manufacturer_info['manufacturers_name'],'meta_keywords'=>$manufacturer_info['manufacturers_name'],'meta_description'=>''));
				
				$this->view->manufacturer_info=$manufacturer_info;
				if ($manufacturer_info) {
					
					
					
					$this->view->cat_heading_title = $manufacturer_info['manufacturers_name'];
					$imgSize=explode("*",IMAGE_M_LIST_SIZE);
					$this->view->cat_thumb = PATH_TO_UPLOADS."image/".$manufacturer_info['manufacturers_image']."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
					
					/*$this->data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));*/
					
					$this->view->cat_description = html_entity_decode($manufacturer_info['categories_description'], ENT_QUOTES, 'UTF-8');
					//$this->data['compare'] = $this->url->link('product/compare');
					
					$url=$this->getUrl(array('sort','order','limit','price_filter','manu_filter'));
					$this->view->breadcrumbs[] = array(
					'text'      => 'Brand',
					'href'=>$this->view->url_to_site.$urlParam['category'],
					'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
					);
					
					$this->view->breadcrumbs[] = array(
					'text'      => $manufacturer_info['manufacturers_name'],
					'href'=>$this->view->url_to_site.$urlParam['category'].$url,
					'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
					);
					
					$prodObj=new Model_Products();
					$this->view->products = array();
					
					$data = array(
					'option_filter'=>$this->poption_filter,
					'manu_filter'=>$this->pmanu_filter,
					//'filter_category_id' => $category_id,
					'sort'               => $sort,
					'order'              => $order,
					'start'              => ($page - 1) * $limit,
					'limit'              => $limit
					);
					
					$data['price_filter']=$this->pprice_filter;
					
					$product_total = $prodObj->getTotalProducts($data);
					$results = $prodObj->getProducts($data);
					foreach ($results as $result) {
                        if ($result['image']) {
							$imgSize=explode("*",IMAGE_P_LIST_SIZE);
							$image_avail=strpbrk($result['image'],'.');
							$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
							} else {
							$imgSize=explode("*",IMAGE_P_LIST_SIZE);
							$image=PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
						}
                        $taxObj=new Model_Tax();
						
                        $LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=="false"?"0":"1";
                        if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
							$price = $currObj->format($taxObj->calculate($result['price'], $result['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
							} else {
							$price = false;
						}
						
                        if ((float)$result['special']) {
							$special = $currObj->format($taxObj->calculate($result['special'], $result['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
							} else {
							$special = false;
						}
						
                        if (constant(DISPLAY_PRICE_WITH_TAX)) {
							//$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);
							$tax = $currObj->format((float)$result['special'] ? $result['special'] : $result['price']);
							} else {
							$tax = false;
						}
                        if (ALLOW_REVIEWS) {
							$rating = (int)$result['rating'];
							//$this->view->captcha=$this->captcha();
							} else {
							$rating = false;
						}
						
                        $this->view->products[] = array(
                        'product_id'  => $result['products_id'],
                        'thumb'       => $image,
                        'name'        => $result['products_name'],
                        'description' => substr(strip_tags(html_entity_decode($result['products_description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
                        'price'       => $price,
                        'special'     => $special,
                        'tax'         => $tax,
                        'rating'      => $result['rating'],
                        'reviews'     => sprintf($_SESSION['OBJ']['tr']->translate('text_reviews'), (int)$result['reviews']),
                        'href' => $urlObj->getLink(array("controller"=>"product","action"=>"product-details",'',"product_id"=>$result['products_id']), "product_id/".$result['products_id'])
						);
					}
					
					
					$url=$this->getUrl(array('limit','price_filter','manu_filter','option_filter'));
					
					$this->view->sorts = array();
					
					$this->view->sorts[] = array(
					'text'  => $_SESSION['OBJ']['tr']->translate('text_default_product_category'),
					'value' => 'p.sort_order-ASC',
					//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/p.sort_order/order/ASC/".$url)
					//'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category",''),"/sort/p.sort_order/order/ASC/".$url)
                    'href'=>$this->view->url_to_site.$urlParam['category']."/sort/p.sort_order/order/ASC".$url
					);
					
					$this->view->sorts[] = array(
					'text'  => $_SESSION['OBJ']['tr']->translate('text_name_asc_product_category'),
					'value' => 'pd.products_name-ASC',
					//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/pd.products_name/order/ASC/".$url)
					//'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/pd.products_name/order/ASC/".$url)
                    'href'=>$this->view->url_to_site.$urlParam['category']."/sort/pd.products_name/order/ASC".$url
					);
					
					$this->view->sorts[] = array(
					'text'  => $_SESSION['OBJ']['tr']->translate('text_name_desc_product_category'),
					'value' => 'pd.products_name-DESC',
					//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/pd.products_name/order/DESC/".$url)
					//'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/pd.products_name/order/DESC/".$url)
                    'href'=>$this->view->url_to_site.$urlParam['category']."/sort/pd.products_name/order/DESC".$url
					);
					
					$this->view->sorts[] = array(
					'text'  => $_SESSION['OBJ']['tr']->translate('text_price_asc_product_category'),
					'value' => 'p.products_price-ASC',
					//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/p.products_price/order/ASC/".$url)
					//'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/p.products_price/order/ASC/".$url)
                    'href'=>$this->view->url_to_site.$urlParam['category']."/sort/p.products_price/order/ASC".$url
					);
					
					$this->view->sorts[] = array(
					'text'  => $_SESSION['OBJ']['tr']->translate('text_price_desc_product_category'),
					'value' => 'p.products_price-DESC',
					//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/p.products_price/order/DESC/".$url)
					//'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/p.products_price/order/DESC/".$url)
                    'href'=>$this->view->url_to_site.$urlParam['category']."/sort/p.products_price/order/DESC".$url
					);
					
					$this->view->sorts[] = array(
					'text'  => $_SESSION['OBJ']['tr']->translate('text_rating_desc_product_category'),
					'value' => 'rating-DESC',
					//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/rating/order/DESC/".$url)
					//'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/rating/order/DESC/".$url)
					'href'=>$this->view->url_to_site.$urlParam['category']."/sort/rating/order/DESC".$url
					
					);
					
					$this->view->sorts[] = array(
					'text'  => $_SESSION['OBJ']['tr']->translate('text_rating_asc_product_category'),
					'value' => 'rating-ASC',
					
					//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/rating/order/ASC/".$url)
					//'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/rating/order/ASC/".$url)
                    'href'=>$this->view->url_to_site.$urlParam['category']."/sort/rating/order/ASC".$url
					);
					
					$this->view->sorts[] = array(
					'text'  => $_SESSION['OBJ']['tr']->translate('text_model_asc_product_category'),
					'value' => 'p.products_model-ASC',
					//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/p.products_model/order/ASC/".$url)
					//'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/p.products_model/order/ASC/".$url)
                    'href'=>$this->view->url_to_site.$urlParam['category']."/sort/p.products_model/order/ASC".$url
					);
					
					$this->view->sorts[] = array(
					'text'  => $_SESSION['OBJ']['tr']->translate('text_model_desc_product_category'),
					'value' => 'p.products_model-DESC',
					//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/sort/p.products_model/order/DESC/".$url)
					//'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/sort/p.products_model/order/DESC/".$url)
					//				'href'  => $this->url->link('product/category', 'path=' . $this->path. '&sort=p.model&order=DESC' . $url)
                    'href'=>$this->view->url_to_site.$urlParam['category']."/sort/p.products_model/order/DESC".$url
					);
					
					
					$url=$this->getUrl(array('sort','order','price_filter','manu_filter','option_filter'));
					
					
					$this->view->limits = array();
					
					$this->view->limits[] = array(
					'text'  => MAX_ITEMS_PER_PAGE_CATALOG,
					'value' => MAX_ITEMS_PER_PAGE_CATALOG,
					//			'href'=>HTTP_SERVER."product/category/path/".$this->path.$url."/limit/".MAX_ITEMS_PER_PAGE_CATALOG
					//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.$url."/limit/".MAX_ITEMS_PER_PAGE_CATALOG)
					//'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url."/limit/".MAX_ITEMS_PER_PAGE_CATALOG)
                    'href'=>$this->view->url_to_site.$urlParam['category'].$url."limit/".MAX_ITEMS_PER_PAGE_CATALOG
					
					);
					
					
					//echo "value of url ".$url;
					if(PRODUCT_LIST_SHOW_LIMIT!="")
					{
                        $show_exp=explode(",",PRODUCT_LIST_SHOW_LIMIT);
                        foreach($show_exp as $k=>$v)
                        {
							$this->view->limits[] = array(
							'text'  => $v,
							'value' => $v,
							'href'=>$this->view->url_to_site.$urlParam['category'].$url."limit/".$v
							
							
							);
							//echo "<br/>valu eof ".$this->view->url_to_site.$urlParam['category'].$url."limit/".$v;
							
						}
                        
					}
					
					/*start filters*/
					if($manufacturer_info[filters]!="")
					{
                        $selected_array=array();
                        $this->view->global_filters=array();
						$exp_filter=explode("&",$manufacturer_info[filters]);
						
						$arr_filters=array();
						$filterContainer=array();
						sizeof($exp_filter);
						foreach($exp_filter as $k)
						{
							$exp=explode("#",$k);
							//$arr_filters[$exp[0]]=$exp[1];
							$arr_filters_sort[$exp[1]."-".$exp[0]]=$exp[0];
						}
						
						ksort($arr_filters_sort);//sorts an array by key
						foreach($arr_filters_sort as $k=>$v)
						{
							if($v=='p')
							{
                                $ifprice_filter=$this->pprice_filter;
                                //$this->view->price_filter_selected=$this->pprice_filter;
								
                                $this->view->filter_option_selected=$ifprice_filter==""?array("-1"=>'price'):array("-1"=>$ifprice_filter);
                                $price_limit = $prodObj->getProductsPriceLimits($data);
								
                                if($price_limit['max_price']!="") //if no products then display null price block
                                {
									$diff_price=round($price_limit['max_price']-$price_limit['min_price'])/5;
									$arrprice=array();
									$arrprice[]=$price_limit['min_price'];
									$between_price=$price_limit['min_price'];
									for($i=0;$i<5;$i++)
									{
                                        $between_price=$between_price+$diff_price;
                                        $arrprice[]=floor($between_price);
									}
									
									$url=$this->getUrl(array('sort','order','manu_filter','limit','option_filter'));
									
									$this->view->price_filter = array();
									/*$this->view->price_filter[] = array(
                                        'text'  => 'Select Price',
                                        'value' => '',
										//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.$url)
										'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url)
									);*/
									for($i=0;$i<5;$i++)
									{
										$this->view->price_filter[] = array(
                                        'text'  => $arrprice[$i].'-'.$arrprice[$i+1],
                                        'value' => 'between '.$arrprice[$i].' and '.$arrprice[$i+1],
										//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/price_filter/between ".$arrprice[$i]." and ".$arrprice[$i+1].$url)
										'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/price_filter/between ".$arrprice[$i]." and ".$arrprice[$i+1].$url)
										);
										
									}
									$this->view->price_filter[] = array(
									'text'  => 'Clear',
									'value' => '',
									
									//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.$url)
									'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url)
									);
									$this->view->global_filters['Prices']=$this->view->price_filter;
								}else
                                {
									$this->view->global_filters['Prices']="";
								}
								
								
							}else if($v=='m')
							{
                                $manuObj=new Model_Manufacturer();
                                $manu_info=$manuObj->getManufacturersByCategory(array("path"=>$path_id));
                                if(sizeof($manu_info)!=0)
                                {
									$url=$this->getUrl(array('sort','order','price_filter','limit','option_filter'));
									
									$this->view->manu_filter=array();
									$ifmanu_filter=$this->pmanu_filter;
									
									//$this->view->filter_option_selected=array("-2"=>$ifmanu_filter);
									$this->view->filter_option_selected=$ifmanu_filter==""?array("-2"=>'manu'):array("-2"=>$ifmanu_filter);
									
									
									/*$this->view->manu_filter[]= array(
										'text'  => $_SESSION['OBJ']['tr']->translate('text_select'),
										'value' => '',
										//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/".$url)
										'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url)
									);*/
									foreach($manu_info as $manu)
									{
                                        $this->view->manu_filter[]= array(
                                        'text'  => $manu['manufacturers_name'],
                                        'value' => $manu['manufacturers_id'],
                                        //'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path."/manu_filter/".$manu['manufacturers_id'].$url)
                                        'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path."/manu_filter/".$manu['manufacturers_id'].$url)
                                        );
									}
									$this->view->manu_filter[] = array(
									'text'  => 'Clear',
									'value' => '',
									//'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.$url)
									'href'  =>$urlObj->getLink(array("controller"=>"product","action"=>"category","path"=>$this->path),"path/".$this->path.$url)
									);
									$this->view->global_filters['Manufacturers']=$this->view->manu_filter;
								}else
								{
									$this->view->global_filters['Manufacturers']="";
								}
							}else
							{
								//	echo "value of option ".$v;
                                $filter_option_name=$prodObj->getOption($v);
                                $url=$this->getUrl(array('sort','order','price_filter','limit','manu_filter'));
                                $this->view->filter_option_value=array();
                                $this->view->filter_option_value=$prodObj->getOptionValue($v,array("path"=>$ifpath,"option_filter"=>$this->poption_filter,"url"=>$url));
                                $this->view->global_filters[$filter_option_name]=$this->view->filter_option_value;
                                $ifoption_filter=$this->poption_filter;
                                if(isset($ifoption_filter))
                                {
									$res=strstr($ifoption_filter,",");
									if($res!="")
									{
										$this->view->filter_option_selected=explode(",",$ifoption_filter);
										
									}else
									{
										$this->view->filter_option_selected=array($ifoption_filter);
									}
									
								}
								
							}
							$selected_array=@array_merge($selected_array,$this->view->filter_option_selected); //got warnings while manu and price or not present so ketp @
							
							
						}
						$this->view->filter_option_selected=@array_unique($selected_array);//got warnings while manu and price or not present so ketp @
					}
					/*end filters*/
					
					$url=$this->getUrl(array('sort','order','manu_filter','limit','price_filter','option_filter'));//option_fitler added extra
					
					/*start pagination*/
					$pagination = new Model_FrontPagination();
					$pagination->total = $product_total;
					$pagination->page = $page;
					$pagination->limit = $limit;
					$pagination->text =$_SESSION['OBJ']['tr']->translate('text_pagination');
					// $this->language->get('text_pagination');
					//$UrlObj=new Model_Url('','');
					//echo "value of ".$url;
					//$pagination->url = $UrlObj->link('product/category', 'path/' . $this->path . $url . '/page/{page}');
					//$pagination->url =HTTP_SERVER.str_replace("//","/","product/category/path/".$this->path.$url. '/page/{page}');
					//echo "value of ".$this->url_to_site.$urlParam['category'].$url.'/page/{page}';
					$pagination->url=$this->view->url_to_site.$urlParam['category'].$url.'page/{page}';//$urlObj->getLink(array("controller"=>"product","action"=>"category",''),$url. '/page/{page}');
					//HTTP_SERVER.'product/category/path/' . $this->path."/". $url . 'page/{page}';
					$this->view->pagination = $pagination->render();
					/*end pagination*/
					
					$this->view->sort = $sort;
					$this->view->order = $order;
					$this->view->limit = $limit;
					
					//$this->view->continue = $this->url->link('common/home');
					$this->view->continue = HTTP_SERVER."index/index";
					
				} else
                {
					//echo "here in else";
					$url = '';
					
					/*$ifpath=$this->path;
						if (isset($ifpath)) {
                        $url .= '&path=' . $this->path;
					}*/
					
					$ifsort=$this->psort;
					if (isset($ifsort)) {
                        $url .= '&sort=' . $this->psort;
					}
					$iforder=$this->porder;
					if (isset($iforder)) {
                        $url .= '&order=' . $this->porder;
					}
					
					$ifpage=$this->ppage;
					if (isset($ifpage)) {
                        $url .= '&page=' . $this->ppage;
					}
					
					$iflimit=$this->plimit;
					if (isset($iflimit)) {
                        $url .= '&limit=' . $this->plimit;
					}
					
					$this->data['breadcrumbs'][] = array(
					'text'      => $_SESSION['OBJ']['tr']->translate('text_error'),
					'href'      => HTTP_SERVER."product/category",
					'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
					);
					
					//	$this->document->setTitle($_SESSION['OBJ']['tr']->translate('text_error'));
					
					$this->view->heading_title = $_SESSION['OBJ']['tr']->translate('text_error_product');
					
					$this->view->text_error = $_SESSION['OBJ']['tr']->translate('text_error_product');
					
					$this->view->button_continue =$_SESSION['OBJ']['tr']->translate('button_continue');
					
					$this->view->continue = HTTP_SERVER."index/index";
					$this->view->breadcrumbs[] = array(
					'text'      => 'Brand',
					'href'=>$this->view->url_to_site.$urlParam['category'],
					'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
					);
				}
				
                if (file_exists(PATH_TO_FILES.'product/manufacturer.phtml'))
                {
					$this->view->addScriptPath(PATH_TO_FILES.'product/');
					$this->renderScript('manufacturer.phtml');
				}else 
                if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/product/manufacturer.phtml'))
                {
					//$this->render('category');
				} else
                {
					$this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/product/');
					$this->renderScript('manufacturer.phtml');
				}
			}
		}
		
		
		public function compareAction()
		{
			$this->getMetaTags(array());
			//$this->page=2;
			/*start modules*/
			$moduleObj=new Model_Module();
			$this->view->pos=$moduleObj->getModules(array('page'=>2)); 
			/*end modules*/
			$ifremove=$this->_getParam('remove');
			if (isset($ifremove))
			{
				$key = array_search($this->_getParam('remove'), $_SESSION['compare']);
				
				if ($key !== false)
				{
					unset($_SESSION['compare'][$key]);
				}
			}
			$currObj=new Model_currencies();
			$currObj->setCurrency($this->_getParam('curr'));
			$this->view->curr=$currObj;
			
			
			if (!isset($_SESSION['compare'])) {
				$_SESSION['compare'] = array();
			}
			
			
			//$this->document->setTitle($this->language->get('heading_title'));
			
			
			$url = '';
			$ifcompare=$this->_getParam('compare');
			if (isset($ifcompare)) {
				$url .= 'compare/' . $this->_getParam('compare');
			}
			
			$this->view->breadcrumbs[] = array(
			'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_product_compare'),
			'href'      => HTTP_SERVER."product/compare",
			'separator' =>$_SESSION['OBJ']['tr']->translate('text_separator')
			);
			
			
			$this->data['action'] = HTTP_SERVER."product/compare";
			
			$this->data['products'] = array();
			$this->data['attribute_groups'] = array();
			
			foreach ($_SESSION['compare'] as $product_id) {
				$prodObj=new Model_Products();
				$product_info = $prodObj->getProduct($product_id);
				
				if ($product_info) {
					if ($product_info['image']) {
						$imgSize=explode("*",IMAGE_COMPARE_SIZE);
						//$image=PATH_TO_UPLOADS."products/".$product_info['image']."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
						$image_avail=strpbrk($product_info['image'],'.');
						//echo "value of ".$res."<br/>";
						$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1":PATH_TO_UPLOADS."products/".$product_info['image']."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
						
						} else {
						$image = false;
						$imgSize=explode("*",IMAGE_COMPARE_SIZE);
						$image=PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
						//$image =PATH_TO_UPLOADS."products/".constant(STORE_NO_IMAGE_ICON)."&w=".$imgRSize[0]."&h=".$imgRSize[1]."&zc=1";
					}
					//echo "value of ".$image;
					
					
					$taxObj=new Model_Tax();
					$custObj=new Model_Customer();
					$LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=="false"?"0":"1";
					
					if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
						$price = $currObj->format($taxObj->calculate($product_info['price'], $product_info['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
						} else {
						$price = false;
					}
					
					if ((float)$result['special']) {
						$special = $currObj->format($taxObj->calculate($product_info['special'], $product_info['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
						} else {
						$special = false;
					}
					
					$DISPLAY_STOCK=STOCK_DISPLAY=="false"?"0":"1";
					if ($product_info['products_quantity'] <= 0) {
						$availability = $product_info['stock_status_id'];
						} elseif ($DISPLAY_STOCK) {
						$this->view->stock = $product_info['products_quantity'];
						} else {
						$availability = $prodObj->getStockStatus(constant('DEFAULT_AVAILABILITY_STOCK_STATUS_ID'));
					}
					
					/*$DISPLAY_STOCK=STOCK_DISPLAY=="false"?"0":"1";
						if ($product_info['products_quantity'] <= 0) {
						$availability = $product_info['stock_status_id'];
						} elseif ($DISPLAY_STOCK) {
						$this->view->stock = $product_info['products_quantity'];
						} else {
						$availability = $_SESSION['OBJ']['tr']->translate('text_instock');
					}*/
					
					$attribute_data = array();
					
					$attribute_groups = $prodObj->getProductAttributes($product_id);
					
					foreach ($attribute_groups as $attribute_group) {
						foreach ($attribute_group['attribute'] as $attribute) {
							$attribute_data[$attribute['attribute_id']] = $attribute['text'];
						}
					}
					$weightObj=new Model_Weight();
					$lengthObj=new Model_Length();
					$this->data['products'][$product_id] = array(
					'product_id'   => $product_info['products_id'],
					'name'         => $product_info['products_name'],
					'thumb'        => $image,
					'price'        => $price,
					'special'      => $special,
					'description'  => substr(strip_tags(html_entity_decode($product_info['products_description'], ENT_QUOTES, 'UTF-8')), 0, 200) . '..',
					'model'        => $product_info['products_model'],
					'manufacturer' => $product_info['manufacturers_name'],
					'availability' => $availability,
					'rating'       => (int)$product_info['rating'],
					'reviews'      => sprintf($_SESSION['OBJ']['tr']->translate('text_reviews'), (int)$product_info['reviews']),
					'weight'       => $weightObj->format($product_info['weight'], $product_info['weight_class']),
					'length'       => $lengthObj->format($product_info['length'], $product_info['length_class']),
					'width'        => $lengthObj->format($product_info['width'], $product_info['length_class']),
					'height'       => $lengthObj->format($product_info['height'], $product_info['length_class']),
					'attribute'    => $attribute_data,
					'href'         => HTTP_SERVER."product/product-details/product_id/" . $product_id,
					'remove'       => HTTP_SERVER."product/product-details/product_id/" . $product_id
					);
					
					foreach ($attribute_groups as $attribute_group) {
						$this->data['attribute_groups'][$attribute_group['attribute_group_id']]['name'] = $attribute_group['name'];
						
						foreach ($attribute_group['attribute'] as $attribute) {
							$this->data['attribute_groups'][$attribute_group['attribute_group_id']]['attribute'][$attribute['attribute_id']]['name'] = $attribute['name'];
						}
					}
				}
			}
			
			$this->data['continue'] = HTTP_SERVER."index/index";
			$this->view->data=array();
			$this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'product/compare.phtml'))
            {
				$this->view->addScriptPath(PATH_TO_FILES.'product/');
				$this->renderScript('compare.phtml');
			}else 
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/product/compare.phtml'))
            {
                $this->render('compare');
			} else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/product/');
                $this->renderScript('compare.phtml');
			}
			
		}
		
		/*public function captchaAction() {
			$this->_helper->layout()->disableLayout();
			//$this->_helper->viewRenderer->setNoRender(true);
			$captchaObj = new Model_Captcha();
			$_SESSION['captcha'] = $captchaObj->getCode();
			$captchaObj->showImage();
			exit;
		}*/
		
		/*public function productuploadAction() {
			//echo "suresh";
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			
			$json = array();
			
			if (isset($_FILES['file']['name']) && $_FILES['file']['name']) {
			if ((strlen(utf8_decode($_FILES['file']['name'])) < 3) || (strlen(utf8_decode($_FILES['file']['name'])) > 128)) {
			$json['error'] = $_SESSION['OBJ']['tr']->translate('error_filename_product_product');
	  		}
			
			$allowed = array();
			
			$filetypes = explode(',', @constant('ALLOWED_FILE_EXTENSIONS'));
			
			foreach ($filetypes as $filetype) {
			$allowed[] = trim($filetype);
			}
			//echo substr(strrchr($_FILES['file']['name'], '.'), 1)."<pre>";
			
			
			if (!in_array(".".substr(strrchr($_FILES['file']['name'], '.'), 1), $allowed)) {
			$json['error'] = $_SESSION['OBJ']['tr']->translate('error_filetype_product_product');
       		}
			
			if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
			$json['error'] = $_SESSION['OBJ']['tr']->translate('error_upload_' . $_FILES['file']['error']);
			}
			} else {
			$json['error'] = $_SESSION['OBJ']['tr']->translate('error_upload_product_product');
			}
			
			if (($_SERVER['REQUEST_METHOD'] == 'POST') && !isset($json['error'])) {
			if (is_uploaded_file($_FILES['file']['tmp_name']) && file_exists($_FILES['file']['tmp_name'])) {
			$file = basename($_FILES['file']['name']) . '.' . md5(rand());
			
			// Hide the uploaded file name sop people can not link to it directly.
			//$this->load->library('encryption');
			
			$encryption = new Model_Encryption(ENCRYPTION_KEY);
			
			$json['file'] = $encryption->encrypt($file);
			
			move_uploaded_file($_FILES['file']['tmp_name'], @constant('PATH_TO_UPLOADS_DIR').'downloads/' . $file);
			}
			
			$json['success'] = $_SESSION['OBJ']['tr']->translate('text_upload_product_product');
			}
			
			echo Model_Json::encode($json);
		}*/
		
		public function searchAction()
		{
			$this->getMetaTags(array());
			//$this->page=2;
			/*start modules*/
			$moduleObj=new Model_Module();
			$this->view->pos=$moduleObj->getModules(array('page'=>2)); 
			/*end modules*/
			
			/*start view*/
			if(!isset($_SESSION['PRODUCT_LIST_VIEW']))
			{
				$_SESSION['PRODUCT_LIST_VIEW']=PRODUCT_LIST_DEFAULT_VIEW;
			}else if($this->_getParam('view_prod')!="")
			{
				$_SESSION['PRODUCT_LIST_VIEW']=$this->_getParam('view_prod');
				//header("location:".$_SERVER['HTTP_REFERER']."/q/".$this->_request->q."/dd/".$this->_request->dd);
				$this->_redirect(HTTP_SERVER.'product/search/q/'.$this->_request->q.'/dd/'.$this->_request->dd);
			}
			/*end view*/
			
			$currObj=new Model_currencies();
			$currObj->setCurrency($this->_getParam('curr'));
			$this->view->curr=$currObj;
			$cartObj=new Model_Cart();
			
			
			/*start modules*/
			$moduleObj=new Model_Module();
			$this->view->pos=$moduleObj->getModules(array('page'=>'2')); //refers to category page as per r_layout
			/*end modules*/
			
			$this->view->breadcrumbs[] = array(
			'text'      => 'Search',
			'href'      => HTTP_SERVER."product/search/q/".$this->_request->q."/dd/".$this->_request->dd,
			'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
			);
			
			$data = array();
			//	print_r($this->_getAllParams());
			//$key='"Inkfruit Blue de-meno"'; //excat result
			//$key='"test-Music"'; //excat result
			//$key='Inkfruit-Mens'; //multiple result
			//$key = '\'"'.$this->_request->q.'-'.ltrim($this->_request->dd).'"\'';
			$key = "' ".$this->_request->q.'-'.ltrim($this->_request->dd)."'";
			//$index = Zend_Search_Lucene::open(PATH_TO_PUBLIC."searchindex");
			/*echo "value of ".sizeof(scandir(@constant('PATH_TO_SEARCHINDEX')));
				print_r(scandir(@constant('PATH_TO_SEARCHINDEX')));
			exit;*/
			if(sizeof(scandir(@constant('PATH_TO_SEARCHINDEX')))>3) //when search index is empty at initial stage..
			{
				$index = Zend_Search_Lucene::open(@constant('PATH_TO_SEARCHINDEX'));
				
				if($this->_request->dd!='0')
				{
					/*$category=$this->_request->dd;
						$prod_name=$this->_request->q;
						//echo $category. " ".$prod_name;
						//$category=explode(" ", $category);
						//$prod_name=explode(" ", $prod_name);
						$category_query = explode(" ", $category);
					$prod_name_query = explode(" ", $prod_name);*/
					$category = new Zend_Search_Lucene_Search_Query_Phrase(array($this->_request->dd));
					$prod_name = new Zend_Search_Lucene_Search_Query_Phrase(array($this->_request->q));
					
					$results = $index->find("categories_name:".$category." AND products_name:".$prod_name,products_model, SORT_NUMERIC, SORT_ASC);
					//echo $key;
					//echo "in if ";
					//$results = $index->find('search_key:'.$key);
					//$results = $index->find('products_name:'.$this->_request->q.'search_key:'.$this->_request->dd);
					//$results = $index->find($query);
				}else
				{
					// echo "in else";
					$results = $index->find('products_name:'.$key);
					//$results = $index->find('search_key:'.$key);
				}
				/*echo "<pre>"; 
					print_r($results);
				echo "</pre>"; */
				//echo "value of ".$key;
				//echo count($results);
				$this->data[q]=$this->_request->q;
				$this->data[dd]=$this->_request->dd;
				$this->view->q=$this->_request->q;
				$this->view->dd=$this->_request->dd;
				
				if($index->count())
				{
					$this->view->qpopup=explode("*",@constant('PRODUCT_LISTING_QUICK_LINK_POPUP_WH'));
					$count = 0;
					$imgSize=explode("*",IMAGE_P_LIST_SIZE);
					foreach ($results as $result)
					{
						$image_avail=strpbrk($result->products_image,'.');
						$data[$count]['thumb']=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1":PATH_TO_UPLOADS."products/".$result->products_image."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
						
						$data[$count]['product_id']= $result->products_id;
						$data[$count]['language_id']= $result->language_id;
						$data[$count]['name']= $result->products_name;
						$data[$count]['description'] = substr(strip_tags(html_entity_decode($result->products_description, ENT_QUOTES, 'UTF-8')), 0, 100) . '..';
						$data[$count]['products_model'] = $result->products_model;
						$data[$count]['products_quantity'] = $result->products_quantity;
						//$data[$count]['thumb'] = $result->products_image;
						$data[$count]['products_price'] = $result->products_price;
						$data[$count]['products_date_added'] = $result->products_date_added;
						$data[$count]['products_date_available'] = $result->products_date_available;
						//$data[$count]['categories_name'] = $result->categories_name;
						$data[$count]['categories_id'] = $result->categories_id;
						$count++;
					}
				}
				$this->view->heading_title = $_SESSION['OBJ']['tr']->translate('text_error_product');
				$this->view->text_error = "Search Not Found for ".$this->_request->q."!!";
				$this->view->button_continue =$_SESSION['OBJ']['tr']->translate('button_continue');
				$this->view->continue = HTTP_SERVER."index/index";
				//echo $this->view->text_error;
				/*start product details*/
				$ifpage=$this->_getParam('page');
				$page=isset($ifpage)?$ifpage:'1';
				
				$iflimit=$this->_getParam('limit');
				//$limit=isset($iflimit)?$iflimit:MAX_ITEMS_PER_PAGE_CATALOG;
				$limit="1";
				$prodObj=new Model_Products();
				
				
				$this->view->products = array();
				
				foreach ($results as $search_result)
				{
					if($search_result->language_id!=$_SESSION['Lang']['language_id'])
					{
						continue;
					}
					
					
					$result = $prodObj->getProduct($search_result->products_id);
					
					if ($result['image']) {
						$imgSize=explode("*",IMAGE_P_LIST_SIZE);
						$image_avail=strpbrk($result['image'],'.');
						$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
						
						} else {
						$imgSize=explode("*",IMAGE_P_LIST_SIZE);
						$image=PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
					}
					
					$taxObj=new Model_Tax();
					
					$custObj=new Model_Customer();
					$LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=="false"?"0":"1";
					if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
						$price = $currObj->format($taxObj->calculate($result['price'], $result['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
						} else {
						$price = false;
					}
					
					if ((float)$result['special']) {
						$special = $currObj->format($taxObj->calculate($result['special'], $result['products_tax_class_id'], DISPLAY_PRICE_WITH_TAX));
						} else {
						$special = false;
					}
					
					if (constant(DISPLAY_PRICE_WITH_TAX)) {
						//$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price']);
						$tax = $currObj->format((float)$result['special'] ? $result['special'] : $result['price']);
						} else {
						$tax = false;
					}
					if (ALLOW_REVIEWS) {
						$rating = (int)$result['rating'];
						//$this->view->captcha=$this->captcha();
						} else {
						$rating = false;
					}
					
					$this->view->products[] = array(
					'product_id'  => $result['products_id'],
					'thumb'       => $image,
					'name'        => $result['products_name'],
					'description' => substr(strip_tags(html_entity_decode($result['products_description'], ENT_QUOTES, 'UTF-8')), 0, 100) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $result['rating'],
					'reviews'     => sprintf($_SESSION['OBJ']['tr']->translate('text_reviews'), (int)$result['reviews']),
					'href'        => HTTP_SERVER."product/product-details/product_id/".$result['products_id'] //$this->url->link('product/product', 'path=' . $this->_getParam('path') . '&product_id=' . $result['product_id'])
					);
				}
				
				
				$this->view->limits = array();
				
				$this->view->limits[] = array(
				'text'  => MAX_ITEMS_PER_PAGE_CATALOG,
				'value' => MAX_ITEMS_PER_PAGE_CATALOG,
				//'href'  =>HTTP_SERVER.str_replace("//","/","product/search/q/".$this->_getParam('q')."/dd/".$this->_getParam('dd').$url."/limit/".MAX_ITEMS_PER_PAGE_CATALOG)
				'href'  =>HTTP_SERVER.str_replace("//","/","product/search/q/".$this->_getParam('q')."/dd/".$this->_getParam('dd')."/limit/".MAX_ITEMS_PER_PAGE_CATALOG)
				);
				
				
				//echo "value of url ".$url;
				if(PRODUCT_LIST_SHOW_LIMIT!="")
				{
					$show_exp=explode(",",PRODUCT_LIST_SHOW_LIMIT);
					foreach($show_exp as $k=>$v)
					{
						$this->view->limits[] = array(
						'text'  => $v,
						'value' => $v,
						'href'  =>HTTP_SERVER.str_replace("//","/","product/search/q/".$this->_getParam('q')."/dd/".$this->_getParam('dd').$url."/limit/".$v)
						);
					}
				}
				
				$this->view->limit = $limit;
				//$this->view->continue = $this->url->link('common/home');
				$this->view->continue = HTTP_SERVER."index/index";
				/*end product details*/
				
				$this->view->data=$this->data;
				$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($this->view->products));
				$paginator->setItemCountPerPage(@constant('MAX_ITEMS_PER_PAGE_CATALOG'))
				->setCurrentPageNumber($this->_getParam('page',1));
				$this->view->products=$paginator;
				$prodObj->updateSearchKeyword($this->_request->q);
			}
			if (file_exists(PATH_TO_FILES.'product/search.phtml'))
			{
				$this->view->addScriptPath(PATH_TO_FILES.'product/');
				$this->renderScript('search.phtml');
			}else 
			if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/product/search.phtml'))
			{
				$this->render('search');
			} else
			{
				$this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/product/');
				$this->renderScript('search.phtml');
			}
		}
	}
	
