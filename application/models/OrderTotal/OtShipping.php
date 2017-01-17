<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  Released under the GNU General Public License
*/

  class Model_OrderTotal_OtShipping {
    var $title, $output;

    function Model_OrderTotal_OtShipping() {
      $this->code = 'ot_shipping';
      $this->title = @constant('MODULE_ORDER_TOTAL_SHIPPING_TITLE')==""?"Shipping : ":MODULE_ORDER_TOTAL_SHIPPING_TITLE;//MODULE_ORDER_TOTAL_SHIPPING_TITLE;
      $this->description = MODULE_ORDER_TOTAL_SHIPPING_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_SHIPPING_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER;

      $this->output = array();
	  $this->db = Zend_Db_Table::getDefaultAdapter();
    }

    function process() {
      global $order, $currencies;

      if (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') {
        switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
          case 'national':
            if ($order->delivery['country_id'] == STORE_COUNTRY) $pass = true; break;
          case 'international':
            if ($order->delivery['country_id'] != STORE_COUNTRY) $pass = true; break;
          case 'both':
            $pass = true; break;
          default:
            $pass = false; break;
        }

        if ( ($pass == true) && ( ($order->info['total'] - $order->info['shipping_cost']) >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
          $order->info['shipping_method'] = FREE_SHIPPING_TITLE;
          $order->info['total'] -= $order->info['shipping_cost'];
          $order->info['shipping_cost'] = 0;
        }
      }

      $module = substr($GLOBALS['shipping']['id'], 0, strpos($GLOBALS['shipping']['id'], '_'));

      if (tep_not_null($order->info['shipping_method'])) {
        if ($GLOBALS[$module]->tax_class > 0) {
          $shipping_tax = tep_get_tax_rate($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
          $shipping_tax_description = tep_get_tax_description($GLOBALS[$module]->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);

          $order->info['tax'] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);
          $order->info['tax_groups']["$shipping_tax_description"] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);
          $order->info['total'] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);

          if (DISPLAY_PRICE_WITH_TAX == 'true') $order->info['shipping_cost'] += tep_calculate_tax($order->info['shipping_cost'], $shipping_tax);
        }

        $this->output[] = array('title' => $order->info['shipping_method'] . ':',
                                'text' => $currencies->format($order->info['shipping_cost'], true, $order->info['currency'], $order->info['currency_value']),
                                'value' => $order->info['shipping_cost']);
      }
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_SHIPPING_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_SHIPPING_TITLE','MODULE_ORDER_TOTAL_SHIPPING_STATUS', 'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING', 'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER', 'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION');
    }

    function install() {

	$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_ORDER_TOTAL_SHIPPING_TITLE','configuration_value'=>'Shipping : ','configuration_description'=>'enter title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'1','date_added'=>new Zend_Db_Expr('NOW()')));

	$this->db->insert('r_configuration',array('configuration_title'=>'Display Shipping','configuration_key'=>'MODULE_ORDER_TOTAL_SHIPPING_STATUS','configuration_value'=>'true','configuration_description'=>'Do you want to display the order shipping cost?','configuration_group_id'=>'6','sort_order'=>'1','set_function'=>'tep_cfg_select_option(array(\'true\', \'false\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

   $this->db->insert('r_configuration',array('configuration_title'=>'Sort Order','configuration_key'=>'MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER','configuration_value'=>'2','configuration_description'=>'Sort order of display.','configuration_group_id'=>'6','sort_order'=>'2','date_added'=>new Zend_Db_Expr('NOW()')));

$this->db->insert('r_configuration',array('configuration_title'=>'Allow Free Shipping','configuration_key'=>'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING','configuration_value'=>'false','configuration_description'=>'Do you want to allow free shipping?','configuration_group_id'=>'6','sort_order'=>'3','set_function'=>'tep_cfg_select_option(array(\'true\', \'false\'), ','date_added'=>new Zend_Db_Expr('NOW()')));


$this->db->insert('r_configuration',array('configuration_title'=>'Free Shipping For Orders Over','configuration_key'=>'MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER','configuration_value'=>'50','configuration_description'=>'Provide free shipping for orders over the set amount.','configuration_group_id'=>'6','sort_order'=>'4','use_function'=>'currencies->format','date_added'=>new Zend_Db_Expr('NOW()')));


$this->db->insert('r_configuration',array('configuration_title'=>'Provide Free Shipping For Orders Made','configuration_key'=>'MODULE_ORDER_TOTAL_SHIPPING_DESTINATION','configuration_value'=>'national','configuration_description'=>'Provide free shipping for orders sent to the set destination.','configuration_group_id'=>'6','sort_order'=>'5','set_function'=>'tep_cfg_select_option(array(\'national\', \'international\', \'both\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

    }

    function remove() {
 	  	  	  	$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }


	public function getTotal(&$total_data, &$total, &$taxes) {
		$currObj=new Model_currencies();
		$cart=new Model_Cart();
		$tax=new Model_Tax();
		if ($cart->hasShipping() && isset($_SESSION['shipping_method'])) {
			$total_data[] = array(
				'code'       => 'shipping',
        		'title'      => @constant('MODULE_ORDER_TOTAL_SHIPPING_TITLE'),//$_SESSION['shipping_method']['title'] . ':',
        		'text'       => $currObj->format($_SESSION['shipping_method']['cost']),
        		'value'      => $_SESSION['shipping_method']['cost'],
				'sort_order' =>MODULE_ORDER_TOTAL_SHIPPING_SORT_ORDER);

			if ($_SESSION['shipping_method']['tax_class_id']) {
				if (!isset($taxes[$_SESSION['shipping_method']['tax_class_id']])) {
					$taxes[$_SESSION['shipping_method']['tax_class_id']] = $_SESSION['shipping_method']['cost'] / 100 * $tax->getRate($_SESSION['shipping_method']['tax_class_id']);
				} else {
					$taxes[$_SESSION['shipping_method']['tax_class_id']] += $_SESSION['shipping_method']['cost'] / 100 * $tax->getRate($_SESSION['shipping_method']['tax_class_id']);
				}
			}

			$total += $_SESSION['shipping_method']['cost'];
		}
	}
  }
?>
