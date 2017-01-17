<?php
/**
 * Handling Modules in the application
 *
 * @category   Zend
 * @package    AddonsController
 * @author     suresh babu k
 */
//class ModulesController extends Zend_Controller_Action {
class AddonsController extends My_Controller_Main {
	public function init()
	{

	}

	public function addonAction()
	{
				$str=$this->_getParam('mod');
				$params=$this->_getParam('params');
				$mod=Model_Addons_.ucfirst($str);
				$modObj=new $mod($params);
				$this->view->info=$modObj->_array;
                                
                                if (file_exists(PATH_TO_FILES.'addons/'.$str.'.phtml'))
                                {
                                    $this->view->addScriptPath(PATH_TO_FILES.'addons/');
                                    $this->renderScript($str.'.phtml');
                                }else  
                                if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/addons/'.$str.'.phtml'))
				{
					$this->render($str);
				} else
				{
					$this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/addons/');
					$this->renderScript($str.'.phtml');
				}
			
	}
}
