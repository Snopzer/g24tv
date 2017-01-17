<?php
ob_start();
/**
 * Handling Errors in the application
 *
 * @category   Zend
 * @package    AjaxController
 * @author     suresh babu k
 */
//class AjaxController extends Zend_Controller_Action {
class AjaxController extends My_Controller_Main {
	private $error = array();

	public function init()
	{
		Zend_Session::start();
		$this->getConstants();
                //$this->setHttps();
		//$this->view->vaction=$this->getRequest()->getActionName();
	}

	public function ajaxseachautoAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		if(!empty($_REQUEST['q']))
		{
			$this->db=Zend_Db_Table::getDefaultAdapter();
					//echo "select keyword from r_search_keywords where
			//lower(keyword) like '%".strtolower(htmlspecialchars($_REQUEST['q'], ENT_COMPAT))."%' order by hits desc";        
			$select = $this->db->fetchAssoc("select keyword from r_search_keywords where
			lower(keyword) like '%".$this->view->escape(strtolower(htmlspecialchars($_REQUEST['q'], ENT_COMPAT)))."%' order by hits desc");
			foreach($select as $k=>$v)
			{
				echo "$v[keyword]\n";
			}
		}
	}

	public function zoneAction()
	{
                $this->setLangSession();
 		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$output = '<option value="">' . $_SESSION['OBJ']['tr']->translate('text_select') . '</option>';
		$locZoneObj=new Model_LocalisationZone();

		$results = $locZoneObj->getZonesByCountryId($this->_request->country_id);

      	foreach ($results as $result) {
        	$output .= '<option value="' . $result['zone_id'] . '"';

	    	if (isset($this->_request->zone_id) && ($this->_request->zone_id == $result['zone_id'])) {
	      		$output .= ' selected="selected"';
	    	}else if($result['zone_id']=='1476' && ($this->_request->zone_id == ''))
                {
                                        $output .= ' selected="selected"';
                }

	    	$output .= '>' . $result['zone_name'] . '</option>';
    	}

		if (!$results) {
		  	$output .= '<option value="0">' . $_SESSION['OBJ']['tr']->translate('text_none') . '</option>';
		}
		echo $output;
	}

	public function cartupdateAction()
	{
        $this->setLangSession();    
        $this->setHttps();
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$cartObj=new Model_Cart();

		$this->view->tr=$_SESSION['OBJ']['tr'];
		$this->tr=$_SESSION['OBJ']['tr'];
		$this->tax=new Model_Tax();
		$this->currObj=new Model_currencies();
		$custObj=new Model_Customer();
		$json = array();
		$json['close_url']=@constant('URL_TO_TEMPLATES').@constant('SITE_DEFAULT_TEMPLATE')."/includes/images/close.png";
        if (isset($this->_request->product_id)) {
			$prodObj=new Model_Products();
			$product_info = $prodObj->getProduct($this->_request->product_id);
			/*echo "<pre>";
			print_r($product_info);
			echo "</pre>";
			exit;*/
			if ($product_info) {
				// Minimum quantity validation
				if (isset($this->_request->quantity)) {
					$quantity = $this->_request->quantity;
				} else {
					$quantity = 1;
				}

				$product_total = 0;
				print_r($_SESSION['cart']);exit;
				if(sizeof($_SESSION['cart'])=='0')
				{
				$_SESSION['cart']=array();
				}
	 			foreach ($_SESSION['cart'] as $key => $value) {
					$product = explode(':', $key);

					if ($product[0] == $this->_request->product_id) {
						$product_total += $value;
					}
				}

				if ($product_info['products_minimum_quantity'] > ($product_total + $quantity)) {
					$json['error']['warning'] = sprintf($this->tr->translate('error_minimum'), $product_info['products_name_full'], $product_info['products_minimum_quantity']);
				}

				// Option validation
				if (isset($this->_request->option)) {
					$option = array_filter($this->_request->option);
				} else {
					$option = array();
				}

				$product_options = $prodObj->getProductOptions($this->_request->product_id);

				foreach ($product_options as $product_option) {
					if ($product_option['required'] && (!isset($this->_request->option[$product_option['product_option_id']]) || !$this->_request->option[$product_option['product_option_id']])) {
						$json['error'][$product_option['product_option_id']] = sprintf($this->tr->translate('error_required_checkout_cart'), $product_option['name']);
					}
				}
			}

 			if (!isset($json['error'])) {
				$cartObj->add($this->_request->product_id, $quantity, $option);

				$json['success'] = sprintf($this->tr->translate('text_success_checkout_cart'), HTTP_SERVER.'product/product-details/product_id/'.$this->_request->product_id,$product_info['products_name_full'], HTTP_SERVER.'checkout/cart');

				unset($_SESSION['shipping_methods']);
				unset($_SESSION['shipping_method']);
				unset($_SESSION['payment_methods']);
				unset($_SESSION['payment_method']);
			} else {
				$json['redirect'] = str_replace('&amp;', '&', HTTP_SERVER.'product/product-details/product_id/'.$this->_request->product_id);
			}
 		}

      	if (isset($this->_request->remove)) {
        	$cartObj->remove($this->_request->remove);
      	}

      	if (isset($this->_request->voucher)) {
			if ($_SESSION['vouchers'][$this->_request->voucher]) {
				unset($_SESSION['vouchers'][$this->_request->voucher]);
			}
		}

		$this->data['text_empty'] = $this->tr->translate('text_empty_checkout_cart');

		$this->data['button_checkout'] = $this->tr->translate('button_checkout');
		$this->data['button_remove'] = $this->tr->translate('button_remove');

		$this->data['products'] = array();

			/*echo "<pre>";
			print_r($cartObj->getProducts());
			echo "</pre>";
			exit;*/
		foreach ($cartObj->getProducts() as $result) {
			if ($result['image']) {
				//$image = $this->model_tool_image->resize($result['image'], 40, 40);
				$image=PATH_TO_UPLOADS."products/".$result['image']."&w=40&h=40&zc=1";
			} else {
				$image = '';
			}

			$option_data = array();

			foreach ($result['option'] as $option) {
				if ($option['type'] != 'file') {
					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (strlen($option['option_value']) > 20 ? substr($option['option_value'], 0, 20) . '..' : $option['option_value'])
					);
				} else {
					//$this->load->library('encryption');
					$encryption = new Model_Encryption(ENCRYPTION_KEY);
					//$encryption = new Encryption($this->config->get('config_encryption'));

					$file = substr($encryption->decrypt($option['option_value']), 0, strrpos($encryption->decrypt($option['option_value']), '.'));

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (strlen($file) > 20 ? substr($file, 0, 20) . '..' : $file)
					);
				}
			}

		 	$LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=='true'?'1':'0';
			if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
				$price = $this->currObj->format($this->tax->calculate($result['price'], $result['tax_class_id'], DISPLAY_PRICE_WITH_TAX));
			} else {
				$price = false;
			}

			if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
				$total = $this->currObj->format($this->tax->calculate($result['total'], $result['tax_class_id'], DISPLAY_PRICE_WITH_TAX));
			} else {
				$total = false;
			}
			//echo "value of total here".$total;
				/*echo "<pre>";
				print_r($result);
				echo "</pre>";*/
				$this->data['products'][] = array(
				'key'      => $result['key'],
				'thumb'    => $image,
				'name'     => $result['name'],
				'model'    => $result['model'],
				'option'   => $option_data,
				'quantity' => $result['quantity'],
				'stock'    => $result['stock'],
				'price'    => $price,
				'total'    => $total,
				'href'     => HTTP_SERVER.'product/product-details/product_id/'. $result['product_id']//$this->url->link('product/product', 'product_id=' . $result['product_id'])
			);
		}
				/*echo "<pre>";
				print_r($this->data['products']);
				echo "</pre>";*/
		// Gift Voucher
		$this->data['vouchers'] = array();

		if (isset($_SESSION['vouchers']) && $_SESSION['vouchers']) {
			foreach ($_SESSION['vouchers'] as $key => $voucher) {
				$this->data['vouchers'][] = array(
					'key'         => $key,
					'description' => $voucher['description'],
					'amount'      => $this->currObj->format($voucher['amount'])
				);
			}
		}

		// Calculate Totals
		$total_data = array();
		$total = 0;
		$taxes = $cartObj->getTaxes();
	//	echo "login display price ".LOGIN_DISPLAY_PRICE;

		//echo "login ".$LOGIN_DISPLAY_PRICE;
		//exit;
		if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {

			$sort_order = array();

 			 $otmodules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
			 foreach($otmodules as $k=>$v)
			 {
				//echo substr(substr($v,0,-4),2)." ".'MODULE_ORDER_TOTAL_'.strtoupper(substr(substr($v,0,-4),2)).'_SORT_ORDER'."<br/>";
				$sort_order[]=constant('MODULE_ORDER_TOTAL_'.strtoupper(substr(substr($v,0,-4),2)).'_SORT_ORDER')."-".substr(substr($v,0,-4),2);

			 }
			 //exit;
			 		sort($sort_order,SORT_NUMERIC);
			 $results=array();

			foreach($sort_order as $k=>$v)
			{
				$exp=explode("-",$v);
				//echo constant('MODULE_ORDER_TOTAL_'.strtoupper($exp[1]).'_STATUS')."<br/>";

				if(constant('MODULE_ORDER_TOTAL_'.strtoupper($exp[1]).'_STATUS')=='true')
				{
					//echo $exp[1]." ship<br>";

					$class='Model_OrderTotal_Ot'.$exp[1];
					//echo $class."<br>";
					$oTobj=new $class;

					$oTobj->getTotal($total_data, $total, $taxes);

				}
			}

			$sort_order = array();
 			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}


			array_multisort($sort_order, SORT_ASC, $total_data);
		}

		//echo "<pre>";
		//print_r($total_data);
		//echo "</pre>";
		//exit;

		//echo "value of total ".$total;
		$json['total'] = sprintf($this->tr->translate('text_items_checkout_cart'), $cartObj->countProducts() + (isset($_SESSION['vouchers']) ? count($_SESSION['vouchers']) : 0), $this->currObj->format($total));

		$this->data['totals'] = $total_data;

		/*start modules*/
			// Modules
			$this->data['modules'] = array();

			if (isset($_SESSION['redirect'])) {
      			$this->data['continue'] = $_SESSION['redirect'];

				unset($_SESSION['redirect']);
			} else {
				$this->data['continue'] = HTTP_SERVER.'index/index';//$this->url->link('common/home');
			}
		/*end moduels*/
		$this->data['checkout'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);//HTTP_SERVER.'checkout/checkout';//$this->url->link('checkout/checkout', '', 'SSL');
		$this->view->data=$this->data;
		   ob_start();
		   //include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/cart-flash.phtml';
                   if (file_exists(PATH_TO_FILES.'checkout/cart-flash.phtml'))
                    {
                        include_once PATH_TO_FILES.'checkout/cart-flash.phtml';
                    }else
                    if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/cart-flash.phtml'))
                    {
                        //echo "in";
                        include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/cart-flash.phtml';
                    } else
                    {
                        //echo "else";
                        include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/checkout/cart-flash.phtml';
                    }
			$json['output'] = ob_get_contents();
			ob_end_clean();
		echo Model_Json::encode($json);
	}

	public function ajaxwishlistupdateAction()
		{

			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$json = array();
			$json['close_url']=@constant('URL_TO_TEMPLATES').@constant('SITE_DEFAULT_TEMPLATE')."/includes/images/close.png";
			if (!isset($_SESSION['wishlist']))
			{
				$_SESSION['wishlist'] = array();
			}
			$ifproduct_id=$this->_getParam('product_id');
			if (isset($ifproduct_id)) {
				$product_id = $ifproduct_id;
			} else {
				$product_id = 0;
			}
			$prodObj=new Model_Products();
			$product_info = $prodObj->getProduct($product_id);

			if ($product_info) {
				if (!in_array($this->_getParam('product_id'), $_SESSION['wishlist'])) {
					$_SESSION['wishlist'][] = $this->_getParam('product_id');
				}
				 $custObj=new Model_Customer();
				if ($custObj->isLogged()) {
				$json['success'] = sprintf($_SESSION['OBJ']['tr']->translate('text_success_account_wishlist'),HTTP_SERVER."product/product-details/product_id/".$product_id
				, $product_info['products_name_full'], HTTP_SERVER."account/wishlist");
			} else {
				/*$json['success'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', 'SSL'), $this->url->link('account/register', '', 'SSL'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('account/wishlist'));				*/

				$json['success'] = sprintf($_SESSION['OBJ']['tr']->translate('text_login_account_wishlist'),Model_Url::getLink(array("controller"=>"account","action"=>"login"),'',SERVER_SSL), Model_Url::getLink(array("controller"=>"account","action"=>"register"),'',SERVER_SSL), HTTP_SERVER."product/product-details/product_id/".$product_id, $product_info['products_name_full'], HTTP_SERVER."account/wishlist");
			}
			$json['total'] = sprintf($_SESSION['OBJ']['tr']->translate('text_wishlist_common_header'), (isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0));
			}
			echo Model_Json::encode($json);
	}


	public function ajaxcompareupdateAction()
{
	$this->_helper->layout()->disableLayout();
	$this->_helper->viewRenderer->setNoRender(true);
	$json = array();
	
	$moduleObj=new Model_Module();
	$mod=$moduleObj->isModuleEnable(array("module"=>"compare"));
	if($mod=='1') //if module is enabled
	{
		$ifremove=$this->_getParam('remove');
		if (isset($ifremove))
		{
                    $key = array_search($this->_getParam('remove'), $_SESSION['compare']);
                    if ($key !== false)
                    {
                                    unset($_SESSION['compare'][$key]);
                    }
		}
	}
	
	if (!isset($_SESSION['compare'])) 
	{
		$_SESSION['compare'] = array();
	}

	$ifprodid=$this->_getParam('product_id');
	if (isset($ifprodid)) 
	{
		$product_id = $ifprodid;
	} else 
	{
		$product_id = 0;
	}

	$prodObj=new Model_Products();
	$product_info = $prodObj->getProduct($ifprodid);

	if ($product_info) 
	{
		if (!in_array($ifprodid, $_SESSION['compare'])) 
		{
			if (count($_SESSION['compare']) > 4) 
			{
				array_shift($_SESSION['compare']);
			}
			if($ifprodid!="")
			{    
				$_SESSION['compare'][] = $ifprodid;
			}
		}
	if($mod=='1') //if module is enabled
	{
		$this->currency=new Model_currencies();
		$this->customer=new Model_Customer();
		$this->tax=new Model_Tax();
	
		if (isset($_SESSION['compare']) && sizeof($_SESSION['compare'])>0) 
		{
			foreach($_SESSION['compare'] as $k=>$v) //$v is product id
			{
				$result=$prodObj->getProduct($v);
				if ($result['image']) 
				{
					$image_avail=strpbrk($result['image'],'.');
					$image=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=40&h=40&zc=1":PATH_TO_UPLOADS."products/".$result['image']."&w=40&h=40&zc=1";

				} 
				else 
				{
					$image = false;
				}

			$LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=="false"?"0":"1";
			if (($LOGIN_DISPLAY_PRICE && $this->customer->isLogged()) || !$LOGIN_DISPLAY_PRICE) 
			{
				$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], constant('DISPLAY_PRICE_WITH_TAX')));
			} else 
			{
				$price = false;
			}

			if ((float)$result['special']) 
			{
				$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], constant('DISPLAY_PRICE_WITH_TAX')));
			} else 
			{
				$special = false;
			}

		$this->data['products'][] = array(
			'product_id' => $result['products_id'],
			'thumb'   	 => $image,
			'name'    	 => $result['products_name_full'],
			'price'   	 => $price,
			'special' 	 => $special,
			'href'    	 => HTTP_SERVER.'product/product-details/product_id/'.$result['products_id'],
		);                
			}	
		}
					
		//start
                if (file_exists(PATH_TO_FILES.'checkout/compare-flash.phtml'))
                {
                     include_once PATH_TO_FILES.'checkout/compare-flash.phtml';   
                }else
		if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/compare-flash.phtml'))
		{
                    //echo "in";
			include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/compare-flash.phtml';
		} else
		{
                    //echo "out";
			include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/checkout/compare-flash.phtml';
		}
		$json['output'] = ob_get_contents();
		ob_end_clean();
		//end
		
	}
		if (isset($ifremove))
		{
			$json['success'] = $_SESSION['OBJ']['tr']->translate('text_remove_success_product_compare');   
		}else
		{
			$json['success'] = sprintf($_SESSION['OBJ']['tr']->translate('text_success_product_compare'), HTTP_SERVER."product/product-details/product_id/".$product_id, $product_info['products_name_full'], HTTP_SERVER."product/compare");
		}    
			$json['total'] = sprintf($_SESSION['OBJ']['tr']->translate('text_compare_product_compare'), (isset($_SESSION['compare']) ? count($_SESSION['compare']) : 0));
			$json['close_url']=@constant('URL_TO_TEMPLATES').@constant('SITE_DEFAULT_TEMPLATE')."/includes/images/close.png";
		}
	echo Model_Json::encode($json);
}

        public function captchaAction() {
            	$this->_helper->layout()->disableLayout();
		//$this->_helper->viewRenderer->setNoRender(true);
 		$captchaObj = new Model_Captcha();
		$_SESSION['captcha'] = $captchaObj->getCode();
 		$captchaObj->showImage();
                exit;
	}
        
        public function productuploadAction() {
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
	}

	/*public function captchaAction() {
		$this->_helper->layout()->disableLayout();
		//$this->_helper->viewRenderer->setNoRender(true);
 		$captchaObj = new Model_Captcha();
		$_SESSION['captcha'] = $captchaObj->getCode();
 		$captchaObj->showImage();
		$this->renderScript('product/captcha.phtml');
	}*/
		public function productwriteAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$revObj=new Model_Review();
		$json = array();

		if ((strlen(utf8_decode($this->_request->name)) < 3) || (strlen(utf8_decode($this->_request->name)) > 25)) {
			$json['error'] = $_SESSION['OBJ']['tr']->translate('error_name_product_product');
		}

		if ((strlen(utf8_decode($this->_request->text)) < 25) || (strlen(utf8_decode($this->_request->text)) > 1000)) {
			$json['error'] = $_SESSION['OBJ']['tr']->translate('error_text_product_product');
		}

		if (!$this->_request->rating) {
			$json['error'] = $_SESSION['OBJ']['tr']->translate('error_rating_product_product');
		}

		if (!isset($_SESSION['captcha']) || ($_SESSION['captcha'] != $this->_request->captcha)) {
			$json['error'] = $_SESSION['OBJ']['tr']->translate('error_captcha_product_product');
		}

		if (($_SERVER['REQUEST_METHOD'] == 'POST') && !isset($json['error'])) {
			$revObj->addReview($this->_request->product_id, $_REQUEST);

			$json['success'] = $_SESSION['OBJ']['tr']->translate('text_success_product_product');
		}
	echo Model_Json::encode($json);
	}

	public function productreviewAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

    	$revObj=new Model_Review();
		$this->data['text_no_reviews'] = $_SESSION['OBJ']['tr']->translate('text_no_reviews_product_product');

		if (isset($this->_request->page)) {
			$page = $this->_request->page;
		} else {
			$page = 1;
		}

		$this->data['reviews'] = array();

		$review_total = $revObj->getTotalReviewsByProductId($this->_request->product_id);
		//echo "value of ".$review_total;
		$results = $revObj->getReviewsByProductId($this->_request->product_id, ($page - 1) * 5, 5);

		foreach ($results as $result) {
        	$this->data['reviews'][] = array(
        		'author'     => $result['customers_name'],
				'text'       => strip_tags($result['reviews_text']),
				'rating'     => (int)$result['reviews_rating'],
        		'reviews'    => sprintf($_SESSION['OBJ']['tr']->translate('reviews_text'), (int)$review_total),
        		'date_added' => date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($result['date_added']))
        	);
      	}

		$pagination = new Model_FrontPagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->text = $_SESSION['OBJ']['tr']->translate('text_pagination');
		$pagination->url = HTTP_SERVER.'ajax/productreview/product_id/'.$this->_request->product_id . '/page/{page}';//$this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$this->data['pagination'] = $pagination->render();

		/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/product/review.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/product/review.tpl';
		} else {
			$this->template = SITE_DEFAULT_TEMPLATE.'/template/product/review.tpl';
		}*/
		$this->view->data=$this->data;
		//$this->renderScript('product/review.phtml');
                if (file_exists(PATH_TO_FILES.'product/review.phtml'))
                {
                    $this->view->addScriptPath(PATH_TO_FILES.'product/');
                    $this->renderScript('review.phtml');
                }else
                if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/product/review.phtml'))
                {
                    $this->renderScript('product/review.phtml');
                } else
                {
                    $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/product/');
                    $this->renderScript('review.phtml');
                }

		//$this->response->setOutput($this->render());
	}

		public function autocompleteAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$this->affinfo=new Model_Affinfo();
		$json = array();
		if (isset($this->_request->filter_name)) {

			$data = array(
				'filter_name' => $this->_request->filter_name,
				'start'       => 0,
				'limit'       => 20
			);

			$prodObj=new Model_Products();
			$results = $prodObj->getProducts($data);
			foreach ($results as $result) {
				$json[] = array(
					'name' => html_entity_decode($result['products_name_full'], ENT_QUOTES, 'UTF-8'),
					//'link' => str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $result['product_id'] . '&tracking=' . $this->affiliate->getCode()))
					'link' => str_replace('&amp;', '&', HTTP_SERVER.'product/product-details/product_id/' . $result['products_id'] . '/tracking/' . $this->affinfo->getCode())
				);
			}
		}
		echo Model_Json::encode($json);
	}

	public function infoAction()
	{
		$this->_helper->layout()->disableLayout();
		if (isset($this->_request->information_id))
		{
			$information_id = $this->_request->information_id;
		} else
		{
			$information_id = 0;
		}
		$this->infoObj=new Model_Information();
		$information_info = $this->infoObj->getInformation($information_id);
		if ($information_info) {
			$output  = '<html dir="ltr" lang="en">' . "\n";
			$output .= '<head>' . "\n";
			$output .= '  <title>' . $information_info['title'] . '</title>' . "\n";
			$output .= '  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
			$output .= '</head>' . "\n";
			$output .= '<body>' . "\n";
			$output .= '  <br /><br /><h1>' . $information_info['title'] . '</h1>' . "\n";
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
			$output .= '  </body>' . "\n";
			$output .= '</html>' . "\n";
		}
		$this->view->output=$output;
		//$this->renderScript('information/info.phtml');
                if (file_exists(PATH_TO_FILES.'information/info.phtml'))
                {
                        $this->view->addScriptPath(PATH_TO_FILES.'information/');
                        $this->renderScript('info.phtml');
                }else
                if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/information/info.phtml'))
                {
                    $this->renderScript('information/info.phtml');
                } else
                {
                    $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/information/');
                    $this->renderScript('info.phtml');
                }
	}
        
        public function quickProductDetailsAction()
        {
            $this->_helper->layout()->disableLayout();
            
            if($this->_request->product_id!="")
            {
                $urlObj=new Model_Url('','');

		$urlParam=$urlObj->getUrlParams($this->_getAllParams());
		$this->path=$urlParam['path'];
		$this->product_id=$urlParam['product_id'];

		$this->page='2';
		$currObj=new Model_currencies();
 		$currObj->setCurrency($this->_getParam('curr'));
 		$this->view->curr=$currObj;
		$catObj=new Model_Categories();

		
		$ifproduct_id=$this->product_id;
		if (isset($ifproduct_id)) {
			$product_id = $ifproduct_id;
		} else {
			$product_id = 0;
		}
		$prodObj=new Model_Products();
		$product_info = $prodObj->getProduct($product_id);
		$this->view->product_info = $product_info;
        
			$this->view->heading_title = $product_info['products_name_full'];
			$this->view->thumb = $product_info['image'];

			$this->view->product_id = $this->product_id;
			$this->view->manufacturer = $product_info['manufacturers_name'];
			$this->view->manufacturers = HTTP_SERVER.'product/manufacturer/product/manufacturer_id/' . $product_info['manufacturers_id'];
			$this->view->model = $product_info['model'];
			$this->view->reward = $product_info['reward'];
			$this->view->points = $product_info['points'];

			$DISPLAY_STOCK=STOCK_DISPLAY=="false"?"0":"1";
			//echo "value of display stock".$DISPLAY_STOCK;
			if ($product_info['products_quantity'] <= 0) {
				$this->view->stock = $product_info['stock_status_id'];
			} elseif ($DISPLAY_STOCK) {
				$this->view->stock = $product_info['products_quantity'];
			} else 
			{
	            $this->view->stock = $prodObj->getStockStatus(constant('DEFAULT_AVAILABILITY_STOCK_STATUS_ID'));
            }

			if ($product_info['image']) {
                            $imgPSize=explode("*",IMAGE_P_POPUP_SIZE);
                            $this->view->popup=PATH_TO_UPLOADS."products/".$product_info['image'];
			} else
			{
				$this->view->popup = '';
			}

			if ($product_info['image']) {

				$image_avail=strpbrk($product_info['image'],'.');
				$imgTSize=explode("*",IMAGE_P_THUMB_SIZE);
					$this->view->thumb=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgTSize[0]."&h=".$imgTSize[1]."&zc=1":PATH_TO_UPLOADS."products/".$product_info['image']."&w=".$imgTSize[0]."&h=".$imgTSize[1]."&zc=1";
			} else {
				  $imgSize=explode("*",IMAGE_P_THUMB_SIZE);
					$this->view->thumb=PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
			}

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
			
				$this->view->tax = $currObj->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price']);

				} else {
					$this->view->tax = false;
				}


			$discounts = $prodObj->getProductDiscounts($this->product_id);
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
                                        
                    if (file_exists(PATH_TO_FILES.'product/quick-product-details.phtml'))
                    {
                            $this->view->addScriptPath(PATH_TO_FILES.'product/');
                            $this->renderScript('quick-product-details.phtml');
                    }else        
                    if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/product/quick-product-details.phtml'))
                    {
                        $this->renderScript('product/quick-product-details.phtml');
                    } else
                    {
                        $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/product/');
                        $this->renderScript('quick-product-details.phtml');
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
        }

	public function ajaxloadAction()
	{
     		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
                if($this->_request->color!="")
                {
		$prodObj=new Model_Products();
		$option_info = $prodObj->getAjaxSize((int)$this->_request->product_id,(int)$this->_request->color);
                }else
				{
					$option_info ="<option value=''> --- Please Select --- </option>";
				}
		
		echo $option_info;
	}
        
       	public function ajaximageloadAction()
	{
          
                $this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$prodObj=new Model_Products();
		$product_info = $prodObj->getProduct($this->_request->product_id);
                
		if($this->_request->color!=""){
			$results = $prodObj->getProductImagesColor($this->_request->product_id,$this->_request->color);
                }else
		{
			$results = $prodObj->getProductImages($this->_request->product_id);
		}
                
                
		if ($results[0]['image']!="")
		{
		        $image_avail=strpbrk($results[0]['image'],'.');
        		$imgPSize=explode("*",IMAGE_P_POPUP_SIZE);
			$popup=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgPSize[0]."&h=".$imgPSize[1]."&zc=1":PATH_TO_UPLOADS."products/".$results[0]['image'];//."&w=".$imgPSize[0]."&h=".$imgPSize[1]."&zc=1";
                        
                        $imgTSize=explode("*",IMAGE_P_THUMB_SIZE);
			$thumb=$image_avail==""?PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgTSize[0]."&h=".$imgTSize[1]."&zc=1":PATH_TO_UPLOADS."products/".$results[0]['image']."&w=".$imgTSize[0]."&h=".$imgTSize[1]."&zc=1";

		} else
		{
			 $imgSize=explode("*",IMAGE_P_POPUP_SIZE);
                         $popup=PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON;//."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
                         $imgTSize=explode("*",IMAGE_P_THUMB_SIZE);
                         $thumb=PATH_TO_UPLOADS."image/".STORE_NO_IMAGE_ICON."&w=".$imgTSize[0]."&h=".$imgTSize[1]."&zc=1";
		}
		$this->data['images'] = array();
                
                $imgPAddSize=explode("*",IMAGE_P_ADDITIONAL_SIZE);    
                foreach ($results as $result) 
                {
                    if($result['image']!=$results[0][image])
                    {
                        $images[] = array(
                        'popup' => PATH_TO_UPLOADS."products/".$result['image'],//."&w=".$imgPSize[0]."&h=".$imgPSize[1]."&zc=1",
                        'thumb' =>PATH_TO_UPLOADS."products/".$result['image']."&w=".$imgPAddSize[0]."&h=".$imgPAddSize[1]."&zc=1"
                        );
                    }
                }
                $image_info="";
                 if ($thumb!="") {
                    $image_info.='<div class="image"><a href="'.$popup.'"   onclick="return hs.expand(this)" class="highslide" ><img src="'.HTTP_SERVER."timthumb.php?src=".$thumb.'" title="'.$product_info[products_name_full].'" alt="'.$this->heading_title.'"/></a></div>';
			}
                 	if ($images) { 
                      $image_info.='<div class="image-additional">
                                        <div class="gallery gallery1">
                                            <div class="holder">
                                                <ul style="margin-left: 0px;">';
				
				$video=0;
				$video_array=array();
				 foreach ($images as $image) {
                $image_info.='<li><a href="'.$image['popup'].'" class="highslide" onclick="return hs.expand(this)">
	<img src="'.HTTP_SERVER."timthumb.php?src=".$image['thumb'].'" alt=""
		title="" /></a></li>';
                
                }
                $image_info.='</ul></div>';		
                if(sizeof($images)>2)
                {
                    $image_info.='<div class="control"><a href="#" class="prev">prev</a><a href="#" class="next">next</a></div>'; 
                }
                    $image_info.='</div></div>';
                }

                echo $image_info;
                
     

//echo '<script type="text/javascript" src="'.URL_TO_TEMPLATES.SITE_DEFAULT_TEMPLATE.'/includes/scripts/slideGallery.js"></script>';
echo '<script type="text/javascript">
	window.addEvent("domready", function() {
		
		var gallery1 = new slideGallery($$(".gallery1"), {
			steps: 2,
			mode: "circle",
			random: false,
			autoplay: true,
			stop: ".stop",
			start: ".start",
			duration: 4000,
			speed: 800
		});
	});
</script>';
	}

	public function getOrderCountryAction() {
            
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		 
                $json = array();
		$locCtry=new Model_LocalisationCountry();
		$locZone=new Model_LocalisationZone();
    	$country_info = $locCtry->getCountry($this->_request->country_id);
		
		if ($country_info) {
		 

			$json = array(
				'country_id'        => $country_info['countries_id'],
				'name'              => $country_info['countries_name'],
				'iso_code_2'        => $country_info['countries_iso_code_2'],
				'iso_code_3'        => $country_info['countries_iso_code_3'],
				'address_format'    => $country_info['address_format'],
				'postcode_required' => $country_info['postcode_required'],
				'zone'              => $locZone->getZonesByCountryId($this->_request->country_id),
				'status'            => $country_info['status']		
			);
		}
		echo Model_Json::encode($json);
	}

	public function autocompleteAffiliateAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$affiliate_data = array();
		
		if (isset($this->_request->filter_name)) {
		
			$data = array(
				'filter_name' => $this->_request->filter_name,
				'start'       => 0,
				'limit'       => 20
			);
			$affObj=new Model_Affiliate();
			$results = $affObj->getAffiliates($data);
			
			foreach ($results as $result) {
				$affiliate_data[] = array(
					'affiliate_id' => $result['affiliate_id'],
					'name'         => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')
				);
			}
		}
		echo Model_Json::encode($affiliate_data);
	}

	public function autocompleteCustomerAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$json = array();
		if (isset($this->_request->filter_name)) {
			$data = array(
				'filter_name' => $this->_request->filter_name,
				'start'       => 0,
				'limit'       => 20
			);
			$custObj=new Model_AccountCustomer();
			$custaddrObj=new Model_AccountAddress();
			$results = $custObj->getCustomers($data);
			
			foreach ($results as $result) {
				$json[] = array(
					'customer_id'       => $result['customers_id'], 
					'customer_group_id' => $result['customer_group_id'],
					'name'              => strip_tags(html_entity_decode($result['customers_firstname']." ".$result['customers_lasttname'] , ENT_QUOTES, 'UTF-8')),
					'customer_group'    => $result['customer_group'],
					'firstname'         => $result['customers_firstname'],
					'lastname'          => $result['customers_lastname'],
					'email'             => $result['customers_email_address'],
					'telephone'         => $result['customers_telephone'],
					'fax'               => $result['customers_fax'],
					'address'           => $custaddrObj->getOrderAddresses($result['customers_id'])
				);					
			}
		}

		$sort_order = array();
	  	foreach ($json as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		/*echo "<pre>";
		print_r($json);
		echo "</pre>";
		exit;*/
		array_multisort($sort_order, SORT_ASC, $json);
		echo Model_Json::encode($json);
	}

	public function addressAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$json = array();
		$acctaddrObj=new Model_AccountAddress();
		if (!empty($this->_request->address_id)) {
			
			$json = $acctaddrObj->getAddress($this->_request->address_id);
		}

		/*29-Dec-2012echo "<pre>";
		print_r($json);
		exit;*/
		echo Model_Json::encode($json);
	}

	public function autocompleteProductAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$json = array();
		$currObj=new Model_currencies();

		if (isset($this->_request->filter_name) || isset($this->_request->filter_model) || isset($this->_request->filter_category_id)) {
			
			if (isset($this->_request->filter_name)) {
				$filter_name = $this->_request->filter_name;
			} else {
				$filter_name = '';
			}
			
			if (isset($this->_request->filter_model)) {
				$filter_model = $this->_request->filter_model;
			} else {
				$filter_model = '';
			}
						
			if (isset($this->_request->filter_category_id)) {
				$filter_category_id = $this->_request->filter_category_id;
			} else {
				$filter_category_id = '';
			}
			
			if (isset($this->_request->filter_sub_category)) {
				$filter_sub_category = $this->_request->filter_sub_category;
			} else {
				$filter_sub_category = '';
			}
			
			if (isset($this->_request->limit)) {
				$limit = $this->_request->limit;	
			} else {
				$limit = 20;	
			}			
						
			$data = array(
				'filter_name'         => $filter_name,
				'filter_model'        => $filter_model,
				'filter_category_id'  => $filter_category_id,
				'filter_sub_category' => $filter_sub_category,
				'start'               => 0,
				'limit'               => $limit
			);
                        $_SESSION['Lang']['language_id']=$_SESSION['Lang']['language_id']==""?"1":$_SESSION['Lang']['language_id'];//when front end is not opened and when admin add orders then a problem is getting while selecting product as lang session is becoming 0
			$prodObj=new Model_Products();
			$results = $prodObj->getProducts($data);
			//echo "<pre>";
			foreach ($results as $result) {
				$option_data = array();
				
				$product_options = $prodObj->getProductOptions($result['products_id']);	
				//echo "value of ".$result['products_id']."<br/>";
				//print_r($product_options);
				foreach ($product_options as $product_option) {
					if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox') {
						$option_value_data = array();
					
						foreach ($product_option['option_value'] as $product_option_value) {
							$option_value_data[] = array(
								'product_option_value_id' => $product_option_value['product_option_value_id'],
								'option_value_id'         => $product_option_value['option_value_id'],
								'name'                    => $product_option_value['name'],
								//'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
								'price'                   => (float)$product_option_value['price'] ? $currObj->format($product_option_value['price']) : false,
								'price_prefix'            => $product_option_value['price_prefix']
							);	
						}
					
						$option_data[] = array(
							'product_option_id' => $product_option['product_option_id'],
							'option_id'         => $product_option['option_id'],
							'name'              => $product_option['name'],
							'type'              => $product_option['type'],
							'option_value'      => $option_value_data,
							'required'          => $product_option['required']
						);	
					} else {
						$option_data[] = array(
							'product_option_id' => $product_option['product_option_id'],
							'option_id'         => $product_option['option_id'],
							'name'              => $product_option['name'],
							'type'              => $product_option['type'],
							'option_value'      => $product_option['option_value'],
							'required'          => $product_option['required']
						);				
					}
				}
					
				$json[] = array(
					'product_id' => $result['products_id'],
					'name'       => html_entity_decode($result['products_name_full'], ENT_QUOTES, 'UTF-8'),	
					'model'      => $result['products_model'],
					'option'     => $option_data,
					'price'      => $result['price']
				);	
			}
		}
		echo Model_Json::encode($json);
	}

		public function orderProductUploadAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$json = array();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (!empty($_FILES['file']['name'])) {
				$filename = html_entity_decode($_FILES['file']['name'], ENT_QUOTES, 'UTF-8');
				
				if ((strlen($filename) < 3) || (strlen($filename) > 100)) {
					$json['error'] = "File name must be between 3 and 100 characters!!";
				}
				
				$allowed = array();
				$filetypes = explode(',', @constant('ALLOWED_FILE_EXTENSIONS'));
				
				foreach ($filetypes as $filetype) {
					$allowed[] = trim($filetype);
				}
				
				//if (!in_array(substr(strrchr($filename, '.'), 1), $allowed)) {
				if (!in_array(".".substr(strrchr($_FILES['file']['name'], '.'), 1), $allowed)) {
					$json['error'] = "File type not allowed!!";
				}
							
				if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = "File upload mandatory!!";
				}
			} else {
				$json['error'] = "file upload mandatory!!";
			}
		
			if (!isset($json['error'])) {
				if (is_uploaded_file($_FILES['file']['tmp_name']) && file_exists($_FILES['file']['tmp_name'])) {
					$file = basename($filename) . '.' . md5(mt_rand());
					//$encryption = new Model_Encryption(ENCRYPTION_KEY);
					//$json['file'] = $encryption->encrypt($file);
					$json['file'] = $file;
					
					//move_uploaded_file($_FILES['file']['tmp_name'], DIR_DOWNLOAD . $file);
					move_uploaded_file($_FILES['file']['tmp_name'], @constant('PATH_TO_UPLOADS_DIR').'downloads/' . $file);
				}
							
				$json['success'] = "File upload successfull!!";
			}	
		}
		echo Model_Json::encode($json);
	}
}

