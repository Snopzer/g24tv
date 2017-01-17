<?php

/**
 * Handling Errors in the application
 *
 * @category   Zend
 * @package    ErrorController
 * @author    Suresh babu k
 */
class ErrorController extends My_Controller_Main {

    public function errorAction() {
                if($this->_getParam('controller')!='admin')
		{
                    $this->getConstants();
                    $this->view->vaction=$this->getRequest()->getActionName();

                    $this->setLangSession();

                    $this->isAffiliateTrackingSet();
                    $this->globalKeywords();
                    //print_r($this->_getParam('controller'));
                    $this->getHeader();
                    $this->getFlashCart();
		}
		//$this->_helper->layout()->disableLayout();
		//$this->_helper->viewRenderer->setNoRender(true);

        $errors = $this->_getParam('error_handler');

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error 
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }

        $this->view->exception = $errors->exception;
        $this->view->request = $errors->request;
        /*if($_SERVER['SERVER_NAME']=='sun-network')
        {
            $this->render('error');
        }else
        {
            $this->render('error-live');
        }*/
	    if (file_exists(PATH_TO_FILES.'error/error.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'error/');
                    $this->renderScript('error.phtml');
            }else 
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/error/error.phtml'))
            {
                $this->render('error');
            } else
            {   
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/error/');
                $this->renderScript('error.phtml');
            }

    }

}

