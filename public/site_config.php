<?php
//echo "value of ".APPLICATION_PATH;
//exit;
define('R_CART_VERSION', 'V.1.0');
define('localhost','g24tv.com');
//define('localhost',"sun-network/kusuma");
define('HTTP_SERVER','http://'.localhost.'/');
define('HTTPS_SERVER','https://'.localhost.'/');
define('GLOBAL_DOMAIN_KEY','');

define('HTTP_IMAGE','http://'.localhost.'/public/uploads/');
define('HTTPS_IMAGE','https://'.localhost.'/public/public/uploads/');

//define('DOCUMENT_ROOT',$_SERVER['DOCUMENT_ROOT']."/g24tv");
define('DOCUMENT_ROOT','/home/a9309798/public_html'); 
//define('PUBLIC_PATH','http://'.localhost.'/public/');
define('PUBLIC_PATH','http://'.localhost.'/public/');
define('PATH_TO_PUBLIC',DOCUMENT_ROOT.'/public/');


define('PATH_TO_ADMIN_CSS',PUBLIC_PATH.'admin/css/');
define('PATH_TO_ADMIN_JS',PUBLIC_PATH.'admin/js/');
define('PATH_TO_ADMIN_IMAGES',PUBLIC_PATH.'admin/images/');

//define('PATH_TO_UPLOADS',PUBLIC_PATH.'uploads/');//defined internally inside header function in front and admincontroller may 21 2012
define('DIR_DOWNLOAD', 'http://'.localhost.'/public/uploads/downloads/');
define('PATH_TO_UPLOADS_DIR',DOCUMENT_ROOT.'/public/uploads/');
define('PATH_TO_UPLOADS_CATEGORIES',DOCUMENT_ROOT.'/public/uploads/categories/');
define('PATH_TO_SHIPPING',DOCUMENT_ROOT.'/application/models/Shipping');
define('PATH_TO_PAYMENT',DOCUMENT_ROOT.'/application/models/Payment');
define('PATH_TO_TEMPLATES',DOCUMENT_ROOT.'/application/views/scripts/templates');
define('URL_TO_TEMPLATES','http://'.localhost.'/application/views/scripts/templates/');
define('URL_TO_TEMPLATES_HTTPS','https://'.localhost.'/application/views/scripts/templates/');
define('PATH_TO_ORDER_TOTAL',DOCUMENT_ROOT.'/application/models/OrderTotal');
define('PATH_TO_FCKEDITOR','http://'.localhost.'/tools/fckeditor/');
//define('ADMIN_URL_CONTROLLER',PUBLIC_PATH.'admin/');//defined in admincontroller setHttps

//start new
define('PATH_TO_SEARCHINDEX',PATH_TO_PUBLIC.'uploads/searchindex');
define('PATH_TO_SITECACHE',PATH_TO_PUBLIC.'uploads/site-cache/');
define('PATH_TO_SESSION',PATH_TO_PUBLIC."uploads/session/");
define('PATH_TO_LANGUAGECACHE',PATH_TO_PUBLIC.'uploads/language-cache/');
define('PATH_TO_FILES',PATH_TO_PUBLIC.'uploads/files/');
define('PATH_TO_LANGUAGE',PATH_TO_PUBLIC."uploads/language/");
//end new

define('ADMIN_ADD_MSG','new %row% added successfully!!');
define('ADMIN_EDIT_SAVE_MSG','selected %row% modified successfully!!');
define('ADMIN_EDIT_APPLY_MSG',' %row% modified successfully!!');
define('ADMIN_DEL_SINGLE_MSG','selected %row% deleted successfully!!');
define('ADMIN_DEL_MULTIPLE_MSG','selected %row% deleted successfully!!');
define('ADMIN_PUB_SINGLE_MSG','selected %row% published successfully!!');
define('ADMIN_PUB_MULTIPLE_MSG','selected %row%s published successfully!!');
define('ADMIN_UNPUB_SINGLE_MSG','selected %row% unpublished successfully!!');
define('ADMIN_UNPUB_MULTIPLE_MSG','selected %row% unpublished successfully!!');

define('STATUS_ENABLE_1','Enable');
define('STATUS_DISABLE_0','Disable');

//define('DEMO_TEMPALTE','demo');//in reality it will be demo
define('DEMO_TEMPALTE','original');//in reality it will be demo

/*start ups shipping */
define('SHIPPING_ORIGIN_ZIP','70820');
define('SHIPPING_ORIGIN_COUNTRY','223'); //US,USA
/*end ups shipping*/

include @constant('PATH_TO_FILES').'template.php';