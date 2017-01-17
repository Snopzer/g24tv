<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class Model_OrderTotal_OtSubtotal {
    var $title, $output;

    function Model_OrderTotal_OtSubtotal() {
      $this->code = 'ot_subtotal';
      $this->title = @constant('MODULE_ORDER_TOTAL_SUBTOTAL_TITLE')==""?"Sub-Total ":MODULE_ORDER_TOTAL_SUBTOTAL_TITLE;//MODULE_ORDER_TOTAL_SUBTOTAL_TITLE;
      $this->description = MODULE_ORDER_TOTAL_SUBTOTAL_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_SUBTOTAL_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER;

      $this->output = array();

	  $this->db = Zend_Db_Table::getDefaultAdapter();
    }

    function process() {
      global $order, $currencies;

      $this->output[] = array('title' => $this->title . ':',
                              'text' => $currencies->format($order->info['subtotal'], true, $order->info['currency'], $order->info['currency_value']),
                              'value' => $order->info['subtotal']);
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_SUBTOTAL_TITLE','MODULE_ORDER_TOTAL_SUBTOTAL_STATUS', 'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER');
    }

    function install() {
		
		$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_ORDER_TOTAL_SUBTOTAL_TITLE','configuration_value'=>'Sub-Total','configuration_description'=>'enter title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'1','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Display Sub-Total','configuration_key'=>'MODULE_ORDER_TOTAL_SUBTOTAL_STATUS','configuration_value'=>'true','configuration_description'=>'Do you want to display the order sub-total cost?','configuration_group_id'=>'6','sort_order'=>'1','set_function'=>'tep_cfg_select_option(array(\'true\', \'false\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Sort Order','configuration_key'=>'MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER','configuration_value'=>'1','configuration_description'=>'Sort order of display.','configuration_group_id'=>'6','sort_order'=>'2','date_added'=>new Zend_Db_Expr('NOW()')));
    }

    function remove() {
	  	  	$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

	public function getTotal(&$total_data, &$total, &$taxes) {
		$currObj=new Model_currencies();
		$cartObj=new Model_Cart();
		$sub_total = $cartObj->getSubTotal();

		if (isset($_SESSION['vouchers']) && $_SESSION['vouchers']) {
			foreach ($_SESSION['vouchers'] as $voucher) {
				$sub_total += $voucher['amount'];
			}
		}

		$total_data[] = array(
			'code'       => 'subtotal',
			'title'      => @constant('MODULE_ORDER_TOTAL_SUBTOTAL_TITLE'),
			'text'       => $currObj->format($sub_total),
			'value'      => $sub_total,
			'sort_order' => MODULE_ORDER_TOTAL_SUBTOTAL_SORT_ORDER
		);

		$total += $sub_total;
	}
  }
?>
