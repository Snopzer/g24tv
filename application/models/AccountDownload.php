<?php
class Model_AccountDownload {
public $customer;
	public function __construct() {
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$this->customer=new Model_Customer();
		}

	public function getDownload($order_download_id) {
		$query = $this->db->query("SELECT * FROM r_orders_products_download od LEFT JOIN `r_orders` o ON (od.orders_id = o.orders_id) WHERE o.customers_id = '" . (int)$this->customer->getId(). "' AND o.orders_status > '0' AND o.orders_status = '" . (int)ORDER_COMPLETE_STATUS_ID . "' AND od.orders_products_download_id = '" . (int)$order_download_id . "' AND od.remaining > 0");
		/*echo "SELECT * FROM r_orders_products_download od LEFT JOIN `r_orders` o ON (od.orders_id = o.orders_id) WHERE o.customers_id = '" . (int)$this->customer->getId(). "' AND o.orders_status > '0' AND o.orders_status = '" . (int)ORDER_COMPLETE_STATUS_ID . "' AND od.orders_products_download_id = '" . (int)$order_download_id . "' AND od.remaining > 0";*/
		//return $query->row;
		return $query->fetch();
	}

	public function getDownloads($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		$query = $this->db->query("SELECT o.orders_id, o.date_purchased, od.orders_products_download_id, od.name, od.orders_products_filename	, od.remaining FROM r_orders_products_download od LEFT JOIN `r_orders` o ON (od.orders_id = o.orders_id) WHERE o.customers_id = '" . (int)$this->customer->getId() . "' AND o.orders_status > '0' AND o.orders_status = '" . (int)ORDER_COMPLETE_STATUS_ID . "' ORDER BY o.date_purchased DESC LIMIT " . (int)$start . "," . (int)$limit);

		//return $query->rows;
		return $query->fetchAll();
	}

	public function updateRemaining($order_download_id) {
		$this->db->query("UPDATE r_orders_products_download SET remaining = (remaining - 1) WHERE orders_products_download_id = '" . (int)$order_download_id . "'");
	}

	public function getTotalDownloads() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM r_orders_products_download od LEFT JOIN `r_orders` o ON (od.orders_id = o.orders_id) WHERE o.customers_id = '" . (int)$this->customer->getId() . "' AND o.orders_status > '0' AND o.orders_status = '" . (int)ORDER_COMPLETE_STATUS_ID . "'");

		//return $query->row['total'];\
		return $query->fetchColumn(0);
	}
}
?>