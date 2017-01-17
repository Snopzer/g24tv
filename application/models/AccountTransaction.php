<?php
class Model_AccountTransaction{
public $custObj=null;
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$this->custObj=new Model_Customer();
	}

	public function getTransactions($data = array()) {
		$sql = "SELECT * FROM `r_customer_transaction` WHERE customer_id = '" . (int)$this->custObj->getId() . "'";

		$sort_data = array(
			'amount',
			'description',
			'date_added'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		//return $query->rows;
		return $query->fetchAll();
	}

	public function getTotalTransactions() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `r_customer_transaction` WHERE customer_id = '" . (int)$this->custObj->getId() . "'");
		//return $query->row['total'];
		return $query->fetchColumn(0);
	}

	public function getTotalAmount() {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM `r_customer_transaction` WHERE customer_id = '" . (int)$this->custObj->getId() . "' GROUP BY customer_id");

		if ($query->rowCount()) {
			//return $query->row['total'];
			return $query->fetchColumn(0);
		} else {
			return 0;
		}
	}
}
?>