<?php
/*
  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com
  Copyright (c) 2003 osCommerce
  Released under the GNU General Public License
*/
// Please make sure you insert your merchant id in the OSC admin area
  class Model_Payment_Ebs {
    var $code, $title, $description, $enabled;
// class constructor
    function Model_Payment_Ebs() {
      global $order;
      $this->code = 'Ebs';
      $this->title =  @constant('MODULE_PAYMENT_EBS_TEXT_TITLE')==""?"EBS Online Payment Gateway":MODULE_PAYMENT_EBS_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_EBS_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_EBS_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_EBS_STATUS == 'True') ? true : false);
      $this->form_action_url = 'https://secure.ebs.in/pg/ma/sale/pay/';
      $this->order_status = DEFAULT_ORDERS_STATUS_ID;//kept by me as we dont have order status drop down july 17 2012
	  $this->db = Zend_Db_Table::getDefaultAdapter();
    }

// class methods
    function javascript_validation() {}
    function selection() {
      global $order;
      for ($i=1; $i < 13; $i++) {
        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
      }
      $today = getdate(); 
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }
      /*$selection = array('id' => $this->code,
                         'module' => $this->title,
                         'fields' => array(array('title' => $_SESSION['OBJ']['tr']->translate('MODULE_PAYMENT_EBS_TEXT_CREDIT_CARD_OWNER_FIRST_NAME'),
                                                 'field' => tep_draw_input_field('ebs_cc_owner_firstname', $order->billing['firstname'])),
                                           array('title' => $_SESSION['OBJ']['tr']->translate('MODULE_PAYMENT_EBS_TEXT_CREDIT_CARD_OWNER_LAST_NAME'),
                                                 'field' => tep_draw_input_field('ebs_cc_owner_lastname', $order->billing['lastname']))));*/
		$selection = array('id' => $this->code,
		'module' => $this->title);

      return $selection;
    }

    function pre_confirmation_check() {}

	function confirmation() {
      global $HTTP_POST_VARS;
	  $confirmation='';
      return $confirmation;
    }

    function process_button() {
      	$row=$this->getAddress();
		$cartObj=new Model_Cart();
		$custObj=new Model_Customer();
		if($_SESSION['guest']!="")
		{
			$telephone=$row['payment']['telephone'];
			$email=$row['payment']['email'];
		}
		else
		{
			$telephone=$custObj->getTelephone();
			$email=$custObj->getEmail();
		}
		$taxes=array_sum($cartObj->getTaxes());
		$complete_total=$cartObj->getTotal()+$_SESSION['shipping_method']['cost']+$taxes;

		$return_url=Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout-process"),'?DR=${DR}','');
		//all fields are mandatory
	  $process_button_string = tep_draw_hidden_field('account_id', MODULE_PAYMENT_EBS_ACCOUNTID).

								tep_draw_hidden_field('amount',number_format($complete_total,2,'.','')).

							   tep_draw_hidden_field('description','Order ID'. $_SESSION['order_id']).

                               tep_draw_hidden_field('reference_no', date('YmdHis')) .

                               tep_draw_hidden_field('name', $row['payment']['firstname'] . ' ' . $row['payment']['lastname']) .

                               tep_draw_hidden_field('address',$row['payment']['address_1'].",".$row['payment']['address_2']) .

                               tep_draw_hidden_field('city', $row['payment']['city']) .

                               tep_draw_hidden_field('state', $row['payment']['state']) .

                               tep_draw_hidden_field('postal_code', $row['payment']['postcode']) .

                               tep_draw_hidden_field('country', $row['payment']['iso_code_3']) .

                               tep_draw_hidden_field('email', $email) .

                               tep_draw_hidden_field('phone', $telephone) .

                               tep_draw_hidden_field('ship_name', $row['shipping']['firstname'] . ' ' . $row['shipping']['lastname']) .

                               tep_draw_hidden_field('ship_address', $row['shipping']['address_1'].",".$row['shipping']['address_2']) .

                               tep_draw_hidden_field('ship_city', $row['shipping']['city']) .

                               tep_draw_hidden_field('ship_state', $row['shipping']['state']) .

                               tep_draw_hidden_field('ship_postal_code', $row['shipping']['postcode']) .

                               tep_draw_hidden_field('ship_country', $row['shipping']['iso_code_3']) .

							   tep_draw_hidden_field('ship_phone', $telephone) .

							   tep_draw_hidden_field('mode', MODULE_PAYMENT_EBS_TESTMODE) .
							   
                               tep_draw_hidden_field('return_url',$return_url);
      
	  return $process_button_string;

    }



    function before_process() {
		$QueryString=substr($_SERVER['QUERY_STRING'],3,-1);//retrieve DR value
	  //$QueryString = ($_GET['DR']);
	  //$QueryString = $this->_request->DR;
  //echo "query string ".$QueryString;
	  $QueryString = preg_replace('/\s/','+',$QueryString);
	  
	  $secret_key = MODULE_PAYMENT_EBS_SECRET_KEY;
	//echo "value of ".$secret_key."<br/>";
	  $rc4 = new Model_Payment_Extension_Ebscrypt($secret_key);
	  $QueryString = base64_decode($QueryString);
	  $rc4->decrypt($QueryString);

	  $QueryString = split('[&]',$QueryString);
	
	  $response = array();
	  
	  foreach($QueryString as $param){
	   $param = split('=',$param);
	   $response[$param[0]] = $param[1];		
	  }
	  /*echo "<pre>";
	  print_r($response);
	  echo "</pre>";*/


      if (!isset($response['ResponseCode']) || !is_numeric($response['ResponseCode']) || $response['ResponseCode']  != '0') {
		$error_message=$_SESSION['OBJ']['tr']->translate('MODULE_PAYMENT_EBS_TEXT_ERROR_MESSAGE');
		//$error_redirect=Model_Url::getLink(array("controller"=>"checkout","action"=>"cart"),'error_message/'.urlencode($error_message),SERVER_SSL);
		$error_redirect=Model_Url::getLink(array("controller"=>"checkout","action"=>"cart"),'?error_message='.urlencode($error_message),SERVER_SSL);
	  header("location:".$error_redirect);
	  	  exit;

      } 
   }



    function after_process() {

      return false;

    }



    function get_error() {

      global $HTTP_GET_VARS;



      $error = array('title' => MODULE_PAYMENT_EBS_TEXT_ERROR,

                     'error' => stripslashes(urldecode($HTTP_GET_VARS['error'])));

      return $error;

    }



    function check() {

      if (!isset($this->_check)) {

        $check_query = $this->db->query("select configuration_value from r_configuration where configuration_key = 'MODULE_PAYMENT_EBS_STATUS'");
		$fch=$check_query->fetch();
        $this->_check = $fch->rowCount();//tep_db_num_rows($check_query);

      }

      return $this->_check;

    }



    function install() {


		$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_PAYMENT_EBS_TEXT_TITLE','configuration_value'=>'EBS Online Payment Gateway','configuration_description'=>'enter title to display in front end','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

      $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable EBS Pyment Module', 'MODULE_PAYMENT_EBS_STATUS', 'True', 'Do you want to accept EBS payments?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

      $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Merchant User ID', 'MODULE_PAYMENT_EBS_ACCOUNTID', '5', 'Your Merchant Account ID of EBS', '5800', '0', now())");

      $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Secret key', 'MODULE_PAYMENT_EBS_SECRET_KEY', '18157', 'Your secret key of EBS', '6', '0', now())");	  

      $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Test Mode', 'MODULE_PAYMENT_EBS_TESTMODE', 'TEST', 'Test mode used for the EBS', '6', '0', 'tep_cfg_select_option(array(\'TEST\', \'LIVE\'), ', now())");

	    $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_EBS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");

//exit;
    }

    function remove() {
      $this->db->query("delete from r_configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_EBS_SORT_ORDER','MODULE_PAYMENT_EBS_TEXT_TITLE','MODULE_PAYMENT_EBS_STATUS', 'MODULE_PAYMENT_EBS_ACCOUNTID', 'MODULE_PAYMENT_EBS_SECRET_KEY', 'MODULE_PAYMENT_EBS_TESTMODE');
    }

	public function getAddress()
		{
			if($_SESSION['customer_id']=="" && $_SESSION['guest']!="")
			{
  				$row['shipping']['firstname']=$_SESSION['guest']['shipping']['firstname'];
				$row['shipping']['lastname']=$_SESSION['guest']['shipping']['lastname'];
				$row['shipping']['address_1']=$_SESSION['guest']['shipping']['address_1'];
				$row['shipping']['address_2']=$_SESSION['guest']['shipping']['address_2'];
				$row['shipping']['postcode']=$_SESSION['guest']['shipping']['postcode'];
				$row['shipping']['city']=$_SESSION['guest']['shipping']['city'];
				$row['shipping']['state']=$_SESSION['guest']['shipping']['zone'];
				$row['shipping']['country']=$_SESSION['guest']['shipping']['country'];
				$row['shipping']['iso_code_2']=$_SESSION['guest']['shipping']['iso_code_2'];
				$row['shipping']['iso_code_3']=$_SESSION['guest']['shipping']['iso_code_3'];

				$row['payment']['firstname']=$_SESSION['guest']['payment']['firstname'];
				$row['payment']['lastname']=$_SESSION['guest']['payment']['lastname'];
				$row['payment']['address_1']=$_SESSION['guest']['payment']['address_1'];
				$row['payment']['address_2']=$_SESSION['guest']['payment']['address_2'];
				$row['payment']['postcode']=$_SESSION['guest']['payment']['postcode'];
				$row['payment']['city']=$_SESSION['guest']['payment']['city'];
				$row['payment']['state']=$_SESSION['guest']['payment']['zone'];
				$row['payment']['country']=$_SESSION['guest']['payment']['country'];
				$row['payment']['iso_code_2']=$_SESSION['guest']['shipping']['iso_code_2'];
				$row['payment']['iso_code_3']=$_SESSION['guest']['shipping']['iso_code_3'];
				$row['payment']['telephone']=$_SESSION['guest']['telephone'];
				$row['payment']['email']=$_SESSION['guest']['email'];
			}else
			{
				$address=array();
				if($_SESSION['shipping_address_id']==$_SESSION['payment_address_id'])
				{
					$address['shipping']=$_SESSION['shipping_address_id'];
				}
				else
				{
					$address['shipping']=$_SESSION['shipping_address_id'];
					$address['payment']=$_SESSION['payment_address_id'];
				}

				$row=array();
				foreach($address as $k=>$v)
				{
					$row[$k]=$this->db->fetchRow("select a.entry_firstname as firstname,a.entry_lastname as lastname,a.entry_street_address as address_1,entry_suburb as address_2,a.entry_city as city,a.entry_postcode as postcode,c.countries_iso_code_2 as iso_code_2,c.countries_iso_code_3 as iso_code_3,c.countries_name as country,z.zone_code,z.zone_name as state from r_address_book a,r_countries c,r_zones z where a.entry_zone_id=z.zone_id and a.entry_country_id=c.countries_id and a.address_book_id='".$v."'");
				}
				$row['payment']=$row['payment']==""?$row['shipping']:$row['payment'];
			}
			return $row;
		}

  }

?>