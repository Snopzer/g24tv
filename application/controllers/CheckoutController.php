<?php
ob_start();
/**
 * Handling checkout in the application
 *
 * @category   Zend
 * @package    ErrorController
 * @author     suresh babu k
 */
class CheckoutController extends My_Controller_Main {
	private $error = array();
	public $tr=null;
	public $currObj=null;
	public $customer=null;
	public $cart=null;
	public $locCtry=null;
	public $accAddr=null;
	public $locZone=null;
	public $tax=null;
	public function init()
	{
		Zend_Session::start();
		$this->getConstants();
                  //$this->setLangSession();
		//$this->view->vaction=$this->getRequest()->getActionName();

		$this->currObj=new Model_currencies();
 		$this->currObj->setCurrency($this->_getParam('curr'));
		$this->tax=new Model_Tax();
		$this->view->curr=$this->currObj;
		$this->customer=new Model_Customer();
		$this->cart=new Model_Cart();
		$this->locCtry=new Model_LocalisationCountry();
		$this->accAddr=new Model_AccountAddress();
		$this->locZone=new Model_LocalisationZone();
		$this->tax=new Model_Tax();
	}

	public function postDispatch()
	{
		$this->fnAddressSession();
	}

	function fnAddressSession()
	{
		/*unset($_SESSION['shipping_country_id']);
		unset($_SESSION['shipping_zone_id']);
		unset($_SESSION['payment_country_id']);
		unset($_SESSION['payment_zone_id']);*/

		if($_SESSION['guest']['payment']['country_id']!="" || $_SESSION['payment_address_id']!="")
		{
			if($_SESSION['guest']['payment']['country_id']!="")
			{
				$_SESSION['shipping_country_id']=$_SESSION['guest']['shipping']['country_id'];
				$_SESSION['shipping_zone_id']=$_SESSION['guest']['shipping']['zone_id'];

				$_SESSION['payment_country_id']=$_SESSION['guest']['payment']['country_id'];
				$_SESSION['payment_zone_id']=$_SESSION['guest']['payment']['zone_id'];
			}else if($_SESSION['payment_address_id']!="")
			{
				/*modified on july 16 2012 as wrong information retrived 
                                 * $payment_country=$this->locCtry->getCountry($_SESSION['payment_address_id']);
				$payment_zone=$this->locZone->getZone($_SESSION['payment_address_id']);

				$ship_country=$this->locCtry->getCountry($_SESSION['shipping_address_id']);
				$ship_zone=$this->locZone->getZone($_SESSION['shipping_address_id']);*/
                                
                                $payment_details=$this->accAddr->getAddress($_SESSION['payment_address_id']);
				$ship_details=$this->accAddr->getAddress($_SESSION['shipping_address_id']);
				                             
				$_SESSION['shipping_country_id']=$ship_details['country_id'];
				$_SESSION['shipping_zone_id']=$ship_details['zone_id'];

				$_SESSION['payment_country_id']=$payment_details['country_id'];
				$_SESSION['payment_zone_id']=$payment_details['zone_id'];
                                
                                /*echo "<pre>";
                                print_r($payment_details);
                                print_r($ship_details);
                                echo "</pre>";*/
			}
		}
	}

	public function checkoutAction() {
             $this->setLangSession();
            $this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_checkout_checkout')));
		$this->isAffiliateTrackingSet();

		$this->globalKeywords();
		$this->getHeader();
		$this->getFlashCart();

			/*start modules*/
		$moduleObj=new Model_Module();
		$this->view->pos=$moduleObj->getModules(array('page'=>'7')); //refers to category page as per r_layout
		/*end modules*/
		$STOCK_ALLOW_CHECKOUT=STOCK_ALLOW_CHECKOUT=='true'?'1':'0';
		if ((!$this->cart->hasProducts() && (!isset($_SESSION['vouchers']) || !$_SESSION['vouchers'])) || (!$this->cart->hasStock() && !$STOCK_ALLOW_CHECKOUT)) {
	  		//$this->redirect($this->url->link('checkout/cart'));
			$this->_redirect(HTTP_SERVER.'checkout/cart');
    	}

	 
		// Minimum quantity validation
		$products = $this->cart->getProducts();
	 
		foreach ($products as $product) {
			$product_total = 0;

			foreach ($_SESSION['cart'] as $key => $quantity) {
				$product_2 = explode(':', $key);

				if ($product_2[0] == $product['product_id']) {
					$product_total += $quantity;
				}
			}

			if ($product['minimum'] > $product_total) {
				$_SESSION['error'] = sprintf($_SESSION['OBJ']['tr']->translate('error_minimum_checkout_cart'), $product['name'], $product['minimum']);

				//$this->redirect($this->url->link('checkout/cart'));
				$this->_redirect(HTTP_SERVER.'checkout/cart');
			}
		}

 

                //$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title');

		//$this->data['text_checkout_option'] = sprintf($_SESSION['OBJ']['tr']->translate('text_checkout_option'));
		//$this->data['text_checkout_account'] = $_SESSION['OBJ']['tr']->translate('text_checkout_account');
		//$this->data['text_checkout_payment_address'] = $_SESSION['OBJ']['tr']->translate('text_checkout_payment_address');
		//$this->data['text_checkout_shipping_address'] = $_SESSION['OBJ']['tr']->translate('text_checkout_shipping_address');
		//$this->data['text_checkout_shipping_method'] = $_SESSION['OBJ']['tr']->translate('text_checkout_shipping_method');
		//$this->data['text_checkout_payment_method'] = $_SESSION['OBJ']['tr']->translate('text_checkout_payment_method');
		//$this->data['text_checkout_confirm'] = $_SESSION['OBJ']['tr']->translate('text_checkout_confirm');
		//$this->data['text_modify'] = $_SESSION['OBJ']['tr']->translate('text_modify');

		$this->data['logged'] = $this->customer->isLogged();
		$this->data['shipping_required'] = $this->cart->hasShipping();

	 
		$this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'checkout/checkout.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'checkout/');
                    $this->renderScript('checkout.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/checkout.phtml'))
            {
                $this->render('checkout');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/checkout/');
                $this->renderScript('checkout.phtml');
            }
  	}

	public function cartAction()
	{
                $this->setLangSession();
                $this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_checkout_cart')));
		$this->isAffiliateTrackingSet();

		$this->globalKeywords();
		$this->getHeader();
		$this->getFlashCart();
				$this->data[breadcrumbs] = array();

   		$this->data[breadcrumbs][] = array(
       		'text'      => $this->tr->translate('text_home'),
			'href'      => HTTP_SERVER."index/index",
       		'separator' => false
   		);
			/*start modules*/
		$moduleObj=new Model_Module();
		$this->view->pos=$moduleObj->getModules(array('page'=>'7')); //refers to category page as per r_layout
		/*end modules*/

		$cartObj=new Model_Cart();
		$custObj=new Model_Customer();
		//$encryptionObj=new Model_Encryption();
		$taxObj=new Model_Tax();
		$currObj=new Model_Currencies();

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    /*echo "<pre>";
                    print_r($_REQUEST);
                    echo "</pre>";
                    exit;*/
      		if (isset($this->_request->quantity)) {
				if (!is_array($this->_request->quantity)) {
					if (isset($this->_request->option)) {
						$option = $this->_request->option;
					} else {
						$option = array();
					}
                                        

      				$cartObj->add($this->_request->product_id, $this->_request->quantity, $option);
				} else {
					foreach ($this->_request->quantity as $key => $value) {
	      				$cartObj->update($key, $value);
					}
				}
      		}
                    
      		if (isset($this->_request->remove)) {
	    		foreach ($this->_request->remove as $key) {
          			$cartObj->remove($key);
				}
      		}
                /*echo "<pre>";
                print_r($_REQUEST);
                exit; */   
      		if (isset($this->_request->voucher) && $this->_request->voucher) {
	    		foreach ($this->_request->voucher as $key) {
          			if (isset($_SESSION['vouchers'][$key])) {
						unset($_SESSION['vouchers'][$key]);
					}
				}
      		}

                if (isset($this->_request->redirect)) {
                        $_SESSION['redirect'] = $this->_request->redirect;
                }

                if (isset($this->_request->quantity) || isset($this->_request->remove) || isset($this->_request->voucher)) {
                        unset($_SESSION['shipping_methods']);
                        unset($_SESSION['shipping_method']);
                        unset($_SESSION['payment_methods']);
                        unset($_SESSION['payment_method']);
                        unset($_SESSION['reward']);
                            
                        //$this->redirect($this->url->link('checkout/cart'));
                        $this->_redirect('checkout/cart');
                }
    	}

    	//$this->document->setTitle($$_SESSION['OBJ']['tr']->translate('heading_title'));

       	$this->data['breadcrumbs'][] = array(
        	'href'      => HTTP_SERVER.'checkout/cart',//$this->url->link('checkout/cart'),
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_checkout_cart'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator'));

    	if ($cartObj->hasProducts() || (isset($_SESSION['vouchers']) && $_SESSION['vouchers'])) {
			//echo "in if";
      		/*$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_checkout_cart');
                  $this->data['text_select'] = $_SESSION['OBJ']['tr']->translate('text_select');
                  $this->data['text_weight'] = $_SESSION['OBJ']['tr']->translate('text_weight');

                $this->data['column_remove'] = $_SESSION['OBJ']['tr']->translate('column_remove');
      		$this->data['column_image'] = $_SESSION['OBJ']['tr']->translate('column_image');
      		$this->data['column_name'] = $_SESSION['OBJ']['tr']->translate('column_name');
      		$this->data['column_model'] = $_SESSION['OBJ']['tr']->translate('column_model');
      		$this->data['column_quantity'] = $_SESSION['OBJ']['tr']->translate('column_quantity');
			$this->data['column_price'] = $_SESSION['OBJ']['tr']->translate('column_price');
      		$this->data['column_total'] = $_SESSION['OBJ']['tr']->translate('column_total');

      		$this->data['button_update'] = $_SESSION['OBJ']['tr']->translate('button_update');
      		$this->data['button_shopping'] = $_SESSION['OBJ']['tr']->translate('button_shopping');
      		$this->data['button_checkout'] = $_SESSION['OBJ']['tr']->translate('button_checkout');*/
			$LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=="false"?"0":"1";
			if ($LOGIN_DISPLAY_PRICE	 && !$custObj->isLogged()) {
				//$this->data['attention'] = sprintf(this->tr->translate('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
				$this->data['attention'] = sprintf($_SESSION['OBJ']['tr']->translate('text_login_checkout_cart'), HTTP_SERVER.'account/login', HTTP_SERVER.'account/register');
			} else {
				$this->data['attention'] = '';
			}

			if (!$cartObj->hasStock() && (!constant('STOCK_ALLOW_CHECKOUT') || constant('STOCK_WARNING_DISPLAY'))) {
      			$this->data['error_warning'] = str_replace("***",@constant('STOCK_MARK_PRODUCT_OUT_OF_STOCK'),$_SESSION['OBJ']['tr']->translate('error_stock_checkout_cart'));
			} elseif (isset($_SESSION['error'])) {
				$this->data['error_warning'] = $_SESSION['error'];

				unset($_SESSION['error']);
			} else {
				$this->data['error_warning'] = '';
			}

			if (isset($_SESSION['success'])) {
				$this->data['success'] = $_SESSION['success'];

				unset($_SESSION['success']);
			} else {
				$this->data['success'] = '';
			}

			$this->data['action'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart');
			$DISPLAY_WEIGHT_ON_CART=DISPLAY_WEIGHT_ON_CART=='true'?'1':'0';
			if ($DISPLAY_WEIGHT_ON_CART) {
				$weightObj=new Model_Weight();
				$this->data['weight'] = $weightObj->format($cartObj->getWeight(), constant('DEFAULT_WEIGHT_CLASS'));
			} else {
				$this->data['weight'] = false;
			}

     		$this->data['products'] = array();

      		foreach ($cartObj->getProducts() as $result) {
				if ($result['image']) {
				$imgRSize=explode("*",IMAGE_CART_SIZE);
					$image = PATH_TO_UPLOADS."products/".$result['image']."&w=".$imgRSize[0]."&h=".$imgRSize[1]."&zc=1";//$this->model_tool_image->resize($result['image'], $this->config->get('config_image_cart_width'), $this->config->get('config_image_cart_height'));
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


				if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
				//	echo "here in if ";
					$price = $currObj->format($taxObj->calculate($result['price'], $result['tax_class_id'], constant('DISPLAY_PRICE_WITH_TAX')));
				} else {
					$price = false;
				}

				if (($LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !$LOGIN_DISPLAY_PRICE) {
					$total = $currObj->format($taxObj->calculate($result['total'], $result['tax_class_id'], constant('DISPLAY_PRICE_WITH_TAX')));
				} else {
					$total = false;
				}

        		$this->data['products'][] = array(
          			'key'      => $result['key'],
          			'thumb'    => $image,
					'name'     => $result['name'],
          			'model'    => $result['model'],
          			'option'   => $option_data,
          			'quantity' => $result['quantity'],
          			'stock'    => $result['stock'],
					'points'   => ($result['points'] ? sprintf($_SESSION['OBJ']['tr']->translate('text_points_checkout_cart'), $result['points']) : ''),
					'price'    => $price,
					'total'    => $total,
					'href'     => HTTP_SERVER.'product/product-details/product_id/'.$result['product_id']//$this->url->link('product/product', 'product_id=' . $result['product_id'])
        		);
      		}

			// Gift Voucher
			$this->data['vouchers'] = array();

			if (isset($_SESSION['vouchers']) && $_SESSION['vouchers']) {
				foreach ($_SESSION['vouchers'] as $key => $voucher) {
					$this->data['vouchers'][] = array(
						'key'         => $key,
						'description' => $voucher['description'],
						'amount'      => $currObj->format($voucher['amount'])
					);
				}
			}

			$total_data = array();
			$total = 0;
			$taxes = $cartObj->getTaxes();

			if ((constant(LOGIN_DISPLAY_PRICE) && $custObj->isLogged()) || !constant(LOGIN_DISPLAY_PRICE)) {
			$sort_order = array();
			 $otmodules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
			 $otmodulesarray=array();
			 foreach($otmodules as $k=>$v)
			 {
				$otmodulesarray[]=substr(substr($v,0,-4),2);
				$sort_order[]=constant('MODULE_ORDER_TOTAL_'.strtoupper(substr(substr($v,0,-4),2)).'_SORT_ORDER')."-".substr(substr($v,0,-4),2);

			 }
			sort($sort_order,SORT_NUMERIC);
			 /*echo "<pre>";
			 print_r($sort_order);
			 echo "</pre>";
			 exit;*/
			 $results=array();

			foreach($sort_order as $k=>$v)
			{
				$exp=explode("-",$v);
				if(constant('MODULE_ORDER_TOTAL_'.strtoupper($exp[1]).'_STATUS')=='true')
				{
					$class='Model_OrderTotal_Ot'.$exp[1];
					$oTobj=new $class;
					//echo $class." total ".$total."total data".print_r($total_data)."taxes".print_r($taxes)."<br/>";
					$oTobj->getTotal($total_data, $total, $taxes);
					/*echo "<pre>";
			print_r($total_data);
			echo "</pre>";*/

				}
			}

			$sort_order = array();

			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $total_data);
		}

			$this->data['totals'] = $total_data;
 			// Modules
			$this->data['modules'] = array();
			/*echo "<pre>";
			print_r($this->data['totals']);
			echo "</pre>";*/
			//exit;
			$this->view->tr=$_SESSION['OBJ']['tr'];
			if (isset($otmodulesarray)) {
				/*foreach ($otmodulesarray as $result) {
					if ($this->config->get($result['code'] . '_status') && file_exists(DIR_APPLICATION . 'controller/total/' . $result['code'] . '.php')) {
						$this->data['modules'][] = $this->getChild('total/' . $result['code']);
					}
				}*/

                foreach ($otmodulesarray as $k=>$v) 
                {
                        if (constant('MODULE_ORDER_TOTAL_'.strtoupper($v).'_STATUS')) {
                                                        ob_start();
        //include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/ordertotal/'.strtolower($v).'.phtml';
    if (file_exists(PATH_TO_FILES.'ordertotal/'.strtolower($v).'.phtml'))
    {
            include_once PATH_TO_FILES.'ordertotal/'.strtolower($v).'.phtml';   
    }else
    if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/ordertotal/'.strtolower($v).'.phtml'))
    {
       
            include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/ordertotal/'.strtolower($v).'.phtml';
    } else
    {
        
            @include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/ordertotal/'.strtolower($v).'.phtml';
    }
    $this->data['modules'][] = ob_get_contents();
ob_end_clean();
                        }
                }
			}

		/*	echo "<pre>";
			print_r($this->data['modules']);
			echo "</pre>";
exit;*/
			if (isset($_SESSION['redirect'])) {
      			$this->data['continue'] = $_SESSION['redirect'];

				unset($_SESSION['redirect']);
			} else {
				$this->data['continue'] = HTTP_SERVER.'index/index';//$this->url->link('common/home');
			}

			$this->data['checkout'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);//$this->url->link('checkout/checkout', '', 'SSL');

			/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/cart.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/checkout/cart.tpl';
			} else {
				$this->template = SITE_DEFAULT_TEMPLATE.'/template/checkout/cart.tpl';
			}*/
			$this->view->data=$this->data;
			//$this->render('cart');
                        if (file_exists(PATH_TO_FILES.'checkout/cart.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'checkout/');
                    $this->renderScript('cart.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/cart.phtml'))
            {
                $this->render('cart');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/checkout/');
                $this->renderScript('cart.phtml');
            }
    	} else {
       		$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_checkout_cart');
     		$this->data['text_error'] = $_SESSION['OBJ']['tr']->translate('text_empty_checkout_cart');
      		$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');
      		$this->data['continue'] = HTTP_SERVER.'index/index';//$this->url->link('common/home');
			/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = SITE_DEFAULT_TEMPLATE.'/template/error/not_found.tpl';
			}*/
		   	$this->view->data=$this->data;
			//$this->renderScript('account/not-found.phtml');
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

	public function cartupdateAction()
	{
		$this->setHttps();
                $this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$lang=new Model_Languages($this->_getParam('lang'));
		$lC=new Model_Cache();
		$cartObj=new Model_Cart();

		$this->view->tr=$lC->getLangCache($lang);

		$custObj=new Model_Customer();
		$json = array();
		$json['close_url']=$this->url_to_templates.SITE_DEFAULT_TEMPLATE."/includes/images/close.png";
		//echo $this->_request->product_id;exit;
		if (isset($this->_request->product_id)) {
			$prodObj=new Model_Products();
			$product_info = $prodObj->getProduct($this->_request->product_id);
 			if ($product_info) {
				// Minimum quantity validation
				if (isset($this->_request->quantity)) {
					$quantity = $this->_request->quantity;
				} else {
					$quantity = 1;
				}

				$product_total = 0;
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
					$json['error']['warning'] = sprintf($_SESSION['OBJ']['tr']->translate('error_minimum_checkout_cart'), $product_info['products_name_full'], $product_info['products_minimum_quantity']);
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
						$json['error'][$product_option['product_option_id']] = sprintf($_SESSION['OBJ']['tr']->translate('error_required_checkout_cart'), $product_option['name']);
					}
				}
			}
             
 			if (!isset($json['error'])) {
				$cartObj->add($this->_request->product_id, $quantity, $option);
				$json['success'] = sprintf($_SESSION['OBJ']['tr']->translate('text_success_checkout_cart'), HTTP_SERVER.'product/product-details/product_id/'.$this->_request->product_id,$product_info['products_name_full'], HTTP_SERVER.'checkout/cart');

				unset($_SESSION['shipping_methods']);
				unset($_SESSION['shipping_method']);
				unset($_SESSION['payment_methods']);
				unset($_SESSION['payment_method']);
			} else {
		$json['redirect'] = str_replace('&amp;', '&', HTTP_SERVER.'product/product-details/product_id/'.$this->_request->product_id);
//$json['redirect'] = HTTP_SERVER.'product/product-details/product_id/'.$this->_request->product_id;
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

		$this->data['text_empty'] = $_SESSION['OBJ']['tr']->translate('text_empty_checkout_cart');

		$this->data['button_checkout'] = $_SESSION['OBJ']['tr']->translate('button_checkout');
		$this->data['button_remove'] = $_SESSION['OBJ']['tr']->translate('button_remove');

		$this->data['products'] = array();

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
//echo "in if";
//exit;
		//$this->load->model('setting/extension');

			$sort_order = array();

	 		 $otmodules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
			 foreach($otmodules as $k=>$v)
			 {
				//echo substr(substr($v,0,-4),2)." ".'MODULE_ORDER_TOTAL_'.strtoupper(substr(substr($v,0,-4),2)).'_SORT_ORDER'."<br/>";
				$sort_order[]=constant('MODULE_ORDER_TOTAL_'.strtoupper(substr(substr($v,0,-4),2)).'_SORT_ORDER')."-".substr(substr($v,0,-4),2);
				//echo constant('MODULE_ORDER_TOTAL_'.strtoupper(substr(substr($v,0,-4),2)).'_SORT_ORDER')."-".substr(substr($v,0,-4),2)."<br/>";
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

        //echo print_r($total_data);exit;
		//echo "value of total ".$total;
		$json['total'] = sprintf($_SESSION['OBJ']['tr']->translate('text_items_checkout_cart'), $cartObj->countProducts() + (isset($_SESSION['vouchers']) ? count($_SESSION['vouchers']) : 0), $this->currObj->format($total));

		$this->data['totals'] = $total_data;
		print_r($this->data['totals']);exit;

		//start modules
			// Modules
			$this->data['modules'] = array();

			 

			if (isset($_SESSION['redirect'])) {
      			$this->data['continue'] = $_SESSION['redirect'];

				unset($_SESSION['redirect']);
			} else {
				$this->data['continue'] = HTTP_SERVER.'index/index';//$this->url->link('common/home');
			}
		//end moduels

		$this->data['checkout'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);
		//$json['output'] ='';// ob_get_contents('http://localhost/mve_front/application/views/scripts/templates/default/checkout/cart-flash.phtml');
		//$this->render('cart-flash');//include 'checkout/cart-flash';
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
                        //exit
                        include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/cart-flash.phtml';
                    } else
                    {
                        //echo "else";
                        //exit;
                        include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/checkout/cart-flash.phtml';
                    }
			$json['output'] = ob_get_contents();
			ob_end_clean();
			//echo $this->output;
		echo Model_Json::encode($json);
        }

	/*public function cartFlashAction()
	{

	}*/

	public function voucherAction() {
		//ECHO $_SESSION['Curr'][currency];
		$this->setLangSession();
		$this->isAffiliateTrackingSet();
		$this->globalKeywords();
		$this->getHeader();
		$this->getFlashCart();
		$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_checkout_voucher')));

		//$this->document->setTitle($_SESSION['OBJ']['tr']->translate('heading_title'));
		$vouObj=new Model_Checkout_Voucher();
		$this->customer=new Model_Customer();
		if (!isset($_SESSION['vouchers'])) {
			$_SESSION['vouchers'] = array();
		}

    	if (($_SERVER['REQUEST_METHOD'] == 'POST') && $this->validatevoucher()) {
			$_SESSION['vouchers'][rand()] = array(
				'description'      => sprintf($_SESSION['OBJ']['tr']->translate('text_for_checkout_voucher'), $this->currObj->format($this->currObj->convert($this->_request->amount, $this->currObj->getCode(), constant('DEFAULT_CURRENCY'))), $this->_request->to_name),
				'to_name'          => $this->_request->to_name,
				'to_email'         => $this->_request->to_email,
				'from_name'        => $this->_request->from_name,
				'from_email'       => $this->_request->from_email,
				'message'          => $this->_request->message,
				'amount'           => $this->currObj->convert($this->_request->amount, $this->currObj->getCode(), DEFAULT_CURRENCY),
				'voucher_theme_id' => $this->_request->voucher_theme_id
			);

	  		//$this->redirect($this->url->link('checkout/voucher/success'));
			$this->_redirect(HTTP_SERVER.'checkout/voucher-success');
    	}
		$this->data[breadcrumbs] = array();

   		$this->data[breadcrumbs][] = array(
       		'text'      => $_SESSION['OBJ']['tr']->translate('text_home_common_header'),
			'href'      => HTTP_SERVER."index/index",
       		'separator' => false
   		);

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_voucher_checkout_voucher'),
			'href'      =>Model_Url::getLink(array("controller"=>"checkout","action"=>"voucher"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

            /*$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_checkout_voucher');

		$this->data['text_description'] = $_SESSION['OBJ']['tr']->translate('text_description_checkout_voucher');
		$this->data['text_agree'] = $_SESSION['OBJ']['tr']->translate('text_agree_checkout_voucher');

		$this->data['entry_to_name'] = $_SESSION['OBJ']['tr']->translate('entry_to_name_checkout_voucher');
		$this->data['entry_to_email'] = $_SESSION['OBJ']['tr']->translate('entry_to_email_checkout_voucher');
		$this->data['entry_from_name'] = $_SESSION['OBJ']['tr']->translate('entry_from_name_checkout_voucher');
		$this->data['entry_from_email'] = $_SESSION['OBJ']['tr']->translate('entry_from_email_checkout_voucher');
		$this->data['entry_message'] = $_SESSION['OBJ']['tr']->translate('entry_message_checkout_voucher');
		
		$this->data['entry_theme'] = $_SESSION['OBJ']['tr']->translate('entry_theme_checkout_voucher');

		$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');*/
        $vamt=explode(',',@constant('VOUCHER_MIN_MAX_VALUE'));
        $this->data['entry_amount'] = sprintf($_SESSION['OBJ']['tr']->translate('entry_amount_checkout_voucher'), $this->currObj->format($vamt[0], false, 1), $this->currObj->format($vamt[1], false, 1));

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['to_name'])) {
			$this->data['error_to_name'] = $this->error['to_name'];
		} else {
			$this->data['error_to_name'] = '';
		}

		if (isset($this->error['to_email'])) {
			$this->data['error_to_email'] = $this->error['to_email'];
		} else {
			$this->data['error_to_email'] = '';
		}

		if (isset($this->error['from_name'])) {
			$this->data['error_from_name'] = $this->error['from_name'];
		} else {
			$this->data['error_from_name'] = '';
		}

		if (isset($this->error['from_email'])) {
			$this->data['error_from_email'] = $this->error['from_email'];
		} else {
			$this->data['error_from_email'] = '';
		}

		if (isset($this->error['amount'])) {
			$this->data['error_amount'] = $this->error['amount'];
		} else {
			$this->data['error_amount'] = '';
		}

		if (isset($this->error['theme'])) {
			$this->data['error_theme'] = $this->error['theme'];
		} else {
			$this->data['error_theme'] = '';
		}

		$this->data['action'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"voucher"),'',SERVER_SSL);

		if (isset($this->_request->to_name)) {
			$this->data['to_name'] = $this->_request->to_name;
		} else {
			$this->data['to_name'] = '';
		}

		if (isset($this->_request->to_email)) {
			$this->data['to_email'] = $this->_request->to_email;
		} else {
			$this->data['to_email'] = '';
		}

		if (isset($this->_request->from_name)) {
			$this->data['from_name'] = $this->_request->from_name;
		} elseif ($this->customer->isLogged()) {
			$this->data['from_name'] = $this->customer->getFirstName() . ' '  . $this->customer->getLastName();
		} else {
			$this->data['from_name'] = '';
		}

		if (isset($this->_request->from_email)) {
			$this->data['from_email'] = $this->_request->from_email;
		} elseif ($this->customer->isLogged()) {
			$this->data['from_email'] = $this->customer->getEmail();
		} else {
			$this->data['from_email'] = '';
		}

		if (isset($this->_request->message)) {
			$this->data['message'] = $this->_request->message;
		} else {
			$this->data['message'] = '';
		}

		if (isset($this->_request->amount)) {
			$this->data['amount'] = $this->_request->amount;
		} else {
			$this->data['amount'] = '25.00';
		}

 		$voutheObj=new Model_Checkout_VoucherTheme();

		$this->data['voucher_themes'] = $voutheObj->getVoucherThemes();

    	if (isset($this->_request->voucher_theme_id)) {
      		$this->data['voucher_theme_id'] = $this->_request->voucher_theme_id;
		} else {
      		$this->data['voucher_theme_id'] = '';
    	}

		if (isset($this->_request->agree)) {
			$this->data['agree'] = $this->_request->agree;
		} else {
			$this->data['agree'] = false;
		}
		$this->view->data=$this->data;
            
            if (file_exists(PATH_TO_FILES.'checkout/voucher.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'checkout/');
                    $this->renderScript('voucher.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/voucher.phtml'))
            {
                $this->render('voucher');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/checkout/');
                $this->renderScript('voucher.phtml');
            }
	}

	private function validatevoucher() {
    	if ((strlen(utf8_decode($this->_request->to_name)) < 1) || (strlen(utf8_decode($this->_request->to_name)) > 64)) {
      		$this->error['to_name'] = $_SESSION['OBJ']['tr']->translate('error_to_name_checkout_voucher');
    	}

		if ((strlen(utf8_decode($this->_request->to_email)) > 96) || !filter_var($this->_request->to_email, FILTER_VALIDATE_EMAIL)) {
      		$this->error['to_email'] = $_SESSION['OBJ']['tr']->translate('error_email_checkout_voucher');
    	}

    	if ((strlen(utf8_decode($this->_request->from_name)) < 1) || (strlen(utf8_decode($this->_request->from_name)) > 64)) {
      		$this->error['from_name'] = $_SESSION['OBJ']['tr']->translate('error_from_name_checkout_voucher');
    	}

		if ((strlen(utf8_decode($this->_request->from_email)) > 96) || !filter_var($this->_request->from_email, FILTER_VALIDATE_EMAIL)) {
      		$this->error['from_email'] = $_SESSION['OBJ']['tr']->translate('error_email_checkout_voucher');
    	}
        $vamt=explode(',',@constant('VOUCHER_MIN_MAX_VALUE'));
	if (($this->_request->amount < $vamt[0]) || ($this->_request->amount > $vamt[1])) {
      		$this->error['amount'] = sprintf($_SESSION['OBJ']['tr']->translate('error_amount_checkout_voucher'), $this->currObj->format($vamt[0], false, 1), $this->currObj->format($vamt[1], false, 1) . ' ' . $this->currObj->getCode());
    	}

		if (!isset($this->_request->voucher_theme_id)) {
      		$this->error['theme'] = $_SESSION['OBJ']['tr']->translate('error_theme_checkout_voucher');
    	}

		if (!isset($this->_request->agree)) {
      		$this->error['warning'] = $_SESSION['OBJ']['tr']->translate('error_agree_checkout_voucher');
		}

    	if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
	}

	public function voucherSuccessAction() {
            
		$this->setLangSession();
		$this->isAffiliateTrackingSet();
		$this->globalKeywords();
		$this->getHeader();
		$this->getFlashCart();

$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_checkout_voucher')));
		//$this->document->setTitle($_SESSION['OBJ']['tr']->translate('heading_title_voucher'));

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_home_common_header'),
			'href'      => HTTP_SERVER,//$this->url->link('checkout/voucher'),
        	'separator' => ''
      	);
        
        $this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_checkout_voucher'),
			'href'      => HTTP_SERVER.'checkout/voucher',//$this->url->link('checkout/voucher'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

    	$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_checkout_voucher');

    	$this->data['text_message_success'] = $_SESSION['OBJ']['tr']->translate('text_message_checkout_voucher');

    	//$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');

    	//$this->data['continue'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart');


		$this->view->data=$this->data;
		//$this->renderScript('account/success.phtml');
             if (file_exists(PATH_TO_FILES.'account/success.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'account/');
                    $this->renderScript('success.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/success.phtml'))
            {
                //echo "in";
                $this->renderScript('account/success.phtml');
            } else
            {
                //echo "else";
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('success.phtml');
            }
	}

	public function loginAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$json = array();
		$STOCK_ALLOW_CHECKOUT=STOCK_ALLOW_CHECKOUT=='true'?'1':'0';
		if ((!$this->cart->hasProducts() && (!isset($_SESSION['vouchers']) || !$_SESSION['vouchers'])) || (!$this->cart->hasStock() && !$STOCK_ALLOW_CHECKOUT))     {
			$json['redirect'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart');
		}


		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (isset($this->_request->account)) {
				$_SESSION['account'] = $this->_request->account;
			}

			if (isset($this->_request->email) && isset($this->_request->password)) {
				if ($this->customer->login($this->_request->email, $this->_request->password)) {
					unset($_SESSION['guest']);

					$address_info = $this->accAddr->getAddress($this->customer->getAddressId());

					if ($address_info) {
						$this->tax->setZone($address_info['country_id'], $address_info['zone_id']);
					}
				} else {
					$json['error']['warning'] = $_SESSION['OBJ']['tr']->translate('error_login_checkout_checkout');
				}
			}
			//exit;
		} else {
			/*$this->data['text_new_customer'] = $_SESSION['OBJ']['tr']->translate('text_new_customer_checkout_checkout');
			$this->data['text_returning_customer'] = $_SESSION['OBJ']['tr']->translate('text_returning_customer_checkout_checkout');
			$this->data['text_checkout'] = $_SESSION['OBJ']['tr']->translate('text_checkout_checkout_checkout');
			$this->data['text_register'] = $_SESSION['OBJ']['tr']->translate('text_register_checkout_checkout_checkout_checkout');
			$this->data['text_guest'] = $_SESSION['OBJ']['tr']->translate('text_guest_checkout_checkout');
			$this->data['text_i_am_returning_customer'] = $_SESSION['OBJ']['tr']->translate('text_i_am_returning_customer_checkout_checkout');
			$this->data['text_register_account'] = $_SESSION['OBJ']['tr']->translate('text_register_account_checkout_checkout');
			$this->data['text_forgotten'] = $_SESSION['OBJ']['tr']->translate('text_forgotten_checkout_checkout');

			$this->data['entry_email'] = $_SESSION['OBJ']['tr']->translate('entry_email_checkout_checkout');
			$this->data['entry_password'] = $_SESSION['OBJ']['tr']->translate('entry_password_checkout_checkout');

			$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');
			$this->data['button_login'] = $_SESSION['OBJ']['tr']->translate('button_login');*/

			$ALLOW_GUEST_CHECKOUT=ALLOW_GUEST_CHECKOUT=='true'?'1':'0';
			$LOGIN_DISPLAY_PRICE=LOGIN_DISPLAY_PRICE=='true'?'1':'0';

			$this->data['guest_checkout'] = ($ALLOW_GUEST_CHECKOUT && !$LOGIN_DISPLAY_PRICE && !$this->cart->hasDownload());

			if (isset($_SESSION['account'])) {
				$this->data['account'] = $_SESSION['account'];
			} else {
				$this->data['account'] = 'register';
			}

			$this->data['forgotten'] = Model_Url::getLink(array("controller"=>"account","action"=>"forgotten"),'',SERVER_SSL);//HTTP_SERVER.'account/forgotten';//$this->url->link('account/forgotten', '', 'SSL');

 

			//$json['output'] =$this->data;
			$this->view->data=$this->data;
		   ob_start();
		   //include_once PATH_TO_TEMPLATES.'\default\checkout\login_mar_13_2012_before_design.phtml';
		   //include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/login.phtml';
                   if (file_exists(PATH_TO_FILES.'checkout/login.phtml'))
                    {
                            $this->view->addScriptPath(PATH_TO_FILES.'checkout/');
                            $this->renderScript('login.phtml');
                    }else
                    if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/login.phtml'))
                    {
                        include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/login.phtml';
                    } else
                    {
                        include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/checkout/login.phtml';
                    }
			$json['output'] = ob_get_contents();
			ob_end_clean();
		}
 		echo Model_Json::encode($json);
	}

	public function guestAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
                        
                $json = array();
                $this->data['shipping_required'] = $this->cart->hasShipping();
		if ($this->customer->isLogged()) {
			$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);//HTTP_SERVER.'checkout/checkout';//$this->url->link('checkout/checkout', '', 'SSL');
		}

		$STOCK_ALLOW_CHECKOUT=STOCK_ALLOW_CHECKOUT=='true'?'1':'0';
		if ((!$this->cart->hasProducts() && (!isset($_SESSION['vouchers']) || !$_SESSION['vouchers'])) || (!$this->cart->hasStock() && !$STOCK_ALLOW_CHECKOUT))
		{
			$json['redirect'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart');
		}

		$ALLOW_GUEST_CHECKOUT=ALLOW_GUEST_CHECKOUT=='true'?'1':'0';
		if (!$ALLOW_GUEST_CHECKOUT || $this->cart->hasDownload()) {
			$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);//HTTP_SERVER.'checkout/checkout';//$this->url->link('checkout/checkout', '', 'SSL');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (!$json) {
				if ((strlen(utf8_decode($this->_request->firstname)) < 1) || (strlen(utf8_decode($this->_request->firstname)) > 32)) {
					$json['error']['firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_checkout_checkout');
				}

				if ((strlen(utf8_decode($this->_request->lastname)) < 1) || (strlen(utf8_decode($this->_request->lastname)) > 32)) {
					$json['error']['lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_checkout_checkout');
				}

				if ((strlen(utf8_decode($this->_request->email)) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->_request->email)) {
					$json['error']['email'] = $_SESSION['OBJ']['tr']->translate('error_email_checkout_checkout');
				}

				if ((strlen(utf8_decode($this->_request->telephone)) < 3) || (strlen(utf8_decode($this->_request->telephone)) > 32)) {
                                    
					$json['error']['telephone'] = $_SESSION['OBJ']['tr']->translate('error_telephone_checkout_checkout');
				}

				if ((strlen(utf8_decode($this->_request->address_1)) < 3) || (strlen(utf8_decode($this->_request->address_1)) > 128)) {
					$json['error']['address_1'] = $_SESSION['OBJ']['tr']->translate('error_address_1_checkout_checkout');
				}

				if ((strlen(utf8_decode($this->_request->city)) < 2) || (strlen(utf8_decode($this->_request->city)) > 128)) {
					$json['error']['city'] = $_SESSION['OBJ']['tr']->translate('error_city_checkout_checkout');
				}

				$country_info = $this->locCtry->getCountry($this->_request->country_id);

				/*if ($country_info && $country_info['postcode_required'] && (strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
					$json['error']['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
				}*/

				if ((strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
					$json['error']['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
				}

				if ($this->_request->country_id == '') {
					$json['error']['country'] = $_SESSION['OBJ']['tr']->translate('error_country_checkout_checkout');
				}

				if ($this->_request->zone_id == '') {
					$json['error']['zone'] = $_SESSION['OBJ']['tr']->translate('error_zone_checkout_checkout');
				}
			}

			if (!$json) {
				$_SESSION['guest']['firstname'] = $this->_request->firstname;
				$_SESSION['guest']['lastname'] = $this->_request->lastname;
				$_SESSION['guest']['email'] = $this->_request->email;
				$_SESSION['guest']['telephone'] = $this->_request->telephone;
				$_SESSION['guest']['fax'] = $this->_request->fax;

				$_SESSION['guest']['payment']['firstname'] = $this->_request->firstname;
				$_SESSION['guest']['payment']['lastname'] = $this->_request->lastname;
				$_SESSION['guest']['payment']['company'] = $this->_request->company;
				$_SESSION['guest']['payment']['address_1'] = $this->_request->address_1;
				$_SESSION['guest']['payment']['address_2'] = $this->_request->address_2;
				$_SESSION['guest']['payment']['postcode'] = $this->_request->postcode;
				$_SESSION['guest']['payment']['city'] = $this->_request->city;
				$_SESSION['guest']['payment']['country_id'] = $this->_request->country_id;
				$_SESSION['guest']['payment']['zone_id'] = $this->_request->zone_id;


				$country_info = $this->locCtry->getCountry($this->_request->country_id);
                                if ($country_info) {
					$_SESSION['guest']['payment']['country'] = $country_info['countries_name'];
					$_SESSION['guest']['payment']['iso_code_2'] = $country_info['countries_iso_code_2'];
					$_SESSION['guest']['payment']['iso_code_3'] = $country_info['countries_iso_code_3'];
					$_SESSION['guest']['payment']['address_format'] = $country_info['address_format'];
				} else {
					$_SESSION['guest']['payment']['country'] = '';
					$_SESSION['guest']['payment']['iso_code_2'] = '';
					$_SESSION['guest']['payment']['iso_code_3'] = '';
					$_SESSION['guest']['payment']['address_format'] = '';
				}

				$zone_info = $this->locZone->getZone($this->_request->zone_id);

				if ($zone_info) {
					$_SESSION['guest']['payment']['zone'] = $zone_info['zone_name'];
					$_SESSION['guest']['payment']['zone_code'] = $zone_info['zone_code'];
				} else {
					$_SESSION['guest']['payment']['zone'] = '';
					$_SESSION['guest']['payment']['zone_code'] = '';
				}

				unset($_SESSION['shipping_methods']);
				unset($_SESSION['shipping']);
				unset($_SESSION['shipping_method']);
				unset($_SESSION['payment_methods']);
				unset($_SESSION['payment_method']);
				unset($_SESSION['payment']);
			}
                        //start guest shipping
                        if($this->data['shipping_required']!="")
                        {
                        if (isset($this->_request->shipping_address) && $this->_request->shipping_address) 
                        {
                      		$_SESSION['guest']['shipping_address'] = true;
			} else 
                        {
                      		$_SESSION['guest']['shipping_address'] = false;
			}


                        if ($_SESSION['guest']['shipping_address']) {
                            
                        $_SESSION['guest']['shipping']['firstname'] = $this->_request->firstname;
                        $_SESSION['guest']['shipping']['lastname'] = $this->_request->lastname;
                        $_SESSION['guest']['shipping']['company'] = $this->_request->company;
                        $_SESSION['guest']['shipping']['address_1'] = $this->_request->address_1;
                        $_SESSION['guest']['shipping']['address_2'] = $this->_request->address_2;
                        $_SESSION['guest']['shipping']['postcode'] = $this->_request->postcode;
                        $_SESSION['guest']['shipping']['city'] = $this->_request->city;
                        $_SESSION['guest']['shipping']['country_id'] = $this->_request->country_id;
                        $_SESSION['guest']['shipping']['zone_id'] = $this->_request->zone_id;

                        if ($country_info) {
                                $_SESSION['guest']['shipping']['country'] = $country_info['countries_name'];
                                $_SESSION['guest']['shipping']['iso_code_2'] = $country_info['countries_iso_code_2'];
                                $_SESSION['guest']['shipping']['iso_code_3'] = $country_info['countries_iso_code_3'];
                                $_SESSION['guest']['shipping']['address_format'] = $country_info['address_format'];
                        } else {
                                $_SESSION['guest']['shipping']['country'] = '';
                                $_SESSION['guest']['shipping']['iso_code_2'] = '';
                                $_SESSION['guest']['shipping']['iso_code_3'] = '';
                                $_SESSION['guest']['shipping']['address_format'] = '';
                        }

                        if ($zone_info) {
                                $_SESSION['guest']['shipping']['zone'] = $zone_info['zone_name'];
                                $_SESSION['guest']['shipping']['zone_code'] = $zone_info['zone_code'];
                        } else {
                                $_SESSION['guest']['shipping']['zone'] = '';
                                $_SESSION['guest']['shipping']['zone_code'] = '';
                        }

                        $this->tax->setZone($this->_request->country_id, $this->_request->zone_id);
                        }
                        /*echo "<pre>";
                        print_r($_SESSION['guest']['shipping']);
                        echo "</pre>";*/
                  
                        if($_SESSION['guest']['shipping_address']==false)
                        {
                           
                        if ((strlen(utf8_decode($this->_request->shipping_firstname)) < 1) || (strlen(utf8_decode($this->_request->shipping_firstname)) > 32)) {
				$json['error']['shipping_firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_checkout_checkout');
			}

			if ((strlen(utf8_decode($this->_request->shipping_lastname)) < 1) || (strlen(utf8_decode($this->_request->shipping_lastname)) > 32)) {
				$json['error']['shipping_lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_checkout_checkout');
			}

			if ((strlen(utf8_decode($this->_request->shipping_address_1)) < 3) || (strlen(utf8_decode($this->_request->shipping_address_1)) > 128)) {
				$json['error']['shipping_address_1'] = $_SESSION['OBJ']['tr']->translate('error_address_1_checkout_checkout');
			}

			if ((strlen(utf8_decode($this->_request->shipping_city)) < 2) || (strlen(utf8_decode($this->_request->shipping_city)) > 128)) {
				$json['error']['shipping_city'] = $_SESSION['OBJ']['tr']->translate('error_city_checkout_checkout');
			}

			$country_info = $this->locCtry->getCountry($this->_request->shipping_country_id);

			/*if ($country_info && $country_info['postcode_required'] && (strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
				$json['error']['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
			}*/

			if ((strlen(utf8_decode($this->_request->shipping_postcode)) < 2) || (strlen(utf8_decode($this->_request->shipping_postcode)) > 10)) {
				$json['error']['shipping_postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
			}

			if ($this->_request->shipping_country_id == '') {
				$json['error']['shipping_country'] = $_SESSION['OBJ']['tr']->translate('error_country_checkout_checkout');
			}

			if ($this->_request->shipping_zone_id == '') {
				$json['error']['shipping_zone'] = $_SESSION['OBJ']['tr']->translate('error_zone_checkout_checkout');
			}
                        
                        if (!$json) {
                      
				$_SESSION['guest']['shipping']['firstname'] = trim($this->_request->shipping_firstname);
				$_SESSION['guest']['shipping']['lastname'] = trim($this->_request->shipping_lastname);
				$_SESSION['guest']['shipping']['company'] = trim($this->_request->shipping_company);
				$_SESSION['guest']['shipping']['address_1'] = $this->_request->shipping_address_1;
				$_SESSION['guest']['shipping']['address_2'] = $this->_request->shipping_address_2;
				$_SESSION['guest']['shipping']['postcode'] = $this->_request->shipping_postcode;
				$_SESSION['guest']['shipping']['city'] = $this->_request->shipping_city;
				$_SESSION['guest']['shipping']['country_id'] = $this->_request->shipping_country_id;
				$_SESSION['guest']['shipping']['zone_id'] = $this->_request->shipping_zone_id;


				$country_info = $this->locCtry->getCountry($this->_request->shipping_country_id);

				if ($country_info) {
					$_SESSION['guest']['shipping']['country'] = $country_info['countries_name'];
					$_SESSION['guest']['shipping']['iso_code_2'] = $country_info['countries_iso_code_2'];
					$_SESSION['guest']['shipping']['iso_code_3'] = $country_info['countries_iso_code_3'];
					$_SESSION['guest']['shipping']['address_format'] = $country_info['address_format'];
				} else {
					$_SESSION['guest']['shipping']['country'] = '';
					$_SESSION['guest']['shipping']['iso_code_2'] = '';
					$_SESSION['guest']['shipping']['iso_code_3'] = '';
					$_SESSION['guest']['shipping']['address_format'] = '';
				}


				$zone_info = $this->locZone->getZone($this->_request->shipping_zone_id);

				if ($zone_info) {
					$_SESSION['guest']['shipping']['zone'] = $zone_info['zone_name'];
					$_SESSION['guest']['shipping']['zone_code'] = $zone_info['zone_code'];
				} else {
					$_SESSION['guest']['shipping']['zone'] = '';
					$_SESSION['guest']['shipping']['zone_code'] = '';
				}

				if ($this->cart->hasShipping()) {
					$this->tax->setZone($this->_request->shipping_country_id, $this->_request->shipping_zone_id);
				}
			}
                }
                
                        //end guest shipping
                }
    	} else {
			if (isset($_SESSION['guest']['firstname'])) {
				$this->data['firstname'] = $_SESSION['guest']['firstname'];
			} else {
				$this->data['firstname'] = '';
			}

			if (isset($_SESSION['guest']['lastname'])) {
				$this->data['lastname'] = $_SESSION['guest']['lastname'];
			} else {
				$this->data['lastname'] = '';
			}

			if (isset($_SESSION['guest']['email'])) {
				$this->data['email'] = $_SESSION['guest']['email'];
			} else {
				$this->data['email'] = '';
			}

			if (isset($_SESSION['guest']['telephone'])) {
				$this->data['telephone'] = $_SESSION['guest']['telephone'];
			} else {
				$this->data['telephone'] = '';
			}

			if (isset($_SESSION['guest']['fax'])) {
				$this->data['fax'] = $_SESSION['guest']['fax'];
			} else {
				$this->data['fax'] = '';
			}

			if (isset($_SESSION['guest']['payment']['company'])) {
				$this->data['company'] = $_SESSION['guest']['payment']['company'];
			} else {
				$this->data['company'] = '';
			}

			if (isset($_SESSION['guest']['payment']['address_1'])) {
				$this->data['address_1'] = $_SESSION['guest']['payment']['address_1'];
			} else {
				$this->data['address_1'] = '';
			}

			if (isset($_SESSION['guest']['payment']['address_2'])) {
				$this->data['address_2'] = $_SESSION['guest']['payment']['address_2'];
			} else {
				$this->data['address_2'] = '';
			}

			if (isset($_SESSION['guest']['payment']['postcode'])) {
				$this->data['postcode'] = $_SESSION['guest']['payment']['postcode'];
			} else {
				$this->data['postcode'] = '';
			}

			if (isset($_SESSION['guest']['payment']['city'])) {
				$this->data['city'] = $_SESSION['guest']['payment']['city'];
			} else {
				$this->data['city'] = '';
			}

			if (isset($_SESSION['guest']['payment']['country_id'])) {
				$this->data['country_id'] = $_SESSION['guest']['payment']['country_id'];
			} else {
				$this->data['country_id'] = STORE_COUNTRY;
			}

			if (isset($_SESSION['guest']['payment']['zone_id'])) {
				$this->data['zone_id'] = $_SESSION['guest']['payment']['zone_id'];
			} else {
				$this->data['zone_id'] = '';
			}

			$this->data['countries'] = $this->locCtry->getCountries();
                        if($this->data['shipping_required']!="")
                        {
			//echo "value of shipping required".$this->data['shipping_required'];
			if (isset($_SESSION['guest']['shipping_address'])) {
				$this->data['shipping_address'] = $_SESSION['guest']['shipping_address'];
			} else {
				$this->data['shipping_address'] = true;
			}
                        
                        //start shipping guest
                        if (isset($_SESSION['guest']['shipping']['firstname'])) {
				$this->data['shipping_firstname'] = $_SESSION['guest']['shipping']['firstname'];
			} else {
				$this->data['shipping_firstname'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['lastname'])) {
				$this->data['shipping_lastname'] = $_SESSION['guest']['shipping']['lastname'];
			} else {
				$this->data['shipping_lastname'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['company'])) {
				$this->data['shipping_company'] = $_SESSION['guest']['shipping']['company'];
			} else {
				$this->data['shipping_company'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['address_1'])) {
				$this->data['shipping_address_1'] = $_SESSION['guest']['shipping']['address_1'];
			} else {
				$this->data['shipping_address_1'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['address_2'])) {
				$this->data['shipping_address_2'] = $_SESSION['guest']['shipping']['address_2'];
			} else {
				$this->data['shipping_address_2'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['postcode'])) {
				$this->data['shipping_postcode'] = $_SESSION['guest']['shipping']['postcode'];
			} else {
				$this->data['shipping_postcode'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['city'])) {
				$this->data['shipping_city'] = $_SESSION['guest']['shipping']['city'];
			} else {
				$this->data['shipping_city'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['country_id'])) {
				$this->data['shipping_country_id'] = $_SESSION['guest']['shipping']['country_id'];
			} else {
				$this->data['shipping_country_id'] =STORE_COUNTRY;
			}

			if (isset($_SESSION['guest']['shipping']['zone_id'])) {
				$this->data['shipping_zone_id'] = $_SESSION['guest']['shipping']['zone_id'];
			} else {
				$this->data['shipping_zone_id'] = '';
			}

			$this->data['countries'] = $this->locCtry->getCountries();
                        //end shipping guest
                        }

 
			$this->view->data=$this->data;
			ob_start();
                        if (file_exists(PATH_TO_FILES.'checkout/guest.phtml'))
                        {
                            include_once PATH_TO_FILES.'checkout/guest.phtml';
                        }else
                        if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/guest.phtml'))
                        {
                            //echo "inside";
                            include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/guest.phtml';
                        } else
                        {
                            //echo "outside";
                            include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/checkout/guest.phtml';
                        }

			$json['output'] = ob_get_contents();
			ob_end_clean();
		}
                
                /*echo "<pre>";
                print_r($_SESSION['guest']);
                //print_r($this->data);
                echo "</pre>";
                exit;*/
		echo Model_Json::encode($json);
	}

	public function shippingAction()
	{
            
            /*echo "<pre>";
            print_r($_REQUEST);
            echo "</pre>";
            exit;*/
		$_SESSION[TObj]=$_SESSION['OBJ']['tr'];
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
                $shipping_required=$this->cart->hasShipping();
                $this->data[shipping_required]=$shipping_required;
                //echo "value of ".$shipping_required;
                //exit;
		$json = array();
		include APPLICATION_PATH.'/models/functions.php';
                //start payment
                $infoObj=new Model_Information();
	 	$payment_modules = new Model_Payment();
		$payment_modules->javascript_validation();
		$this->data[selection] = $payment_modules->selection ();
		$this->data[selection_format] = $payment_modules->selection_format();
                //end payment
                if($shipping_required!="")
                {
                    $total_weight = $this->cart->getWeight();//$cart->show_weight(); //quantity*weight
                    $total_count = $this->cart->countProducts();//$cart->count_contents();//total items in the cart

                    $shipping_modules = new Model_Shipping ();
                    $this->data['count_shipping_modules']=tep_count_shipping_modules();
                    $quotes = $shipping_modules->quote();
                    if ($this->customer->isLogged()) {
                            $shipping_address = $this->accAddr->getAddress($_SESSION['shipping_address_id']);
                    } elseif (isset($_SESSION['guest'])) {
                            $shipping_address = $_SESSION['guest']['shipping'];
                    }
                    
                    if (!isset($shipping_address)) 
                    {
			$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);
                    }
                }
                
                if ($this->customer->isLogged()) {
			$payment_address = $this->accAddr->getAddress($_SESSION['payment_address_id']);
		} elseif (isset($_SESSION['guest'])) {
			$payment_address = $_SESSION['guest']['payment'];
		}
                
                if (!isset($payment_address)) 
                {
			$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);
		}

		$STOCK_ALLOW_CHECKOUT=STOCK_ALLOW_CHECKOUT=='true'?'1':'0';
		if ((!$this->cart->hasProducts() && (!isset($_SESSION['vouchers']) || !$_SESSION['vouchers'])) || (!$this->cart->hasStock() && !$STOCK_ALLOW_CHECKOUT))      {
			$json['redirect'] = HTTP_SERVER.'checkout/cart';
		}

		if (defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
		$pass = false;

		switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
		case 'national':
		if ($_SESSION['shipping_country_id'] == STORE_COUNTRY) {
		$pass = true;
		}
		break;
		case 'international':
		if ($_SESSION['shipping_country_id'] != STORE_COUNTRY) {
		$pass = true;
		}
		break;
		case 'both':
		$pass = true;
		break;
		}

		$free_shipping = false;
		if ( ($pass == true) && ($this->cart->getTotal() >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
		$free_shipping = true;

		}
		} else {
		$free_shipping = false;
		}
                if (isset($this->_request->raction) && ($this->_request->raction == 'process')) {
		if ($shipping_required!="") 
                {
		
		if (!tep_session_is_registered('shipping')) tep_session_register('shipping'); //session registered at the time of submission

		if ( ($this->data['count_shipping_modules'] > 0) || ($free_shipping == true) ) {
		if ( (isset($this->_request->shipping)) && (strpos($this->_request->shipping, '_')) ) {
		$shipping = $this->_request->shipping;
		
                list($module, $method) = explode('_', $shipping);
		if ( is_object($GLOBALS[$module]) || ($shipping == 'free_free') ) {
		if ($shipping == 'free_free') {
		$quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
		$quote[0]['methods'][0]['cost'] = '0';
		} else {
                $quote = $shipping_modules->quote($method, $module);
		
                
		}
		if (isset($quote['error'])) {
		tep_session_unregister('shipping');
		tep_session_unregister('shipping_method');
		} else
		{
			if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
			$shipping = array('id' => $shipping,
			'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
			'cost' => $quote[0]['methods'][0]['cost']);
			$_SESSION['shipping']=$shipping; //coded added as session is not creating automatically
			$_SESSION['shipping_method']=$shipping; //coded added as session is not creating automatically
			}
		}
		} else {
		tep_session_unregister('shipping');
		tep_session_unregister('shipping_method');
		}
		}else //if nothing is selected
		{
			$json['error']['warning'] = $_SESSION['OBJ']['tr']->translate('error_shipping_checkout_checkout');
		}
		} else  //if no shipping active along with free then move to payment
		{
			$shipping = false;
			$_SESSION['shipping']="";
		}
                }
                
                //start payment
                if (!$json) 
                {
                    if (!isset($this->_request->payment)) 
                    {
                            $json['error']['warning'] = $_SESSION['OBJ']['tr']->translate('error_payment_checkout_checkout');
                    } else 
                    {
                            if (!isset($_SESSION['payment_methods'][$this->_request->payment])) 
                            {
                                    $json['error']['warning'] = $_SESSION['OBJ']['tr']->translate('error_payment_checkout_checkout');
                            }
                    }

                    if (CHECKOUT_TERMS) 
                    {
                           $information_info = $infoObj->getInformation(CHECKOUT_TERMS);
                            if ($information_info && !isset($this->_request->agree)) 
                            {
                                    $json['error']['warning'] = sprintf($_SESSION['OBJ']['tr']->translate('error_agree_checkout_checkout'), $information_info['title']);
                            }
                    }
		}

                if (!$json) {
                        $_SESSION['payment_method'] = $_SESSION['payment_methods'][$this->_request->payment];
                        $_SESSION['payment'] = $_SESSION['payment_methods'][$this->_request->payment];
                }
                //end payment

		}else
		{
                    
                if ($shipping_required!="") 
                {			
// get all available shipping quotes
			$this->data[quotes] = $shipping_modules->quote();
			$_SESSION['shipping_methods']=="";
			$_SESSION['shipping_methods']=$this->data[quotes];
			if (!tep_session_is_registered('shipping') || ( tep_session_is_registered('shipping') && ($shipping == false) && ($this->data['count_shipping_modules'] > 1) ) ) $shipping = $shipping_modules->cheapest();
                }

			$this->data['currencies']=$this->currObj;
                        //start payment
                        			$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');

                        $_SESSION['payment_methods']=$this->data[selection_format];
			if (isset($_SESSION['payment_methods']) && !$_SESSION['payment_methods']) {
				$this->data['error_warning'] = sprintf($_SESSION['OBJ']['tr']->translate('error_no_payment_checkout_checkout'), HTTP_SERVER.'information/contact');
			} else {
				$this->data['error_warning'] = '';
			}
			if (CHECKOUT_TERMS) 
                        {
				$information_info = $infoObj->getInformation(CHECKOUT_TERMS);
				if ($information_info) 
                                {
					$this->data['text_agree'] = sprintf($_SESSION['OBJ']['tr']->translate('text_agree_checkout_checkout'), Model_Url::getLink(array("controller"=>"ajax","action"=>"info"),'information_id/'.CHECKOUT_TERMS,SERVER_SSL),$information_info['title'], $information_info['title']);
				} else 
                                {
					$this->data['text_agree'] = '';
				}
			} else {
                            $this->data['text_agree'] = '';
			}

			if (isset($_SESSION['agree'])) {
                            $this->data['agree'] = $_SESSION['agree'];
			} else {
                            $this->data['agree'] = '';
			}
                        //end payment
			$this->view->data=$this->data;
			ob_start();
			//include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/shipping.phtml';
                        if (file_exists(PATH_TO_FILES.'checkout/shipping.phtml'))
                        {
                                $this->view->addScriptPath(PATH_TO_FILES.'checkout/');
                                $this->renderScript('shipping.phtml');
                        }else    
                        if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/shipping.phtml'))
                        {
                             include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/shipping.phtml';
                        } else
                        {
                             include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/checkout/shipping.phtml';
                        }
			$json['output'] = ob_get_contents();
			//echo ob_get_contents();
			ob_end_clean();
		}
		//	exit;
		echo Model_Json::encode($json);

	}

	public function guestshippingAction() {
			$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$json = array();

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if ((strlen(utf8_decode($this->_request->firstname)) < 1) || (strlen(utf8_decode($this->_request->firstname)) > 32)) {
				$json['error']['firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_checkout_checkout');
			}

			if ((strlen(utf8_decode($this->_request->lastname)) < 1) || (strlen(utf8_decode($this->_request->lastname)) > 32)) {
				$json['error']['lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_checkout_checkout');
			}

			if ((strlen(utf8_decode($this->_request->address_1)) < 3) || (strlen(utf8_decode($this->_request->address_1)) > 128)) {
				$json['error']['address_1'] = $_SESSION['OBJ']['tr']->translate('error_address_1_checkout_checkout');
			}

			if ((strlen(utf8_decode($this->_request->city)) < 2) || (strlen(utf8_decode($this->_request->city)) > 128)) {
				$json['error']['city'] = $_SESSION['OBJ']['tr']->translate('error_city_checkout_checkout');
			}

			$country_info = $this->locCtry->getCountry($this->_request->country_id);

			/*if ($country_info && $country_info['postcode_required'] && (strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
				$json['error']['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
			}*/

			if ((strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
				$json['error']['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
			}

			if ($this->_request->country_id == '') {
				$json['error']['country'] = $_SESSION['OBJ']['tr']->translate('error_country_checkout_checkout');
			}

			if ($this->_request->zone_id == '') {
				$json['error']['zone'] = $_SESSION['OBJ']['tr']->translate('error_zone_checkout_checkout');
			}

			if (!$json) {
				$_SESSION['guest']['shipping']['firstname'] = trim($this->_request->firstname);
				$_SESSION['guest']['shipping']['lastname'] = trim($this->_request->lastname);
				$_SESSION['guest']['shipping']['company'] = trim($this->_request->company);
				$_SESSION['guest']['shipping']['address_1'] = $this->_request->address_1;
				$_SESSION['guest']['shipping']['address_2'] = $this->_request->address_2;
				$_SESSION['guest']['shipping']['postcode'] = $this->_request->postcode;
				$_SESSION['guest']['shipping']['city'] = $this->_request->city;
				$_SESSION['guest']['shipping']['country_id'] = $this->_request->country_id;
				$_SESSION['guest']['shipping']['zone_id'] = $this->_request->zone_id;


				$country_info = $this->locCtry->getCountry($this->_request->country_id);

				if ($country_info) {
					$_SESSION['guest']['shipping']['country'] = $country_info['countries_name'];
					$_SESSION['guest']['shipping']['iso_code_2'] = $country_info['countries_iso_code_2'];
					$_SESSION['guest']['shipping']['iso_code_3'] = $country_info['countries_iso_code_3'];
					$_SESSION['guest']['shipping']['address_format'] = $country_info['address_format'];
				} else {
					$_SESSION['guest']['shipping']['country'] = '';
					$_SESSION['guest']['shipping']['iso_code_2'] = '';
					$_SESSION['guest']['shipping']['iso_code_3'] = '';
					$_SESSION['guest']['shipping']['address_format'] = '';
				}


				$zone_info = $this->locZone->getZone($this->_request->zone_id);

				if ($zone_info) {
					$_SESSION['guest']['shipping']['zone'] = $zone_info['zone_name'];
					$_SESSION['guest']['shipping']['zone_code'] = $zone_info['zone_code'];
				} else {
					$_SESSION['guest']['shipping']['zone'] = '';
					$_SESSION['guest']['shipping']['zone_code'] = '';
				}

				if ($this->cart->hasShipping()) {
					$this->tax->setZone($this->_request->country_id, $this->_request->zone_id);
				}
			}
		} else {
			if (isset($_SESSION['guest']['shipping']['firstname'])) {
				$this->data['firstname'] = $_SESSION['guest']['shipping']['firstname'];
			} else {
				$this->data['firstname'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['lastname'])) {
				$this->data['lastname'] = $_SESSION['guest']['shipping']['lastname'];
			} else {
				$this->data['lastname'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['company'])) {
				$this->data['company'] = $_SESSION['guest']['shipping']['company'];
			} else {
				$this->data['company'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['address_1'])) {
				$this->data['address_1'] = $_SESSION['guest']['shipping']['address_1'];
			} else {
				$this->data['address_1'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['address_2'])) {
				$this->data['address_2'] = $_SESSION['guest']['shipping']['address_2'];
			} else {
				$this->data['address_2'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['postcode'])) {
				$this->data['postcode'] = $_SESSION['guest']['shipping']['postcode'];
			} else {
				$this->data['postcode'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['city'])) {
				$this->data['city'] = $_SESSION['guest']['shipping']['city'];
			} else {
				$this->data['city'] = '';
			}

			if (isset($_SESSION['guest']['shipping']['country_id'])) {
				$this->data['country_id'] = $_SESSION['guest']['shipping']['country_id'];
			} else {
				$this->data['country_id'] =STORE_COUNTRY;
			}

			if (isset($_SESSION['guest']['shipping']['zone_id'])) {
				$this->data['zone_id'] = $_SESSION['guest']['shipping']['zone_id'];
			} else {
				$this->data['zone_id'] = '';
			}

			$this->data['countries'] = $this->locCtry->getCountries();

			/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/guest_shipping.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/checkout/guest_shipping.tpl';
			} else {
				$this->template = SITE_DEFAULT_TEMPLATE.'/template/checkout/guest_shipping.tpl';
			}*/

			$this->view->data=$this->data;
			ob_start();
			//include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/guest-shipping.phtml';
                        if (file_exists(PATH_TO_FILES.'checkout/guest-shipping.phtml'))
                        {
                            include_once PATH_TO_FILES.'checkout/guest-shipping.phtml';
                        }else
                        if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/guest-shipping.phtml'))
                        {
                            include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/guest-shipping.phtml';
                        } else
                        {
                            include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/checkout/guest-shipping.phtml';
                        }
			$json['output'] = ob_get_contents();
			ob_end_clean();

			}

		echo Model_Json::encode($json);
	}


	public function paymentAction()
	{
	 	$json = array();
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$infoObj=new Model_Information();

	 	include APPLICATION_PATH.'/models/functions.php';

	 	$payment_modules = new Model_Payment();
		$payment_modules->javascript_validation ();
		$this->data[selection] = $payment_modules->selection ();
		$this->data[selection_format] = $payment_modules->selection_format();
		//echo "<pre>";
                //print_r($this->data[selection]);
	 if ($this->customer->isLogged()) {
			$payment_address = $this->accAddr->getAddress($_SESSION['payment_address_id']);
		} elseif (isset($_SESSION['guest'])) {
			$payment_address = $_SESSION['guest']['payment'];
		}

		if (!isset($payment_address)) {
			$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);//HTTP_SERVER.'checkout/checkout';//$this-cho >url->link('checkout/checkout', '', 'SSL');
		}

		$STOCK_ALLOW_CHECKOUT=STOCK_ALLOW_CHECKOUT=='true'?'1':'0';
		if ((!$this->cart->hasProducts() && (!isset($_SESSION['vouchers']) || !$_SESSION['vouchers'])) || (!$this->cart->hasStock() && !$STOCK_ALLOW_CHECKOUT)) {
			$json['redirect'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST')  //after submission
		//if (isset($this->_request->payment) && ($this->_request->payment != ''))  //after submission
		{

			if (!$json) {
				if (!isset($this->_request->payment)) {
				//echo "in if ";
			//exit;
					$json['error']['warning'] = $_SESSION['OBJ']['tr']->translate('error_payment_checkout_checkout');
					//echo "here";
				} else {
				//echo "in else";
				//exit;
					if (!isset($_SESSION['payment_methods'][$this->_request->payment])) {
						$json['error']['warning'] = $_SESSION['OBJ']['tr']->translate('error_payment_checkout_checkout');
					}
				}

				if (CHECKOUT_TERMS) {

					$information_info = $infoObj->getInformation(CHECKOUT_TERMS);

					if ($information_info && !isset($this->_request->agree)) {
					//exit;
						$json['error']['warning'] = sprintf($_SESSION['OBJ']['tr']->translate('error_agree_checkout_checkout'), $information_info['title']);
					}
				}
			}


			if (!$json) {
				$_SESSION['payment_method'] = $_SESSION['payment_methods'][$this->_request->payment];
				$_SESSION['payment'] = $_SESSION['payment_methods'][$this->_request->payment];

				//$_SESSION['comment'] = strip_tags($this->_request->comment);
			}

		}else
		{
			/*if (!isset($this->_request->payment)) {
					$json['error']['warning'] = $_SESSION['OBJ']['tr']->translate('error_payment');
				}*/

			$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');
			$_SESSION['payment_methods']=$this->data[selection_format];
			if (isset($_SESSION['payment_methods']) && !$_SESSION['payment_methods']) {
				//$this->data['error_warning'] = sprintf($_SESSION['OBJ']['tr']->translate('error_no_payment'), $this->url->link('information/contact'));
				$this->data['error_warning'] = sprintf($_SESSION['OBJ']['tr']->translate('error_no_payment_checkout_checkout'), HTTP_SERVER.'information/contact');
			} else {
				$this->data['error_warning'] = '';
			}
				if (CHECKOUT_TERMS) {
				 $information_info = $infoObj->getInformation(CHECKOUT_TERMS);

				if ($information_info) {
					//$this->data['text_agree'] = sprintf($_SESSION['OBJ']['tr']->translate('text_agree'), $this->url->link('information/information/info', 'information_id=' . $this->config->get('config_checkout_id'), 'SSL'), $information_info['title'], $information_info['title']);

					$this->data['text_agree'] = sprintf($_SESSION['OBJ']['tr']->translate('text_agree_checkout_checkout'), Model_Url::getLink(array("controller"=>"ajax","action"=>"info"),'information_id/'.CHECKOUT_TERMS,SERVER_SSL),$information_info['title'], $information_info['title']);
				} else {
					$this->data['text_agree'] = '';
				}
			} else {
				$this->data['text_agree'] = '';
			}

			if (isset($_SESSION['agree'])) {
				$this->data['agree'] = $_SESSION['agree'];
			} else {
				$this->data['agree'] = '';
			}
 			$this->view->data=$this->data;
			ob_start();
			//include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/payment.phtml';
                        if (file_exists(PATH_TO_FILES.'checkout/payment.phtml'))
                        {
                                include_once PATH_TO_FILES.'checkout/payment.phtml';
                        }else
                        if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/payment.phtml'))
                        {
                            include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/payment.phtml';
                        } else
                        {
                            include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/checkout/payment.phtml';
                        }
			$json['output'] = ob_get_contents();
			ob_end_clean();
		}

		echo Model_Json::encode($json);
	}

	public function confirmAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$this->data[tr]=$_SESSION['OBJ']['tr'];
		include APPLICATION_PATH.'/models/functions.php';
		$json = array();
		//$this->data[payment_modules] = new Model_Payment($_SESSION['payment']);
		$this->data[payment_modules] = new Model_Payment($_SESSION['payment']['id']);

		$this->data[payment_modules]->update_status();

		$STOCK_ALLOW_CHECKOUT=STOCK_ALLOW_CHECKOUT=='true'?'1':'0';
                
		if ((!$this->cart->hasProducts() && (!isset($_SESSION['vouchers']) || !$_SESSION['vouchers'])) || (!$this->cart->hasStock() && !$STOCK_ALLOW_CHECKOUT))
		{
                        //echo "in redirect 1";
			//$json['redirect'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart');
			$this->_redirect('checkout/cart');
		}

		
	  if ( ($this->data[payment_modules]->selected_module != $_SESSION['payment']['id']) || ( is_array($this->data[payment_modules]->modules) && (sizeof($this->data[payment_modules]->modules) > 1) && !is_object($_SESSION[$_SESSION['payment']['id']]) ) || (is_object($_SESSION[$_SESSION['payment']['id']]) && ($_SESSION[$_SESSION['payment']['id']]->enabled == false)) ) {
		//ECHO "<br/>in condition";
		//tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));
		//header("location:checkout_payment.php?error_message=no payment mode seleceted");
		//exit;

		$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'error_message/no payment mode selected',SERVER_SSL);//HTTP_SERVER.'checkout/checkout/error_message/no payment mode selected';//$this->url->link('checkout/checkout', '', 'SSL');
	  }

	  if (is_array($this->data[payment_modules]->modules)) {
		$this->data[payment_modules]->pre_confirmation_check();
	  }

		if ($this->customer->isLogged()) {
			$payment_address = $this->accAddr->getAddress($_SESSION['payment_address_id']);
		} elseif (isset($_SESSION['guest'])) {
			$payment_address = $_SESSION['guest']['payment'];
		}

		if (!isset($payment_address)) {
                     
			$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);//HTTP_SERVER.'checkout/checkout';//$this->url->link('checkout/checkout', '', 'SSL');
		}

		if (!isset($_SESSION['payment_method'])) {
                     
	  		$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);//HTTP_SERVER.'checkout/checkout';//$this->url->link('checkout/checkout', '', 'SSL');
    	}

    	if ($this->cart->hasShipping()) {
			if ($this->customer->isLogged()) {
				$shipping_address = $this->accAddr->getAddress($_SESSION['shipping_address_id']);
			} elseif (isset($_SESSION['guest'])) {
				$shipping_address = $_SESSION['guest']['shipping'];
			}

			if (!isset($shipping_address)) {
                            
				$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);//HTTP_SERVER.'checkout/checkout';//$this->url->link('checkout/checkout', '', 'SSL');
			}

			if (!isset($_SESSION['shipping_method'])) {
                            
	  			$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);//HTTP_SERVER.'checkout/checkout';//$this->url->link('checkout/checkout', '', 'SSL');
    		}
		} else {
			unset($_SESSION['guest']['shipping']);
			unset($_SESSION['shipping']);
			unset($_SESSION['shipping_address_id']);
			unset($_SESSION['shipping_method']);
			unset($_SESSION['shipping_methods']);
		}



		if (!$json) {
						$total_data = array();
			$total = 0;
			$taxes = $this->cart->getTaxes();

			$sort_order = array();
			 $otmodules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
			 foreach($otmodules as $k=>$v)
			 {
				$sort_order[]=constant('MODULE_ORDER_TOTAL_'.strtoupper(substr(substr($v,0,-4),2)).'_SORT_ORDER')."-".substr(substr($v,0,-4),2);

			 }
			sort($sort_order,SORT_NUMERIC);
			 $results=array();

			foreach($sort_order as $k=>$v)
			{
				$exp=explode("-",$v);
				if(constant('MODULE_ORDER_TOTAL_'.strtoupper($exp[1]).'_STATUS')=='true')
				{
					$class='Model_OrderTotal_Ot'.$exp[1];
					$oTobj=new $class;

					$oTobj->getTotal($total_data, $total, $taxes);

				}
			}

			$sort_order = array();

			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $total_data);

			$this->data['totals'] = $total_data;

			/*$total_data = array();
			$total = 0;
			$taxes = $this->cart->getTaxes();

			$sort_order = array();

			$results = $this->model_setting_extension->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);

					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
				}
			}

			$sort_order = array();

			foreach ($total_data as $key => $value) {
				$sort_order[$key] = $value['sort_order'];
			}

			array_multisort($sort_order, SORT_ASC, $total_data);*/

			$data = array();

			$data['invoice_prefix'] = INVOICE_PREFIX;
			$data['store_name'] = STORE_NAME;

			if ($this->customer->isLogged()) {
				$data['customer_id'] = $this->customer->getId();
				$data['customer_group_id'] = $this->customer->getCustomerGroupId();
				$data['firstname'] = $this->customer->getFirstName();
				$data['lastname'] = $this->customer->getLastName();
				$data['email'] = $this->customer->getEmail();
				$data['telephone'] = $this->customer->getTelephone();
				$data['fax'] = $this->customer->getFax();

 				$payment_address = $this->accAddr->getAddress($_SESSION['payment_address_id']);
			} elseif (isset($_SESSION['guest'])) {
				$data['customer_id'] = 0;
				$data['customer_group_id'] = DEFAULT_CGROUP;
				$data['firstname'] = $_SESSION['guest']['firstname'];
				$data['lastname'] = $_SESSION['guest']['lastname'];
				$data['email'] = $_SESSION['guest']['email'];
				$data['telephone'] = $_SESSION['guest']['telephone'];
				$data['fax'] = $_SESSION['guest']['fax'];

				$payment_address = $_SESSION['guest']['payment'];
			}

			$data['payment_firstname'] = $payment_address['firstname'];
			$data['payment_lastname'] = $payment_address['lastname'];
			$data['payment_company'] = $payment_address['company'];
			$data['payment_address_1'] = $payment_address['address_1'];
			$data['payment_address_2'] = $payment_address['address_2'];
			$data['payment_city'] = $payment_address['city'];
			$data['payment_postcode'] = $payment_address['postcode'];
			$data['payment_zone'] = $payment_address['zone'];
			$data['payment_zone_id'] = $payment_address['zone_id'];
			$data['payment_country'] = $payment_address['country'];
			$data['payment_country_id'] = $payment_address['country_id'];
			$data['payment_address_format'] = $payment_address['address_format'];

			if (isset($_SESSION['payment_method']['module'])) {
				$data['payment_method'] = $_SESSION['payment_method']['module'];
			} else {
				$data['payment_method'] = '';
			}

			if ($this->cart->hasShipping()) {
				if ($this->customer->isLogged()) {
					$shipping_address = $this->accAddr->getAddress($_SESSION['shipping_address_id']);
				} elseif (isset($_SESSION['guest'])) {
					$shipping_address = $_SESSION['guest']['shipping'];
				}

				$data['shipping_firstname'] = $shipping_address['firstname'];
				$data['shipping_lastname'] = $shipping_address['lastname'];
				$data['shipping_company'] = $shipping_address['company'];
				$data['shipping_address_1'] = $shipping_address['address_1'];
				$data['shipping_address_2'] = $shipping_address['address_2'];
				$data['shipping_city'] = $shipping_address['city'];
				$data['shipping_postcode'] = $shipping_address['postcode'];
				$data['shipping_zone'] = $shipping_address['zone'];
				$data['shipping_zone_id'] = $shipping_address['zone_id'];
				$data['shipping_country'] = $shipping_address['country'];
				$data['shipping_country_id'] = $shipping_address['country_id'];
				$data['shipping_address_format'] = $shipping_address['address_format'];

				if (isset($_SESSION['shipping_method']['title'])) {
					$data['shipping_method'] = $_SESSION['shipping_method']['title'];
				} else {
					$data['shipping_method'] = '';
				}
			} else {
				$data['shipping_firstname'] = '';
				$data['shipping_lastname'] = '';
				$data['shipping_company'] = '';
				$data['shipping_address_1'] = '';
				$data['shipping_address_2'] = '';
				$data['shipping_city'] = '';
				$data['shipping_postcode'] = '';
				$data['shipping_zone'] = '';
				$data['shipping_zone_id'] = '';
				$data['shipping_country'] = '';
				$data['shipping_country_id'] = '';
				$data['shipping_address_format'] = '';
				$data['shipping_method'] = '';
			}
			/*echo "<pre>";
			print_r($shipping_address);
			print_r($payment_address);
			echo "</pre>";
			exit;*/
			if ($this->cart->hasShipping()) {
				$this->tax->setZone($shipping_address['country_id'], $shipping_address['zone_id']);
			} else {
				$this->tax->setZone($payment_address['country_id'], $payment_address['zone_id']);
			}

			$product_data = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $option['option_value'],
							'type'                    => $option['type']
						);
					} else {

						$encryption = new Model_Encryption(ENCRYPTION_KEY);

						$option_data[] = array(
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'product_option_id'       => $option['product_option_id'],
							'product_option_value_id' => $option['product_option_value_id'],
							'option_id'               => $option['option_id'],
							'option_value_id'         => $option['option_value_id'],
							'name'                    => $option['name'],
							'value'                   => $encryption->decrypt($option['option_value']),
							'type'                    => $option['type']
						);
					}
				}

				$product_data[] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'download'   => $product['download'],
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'price'      => $product['price'],
					'total'      => $product['total'],
					'tax'        => $this->tax->getRate($product['tax_class_id'])
				);
			}

			// Gift Voucher
			if (isset($_SESSION['vouchers']) && $_SESSION['vouchers']) {
				foreach ($_SESSION['vouchers'] as $voucher) {
					$product_data[] = array(
						'product_id' => 0,
						'name'       => $voucher['description'],
						'model'      => '',
						'option'     => array(),
						'download'   => array(),
						'quantity'   => 1,
						'subtract'   => false,
						'price'      => $voucher['amount'],
						'total'      => $voucher['amount'],
						'tax'        => 0
					);
				}
			}

			$data['products'] = $product_data;
			$data['totals'] = $total_data;
			//$data['comment'] = $_SESSION['comment'];
			$data['total'] = $total;
			$data['reward'] = $this->cart->getTotalRewardPoints();


			if (isset($_COOKIE['tracking'])) {
			$affObj=new Model_Affiliate();
			$affiliate_info = $affObj->getAffiliateByCode($_COOKIE['tracking']);
				if ($affiliate_info) {
					$data['affiliate_id'] = $affiliate_info['affiliate_id'];
					$data['commission'] = ($total / 100) * $affiliate_info['commission'];
				} else {
					$data['affiliate_id'] = 0;
					$data['commission'] = 0;
				}
			} else {
				$data['affiliate_id'] = 0;
				$data['commission'] = 0;
			}

			$data['language_id'] =$_SESSION['Lang']['language_id'];
			$data['currency_id'] = $this->currObj->getId();
			$data['currency_code'] = $this->currObj->getCode();
			$data['currency_value'] = $this->currObj->get_value($_SESSION['Curr']['currency']);
			$data['ip'] = $_SERVER['REMOTE_ADDR'];
			
			//$this->load->model('checkout/order');
			$ordObj=new Model_CheckoutOrder();
			$_SESSION['order_id'] = $ordObj->create($data);

			// Gift Voucher
			if (isset($_SESSION['vouchers']) && is_array($_SESSION['vouchers'])) {
				$VouObj=new Model_CheckoutVoucher();
				foreach ($_SESSION['vouchers'] as $voucher) {
					$VouObj->addVoucher($_SESSION['order_id'], $voucher);
				}
			}

			/*$this->data['column_name'] = $_SESSION['OBJ']['tr']->translate('column_name_checkout_checkout');
			$this->data['column_model'] = $_SESSION['OBJ']['tr']->translate('column_model_checkout_checkout');
			$this->data['column_quantity'] = $_SESSION['OBJ']['tr']->translate('column_quantity_checkout_checkout');
			$this->data['column_price'] = $_SESSION['OBJ']['tr']->translate('column_price_checkout_checkout');
			$this->data['column_total'] = $_SESSION['OBJ']['tr']->translate('column_total_checkout_checkout');*/

			$this->data['products'] = array();

			foreach ($this->cart->getProducts() as $product) {
				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => (strlen($option['option_value']) > 20 ? substr($option['option_value'], 0, 20) . '..' : $option['option_value'])
						);
					} else {

						$file = substr($encryption->decrypt($option['option_value']), 0, strrpos($encryption->decrypt($option['option_value']), '.'));

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => (strlen($file) > 20 ? substr($file, 0, 20) . '..' : $file)
						);
					}
				}

				$this->data['products'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'],
					'option'     => $option_data,
					'quantity'   => $product['quantity'],
					'subtract'   => $product['subtract'],
					'tax'        => $this->tax->getRate($product['tax_class_id']),
					'price'      => $this->currObj->format($product['price']),
					'total'      => $this->currObj->format($product['total']),
					'href'       => HTTP_SERVER.'product/product-details/product_id/'.$product['product_id']//$this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}

			// Gift Voucher
			$this->data['vouchers'] = array();

			if (isset($_SESSION['vouchers']) && $_SESSION['vouchers']) {
				foreach ($_SESSION['vouchers'] as $key => $voucher) {
					$this->data['vouchers'][] = array(
						'description' => $voucher['description'],
						'amount'      => $this->currObj->format($voucher['amount'])
					);
				}
			}

			$this->data['totals'] = $total_data;
                        //echo "<pre>";
                        //print_r($this->data);

			//$this->data['payment'] = $this->getChild('payment/' . $_SESSION['payment_method']['code']);

			/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/confirm.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/checkout/confirm.tpl';
			} else {
				$this->template = SITE_DEFAULT_TEMPLATE.'/template/checkout/confirm.tpl';
			}*/
				$this->view->data=$this->data;
				ob_start();
				//include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/confirm.phtml';
                                if (file_exists(PATH_TO_FILES.'checkout/confirm.phtml'))
                                {
                                    include_once PATH_TO_FILES.'checkout/confirm.phtml';
                                }else
                                if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/confirm.phtml'))
                                {
                                    include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/confirm.phtml';
                                } else
                                {
                                    include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/checkout/confirm.phtml';
                                }
				$json['output'] = ob_get_contents();
				ob_end_clean();
		}
                echo Model_Json::encode($json);
	}

	public function addresspaymentAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
                $shipping_required=$this->cart->hasShipping();
                $this->data[shipping_required]=$shipping_required;    
		$json = array();
                if ($this->customer->isLogged()==0 && !is_array($_SESSION['guest'])) 
                {
                	$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);
		}else if(is_array($_SESSION['guest']) && $this->customer->isLogged()==0)
                {
                    $this->guestAction();
                    exit;
                }

		$STOCK_ALLOW_CHECKOUT=STOCK_ALLOW_CHECKOUT=='true'?'1':'0';
		if ((!$this->cart->hasProducts() && (!isset($_SESSION['vouchers']) || !$_SESSION['vouchers'])) || (!$this->cart->hasStock() && !$STOCK_ALLOW_CHECKOUT)) {
			$json['redirect'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (!$json) {
				if ($this->_request->payment_address == 'existing') {
					if (!isset($this->_request->payment_address_id)) {
						$json['error']['warning'] = $_SESSION['OBJ']['tr']->translate('error_address_checkout_checkout');
					}

					if (!$json) {
						$_SESSION['payment_address_id'] = $this->_request->payment_address_id;

						unset($_SESSION['payment_methods']);
						unset($_SESSION['payment']);
						unset($_SESSION['payment_method']);
					}
				}
                                
                                	if ($this->_request->shipping_address == 'existing') {
					if (!isset($this->_request->shipping_address_id)) {
						$json['error']['warning'] = $_SESSION['OBJ']['tr']->translate('error_address_checkout_checkout');
					}

				if (!$json) {
						$_SESSION['shipping_address_id'] = $this->_request->shipping_address_id;

						$address_info = $this->accAddr->getAddress($this->_request->shipping_address_id);

						if ($address_info) {
							$this->tax->setZone($address_info['country_id'], $address_info['zone_id']);
						}

						unset($_SESSION['shipping_methods']);
						unset($_SESSION['shipping']);
						unset($_SESSION['shipping_method']);
					}
				}

				if ($this->_request->payment_address == 'new') {
					if ((strlen(utf8_decode($this->_request->payment_firstname)) < 1) || (strlen(utf8_decode($this->_request->payment_firstname)) > 32)) {
						$json['error']['payment_firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_checkout_checkout');
					}

					if ((strlen(utf8_decode($this->_request->payment_lastname)) < 1) || (strlen(utf8_decode($this->_request->payment_lastname)) > 32)) {
						$json['error']['payment_lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_checkout_checkout');
					}

					if ((strlen(utf8_decode($this->_request->payment_address_1)) < 3) || (strlen(utf8_decode($this->_request->payment_address_1)) > 64)) {
						$json['error']['payment_address_1'] = $_SESSION['OBJ']['tr']->translate('error_address_1_checkout_checkout');
					}

					if ((strlen(utf8_decode($this->_request->payment_city)) < 2) || (strlen(utf8_decode($this->_request->payment_city)) > 32))                                         {
						$json['error']['payment_city'] = $_SESSION['OBJ']['tr']->translate('error_city_checkout_checkout');
					}

					/*$country_info = $this->locCtry->getCountry($this->_request->country_id);

					if ($country_info && $country_info['postcode_required'] && (strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
						$json['error']['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
					}*/

					if ((strlen(utf8_decode($this->_request->payment_postcode)) < 2) || (strlen(utf8_decode($this->_request->payment_postcode)) > 10)) {
						$json['error']['payment_postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
					}

					if ($this->_request->payment_country_id == '') {
						$json['error']['payment_country'] = $_SESSION['OBJ']['tr']->translate('error_country_checkout_checkout');
					}

					if ($this->_request->payment_zone_id == '') {
						$json['error']['payment_zone'] = $_SESSION['OBJ']['tr']->translate('error_zone_checkout_checkout');
					}

					if (!$json) {
                                          $paymentaddressarray=array("firstname"=>$this->_request->payment_firstname,"lastname"=>$this->_request->payment_lastname,"company"=>$this->_request->payment_company,"address_1"=>$this->_request->payment_address_1,"address_2"=>$this->_request->payment_address_2,"city"=>$this->_request->payment_city,"postcode"=>$this->_request->payment_postcode,"country_id"=>$this->_request->payment_country_id,"zone_id"=>$this->_request->payment_zone_id);
						$_SESSION['payment_address_id'] = $this->accAddr->addAddress($paymentaddressarray);

						unset($_SESSION['payment_methods']);
						unset($_SESSION['payment']);
						unset($_SESSION['payment_method']);
					}
				}
                                
                                if ($this->_request->shipping_address == 'new') {
					if ((strlen(utf8_decode($this->_request->shipping_firstname)) < 1) || (strlen(utf8_decode($this->_request->shipping_firstname)) > 32)) {
						$json['error']['shipping_firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_checkout_checkout');
					}

					if ((strlen(utf8_decode($this->_request->shipping_lastname)) < 1) || (strlen(utf8_decode($this->_request->shipping_lastname)) > 32)) {
						$json['error']['shipping_lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_checkout_checkout');
					}

					if ((strlen(utf8_decode($this->_request->shipping_address_1)) < 3) || (strlen(utf8_decode($this->_request->shipping_address_1)) > 64)) {
						$json['error']['shipping_address_1'] = $_SESSION['OBJ']['tr']->translate('error_address_1_checkout_checkout');
					}

					if ((strlen(utf8_decode($this->_request->shipping_city)) < 2) || (strlen(utf8_decode($this->_request->shipping_city)) > 128)) {
						$json['error']['shipping_city'] = $_SESSION['OBJ']['tr']->translate('error_city_checkout_checkout');
					}

					/*$country_info = $this->locCtry->getCountry($this->_request->country_id);

					if ($country_info && $country_info['postcode_required'] && (strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
						$json['error']['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
					}*/

					if ((strlen(utf8_decode($this->_request->shipping_postcode)) < 2) || (strlen(utf8_decode($this->_request->shipping_postcode)) > 10)) {
						$json['error']['shipping_postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
					}

					if ($this->_request->shipping_country_id == '') {
						$json['error']['shipping_country'] = $_SESSION['OBJ']['tr']->translate('error_country_checkout_checkout');
					}

					if ($this->_request->shipping_zone_id == '') {
						$json['error']['shipping_zone'] = $_SESSION['OBJ']['tr']->translate('error_zone_checkout_checkout');
					}

					if (!$json) {
                                            $shippaddressarray=array("firstname"=>$this->_request->shipping_firstname,"lastname"=>$this->_request->shipping_lastname,"company"=>$this->_request->shipping_company,"address_1"=>$this->_request->shipping_address_1,"address_2"=>$this->_request->shipping_address_2,"city"=>$this->_request->shipping_city,"postcode"=>$this->_request->shipping_postcode,"country_id"=>$this->_request->shipping_country_id,"zone_id"=>$this->_request->shipping_zone_id);
						$_SESSION['shipping_address_id'] = $this->accAddr->addAddress($shippaddressarray);

						if ($this->cart->hasShipping()) {
							$this->tax->setZone($this->_request->shipping_country_id, $this->_request->shipping_zone_id);
						}

						unset($_SESSION['shipping_methods']);
						unset($_SESSION['shipping']);
						unset($_SESSION['shipping_method']);
					}
				}
			}
		} else {

			$this->data['type'] = 'payment';

			if (isset($_SESSION['payment_address_id'])) {
				$this->data['payment_address_id'] = $_SESSION['payment_address_id'];
			} else {
				$this->data['payment_address_id'] = $this->customer->getAddressId();
			}
                        
                        if (isset($_SESSION['shipping_address_id'])) {
				$this->data['shipping_address_id'] = $_SESSION['shipping_address_id'];
			} else {
				$this->data['shipping_address_id'] = $this->customer->getAddressId();
			}

			$this->data['addresses'] = $this->accAddr->getAddresses();

			$this->data['country_id'] = STORE_COUNTRY;

			$this->data['countries'] = $this->locCtry->getCountries();
			//print_r($this->data['countries']);
			$this->view->data_payment=$this->data;
                        /*echo "<pre>";
                        print_r($this->data);
                        echo "</pre>";
                        exit;*/
			ob_start();
			//include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/address.phtml';
                        if (file_exists(PATH_TO_FILES.'checkout/address.phtml'))
                        {
                            include_once PATH_TO_FILES.'checkout/address.phtml';
                        }else
                        if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/address.phtml'))
                        {
                            include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/address.phtml';
                        } else
                        {
                            include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/checkout/address.phtml';
                        }
			$json['output'] = ob_get_contents();
			ob_end_clean();
		}
		echo Model_Json::encode($json);
  	}

	public function addressshippingAction() {

		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$json = array();

		if ($this->customer->isLogged()==0 && !is_array($_SESSION['guest'])) {
		
                    $json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);//HTTP_SERVER.'checkout/checkout';//$this->url->link('checkout/checkout', '', 'SSL');
		}else if(is_array($_SESSION['guest']) && $this->customer->isLogged()==0)
                {
                    $this->guestshippingAction();
                    exit;
                }

		if (!$this->cart->hasShipping()) {
			$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);//HTTP_SERVER.'checkout/checkout';//$this->url->link('checkout/checkout', '', 'SSL');
		}

		$STOCK_ALLOW_CHECKOUT=STOCK_ALLOW_CHECKOUT=='true'?'1':'0';
		if ((!$this->cart->hasProducts() && (!isset($_SESSION['vouchers']) || !$_SESSION['vouchers'])) || (!$this->cart->hasStock() && !$STOCK_ALLOW_CHECKOUT)) {
			$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"cart"),'',SERVER_SSL);//$this->url->link('checkout/cart');
		}

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			if (!$json) {
				if ($this->_request->shipping_address == 'existing') {
					if (!isset($this->_request->address_id)) {
						$json['error']['warning'] = $_SESSION['OBJ']['tr']->translate('error_address_checkout_checkout');
					}

					if (!$json) {
						$_SESSION['shipping_address_id'] = $this->_request->address_id;

						$address_info = $this->accAddr->getAddress($this->_request->address_id);

						if ($address_info) {
							$this->tax->setZone($address_info['country_id'], $address_info['zone_id']);
						}

						unset($_SESSION['shipping_methods']);
						unset($_SESSION['shipping']);
						unset($_SESSION['shipping_method']);
					}
				}

				if ($this->_request->shipping_address == 'new') {
					if ((strlen(utf8_decode($this->_request->firstname)) < 1) || (strlen(utf8_decode($this->_request->firstname)) > 32)) {
						$json['error']['shipping_firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_checkout_checkout');
					}

					if ((strlen(utf8_decode($this->_request->lastname)) < 1) || (strlen(utf8_decode($this->_request->lastname)) > 32)) {
						$json['error']['shipping_lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_checkout_checkout');
					}

					if ((strlen(utf8_decode($this->_request->address_1)) < 3) || (strlen(utf8_decode($this->_request->address_1)) > 64)) {
						$json['error']['shipping_address_1'] = $_SESSION['OBJ']['tr']->translate('error_address_1_checkout_checkout');
					}

					if ((strlen(utf8_decode($this->_request->city)) < 2) || (strlen(utf8_decode($this->_request->city)) > 128)) {
						$json['error']['shipping_city'] = $_SESSION['OBJ']['tr']->translate('error_city_checkout_checkout');
					}

					/*$country_info = $this->locCtry->getCountry($this->_request->country_id);

					if ($country_info && $country_info['postcode_required'] && (strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
						$json['error']['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
					}*/

					if ((strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
						$json['error']['shipping_postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_checkout_checkout');
					}

					if ($this->_request->country_id == '') {
						$json['error']['shipping_country'] = $_SESSION['OBJ']['tr']->translate('error_country_checkout_checkout');
					}

					if ($this->_request->zone_id == '') {
						$json['error']['shipping_zone'] = $_SESSION['OBJ']['tr']->translate('error_zone_checkout_checkout');
					}

					if (!$json) {
						$_SESSION['shipping_address_id'] = $this->accAddr->addAddress($this->_getAllParams());

						if ($this->cart->hasShipping()) {
							$this->tax->setZone($this->_request->country_id, $this->_request->zone_id);
						}

						unset($_SESSION['shipping_methods']);
						unset($_SESSION['shipping']);
						unset($_SESSION['shipping_method']);
					}
				}
			}
		} else {

			$this->data['type'] = 'shipping';

			if (isset($_SESSION['shipping_address_id'])) {
				$this->data['address_id'] = $_SESSION['shipping_address_id'];
			} else {
				$this->data['address_id'] = $this->customer->getAddressId();
			}

			$this->data['addresses'] = $this->accAddr->getAddresses();

			$this->data['country_id'] = STORE_COUNTRY;


			$this->data['countries'] = $this->locCtry->getCountries();


			$this->view->data_shipping=$this->data;
			ob_start();
			//include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/address.phtml';
                        if (file_exists(PATH_TO_FILES.'checkout/address.phtml'))
                        {
                            include_once PATH_TO_FILES.'checkout/address.phtml';
                        }else
                        if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/address.phtml'))
                        {
                            include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/address.phtml';
                        } else
                        {
                            include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/checkout/address.phtml';
                        }
			$json['output'] = ob_get_contents();
			ob_end_clean();
		}
		echo Model_Json::encode($json);
  	}

	public function checkoutProcessAction()
	{

	$ordObj=new Model_CheckoutOrder();
	$order_info = $ordObj->getOrder($_SESSION['order_id']);
	include APPLICATION_PATH.'/models/functions.php';
	/*echo "<pre>";
	print_r($order_info);
	echo "</pre>";*/

	$payment_modules=new Model_Payment($_SESSION['payment']['id']);
	$shipping_modules = new Model_Shipping($_SESSION['shipping']['id']);
		// Stock Check
	  /*
	  $any_out_of_stock = false;
	  if (STOCK_CHECK == 'true') {
		for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
		  if (tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])) {
			$any_out_of_stock = true;
		  }
		}
		// Out of Stock
		if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
		  tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
		}
	  }*/
		$STOCK_ALLOW_CHECKOUT=STOCK_ALLOW_CHECKOUT=='true'?'1':'0';
		if ((!$this->cart->hasProducts() && (!isset($_SESSION['vouchers']) || !$_SESSION['vouchers'])) || (!$this->cart->hasStock() && !$STOCK_ALLOW_CHECKOUT)) {
			$json['redirect'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'msg/stock not available',SERVER_SSL);//HTTP_SERVER.'checkout/checkout/msg/stock not available';//$this->url->link('checkout/cart');
		}

		$payment_modules->update_status();
		  //print_r($$payment);
		//echo $$payment->enabled;
		/*echo $payment_modules->selected_module;
		print_r($payment_modules->modules);
		//echo $_SESSION[$_SESSION['payment']['id']];
		echo "<pre>";
		print_r($_SESSION[$_SESSION['payment']['id']]);
		echo "</pre>";
		exit;*/
  if ( ($payment_modules->selected_module != $_SESSION['payment']['id']) || ( is_array($payment_modules->modules) && (sizeof($payment_modules->modules) > 1) && !is_object($_SESSION[$_SESSION['payment']['id']]) ) || (is_object($$payment) && ($_SESSION[$_SESSION['payment']['id']]->enabled == false)) ) {
    //tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));
  }
  //returns if payment module is disable

// load the before_process function from the payment modules
 $payment_modules->before_process();
//print_r($payment_modules);
/*start datebase insertiona and mail*/

 $ordObj->confirm($_SESSION['order_id'],$_SESSION[$_SESSION['payment']['id']]->order_status);
//exit;
/*end datebase insertion and mail*/
$payment_modules->after_process();
$this->_redirect('checkout/success');
	}

	public function successAction() {

            $this->setLangSession();
            $this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('text_success_checkout_success')));

		$this->isAffiliateTrackingSet();

		$this->globalKeywords();
		$this->getHeader();
		$this->getFlashCart();

		/*start modules*/
		$moduleObj=new Model_Module();
		$this->view->pos=$moduleObj->getModules(array('page'=>'7')); //refers to category page as per r_layout
		/*end modules*/

		if (isset($_SESSION['order_id'])) {
			$this->cart->clear();

			unset($_SESSION['shipping_method']);
			unset($_SESSION['shipping_methods']);
			unset($_SESSION['payment_method']);
			unset($_SESSION['payment_methods']);
			unset($_SESSION['guest']);
			unset($_SESSION['comment']);
			unset($_SESSION['order_id']);
			unset($_SESSION['coupon']);
			unset($_SESSION['voucher']);
			unset($_SESSION['vouchers']);
			/*start session defined by me*/
			unset($_SESSION['payment_address_id']);
			unset($_SESSION['payment_country_id']);
			unset($_SESSION['payment_zone_id']);
			unset($_SESSION['shipping_country_id']);
			unset($_SESSION['shipping_zone_id']);
			unset($_SESSION['shipping_address_id']);
			unset($_SESSION['shipping']);
			unset($_SESSION['payment']);
			/*end session defined by me*/
		}

		//$this->document->setTitle($this->language->get('heading_title'));


				$this->data[breadcrumbs] = array();

   		$this->data[breadcrumbs][] = array(
       		'text'      => $_SESSION['OBJ']['tr']->translate('text_home'),
			'href'      => HTTP_SERVER."index/index",
       		'separator' => false
   		);

		$this->data['breadcrumbs'][] = array(
			'href'      => Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL),//$this->url->link('checkout/checkout', '', 'SSL'),
			'text'      => $_SESSION['OBJ']['tr']->translate('text_checkout_checkout_success'),
			'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
		);

      	$this->data['breadcrumbs'][] = array(
        	'href'      =>  HTTP_SERVER.'checkout/checkout',//$this->url->link('checkout/success'),
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_success_checkout_success'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

    	$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_checkout_success');

		if ($this->customer->isLogged()) {
    		/*$this->data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', 'SSL'), $this->url->link('account/order', '', 'SSL'), $this->url->link('account/download', '', 'SSL'), $this->url->link('information/contact'));*/
			$this->data['text_message'] = sprintf($_SESSION['OBJ']['tr']->translate('text_customer_checkout_success'), $this->view->link_account_account,Model_Url::getLink(array("controller"=>"account","action"=>"order"),'',SERVER_SSL) ,Model_Url::getLink(array("controller"=>"account","action"=>"download"),'',SERVER_SSL), HTTP_SERVER.'information/contact');

		} else {
    		//$this->data['text_message'] = sprintf($_SESSION['OBJ']['tr']->translate('text_guest'), $this->url->link('information/contact'));
			$this->data['text_message'] = sprintf($_SESSION['OBJ']['tr']->translate('text_guest_checkout_success'), HTTP_SERVER.'information/contact');
		}

    	$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');

    	$this->data['continue'] = HTTP_SERVER.'index/index';

	
		$this->view->data=$this->data;
		//$this->renderScript('account/success.phtml');
		//$this->render('success');
                if (file_exists(PATH_TO_FILES.'checkout/success.phtml'))
                {
                        $this->view->addScriptPath(PATH_TO_FILES.'checkout/');
                        $this->renderScript('success.phtml');
                }else
                if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/checkout/success.phtml'))
		{
			$this->render('success');
		} else
		{
			$this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/checkout/');
			$this->renderScript('success.phtml');
		}
  	}
}