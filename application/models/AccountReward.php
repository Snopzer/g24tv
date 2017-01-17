<?php
class Model_AccountReward {	
		public $customer=null;
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$this->customer=new Model_Customer();
	}
	public function getRewards($data = array()) {
		$sql = "SELECT * FROM `r_customer_reward` WHERE customer_id = '" . (int)$this->customer->getId() . "'";
		   
		$sort_data = array(
			'points',
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
		
	public function getTotalRewards() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `r_customer_reward` WHERE customer_id = '" . (int)$this->customer->getId() . "'");
			
		//return $query->row['total'];
		return $query->fetchColumn(0);
	}	
			
	public function getTotalPoints() {
		$query = $this->db->query("SELECT SUM(points) AS total FROM `r_customer_reward` WHERE customer_id = '" . (int)$this->customer->getId() . "' GROUP BY customer_id");
		
		if ($query->num_rows) {
			//return $query->row['total'];
			return $query->fetchColumn(0);
		} else {
			return 0;	
		}
	}

	public function addReward($customer_id, $description = '', $points = '', $order_id = 0) {
		$accCustObj=new Model_AccountCustomer();
		$customer_info = $accCustObj->getCustomer($customer_id);
			
		if ($customer_info) { 
			$this->db->query("INSERT INTO r_customer_reward SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', points = '" . (int)$points . "', description = '" . stripslashes($description) . "', date_added = '".date('Y-m-d H:i:s')."'");

			/*$order_info = $this->model_sale_order->getOrder($order_id);
			 		
			$store_name = STORE_NAME;	
			$message  = sprintf($this->language->get('text_reward_received'), $points) . "\n\n";
			$message .= sprintf($this->language->get('text_reward_total'), $this->getRewardTotal($customer_id));
				
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			$mail->setTo($customer_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($store_name);
			$mail->setSubject(sprintf($this->language->get('text_reward_subject'), $store_name));
			$mail->setText($message);
			$mail->send();*/
		}
	}

	public function getTotalCustomerRewardsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM r_customer_reward WHERE order_id = '" . (int)$order_id . "'");
		$query_row=$query->fetch();
		return $query_row['total'];
	}
}
?>