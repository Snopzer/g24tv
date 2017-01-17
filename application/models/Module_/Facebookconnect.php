<?php
class Model_Module_Facebookconnect {
	public $data;
	public $_array;
	
	public function __construct($var=null) {
		$module=$var[2];
		
		require APPLICATION_PATH.'/../ext/facebook/facebook.php';
		//exit;
		$facebook = new Facebook(array(
		'appId' => @constant('facebookconnect_apikey'),
		'secret' => @constant('facebookconnect_apisecret'),
		));

		$user = $facebook->getUser();

		if ($user) {
		try 
		{
			// Proceed knowing you have a logged in user who's authenticated.
			$user_profile = $facebook->api('/me');
		} catch (FacebookApiException $e) 
		{
			error_log($e);
			$user = null;
		}

		if (!empty($user_profile )) 
		{
			# User info ok? Let's print it (Here we will be adding the login and registering routines)

			$username = $user_profile['name'];
			$uid = $user_profile['id'];
			$email = $user_profile['email'];
			$user = new User();
			//exit("in if");
			$userdata = $user->checkUser($uid, 'facebook', $username,$email,$twitter_otoken,$twitter_otoken_secret);

			if(!empty($userdata))
			{
				session_start();
				$_SESSION['id'] = $userdata['id'];
				$_SESSION['oauth_id'] = $uid;

				$_SESSION['username'] = $userdata['username'];
				$_SESSION['email'] = $email;
				$_SESSION['oauth_provider'] = $userdata['oauth_provider'];
				header("Location: home.php");
			}

		} else 
		{
			//exit("in if else");
			# For testing purposes, if there was an error, let's kill the script
			die("There was an error.");
		}
		} else {
		//exit("in else");
		# There's no active session, let's generate one
		$login_url = $facebook->getLoginUrl(array( 'scope' => 'email'));
		//header("Location: " . $login_url);
		$this->data['facebook_url']=$login_url;
		}
		$this->_array['data']=$this->data;
	}

	public function updateModule()
	{
		$prefix="facebookconnect";
		$setobj=new Model_AdminModuleSetting();
		$setobj->getConstant($prefix);
		$front = Zend_Controller_Front::getInstance();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

				$setobj->editSetting($prefix, $this->_getAllParams());
				$this->_redirect('module.phtml?msg=update successfull');
			}
		$ifam=$front->getRequest()->getParam($prefix.'_module');
		if (isset($ifam)) {
			$modules = explode(',', $ifam);
		} elseif (@constant($prefix.'_module') != '') {
			$modules = explode(',',@constant($prefix.'_module'));
		} else {
			$modules = array();
		}
		
		$ifamkey=$front->getRequest()->getParam($prefix.'_apikey');
		if (isset($ifamkey)) {
			$this->data[$prefix.'_apikey'] = $ifamkey;
		} else {
			$this->data[$prefix.'_apikey'] = @constant($prefix.'_apikey');
		}

		$ifamsec=$front->getRequest()->getParam($prefix.'_apisecret');
		if (isset($ifamsec)) {
			$this->data[$prefix.'_apisecret'] = $ifamsec;
		} else {
			$this->data[$prefix.'_apisecret'] = @constant($prefix.'_apisecret');
		}

		$ifambut=$front->getRequest()->getParam($prefix.'_button');
		if (isset($ifambut)) {
			$this->data[$prefix.'_button'] = $ifambut;
		} else {
			$this->data[$prefix.'_button'] = @constant($prefix.'_button');
		}

		$this->data['layouts']=$setobj->getLayouts();
		foreach ($modules as $module) {
			$ifamlid=$front->getRequest()->getParam($prefix.'_'.$module.'_layout_id');
			if (isset($ifamlid)) {
				$this->data[$prefix.'_' . $module . '_layout_id'] = $ifamlid;
			} else {
				$this->data[$prefix.'_' . $module . '_layout_id'] = @constant($prefix.'_' . $module . '_layout_id');
			}

			 

			$ifampos=$front->getRequest()->getParam($prefix.'_'.$module.'_position');
			if (isset($ifampos)) {
				$this->data[$prefix.'_' . $module . '_position'] = $ifampos;
			} else {
				$this->data[$prefix.'_' . $module . '_position'] = @constant($prefix.'_' . $module . '_position');
			}

			$ifamstatus=$front->getRequest()->getParam($prefix.'_'.$module.'_status');
			if (isset($ifamstatus)) {
				$this->data[$prefix.'_' . $module . '_status'] = $ifamstatus;
			} else {
				$this->data[$prefix.'_' . $module . '_status'] = @constant($prefix.'_' . $module . '_status');
			}

			$ifamsort=$front->getRequest()->getParam($prefix.'_'.$module.'_sort_order');
			if (isset($ifamsort)) {
				$this->data[$prefix.'_' . $module . '_sort_order'] = $ifamsort;
			} else {
				$this->data[$prefix.'_' . $module . '_sort_order'] = @constant($prefix.'_' . $module . '_sort_order');
			}
		}

		$this->data['modules'] = $modules;
		$ifam=$front->getRequest()->getParam($prefix.'_module');
		if (isset($ifam)) {
			$this->data[$prefix.'_module'] =$ifam;
		} else {
			$this->data[$prefix.'_module'] = @constant($prefix.'_module');
		}
		return $this->data;
	}

}
?>