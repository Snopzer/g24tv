<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class Model_Payment_Cod {
    var $code, $title, $description, $enabled;

// class constructor
    function Model_Payment_Cod() {
      global $order;

      $this->code = 'Cod';
      $this->title = @constant('MODULE_PAYMENT_COD_TEXT_TITLE')==""?"Cash On Delivery":MODULE_PAYMENT_COD_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_COD_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_COD_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_COD_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_COD_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_COD_ORDER_STATUS_ID;
      }else
	  {
		$this->order_status=ORDER_STATUS_ID;
	  }

      if (is_object($order)) $this->update_status();

		$this->db = Zend_Db_Table::getDefaultAdapter();
	}

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_COD_ZONE > 0) ) {
        $check_flag = false;

        //$check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_COD_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
		$check_query = $this->db->query("select zone_id from r_zones_to_geo_zones where geo_zone_id = '" . MODULE_PAYMENT_COD_ZONE . "' and zone_country_id = '" . $_SESSION['shipping_country_id'] . "' order by zone_id");
        /*while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $_SESSION['shipping_zone_id']) {
            $check_flag = true;
            break;
          }
        }*/
		$check_query_row=$check_query->fetchAll();
		foreach($check_query_row as $check )
		{
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $_SESSION['shipping_zone_id']) {
            $check_flag = true;
            break;
          }
        }

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }

// disable the module if the order only contains virtual products
      if ($this->enabled == true) {  //if product is downlaodable then this should be disable
        if ($order->content_type == 'virtual') {
          $this->enabled = false;
        }
      }
    }//it returns enable value true or false

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
        $check_query = $this->db->query("select configuration_value from r_configuration where configuration_key = 'MODULE_PAYMENT_COD_STATUS'");
		//$check_query_rows=$check_query->fetchAll();
		//$this->_check = tep_db_num_rows($check_query);
		$this->_check =$check_query->rowCount();
      }
      return $this->_check;
    }

       function install()
		{
			$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_PAYMENT_COD_TEXT_TITLE','configuration_value'=>'Cash On Delivery','configuration_description'=>'enter title to display in front end','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

			$this->db->insert('r_configuration',array('configuration_title'=>'Enable Cash On Delivery Module','configuration_key'=>'MODULE_PAYMENT_COD_STATUS','configuration_value'=>'True','configuration_description'=>'Do you want to accept Cash On Delevery payments?','configuration_group_id'=>'6','sort_order'=>'1','set_function'=>'tep_cfg_select_option(array(\'True\', \'False\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

			$this->db->insert('r_configuration',array('configuration_title'=>'Payment Zone','configuration_key'=>'MODULE_PAYMENT_COD_ZONE','configuration_value'=>'0','configuration_description'=>'If a zone is selected, only enable this payment method for that zone.','configuration_group_id'=>'6','sort_order'=>'2','use_function'=>'tep_get_zone_class_title','set_function'=>'tep_cfg_pull_down_zone_classes(','date_added'=>new Zend_Db_Expr('NOW()')));

			$this->db->insert('r_configuration',array('configuration_title'=>'Sort order of display.','configuration_key'=>'MODULE_PAYMENT_COD_SORT_ORDER','configuration_value'=>'0','configuration_description'=>'Sort order of display. Lowest is displayed first.','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

			$this->db->insert('r_configuration',array('configuration_title'=>'Set Order Status','configuration_key'=>'MODULE_PAYMENT_COD_ORDER_STATUS_ID','configuration_value'=>'0','configuration_description'=>'Set the status of orders made with this payment module to this value','configuration_group_id'=>'6','sort_order'=>'0','use_function'=>'tep_get_order_status_name','set_function'=>'tep_cfg_pull_down_order_statuses(','date_added'=>new Zend_Db_Expr('NOW()')));
		}

    function remove() {
	$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_COD_TEXT_TITLE','MODULE_PAYMENT_COD_STATUS', 'MODULE_PAYMENT_COD_ZONE', 'MODULE_PAYMENT_COD_ORDER_STATUS_ID', 'MODULE_PAYMENT_COD_SORT_ORDER');
    }
  }
?>
