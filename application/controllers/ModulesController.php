<?php
/**
 * Handling Modules in the application
 *
 * @category   Zend
 * @package    ModulesController
 * @author     suresh babu k
 */
//class ModulesController extends Zend_Controller_Action {
class ModulesController extends My_Controller_Main {
	public function init()
	{

	}

	/*public function columnAction()
	{
		$modObj=new Model_Module_Category();
		$this->view->info=$modObj->_array;
	}*/

	public function leftAction()
	{
 		$arrMod=$this->_getParam('mod');
		sort($arrMod,SORT_NUMERIC);
		if(count($arrMod)>0)
		{
			foreach ($arrMod as $k=>$v)
			{
				$exp=explode("_",$v);
				$mod=Model_Module_.ucfirst($exp[1]);
				$modObj=new $mod($exp);
				$this->view->info=$modObj->_array;
                                
                                if (file_exists(PATH_TO_FILES.'modules/'.$exp[1].'.phtml'))
                                {
                                        $this->view->addScriptPath(PATH_TO_FILES.'modules/');
                                        $this->renderScript($exp[1].'.phtml');
                                }else  
		               if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/modules/'.$exp[1].'.phtml'))
                                {
								//echo "inside";
								//exit;
                                     $this->render($exp[1]);
                                } else
                                {
									//echo "outside";
									//exit;
                                    $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/modules/');
                                    $this->renderScript($exp[1].'.phtml');
                                }
			}
		}
	}

	public function rightAction()
	{
		$arrMod=$this->_getParam('mod');
		sort($arrMod,SORT_NUMERIC);
		if(count($arrMod)>0)
		{
			foreach ($arrMod as $k=>$v)
			{
				$exp=explode("_",$v);
				$mod=Model_Module_.ucfirst($exp[1]);
				$modObj=new $mod($exp);
				$this->view->info=$modObj->_array;
                                
                                if (file_exists(PATH_TO_FILES.'modules/'.$exp[1].'.phtml'))
                                {
                                        $this->view->addScriptPath(PATH_TO_FILES.'modules/');
                                        $this->renderScript($exp[1].'.phtml');
                                }else  
				if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/modules/'.$exp[1].'.phtml'))
                                {
                                    $this->render($exp[1]);
                                } else
                                {
                                    $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/modules/');
                                    $this->renderScript($exp[1].'.phtml');
                                }
			}
		}
	}

	public function topAction()
	{
		$arrMod=$this->_getParam('mod');
		sort($arrMod,SORT_NUMERIC);
		if(count($arrMod)>0)
		{
			foreach($arrMod as $k=>$v)
			{
				$exp=explode("_",$v);
				$mod=Model_Module_.ucfirst($exp[1]);
				$modObj=new $mod($exp);
				$this->view->info=$modObj->_array;
                                
                                if (file_exists(PATH_TO_FILES.'modules/'.$exp[1].'.phtml'))
                                {
                                        $this->view->addScriptPath(PATH_TO_FILES.'modules/');
                                        $this->renderScript($exp[1].'.phtml');
                                }else  
				if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/modules/'.$exp[1].'.phtml'))
                                {
                                    $this->render($exp[1]);
                                } else
                                {
                                    $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/modules/');
                                    $this->renderScript($exp[1].'.phtml');
                                }
			}
		}
	}

	public function bottomAction()
	{
		$arrMod=$this->_getParam('mod');
		sort($arrMod,SORT_NUMERIC);
		if(count($arrMod)>0)
		{
			foreach($arrMod as $k=>$v)
			{
				$exp=explode("_",$v);
				$mod=Model_Module_.ucfirst($exp[1]);
				$modObj=new $mod($exp);
				$this->view->info=$modObj->_array;
                                if (file_exists(PATH_TO_FILES.'modules/'.$exp[1].'.phtml'))
                                {
                                        $this->view->addScriptPath(PATH_TO_FILES.'modules/');
                                        $this->renderScript($exp[1].'.phtml');
                                }else  
				if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/modules/'.$exp[1].'.phtml'))
                                {
                                    $this->render($exp[1]);
                                } else
                                {
                                    $this->view->addScriptPath(APPLICATION_PATH . '/views/scripts/templates/'.DEMO_TEMPALTE.'/modules/');
                                    $this->renderScript($exp[1].'.phtml');
                                }
			}
		}
	}
}