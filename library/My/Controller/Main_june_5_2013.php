<?php
ob_start();
/**
 * Handling Errors in the application
 *
 * @category   Zend
 * @package    MainController
 * @author     suresh babu k
 */
class My_Controller_Main extends Zend_Controller_Action {
	//public static $db=null;

	public function init()
	{
		//$this->db=Zend_Db_Table::getDefaultAdapter();

	}

	public function preDispatch()
	{
		//echo "value of ".$this->getRequest()->getControllerName();
		//exit;
		//exit("in main predispatch");
		if(@constant('STORE_INTRODUCTION_STATUS')=='1' && $_SESSION['STORE_INTRODUCTION_STATUS']=="" && $this->getRequest()->getControllerName()!='ajax') //introduction page
            {
                $this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
                $_SESSION['STORE_INTRODUCTION_STATUS']="1";
                echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><title>'.@constant('STORE_META_TITLE').'</title><meta name="Keywords" content="'.@constant('STORE_META_KEYWORDS').'"><meta name="Description" content="'.@constant('STORE_META_DESCRIPTION').'"></head><body>';
                ECHO stripslashes(@constant('STORE_INTRODUCTION_CONTENT'));
                echo '</body></html>';
                EXIT;
            }
		
	}
 

	public function isAffiliateTrackingSet()
	{
		$tracking=$this->_request->tracking;
		if (isset($tracking) && !isset($_COOKIE['tracking']))
		{
			//setcookie('tracking', $this->_request->tracking, time() + 3600 * 24 * 1000, '/');
			setcookie('tracking', $this->_request->tracking, time() + 3600 * 24 * 1, '/');
		}
	}

	public function setLangSession()
	{
		$l=$this->_getParam('lang');
		if(($_SESSION['Lang']['language_code']=="") || ($_SESSION['OBJ']['tr']=="") || (isset($l) &&  $_SESSION['Lang']['language_code']!=$this->_getParam('lang')))
		{
			$lang=new Model_Languages($this->_getParam('lang'));
			$tr=Model_Cache::getLangCache($lang);
		}
		$this->view->trans=$_SESSION['OBJ']['tr'];
		$this->tr=$_SESSION['OBJ']['tr'];
	}

	public function globalKeywords()
	{
			if($_SESSION['admin_id']!='1' && @constant('SERVER_MAINTENANCE_MODE')=='true')
			{
				header("location:".HTTP_SERVER.'maintenance');
				exit;
			}
            $urlObj=new Model_Url('','');
            $this->view->link_account_login=$urlObj->getLink(array("controller"=>"account","action"=>"login"),'',SERVER_SSL);
            $this->view->link_account_register=$urlObj->getLink(array("controller"=>"account","action"=>"register"),'',SERVER_SSL);
            $this->view->link_account=$urlObj->getLink(array("controller"=>"account","action"=>"index"),'',SERVER_SSL);
            $this->view->link_account_account=$urlObj->getLink(array("controller"=>"account","action"=>"account"),'',SERVER_SSL);
            $this->view->link_checkout=$urlObj->getLink(array("controller"=>"checkout","action"=>"checkout"),'',SERVER_SSL);
            $this->view->link_account_order_history=$urlObj->getLink(array("controller"=>"account","action"=>"order"),'',SERVER_SSL);

            $this->view->wishlistTotal=sprintf($_SESSION['OBJ']['tr']->translate('text_wishlist_common_header'), (isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0));
			//print_r($_SESSION['OBJ']['tr']->translate('text_welcome_common_header'));
            $this->view->text_welcome_header= sprintf($_SESSION['OBJ']['tr']->translate('text_welcome_common_header'), $this->view->link_account_login, $this->view->link_account_register);

            $custObj=new Model_Customer();
            $this->view->text_logged_header = sprintf($_SESSION['OBJ']['tr']->translate('text_logged_common_header'), HTTP_SERVER.'account/account', $custObj->getFirstName(),HTTP_SERVER.'account/logout');
	    $this->view->logged = $custObj->isLogged();
		Model_Template::getTemplateText();
		//echo "here";
    }

	public function getCache($arr)
	{
		$frontendOptions = array('lifeTime' => 6000,"automatic_serialization" => true );
		$backendOptions = array('cache_dir' => @constant('PATH_TO_SITECACHE'));

		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);

		if (!$result = $cache->load($arr['id']) ){
			//print_r($arr['input']);
			//$cache->save($arr['input'], @constant('GLOBAL_DOMAIN_KEY').$arr['id']);
			if($arr['input']!="")
			{
			$cache->save($arr['input'],$arr['id']);
			}
			//return $cache->load(@constant('GLOBAL_DOMAIN_KEY').$arr['id']);
			return $cache->load($arr['id']);
		} else
		{
			//return $cache->load(@constant('GLOBAL_DOMAIN_KEY').$arr['id']);
			return $cache->load($arr['id']);
		}
	}

	public function getHeader()
	{

		/*start categories*/
		$catObj=new Model_Categories();
		//$this->view->categories=$catObj->getCatArray();
		//$this->view->menucategories=$catObj->getCatArray();
		$this->view->menucategories=$this->getCache(array('id'=>'menuCat'));
		if($this->view->menucategories=="")
		{
			$input=$catObj->getCatArray();
			$this->view->menucategories=$this->getCache(array('id'=>'menuCat','input'=>$input));
		}

		$this->view->allCategories=$this->getCache(array('id'=>'allCat'));
		if($this->view->allCategories=="")
		{
			$input=$catObj->getAllCatArray();
			$this->view->allCategories=$this->getCache(array('id'=>'allCat','input'=>$input));
		}
		$this->view->more=$this->view->allCategories['more'];
		//$this->view->catDropDown=$catObj->getParentCategoriesDropDown(0);
		$this->view->catDropDown=$this->getCache(array('id'=>'dropCat'));
		if($this->view->catDropDown=="")
		{
			$input=$catObj->getParentCategoriesDropDown(0);
			$this->view->catDropDown=$this->getCache(array('id'=>'dropCat','input'=>$input));
		}
		/*end categories*/
			$script="<script type='text/javascript'> 
var HTTP_SERVER= '".$this->view->url_to_site."'; var JAVASCRIPT_ADD_TO_CART_REDIRECTION='".@constant('ADD_TO_CART_REDIRECTION')."'; var JAVASCRIPT_TEXT_ADDTOCART_POPUP='".$this->tr->_('text_addtocart_popup')."';</script>";
		define('SITE_JAVASCRIPT_CONSTANTS',$script);		
		}

	public function getFlashCart()
	{

		$currObj=new Model_currencies();
 		$currObj->setCurrency($this->_getParam('curr'));
 		$this->view->curr=$currObj;

		/*start calculate total*/
		$cartObj=new Model_Cart();
		$otmodules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
		foreach($otmodules as $k=>$v)
		{
			$sort_order[]=constant('MODULE_ORDER_TOTAL_'.strtoupper(substr(substr($v,0,-4),2)).'_SORT_ORDER')."-".substr(substr($v,0,-4),2);
		}
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

		$this->view->text_items = sprintf($_SESSION['OBJ']['tr']->translate('text_items_common_header'), $cartObj->countProducts() + (isset($_SESSION['vouchers']) ? count($_SESSION['vouchers']) : 0), $currObj->format($total));

		/*end calculate total*/
	}

	public function getConstants()
	{
		$define=$this->getCache(array('id'=>'define'));
		if($define=="")
		{
			$confObj=new Model_DbTable_rconfiguration();
			$input=$confObj->returnConfiguration();
			$define=$this->getCache(array('id'=>'define','input'=>$input));
		}
		foreach ($define as $results)
		{
			define($results['configuration_key'],$results['configuration_value']);
		}

		$modules=$this->getCache(array('id'=>'modules'));
		if($modules=="")
		{
			$rSetting=new Model_DbTable_rsetting();
			$input=$rSetting->returnSetting();
			$modules=$this->getCache(array('id'=>'modules','input'=>$input));
		}

		foreach ($modules as $results1)
		{
			define($results1['key'],$results1['value']);
		}

                $this->setHttps();
	}

         public function setHttps()
        {
                /*start http/https template urls*/
                if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
					$this->view->url_to_templates = URL_TO_TEMPLATES_HTTPS;
					$this->view->url_to_site = HTTPS_SERVER;
					$this->view->url_to_image = HTTPS_IMAGE;
					$this->view->url_to_commonfiles=$this->view->url_to_site."library/CommonFiles/";
					define('PATH_TO_UPLOADS',HTTPS_SERVER.'public/uploads/'.GLOBAL_DOMAIN_KEY.'/');
                } else
                {
					$this->view->url_to_templates = URL_TO_TEMPLATES;
					$this->view->url_to_site = HTTP_SERVER;
					$this->view->url_to_image = HTTP_IMAGE;
					$this->view->url_to_commonfiles=$this->view->url_to_site."library/CommonFiles/";
					define('PATH_TO_UPLOADS',HTTP_SERVER.'public/uploads/'.GLOBAL_DOMAIN_KEY.'/');
                }

                /*end http/https template urls*/
        }

	public function getMetaTags($arr)
	{
		$this->view->meta_title=$arr['meta_title']==""?STORE_META_TITLE:$arr['meta_title'];
		$this->view->meta_keywords=$arr['meta_keywords']==""?STORE_META_KEYWORDS:$arr['meta_keywords'];
		$this->view->meta_description=$arr['meta_description']==""?STORE_META_DESCRIPTION:$arr['meta_description'];
	}
        
        public function productNameShort($name)
        {
                $return=strlen($name)>@constant('LIMIT_PRODUCT_NAME')?substr($name,@constant('LIMIT_PRODUCT_NAME'))."..":stripslashes($name);
                return $return;
        }
    }

