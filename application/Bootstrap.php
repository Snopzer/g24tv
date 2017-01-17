<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public function _initAutoloader() {
        $moduleLoader = new Zend_Application_Module_Autoloader(array(
                    'namespace' => '',
                    'basePath' => APPLICATION_PATH
                ));
        return $moduleLoader;
    }


    public function _initView()
    {
        $this->bootstrap('layout');

        $layout = $this->getResource('layout');

        $view = $layout->getView();
        $view->setHelperPath(APPLICATION_PATH . '/views/helpers/', '');
		$view->setScriptPath(APPLICATION_PATH . '/views/scripts/'.TEMPLATE_VIEW_PATH);
		//Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH .'/controllers/action/helper');



        $view->doctype('HTML4_STRICT');
        return $view;
    }

    public function _initRoutes()
     {
	 // get instance of front controller
        $frontController  = Zend_Controller_Front::getInstance();
        $reqHttp = new Zend_Controller_Request_Http();
        $url = $reqHttp->getRequestUri();
        $param  = explode('/',$url);
		/*echo "<pre>";
		 print_r(array_keys(get_defined_constants()));
		 exit;*/
		/*
		// Action Helpers
		Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH .'/controllers/helpers');
			
		$hooks = Zend_Controller_Action_HelperBroker::getStaticHelper('Hooks');
		Zend_Controller_Action_HelperBroker::addHelper($hooks);*/

        $controllers=array("","index","product","account","affiliate","checkout","information","ajax","error","modules","order-total","maintenance");
		//echo "<pre>";
		//print_r(get_defined_constants());
		//admin commented
		//echo "value of ".$param[2]."==".@constant('ADMIN_FRIENDLY_URL');
		//exit;
		//echo "value of ".$param[3];
		//exit;
		
        if(!in_array($param[1],$controllers))
		{
                if($param[2]!="" && $param[3]=='' && $param[1]!=@constant('ADMIN_FRIENDLY_URL')) //product details page should only have category/product should not contain extra keyword
                {
					
                    $route = new Zend_Controller_Router_Route(':category/:product/',
                    array('controller' =>'product',
                    'module' => 'default',
                    'action' => 'product-details',
                    'category' => "",
                              'product'=>""));
                }else if($param[1]=='brand')  //for manufacturer
                {
                   	$route = new Zend_Controller_Router_Route(':category/*',array('controller' =>'product',
                    'module' => 'default',
                    'action' => 'manufacturer',
                    'category' => ""));
                }else if($param[1]==@constant('ADMIN_FRIENDLY_URL'))  //for manufacturer
                {
					//echo $this->getRequest()->getActionName();
					//echo "<pre>";
					$action=explode("?",$param[2]);
					//print_r($action);
					//exit;
					//exit("in admin");
                   	$route = new Zend_Controller_Router_Route(':admin/*',array('controller' =>'admin',
                    'module' => 'default',
                    'action' => $action[0],
                    'admin' => ""));
                }
				else   //categories page
                {
					$route = new Zend_Controller_Router_Route(':category/*',array('controller' =>'product',
                    'module' => 'default',
                    'action' => 'category',
                    'category' => ""));
                }
			$frontController->getRouter()->addRoute('product',$route);

		
		}

		
    }
}

