<?php
/**
 * Handling Errors in the application
 *
 * @category   Zend
 * @package    AffiliateController
 * @author     suresh babu k
 */
class AffiliateController extends My_Controller_Main {
	public $tr=null;
	public $affiliate=null;
	public $affinfo=null;
	public $currObj=null;
	public $custObj=null;
	public $infoObj=null;
	private $error = array();

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
       		'text'      => $_SESSION['OBJ']['tr']->translate('text_home'),
			'href'      => HTTP_SERVER."index/index",
       		'separator' => false
   		);

		      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_affiliate_account'),
			'href'      => Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

		/*start modules*/
		$moduleObj=new Model_Module();
		$this->view->pos=$moduleObj->getModules(array('page'=>'10')); //refers to category page as per r_layout
		/*end modules*/

		$this->affiliate=new Model_Affiliate();
		$this->affinfo=new Model_Affinfo();
	}

		public function registerAction() {
		if ($this->affinfo->isLogged()) {
				$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL));
    	}
$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_affiliate_register')));
    	//$this->document->setTitle($_SESSION['OBJ']['tr']->translate('heading_title'));

    	if (($_SERVER['REQUEST_METHOD'] == 'POST') && $this->validateregistration()) {
			$this->affiliate->addAffiliate($this->_getAllParams());

			$this->affinfo->login($this->_request->email, $this->_request->password);

	  		//$this->redirect($this->url->link('affiliate/success'));
			$this->_redirect(HTTP_SERVER.'affiliate/success');
    	}

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_register_affiliate_register'),
			'href'      => Model_Url::getLink(array("controller"=>"affiliate","action"=>"register"),'',SERVER_SSL),
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

    	$this->data['action'] =Model_Url::getLink(array("controller"=>"affiliate","action"=>"register"),'',SERVER_SSL);

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
    		$this->data['email'] = $this->_request->email;
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

		if (isset($this->_request->website)) {
    		$this->data['website'] = $this->_request->website;
		} else {
			$this->data['website'] = '';
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
      		$this->data['country_id'] = STORE_COUNTRY;
    	}

    	if (isset($this->_request->zone_id)) {
      		$this->data['zone_id'] = $this->_request->zone_id;
		} else {
      		$this->data['zone_id'] = '';
    	}


		$ctryObj=new Model_LocalisationCountry();
    	$this->data['countries'] = $ctryObj->getCountries();

		if (isset($this->_request->tax)) {
    		$this->data['tax'] = $this->_request->tax;
		} else {
			$this->data['tax'] = '';
		}

		if (isset($this->_request->payment)) {
    		$this->data['payment'] = $this->_request->payment;
		} else {
			$this->data['payment'] = 'cheque';
		}

		if (isset($this->_request->cheque)) {
    		$this->data['cheque'] = $this->_request->cheque;
		} else {
			$this->data['cheque'] = '';
		}

		if (isset($this->_request->paypal)) {
    		$this->data['paypal'] = $this->_request->paypal;
		} else {
			$this->data['paypal'] = '';
		}

		if (isset($this->_request->bank_name)) {
    		$this->data['bank_name'] = $this->_request->bank_name;
		} else {
			$this->data['bank_name'] = '';
		}

		if (isset($this->_request->bank_branch_number)) {
    		$this->data['bank_branch_number'] = $this->_request->bank_branch_number;
		} else {
			$this->data['bank_branch_number'] = '';
		}

		if (isset($this->_request->bank_swift_code)) {
    		$this->data['bank_swift_code'] = $this->_request->bank_swift_code;
		} else {
			$this->data['bank_swift_code'] = '';
		}

		if (isset($this->_request->bank_account_name)) {
    		$this->data['bank_account_name'] = $this->_request->bank_account_name;
		} else {
			$this->data['bank_account_name'] = '';
		}

		if (isset($this->_request->bank_account_number)) {
    		$this->data['bank_account_number'] = $this->_request->bank_account_number;
		} else {
			$this->data['bank_account_number'] = '';
		}

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

		if (constant('AFFILIATE_TERMS')) {
			$infoObj=new Model_Information();
			$information_info = $infoObj->getInformation(constant('AFFILIATE_TERMS'));

			if ($information_info) {
				//$this->data['text_agree'] = sprintf($_SESSION['OBJ']['tr']->translate('text_agree'), $this->url->link('information/information/info', 'information_id=' . $this->config->get('config_affiliate_id'), 'SSL'), $information_info['title'], $information_info['title']);
				$this->data['text_agree'] = sprintf($_SESSION['OBJ']['tr']->translate('text_agree_affiliate_register'), HTTP_SERVER.'ajax/info/information_id/'.AFFILIATE_TERMS
				//$this->url->link('information/information/info', 'information_id=' . $this->config->get('config_account_id'), 'SSL')
					, $information_info['title'], $information_info['title']);
					//echo $this->data['text_agree'];
					//exit;

			} else {
				$this->data['text_agree'] = '';
			}
		} else {
			$this->data['text_agree'] = '';
		}

		if (isset($this->_request->agree)) {
      		$this->data['agree'] = $this->_request->agree;
		} else {
			$this->data['agree'] = false;
		}

		/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/affiliate/register.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/affiliate/register.tpl';
		} else {
			$this->template = SITE_DEFAULT_TEMPLATE.'/template/affiliate/register.tpl';
		}*/
		$this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'affiliate/register.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'affiliate/');
                    $this->renderScript('register.phtml');
            }else 
            if (file_exists(PATH_TO_FILES.'affiliate/register.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'affiliate/');
                    $this->renderScript('register.phtml');
            }else          
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/affiliate/register.phtml'))
            {
                $this->render('register');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/affiliate/');
                $this->renderScript('register.phtml');
            }
  	}

  	private function validateregistration() {
    	if ((strlen(utf8_decode($this->_request->firstname)) < 1) || (strlen(utf8_decode($this->_request->firstname)) > 32)) {
      		$this->error['firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_affiliate_register');
    	}

    	if ((strlen(utf8_decode($this->_request->lastname)) < 1) || (strlen(utf8_decode($this->_request->lastname)) > 32)) {
      		$this->error['lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_affiliate_register');
    	}

    	if ((strlen(utf8_decode($this->_request->email)) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->_request->email)) {
      		$this->error['email'] = $_SESSION['OBJ']['tr']->translate('error_email_affiliate_register');
    	}

    	if ($this->affiliate->getTotalAffiliatesByEmail($this->_request->email)) {
      		$this->error['warning'] = $_SESSION['OBJ']['tr']->translate('error_exists_affiliate_register');
    	}

    	if ((strlen(utf8_decode($this->_request->telephone)) < 3) || (strlen(utf8_decode($this->_request->telephone)) > 32)) {
      		$this->error['telephone'] = $_SESSION['OBJ']['tr']->translate('error_telephone_affiliate_register');
    	}

    	if ((strlen(utf8_decode($this->_request->address_1)) < 3) || (strlen(utf8_decode($this->_request->address_1)) > 128)) {
      		$this->error['address_1'] = $_SESSION['OBJ']['tr']->translate('error_address_1_affiliate_register');
    	}

    	if ((strlen(utf8_decode($this->_request->city)) < 2) || (strlen(utf8_decode($this->_request->city)) > 128)) {
      		$this->error['city'] = $_SESSION['OBJ']['tr']->translate('error_city_affiliate_register');
    	}

		$ctryObj=new Model_LocalisationCountry();

		$country_info = $ctryObj->getCountry($this->_request->country_id);

		if ($country_info && $country_info['postcode_required'] && (strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
			$this->error['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_affiliate_register');
		}

    	if ($this->_request->country_id == '') {
      		$this->error['country'] = $_SESSION['OBJ']['tr']->translate('error_country_affiliate_register');
    	}

    	if ($this->_request->zone_id == '') {
      		$this->error['zone'] = $_SESSION['OBJ']['tr']->translate('error_zone_affiliate_register');
    	}

    	if ((strlen(utf8_decode($this->_request->password)) < 4) || (strlen(utf8_decode($this->_request->password)) > 20)) {
      		$this->error['password'] = $_SESSION['OBJ']['tr']->translate('error_password_affiliate_register');
    	}

    	if ($this->_request->confirm != $this->_request->password) {
      		$this->error['confirm'] = $_SESSION['OBJ']['tr']->translate('error_confirm_affiliate_register');
    	}

		if (constant('AFFILIATE_TERMS')) {
			$infoObj=new Model_Information();
			$information_info = $infoObj->getInformation(constant('AFFILIATE_TERMS'));

			if ($information_info && !isset($this->_request->agree)) {
      			$this->error['warning'] = sprintf($_SESSION['OBJ']['tr']->translate('error_agree_affiliate_register'), $information_info['title']);
			}
		}

    	if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
  	}

	public function successAction() {
		//$this->document->setTitle($_SESSION['OBJ']['tr']->translate('heading_title'));


$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('text_success_affiliate_success')));
	
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_success_affiliate_success'),
			'href'      => HTTP_SERVER.'affiliate/success',//$this->url->link('information/contact'),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

    	$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_affiliate_success');

    	//$this->data['text_message'] = sprintf($_SESSION['OBJ']['tr']->translate('text_approval'), $this->config->get('config_name'), $this->url->link('information/contact'));
		//$this->data['text_message_success'] = sprintf($_SESSION['OBJ']['tr']->translate('text_approval'), STORE_NAME,HTTP_SERVER.'information/contact');
    			$this->data['text_message_success'] = sprintf($_SESSION['OBJ']['tr']->translate('text_approval_affiliate_success'), STORE_NAME, HTTP_SERVER.'information/contact');
		//$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');

    	//$this->data['continue'] = HTTP_SERVER.'index/index';//$this->url->link('common/home');

		/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/success.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/common/success.tpl';
		} else {
			$this->template = SITE_DEFAULT_TEMPLATE.'/template/common/success.tpl';
		}*/
		$this->view->data=$this->data;
		//$this->renderScript('account/success.phtml');
            //echo PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/account/success.phtml';
            //exit;    
            if (file_exists(PATH_TO_FILES.'account/success.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'account/');
                    $this->renderScript('success.phtml');
            }else  if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/success.phtml'))
            {

                $this->renderScript('account/success.phtml');
            } else
            {
                
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('success.phtml');
            }
	}

	public function loginAction() {
	$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_affiliate_login')));
		if ($this->affinfo->isLogged()) {
      		$this->_redirect($this->url_to_site.'affiliate/account');
    	}
	  	if (($_SERVER['REQUEST_METHOD'] == 'POST') && isset($this->_request->email) && isset($this->_request->password) && $this->validatelogin()) {
			if (isset($this->_request->redirect)) {
				$this->_redirect(str_replace('&amp;', '&', $this->_request->redirect));
			} else {
				//$this->redirect($this->url->link('affiliate/account', '', 'SSL'));
				$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL));
			}
		}


      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_login_affiliate_login'),
			'href'      => Model_Url::getLink(array("controller"=>"affiliate","action"=>"login"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['action'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"login"),'',SERVER_SSL);
		$this->data['register'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"register"),'',SERVER_SSL);
		$this->data['forgotten'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"forgotten"),'',SERVER_SSL);

		if (isset($this->_request->redirect)) {
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
            
            if (file_exists(PATH_TO_FILES.'affiliate/login.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'affiliate/');
                    $this->renderScript('login.phtml');
            }else  
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/affiliate/login.phtml'))
            {
             
                $this->render('login');
            } else
            {
              
                //echo "in else";
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/affiliate/');
                $this->renderScript('login.phtml');
            }
  	}

  	private function validatelogin() {
    	if (!$this->affinfo->login($this->_request->email, $this->_request->password)) {
      		$this->error['warning'] = $_SESSION['OBJ']['tr']->translate('error_login_affiliate_login');
    	}

    	if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
  	}

	public function accountAction() {
	$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_affiliate_account')));
		if (!$this->affinfo->isLogged()) {
	  		$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL);

			$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"login"),'',SERVER_SSL));
    	}
		if (isset($_SESSION['success'])) {
    		$this->data['success'] = $_SESSION['success'];

			unset($_SESSION['success']);
		} else {
			$this->data['success'] = '';
		}

    	$this->data['edit'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"edit"),'',SERVER_SSL);
        $this->data['password'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"password"),'',SERVER_SSL);
        $this->data['payment'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"payment"),'',SERVER_SSL);
        $this->data['tracking'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"tracking"),'',SERVER_SSL);
    	$this->data['transaction'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"transaction"),'',SERVER_SSL);

		$this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'affiliate/account.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'affiliate/');
                    $this->renderScript('account.phtml');
            }else  
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/affiliate/account.phtml'))
            {
                $this->render('account');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/affiliate/');
                $this->renderScript('account.phtml');
            }
  	}

	public function forgottenAction() {
	$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('text_success_affiliate_forgotten')));
		if ($this->affinfo->isLogged()) {
			$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL));
		}

		//$this->document->setTitle($_SESSION['OBJ']['tr']->translate('heading_title'));


		if (($_SERVER['REQUEST_METHOD'] == 'POST') && $this->validateforgotten()) {
			$password = substr(md5(rand()), 0, 7);
			$this->affiliate->editPassword($this->_request->email, $password);

			/*start mail*/
			$mailObj=new Model_Mail();
			$email=$mailObj->getEmailContent(array('lang'=>$_SESSION['Lang']['language_id'],'id'=>'6','replace'=>array('%password%'=>$password)));
                        
			$array_mail=array('to'=>array('name'=>trim($this->_request->email),'email'=>trim($this->_request->email)),'html'=>array('content'=>$email['content']),'subject'=>$email['subject']);
			$mailObj->sendMail($array_mail);
			/*end mail*/

			//$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
			$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_success_affiliate_forgotten');

			$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"login"),'',SERVER_SSL));
		}

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_forgotten_affiliate_forgotten'),
		'href'      =>Model_Url::getLink(array("controller"=>"affiliate","action"=>"forgotten"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->data['action'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"forgotten"),'',SERVER_SSL);

		$this->data['back'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"login"),'',SERVER_SSL);

		$this->view->data=$this->data;
                    if (file_exists(PATH_TO_FILES.'affiliate/forgotten.phtml'))
                    {
                            $this->view->addScriptPath(PATH_TO_FILES.'affiliate/');
                            $this->renderScript('forgotten.phtml');
                    }else 
                    if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/affiliate/forgotten.phtml'))
                    {
                        $this->render('forgotten');
                    } else
                    {
                        $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/affiliate/');
                        $this->renderScript('forgotten.phtml');
                    }
		}

	private function validateforgotten() {
		if (!isset($this->_request->email)) {
			$this->error['warning'] = $_SESSION['OBJ']['tr']->translate('error_email_affiliate_forgotten');
		} elseif (!$this->affiliate->getTotalAffiliatesByEmail($this->_request->email)) {
			$this->error['warning'] = $_SESSION['OBJ']['tr']->translate('error_email_affiliate_forgotten');
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function editAction() {
	$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_affiliate_edit')));
		if (!$this->affinfo->isLogged()) {

			$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"edit"),'',SERVER_SSL);
			$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"login"),'',SERVER_SSL));
		}

		//$this->document->setTitle($_SESSION['OBJ']['tr']->translate('heading_title'));

		if (($_SERVER['REQUEST_METHOD'] == 'POST') && $this->validateedit()) {
		//exit("here in");
			$this->affiliate->editAffiliate($this->_getAllParams());

			$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_success_affiliate_edit');

			$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL));
		}


      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_edit_affiliate_edit'),
			'href'      =>Model_Url::getLink(array("controller"=>"affiliate","action"=>"edit"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

		/*$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_affiliate_edit');

		$this->data['text_select'] = $_SESSION['OBJ']['tr']->translate('text_select');
		$this->data['text_your_details'] = $_SESSION['OBJ']['tr']->translate('text_your_details');
    	$this->data['text_your_address'] = $_SESSION['OBJ']['tr']->translate('text_your_address');

		$this->data['entry_firstname'] = $_SESSION['OBJ']['tr']->translate('entry_firstname');
		$this->data['entry_lastname'] = $_SESSION['OBJ']['tr']->translate('entry_lastname');
		$this->data['entry_email'] = $_SESSION['OBJ']['tr']->translate('entry_email');
		$this->data['entry_telephone'] = $_SESSION['OBJ']['tr']->translate('entry_telephone');
		$this->data['entry_fax'] = $_SESSION['OBJ']['tr']->translate('entry_fax');
    	$this->data['entry_company'] = $_SESSION['OBJ']['tr']->translate('entry_company');
		$this->data['entry_website'] = $_SESSION['OBJ']['tr']->translate('entry_website');
    	$this->data['entry_address_1'] = $_SESSION['OBJ']['tr']->translate('entry_address_1');
    	$this->data['entry_address_2'] = $_SESSION['OBJ']['tr']->translate('entry_address_2');
    	$this->data['entry_postcode'] = $_SESSION['OBJ']['tr']->translate('entry_postcode');
    	$this->data['entry_city'] = $_SESSION['OBJ']['tr']->translate('entry_city');
    	$this->data['entry_country'] = $_SESSION['OBJ']['tr']->translate('entry_country');
    	$this->data['entry_zone'] = $_SESSION['OBJ']['tr']->translate('entry_zone');

		$this->data['button_continue'] = $_SESSION['OBJ']['tr']->translate('button_continue');
		$this->data['button_back'] = $_SESSION['OBJ']['tr']->translate('button_back');*/

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

		$this->data['action'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"edit"),'',SERVER_SSL);

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			$affiliate_info = $this->affiliate->getAffiliate($this->affinfo->getId());
		}
		/*echo "<pre>";
		print_r($affiliate_info);
		echo "</pre>";*/
		if (isset($this->_request->firstname)) {
			$this->data['firstname'] = $this->_request->firstname;
		} elseif (isset($affiliate_info)) {
			$this->data['firstname'] = $affiliate_info['firstname'];
		} else {
			$this->data['firstname'] = '';
		}

		if (isset($this->_request->lastname)) {
			$this->data['lastname'] = $this->_request->lastname;
		} elseif (isset($affiliate_info)) {
			$this->data['lastname'] = $affiliate_info['lastname'];
		} else {
			$this->data['lastname'] = '';
		}

		if (isset($this->_request->email)) {
			$this->data['email'] = $this->_request->email;
		} elseif (isset($affiliate_info)) {
			$this->data['email'] = $affiliate_info['email'];
		} else {
			$this->data['email'] = '';
		}

		if (isset($this->_request->telephone)) {
			$this->data['telephone'] = $this->_request->telephone;
		} elseif (isset($affiliate_info)) {
			$this->data['telephone'] = $affiliate_info['telephone'];
		} else {
			$this->data['telephone'] = '';
		}

		if (isset($this->_request->fax)) {
			$this->data['fax'] = $this->_request->fax;
		} elseif (isset($affiliate_info)) {
			$this->data['fax'] = $affiliate_info['fax'];
		} else {
			$this->data['fax'] = '';
		}

		if (isset($this->_request->company)) {
    		$this->data['company'] = $this->_request->company;
		} elseif (isset($affiliate_info)) {
			$this->data['company'] = $affiliate_info['company'];
		} else {
			$this->data['company'] = '';
		}

		if (isset($this->_request->website)) {
    		$this->data['website'] = $this->_request->website;
		} elseif (isset($affiliate_info)) {
			$this->data['website'] = $affiliate_info['website'];
		} else {
			$this->data['website'] = '';
		}

		if (isset($this->_request->address_1)) {
    		$this->data['address_1'] = $this->_request->address_1;
		} elseif (isset($affiliate_info)) {
			$this->data['address_1'] = $affiliate_info['address_1'];
		} else {
			$this->data['address_1'] = '';
		}

		if (isset($this->_request->address_2)) {
    		$this->data['address_2'] = $this->_request->address_2;
		} elseif (isset($affiliate_info)) {
			$this->data['address_2'] = $affiliate_info['address_2'];
		} else {
			$this->data['address_2'] = '';
		}

		if (isset($this->_request->postcode)) {
    		$this->data['postcode'] = $this->_request->postcode;
		} elseif (isset($affiliate_info)) {
			$this->data['postcode'] = $affiliate_info['postcode'];
		} else {
			$this->data['postcode'] = '';
		}

		if (isset($this->_request->city)) {
    		$this->data['city'] = $this->_request->city;
		} elseif (isset($affiliate_info)) {
			$this->data['city'] = $affiliate_info['city'];
		} else {
			$this->data['city'] = '';
		}

    	if (isset($this->_request->country_id)) {
      		$this->data['country_id'] = $this->_request->country_id;
		} elseif (isset($affiliate_info)) {
			$this->data['country_id'] = $affiliate_info['country_id'];
		} else {
      		$this->data['country_id'] = STORE_COUNTRY;
    	}

    	if (isset($this->_request->zone_id)) {
      		$this->data['zone_id'] = $this->_request->zone_id;
		} elseif (isset($affiliate_info)) {
			$this->data['zone_id'] = $affiliate_info['zone_id'];
		} else {
      		$this->data['zone_id'] = '';
    	}

		$ctrObj=new Model_LocalisationCountry();
    	$this->data['countries'] = $ctrObj->getCountries();

		$this->data['back'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL);

		$this->view->data=$this->data;
                if (file_exists(PATH_TO_FILES.'affiliate/edit.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'affiliate/');
                    $this->renderScript('edit.phtml');
            }else 
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/affiliate/edit.phtml'))
            {
                $this->render('edit');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/affiliate/');
                $this->renderScript('edit.phtml');
            }
	}

	private function validateedit() {
		if ((strlen(utf8_decode($this->_request->firstname)) < 1) || (strlen(utf8_decode($this->_request->firstname)) > 32)) {
			$this->error['firstname'] = $_SESSION['OBJ']['tr']->translate('error_firstname_affiliate_edit');
		}

		if ((strlen(utf8_decode($this->_request->lastname)) < 1) || (strlen(utf8_decode($this->_request->lastname)) > 32)) {
			$this->error['lastname'] = $_SESSION['OBJ']['tr']->translate('error_lastname_affiliate_edit');
		}

		if ((strlen(utf8_decode($this->_request->email)) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->_request->email)) {
			$this->error['email'] = $_SESSION['OBJ']['tr']->translate('error_email_affiliate_edit');
		}

		if (($this->affinfo->getEmail() != $this->_request->email) && $this->affiliate->getTotalAffiliatesByEmail($this->_request->email)) {
			$this->error['warning'] = $_SESSION['OBJ']['tr']->translate('error_exists_affiliate_edit');
		}

		if ((strlen(utf8_decode($this->_request->telephone)) < 3) || (strlen(utf8_decode($this->_request->telephone)) > 32)) {
			$this->error['telephone'] = $_SESSION['OBJ']['tr']->translate('error_telephone_affiliate_edit');
		}
                
                if ((strlen(utf8_decode($this->_request->address_1)) < 3) || (strlen(utf8_decode($this->_request->address_1)) > 128)) {
                        $this->error['address_1'] = $_SESSION['OBJ']['tr']->translate('error_address_1_affiliate_edit');
                }

                if ((strlen(utf8_decode($this->_request->city)) < 2) || (strlen(utf8_decode($this->_request->city)) > 128)) {
                        $this->error['city'] = $_SESSION['OBJ']['tr']->translate('error_city_affiliate_edit');
                }

		$ctrObj=new Model_LocalisationCountry();
		$country_info = $ctrObj->getCountry($this->_request->country_id);

		if ($country_info && $country_info['postcode_required'] && (strlen(utf8_decode($this->_request->postcode)) < 2) || (strlen(utf8_decode($this->_request->postcode)) > 10)) {
			$this->error['postcode'] = $_SESSION['OBJ']['tr']->translate('error_postcode_affiliate_edit');
		}

    	if ($this->_request->country_id == '') {
      		$this->error['country'] = $_SESSION['OBJ']['tr']->translate('error_country_affiliate_edit');
    	}

    	if ($this->_request->zone_id == '') {
      		$this->error['zone'] = $_SESSION['OBJ']['tr']->translate('error_zone_affiliate_edit');
    	}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}

	public function passwordAction() {
		$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_affiliate_password')));
    	if (!$this->affinfo->isLogged()) {

			$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"password"),'',SERVER_SSL);

			$this->_redirect( Model_Url::getLink(array("controller"=>"affiliate","action"=>"login"),'',SERVER_SSL));
    	}

		//$this->document->setTitle($_SESSION['OBJ']['tr']->translate('heading_title'));

    	if (($_SERVER['REQUEST_METHOD'] == 'POST') && $this->validatepassword()) {
			$this->affiliate->editPassword($this->affinfo->getEmail(), $this->_request->password);

      		$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_success_affiliate_password');

	  	$this->_redirect( Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL));
		}

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_affiliate_password'),
		'href'      =>  Model_Url::getLink(array("controller"=>"affiliate","action"=>"password"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

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

    	$this->data['action'] =  Model_Url::getLink(array("controller"=>"affiliate","action"=>"password"),'',SERVER_SSL);

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

    	$this->data['back'] =  Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL);

		 $this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'affiliate/password.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'affiliate/');
                    $this->renderScript('password.phtml');
            }else 
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/affiliate/password.phtml'))
            {
                $this->render('password');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/affiliate/');
                $this->renderScript('password.phtml');
            }
  	}

  	private function validatepassword() {
    	if ((strlen(utf8_decode($this->_request->password)) < 4) || (strlen(utf8_decode($this->_request->password)) > 20)) {
      		$this->error['password'] = $_SESSION['OBJ']['tr']->translate('error_password_affiliate_password');
    	}

    	if ($this->_request->confirm != $this->_request->password) {
      		$this->error['confirm'] = $_SESSION['OBJ']['tr']->translate('error_confirm_affiliate_password');
    	}

		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}

	public function paymentAction() {
		$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_affiliate_payment')));
		if (!$this->affinfo->isLogged()) {
			$_SESSION['redirect'] =  Model_Url::getLink(array("controller"=>"affiliate","action"=>"payment"),'',SERVER_SSL);
			$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"login"),'',SERVER_SSL));
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->affiliate->editPayment($this->_getAllParams());

			$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_success_affiliate_payment');
			$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL));
		}


      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_payment_affiliate_payment'),
			'href'      => Model_Url::getLink(array("controller"=>"affiliate","action"=>"payment"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);


		$this->data['action'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"payment"),'',SERVER_SSL);

		if ($_SERVER['REQUEST_METHOD'] != 'POST') {
			$affiliate_info = $this->affiliate->getAffiliate($this->affinfo->getId());
		}

		if (isset($this->_request->tax)) {
    		$this->data['tax'] = $this->_request->tax;
		} else {
			$this->data['tax'] = '';
		}

		if (isset($this->_request->payment)) {
    		$this->data['payment'] = $this->_request->payment;
		} else {
			$this->data['payment'] = 'cheque';
		}

		if (isset($this->_request->cheque)) {
    		$this->data['cheque'] = $this->_request->cheque;
		} else {
			$this->data['cheque'] = '';
		}

		if (isset($this->_request->paypal)) {
    		$this->data['paypal'] = $this->_request->paypal;
		} else {
			$this->data['paypal'] = '';
		}

		if (isset($this->_request->bank_name)) {
    		$this->data['bank_name'] = $this->_request->bank_name;
		} else {
			$this->data['bank_name'] = '';
		}

		if (isset($this->_request->bank_branch_number)) {
    		$this->data['bank_branch_number'] = $this->_request->bank_branch_number;
		} else {
			$this->data['bank_branch_number'] = '';
		}

		if (isset($this->_request->bank_swift_code)) {
    		$this->data['bank_swift_code'] = $this->_request->bank_swift_code;
		} else {
			$this->data['bank_swift_code'] = '';
		}

		if (isset($this->_request->bank_account_name)) {
    		$this->data['bank_account_name'] = $this->_request->bank_account_name;
		} else {
			$this->data['bank_account_name'] = '';
		}

		if (isset($this->_request->bank_account_number)) {
    		$this->data['bank_account_number'] = $this->_request->bank_account_number;
		} else {
			$this->data['bank_account_number'] = '';
		}

		$this->data['back'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL);

            $this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'affiliate/payment.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'affiliate/');
                    $this->renderScript('payment.phtml');
            }else 
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/affiliate/payment.phtml'))
            {
                $this->render('payment');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/affiliate/');
                $this->renderScript('payment.phtml');
            }
	}

	public function trackingAction() {
		$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_affiliate_tracking')));
		if (!$this->affinfo->isLogged()) {
	  		$_SESSION['redirect'] = Model_Url::getLink(array("controller"=>"affiliate","action"=>"tracking"),'',SERVER_SSL);
			$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"login"),'',SERVER_SSL));
    	}

		$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('heading_title_affiliate_tracking'),
			'href'      =>Model_Url::getLink(array("controller"=>"affiliate","action"=>"tracking"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

  
    	$this->data['code'] = $this->affinfo->getCode();

		//$this->data['continue'] =Model_Url::getLink(array("controller"=>"affiliate","action"=>"account"),'',SERVER_SSL);
		$this->view->data=$this->data;
		
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/affiliate/tracking.phtml'))
            {
                $this->render('tracking');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/affiliate/');
                $this->renderScript('tracking.phtml');
            }
 	}

	public function transactionAction()
	{
		$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_affiliate_transaction')));
		if (!$this->affinfo->isLogged()) {
		$_SESSION['redirect'] =Model_Url::getLink(array("controller"=>"affiliate","action"=>"transaction"),'',SERVER_SSL);
		$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"login"),'',SERVER_SSL));
    	}

       	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_transaction_affiliate_transaction'),
			'href'      => Model_Url::getLink(array("controller"=>"affiliate","action"=>"transaction"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

		if (isset($this->_request->page)) {
			$page = $this->_request->page;
		} else {
			$page = 1;
		}

		$this->data['transactions'] = array();

		$data = array(
			'sort'  => 't.date_added',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);

		$afftraObj=new Model_AffiliateTransaction();
		$transaction_total = $afftraObj->getTotalTransactions($data);

		$results = $afftraObj->getTransactions($data);

    	foreach ($results as $result) {
			$this->data['transactions'][] = array(
				'amount'      => $this->currObj->format($result['amount'], DEFAULT_CURRENCY),
				'description' => $result['description'],
				'date_added'  => date($_SESSION['OBJ']['tr']->translate('date_format_short'), strtotime($result['date_added']))
			);
		}

		$pagination = new Model_FrontPagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = Model_Url::getLink(array("controller"=>"affiliate","action"=>"transaction"),'page/{page}',SERVER_SSL);//HTTP_SERVER.'affiliate/transaction';//$this->url->link('affiliate/transaction', 'page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['balance'] = $this->currObj->format($afftraObj->getBalance());


		$this->view->data=$this->data;
            if (file_exists(PATH_TO_FILES.'affiliate/transaction.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'affiliate/');
                    $this->renderScript('transaction.phtml');
            }else 
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/affiliate/transaction.phtml'))
            {
                $this->render('transaction');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/affiliate/');
                $this->renderScript('transaction.phtml');
            }
	}

		public function logoutAction() {
			$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_affiliate_logout')));
    	if ($this->affinfo->isLogged()) {
      		$this->affinfo->logout();
    		$this->_redirect(Model_Url::getLink(array("controller"=>"affiliate","action"=>"logout"),'',SERVER_SSL));
		}

    
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $_SESSION['OBJ']['tr']->translate('text_logout_affiliate_logout'),
			'href'      => Model_Url::getLink(array("controller"=>"affiliate","action"=>"logout"),'',SERVER_SSL),
        	'separator' => $_SESSION['OBJ']['tr']->translate('text_separator')
      	);

    	$this->data['heading_title'] = $_SESSION['OBJ']['tr']->translate('heading_title_affiliate_logout');
    	$this->data['text_message_success'] = $_SESSION['OBJ']['tr']->translate('text_message_affiliate_logout');
	$this->view->data=$this->data;
            /*(if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/success.phtml'))
            {
               $this->renderScript('account/success.phtml');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                $this->renderScript('account/success.phtml');
            }*/
        
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

