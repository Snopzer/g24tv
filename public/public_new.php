<?php
// Define path to application directory
 
require_once 'site_config.php';

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));


// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
//exit;
// Ensure library/ is on include_path
/*set_include_path(implode(PATH_SEPARATOR, array(
    realpath('library'),
//realpath('D:\xampp\htdocs\mve\library'),
    get_include_path(),
)));*/
//echo APPLICATION_PATH;


// Create application, bootstrap, and run
/** My_Application */
require_once 'My/Application.php';

// Create application, bootstrap, and run
define('TEMPLATE_LAYOUT_PATH','/admin');
$application = new My_Application(
    APPLICATION_ENV,
    array(
            'configFile' => APPLICATION_PATH . '/configs/application.ini'
    )
);
$arr_url=explode("/",$_SERVER[REQUEST_URI]);


if($arr_url[1]==@constant('ADMIN_FRIENDLY_URL'))
{
	//define('TEMPLATE_LAYOUT_PATH','/admin');
	define('TEMPLATE_VIEW_PATH','');
}


//$info = new Zend_Config_Ini(APPLICATION_PATH . '/configs/template.ini');
//print_r($info);
//echo $info->TEMPLATE_LAYOUT_PATH_ADDRESS_FRONT;
//exit;
if($arr_url[1]!=@constant('ADMIN_FRIENDLY_URL'))
{
		

	//define('TEMPLATE_LAYOUT_PATH',$info->TEMPLATE_LAYOUT_PATH_ADDRESS_FRONT);
	//define('TEMPLATE_VIEW_PATH',$info->TEMPLATE_VIEW_PATH_ADDRESS_FRONT);
	define('TEMPLATE_VIEW_PATH',TEMPLATE_VIEW_PATH_ADDRESS_FRONT);
}
//echo "value of ".TEMPLATE_VIEW_PATH;
//exit;
//echo "value of ".SITE_DEFAULT_TEMPLATE;
//exit;

//echo "value of ".APPLICATION_PATH;
$application->bootstrap()
            ->run();