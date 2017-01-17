<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class Model_OrderTotal_OtRewardPoints {
    var $title, $output;

    function Model_OrderTotal_OtRewardPoints() {
      $this->code = 'ot_rewardPoints';
      $this->title = @constant('MODULE_ORDER_TOTAL_REWARDPOINTS_TITLE')==""?"Total":MODULE_ORDER_TOTAL_REWARDPOINTS_TITLE;//MODULE_ORDER_TOTAL_REWARDPOINTS_TITLE;
      $this->description = MODULE_ORDER_TOTAL_REWARDPOINTS_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_REWARDPOINTS_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_REWARDPOINTS_SORT_ORDER;

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

    /*function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_TOTAL_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }

      return $this->_check;
    }*/

    function keys() {
      return array('MODULE_ORDER_TOTAL_REWARDPOINTS_TITLE','MODULE_ORDER_TOTAL_REWARDPOINTS_STATUS', 'MODULE_ORDER_TOTAL_REWARDPOINTS_SORT_ORDER');
    }

    function install() {

		
						$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_ORDER_TOTAL_REWARDPOINTS_TITLE','configuration_value'=>'Reward Points','configuration_description'=>'enter title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'1','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Display Reward Points','configuration_key'=>'MODULE_ORDER_TOTAL_REWARDPOINTS_STATUS','configuration_value'=>'true','configuration_description'=>'Do you want to display the total order value?','configuration_group_id'=>'6','sort_order'=>'1','set_function'=>'tep_cfg_select_option(array(\'true\', \'false\'), ','date_added'=>new Zend_Db_Expr('NOW()')));


		$this->db->insert('r_configuration',array('configuration_title'=>'Sort Order','configuration_key'=>'MODULE_ORDER_TOTAL_REWARDPOINTS_SORT_ORDER','configuration_value'=>'4','configuration_description'=>'Sort order of display.','configuration_group_id'=>'6','sort_order'=>'2','date_added'=>new Zend_Db_Expr('NOW()')));

    }

    function remove() {
	  	$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");

    }

 public function getTotal(&$total_data, &$total, &$taxes) {

	 $custObj=new Model_Customer();
	 $cartObj=new Model_Cart();
	 $taxObj=new Model_Tax();
	 $currObj=new Model_currencies();

 

	if (isset($_SESSION['reward'])) {
		//echo "here in session";
		//echo $_SESSION['reward'];
			$points = $custObj->getRewardPoints();

			if ($_SESSION['reward'] <= $points) {
				$discount_total = 0;

				$points_total = 0;

				foreach ($cartObj->getProducts() as $product) {
					if ($product['points']) {
						$points_total += $product['points'];
					}
				}

				$points = min($points, $points_total);

				foreach ($cartObj->getProducts() as $product) {
					$discount = 0;

					if ($product['points']) {
						$discount = $product['total'] * ($_SESSION['reward'] / $points_total);

						if ($product['tax_class_id']) {
							$taxes[$product['tax_class_id']] -= ($product['total'] / 100 * $taxObj->getRate($product['tax_class_id'])) - (($product['total'] - $discount) / 100 * $taxObj->getRate($product['tax_class_id']));
						}
					}

					$discount_total += $discount;
				}

				$total_data[] = array(
					'code'       => 'rewardPoints',
        			'title'      => sprintf($_SESSION['OBJ']['tr']->translate('text_reward_total_reward'), $_SESSION['reward']),
	    			'text'       => $currObj->format(-$discount_total),
        			'value'      => -$discount_total,
					'sort_order' => MODULE_ORDER_TOTAL_REWARDPOINTS_SORT_ORDER
      			);

				$total -= $discount_total;
 			}
		}
	}

	public function confirm($order_info, $order_total) {
	 	$points = 0;

		$start = strpos($order_total['title'], '(') + 1;
		$end = strrpos($order_total['title'], ')');

		if ($start && $end) {
			$points = substr($order_total['title'], $start, $end - $start);
		}

		if ($points) {
			//$this->db->query("INSERT INTO r_customer_reward SET customer_id = '" . (int)$order_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . stripslashes(sprintf($tr->translate('text_order_id'), (int)$order_info['order_id'])) . "', points = '" . (float)-$points . "', date_added = NOW()");

			$this->db->query("INSERT INTO r_customer_reward SET customer_id = '" . (int)$order_info['customer_id'] . "', description = '" . stripslashes(sprintf($_SESSION['OBJ']['tr']->translate('text_order_id'), (int)$order_info['order_id'])) . "', points = '" . (float)-$points . "', date_added = NOW()");
		}
	}
  }
?>
