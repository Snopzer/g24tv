<?php
/*
  $Id: $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2009 osCommerce

  Released under the GNU General Public License
*/

  function sage_pay_form_textarea_field($value = '', $key = '') {
    return tep_draw_textarea_field('configuration[' . $key . ']', 'soft', 60, 5, $value);
  }

  class Model_Payment_SagePayForm {
    var $code, $title, $description, $enabled;

// class constructor
    function Model_Payment_SagePayForm() {
      //global $order;
		$order=$_SESSION['order'];
      $this->signature = 'sage_pay|sage_pay_form|1.2|2.2';

      $this->code = 'SagePayForm';
      $this->title = @constant('MODULE_PAYMENT_SAGE_PAY_FORM_TEXT_TITLE')==""?"Sage Pay Form Payment Gateway":MODULE_PAYMENT_SAGE_PAY_FORM_TEXT_TITLE;//MODULE_PAYMENT_SAGE_PAY_FORM_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_SAGE_PAY_FORM_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_SAGE_PAY_FORM_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_SAGE_PAY_FORM_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_SAGE_PAY_FORM_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

	  $this->db = Zend_Db_Table::getDefaultAdapter();

      switch (MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_SERVER) {
        case 'Live':
          $this->form_action_url = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
          break;

        case 'Test':
          $this->form_action_url = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
          break;

        default:
          $this->form_action_url = 'https://test.sagepay.com/Simulator/VSPFormGateway.asp';
          break;
      }
    }

// class methods
    function update_status() {
      //global $order;
	  $order=$_SESSION['order'];

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_SAGE_PAY_FORM_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_SAGE_PAY_FORM_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
    }

    function javascript_validation() {
      return false;
    }

    function selection() {
      return array('id' => $this->code,
                   'module' => $this->title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      return false;
    }

    function process_button() {
      global $customer_id, $order, $currency, $cartID;
		/*$customer_id=$_SESSION['customer_id'];
		$order=$_SESSION['order'];
		$currency=$_SESSION['currency'];
		$cartID=$_SESSION['cartID'];*/
        $process_button_string = '';

      $params = array('VPSProtocol' => '2.23',
                      'ReferrerID' => 'C74D7B82-E9EB-4FBD-93DB-76F0F551C802',
                      'Vendor' => substr(MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_LOGIN_NAME, 0, 15));

      if ( MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_METHOD == 'Payment' ) {
        $params['TxType'] = 'PAYMENT';
      } elseif ( MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_METHOD == 'Deferred' ) {
        $params['TxType'] = 'DEFERRED';
      } else {
        $params['TxType'] = 'AUTHENTICATE';
      }

      $crypt = array('VendorTxCode' => substr(date('YmdHis') . '-' . $customer_id . '-' . $cartID, 0, 40),
                     'Amount' => $this->format_raw($order->info['total']),
                     'Currency' => $currency,
                     'Description' => substr(STORE_NAME, 0, 100),
                     'SuccessURL' => tep_href_link(FILENAME_CHECKOUT_PROCESS, tep_session_name() . '=' . tep_session_id(), 'SSL', false),
                     'FailureURL' => tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . '&' . tep_session_name() . '=' . tep_session_id(), 'SSL', false),
                     'CustomerName' => substr($order->billing['firstname'] . ' ' . $order->billing['lastname'], 0, 100),
                     'CustomerEMail' => substr($order->customer['email_address'], 0, 255),
                     'BillingSurname' => substr($order->billing['lastname'], 0, 20),
                     'BillingFirstnames' => substr($order->billing['firstname'], 0, 20),
                     'BillingAddress1' => substr($order->billing['street_address'], 0, 100),
                     'BillingCity' => substr($order->billing['city'], 0, 40),
                     'BillingPostCode' => substr($order->billing['postcode'], 0, 10),
                     'BillingCountry' => $order->billing['country']['iso_code_2']);

      if ($crypt['BillingCountry'] == 'US') {
        $crypt['BillingState'] = tep_get_zone_code($order->billing['country']['id'], $order->billing['zone_id'], '');
      }

      $crypt['BillingPhone'] = substr($order->customer['telephone'], 0, 20);
      $crypt['DeliverySurname'] = substr($order->delivery['lastname'], 0, 20);
      $crypt['DeliveryFirstnames'] = substr($order->delivery['firstname'], 0, 20);
      $crypt['DeliveryAddress1'] = substr($order->delivery['street_address'], 0, 100);
      $crypt['DeliveryCity'] = substr($order->delivery['city'], 0, 40);
      $crypt['DeliveryPostCode'] = substr($order->delivery['postcode'], 0, 10);
      $crypt['DeliveryCountry'] = $order->delivery['country']['iso_code_2'];

      if ($crypt['DeliveryCountry'] == 'US') {
        $crypt['DeliveryState'] = tep_get_zone_code($order->delivery['country']['id'], $order->delivery['zone_id'], '');
      }

      if (tep_not_null(MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_EMAIL)) {
        $crypt['VendorEMail'] = substr(MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_EMAIL, 0, 255);
      }

      switch (MODULE_PAYMENT_SAGE_PAY_FORM_SEND_EMAIL) {
        case 'No One':
          $crypt['SendEMail'] = 0;
          break;

        case 'Customer and Vendor':
          $crypt['SendEMail'] = 1;
          break;

        case 'Vendor Only':
          $crypt['SendEMail'] = 2;
          break;
      }

      if (tep_not_null(MODULE_PAYMENT_SAGE_PAY_FORM_CUSTOMER_EMAIL_MESSAGE)) {
        $crypt['eMailMessage'] = substr(MODULE_PAYMENT_SAGE_PAY_FORM_CUSTOMER_EMAIL_MESSAGE, 0, 7500);
      }

      $contents = array();

      foreach ($order->products as $product) {
        $product_name = $product['name'];

        if (isset($product['attributes'])) {
          foreach ($product['attributes'] as $att) {
            $product_name .= '; ' . $att['option'] . '=' . $att['value'];
          }
        }

        $contents[] = str_replace(array(':', "\n", "\r", '&'), '', $product_name) . ':' . $product['qty'] . ':' . $this->format_raw($product['final_price']) . ':' . $this->format_raw(($product['tax'] / 100) * $product['final_price']) . ':' . $this->format_raw((($product['tax'] / 100) * $product['final_price']) + $product['final_price']) . ':' . $this->format_raw(((($product['tax'] / 100) * $product['final_price']) + $product['final_price']) * $product['qty']);
      }

      foreach ($this->getOrderTotalsSummary() as $ot) {
        $contents[] = str_replace(array(':', "\n", "\r", '&'), '', strip_tags($ot['title'])) . ':---:---:---:---:' . $this->format_raw($ot['value']);
      }

      $crypt['Basket'] = substr(sizeof($contents) . ':' . implode(':', $contents), 0, 7500);
      $crypt['Apply3DSecure'] = '0';

      $crypt_string = '';

      foreach ($crypt as $key => $value) {
        $crypt_string .= $key . '=' . trim($value) . '&';
      }

      $crypt_string = substr($crypt_string, 0, -1);
echo $crypt_string;

      $params['Crypt'] = base64_encode($this->simpleXor($crypt_string, MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD));

      foreach ($params as $key => $value) {
        $process_button_string .= tep_draw_hidden_field($key, $value);
      }
//exit;
      return $process_button_string;
    }

    function before_process() {
      //global $HTTP_GET_VARS, $HTTP_POST_VARS, $order;
		$order=$_SESSION['order'];
      if (isset($HTTP_GET_VARS['crypt']) && tep_not_null($HTTP_GET_VARS['crypt'])) {
        $transaction_response = $this->simpleXor($this->base64Decode($HTTP_GET_VARS['crypt']), MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD);

        $string_array = explode('&', $transaction_response);
        $return = array('Status' => null);

        foreach ($string_array as $string) {
          if (strpos($string, '=') != false) {
            $parts = explode('=', $string, 2);
            $return[trim($parts[0])] = trim($parts[1]);
          }
        }

        if ( ($return['Status'] != 'OK') && ($return['Status'] != 'AUTHENTICATED') && ($return['Status'] != 'REGISTERED') ) {
          $error = $this->getErrorMessageNumber($return['StatusDetail']);

          tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code . (tep_not_null($error) ? '&error=' . $error : ''), 'SSL'));
        }

        if ( isset($return['VPSTxId']) ) {
          $order->info['comments'] = 'Sage Pay Reference ID: ' . $return['VPSTxId'] . (tep_not_null($order->info['comments']) ? "\n\n" . $order->info['comments'] : '');
        }
      } else {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL'));
      }
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $HTTP_GET_VARS;

      $message = MODULE_PAYMENT_SAGE_PAY_FORM_ERROR_GENERAL;

      if ( isset($HTTP_GET_VARS['error']) && is_numeric($HTTP_GET_VARS['error']) && $this->errorMessageNumberExists($HTTP_GET_VARS['error']) ) {
        $message = $this->getErrorMessage($HTTP_GET_VARS['error']) . ' ' . MODULE_PAYMENT_SAGE_PAY_FORM_ERROR_GENERAL;
      } elseif (isset($HTTP_GET_VARS['crypt']) && tep_not_null($HTTP_GET_VARS['crypt'])) {
        $transaction_response = $this->simpleXor($this->base64Decode($HTTP_GET_VARS['crypt']), MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD);

        $string_array = explode('&', $transaction_response);
        $return = array('Status' => null);

        foreach ($string_array as $string) {
          if (strpos($string, '=') != false) {
            $parts = explode('=', $string, 2);
            $return[trim($parts[0])] = trim($parts[1]);
          }
        }

        $error_number = $this->getErrorMessageNumber($return['StatusDetail']);

        if ( is_numeric($error_number) && $this->errorMessageNumberExists($error_number) ) {
          $message = $this->getErrorMessage($error_number) . ' ' . MODULE_PAYMENT_SAGE_PAY_FORM_ERROR_GENERAL;
        }
      }

      $error = array('title' => MODULE_PAYMENT_SAGE_PAY_FORM_ERROR_TITLE,
                     'error' => $message);

      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_SAGE_PAY_FORM_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {

	$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_TEXT_TITLE','configuration_value'=>'Sage Pay Form Payment Gateway','configuration_description'=>'enter title to display in front end','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

	$this->db->insert('r_configuration',array('configuration_title'=>'Enable Sage Pay Form Module','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_STATUS','configuration_value'=>'False','configuration_description'=>'Do you want to accept Sage Pay Form payments?','configuration_group_id'=>'6','sort_order'=>'0','set_function'=>'tep_cfg_select_option(array(\'True\', \'False\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

	$this->db->insert('r_configuration',array('configuration_title'=>'Vendor Login Name','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_LOGIN_NAME','configuration_value'=>'','configuration_description'=>'The vendor login name to connect to the gateway with.','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

$this->db->insert('r_configuration',array('configuration_title'=>'Encryption Password','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD','configuration_value'=>'','configuration_description'=>'The encrpytion password to secure transactions with.','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

	 $this->db->insert('r_configuration',array('configuration_title'=>'Transaction Method','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_METHOD','configuration_value'=>'Authenticate','configuration_description'=>'The processing method to use for each transaction.','configuration_group_id'=>'6','sort_order'=>'0','set_function'=>'tep_cfg_select_option(array(\'Authenticate\', \'Deferred\', \'Payment\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

	 $this->db->insert('r_configuration',array('configuration_title'=>'Transaction Server','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_SERVER','configuration_value'=>'Simulator','configuration_description'=>'Perform transactions on the production server or on the testing server.','configuration_group_id'=>'6','sort_order'=>'0','set_function'=>'tep_cfg_select_option(array(\'Live\', \'Test\', \'Simulator\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

      $this->db->insert('r_configuration',array('configuration_title'=>'Vendor E-Mail','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_EMAIL','configuration_value'=>'','configuration_description'=>'An e-mail address on which you can be contacted when a transaction completes. NOTE: If you wish to use multiple email addresses, you should add them using the : (colon) character as a separator. e.g. me@mail1.com:me@mail2.com','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));


$this->db->insert('r_configuration',array('configuration_title'=>'Send E-Mail','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_SEND_EMAIL','configuration_value'=>'Customer and Vendor','configuration_description'=>'Who to send e-mails to.','configuration_group_id'=>'6','sort_order'=>'0','set_function'=>'tep_cfg_select_option(array(\'No One\', \'Customer and Vendor\', \'Vendor Only\'),', 'date_added'=>new Zend_Db_Expr('NOW()')));

$this->db->insert('r_configuration',array('configuration_title'=>'Customer E-Mail Message','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_CUSTOMER_EMAIL_MESSAGE','configuration_value'=>'','configuration_description'=>'A message to the customer which is inserted into the successful transaction e-mails only.','configuration_group_id'=>'6','sort_order'=>'0','set_function'=>'sage_pay_form_textarea_field(', 'date_added'=>new Zend_Db_Expr('NOW()')));

$this->db->insert('r_configuration',array('configuration_title'=>'Sort order of display.','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_SORT_ORDER','configuration_value'=>'0','configuration_description'=>'Sort order of display. Lowest is displayed first.','configuration_group_id'=>'6','sort_order'=>'0', 'date_added'=>new Zend_Db_Expr('NOW()')));

$this->db->insert('r_configuration',array('configuration_title'=>'Payment Zone','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_ZONE','configuration_value'=>'0','configuration_description'=>'If a zone is selected, only enable this payment method for that zone.','configuration_group_id'=>'6','sort_order'=>'2','use_function'=>'tep_get_zone_class_title','set_function'=> 'tep_cfg_pull_down_zone_classes(', 'date_added'=>new Zend_Db_Expr('NOW()')));

$this->db->insert('r_configuration',array('configuration_title'=>'Set Order Status','configuration_key'=>'MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID','configuration_value'=>'0','configuration_description'=>'Set the status of orders made with this payment module to this value','configuration_group_id'=>'6','sort_order'=>'0','use_function'=>'tep_get_order_status_name','set_function'=> 'tep_cfg_pull_down_order_statuses(', 'date_added'=>new Zend_Db_Expr('NOW()')));

    }

    function remove() {
        $this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");
	}

    function keys() {
      return array('MODULE_PAYMENT_SAGE_PAY_FORM_TEXT_TITLE','MODULE_PAYMENT_SAGE_PAY_FORM_STATUS', 'MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_LOGIN_NAME', 'MODULE_PAYMENT_SAGE_PAY_FORM_ENCRYPTION_PASSWORD', 'MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_METHOD', 'MODULE_PAYMENT_SAGE_PAY_FORM_TRANSACTION_SERVER', 'MODULE_PAYMENT_SAGE_PAY_FORM_VENDOR_EMAIL', 'MODULE_PAYMENT_SAGE_PAY_FORM_SEND_EMAIL', 'MODULE_PAYMENT_SAGE_PAY_FORM_CUSTOMER_EMAIL_MESSAGE', 'MODULE_PAYMENT_SAGE_PAY_FORM_ZONE', 'MODULE_PAYMENT_SAGE_PAY_FORM_ORDER_STATUS_ID', 'MODULE_PAYMENT_SAGE_PAY_FORM_SORT_ORDER');
    }

// format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '') {
     // global $currencies, $currency;
		$currencies=$_SESSION['currencies'];
		$currency=$_SESSION['currency'];
      if (empty($currency_code) || !$currencies->is_set($currency_code)) {
        $currency_code = $currency;
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }

    function getOrderTotalsSummary() {
      global $order_total_modules;
	  //$order_total_modules=$_SESSION['order_total_modules'];
      $order_total_array = array();

      if (is_array($order_total_modules->modules)) {
        foreach ($order_total_modules->modules as $value) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($GLOBALS[$class]->enabled) {
            for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
              if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                $order_total_array[] = array('code' => $GLOBALS[$class]->code,
                                             'title' => $GLOBALS[$class]->output[$i]['title'],
                                             'text' => $GLOBALS[$class]->output[$i]['text'],
                                             'value' => $GLOBALS[$class]->output[$i]['value'],
                                             'sort_order' => $GLOBALS[$class]->sort_order);
              }
            }
          }
        }
      }

      return $order_total_array;
    }

    function loadErrorMessages() {
      $errors = array();

      if (file_exists(dirname(__FILE__) . '/../../../ext/modules/payment/sage_pay/errors.php')) {
        include(dirname(__FILE__) . '/../../../ext/modules/payment/sage_pay/errors.php');
      }

      $this->_error_messages = $errors;
    }

    function getErrorMessageNumber($string) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      $error = explode(' ', $string, 2);

      if (is_numeric($error[0]) && $this->errorMessageNumberExists($error[0])) {
        return $error[0];
      }

      return false;
    }

    function getErrorMessage($number) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      if (is_numeric($number) && $this->errorMessageNumberExists($number)) {
        return $this->_error_messages[$number];
      }

      return false;
    }

    function errorMessageNumberExists($number) {
      if (!isset($this->_error_messages)) {
        $this->loadErrorMessages();
      }

      return (is_numeric($number) && isset($this->_error_messages[$number]));
    }

/*  From the Sage Pay Form PHP Kit:
**  The SimpleXor encryption algorithm                                                                                **
**  NOTE: This is a placeholder really.  Future releases of VSP Form will use AES or TwoFish.  Proper encryption      **
**  This simple function and the Base64 will deter script kiddies and prevent the "View Source" type tampering        **
**  It won't stop a half decent hacker though, but the most they could do is change the amount field to something     **
**  else, so provided the vendor checks the reports and compares amounts, there is no harm done.  It's still          **
**  more secure than the other PSPs who don't both encrypting their forms at all                                      */

    function simpleXor($InString, $Key) {
// Initialise key array
      $KeyList = array();
// Initialise out variable
      $output = "";

// Convert $Key into array of ASCII values
      for ($i=0; $i<strlen($Key); $i++) {
        $KeyList[$i] = ord(substr($Key, $i, 1));
      }

// Step through string a character at a time
      for ($i=0; $i<strlen($InString); $i++) {
// Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the two, get the character from the result
// % is MOD (modulus), ^ is XOR
        $output .= @chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
      }

// Return the result
      return $output;
    }

/*  From the Sage Pay Form PHP Kit:
** Base 64 decoding function **
** PHP does it natively but just for consistency and ease of maintenance, let's declare our own function **/

    function base64Decode($scrambled) {
// Initialise output variable
      $output = '';

// Fix plus to space conversion issue
      $scrambled = str_replace(' ', '+', $scrambled);

// Do encoding
      $output = base64_decode($scrambled);

// Return the result
      return $output;
    }
  }
?>
