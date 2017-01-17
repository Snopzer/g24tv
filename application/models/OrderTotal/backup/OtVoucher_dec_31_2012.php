<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class Model_OrderTotal_OtVoucher {
    var $title, $output;

    function Model_OrderTotal_OtVoucher() {
      $this->code = 'ot_voucher';
      $this->title = MODULE_ORDER_TOTAL_VOUCHER_TITLE;
      $this->description = MODULE_ORDER_TOTAL_VOUCHER_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_VOUCHER_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_VOUCHER_SORT_ORDER;

      $this->output = array();

 	  $this->db = Zend_Db_Table::getDefaultAdapter();
     }

    function process() {
      global $order, $currencies;

      $this->output[] = array('title' => $this->title . ':',
                              'text' => '<strong>' . $currencies->format($order->info['total'], true, $order->info['currency'], $order->info['currency_value']) . '</strong>',
                              'value' => $order->info['total']);
	 // print_r($this->output);
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_VOUCHER_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_VOUCHER_STATUS', 'MODULE_ORDER_TOTAL_VOUCHER_SORT_ORDER');
    }

    function install() {

		$this->db->insert('r_configuration',array('configuration_title'=>'Display Voucher','configuration_key'=>'MODULE_ORDER_TOTAL_VOUCHER_STATUS','configuration_value'=>'true','configuration_description'=>'Do you want to display the voucher value?','configuration_group_id'=>'6','sort_order'=>'1','set_function'=>'tep_cfg_select_option(array(\'true\', \'false\'), ','date_added'=>new Zend_Db_Expr('NOW()')));


		$this->db->insert('r_configuration',array('configuration_title'=>'Sort Order','configuration_key'=>'MODULE_ORDER_TOTAL_VOUCHER_SORT_ORDER','configuration_value'=>'4','configuration_description'=>'Sort order of display.','configuration_group_id'=>'6','sort_order'=>'2','date_added'=>new Zend_Db_Expr('NOW()')));

    }

    function remove() {
	  	$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");

    }

	public function getTotal(&$total_data, &$total, &$taxes) {
		$lang=new Model_Languages();
		$lC=new Model_Cache();
		$tr=$lC->getLangCache($lang);
		$currObj=new Model_Currencies();
		if (isset($_SESSION['voucher'])) {
			$vouObj=new Model_CheckoutVoucher();
			$voucher_info = $vouObj->getVoucher($_SESSION['voucher']);

			/*if ($voucher_info) {
				if ($voucher_info['amount'] > $total) {
					$amount = $total;
				} else {
					$amount = $voucher_info['amount'];
				}

				$total_data[] = array(
					'code'       => 'voucher',
        			'title'      => sprintf($tr->translate('text_voucher_total_voucher'), $_SESSION['voucher']),
	    			'text'       => $currObj->format(-$amount),
        			'value'      => -$amount,
					'sort_order' =>constant('MODULE_ORDER_TOTAL_VOUCHER_SORT_ORDER')
      			);

				$total -= $amount;*/
				//8
				//$total=99;
				$total_percent=@constant('VOUCHER_USAGE_PERCENT');
				$remain_percent=100-@constant('VOUCHER_USAGE_PERCENT');
				if($voucher_info['amount']<($total*$total_percent/100))
				{
				//	echo "in if";
				$remain=($total*$total_percent/100)-$voucher_info['amount'];
				//$amount=floor($voucher_info['amount']);
				$amount=$voucher_info['amount'];
				//$total=$total*20/100;
				}else
				{
					//echo "in else";
				$remain=0;
				//$amount=floor(($total*80/100));//$voucher_info['amount']-($total*20/100);
				$amount=($total*$total_percent/100);//$voucher_info['amount']-($total*20/100);
				}

				$total_data[] = array(
					'code'       => 'voucher',
        			'title'      => sprintf($tr->translate('text_voucher_total_voucher'), $_SESSION['voucher']),
	    			'text'       => $currObj->format(-$amount),
        			'value'      => -$amount,
					'sort_order' =>constant('MODULE_ORDER_TOTAL_VOUCHER_SORT_ORDER')
      			);

				//$total=ceil($remain+$total*20/100);
				$total=$remain+$total*$remain_percent/100;
			//}
		}
	}

	public function confirm($order_info, $order_total) {
		$code = '';

		$start = strpos($order_total['title'], '(') + 1;
		$end = strrpos($order_total['title'], ')');

		if ($start && $end) {
			$code = substr($order_total['title'], $start, $end - $start);
		}
		$vouObj=new Model_CheckoutVoucher();

		$voucher_info = $vouObj->getVoucher($code);

		if ($voucher_info) {
			$vouObj->redeem($voucher_info['voucher_id'], $order_info['order_id'], $order_total['value']);
		}
	}
  }
?>
