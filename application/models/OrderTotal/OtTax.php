<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class Model_OrderTotal_OtTax {
    var $title, $output;

    function Model_OrderTotal_OtTax() {
      $this->code = 'ot_tax';
      $this->title = @constant('MODULE_ORDER_TOTAL_TAX_TITLE')==""?"Tax":MODULE_ORDER_TOTAL_TAX_TITLE;//MODULE_ORDER_TOTAL_TAX_TITLE;
      $this->description = MODULE_ORDER_TOTAL_TAX_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_TAX_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_TAX_SORT_ORDER;

      $this->output = array();

   	  $this->db = Zend_Db_Table::getDefaultAdapter();
    }

    function process() {
      global $order, $currencies;

      reset($order->info['tax_groups']);
      while (list($key, $value) = each($order->info['tax_groups'])) {
        if ($value > 0) {
          $this->output[] = array('title' => $key . ':',
                                  'text' => $currencies->format($value, true, $order->info['currency'], $order->info['currency_value']),
                                  'value' => $value);
        }
      }
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_TAX_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_TAX_TITLE','MODULE_ORDER_TOTAL_TAX_STATUS', 'MODULE_ORDER_TOTAL_TAX_SORT_ORDER');
    }

    function install() {

	$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_ORDER_TOTAL_TAX_TITLE','configuration_value'=>'Tax','configuration_description'=>'enter title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'1','date_added'=>new Zend_Db_Expr('NOW()')));

	$this->db->insert('r_configuration',array('configuration_title'=>'Display Tax','configuration_key'=>'MODULE_ORDER_TOTAL_TAX_STATUS','configuration_value'=>'true','configuration_description'=>'Do you want to display the order tax value?','configuration_group_id'=>'6','sort_order'=>'1','set_function'=>'tep_cfg_select_option(array(\'true\', \'false\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

      $this->db->insert('r_configuration',array('configuration_title'=>'Sort Order','configuration_key'=>'MODULE_ORDER_TOTAL_TAX_SORT_ORDER','configuration_value'=>'2','configuration_description'=>'Sort order of display.','configuration_group_id'=>'6','sort_order'=>'2','date_added'=>new Zend_Db_Expr('NOW()')));

    }

    function remove() {
 	  	  	  	$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");

    }

	public function getTotal(&$total_data, &$total, &$taxes) {
		$taxObj=new Model_Tax();
		$currObj=new Model_currencies();
 		if($taxes=="")
		{
			$taxes=array();
		}

		foreach ($taxes as $key => $value) {
			if ($value > 0) {
				$tax_classes = $taxObj->getDescription($key);

				foreach ($tax_classes as $tax_class) {
					$rate = $taxObj->getRate($key);

					$tax = $value * ($tax_class['rate'] / $rate);

					$total_data[] = array(
						'code'       => 'tax',
						'title'      => $tax_class['description'] . ':',
						'text'       => $currObj->format($tax),
						'value'      => $tax,
						'sort_order' => constant('MODULE_ORDER_TOTAL_TAX_SORT_ORDER')
					);

					$total += $tax;
				}
			}
		}
	}
  }
?>
