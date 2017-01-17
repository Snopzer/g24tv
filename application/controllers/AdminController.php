<?php
	session_start();
	/**
		* Handles all the admin module requests
		* @version 1.0
		* @category   Zend
		* @package    AdminController
	*/
	
	class AdminController extends Zend_Controller_Action { 
        private $_model;
		private $_form;
        private $_sform;
		public $disType;
		public $rConfig;
		public $field;
		public $_table;
		public $_id;
		public $_action;
        public $_date;
		
		
		public function init()
		{
			ob_start();
			$this->_helper->layout->setLayout('admin');
			$this->disType=$_REQUEST['disType'];
			if ($this->disType == "")
			{
				$this->disType = "desc";
				
			} elseif ($this->disType == "asc")
			{
				$this->disType = "desc";
				
			} elseif ($this->disType == "desc")
			{
				$this->disType = "asc";
			}
			
			
			define('PATH_TO_UPLOADS',PUBLIC_PATH.'uploads/'.GLOBAL_DOMAIN_KEY.'/');
			$this->rConfig=new Model_DbTable_rconfiguration();
			$this->rConfig->getConfiguration();
			
			$contextSwitch = $this->_helper->getHelper('contextSwitch');
			$contextSwitch->addActionContext('ajaxzone', 'json');
			$contextSwitch->addActionContext('ajaxprodrelated', 'json');
			$contextSwitch->initContext();
			
			//$this->_action=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
			$this->_action=$this->getRequest()->getActionName();
			$this->view->view_action=$this->_action;
			
			$this->setHttps();
			$this->view->searchResult=$this->getSearchPage();
			// $objController = Zend_Controller_Front::getInstance();
			//$arrOptions = $objController->getParam("bootstrap")->getOptions();
			$this->_date=date('Y-m-d H:i:s');
			$this->reqObj=new Model_Request();
			
		}
		
		
		public function gettingStartedAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$action=new Model_Adminaction();
		}	
        
        public function getSearchPage()
		{
			$searchPage_data=Model_Cache::getCache(array("id"=>"admin_searchpage_".$_SESSION['admin_id']));
			if(!$searchPage_data)
			{
				$this->db = Zend_Db_Table::getDefaultAdapter();
				$select = $this->db->fetchAll("select file_name from r_admin_permissions where admin_roles_id='".(int)$_SESSION[role_id]."'");
				$result="";
				foreach($select as $v)
				{
					$result=$result.$pre."{ label: '".$v[file_name]."', value: '".@constant('ADMIN_URL_CONTROLLER').$v[file_name]."' }";
					$pre=",";
				}
				Model_Cache::getCache(array("id"=>"admin_searchpage_".$_SESSION['admin_id'],"input"=>$result,"tags"=>array("admin","searchpage","general")));
				$searchPage_data=$result;
			}
			return $searchPage_data;
		}
		
        public function optionautocompleteAction() {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$json = array();
			if (isset($this->_request->filter_name)) {
				$this->db = Zend_Db_Table::getDefaultAdapter();
				//echo "select od.name,od.option_id from r_option o,r_option_description od where o.option_id=od.option_id and od.language_id='1' and o.type='select' and od.name like '%".$this->_request->filter_name."%' limit 0,5";
				//exit;
                $results=$this->db->fetchAll("select od.name,od.option_id from r_option o,r_option_description od where o.option_id=od.option_id and od.language_id='1' and o.type='select' and lower(od.name) like '%".strtolower($this->_request->filter_name)."%' limit 0,5");
                
                foreach ($results as $result) {
					$json[] = array(
					'product_id' => $result['option_id'],
					'name'       => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'),
					'model'      => $result['products_model'],
					'price'      => $result['products_price']
					);
				}
			}
			echo Model_Json::encode($json);
		}
        
        public function setHttps()
        {
			/*start http/https template urls*/
			if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
				$this->view->url_to_templates = URL_TO_TEMPLATES_HTTPS;
				$this->view->url_to_site = HTTPS_SERVER;
				$this->view->url_to_image = HTTPS_IMAGE;
				$this->view->url_to_commonfiles=$this->view->url_to_site."library/CommonFiles/";
				define('PATH_TO_UPLOADS',HTTPS_SERVER.'public/uploads/'.GLOBAL_DOMAIN_KEY.'/');
				define('ADMIN_URL_CONTROLLER',@constant('HTTP_SERVERS').@constant('ADMIN_FRIENDLY_URL').'/');
			} else
			{
				$this->view->url_to_templates = URL_TO_TEMPLATES;
				$this->view->url_to_site = HTTP_SERVER;
				$this->view->url_to_image = HTTP_IMAGE;
				$this->view->url_to_commonfiles=$this->view->url_to_site."library/CommonFiles/";
				define('PATH_TO_UPLOADS',HTTPS_SERVER.'public/uploads/'.GLOBAL_DOMAIN_KEY.'/');
				define('ADMIN_URL_CONTROLLER',@constant('HTTP_SERVER').@constant('ADMIN_FRIENDLY_URL').'/');
			}
			/*end http/https template urls*/
		}
		public function productReturnActionAction() {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$json = array();
			
			if ($_SERVER['REQUEST_METHOD'] == 'POST') {
				if (!$json)
				{
					$json['success'] = "updated successfully!!";
					$act_ext=new Model_Adminextaction();
					$act_ext->editReturnAction((int)$_REQUEST['return_id'], (int)$_REQUEST['return_action_id']);
				}
			}
			echo Model_Json::encode($json);
		}
		
		public function addrewardAction() {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$json = array();
			if (isset($this->_request->order_id)) {
				if ($this->_request->hid_cust_id!="") {
					$accRewdObj=new Model_AccountReward();
					$accRewdObj->addReward($this->_request->hid_cust_id, 'order id  is #' . $this->_request->order_id, $this->_request->reward, $this->_request->order_id);
					
					$json['success'] = "<a href='javascript:removeReward();'>Remove Reward Points</a> <b>Reward Points Added Successfully!!</b>";
				}
				$action=new Model_Adminaction();
				$cust=$action->getEditdetails('r_customers','customers_id',(int)$this->_request->hid_cust_id);
				/*start mail*/
				$fetch=$action->db->fetchRow('select sum(points) as amt from r_customer_reward where customer_id="'.(int)$this->_request->hid_cust_id.'"');
				$total=$fetch['amt'];
				
				$mailObj=new Model_Mail();
				$arrmc=$mailObj->getEmailContent(array('id'=>'9','lang'=>'1','replace'=>array('%reward%'=>$this->_request->reward,'%total_reward%'=>$total)));
				
				
				$array_mail=array('to'=>array('name'=>$cust[0]['customers_firstname']." ".$cust[0]['customers_lastname'],'email'=>trim($cust[0]['customers_email_address'])),'html'=>array('content'=>$arrmc['content']),'subject'=>$arrmc['subject']);
				/*echo "<pre>";
					print_r($array_mail);	
					print_r($cust);
					echo "</pre>";
				exit;*/
				$mailObj->sendMail($array_mail);
				/*end mail*/
			}
			echo Model_Json::encode($json);
		}
		
		public function removerewardAction() {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$json = array();
			if (isset($this->_request->order_id)) {
				$action=new Model_Adminaction();
				$action->RecordDelete('r_customer_reward','order_id',array($this->_request->order_id));
				$json['success'] = "<a href='javascript:addReward();'>Add Reward Points</a> <b>Reward Points Removed Successfully!!</b>";
			}
			echo Model_Json::encode($json);
		}
        
        public function skuavailabilityAction() 
        {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$json = array();
			$action=new Model_Adminaction();
			if($_REQUEST['pid']!="" && $_REQUEST['sku']!="")
			{
				$row=$action->db->fetchRow("select count(*) as count from r_products where sku='".$_REQUEST['sku']."' and products_id!='".(int)$_REQUEST['pid']."'");                   if($row['count']>0)
				{
					$json['success'] = "<font color='red'>SKU aready exists!!</font>";
				}else
				{
					$json['success'] = "<font color='green'>SKU not available!!</font>";
				}
			}
			else if($_REQUEST['sku']!="")
			{
				$row=$action->db->fetchRow("select count(*) as count from r_products where sku='".$_REQUEST['sku']."'");
				if($row['count']>0)
				{
					$json['success'] = "<font color='red'>SKU aready exists!!</font>";
				}else
				{
					$json['success'] = "<font color='green'>SKU not available!!</font>";
				}
			}
    		echo Model_Json::encode($json);
		}
        
        public function generateInvoiceAction() {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$json = array();
			if (isset($this->_request->order_id)) {
				$action=new Model_Adminaction();
				$row=$action->db->fetchRow("SELECT max( invoice_id ) +1 AS invoice_id FROM r_orders");
				$action->db->query("update r_orders set invoice_id='".(int)$row[invoice_id]."' where orders_id=".(int)$this->_request->order_id);
				$json['invoice_id']=@constant('INVOICE_PREFIX').$row[invoice_id];
				$json['success']="Invoice Id genereated successfully!!";
			}
			echo Model_Json::encode($json);
		}
		
		public function addCommissionAction() {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$json = array();
			$affTransObj=new Model_AffiliateTransaction();
			$affTransObj->addTransaction((int)$this->_request->hid_aff_id, 'order id #' . $this->_request->order_id, $this->_request->hid_aff_com, (int)$this->_request->order_id);
			$json['success'] = "<a href='javascript:removeCommission();'>Remove Commission</a> <b>Commission Added Successfully!!</b>";
			
			$action=new Adminaction();
			$aff=$action->getEditdetails('r_affiliate','affiliate_id',(int)$this->_request->hid_aff_id);
			
			$fetch=$action->db->fetchRow('select sum(amount) as amt from r_affiliate_transaction where affiliate_id="'.(int)$this->_request->hid_aff_id.'"');
			$currency=new Model_currencies();
			$total=$currency->format($fetch['amt']);
			
			/*start mail*/
			$mailObj=new Model_Mail();
			$arrmc=$mailObj->getEmailContent(array('id'=>'11','lang'=>'1','replace'=>array('%commission%'=>$this->_request->hid_aff_com,'%total_commission%'=>$total)));
			
			$array_mail=array('to'=>array('name'=>$aff[0]['firstname']." ".$aff[0]['lastname'],'email'=>trim($aff[0]['email'])),'html'=>array('content'=>$arrmc['content']),'subject'=>$arrmc['subject']);
			
			$mailObj->sendMail($array_mail);
			/*end mail*/
			
			echo Model_Json::encode($json);
		}
		
        public function clearCacheAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			Model_Cache::removeAllCache();
			$json = array();
			$json['success'] = "Cache Cleared Successfully!!";
			echo Model_Json::encode($json);
		}
        
        public function updateTemplateFile($template,$admin)
        {
			$data="<?php 
			define('TEMPLATE_LAYOUT_PATH_ADDRESS_FRONT','/templates/".$template."');
			define('TEMPLATE_VIEW_PATH_ADDRESS_FRONT','templates/".$template."');
			define('ADMIN_FRIENDLY_URL','".$admin."');";
			
			$my_file=PATH_TO_FILES."template.php";
			$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
			fwrite($handle, $data);
		}
        
        public function setThemeAction()
		{
            if($_REQUEST['id']!="")
            {
                $this->_helper->layout()->disableLayout();
				$this->_helper->viewRenderer->setNoRender(true);
				
                $action=new Model_Adminaction();
                $action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['id']),'configuration_key=\'SITE_DEFAULT_TEMPLATE\'');
                $this->updateTemplateFile($_REQUEST['id'],@constant('ADMIN_FRIENDLY_URL'));
                /*$data="<?php 
					define('TEMPLATE_LAYOUT_PATH_ADDRESS_FRONT','/templates/".$_REQUEST['id']."');
					define('TEMPLATE_VIEW_PATH_ADDRESS_FRONT','templates/".$_REQUEST['id']."');";
					
					$my_file=PATH_TO_FILES."template.php";
					$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
				fwrite($handle, $data);*/
                Model_Cache::removeAllCache();
				$json = array();
                $action->db->delete("r_template","template_id!=0");
                @unlink(PATH_TO_LANGUAGE."language.php");
                @unlink(PATH_TO_FILES."load.css");
                @unlink(PATH_TO_FILES."footer.phtml");
                @unlink(PATH_TO_UPLOADS_DIR."language.csv");
                
				//start remove lang cache
                $dir=scandir(@constant('PATH_TO_LANGUAGECACHE'));
                unset($dir[0]);
                unset($dir[1]);
                foreach($dir as $k=>$v)
                {
                    @unlink(@constant('PATH_TO_LANGUAGECACHE').$v);
				}
                //end remove lang cache
				
                $json['success'] = $_REQUEST['id']." is selected as default theme!!";
				echo Model_Json::encode($json);
			}
		}
        
        public function updateProductSearchAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$this->createIndex();
			$json = array();
			$json['success'] = "Updated Product Search Successfully!!";
			echo Model_Json::encode($json);
		}
		
        public function clearSessionAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$path=@constant('PATH_TO_SESSION');//PATH_TO_PUBLIC."../session/";
			if($path!="")
			{
				shell_exec("chmod -R 777 ".$path);
			}
			//echo $path;
			$dir=scandir($path);
			unset($dir[0]);
			unset($dir[1]);
			/*echo "<pre>";
                print_r($dir);
			echo "</pre>";*/
			$past=strtotime(date('F j, Y, g:i a'))-86440;//one day before
			//echo strtotime(date('F j, Y, g:i a'))."</br>".$past."<br/>";
			foreach($dir as $k=>$v)
			{
				/*$time=filemtime($path.$v); //clear ystdays session
					if($time<$past) 
					{
					@unlink($path.$v);
				}*/
				
				@unlink($path.$v);//clear all sessions
			}
			
			//$command = "del /Q ".PATH_TO_PUBLIC."..\\session\\*";
			//system($command);
			$json = array();
			$json['success'] = "Session Cleared Successfully!!";
			echo Model_Json::encode($json);
		}
		
		public function removeCommissionAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$json = array();
			$action=new Model_Adminaction();
			$action->RecordDelete('r_affiliate_transaction','order_id',array($this->_request->order_id));
			$json['success'] = "<a href='javascript:addCommission();'>Apply Commission</a> <b>Commission Removed Successfully!!</b>";
			
			echo Model_Json::encode($json);
		}
		
		public function productautocompleteAction() {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$json = array();
			if (isset($this->_request->filter_name)) {
				$data = array(
				'filter_name' => $this->_request->filter_name,
				'start'       => 0,
				'limit'       => 20
				);
				$_SESSION['Lang']['language_id']='1';//for default langugae
				$prodObj=new Model_Products();
				$results = $prodObj->getProducts($data);
				foreach ($results as $result) {
					$json[] = array(
					'product_id' => $result['products_id'],
					'name'       => html_entity_decode($result['products_name_full'], ENT_QUOTES, 'UTF-8'),
					'model'      => $result['products_model'],
					'price'      => $result['products_price']
					);
				}
			}
			echo Model_Json::encode($json);
		}
        
		public function categoryautocompleteAction() {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$json = array();
			$act=new Model_Adminaction();
			$results=$act->db->fetchAssoc("select cd.categories_name,cd.categories_id from r_categories_description cd,r_categories c where c.categories_id=cd.categories_id and cd.categories_name like '%".$this->_request->filter_name."%' and c.del='0' and cd.language_id='1'");
			foreach ($results as $result) {
				$json[] = array(
				'product_id' => $result['categories_id'],
				'name'       => html_entity_decode($result['categories_name'], ENT_QUOTES, 'UTF-8')
				);
			}
			echo Model_Json::encode($json);
		}
		
		
		
		public function modulesAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$act_ext=new Model_Adminextaction();
			
			switch($_REQUEST['type'])
			{
				case 'Edit':
				if($_REQUEST[play]=='apply'  || $_REQUEST[play]=='save')
				{
					/*echo "<pre>";
						print_r($this->_getAllParams());
						echo "</pre>";
					exit;*/
					Model_Cache::removeAllCache();
					$arrPost=$this->_getAllParams();
					unset($arrPost[controller]);
					unset($arrPost[admin]);
					unset($arrPost[page]);
					unset($arrPost[action]);
					unset($arrPost[rid]);
					unset($arrPost[type]);
					unset($arrPost[play]);
					unset($arrPost[module]);
					$setObj=new Model_AdminModuleSetting();
					$setObj->editSetting(preg_replace("([ ,;']+)", "", strtolower($_REQUEST['rid'])), $arrPost);
					if($_REQUEST['play']=='apply')
					$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'modules?rid='.$_REQUEST['rid'].'&type=Edit&msg='.base64_encode('update successfull!!'));
					else
					$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'modules?msg='.base64_encode('update successfull!!'));
					
				}
				//exit;
				$class='Model_Module_'.$_REQUEST['rid'];
				
				$res=$class->updateModule();
				$this->view->data=$res;
				$this->render('module-'.$_REQUEST['rid']);
				
				break;
				case 'Install':
				/*start install*/
				/*echo "insert into r_extension(type,code) values('module','".strtolower($_REQUEST['rid'])."')";
				exit;*/
				Model_Cache::removeAllCache();
				$act_ext->db->query("insert into r_extension(type,code) values('module','".preg_replace("([ ,;']+)", "", strtolower($_REQUEST['rid']))."')");
				$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'modules?msg='.base64_encode($_REQUEST['rid'].' installed successfull!!'));
				
				/*end install*/
				break;
				case 'UnInstall':
				Model_Cache::removeAllCache();
				/*start uninstall query*/
				//$this->db->query("DELETE FROM r_extension WHERE `type` = '" . stripslashes($type) . "' AND `code` = '" . stripslashes($code) . "'");
				/*echo "DELETE FROM r_extension WHERE `type` ='module' and `code` = '" . stripslashes(strtolower($_REQUEST['rid']));
					echo "DELETE FROM r_setting WHERE `group` = '" . stripslashes($_REQUEST['rid']);
				exit;*/
				$act_ext->db->query("DELETE FROM r_extension WHERE `code` = '" . preg_replace("([ ,;']+)", "a", stripslashes(strtolower($_REQUEST['rid'])))."' and `type` ='module'");
				$act_ext->db->query("DELETE FROM r_setting WHERE `group` = '" . preg_replace("([ ,;']+)", "a", stripslashes(strtolower($_REQUEST['rid'])))."' and setting_id!=0");
				//exit;
				$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'modules?msg='.base64_encode($_REQUEST['rid'].' uninstalled successfull!!'));
				/*end unistall query*/
				break;
				default:break;
			}
			$extensions = $act_ext->getInstalled('module');
			/*echo "<pre>";
				print_r($extensions);
			echo "</pre>";*/
			
			
			foreach ($extensions as $key => $value) {
				if (!file_exists(APPLICATION_PATH.'/models/Module/' . $value . '.php')) {
					
					$act_ext->db->query("DELETE FROM r_extension WHERE `type` ='module' and `code` = '".$value."'");
					$act_ext->db->query("DELETE FROM r_setting WHERE `group` = '" . stripslashes($value)."'");
					unset($extensions[$key]);
					
				}
			}
			
			$this->data['extensions'] = array();
			
			$files = glob(APPLICATION_PATH.'/models/Module/*.php');
			/*explode($files);
				echo "<pre>";
				print_r($files);
			echo "</pre>";*/
			//exit;
			$userModulesArray=array();//array("Account");
			if ($files) {
				foreach ($files as $file) {
					$extension = basename($file, '.php');
					$action = array();
					//$class="Model_Module_".$extension;
					//$type=$class::type();
					//echo "value of ".$extension." ".substr($extension,"-4")."<br/>";
					/*echo "value of ".$extension;
					exit;*/
					$type=substr($extension,"-4")=="paid"?"paid":"free";
					
					if($type=="paid")
					{
						$status=in_array($extension,$userModulesArray)?"1":"0";
					}
					else if($type=="free")
					{
						$status="1";
					}
					
					//echo $type."<br/>";
					if (!in_array($extension, $extensions)) {
						$action[] = array(
						'text' => 'Install',
						//'type'=>$type,
						//'status'=>$status,
						'href' => @constant('ADMIN_URL_CONTROLLER').'modules?rid='. $extension.'&type=Install'
						);
						} else {
						$action[] = array(
						'text' => 'Edit',
						//'type'=>$type,
						//'status'=>$status,
						'href' => @constant('ADMIN_URL_CONTROLLER').'modules?rid='. $extension.'&type=Edit'
						);
						
						$action[] = array(
						'text' => 'Uninstall',
						//'type'=>$type,
						//'status'=>$status,
						'href' => @constant('ADMIN_URL_CONTROLLER').'modules?rid='. $extension.'&type=UnInstall'
						);
					}
					
					$this->data['extensions'][] = array(
					'name'   => $extension,
					'type'=>$type,
					'status'=>$status,
					'action' => $action
					);
				}
			}
			/*echo "<pre>";
				print_r($this->data);
				echo "</pre>";
			exit;*/
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($this->data['extensions']));
            $paginator->setItemCountPerPage(@constant('ADMIN_PAGE_LIMIT'))
            ->setCurrentPageNumber($this->_getParam('page',1));
            $this->view->modules=$paginator;
			$this->view->data=$this->data;
		}
		
		public function ajaxdashboardAction()
		{
			//$this->view->test="how are you";
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$this->view->test=$this->render('dashboard-graph');
		}
		
		
		
		public function log_history_entry()
		{
			
			$model = $this->getModel('Model_DbTable_radminactivitylog');
			//new Zend_Db_Expr('NOW()')
			$data=array("ip_address"=>$_SERVER['REMOTE_ADDR'],"access_date"=>$this->_date,
			'admin_id'=>$_SESSION['admin_id'],'page_accessed'=>$this->_action);
			
			switch($_REQUEST[type])
			{
				case 'Add': if($_REQUEST[play]=='save' || $_REQUEST[play]=='apply')
				{
					$data[page_url]="http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']."?type=Add";
					$data['action']="Add";
					$model->insert($data);
				}
				break;
				
				case 'Edit': $data['action']="Edit";
				$data[page_url]="http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING'];
				if($_REQUEST[play]=='save' || $_REQUEST[play]=='apply')
				{
					$data['action']="Edit";
					$model->insert($data);
				}
				break;
				
				default:
				if($_REQUEST[action]=='Del' && $_REQUEST[rid]!='')
				{
					$data['action']="Delete";
					$data[page_url]="http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$model->insert($data);
				}else
				{
					$data['action']="View";
					$data[page_url]="http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$model->insert($data);
				}
			}
		}
		
		public function checkpermission()
		{
			
			//echo '<pre>';
			//print_r($_SESSION['arr_access_files']);
			//exit;
			
			if(!in_array($this->_action,$_SESSION['arr_access_files']))
			{
				$this->_forward('page-restricted');
				//exit;
			}
			
			if($_REQUEST['type']=='Edit')
			{
				if($_SESSION[arr_files_per][$this->_action][Edit]=='N')
				{
					if($_REQUEST['play']!="")
					{
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'page-restricted');
					}
				}
			}
			
			if($_REQUEST['type']=='Add')
			{
				if($_SESSION[arr_files_per][$this->_action][Add]=='N')
				{
					if($_REQUEST['type']=="Add")
					{
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'page-restricted');
					}
				}
			}
		}
		
		public function pageRestrictedAction()
		{
			if($_SESSION['admin_id']=="")
			{
				$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'login');
			}
			$this->view->actionTitle=$this->_action;
		}
		
		public function ajaxprodrelatedAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			$select = $act->db->fetchAssoc("select products_name,products_id from r_products_description where lower(products_name)
			like '%".strtolower($_REQUEST['name_startsWith'])."%'  and products_id!=0");
			$i=0;
			foreach($select as $k)
			{
				$arr[$i][name]=$k[products_name];
				$arr[$i][pid]=$k[products_id];
				$arr[$i][info]="<div id='".$k[products_id]."'>".$k[products_name]."<input type='hidden'
				name='r_product_related[related_id]' id='r_product_related[related_id]' value='".$k[products_id]."'>
				<input type='checkbox'
				name='r_del_related[related_id]' id='r_del_related[related_id]' value='".$k[products_id]."' onclick='fnclose(this.value);'>Delete</div>";
			$i++;}
			$this->view->geonames=$arr;
		}
		
		public function ajaxcnameautoAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			//$select = $act->db->fetchAssoc("select concat(customers_firstname,' ',customers_lastname) as fname from r_customers where	lower(customers_firstname) like '%".strtolower($_REQUEST['q'])."%' or lower(customers_lastname) like '%".strtolower($_REQUEST['q'])."%'");
			
			$select = $act->db->fetchAssoc("select concat(customers_firstname,' ',customers_lastname) as fname from r_customers where
			lower(customers_firstname) like '%".strtolower($_REQUEST['q'])."%' or lower(customers_lastname) like '%".strtolower($_REQUEST['q'])."%' order by customers_firstname asc");
			foreach($select as $k=>$v)
			{
				echo $v[fname]."\n";
				/*echo "select concat(customers_firstname,' ',customers_lastname) from r_customers where
					lower(customers_firstname) like '%".strtolower($_REQUEST['q'])."%' or lower(customers_lastname) like '%".strtolower($_REQUEST['q'])."%'";
				*/
			}
		}
		
		public function ajaxcemailautoAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			$select = $act->db->fetchAssoc("select customers_email_address from r_customers where
			lower(customers_email_address) like '%".strtolower($_REQUEST['q'])."%' order by customers_email_address asc");
			foreach($select as $k=>$v)
			{
				echo "$v[customers_email_address]\n";
			}
		}
		
		public function ajaxaffemailautoAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			$select = $act->db->fetchAssoc("select email from r_affiliate where
			lower(email) like '%".strtolower($_REQUEST['q'])."%'  order by email asc");
			foreach($select as $k=>$v)
			{
				echo "$v[email]\n";
			}
		}
		
		public function ajaxaffnameautoAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			$select = $act->db->fetchAssoc("select concat(firstname,' ',lastname) as name from r_affiliate where
			lower(firstname) like '%".strtolower($_REQUEST['q'])."%' or lower(lastname) like '%".strtolower($_REQUEST['q'])."%' order by firstname asc");
			foreach($select as $k=>$v)
			{
				echo "$v[name]\n";
			}
		}
		
		public function ajaxpnameautoAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			$select = $act->db->fetchAssoc("select products_name from r_products_description where
			lower(products_name) like '%".strtolower($_REQUEST['q'])."%' and language_id='1'");
			foreach($select as $k=>$v)
			{
				echo "$v[products_name]\n";
				
			}
		}
        
        public function ajaxbrandautoAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			$select = $act->db->fetchAssoc("select manufacturers_name from r_manufacturers where
			lower(manufacturers_name) like '%".strtolower($_REQUEST['q'])."%' order by manufacturers_name asc");
			foreach($select as $k=>$v)
			{
				echo "$v[manufacturers_name]\n";
			}
		}
        
        public function ajaxcatautoAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			$rows=$act->db->fetchAssoc("select categories_id,categories_name from r_categories_description where  lower(categories_name) like '%".strtolower($_REQUEST['q'])."%' and language_id=1");
			$name="";
			foreach($rows as $k=>$v)
			{
				$name=str_replace('&nbsp;&gt;&nbsp;','>',$act->tep_output_generated_category_path($v[categories_id]));
				echo "$name\n";
				$name="";
			}
		}
        
        
        public function searchfileautoAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			$select = $act->db->fetchAssoc("select file_name from r_admin_permissions where
			lower(file_name) like '%".strtolower($_REQUEST['q'])."%' and admin_roles_id='".(int)$_SESSION[role_id]."' order by file_name asc");
			foreach($select as $k=>$v)
			{
				echo "$v[file_name]\n";
				//echo "<span onclick='golink('".$v[file_name]."')'>$v[file_name]</span>\n";
				
			}
		}
		
		public function ajaxpmodelautoAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			$select = $act->db->fetchAssoc("select products_model from r_products where
			lower(products_model) like '%".strtolower($_REQUEST['q'])."%' order by products_model asc");
			foreach($select as $k=>$v)
			{
				echo "$v[products_model]\n";
			}
		}
		
		public function ajaxprodreturnautoAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			$select = $act->db->fetchAssoc("select concat(firstname,' ',lastname) as customers_name from r_return where lower(concat(firstname,' ',lastname)) like '%".strtolower($_REQUEST['q'])."%' order by firstname asc");
			foreach($select as $k=>$v)
			{
				echo "$v[customers_name]\n";
				
			}
		}
		
		public function ajaxorderautoAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$act=new Model_Adminaction();
			$select = $act->db->fetchAssoc("select customers_name from r_orders where lower(customers_name) like '%".strtolower($_REQUEST['q'])."%' order by customers_name asc");
			foreach($select as $k=>$v)
			{
				echo "$v[customers_name]\n";
				
			}
			/*foreach($arr as $k=>$v)
				{
				//echo "<li onclick='alert(this.innerHTML)'>$v|$k\n</li>";
				echo "$v|$k\n";
			}*/
			
		}
        
        public function databaseBackupAction()
        {
            $this->checkSession($_SESSION['admin_id']);
            $this->view->actionTitle=$this->_action;
            if($_REQUEST['type']=="backup")
            {
				$this->backuptextAction();  
			}else if($_REQUEST['type']=="restore")
            {
				if ($_SERVER['REQUEST_METHOD'] == 'POST') {
					if (is_uploaded_file($_FILES['import']['tmp_name'])) {
						$content = file_get_contents($_FILES['import']['tmp_name']);
						} else {
						$content = false;
					}
					
					if ($content) 
					{
						$DbackupObj=new Model_Dbackup();
						$DbackupObj->restore($content);
						} else {
						//$this->error['warning'] = $this->language->get('error_empty');
					}
				}
			}
		}
        
        public function backuptextAction()
		{
			if (!headers_sent()) 
			{
				header('Expires: 0', true);
				header('Content-Description: File Transfer', true);
				header('Content-Type: application/octet-stream', true);
				header('Content-Disposition: attachment; filename=backup-'.date('F-j-Y-h:m:s').'.sql', true);
				header('Content-Transfer-Encoding: binary', true);                     
				$DbackupObj=new Model_Dbackup();
				$output=$DbackupObj->backup();
				//exit;
				
			}
			echo $output;
			exit;
		}
        public function exportAction()
        {
			
            $action=new Model_Adminaction();
            $this->view->actionTitle=$this->_action;
            $this->checkSession($_SESSION['admin_id']);
            $row_cat=$action->db->fetchRow("select max(categories_id)+1 as category_id from r_categories");
            $row_prod=$action->db->fetchRow("select max(products_id)+1 as product_id from r_products");
            $this->view->category_id=$row_cat['category_id'];
            $this->view->product_id=$row_prod['product_id'];
            $this->view->lang=$action->db->fetchAll("SELECT languages_id, name FROM r_languages");
            $this->view->tax=$action->db->fetchAll("SELECT tax_class_title,tax_class_id FROM r_tax_class");
            $this->view->downloads=$action->db->fetchAll("SELECT name FROM r_download_description");
            $this->view->stock=$action->db->fetchAll("SELECT name, stock_status_id FROM r_stock_status where language_id=1");
			/* echo "<pre>";
				print_r($_REQUEST);
				print_r($_FILES);
			echo "</pre>";*/
            //exit;
            if($_FILES['browser']['name']!="")
            {
				$exp=explode(".",$_FILES['browser']['name']);
				if(end($exp)=="zip")
				{
					$act_ext=new Model_Adminextaction();
					$upload=$act_ext->fnupload(	array("action"=>"export","type"=>"Edit","field"=>"browser","path"=>PATH_TO_UPLOADS_DIR."/".$_REQUEST['directory']."/","prev_img"=>'',"prefix"=>''));
					//echo "vile name ".$upload;
					$path_to_backup_file=PATH_TO_UPLOADS_DIR.$_REQUEST['directory']."/".$upload;
					
					/* if($_FILES['browser']['name']!="")
						{
						
						$exp=explode(".",$_FILES['browser']['name']);
						if(end($exp)=="zip")
						{
						$act_ext=new Model_Adminextaction();
						$upload=$act_ext->fnupload(	array("action"=>"export","type"=>"Edit","field"=>"browser","path"=>PATH_TO_UPLOADS_DIR."/".$_REQUEST['directory']."/","prev_img"=>'',"prefix"=>''));
					$path_to_backup_file=PATH_TO_UPLOADS_DIR.$_REQUEST['directory']."/".substr($_FILES['browser']['name'],"0",-4)."_.zip";*/
					$zip = new ZipArchive;
					$res = $zip->open(@constant('PATH_TO_UPLOADS_DIR').$_REQUEST['directory'].'/'.$upload);
					//echo "value of ".$res;
					//exit;
					if ($res == TRUE) {
						$zip->extractTo(@constant('PATH_TO_UPLOADS_DIR').$_REQUEST['directory'].'/');
						$zip->close();
						@unlink($path_to_backup_file);
						echo @constant('PATH_TO_UPLOADS_DIR').$_REQUEST['directory'].'/<br>'.$path_to_backup_file;
						echo 'ok';
						//exit;
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'export?msg='.base64_encode('Images uploaded successfully to '.$_REQUEST['directory'].'!!'));
						} else {
						echo 'failed';
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'export?msg='.base64_encode('Upload failed!!'));
					}
					
					
                    /*}else
						{
                        
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'export?msg='.base64_encode('Invalid file format.upload zip file only!!'));
						}
					}     */                // exit;  
				}else
				{
					
					$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'export?msg='.base64_encode('Invalid file format.upload zip file only!!'));
				}
			}
            
            if($_REQUEST['type']=="export")
            {
                $expObj=new Model_Export();
                $expObj->download();
			}else if($_REQUEST['type']=="import")
            {
                //exit("here");
                if (($this->getRequest()->isPost())) {
                    if ((isset( $_FILES['import'] )) && (is_uploaded_file($_FILES['import']['tmp_name']))) {
						$file = $_FILES['import']['tmp_name'];
						$expObj=new Model_Export();
						$return=$expObj->upload($file);        
						
						if($return=='1')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'export?msg='.base64_encode('Import Successfully!!').'&type=import');
						}else
						{
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'export?msg='.base64_encode('Import Failed Please check your excel file!!').'&type=import');                  }
					}
				}
			}
		}
		
		public function themesAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			
			if($_REQUEST['type']=='')
			{
				$ship_files_array=scandir(PATH_TO_TEMPLATES);
				unset($ship_files_array[0]);
				unset($ship_files_array[1]);
				
				
				$this->view->ship_files_array=$ship_files_array;
				$filter_arr=array();
				foreach($this->view->ship_files_array as $k=>$v)
				{
					$exp=explode("_",$v);
					if($exp[1]!="" && $exp[1]!=@constant('GLOBAL_DOMAIN_KEY'))
					{
						continue;
					}
					$filter_arr[]=$v;
				}
                
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($filter_arr));
                $paginator->setItemCountPerPage(@constant('ADMIN_PAGE_LIMIT'))
				->setCurrentPageNumber($this->_getParam('page',1));
                $this->view->themes=$paginator;
				$this->view->results=$ship_mod_obj;
				$this->view->row=$row;
                
			}else if($_REQUEST['type']!="")
			{
				$array_key=$action->db->fetchAll("select concat(`key`,'==',`value`) as con from r_template");
				$return_array_key=array();
				foreach($array_key as $k=>$v)
				{
					$return_array_key[]=$v['con'];
				}
				
				$this->view->temp_keys=$return_array_key;
				if(file_exists(PATH_TO_LANGUAGE."language.php"))
				{
					$this->view->status="Modified";
				}else
				{
					$this->view->status="Default";
				}
				
				switch ($_REQUEST['type'])
				{
					case 'Edit':
					
					if ($_REQUEST['do']=="defaultfooter") 
					{
						@unlink(PATH_TO_FILES."footer.phtml");
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?rid='.$_REQUEST['rid'].'&type=Edit&msg='.base64_encode("default footer applied successfully"));
					}
					
					if ($_REQUEST['do']=="defaultsettings") 
					{
						@unlink(PATH_TO_FILES."load.css");
						$action->db->delete("r_template","template_id!=0");
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?rid='.$_REQUEST['rid'].'&type=Edit&msg='.base64_encode("default settings applied successfully"));
					}
					
					if ($_REQUEST['do']=="default") 
					{
						@unlink(@constant('PATH_TO_LANGUAGE')."language.php");
						//@unlink(@constant('PATH_TO_UPLOADS_DIR')."language.csv");
						//start remove lang cache
						$dir=scandir(@constant('PATH_TO_LANGUAGECACHE'));
						unset($dir[0]);
						unset($dir[1]);
						foreach($dir as $k=>$v)
						{
							@unlink(@constant('PATH_TO_LANGUAGECACHE').$v);
						}
						//end remove lang cache
						unset($_SESSION['OBJ']['tr']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?rid='.$_REQUEST['rid'].'&type=Edit&msg='.base64_encode("default language settings applied successfully"));
					}
					
					if ($_REQUEST['do']=="download") 
					{
						include PATH_TO_TEMPLATES."/".$_REQUEST['rid']."/language/language.php";	
						$file=PATH_TO_UPLOADS_DIR."language.txt";
						$fp = fopen($file, "w");
						//ksort($_);
						/*echo "<pre>";
							print_r($_);
							echo "</pre>";
						exit;*/
						$text="";
						foreach($_ as $k=>$v)
						{
							//$text.=$k.",".addslashes($v)."\r\n";		
							$text.=$k."==".$v."\r\n";		
						}
						// echo $text;
						//echo $text;
						// exit;
						fwrite($fp, $text);
						fclose($fp);
						
						if (file_exists($file)) 
						{
							header('Pragma: public');
							header('Expires: 0');
							header('Content-Description: File Transfer');
							header('Content-Type: ' . $mime);
							header('Content-Transfer-Encoding: ' . $encoding);
							header('Content-Disposition: attachment; filename='.($mask ? $mask : basename($file)));
							header('Content-Length: ' . filesize($file));
							
							$file = readfile($file);
							
							print($file);
							exit;
						}
						//exit;
					}
					
					if ($_REQUEST['do']=="downloadmod") 
					{
						include PATH_TO_LANGUAGE."language.php";	
						
						$file=PATH_TO_UPLOADS_DIR."language.txt";
						$fp = fopen($file, "w");
						ksort($_);
						$text="";
						foreach($_ as $k=>$v)
						{
							$text.=$k."==".$v."\r\n";		
						}
						fwrite($fp, $text);
						fclose($fp);
						
						if (file_exists($file)) 
						{
							header('Pragma: public');
							header('Expires: 0');
							header('Content-Description: File Transfer');
							header('Content-Type: ' . $mime);
							header('Content-Transfer-Encoding: ' . $encoding);
							header('Content-Disposition: attachment; filename='.($mask ? $mask : basename($file)));
							header('Content-Length: ' . filesize($file));
							
							$file = readfile($file);
							
							print($file);
							exit;
						}
					}
					if($_REQUEST['play']=='save')
					{
						if($_REQUEST['default']=="")
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?type=Edit&s=0&rid='.$_REQUEST['rid'].'&msg='.base64_encode('Cannot update as theme is not selected as default!!'));
						}
						
						if($_REQUEST['modify_footer']=='1')
						{
							$my_file=PATH_TO_FILES."footer.phtml";
							$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
							fwrite($handle, $_REQUEST['footer']); 
						}
						$action->db->update('r_configuration',array("configuration_value"=>addslashes($_REQUEST['intro_page'])),'configuration_key=\'STORE_INTRODUCTION_CONTENT\'');
						$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['intro_on']),'configuration_key=\'STORE_INTRODUCTION_STATUS\'');
						
						if (is_uploaded_file($_FILES['language']['tmp_name']))
						{
							@unlink(@constant('PATH_TO_UPLOADS_DIR')."language.txt"); 
							move_uploaded_file($_FILES['language']['tmp_name'],@constant('PATH_TO_UPLOADS_DIR')."language.txt");
							
							//echo substr($_FILES['language']['name'],strlen($_FILES['language']['name'])-3,3);
							$lf=file(@constant('PATH_TO_UPLOADS_DIR')."language.txt");
							$langscript="<?php \n";
							foreach($lf as $lk=>$lv)
							{
								$larray=explode("==",$lv);
								//$langscript.="";
								//$langscript.='$'.'_'.'["'.trim($larray[0]).'"]="'.trim($larray[1]).'"'.";\n";
								$langscript.="$"."_"."['".trim($larray[0])."']='".trim($larray[1])."';\n";
								//$langscript.="$"."_"."['".trim($larray[0])."']='".trim($larray[1])."';\n";
								
							}
							$langscript.="return $"."_;";
							$lang_file=PATH_TO_LANGUAGE."language.php";
							$handle = fopen($lang_file, 'w') or die('Cannot open file:  '.$lang_file);
							fwrite($handle, $langscript);
							
							//start remove lang cache
							$dir=scandir(@constant('PATH_TO_LANGUAGECACHE'));
							unset($dir[0]);
							unset($dir[1]);
							foreach($dir as $k=>$v)
							{
								@unlink(@constant('PATH_TO_LANGUAGECACHE').$v);
							}
							//end remove lang cache
							unset($_SESSION['OBJ']['tr']);
							//exit;
						}
						//echo $langscript;
						/*exit;
							echo "<pre>"; 
							print_r($lf);
						exit;*/
						//write template.php file with selected template name
						if($_REQUEST['default']!="")
						{
							$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['default']),'configuration_key=\'SITE_DEFAULT_TEMPLATE\'');
							$data="<?php 
							define('TEMPLATE_LAYOUT_PATH_ADDRESS_FRONT','/templates/".$_REQUEST['default']."');
							define('TEMPLATE_VIEW_PATH_ADDRESS_FRONT','templates/".$_REQUEST['default']."');
							define('ADMIN_FRIENDLY_URL','".constant('ADMIN_FRIENDLY_URL')."');";
							
							$my_file=PATH_TO_FILES."template.php";
							$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
							fwrite($handle, $data);
						}
						if($_REQUEST['modify_css']=='1') 
						{
							$action->db->delete("r_template","template_id!=0");
							foreach ($_REQUEST[cvs] as $k=>$v)
							{
								$list[]=$k.",".$v;
								$action->db->insert("r_template",array("key"=>$k,"value"=>$v));
							}
							
							/*start writing file*/
							$my_file=PATH_TO_TEMPLATES."/".$_REQUEST['rid']."/includes/css/main.css";
							$handle = fopen($my_file, 'r');
							$data = fread($handle,filesize($my_file));
							
							foreach ($list as $line)
							{
								$exp=explode(',',trim($line));
								if(strtolower(rtrim($exp[1]))=='blank')
								{
									$val="";
								}else
								{
									$val=$exp[1];
								}
								$data=str_replace($exp[0],$val,$data);
							}
							
							$my_file=PATH_TO_FILES."load.css";
							$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
							fwrite($handle, $data);
							
						}
						Model_Cache::removeAllCache();
						//exit;
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode('Updated Successfully!!'));
					}
					break;
				}
			}
		}
		
		public function invoiceAction(){
			$this->_helper->layout()->disableLayout();
			
			//$this->_helper->viewRenderer->setNoRender(true);
			//print_r($_REQUEST);
			
		}
		
		public function ajaxzoneAction()
		{
			$act=new Model_Adminaction();
			$select = $act->db->fetchAll("select zone_id,zone_code,zone_name from r_zones where zone_country_id=".(int)$_REQUEST['q']);
			$str="";
			foreach($select as $v)
			{
				$selected=$v['zone_id']==$zid?'selected':'';
				$str.= "<option value='".$v['zone_id']."' ".$selected." >".$v['zone_name']."</option>";
			}
			$this->view->test=$str;
		}
		/*performs login authentication*/
        public function loginAction()
        {
            if($_SESSION['admin_id']!="")
            {
                $this->_forward('home');
			}
            $this->_helper->layout()->disableLayout();
		}
        
        public function forgotPasswordAction()
        {
			if($_SESSION['admin_id']!="")
            {
                $this->_forward('home');
			}
            
            if($_REQUEST['email']!="")
            {
				$radmin=new Model_DbTable_radmin();
				$row = $radmin->fetchRow("email ='$_REQUEST[email]'");
				
				if(count($row)!='0')
				{	$act_ext=new Model_Adminextaction();
					$password=$act_ext->getDecryptPassword($row['admin_pass']);
					//echo "value of ".$password;
					$mailObj=new Model_Mail();
					$email=$mailObj->getEmailContent(array('lang'=>'1','id'=>'2','replace'=>array("%password%"=>$password)));
					
					$array_mail=array('to'=>array('name'=>'hi','email'=>trim($_REQUEST[email])),'html'=>array('content'=>$email['content']),'subject'=>$email['subject']);
					
					$mailObj->sendMail($array_mail);
					$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'login?msg='.base64_encode('Please check you email id for details!!'));
				}else
				{
					$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'forgot-password?msg='.base64_encode('invalid email id!!'));
				}
			}
            $this->_helper->layout()->disableLayout();
		}
        
        public function indexAction()
		{
			//echo "value of ".$_SESSION['admin_id'];
			//exit;
			$act_ext=new Model_Adminextaction();
			if($_SESSION['admin_id']!="")
			{
				$this->checkpermission();
			}
			
			$request = $this->getRequest();
			if($this->_getParam('uname')!="" && $this->_getParam('pwd')!="")
			{
				$model = $this->getModel('Model_DbTable_radmin');
				/*$data = $model->fetchAll("select * from r_admin where admin_name ='".$this->_getParam('uname')."'
				and admin_pass='".$this->_getParam('pwd')."'");*/
				//$data=$model->fetchRow($model->select()->where('admin_name = "'.$this->_getParam('uname').'" and admin_pass="'.$act_ext->setEncryptPassword($this->_getParam('pwd')).'"'));
				$data=$model->fetchRow($model->select()->where('admin_name = "'.$this->view->escape($this->_getParam('uname')).'" and admin_pass=
				"'.$this->view->escape($act_ext->setEncryptPassword($this->_getParam('pwd'))).'"'));
				if(count($data)>0)
				{
					$_SESSION['admin_id']=$data['admin_id'];
					$_SESSION['role_id']=$data['admin_roles_id'];
					$act_ext=new Model_Adminextaction();
					$act_ext->fndologin();
					$adminext=new Model_Adminextaction();
					$go=$adminext->loginRedirection(@constant('GLOBAL_STORE_DATE_REGISTERED'));
					$this->_forward($go);
					//$this->_forward('home');
				}else
				{
					$_redirector = $this->_helper->getHelper('Redirector');
					$_redirector->gotoUrl(@constant('ADMIN_URL_CONTROLLER').'login?msg='.base64_encode('invalid login details'));
                    
				}
			}
			else if($_SESSION['admin_id']!='')
			{
				$adminext=new Model_Adminextaction();
				$go=$adminext->loginRedirection(@constant('GLOBAL_STORE_DATE_REGISTERED'));
				$this->_forward($go);
				//$this->_forward('home');
			}else
			{
				$_redirector = $this->_helper->getHelper('Redirector');
				$prefix=$_SERVER['REQUEST_METHOD']=='POST'?'?msg='.base64_encode('invalid login details'):"";
				$_redirector->gotoUrl(@constant('ADMIN_URL_CONTROLLER').'login'.$prefix);
				
			}
		}
		
		
		/*performs logout action*/
		public function logoutAction()
		{
			session_destroy();
			unset($_SESSION);
			$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'login?msg='.base64_encode('end of session'));
		}
		
		
		
		/* moves to home page after successfull authentication*/
		public function homeAction()
		{
			$_SESSION['OBJ']['tr']=$tr;
			/*checks admin session*/
			$this->checkpermission();
			$this->checkSession($_SESSION['admin_id']);
			
		}
		
		/*checks admin session and moves to admin.php if no session*/
		private function checkSession($id)
		{
			if($id=='')
			{
				$_redirector = $this->_helper->getHelper('Redirector');
				$_redirector->gotoUrl(@constant('ADMIN_URL_CONTROLLER').'login?msg='.base64_encode('invalid login details'));
			}else
			{
				$this->checkpermission();
				if($this->_action!='admin-log-history')
				{
					$this->log_history_entry();
				}
			}
		}
		
		public function orderTotalAction()
		{
			//$this->checkpermission();
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			//$tr = new Zend_Translate('array', PATH_TO_PUBLIC.'languages/english/total', 'en', array('scan' => Zend_Translate::LOCALE_DIRECTORY));
			
			$action=new Model_Adminaction();
			
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Inst':if($_REQUEST['rid']!="")
					{
						//echo substr(ucfirst($_REQUEST['rid']),0,-4);
						//exit;
						$class="Model_OrderTotal_".substr(ucfirst($_REQUEST['rid']),0,-4);
						$Inst_obj=new $class;
						$Inst_obj->install();
						$action->updateOrderTotal(preg_replace("([ ,;']+)", "", $_REQUEST['rid']),'Inst');
						Model_Cache::removeCache(array("id"=>'define'));
						Model_Cache::removeCache(array("id"=>'conf'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode('Installation Successfull!!'));
					}
					break;
					
					case 'UnInst':if($_REQUEST['rid']!="")
					{
						$class="Model_OrderTotal_".substr(ucfirst($_REQUEST['rid']),0,-4);
						$Inst_obj=new $class;
						$Inst_obj->remove();
						$action->updateOrderTotal(preg_replace("([ ,;']+)", "", $_REQUEST['rid']),'UnInst');
						Model_Cache::removeCache(array("id"=>'define'));
						Model_Cache::removeCache(array("id"=>'conf'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode('Uninstalled Successfull!!'));
					}
					break;
					
				}
				
				$ship_files_array=scandir(PATH_TO_ORDER_TOTAL);
				foreach($ship_files_array as $k=>$v)
				{
					if(substr($v,-4,4)!='.php')
					{
						unset($ship_files_array[$k]);
					}
				}
				
				$this->view->ship_files_array=$ship_files_array;
				$this->view->arr_ship_module=explode(";",MODULE_ORDER_TOTAL_INSTALLED);
				
				$itrt=1;
				foreach($this->view->ship_files_array as $k=>$v)
				{
					$class_td=fmod($itrt,2)==0?'':'addcolorne';
					$class="Model_OrderTotal_".substr(ucfirst($v),0,-4);
					$ship_mod_obj[$itrt]=new $class;
					$arr=(array)$ship_mod_obj[$itrt];
					$status=$arr['enabled']=='1'?'Enable':'Disable';
					if(in_array($v,$this->view->arr_ship_module))
					{
						
						$row.= '<tr><td align="center" class="'.$class_td.'">'.$itrt.'</td>
						<td class="'.$class_td.'">'.$arr['title'].'</td>
						<td class="'.$class_td.'">'.$arr['sort_order'].'</td>
						<td class="'.$class_td.'">'.$status.'</td>
						<td class="'.$class_td.'">
						<a href="'.$act.'?type=Edit&rid='.$v.'&page='.$page.'">Edit</a> |
						<a href="'.$act.'?action=UnInst&rid='.$v.'&page='.$page.'">Uninstall</a></td></tr>';
					}else
					{
						$row.= '<tr><td align="center" class="'.$class_td.'">'.$itrt.'</td>
						<td class="'.$class_td.'">'.$arr['title'].'</td>
						<td class="'.$class_td.'">&nbsp;</td>
						<td class="'.$class_td.'">'.$status.'</td>
						<td class="'.$class_td.'"><a href="'.$act.'?action=Inst&rid='.$v.'">Install</a> </td>	</tr>';
					}
					$itrt++;
				}
				$this->view->results=$ship_mod_obj;
				$this->view->row=$row;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']=='save')
					{
						$class="Model_OrderTotal_".substr(ucfirst($_REQUEST['rid']),0,-4);
						$edit=new $class;
						foreach($edit->keys() as $k=>$v)
						{
							$action->updateConfig($v,$_REQUEST['configuration'][$v]);
						}
						Model_Cache::removeCache(array("id"=>'define'));
						Model_Cache::removeCache(array("id"=>'conf'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode('Updated Successfully!!'));
					}
					break;
				}
			}
		}
		
		public function paymentAction()
		{
			//$this->checkpermission();
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$action=new Model_Adminaction();
			
			//$tr = new Zend_Translate('array',PATH_TO_PUBLIC.'languages/english/payment', 'en', array('scan' => Zend_Translate::LOCALE_DIRECTORY));
			
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Inst':if($_REQUEST['rid']!="")
					{
						
						$class="Model_Payment_".substr(ucfirst($_REQUEST['rid']),0,-4);
						$Inst_obj=new $class;
						$Inst_obj->install();
						$action->updatePayment(preg_replace("([ ,;']+)", "", $_REQUEST['rid']),'Inst');
						Model_Cache::removeCache(array("id"=>'define'));
						Model_Cache::removeCache(array("id"=>'conf'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode('Installation Successfull!!'));
					}
					break;
					
					case 'UnInst':if($_REQUEST['rid']!="")
					{
						
						$class="Model_Payment_".substr(ucfirst($_REQUEST['rid']),0,-4);
						$Inst_obj=new $class;
						$Inst_obj->remove();
						$action->updatePayment(preg_replace("([ ,;']+)", "", $_REQUEST['rid']),'UnInst');
						Model_Cache::removeCache(array("id"=>'define'));
						Model_Cache::removeCache(array("id"=>'conf'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode('Uninstalled Successfull!!'));
					}
					break;
					
				}
				
				$ship_files_array=scandir(PATH_TO_PAYMENT);
				foreach($ship_files_array as $k=>$v)
				{
					if(substr($v,-4,4)!='.php')
					{
						unset($ship_files_array[$k]);
					}
				}
				
				$this->view->ship_files_array=$ship_files_array;
				$this->view->arr_ship_module=explode(";",MODULE_PAYMENT_INSTALLED);
				
                $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($ship_files_array));
                $paginator->setItemCountPerPage(@constant('ADMIN_PAGE_LIMIT'))
				->setCurrentPageNumber($this->_getParam('page',1));
                $this->view->payments=$paginator;
				
				$itrt=1;
                if($_REQUEST['page']=="" || $_REQUEST['page']=="1")
                {
					$itrt=1;
				}else
                {
					$itrt+=($_REQUEST['page']-1)*@constant('ADMIN_PAGE_LIMIT');
				}
				foreach($paginator as $k=>$v)
				{
					$class_td=fmod($itrt,2)==0?'':'addcolorne';
					
					$class="Model_Payment_".substr(ucfirst($v),0,-4);
					$ship_mod_obj[$itrt]=new $class;
					$arr=(array)$ship_mod_obj[$itrt];
					if(in_array($v,$this->view->arr_ship_module))
					{
						$row.= '<tr><td align="center" class="'.$class_td.'">'.$itrt.'</td>
						<td class="'.$class_td.'">'.$arr['title'].'</td>
						<td class="'.$class_td.'">'.$arr['sort_order'].'</td>
						<td class="'.$class_td.'">
						<a href="'.$act.'?type=Edit&rid='.$v.'&page='.$page.'">Edit</a> |
						<a href="'.$act.'?action=UnInst&rid='.$v.'&page='.$page.'">Uninstall</a></td></tr>';
					}else
					{
						$row.= '<tr><td align="center" class="'.$class_td.'">'.$itrt.'</td>
						<td class="'.$class_td.'">'.$arr['title'].'</td>
						<td class="'.$class_td.'">&nbsp;</td>
						<td class="'.$class_td.'"><a href="'.$act.'?action=Inst&rid='.$v.'">Install</a> </td>	</tr>';
					}
					$itrt++;
				}
				
				$this->view->results=$ship_files_array;//$ship_mod_obj;
				$this->view->row=$row;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']=='save')
					{
						$class="Model_Payment_".substr(ucfirst($_REQUEST['rid']),0,-4);
						$edit=new $class;
						
						foreach($edit->keys() as $k=>$v)
						{
							$action->updateConfig($v,$_REQUEST['configuration'][$v]);
						}
						Model_Cache::removeCache(array("id"=>'define'));
						Model_Cache::removeCache(array("id"=>'conf'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode('Updated Successfully!!'));
					}
					break;
				}
			}
		}
		
		public function shippingAction()
		{
			//$this->checkpermission();
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$action=new Model_Adminaction();
			
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Inst':if($_REQUEST['rid']!="")
					{
						$class="Model_Shipping_".substr(ucfirst($_REQUEST['rid']),0,-4);
						$Inst_obj=new $class;
						$Inst_obj->install();
						$action->updateShipping(preg_replace("([ ,;']+)", "", $_REQUEST['rid']),'Inst');
						Model_Cache::removeCache(array("id"=>'define'));
						Model_Cache::removeCache(array("id"=>'conf'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'shipping?page='.$_REQUEST['page'].'&msg='.base64_encode('Installation Successfull!!'));
					}
					break;
					
					case 'UnInst':if($_REQUEST['rid']!="")
					{
						
						$class="Model_Shipping_".substr(ucfirst($_REQUEST['rid']),0,-4);
						$Inst_obj=new $class;
						$Inst_obj->remove();
						$action->updateShipping(preg_replace("([ ,;']+)", "", $_REQUEST['rid']),'UnInst');
						Model_Cache::removeCache(array("id"=>'define'));
						Model_Cache::removeCache(array("id"=>'conf'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'shipping?page='.$_REQUEST['page'].'&msg='.base64_encode('Uninstalled Successfull!!'));
					}
					break;
					
				}
				
				$ship_files_array=scandir(PATH_TO_SHIPPING);
				foreach($ship_files_array as $k=>$v)
				{
					if(substr($v,-4,4)!='.php')
					{
						unset($ship_files_array[$k]);
					}
				}
				
				$this->view->ship_files_array=$ship_files_array;
				$this->view->arr_ship_module=explode(";",MODULE_SHIPPING_INSTALLED);
				$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Array($ship_files_array));
                $paginator->setItemCountPerPage(@constant('ADMIN_PAGE_LIMIT'))
				->setCurrentPageNumber($this->_getParam('page',1));
                $this->view->shipping=$paginator;
				
				$itrt=1;
                if($_REQUEST['page']=="" || $_REQUEST['page']=="1")
                {
					$itrt=1;
				}else
                {
					$itrt+=($_REQUEST['page']-1)*@constant('ADMIN_PAGE_LIMIT');
				}
				
				foreach($paginator as $k=>$v)
				{
					$class="Model_Shipping_".substr(ucfirst($v),0,-4);
					$ship_mod_obj[$itrt]=new $class;
					$arr=(array)$ship_mod_obj[$itrt];
					
					if(in_array($v,$this->view->arr_ship_module))
					{
						$class=fmod($itrt,2)==0?'':'addcolorne';
						$row.= '<tr><td align="center" class="'.$class.'">'.$itrt.'</td>
						<td class="'.$class.'">'.$arr['title'].'</td>
						<td class="'.$class.'">'.$arr['sort_order'].'</td>
						<td class="'.$class.'">
						<a href="'.$act.'?type=Edit&rid='.$v.'&page='.$page.'">Edit</a> |
						<a href="'.$act.'?action=UnInst&rid='.$v.'&page='.$page.'">Uninstall</a></td></tr>';
					}else
					{
						$row.= '<tr><td align="center" class="'.$class.'">'.$itrt.'</td>
						<td class="'.$class.'"><a href="">'.$arr['title'].'</a></td>
						<td class="'.$class.'">&nbsp;</td>
						<td class="'.$class.'"><a href="'.$act.'?action=Inst&rid='.$v.'">Install</a> </td>	</tr>';
					}
					$itrt++;
				}
				$this->view->results=$ship_files_array;//$ship_mod_obj;
				$this->view->row=$row;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']=='save')
					{
						
						$class="Model_Shipping_".substr(ucfirst($_REQUEST['rid']),0,-4);
						$edit=new $class;
						foreach($edit->keys() as $k=>$v)
						{
							$conf_value=$_REQUEST['configuration'][$v];
							if( is_array( $_REQUEST['configuration'][$v] ) )
							$conf_value = implode( ", ", $_REQUEST['configuration'][$v]);
							$action->updateConfig($v,$conf_value);
							//$action->updateConfig($v,$_REQUEST['configuration'][$v]);
						}
						Model_Cache::removeCache(array("id"=>'define'));
						Model_Cache::removeCache(array("id"=>'conf'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode('Updated Successfully!!'));
					}
					break;
				}
			}
		}
		
		public function newsletterAction()
		{
			//$this->checkpermission();
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type='';
			$action=new Model_Adminaction();
			if($this->view->type=='')
			{
				switch($_REQUEST['play'])
				{
					case 'save':
					if($_REQUEST['template']!='0')
					{
						$model = new Model_DbTable_rnewslettertemplate();
						$data=array('sent'=>'1','date_sent'=>$this->_date);
						$model->update($data,'newsletter_template_id='.$_REQUEST['template']);
						
					}
					switch($_REQUEST['to'])
					{
						case 'All':	$array=$action->db->fetchAll("select concat(customers_firstname,' ',customers_lastname) as 			name,customers_email_address as email  from r_customers");
						break;
						
						case 'NSub':$array=$action->db->fetchAll("select concat(customers_firstname,' ',customers_lastname) as 			name,customers_email_address as email  from r_customers where customers_newsletter='1'");
						break;
						
						case 'custgroup':
						$cust_group_id=implode(",",$_REQUEST['customer_groups']);
						$array=$action->db->fetchAll("select concat(customers_firstname,' ',customers_lastname) as name,customers_email_address as email  from r_customers where customers_approved='1' and customers_status='1' and customer_group_id in ($cust_group_id)");
						break;
						
						case 'cust':
						$cust=implode(",",$_REQUEST['customers']);
						$array=$action->db->fetchAll("select concat(customers_firstname,' ',customers_lastname) as name,customers_email_address as email  from r_customers where customers_id in ($cust)");
						break;
						
						case 'prod':	$products=implode(",",$_REQUEST['products']);
						$array=$action->db->fetchAll("select customers_name as name,customers_email_address as email  from r_orders where orders_id in (select orders_id from r_orders_products where products_id in (".$products.")) group by customers_email_address");
						
						break;
					}
					
					//$action->p($_REQUEST,'0');
					//$action->p($array,'0');
					
					$mailObj=new Model_Mail();
					$array_mail=array('to'=>array('name'=>STORE_OWNER,'email'=>STORE_OWNER_EMAIL_ADDRESS),'html'=>array('content'=>$_REQUEST['html']),'subject'=>trim($_REQUEST['subject']),'bcc'=>$array);
					//$action->p($array_mail,'1');
					$mailObj->sendMail($array_mail);
					
					//EXIT;
					$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'newsletter?msg='.base64_encode('Newsletter sent successfully!!'));
					break;
				}
			}
		}
		
		public function newslettertemplateAction(){
			//$this->checkpermission();
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rnewslettertemplate();
			$action=new Model_Adminaction();
			$this->_table="r_newsletter_template";
			$this->_id="newsletter_template_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?$this->_id:$this->_getParam('sortby');
				$select =$action->db->fetchAll("select * from ".$this->_table." order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						
						$data=array('title'=>$this->reqObj->request['title'],'description'=>addslashes($this->reqObj->request['description']),
						'date_modified'=>$this->_date);
						$model->update($data,$this->_id.'='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,(int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$data=array('title'=>$this->reqObj->request['title'],'description'=>addslashes($this->reqObj->request['description']),
						'date_created'=>$this->_date);
						
						$insert_id=$model->insert($data);
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,$insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
		}
		
		public function optionAction(){
			//$this->checkpermission();
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_roption();
			$action=new Model_Adminaction();
			$this->_table="r_option";
			$this->_id="option_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						//echo "select count(*) as count from r_products_option  where option_id in (".implode(',',$rid).")";
						$c=$action->db->fetchRow("select count(*) as count from r_products_option
						where option_id in (".implode(',',$rid).")");
						if($c['count']=='0'){
							$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
							$action->RecordDelete('r_option_description',$this->_id,$_REQUEST['rid']);
							$action->RecordDelete('r_option_value',$this->_id,$_REQUEST['rid']);
							$action->RecordDelete('r_option_value_description',$this->_id,$_REQUEST['rid']);
							Model_Cache::removeMTCache(array("multipledropdown"));        
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
						}
						else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('cannot delete as some of the options used in products'));
						}
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="asc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'name':$_REQUEST['sortby'];
				
				$select =$action->db->fetchAll("select od.name,o.option_id,o.sort_order from r_option o,r_option_description od
				where o.option_id=od.option_id and od.language_id='1'  order by ".$sortby." ".$this->disType. " limit
				".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						//$action->p($_REQUEST,'0');
						
						$data=array('type'=>$this->_getParam('select_type'),'sort_order'=>$this->_getParam('sort_order'),'filter'=>$this->_getParam('filter'));
						if($_REQUEST['dp']=='1' && $_REQUEST['child']!='')
						{
							$data['dependent_option']="1";
							$data['child']=$_REQUEST['child'];
						}else
						{
							$data['dependent_option']="0";
							$data['child']="0";    
						}
						$model->update($data,$this->_id.'='.(int)$_REQUEST['rid']);
						
						$action->lang_action(array("name"),	array("table"=>"r_option_description",
						"comp_col_1"=>"option_id","comp_col_2"=>"language_id","rid"=>(int)$_REQUEST['rid']),'update');
						
						$msg=$action->option('update','');
						if($msg!="")
						$this->view->msg=base64_encode($msg);
						else
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						Model_Cache::removeMTCache(array("multipledropdown"));
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$filter=$this->_getParam('filter')==''?'0':'1';
						
						$data=array('type'=>$this->_getParam('select_type'),'sort_order'=>$this->_getParam('sort_order'),'filter'=>$filter);
						
						if($_REQUEST['dp']=='1' && $_REQUEST['child']!='')
						{
							$data['dependent_option']="1";
							$data['child']=$_REQUEST['child'];
						}else
						{
							$data['dependent_option']="0";
							$data['child']="0";    
						}
						$insert_id=$model->insert($data);
						$action->lang_action(array("name"),	array("table"=>"r_option_description",
						"comp_col_1"=>"option_id","comp_col_2"=>"language_id","rid"=>$insert_id),'insert');
						$action->option('insert',$insert_id);
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						Model_Cache::removeMTCache(array("multipledropdown"));
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					//$sel1=$action->getEditdetails($this->_table,$this->_id,$insert_id);
					//$this->view->data = $sel1;
					break;
				}
			}
		}
		
		public function adminRoleAction()
		{
			//	$this->checkpermission();
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_radminroles();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			$this->_table="r_admin_roles";
			$this->_id="admin_roles_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table)->where("admin_roles_id!='1'"));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'admin_roles_id':$this->_getParam('sortby');
				$select =$action->db->fetchAll("select * from ".$this->_table." where admin_roles_id!='1' order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						//$action->p($_REQUEST,'1');
						$act_ext->fnEditPermissions();
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					if($_REQUEST['rid']!="1")
					{
						$sel1=$action->getEditdetails($this->_table,$this->_id,(int)$_REQUEST['rid']);
						$this->view->data = $sel1;
						$this->view->permissions=$act_ext->_permissions($_REQUEST[rid]);
					}else
					{
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode("invalid access"));        
					}
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						/*echo "<pre>";
							print_r($_REQUEST);
						exit;*/
						$act_ext->fnAddPermissions();
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,$insert_id);
					$this->view->data = $sel1;
					$this->view->permissions=$act_ext->_permissions();
					break;
				}
			}
			
		}
		
		public function adminLogHistoryAction()
		{
			$this->checkpermission();
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$model = new Model_DbTable_radminactivitylog();
			$action=new Model_Adminaction();
			$this->_table="r_admin_activity_log";
			$this->_id="log_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':
					if($_REQUEST['rid']!="")
					{
						$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'log_id':$_REQUEST['sortby'];
				$select =$action->db->fetchAll("select l.*,a.admin_name as name,r.role from ".$this->_table." l,r_admin a, r_admin_roles r
				where l.admin_id=a.admin_id and a.admin_roles_id =r.admin_roles_id order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}
		}
        
		public function reviewAction()
		{
			//$this->checkSession($_SESSION['admin_id']);
			$this->checkpermission();
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rreviews();
			$action=new Model_Adminaction();
			$this->_table="r_reviews";
			$this->_id="reviews_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($_REQUEST['action']=='Approve')
			{
				if(sizeof($_REQUEST['rid'])!="0")
				{
					$action->RecordPublish('r_reviews',array("reviews_status"=>1),'reviews_id',$_REQUEST['rid']);
					$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'review?page='.$_REQUEST['page'].'&action=Approve&msg='.base64_encode('selected Reviews Approved successfully!!'));
				}
			}
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':if($this->_getParam('rid')!="")
					{
						$action->RecordDelete($this->_table,$this->_id,$this->_getParam('rid'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$this->_getParam('page').'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'reviews_id':$_REQUEST['sortby'];
				$select =$action->db->fetchAll("select r.*,p.products_name from ".$this->_table." r,r_products_description p where p.language_id='1'
				and p.products_id=r.products_id order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						//$action->p($_REQUEST,'1');
						$data=array('reviews_text'=>$this->reqObj->request['text'],
						'reviews_rating'=>$this->reqObj->request['rating'],'last_modified'=>$this->_date,
						'reviews_status'=>$_REQUEST['status']);
						$model->update($data,$this->_id.'='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->db->fetchAll("select r.*,p.products_name from ".$this->_table." r,r_products_description p where p.language_id='1'
					and p.products_id=r.products_id and r.reviews_id='".(int)$_REQUEST['rid']."'");
					
					//$sel1=$action->getEditdetails($this->_table,$this->_id,(int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$data=array('customers_name'=>$this->reqObj->request['name'],'products_id'=>$this->reqObj->request['product'],'reviews_text'=>$this->reqObj->request['text'],'reviews_rating'=>$this->reqObj->request['rating'],'date_added'=>$this->_date,'reviews_status'=>$_REQUEST['status']);
						$insert_id=$model->insert($data);
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,$insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
		}
		
		public function administratorAction()
		{
			//$this->checkSession($_SESSION['admin_id']);
			$this->checkpermission();
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_radmin();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			$this->_table="r_admin";
			$this->_id="admin_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						$action->RecordDelete($this->_table,$this->_id,$rid);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'admin_id':$this->_getParam('sortby');
				$select =$action->db->fetchAll("select a.*,r.role from ".$this->_table." a left join r_admin_roles r
				on  a.admin_roles_id=r.admin_roles_id order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						//$action->p($_REQUEST,'1');
						$data=$this->_getParam('r_admin');
						$data['admin_pass']=$act_ext->setEncryptPassword($_REQUEST['r_admin']['admin_pass']);
						$data['last_modified']=$this->_date;
						$model->update($data,$this->_id.'='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
						
					}
					
					$sel1=$action->getEditdetails($this->_table,$this->_id,(int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					
					case 'Add':	
					if($_REQUEST['play']!="")
					{
						$uniqueUserName=$act_ext->getUnique('admin_username',$_REQUEST[r_admin][admin_name]);
						$uniqueEmail=$act_ext->getUnique('admin_email',$_REQUEST[r_admin][email]);
						if($uniqueUserName=='0' && $uniqueEmail=='0')
						{
							$data=$_REQUEST['r_admin'];
							$data['admin_pass']=$act_ext->setEncryptPassword($_REQUEST['r_admin']['admin_pass']);
							$data['date_added']=$this->_date;
							$insert_id=$model->insert($data);
							$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
							if($_REQUEST['play']=='save')
							{
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
							}
						}else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->_action.'?m=f&msg='.base64_encode('cannot add as username and email id are unique fields'));
						}
						
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,$insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
		}
		
		public function productsReturnedAction(){
			$this->checkSession($_SESSION['admin_id']);
			
			//$this->view->actionTitle=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
			$this->view->actionTitle=$this->_action;
			
			$this->view->type=$_REQUEST['type'];
			$model = new Model_DbTable_rreturn();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			$this->_table="r_return";
			$this->_id="return_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			//$action->p($_REQUEST,'0');
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
						$action->RecordDelete('r_orders_products','orders_id',$_REQUEST['rid']);
						$action->RecordDelete('r_orders_products_download','orders_id',$_REQUEST['rid']);
						$action->RecordDelete('r_orders_products_option','order_id',$_REQUEST['rid']);
						$action->RecordDelete('r_orders_status_history','orders_id',$_REQUEST['rid']);
						$action->RecordDelete('r_orders_total','orders_id',$_REQUEST['rid']);
						
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&
						msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				/*start search*/
				$search=array();
				$pageing=array();
				if($_REQUEST['order_id']!="" && $_REQUEST['order_id']!="Order Id")
				{
					$search[]="o.order_id='".(int)$_REQUEST['order_id']."'";
					$pageing[order_id]=(int)$_REQUEST['order_id'];
				}
				if($_REQUEST['customer']!="" && $_REQUEST['customer']!="Customer")
				{
					$search[]="LOWER( CONCAT( o.firstname,  ' ', o.lastname ) ) LIKE  '%".addslashes(strtolower($_REQUEST['customer']))."%'";
					//$pageing[customer]=$_REQUEST['customer'];
					$pageing[customer]=$this->reqObj->request['customer'];
				}
				if($_REQUEST['date_added']!="" && $_REQUEST['date_added']!="Date Purchased")
				{
					$search[]="date(o.date_added) = '".$_REQUEST['date_added']."'";
					//$pageing[date_added]=$_REQUEST['date_added'];
					$pageing[date_added]=$this->reqObj->request['date_added'];
				}
				if($_REQUEST['date_modified']!="" && $_REQUEST['date_modified']!="Date Modified")
				{
					$search[]="date(o.date_modified) = '".$_REQUEST['date_modified']."'";
					//$pageing[date_modified]=$_REQUEST['date_modified'];
					$pageing[date_modified]=$this->reqObj->request['date_modified'];
				}
				
				if($_REQUEST['status']!="")
				{
					$search[]="o.return_status_id ='".$_REQUEST['status']."'";
					$pageing[status]=$_REQUEST['status'];
				}
				if(count($search)>0){
					$srch_str=implode(' and ', $search);
					$srch_str=" and ".$srch_str;
					$main_srch_str="where o.return_id!='0' ".$srch_str; //added for main query as i dont have where clause,starts with and
				}else
				{
					$srch_str="";
				}
				//echo $srch_str;
				//exit;
				/*end search*/
				//echo "select o.return_id from r_return o where return_id!='0' ".$srch_str;
				//exit;
				//$count=$model->fetchAll($model->select()->from($this->_table o)->where($srch_count));
				$count=$action->db->fetchAll("select o.return_id from r_return o where return_id!='0' ".$srch_str." order by o.return_id asc");
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->paginationP(ADMIN_URL_CONTROLLER.$this->view->actionTitle,$pageing);
				//$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'return_id':$_REQUEST['sortby'];
				/*echo "SELECT * , CONCAT( o.firstname, ' ', o.lastname ) AS customer, (SELECT SUM( rp.quantity ) FROM r_return_product rp WHERE rp.return_id = o.return_id GROUP BY rp.return_id) AS quantity, (SELECT rs.name FROM r_return_status rs WHERE rs.return_status_id = o.return_status_id AND rs.language_id = '1') AS STATUS FROM r_return o ".$srch_str." order by ".$sortby." ".$this->disType. "
					limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT;
				exit;*/
				$select =$action->db->fetchAll("SELECT * , CONCAT( o.firstname, ' ', o.lastname ) AS customer,  (SELECT rs.name FROM r_return_status rs WHERE rs.return_status_id = o.return_status_id AND rs.language_id = '1') AS STATUS FROM r_return o ".$main_srch_str." order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						//$action->p($_REQUEST,'o');
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						
						$data=array('return_id'=>(int)$_REQUEST['rid'],'return_status_id'=>$_REQUEST['update_return_status'],
						'date_added'=>$this->_date,'notify'=>$_REQUEST['notify'],
						'comment'=>$_REQUEST['status_comments']);
						
						$action->db->insert('r_return_history',$data);
						$model->update(array('date_modified'=>$this->_date,'return_status_id'=>$_REQUEST['update_return_status']),'return_id='.(int)$_REQUEST['rid']);
						
						if($_REQUEST['notify']=='1')//Admin Return Status Mail
						{
							$date =$this->_date;
							$row=$action->db->fetchRow('select concat(o.firstname," ",o.lastname) as customer,o.email,s.name as status
							from r_return_status s,r_return o	where s.return_status_id="'.$_REQUEST['update_return_status'].'" and s.language_id="1" and o.return_id="'.(int)$_REQUEST['rid'].'"');
							
							/*start mail*/
							$mailObj=new Model_Mail();
							$arrmc=$mailObj->getEmailContent(array('id'=>'16','lang'=>'1','replace'=>array('%return_id%'=>(int)$_REQUEST['rid'],'%return_date%'=>$date,'%return_status%'=>$row['status'],'%comment%'=>$_REQUEST['status_comments'])));
							
							$array_mail=array('to'=>array('name'=>$row['customer'],'email'=>trim($row['email'])),'html'=>array('content'=>$arrmc['content']),'subject'=>$arrmc['subject']);
							$mailObj->sendMail($array_mail);
							/*end mail*/
						}else
						{
							$_REQUEST['notify']="0";
						}
						
						$this->view->msg=base64_decode('order stauts updated successfully!!');
						if($_REQUEST['play']=='1')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?type=Edit&rid='.$_REQUEST[rid]);
						}
						
					}
					$this->view->data=$action->db->fetchAll("select *, CONCAT( r.firstname, ' ', r.lastname ) AS customer,name as status from r_return r,r_return_status rs where r.return_status_id=rs.return_status_id and rs.language_id='1' and r.return_id='".(int)$_REQUEST[rid]."'");
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$data=array('name'=>$_REQUEST['name']);
						$insert_id=$model->insert($data);
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,$insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
		}
		
		
        public function orderDownloadFileAction()
        {
            if($_REQUEST['rid']!="" && $_REQUEST['oid']!="")
            {
				$this->db = Zend_Db_Table::getDefaultAdapter();
                $option_info=$this->db->fetchRow("select type,value from r_orders_products_option where order_id='".(int)$_REQUEST['rid']."' and order_option_id='".(int)$_REQUEST['oid']."'");
                
				if ($option_info['type'] == 'file') 
                {
					$file = PATH_TO_UPLOADS_DIR.'downloads/' . $option_info['value'];
					$mask = basename(substr($option_info['value'], 0, strrpos($option_info['value'], '.')));
					$mime = 'application/octet-stream';
					$encoding = 'binary';
					
					if (!headers_sent()) {
						if (file_exists($file)) {
							//echo "<pre>";
							//print_r($option_info);
							//exit;
							header('Pragma: public');
							header('Expires: 0');
							header('Content-Description: File Transfer');
							header('Content-Type: ' . $mime);
							header('Content-Transfer-Encoding: ' . $encoding);
							header('Content-Disposition: attachment; filename=' . ($mask ? $mask : basename($file)));
							header('Content-Length: ' . filesize($file));
							
							$file = readfile($file, 'rb');
							
							print($file);
							exit;
							} else {
							exit('Error: Could not find file ' . $file . '!');
						}
						} else {
						exit('Error: Headers already sent out!');
					}
				}
			}
		}
        
		public function ordersAction(){
			$this->checkSession($_SESSION['admin_id']);
			
			$this->view->actionTitle=$this->_action;
			
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rorders();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			$act_ext->trashOrders();
			$this->_table="r_orders";
			$this->_id="orders_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
						$action->RecordDelete('r_orders_products','orders_id',$_REQUEST['rid']);
						$action->RecordDelete('r_orders_products_download','orders_id',$_REQUEST['rid']);
						$action->RecordDelete('r_orders_products_option','order_id',$_REQUEST['rid']);
						$action->RecordDelete('r_orders_status_history','orders_id',$_REQUEST['rid']);
						$action->RecordDelete('r_orders_total','orders_id',$_REQUEST['rid']);
						
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				/*start search*/
				$search=array();
				$pageing=array();
				if($this->_getParam('order_id')!="" && $this->_getParam('order_id')!="Order Id")
				{
					$search[]="o.orders_id='".$this->_getParam('order_id')."'";
					$pageing[order_id]=$this->_getParam('order_id');
				}
				if($this->_getParam('customer')!="" && $this->_getParam('customer')!="Customer")
				{
					$search[]="o.customers_name like '%".addslashes($this->_getParam('customer'))."%'";
					$pageing[customer]=$this->_getParam('customer');
				}
				if($this->_getParam('date_added')!="" && $this->_getParam('date_added')!="Date Purchased")
				{
					$search[]="date(o.date_purchased) = '".$this->_getParam('date_added')."'";
					$pageing[date_added]=$this->_getParam('date_added');
				}
				if($this->_getParam('date_modified')!="")
				{
					$search[]="date(o.last_modified) = '".$this->_getParam('date_modified')."'";
					$pageing[date_modified]=$this->_getParam('date_modified');
				}
				
				if($this->_getParam('status')!="")
				{
					$search[]="o.orders_status ='".$this->_getParam('status')."'";
					$pageing[status]=$this->_getParam('status');
				}
				if(count($search)>0){
					$srch_str=implode(' and ', $search);
					$srch_str=" and ".$srch_str;
				}else
				{
					$srch_str="";
				}
				/*end search*/
				
				//$count=$model->fetchAll($model->select()->from($this->_table o)->where($srch_count));
				if($this->_getParam('status')=='0')//for incomplete orders
				{
					$count=$action->db->fetchAll("select o.orders_id from r_orders o where o.orders_id!=0  ".$srch_str." and concat(o.customers_email_address,'_',date(o.date_purchased)) not in (select concat(oin.customers_email_address,'_',date(oin.date_purchased)) as term from r_orders oin where oin.orders_status!=0 and  oin.date_purchased > DATE_SUB(CURDATE(),INTERVAL 31 DAY)) and  o.date_purchased > DATE_SUB(CURDATE(),INTERVAL 31 DAY) group by o.customers_email_address,date(o.date_purchased)");
				}else
				{
					$count=$action->db->fetchAll("select o.orders_id from r_orders o where invoice_id!='0' ".$srch_str." order by o.orders_id asc");
				}
				
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->paginationP(ADMIN_URL_CONTROLLER.$this->view->actionTitle,$pageing);
				//$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="desc";
				$this->view->disType=$this->disType;
				//$sortby=$this->_getParam('sortby')==''?'date_purchased':$this->_getParam('sortby');
				if($this->_getParam('sortby')=='')
				{
					$this->disType="desc";
					$sortby='date_purchased';
				}else
				{
					$sortby=$this->_getParam('sortby');
				}
				
				if($this->_getParam('status')=='0')//for incomplete orders
				{
					//$select =$action->db->fetchAll("select cg.name as group_name,o.orders_id,o.customers_name,o.total, o.date_purchased,c.symbol_left,c.symbol_right from r_orders o,r_currencies c,r_customer_group cg where  o.invoice_id =0 and o.currency_id=c.currencies_id and cg.customer_group_id=o.customer_group_id  ".$srch_str." order by ".$sortby." ".$this->disType. "	limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
					//echo "select o.orders_id,o.customers_name,o.total, o.date_purchased,c.symbol_left,c.symbol_right from r_orders o,r_currencies c where   o.currency_id=c.currencies_id ".$srch_str." and concat(o.customers_email_address,'_',date(o.date_purchased)) not in (select concat(oin.customers_email_address,'_',date(oin.date_purchased)) as term from r_orders oin where oin.orders_status!=0 and  oin.date_purchased > DATE_SUB(CURDATE(),INTERVAL 31 DAY)) and  o.date_purchased > DATE_SUB(CURDATE(),INTERVAL 31 DAY) group by o.customers_email_address,date(o.date_purchased) order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT;
					//exit;
					$select=$action->db->fetchAll("select o.orders_id, o.customers_name,o.total,o.currency_value, o.date_purchased,c.symbol_left,c.symbol_right from r_orders o,r_currencies c where o.currency_id=c.currencies_id ".$srch_str." and concat(o.customers_email_address,'_',date(o.date_purchased)) not in (select concat(oin.customers_email_address,'_',date(oin.date_purchased)) as term from r_orders oin where oin.orders_status!=0 and  oin.date_purchased > DATE_SUB(CURDATE(),INTERVAL 31 DAY)) and  o.date_purchased > DATE_SUB(CURDATE(),INTERVAL 31 DAY) group by o.customers_email_address,date(o.date_purchased) order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
					
					
					
				}else
				{
					$select =$action->db->fetchAll("select cg.name as group_name,o.orders_id,o.customers_name,o.total,o.currency_value,
					o.date_purchased,os.orders_status_name,c.symbol_left,c.symbol_right from ".$this->_table." o,r_orders_status os,r_currencies c,r_customer_group cg where o.invoice_id!='0' and
					o.orders_status=os.orders_status_id and	os.language_id='1' and o.currency_id=c.currencies_id and
					cg.customer_group_id=o.customer_group_id ".$srch_str." order by ".$sortby." ".$this->disType. "
					limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
					
					
				}
				/*echo "select cg.name as group_name,o.orders_id,o.customers_name,o.total,
					o.date_purchased,os.orders_status_name,c.symbol_left,c.symbol_right from ".$this->_table." o,r_orders_status os,r_currencies c,r_customer_group cg where o.invoice_id!='0' and
					o.orders_status=os.orders_status_id and	os.language_id='1' and o.currency_id=c.currencies_id and
					cg.customer_group_id=o.customer_group_id ".$srch_str." order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT;*/
				
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						//$action->p($_REQUEST,'1');
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['notify']=='1')
						{
							$row=$action->db->fetchRow('select s.html,s.email_template,s.subject,s.orders_status_name,o.customers_name,o.customers_email_address
							from r_orders_status s,r_orders o
							where s.orders_status_id="'.(int)$_REQUEST['update_order_status'].'" and s.language_id=o.language_id
							and o.orders_id="'.(int)$_REQUEST['rid'].'"');
							
							/*start mail*/
							
							$mailObj=new Model_Mail();
							$exp=array("%customer_name%"=>$_REQUEST['customers_name'],"%order_id%"=>(int)$_REQUEST['rid'],"%order_status%"=>$row['orders_status_name'],"%comments%"=>$_REQUEST['status_comments']);
							//$content=nl2br($row['email_template']);
							$content=$row['html']=='1'?$row['email_template']:nl2br($row['email_template']);
							//echo $content;
							//exit;
							foreach($exp as $k=>$v)
							{
								$content=str_replace($k,$v,$content);
							}
							
							$array_mail=array('to'=>array('name'=>$this->reqObj->request['customers_name'],'email'=>trim($row['customers_email_address'])),'html'=>array('content'=>$content),'subject'=>$row['subject']);
							
							
							$mailObj->sendMail($array_mail);
							//$action->p($array_mail,'1');
							/*echo "<pre>";
								print_r($array_mail);
								echo "</pre>";
								
							exit;*/
							/*end mail*/
							
							//voucher start
							if(@constant('ORDER_COMPLETE_STATUS_ID')==$_REQUEST['update_order_status'])
							{
								$cVObj=new Model_CheckoutVoucher();
								$cVObj->sendVoucher($_REQUEST['rid'],'');
							}
							//voucher end
						}
						$data=array('orders_id'=>(int)$_REQUEST['rid'],'orders_status_id'=>$_REQUEST['update_order_status'],
						'date_added'=>$this->_date,'customer_notified'=>$_REQUEST['notify'],
						'comments'=>$_REQUEST['status_comments']);
						
						$action->db->insert('r_orders_status_history',$data);
						$model->update(array('last_modified'=>$this->_date,'orders_status'=>$_REQUEST['update_order_status']),'orders_id='.(int)$_REQUEST['rid']);
						$this->view->msg=base64_encode('order stauts updated successfully!!');
						if($_REQUEST['play']=='1')
						{
							//$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?type=Edit&rid='.$_REQUEST[rid]);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?type=Edit&rid='.$_REQUEST[rid].'&msg='.base64_encode('Order Status Updated Successfully!!'));
						}
						
					}
                    //$sel1 =$action->db->fetchAll("select cg.name as group_name,o.*,os.orders_status_name,c.symbol_left,c.symbol_right from ".$this->_table." o,r_orders_status os,r_currencies c,r_customer_group cg where os.language_id='1' and o.currency_id=c.currencies_id and cg.customer_group_id=o.customer_group_id and o.orders_id='".$_REQUEST['rid']."'");
					$sel1 =$action->db->fetchAll("select (select cg.name from r_customer_group cg where cg.customer_group_id=o.customer_group_id) as group_name,o.*,(select os.orders_status_name from r_orders_status os where os.language_id=1 and os.orders_status_id=o.orders_status) as orders_status_name,c.symbol_right,c.symbol_left from r_orders o,r_currencies c where o.currency_id=c.currencies_id and o.orders_id='".(int)$_REQUEST['rid']."'");
					
					
					$this->view->data = $sel1;
					$this->renderScript('admin/orders.phtml');
					break;
					
					case 'Add':	
					if($_REQUEST['play']!="")
					{
						//echo "value of ".$_SERVER['REMOTE_ADDR'];
						/*echo "<pre>";
							print_r($_REQUEST);
							echo "</pre>";
						exit;*/
						//start add order
						//echo "select * from r_currencies where code like '".DEFAULT_CURRENCY."'";
						//exit;
						$cur_rows=$action->db->fetchRow("select * from r_currencies where code like '".DEFAULT_CURRENCY."'");	
						$inv_rows=$action->db->fetchRow("select max(invoice_id) as max from r_orders");	
						$invoice_id=$inv_rows['max']+1;
						$bctry=$action->getcountrydetails((int)$_REQUEST['payment_country_id']);
						$sctry=$action->getcountrydetails((int)$_REQUEST['shipping_country_id']);
						$payment_country=$bctry[0]['countries_name'];
						$payment_address_format=$bctry[0]['address_format'];
						$shipping_address_format=$sctry[0]['address_format'];
						$shipping_country=$sctry[0]['countries_name'];
						
						$bzone=$action->getzonedetails((int)$_REQUEST['payment_zone_id']);
						$szone=$action->getzonedetails((int)$_REQUEST['shipping_zone_id']);
						$shipping_zone=$szone[0]['zone_name'];
						$payment_zone=$bzone[0]['zone_name'];
						
						$action->db->query("INSERT INTO `r_orders` SET  ip_address='".$_SERVER['REMOTE_ADDR']."',rewards='".$this->reqObj->request['reward']."',invoice_id='".$invoice_id."',customers_id = '" . (int)$_REQUEST['customer_id'] . "', customer_group_id = '" . (int)$_REQUEST['customer_group_id'] . "', customers_name = '" . addslashes($this->reqObj->request['customer']) . "', customers_email_address = '" . addslashes($this->reqObj->request['email']) . "', customers_telephone = '" . addslashes($_REQUEST['telephone']) . "', customers_fax = '" . addslashes($_REQUEST['fax']) . "', billing_name = '" . addslashes($this->reqObj->request['payment_name']) . "', billing_company = '" . addslashes($this->reqObj->request['payment_company']) . "',  billing_street_address = '" . addslashes($this->reqObj->request['payment_address_1']) . "', billing_suburb = '" . addslashes($this->reqObj->request['payment_address_2']) . "', billing_city = '" . addslashes($this->reqObj->request['payment_city']) . "', billing_postcode = '" . addslashes($_REQUEST['payment_postcode']) . "', billing_country = '" . addslashes($payment_country) . "', billing_country_id = '" . (int)$_REQUEST['payment_country_id'] . "', billing_zone = '" . addslashes($payment_zone) . "', billing_zone_id = '" . (int)$_REQUEST['payment_zone_id'] . "', billing_address_format_id = '" . addslashes($payment_address_format) . "', payment_method = '" . addslashes($this->reqObj->request['payment_method']) . "', delivery_name = '" . addslashes($this->reqObj->request['shipping_name']) . "', delivery_company = '" . addslashes($this->reqObj->request['shipping_company']) . "', delivery_street_address = '" . addslashes($this->reqObj->request['shipping_address_1']) . "', delivery_suburb = '" . addslashes($this->reqObj->request['shipping_address_2']) . "', delivery_city = '" . addslashes($this->reqObj->request['shipping_city']) . "', delivery_postcode = '" . addslashes($_REQUEST['shipping_postcode']) . "', delivery_country = '" . addslashes($shipping_country) . "', delivery_country_id = '" . (int)$_REQUEST['shipping_country_id'] . "', delivery_zone = '" . addslashes($shipping_zone) . "', delivery_zone_id = '" . (int)$_REQUEST['shipping_zone_id'] . "', delivery_address_format_id = '" . addslashes($shipping_address_format) . "', shipping_method = '" . addslashes($this->reqObj->request['shipping_method']) . "',  orders_status = '" . (int)$_REQUEST['order_status_id'] . "', affiliate_id  = '" . (int)$_REQUEST['affiliate_id'] . "', language_id = '1', currency_id = '" . (int)$cur_rows['currencies_id'] . "', currency = '" . addslashes(DEFAULT_CURRENCY) . "', currency_value = '" . (float)$cur_rows['value'] . "', date_purchased = '".$this->_date."', last_modified = '".$this->_date."'");
						
						$order_id = $action->db->lastInsertId();
						
						if (isset($_REQUEST['order_product'])) {		
							foreach ($_REQUEST['order_product'] as $order_product) {	
								$action->db->query("INSERT INTO r_orders_products SET orders_id = '" . (int)$order_id . "', products_id = '" . (int)$order_product['product_id'] . "', products_name = '" . addslashes($order_product['name']) . "', products_model = '" . addslashes($order_product['model']) . "', products_quantity = '" . (int)$order_product['quantity'] . "', products_price = '" . (float)$order_product['price'] . "', final_price = '" . (float)$order_product['total'] . "', products_tax = '" . (float)$order_product['tax'] . "'");
								
								$order_product_id = $action->db->lastInsertId();
								
								if (isset($order_product['order_option'])) {
									foreach ($order_product['order_option'] as $order_option) {
										$action->db->query("INSERT INTO r_orders_products_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$order_option['product_option_id'] . "', product_option_value_id = '" . (int)$order_option['product_option_value_id'] . "', name = '" . addslashes($order_option['name']) . "', `value` = '" . addslashes($order_option['value']) . "', `type` = '" . addslashes($order_option['type']) . "'");
									}
								}
								
								if (isset($order_product['order_download'])) {
									foreach ($order_product['order_download'] as $order_download) {
										$action->db->query("INSERT INTO r_orders_products_download SET orders_id = '" . (int)$order_id . "', orders_products_id = '" . (int)$order_product_id . "', name = '" . addslashes($order_download['name']) . "', orders_products_filename = '" . addslashes($order_download['filename']) . "', mask = '" . addslashes($order_download['mask']) . "', remaining = '" . (int)$order_download['remaining'] . "'");
									}
								}
							}
						}
						
						
						// Get the total
						$total = 0;
						
						if (isset($_REQUEST['order_total'])) {		
							foreach ($_REQUEST['order_total'] as $order_total) {	
								$action->db->query("INSERT INTO r_orders_total SET orders_id = '" . (int)$order_id . "', class = '" . addslashes($order_total['code']) . "', title = '" . addslashes($order_total['title']) . "', text = '" . addslashes($order_total['text']) . "', `value` = '" . (float)$order_total['value'] . "', sort_order = '" . (int)$order_total['sort_order'] . "'");
							}
							
							$total += $order_total['value'];
						}
						
						// Affiliate
						$affiliate_id = 0;
						$commission = 0;
						
						if (!empty($_REQUEST['affiliate_id'])) {
							
							$affiliate_info = $act_ext->db->fetchRow("select * from r_affiliate where affiliate_id='".(int)$_REQUEST['affiliate_id']."'");
							
							if ($affiliate_info) {
								$affiliate_id = $affiliate_info['affiliate_id']; 
								$commission = ($total / 100) * $affiliate_info['commission']; 
							}
						}
						
						// Update order total			 
						$action->db->query("UPDATE `r_orders` SET total = '" . (float)$total . "', affiliate_id = '" . (int)$affiliate_id . "', commission = '" . (float)$commission . "' WHERE orders_id = '" . (int)$order_id . "'"); 
						
						$action->db->query("insert into r_orders_status_history set orders_id='".(int)$order_id."',orders_status_id='".(int)$_REQUEST['order_status_id']."',date_added='".$this->_date."',customer_notified='1',comments='".addslashes($_REQUEST['comment'])."'");
						//end add order
						//exit;
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						$this->sendOrderMail($order_id);   
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode('New Order Placed Successfully!!'));
						
					}
					
					$this->renderScript('admin/orders-form.phtml');
					break;
				}
			}
		}
        
        public function sendOrderMail($order_id)
        {
			// HTML Mail
			$act_ext=new Model_Adminextaction();
			$currObj=new Model_currencies();
			$chkordObj=new Model_CheckoutOrder();
			$order_info = $chkordObj->getOrder($order_id);
			$order_status_query = $act_ext->db->query("SELECT * FROM r_orders_status WHERE orders_status_id = '" . (int)$order_status_id . "' AND language_id = '1'");
			$order_status_query_row=$order_status_query->fetch();
			if ($order_status_query->rowCount()) {
				$order_status = $order_status_query_row['orders_status_name'];
                } else {
				$order_status = '';
			}
			
			$order_product_query = $act_ext->db->query("SELECT * FROM r_orders_products WHERE orders_id = '" . (int)$order_id . "'");
			$order_total_query = $act_ext->db->query("SELECT * FROM r_orders_total WHERE orders_id = '" . (int)$order_id . "' ORDER BY sort_order ASC");
			$order_download_query = $act_ext->db->query("SELECT * FROM r_orders_products_download WHERE orders_id = '" . (int)$order_id . "'");
			
			
			$efObj=new Model_DbTable_rEmailFormat();
			$template=$efObj->getEmailFormat('3',(int)$_SESSION['Lang']['language_id']);
			$title = sprintf($template['subject'], html_entity_decode(@constant('STORE_OWNER'), ENT_QUOTES, 'UTF-8'	), $order_id);
			
			//$template->data['text_greeting'] = sprintf($language->get('text_new_greeting'), html_entity_decode(@constant('STORE_OWNER'), ENT_QUOTES, 'UTF-8'));
			
			$logo = HTTP_SERVER.'public/uploads/image/'.STORE_LOGO;
			$store_name = @constant('STORE_OWNER');
			$store_url = @constant('HTTP_SERVER');
			$customer_id = $_REQUEST['customer_id'];
			$link = HTTP_SERVER.'account/orderinfo/order_id/' . $order_id;
			
			if ($order_download_query->rowCount()) {
				$download = HTTP_SERVER.'account/download';//$order_info['store_url'] . 'index.php?route=account/download';
                } else {
				$download = '';
			}
			
			$invoice_no = $invoice_no;
			$order_id = $order_id;
			
			$date_added = date('d/m/Y', strtotime($order_info['date_added']));
			$payment_method = $order_info['payment_method'];
			$shipping_method = $order_info['shipping_method'];
			$email = $order_info['email'];
			$telephone = $order_info['telephone'];
			$ip = $order_info['ip'];
			
			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
                } else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}
			
			$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
			);
			
			$replace = array(
			'firstname' => $order_info['shipping_name'],
			'lastname'  => '',
			'company'   => $order_info['shipping_company'],
			'address_1' => $order_info['shipping_address_1'],
			'address_2' => $order_info['shipping_address_2'],
			'city'      => $order_info['shipping_city'],
			'postcode'  => $order_info['shipping_postcode'],
			'zone'      => $order_info['shipping_zone'],
			'zone_code' => $order_info['shipping_zone_code'],
			'country'   => $order_info['shipping_country']
			);
			
			$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
			
			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
                } else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}
			
			$find = array(
			'{firstname}',
			'{lastname}',
			'{company}',
			'{address_1}',
			'{address_2}',
			'{city}',
			'{postcode}',
			'{zone}',
			'{zone_code}',
			'{country}'
			);
			
			$replace = array(
			'firstname' => $order_info['payment_name'],
			'lastname'  => '',
			'company'   => $order_info['payment_company'],
			'address_1' => $order_info['payment_address_1'],
			'address_2' => $order_info['payment_address_2'],
			'city'      => $order_info['payment_city'],
			'postcode'  => $order_info['payment_postcode'],
			'zone'      => $order_info['payment_zone'],
			'zone_code' => $order_info['payment_zone_code'],
			'country'   => $order_info['payment_country']
			);
			
			$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));
			
			$products = array();
			
			$order_product_query_rows=$order_product_query->fetchAll();
			foreach ($order_product_query_rows as $product) {
				$option_data = array();
				
				$order_option_query = $act_ext->db->query("SELECT * FROM r_orders_products_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['orders_products_id'] . "'");
				//echo "SELECT * FROM r_orders_products_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'<br/>";
				$order_option_query_rows=$order_option_query->fetchAll();
				foreach ($order_option_query_rows as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
						'name'  => $option['name'],
						'value' => (strlen($option['value']) > 20 ? substr($option['value'], 0, 20) . '..' : $option['value'])
						);
						} else {
						$filename = substr($option['value'], 0, strrpos($option['value'], '.'));
						
						$option_data[] = array(
						'name'  => $option['name'],
						'value' => (strlen($filename) > 20 ? substr($filename, 0, 20) . '..' : $filename)
						);
					}
				}
				
				
				//echo $product['products_price']." ".$order_info['currency_code']." ".$order_info['currency_value']."<br/>";
				$products[] = array(
				'name'     => $product['products_name'],
				'model'    => $product['products_model'],
				'option'   => $option_data,
				'quantity' => $product['products_quantity'],
				'price'    => $currObj->format($product['products_price'], true, $order_info['currency_code'], $order_info['currency_value']),
				'total'    =>$currObj->format($product['final_price'], true, $order_info['currency_code'], $order_info['currency_value'])
				
				);
			}
			
			//$template->data['totals'] = $order_total_query->rows;
			$totals = $order_total_query_rows;
			
			/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/order.tpl')) {
				$html = $template->fetch($this->config->get('config_template') . '/template/mail/order.tpl');
                } else {
				$html = $template->fetch(SITE_DEFAULT_TEMPLATE.'/template/mail/order.tpl');
			}*/
			
			
			ob_start();
			include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/mail/order-'.$_SESSION['Lang']['language_code'].'.phtml';
			$html = ob_get_contents();
			ob_end_clean();
			//print_r($template);
			//echo $html;
			//exit;
			$act_ext=new Model_Adminextaction();
			$email=$act_ext->getEmailContent(array('lang'=>$_SESSION['Lang']['language_id'],'id'=>'3'));
			//start conversion
			$html=str_replace('%order_id%',$order_id,$html);
			$subject=str_replace('%order_id%',$order_id,$email['subject']);
			
			$html=str_replace('%order_status%',$order_status,$html);
			$subject=str_replace('%order_status%',$order_status,$subject);
			
			$html=str_replace('%customer_name%',$order_info['customers_name'],$html);
			$subject=str_replace('%customer_name%',$order_info['customers_name'],$subject);
			//end conversion
			
			$array_mail=array('to'=>array('name'=>$order_info['customers_name'],'email'=>trim($order_info['email'])),'html'=>array('content'=>$html),'subject'=>$subject);
			
			/*echo "in mail";
                echo "<pre>";
                print_r($array_mail);
                echo "</pre>";
			exit;*/
			$mailObj=new Model_Mail();
			$mailObj->sendMail($array_mail);
		}
		
        public function productsViewedAction(){
			//$this->checkSession($_SESSION['admin_id']);
			$this->checkpermission();
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$action=new Model_Adminaction();
			
			if($_REQUEST['type']=='')
			{
				
				//$count=$action->db->fetchRow("SELECT sum( products_viewed ) AS sum, count( products_viewed ) AS count FROM r_products_description 			WHERE products_viewed != '0'");
				
				$count=$action->db->fetchRow("SELECT sum( viewed ) AS sum, count( viewed ) AS count FROM r_products	WHERE viewed != '0' and del=0");
				$total_count = $count[count];
				//$total_sum = $count[sum];
				$total_sum = $count[sum]==""?0:$count[sum];
				
				//echo "value of ".$total_sum;     
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'percent':$_REQUEST['sortby'];
				/*$select =$action->db->fetchAll("select l.name,l.image,p.products_name,p.products_viewed,round((p.products_viewed/".$total_sum.")*100,2) as percent
					from r_products_description p,r_languages l where p.products_viewed!='0' and p.language_id=l.languages_id order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);*/
				
				//$select=$action->db->fetchAll("select d.products_name,p.viewed,p.products_id,round((p.viewed/".$total_sum.")*100,2) as percent	from r_products p left join r_products_description pd on p.products_id=pd.products_id  and p.viewed!='0' and pd.language_id ='1' order by ".$sortby." ".$this->disType. "limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				$select=$action->db->fetchAll("select pd.products_name,p.viewed,p.products_id,round((p.viewed/".$total_sum.")*100,2) as percent from r_products p, r_products_description pd where p.products_id=pd.products_id and pd.language_id ='1' and p.viewed!=0 ORDER BY  p.viewed desc	limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				$this->view->results=$select;
			}
		}
		
		public function salesReportAction(){
			
			$this->checkSession($_SESSION['admin_id']);
			//$this->checkpermission();
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$action=new Model_Adminaction();
			$f=$action->db->fetchRow("select min(date(date_purchased)) as min,max(date(date_purchased)) as max from r_orders");
			if($_REQUEST[date_start]=='' || $_REQUEST[date_start]=='Start Date')
			{
				$this->view->date_start=$f[min];
			}else
			{
				$this->view->date_start=$_REQUEST['date_start'];
			}
			
			if($_REQUEST[date_end]==''  || $_REQUEST[date_end]=='End Date')
			{
				$this->view->date_end=$f[max];
			}else
			{
				$this->view->date_end=$_REQUEST['date_end'];
			}
			
			if($_REQUEST[status]=='' || $_REQUEST[status]=='-1')
			{
				$s=$action->db->fetchAll("select orders_status_id from r_orders_status where language_id='1'");
				foreach($s as $k)
				{
					$stat_link=$stat_link.$pre.$k['orders_status_id'];
					$pre=',';
				}
				$this->view->status=$stat_link;
			}else
			{
				$this->view->status=$_REQUEST['status'];
			}
			
			$this->view->group=$_REQUEST[status]==''?'year':$_REQUEST[group];
			
			if($_REQUEST['type']=='')
			{
				
				$count=$action->db->fetchAll("SELECT count(orders_id) AS count from r_orders WHERE date(date_purchased) >=
				'".$this->view->date_start."' AND date(date_purchased)<='".$this->view->date_end."' and orders_status in (".$this->view->status.") GROUP BY ".$this->view->group."(date_purchased)");
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'date_purchased':$_REQUEST['sortby'];
				
				$select =$action->db->fetchAll("SELECT count(orders_id) AS count, round(sum(total),2) as total,date_purchased,max(date_purchased) as end_date FROM r_orders WHERE invoice_id!='0' and
				date(date_purchased) >= '".addslashes($this->view->date_start)."' AND date(date_purchased)<='".addslashes($this->view->date_end)."' and orders_status in (".$this->view->status.")
				GROUP BY ".$this->view->group."(date_purchased) order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				//echo "SELECT count(orders_id) AS count, round(sum(total),2) as total,date_purchased,max(date_purchased) as end_date FROM r_orders WHERE invoice_id!='0' and	date(date_purchased) >= '".$this->view->date_start."' AND date(date_purchased)<='".$this->view->date_end."' and orders_status in (".$this->view->status.") GROUP BY ".$this->view->group."(date_purchased) order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT;
				
				/*echo "SELECT count(orders_id) AS count, round(sum(total),2) as total,date_purchased,max(date_purchased) as end_date FROM r_orders WHERE invoice_id!='0' and date_purchased BETWEEN '".$this->view->date_start."' AND '".$this->view->date_end."' and orders_status in (".$this->view->status.") GROUP BY ".$this->view->group."(date_purchased) order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT;*/
				$this->view->results=$select;
				/*echo "<pre>";
					print_r($select);
				echo "<pre>";*/
			}
		}
		
		public function affiliateReportAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$action=new Model_Adminaction();
			
			$sql1 = "SELECT COUNT(DISTINCT affiliate_id) AS total FROM `r_affiliate_transaction`";
			
			$sql2 = "SELECT at.affiliate_id, CONCAT(a.firstname, ' ', a.lastname) AS affiliate, a.email, a.status, SUM(at.amount) AS commission, COUNT(o.orders_id) AS orders, SUM(o.total) AS total FROM r_affiliate_transaction at LEFT JOIN `r_affiliate` a ON (at.affiliate_id = a.affiliate_id) LEFT JOIN `r_orders` o ON (at.order_id = o.orders_id)";
			
			$implode2 = array();
			$implode1 = array();
			
			if (isset($_REQUEST['date_start']) && $this->_getParam('date_start')!="Start Date") {
				$implode1[] = "DATE(date_added) >= '" . $this->_getParam('date_start') . "'";
				$implode2[] = "DATE(at.date_added) >= '" . $this->_getParam('date_start') . "'";
			}
			
			if (isset($_REQUEST['date_end']) && $this->_getParam('date_end')!="End Date") {
				$implode1[] = "DATE(date_added) <= '" . $this->_getParam('date_end') . "'";
				$implode2[] = "DATE(at.date_added) <= '" . $this->_getParam('date_end') . "'";
			}
			if ($implode1) {
				$sql1 .= " WHERE " . implode(" AND ", $implode1);
			}
			//echo $sql1;
			//exit;
			if ($implode2) {
				$sql2 .= " WHERE " . implode(" AND ", $implode2);
			}
			
			if($_REQUEST['type']=='')
			{
				
				$count=$action->db->fetchRow($sql1);
				$total_count = $count[total];
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'at.date_added':$_REQUEST['sortby'];
				
				$select =$action->db->fetchAll($sql2."GROUP BY at.affiliate_id ORDER BY commission DESC
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}
		}
		
		public function taxReportAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$action=new Model_Adminaction();
			
			$sql1 = "SELECT COUNT(*) AS total FROM (SELECT COUNT(*) AS total FROM `r_orders_total` ot LEFT JOIN `r_orders` o ON (ot.orders_id = o.orders_id) WHERE ot.class = 'tax' and o.invoice_id!='0'";
			
			$sql2 = "SELECT MIN(o.date_purchased) AS date_start, MAX(o.date_purchased) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.orders_id) AS `orders` FROM `r_orders_total` ot LEFT JOIN `r_orders` o ON (ot.orders_id = o.orders_id) WHERE ot.class = 'tax'  and o.invoice_id!='0'";
			
			
			if (isset($_REQUEST['status']) && $this->_getParam('status')) {
				$sql1 .= " AND orders_status = '" . (int)$this->_getParam('status') . "'";
				$sql2 .= " AND orders_status = '" . (int)$this->_getParam('status') . "'";
				} else {
				$sql1 .= " AND orders_status > '0'";
				$sql2 .= " AND o.orders_status > '0'";
			}
			
			if (isset($_REQUEST['date_start']) && $this->_getParam('date_start')!="Start Date") {
				$sql1 .= " AND DATE(date_purchased) >= '" . $this->_getParam('date_start') . "'";
				$sql2 .= " AND DATE(o.date_purchased) >= '" . $this->_getParam('date_start') . "'";
			}
			
			if (isset($_REQUEST['date_end']) && $this->_getParam('date_end')!="End Date") {
				$sql1 .= " AND DATE(date_purchased) <= '" . $this->_getParam('date_end') . "'";
				$sql2 .= " AND DATE(o.date_purchased) <= '" . $this->_getParam('date_end') . "'";
			}
			
			if (isset($_REQUEST['group'])) {
				$group = $this->_getParam('group');
				
				} else {
				$group = 'week';
			}
			
			switch($group) {
				case 'day';
				$sql1 .= " GROUP BY DAY(o.date_purchased), ot.title";
				$sql2 .= " GROUP BY ot.title, DAY(o.date_purchased)";
				break;
				default:
				case 'week':
				$sql1 .= " GROUP BY WEEK(o.date_purchased), ot.title";
				$sql2 .= " GROUP BY ot.title, WEEK(o.date_purchased)";
				break;
				case 'month':
				$sql1 .= " GROUP BY MONTH(o.date_purchased), ot.title";
				$sql2 .= " GROUP BY ot.title, MONTH(o.date_purchased)";
				break;
				case 'year':
				$sql1 .= " GROUP BY YEAR(o.date_purchased), ot.title";
				$sql2 .= " GROUP BY ot.title, YEAR(o.date_purchased)";
				break;
			}
			
			$this->view->data=array('date_start'=>$this->_getParam('date_start'), 'date_end'=>$this->_getParam('date_end'), 'status'=>$this->_getParam('status'), 'group'=>$group);
			$sql1 .= ") tmp";
			
			if($_REQUEST['type']=='')
			{
				$count=$action->db->fetchRow($sql1);
				$total_count = $count[total];
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'date_purchased':$_REQUEST['sortby'];
				$select =$action->db->fetchAll($sql2." order by ".$sortby." ".$this->disType. "	limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}
		}
		
		public function returnsReportAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$action=new Model_Adminaction();
			
			/*$sql2 = "SELECT MIN(tmp.date_added) AS date_start, MAX(tmp.date_added) AS date_end, COUNT(tmp.return_id) AS `returns`, SUM(tmp.products) AS products FROM (SELECT r.return_id, (SELECT SUM(rp.quantity) FROM `r_return_product` rp WHERE rp.return_id = r.return_id) AS products, r.date_added FROM `r_return` r";*/
			
			$sql2 = "SELECT MIN(r.date_added) AS date_start, MAX(r.date_added) AS date_end, COUNT(r.return_id) AS `returns` FROM `r_return` r";
			
			
			if (isset($_REQUEST['group'])) {
				$group = $this->_getParam('group');
				
				} else {
				$group = 'week';
			}
			
			switch($group) {
				case 'day';
				$sql1 = "SELECT COUNT(DISTINCT DAY(date_added)) AS total FROM `r_return`";
				//$group2 .= " GROUP BY DAY(tmp.date_added)";
				$group2 .= " GROUP BY DAY(r.date_added)";
				break;
				default:
				case 'week':
				$sql1 = "SELECT COUNT(DISTINCT WEEK(date_added)) AS total FROM `r_return`";
				$group2 .= " GROUP BY WEEK(r.date_added)";
				break;
				case 'month':
				$sql1 = "SELECT COUNT(DISTINCT MONTH(date_added)) AS total FROM `r_return`";
				$group2 .= " GROUP BY MONTH(r.date_added)";
				break;
				case 'year':
				$sql1 = "SELECT COUNT(DISTINCT YEAR(date_added)) AS total FROM `r_return`";
				$group2 .= " GROUP BY YEAR(r.date_added)";
				break;
			}
			
			if (isset($_REQUEST['status']) && $this->_getParam('status')) {
				$sql1 .= " WHERE return_status_id = '" . (int)$this->_getParam('status') . "'";
				$sql2 .= " WHERE r.return_status_id = '" . (int)$this->_getParam('status') . "'";
				} else {
				$sql1 .= " WHERE return_status_id > '0'";
				$sql2 .= " WHERE r.return_status_id > '0'";
			}
			
			if (isset($_REQUEST['date_start']) && $this->_getParam('date_start')!="Start Date") {
				$sql1 .= " AND DATE(date_added) >= '" . $this->_getParam('date_start') . "'";
				$sql2 .= " AND DATE(r.date_added) >= '" . $this->_getParam('date_start') . "'";
			}
			
			if (isset($_REQUEST['date_end']) && $this->_getParam('date_end')!="End Date") {
				$sql1 .= " AND DATE(date_added) <= '" . $this->_getParam('date_end') . "'";
				$sql2 .= " AND DATE(r.date_added) <= '" . $this->_getParam('date_end') . "'";
			}
			$this->view->data=array('date_start'=>$this->_getParam('date_start'), 'date_end'=>$this->_getParam('date_end'), 'status'=>$this->_getParam('status'), 'group'=>$group);
			
			
			$sql2.=$group2;
			//echo $sql2;
			//exit;
			
			if($_REQUEST['type']=='')
			{
				$count=$action->db->fetchRow($sql1);
				$total_count = $count[total];
				//echo $total_count."count";
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'date_added':$_REQUEST['sortby'];
				
				$select =$action->db->fetchAll($sql2." order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}
			//echo $sql1."<br/>".$sql2;
		}
		
		public function couponsReportAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$action=new Model_Adminaction();
			
			
			//$sql1 = "SELECT COUNT(DISTINCT c.coupon_id) AS total,o.date_purchased FROM `r_discount_coupons_to_orders` c,`r_orders` o";
			$sql1 = "SELECT COUNT(DISTINCT coupon_id) AS total FROM `r_coupon_history`";
			
			/*$sql2 = "SELECT ch.coupons_id, c.title, c.coupons_id,o.date_purchased, COUNT(DISTINCT ch.orders_id) AS `orders` FROM `r_discount_coupons_to_orders` ch LEFT JOIN `r_discount_coupons` c ON (ch.coupons_id = c.coupons_id) LEFT JOIN `r_orders` o ON (ch.orders_id = o.orders_id) ";*/
			
			$sql2 = "SELECT ch.coupon_id, c.name, c.code, COUNT(DISTINCT ch.order_id) AS `r_orders`, SUM(ch.amount) AS total FROM `r_coupon_history` ch LEFT JOIN `r_coupon` c ON (ch.coupon_id = c.coupon_id)";
			
			
			$implode1 = array();
			
			$implode2 = array();
			if (isset($_REQUEST['date_start']) && $this->_getParam('date_start')!="Start Date") {
				$implode1[]= "DATE(date_added) >= '" . $this->_getParam('date_start') . "'";
				
				$implode2[]= "DATE(c.date_added) >= '" . $this->_getParam('date_start') . "'";
			}
			
			if (isset($_REQUEST['date_end']) && $this->_getParam('date_end')!="End Date") {
				$implode1[]= "DATE(date_added) <= '" . $this->_getParam('date_end') . "'";
				$implode2[]= "DATE(c.date_added) <= '" . $this->_getParam('date_end') . "'";
			}
			//$implode1[]="c.orders_id=o.orders_id";
			if ($implode1) {
				$sql1 .= " WHERE " . implode(" AND ", $implode1);
			}
			
			if ($implode2) {
				$sql2 .= " WHERE " . implode(" AND ", $implode2);
			}
			
			$sql2 .= " GROUP BY ch.coupon_id ORDER BY total DESC";
			
			
			$this->view->data=array('date_start'=>$this->_getParam('date_start'), 'date_end'=>$this->_getParam('date_end'));
			
			
			//echo $sql1."<br/> ".$sql2;
			//exit;
			if($_REQUEST['type']=='')
			{
				
				$count=$action->db->fetchRow($sql1);
				$total_count = $count[total];
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				
				$select =$action->db->fetchAll($sql2."	limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
				
				
				
			}
			
			
		}
		
		public function productsDownloadAction(){
			
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$action=new Model_Adminaction();
			
			$f=$action->db->fetchRow("select min(date(date_purchased)) as min,max(date(date_purchased)) as max from r_orders");
			
			if($this->_getParam(date_start)=='')
			{
				$this->view->date_start=$f[min];
			}else
			{
				$this->view->date_start=$this->_getParam('date_start');
			}
			
			if($this->_getParam(date_end)=='')
			{
				$this->view->date_end=$f[max];
			}else
			{
				$this->view->date_end=$this->_getParam('date_end');
			}
			
			if($this->_getParam(status)=='' || $this->_getParam(status)=='-1')
			{
				$s=$action->db->fetchAll("select orders_status_id from r_orders_status where language_id='1'");
				foreach($s as $k)
				{
					$stat_link=$stat_link.$pre.$k['orders_status_id'];
					$pre=',';
				}
				$this->view->status=$stat_link;
			}else
			{
				$this->view->status=$this->_getParam('status');
			}
			
			$this->view->group=$this->_getParam(status)==''?'year':$this->_getParam(group);
			
			if($this->_getParam('type')=='')
			{
				
				$count=$action->db->fetchAll("SELECT count(orders_id) AS count from r_orders WHERE date_purchased BETWEEN
				'".$this->view->date_start."' AND '".$this->view->date_end."' and orders_status in ('".$this->view->status."') GROUP BY ".$this->view->group."(date_purchased)");
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'date_purchased':$this->_getParam('sortby');
				
				$select =$action->db->fetchAll("SELECT count(orders_id) AS count, round(sum(total),2) as total,date_purchased,max(date_purchased) as end_date FROM r_orders WHERE
				date_purchased BETWEEN '".$this->view->date_start."' AND '".$this->view->date_end."' and orders_status in ('".$this->view->status."')
				GROUP BY ".$this->view->group."(date_purchased) order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}
		}
		
		public function customersByOrderTotalReportAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$action=new Model_Adminaction();
			
			$sql1 = "SELECT COUNT(DISTINCT o.customers_id) AS total FROM `r_orders` o WHERE o.customers_id > '0'";
			
			/*$sql2 = "SELECT tmp.customers_id, tmp.customer, tmp.email, tmp.customer_group, tmp.status, COUNT(tmp.orders_id) AS orders, SUM(tmp.products) AS products, SUM(tmp.total) AS total FROM (SELECT o.orders_id, c.customers_id, o.customers_name AS customer, o.customers_email_address as email, cg.name AS customer_group, c.customers_status as status, (SELECT SUM(op.products_quantity) FROM `r_orders_products` op WHERE op.orders_id = o.orders_id GROUP BY op.orders_id) AS products, o.total FROM `r_orders` o LEFT JOIN `r_customers` c ON (o.customers_id > '0' AND o.customers_id = c.customers_id) LEFT JOIN r_customer_group cg ON (c.customer_group_id = cg.customer_group_id)"; commented on april 30 2012*/
			
			$sql2 = "SELECT tmp.customers_id, tmp.customer, tmp.email, tmp.customer_group, tmp.status, COUNT(tmp.orders_id) AS orders, SUM(tmp.products) AS products, SUM(tmp.total) AS total FROM (SELECT o.orders_id, c.customers_id, o.customers_name AS customer, o.customers_email_address as email, cg.name AS customer_group, c.customers_status as status, (SELECT SUM(op.products_quantity) FROM `r_orders_products` op WHERE op.orders_id = o.orders_id GROUP BY op.orders_id) AS products, o.total FROM `r_orders` o LEFT JOIN `r_customers` c ON (o.customers_id = c.customers_id) LEFT JOIN r_customer_group cg ON (c.customer_group_id = cg.customer_group_id) where o.customers_id > '0'";
			
			if (isset($_REQUEST['status']) && $this->_getParam('status')) {
				$sql1 .= " AND o.orders_status = '" . (int)$this->_getParam('status') . "'";
				$sql2 .= " AND o.orders_status = '" . (int)$this->_getParam('status') . "'";
				} else {
				$sql1 .= " AND orders_status > '0'";
				$sql2 .= " AND o.orders_status > '0'";
			}
			
			if (isset($_REQUEST['date_start']) && $this->_getParam('date_start')!="Start Date") {
				$sql1 .= " AND DATE(o.date_purchased) >= '" . $this->_getParam('date_start') . "'";
				$sql2 .= " AND DATE(o.date_purchased) >= '" . $this->_getParam('date_start') . "'";
			}
			
			if (isset($_REQUEST['date_end']) && $this->_getParam('date_end')!="End Date") {
				$sql1 .= " AND DATE(o.date_purchased) <= '" . $this->_getParam('date_end') . "'";
				$sql2 .= " AND DATE(o.date_purchased) <= '" . $this->_getParam('date_end') . "'";
			}
			$sql2 .= ") tmp GROUP BY tmp.customers_id ORDER BY total DESC";
			
			$this->view->data=array('date_start'=>$this->_getParam('date_start'), 'date_end'=>$this->_getParam('date_end'), 'status'=>$this->_getParam('status'));
			
			
			//echo $sql1."<br/> ".$sql2;
			//exit;
			if($_REQUEST['type']=='')
			{
				$count=$action->db->fetchRow($sql1);
				$total_count = $count[total];
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				
				$select =$action->db->fetchAll($sql2. "	limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				$this->view->results=$select;
			}
		}
		
		public function searchTermsReportAction(){
			
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$action=new Model_Adminaction();
			
			if($_REQUEST['type']=='')
			{       
				if(sizeof($_REQUEST['rid'])>0)
				{       
					$action->RecordDelete('r_search_keywords','search_keywords_id',$_REQUEST[rid]); 
				}
				
				$count=$action->db->fetchRow("select count(*) as count from r_search_keywords");
				$total_count = $count['count'];
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'hits':$_REQUEST['sortby'];
				
				$select =$action->db->fetchAll("select * from r_search_keywords order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				/*echo "SELECT count(orders_id) AS count, sum(total) as total,date_purchased,max(date_purchased) as end_date FROM r_orders WHERE
					date_purchased BETWEEN '".$_REQUEST['date_start']."' AND '".$_REQUEST['date_end']."' and orders_status in ('".$_REQUEST['status']."')
					GROUP BY ".$_REQUEST['group']."(date_purchased) order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT;*/
				$this->view->results=$select;
			}
		}
		
		public function shippingReportAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$action=new Model_Adminaction();
			
			$sql1 = "SELECT COUNT(*) AS total FROM (SELECT COUNT(*) AS total FROM `r_orders_total` ot LEFT JOIN `r_orders` o ON (ot.orders_id = o.orders_id)  WHERE ot.class = 'shipping' and o.invoice_id!='0'";
			
			$sql2 = "SELECT MIN(o.date_purchased) AS date_start, MAX(o.date_purchased) AS date_end, ot.title, SUM(ot.value) AS total, COUNT(o.orders_id) AS `orders` FROM `r_orders_total` ot LEFT JOIN `r_orders` o ON (ot.orders_id = o.orders_id) WHERE ot.class = 'shipping' and o.invoice_id!='0'";
			
			
			if (isset($_REQUEST['status']) && $this->_getParam('status')) {
				$sql1 .= " AND orders_status = '" . (int)$this->_getParam('status') . "'";
				$sql2 .= " AND orders_status = '" . (int)$this->_getParam('status') . "'";
				} else {
				$sql1 .= " AND orders_status > '0'";
				$sql2 .= " AND o.orders_status > '0'";
			}
			
			if (isset($_REQUEST['date_start']) && $this->_getParam('date_start')!="Start Date") {
				$sql1 .= " AND DATE(date_purchased) >= '" . $this->_getParam('date_start') . "'";
				$sql2 .= " AND DATE(o.date_purchased) >= '" . $this->_getParam('date_start') . "'";
			}
			
			if (isset($_REQUEST['date_end']) && $this->_getParam('date_end')!="End Date") {
				$sql1 .= " AND DATE(date_purchased) <= '" . $this->_getParam('date_end') . "'";
				$sql2 .= " AND DATE(o.date_purchased) <= '" . $this->_getParam('date_end') . "'";
			}
			
			if (isset($_REQUEST['group'])) {
				$group = $this->_getParam('group');
				
				} else {
				$group = 'week';
			}
			
			switch($group) {
				case 'day';
				$sql1 .= " GROUP BY DAY(o.date_purchased), ot.title";
				$sql2 .= " GROUP BY ot.title, DAY(o.date_purchased)";
				break;
				default:
				case 'week':
				$sql1 .= " GROUP BY WEEK(o.date_purchased), ot.title";
				$sql2 .= " GROUP BY ot.title, WEEK(o.date_purchased)";
				break;
				case 'month':
				$sql1 .= " GROUP BY MONTH(o.date_purchased), ot.title";
				$sql2 .= " GROUP BY ot.title, MONTH(o.date_purchased)";
				break;
				case 'year':
				$sql1 .= " GROUP BY YEAR(o.date_purchased), ot.title";
				$sql2 .= " GROUP BY ot.title, YEAR(o.date_purchased)";
				break;
			}
			
			$this->view->data=array('date_start'=>$this->_getParam('date_start'), 'date_end'=>$this->_getParam('date_end'), 'status'=>$this->_getParam('status'), 'group'=>$group);
			$sql1 .= ") tmp";
			
			//	echo $sql1."<br/> ".$sql2;
			//exit;
			if($_REQUEST['type']=='')
			{
				
				$count=$action->db->fetchRow($sql1);
				$total_count = $count[total];
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'date_purchased':$_REQUEST['sortby'];
				
				$select =$action->db->fetchAll($sql2." order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}
		}
		
		public function productReviewsReportAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$action=new Model_Adminaction();
			
			//$sql1 = "select count(distinct r.products_id) as total from r_reviews r where reviews_rating!='0'";
			
			$sql1 = "select count(distinct r.products_id) as total from r_reviews r,r_products p where reviews_rating!='0' and r.products_id=p.products_id";
			
			$sql2 = "select rd.products_name, sum(r.reviews_rating) as total,r.products_id from r_reviews r,r_products_description rd where r.products_id=rd.products_id and rd.language_id='1' and r.reviews_rating!='0'";
			
			
			$implode1 = array();
			$implode2 = array();
			if (isset($_REQUEST['date_start']) && $this->_getParam('date_start')) {
				$implode1[]= "DATE(r.date_added) >= '" . $this->_getParam('date_start') . "'";
				$implode2[]= "DATE(r.date_added) >= '" . $this->_getParam('date_start') . "'";
			}
			
			if (isset($_REQUEST['date_end']) && $this->_getParam('date_end')) {
				$implode1[]= "DATE(r.date_added) <= '" . $this->_getParam('date_end') . "'";
				$implode2[]= "DATE(r.date_added) <= '" . $this->_getParam('date_end') . "'";
			}
			if ($implode1) {
				$sql1 .= " And " . implode(" AND ", $implode1);
			}
			
			if ($implode2) {
				$sql2 .= 'And '.implode(" AND ", $implode2);
			}
			
			$sql2 .= " GROUP BY rd.products_name ORDER BY rd.products_name DESC";
			$this->view->data=array('date_start'=>$this->_getParam('date_start'), 'date_end'=>$this->_getParam('date_end'));
			//echo $sql1."<br/> ".$sql2;
			//exit;
			if($_REQUEST['type']=='')
			{
				
				$count=$action->db->fetchRow($sql1);
				$total_count = $count[total];
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				
				$select =$action->db->fetchAll($sql2."	limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}
		}
		
		public function productsDownloadReportAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$action=new Model_Adminaction();
			
			//$sql1 = "select count(distinct rp.products_id) as total from r_orders r,r_orders_products_download rd,r_orders_products rp where r.invoice_id!='0' and r.orders_id=rd.orders_id and rd.orders_products_id=rp.orders_products_id";
			
			$sql1="select count(distinct rp.products_id) as total from r_orders r,r_orders_products_download rd,r_orders_products rp,r_products p where r.invoice_id!='0' and r.orders_id=rd.orders_id and rd.orders_products_id=rp.orders_products_id and rp.orders_products_id=p.products_id";
			
			$sql2 = "select p.products_name, count(rd.orders_products_id) as total,p.products_id from r_orders r,r_orders_products_download rd,r_products_description p,r_orders_products rp where r.invoice_id!='0' and r.orders_id=rd.orders_id and rd.orders_products_id=rp.orders_products_id and rp.products_id=p.products_id and p.language_id='1'";
			
			
			$implode1 = array();
			$implode2 = array();
			if (isset($_REQUEST['date_start']) && $this->_getParam('date_start')!="Start Date") {
				$implode1[]= "DATE(r.date_purchased) >= '" . $this->_getParam('date_start') . "'";
				$implode2[]= "DATE(r.date_purchased) >= '" . $this->_getParam('date_start') . "'";
			}
			
			if (isset($_REQUEST['date_end']) && $this->_getParam('date_end')!="End Date") {
				$implode1[]= "DATE(r.date_purchased) <= '" . $this->_getParam('date_end') . "'";
				$implode2[]= "DATE(r.date_purchased) <= '" . $this->_getParam('date_end') . "'";
			}
			if ($implode1) {
				$sql1 .= " And " . implode(" AND ", $implode1);
			}
			
			if ($implode2) {
				$sql2 .= 'And '.implode(" AND ", $implode2);
			}
			
			$sql2 .= " GROUP BY p.products_name ORDER BY p.products_name DESC";
			$this->view->data=array('date_start'=>$this->_getParam('date_start'), 'date_end'=>$this->_getParam('date_end'));
			//echo $sql1."<br/> ".$sql2;
			//exit;
			if($_REQUEST['type']=='')
			{
				
				$count=$action->db->fetchRow($sql1);
				$total_count = $count[total];
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				
				$select =$action->db->fetchAll($sql2."	limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}
		}
		
		public function rewardPointsReportAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$action=new Model_Adminaction();
			
			$sql1 = "SELECT COUNT(DISTINCT customer_id) AS total FROM `r_customer_reward` cr,r_customers c,r_orders o where cr.customer_id=c.customers_id and cr.order_id=o.orders_id";
			
			$sql2 = "SELECT cr.customer_id, CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS customer, c.customers_email_address, cg.name AS customer_group, c.customers_status, SUM(cr.points) AS points, COUNT(o.orders_id) AS orders, SUM(o.total) AS total FROM r_customer_reward cr ,`r_customers` c,r_customer_group cg,r_orders o where cr.customer_id = c.customers_id and  c.customer_group_id = cg.customer_group_id and cr.order_id = o.orders_id";
			
			/*echo "<pre>";
                print_r($_REQUEST);
                echo "</pre>";
			exit;*/
			$implode1 = array();
			$implode2 = array();
			if (isset($_REQUEST['date_start']) && $this->_getParam('date_start')!="Start Date") {
				$implode1[]= "DATE(cr.date_added) >= '" . $this->_getParam('date_start') . "'";
				$implode2[]= "DATE(cr.date_added) >= '" . $this->_getParam('date_start') . "'";
			}
			
			if (isset($_REQUEST['date_end']) && $this->_getParam('date_end')!="End Date") {
				$implode1[]= "DATE(cr.date_added) <= '" . $this->_getParam('date_end') . "'";
				$implode2[]= "DATE(cr.date_added) <= '" . $this->_getParam('date_end') . "'";
			}
			/*$sql1 .=" and ";
				if ($implode1) {
				$sql1 .= implode(" AND ", $implode1);
				}
				
				if ($implode2) {
				$sql2 .= implode(" AND ", $implode2);
			}*/
			
			if(sizeof($implode1)!=0 || sizeof($implode2)!=0)
			{
				$sql1 .=" and ";
				$sql2 .=" and ";
				
				if ($implode1) {
					$sql1 .= implode(" AND ", $implode1);
				}
				
				if ($implode2) {
					$sql2 .= implode(" AND ", $implode2);
				}
			}
			
			
			
			$sql2 .= " GROUP BY cr.customer_id ORDER BY points DESC";
			
			
			$this->view->data=array('date_start'=>$this->_getParam('date_start'), 'date_end'=>$this->_getParam('date_end'));
			
			
			//	echo $sql1."<br/> ".$sql2;
			//exit;
			if($_REQUEST['type']=='')
			{
				//echo $sql1;
				//exit;
				$count=$action->db->fetchRow($sql1);
				$total_count = $count[total];
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$select =$action->db->fetchAll($sql2."	limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				$this->view->results=$select;
			}
		}
		
		public function productsPurchasedAction(){
			$this->checkSession($_SESSION['admin_id']);
			//$this->checkpermission();
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$action=new Model_Adminaction();
			
			if($_REQUEST['type']=='')
			{
				
				//$count=$action->db->fetchRow("SELECT count(distinct products_id) as count from r_orders_products");
				$count=$action->db->fetchRow("SELECT count(distinct rp.products_id) as count from r_orders_products rp,r_orders r where r.orders_id=rp.orders_id and r.invoice_id!='0'");
				$total_count = $count[count];
				
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'products_quantity':$_REQUEST['sortby'];
				//$select =$action->db->fetchAll("SELECT products_model, products_name, sum( products_quantity ) AS qty,count( DISTINCT orders_id ) AS orders FROM r_orders_products GROUP BY products_name order by ".$sortby." ".$this->disType. "limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$select =$action->db->fetchAll("SELECT rp.products_model, rp.products_name,rp.products_id, sum( rp.products_quantity ) AS qty,count( DISTINCT rp.orders_id ) AS orders FROM r_orders_products rp,r_orders r where rp.orders_id=r.orders_id and r.invoice_id!='0' GROUP BY rp.products_name order by ".$sortby." ".$this->disType. "	limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				$this->view->results=$select;
			}
		}
		public function bannerGraphAction()
		{
			
		}
		
		public function dashboardGraphAction()
		{
			//$this->_helper->layout->setLayout('admin');
			
			$action=new Model_Adminaction();
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$range=$this->_getParam('range')==""?'day':$this->_getParam('range');
			switch ($range) {
				case 'day':
				for ($i = 0; $i < 24; $i++) {
					
					$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM `r_orders` WHERE orders_status > '0' AND (DATE(date_purchased) = DATE('".$this->_date."') AND HOUR(date_purchased) = '" . $i . "') GROUP BY HOUR(date_purchased) ORDER BY date_purchased ASC");
					//echo "SELECT COUNT(*) AS total FROM `r_orders` WHERE orders_status > '0' AND (DATE(date_purchased) = DATE('".$this->_date."') AND HOUR(date_purchased) = '" . $i . "') GROUP BY HOUR(date_purchased) ORDER BY date_purchased ASC";
					
					if (count($query)) {
						$data['order']['data'][]  = array($i, (int)$query['total']);
						} else {
						$data['order']['data'][]  = array($i, 0);
					}
					
					/*$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM r_customers_info WHERE DATE(customers_info_date_account_created) = DATE(NOW()) AND HOUR(customers_info_date_account_created) = '" . (int)$i . "' GROUP BY HOUR(customers_info_date_account_created) ORDER BY customers_info_date_account_created ASC");*/
					
					$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM r_customers WHERE DATE(customers_date_account_created) = DATE('".$this->_date."') AND HOUR(customers_date_account_created) = '" . $i . "' GROUP BY HOUR(customers_date_account_created) ORDER BY customers_date_account_created ASC");
					
					if (count($query)) {
						$data['customer']['data'][] = array($i, (int)$query['total']);
						} else {
						$data['customer']['data'][] = array($i, 0);
					}
					
					//$data['xaxis'][] = array($i, date('H', mktime($i, 0, 0, date('n'), date('j'), date('Y'))));
				}
				break;
				case 'week':
				$date_start = strtotime('-' . date('w') . ' days');
				
				for ($i = 0; $i < 7; $i++) {
					$date = date('Y-m-d', $date_start + ($i * 86400));
					
					$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM `r_orders` WHERE orders_status > '0' AND DATE(date_purchased) = '" . $this->view->escape($date) . "' GROUP BY DATE(date_purchased)");
					
					if (count($query)) {
						$data['order']['data'][] = array($i, (int)$query['total']);
						} else {
						$data['order']['data'][] = array($i, 0);
					}
					
					//$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM r_customers_info WHERE DATE(customers_info_date_account_created) = '" . $this->view->escape($date) . "' GROUP BY DATE(customers_info_date_account_created)");
					
					$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM r_customers WHERE DATE(customers_date_account_created) = '" . $this->view->escape($date) . "' GROUP BY DATE(customers_date_account_created)");
					
					if (count($query)) {
						$data['customer']['data'][] = array($i, (int)$query['total']);
						} else {
						$data['customer']['data'][] = array($i, 0);
					}
					
					//$data['xaxis'][] = array($i, date('D', strtotime($date)));
				}
				
				break;
				default:
				case 'month':
				for ($i = 1; $i <= date('t'); $i++) {
					$date = date('Y') . '-' . date('m') . '-' . $i;
					
					$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM `r_orders` WHERE orders_status > '0' AND (DATE(date_purchased) = '" . $this->view->escape($date) . "') GROUP BY DAY(date_purchased)");
					
					if (count($query)) {
						$data['order']['data'][] = array($i, (int)$query['total']);
						} else {
						$data['order']['data'][] = array($i, 0);
					}
					
					//$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM r_customers_info WHERE DATE(customers_info_date_account_created) = '" . $this->view->escape($date) . "' GROUP BY DAY(customers_info_date_account_created)");
					
					$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM r_customers WHERE DATE(customers_date_account_created) = '" . $this->view->escape($date) . "' GROUP BY DAY(customers_date_account_created)");
					
					if (count($query)) {
						$data['customer']['data'][] = array($i, (int)$query['total']);
						} else {
						$data['customer']['data'][] = array($i, 0);
					}
					
					//$data['xaxis'][] = array($i, date('j', strtotime($date)));
				}
				break;
				case 'year':
				for ($i = 1; $i <= 12; $i++) {
					$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM `r_orders` WHERE orders_status > '0' AND YEAR(date_purchased) = '" . date('Y') . "' AND MONTH(date_purchased) = '" . $i . "' GROUP BY MONTH(date_purchased)");
					//echo $query[total];
					if (count($query)) {
						$data['order']['data'][] = array($i, (int)$query['total']);
						} else {
						$data['order']['data'][] = array($i, 0);
					}
					
					//$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM r_customers_info WHERE YEAR(customers_info_date_account_created) = '" . date('Y') . "' AND MONTH(customers_info_date_account_created ) = '" . $i . "' GROUP BY MONTH(customers_info_date_account_created )");
					
					$query = $action->db->fetchRow("SELECT COUNT(*) AS total FROM r_customers WHERE YEAR(customers_date_account_created) = '" . date('Y') . "' AND MONTH(customers_date_account_created ) = '" . $i . "' GROUP BY MONTH(customers_date_account_created )");
					
					if (count($query)) {
						$data['customer']['data'][] = array($i, (int)$query['total']);
						} else {
						$data['customer']['data'][] = array($i, 0);
					}
					
					//$data['xaxis'][] = array($i, date('M', mktime(0, 0, 0, $i, 1, date('Y'))));
				}
				break;
			}
			$this->view->range=$range;
			$this->view->data=$data;
			//echo "<pre>";
			//print_r($data);
			//echo "</pre>";
			$this->render('dashboard-graph');
		}
		
		public function bannerAction(){
			$this->checkSession($_SESSION['admin_id']);
			//$this->checkpermission();
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$model = new Model_DbTable_rbanners();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			$this->_table="r_banners";
			$this->_id="banners_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Pub':	if($_REQUEST['rid']!="")
					{
						$data=array('status'=>'1');
						$action->RecordPublish('r_banners',$data,'banners_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'banner?page='.$_REQUEST['page'].'&action=Pub&msg='.base64_decode('selected items enabled successfully!!'));
					}
					break;
					case 'UnPub':
					if($_REQUEST['rid']!="")
					{
						$data=array('status'=>'0');
						$action->RecordPublish('r_banners',$data,'banners_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'banner?page='.$_REQUEST['page'].'&action=UnPub&msg='.base64_decode('selected items disabled successfully!!'));
					}
					break;
					
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="asc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'banners_title':$_REQUEST['sortby'];
				$select =$action->db->fetchAll("select b.* from ".$this->_table." b order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						/*if($image=="")
							{
							$html_text=$_REQUEST['html_text'];
							}else if($_REQUEST['html_text']!="")
							{
							$image="";
							$html_text=$_REQUEST['html_text'];
						}*/
						if($_REQUEST['html_text']!="")
						{
							$fch=$action->db->fetchRow("select banners_image,banners_html_text from r_banners where banners_id='".(int)$_REQUEST[rid]."'");
							if($fch[banners_image]!='')
							{
								@unlink(PATH_TO_UPLOADS_DIR."/image/".$fch[banners_image]);
							}
							$upload="";
							$html_text=$_REQUEST['html_text'];
						}
						else
						{
							$upload=$act_ext->fnupload(	array("action"=>$this->_action,"type"=>"Edit",
							"field"=>"banner","path"=>PATH_TO_UPLOADS_DIR."/image/","prev_img"=>$_REQUEST['prev_image'],"prefix"=>"banner_".$_REQUEST['rid']));
							$html_text="";
						}
						
						if($_REQUEST['expires_on']=="")
						{
							$expires_on="";
							$impressions=$_REQUEST['impressions'];
						}else if($_REQUEST['impressions']=="" || $_REQUEST['impressions']=="0" )
						{
							$expires_on=$_REQUEST['expires_on'];
							$impressions="";
						}
						
						$data=array('banners_title'=>$this->reqObj->request['title'],'banners_url'=>$this->reqObj->request['url'],'banners_url'=>$this->reqObj->request['url'],'banners_html_text'=>$html_text,'banners_image'=>$upload,
						'date_scheduled'=>$this->reqObj->request['date_scheduled'],'expires_date'=>$expires_on,
						'expires_impressions'=>$impressions,'status'=>$_REQUEST['status'],
						'date_status_change'=>$this->_date);
						$model->update($data,$this->_id.'='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,(int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					
				}
			}
		}
		
		public function myStoreAction(){
			
			
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			$this->view->total_count='1';//for view
			if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':
					if($_REQUEST['play']!="")
					{//$action->p($_REQUEST[configuration],'0');
						
						//start update admin url
						if($_REQUEST['configuration']['ADMIN_FRIENDLY_URL']!=@constant('ADMIN_FRIENDLY_URL'))
						{
							
							$this->updateTemplateFile(@constant('SITE_DEFAULT_TEMPLATE'),$_REQUEST['configuration']['ADMIN_FRIENDLY_URL']); 
						}
						
						//will update time zone in r_configuration of client and customer table of sporanzo
						if($_REQUEST['configuration']['DEFAULT_TIME_ZONE']!=@constant('DEFAULT_TIME_ZONE'))
						{
							mystoreUpdateTimeZone($_REQUEST['configuration']['DEFAULT_TIME_ZONE']);	
						}
						
						//end update admin url
						
						Model_Cache::removeAllCache();
						
						$store_logo=$act_ext->fnupload(array("action"=>$this->_action,"type"=>"Edit","field"=>"STORE_LOGO",
						"path"=>PATH_TO_UPLOADS_DIR."/image/","prev_img"=>$_REQUEST['prev_logo'],"prefix"=>"logo"));
						
						$store_favi_icon=$act_ext->fnupload(array("action"=>$this->_action,"type"=>"Edit","field"=>"STORE_FAVI_ICON",
						"path"=>PATH_TO_UPLOADS_DIR."/image/","prev_img"=>$_REQUEST['prev_favi'],"prefix"=>"favi"));
						
						$store_no_image_icon=$act_ext->fnupload(array("action"=>$this->_action,"type"=>"Edit","field"=>"STORE_NO_IMAGE_ICON",
						"path"=>PATH_TO_UPLOADS_DIR."/image/","prev_img"=>$_REQUEST['prev_no_image'],"prefix"=>"no-image"));
						
						$action->db->update('r_configuration',array("configuration_value"=>$store_logo),"configuration_key like 'STORE_LOGO'");
						$action->db->update('r_configuration',array("configuration_value"=>$store_favi_icon),"configuration_key like 'STORE_FAVI_ICON'");
						$action->db->update('r_configuration',array("configuration_value"=>$store_no_image_icon),"configuration_key like 'STORE_NO_IMAGE_ICON'");
						
						
						
						foreach($_REQUEST[configuration] as $k=>$v)
						{
							
							//$data=array("configuration_value"=>addslashes($v));
							if($k=='SERVER_GOOGLE_ANALYTICS')
							{
								//echo stripslashes($v);
								//exit;
								//$data=array("configuration_value"=>mysql_real_escape_string($v));
								$data=array("configuration_value"=>stripslashes($v));
							}else
							{
								$data=array("configuration_value"=>htmlspecialchars($v));
							}
							
							// echo $k." ".$v."<br/>";
							$action->db->update('r_configuration',$data,"configuration_key like '".$k."'");
						}
						//echo "<pre>";
						//print_r($data);
						//exit;
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							
							//$this->_redirect(@constant('ADMIN_URL_CONTROLLER')."".$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
							$this->_redirect($this->view->url_to_site.$_REQUEST['configuration']['ADMIN_FRIENDLY_URL']."/".$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$sel1=$action->getEditdetails($this->_table,$this->_id,"'".(int)$_REQUEST['rid']."'");
					$this->view->data = $sel1;
					break;
				}
			}
		}
		
		public function couponAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rcoupon();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			$this->_table="r_coupon";
			$this->_id="coupon_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
						$action->RecordDelete('r_coupon_product',$this->_id,$_REQUEST['rid']);
						$action->RecordDelete('r_coupon_history',$this->_id,$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'date_added':$this->_getParam('sortby');
				$select =$action->db->fetchAll("select * from ".$this->_table." order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($this->_getParam('type')!="")
			{
				switch ($this->_getParam('type'))
				{
					case 'Edit':	if($_REQUEST['play']!="")
					{
						$inif="";
						if(strtotime($this->_getParam('date_start'))>strtotime($this->_getParam('date_end')))
						{
							$inif=true;
						}
						if($inif=="")
						{
							$data=array('name'=>$this->reqObj->request['name'],'code'=>$this->reqObj->request['code'],'type'=>$this->reqObj->request['type'],'discount'=>preg_replace('/[^0-9.]/s', '', $_REQUEST['discount']),'total'=>preg_replace('/[^0-9.]/s', '',$_REQUEST['total']),'logged'=>$this->reqObj->request['logged'],'shipping'=>$this->reqObj->request['shipping'],'uses_total'=>preg_replace('/[^0-9.]/s', '', $_REQUEST['uses_total']),'uses_customer'=>preg_replace('/[^0-9.]/s', '', $this->reqObj->request['uses_customer']),'date_start'=>$this->reqObj->request['date_start'],'date_end'=>$this->reqObj->request['date_end'],'status'=>$_REQUEST['status']);
							$model->update($data,$this->_id.'="'.(int)$_REQUEST['rid'].'"');
							
							/*echo "<pre>";
								print_r($_REQUEST['coupon_product']);
							echo "</pre>";*/
							//exit;
							$action->db->delete('r_coupon_product',"coupon_id='".(int)$_REQUEST['rid']."'");
							
							if(sizeof($_REQUEST['coupon_product'])>0)
							{
								foreach($_REQUEST['coupon_product'] as $k=>$v)
								{
									$data=array("coupon_id"=>(int)$_REQUEST['rid'],"product_id"=>$v);
									//print_r($data);
									$action->db->insert('r_coupon_product',$data);
									//$action->insertRow();
								}
							}
							//exit;
							
							
							$this->view->msg=$inif==true?base64_encode('expiry date should be greater than starting date!!'):$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
							if($_REQUEST['play']=='save')
							{
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
							}
						}
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,"'".(int)$_REQUEST['rid']."'");
					$this->view->data = $sel1;
					$this->view->histories=$act_ext->getCouponHistories((int)$_REQUEST['rid']);
					$this->view->coupon_product=$act_ext->getCouponProducts((int)$_REQUEST['rid']);
					
					$products = $act_ext->getCouponProducts((int)$_REQUEST['rid']);
					
					//print_r($products);
					if($products==""){
						$products = array();
					}
					$this->view->coupon_product = array();
					$prodObj=new Model_Products();
					foreach ($products as $product_id) {
						
						$product_info = $act_ext->getProduct($product_id);
						//print_r($product_info);
						if ($product_info) {
							$this->data['coupon_product'][] = array(
							'product_id' => $product_info['products_id'],
							'name'       => $product_info['products_name']
							);
						}
					}
					$this->view->coupon_product=$this->data['coupon_product'];
					
					break;
					
					case 'Add':
					if($_REQUEST['play']!="")
					{
						if(strtotime($this->_getParam('date_start'))<strtotime($this->_getParam('date_end')))
						{
							
							$select =$action->db->fetchRow("select count(coupon_id) as count_row from ".$this->_table." where code='".$this->_getParam('code')."'");
							
							if($select[count_row]==0)
							{
								$data=array('name'=>$this->reqObj->request['name'],'code'=>$this->reqObj->request['code'],'type'=>$this->reqObj->request['type'],'discount'=>preg_replace('/[^0-9.]/s', '', $_REQUEST['discount']),'total'=>preg_replace('/[^0-9.]/s', '', $_REQUEST['total']),'logged'=>$this->reqObj->request['logged'],'shipping'=>$this->reqObj->request['shipping'],'uses_total'=>preg_replace('/[^0-9.]/s', '', $_REQUEST['uses_total']),'uses_customer'=>preg_replace('/[^0-9.]/s', '', $this->reqObj->request['uses_customer']),'date_start'=>$this->reqObj->request['date_start'],'date_end'=>$this->reqObj->request['date_end'],'status'=>$_REQUEST['status'],'date_added'=>$this->_date);
								$insert_id=$model->insert($data);
								
								$action->db->delete('r_coupon_product',"coupon_id='".(int)$_REQUEST['rid']."'");
								if(sizeof($_REQUEST['coupon_product'])>0)
								{
									foreach($_REQUEST['coupon_product'] as $k=>$v)
									{
										$data=array("coupon_id"=>$insert_id,"product_id"=>$v);
										//print_r($data);
										$action->db->insert('r_coupon_product',$data);
										//$action->insertRow();
									}
								}
								
								$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
								if($_REQUEST['play']=='save')
								{
									$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
								}
								
								$sel1=$action->getEditdetails($this->_table,$this->_id,$insert_id);
								$this->view->data = $sel1;
							}else
							{
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?type=Add&msg='.base64_encode('Coupon code already exists!!'));
							}
						}else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?type=Add&msg='.base64_encode('expiry date should be greater than starting date!!'));
						}
					}
					
					break;
				}
			}
		}
		
		public function affiliateAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_raffiliate();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			$this->_table="r_affiliate";
			$this->_id="affiliate_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			/*start transactions*/
			
			if($_REQUEST[trans]=='1')
			{
				
				$data_trans=$this->_getParam('r_affiliate_transaction');
				$data_trans[affiliate_id]=(int)$this->_getParam(rid);
				$data_trans['date_added']=$this->_date;
				$action->db->insert('r_affiliate_transaction',$data_trans);
				//email $_REQUEST['trans_email'];
				$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?rid='.$this->_getParam('rid').'&type=Edit&msg='.base64_encode('transaction added successfully'));
			}
			/*end transactions*/
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					
					case 'Pub':if($_REQUEST['rid']!="") //Affiliate Account Activation
					{
						$mailObj=new Model_Mail();
						foreach($_REQUEST['rid'] as $k=>$v)
						{
							/*start mail*/
							$row=$action->db->fetchRow('select concat(firstname," ",lastname) as name, email from r_affiliate where affiliate_id="'.$v.'" and approved="0"');
							
							$data=array('approved'=>'1');
							$action->RecordPublish('r_affiliate',$data,'affiliate_id',array($v));
							
							if($row['email']!="")
							{
								$arrmc=$mailObj->getEmailContent(array('id'=>'12','lang'=>'1'));
								$array_mail=array('to'=>array('name'=>$row['name'],'email'=>trim($row['email'])),'html'=>array('content'=>$arrmc['content']),'subject'=>$arrmc['subject']);
								$mailObj->sendMail($array_mail);
							}
						}
						/*end mail*/
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'affiliate?page='.$this->_getParam('page').'&action=Pub&msg='.base64_encode('selected affiliates approved successfully!!'));
					}
					break;
					
					case 'UnPub':if($_REQUEST['rid']!="")
					{
						$data=array('status'=>'0');
						$action->RecordPublish('r_affiliate',$data,'affiliate_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'affiliate?page='.$this->_getParam('page').'&action=UnPub&msg='.base64_encode('selected items disabled successfully!!'));
					}
					break;
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
						$action->RecordDelete('r_affiliate_transaction',$this->_id,$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$this->_getParam('page').'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				/*start search*/
				$search=array();
				$pageing=array();
				if($this->_getParam('affname')!="")
				{
					$search[]="concat(firstname,' ',lastname)='".$this->_getParam('affname')."'";
					$pageing[affname]=$this->_getParam('affname');
				}
				if($_REQUEST['affemailid']!="")
				{
					$search[]="email like '%".$this->_getParam('affemailid')."%'";
					$pageing[affemailid]=$this->_getParam('affemailid');
				}
				if(count($search)>0)
				{
					$srch_str=implode(' and ', $search);
					$srch_str=" and ".$srch_str;
				}else
				{
					$srch_str="";
				}
				/*end search*/
				
				//$count=$model->fetchAll($model->select()->from($this->_table));
				$count=$action->db->fetchAll("select affiliate_id from r_affiliate  where affiliate_id!='0' ".$srch_str);
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->paginationP(ADMIN_URL_CONTROLLER.$this->view->actionTitle,$pageing);
				//$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'affiliate_id':$this->_getParam('sortby');
				$select =$action->db->fetchAll("select concat(firstname,' ',lastname) as name,affiliate_id,email,status,date_added,approved,telephone from ".$this->_table." where affiliate_id!='' ".$srch_str." order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
				
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						//$action->p($_REQUEST[r_affiliate],'0');
						$data=$_REQUEST[r_affiliate];
						$data['password']=$act_ext->setEncryptPassword($_REQUEST['r_affiliate']['password']);
						$model->update($data,$this->_id.'='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$this->view->msg=$_REQUEST['msg'];
					$sel1=$action->getEditdetails($this->_table,$this->_id,(int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$row=$action->db->fetchRow("select count(*) as count from r_affiliate where email like '".$_REQUEST['r_affiliate']['email']."'");       
						if($row['count']!="0")
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?type=Add&msg='.base64_encode('email id already exists.please try another!!'));
						}
						$data=$_REQUEST[r_affiliate];
						$data['status']='1';
						$data['approved']='1';
						$data['password']=$act_ext->setEncryptPassword($_REQUEST['r_affiliate']['password']);
						$data['date_added']=$this->_date;
						
						$insert_id=$model->insert($data);
						$this->sendAffiliateMail($_REQUEST['r_affiliate']);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$this->view->msg=$_REQUEST['msg'];
					$sel1=$action->getEditdetails($this->_table,$this->_id,$insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
		}
        
        public function sendAffiliateMail($data)
        {
            //echo "<pre>";
            //print_r($data);
            /*start email*/
            $mailObj=new Model_Mail();
            $replace_array=array('%affiliate_name%'=>$data['firstname']." ".$data['lastname']);
            $email_content=$mailObj->getEmailContent(array('lang'=>'1','id'=>'7','replace'=>$replace_array)); //general mail
            //print_r($email);
            if(@constant('EMAIL_NEW_ACCOUNT_ALERT')=='true')//if(EMAIL_NEW_ACCOUNT_ALERT==true)
            {
                $alert_replace_array=array('%affiliate_name%'=>$data['firstname']." ".$data['lastname'],'%affiliate_email%'=>$data['email']);
                $alert_return=$mailObj->getEmailContent(array('lang'=>'1','id'=>'17','replace'=>$alert_replace_array));
				
                // Send to additional alert emails if new account email is enabled
                $emails = explode(',', @constant('SEND_EXTRA_EMAILS_TO'));
                foreach ($emails as $email) 
                {
                    if (strlen($email) > 0 && preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i', $email))                              {
						$users['bcc'][]=array("name"=>STORE_NAME,"email"=>$email);
					}
				}
				
                $alert_array_mail=array('to'=>array('name'=>STORE_OWNER,'email'=>STORE_OWNER_EMAIL_ADDRESS),'html'=>array('content'=>$alert_return['content']),'subject'=>$alert_return['subject'],"bcc"=>$users['bcc']);
                
				//echo "<pre>";
				//print_r($alert_array_mail);
				
                $mailObj->sendMail($alert_array_mail);
			}
			
            $array_mail=array('to'=>array('name'=>$data['firstname']." ".$data['lastname'],'email'=>trim($data['email'])),'html'=>array('content'=>$email_content['content']),'subject'=>$email_content['subject']);
			/*echo "<pre>";
				print_r($array_mail);
				echo "</pre>";
			exit;*/
            $mailObj->sendMail($array_mail);
            /*end email*/
		}
		
        public function sideBannerAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rbanner();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			$this->_table="r_banner";
			$this->_id="banner_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':
					if($_REQUEST['rid']!="")
					{
						$rid1=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						foreach($rid1 as $a=>$rid)
						{
							Model_Cache::removeCache('banner_'.$rid);
							$action->RecordDelete('r_banner','banner_id',array($rid));
							$c=$action->db->fetchAll("select * from r_banner_image	where banner_id= '".(int)$rid."'");
							if(sizeof($c)>'0')
							{
								foreach($c as $k=>$v)
								{
									//echo PATH_TO_UPLOADS_DIR."banners\\".$v[image];
									@unlink(PATH_TO_UPLOADS_DIR."/banners/".$v[image]);
								}
								
							}
							$action->RecordDelete('r_banner_image','banner_id',array($rid));
							$action->RecordDelete('r_banner_image_description','banner_id',array($rid));
						}
						//exit;
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'side-banner?page='.$_REQUEST['page'].'&action=Del&msg='.base64_encode('selected row deleted successfully!!'));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="asc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'name':$this->_getParam('sortby');
				$select =$action->db->fetchAll("select * from ".$this->_table." order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
				
				
				
			}else if($_REQUEST['type']!="")
			{
				$this->view->languages=$action->getLanguages();
				/*echo "hwerwe<pre>";
					print_r($this->view->languages);
				echo "</pre>";*/
				
				switch ($_REQUEST['type'])
				{
					case 'Edit':
					if($_REQUEST['play']!="")
					{
						Model_Cache::removeCache('banner_'.$_REQUEST[rid]);
						$data=array('name'=>$_REQUEST['name'],'status'=>$_REQUEST['status']);
						$model->update($data,$this->_id.'='.(int)$_REQUEST['rid']);
						$action->db->query("DELETE FROM r_banner_image WHERE banner_id = '" . (int)$_REQUEST['rid'] . "'");
						$action->db->query("DELETE FROM r_banner_image_description WHERE banner_id = '" . (int)$_REQUEST['rid'] . "'");
						if (isset($_REQUEST['banner_image']))
						{
							foreach ($_REQUEST['banner_image'] as $banner_image=>$v )
							{
								$upload=$act_ext->fnupload(	array("action"=>$this->_action,"type"=>"Edit","field"=>"image".$banner_image,"path"=>PATH_TO_UPLOADS_DIR."/banners/","prev_img"=>$_REQUEST["preimage".$banner_image],"prefix"=>"scroll_banner_".$_REQUEST['rid']));
								
								$action->db->query("INSERT INTO r_banner_image SET banner_id = '" . (int)$_REQUEST['rid'] . "', link = '" .  $_REQUEST[banner_image][$banner_image]['link'] . "', image = '" .  $upload . "'");
								
								$banner_image_id = $action->db->lastInsertId();
								foreach ($v['banner_image_description'] as $language_id => $banner_image_description)
								{
									$action->db->query("INSERT INTO r_banner_image_description SET banner_image_id = '" . (int)$banner_image_id . "', language_id = '" . (int)$language_id . "', banner_id = '" . (int)$_REQUEST['rid'] . "', title = '" .  $banner_image_description['title'] . "'");
								}
							}
						}
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));       
						}
						
					}	$this->view->banner=$action->getEditdetails($this->_table,$this->_id,(int)$_REQUEST['rid']);
					$this->view->banner_images=$act_ext->getBannerImages((int)$_REQUEST['rid']);
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$data=array('name'=>$_REQUEST['name'],'status'=>$_REQUEST['status']);
						$insert_id=$model->insert($data);
						if (isset($_REQUEST['banner_image']))
						{
							foreach ($_REQUEST['banner_image'] as $banner_image=>$v )
							{
								$upload=$act_ext->fnupload(	array("action"=>$this->_action,"type"=>"Edit","field"=>"image".$banner_image,"path"=>PATH_TO_UPLOADS_DIR."/banners/","prev_img"=>$_REQUEST["preimage".$banner_image],"prefix"=>"scroll_banner_".$_REQUEST['rid']));
								
								$action->db->query("INSERT INTO r_banner_image SET banner_id = '" .(int)$insert_id. "', link = '" .  $_REQUEST[banner_image][$banner_image]['link'] . "', image = '" .  $upload . "'");
								
								$banner_image_id = $action->db->lastInsertId();
								foreach ($v['banner_image_description'] as $language_id => $banner_image_description)
								{
									$action->db->query("INSERT INTO r_banner_image_description SET banner_image_id = '" . (int)$banner_image_id . "', language_id = '" . (int)$language_id . "', banner_id = '" . (int)$insert_id. "', title = '" .  $banner_image_description['title'] . "'");
								}
							}
						}
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$this->view->banner=$action->getEditdetails($this->_table,$this->_id,(int)$_REQUEST['rid']);
					$this->view->banner_images=$act_ext->getBannerImages((int)$_REQUEST['rid']);
					break;
				}
			}
		}
        
		public function customergroupAction(){
			// echo "<pre>";
			//print_r($_REQUEST);
			//echo "</pre>";
			//exit;
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rcustomergroup();
			$action=new Model_Adminaction();
			$this->_table="r_customer_group";
			$this->_id="customer_group_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						Model_Cache::removeMTCache(array("customergroup"));
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						$f=$action->db->fetchRow("select count(*) as count from r_customers where customer_group_id in (".implode(",",$rid).")");
						if($f['count']=='0')
						{
							$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
						}
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('cannot delete as some customers are available in this group'));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="asc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'name':$this->_getParam('sortby');
				$select =$action->db->fetchAll("select * from ".$this->_table." order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						Model_Cache::removeMTCache(array("customergroup"));
						$data=array('name'=>$this->reqObj->request['name']);
						$model->update($data,$this->_id.'='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,(int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						Model_Cache::removeMTCache(array("customergroup"));
						$data=array('name'=>$this->reqObj->request['name']);
						$insert_id=$model->insert($data);
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,$insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
		}
		
		public function customersAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$model = new Model_DbTable_rcustomers();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			
			
			$this->view->msg=$this->_getParam('msg');//for transaction msg
			if($_REQUEST['r_customer_transaction'][description]!="" && $_REQUEST['r_customer_transaction'][amount]!="")
			{
				$action->db->insert('r_customer_transaction',array('customer_id'=>(int)$this->_getParam('rid'),'description'=>$this->reqObj->request['r_customer_transaction'][description],'amount'=>$this->reqObj->request['r_customer_transaction'][amount],'date_added'=>$this->_date));
				
				/*start mail*/
				$fetch=$action->db->fetchRow('select sum(amount) as amt from r_customer_transaction where customer_id="'.(int)$this->_getParam('rid').'"');
				$currency=new Model_currencies();
				$total=$currency->format($fetch['amt']);
				$mailObj=new Model_Mail();
				$arrmc=$mailObj->getEmailContent(array('id'=>'10','lang'=>'1','replace'=>array('%credit%'=>$_REQUEST['r_customer_transaction'][amount],'%total_credit%'=>$total)));
				
				$array_mail=array('to'=>array('name'=>$this->reqObj->request['customers_firstname']." ".$this->reqObj->request['customers_lastname'],'email'=>trim($this->reqObj->request['customers_email'])),'html'=>array('content'=>$arrmc['content']),'subject'=>$arrmc['subject']);
				
				$mailObj->sendMail($array_mail);
				/*end mail*/
				
				$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?rid='.$_REQUEST['rid'].'&type=Edit&msg='.base64_encode('transaction added successfully and mail sent to customer!!'));
			}
			
			$this->view->msg=$this->_getParam('msg');//for rewards msg
			if($_REQUEST['r_customer_reward'][description]!="" && $_REQUEST['r_customer_reward'][amount]!="")
			{
				$arr=array('customer_id'=>(int)$this->_getParam('rid'),'description'=>$this->reqObj->request['r_customer_reward'][description],'points'=>$this->reqObj->request['r_customer_reward'][amount],'date_added'=>$this->_date);
				
				$action->db->insert('r_customer_reward',array('customer_id'=>(int)$this->_getParam('rid'),'description'=>$this->reqObj->request['r_customer_reward'][description],'points'=>$this->reqObj->request['r_customer_reward'][amount],'date_added'=>$this->_date));
				
				/*start mail*/
				$mailObj=new Model_Mail();
				$arrmc=$mailObj->getEmailContent(array('id'=>'9','lang'=>'1','replace'=>array('%reward%'=>$_REQUEST['r_customer_reward'][amount],'%total_reward%'=>$total)));
				
				$fetch=$action->db->fetchRow('select sum(points) as amt from r_customer_reward where customer_id="'.(int)$this->_getParam('rid').'"');
				$total=$fetch['amt'];
				
				$array_mail=array('to'=>array('name'=>$this->reqObj->request['customers_firstname']." ".$this->reqObj->request['customers_lastname'],'email'=>trim($this->reqObj->request['customers_email'])),'html'=>array('content'=>$arrmc['content']),'subject'=>$arrmc['subject']);
				
				$mailObj->sendMail($array_mail);
				/*end mail*/
				
				$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?rid='.$_REQUEST['rid'].'&type=Edit&msg='.base64_encode('reward points added successfully and mail sent to customer!!'));
			}
			
			if($_REQUEST['type']=='')
			{
				if($_REQUEST['action']!="")
				{
					
					
					if($_REQUEST['action']=='Pub')
					{
						if($_REQUEST['rid']!="")
						{
							$data=array('customers_status'=>'1');
							$action->RecordPublish('r_customers',$data,'customers_id',$_REQUEST['rid']);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=Pub&msg='.base64_encode('selected Customer published successfully!!'));
						}
					}
					elseif($_REQUEST['action']=='Approve')
					{
						if($_REQUEST['rid']!="")
						{
							$mailObj=new Model_Mail();
							foreach($_REQUEST['rid'] as $k=>$v)
							{
								/*start mail Account Registration Activation*/
								$row=$action->db->fetchRow('select concat(customers_firstname," ",customers_lastname) as name, customers_email_address as email from r_customers where customers_id="'.(int)$v.'" and customers_approved="0"');
								
								$data=array('customers_approved'=>'1');
								$action->RecordPublish('r_customers',$data,'customers_id',array($v));
								
								if($row['email']!="")
								{
									$arrmc=$mailObj->getEmailContent(array('id'=>'13','lang'=>'1'));
									$array_mail=array('to'=>array('name'=>$row['name'],'email'=>trim($row['email'])),'html'=>array('content'=>$arrmc['content']),'subject'=>$arrmc['subject']);
									$mailObj->sendMail($array_mail);
								}
							}
							/*end mail*/
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=UnPub&msg='.base64_encode('selected Customer Approved successfully!!'));
						}
					}
					elseif($_REQUEST['action']=='UnPub')
					{
						if($_REQUEST['rid']!="")
						{
							$data=array('customers_status'=>'0');
							$action->RecordPublish('r_customers',$data,'customers_id',$_REQUEST['rid']);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=UnPub&msg='.base64_encode('selected Customer Unpublished successfully!!'));
						}
					}
					elseif($_REQUEST['action']=='Del')
					{
						if($_REQUEST['rid']!="")
						{
							$action->RecordDelete('r_customers','customers_id',$_REQUEST['rid']);
							$action->RecordDelete('r_address_book','customers_id',$_REQUEST['rid']);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=Del&msg='.base64_encode('selected Customer Deleted successfully!!'));
						}
						
					}
				}
				
				/*start search*/
				$search=array();
				$pageing=array();
				if($_REQUEST['name']!="")
				{
					$search[]="concat(customers_firstname,' ',customers_lastname) like '%".addslashes($_REQUEST['name'])."%'";
					$pageing[name]=$_REQUEST['name'];
				}
				if($_REQUEST['emailid']!="")
				{
					$search[]="customers_email_address like '%".$_REQUEST['emailid']."%'";
					$pageing[emailid]=$_REQUEST['emailid'];
				}
				if(count($search)>0)
				{
					$srch_str=implode(' and ', $search);
					$srch_str=" and ".$srch_str;
				}else
				{
					$srch_str="";
				}
				/*end search*/
				
				$count=$action->db->fetchAll("select customers_id from r_customers where customers_id!='0' ".$srch_str);
				//$count=$model->fetchAll($model->select()->from('r_customers'));
				$total_count = count($count);
				$this->view->total_count=$total_count;
				
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->paginationP(ADMIN_URL_CONTROLLER.$this->view->actionTitle,$pageing);
				//$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'customers_id':$_REQUEST['sortby'];
				
				$select =$action->db->fetchAll("select c.*,cg.name from r_customers c,r_customer_group cg
				where c.customer_group_id=cg.customer_group_id ".$srch_str." order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				/*$select = $model->fetchAll($model->select()
					->from('r_customers',
					array('customers_id','customers_telephone','customers_firstname','customers_lastname','customers_status','customers_email_address'))
					->where('customers_id <> ?', '0')
					->order($sortby." ".$this->disType)
				->limit(ADMIN_PAGE_LIMIT,$pagination->offset()));*/
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				if($_REQUEST['type']=='Edit')
				{
					if($_REQUEST['play']!="")
					{
						//updating r_customers
						//print_r($_REQUEST);
						$password=$act_ext->setEncryptPassword($_REQUEST['password']);
						$data=array('customers_password'=>$password,'customer_group_id'=>(int)$_REQUEST['customer_group_id'],'customers_gender'=>$this->reqObj->request['customers_gender'],'customers_lastname'=>$this->reqObj->request['customers_lastname'],'customers_dob'=>$this->reqObj->request['dob'],'customers_email_address'=>$this->reqObj->request['customers_email'],'customers_telephone'=>$_REQUEST['tele'],'customers_fax'=>$_REQUEST['fax'],'customers_newsletter'=>$this->reqObj->request['newsletter'],'customers_firstname'=>$this->reqObj->request['customers_firstname'],'customers_status'=>$_REQUEST['customers_status']);
						//print_r($data);
						$model->update($data,'customers_id="'.(int)$_REQUEST['rid'].'"');
						//start address book                
						if(sizeof($_REQUEST[address])>0)
						{
							foreach($_REQUEST[address] as $k=>$v)
							{
								//updating r_address_book
								$raddressbook = new Model_DbTable_raddressbook();
								$data=array('entry_street_address'=>$v['address_1'],'entry_suburb'=>$v['address_2'],'entry_postcode'=>$v['postcode'],'entry_city'=>$v['city'],'entry_zone_id'=>$v['zone_id'],'entry_country_id'=>$v['country_id'],'entry_lastname'=>$v['lastname'],'entry_firstname'=>$v['firstname'],'entry_company'=>$v['company']);
								$raddressbook->update($data,'address_book_id='.$v['address_id']);
								
								if($v['default']!=='')
								{
									$data=array('customers_default_address_id'=>$v['address_id']);
									$model->update($data,'customers_id='.(int)$_REQUEST['rid']);
									
								}
							}
							
						}
						//end address book
						
						$this->view->msg=base64_encode("update successfully!!");
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?msg='.base64_encode('selected row updated successfully!!'));  
						}
					}
 					$sel1=$action->getcustomers((int)$_REQUEST['rid']);
					$this->view->addresses = $act_ext->getAddresses((int)$_REQUEST['rid']);
					/*echo "<pre>";
						print_r($this->view->addresses);
					echo "</pre>";*/
					$this->view->countries=$action->getCountries();
					
 					$this->view->data = $sel1;
				}else if($_REQUEST['type']=='Add')
				{
					if($_REQUEST['play']!="")
					{
						$password=$act_ext->setEncryptPassword($_REQUEST['password']);
						$data=array('customers_password'=>$password,'customer_group_id'=>(int)$_REQUEST['customer_group_id'],'customers_lastname'=>$this->reqObj->request['customers_lastname'],'customers_email_address'=>$this->reqObj->request['customers_email'],'customers_telephone'=>$_REQUEST['tele'],'customers_fax'=>$_REQUEST['fax'],'customers_newsletter'=>$this->reqObj->request['newsletter'],'customers_firstname'=>$this->reqObj->request['customers_firstname'],'customers_status'=>$_REQUEST['customers_status']);
						//print_r($data);
						$inst_id=$model->insert($data);
						
						//start address book                
						if(sizeof($_REQUEST[address])>0)
						{
							foreach($_REQUEST[address] as $k=>$v)
							{
								//updating r_address_book
								$raddressbook = new Model_DbTable_raddressbook();
								$data=array('entry_street_address'=>$v['address_1'],'customers_id'=>$inst_id,'entry_suburb'=>$v['address_2'],'entry_postcode'=>$v['postcode'],'entry_city'=>$v['city'],'entry_zone_id'=>$v['zone_id'],'entry_country_id'=>$v['country_id'],'entry_lastname'=>$v['lastname'],'entry_firstname'=>$v['firstname'],'entry_company'=>$v['company']);
								
								$inst_raddressbook=$raddressbook->insert($data);
								$data=array('customers_default_address_id'=>$inst_raddressbook);
								$model->update($data,'customers_id='.$inst_id);
								
							}
						}
						
						$this->registrationMail(array("firstname"=>$_REQUEST['customers_firstname'],"lastname"=>$_REQUEST['customers_lastname'],"email"=>$_REQUEST['customers_email']));
						// exit;
						$this->view->msg=base64_encode("update successfully!!");
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?msg='.base64_encode('selected row updated successfully!!'));
						}
					}
					$this->view->countries=$action->getCountries();
				}
			}
		}
        
        public function registrationMail($data)
        {
			/*start email*/
			$mailObj=new Model_Mail();
			if(@constant('APPROVE_NEW_CUSTOMER')=='true') //Account Registration Before Approval
			{
				$replace_array=array('%customer_name%'=>$data['firstname']." ".$data['lastname']);
				$return=$mailObj->getEmailContent(array('lang'=>'1','id'=>'1','replace'=>$replace_array));	//request approval
				
			}else //Account Registration General
			{
				$replace_array=array('%customer_name%'=>$data['firstname']." ".$data['lastname']);
				$return=$mailObj->getEmailContent(array('lang'=>'1','id'=>'14','replace'=>$replace_array)); 	
			}
			
			/*start additional alert emails*/
			if(@constant('EMAIL_NEW_ACCOUNT_ALERT')=='true')
			{
				$alert_replace_array=array('%customer_name%'=>$data['firstname']." ".$data['lastname'],'%customer_email%'=>$data['email']);
				$alert_return=$mailObj->getEmailContent(array('lang'=>'1','id'=>'15','replace'=>$alert_replace_array));
				
				// Send to additional alert emails if new account email is enabled
				$emails = explode(',', @constant('SEND_EXTRA_EMAILS_TO'));
				foreach ($emails as $email) 
				{
					if (strlen($email) > 0 && preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i', $email)) {
						$users['bcc'][]=array("name"=>@constant('STORE_NAME'),"email"=>$email);
					}
				}
				
				$alert_array_mail=array('to'=>array('name'=>@constant('STORE_OWNER'),'email'=>@constant('STORE_OWNER_EMAIL_ADDRESS')),'html'=>array('content'=>$alert_return['content']),'subject'=>$alert_return['subject']);
				/*echo "<pre>";    
					print_r($alert_array_mail);
				echo "</pre>";*/
				$mailObj->sendMail($alert_array_mail);
			}
			
			/*end additonal alert emails*/
			$array_mail=array('to'=>array('name'=>$data['firstname']." ".$data['lastname'],'email'=>trim($data['email'])),'html'=>array('content'=>$return['content']),'subject'=>$return['subject'],"bcc"=>$users['bcc']);
			/*echo "<pre>";    
                print_r($array_mail);
			echo "</pre>";*/
			$mailObj->sendMail($array_mail);
			/*end email*/
		}
		
		/*public function taxrateAction()
			{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$model = new Model_DbTable_rtaxrates();
			$action=new Model_Adminaction();
			
			if($_REQUEST['type']=='')
			{
			switch($_REQUEST['action'])
			{
			case 'Del':	if($_REQUEST['rid']!="")
			{
			$action->RecordDelete('r_tax_rates','tax_rates_id',$_REQUEST['rid']);
			$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&tid='.$_REQUEST['tid'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
			}
			break;
			}
			
			$count=$model->fetchAll($model->select()->from('r_tax_rates')->where('tax_class_id = ?', $_REQUEST['tid']));
			$total_count = count($count);
			
			$this->view->total_count=$total_count;
			$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
			$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
			$this->view->page=$page;
			$this->view->per_page=ADMIN_PAGE_LIMIT;
			
			//$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
			$this->view->view_pagination=$pagination->paginationP(ADMIN_URL_CONTROLLER.$this->view->actionTitle,array("tid"=>$_REQUEST['tid']));
			$this->view->disType=$this->disType;
			$sortby=$_REQUEST['sortby']==''?'tax_rates_id':$_REQUEST['sortby'];
			$select = $model->fetchAll($model->select()
			->from('r_tax_rates',
			array('tax_rates_id','tax_zone_id','tax_class_id','tax_priority','tax_rate','tax_description','last_modified','date_added'))
			->where('tax_class_id = ?', $_REQUEST['tid'])
			->order($sortby." ".$this->disType)
			->limit(ADMIN_PAGE_LIMIT,$pagination->offset()));
			
			$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
			switch ($_REQUEST['type'])
			{
			case 'Edit':if($_REQUEST['play']!="")
			{
			$data=array('tax_zone_id'=>$_REQUEST['tax_zone_id'],'tax_priority'=>$_REQUEST['tax_priority'],'tax_rate'=>$_REQUEST['tax_rate'],'tax_description'=>$_REQUEST['tax_description'],'last_modified'=>$this->_date);
			$model->update($data,'tax_rates_id='.$_REQUEST['rid']);
			
			$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
			if($_REQUEST['play']=='save')
			{
			$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&tid='.$_REQUEST['tax_class_id'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
			}
			}
			//$action=new Model_Adminaction();
			$sel1=$action->gettaxratesdetails($_REQUEST['rid']);
			$this->view->data = $sel1;
			break;
			
			case 'Add':	if($_REQUEST['play']!="")
			{
			$data=array('tax_class_id'=>$_REQUEST['tax_class_id'],'tax_zone_id'=>$_REQUEST['tax_zone_id'],'tax_priority'=>$_REQUEST['tax_priority'],'tax_rate'=>$_REQUEST['tax_rate'],'tax_description'=>$_REQUEST['tax_description'],'date_added'=>$this->_date);
			$insert_id=$model->insert($data);
			$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
			if($_REQUEST['play']=='save')
			{
			$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&tid='.$_REQUEST['tax_class_id'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
			}
			}
			$sel1=$action->gettaxratesdetails($insert_id);
			$this->view->data = $sel1;
			break;
			}
			}
			
		}*/
		
		/*public function geozoneAction()
			{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$model = new Model_DbTable_rzonestogeozones();
			$action=new Model_Adminaction();
			
			if($_REQUEST['type']=='')
			{
			switch($_REQUEST['action'])
			{
			case 'Del':	if($_REQUEST['rid']!="")
			{
			$action->RecordDelete('r_zones_to_geo_zones','association_id',$_REQUEST['rid']);
			$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&tid='.$_REQUEST['tid'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
			}
			break;
			}
			
			$count=$model->fetchAll($model->select()->from('r_zones_to_geo_zones')->where('geo_zone_id = ?', $_REQUEST['tid']));
			$total_count = count($count);
			
			$this->view->total_count=$total_count;
			$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
			$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
			$this->view->page=$page;
			$this->view->per_page=ADMIN_PAGE_LIMIT;
			
			//$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
			$this->view->view_pagination=$pagination->paginationP(ADMIN_URL_CONTROLLER.$this->view->actionTitle,array("tid"=>$_REQUEST['tid']));
			$this->view->disType=$this->disType;
			$sortby=$_REQUEST['sortby']==''?'association_id':$_REQUEST['sortby'];
			$select = $model->fetchAll($model->select()
			->from('r_zones_to_geo_zones',
			array('association_id','zone_country_id','zone_id','geo_zone_id','last_modified','date_added'))
			->where('geo_zone_id = ?', $_REQUEST['tid'])
			->order($sortby." ".$this->disType)
			->limit(ADMIN_PAGE_LIMIT,$pagination->offset()));
			
			$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
			switch ($_REQUEST['type'])
			{
			case 'Edit':if($_REQUEST['play']!="")
			{
			$data=array('zone_country_id'=>$_REQUEST['zone_country_id'],'zone_id'=>$_REQUEST['zone_id'],'geo_zone_id'=>$_REQUEST['geo_zone_id'],'last_modified'=>$this->_date);
			$model->update($data,'association_id='.$_REQUEST['rid']);
			
			$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
			if($_REQUEST['play']=='save')
			{
			$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&tid='.$_REQUEST['geo_zone_id'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
			}
			}
			//$action=new Model_Adminaction();
			$sel1=$action->getgeozonecountrydetails($_REQUEST['rid']);
			$this->view->data = $sel1;
			break;
			
			case 'Add':	if($_REQUEST['play']!="")
			{
			$data=array('zone_country_id'=>$_REQUEST['zone_country_id'],'zone_id'=>$_REQUEST['zone_id'],'geo_zone_id'=>$_REQUEST['geo_zone_id'],'date_added'=>$this->_date);
			$insert_id=$model->insert($data);
			$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
			if($_REQUEST['play']=='save')
			{
			$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&tid='.$_REQUEST['geo_zone_id'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
			}
			}
			$sel1=$action->getgeozonecountrydetails($insert_id);
			$this->view->data = $sel1;
			break;
			}
			}
			
		}*/
		
		public function taxclassAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rtaxclass();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$action->RecordDelete('r_tax_class','tax_class_id',$_REQUEST['rid']);
						$action->RecordDelete('r_tax_rates','tax_class_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from('r_tax_class'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'tax_class_id':$this->_getParam('sortby');
				$select = $model->fetchAll($model->select()
				->from('r_tax_class',array('tax_class_id','tax_class_title','tax_class_description'))
				->where('tax_class_id <> ?', '0')
				->order($sortby." ".$this->disType)
				->limit(ADMIN_PAGE_LIMIT,$pagination->offset()));
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$data=array('tax_class_title'=>$_REQUEST['tax_class_title'],'tax_class_description'=>$_REQUEST['tax_class_description'],'last_modified'=>$this->_date);
						$model->update($data,'tax_class_id='.(int)$_REQUEST['rid']);
						
						$action->db->delete('r_tax_rates',"tax_class_id='".(int)$_REQUEST['rid']."'");
						
						if (isset($_REQUEST['tax_rate'])) {
							foreach ($_REQUEST['tax_rate'] as $value) {
								$data=array("tax_zone_id"=>$value['geo_zone_id'],"tax_class_id"=>(int)$_REQUEST['rid'],"tax_priority"=>$value[priority],"tax_rate"=>$value[rate],"tax_description"=>$value['description'],"date_added"=>$this->_date);
								//print_r($data);366
								$action->db->insert('r_tax_rates',$data);
							}
						}
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					$sel1=$action->gettaxclassdetails((int)$_REQUEST['rid']);
					$this->view->tax_rates=$act_ext->getTaxRates((int)$_REQUEST['rid']);
					$this->view->geo_zones=$act_ext->getGeoZones();
					$this->view->data = $sel1;
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$data=array('tax_class_title'=>$_REQUEST['tax_class_title'],'tax_class_description'=>$_REQUEST['tax_class_description'],'date_added'=>$this->_date);
						$insert_id=$model->insert($data);
						
						$action->db->delete('r_tax_rates',"tax_class_id='".(int)$_REQUEST['rid']."'");
						
						if (isset($_REQUEST['tax_rate'])) {
							foreach ($_REQUEST['tax_rate'] as $value) {
								$data=array("tax_zone_id"=>$value['geo_zone_id'],"tax_class_id"=>$insert_id,"tax_priority"=>$value[priority],"tax_rate"=>$value[rate],"tax_description"=>$value['description'],"date_added"=>$this->_date);
								//print_r($data);
								$action->db->insert('r_tax_rates',$data);
							}
						}
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->gettaxclassdetails($insert_id);
					$this->view->data = $sel1;
					$this->view->tax_rates=array();
					$this->view->geo_zones=$act_ext->getGeoZones();
					
					break;
				}
			}
		}
		
		public function languageAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rlanguages();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			//$act_ext->addLang();
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					
					case 'Pub':if($_REQUEST['rid']!="")
					{
						$data=array('status'=>'1');
						$action->RecordPublish('r_languages',$data,'languages_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Pub&msg='.$action->getstringmsg(ADMIN_UNPUB_SINGLE_MSG,$this->view->actionTitle));
					}
					break;
					
					case 'UnPub':if($_REQUEST['rid']!="")
					{
						$data=array('status'=>'0');
						$action->RecordPublish('r_languages',$data,'languages_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=UnPub&msg='.$action->getstringmsg(ADMIN_UNPUB_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
					case 'Del':
					$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
					if($rid[0]!="" && $rid[0]!="1")
					{
						/*//$action->RecordDelete('r_languages','languages_id',$_REQUEST['rid']);*/
						//print_r($_REQUEST['rid']);
						foreach($rid as $k=>$v)
						{
							//echo $v."value of <br/>";
							$act_ext->deleteLang($v);
						}
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from('r_languages'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'languages_id':$_REQUEST['sortby'];
				$select = $model->fetchAll($model->select()
				->from('r_languages')
				->where('languages_id <> ?', '0')
				->order($sortby." ".$this->disType)
				->limit(ADMIN_PAGE_LIMIT,$pagination->offset()));
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						if(trim($_REQUEST['default'])!="")
						{
							$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['default']),'configuration_key=\'DEFAULT_LANGUAGE\'');
						}
						$data=array('name'=>$_REQUEST['name'],'code'=>$_REQUEST['code'],'image'=>$_REQUEST['image'], 'directory'=>$_REQUEST['directory'],'sort_order'=>$_REQUEST['sort_order'],'status'=>$_REQUEST['status']);
						$model->update($data,'languages_id='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails('r_languages','languages_id',(int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$insert_id=$act_ext->addLang();
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails('r_languages','languages_id',$insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
			
		}
		
		public function currencyAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rcurrencies();
			$action=new Model_Adminaction();
			
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Pub':if($_REQUEST['rid']!="")
					{
						//echo "<pre>";
						//print_r($_REQUEST);
						//EXIT;
						$data=array('status'=>'1');
						$action->RecordPublish('r_currencies',$data,'currencies_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Pub&msg='.$action->getstringmsg(ADMIN_UNPUB_SINGLE_MSG,$this->view->actionTitle));
					}
					break;
					
					case 'UnPub':if($_REQUEST['rid']!="")
					{
						$data=array('status'=>'0');
						$action->RecordPublish('r_currencies',$data,'currencies_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=UnPub&msg='.$action->getstringmsg(ADMIN_UNPUB_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
					case 'Del':	if($_REQUEST['rid']!="")
					{
						Model_Cache::removeAllCache();
						//exit;
						$action->RecordDelete('r_currencies','currencies_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from('r_currencies'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'currencies_id':$_REQUEST['sortby'];
				$select = $model->fetchAll($model->select()
				->from('r_currencies',
				array('currencies_id','title','code','symbol_left','symbol_right','decimal_point','thousands_point','decimal_places','value','last_updated','status'))
				->where('currencies_id <> ?', '0')
				->order($sortby." ".$this->disType)
				->limit(ADMIN_PAGE_LIMIT,$pagination->offset()));
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						Model_Cache::removeAllCache();
						if(trim($_REQUEST['default'])!="")
						{
							$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['default']),'configuration_key=\'DEFAULT_CURRENCY\'');
						}
						$data=array('title'=>$_REQUEST['title'],'code'=>$_REQUEST['code'],'symbol_left'=>$_REQUEST['symbol_left'],'symbol_right'=>$_REQUEST['symbol_right'],'decimal_point'=>$_REQUEST['decimal_point'],'thousands_point'=>$_REQUEST['thousands_point'],'decimal_places'=>$_REQUEST['decimal_places'],'value'=>$_REQUEST['value'],'last_updated'=>$this->_date,'status'=>$_REQUEST['status']);
						
						$model->update($data,'currencies_id='.(int)$_REQUEST['rid']);
						
						
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?type=Edit&rid='.$_REQUEST['rid'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					$this->view->msg=$_REQUEST['msg'];
					$sel1=$action->getcurrencydetails((int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					/*case 'Edit':if($_REQUEST['play']!="")
						{
						Model_Cache::removeAllCache();
						if(trim($_REQUEST['default'])!="")
						{
						$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['default']),'configuration_key=\'DEFAULT_CURRENCY\'');
						}
						$data=array('title'=>$_REQUEST['title'],'code'=>$_REQUEST['code'],'symbol_left'=>$_REQUEST['symbol_left'],'symbol_right'=>$_REQUEST['symbol_right'],'decimal_point'=>$_REQUEST['decimal_point'],'thousands_point'=>$_REQUEST['thousands_point'],'decimal_places'=>$_REQUEST['decimal_places'],'value'=>$_REQUEST['value'],'last_updated'=>new Zend_Db_Expr('NOW()'),'status'=>$_REQUEST['status']);
						$model->update($data,'currencies_id='.$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
						}
						$sel1=$action->getcurrencydetails($_REQUEST['rid']);
						$this->view->data = $sel1;
					break;*/
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						Model_Cache::removeAllCache();
						$data=array('title'=>$this->reqObj->request['title'],'code'=>$this->reqObj->request['code'],'symbol_left'=>$this->reqObj->request['symbol_left'],'symbol_right'=>$this->reqObj->request['symbol_right'],'decimal_point'=>$_REQUEST['decimal_point'],'thousands_point'=>$_REQUEST['thousands_point'],'decimal_places'=>$_REQUEST['decimal_places'],'value'=>$_REQUEST['value'],'last_updated'=>$this->_date,'status'=>$_REQUEST['status']);
						$insert_id=$model->insert($data);
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getcurrencydetails($insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
		}
		
		public function taxzonesAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rgeozones();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	
					if($_REQUEST['rid']!="")
					{
						$action->RecordDelete('r_geo_zones','geo_zone_id',$_REQUEST['rid']);
						$action->RecordDelete('r_zones_to_geo_zones','geo_zone_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from('r_geo_zones'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'geo_zone_name':$this->_getParam('sortby');
				$select = $model->fetchAll($model->select()
				->from('r_geo_zones',
				array('geo_zone_id','geo_zone_name','geo_zone_description'))
				->where('geo_zone_id <> ?', '0')
				->order($sortby." ".$this->disType)
				->limit(ADMIN_PAGE_LIMIT,$pagination->offset()));
				
				$this->view->results=$select;
				$this->view->zone_to_geo_zones=array();
			}else if($_REQUEST['type']!="")
			{
				$this->view->countries=$action->getCountries();
				$this->view->zone_to_geo_zones=array();
				
				
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$data=array('geo_zone_name'=>$this->reqObj->request['geo_zone_name'],'geo_zone_description'=>$this->reqObj->request['geo_zone_description'],'last_modified'=>$this->_date);
						$model->update($data,'geo_zone_id='.(int)$_REQUEST['rid']);
						
						$action->db->delete('r_zones_to_geo_zones',"geo_zone_id='".(int)$_REQUEST['rid']."'");
						if (isset($_REQUEST['zone_to_geo_zone']))
						{
							foreach ($_REQUEST['zone_to_geo_zone'] as $value)
							{
								
								$data=array("zone_country_id"=>$value['country_id'],"geo_zone_id"=>(int)$_REQUEST['rid'],"zone_id"=>$value[zone_id],"date_added"=>$this->_date);
								$action->db->insert('r_zones_to_geo_zones',$data);
								//PRINT_R($data);
							}
						}
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					$sel1=$action->getgeozonedetails((int)$_REQUEST['rid']);
					$this->view->zone_to_geo_zones=$act_ext->getZoneToGeoZones((int)$_REQUEST['rid']);
					
					$this->view->data = $sel1;
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$data=array('geo_zone_name'=>$this->reqObj->request['geo_zone_name'],'geo_zone_description'=>$this->reqObj->request['geo_zone_description'],'date_added'=>$this->_date);
						$insert_id=$model->insert($data);
						$action->db->delete('r_zones_to_geo_zones',"geo_zone_id='".(int)$_REQUEST['rid']."'");
						if (isset($_REQUEST['zone_to_geo_zone']))
						{
							foreach ($_REQUEST['zone_to_geo_zone'] as $value)
							{
								$data=array("zone_country_id"=>$value['country_id'],"geo_zone_id"=>$insert_id,"zone_id"=>$value[zone_id],"date_added"=>$this->_date);
								$action->db->insert('r_zones_to_geo_zones',$data);
							}
						}
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getgeozonedetails($insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
		}
        
		public function orderStatusAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rordersstatus();
			$action=new Model_Adminaction();
			$this->_table="r_orders_status";
			$this->_id="orders_status_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':if($_REQUEST['rid']!="")
					{
						
						//$action->p($_REQUEST['rid'],'0');
						$rcat=$action->getEditdetails('r_configuration','configuration_key','\'DEFAULT_ORDERS_STATUS_ID\'');
						$rcat[0]['configuration_value'];
						//echo $rcat[0]['configuration_value'];
						//exit;
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						if(!in_array($rcat[0]['configuration_value'],$rid))
						{
							$c=$action->db->fetchRow("select count(*) as count from r_orders_status_history
							where orders_status_id in (".implode(',',$rid).")");
							if($c['count']=='0')
							{
								$action->RecordDelete($this->_table,$this->_id,$rid);
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.base64_encode('selected rows deleted successfully!!'));
							}else
							{
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.base64_encode('Cannot delete as this order status is used in order status history'));
							}
						}
						else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'
							&action=Del&m=f&msg='.base64_encode('Cannot delete default order status'));
						}
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table)->where('language_id=1'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="asc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'orders_status_name':$this->_getParam('sortby');
				$select =$action->db->fetchAll("select * from r_orders_status where language_id='1'
				order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$la=$action->db->fetchAll("select * from r_languages");
						foreach ($la as $k)
						{
							unset($where);
							$where[]="language_id=".$k['languages_id'];
							$where[]="orders_status_id=".(int)$_REQUEST['rid'];
							$data=array('orders_status_name'=>$_REQUEST['orders_status_name_'.$k['languages_id']], 'subject'=>$_REQUEST['subject_'.$k['languages_id']],'email_template'=>$_REQUEST['email_temp_'.$k['languages_id']]);
							$data['html']=$_REQUEST['html']!=""?$_REQUEST['html']:"0";
							$model->update($data,$where);
							
						}
						$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['default']),'configuration_key=\'DEFAULT_ORDERS_STATUS_ID\'');
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$la=$action->db->fetchAll("select * from r_languages");
						$count=$action->db->fetchAll("select max(orders_status_id) as max from r_orders_status");
						
						$instid=$count[0][max]+1;
						foreach ($la as $k)
						{
							unset($data);
							//$data=array('orders_status_id'=>$instid,'email_template'=>$_REQUEST['email_temp_'.$k['languages_id']], 'subject'=>$_REQUEST['subject_'.$k['languages_id']],'orders_status_name'=>$_REQUEST['orders_status_name_'.$k['languages_id']],'public_flag'=>$_REQUEST['public_flag'],'downloads_flag'=>$_REQUEST['downloads_flag'],'language_id'=>$k['languages_id']);
							
							$data=array('orders_status_id'=>$instid,'email_template'=>$_REQUEST['email_temp_'.$k['languages_id']], 'subject'=>$_REQUEST['subject_'.$k['languages_id']],'orders_status_name'=>$_REQUEST['orders_status_name_'.$k['languages_id']],'language_id'=>$k['languages_id']);
							
							$data['html']=$_REQUEST['html']!=""?$_REQUEST['html']:"0";
							$model->insert($data);
						}
						$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['default']),'configuration_key=\'DEFAULT_ORDERS_STATUS_ID\'');
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
		}
		
		public function emailTemplatesAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rEmailFormat();
			$action=new Model_Adminaction();
			$this->_table="r_email_format";
			$this->_id="email_format_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				$count=$model->fetchAll($model->select()->from($this->_table)->where('language_id=1'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType="asc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'email_format_id':$this->_getParam('sortby');
				$select =$action->db->fetchAll("select * from ".$this->_table." where language_id='1' order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($this->_getParam('type')!="")
			{
				switch ($this->_getParam('type'))
				{
					case 'Edit':if($this->_getParam('play')!="")
					{
						$la=$action->db->fetchAll("select * from r_languages");
						foreach ($la as $k)
						{
							if($_REQUEST['rid']=='3')//final order
							{
								$my_file=PATH_TO_FILES."mail/order-".$k['code'].".phtml";
								$handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
								fwrite($handle,stripslashes($this->_getParam('email_temp_'.$k['languages_id'])));
							}
							unset($where);
							$where[]="language_id=".$k['languages_id'];
							$where[]="email_format_id=".(int)$this->_getParam('rid');
							
							$data=array('email_template'=>stripslashes($this->_getParam('email_temp_'.$k['languages_id'])),'subject'=>$this->_getParam('subject_'.$k['languages_id']));
							$data['html']=$_REQUEST['html']!=""?$_REQUEST['html']:"0";
							$model->update($data,$where);
							
						}
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($this->_getParam('play')=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$this->_getParam('page').'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					
					break;
				}
			}
		}
		
		public function pageAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rcms();
			$action=new Model_Adminaction();
			$this->_table="r_cms";
			$this->_id="page_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Pub': if($this->_getParam('rid')!="")
					{
						$data=array('status'=>'1');
						$action->RecordPublish('r_cms',$data,'page_id',$this->_getParam('rid'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'page?page='.$this->_getParam('page').'&action=Pub&msg='.base64_encode('selected page status changed!!'));
					}
					
					case 'UnPub':if($this->_getParam('rid')!="")
					{
						$data=array('status'=>'0');
						$action->RecordPublish('r_cms',$data,'page_id',$this->_getParam('rid'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'page?page='.$_REQUEST['page'].'&action='.base64_encode('UnPub&msg=selected page status changed!!'));
					}
					
					case 'Del':if($this->_getParam('rid')!="")
					{
						$action->RecordDelete($this->_table,$this->_id,$this->_getParam('rid'));
						$action->RecordDelete('r_cms_description',$this->_id,$this->_getParam('rid'));
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$this->_getParam('page').'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'page_id':$this->_getParam('sortby');
				$select =$action->db->fetchAll("select c.page_name,c.status,cd.* from r_cms c,r_cms_description cd where c.page_id=cd.page_id and cd.language_id='1'
				order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						Model_Cache::removeMATCache(array("information","general"));
						$action->lang_action(array("title","meta_title","meta_keywords",
						"meta_description","description"),	array("table"=>"r_cms_description",
						"comp_col_1"=>"page_id","comp_col_2"=>"language_id","rid"=>(int)$_REQUEST['rid']),'update');
						
						$data=array('page_name'=>$this->reqObj->request['page_name'],'status'=>$_REQUEST['status'],'sort_order'=>$_REQUEST['sort_order']);
						$model->update($data,$this->_id.'='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
						
					}
					//$action=new Model_Adminaction();
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						Model_Cache::removeMATCache(array("information","general"));
						$data=array('page_name'=>$this->reqObj->request['page_name'],'status'=>$_REQUEST['status'],'sort_order'=>$_REQUEST['sort_order']);
						$insert_id=$model->insert($data);
						
						$action->lang_action(array("page_id","title","meta_keywords",
						"meta_description","description"),	array("table"=>"r_cms_description",
						"comp_col_1"=>"page_id","comp_col_2"=>"language_id","rid"=>$insert_id),'insert');
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
						
					}
					break;
				}
			}
		}
		public function attributeAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$model = new Model_DbTable_rattribute();
			$action=new Model_Adminaction();
			$this->_table="r_attribute";
			$this->_id="attribute_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	
					if($_REQUEST['rid']!="")
					{
						// exit;
						Model_Cache::removeMTCache(array("attribute"));
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						foreach($rid as $k=>$v)
						{
							$fch=$action->db->fetchRow("select count(*) as count from r_product_attribute_group where attribute_id='".(int)$v."' and language_id='1'");
							if($fch['count']=='0')
							{
								$action->RecordDelete($this->_table,$this->_id,$v);
								$action->RecordDelete('r_attribute_description',$this->_id,$v);
							}else
							{
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('cannot delete as this attribute is used in some products!!'));   
							}
						}
						//exit;
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="asc";
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'agd.name':$_REQUEST['sortby'];
				//echo "select ag.*,agd.name as group_name,g.name as gname from r_attribute ag,r_attribute_description agd,r_attribute_group_description g where ag.attribute_id=agd.attribute_id and agd.language_id='1' and (ag.attribute_group_id=g.attribute_group_id and agd.language_id=g.language_id) order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT;
				//exit;
				$select =$action->db->fetchAll("select ag.*,agd.name as group_name,g.name as gname from r_attribute ag,r_attribute_description agd,r_attribute_group_description g
				where ag.attribute_id=agd.attribute_id and agd.language_id='1' and (ag.attribute_group_id=g.attribute_group_id and agd.language_id=g.language_id) order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						Model_Cache::removeMTCache(array("attribute"));
						$action->lang_action(array("name"),	array("table"=>"r_attribute_description",
						"comp_col_1"=>"attribute_id","comp_col_2"=>"language_id","rid"=>(int)$_REQUEST['rid']),'update');
						
						$data=array('sort_order'=>$_REQUEST['sort_order'],'attribute_group_id'=>$_REQUEST['attribute_group']);
						$model->update($data,'attribute_id='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						Model_Cache::removeMTCache(array("attribute"));
						$data=array('sort_order'=>$_REQUEST['sort_order'],'attribute_group_id'=>$_REQUEST['attribute_group']);
						$insert_id=$model->insert($data);
						
						$action->lang_action(array("name"),	array("table"=>"r_attribute_description",
						"comp_col_1"=>"attribute_id","comp_col_2"=>"language_id","rid"=>$insert_id),'insert');
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
		}
		public function returnReasonAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rreturnreason();
			$action=new Model_Adminaction();
			$this->_table="r_return_reason";
			$this->_id="return_reason_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						/*start delete return reason*/
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						$c=$action->db->fetchRow("select count(*) as count from r_return_product where return_reason_id in (".implode(',',$rid).")");
						if($c['count']=='0')
						{
							$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.base64_encode('selected rows deleted successfully!!'));
						}else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('Cannot delete as this return reason is used in  some returned products'));
						}
						/*end delete availability and out of stock status*/
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table)->where('language_id=1'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				
				$sortby=$this->_getParam('sortby')==''?$this->_id:$this->_getParam('sortby');
				$select =$action->db->fetchAll("select * from ".$this->_table." where  language_id='1'	order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$la=$action->db->fetchAll("select * from r_languages");
						foreach ($la as $k)
						{
							unset($where);
							$where[]="language_id=".$k['languages_id'];
							$where[]="return_reason_id=".(int)$_REQUEST['rid'];
							$data=array('name'=>$_REQUEST['name_'.$k['languages_id']]);
							$model->update($data,$where);
						}
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$la=$action->db->fetchAll("select * from r_languages");
						$count=$action->db->fetchAll("select max(return_reason_id) as max from r_return_reason");
						
						$instid=$count[0][max]+1;
						foreach ($la as $k)
						{
							unset($data);
							$data=array('return_reason_id'=>$instid,'name'=>$_REQUEST['name_'.$k['languages_id']],'language_id'=>$k['languages_id']);
							$model->insert($data);
						}
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
			
		}
		public function returnActionAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rreturnaction();
			$action=new Model_Adminaction();
			$this->_table="r_return_action";
			$this->_id="return_action_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						/*start delete return action after checking whether used in r_return_product table*/
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						$c=$action->db->fetchRow("select count(*) as count from r_return_product
						where return_action_id in (".implode(',',$rid).")");
						if($c['count']=='0')
						{
							$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.base64_encode('selected rows deleted successfully!!'));
						}else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&m=f&action=Del&msg='.base64_encode('Cannot delete as this return action is used in  some returned products'));
						}
						/*end delete return action*/
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table)->where('language_id=1'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				
				$sortby=$this->_getParam('sortby')==''?$this->_id:$this->_getParam('sortby');
				$select =$action->db->fetchAll("select * from ".$this->_table." where  language_id='1' order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$la=$action->db->fetchAll("select * from r_languages");
						foreach ($la as $k)
						{
							unset($where);
							$where[]="language_id=".$k['languages_id'];
							$where[]="return_action_id=".(int)$_REQUEST['rid'];
							$data=array('name'=>$_REQUEST['name_'.$k['languages_id']]);
							$model->update($data,$where);
						}
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$la=$action->db->fetchAll("select * from r_languages");
						$count=$action->db->fetchAll("select max(return_action_id) as max from r_return_action");
						
						$instid=$count[0][max]+1;
						foreach ($la as $k)
						{
							unset($data);
							$data=array('return_action_id'=>$instid,'name'=>$_REQUEST['name_'.$k['languages_id']],'language_id'=>$k['languages_id']);
							$model->insert($data);
						}
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
		}
		
		/*public function alertAction()
			{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_ralert();
			$action=new Model_Adminaction();
			$this->_table="r_alert";
			$this->_id="alert_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
			
			$count=$model->fetchAll($model->select()->from($this->_table)->where('language_id=1'));
			$total_count = count($count);
			
			$this->view->total_count=$total_count;
			$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
			$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
			$this->view->page=$page;
			$this->view->per_page=ADMIN_PAGE_LIMIT;
			
			$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
			$this->view->disType=$this->disType;
			
			$sortby=$this->_getParam('sortby')==''?$this->_id:$this->_getParam('sortby');
			$select =$action->db->fetchAll("select * from ".$this->_table." where  language_id='1' order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
			
			$this->view->results=$select;
			}else if($this->_getParam('type')!="")
			{
			switch ($this->_getParam('type'))
			{
			case 'Edit':if($this->_getParam('play')!="")
			{
			$la=$action->db->fetchAll("select * from r_languages");
			foreach ($la as $k)
			{
			unset($where);
			$where[]="language_id=".$k['languages_id'];
			$where[]="alert_id=".$_REQUEST['rid'];
			$data=array('value'=>$_REQUEST['value_'.$k['languages_id']]);
			
			$model->update($data,$where);
			}
			
			$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
			if($this->_getParam('play')=='save')
			{
			$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$this->_getParam('page').'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
			}
			}
			break;
			}
			}
			
		}*/
		
		public function returnStatusAction(){
			$this->checkSession($_SESSION['admin_id']);
			//$this->view->actionTitle=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$model = new Model_DbTable_rreturnstatus();
			$action=new Model_Adminaction();
			$this->_table="r_return_status";
			$this->_id="return_status_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						/*start delete availability status and out of stock status*/
						$rcat1=$action->getEditdetails('r_configuration','configuration_key','\'DEFAULT_RETURN_STATUS_ID\'');
						$rcat1[0]['configuration_value'];
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						if(!in_array($rcat1[0]['configuration_value'],$rid))
						{
							$c=$action->db->fetchRow("select count(*) as count from r_return
							where return_status_id in (".implode(',',$rid).")");
							if($c['count']=='0')
							{
								$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.base64_encode('selected rows deleted successfully!!'));
							}else
							{
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('Cannot delete as this return status is used in  some products returned'));
							}
						}
						else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('Cannot delete default return status'));
						}
						/*end delete availability and out of stock status*/
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table)->where('language_id=1'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				
				$sortby=$_REQUEST['sortby']==''?$this->_id:$_REQUEST['sortby'];
				$select =$action->db->fetchAll("select * from ".$this->_table." where  language_id='1'
				order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$la=$action->db->fetchAll("select * from r_languages");
						foreach ($la as $k)
						{
							unset($where);
							$where[]="language_id=".$k['languages_id'];
							$where[]="return_status_id=".(int)$_REQUEST['rid'];
							$data=array('name'=>$_REQUEST['name_'.$k['languages_id']]);
							$model->update($data,$where);
						}
						$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['rid']),'configuration_key=\'DEFAULT_RETURN_STATUS_ID\'');
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$la=$action->db->fetchAll("select * from r_languages");
						$count=$action->db->fetchAll("select max(return_status_id) as max from r_return_status");
						
						$instid=$count[0][max]+1;
						foreach ($la as $k)
						{
							unset($data);
							$data=array('return_status_id'=>$instid,'name'=>$_REQUEST['name_'.$k['languages_id']],'language_id'=>$k['languages_id']);
							$model->insert($data);
						}
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
		}
        
		public function stockStatusAction(){
        	$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rstockstatus();
			$action=new Model_Adminaction();
			$this->_table="r_stock_status";
			$this->_id="stock_status_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	
					if($_REQUEST['rid']!="")
					{
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						
						/*start delete availability status and out of stock status*/
						$rcat1=$action->getEditdetails('r_configuration','configuration_key','\'DEFAULT_OUT_STOCK_STATUS_ID\'');
						$rcat1[0]['configuration_value'];
						$rcat2=$action->getEditdetails('r_configuration','configuration_key','\'DEFAULT_AVAILABILITY_STOCK_STATUS_ID\'');
						$rcat2[0]['configuration_value'];
						
						if(!in_array($rcat1[0]['configuration_value'],$rid) && !in_array($rcat2[0]['configuration_value'],$rid))
						{
							$c=$action->db->fetchRow("select count(*) as count from r_products where stock_status_id in (".implode(',',$rid).")");
							if($c['count']=='0')
							{
								$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.base64_encode('selected rows deleted successfully!!'));
							}else
							{
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('Cannot delete as this stock status is used in  some products'));
							}
						}
						else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('Cannot delete default stock status'));
						}
						/*end delete availability and out of stock status*/
						
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table)->where('language_id=1'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				
				$sortby=$this->_getParam('sortby')==''?$this->_id:$this->_getParam('sortby');
				$select =$action->db->fetchAll("select * from ".$this->_table." where  language_id='1'	order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$la=$action->db->fetchAll("select * from r_languages");
						foreach ($la as $k)
						{
							unset($where);
							$where[]="language_id=".$k['languages_id'];
							$where[]="stock_status_id=".(int)$_REQUEST['rid'];
							$data=array('name'=>$_REQUEST['name_'.$k['languages_id']]);
							$model->update($data,$where);
						}
						$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['rid']),'configuration_key="'.$_REQUEST['default'].'"');
						
						//$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['availability']),'configuration_key=\'DEFAULT_AVAILABILITY_STOCK_STATUS_ID\'');
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					break;
					
					case 'Add':	
					if($_REQUEST['play']!="")
					{
						$la=$action->db->fetchAll("select * from r_languages");
						$count=$action->db->fetchAll("select max(stock_status_id) as max from r_stock_status");
						$instid=$count[0][max]+1;
						foreach ($la as $k)
						{
							unset($data);
							$data=array('stock_status_id'=>$instid,'name'=>$_REQUEST['name_'.$k['languages_id']],'language_id'=>$k['languages_id']);
							$model->insert($data);
						}
						$action->db->update('r_configuration',array("configuration_value"=>$instid),'configuration_key="'.$_REQUEST['default'].'"');
						
						//	$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['outofstock']),'configuration_key=\'DEFAULT_OUT_STOCK_STATUS_ID\'');
						
						//	$action->db->update('r_configuration',array("configuration_value"=>$_REQUEST['availability']),'configuration_key=\'DEFAULT_AVAILABILITY_STOCK_STATUS_ID\'');
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
		}
		
		public function giftVoucherThemeAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rvouchertheme();
			$this->_table=r_voucher_theme;
			$this->_id=voucher_theme_id;
			$this->view->r_table=r_voucher_theme;
			$this->view->r_id=voucher_theme_id;
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($this->_getParam('rid')!="")
					{
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);    
						$fch=$action->db->fetchRow("select count(voucher_id) as count from r_voucher where  voucher_theme_id in (".implode(',',$rid).")");
						if($fch[count]==0)
						{
							foreach($rid as $k=>$v)
							{	$fch=$action->db->fetchRow("select image from r_voucher_theme where voucher_theme_id='".(int)$v."'");
								@unlink(PATH_TO_UPLOADS_DIR."/image/".$fch[image]);
							}
							
							$action->RecordDelete('r_voucher_theme','voucher_theme_id',$_REQUEST['rid']);
							$action->RecordDelete('r_voucher_theme_description','voucher_theme_id',$_REQUEST['rid']);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$this->_getParam('page').'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
						}else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$this->_getParam('page').'&m=f&msg='.base64_encode('cannot delete theme as it is used in some gift vouchers'));
						}
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'t.voucher_theme_id':$this->_getParam('sortby');
				$select =$action->db->fetchAll("select td.name,t.voucher_theme_id from r_voucher_theme t,r_voucher_theme_description td
				where t.voucher_theme_id=td.voucher_theme_id and td.language_id='1' order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$upload=$act_ext->fnupload(	array("action"=>$this->_action,"type"=>"Edit","field"=>"image",
						"path"=>PATH_TO_UPLOADS_DIR."/image/","prev_img"=>$_REQUEST['prev_image'],"prefix"=>"gvt_".$_REQUEST['rid']));
						$action->lang_action(array("name"),	array("table"=>"r_voucher_theme_description",
						"comp_col_1"=>"voucher_theme_id","comp_col_2"=>"language_id","rid"=>(int)$_REQUEST['rid']),'update');
						
						$data=array('image'=>$upload);
						$model->update($data,'voucher_theme_id='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					break;
					
					case 'Add':if($_REQUEST['play']!="")
					{
						$fch=$action->db->fetchRow("select max(categories_id) as catid from r_categories");
						$upload=$act_ext->fnupload(	array("action"=>$this->_action,"type"=>"Add","field"=>"image",
						"path"=>PATH_TO_UPLOADS_DIR."/image/","prev_img"=>$_REQUEST['prev_image'],"prefix"=>"gvt_".$fch[catid]+1));
						$data=array('image'=>$upload);
						$insert_id=$model->insert($data);
						
						$action->lang_action(array("name"),	array("table"=>"r_voucher_theme_description",
						"comp_col_1"=>"voucher_theme_id","comp_col_2"=>"language_id","rid"=>$insert_id),'insert');
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
		}
		
		public function lengthClassAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rlengthclass();
			$action=new Model_Adminaction();
			$this->_table="r_length_class";
			$this->_id="length_class_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);    
						$c=$action->db->fetchRow("select count(*) as count from r_products	where length_class_id in (".implode(',',$rid).")");
						if($c['count']=='0'){
							$action->RecordDelete($this->_table,$this->_id,$rid);
							$action->RecordDelete('r_length_class_description',$this->_id,$rid);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->_action.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
						}
						else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('cannot delete as some of the products use this lenght class'));
						}
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?$this->_id:$this->_getParam('sortby');
				$select =$action->db->fetchAll("select ag.*,agd.title as name from r_length_class ag,r_length_class_description agd
				where ag.length_class_id=agd.length_class_id and agd.language_id='1' order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$action->lang_action(array("title","unit"),	array("table"=>"r_length_class_description",
						"comp_col_1"=>"length_class_id","comp_col_2"=>"language_id","rid"=>(int)$_REQUEST['rid']),'update');
						
						$data=array('value'=>$_REQUEST['value']);
						$model->update($data,'length_class_id='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$data=array('value'=>$_REQUEST['value']);
						$insert_id=$model->insert($data);
						
						$action->lang_action(array("title","unit"),	array("table"=>"r_length_class_description",
						"comp_col_1"=>"length_class_id","comp_col_2"=>"language_id","rid"=>$insert_id),'insert');
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
		}
        
		public function weightClassAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rweightclass();
			$action=new Model_Adminaction();
			$this->_table="r_weight_class";
			$this->_id="weight_class_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	
					if($_REQUEST['rid']!="")
					{
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						$c=$action->db->fetchRow("select count(*) as count from r_products	where weight_class_id in (".implode(',',$rid).")");
						if($c['count']=='0')
						{
							$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
							$action->RecordDelete('r_weight_class_description',$this->_id,$_REQUEST['rid']);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->_action.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
						}
						else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('cannot delete as some of the products use this weight class'));
						}
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from($this->_table));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?$this->_id:$this->_getParam('sortby');
				$select =$action->db->fetchAll("select ag.*,agd.title as name from r_weight_class ag,r_weight_class_description agd
				where ag.weight_class_id=agd.weight_class_id and agd.language_id='1' order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$action->lang_action(array("title","unit"),	array("table"=>"r_weight_class_description",
						"comp_col_1"=>"weight_class_id","comp_col_2"=>"language_id","rid"=>(int)$_REQUEST['rid']),'update');
						
						$data=array('value'=>$_REQUEST['value']);
						$model->update($data,'weight_class_id='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$data=array('value'=>$_REQUEST['value']);
						$insert_id=$model->insert($data);
						
						$action->lang_action(array("title","unit"),	array("table"=>"r_weight_class_description",
						"comp_col_1"=>"weight_class_id","comp_col_2"=>"language_id","rid"=>$insert_id),'insert');
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
			
		}
		
		
		public function downloadsAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$model = new Model_DbTable_rdownload();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			$this->_table="r_download";
			$this->_id="download_id";
			
			//start download file
			if ($_REQUEST['file']!="") 
			{
				$file = PATH_TO_UPLOADS_DIR."downloads/".$_REQUEST['file'];	
				$mask = basename($_REQUEST['file']);
				$mime = 'application/octet-stream';
				$encoding = 'binary';
				if (!headers_sent()) 
				{
					if (file_exists($file)) 
					{
						header('Pragma: public');
						header('Expires: 0');
						header('Content-Description: File Transfer');
						header('Content-Type: ' . $mime);
						header('Content-Transfer-Encoding: ' . $encoding);
						header('Content-Disposition: attachment; filename='.($mask ? $mask : basename($file)));
						header('Content-Length: ' . filesize($file));
						
						$file = readfile($file);
						
						print($file);
						exit;
					}
				}
			}
			//end download file
			
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						//$c=$action->db->fetchRow("select count(*) as count from r_product_to_download	where download_id in (".implode(',',$rid).")");
						//echo "select count(*) as count from r_product_to_download	where download_id in (".implode(',',$rid).")";
						//exit;
						$qry=$action->db->query("select (select pd.products_name from r_products_description pd where pd.products_id=p.product_id) as name from r_product_to_download p where p.download_id in (".implode(',',$rid).")");
						//$c=$action->db->query("select count(*) as count from r_product_to_download	where download_id in (".implode(',',$rid).")");
						
						$count=$qry->rowCount();
						if($count=='0')
						{
							foreach($rid as $k=>$v)
							{
								$fch=$action->db->fetchRow("select filename from r_download where download_id='".$v."'");
								@unlink(PATH_TO_UPLOADS_DIR."/downloads/".$fch[filename]);
							}
							$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
							$action->RecordDelete('r_download_description','download_id',$_REQUEST['rid']);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.base64_encode('selected rows deleted successfully!!'));
						}else
						{
							$p="";
							foreach($qry->fetchAll() as $k=>$v)
							{
								$p.=$s.$v[name];
								$s=",";
							}
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('Cannot delete as this download file is used in products like '.$p.'.'));
						}
					} 
					break;
				}
				
				$count=$model->fetchAll($model->select()->from('r_download'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'download_id':$_REQUEST['sortby'];
				$select =$action->db->fetchAll("select ag.*,agd.name from r_download ag,r_download_description agd
				where ag.download_id=agd.download_id and agd.language_id='1' order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$filename=$act_ext->fnupload(array("action"=>$this->_action,"type"=>"Edit","field"=>"image","path"=>PATH_TO_UPLOADS_DIR."/downloads/","prev_img"=>$_REQUEST['prev_image'],"prefix"=>"download_".$_REQUEST['rid']));
						
						$action->lang_action(array("name"),	array("table"=>"r_download_description",
						"comp_col_1"=>"download_id","comp_col_2"=>"language_id","rid"=>(int)$_REQUEST['rid']),'update');
						
						$data=array('date_added'=>$this->_date,'filename'=>$filename,'remaining'=>$this->reqObj->request['remaining']);
						$model->update($data,'download_id='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						$fch=$action->db->fetchRow("select max(download_id) as catid from r_download");
						$pre=$fch[catid]+1;
						$filename=$act_ext->fnupload(array("action"=>$this->_action,"type"=>"Add","field"=>"image","path"=>PATH_TO_UPLOADS_DIR."/downloads/","prev_img"=>$_REQUEST['prev_image'],"prefix"=>"download_".$pre));
						
						$data=array('remaining'=>$this->reqObj->request['remaining'],'date_added'=>$this->_date,'filename'=>$filename);
						$insert_id=$model->insert($data);
						
						$action->lang_action(array("name"),	array("table"=>"r_download_description",
						"comp_col_1"=>"download_id","comp_col_2"=>"language_id","rid"=>$insert_id),'insert');
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
			
		}
		public function attributegroupAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$model = new Model_DbTable_rattributegroup();
			$action=new Model_Adminaction();
			
			if($_REQUEST['type']=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($_REQUEST['rid']!="")
					{
						Model_Cache::removeMTCache(array("attribute"));
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						foreach($rid as $k=>$v)
						{
							$fch=$action->db->fetchRow("select count(*) as count from r_attribute where attribute_group_id='".(int)$v."'");
							if($fch['count']=='0')
							{
								$action->RecordDelete("r_attribute_group","attribute_group_id",array($v));//it will deleted in the bottom
								$action->RecordDelete('r_attribute_group_description',"attribute_group_id",array($v));
								
							}else
							{
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'attributegroup?page='.$_REQUEST['page'].'&action=Del&m=f&msg='.base64_encode('cannot delete as this attribute group is used in some attributes!!'));   
							}
						}
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from('r_attribute_group'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'sort_order':$_REQUEST['sortby'];
				$select =$action->db->fetchAll("select ag.*,agd.name from r_attribute_group ag,r_attribute_group_description agd
				where ag.attribute_group_id=agd.attribute_group_id and agd.language_id='1' order by ".$sortby." ".$this->disType. " limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						Model_Cache::removeMTCache(array("attribute"));
						$action->lang_action(array("name"),	array("table"=>"r_attribute_group_description",
						"comp_col_1"=>"attribute_group_id","comp_col_2"=>"language_id","rid"=>(int)$_REQUEST['rid']),'update');
						
						$data=array('sort_order'=>$_REQUEST['sort_order']);
						$model->update($data,'attribute_group_id='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						Model_Cache::removeMTCache(array("attribute"));
						$data=array('sort_order'=>$_REQUEST['sort_order']);
						$insert_id=$model->insert($data);
						
						$action->lang_action(array("name"),	array("table"=>"r_attribute_group_description",
						"comp_col_1"=>"attribute_group_id","comp_col_2"=>"language_id","rid"=>$insert_id),'insert');
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
			
		}
        
        public function copyProduct($id)
        {
    	    $this->db = Zend_Db_Table::getDefaultAdapter();
            //r_products
            $prow=$this->db->fetchRow("select * from r_products where products_id='".(int)$id."'");
            $pre_pid=$prow['products_id'];
            unset($prow['products_id']);
            unset($prow['products_date_added']);
            unset($prow['products_last_modified']);
            unset($prow['viewed']);
            $prow['products_date_added']=$this->_date;
            $this->db->insert('r_products',$prow);
            $inst_id=$this->db->lastInsertId();
			
            $pimage=$inst_id.'_copy_'.$pre_pid.'_'.$prow['products_image'];
            copy(PATH_TO_UPLOADS_DIR.'products/'.$prow['products_image'], PATH_TO_UPLOADS_DIR.'products/'.$pimage);	
            $this->db->update('r_products',array("products_image"=>$pimage),"products_id=".$inst_id);
			
            //r_products_description
            $prow=$this->db->fetchRow("select * from r_products_description where products_id='".(int)$id."'");
            unset($prow['products_id']);
            //unset($prow['products_image']);
            $prow['products_id']=$inst_id;
            //$prow['products_image']=$prow['products_image']."_".$inst_id;
            $this->db->insert('r_products_description',$prow);
			
            //r_products_images
            $pimages=$this->db->fetchAll("select * from r_products_images where products_id='".(int)$id."'");
            foreach($pimages as $k=>$v)
            {
				unset($v['products_id']);
				unset($v['id']);
				$v['products_id']=$inst_id;
				$multimage=$inst_id.'_copy_'.$pre_pid.'_'.$v['image'];
				copy(PATH_TO_UPLOADS_DIR.'products/'.$v['image'], PATH_TO_UPLOADS_DIR.'products/'.$multimage);	
				unset($v['image']);
				$v['image']=$multimage;
				$this->db->insert('r_products_images',$v);
			}
			
            //r_products_option
            $product_option_id=array();
            $poptions=$this->db->fetchAll("select * from r_products_option where product_id='".(int)$id."' order by product_option_id asc");
			
            foreach($poptions as $k=>$v)
            {
                $product_option_id_value=$v['product_option_id'];
				unset($v['product_id']);
				unset($v['product_option_id']);
				$v['product_id']=$inst_id;
				$this->db->insert('r_products_option',$v);
				$product_option_id[$product_option_id_value]=$this->db->lastInsertId();
			}
            
            /*echo "<pre>";    
				print_r($poptions);
				echo "</pre>";
			exit;*/
			
            //r_products_option_value
            $poptionsvalue=$this->db->fetchAll("select * from r_products_option_value where product_id='".(int)$id."' order by product_option_value_id asc");
            foreach($poptionsvalue as $k=>$v)
            {
                $poid=$v['product_option_id'];
				unset($v['product_id']);
				$v['product_id']=$inst_id;
				unset($v['product_option_value_id']);
				unset($v['product_option_id']);
				$v['product_option_id']=$product_option_id[$poid];
				$this->db->insert('r_products_option_value',$v);
			}
			
            //r_products_specials
            $pspecial=$this->db->fetchAll("select * from r_products_specials where products_id='".(int)$id."'");
            foreach($pspecial as $k=>$v)
            {
				unset($v['products_id']);
				unset($v['specials_id']);
				$v['products_id']=$inst_id;
				$this->db->insert('r_products_specials',$v);
			}
			
            //r_products_to_categories
            $pcat=$this->db->fetchAll("select * from r_products_to_categories where products_id='".(int)$id."'");
            foreach($pcat as $k=>$v)
            {
				unset($v['products_id']);
				$v['products_id']=$inst_id;
				$this->db->insert('r_products_to_categories',$v);
			}
			
			
            //r_product_attribute_group
            $pagroup=$this->db->fetchAll("select * from r_product_attribute_group where product_id='".(int)$id."'");
            foreach($pagroup as $k=>$v)
            {
				unset($v['product_id']);
				$v['product_id']=$inst_id;
				$this->db->insert('r_product_attribute_group',$v);
			}
			
            //r_product_discount
            $pdisc=$this->db->fetchAll("select * from r_product_discount where product_id='".(int)$id."'");
            foreach($pdisc as $k=>$v)
            {
				unset($v['product_id']);
				unset($v['product_discount_id']);
				$v['product_id']=$inst_id;
				$this->db->insert('r_product_discount',$v);
			}
			
            //r_product_related
            $prealted=$this->db->fetchAll("select * from r_product_related where product_id='".(int)$id."'");
            foreach($prealted as $k=>$v)
            {
				unset($v['product_id']);
				$v['product_id']=$inst_id;
				$this->db->insert('r_product_related',$v);
			}
			
            //r_product_reward
            $preward=$this->db->fetchAll("select * from r_product_reward where product_id='".(int)$id."'");
            foreach($preward as $k=>$v)
            {
				unset($v['product_id']);
				unset($v['product_reward_id']);
				$v['product_id']=$inst_id;
				$this->db->insert('r_product_reward',$v);
			}
			
            //r_product_tag
            $ptag=$this->db->fetchAll("select * from r_product_tag where products_id='".(int)$id."'");
            foreach($ptag as $k=>$v)
            {
				unset($v['products_id']);
				unset($v['product_tag_id']);
				$v['products_id']=$inst_id;
				$this->db->insert('r_product_tag',$v);
			}
			
            //r_product_to_download
            $pdownload=$this->db->fetchAll("select * from r_product_to_download where product_id='".(int)$id."'");
            foreach($pdownload as $k=>$v)
            {
				unset($v['product_id']);
				$v['product_id']=$inst_id;
				$this->db->insert('r_product_to_download',$v);
			}
		}
		
		public function productsAction()
		{
			/* $bootstrap = $this->getInvokeArg('bootstrap');
                $options = $bootstrap->getOptions();
				$action->p($options,'0');
			echo "asfd".$options[resources][db][params][host];*/
			// echo "<pre>";
			// print_r($_REQUEST);
			
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$_REQUEST['type'];
			$model = new Model_DbTable_rproducts();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			
			
			if($_REQUEST['action']=='Copy')
			{
				if(sizeof($_REQUEST['rid'])>0)
				{
					foreach($_REQUEST['rid'] as $k=>$v)
					{
						$this->copyProduct($v);
					}
                    $this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode('Selected products copied successfully!!'));
				}else
				{
                    $this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.base64_encode('No Products selected!!'));
				}
			}
			
			if($_REQUEST['type']=='undo' && $this->view->spl!="1")
			{
				//echo "inside";
				//exit;
				$return=$act_ext->undoProductDelete();
				$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?msg='.base64_encode($return));
			}else if($_REQUEST['type']=='undo' && $this->view->spl=="1")
			{
				$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle);
			}
			
			//end unto delete
			$act_ext->trashProducts();        
			/*for dependent options if dependent options is enabled*/
			if(@constant('DEPENDENT_OPTIONS')=='1')
			{
				$base=$act_ext->getBaseDropDown();
				$base_result_array=$act_ext->getBaseResultArray();
				$this->view->base_result_array=$base_result_array;
				$this->view->base=$base;
			}
			/*for dependent options*/
			
			if($_REQUEST['type']=='')
			{
				
				/*start search*/
				$search=array();
				$pageing=array();
				/*echo "<pre>";
					print_r($_REQUEST);
					echo "</pre>";
				exit;*/
				if($this->_getParam('prodname')!="Product Name" && $this->_getParam('prodname')!="")
				{
					$search[]='trim(lower(rpd.products_name)) like "%'.trim(strtolower(addslashes($this->_getParam('prodname')))).'%"';
					$pageing[prodname]=$this->_getParam('prodname');
				}
				
				if($_REQUEST['prodmodel']!="Model" && $this->_getParam('prodmodel')!="")
				{
					$search[]='lower(rp.products_model) like "%'.strtolower(addslashes($this->_getParam('prodmodel'))).'%"';
					$pageing[prodmodel]=$this->_getParam('prodmodel');
				}
				
				if($_REQUEST['brand']!="Manufacturer"  && $this->_getParam('brand')!="")
				{
					$row=$action->db->fetchRow("select manufacturers_id from r_manufacturers where lower(manufacturers_name)='".strtolower($_REQUEST['brand'])."'");
					//echo 'lower(rp.manufacturers_id) like "'.$row[manufacturers_id].'"';
					$search[]='lower(rp.manufacturers_id) like "%'.strtolower($row[manufacturers_id]).'%"';
					$pageing[brand]=$this->_getParam('brand');
				}
				
				if($_REQUEST['prodstatus']!="")
				{
					$search[]='rp.products_status = "'.$this->_getParam('prodstatus').'"';
					$pageing[prodstatus]=$this->_getParam('prodstatus');
				}
				
				if($_REQUEST['prodqty']!="Number" && $this->_getParam('prodqty')!="")
				{
					$search[]='lower(rp.products_quantity) '.$_REQUEST['qty_sym'] .' '.trim($_REQUEST['prodqty']);
					$pageing[prodqty]=$this->_getParam('prodqty');
			        $pageing[qty_sym]=$this->_getParam('qty_sym');
				}
				
				if($_REQUEST['cat']!="Category" && $this->_getParam('cat')!="")
				{
					$exp_cat=explode(">",$_REQUEST['cat']);
					if(sizeof($exp_cat)>1)
					{
						$cat=$action->db->fetchRow("select c.categories_id from r_categories_description cd,r_categories c where cd.language_id=1 and lower(cd.categories_name) like '".trim(strtolower($exp_cat[0]))."' and cd.categories_id=c.categories_id and c.parent_id=(select categories_id from r_categories_description where language_id=1 and lower(categories_name) like '".trim(strtolower($exp_cat[1]))."')");
					}
					else
					{
						$cat=$action->db->fetchRow("select categories_id from r_categories_description where language_id=1 and lower(categories_name) like '".trim(strtolower($_REQUEST['cat']))."'"); 
					}
					$search[]="rp.products_id in (select rpc.products_id from r_products_to_categories rpc where rpc.categories_id='".$cat['categories_id']."')";
					
					//  echo " and rp.products_id in (select rpc.products_id from r_products_to_categories rpc where rpc.categories_id='".$cat['categories_id']."')";
					//exit;
					$pageing[cat]=$this->_getParam('cat');
				}
				
				if(count($search)>0)
				{
					$srch_str=implode(' and ', $search);
					$srch_str=" and ".$srch_str;
					
				}else
				{
					$srch_str="";
				}
				/*end search*/
				
				switch($_REQUEST['action'])
				{
					
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						foreach($rid as $k=>$v)
						{
							Model_Cache::removeMTCache(array("product_multiple_images_".$v));
							$action->updateRow(array('table'=>'r_products','cols'=>array('del'=>'1','products_status'=>'0','products_last_modified'=>$this->_date),'where'=>array('products_id=?'=>$v)));
						}
						$this->createIndex();
						//exit;
						//$action->RecordDelete('r_zones','zone_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$action->db->fetchRow("select count(rp.products_id) as count from r_products rp,r_products_description rpd where rpd.language_id='1' and rp.del='0' and rp.products_id=rpd.products_id ".$srch_str);
				
				$total_count = $count['count'];//count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->paginationP(ADMIN_URL_CONTROLLER.$this->view->actionTitle,$pageing);
				//$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType='desc';
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'products_id':$_REQUEST['sortby'];
				
				$select =$action->db->fetchAll("select rp.products_id,rp.products_price,rp.products_quantity,rp.products_model,rp.products_status,rpd.products_name from r_products rp,r_products_description rpd  where rp.del='0' and rp.products_id=rpd.products_id and rpd.language_id='1' ".$srch_str." order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
        		switch ($_REQUEST['type'])
				{
					
					case 'Edit':if($_REQUEST['play']!="")
					{
						
						Model_Cache::removeMTCache(array("product_multiple_images_".(int)$_REQUEST['rid']));
						
						$seo_keyword = preg_replace('/[^a-zA-Z0-9]/', '_', $_REQUEST['keyword']);
						$act_ext->setSeoKeyword(array("type"=>"insert","query"=>"product","id"=>(int)$_REQUEST['rid'],"keyword"=>$seo_keyword));
						
						//echo $_REQUEST['ship_req'];
						//exit;
						//exit;
						/*echo "<pre>";
							print_r($_FILES[multi_image]);
							PRINT_R($_REQUEST[multi_image_text]);
							echo "count".$_REQUEST['multi_image_count'];
							echo "hidden".$_REQUEST['multi_image_hidden'];
						echo "<pre>";*/
						//echo $_REQUEST['multi_image_hidden'];
						
						/*start multiple image upload*/
						$act_ext->delMultiImage();
						$act_ext->uploadMultipleImage((int)$this->_getParam('rid'));
						/*end multiple image upload*/
						//exit;
						//		$action->p($_REQUEST['downloads'],'1');
						//start download
						$action->db->delete('r_product_to_download',"product_id=".(int)$_REQUEST['rid']);
						if(count($_REQUEST['downloads'])>0)
						{
							foreach($_REQUEST['downloads'] as $k=>$v)
							{
								if($v!="")
								{
									$action->db->insert('r_product_to_download',array('download_id'=>$v,'product_id'=>(int)$_REQUEST['rid']));
								}
							}
						}
						//end download
						/*start reward points*/
						$action->db->delete('r_product_reward','product_id='.(int)$_REQUEST['rid']);
						foreach($_REQUEST[r_product_reward][points] as $k=>$v)
						{
							$action->db->insert('r_product_reward',array('points'=>$v,
							'customer_group_id'=>$_REQUEST[r_product_reward][customer_group_id][$k],
							'product_id'=>(int)$_REQUEST['rid']));
						}
						//$action->p($reward_points_data,'1');
						
						//$action->db->update();
						/*end reward points*/
						
						/*start related products*/
						if($_REQUEST['related_product']!='')
						{
							//$exp=explode(",",substr($_REQUEST['related_product'],0,-1));
							$exp=explode(",",$_REQUEST['related_product']);
							$action->db->delete('r_product_related','product_id='.(int)$_REQUEST[rid]);
							foreach($exp as $k=>$v)
							{
								$related_data['product_id']=(int)$_REQUEST['rid'];
								$related_data['related_id']=$v;
								$action->db->insert('r_product_related',$related_data);
							}
						}else{$action->db->delete('r_product_related','product_id='.(int)$_REQUEST[rid]);}
						//exit;
						/*end related products*/
						//$action->p($_REQUEST['r_product_related'],'1');
						$action->product_option_multiple('update',(int)$_REQUEST['rid'],'select');
						$action->product_option_multiple('update',(int)$_REQUEST['rid'],'radio');
						$action->product_option_multiple('update',(int)$_REQUEST['rid'],'checkbox');
						/*foreach($_REQUEST['select_cg'] as $k=>$v)
							{
							if($_REQUEST['select_rem_'.$k]!='1')
							{
							$exp=explode("_",$v);
							$arr_opt_id[]=$exp[1];
							$arr_req[$exp[1]]=$arr_req[$exp[1]]+$_REQUEST['select_req'][$k];
							$arr_opt_val_id[]=$exp[0];
							}
							}
							$unq_arr_opt_id=array_unique($arr_opt_id);
							
							$action->p($unq_arr_opt_id,'0');
						$action->p($arr_req,'1');*/
						
						//$action->p($_REQUEST['disc_price'],'0');
						//$action->p($_REQUEST,'1');
						//print_r($_REQUEST['categories_id']);
						//$action->p($_REQUEST,'1');
						/*start text,textarea,file option*/
						$action->product_option_text('update',(int)$_REQUEST['rid'],'text');
						$action->product_option_text('update',(int)$_REQUEST['rid'],'textarea');
						$action->product_option_text('update',(int)$_REQUEST['rid'],'file');
						$action->product_option_text('update',(int)$_REQUEST['rid'],'date');
						$action->product_option_text('update',(int)$_REQUEST['rid'],'time');
						$action->product_option_text('update',(int)$_REQUEST['rid'],'datetime');
						//exit;
						/*end text,textarea,file option*/
						
						/*start attribute*/
						$action->productattribute('update','');//modified on oct 10 2011
						/*end attribute*/
						
						/*start discount*/
						if($_REQUEST['disc_hid_val']>0)
						{
							$action->productdiscount('update','');
						}
						/*end discount*/
						/*start categories*/
						$action->db->delete("r_products_to_categories","products_id=".(int)$_REQUEST['rid']);
						if(count($_REQUEST['categories_id'])>0)
						{
							foreach($_REQUEST['categories_id'] as $k=>$v)
							{
								$data=array("products_id"=>(int)$_REQUEST['rid'],"categories_id"=>$v);
								$action->db->insert("r_products_to_categories",$data);
							}
						}
						//exit;
						/*end categories*/
						
						$action->lang_action(array("products_name","meta_keywords","meta_description",								"products_description"),
						array("table"=>"r_products_description","comp_col_1"=>"products_id","comp_col_2"=>"language_id",
						"rid"=>(int)$_REQUEST['rid']),'update');
						
						$products_image=$act_ext->fnupload(array("action"=>$this->_action,"type"=>"Edit","field"=>"products_image","path"=>PATH_TO_UPLOADS_DIR."products/","prev_img"=>$_REQUEST['prev_image'],"prefix"=>"prod_".(int)$_REQUEST['rid']));
						
						if($_REQUEST['sku']!="")
						{
							$rowsku=$action->db->fetchRow("select count(*) as count from r_products where sku='".$_REQUEST['sku']."' and products_id!='".(int)$_REQUEST['rid']."'");
							if($rowsku[count]>0)
							{
								$sku_error=base64_encode("sku already exists please try another!!");
								$sk="";
							}else
							{
								$sku=$_REQUEST['sku']; 
							}
						}else
						{
							$sku="";
							$sku_error="";
						}
						
						
						
						$data=array('shipping'=>$this->reqObj->request['ship_req'],'sku'=>$sku,'length'=>$this->reqObj->request['length'],'width'=>$this->reqObj->request['width'],'height'=>$this->reqObj->request['height'],'weight_class_id'=>$this->reqObj->request['weight_class_id'],'length_class_id'=>$this->reqObj->request['length_class_id'],'upc'=>$_REQUEST['upc'],'products_minimum_quantity'=>(int)$_REQUEST['products_minimum_quantity'],'substract_stock'=>$_REQUEST['substract_stock'],'stock_status_id'=>(int)$_REQUEST['stock_status_id'],'products_points'=>$_REQUEST['reward_points'],'products_quantity'=>(int)$_REQUEST['products_quantity'],'products_model'=>$this->reqObj->request['products_model'],'products_price'=>preg_replace('/[^0-9.]/s', '', $_REQUEST['products_price']),'products_last_modified'=>$this->_date,'products_date_available'=>$this->reqObj->request['products_date_available'],'products_weight'=>$this->reqObj->request['products_weight'],'products_status'=>$_REQUEST['products_status'],'products_tax_class_id'=>(int)$_REQUEST['products_tax_class_id'],'manufacturers_id'=>(int)$_REQUEST['manufacturers_id'],'sort_order'=>(int)$_REQUEST['product_sort_order'],'products_image'=>$products_image);
						
						$model->update($data,'products_id='.(int)$_REQUEST['rid']);
						
						//start products tag
						if($_REQUEST['tags']!="")
						{
							$action->db->delete('r_product_tag','products_id="'.(int)$_REQUEST[rid].'"');
							$exp=explode(",",$_REQUEST['tags']);
							foreach($exp as $k=>$v)
							{
								$action->db->insert('r_product_tag',array("products_id"=>(int)$_REQUEST['rid'],"tag"=>$v));
							}
						}
						//end products tag
						
						
						//start special
						$action->productspecial('update','');
						//end special
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						//$this->createIndex();
						$this->createIndex();
						Model_Cache::removeAllCache();
						if($_REQUEST['play']=='save')
						{
							if($sku_error!="")
							{
								$url="&m=1&msg=".$sku_error;
							}else
							{
								$url='&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle);
							}
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$url.$_REQUEST['page']);
						}
						
					}
					$sel1=$action->getEditdetails('r_products','products_id',(int)$_REQUEST['rid']);
					$this->view->seo_keyword=$act_ext->setSeoKeyword(array("type"=>"select","query"=>"product","id"=>(int)$_REQUEST['rid']));
					$this->view->data = $sel1;
					
					break;
					
					case 'Add':	
					if($this->view->spl=='1')
					{
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle);
					}
					if($_REQUEST['play']!="")
					{
						//exit("inside");
						Model_Cache::removeMTCache(array("product_multiple_images_".(int)$_REQUEST['rid']));
						$fch=$action->db->fetchRow("select max(products_id) as catid from r_products");
						$pre=$fch[catid]+1;
						$products_image=$act_ext->fnupload(array("action"=>$this->_action,"type"=>"Add","field"=>"products_image","path"=>PATH_TO_UPLOADS_DIR."/products/","prev_img"=>"","prefix"=>"prod_".$pre));
						//$_REQUEST['substract_stock']="1"; no more use
						if($_REQUEST['sku']!="")
						{
							$rowsku=$action->db->fetchRow("select count(*) as count from r_products where sku='".$_REQUEST['sku']."'");
							if($rowsku[count]>0)
							{
								$sku_error=base64_encode("sku already exists please try another!!");
								$sk="";
							}else
							{
								$sku=$_REQUEST['sku']; 
							}
						}else
						{
							$sku="";
							$sku_error="";
						}
						$reward_points=$_REQUEST['reward_points']==""?"0":$_REQUEST['reward_points'];
						$data=array('shipping'=>$this->reqObj->request['ship_req'],'sku'=>$sku,'length'=>$this->reqObj->request['length'],'width'=>$this->reqObj->request['width'],'height'=>$this->reqObj->request['height'],'weight_class_id'=>$this->reqObj->request['weight_class_id'],'length_class_id'=>$this->reqObj->request['length_class_id'],'upc'=>$_REQUEST['upc'],'products_minimum_quantity'=>(int)$_REQUEST['products_minimum_quantity'],'substract_stock'=>$_REQUEST['substract_stock'],'stock_status_id'=>$_REQUEST['stock_status_id'],'products_points'=>$reward_points,'products_quantity'=>$_REQUEST['products_quantity'],'products_model'=>$this->reqObj->request['products_model'],'products_price'=>preg_replace('/[^0-9.]/s', '', $_REQUEST['products_price']),'products_date_added'=>$this->_date
						,'products_date_available'=>$this->reqObj->request['products_date_available'],'products_weight'=>$this->reqObj->request['products_weight'],'products_status'=>(int)$_REQUEST['products_status'],'products_tax_class_id'=>(int)$_REQUEST['products_tax_class_id'],'manufacturers_id'=>(int)$_REQUEST['manufacturers_id'],'sort_order'=>(int)$_REQUEST['product_sort_order'],'products_image'=>$products_image);
						
						$insert_id=$model->insert($data);
						$seo_keyword = preg_replace('/[^a-zA-Z0-9]/', '_', $_REQUEST['keyword']);
						$act_ext->setSeoKeyword(array("type"=>"insert","query"=>"product","id"=>$insert_id,"keyword"=>$seo_keyword));
						//start multiple image uplaod
						
						$act_ext->uploadMultipleImage($insert_id);
						//end multiple image uplaod
						//start download
						
						foreach($_REQUEST['downloads'] as $k=>$v)
						{
							if($v!="")
							{
								$action->db->insert('r_product_to_download',array('download_id'=>$v,'product_id'=>$insert_id));
							}
						}
						//end download
						
						//start products tag
						if($_REQUEST['tags']!="")
						{
							$exp=explode(",",$_REQUEST['tags']);
							foreach($exp as $k=>$v)
							{
								$action->db->insert('r_product_tag',array("products_id"=>$insert_id,"tag"=>$v));
							}
						}
						//end products tag
						
						/*start reward points*/
						foreach($_REQUEST[r_product_reward][points] as $k=>$v)
						{
							$action->db->insert('r_product_reward',array('points'=>$v,'customer_group_id'=>$_REQUEST[r_product_reward][customer_group_id][$k],'product_id'=>$insert_id));
						}
						/*end reward points*/
						
						/*start related products*/
						if($_REQUEST['related_product']!='')
						{
							//$exp=explode(",",substr($_REQUEST['related_product'],0,-1));
							$exp=explode(",",$_REQUEST['related_product']);
							foreach($exp as $k=>$v)
							{
								$related_data['product_id']=$insert_id;
								$related_data['related_id']=$v;
								$action->db->insert('r_product_related',$related_data);
							}
						}
						
						/*end related products*/
						
						$action->product_option_multiple('insert',$insert_id,'select');
						$action->product_option_multiple('insert',$insert_id,'radio');
						$action->product_option_multiple('insert',$insert_id,'checkbox');
						//exit;
						$action->product_option_text('insert',$insert_id,'text');
						$action->product_option_text('insert',$insert_id,'textarea');
						$action->product_option_text('insert',$insert_id,'file');
						$action->product_option_text('insert',$insert_id,'date');
						$action->product_option_text('insert',$insert_id,'time');
						$action->product_option_text('insert',$insert_id,'datetime');
						
						/*end text,textarea,file option*/
						
						/*start attribute*/
						$action->productattribute('insert',$insert_id);
						/*end attribute*/
						
						/*start discount*/
						$action->productdiscount('insert',$insert_id);
						/*end discount*/
						
						/*start categories*/
						foreach($_REQUEST['categories_id'] as $k=>$v)
						{
							$data=array("products_id"=>$insert_id,"categories_id"=>$v);
							$action->db->insert("r_products_to_categories",$data);
						}
						/*end categories*/
						
						$action->lang_action(array("products_name","meta_keywords","meta_description",
						"products_description"),
						array("table"=>"r_products_description","comp_col_1"=>"products_id","comp_col_2"=>"language_id",
						"rid"=>$insert_id),'insert');
						
						//start special
						$action->productspecial('insert',$insert_id);
						//end special
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						$this->createIndex();
						Model_Cache::removeAllCache();
						if($_REQUEST['play']=='save')
						{
							if($sku_error!="")
							{
								$url="&m=1&msg=".$sku_error;
							}else
							{
								$url='&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle);
							}
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$url.$_REQUEST['page']);
							//$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
						//$this->createIndex();
					}
					$sel1=$action->getEditdetails('r_products','products_id',$insert_id);
					$this->view->seo_keyword=$act_ext->setSeoKeyword(array("type"=>"select","query"=>"product/".$insert_id));
					$this->view->data = $sel1;
					
					break;
				}
			}
		}
		
		public function categoriesAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			//$this->log_history_entry();
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rcategories();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			//start undo delete
			if($_REQUEST['type']=='undo')
			{
				$return=$act_ext->undoCategoryDelete();
				$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?msg='.base64_encode($return));
			}
			//end unto delete
			$act_ext->trashCategories();
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					
					case 'Pub':if($_REQUEST['rid']!="")
					{
						$data=array('customers_status'=>'1');
						$action->RecordPublish('r_customers',$data,'customers_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=Pub&msg='.$action->getstringmsg(ADMIN_UNPUB_SINGLE_MSG,$this->view->actionTitle));
					}
					break;
					
					case 'UnPub':if($_REQUEST['rid']!="")
					{
						$data=array('customers_status'=>'0');
						$action->RecordPublish('r_customers',$data,'customers_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=UnPub&msg='.$action->getstringmsg(ADMIN_UNPUB_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
					
					case 'Del':	if($_REQUEST['rid']!="")
					{
						/*foreach($_REQUEST['rid'] as $k=>$v)
							{//echo "select categories_image from r_categories where categories_id='".$v."'";
							$fch=$action->db->fetchRow("select categories_image from r_categories where categories_id='".$v."'");
							//	print_r($fch);
							@unlink(PATH_TO_UPLOADS_CATEGORIES.$fch[categories_image]);
							//echo PATH_TO_UPLOADS_CATEGORIES.$fch[categories_image];
							}
							//exit;
							$action->RecordDelete('r_categories','categories_id',$_REQUEST['rid']);
						$action->RecordDelete('r_categories_description','categories_id',$_REQUEST['rid']);*/
						Model_Cache::removeAllCache();
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						foreach($rid as $k=>$v)
						{
							$action->updateRow(array('table'=>'r_categories','cols'=>array('del'=>'1','status'=>'0','last_modified'=>$this->_date),'where'=>array('categories_id=?'=>$v)));
						}
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from('r_categories')->where('del = ?', '0'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->disType='desc';
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'categories_id':$_REQUEST['sortby'];
				$select = $model->fetchAll($model->select()
				->from('r_categories',array('categories_id','parent_id','sort_order'))
				->where('categories_id <> ?', '0')
				->where('del = ?', '0')
				->order($sortby." ".$this->disType)
				->limit(ADMIN_PAGE_LIMIT,$pagination->offset()));
				
				$this->view->results=$select;
			}else if($this->_getParam('type')!="")
			{
				
				switch ($this->_getParam('type'))
				{
					
					case 'Edit':if($_REQUEST['play']!="")
					{
						//$action->p($_REQUEST,'1');
						Model_Cache::removeAllCache();
						$seo_keyword = preg_replace('/[^a-zA-Z0-9]/', '_', $_REQUEST['keyword']);
						$act_ext->setSeoKeyword(array("type"=>"insert","id"=>(int)$_REQUEST['rid'],"query"=>"category","keyword"=>$seo_keyword));
						$this->view->msg="";
						if($this->_getParam('parent_id')!=$this->_getParam('rid')) //returns false when same category is selected as a parent category
						{
							$upload=$act_ext->fnupload(	array("action"=>$this->_action,"type"=>"Edit","field"=>"categories_image","path"=>PATH_TO_UPLOADS_CATEGORIES,"prev_img"=>$_REQUEST['prev_image'],"prefix"=>$_REQUEST['rid']));
							//exit;
							
							$action->lang_action(array("categories_name","meta_description","categories_description","meta_keywords"),	array("table"=>"r_categories_description","comp_col_1"=>"categories_id","comp_col_2"=>"language_id","rid"=>(int)$_REQUEST['rid']),'update');
							
							/*start filters*/
							if(sizeof($_REQUEST[filter])>0)
							{
								//echo sizeof($_REQUEST[filter]);
								$str="";
								foreach($_REQUEST['filter'] as $k=>$v)
								{
									$exp=$v."#".$_REQUEST['sort'][$k];
									$str=$str.$pre.$exp;
									$pre="&";
								}
								//echo "value of ".$str;
							}
							/*end filters*/
							
							$data=array('filters'=>$str,'categories_image'=>$upload,'parent_id'=>(int)$this->_getParam('parent_id'),'sort_order'=>(int)$this->_getParam('sort_order'),'top'=>$this->_getParam('top'),'column'=>$this->_getParam('column'),'status'=>(int)$this->_getParam('status'),'last_modified'=>$this->_date);
							$model->update($data,'categories_id='.(int)$_REQUEST['rid']);
							
							$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
							if($_REQUEST['play']=='save')
							{
								$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
							}
						}else
						{
							$this->view->msg=base64_encode("invalid parent category selection!!");
						}
					}
					//$action=new Model_Adminaction();
					$this->view->seo_keyword=$act_ext->setSeoKeyword(array("type"=>"select","query"=>"category","id"=>(int)$_REQUEST['rid']));
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						Model_Cache::removeAllCache();
						
						$fch=$action->db->fetchRow("select max(categories_id) as catid from r_categories");
						
						$upload=$act_ext->fnupload(array("action"=>$this->_action,"type"=>"Add","field"=>"categories_image",
						"path"=>PATH_TO_UPLOADS_CATEGORIES,"prev_img"=>"","prefix"=>$fch[catid]));
						$top=$this->_getParam('top')==''?'0':'1';
						
						/*start filters*/
						if(sizeof($_REQUEST[filter])>0)
						{
							//echo sizeof($_REQUEST[filter]);
							$str="";
							foreach($_REQUEST['filter'] as $k=>$v)
							{
								$sort=$_REQUEST['sort'][$k]!=""?$_REQUEST['sort'][$k]:'0';
								$exp=$v."#".$sort;
								$str=$str.$pre.$exp;
								$pre="&";
							}
							//echo "value of ".$str;
						}
						/*end filters*/
						
						$data=array('filters'=>$str,'categories_image'=>$upload,'parent_id'=>(int)$this->_getParam('parent_id'),'sort_order'=>(int)$this->_getParam('sort_order'),'top'=>$top,'column'=>(int)$this->_getParam('column'),'status'=>(int)$this->_getParam('status'),'date_added'=>$this->_date);
						$insert_id=$model->insert($data);
						$seo_keyword = preg_replace('/[^a-zA-Z0-9]/', '_', $_REQUEST['keyword']);
						$act_ext->setSeoKeyword(array("type"=>"insert","query"=>"category","id"=>$insert_id,"keyword"=>$seo_keyword));
						$action->lang_action(array("categories_name","meta_description","categories_description","meta_keywords"),	array("table"=>"r_categories_description","comp_col_1"=>"categories_id","comp_col_2"=>"language_id","rid"=>$insert_id),'insert');
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						$this->view->seo_keyword=$act_ext->setSeoKeyword(array("type"=>"select","query"=>"product/".$insert_id));
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					break;
				}
			}
		}
        
		public function manufacturerAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rmanufacturers();
			$action=new Model_Adminaction();
			$act_ext=new Model_Adminextaction();
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					
					case 'Pub':if($_REQUEST['rid']!="")
					{
						$data=array('customers_status'=>'1');
						$action->RecordPublish('r_customers',$data,'customers_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=Pub&msg='.$action->getstringmsg(ADMIN_UNPUB_SINGLE_MSG,$this->view->actionTitle));
					}
					break;
					
					case 'UnPub':if($_REQUEST['rid']!="")
					{
						$data=array('customers_status'=>'0');
						$action->RecordPublish('r_customers',$data,'customers_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=UnPub&msg='.$action->getstringmsg(ADMIN_UNPUB_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
					case 'Del':	if($_REQUEST['rid']!="")
					{
						/*start delete*/
						$rid=is_array($_REQUEST['rid'])==true?$_REQUEST['rid']:array($_REQUEST['rid']);
						$fch=$action->db->fetchRow("select count(products_id) as count from r_products where  manufacturers_id in (".implode(',',$rid).")");
						if($fch[count]==0)
						{
							foreach($rid as $k=>$v)
							{	$fch=$action->db->fetchRow("select manufacturers_image from r_manufacturers where manufacturers_id='".$v."'");
								@unlink(PATH_TO_UPLOADS_DIR."/image/".$fch[manufacturers_image]);
							}
							
							$action->RecordDelete('r_manufacturers','manufacturers_id',$_REQUEST['rid']);
							$action->RecordDelete('r_manufacturers_info','manufacturers_id',$_REQUEST['rid']);
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
						}else
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&m=f&msg='.base64_encode('cannot delete manufacturer as it is used in some products'));
						}
						
						/*end delete*/
						
						//$action->RecordDelete('r_manufacturers','manufacturers_id',$_REQUEST['rid']);
						//$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from('r_manufacturers'));
				$total_count = count($count);
				//$this->disType="desc";
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$_REQUEST['sortby']==''?'manufacturers_id':$_REQUEST['sortby'];
				$select = $model->fetchAll($model->select()
				->from('r_manufacturers')
				->where('manufacturers_id <> ?', '0')
				->order($sortby." ".$this->disType)
				->limit(ADMIN_PAGE_LIMIT,$pagination->offset()));
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						Model_Cache::removeMTCache(array("admin","general","manufacturer"));
						$action->lang_action(array("manufacturers_url"),	array("table"=>"r_manufacturers_info","comp_col_1"=>"manufacturers_id","comp_col_2"=>"language_id","rid"=>(int)$_REQUEST['rid']),'update');
						
						//print_r($_REQUEST);	exit;
						//updating r_customers
						$upload=$act_ext->fnupload(array("action"=>$this->_action,"type"=>"Edit","field"=>"image",
						"path"=>PATH_TO_UPLOADS_DIR."/image/","prev_img"=>$_REQUEST['prev_image'],"prefix"=>"mftr_".$_REQUEST[rid]));
						
						$data=array('sort_order'=>$_REQUEST['sort_order'],'manufacturers_name'=>$this->reqObj->request['name'],'manufacturers_image'=>$upload,'last_modified'=>$this->_date);
						$model->update($data,'manufacturers_id='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails('r_manufacturers','manufacturers_id',(int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						Model_Cache::removeMTCache(array("admin","general","manufacturer"));
						$fch=$action->db->fetchRow("select max(manufacturers_id) as catid from r_manufacturers");
						//echo $fch['catid'];
						//exit;
						$val=$fch[catid]+1;
						$upload=$act_ext->fnupload(array("action"=>$this->_action,"type"=>"Add","field"=>"image",
						"path"=>PATH_TO_UPLOADS_DIR."/image/","prev_img"=>$_REQUEST['prev_image'],"prefix"=>"mftr_".$val));
						
						$data=array('sort_order'=>(int)$_REQUEST['sort_order'],'manufacturers_name'=>$this->reqObj->request['name'],'manufacturers_image'=>$upload,'date_added'=>$this->_date);
						$insert_id=$model->insert($data);
						
						$action->lang_action(array("manufacturers_url"),	array("table"=>"r_manufacturers_info","comp_col_1"=>"manufacturers_id","comp_col_2"=>"language_id","rid"=>$insert_id),'insert');
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails('r_manufacturers','manufacturers_id',$insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
		}
		
		public function zonesAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rzones();
			$action=new Model_Adminaction();
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					
					case 'Pub':if($_REQUEST['rid']!="")
					{
						$data=array('customers_status'=>'1');
						$action->RecordPublish('r_customers',$data,'customers_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=Pub&msg='.$action->getstringmsg(ADMIN_UNPUB_SINGLE_MSG,$this->view->actionTitle));
					}
					break;
					case 'UnPub':if($_REQUEST['rid']!="")
					{
						$data=array('customers_status'=>'0');
						$action->RecordPublish('r_customers',$data,'customers_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=UnPub&msg='.$action->getstringmsg(ADMIN_UNPUB_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$action->RecordDelete('r_zones','zone_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from('r_zones'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'zone_id':$this->_getParam('sortby');
				$select = $model->fetchAll($model->select()
				->from('r_zones',
				array('zone_id','zone_country_id','zone_code','zone_name'))
				->where('zone_id <> ?', '0')
				->order($sortby." ".$this->disType)
				->limit(ADMIN_PAGE_LIMIT,$pagination->offset()));
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$data=array('zone_name'=>$this->reqObj->request['zone_name'],'zone_code'=>$this->reqObj->request['zone_code'],'zone_country_id'=>$_REQUEST['zone_country_id']);
						$model->update($data,'zone_id='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					$sel1=$action->getzonedetails((int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					
					case 'Add':	
					if($_REQUEST['play']!="")
					{
						$data=array('zone_name'=>$this->reqObj->request['zone_name'],'zone_code'=>$this->reqObj->request['zone_code'],'zone_country_id'=>(int)$_REQUEST['zone_country_id']);
						$insert_id=$model->insert($data);
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getzonedetails($insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
		}
		
		public function countriesAction()
		{
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rcountries();
			$action=new Model_Adminaction();
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					
					case 'Pub':if($_REQUEST['rid']!="")
					{
						$data=array('customers_status'=>'1');
						$action->RecordPublish('r_customers',$data,'customers_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=Pub&msg='.$action->getstringmsg(ADMIN_UNPUB_SINGLE_MSG,$this->view->actionTitle));
					}
					break;
					
					case 'UnPub':if($_REQUEST['rid']!="")
					{
						$data=array('customers_status'=>'0');
						$action->RecordPublish('r_customers',$data,'customers_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').'customers?page='.$_REQUEST['page'].'&action=UnPub&msg='.$action->getstringmsg(ADMIN_UNPUB_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
					case 'Del':	if($_REQUEST['rid']!="")
					{
						$action->RecordDelete('r_countries','countries_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.$action->getstringmsg(ADMIN_DEL_MULTIPLE_MSG,$this->view->actionTitle));
					}
					break;
				}
				
				$count=$model->fetchAll($model->select()->from('r_countries'));
				$total_count = count($count);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'countries_name':$this->_getParam('sortby');
				$select = $model->fetchAll($model->select()
				->from('r_countries',
				array('countries_id','countries_name','countries_iso_code_2','countries_iso_code_3'))
				->where('countries_id <> ?', '0')
				->order($sortby." ".$this->disType)
				->limit(ADMIN_PAGE_LIMIT,$pagination->offset()));
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						//print_r($_REQUEST);	exit;
						//updating r_customers
						$data=array('countries_name'=>$this->reqObj->request['countries_name'],'countries_iso_code_2'=>$_REQUEST['countries_iso_code_2'],'countries_iso_code_3'=>$_REQUEST['countries_iso_code_3']);
						
						$model->update($data,'countries_id='.(int)$_REQUEST['rid']);
						
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					//$action=new Model_Adminaction();
					$sel1=$action->getcountrydetails((int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					
					case 'Add':	if($_REQUEST['play']!="")
					{
						//print_r($_REQUEST);	exit;
						//updating r_customers
						$data=array('countries_name'=>$this->reqObj->request['countries_name'],'countries_iso_code_2'=>$_REQUEST['countries_iso_code_2'],'countries_iso_code_3'=>$_REQUEST['countries_iso_code_3']);
						$insert_id=$model->insert($data);
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getcountrydetails($insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
			
		}
		
		
		public function giftVoucherAction(){
			$this->checkSession($_SESSION['admin_id']);
			$this->view->actionTitle=$this->_action;
			$this->view->type=$this->_getParam('type');
			$model = new Model_DbTable_rvoucher();
			$action=new Model_Adminaction();
			$this->_table="r_voucher";
			$this->_id="voucher_id";
			
			$this->view->r_table=$this->_table;
			$this->view->r_id=$this->_id;
			
			if($_REQUEST['em']=='sendmail')
			{
				$cVObj=new Model_CheckoutVoucher();
				if($_REQUEST[oid]!="")
				{
					$cVObj->sendVoucher($_REQUEST['oid'],'');
				}else
				{
					$cVObj->sendVoucher('',(int)$_REQUEST['rid']);
				}	
				$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$this->_getParam('page').'&msg='.base64_encode('Email sent to Customer successfully!!'));
			}
			
			if($this->_getParam('type')=='')
			{
				switch($_REQUEST['action'])
				{
					case 'Del':	if($this->_getParam('rid')!="")
					{
						$action->RecordDelete($this->_table,$this->_id,$_REQUEST['rid']);
						$action->RecordDelete('r_voucher_history','voucher_id',$_REQUEST['rid']);
						$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&action=Del&msg='.base64_encode('Selected Gift Voucher Deleted Successfully!!'));
					}
					break;
				}
				
				$count=$action->db->fetchAll("SELECT v.order_id, (SELECT o.invoice_id FROM r_orders o WHERE o.orders_id = v.order_id) AS invoice_id FROM r_voucher v");
				foreach($count as $k=>$v)
				{
					
					if($v[invoice_id]=='0')
					{
						
						continue;
					}
					$c[]=$v[order_id];
				}
				
				$total_count = count($c);
				
				$this->view->total_count=$total_count;
				$page = !empty($_GET['page']) ? (int)$_GET['page'] : 1;
				$pagination = new Model_Pagination($page, ADMIN_PAGE_LIMIT, $total_count);
				$this->view->page=$page;
				$this->view->per_page=ADMIN_PAGE_LIMIT;
				
				$this->view->view_pagination=$pagination->pagination(ADMIN_URL_CONTROLLER.$this->view->actionTitle);
				//$this->disType="desc";
				$this->view->disType=$this->disType;
				$sortby=$this->_getParam('sortby')==''?'voucher_id':$this->_getParam('sortby');
				$select =$action->db->fetchAll("select (select invoice_id from r_orders where r_orders.orders_id=v.order_id) as invoice_id,v.*,t.name from ".$this->_table." v,r_voucher_theme_description t where
				v.voucher_theme_id=t.voucher_theme_id and t.language_id='1' order by ".$sortby." ".$this->disType. "
				limit ".$pagination->offset().",".ADMIN_PAGE_LIMIT);
				
				$this->view->results=$select;
			}else if($_REQUEST['type']!="")
			{
				switch ($_REQUEST['type'])
				{
					case 'Edit':if($_REQUEST['play']!="")
					{
						$model->update($_REQUEST['r_voucher'],$this->_id.'='.(int)$_REQUEST['rid']);
						$this->view->msg=$action->getstringmsg(ADMIN_EDIT_APPLY_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_EDIT_SAVE_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,(int)$_REQUEST['rid']);
					$this->view->data = $sel1;
					break;
					
					case 'Add':if($_REQUEST['play']!="")
					{
						$data=$_REQUEST['r_voucher'];
						$data['date_added']=$this->_date;
						$insert_id=$model->insert($data);
						
						$this->view->msg=$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle);
						if($_REQUEST['play']=='save')
						{
							$this->_redirect(@constant('ADMIN_URL_CONTROLLER').$this->view->actionTitle.'?page='.$_REQUEST['page'].'&msg='.$action->getstringmsg(ADMIN_ADD_MSG,$this->view->actionTitle));
						}
					}
					$sel1=$action->getEditdetails($this->_table,$this->_id,$insert_id);
					$this->view->data = $sel1;
					break;
				}
			}
		}
		
		/*creates model object*/
		private function getModel($table)
		{
			if (!$this->_model)
			$this->_model = (object)new $table;
			return $this->_model;
		}
		
		public function createIndex()
		{
			try{
				//$index = Zend_Search_Lucene::create(PATH_TO_PUBLIC.'searchindex');
				$index = Zend_Search_Lucene::create(@constant('PATH_TO_SEARCHINDEX'));
				
				//	$db=Zend_Registry::get('db');
				
				/*$sql = "SELECT p.products_model,p.products_image,p.products_quantity,p.products_price,p.products_date_added,p.products_date_available,pd.products_id,pd.products_description, pd.products_name,pc.categories_id, (
					
					SELECT c.categories_name
					FROM r_categories_description c
					WHERE categories_id = pc.categories_id
					AND c.language_id = pd.language_id
					) AS categories_name
					FROM r_products p, r_products_description pd, r_products_to_categories pc
					WHERE p.products_id = pd.products_id
					AND p.products_id = pc.products_id
					AND p.products_status = '1'
					AND p.del = '0'
				AND p.products_date_available <= NOW( )";*/
				/*//search-key as product_name-category-id
					$sql="SELECT   concat(pd.products_name,'-',pc.categories_id) as search_key,p.products_model,pd.products_name, p.products_image, p.products_quantity, p.products_price, p.products_date_added, p.products_date_available, pd.products_id, pd.products_description, pc.categories_id, (
					
					SELECT c.categories_name
					FROM r_categories_description c
					WHERE categories_id = pc.categories_id
					AND c.language_id = pd.language_id
					) AS categories_name
					FROM r_products p, r_products_description pd, r_products_to_categories pc
					WHERE p.products_id = pd.products_id
					AND p.products_id = pc.products_id
					AND p.products_status = '1'
					AND p.del = '0'
				AND p.products_date_available <= NOW( )";*/
				
				$sql="SELECT   pd.language_id,p.products_model,pd.products_name, p.products_image, p.products_quantity, p.products_price, p.products_date_added, p.products_date_available, pd.products_id, pd.products_description, pc.categories_id, (
				
				SELECT c.categories_name
				FROM r_categories_description c
				WHERE categories_id = pc.categories_id
				AND c.language_id = pd.language_id
				) as search_key
				FROM r_products p, r_products_description pd, r_products_to_categories pc
				WHERE p.products_id = pd.products_id
				AND p.products_id = pc.products_id
				AND p.products_status = '1'
				AND p.del = '0'
				AND p.products_date_available <= '".$this->_date."' group by pd.products_id";
				
				$db = Zend_Db_Table::getDefaultAdapter();
				
				$result = $db->fetchAll($sql);
				
				//echo count($result);
				
				foreach($result as $record){
					$doc = new Zend_Search_Lucene_Document();
					$cur_date = date("Ymd");
					$doc->addField(Zend_Search_Lucene_Field::UnIndexed('products_id', $record[products_id]));
					$doc->addField(Zend_Search_Lucene_Field::Text('products_name', $record[products_name]));
					//$doc->addField(Zend_Search_Lucene_Field::Text('products_keywords', $record[products_keywords]));
					$doc->addField(Zend_Search_Lucene_Field::UnIndexed('products_description', $record[products_description]));
					$doc->addField(Zend_Search_Lucene_Field::UnIndexed('products_model', $record[products_model]));
					$doc->addField(Zend_Search_Lucene_Field::UnIndexed('products_quantity', $record[products_quantity]));
					$doc->addField(Zend_Search_Lucene_Field::UnIndexed('products_image', $record[products_image]));
					$doc->addField(Zend_Search_Lucene_Field::UnIndexed('products_price', $record[products_price]));
					$doc->addField(Zend_Search_Lucene_Field::UnIndexed('products_date_added', $record[products_date_added]));
					$doc->addField(Zend_Search_Lucene_Field::UnIndexed('products_date_available', $record[products_date_available]));
					$doc->addField(Zend_Search_Lucene_Field::UnIndexed('language_id', $record[language_id]));
					//$doc->addField(Zend_Search_Lucene_Field::UnIndexed('categories_name', $record[categories_name]));
					//$doc->addField(Zend_Search_Lucene_Field::UnIndexed('categories_id', $record[categories_id]));
					$doc->addField(Zend_Search_Lucene_Field::UnIndexed('categories_id', $record[categories_id]));
					$doc->addField(Zend_Search_Lucene_Field::Text('search_key', $record[search_key]));
					
					
					/*$doc->addField(Zend_Search_Lucene_Field::Text('area', $record[area]));
						$doc->addField(Zend_Search_Lucene_Field::Text('city', $record[city]));
						$doc->addField(Zend_Search_Lucene_Field::unIndexed('account_status', $record[account_status]));
						$doc->addField(Zend_Search_Lucene_Field::unIndexed('address', $record[address]));
						$doc->addField(Zend_Search_Lucene_Field::unIndexed('mobile', $record[mobile]));
						$doc->addField(Zend_Search_Lucene_Field::unIndexed('users', $record[users]));
					$doc->addField(Zend_Search_Lucene_Field::unIndexed('landmark', $record[landmark]));*/
					//$doc->addField(Zend_Search_Lucene_Field::unIndexed('end_date', $end_date));
					$index->addDocument($doc);
					
				}
				
				$index->commit();
				
				
				//echo 'Index created.<br>';
				
				
				}catch(Zend_Search_Exception $e){
				//echo $e->getMessage();
			}
			
			//$this->_helper->viewRenderer->setNoRender();
			//exit;
		}
		
		public function zoneAction()
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			
			$output = '<option value="0">All Zones</option>';
			$locZoneObj=new Model_LocalisationZone();
			//echo "value of ".$this->_request->country_id;
			//exit;
			$results = $locZoneObj->getZonesByCountryId((int)$this->_request->country_id);
			
			foreach ($results as $result) {
				$output .= '<option value="' . $result['zone_id'] . '"';
				
				if (isset($this->_request->zone_id) && ($this->_request->zone_id == $result['zone_id'])) {
					$output .= ' selected="selected"';
				}
				
				$output .= '>' . $result['zone_name'] . '</option>';
			}
			echo $output;
		}
        
        public function orderCheckoutAction() {
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);
			$json = array();
			$_SESSION['Curr']['currency']=$_SESSION['Curr']['currency']==""?@constant('DEFAULT_CURRENCY'):$_SESSION['Curr']['currency'];
			/*echo "<pre>";	
                print_r($_REQUEST);
                print_r($this->_request);
                echo "</pre>";
			exit;*/
			if ($_SESSION['admin_id']!="") {	
				$custacctObj=new Model_AccountCustomer();
				$custObj=new Model_Customer();
				$prodObj=new Model_Products();
				$cartObj=new Model_Cart();
				$taxObj=new Model_Tax();
				$locCtryObj=new Model_LocalisationCountry();
				$locZoneObj=new Model_LocalisationZone();
				$couObj=new Model_CheckoutCoupon();
				$vouObj=new Model_CheckoutVoucher();
				// Reset everything
				$cartObj->clear();
				//$custObj->logout();
				
				unset($_SESSION['shipping_method']);
				unset($_SESSION['shipping_methods']);			
				unset($_SESSION['payment_method']);
				unset($_SESSION['payment_methods']);
				unset($_SESSION['coupon']);
				unset($_SESSION['reward']);
				unset($_SESSION['voucher']);
				unset($_SESSION['vouchers']);
				
				
				// Customer
				if ($this->_request->customer_id) 
				{
					$customer_info = $custacctObj->getCustomer((int)$_REQUEST['customer_id']);
					if ($customer_info) {       
						$custObj->login($customer_info['email'], '', true);
						} else {
						$json['error']['customer'] = "Can not find selected customer!!";
					}
					} else {
					// Customer Group
					//$this->config->set('config_customer_group_id', $_REQUEST['customer_group_id']);
				}
				
				// Product
				
				if (isset($_REQUEST['order_product'])) {
					foreach ($_REQUEST['order_product'] as $order_product) {
						$product_info = $prodObj->getProduct($order_product['product_id']);
						
						if ($product_info) {	
							$option_data = array();
							
							if (isset($order_product['order_option'])) {
								foreach ($order_product['order_option'] as $option) {
									if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'image') { 
										$option_data[$option['product_option_id']] = $option['product_option_value_id'];
										} elseif ($option['type'] == 'checkbox') {
										$option_data[$option['product_option_id']][] = $option['product_option_value_id'];
										} elseif ($option['type'] == 'text' || $option['type'] == 'textarea' || $option['type'] == 'file' || $option['type'] == 'date' || $option['type'] == 'datetime' || $option['type'] == 'time') {
										$option_data[$option['product_option_id']] = $option['value'];						
									}
								}
							}
							
							$cartObj->add($order_product['product_id'], $order_product['quantity'], $option_data);
						}
					}
				}
				
				if (isset($_REQUEST['product_id'])) {
					$product_info = $prodObj->getProduct((int)$_REQUEST['product_id']);
					
					if ($product_info) {
						if (isset($_REQUEST['quantity'])) {
							$quantity = $_REQUEST['quantity'];
							} else {
							$quantity = 1;
						}
						
						if (isset($_REQUEST['option'])) {
							$option = array_filter($_REQUEST['option']);
							} else {
							$option = array();	
						}
						
						$product_options = $prodObj->getProductOptions((int)$_REQUEST['product_id']);
						
						foreach ($product_options as $product_option) {
							if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
								$json['error']['product']['option'][$product_option['product_option_id']] = sprintf("%s required!!", $product_option['name']);
							}
						}
						
						if (!isset($json['error']['product']['option'])) {
							$cartObj->add((int)$_REQUEST['product_id'], $quantity, $option);
						}
					}
				}
				
				if (!$cartObj->hasStock() && (!@constant('STOCK_ALLOW_CHECKOUT') || @constant('STOCK_WARNING_DISPLAY'))) {
					$json['error']['product']['stock'] = 'Products marked with *** are not available in the desired quantity or not in stock!!';
				}
				
				// Tax
				if ($cartObj->hasShipping()) {
					$taxObj->setShippingAddress((int)$_REQUEST['shipping_country_id'], (int)$_REQUEST['shipping_zone_id']);
					} else {
					$taxObj->setShippingAddress(@constant('STORE_COUNTRY'), @constant('STORE_ZONE'));
				}
				
				$taxObj->setPaymentAddress((int)$_REQUEST['payment_country_id'], (int)$_REQUEST['payment_zone_id']);				
				//$taxObj->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));	
				
				// Products
				$json['order_product'] = array();
				
				$products = $cartObj->getProducts();
				
				foreach ($products as $product) {
					$product_total = 0;
					
					foreach ($products as $product_2) {
						if ($product_2['product_id'] == $product['product_id']) {
							$product_total += $product_2['quantity'];
						}
					}	
					
					if ($product['minimum'] > $product_total) {
						$json['error']['product']['minimum'][] = sprintf('Minimum order amount for %s is %s!!', $product['name'], $product['minimum']);
					}	
					
					$option_data = array();
					
					foreach ($product['option'] as $option) {
						$option_data[] = array(
						'product_option_id'       => $option['product_option_id'],
						'product_option_value_id' => $option['product_option_value_id'],
						'name'                    => $option['name'],
						'value'                   => $option['option_value'],
						'type'                    => $option['type']
						);
					}
					
					$download_data = array();
					
					foreach ($product['download'] as $download) {
						$download_data[] = array(
						'name'      => $download['name'],
						'filename'  => $download['filename'],
						'mask'      => $download['mask'],
						'remaining' => $download['remaining']
						);
					}
					
					$json['order_product'][] = array(
					'product_id' => $product['product_id'],
					'name'       => $product['name'],
					'model'      => $product['model'], 
					'option'     => $option_data,
					'download'   => $download_data,
					'quantity'   => $product['quantity'],
					'price'      => $product['price'],	
					'total'      => $product['total'],	
					'tax'        => '',//$taxObj->getTax($product['price'], $product['tax_class_id']),
					'reward'     => $product['reward']				
					);
				}
				
				// Voucher
				/*$_SESSION['vouchers'] = array();
					
					if (isset($_REQUEST['order_voucher'])) {
					foreach ($_REQUEST['order_voucher'] as $voucher) {
					$_SESSION['vouchers'][] = array(
					'voucher_id'       => $voucher['voucher_id'],
					'description'      => $voucher['description'],
					'code'             => substr(md5(mt_rand()), 0, 10),
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'], 
					'message'          => $voucher['message'],
					'amount'           => $voucher['amount']    
					);
					}
					}
					
					// Add a new voucher if set
					if (isset($_REQUEST['from_name']) && isset($_REQUEST['from_email']) && isset($_REQUEST['to_name']) && isset($_REQUEST['to_email']) && isset($_REQUEST['amount'])) {
					if ((utf8_strlen($_REQUEST['from_name']) < 1) || (utf8_strlen($_REQUEST['from_name']) > 64)) {
					$json['error']['vouchers']['from_name'] ='Your Name must be between 1 and 64 characters!';
					}  
					
					if ((utf8_strlen($_REQUEST['from_email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $_REQUEST['from_email'])) {
					$json['error']['vouchers']['from_email'] = 'E-Mail Address does not appear to be valid!';
					}
					
					if ((utf8_strlen($_REQUEST['to_name']) < 1) || (utf8_strlen($_REQUEST['to_name']) > 64)) {
					$json['error']['vouchers']['to_name'] = 'Recipient\'s Name must be between 1 and 64 characters!';
					}       
					
					if ((utf8_strlen($_REQUEST['to_email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $_REQUEST['to_email'])) {
					$json['error']['vouchers']['to_email'] = 'E-Mail Address does not appear to be valid!';
					}
					
					if (($_REQUEST['amount'] < 1) || ($_REQUEST['amount'] > 1000)) {
					$json['error']['vouchers']['amount'] = sprintf('Amount must be between %s and %s!', $this->currency->format(1, false, 1), $this->currency->format(1000, false, 1) . ' ' . $this->config->get('config_currency'));
					}
					
					if (!isset($json['error']['vouchers'])) { 
					$voucher_data = array(
					'order_id'         => 0,
					'code'             => substr(md5(mt_rand()), 0, 10),
					'from_name'        => $_REQUEST['from_name'],
					'from_email'       => $_REQUEST['from_email'],
					'to_name'          => $_REQUEST['to_name'],
					'to_email'         => $_REQUEST['to_email'],
					'voucher_theme_id' => $_REQUEST['voucher_theme_id'], 
					'message'          => $_REQUEST['message'],
					'amount'           => $_REQUEST['amount'],
					'status'           => true             
					); 
					
					$this->load->model('checkout/voucher');
					
					$voucher_id = $this->model_checkout_voucher->addVoucher(0, $voucher_data);  
					
					$_SESSION['vouchers'][] = array(
					'voucher_id'       => $voucher_id,
					'description'      => sprintf('%s Gift Certificate for %s', $this->currency->format($_REQUEST['amount'], $this->config->get('config_currency')), $_REQUEST['to_name']),
					'code'             => substr(md5(mt_rand()), 0, 10),
					'from_name'        => $_REQUEST['from_name'],
					'from_email'       => $_REQUEST['from_email'],
					'to_name'          => $_REQUEST['to_name'],
					'to_email'         => $_REQUEST['to_email'],
					'voucher_theme_id' => $_REQUEST['voucher_theme_id'], 
					'message'          => $_REQUEST['message'],
					'amount'           => $_REQUEST['amount']            
					); 
					}
					}
					
					$json['order_voucher'] = array();
					
					foreach ($_SESSION['vouchers'] as $voucher) {
					$json['order_voucher'][] = array(
					'voucher_id'       => $voucher['voucher_id'],
					'description'      => $voucher['description'],
					'code'             => $voucher['code'],
					'from_name'        => $voucher['from_name'],
					'from_email'       => $voucher['from_email'],
					'to_name'          => $voucher['to_name'],
					'to_email'         => $voucher['to_email'],
					'voucher_theme_id' => $voucher['voucher_theme_id'], 
					'message'          => $voucher['message'],
					'amount'           => $voucher['amount']    
					);
				}*/
				
				include APPLICATION_PATH.'/models/functions.php';
				// Shipping
				$json['shipping_method'] = array();
				
				if ($cartObj->hasShipping()) {		
					
					$country_info = $locCtryObj->getCountry((int)$_REQUEST['shipping_country_id']);
					
					if ($country_info && $country_info['postcode_required'] && (strlen($_REQUEST['shipping_postcode']) < 2) || (strlen($_REQUEST['shipping_postcode']) > 10)) {
						$json['error']['shipping']['postcode'] = 'Postcode must be between 2 and 10 characters!';
					}
					
					if ($_REQUEST['shipping_country_id'] == '') {
						$json['error']['shipping']['country'] = 'Please select a country!';
					}
					
					if ($_REQUEST['shipping_zone_id'] == '') {
						$json['error']['shipping']['zone'] ='Please select a region / state!';
					}
					
					$country_info = $locCtryObj->getCountry($_REQUEST['shipping_country_id']);
					
					if ($country_info && $country_info['postcode_required'] && (strlen($_REQUEST['shipping_postcode']) < 2) || (strlen($_REQUEST['shipping_postcode']) > 10)) {
						$json['error']['shipping']['postcode'] = 'Postcode must be between 2 and 10 characters!';
					}
					
					if (!isset($json['error']['shipping'])) {
						if ($country_info) {
							$country = $country_info['name'];
							$iso_code_2 = $country_info['iso_code_2'];
							$iso_code_3 = $country_info['iso_code_3'];
							$address_format = $country_info['address_format'];
							} else {
							$country = '';
							$iso_code_2 = '';
							$iso_code_3 = '';	
							$address_format = '';
						}
						
						$zone_info = $locZoneObj->getZone($_REQUEST['shipping_zone_id']);
						
						if ($zone_info) {
							$zone = $zone_info['name'];
							$zone_code = $zone_info['code'];
							} else {
							$zone = '';
							$zone_code = '';
						}					
						
						$address_data = array(
						'firstname'      => $_REQUEST['shipping_firstname'],
						'lastname'       => $_REQUEST['shipping_lastname'],
						'company'        => $_REQUEST['shipping_company'],
						'address_1'      => $_REQUEST['shipping_address_1'],
						'address_2'      => $_REQUEST['shipping_address_2'],
						'postcode'       => $_REQUEST['shipping_postcode'],
						'city'           => $_REQUEST['shipping_city'],
						'zone_id'        => $_REQUEST['shipping_zone_id'],
						'zone'           => $zone,
						'zone_code'      => $zone_code,
						'country_id'     => $_REQUEST['shipping_country_id'],
						'country'        => $country,	
						'iso_code_2'     => $iso_code_2,
						'iso_code_3'     => $iso_code_3,
						'address_format' => $address_format
						);
						
						$shipping_modules = new Model_Shipping ();
						$this->data['count_shipping_modules']=tep_count_shipping_modules();
						$quotes = $shipping_modules->quote();
						
						//echo "<pre>";
						//print_r($quotes);
						
						
						foreach($quotes as $k=>$v)
						{
							$methods="";
							if(sizeof($v['methods'])>0)
							{
								foreach($v['methods'] as $k1=>$v1)
								{
									$methods[$v1['id']]=array("code"=>$v['id']."_".$v1['id'],"title"=>$v1['title'],"cost"=>$v1['cost']);
								}
							}
							
							$json['shipping_method'][$v['id']] = array( 
							'title'      => $v['module'],
							'quote'      => $methods, 
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
							);
						}
						
						//print_r($json['shipping_method']);
						//echo "</pre>";
						//exit;			
						/*$results = $this->model_setting_extension->getExtensions('shipping');
							
							foreach ($results as $result) {
							if ($this->config->get($result['code'] . '_status')) {
							$this->load->model('shipping/' . $result['code']);
							
							$quote = $this->{'model_shipping_' . $result['code']}->getQuote($address_data); 
							
							if ($quote) {
							$json['shipping_method'][$result['code']] = array( 
							'title'      => $quote['title'],
							'quote'      => $quote['quote'], 
							'sort_order' => $quote['sort_order'],
							'error'      => $quote['error']
							);
							}
							}
							}
							
							$sort_order = array();
							
							foreach ($json['shipping_method'] as $key => $value) {
							$sort_order[$key] = $value['sort_order'];
							}
							
							array_multisort($sort_order, SORT_ASC, $json['shipping_method']);
						*/
						if (!$json['shipping_method']) {
							$json['error']['shipping_method'] = "No Shipping methods are available!!";
							} elseif ($_REQUEST['shipping_code']) {
							//echo "inside";
							$shipping = explode('_', $_REQUEST['shipping_code']);
							/*echo "<pre>";
								print_r($shipping);
								print_r($json['shipping_method']);
								echo "</pre>";
							exit;*/
							
							if (!isset($shipping[0]) || !isset($shipping[1]) || !isset($json['shipping_method'][$shipping[0]]['quote'][$shipping[1]])) {		
								$json['error']['shipping_method'] = 'shipping method required!!';
								} else {
								$_SESSION['shipping_method'] = $json['shipping_method'][$shipping[0]]['quote'][$shipping[1]];
							}				
						}					
					}
				}
				
				// Coupon
				if (!empty($_REQUEST['coupon'])) {
					
					$coupon_info = $couObj->getCoupon($_REQUEST['coupon']);			
					
					if ($coupon_info) {					
						$_SESSION['coupon'] = $_REQUEST['coupon'];
						} else {
						$json['error']['coupon'] = 'Coupon is either invalid, expired or reached it\'s usage limit!';
					}
				}
				
				// Voucher
				if (!empty($_REQUEST['voucher'])) {
					
					$voucher_info = $vouObj->getVoucher($_REQUEST['voucher']);			
					
					if ($voucher_info) {					
						$_SESSION['voucher'] = $_REQUEST['voucher'];
						} else {
						$json['error']['voucher'] = 'Gift Voucher is either invalid or the balance has been used up!!';
					}
				}
				
				// Reward Points
				if (!empty($_REQUEST['reward'])) {
					$points = $custObj->getRewardPoints();
					
					if ($_REQUEST['reward'] > $points) {
						$json['error']['reward'] = sprintf('You don\'t have %s reward points!', $_REQUEST['reward']);
					}
					
					if (!isset($json['error']['reward'])) {
						$points_total = 0;
						
						foreach ($cartObj->getProducts() as $product) {
							if ($product['points']) {
								$points_total += $product['points'];
							}
						}				
						
						if ($_REQUEST['reward'] > $points_total) {
							$json['error']['reward'] = sprintf("The maximum number of points that can be applied is %s!!", $points_total);
						}
						
						if (!isset($json['error']['reward'])) {		
							$_SESSION['reward'] = $_REQUEST['reward'];
						}
					}
				}
				
				// Totals
				$json['order_total'] = array();					
				$total = 0;
				$taxes = $cartObj->getTaxes();
				
				//start order total
				$sort_order = array();
  		        $otmodules = explode(';', MODULE_ORDER_TOTAL_INSTALLED);
				foreach($otmodules as $k=>$v)
				{
					$sort_order[]=constant('MODULE_ORDER_TOTAL_'.strtoupper(substr(substr($v,0,-4),2)).'_SORT_ORDER')."-".substr(substr($v,0,-4),2);
				}
				sort($sort_order,SORT_NUMERIC);
				$results=array();
				
				foreach($sort_order as $k=>$v)
				{
					$exp=explode("-",$v);
					if(constant('MODULE_ORDER_TOTAL_'.strtoupper($exp[1]).'_STATUS')=='true')
					{
						//echo $exp[1]."<br/>";
						$class='Model_OrderTotal_Ot'.$exp[1];
						$oTobj=new $class;
						$oTobj->getTotal($total_data, $total, $taxes);
					}
				}
				/*echo "<pre>";
					print_r($_SESSION['shipping_method']);
					echo "</pre>";
				exit;*/
				$sort_order = array();
				foreach ($total_data as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}
				
				
				array_multisort($sort_order, SORT_ASC, $total_data);
				
                $json['order_total']=$total_data;
                /*echo "<pre>";
					print_r($json['order_total']);
					echo "</pre>";
				exit;*/
				//end order total
				
				/*$sort_order = array(); 
					
					$results = $this->model_setting_extension->getExtensions('total');
					
					foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
					}
					
					array_multisort($sort_order, SORT_ASC, $results);
					
					foreach ($results as $result) {
					if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);
					
					$this->{'model_total_' . $result['code']}->getTotal($json['order_total'], $total, $taxes);
					}
					
					$sort_order = array(); 
					
					foreach ($json['order_total'] as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
					}
					
					array_multisort($sort_order, SORT_ASC, $json['order_total']);				
				}*/
				
				// Payment
				if ($_REQUEST['payment_country_id'] == '') {
					$json['error']['payment']['country'] = 'please select country';
				}
				
				if ($_REQUEST['payment_zone_id'] == '') {
					$json['error']['payment']['zone'] = 'please select region/state';
				}		
				
				if (!isset($json['error']['payment'])) {
					$json['payment_methods'] = array();
					
					$country_info = $locCtryObj->getCountry((int)$_REQUEST['payment_country_id']);
					
					if ($country_info) {
						$country = $country_info['name'];
						$iso_code_2 = $country_info['iso_code_2'];
						$iso_code_3 = $country_info['iso_code_3'];
						$address_format = $country_info['address_format'];
						} else {
						$country = '';
						$iso_code_2 = '';
						$iso_code_3 = '';	
						$address_format = '';
					}
					
					$zone_info = $locZoneObj->getZone((int)$_REQUEST['payment_zone_id']);
					
					if ($zone_info) {
						$zone = $zone_info['name'];
						$zone_code = $zone_info['code'];
						} else {
						$zone = '';
						$zone_code = '';
					}					
					
					$address_data = array(
					'firstname'      => $_REQUEST['payment_firstname'],
					'lastname'       => $_REQUEST['payment_lastname'],
					'company'        => $_REQUEST['payment_company'],
					'address_1'      => $_REQUEST['payment_address_1'],
					'address_2'      => $_REQUEST['payment_address_2'],
					'postcode'       => $_REQUEST['payment_postcode'],
					'city'           => $_REQUEST['payment_city'],
					'zone_id'        => $_REQUEST['payment_zone_id'],
					'zone'           => $zone,
					'zone_code'      => $zone_code,
					'country_id'     => $_REQUEST['payment_country_id'],
					'country'        => $country,	
					'iso_code_2'     => $iso_code_2,
					'iso_code_3'     => $iso_code_3,
					'address_format' => $address_format
					);
					
					$json['payment_method'] = array();
					
					/*$results = $this->model_setting_extension->getExtensions('payment');
						
						foreach ($results as $result) {
						if ($this->config->get($result['code'] . '_status')) {
						
						
						$method = $this->{'model_payment_' . $result['code']}->getMethod($address_data, $total); 
						
						if ($method) {
						$json['payment_method'][$result['code']] = $method;
						}
						}
						}
						
						$sort_order = array(); 
						
						foreach ($json['payment_method'] as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
						}
						
					array_multisort($sort_order, SORT_ASC, $json['payment_method']);*/	
					
					$payment_modules = new Model_Payment();
					//$payment_modules->javascript_validation ();
					$payment_method = $payment_modules->selection ();
					$pm=array();
					foreach($payment_method as $k=>$v)
					{
						$pm[$v['id']]=array("code"=>$v['id'],"title"=>$v['module'],"sort_order"=>"");
					}
					$json['payment_method']=$pm;
					/*echo "<pre>";
						print_r($json['payment_method']);
						echo "</pre>";
					exit;*/
					if (!$json['payment_method']) {
						$json['error']['payment_method'] = "no payment options available";
						} elseif ($_REQUEST['payment_code']) {			
						if (!isset($json['payment_method'][$_REQUEST['payment_code']])) {
							$json['error']['payment_method'] = "payment method is mandatory!!";
						}
					}
				}
				
				if (!isset($json['error'])) { 
					$json['success'] = "Order total has been successfully recalculated!!";
					} else {
					$json['error']['warning'] = "please recheck the form for errors!!";
				}
				
				// Reset everything
				$cartObj->clear();
				//$custObj->logout();
				
				unset($_SESSION['shipping_method']);
				unset($_SESSION['shipping_methods']);
				unset($_SESSION['payment_method']);
				unset($_SESSION['payment_methods']);
				unset($_SESSION['coupon']);
				unset($_SESSION['reward']);
				unset($_SESSION['voucher']);
				unset($_SESSION['vouchers']);
				} else {
				$json['error']['warning'] = "you dont have permission!!";
			}
			echo Model_Json::encode($json);
		}
	}
	
