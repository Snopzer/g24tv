<?php
class Model_AccountOrder {
	public $_arrObj=array();
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$this->_arrObj['custObj']=new Model_Customer();
		$this->_arrObj['catObj']=new Model_Categories();
	}

	public function getOrder($order_id) {
		$order_query = $this->db->query("SELECT * FROM `r_orders` WHERE orders_id = '" . (int)$order_id . "' AND customers_id = '" . (int)$this->_arrObj['custObj']->getId() . "' AND orders_status > '0'");
		$order_query->row=$order_query->fetch();
		if ($order_query->rowCount()) {
			$country_query = $this->db->query("SELECT * FROM `r_countries` WHERE countries_id = '" . (int)$order_query->row['shipping_country_id'] . "'");
			$country_query->row=$country_query->fetch();
			if ($country_query->rowCount()) {
				$shipping_iso_code_2 = $country_query->row['iso_code_2'];
				$shipping_iso_code_3 = $country_query->row['iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';				
			}
			
/*			$zone_query = $this->db->query("SELECT * FROM `r_zones` WHERE zone_id = '" . (int)$order_query->row['shipping_zone_id'] . "'");*/
$zone_query = $this->db->query("SELECT * FROM `r_zones` WHERE zone_name like '" . (int)$order_query->row['delivery_state'] . "'");
			$zone_query->row=$zone_query->fetch();
			if ($zone_query->rowCount()) {
				$shipping_zone_code = $zone_query->row['zone_code'];
			} else {
				$shipping_zone_code = '';
			}
			
/*			$country_query = $this->db->query("SELECT * FROM `r_country` WHERE country_id = '" . (int)$order_query->row['payment_country_id'] . "'");*/
			$country_query = $this->db->query("SELECT * FROM `r_countries` WHERE countries_id = '" . (int)$order_query->row['delivery_country'] . "'");
			$country_query->row=$country_query->fetch();
			if ($country_query->rowCount()) {
				$payment_iso_code_2 = $country_query->row['countries_iso_code_2'];
				$payment_iso_code_3 = $country_query->row['countries_iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';				
			}
			
/*			$zone_query = $this->db->query("SELECT * FROM `r_zone` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");*/
			$zone_query = $this->db->query("SELECT * FROM `r_zones` WHERE zone_id = '" . (int)$order_query->row['billing_state'] . "'");
			$zone_query->row=$zone_query->fetch();
			if ($zone_query->rowCount()) {
				$payment_zone_code = $zone_query->row['zone_code'];
			} else {
				$payment_zone_code = '';
			}
			
			return array(
				'order_id'                => $order_query->row['orders_id'],
				'invoice_no'              => $order_query->row['invoice_id'],
				'invoice_prefix'          => $order_query->row['invoice_prefix'],
				//'store_id'                => $order_query->row['store_id'],
				//'store_name'              => $order_query->row['store_name'],
				//'store_url'               => $order_query->row['store_url'],				
				'customer_id'             => $order_query->row['customers_id'],
				//'firstname'               => $order_query->row['firstname'],
				//'lastname'                => $order_query->row['lastname'],
				'lastname'                => $order_query->row['customers_name'],
				'telephone'               => $order_query->row['customers_telephone'],
				'fax'                     => $order_query->row['customers_fax'],
				'email'                   => $order_query->row['customers_email_address'],
				//'shipping_firstname'      => $order_query->row['shipping_firstname'],
				//'shipping_lastname'       => $order_query->row['shipping_lastname'],				
				'shipping_name'       => $order_query->row['delivery_name'],				
				'shipping_company'        => $order_query->row['delivery_company'],
				'shipping_address_1'      => $order_query->row['delivery_street_address'],
				'shipping_address_2'      => $order_query->row['delivery_suburb'],
				'shipping_postcode'       => $order_query->row['delivery_postcode'],
				'shipping_city'           => $order_query->row['delivery_city'],
				'shipping_zone_id'        => $order_query->row['delivery_zone_id'],
				'shipping_zone'           => $order_query->row['delivery_state'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query->row['delivery_country_id'],
				'shipping_country'        => $order_query->row['delivery_country'],	
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query->row['delivery_address_format_id'],
				'shipping_method'         => $order_query->row['shipping_method'],
				//'payment_firstname'       => $order_query->row['payment_firstname'],
				//'payment_lastname'        => $order_query->row['payment_lastname'],				
				'billing_name'        => $order_query->row['billing_name'],				
				'payment_company'         => $order_query->row['billing_company'],
				'payment_address_1'       => $order_query->row['billing_street_address'],
				'payment_address_2'       => $order_query->row['billing_suburb'],
				'payment_postcode'        => $order_query->row['billing_postcode'],
				'payment_city'            => $order_query->row['billing_city'],
				'payment_zone_id'         => $order_query->row['billing_zone_id'],
				'payment_zone'            => $order_query->row['billing_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query->row['billing_country_id'],
				'payment_country'         => $order_query->row['billing_country'],	
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query->row['billing_address_format_id'],
				'payment_method'          => $order_query->row['payment_method'],
				'comment'                 => $order_query->row['comment'],
				'total'                   => $order_query->row['total'],
				'order_status_id'         => $order_query->row['orders_status'],
				'language_id'             => $order_query->row['language_id'],
				'currency_id'             => $order_query->row['currency_id'],
				'currency_code'           => $order_query->row['currency'],
				'currency_value'          => $order_query->row['currency_value'],
				'date_modified'           => $order_query->row['last_modified'],
				'date_added'              => $order_query->row['date_purchased'],
				'ip'                      => $order_query->row['ip_address']
			);
		} else {
			return false;	
		}
	}
	 
	public function getOrders($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}
		$query = $this->db->query("SELECT o.orders_id, o.customers_name, os.orders_status_name as status, o.date_purchased, o.total, o.currency, o.currency_value,o.delivery_street_address,o.delivery_suburb,o.delivery_city,o.delivery_zone,o.delivery_country FROM `r_orders` o LEFT JOIN r_orders_status os ON (o.orders_status = os.orders_status_id) WHERE o.customers_id = '" . (int)$this->_arrObj['custObj']->getId() . "' AND o.orders_status > '0' AND os.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' ORDER BY o.orders_id DESC LIMIT " . (int)$start . "," . (int)$limit);	
		
		return $query->fetchAll();
	}
	
	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM r_orders_products WHERE orders_id = '" . (int)$order_id . "'");
	
		//return $query->rows;
		return $query->fetchAll();
	}
	
	public function getOrderOptions($order_id, $order_product_id) {
		$query = $this->db->query("SELECT * FROM r_orders_products_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product_id . "'");
	
		//return $query->rows;
		return $query->fetchAll();
	}

	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM r_orders_total WHERE orders_id = '" . (int)$order_id . "' ORDER BY sort_order");
	
		//return $query->rows;
		return $query->fetchAll();
	}	

	public function getOrderHistories($order_id) {
		$query = $this->db->query("SELECT date_added, os.orders_status_name AS status, oh.comments, oh.customer_notified FROM r_orders_status_history oh LEFT JOIN r_orders_status os ON oh.orders_status_id = os.orders_status_id WHERE oh.orders_id = '" . (int)$order_id . "' AND oh.customer_notified= '1' AND os.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' ORDER BY oh.date_added");
	//echo "SELECT date_added, os.orders_status_name AS status, oh.comments, oh.customer_notified FROM r_orders_status_history oh LEFT JOIN r_orders_status os ON oh.orders_status_history_id = os.orders_status_id WHERE oh.orders_id = '" . (int)$order_id . "' AND oh.customer_notified= '1' AND os.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' ORDER BY oh.date_added";
		//return $query->rows;
		return $query->fetchAll();
	}	

	public function getOrderDownloads($order_id) {
		$query = $this->db->query("SELECT * FROM r_orders_products_download WHERE orders_id = '" . (int)$orders_id . "' ORDER BY name");
	
		//return $query->rows; 
		return $query->fetchAll(); 
	}	

	public function getTotalOrders() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `r_orders` WHERE customers_id = '" . (int)$this->_arrObj['custObj']->getId() . "' AND orders_status > '0'");
		
		//return $query->row['total'];
		return $query->fetchColumn(0);
	}
		
	public function getTotalOrderProductsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM r_orders_products WHERE orders_id = '" . (int)$order_id . "'");
		//return $query->row['total'];
		return $query->fetchColumn(0);
	}
}
?>