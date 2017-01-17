<?php
/**
 * Handling cms pages in the application
 *
 * @category   Zend
 * @package    InformationController
 * @author     suresh babu k
 */
class InformationController extends My_Controller_Main {
	public $tr=null;
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

		$this->custObj=new Model_Customer();
		$this->view->logged = $this->custObj->isLogged();

		$this->data[breadcrumbs] = array();

   		$this->data[breadcrumbs][] = array(
       		'text'      => $this->tr->translate('text_home'),
			'href'      => HTTP_SERVER."index/index",
       		'separator' => false
   		);

		/*start modules*/
		$moduleObj=new Model_Module();
		$this->view->pos=$moduleObj->getModules(array('page'=>'11')); //refers to category page as per r_layout
		/*end modules*/

		$this->infoObj=new Model_Information();
	}

	public function informationAction()
	{
		if (isset($this->_request->information_id)) {
			$information_id = $this->_request->information_id;
		} else {
			$information_id = 0;
		}

		$information_info = $this->infoObj->getInformation($information_id);
		$this->getMetaTags(array("meta_title"=>$information_info['title'],"meta_keywords"=>$information_info['meta_keywords'],"meta_description"=>$information_info['meta_description']));
		if ($information_info) {
	  	$this->data['breadcrumbs'][] = array(
        	'text'      => $information_info['title'],
		'href'      => HTTP_SERVER.'information/information/information_id/'.$information_id,
       		'separator' => $this->tr->translate('text_separator'));

                $this->data['heading_title'] = $information_info['title'];
                $this->data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

                $this->view->data=$this->data;
                
                    if(file_exists(PATH_TO_FILES.'information/information.phtml'))
                    {
                            $this->view->addScriptPath(PATH_TO_FILES.'information/');
                            $this->renderScript('information.phtml');
                    } else 
                    if(file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/information/information.phtml'))
                    {
                        $this->render('information');
                    } else
                    {
                        $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/information/');
                        $this->renderScript('information.phtml');
                    }

		} else {
      		$this->data['breadcrumbs'][] = array(
        		'text'      => $this->tr->translate('text_error_information_information'),
				'href'      => HTTP_SERVER.'information/information/information_id/'.$information_id,//$this->url->link('information/information', 'information_id=' . $information_id),
        		'separator' => $this->tr->translate('text_separator') );

	
      		$this->data['heading_title'] = $this->tr->translate('text_error_information_information');

      		$this->data['text_error'] = $this->tr->translate('text_error_information_information');

		$this->view->data=$this->data;
	
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

	public function sitemapAction()
	{
		$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_information_sitemap')));
		$this->data['breadcrumbs'][] = array(
		'text'      => $this->tr->translate('heading_title_information_sitemap'),
		'href'      => HTTP_SERVER.'information/sitemap',
		//$this->url->link('information/information', 'information_id=' .  $information_id),
		'separator' => $this->tr->translate('text_separator'));

		$prodObj=new Model_Products();
		$catObj=new Model_Categories();
		$this->data['categories'] = array();
		$categories_1 = $catObj->getCategories(0);

		foreach ($categories_1 as $category_1) {
			$level_2_data = array();
			$categories_2 = $catObj->getCategories($category_1['categories_id']);
			foreach ($categories_2 as $category_2) {
				$level_3_data = array();
				$categories_3 = $catObj->getCategories($category_2['categories_id']);
				foreach ($categories_3 as $category_3) {
					$level_3_data[] = array(
						'name' => $category_3['categories_name'],
						'href' => HTTP_SERVER.'product/category/path/'.$category_1['categories_id'] . '_' . $category_2['categories_id'] . '_' . $category_3['categories_id']//$this->url->link('product/category', 'path=' . $category_1['category_id'] . '_' . $category_2['category_id'] . '_' . $category_3['category_id'])
					);
				}

				$level_2_data[] = array(
					'name'     => $category_2['categories_name'],
					'children' => $level_3_data,
					'href'     => HTTP_SERVER.'product/category/path/'.$category_1['categories_id'] . '_' . $category_2['categories_id']//$this->url->link('product/category', 'path=' . $category_1['categories_id'] . '_' . $category_2['categories_id'])
				);
			}

			$this->data['categories'][] = array(
				'name'     => $category_1['categories_name'],
				'children' => $level_2_data,
				'href'     => HTTP_SERVER.'product/category/path/'.$category_1['categories_id']//$this->url->link('product/category', 'path=' . $category_1['category_id'])
			);
		}
		$infoObj=new Model_Information();
		$this->data['informations'] = array();
                //echo "<pre>";
		foreach ($infoObj->getInformations() as $result) {
                //print_r($result);
                    $this->data['informations'][] = array(
        		'title' => $result['title'],
        		'href'  => HTTP_SERVER.'information/information/information_id/'. $result['page_id']//$this->url->link('information/information', 'information_id=' . $result['information_id'])
      		);
    	}
		$this->view->data=$this->data;
                if(file_exists(PATH_TO_FILES.'information/sitemap.phtml'))
                {
                        $this->view->addScriptPath(PATH_TO_FILES.'information/');
                        $this->renderScript('sitemap.phtml');
                } else 
                if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/information/sitemap.phtml'))
                {
                    $this->renderScript('information/sitemap.phtml');
                } else
                {
                    $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/information/');
                    $this->renderScript('sitemap.phtml');
                }    
	}

	public function contactAction()
	{
		$this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('heading_title_information_contact')));
		if (($_SERVER['REQUEST_METHOD'] == 'POST') && $this->validatecontact()) {

                    /*start mail*/
                    $mailObj=new Model_Mail();
                    $array_mail=array('to'=>array('name'=>STORE_NAME,'email'=>trim(STORE_OWNER_EMAIL_ADDRESS)),'html'=>array('content'=>strip_tags(html_entity_decode($this->_request->enquiry, ENT_QUOTES, 'UTF-8'))),'subject'=>"Contact Info",'from'=>array('name'=>$this->_request->name,'email'=>trim($this->_request->email)));
                    $mailObj->sendMail($array_mail);
                    /*end mail*/
	  		//$this->redirect($this->url->link('information/contact/success'));
			$this->_redirect('information/success');
		}

                $this->data['breadcrumbs'][] = array(
                        'text'      => $this->tr->translate('heading_title_information_contact'),
                                'href'      => HTTP_SERVER.'information/contact',//$this->url->link('information/contact'),
                        'separator' => $this->tr->translate('text_separator')
                );

		if (isset($this->error['name'])) {
    		$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = '';
		}

		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}

		if (isset($this->error['enquiry'])) {
			$this->data['error_enquiry'] = $this->error['enquiry'];
		} else {
			$this->data['error_enquiry'] = '';
		}

 		if (isset($this->error['captcha'])) {
			$this->data['error_captcha'] = $this->error['captcha'];
		} else {
			$this->data['error_captcha'] = '';
		}

                $this->data['action'] = HTTP_SERVER.'information/contact';//$this->url->link('information/contact');
                $this->data['store'] = constant('STORE_NAME');
                $this->data['address'] = nl2br(constant('STORE_NAME_ADDRESS'));
                $this->data['telephone'] = constant('STORE_FAX');
                $this->data['fax'] = constant('STORE_TELEPHONE');

		if (isset($this->_request->name)) {
			$this->data['name'] = $this->_request->name;
		} else {
			$this->data['name'] = '';
		}

		if (isset($this->_request->email)) {
			$this->data['email'] = $this->_request->email;
		} else {
			$this->data['email'] = '';
		}

		if (isset($this->_request->enquiry)) {
			$this->data['enquiry'] = $this->_request->enquiry;
		} else {
			$this->data['enquiry'] = '';
		}

		if (isset($this->_request->captcha)) {
			$this->data['captcha'] = $this->_request->captcha;
		} else {
			$this->data['captcha'] = '';
		}
 

		$this->view->data=$this->data;
                if(file_exists(PATH_TO_FILES.'information/contact.phtml'))
                {
                        $this->view->addScriptPath(PATH_TO_FILES.'information/');
                        $this->renderScript('contact.phtml');
                } else 
                if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/information/contact.phtml'))
                {
                    $this->render('contact');
                } else
                {
                    $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/information/');
                    $this->renderScript('contact.phtml');
                }
      }

    	private function validatecontact() {
    	if ((strlen(utf8_decode($this->_request->name)) < 3) || (strlen(utf8_decode($this->_request->name)) > 32)) {
      		$this->error['name'] = $this->tr->translate('error_name_information_contact');
    	}

    	if (!preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->_request->email)) {
      		$this->error['email'] = $this->tr->translate('error_email_information_contact');
    	}

    	if ((strlen(utf8_decode($this->_request->enquiry)) < 10) || (strlen(utf8_decode($this->_request->enquiry)) > 3000)) {
      		$this->error['enquiry'] = $this->tr->translate('error_enquiry_information_contact');
    	}

    	if (!isset($_SESSION['captcha']) || ($_SESSION['captcha'] != $this->_request->captcha)) {
      		$this->error['captcha'] = $this->tr->translate('error_captcha_information_contact');
    	}

		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}

	public function successAction() 
       {
            $this->getMetaTags(array("meta_title"=>$_SESSION['OBJ']['tr']->translate('text_success_account_success')));
            $this->data['breadcrumbs'][] = array(
        	'text'      => $this->tr->translate('heading_title_information_contact'),
			'href'      => HTTP_SERVER.'information/contact',//$this->url->link('information/contact'),
        	'separator' => $this->tr->translate('text_separator')
      	);

    	$this->data['heading_title'] = $this->tr->translate('heading_title_information_contact');

    	$this->data['text_message_success'] = $this->tr->translate('text_message_information_contact');
		$this->view->data=$this->data;
                //$this->renderScript('account/success.phtml');
                if(file_exists(PATH_TO_FILES.'account/success.phtml'))
                {
                        $this->view->addScriptPath(PATH_TO_FILES.'account/');
                        $this->renderScript('success.phtml');
                } else 
                if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/account/success.phtml'))
                {
                    $this->renderScript('account/success.phtml');
                } else
                {
                    $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/account/');
                    $this->renderScript('success.phtml');
                }
	}
}

