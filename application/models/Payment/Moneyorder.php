<?php

  //class Model_Payment_Moneyorder {
	  class Model_Payment_Moneyorder {
    var $code, $title, $description, $enabled;

// class constructor
    function Model_Payment_Moneyorder() {
      global $order;

      $this->code = 'Moneyorder';
      $this->title = @constant('MODULE_PAYMENT_MONEYORDER_TEXT_TITLE')==""?"Money Order":MODULE_PAYMENT_MONEYORDER_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_MONEYORDER_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_MONEYORDER_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();

      $this->email_footer = MODULE_PAYMENT_MONEYORDER_TEXT_EMAIL_FOOTER;

	  	$this->db = Zend_Db_Table::getDefaultAdapter();

    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_MONEYORDER_ZONE > 0) ) {
         $check_flag = false;

/*        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_MONEYORDER_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");*/
$check_query = mysql_query("select zone_id from r_zones_to_geo_zones  where geo_zone_id = '" . MODULE_PAYMENT_MONEYORDER_ZONE . "' and zone_country_id = '223' order by zone_id");
        while ($check = mysql_fetch_array($check_query)) {
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
      return array('title' => MODULE_PAYMENT_MONEYORDER_TEXT_DESCRIPTION);
    }

    function process_button() {
      return false;
    }

    function before_process() {
      return false;
    }

    function after_process() {
      return false;
    }

    function get_error() {
      return false;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_MONEYORDER_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {


   		$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_PAYMENT_MONEYORDER_TEXT_TITLE','configuration_value'=>'Money Order','configuration_description'=>'enter title to display in front end','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Enable Check/Money Order 
		Module','configuration_key'=>'MODULE_PAYMENT_MONEYORDER_STATUS','configuration_value'=>'True','configuration_description'=>'Do you want to accept Check/Money Order payments?','configuration_group_id'=>'6','sort_order'=>'1','set_function'=>'tep_cfg_select_option(array(\'True\', \'False\'),','date_added'=>new Zend_Db_Expr('NOW()')));

	$this->db->insert('r_configuration',array('configuration_title'=>'Make Payable to:','configuration_key'=>'MODULE_PAYMENT_MONEYORDER_PAYTO','configuration_value'=>'','configuration_description'=>'Who should payments be made payable to?','configuration_group_id'=>'6','sort_order'=>'1','date_added'=>new Zend_Db_Expr('NOW()')));

	  $this->db->insert('r_configuration',array('configuration_title'=>'Sort order of display.','configuration_key'=>'MODULE_PAYMENT_MONEYORDER_SORT_ORDER','configuration_value'=>'0','configuration_description'=>'Sort order of display. Lowest is displayed first.','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));


	  $this->db->insert('r_configuration',array('configuration_title'=>'Payment Zone','configuration_key'=>'MODULE_PAYMENT_MONEYORDER_ZONE','configuration_value'=>'0','configuration_description'=>'If a zone is selected, only enable this payment method for that zone.','configuration_group_id'=>'6','sort_order'=>'2','use_function'=>'tep_get_zone_class_title','set_function'=>'tep_cfg_pull_down_zone_classes(','date_added'=>new Zend_Db_Expr('NOW()')));

	  $this->db->insert('r_configuration',array('configuration_title'=>'Set Order Status','configuration_key'=>'MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID','configuration_value'=>'0','configuration_description'=>'Set the status of orders made with this payment module to this value','configuration_group_id'=>'6','sort_order'=>'2','use_function'=>'tep_get_order_status_name','set_function'=>'tep_cfg_pull_down_order_statuses(','date_added'=>new Zend_Db_Expr('NOW()')));
     }

    function remove() {
     		$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");

    }

    function keys() {
      return array('MODULE_PAYMENT_MONEYORDER_TEXT_TITLE','MODULE_PAYMENT_MONEYORDER_STATUS', 'MODULE_PAYMENT_MONEYORDER_ZONE', 'MODULE_PAYMENT_MONEYORDER_ORDER_STATUS_ID', 'MODULE_PAYMENT_MONEYORDER_SORT_ORDER', 'MODULE_PAYMENT_MONEYORDER_PAYTO');
    }
  }
?>
