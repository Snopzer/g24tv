<?php
/**
 * Handling Index in the application
 *
 * @category   Zend
 * @package    IndexController
 * @author     suresh babu k
 */
class IndexController extends My_Controller_Main {
	public function init()
	{
		Zend_Session::start();
		$this->getConstants();
                $this->setLangSession();
		//$this->view->vaction=$this->getRequest()->getActionName();
        }

    public function indexAction()
	{
		
	/*    try{

        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
        $dbAdapter = Zend_Db::factory($config->database);
        $dbAdapter->getConnection();
        $this->connection = $dbAdapter;
    } catch (Zend_Db_Adapter_Exception $e) {
        echo 'perhaps a failed login credential, or perhaps the RDBMS is not running'.$e->message;
    } catch (Zend_Exception $e) {
        echo 'perhaps factory() failed to load the specified Adapter class'.$e->message;
    }
exit;*/
//


//echo "value of ".$this->_helper->test->getName();



            $this->isAffiliateTrackingSet();
            
            $this->globalKeywords();
            $this->getHeader();
            $this->getFlashCart();
            $this->getMetaTags(array());
            /*start modules*/
            $moduleObj=new Model_Module();
            $this->view->pos=$moduleObj->getModules(array('page'=>'1')); //refers to category page as per r_layout
            /*end modules*/
            /*if(@constant('STORE_INTRODUCTION_STATUS')=='1' && $_SESSION['STORE_INTRODUCTION_STATUS']=="")
            {
                $this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
                $_SESSION['STORE_INTRODUCTION_STATUS']="1";
                echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd"><html><head><title>'.@constant('STORE_META_TITLE').'</title><meta name="Keywords" content="'.@constant('STORE_META_KEYWORDS').'"><meta name="Description" content="'.@constant('STORE_META_DESCRIPTION').'"></head><body>';
                ECHO stripslashes(@constant('STORE_INTRODUCTION_CONTENT'));
                echo '</body></html>';
                EXIT;
            }*/
            
            if (file_exists(PATH_TO_FILES.'index/index.phtml'))
            {
                    $this->view->addScriptPath(PATH_TO_FILES.'index/');
                    $this->renderScript('index.phtml');
            }else 
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/index/index.phtml'))
            {
                $this->render('index');
            } else
            {
                $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/index/');
                $this->renderScript('index.phtml');
            }
        }
}

