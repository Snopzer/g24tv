<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class Model_Payment_Freecheckout {
    var $code, $title, $description, $enabled;

// class constructor
    function Model_Payment_Freecheckout() {
      global $order;

      $this->code = 'Freecheckout';
      $this->title = @constant('MODULE_PAYMENT_FREECHECKOUT_TEXT_TITLE')==""?"Free Checkout":MODULE_PAYMENT_FREECHECKOUT_TEXT_TITLE;
      $this->description = MODULE_PAYMENT_FREECHECKOUT_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_FREECHECKOUT_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_FREECHECKOUT_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_FREECHECKOUT_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_FREECHECKOUT_ORDER_STATUS_ID;
      }else
	  {
		$this->order_status=ORDER_STATUS_ID;
	  }

      if (is_object($order)) $this->update_status();

		$this->db = Zend_Db_Table::getDefaultAdapter();

		
	}

// class methods
    function update_status() {
      return;
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
        $check_query = $this->db->query("select configuration_value from r_configuration where configuration_key = 'MODULE_PAYMENT_FREECHECKOUT_STATUS'");
		//$check_query_rows=$check_query->fetchAll();
		//$this->_check = tep_db_num_rows($check_query);
		$this->_check =$check_query->rowCount();
      }
      return $this->_check;
    }

       function install()
		{
		   		$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_PAYMENT_FREECHECKOUT_TEXT_TITLE','configuration_value'=>'Free Checkout','configuration_description'=>'enter title to display in front end','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

			$this->db->insert('r_configuration',array('configuration_title'=>'Enable Free Checkout  Module','configuration_key'=>'MODULE_PAYMENT_FREECHECKOUT_STATUS','configuration_value'=>'True','configuration_description'=>'Do you want to allow freecheckout?','configuration_group_id'=>'6','sort_order'=>'1','set_function'=>'tep_cfg_select_option(array(\'True\', \'False\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

			$this->db->insert('r_configuration',array('configuration_title'=>'Sort order of display.','configuration_key'=>'MODULE_PAYMENT_FREECHECKOUT_SORT_ORDER','configuration_value'=>'0','configuration_description'=>'Sort order of display. Lowest is displayed first.','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

			$this->db->insert('r_configuration',array('configuration_title'=>'Set Order Status','configuration_key'=>'MODULE_PAYMENT_FREECHECKOUT_ORDER_STATUS_ID','configuration_value'=>'0','configuration_description'=>'Set the status of orders made with this payment module to this value','configuration_group_id'=>'6','sort_order'=>'0','use_function'=>'tep_get_order_status_name','set_function'=>'tep_cfg_pull_down_order_statuses(','date_added'=>new Zend_Db_Expr('NOW()')));
		}

    function remove() {
	$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_FREECHECKOUT_TEXT_TITLE','MODULE_PAYMENT_FREECHECKOUT_STATUS', 'MODULE_PAYMENT_FREECHECKOUT_ORDER_STATUS_ID', 'MODULE_PAYMENT_FREECHECKOUT_SORT_ORDER');
    }
  }
?>
