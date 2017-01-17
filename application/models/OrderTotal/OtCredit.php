<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class Model_OrderTotal_OtCredit {
    var $title, $output,$tr;

    function Model_OrderTotal_OtCredit() {
      $this->code = 'ot_credit';
      $this->title = @constant('MODULE_ORDER_TOTAL_CREDIT_TITLE')==""?"Credit":MODULE_ORDER_TOTAL_CREDIT_TITLE;
      $this->description = MODULE_ORDER_TOTAL_CREDIT_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_CREDIT_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_CREDIT_SORT_ORDER;

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
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_CREDIT_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_CREDIT_TITLE','MODULE_ORDER_TOTAL_CREDIT_STATUS', 'MODULE_ORDER_TOTAL_CREDIT_SORT_ORDER');
    }

    function install() {
		
			$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_ORDER_TOTAL_CREDIT_TITLE','configuration_value'=>'Credit','configuration_description'=>'enter title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'1','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Display Credit','configuration_key'=>'MODULE_ORDER_TOTAL_CREDIT_STATUS','configuration_value'=>'true','configuration_description'=>'Do you want to display the credit value?','configuration_group_id'=>'6','sort_order'=>'1','set_function'=>'tep_cfg_select_option(array(\'true\', \'false\'), ','date_added'=>new Zend_Db_Expr('NOW()')));


		$this->db->insert('r_configuration',array('configuration_title'=>'Sort Order','configuration_key'=>'MODULE_ORDER_TOTAL_CREDIT_SORT_ORDER','configuration_value'=>'4','configuration_description'=>'Sort order of display.','configuration_group_id'=>'6','sort_order'=>'2','date_added'=>new Zend_Db_Expr('NOW()')));

    }

    function remove() {
	  	$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");

    }

 

	public function getTotal(&$total_data, &$total, &$taxes) {
		if (MODULE_ORDER_TOTAL_CREDIT_STATUS) {
			$this->currency=new Model_currencies();
			$this->customer=new Model_Customer();
			$balance = $this->customer->getBalance();

			if ((float)$balance) {
				if ($balance > $total) {
					$credit = $total;
				} else {
					$credit = $balance;
				}

				$lang=new Model_Languages('');
				$lC=new Model_Cache();
				$this->tr=$lC->getLangCache($lang);

				if ($credit > 0) {
					$total_data[] = array(
						'code'       => 'credit',
						'title'      => @constant('MODULE_ORDER_TOTAL_CREDIT_TITLE'),
						'text'       => $this->currency->format(-$credit),
						'value'      => -$credit,
						'sort_order' => MODULE_ORDER_TOTAL_CREDIT_SORT_ORDER
					);

					$total -= $credit;
				}
			}
		}
	}

	public function confirm($order_info, $order_total) {
	 		$lang=new Model_Languages('');
		$lC=new Model_Cache();
		$this->tr=$lC->getLangCache($lang);

		if ($order_info['customer_id']) {
			$this->db->query("INSERT INTO r_customer_transaction SET customer_id = '" . (int)$order_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . stripslashes(sprintf($this->tr->translate('text_order_id_total_order'), (int)$order_info['order_id'])) . "', amount = '" . (float)$order_total['value'] . "', date_added = new Zend_Db_Expr('NOW()')");
		}
	}
  }
?>
