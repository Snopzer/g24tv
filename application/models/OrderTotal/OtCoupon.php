<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class Model_OrderTotal_OtCoupon {
    var $title, $output;

    function Model_OrderTotal_OtCoupon() {
      $this->code = 'ot_coupon';
      $this->title = @constant('MODULE_ORDER_TOTAL_COUPON_TITLE')==""?"Coupon(%s):":MODULE_ORDER_TOTAL_COUPON_TITLE;//MODULE_ORDER_TOTAL_COUPON_TITLE;
      $this->description = MODULE_ORDER_TOTAL_COUPON_DESCRIPTION;
      $this->enabled = ((MODULE_ORDER_TOTAL_COUPON_STATUS == 'true') ? true : false);
      $this->sort_order = MODULE_ORDER_TOTAL_COUPON_SORT_ORDER;

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
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_ORDER_TOTAL_COUPON_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }

      return $this->_check;
    }

    function keys() {
      return array('MODULE_ORDER_TOTAL_COUPON_TITLE','MODULE_ORDER_TOTAL_COUPON_STATUS', 'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER');
    }

    function install() {

		
		$this->db->insert('r_configuration',array('configuration_title'=>'Title','configuration_key'=>'MODULE_ORDER_TOTAL_COUPON_TITLE','configuration_value'=>'Coupon(%s):','configuration_description'=>'enter title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'1','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Display Coupon','configuration_key'=>'MODULE_ORDER_TOTAL_COUPON_STATUS','configuration_value'=>'true','configuration_description'=>'Do you want to display the coupon value?','configuration_group_id'=>'6','sort_order'=>'1','set_function'=>'tep_cfg_select_option(array(\'true\', \'false\'), ','date_added'=>new Zend_Db_Expr('NOW()')));


		$this->db->insert('r_configuration',array('configuration_title'=>'Sort Order','configuration_key'=>'MODULE_ORDER_TOTAL_COUPON_SORT_ORDER','configuration_value'=>'5','configuration_description'=>'Sort order of display.','configuration_group_id'=>'6','sort_order'=>'2','date_added'=>new Zend_Db_Expr('NOW()')));

    }

    function remove() {
	  	$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");

    }

	public function getTotal(&$total_data, &$total, &$taxes) {
		if (isset($_SESSION['coupon'])) {
			$lang=new Model_Languages('');
			$lC=new Model_Cache();
			$this->tr=$lC->getLangCache($lang);
			$chckCouObj=new Model_CheckoutCoupon();

			$coupon_info = $chckCouObj->getCoupon($_SESSION['coupon']);
			$this->cart=new Model_Cart();
			$this->tax=new Model_Tax();
			$this->currency=new Model_currencies();
			if ($coupon_info) {
				$discount_total = 0;

				if (!$coupon_info['product']) {
					$sub_total = $this->cart->getSubTotal();
				} else {
					$sub_total = 0;

					foreach ($this->cart->getProducts() as $product) {
						if (in_array($product['product_id'], $coupon_info['product'])) {
							$sub_total += $product['total'];
						}
					}
				}

				if ($coupon_info['type'] == 'F') {
					$coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
				}

				foreach ($this->cart->getProducts() as $product) {
					$discount = 0;

					if (!$coupon_info['product']) {
						$status = true;
					} else {
						if (in_array($product['product_id'], $coupon_info['product'])) {
							$status = true;
						} else {
							$status = false;
						}
					}

					if ($status) {
						if ($coupon_info['type'] == 'F') {
							$discount = $coupon_info['discount'] * ($product['total'] / $sub_total);
						} elseif ($coupon_info['type'] == 'P') {
							$discount = $product['total'] / 100 * $coupon_info['discount'];
						}

						if ($product['tax_class_id']) {
							$taxes[$product['tax_class_id']] -= ($product['total'] / 100 * $this->tax->getRate($product['tax_class_id'])) - (($product['total'] - $discount) / 100 * $this->tax->getRate($product['tax_class_id']));
						}
					}

					$discount_total += $discount;
				}

				if ($coupon_info['shipping'] && isset($_SESSION['shipping_method'])) {
					if (isset($_SESSION['shipping_method']['tax_class_id']) && $_SESSION['shipping_method']['tax_class_id']) {
						$taxes[$_SESSION['shipping_method']['tax_class_id']] -= $_SESSION['shipping_method']['cost'] / 100 * $this->tax->getRate($_SESSION['shipping_method']['tax_class_id']);
					}

					$discount_total += $_SESSION['shipping_method']['cost'];
				}

				$total_data[] = array(
					'code'       => 'coupon',
        			'title'      => sprintf(@constant('MODULE_ORDER_TOTAL_COUPON_TITLE'), $_SESSION['coupon']),
	    			'text'       => $this->currency->format(-$discount_total),
        			'value'      => -$discount_total,
					'sort_order' => MODULE_ORDER_TOTAL_COUPON_SORT_ORDER
      			);

				$total -= $discount_total;
			}
		}
	}

	public function confirm($order_info, $order_total) {
		$code = '';

		$start = strpos($order_total['title'], '(') + 1;
		$end = strrpos($order_total['title'], ')');

		if ($start && $end) {
			$code = substr($order_total['title'], $start, $end - $start);
		}
		$chckCouObj=new Model_CheckoutCoupon();
		$coupon_info = $chckCouObj->getCoupon($code);

		if ($coupon_info) {
			$chckCouObj->redeem($coupon_info['coupon_id'], $order_info['order_id'], $order_info['customer_id'], $order_total['value']);
		}
	}
  }
?>
