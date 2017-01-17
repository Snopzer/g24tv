<?php
class Model_CheckoutCoupon {
	
	public $db;
	public $cart;
	public function __construct() {
	$this->db = Zend_Db_Table::getDefaultAdapter();
	}

	public function getCoupon($code) {
		$status = true;
		$this->cart=new Model_Cart();
		$this->customer=new Model_Customer();
		$coupon_query = $this->db->query("SELECT * FROM r_coupon WHERE code = '" . stripslashes($code) . "' AND ((date_start = '0000-00-00' OR date_start <'".date('Y-m-d H:i:s')."') AND (date_end = '0000-00-00' OR date_end > '".date('Y-m-d H:i:s')."')) AND status = '1'");
			$coupon_query_row=$coupon_query->fetch();
		if ($coupon_query->rowCount()) {
			if ($coupon_query_row['total'] >= $this->cart->getSubTotal()) {
				$status = false;
			}
		
			$coupon_history_query = $this->db->query("SELECT COUNT(*) AS total FROM `r_coupon_history` ch WHERE ch.coupon_id = '" . (int)$coupon_query_row['coupon_id'] . "'");
			
			$coupon_history_query_row=$coupon_history_query->fetch();
			
			if ($coupon_query_row['uses_total'] > 0 && ($coupon_history_query_row['total'] >= $coupon_query_row['uses_total'])) {
				$status = false;
			}
			
			if ($coupon_query_row['logged'] && !$this->customer->getId()) {
				$status = false;
			}
			
			if ($this->customer->getId()) {
				$coupon_history_query = $this->db->query("SELECT COUNT(*) AS total FROM `r_coupon_history` ch WHERE ch.coupon_id = '" . (int)$coupon_query_row['coupon_id'] . "' AND ch.customer_id = '" . (int)$this->customer->getId() . "'");
				
				if ($coupon_query_row['uses_customer'] > 0 && ($coupon_history_query_row['total'] >= $coupon_query_row['uses_customer'])) {
					$status = false;
				}
			}
				
			$coupon_product_data = array();
				
			$coupon_product_query = $this->db->query("SELECT * FROM r_coupon_product WHERE coupon_id = '" . (int)$coupon_query_row['coupon_id'] . "'");
			
			$coupon_product_query_rows=$coupon_product_query->fetchAll();
			
			foreach ($coupon_product_query_rows as $result) {
				$coupon_product_data[] = $result['product_id'];
			}
				
			if ($coupon_product_data) {
				$coupon_product = false;
					
				foreach ($this->cart->getProducts() as $product) {
					if (in_array($product['product_id'], $coupon_product_data)) {
						$coupon_product = true;
							
						break;
					}
				}
					
				if (!$coupon_product) {
					$status = false;
				}
			}
		} else {
			$status = false;
		}
		
		if ($status) {
			return array(
				'coupon_id'     => $coupon_query_row['coupon_id'],
				'code'          => $coupon_query_row['code'],
				'name'          => $coupon_query_row['name'],
				'type'          => $coupon_query_row['type'],
				'discount'      => $coupon_query_row['discount'],
				'shipping'      => $coupon_query_row['shipping'],
				'total'         => $coupon_query_row['total'],
				'product'       => $coupon_product_data,
				'date_start'    => $coupon_query_row['date_start'],
				'date_end'      => $coupon_query_row['date_end'],
				'uses_total'    => $coupon_query_row['uses_total'],
				'uses_customer' => $coupon_query_row['uses_customer'],
				'status'        => $coupon_query_row['status'],
				'date_added'    => $coupon_query_row['date_added']
			);
		}
	}
	
	public function redeem($coupon_id, $order_id, $customer_id, $amount) {
		$this->db->query("INSERT INTO r_coupon_history SET coupon_id = '" . (int)$coupon_id . "', order_id = '" . (int)$order_id . "', customer_id = '" . (int)$customer_id . "', amount = '" . (float)$amount . "', date_added = '".date('Y-m-d H:i:s')."'");
	}
}
?>