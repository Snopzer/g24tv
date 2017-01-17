<?php
class Model_AccountReturn {

	public function __construct() {
		$this->db = Zend_Db_Table::getDefaultAdapter();
		}

	public function addReturn($data) {
		$this->customer=new Model_Customer();
		$this->db->query("INSERT INTO `r_return` SET order_id = '" . (int)$data['order_id'] . "', customer_id = '" . (int)$this->customer->getId() . "', firstname = '" . stripslashes($data['firstname']) . "', lastname = '" . stripslashes($data['lastname']) . "', email = '" . stripslashes($data['email']) . "', telephone = '" . stripslashes($data['telephone']) . "', product = '" . stripslashes($data['product']) . "', model = '" . stripslashes($data['model']) . "', quantity = '" . (int)$data['quantity'] . "', opened = '" . (int)$data['opened'] . "', return_reason_id = '" . (int)$data['return_reason_id'] . "', return_status_id = '" . (int)DEFAULT_RETURN_STATUS_ID . "', comment = '" . stripslashes($data['comment']) . "', date_ordered = '" . stripslashes($data['date_ordered']) . "', date_added = '".date('Y-m-d H:i:s')."', date_modified = '".date('Y-m-d H:i:s')."'");
	}
	
	public function getReturn($return_id) {
		$this->customer=new Model_Customer();
		$query = $this->db->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, r.email, r.telephone, r.product, r.model, r.quantity, r.opened, rr.name as reason, ra.name as action, rs.name as status, r.comment, r.date_ordered, r.date_added, r.date_modified FROM `r_return` r LEFT JOIN r_return_reason rr ON (r.return_reason_id = rr.return_reason_id) LEFT JOIN r_return_action ra ON (r.return_action_id = ra.return_action_id) LEFT JOIN r_return_status rs ON (r.return_status_id = rs.return_status_id) WHERE return_id = '" . (int)$return_id . "' AND customer_id = '" . $this->customer->getId(). "'");
		 
		return $query->fetch();
	}
	
	public function getReturns($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}
		$this->customer=new Model_Customer();		
		$query = $this->db->query("SELECT r.return_id, r.order_id, r.firstname, r.lastname, rs.name as status, r.date_added FROM `r_return` r LEFT JOIN r_return_status rs ON (r.return_status_id = rs.return_status_id) WHERE r.customer_id = '" . $this->customer->getId() . "' AND rs.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' ORDER BY r.return_id DESC LIMIT " . (int)$start . "," . (int)$limit);
		
		return $query->fetchAll();
	}
			
	public function getTotalReturns() {
		$this->customer=new Model_Customer();
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `r_return`WHERE customer_id = '" . $this->customer->getId() . "'");
		$query->row=$query->fetch();
		return $query->row['total'];
	}
	
	public function getReturnHistories($return_id) {
		$query = $this->db->query("SELECT rh.date_added, rs.name AS status, rh.comment, rh.notify FROM r_return_history rh LEFT JOIN r_return_status rs ON rh.return_status_id = rs.return_status_id WHERE rh.return_id = '" . (int)$return_id . "' AND rs.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' ORDER BY rh.date_added ASC");

		return $query->fetchAll();
	}			

	/**********start return reason****************/
	
	public function addReturnReason($data) {
		foreach ($data['return_reason'] as $language_id => $value) {
			if (isset($return_reason_id)) {
				$this->db->query("INSERT INTO r_return_reason SET return_reason_id = '" . (int)$return_reason_id . "', language_id = '" . (int)$language_id . "', name = '" . stripslashes($value['name']) . "'");
			} else {
				$this->db->query("INSERT INTO r_return_reason SET language_id = '" . (int)$language_id . "', name = '" . stripslashes($value['name']) . "'");
				
				$return_reason_id = $this->db->lastInsertId();;
			}
		}
		
		//$this->cache->delete('return_reason');
	}

	public function editReturnReason($return_reason_id, $data) {
		$this->db->query("DELETE FROM r_return_reason WHERE return_reason_id = '" . (int)$return_reason_id . "'");

		foreach ($data['return_reason'] as $language_id => $value) {
			$this->db->query("INSERT INTO r_return_reason SET return_reason_id = '" . (int)$return_reason_id . "', language_id = '" . (int)$language_id . "', name = '" . stripslashes($value['name']) . "'");
		}
				
		//$this->cache->delete('return_reason');
	}
	
	public function deleteReturnReason($return_reason_id) {
		$this->db->query("DELETE FROM r_return_reason WHERE return_reason_id = '" . (int)$return_reason_id . "'");
	
		//$this->cache->delete('return_reason');
	}
		
	public function getReturnReason($return_reason_id) {
		$query = $this->db->query("SELECT * FROM r_return_reason WHERE return_reason_id = '" . (int)$return_reason_id . "' AND language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'");
		
		return $query->row;
	}
		
	public function getReturnReasons($data = array()) {
      	if ($data) {
			$sql = "SELECT * FROM r_return_reason WHERE language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'";
			
			$sql .= " ORDER BY name";	
			
			if (isset($data['return']) && ($data['return'] == 'DESC')) {
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
		} else {
			//$return_reason_data = $this->cache->get('return_reason.' . (int)$_SESSION['Lang']['language_id']);
		
			if (!$return_reason_data) {
				$query = $this->db->query("SELECT return_reason_id, name FROM r_return_reason WHERE language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' ORDER BY name");
	
				//$return_reason_data = $query->rows;
				$return_reason_data = $query->fetchAll();
			
				//$this->cache->set('return_reason.' . (int)$_SESSION['Lang']['language_id'], $return_reason_data);
			}	
	
			return $return_reason_data;				
		}
	}
	
	public function getReturnReasonDescriptions($return_reason_id) {
		$return_reason_data = array();
		
		$query = $this->db->query("SELECT * FROM r_return_reason WHERE return_reason_id = '" . (int)$return_reason_id . "'");
		$query->rows=$query->fetchAll();
		foreach ($query->rows as $result) {
			$return_reason_data[$result['language_id']] = array('name' => $result['name']);
		}
		
		return $return_reason_data;
	}
	
	public function getTotalReturnReasons() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM r_return_reason WHERE language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'");
		$query->row=$query->fetch();
		return $query->row['total'];
	}

}
?>