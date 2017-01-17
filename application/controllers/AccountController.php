<?php
ob_start();
/**
 * Handling Errors in the application
 *
 * @category   Zend
 * @package    AccountController
 * @author     suresh babu k
 */
class AccountController extends My_Controller_Main {
		private $error = array();
		public $tr=null;
		public $currObj=null;
	public function init()
	{
		Zend_Session::start();
		$this->getConstants();
		//$this->view->vaction=$this->getRequest()->getActionName();
		$this->setLangSession();
		$this->isAffiliateTrackingSet();
		$this->globalKeywords();
		$this->getHeader();
		$this->getFlashCart();


		$this->currObj=new Model_currencies();
 		$this->currObj->setCurrency($this->_getParam('curr'));
		$this->view->curr=$this->currObj;

		$this->data[breadcrumbs] = array();

   		$this->data[breadcrumbs][] = array(
       		'text'      => $_SESSION['OBJ']['tr']->translate('text_home_common_header'),
			'href'      => HTTP_SERVER."index/index",
       		'separator' => false
   		);

		$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_account_common_header'),
		'href'      => $this->view->link_account,       	//$this->url->link('account/account', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

		/*start modules*/
		$moduleObj=new Model_Module();
		$this->view->pos=$moduleObj->getModules(array('page'=>'6')); //refers to category page as per r_layout
		/*end modules*/
	}


	public function indexAction()
	{
        	$this->_redirect($this->view->link_account_account);
	}

	public function forgottenAction()
	{
            
                $this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_forgotten')));
		$custObj=new Model_Customer();
		$actCustObj=new Model_AccountCustomer();
		if ($custObj->isLogged()) {
		                   
		$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"account"),'',SERVER_SSL));
		}

		if (($this->getRequest()->isPost()) && $this->validateForgotten()) {

			$password = substr(md5(rand()), 0, 7);
			$actCustObj->editPassword($this->_request->email, $password);

			/*start mail*/
			$mailObj=new Model_Mail();
                        $email=$mailObj->getEmailContent(array('lang'=>$_SESSION['Lang']['language_id'],'id'=>'5','replace'=>array("%password%"=>$password)));
                      	$array_mail=array('to'=>array('name'=>trim($this->_request->email),'email'=>trim($this->_request->email)),'html'=>array('content'=>$email['content']),'subject'=>$email['subject']);
			$mailObj->sendMail($array_mail);
                       	/*end mail*/

			$_SESSION['success'] = $this->tr->translate('text_success_account_forgotten');

			$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"login"),'',SERVER_SSL));
		}


      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->tr->translate('text_forgotten_account_forgotten'),
			//'href'      => $this->url->link('account/forgotten', '', 'SSL'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"forgotten"),'',SERVER_SSL),
        	'separator' => $this->tr->translate('text_separator')
      	);

	
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['action'] = Model_Url::getLink(array("controller"=>"account","action"=>"forgotten"),'',SERVER_SSL);

		$this->data['back'] = Model_Url::getLink(array("controller"=>"account","action"=>"login"),'',SERVER_SSL);
		$this->view->data=$this->data;
            
            if (file_exists(PATH_TO_FILES.'account/forgotten.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('forgotten.phtml');
            }else    
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/forgotten.phtml'))
            {
                $this->render('forgotten');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('forgotten.phtml');
            }
	}

	private function validateForgotten() {
	$acctCustObj=new Model_AccountCustomer();
		if (!isset($this->_request->email)) {
			$this->error['warning'] = $this->tr->translate('error_email_account_forgotten');
		 } elseif (!$acctCustObj->getTotalCustomersByEmail($this->_request->email)) {
			$this->error['warning'] = $this->tr->translate('error_email_account_forgotten');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

    public function registerAction()
	{
                $this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_register')));
		$custObj=new Model_Customer();
		$actCustObj=new Model_AccountCustomer();
		if ($custObj->isLogged()) {
	  	$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"account"),'',SERVER_SSL));
    	}

 
			if (($this->getRequest()->isPost()) && $this->validate()) {
			unset($_SESSION['guest']);

			$actCustObj->addCustomer($this->_getAllParams(),$tr);

			$custObj->login($this->_request->email, $this->_request->password);

			$this->_redirect('account/success');
    	}

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_register_account_register'),
			'href'      =>Model_Url::getLink(array("controller"=>"account","action"=>"register"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

    	/*$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_account_register');

		$this->data['text_yes'] = $_SESSION['OBJ']['tr']->translate('text_yes');
		$this->data['text_no'] = $_SESSION['OBJ']['tr']->translate('text_no');
		$this->data['text_select'] = $_SESSION['OBJ']['tr']->translate('text_select');
    	$this->data['text_account_already'] = sprintf($_SESSION['OBJ']['tr']->translate('text_account_already'), HTTP_SERVER."account/login");//$this->url->link('account/login', '', 'SSL'));
    	$this->data['text_your_details'] = $_SESSION['OBJ']['tr']->translate('text_your_details');
    	$this->data['text_your_address'] = $_SESSION['OBJ']['tr']->translate('text_your_address');
    	$this->data['text_your_password'] = $_SESSION['OBJ']['tr']->translate('text_your_password');
		$this->data['text_newsletter'] = $_SESSION['OBJ']['tr']->translate('heading_title_account_newsletter');

    	$this->data['entry_firstname'] = $_SESSION['OBJ']['tr']->translate('entry_firstname');
    	$this->data['entry_lastname'] = $_SESSION['OBJ']['tr']->translate('entry_lastname');
    	$this->data['entry_email'] = $_SESSION['OBJ']['tr']->translate('entry_email');
    	$this->data['entry_telephone'] = $_SESSION['OBJ']['tr']->translate('entry_telephone');
    	$this->data['entry_fax'] = $_SESSION['OBJ']['tr']->translate('entry_fax');
    	$this->data['entry_company'] = $_SESSION['OBJ']['tr']->translate('entry_company');
    	$this->data['entry_address_1'] = $_SESSION['OBJ']['tr']->translate('entry_address_1');
    	$this->data['entry_address_2'] = $_SESSION['OBJ']['tr']->translate('entry_address_2');
    	$this->data['entry_postcode'] = $_SESSION['OBJ']['tr']->translate('entry_postcode');
    	$this->data['entry_city'] = $_SESSION['OBJ']['tr']->translate('entry_city');
    	$this->data['entry_country'] = $_SESSION['OBJ']['tr']->translate('entry_country');
    	$this->data['entry_zone'] = $_SESSION['OBJ']['tr']->translate('entry_zone');
		$this->data['entry_newsletter'] = $_SESSION['OBJ']['tr']->translate('entry_newsletter');
    	$this->data['entry_password'] = $_SESSION['OBJ']['tr']->translate('entry_password');
    	$this->data['entry_confirm'] = $_SESSION['OBJ']['tr']->translate('entry_confirm');

		$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');*/

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			//$this->data['error_warning'] = '';
			$this->data['error_warning']=$this->data['error_warning'];
		}

		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}

		if (isset($this->error['password'])) {
			$this->data['error_password'] = $this->error['password'];
		} else {
			$this->data['error_password'] = '';
		}

 		if (isset($this->error['confirm'])) {
			$this->data['error_confirm'] = $this->error['confirm'];
		} else {
			$this->data['error_confirm'] = '';
		}

  		if (isset($this->error['address_1'])) {
			$this->data['error_address_1'] = $this->error['address_1'];
		} else {
			$this->data['error_address_1'] = '';
		}

		if (isset($this->error['city'])) {
			$this->data['error_city'] = $this->error['city'];
		} else {
			$this->data['error_city'] = '';
		}

		if (isset($this->error['postcode'])) {
			$this->data['error_postcode'] = $this->error['postcode'];
		} else {
			$this->data['error_postcode'] = '';
		}

		if (isset($this->error['country'])) {
			$this->data['error_country'] = $this->error['country'];
		} else {
			$this->data['error_country'] = '';
		}

		if (isset($this->error['zone'])) {
			$this->data['error_zone'] = $this->error['zone'];
		} else {
			$this->data['error_zone'] = '';
		}

    	$this->data['action'] =Model_Url::getLink(array("controller"=>"account","action"=>"register"),'',SERVER_SSL);//$this->url->link('account/register', '', 'SSL');

		if (isset($this->_request->firstname)) {
    		$this->data['firstname'] = $this->_request->firstname;
		} else {
			$this->data['firstname'] = '';
		}

		if (isset($this->_request->lastname)) {
    		$this->data['lastname'] = $this->_request->lastname;
		} else {
			$this->data['lastname'] = '';
		}

		if (isset($this->_request->email)) {
    		$this->data['email'] = htmlspecialchars($this->_request->email, ENT_COMPAT);
		} else {
			$this->data['email'] = '';
		}

		if (isset($this->_request->telephone)) {
    		$this->data['telephone'] = $this->_request->telephone;
		} else {
			$this->data['telephone'] = '';
		}

		if (isset($this->_request->fax)) {
    		$this->data['fax'] = $this->_request->fax;
		} else {
			$this->data['fax'] = '';
		}

		if (isset($this->_request->company)) {
    		$this->data['company'] = $this->_request->company;
		} else {
			$this->data['company'] = '';
		}

		if (isset($this->_request->address_1)) {
    		$this->data['address_1'] = $this->_request->address_1;
		} else {
			$this->data['address_1'] = '';
		}

		if (isset($this->_request->address_2)) {
    		$this->data['address_2'] = $this->_request->address_2;
		} else {
			$this->data['address_2'] = '';
		}

		if (isset($this->_request->postcode)) {
    		$this->data['postcode'] = $this->_request->postcode;
		} else {
			$this->data['postcode'] = '';
		}

		if (isset($this->_request->city)) {
    		$this->data['city'] = $this->_request->city;
		} else {
			$this->data['city'] = '';
		}

                if (isset($this->_request->country_id)) {
                    $this->data['country_id'] = $this->_request->country_id;
                    } else {
                    $this->data['country_id'] =STORE_COUNTRY;
                }

                if (isset($this->_request->zone_id)) {
                    $this->data['zone_id'] = $this->_request->zone_id;
                    } else {
                    $this->data['zone_id'] = '';
                }

		//$this->load->model('localisation/country');
		$locCtryObj=new Model_LocalisationCountry();
    	$this->data['countries'] = $locCtryObj->getCountries();
		/*echo "<pre>";
		print_r($this->data['countries']);
		echo "</pre>";*/
		if (isset($this->_request->password)) {
    		$this->data['password'] = $this->_request->password;
		} else {
			$this->data['password'] = '';
		}

		if (isset($this->_request->confirm)) {
    		$this->data['confirm'] = $this->_request->confirm;
		} else {
			$this->data['confirm'] = '';
		}

		if (isset($this->_request->newsletter)) {
    		$this->data['newsletter'] = $this->_request->newsletter;
		} else {
			$this->data['newsletter'] = '';
		}
//echo REGISTRATION_TERMS;

		if (REGISTRATION_TERMS) {
			$infoObj=new Model_Information();
			$information_info = $infoObj->getInformation(REGISTRATION_TERMS);

			if ($information_info) {
				$this->data['text_agree'] = sprintf($_SESSION['OBJ']['tr']->translate('text_agree_account_register'), Model_Url::getLink(array("controller"=>"ajax","action"=>"info"),'information_id/'.REGISTRATION_TERMS,SERVER_SSL)
				//$this->url->link('information/information/info', 'information_id=' . $this->config->get('config_account_id'), 'SSL')
					, $information_info['title'], $information_info['title']);
			} else {
				$this->data['text_agree'] = '';
			}
		} else {
			$this->data['text_agree'] = '';
		}
	//	echo "value of ".$this->data['text_agree'];
///exit;
		if (isset($this->_request->agree)) {
      		$this->data['agree'] = $this->_request->agree;
		} else {
			$this->data['agree'] = false;
		}

		$this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'account/register.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('register.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/register.phtml'))
            {
                $this->render('register');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('register.phtml');
            }

	}

	public function accountAction()
	{
                $this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_account')));
		$custObj=new Model_Customer();
		if (!$custObj->isLogged())
		{
			$_SESSION['redirect'] = $this->view->link_account_account;
                        $this->_redirect($this->view->link_account_login);
		}
		
		$acctCustObj=new Model_AccountCustomer();
                $this->data[info] = $acctCustObj->getCustomer($custObj->getId());
		$this->data['newsletter_statement']=$this->data[info]['customers_newsletter']=='1'?$_SESSION['OBJ']['tr']->translate('text_account_newsletter_subscribed'):$_SESSION['OBJ']['tr']->translate('text_account_newsletter_notsubscribed');
		$acctAddrObj=new Model_AccountAddress();
		$this->data['default_address'] = $acctAddrObj->getAddress($this->data[info][customers_default_address_id]);
		$this->data['default_address']['customers_default_address_id']=$this->data[info][customers_default_address_id];
		$zoneObj=new Model_LocalisationZone();
		$countryObj=new Model_LocalisationCountry();
		$this->data['default_address']['zone']=$zoneObj->getZone($this->data['default_address']['zone_id']);
		$this->data['default_address']['country']=$countryObj->getCountry($this->data['default_address']['country_id']);

		$acctOrdObj=new Model_AccountOrder();
		$this->data['currObj']=$this->currObj;
		$this->data['orders']=$acctOrdObj->getOrders(0,5);

		if (isset($_SESSION['success'])) {
    		$this->data['success'] = $_SESSION['success'];

			unset($_SESSION['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'account/account.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('account.phtml');
            }else        
             if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/account.phtml'))
            {
                 $this->render('account');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('account.phtml');
            }
        }

	public function editAction()
	{
            $this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_edit')));
            $custObj=new Model_Customer();
            if (!$custObj->isLogged()) {
               $_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"edit"),'',SERVER_SSL);//$this->url->link('account/edit', '', 'SSL');

                    //$this->redirect($this->url->link('account/login', '', 'SSL'));
                    $this->_redirect($this->view->link_account_login);
            }

		$acctCustObj=new Model_AccountCustomer();
		if (($_SERVER['REQUEST_METHOD'] == 'POST') && $this->validateEdit()) {
			$acctCustObj->editCustomer($this->_getAllParams());

			$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_success_account_edit');
                        //echo "value of ".$_SESSION['success'];
                        //exit;

			$this->_redirect($this->view->link_account_account);
		}

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_edit_account_edit'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"edit"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}

		$this->data['action'] = Model_Url::getLink(array("controller"=>"account","action"=>"edit"),'',SERVER_SSL);//HTTP_SERVER.'account/edit';//$this->url->link('account/edit', '', 'SSL');

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
 			$customer_info = $acctCustObj->getCustomer($custObj->getId());
		}

		if (isset($this->_request->firstname)) {
			$this->data['firstname'] = $this->_request->firstname;
		} elseif (isset($customer_info)) {
			$this->data['firstname'] = $customer_info['customers_firstname'];
		} else {
			$this->data['firstname'] = '';
		}

		if (isset($this->_request->lastname)) {
			$this->data['lastname'] = $this->_request->lastname;
		} elseif (isset($customer_info)) {
			$this->data['lastname'] = $customer_info['customers_lastname'];
		} else {
			$this->data['lastname'] = '';
		}

		if (isset($this->_request->email)) {
			$this->data['email'] = $this->_request->email;
		} elseif (isset($customer_info)) {
			$this->data['email'] = $customer_info['customers_email_address'];
		} else {
			$this->data['email'] = '';
		}

		if (isset($this->_request->telephone)) {
			$this->data['telephone'] = $this->_request->telephone;
		} elseif (isset($customer_info)) {
			$this->data['telephone'] = $customer_info['customers_telephone'];
		} else {
			$this->data['telephone'] = '';
		}

		if (isset($this->_request->fax)) {
			$this->data['fax'] = $this->_request->fax;
		} elseif (isset($customer_info)) {
			$this->data['fax'] = $customer_info['customers_fax'];
		} else {
			$this->data['fax'] = '';
		}

		$this->data['back'] = Model_Url::getLink(array("controller"=>"account","action"=>"account"),'',SERVER_SSL);//HTTP_SERVER.'account/account';//$this->url->link('account/account', '', 'SSL');
		$this->view->data=$this->data;
        
            if (file_exists(PATH_TO_FILES.'account/edit.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('edit.phtml');
            }else if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/edit.phtml'))
            {
                 $this->render('edit');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('edit.phtml');
            }
	}
        

	private function validateEdit() {
		if ((strlen(utf8_decode($this->_request->firstname)) < 1) || (strlen(utf8_decode($this->_request->firstname)) > 32)) {
			$this->error['firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_account_edit');
		}

		if ((strlen(utf8_decode($this->_request->lastname)) < 1) || (strlen(utf8_decode($this->_request->lastname)) > 32)) {
			$this->error['lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_account_edit');
		}

		if ((strlen(utf8_decode($this->_request->email)) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->_request->email)) {
			$this->error['email'] = $_SESSION['OBJ']['tr']->translate('error_email_account_edit');
		}
		$custObj=new Model_customer();
		$acctCustObj=new Model_AccountCustomer();
		if (($custObj->getEmail() != $this->_request->email) && $acctCustObj->getTotalCustomersByEmail($this->_request->email)) {
			$this->error['warning'] = $_SESSION['OBJ']['tr']->translate('error_exists_account_edit');
		}

		if ((strlen(utf8_decode($this->_request->telephone)) < 3) || (strlen(utf8_decode($this->_request->telephone)) > 32)) {
			$this->error['telephone'] = $_SESSION['OBJ']['tr']->translate('error_telephone_account_edit');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	private function validatePassword() {
    	if ((strlen(utf8_decode($this->_request->password)) < 4) || (strlen(utf8_decode($this->_request->password)) > 20)) {
      		$this->error['password'] = $_SESSION['OBJ']['tr']->translate('error_password_account_password');
    	}

    	if ($this->_request->confirm != $this->_request->password) {
      		$this->error['confirm'] = $_SESSION['OBJ']['tr']->translate('error_confirm_account_password');
    	}

		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}

	public function passwordAction()
	{
                $this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_password')));
		$custObj=new Model_Customer();
		if (!$custObj->isLogged()) {
      		$_SESSION['redirect'] =Model_Url::getLink(array("controller"=>"account","action"=>"password"),'',SERVER_SSL);// HTTP_SERVER.'account/password';//$this->url->link('account/password', '', 'SSL');

      		//$this->redirect($this->url->link('account/login', '', 'SSL'));
			$this->_redirect($this->view->link_account_login);
    	}

	  	//$this->document->setTitle($_SESSION['OBJ']['tr']->translate('heading_title'));

    	if (($_SERVER['REQUEST_METHOD'] == 'POST') && $this->validatePassword()) {
			$acctCustObj=new Model_AccountCustomer();

			$acctCustObj->editPassword($custObj->getEmail(), $this->_request->password);

      		$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_success_account_password');

	  		//$this->redirect($this->url->link('account/account', '', 'SSL'));
			$this->_redirect($this->view->link_account_account);
    	}

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_password'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"password"),'',SERVER_SSL),//$this->url->link('account/password', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

    	/*$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_account_password');

    	$this->data['text_password'] = $_SESSION['OBJ']['tr']->translate('text_password');

    	$this->data['entry_password'] = $_SESSION['OBJ']['tr']->translate('entry_password_account_password');
    	$this->data['entry_confirm'] = $_SESSION['OBJ']['tr']->translate('entry_confirm_account_password');

    	$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');
    	$this->data['button_back'] = $_SESSION['OBJ']['tr']->translate('button_back');*/

		if (isset($this->error['password'])) {
			$this->data['error_password'] = $this->error['password'];
		} else {
			$this->data['error_password'] = '';
		}

		if (isset($this->error['confirm'])) {
			$this->data['error_confirm'] = $this->error['confirm'];
		} else {
			$this->data['error_confirm'] = '';
		}

    	$this->data['action'] = Model_Url::getLink(array("controller"=>"account","action"=>"password"),'',SERVER_SSL);//HTTP_SERVER.'account/password';//$this->url->link('account/password', '', 'SSL');

		if (isset($this->_request->password)) {
    		$this->data['password'] = $this->_request->password;
		} else {
			$this->data['password'] = '';
		}

		if (isset($this->_request->confirm)) {
    		$this->data['confirm'] = $this->_request->confirm;
		} else {
			$this->data['confirm'] = '';
		}

    	$this->data['back'] = Model_Url::getLink(array("controller"=>"account","action"=>"account"),'',SERVER_SSL);//HTTP_SERVER.'account/account';//$this->url->link('account/account', '', 'SSL');
		$this->view->data=$this->data;
             if (file_exists(PATH_TO_FILES.'account/password.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('password.phtml');
            }else
             if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/password.phtml'))
            {
                 $this->render('password');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('password.phtml');
            }
	}

	public function addressAction()
	{
                $this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_address')));
		$custObj=new Model_Customer();
		if (!$custObj->isLogged()) {
		$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"address"),'',SERVER_SSL);//HTTP_SERVER.'account/address';//$this->url->link('account/address', '', 'SSL');

		//$this->redirect($this->url->link('account/login', '', 'SSL'));
		$this->_redirect($this->view->link_account_login);
		}

		//$this->document->setTitle($this->language->get('heading_title'));

		$this->addressgetList();
	}

	private function addressgetList() {

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_address'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"address"),'',SERVER_SSL),//HTTP_SERVER.'account/address',//$this->url->link('account/address', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

    	/*$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_account_address');

    	$this->data['text_address_book'] = $_SESSION['OBJ']['tr']->translate('text_address_book');

    	$this->data['button_new_address'] = $_SESSION['OBJ']['tr']->translate('button_new_address');
    	$this->data['button_edit'] = $_SESSION['OBJ']['tr']->translate('button_edit');
    	$this->data['button_delete'] = $_SESSION['OBJ']['tr']->translate('button_delete');
	$this->data['button_back'] = $_SESSION['OBJ']['tr']->translate('button_back');*/

		if (isset($this->error['warning'])) {
    		$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($_SESSION['success'])) {
			$this->data['success'] = $_SESSION['success'];

    		unset($_SESSION['success']);
		} else {
			$this->data['success'] = '';
		}

    	$this->data['addresses'] = array();
		$acctAddressObj=new Model_AccountAddress();
		$results = $acctAddressObj->getAddresses();
    	foreach ($results as $result) {
			if ($result['address_format']) {
      			$format = $result['address_format'];
    		} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

    		$find = array(
	  			'{firstname}',
	  			'{lastname}',
	  			'{company}',
      			'{address_1}',
      			'{address_2}',
     			'{city}',
      			'{postcode}',
      			'{zone}',
				'{zone_code}',
      			'{country}'
			);

			$replace = array(
	  			'firstname' => $result['firstname'],
	  			'lastname'  => $result['lastname'],
	  			'company'   => $result['company'],
      			'address_1' => $result['address_1'],
      			'address_2' => $result['address_2'],
      			'city'      => $result['city'],
      			'postcode'  => $result['postcode'],
      			'zone'      => $result['zone'],
				'zone_code' => $result['zone_code'],
      			'country'   => $result['country']
			);
      		$this->data['addresses'][] = array(
        		'address_id' => $result['address_id'],
        		'address'    => str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format)))),
        		'update'     => Model_Url::getLink(array("controller"=>"account","action"=>"addressupdate"),'address_id/'.$result['address_id'],SERVER_SSL),//HTTP_SERVER.'account/addressupdate/address_id/'.$result['address_id'],//$this->url->link('account/address/update', 'address_id=' . $result['address_id'], 'SSL'),
    'delete'     => Model_Url::getLink(array("controller"=>"account","action"=>"addressdelete"),'address_id/'.$result['address_id'],SERVER_SSL),//HTTP_SERVER.'account/addressdelete/address_id/'.$result['address_id'],//$this->url->link('account/address/delete', 'address_id=' . $result['address_id'], 'SSL')
      		);
    	}
    	$this->data['insert'] = Model_Url::getLink(array("controller"=>"account","action"=>"addressinsert"),'',SERVER_SSL);//HTTP_SERVER.'account/addressinsert';//$this->url->link('account/address/insert', '', 'SSL');
		$this->data['back'] = $this->view->link_account_account;//HTTP_SERVER.'account/account';//$this->url->link('account/account', '', 'SSL');
		$this->view->data=$this->data;
		//$this->render('address-list');
            if (file_exists(PATH_TO_FILES.'account/address-list.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('address-list.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/address-list.phtml'))
            {
                $this->render('address-list');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('address-list.phtml');
            }
	 }

	private function getForm() {
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_address'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"address"),'',SERVER_SSL),//HTTP_SERVER.'account/address',//$this->url->link('account/address', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

		if (!isset($this->_request->address_id)) {
      		$this->data['breadcrumbs'][] = array(
        		'text'      => $_SESSION['OBJ']['tr']->translate('text_edit_address_account_address'),
				'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"addressinsert"),'',SERVER_SSL),//HTTP_SERVER.'account/addressinsert',//$this->url->link('account/address/insert', '', 'SSL'),
        		'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      		);
		} else {
      		$this->data['breadcrumbs'][] = array(
        		'text'      => $_SESSION['OBJ']['tr']->translate('text_edit_address_account_address'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"addressupdate"),'address_id/'.$this->_request->address_id,SERVER_SSL),//HTTP_SERVER.'account/addressupdate/address_id/'.$this->_request->address_id,//$this->url->link('account/address/update', 'address_id=' . $this->_request->address_id'], 'SSL'),
        		'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      		);
		}

    	$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_address_account_address');

		$this->data['text_edit_address'] = $_SESSION['OBJ']['tr']->translate('text_edit_address_account_address');
    	$this->data['text_yes'] = $_SESSION['OBJ']['tr']->translate('text_yes');
    	$this->data['text_no'] = $_SESSION['OBJ']['tr']->translate('text_no');
		$this->data['text_select'] = $_SESSION['OBJ']['tr']->translate('text_select');

    	/*$this->data['entry_firstname'] = $_SESSION['OBJ']['tr']->translate('entry_firstname');
    	$this->data['entry_lastname'] = $_SESSION['OBJ']['tr']->translate('entry_lastname');
    	$this->data['entry_company'] = $_SESSION['OBJ']['tr']->translate('entry_company');
    	$this->data['entry_address_1'] = $_SESSION['OBJ']['tr']->translate('entry_address_1');
    	$this->data['entry_address_2'] = $_SESSION['OBJ']['tr']->translate('entry_address_2');
    	$this->data['entry_postcode'] = $_SESSION['OBJ']['tr']->translate('entry_postcode');
    	$this->data['entry_city'] = $_SESSION['OBJ']['tr']->translate('entry_city');
    	$this->data['entry_country'] = $_SESSION['OBJ']['tr']->translate('entry_country');
    	$this->data['entry_zone'] = $_SESSION['OBJ']['tr']->translate('entry_zone');
    	$this->data['entry_default'] = $_SESSION['OBJ']['tr']->translate('entry_default');

    	$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');
    	$this->data['button_back'] = $_SESSION['OBJ']['tr']->translate('button_back');*/

		if (isset($this->error['firstname'])) {
    		$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
    		$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}

		if (isset($this->error['address_1'])) {
    		$this->data['error_address_1'] = $this->error['address_1'];
		} else {
			$this->data['error_address_1'] = '';
		}

		if (isset($this->error['city'])) {
    		$this->data['error_city'] = $this->error['city'];
		} else {
			$this->data['error_city'] = '';
		}

		if (isset($this->error['postcode'])) {
    		$this->data['error_postcode'] = $this->error['postcode'];
		} else {
			$this->data['error_postcode'] = '';
		}

		if (isset($this->error['country'])) {
			$this->data['error_country'] = $this->error['country'];
		} else {
			$this->data['error_country'] = '';
		}

		if (isset($this->error['zone'])) {
			$this->data['error_zone'] = $this->error['zone'];
		} else {
			$this->data['error_zone'] = '';
		}

		if (!isset($this->_request->address_id)) {
    		$this->data['action'] = Model_Url::getLink(array("controller"=>"account","action"=>"addressinsert"),'',SERVER_SSL);//HTTP_SERVER.'account/addressinsert';//$this->url->link('account/address/insert', '', 'SSL');
		} else {
    		$this->data['action'] = Model_Url::getLink(array("controller"=>"account","action"=>"addressupdate"),'address_id/'.$this->_request->address_id,SERVER_SSL);//HTTP_SERVER.'account/addressupdate/address_id/'.$this->_request->address_id;//$this->url->link('account/address/update', 'address_id=' . $this->_request->address_id'], 'SSL');
		}

    	if (isset($this->_request->address_id) && ($_SERVER['REQUEST_METHOD'] != 'POST')) {
		$acctAddrObj=new Model_AccountAddress();
			$address_info = $acctAddrObj->getAddress($this->_request->address_id);
		}

    	if (isset($this->_request->firstname)) {
      		$this->data['firstname'] = $this->_request->firstname;
    	} elseif (isset($address_info)) {
      		$this->data['firstname'] = $address_info['firstname'];
    	} else {
			$this->data['firstname'] = '';
		}

    	if (isset($this->_request->lastname)) {
      		$this->data['lastname'] = $this->_request->lastname;
    	} elseif (isset($address_info)) {
      		$this->data['lastname'] = $address_info['lastname'];
    	} else {
			$this->data['lastname'] = '';
		}

    	if (isset($this->_request->company)) {
      		$this->data['company'] = $this->_request->company;
    	} elseif (isset($address_info)) {
			$this->data['company'] = $address_info['company'];
		} else {
      		$this->data['company'] = '';
    	}

    	if (isset($this->_request->address_1)) {
      		$this->data['address_1'] = $this->_request->address_1;
    	} elseif (isset($address_info)) {
			$this->data['address_1'] = $address_info['address_1'];
		} else {
      		$this->data['address_1'] = '';
    	}

    	if (isset($this->_request->address_2)) {
      		$this->data['address_2'] = $this->_request->address_2;
    	} elseif (isset($address_info)) {
			$this->data['address_2'] = $address_info['address_2'];
		} else {
      		$this->data['address_2'] = '';
    	}

    	if (isset($this->_request->postcode)) {
      		$this->data['postcode'] = $this->_request->postcode;
    	} elseif (isset($address_info)) {
			$this->data['postcode'] = $address_info['postcode'];
		} else {
      		$this->data['postcode'] = '';
    	}

    	if (isset($this->_request->city)) {
      		$this->data['city'] = $this->_request->city;
    	} elseif (isset($address_info)) {
			$this->data['city'] = $address_info['city'];
		} else {
      		$this->data['city'] = '';
    	}

    	if (isset($this->_request->country_id)) {
      		$this->data['country_id'] = $this->_request->country_id;
    	}  elseif (isset($address_info)) {
      		$this->data['country_id'] = $address_info['country_id'];
    	} else {
      		$this->data['country_id'] = STORE_COUNTRY;
    	}

    	if (isset($this->_request->zone_id)) {
      		$this->data['zone_id'] = $this->_request->zone_id;
    	}  elseif (isset($address_info)) {
      		$this->data['zone_id'] = $address_info['zone_id'];
    	} else {
      		$this->data['zone_id'] = '';
    	}

		$locCtryObj=new Model_LocalisationCountry();
    	$this->data['countries'] = $locCtryObj->getCountries();

    	if (isset($this->_request->default)) {
      		$this->data['default'] = $this->_request->default;
    	} elseif (isset($this->_request->address_id)) {
		$custObj=new Model_Customer();
      		$this->data['default'] = $custObj->getAddressId() == $this->_request->address_id;
    	} else {
			$this->data['default'] = false;
		}

    	$this->data['back'] =Model_Url::getLink(array("controller"=>"account","action"=>"address"),'',SERVER_SSL);// HTTP_SERVER.'account/address';//$this->url->link('account/address', '', 'SSL');
		$this->view->data=$this->data;
		//$this->render('address-form');
            if (file_exists(PATH_TO_FILES.'account/address-form.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('address-form.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/address-form.phtml'))
            {
                $this->render('address-form');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('address-form.phtml');
            }
  	}

  	private function addressvalidateForm() {
    	if ((strlen(utf8_decode($this->_request->firstname)) < 1) || (strlen(utf8_decode($this->_request->firstname)) > 32)) {
      		$this->error['firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_account_address');
    	}

    	if ((strlen(utf8_decode($this->_request->lastname)) < 1) || (strlen(utf8_decode($this->_request->lastname)) > 32)) {
      		$this->error['lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_account_address');
    	}

    	if ((strlen(utf8_decode($this->_request->address_1)) < 3) || (strlen(utf8_decode($this->_request->address_1)) > 128)) {
      		$this->error['address_1'] = $_SESSION['OBJ']['tr']->translate('error_address_1_account_address');
    	}

    	if ((strlen(utf8_decode($this->_request->city)) < 2) || (strlen(utf8_decode($this->_request->city)) > 128)) {
      		$this->error['city'] = $_SESSION['OBJ']['tr']->translate('error_city_account_address');
    	}

		$locCtryObj=new Model_LocalisationCountry();
		$country_info = $locCtryObj->getCountry($this->_request->country_id);

		if ($country_info && $country_info['postcode_required'] && (strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
			$this->error['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_account_address');
		}

    	if ($this->_request->country_id == '') {
      		$this->error['country'] = $_SESSION['OBJ']['tr']->translate('error_country_account_address');
    	}

    	if ($this->_request->zone_id == '') {
      		$this->error['zone'] = $_SESSION['OBJ']['tr']->translate('error_zone_account_address');
    	}

    	if (!$this->error) {
      		return true;
		} else {
      		return false;
    	}
  	}

  	private function addressvalidateDelete() {
    	$acctAddressObj=new Model_AccountAddress();
		$custObj=new Model_Customer();
		if ($acctAddressObj->getTotalAddresses() == 1) {
      		$this->error['warning'] = $_SESSION['OBJ']['tr']->translate('error_delete_account_address');
    	}

    	if ($custObj->getAddressId() == $this->_request->address_id) {
      		$this->error['warning'] = $_SESSION['OBJ']['tr']->translate('error_default_account_address');
    	}

    	if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
  	}
	public function addressinsertAction() {
    	$custObj=new Model_Customer();
		$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_address')));
		if (!$custObj->isLogged()) {
	  		$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"address"),'',SERVER_SSL);//HTTP_SERVER.'account/address';//$this->url->link('account/address', '', 'SSL');

	  		//$this->redirect($this->url->link('account/login', '', 'SSL'));
			$this->_redirect($this->view->link_account_login);
    	}

    	//$this->document->setTitle($this->language->get('heading_title'));
		$acctAddrObj=new Model_AccountAddress();

    	if (($_SERVER['REQUEST_METHOD'] == 'POST') && $this->addressvalidateForm()) {
			$acctAddrObj->addAddress($this->_getAllParams());

      		$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_insert');

	  		//$this->redirect($this->url->link('account/address', '', 'SSL'));
			$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"address"),'',SERVER_SSL));
    	}
        $this->data['text_address']=$_SESSION['OBJ']['tr']->translate('text_add_address_account_address');

		$this->getForm();
  	}

  	public function addressupdateAction() {
	$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_address')));
    	$custObj=new Model_Customer();
		if (!$custObj->isLogged()) {
	  		$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"address"),'',SERVER_SSL);//HTTP_SERVER.'account/address';//$this->url->link('account/address', '', 'SSL');

	  		$this->_redirect($this->view->link_account_login);
    	}

    		$acctAddrObj=new Model_AccountAddress();
    	if (($_SERVER['REQUEST_METHOD'] == 'POST') && $this->addressvalidateForm()) {
       		$acctAddrObj->editAddress($this->_request->address_id,$this->_getAllParams());

			if (isset($_SESSION['shipping_address_id']) && ($this->_request->address_id == $_SESSION['shipping_address_id'])) {
	  			unset($_SESSION['shipping_methods']);
				unset($_SESSION['shipping_method']);
				$taxObj=new Model_Tax();
				$taxObj->setZone($this->_request->country_id, $this->_request->zone_id);
			}

			if (isset($_SESSION['payment_address_id']) && ($this->_request->address_id == $_SESSION['payment_address_id'])) {
	  			unset($_SESSION['payment_methods']);
				unset($_SESSION['payment_method']);
			}

			$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_update_account_address');

			$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"address"),'',SERVER_SSL));
    	}
                $this->data['text_address']=$_SESSION['OBJ']['tr']->translate('text_edit_address_account_address');
		$this->getForm();
  	}

  	public function addressdeleteAction() {
    	$custObj=new Model_Customer();
		if (!$custObj->isLogged()) {
	  		$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"address"),'',SERVER_SSL);//HTTP_SERVER.'account/address';//$this->url->link('account/address', '', 'SSL');

	  		//$this->redirect($this->url->link('account/login', '', 'SSL'));
			$this->redirect(Model_Url::getLink(array("controller"=>"account","action"=>"login"),'',SERVER_SSL));
    	}

    	$acctAddrObj=new Model_AccountAddress();
		//$this->document->setTitle($this->language->get('heading_title'));

    	if (isset($this->_request->address_id) && ($this->addressvalidateDelete())) {
			$acctAddrObj->deleteAddress($this->_request->address_id);

			if (isset($_SESSION['shipping_address_id']) && ($this->_request->address_id == $_SESSION['shipping_address_id'])) {
	  			unset($_SESSION['shipping_address_id']);
				unset($_SESSION['shipping_methods']);
				unset($_SESSION['shipping_method']);
			}

			if (isset($_SESSION['payment_address_id']) && ($this->_request->address_id == $_SESSION['payment_address_id'])) {
	  			unset($_SESSION['payment_address_id']);
				unset($_SESSION['payment_methods']);
				unset($_SESSION['payment_method']);
			}

			$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_delete');

	  		//$this->redirect($this->url->link('account/address', '', 'SSL'));
			$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"address"),'',SERVER_SSL));
    	}

		$this->addressgetList();
  	}

	public function wishlistAction()
	{
	$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_wishlist')));
		$custObj=new Model_Customer();
    	if (!$custObj->isLogged()) {
	  		$_SESSION['redirect'] = HTTP_SERVER.'account/wishlist';//$this->url->link('account/wishlist', '', 'SSL');
			$this->_redirect($this->view->link_account_login);
	}

		if (!isset($_SESSION['wishlist'])) {
			$_SESSION['wishlist'] = array();
		}

		if (isset($this->_request->remove)) {
			foreach ($this->_request->remove as $product_id) {
				$key = array_search($product_id, $_SESSION['wishlist']);

				if ($key !== false) {
					unset($_SESSION['wishlist'][$key]);
				}
			}

			$this->_redirect('account/wishlist');
		}

	
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_wishlist'),
			'href'      => HTTP_SERVER.'account/wishlist',//$this->url->link('account/wishlist'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

                $this->data['action'] = HTTP_SERVER.'account/wishlist';//$this->url->link('account/wishlist');

		$this->data['products'] = array();

		foreach ($_SESSION['wishlist'] as $product_id) {
		$prodObj=new Model_Products();
			$product_info = $prodObj->getProduct($product_id);

			if ($product_info) {
				if ($product_info['image']) {
					//$image = $this->model_tool_image->resize($product_info['image'], $this->config->get('config_image_wishlist_width'), $this->config->get('config_image_wishlist_height'));
					$imgSize=explode("*",IMAGE_WISHLIST_SIZE);
 					$image=PATH_TO_UPLOADS."products/".$product_info['image']."&w=".$imgSize[0]."&h=".$imgSize[1]."&zc=1";
				} else {
					$image = false;
				}
                                
                         $DISPLAY_STOCK=STOCK_DISPLAY=="false"?"0":"1";
			//echo "value of display stock".$DISPLAY_STOCK;
			if ($product_info['products_quantity'] <= 0) 
                        {
				$stock = $product_info['stock_status_id'];
			} elseif ($DISPLAY_STOCK) 
                        {
				$stock = $product_info['products_quantity'];
			} else {
                            $stock = $prodObj->getStockStatus(constant('DEFAULT_AVAILABILITY_STOCK_STATUS_ID'));
			}
		
				$currObj=new Model_currencies();
				$taxObj=new Model_Tax();
				if ((LOGIN_DISPLAY_PRICE && $custObj->isLogged()) || !LOGIN_DISPLAY_PRICE) {
					$price = $currObj->format($taxObj->calculate($product_info['price'], $product_info['tax_class_id'], DISPLAY_PRICE_WITH_TAX));
				} else {
					$price = false;
				}

				if ((float)$product_info['special']) {
					$special = $currObj->format($taxObj->calculate($product_info['special'], $product_info['tax_class_id'], DISPLAY_PRICE_WITH_TAX));
				} else {
					$special = false;
				}

				$this->data['products'][] = array(
					'product_id' => $product_info['products_id'],
					'thumb'      => $image,
					'name'       => $product_info['products_name'],
					'model'      => $product_info['products_model'],
					'stock'      => $stock,
					'price'      => $price,
					'special'    => $special,
					'href'       => HTTP_SERVER.'product/product-details/product_id/'.$product_info['products_id']//$this->url->link('product/product', 'product_id=' . $product_info['product_id'])
				);
			}
		}

		$this->data['continue'] = $this->view->link_account_account;//HTTP_SERVER.'account/account';//$this->url->link('account/account', '', 'SSL');
		$this->data['back'] = $this->view->link_account_account;//HTTP_SERVER.'account/account';//$this->url->link('account/account', '', 'SSL');
		$this->view->data=$this->data;
                if (file_exists(PATH_TO_FILES.'account/wishlist.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('wishlist.phtml');
            }else
             if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/wishlist.phtml'))
            {
                 $this->render('wishlist');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('wishlist.phtml');
            }
	}


	public function downloadAction()
	{
		$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_download')));
		$custObj=new Model_Customer();
		if (!$custObj->isLogged()) {
			$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"download"),'',SERVER_SSL);//HTTP_SERVER.'account/download';//$this->url->link('account/download', '', 'SSL');
			//$this->redirect($this->url->link('account/login', '', 'SSL'));
			$this->_redirect($this->view->link_account_login);
		}
 		//$this->document->setTitle($this->language->get('heading_title'));

     	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_download'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"download"),'',SERVER_SSL),//HTTP_SERVER.'account/download',//$this->url->link('account/download', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);
		$acctDldObj=new Model_AccountDownload();
		$download_total = $acctDldObj->getTotalDownloads();
		
		if ($download_total) {
	
			if (isset($this->_request->page)) {
				$page = $this->_request->page;
			} else {
				$page = 1;
			}

			$this->data['downloads'] = array();

			$results = $acctDldObj->getDownloads(($page - 1) * MAX_ITEMS_PER_PAGE_CATALOG, MAX_ITEMS_PER_PAGE_CATALOG);

			foreach ($results as $result) {
				if (file_exists(PATH_TO_UPLOADS_DIR."downloads/" . $result['orders_products_filename'])) {
					$size = filesize(PATH_TO_UPLOADS_DIR."downloads/" .$result['orders_products_filename']);
					//echo "size of ".$size;
					$i = 0;

					$suffix = array(
						'B',
						'KB',
						'MB',
						'GB',
						'TB',
						'PB',
						'EB',
						'ZB',
						'YB'
					);

					while (($size / 1024) > 1) {
						$size = $size / 1024;
						$i++;
					}

					$this->data['downloads'][] = array(
						'order_id'   => $result['orders_id'],
						'date_added' => date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($result['date_purchased'])),
						'name'       => $result['name'],
						'remaining'  => $result['remaining'],
						'size'       => round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i],
						'href'       => Model_Url::getLink(array("controller"=>"account","action"=>"download-file"),'order_download_id/'.$result['orders_products_download_id'],SERVER_SSL)//HTTP_SERVER.'account/download-file/order_download_id/'.$result['orders_products_download_id']//$this->url->link('account/download/download', 'order_download_id=' . $result['order_download_id'], 'SSL')
					);
				}
			}

			$pagination = new Model_FrontPagination();
			$pagination->total = $download_total;
			$pagination->page = $page;
			$pagination->limit = MAX_ITEMS_PER_PAGE_CATALOG;
			$pagination->text = $_SESSION['OBJ']['tr']->translate('text_pagination');
			$pagination->url = Model_Url::getLink(array("controller"=>"account","action"=>"download"),'page/{page}',SERVER_SSL);//HTTP_SERVER.'account/download/page/{page}';//$this->url->link('account/download', 'page={page}', 'SSL');

			$this->data['pagination'] = $pagination->render();

			$this->data['continue'] = $this->view->link_account_account;//HTTP_SERVER.'account/account';//$this->url->link('account/account', '', 'SSL');
			$this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'account/download.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('download.phtml');
            }else
             if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/download.phtml'))
            {
                $this->render('download');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('download.phtml');
            }
		} else {
			$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_account_download');

			$this->data['text_error'] = $_SESSION['OBJ']['tr']->translate('text_empty_account_download');

			$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');

			$this->data['continue'] = $this->link_account_account;//HTTP_SERVER.'account/account';//$this->url->link('account/account', '', 'SSL');

			$this->view->data=$this->data;
			//$this->render('not-found');
                        if (file_exists(PATH_TO_FILES.'account/not-found.phtml'))
                        {
                            $this->view->addScriptPath(PATH_TO_FILES.'account/');
                            $this->renderScript('not-found.phtml');
                        }else
                        if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/not-found.phtml'))
                        {
                             $this->render('not-found');
                        } else
                        {
                            $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                            $this->renderScript('not-found.phtml');
                        }
		}
	}
	public function downloadFileAction() {
		$custObj=new Model_Customer();
		$acctDldObj=new Model_AccountDownload();
		if (!$custObj->isLogged()) {
			$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"download"),'',SERVER_SSL);
			$this->_redirect($this->view->link_account_login);
		}

		if (isset($this->_request->order_download_id)) {
			$order_download_id = $this->_request->order_download_id;
		} else {
			$order_download_id = 0;
		}

		$download_info = $acctDldObj->getDownload($order_download_id);
               
		if ($download_info) {
		$file = PATH_TO_UPLOADS_DIR."downloads/".$download_info['orders_products_filename'];	
			$mask = basename($download_info['mask']);
			$mime = 'application/octet-stream';
                        $encoding = 'binary';
                	if (!headers_sent()) {
				if (file_exists($file)) {
                                        header('Pragma: public');
					header('Expires: 0');
					header('Content-Description: File Transfer');
					header('Content-Type: ' . $mime);
					header('Content-Transfer-Encoding: ' . $encoding);
					header('Content-Disposition: attachment; filename='.($mask ? $mask : basename($file)));
					header('Content-Length: ' . filesize($file));

					$file = readfile($file);

					print($file);
				} else {
					exit('Error: Could not find file ' . $file . '!');
				}
			} else {
				exit('Error: Headers already sent out!');
			}

			$acctDldObj->updateRemaining($this->_request->order_download_id);
                        exit;
			//$this->render('download');
		} else {
			//$this->redirect($this->url->link('account/download', '', 'SSL'));
			$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"download"),'',SERVER_SSL));

		}
		//$this->_redirect('account/download');

	}

	public function rewardAction()
	{
	$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_reward')));
		$custObj=new Model_Customer();
		if (!$custObj->isLogged()) {
	  		$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"reward"),'',SERVER_SSL);//HTTP_SERVER.'account/reward';//$this->url->link('account/newsletter', '', 'SSL');

	  		//$this->redirect($this->url->link('account/login', '', 'SSL'));
			$this->_redirect($this->view->link_account_login);
    	}

		
	   	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_reward_account_reward'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"reward"),'',SERVER_SSL),//HTTP_SERVER.'account/reward',//$this->url->link('account/newsletter', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

 		if (isset($this->_request->page)) {
			$page = $this->_request->page;
		} else {
			$page = 1;
		}

		$this->data['rewards'] = array();

		$data = array(
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * MAX_ITEMS_PER_PAGE_CATALOG,
			'limit' => MAX_ITEMS_PER_PAGE_CATALOG
		);
		$acctRewardObj=new Model_AccountReward();
		$reward_total = $acctRewardObj->getTotalRewards($data);

		$results = $acctRewardObj->getRewards($data);

 		foreach ($results as $result) {
			$this->data['rewards'][] = array(
				'order_id'    => $result['order_id'],
				'points'      => $result['points'],
				'description' => $result['description'],
				'date_added'  => date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($result['date_added'])),
				'href'        => Model_Url::getLink(array("controller"=>"account","action"=>"order"),'order_id/'.$result['order_id'],SERVER_SSL)//HTTP_SERVER.'account/order/order_id/'. $result['order_id']//$this->url->link('account/order/info', 'order_id=' . $result['order_id'], 'SSL')
			);

		}

		$pagination = new Model_FrontPagination();
		$pagination->total = $reward_total;
		$pagination->page = $page;
		$pagination->limit = MAX_ITEMS_PER_PAGE_CATALOG;
		$pagination->text = $_SESSION['OBJ']['tr']->translate('text_pagination');
		$pagination->url = Model_Url::getLink(array("controller"=>"account","action"=>"reward"),'page/{page}',SERVER_SSL);//HTTP_SERVER.'account/reward/page/{page}';//$this->url->link('account/reward', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['total'] = $custObj->getRewardPoints();


		//$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');



		$this->data['continue'] = $this->view->link_account_account;//HTTP_SERVER.'account/account';//$this->url->link('account/account', '', 'SSL');
		$this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'account/reward.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('reward.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/reward.phtml'))
            {
                $this->render('reward');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('reward.phtml');
            }
	}

 	public function transactionAction()
	{
		$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_transaction')));
		$custObj=new Model_Customer();
		if (!$custObj->isLogged()) {
	  	$_SESSION['redirect'] =Model_Url::getLink(array("controller"=>"account","action"=>"newsletter"),'',SERVER_SSL);// HTTP_SERVER.'account/newsletter';//$this->url->link('account/newsletter', '', 'SSL');

			$this->_redirect($this->view->link_account_login);
    	}
   	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_transaction'),
		'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"transaction"),'',SERVER_SSL),//HTTP_SERVER.'account/transaction',//$this->url->link('account/newsletter', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

              if (isset($this->_request->page)) {
			$page = $this->_request->page;
		} else {
			$page = 1;
		}

		$this->data['transactions'] = array();

		$data = array(
			'sort'  => 'date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * MAX_ITEMS_PER_PAGE_CATALOG,
			'limit' => MAX_ITEMS_PER_PAGE_CATALOG
		);
		$acctTranObj=new Model_AccountTransaction();
		$transaction_total = $acctTranObj->getTotalTransactions($data);

		$results = $acctTranObj->getTransactions($data);

 		$currObj=new Model_currencies();
    	foreach ($results as $result) {
			$this->data['transactions'][] = array(
				'amount'      => $currObj->format($result['amount'], DEFAULT_CURRENCY),
				'description' => $result['description'],
				'date_added'  => date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($result['date_added']))
			);
		}

		$pagination = new Model_FrontPagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = MAX_ITEMS_PER_PAGE_CATALOG;
		$pagination->text = $_SESSION['OBJ']['tr']->translate('text_pagination');
		$pagination->url = Model_Url::getLink(array("controller"=>"account","action"=>"transaction"),'page/{page}',SERVER_SSL);//HTTP_SERVER.'account/transaction/page/{page}';//$this->url->link('account/transaction', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['total'] = $currObj->format($custObj->getBalance());


		


		$this->data['continue'] = $this->view->link_account_account;//HTTP_SERVER.'account/account';//$this->url->link('account/account', '', 'SSL');
		$this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'account/transaction.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('transaction.phtml');
            }else
             if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/transaction.phtml'))
            {
                 $this->render('transaction');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('transaction.phtml');
            }
	}

	public function preDispatch()
	{
		if($this->getRequest()->getActionName()=='login' && $this->_request->email!="")
		{
 			$this->data[error_warning]=$_SESSION['OBJ']['tr']->translate('error_account_newsletter_login');//"Need to login in order to subscribe for newsletter!!";
		}

		if($this->getRequest()->getActionName()=='register' && $this->_request->email!="" && $this->_request->firstname=="")
		{
 			$this->data[error_warning]=$_SESSION['OBJ']['tr']->translate('error_account_newsletter_register');//"Need to register in order to subscribe for newsletter!!";
		}

	}

	public function newsletterFooterAction()
	{
		$custObj=new Model_Customer();
		if (!$custObj->isLogged())
		{
			$acctCustObj=new Model_AccountCustomer();
			if ($acctCustObj->getTotalCustomersByEmail(htmlspecialchars($this->_request->email, ENT_COMPAT))) {
				$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"login"),'email/'.$this->_request->email,SERVER_SSL));
			}else
			{
				$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"register"),'email/'.$this->_request->email,SERVER_SSL));
			}
    	}else
		{
			$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"newsletter"),'',SERVER_SSL));
		}
	}

	public function newsletterAction()
	{
            $this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_newsletter')));
		$custObj=new Model_Customer();
		if (!$custObj->isLogged()) {
	  		$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"newsletter"),'',SERVER_SSL);//HTTP_SERVER.'account/newsletter';//$this->url->link('account/newsletter', '', 'SSL');

	  		//$this->redirect($this->url->link('account/login', '', 'SSL'));
			$this->_redirect($this->view->link_account_login);
    	}

		//$this->document->setTitle($this->language->get('heading_title'));

		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$acctCustObj=new Model_AccountCustomer();

			$acctCustObj->editNewsletter($this->_request->newsletter);

			$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_success_account_newsletter');

			$this->_redirect($this->view->link_account_account);//$this->url->link('account/account', '', 'SSL'));
		}

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_newsletter'),
		'href'=> Model_Url::getLink(array("controller"=>"account","action"=>"newsletter"),'',SERVER_SSL),//HTTP_SERVER.'account/newsletter',//$this->url->link('account/newsletter', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);


    	$this->data['action'] = Model_Url::getLink(array("controller"=>"account","action"=>"newsletter"),'',SERVER_SSL);//HTTP_SERVER.'account/newsletter';//$this->url->link('account/newsletter', '', 'SSL');

		$this->data['newsletter'] = $custObj->getNewsletter();

		$this->data['back'] = $this->view->link_account_account;//HTTP_SERVER.'account/account';//$this->url->link('account/account', '', 'SSL');
		$this->view->data=$this->data;
		//define('PATH_TO_TEMPLATES','D:\xampp\htdocs\mve_front\application\views\scripts\templates');

            if (file_exists(PATH_TO_FILES.'account/newsletter.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('newsletter.phtml');
            }else if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/newsletter.phtml'))
            {
                 $this->render('newsletter');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('newsletter.phtml');
            }
	}

	 private function validateLogin() {
             
    	$custObj=new Model_Customer();
        if (!$custObj->login($this->_request->email, $this->_request->password)) {
      		$this->error['warning'] = $_SESSION['OBJ']['tr']->translate('error_login_account_login');
    	}
     	if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
  	}

	public function loginAction()
	{
	$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_login')));
		$custObj=new Model_Customer();
		if ($custObj->isLogged()) {
      		//$this->redirect($this->url->link('account/account', '', 'SSL'));
			$this->_redirect($this->view->link_account_account);
    	}

    	//$this->document->setTitle($this->language->get('heading_title'));

		if (($this->getRequest()->isPost()) && $this->validateLogin()) {
			unset($_SESSION['guest']);

			$acctAddrObj=new Model_AccountAddress();
			$address_info = $acctAddrObj->getAddress($custObj->getAddressId());

			if ($address_info) {
				$taxObj=new Model_Tax();
				$taxObj->setZone($address_info['entry_country_id'], $address_info['entry_zone_id']);
			}

			// Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
			if (isset($this->_request->redirect) && (strpos($this->_request->redirect, HTTP_SERVER) !== false || strpos($this->_request->redirect, HTTPS_SERVER) !== false)) {
				//$this->redirect(str_replace('&amp;', '&', $this->_request->redirect));
				$this->_redirect(str_replace('&amp;', '&', $this->_request->redirect));
			} else
			{

			//	print_r($_SESSION);
				//$this->redirect($this->url->link('account/account', '', 'SSL'));
			$this->_redirect($this->view->link_account_account);
				//exit;
			}
    	}

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_login_account_login'),
			'href'      => $this->view->link_account_login,       	//$this->url->link('account/login', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			//$this->data['error_warning'] = '';
			$this->data['error_warning'] = $this->data['error_warning'];
		}

		$this->data['action'] = $this->view->link_account_login;//HTTP_SERVER.'account/login';//$this->url->link('account/login', '', 'SSL');
		$this->data['register'] =$this->view->link_account_register;//HTTP_SERVER.'account/register';//$this->url->link('account/register', '', 'SSL');
		$this->data['forgotten'] = Model_Url::getLink(array("controller"=>"account","action"=>"forgotten"),'',SERVER_SSL);//HTTP_SERVER.'account/forgotten';//$this->url->link('account/forgotten', '', 'SSL');

    	// Added strpos check to pass McAfee PCI compliance test (http://forum.opencart.com/viewtopic.php?f=10&t=12043&p=151494#p151295)
		if (isset($this->_request->redirect) && (strpos($this->_request->redirect, HTTP_SERVER) !== false || strpos($this->_request->redirect, HTTPS_SERVER) !== false)) {
			$this->data['redirect'] = $this->_request->redirect;
		} elseif (isset($_SESSION['redirect'])) {
      		$this->data['redirect'] = $_SESSION['redirect'];

			unset($_SESSION['redirect']);
    	} else {
			$this->data['redirect'] = '';
		}

		if (isset($_SESSION['success'])) {
    		$this->data['success'] = $_SESSION['success'];

			unset($_SESSION['success']);
		} else {
			$this->data['success'] = '';
		}
		$this->view->data=$this->data;
                
            if (file_exists(PATH_TO_FILES.'account/login.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('login.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/login.phtml'))
            {
                $this->render('login');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('login.phtml');
            }
	}


	public function logoutAction()
	{
	$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_logout')));

			$custObj=new Model_Customer();
			//$cartObj=new Model_Cart();
		  	if ($custObj->isLogged()) {
      		$custObj->logout();
	  		//$cartObj->clear();

			unset($_SESSION['shipping_address_id']);
			unset($_SESSION['shipping_method']);
			unset($_SESSION['shipping_methods']);
			unset($_SESSION['payment_address_id']);
			unset($_SESSION['payment_method']);
			unset($_SESSION['payment_methods']);
			unset($_SESSION['comment']);
			unset($_SESSION['order_id']);
			unset($_SESSION['coupon']);

			//$taxObj=new Model_Tax();
			//$taxObj->setZone(STORE_COUNTRY, STORE_ZONE);

      		//$this->redirect($this->url->link('account/logout', '', 'SSL'));
			$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"logout"),'',SERVER_SSL));
    	}

    	//$this->document->setTitle($this->language->get('heading_title'));


      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_logout_account_logout'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"logout"),'',SERVER_SSL),//HTTP_SERVER.'account/logout',//$this->url->link('account/logout', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

    	$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_account_logout');

    	$this->data['text_message_success'] = $_SESSION['OBJ']['tr']->translate('text_message_account_logout');

    	$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');

    	$this->data['continue'] = HTTP_SERVER.'index/index';//$this->url->link('common/home');
        
		$this->view->data=$this->data;
		//$this->render('success');
            if (file_exists(PATH_TO_FILES.'account/success.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('success.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/success.phtml'))
            {
                $this->render('success');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('success.phtml');
            }
    	}

		private function validate()
		{
    	if ((strlen(utf8_decode($this->_request->firstname)) < 1) || (strlen(utf8_decode($this->_request->firstname)) > 32)) {
      		$this->error['firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_account_register');
    	}

    	if ((strlen(utf8_decode($this->_request->lastname)) < 1) || (strlen(utf8_decode($this->_request->lastname)) > 32)) {
      		$this->error['lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_account_register');
    	}

    	if ((strlen(utf8_decode($this->_request->email)) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->_request->email)) {
      		$this->error['email'] = $_SESSION['OBJ']['tr']->translate('error_email_account_register');
    	}

		$acctCustObj=new Model_AccountCustomer();

    	if ($acctCustObj->getTotalCustomersByEmail($this->_request->email)) {
      		$this->error['warning'] = $_SESSION['OBJ']['tr']->translate('error_exists_account_register');
    	}

    	if ((strlen(utf8_decode($this->_request->telephone)) < 3) || (strlen(utf8_decode($this->_request->telephone)) > 32)) {
      		$this->error['telephone'] = $_SESSION['OBJ']['tr']->translate('error_telephone_account_register');
    	}

    	if ((strlen(utf8_decode($this->_request->address_1)) < 3) || (strlen(utf8_decode($this->_request->address_1)) > 128)) {
      		$this->error['address_1'] = $_SESSION['OBJ']['tr']->translate('error_address_1_account_register');
    	}

    	if ((strlen(utf8_decode($this->_request->city)) < 2) || (strlen(utf8_decode($this->_request->city)) > 128)) {
      		$this->error['city'] = $_SESSION['OBJ']['tr']->translate('error_city_account_register');
    	}


	$locCtryObj=new Model_LocalisationCountry();

	$country_info = $locCtryObj->getCountry($this->_request->country_id);

		if ($country_info && $country_info['postcode_required'] && (strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
			$this->error['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_account_register');
		}

    	if ($this->_request->country_id == '') {
      		$this->error['country'] = $_SESSION['OBJ']['tr']->translate('error_country_account_register');
    	}

    	if ($this->_request->zone_id == '') {
      		$this->error['zone'] = $_SESSION['OBJ']['tr']->translate('error_zone_account_register');
    	}

    	if ((strlen(utf8_decode($this->_request->password)) < 4) || (strlen(utf8_decode($this->_request->password)) > 20)) {
      		$this->error['password'] = $_SESSION['OBJ']['tr']->translate('error_password_account_register');
    	}

    	if ($this->_request->confirm != $this->_request->password) {
      		$this->error['confirm'] = $_SESSION['OBJ']['tr']->translate('error_confirm_account_register');
    	}

		if (REGISTRATION_TERMS) {
			$infoObj=new Model_Information();
			$information_info = $infoObj->getInformation(REGISTRATION_TERMS);

			if ($information_info && !isset($this->_request->agree)) {
      			$this->error['warning'] = sprintf($_SESSION['OBJ']['tr']->translate('error_agree_account_register'), $information_info['title']);
			}
		}

    	if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
  	}

	public function successAction()
	{
	$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_account_success')));
	    //$this->document->setTitle($this->language->get('heading_title'));
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_success_account_success'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"success"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

    	$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_account_success');

		if (!constant(APPROVE_NEW_CUSTOMER)) {
    		$this->data['text_message_success'] = sprintf($_SESSION['OBJ']['tr']->translate('text_message_account_success'), HTTP_SERVER.'information/contact');
		} else {
			$this->data['text_message_success'] = sprintf($_SESSION['OBJ']['tr']->translate('text_approval_account_success'), STORE_NAME, HTTP_SERVER.'information/contact');
		}

    	//$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');

		$cartObj=new Model_Cart();
		if ($cartObj->hasProducts()) {
			$this->data['continue'] = Model_Url::getLink(array("controller"=>"checkout","action"=>"cart"),'',SERVER_SSL);//HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart', '', 'SSL');
		} else {
			$this->data['continue'] = $this->view->link_account_account;
		}
		$this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'account/success.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('success.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/success.phtml'))
            {
                $this->render('success');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('success.phtml');
            }

	}
	/*start order*/

	public function orderAction() {
	$custObj=new Model_Customer();
    	if (!$custObj->isLogged()) {
      		$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"order"),'',SERVER_SSL);//HTTP_SERVER.'account/order';//$this->url->link('account/order', '', 'SSL');

	  		//$this->redirect($this->url->link('account/login', '', 'SSL'));
			$this->_redirect($this->view->link_account_login);
    	}

    	//$this->document->setTitle($this->language->get('heading_title'));
		$acctOrdObj=new Model_AccountOrder();
		if (isset($this->_request->order_id)) { //reorder
			$order_info = $acctOrdObj->getOrder($this->_request->order_id);

			if ($order_info) {
				$order_products = $acctOrdObj->getOrderProducts($this->_request->order_id);
				foreach ($order_products as $order_product) {
					$option_data = array();

					$order_options = $acctOrdObj->getOrderOptions($this->_request->order_id, $order_product['orders_products_id']);

					foreach ($order_options as $order_option) {
						if ($order_option['type'] == 'select' || $order_option['type'] == 'radio') {
							$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'checkbox') {
							$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'input' || $order_option['type'] == 'textarea' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
							$option_data[$order_option['product_option_id']] = $order_option['value'];
						} elseif ($order_option['type'] == 'file') {
							$option_data[$order_option['product_option_id']] = $this->encryption->encrypt($order_option['value']);
						}
					}

					$_SESSION['success'] = sprintf($_SESSION['OBJ']['tr']->translate('text_success'), $this->_request->order_id);
					$cartObj=new Model_Cart();
					$cartObj->add($order_product['products_id'], $order_product['products_quantity'], $option_data);
				}
				//$this->redirect($this->url->link('checkout/cart'));
				$this->_redirect('checkout/cart');
			}
		}


      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_order'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"order"),'',SERVER_SSL),//HTTP_SERVER.'account/order',//$this->url->link('account/order', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

	
		$this->data['action'] = Model_Url::getLink(array("controller"=>"account","action"=>"order"),'',SERVER_SSL);//HTTP_SERVER.'account/order';//$this->url->link('account/order', '', 'SSL');

		if (isset($this->_request->page)) {
			$page = $this->_request->page;
		} else {
			$page = 1;
		}

		$this->data['orders'] = array();

		$order_total = $acctOrdObj->getTotalOrders();

		$results = $acctOrdObj->getOrders(($page - 1) * MAX_ITEMS_PER_PAGE_CATALOG, MAX_ITEMS_PER_PAGE_CATALOG);

		foreach ($results as $result) {
			$product_total = $acctOrdObj->getTotalOrderProductsByOrderId($result['orders_id']);

			$this->data['orders'][] = array(
				'order_id'   => $result['orders_id'],
				'name'       => $result['customers_name'] ,//$result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'date_added' => date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($result['date_purchased'])),
				'products'   => $product_total,
				'total'      => $this->currObj->format($result['total'],true, $result['currency'], $result['currency_value']),
				'href'       => Model_Url::getLink(array("controller"=>"account","action"=>"orderinfo"),'order_id/'.$result['orders_id'],SERVER_SSL)//HTTP_SERVER.'account/orderinfo/order_id/'.$result['orders_id'],//$this->url->link('account/order/info', 'order_id=' . $result['order_id'], 'SSL')
			);
		}

		$pagination = new Model_FrontPagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = MAX_ITEMS_PER_PAGE_CATALOG;
		$pagination->text = $_SESSION['OBJ']['tr']->translate('text_pagination');
		$pagination->url =Model_Url::getLink(array("controller"=>"account","action"=>"order"),'page/{page}',SERVER_SSL);// HTTP_SERVER.'account/order/page/{page}';//$this->url->link('account/order', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] = $this->view->link_account_account;//HTTP_SERVER.'account/account';//$this->url->link('account/account', '', 'SSL');
		$this->view->data=$this->data;
		//this->render('order-list');
            if (file_exists(PATH_TO_FILES.'account/order-list.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('order-list.phtml');
            }else
             if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/order-list.phtml'))
            {
                 $this->render('order-list');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('order-list.phtml');
            }
	}

	public function orderinfoAction() {

		if (isset($this->_request->order_id)) {
			$order_id = $this->_request->order_id;
		} else {
			$order_id = 0;
		}
		$custObj=new Model_Customer();
		if (!$custObj->isLogged()) {
			$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"orderinfo"),'order_id/'.$order_id,SERVER_SSL);//'account/orderinfo/order_id/'.$order_id;//$this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL');

			//$this->redirect($this->url->link('account/login', '', 'SSL'));
			$this->_redirect($this->view->link_account_login);
    	}
		$acctOrdObj=new Model_AccountOrder();
		$order_info = $acctOrdObj->getOrder($order_id);

		if ($order_info) {
                 
			if (isset($this->_request->selected)) {
                            
				$order_products = $acctOrdObj->getOrderProducts($order_id);
				$cartObj=new Model_Cart();
		                foreach ($order_products as $order_product) {
					if (in_array($order_product['orders_products_id'], $this->_request->selected)) {
						$option_data = array();

						$order_options = $acctOrdObj->getOrderOptions($order_id, $order_product['orders_products_id']);

						foreach ($order_options as $order_option) {
							if ($order_option['type'] == 'select' || $order_option['type'] == 'radio') {
								$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
							} elseif ($order_option['type'] == 'checkbox') {
								$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
							} elseif ($order_option['type'] == 'input' || $order_option['type'] == 'textarea' || $order_option['type'] == 'file' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
								$option_data[$order_option['product_option_id']] = $order_option['value'];
							}
						}

						$cartObj->add($order_product['products_id'], $order_product['products_quantity'], $option_data);
					}
				}
				$this->_redirect(Model_Url::getLink(array("controller"=>"checkout","action"=>"cart"),'',SERVER_SSL));
			}



			$url = '';

			if (isset($this->_request->page)) {
				$url .= '&page=' . $this->_request->page;
			}

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_order'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"order"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);
			$this->data['breadcrumbs'][] = array(
				'text'      => $_SESSION['OBJ']['tr']->translate('text_order_account_order'),
				'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"orderinfo"),'order_id/'.$this->_request->order_id,SERVER_SSL),//HTTP_SERVER.'account/orderinfo/order_id/'.$this->_request->order_id,//$this->url->link('account/order/info', 'order_id=' . $this->_request->order_id'] . $url, 'SSL'),
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
			);

			$this->data['action'] = Model_Url::getLink(array("controller"=>"account","action"=>"orderinfo"),'order_id/'.$this->_request->order_id,SERVER_SSL);//HTTP_SERVER.'account/orderinfo/order_id/'.$this->_request->order_id;//$this->url->link('account/order/info', 'order_id=' . $this->_request->order_id'] . $url, 'SSL');

			if ($order_info['invoice_no']) {
				$this->data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$this->data['invoice_no'] = '';
			}

			$this->data['order_id'] = $this->_request->order_id;
			$this->data['date_added'] = date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($order_info['date_added']));

			if ($order_info['shipping_address_format']) {
      			$format = $order_info['shipping_address_format'];
    		} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

    		$find = array(
	  			'{firstname}',
	  			'{lastname}',
	  			'{company}',
      			'{address_1}',
      			'{address_2}',
     			'{city}',
      			'{postcode}',
      			'{zone}',
				'{zone_code}',
      			'{country}'
			);

			$replace = array(
	  			'firstname' => $order_info['shipping_firstname'],
	  			'lastname'  => $order_info['shipping_lastname'],
	  			'company'   => $order_info['shipping_company'],
      			'address_1' => $order_info['shipping_address_1'],
      			'address_2' => $order_info['shipping_address_2'],
      			'city'      => $order_info['shipping_city'],
      			'postcode'  => $order_info['shipping_postcode'],
      			'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
      			'country'   => $order_info['shipping_country']
			);

			$this->data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$this->data['shipping_method'] = $order_info['shipping_method'];

			if ($order_info['payment_address_format']) {
      			$format = $order_info['payment_address_format'];
    		} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

    		$find = array(
	  			'{firstname}',
	  			'{lastname}',
	  			'{company}',
      			'{address_1}',
      			'{address_2}',
     			'{city}',
      			'{postcode}',
      			'{zone}',
				'{zone_code}',
      			'{country}'
			);

			$replace = array(
	  			'firstname' => $order_info['payment_firstname'],
	  			'lastname'  => $order_info['payment_lastname'],
	  			'company'   => $order_info['payment_company'],
      			'address_1' => $order_info['payment_address_1'],
      			'address_2' => $order_info['payment_address_2'],
      			'city'      => $order_info['payment_city'],
      			'postcode'  => $order_info['payment_postcode'],
      			'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
      			'country'   => $order_info['payment_country']
			);

			$this->data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

      		$this->data['payment_method'] = $order_info['payment_method'];

			$this->data['products'] = array();

			$products = $acctOrdObj->getOrderProducts($this->_request->order_id);

      		foreach ($products as $product) {
				$option_data = array();

				$options = $acctOrdObj->getOrderOptions($this->_request->order_id, $product['orders_products_id']);

         		foreach ($options as $option) {
          			if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => (strlen($option['value']) > 20 ? substr($option['value'], 0, 20) . '..' : $option['value']),
						);
					} else {
						$filename = substr($option['value'], 0, strrpos($option['value'], '.'));

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => (strlen($filename) > 20 ? substr($filename, 0, 20) . '..' : $filename)
						);
					}
        		}

        		$this->data['products'][] = array(
					'order_product_id' => $product['orders_products_id'],
          			'name'             => $product['products_name'],
          			'model'            => $product['products_model'],
          			'option'           => $option_data,
          			'quantity'         => $product['products_quantity'],
          			'price'            => $this->currObj->format($product['products_price'],true, $order_info['currency_code'], $order_info['currency_value']),
					'total'            => $this->currObj->format($product['final_price'],true, $order_info['currency_code'], $order_info['currency_value']),
					'selected'         => isset($this->_request->selected) && in_array($result['order_product_id'], $this->_request->selected),
					'return'=>HTTP_SERVER.'account/return-insert/order_id/'.$this->_request->order_id.'/product_id/'.$product['products_id']
        		);
      		}

      		$this->data['totals'] = $acctOrdObj->getOrderTotals($this->_request->order_id);

			$this->data['comment'] = $order_info['comment'];

			$this->data['histories'] = array();

			$results = $acctOrdObj->getOrderHistories($this->_request->order_id);
			foreach ($results as $result) {
        		$this->data['histories'][] = array(
          			'date_added' => date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comments'])
        		);
      		}

      		$this->data['continue'] = Model_Url::getLink(array("controller"=>"account","action"=>"order"),'',SERVER_SSL);//HTTP_SERVER.'account/order';//$this->url->link('account/order', '', 'SSL');
			$this->view->data=$this->data;
			//$this->render('order-info');
            if (file_exists(PATH_TO_FILES.'account/order-info.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('order-info.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/order-info.phtml'))
            {
                $this->render('order-info');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('order-info.phtml');
            }
    	} else {
			//$this->document->setTitle($_SESSION['OBJ']['tr']->translate('text_order'));

      		$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('text_order');

      		$this->data['text_error'] = $_SESSION['OBJ']['tr']->translate('text_error');

      		$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');



			$this->data['breadcrumbs'][] = array(
				'text'      => $_SESSION['OBJ']['tr']->translate('heading_title'),
				'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"order"),'',SERVER_SSL),//HTTP_SERVER.'account/order',//$this->url->link('account/order', '', 'SSL'),
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $_SESSION['OBJ']['tr']->translate('text_order'),
				'href'      =>Model_Url::getLink(array("controller"=>"account","action"=>"orderinfo"),'order_id/'.$order_id,SERVER_SSL),// HTTP_SERVER.'account/orderinfo/order_id/'.$order_id,//$this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL'),
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
			);

      		$this->data['continue'] = Model_Url::getLink(array("controller"=>"account","action"=>"order"),'',SERVER_SSL);//HTTP_SERVER.'account/order';//$this->url->link('account/order', '', 'SSL');
			 			$this->view->data=$this->data;
			//$this->render('not-found');
            if (file_exists(PATH_TO_FILES.'account/not-found.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('not-found.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/not-found.phtml'))
            {
                $this->render('not-found');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('not-found.phtml');
            }
    	}
  	}
		/*end download*/

		public function returnAction() {
		$this->customer=new Model_Customer();
    	if (!$this->customer->isLogged()) {
      		//$_SESSION['redirect'] = $this->url->link('account/return', '', 'SSL');
			$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"return"),'',SERVER_SSL);//HTTP_SERVER.'account/return';//$this->url->link('account/return', '', 'SSL');

	  		//$this->redirect($this->url->link('account/login', '', 'SSL'));
			$this->_redirect($this->view->link_account_login);
    	}

    	//$this->document->setTitle($this->language->get('heading_title'));

      	 $url = '';

		if (isset($this->_request->page)) {
			$url .= '&page=' . $this->_request->page;
		}

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_return'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"return"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

		//$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_account_return');

		if (isset($this->_request->page)) {
			$page = $this->_request->page;
		} else {
			$page = 1;
		}

		$this->data['returns'] = array();
		$accRetObj=new Model_AccountReturn();
		$return_total = $accRetObj->getTotalReturns();

		$results = $accRetObj->getReturns(($page - 1) * 10, 10);

		foreach ($results as $result) {
			$this->data['returns'][] = array(
				'return_id'  => $result['return_id'],
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'date_added' => date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($result['date_added'])),
				'href'       => HTTP_SERVER.'account/return-info/return_id/'.$result['return_id'].$url
				//$this->url->link('account/return/info', 'return_id=' . $result['return_id'] . $url, 'SSL')
			);
		}

		$pagination = new Model_FrontPagination();
		$pagination->total = $return_total;
		$pagination->page = $page;
		$pagination->limit = MAX_ITEMS_PER_PAGE_CATALOG;
		$pagination->text = $_SESSION['OBJ']['tr']->translate('text_pagination');
		$pagination->url =Model_Url::getLink(array("controller"=>"account","action"=>"history"),'page/{page}',SERVER_SSL);

		$this->data['pagination'] = $pagination->render();

		$this->data['continue'] =$this->view->link_account_account;

		$this->view->data=$this->data;
		//$this->render('return-list');
            if (file_exists(PATH_TO_FILES.'account/return-list.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('return-list.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/return-list.phtml'))
            {
                $this->render('return-list');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('return-list.phtml');
            }
	}

		public function returnInfoAction() {

		if (isset($this->_request->return_id)) {
			$return_id = $this->_request->return_id;
		} else {
			$return_id = 0;
		}
		$this->customer=new Model_Customer();
		if (!$this->customer->isLogged()) {
			//$_SESSION['redirect'] = $this->url->link('account/return/info', 'return_id=' . $return_id, 'SSL');
			$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"account","action"=>"return-info"),'return_id/'.$return_id,SERVER_SSL);//HTTP_SERVER.'account/return-info/return_id/'.$return_id;//$this->url->link('account/return/info', 'return_id=' . $return_id, 'SSL');

			//$this->redirect($this->url->link('account/login', '', 'SSL'));
			$this->_redirect($this->view->link_account_login);
		}
		$accRetObj=new Model_AccountReturn();
		$return_info = $accRetObj->getReturn($return_id);
		/*echo "<pre>";
		print_r($return_info);
		echo "</pre>";*/
		if ($return_info) {
			//$this->document->setTitle($this->language->get('text_return'));
			$url = '';

			if (isset($this->_request->page)) {
				$url .= '&page=' . $this->_request->page;
			}



			$this->data['breadcrumbs'][] = array(
				'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_return'),
				'href'      => HTTP_SERVER.'account/return',//$this->url->link('account/return', $url, 'SSL'),
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $_SESSION['OBJ']['tr']->translate('text_return_account_return'),
				'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"return-info"),'return_id/'.$this->_request->return_id,SERVER_SSL),//HTTP_SERVER.'account/return-info/return_id/'.$this->_request->return_id.$url,//$this->url->link('account/return/info', 'return_id=' . $this->_request->return_id'] . $url, 'SSL'),
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
			);


			$this->data['return_id'] = $return_info['return_id'];
			$this->data['order_id'] = $return_info['order_id'];
			$this->data['date_ordered'] = date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($return_info['date_ordered']));
			$this->data['date_added'] = date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($return_info['date_added']));
			$this->data['firstname'] = $return_info['firstname'];
			$this->data['lastname'] = $return_info['lastname'];
			$this->data['email'] = $return_info['email'];
			$this->data['telephone'] = $return_info['telephone'];
			$this->data['product'] = $return_info['product'];
			$this->data['model'] = $return_info['model'];
			$this->data['quantity'] = $return_info['quantity'];
			$this->data['reason'] = $return_info['reason'];
			$this->data['opened'] = $return_info['opened'] ? $_SESSION['OBJ']['tr']->translate('text_yes') : $_SESSION['OBJ']['tr']->translate('text_no');
			$this->data['comment'] = nl2br($return_info['comment']);
			$this->data['action'] = $return_info['action'];

			$this->data['histories'] = array();

			$results = $accRetObj->getReturnHistories($this->_request->return_id);

      		foreach ($results as $result) {
        		$this->data['histories'][] = array(
          			'date_added' => date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comment'])
        		);
      		}

			$this->data['continue'] = Model_Url::getLink(array("controller"=>"account","action"=>"return"),'',SERVER_SSL);//HTTP_SERVER.'account/return'.$url;//$this->url->link('account/return', $url, 'SSL');

				$this->view->data=$this->data;
				//$this->render('return-info');
             if (file_exists(PATH_TO_FILES.'account/return-info.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('return-info.phtml');
            }else
             if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/return-info.phtml'))
            {
                 $this->render('return-info');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('return-info.phtml');
            }
		} else {
			//$this->document->setTitle($this->language->get('text_return'));


			$this->data['breadcrumbs'][] = array(
				'text'      => $_SESSION['OBJ']['tr']->translate('text_account_return'),
				'href'      => $this->view->link_account_account,//HTTP_SERVER.'account/account',//$this->url->link('account/account', '', 'SSL'),
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')//$this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_return'),
				'href'      => HTTP_SERVER.'account/return',//$this->url->link('account/return', '', 'SSL'),
				'separator' =>$_SESSION['OBJ']['tr']->translate('text_separator')
			);

			$url = '';

			if (isset($this->_request->page)) {
				$url .= '/page/' . $this->_request->page;
			}

			$this->data['breadcrumbs'][] = array(
				'text'      => $_SESSION['OBJ']['tr']->translate('text_return_account_return'),
				'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"return-info"),'return_id/'.$return_id.$url,SERVER_SSL),//HTTP_SERVER.'account/return-info/return_id/'.$return_id.$url,//$this->url->link('account/return/info', 'return_id=' . $return_id . $url, 'SSL'),
				'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
			);

			$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('text_return_account_return');

			$this->data['continue'] =Model_Url::getLink(array("controller"=>"account","action"=>"return"),'',SERVER_SSL);// HTTP_SERVER.'account/return';//$this->url->link('account/return', '', 'SSL');

			/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = SITE_DEFAULT_TEMPLATE.'/template/error/not_found.tpl';
			}*/
			$this->view->data=$this->data;
			//$this->render('not-found');
                        if (file_exists(PATH_TO_FILES.'account/not-found.phtml'))
                        {
                            $this->view->addScriptPath(PATH_TO_FILES.'account/');
                            $this->renderScript('not-found.phtml');
                        }else
                         if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/not-found.phtml'))
                        {
                            $this->render('not-found');
                        } else
                        {
                            $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                            $this->renderScript('not-found.phtml');
                        }
 		}
	}

		public function returnInsertAction() {

    	if (($_SERVER['REQUEST_METHOD'] == 'POST') && $this->validateReturn()) {
			$acctRetObj=new Model_AccountReturn();
			$acctRetObj->addReturn($this->_getAllParams());

			//$this->redirect($this->url->link('account/return/success', '', 'SSL'));
			$this->_redirect(Model_Url::getLink(array("controller"=>"account","action"=>"return-success"),'',SERVER_SSL));
    	}

		//$this->document->setTitle($this->language->get('heading_title'));



      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_return'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"return-insert"),'',SERVER_SSL),//HTTP_SERVER.'account/return-insert',//$this->url->link('account/return/insert', '', 'SSL'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

    	//$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_account_return');
		$this->customer=new Model_Customer();


		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['order_id'])) {
			$this->data['error_order_id'] = $this->error['order_id'];
		} else {
			$this->data['error_order_id'] = '';
		}

		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}

		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}

		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}

		if (isset($this->error['product'])) {
			$this->data['error_product'] = $this->error['product'];
		} else {
			$this->data['error_product'] = '';
		}

		if (isset($this->error['model'])) {
			$this->data['error_model'] = $this->error['model'];
		} else {
			$this->data['error_model'] = '';
		}

		if (isset($this->error['reason'])) {
			$this->data['error_reason'] = $this->error['reason'];
		} else {
			$this->data['error_reason'] = '';
		}

 		if (isset($this->error['captcha'])) {
			$this->data['error_captcha'] = $this->error['captcha'];
		} else {
			$this->data['error_captcha'] = '';
		}

		//$this->data['action'] = $this->url->link('account/return/insert', '', 'SSL');
		$this->data['action'] = Model_Url::getLink(array("controller"=>"account","action"=>"return-insert"),'',SERVER_SSL);//HTTP_SERVER.'account/return-insert';//$this->url->link('account/return/insert', '', 'SSL');



		if (isset($this->_request->order_id)) {
			$ordObj=new Model_CheckoutOrder();
			$order_info = $ordObj->getOrder($this->_request->order_id);
			if($order_info['customers_name']!="")
			{
				$cust_name=explode("  ",$order_info['customers_name']);
				//print_r($cust_name);
			}
		}



		if (isset($this->_request->product_id)) {
		$prodObj=new Model_Products();
			$product_info = $prodObj->getProduct($this->_request->product_id);
		}

    	if (isset($this->_request->order_id)) {
	  		$this->data['order_id'] = $this->_request->order_id;
		} elseif (!empty($order_info)) {
				$this->data['order_id'] = $order_info['order_id'];
		} else {

      		$this->data['order_id'] = '';
    	}

    	if (isset($this->_request->date_ordered)) {
      		$this->data['date_ordered'] = $this->_request->date_ordered;
		} elseif (!empty($order_info)) {
			$this->data['date_ordered'] = date('Y-m-d', strtotime($order_info['date_added']));
		} else {
      		$this->data['date_ordered'] = '';
    	}

		if (isset($this->_request->firstname)) {
			$this->data['firstname'] = $this->_request->firstname;
		} elseif (!empty($order_info)) {
				$this->data['firstname'] =$cust_name[0];
		} else {
				$this->data['firstname'] = $this->customer->getFirstName();
		}

		if (isset($this->_request->lastname)) {
    		$this->data['lastname'] = $this->_request->lastname;
		} elseif (!empty($order_info)) {
			$this->data['lastname'] = $cust_name[1];
		} else {
			$this->data['lastname'] = $this->customer->getLastName();
		}

		if (isset($this->_request->email)) {
    		$this->data['email'] = $this->_request->email;
		} elseif (!empty($order_info)) {
			$this->data['email'] = $order_info['email'];
		} else {
			$this->data['email'] = $this->customer->getEmail();
		}

		if (isset($this->_request->telephone)) {
    		$this->data['telephone'] = $this->_request->telephone;
		} elseif (!empty($order_info)) {
			$this->data['telephone'] = $order_info['telephone'];
		} else {
			$this->data['telephone'] = $this->customer->getTelephone();
		}
		//echo $product_info['products_name']."product name";
		if (isset($this->_request->product)) {
    		$this->data['product'] = $this->_request->product;
		} elseif (!empty($product_info)) {
			$this->data['product'] = $product_info['products_name'];
		} else {
			$this->data['product'] = '';
		}

		if (isset($this->_request->model)) {
    		$this->data['model'] = $this->_request->model;
		} elseif (!empty($product_info)) {
			$this->data['model'] = $product_info['products_model'];
		} else {
			$this->data['model'] = '';
		}

		if (isset($this->_request->quantity)) {
    		$this->data['quantity'] = $this->_request->quantity;
		} else {
			$this->data['quantity'] = 1;
		}

		if (isset($this->_request->opened)) {
    		$this->data['opened'] = $this->_request->opened;
		} else {
			$this->data['opened'] = false;
		}

		if (isset($this->_request->return_reason_id)) {
    		$this->data['return_reason_id'] = $this->_request->return_reason_id;
		} else {
			$this->data['return_reason_id'] = '';
		}
		$retReasonObj=new Model_AccountReturn();
    	$this->data['return_reasons'] = $retReasonObj->getReturnReasons();

		if (isset($this->_request->comment)) {
    		$this->data['comment'] = $this->_request->comment;
		} else {
			$this->data['comment'] = '';
		}

		if (isset($this->_request->captcha)) {
			$this->data['captcha'] = $this->_request->captcha;
		} else {
			$this->data['captcha'] = '';
		}

		$this->data['back'] = $this->view->link_account_account;

	
		$this->view->data=$this->data;
		//$this->render('return-form');
            if (file_exists(PATH_TO_FILES.'account/return-form.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('return-form.phtml');
            }else
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/return-form.phtml'))
            {
                
                $this->render('return-form');
            } else
            {
                
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('return-form.phtml');
            }
  	}

	private function validateReturn() {
    	if (!$this->_request->order_id) {
      		$this->error['order_id'] = $_SESSION['OBJ']['tr']->translate('error_order_id_account_return');
    	}

		if ((strlen(utf8_decode($this->_request->firstname)) < 1) || (strlen(utf8_decode($this->_request->firstname)) > 32)) {
      		$this->error['firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_account_return');
    	}

    	if ((strlen(utf8_decode($this->_request->lastname)) < 1) || (strlen(utf8_decode($this->_request->lastname)) > 32)) {
      		$this->error['lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_account_return');
    	}

    	if ((strlen(utf8_decode($this->_request->email)) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->_request->email)) {
      		$this->error['email'] = $_SESSION['OBJ']['tr']->translate('error_email_account_return');
    	}

    	if ((strlen(utf8_decode($this->_request->telephone)) < 3) || (strlen(utf8_decode($this->_request->telephone)) > 32)) {
      		$this->error['telephone'] = $_SESSION['OBJ']['tr']->translate('error_telephone_account_return');
    	}

		if ((strlen(utf8_decode($this->_request->product)) < 1) || (strlen(utf8_decode($this->_request->product)) > 255)) {
			$this->error['product'] = $_SESSION['OBJ']['tr']->translate('error_product_account_return');
		}

		if ((strlen(utf8_decode($this->_request->model)) < 1) || (strlen(utf8_decode($this->_request->model)) > 64)) {
			$this->error['model'] = $_SESSION['OBJ']['tr']->translate('error_model_account_return');
		}

		if (empty($this->_request->return_reason_id)) {
			$this->error['reason'] = $_SESSION['OBJ']['tr']->translate('error_reason_account_return');
		}

    	if (empty($_SESSION['captcha']) || ($_SESSION['captcha'] != $this->_request->captcha)) {
      		$this->error['captcha'] = $_SESSION['OBJ']['tr']->translate('error_captcha_account_return');
    	}

		if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
  	}

	public function returnSuccessAction(){
	
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_account_return'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"return"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);
        
        $this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_success_account_success'),
			'href'      => Model_Url::getLink(array("controller"=>"account","action"=>"return-success"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

    	$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_account_return');

    	$this->data['text_message_success'] = $_SESSION['OBJ']['tr']->translate('text_message_account_return');

    	$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');

    	$this->data['continue'] = HTTP_SERVER.'index/index';
        $this->view->data=$this->data;
        if (file_exists(PATH_TO_FILES.'account/success.phtml'))
            {
                $this->view->addScriptPath(PATH_TO_FILES.'account/');
                $this->renderScript('success.phtml');
            }else
	     if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/success.phtml'))
            {
                 $this->render('success');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('success.phtml');
            }
	}
}

