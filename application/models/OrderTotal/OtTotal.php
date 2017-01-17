<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class Model_OrderTotal_OtTotal {
    var $title, $output;

    function Model_OrderTotal_OtTotal() {
      $this->code = 'ot_total';
      $this->title = @constant('MODULE_ORDER_TOTAL_TOTAL_TITLE')==""?"Total":MODULE_ORDER_TOTAL_TOTAL_TITLE;//MODULE_ORDER_TOTAL_TOTAL_TITLE;
      $this->description = MODULE_ORDER_TOTAL_TOTAL_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_TOTAL_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER;

      $this->output = array();
 	  $this->db = Zend_Db_Table::getDefaultAdapter();
    }

    function process() {
      global $order, $currencies;

      $this->output[] = array('title' => $this->title . ':',
                              'text' => '<strong>' . $currencies->format($order->info['total'], true, $order->info['currency'], $order->info['currency_value']) . '</strong>',
                              'value' => $order->info['total']);
	  print_r($this->output);
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_TOTAL_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_TOTAL_TITLE','MODULE_ORDER_TOTAL_TOTAL_STATUS', 'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER');
    }

    function install() {
		
				
			$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_ORDER_TOTAL_TOTAL_TITLE','configuration_value'=>'Total','configuration_description'=>'enter title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'1','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Display Total','configuration_key'=>'MODULE_ORDER_TOTAL_TOTAL_STATUS','configuration_value'=>'true','configuration_description'=>'Do you want to display the total order value?','configuration_group_id'=>'6','sort_order'=>'1','set_function'=>'tep_cfg_select_option(array(\'true\', \'false\'), ','date_added'=>new Zend_Db_Expr('NOW()')));


		$this->db->insert('r_configuration',array('configuration_title'=>'Sort Order','configuration_key'=>'MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER','configuration_value'=>'6','configuration_description'=>'Sort order of display.','configuration_group_id'=>'6','sort_order'=>'2','date_added'=>new Zend_Db_Expr('NOW()')));

    }

    function remove() {
	  	$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");

    }

	public function getTotal(&$total_data, &$total, &$taxes) {
		$currObj=new Model_currencies();
		$total_data[] = array(
			'code'       => 'total',
			'title'      => @constant('MODULE_ORDER_TOTAL_TOTAL_TITLE'),
			'text'       => $currObj->format(max(0, $total)),
			'value'      => max(0, $total),
			'sort_order' => constant('MODULE_ORDER_TOTAL_TOTAL_SORT_ORDER')
		);
	}
  }
?>
