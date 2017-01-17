<?php
class Model_AffiliateTransaction{
	public $affiliate=null;
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$this->affiliate=new Model_Affinfo();
	}

	public function getTransactions($data = array()) {
		$sql = "SELECT * FROM `r_affiliate_transaction` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'";

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

      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `r_affiliate_transaction` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'");

		//return $query->row['total'];
				return $query->fetchColumn(0);

	}

	public function getBalance() {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM `r_affiliate_transaction` WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "' GROUP BY affiliate_id");

		$query->row=$query->fetch();

		if ($query->rowCount()) {
			return $query->row['total'];
		} else {
			return 0;
		}
	}

	public function getTotalTransactionsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM r_affiliate_transaction WHERE order_id = '" . (int)$order_id . "'");
	$row=$query->fetch();
		return $row['total'];
	}
	
	public function addTransaction($affiliate_id, $description = '', $amount = '', $order_id = 0) {
		$affObj=new Model_Affiliate();
		$affiliate_info = $affObj->getAffiliate($affiliate_id);
		
		if ($affiliate_info) { 
			$this->db->query("INSERT INTO r_affiliate_transaction SET affiliate_id = '" . (int)$affiliate_id . "', order_id = '" . (float)$order_id . "', description = '" . stripslashes($description) . "', amount = '" . (float)$amount . "', date_added = '".date('Y-m-d H:i:s')."'");
		
			/*$message  = sprintf($this->language->get('text_transaction_received'), $this->currency->format($amount, $this->config->get('config_currency'))) . "\n\n";
			$message .= sprintf($this->language->get('text_transaction_total'), $this->currency->format($this->getTransactionTotal($affiliate_id), $this->config->get('config_currency')));
								
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->hostname = $this->config->get('config_smtp_host');
			$mail->username = $this->config->get('config_smtp_username');
			$mail->password = $this->config->get('config_smtp_password');
			$mail->port = $this->config->get('config_smtp_port');
			$mail->timeout = $this->config->get('config_smtp_timeout');
			$mail->setTo($affiliate_info['email']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender($this->config->get('config_name'));
			$mail->setSubject(sprintf($this->language->get('text_transaction_subject'), $this->config->get('config_name')));
			$mail->setText($message);
			$mail->send();*/
		}
	}
}
?>