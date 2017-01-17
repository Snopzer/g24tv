<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

 class Model_Payment_Direcpay {
    var $code, $title, $description, $enabled;

	function Model_Payment_Direcpay()	{
		 global $order;

		$this->code = 'Direcpay';
		$this->collaborator = 'TOML';
		$this->title = @constant('MODULE_PAYMENT_DIRECPAY_TEXT_TITLE')==""?"Direc Pay":MODULE_PAYMENT_DIRECPAY_TEXT_TITLE; // from lang-module-payment folder
		$this->public_title = MODULE_PAYMENT_DIRECPAY_TEXT_PUBLIC_TITLE; // from lang-module-payment folder
		$this->description = MODULE_PAYMENT_DIRECPAY_TEXT_DESCRIPTION;// from lang-module-payment folder
		$this->sort_order = MODULE_PAYMENT_DIRECPAY_SORT_ORDER; /// from database configuration table
		$this->enabled = ((MODULE_PAYMENT_DIRECPAY_STATUS == 'True') ? true : false); /// from database configuration table

		  if ((int)MODULE_PAYMENT_DIRECPAY_PREPARE_ORDER_STATUS_ID > 0) { /// from database configuration table
			$this->order_status = MODULE_PAYMENT_DIRECPAY_PREPARE_ORDER_STATUS_ID;
		  }

		  if (is_object($order)) $this->update_status();

		 /* if (MODULE_PAYMENT_DIRECPAY_GATEWAY_SERVER == 'Live') { /// from database configuration table
			$this->form_action_url = 'https://www.timesofmoney.com/direcpay/secure/dpMerchantParams.jsp';
		  } else {
			$this->form_action_url = 'https://test.timesofmoney.com/direcpay/secure/dpMerchantTransaction.jsp';
		  }  */

  		  if (MODULE_PAYMENT_DIRECPAY_GATEWAY_SERVER == 'Live') { /// from database configuration table
			$this->form_action_url = 'https://www.timesofmoney.com/direcpay/secure/dpMerchantTransaction.jsp';
			$this->collaborator='DirecPay';
		  } else {
			$this->form_action_url = 'https://test.timesofmoney.com/direcpay/secure/dpMerchantTransaction.jsp';
			$this->collaborator='TOML';
		  }
		  	  $this->db = Zend_Db_Table::getDefaultAdapter();


	}
	// ------------------------------------------------------------------------------------------------------------------ //
    function update_status() {
      global $order;

	}
	// ------------------------------------------------------------------------------------------------------------------ //
	function javascript_validation() {
      return false;
    }
	// ------------------------------------------------------------------------------------------------------------------ //
	function selection() {
      /*MAY 25 2012
	  global $cart_DirecPay_ID;

      if (tep_session_is_registered('cart_DirecPay_ID')) {
        $order_id = substr($cart_DirecPay_ID, strpos($cart_DirecPay_ID, '-')+1);

        $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

        if (tep_db_num_rows($check_query) < 1) {
          tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
          tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');

          tep_session_unregister('cart_DirecPay_ID');
        }
      }*/

      return array('id' => $this->code,
                   'module' => $this->title);
    }
	// ------------------------------------------------------------------------------------------------------------------ //
	function pre_confirmation_check() {
     /*MAY 25 2012 global $cartID, $cart;

      if (empty($cart->cartID)) {
        $cartID = $cart->cartID = $cart->generate_cart_id();
      }

      if (!tep_session_is_registered('cartID')) {
        tep_session_register('cartID');
      }*/
	  return false;
    }
	// ------------------------------------------------------------------------------------------------------------------ //
	 function confirmation() {
      /*may 25 2012
	  global $cartID, $cart_DirecPay_ID, $customer_id, $languages_id, $order, $order_total_modules;

      if (tep_session_is_registered('cartID')) {
        $insert_order = false;

        if (tep_session_is_registered('cart_DirecPay_ID')) {
          $order_id = substr($cart_DirecPay_ID, strpos($cart_DirecPay_ID, '-')+1);

          $curr_check = tep_db_query("select currency from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
          $curr = tep_db_fetch_array($curr_check);

          if ( ($curr['currency'] != $order->info['currency']) || ($cartID != substr($cart_DirecPay_ID, 0, strlen($cartID))) ) {
            $check_query = tep_db_query('select orders_id from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '" limit 1');

            if (tep_db_num_rows($check_query) < 1) {
              tep_db_query('delete from ' . TABLE_ORDERS . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_TOTAL . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_STATUS_HISTORY . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . ' where orders_id = "' . (int)$order_id . '"');
              tep_db_query('delete from ' . TABLE_ORDERS_PRODUCTS_DOWNLOAD . ' where orders_id = "' . (int)$order_id . '"');
            }

            $insert_order = true;
          }
        } else {
          $insert_order = true;
        }

        if ($insert_order == true) {
          $order_totals = array();
          if (is_array($order_total_modules->modules)) {
            reset($order_total_modules->modules);
            while (list(, $value) = each($order_total_modules->modules)) {
              $class = substr($value, 0, strrpos($value, '.'));
              if ($GLOBALS[$class]->enabled) {
                for ($i=0, $n=sizeof($GLOBALS[$class]->output); $i<$n; $i++) {
                  if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                    $order_totals[] = array('code' => $GLOBALS[$class]->code,
                                            'title' => $GLOBALS[$class]->output[$i]['title'],
                                            'text' => $GLOBALS[$class]->output[$i]['text'],
                                            'value' => $GLOBALS[$class]->output[$i]['value'],
                                            'sort_order' => $GLOBALS[$class]->sort_order);
                  }
                }
              }
            }
          }

          $sql_data_array = array('customers_id' => $customer_id,
                                  'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                                  'customers_company' => $order->customer['company'],
                                  'customers_street_address' => $order->customer['street_address'],
                                  'customers_suburb' => $order->customer['suburb'],
                                  'customers_city' => $order->customer['city'],
                                  'customers_postcode' => $order->customer['postcode'],
                                  'customers_state' => $order->customer['state'],
                                  'customers_country' => $order->customer['country']['title'],
                                  'customers_telephone' => $order->customer['telephone'],
                                  'customers_email_address' => $order->customer['email_address'],
                                  'customers_address_format_id' => $order->customer['format_id'],
                                  'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
                                  'delivery_company' => $order->delivery['company'],
                                  'delivery_street_address' => $order->delivery['street_address'],
                                  'delivery_suburb' => $order->delivery['suburb'],
                                  'delivery_city' => $order->delivery['city'],
                                  'delivery_postcode' => $order->delivery['postcode'],
                                  'delivery_state' => $order->delivery['state'],
                                  'delivery_country' => $order->delivery['country']['title'],
                                  'delivery_address_format_id' => $order->delivery['format_id'],
                                  'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                                  'billing_company' => $order->billing['company'],
                                  'billing_street_address' => $order->billing['street_address'],
                                  'billing_suburb' => $order->billing['suburb'],
                                  'billing_city' => $order->billing['city'],
                                  'billing_postcode' => $order->billing['postcode'],
                                  'billing_state' => $order->billing['state'],
                                  'billing_country' => $order->billing['country']['title'],
                                  'billing_address_format_id' => $order->billing['format_id'],
                                  'payment_method' => $order->info['payment_method'],
                                  'cc_type' => $order->info['cc_type'],
                                  'cc_owner' => $order->info['cc_owner'],
                                  'cc_number' => $order->info['cc_number'],
                                  'cc_expires' => $order->info['cc_expires'],
                                  'date_purchased' => 'now()',
                                  'orders_status' => $order->info['order_status'],
                                  'currency' => $order->info['currency'],
                                  'currency_value' => $order->info['currency_value']);

          tep_db_perform(TABLE_ORDERS, $sql_data_array);

          $insert_id = tep_db_insert_id();

          for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
            $sql_data_array = array('orders_id' => $insert_id,
                                    'title' => $order_totals[$i]['title'],
                                    'text' => $order_totals[$i]['text'],
                                    'value' => $order_totals[$i]['value'],
                                    'class' => $order_totals[$i]['code'],
                                    'sort_order' => $order_totals[$i]['sort_order']);

            tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
          }

          for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
            $sql_data_array = array('orders_id' => $insert_id,
                                    'products_id' => tep_get_prid($order->products[$i]['id']),
                                    'products_model' => $order->products[$i]['model'],
                                    'products_name' => $order->products[$i]['name'],
                                    'products_price' => $order->products[$i]['price'],
                                    'final_price' => $order->products[$i]['final_price'],
                                    'products_tax' => $order->products[$i]['tax'],
                                    'products_quantity' => $order->products[$i]['qty']);

            tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);

            $order_products_id = tep_db_insert_id();

            $attributes_exist = '0';
            if (isset($order->products[$i]['attributes'])) {
              $attributes_exist = '1';
              for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
                if (DOWNLOAD_ENABLED == 'true') {
                  $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                       from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                       left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                       on pa.products_attributes_id=pad.products_attributes_id
                                       where pa.products_id = '" . $order->products[$i]['id'] . "'
                                       and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . $languages_id . "'
                                       and poval.language_id = '" . $languages_id . "'";
                  $attributes = tep_db_query($attributes_query);
                } else {
                  $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
                }
                $attributes_values = tep_db_fetch_array($attributes);

                $sql_data_array = array('orders_id' => $insert_id,
                                        'orders_products_id' => $order_products_id,
                                        'products_options' => $attributes_values['products_options_name'],
                                        'products_options_values' => $attributes_values['products_options_values_name'],
                                        'options_values_price' => $attributes_values['options_values_price'],
                                        'price_prefix' => $attributes_values['price_prefix']);

                tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

                if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
                  $sql_data_array = array('orders_id' => $insert_id,
                                          'orders_products_id' => $order_products_id,
                                          'orders_products_filename' => $attributes_values['products_attributes_filename'],
                                          'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                                          'download_count' => $attributes_values['products_attributes_maxcount']);

                  tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                }
              }
            }
          }

          $cart_DirecPay_ID = $cartID . '-' . $insert_id;
          tep_session_register('cart_DirecPay_ID');
        }
      }

      return false;*/
    }
	// ------------------------------------------------------------------------------------------------------------------ //
	 function process_button() {
      //global $customer_id, $order, $sendto, $currency, $cart_DirecPay_ID, $shipping;
	 $obj=new Model_OrderTotal_OtVoucher();
	 //print_r($obj->output);
		$adres=$this->getAddress();
		$cartObj=new Model_Cart();
		$custObj=new Model_Customer();
		$taxes=array_sum($cartObj->getTaxes());
		
		$complete_total=$cartObj->getTotal()+$_SESSION['shipping_method']['cost']+$taxes;
		//start voucher reduction
		if(isset($_SESSION['voucher']) && $_SESSION['voucher']!="")
		{
			$vou=$cartObj->getVoucherDiscount($complete_total,$_SESSION['voucher']);
			//echo "value of ".$vou." total ".$complete_total;
			$complete_total=$complete_total-$vou;
		}
		//end voucher reduction

	  $custom_id=$_SESSION['customer_id']!=""?$_SESSION['customer_id']:'0';

	  $process_button_string = '';

	  $parameters['custName'] = $adres['payment']['firstname']." ".$adres['payment']['lastname'] ;
	  $parameters['custAddress'] =  $adres['payment']['address_1'];
	  $parameters['custCity'] = $adres['payment']['city'];
	  $parameters['custState'] =  $adres['payment']['state'];
	  $parameters['custPinCode'] = $adres['payment']['postcode'];
	  $parameters['custCountry'] = $adres['payment']['iso_code_2'];
	  //$parameters['custPhoneNo1'] = '91';
	  //$parameters['custPhoneNo2'] = '022';
	  //$parameters['custPhoneNo3'] = '9949600120';
		/*$email=$custObj->getEmail();
		if($email!="")
		{
			$parameters['custEmailId'] = $email;
		}*/

		
		
		if($_SESSION['guest']!="")
		{

			$parameters['custEmailId'] = $adres['payment']['email'];
			$parameters['custMobileNo']=$adres['payment']['telephone'];
			$parameters['deliveryMobileNo']=$adres['payment']['telephone'];
		}
		else
		{
			$tele=$custObj->getTelephone();
			$email=$custObj->getEmail();
			$parameters['custEmailId'] = $email;
			$parameters['custMobileNo'] = $tele;
			$parameters['deliveryMobileNo'] = $tele;
		}

		/*if($tele!="")
		{
			$parameters['custMobileNo'] = $tele;
			$parameters['deliveryMobileNo'] = $tele;
		}*/

	  $parameters['deliveryName'] = $adres['shipping']['firstname'];
	  $parameters['deliveryAddress'] = $adres['shipping']['address_1'];
	  $parameters['deliveryCity'] = $adres['shipping']['city'];
	  $parameters['deliveryState'] = $adres['shipping']['state'];
	   $parameters['deliveryPinCode'] = $adres['shipping']['postcode'];
	  $parameters['deliveryCountry'] = $adres['shipping']['iso_code_2'];
	  //$parameters['deliveryPhNo1'] = '91';
	  //$parameters['deliveryPhNo2'] = '022';
	  //$parameters['deliveryPhNo3'] = '40000000';

	  //$parameters['deliveryMobileNo'] = '9111111111';
	  $parameters['otherNotes'] = 'Online payment';
	  $parameters['editAllowed'] = 'N';
	  $order_id = substr($cart_DirecPay_ID, strpos($cart_DirecPay_ID, '-')+1);
  	  $parameters['requestparameter'] = MODULE_PAYMENT_DIRECPAY_MERCHANT_ID.'|DOM|'.$adres['shipping']['iso_code_3'].'|'.$_SESSION['Curr']['currency'].'|'.$this->format_raw($complete_total).'|'.$_SESSION['order_id'].'|others|'.Model_Url::getLink(array("controller"=>"checkout","action"=>"checkout-process"),'',SERVER_SSL).'|
'.Model_Url::getLink(array("controller"=>"checkout","action"=>"cart"),'',SERVER_SSL).'|'.$this->collaborator.'';
//echo "value of ".$parameters['requestparameter'];
        reset($parameters);
        while (list($key, $value) = each($parameters)) {
          $process_button_string .= tep_draw_hidden_field($key, $value);
        }

      return $process_button_string;
    }
	// ------------------------------------------------------------------------------------------------------------------ //
	 function before_process() {
      /*global $customer_id, $order, $order_totals, $sendto, $billto, $languages_id, $payment, $currencies, $cart, $cart_DirecPay_ID;
      global $$payment;

      $order_id = substr($cart_DirecPay_ID, strpos($cart_DirecPay_ID, '-')+1);

      $check_query = tep_db_query("select orders_status from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
      if (tep_db_num_rows($check_query)) {
        $check = tep_db_fetch_array($check_query);

        if ($check['orders_status'] == MODULE_PAYMENT_DIRECPAY_PREPARE_ORDER_STATUS_ID) {
          $sql_data_array = array('orders_id' => $order_id,
                                  'orders_status_id' => MODULE_PAYMENT_DIRECPAY_PREPARE_ORDER_STATUS_ID,
                                  'date_added' => 'now()',
                                  'customer_notified' => '0',
                                  'comments' => '');

          tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
      }

      tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (MODULE_PAYMENT_DIRECPAY_PREPARE_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_DIRECPAY_PREPARE_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID) . "', last_modified = now() where orders_id = '" . (int)$order_id . "'");

      $sql_data_array = array('orders_id' => $order_id,
                              'orders_status_id' => (MODULE_PAYMENT_DIRECPAY_PREPARE_ORDER_STATUS_ID > 0 ? (int)MODULE_PAYMENT_DIRECPAY_PREPARE_ORDER_STATUS_ID : (int)DEFAULT_ORDERS_STATUS_ID),
                              'date_added' => 'now()',
                              'customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
                              'comments' => $order->info['comments']);

      tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

// initialized for the email confirmation
      $products_ordered = '';
      $subtotal = 0;
      $total_tax = 0;

      for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
// Stock Update - Joao Correia
        if (STOCK_LIMITED == 'true') {
          if (DOWNLOAD_ENABLED == 'true') {
            $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                FROM " . TABLE_PRODUCTS . " p
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                ON p.products_id=pa.products_id
                                LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                ON pa.products_attributes_id=pad.products_attributes_id
                                WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
// Will work with only one option for downloadable products
// otherwise, we have to build the query dynamically with a loop
            $products_attributes = $order->products[$i]['attributes'];
            if (is_array($products_attributes)) {
              $stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
            }
            $stock_query = tep_db_query($stock_query_raw);
          } else {
            $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
          }
          if (tep_db_num_rows($stock_query) > 0) {
            $stock_values = tep_db_fetch_array($stock_query);
// do not decrement quantities if products_attributes_filename exists
            if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
              $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
            } else {
              $stock_left = $stock_values['products_quantity'];
            }
            tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
            if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
              tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
            }
          }
        }

// Update products_ordered (for bestsellers list)
        tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

//------insert customer choosen option to order--------
        $attributes_exist = '0';
        $products_ordered_attributes = '';
        if (isset($order->products[$i]['attributes'])) {
          $attributes_exist = '1';
          for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
            if (DOWNLOAD_ENABLED == 'true') {
              $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                   from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                   left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                   on pa.products_attributes_id=pad.products_attributes_id
                                   where pa.products_id = '" . $order->products[$i]['id'] . "'
                                   and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                                   and pa.options_id = popt.products_options_id
                                   and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                                   and pa.options_values_id = poval.products_options_values_id
                                   and popt.language_id = '" . $languages_id . "'
                                   and poval.language_id = '" . $languages_id . "'";
              $attributes = tep_db_query($attributes_query);
            } else {
              $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
            }
            $attributes_values = tep_db_fetch_array($attributes);

            $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
          }
        }
//------insert customer choosen option eof ----
        $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
        $total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
        $total_cost += $total_products_price;

        $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
      }

// lets start with the email confirmation
      $email_order = STORE_NAME . "\n" .
                     EMAIL_SEPARATOR . "\n" .
                     EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_id . "\n" .
                     EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $order_id, 'SSL', false) . "\n" .
                     EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
      if ($order->info['comments']) {
        $email_order .= tep_db_output($order->info['comments']) . "\n\n";
      }
      $email_order .= EMAIL_TEXT_PRODUCTS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      $products_ordered .
                      EMAIL_SEPARATOR . "\n";

      for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
        $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
      }

      if ($order->content_type != 'virtual') {
        $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        tep_address_label($customer_id, $sendto, 0, '', "\n") . "\n";
      }

      $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                      EMAIL_SEPARATOR . "\n" .
                      tep_address_label($customer_id, $billto, 0, '', "\n") . "\n\n";

      if (is_object($$payment)) {
        $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" .
                        EMAIL_SEPARATOR . "\n";
        $payment_class = $$payment;
        $email_order .= $payment_class->title . "\n\n";
        if ($payment_class->email_footer) {
          $email_order .= $payment_class->email_footer . "\n\n";
        }
      }

      tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

// send emails to other people
      if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
        tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }

// load the after_process function from the payment modules
      $this->after_process();

      $cart->reset(true);

// unregister session variables used during checkout
      tep_session_unregister('sendto');
      tep_session_unregister('billto');
      tep_session_unregister('shipping');
      tep_session_unregister('payment');
      tep_session_unregister('comments');

      tep_session_unregister('cart_DirecPay_ID');

      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));*/
	  return false;
    }

	// ------------------------------------------------------------------------------------------------------------------ //
   function after_process() {
      return false;
    }

	// ------------------------------------------------------------------------------------------------------------------ //
    function output_error() {
      return false;
    }
	// ------------------------------------------------------------------------------------------------------------------ //

	function install() {
      $check_query = $this->db->query("select orders_status_id from r_orders_status where orders_status_name = 'DirecPay[Transactions]' limit 1");
		//exit;
      if ($check_query->rowCount() < 1) {
        $status_query = $this->db->query("select max(orders_status_id) as status_id from r_orders_status");
        $status = $status_query->fetch();

        $status_id = $status['status_id']+1;

		$act=new Model_Adminaction();
		$languages=$act->getLanguages();
        //$languages = tep_get_languages();
	//	echo "<pre>";
	//	print_r($languages);
	//	echo "</pre>";
		//exit;
        foreach ($languages as $lang) {
	         $this->db->query("insert into r_orders_status (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $lang['languages_id'] . "', 'DirecPay[Transactions]')");
        }

      } else {
        $check = $check_query->fetch();

        $status_id = $check['orders_status_id'];
      }

	///

	$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_PAYMENT_DIRECPAY_TEXT_TITLE','configuration_value'=>'Direc Pay','configuration_description'=>'enter title to display in front end','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

	    $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Add Merchant ID', 'MODULE_PAYMENT_DIRECPAY_MERCHANT_ID', '0', 'Merchant ID', '6', '0', now())");

      ////////
      $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable DirecPay', 'MODULE_PAYMENT_DIRECPAY_STATUS', 'False', 'Do you want to accept DirecPay?', '6', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      /////

	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_DIRECPAY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
	  //////

	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_DIRECPAY_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      ///////

	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Preparing Order Status', 'MODULE_PAYMENT_DIRECPAY_PREPARE_ORDER_STATUS_ID', '" . $status_id . "', 'Set the status of prepared orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
	  ///

	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set DirecPay Acknowledged Order Status', 'MODULE_PAYMENT_DIRECPAY_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      ///////

	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Choose Platform', 'MODULE_PAYMENT_DIRECPAY_GATEWAY_SERVER', 'Live', 'Use the testing  or live gateway server for transactions?', '6', '6', 'tep_cfg_select_option(array(\'Live\', \'Test\'), ', now())");


    }
	// ------------------------------------------------------------------------------------------------------------------ //
	function remove() {
      $this->db->query("delete from r_configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

	// ------------------------------------------------------------------------------------------------------------------ //
	function keys() {
      return array('MODULE_PAYMENT_DIRECPAY_TEXT_TITLE','MODULE_PAYMENT_DIRECPAY_STATUS', 'MODULE_PAYMENT_DIRECPAY_SORT_ORDER', 'MODULE_PAYMENT_DIRECPAY_ZONE', 'MODULE_PAYMENT_DIRECPAY_PREPARE_ORDER_STATUS_ID', 'MODULE_PAYMENT_DIRECPAY_ORDER_STATUS_ID', 'MODULE_PAYMENT_DIRECPAY_GATEWAY_SERVER','MODULE_PAYMENT_DIRECPAY_MERCHANT_ID');
    }
	// ------------------------------------------------------------------------------------------------------------------ //

	/*function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies, $currency;

      if (empty($currency_code) || !$this->is_set($currency_code)) {
        $currency_code = $currency;
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }*/

	 function format_raw($number, $currency_code = '', $currency_value = '') {
      //global $currencies, $currency;
	  $currencies=new Model_currencies();
	  $currency=$_SESSION['Curr']['currency'];

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency]['decimal_places']), $currencies->currencies[$currency]['decimal_places'], '.', '');
    }

	// ------------------------------------------------------------------------------------------------------------------ //
	function check() {
		  if (!isset($this->_check)) {
			$check_query = tep_db_query("select configuration_value from r_configuration where configuration_key = 'MODULE_PAYMENT_DIRECPAY_STATUS'");
			$this->_check = tep_db_num_rows($check_query);
		  }
		  return $this->_check;
    }

	// ------------------------------------------------------------------------------------------------------------------ //

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