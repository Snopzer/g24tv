<?php
/**
 * Handling cms pages in the application
 *
 * @category   Zend
 * @package    MaintenanceController
 * @author     suresh babu k
 */
class MaintenanceController extends My_Controller_Main {

	public function init()
	{
		Zend_Session::start();
		$this->getConstants();
	}

	public function indexAction()
	{
            if($_SESSION['admin_id']!='1' && @constant('SERVER_MAINTENANCE_MODE')=='false')
            {
                    header("location:".HTTP_SERVER);
                    exit;
            }	
           
            $this->_helper->layout()->disableLayout();
            if (file_exists(PATH_TO_FILES.'maintenance/index.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'maintenance/');
                    $this->renderScript('index.phtml');
            }else  
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/maintenance/index.phtml'))
            {
                //$this->render('category');
            } else
            {   
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/maintenance/');
                $this->renderScript('index.phtml');
            }
	}	
}

