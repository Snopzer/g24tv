<?php
class Model_CheckoutVoucher {
	public $db;

	public function __construct() {
	$this->db = Zend_Db_Table::getDefaultAdapter();
	}

	public function addVoucher($order_id, $data) {
      	$this->db->query("INSERT INTO r_voucher SET order_id = '" . (int)$order_id . "', code = '" .stripslashes(substr(md5(rand()), 0, 7)) . "', from_name = '" .stripslashes($data['from_name']) . "', from_email = '" .stripslashes($data['from_email']) . "', to_name = '" .stripslashes($data['to_name']) . "', to_email = '" .stripslashes($data['to_email']) . "', message = '" .stripslashes($data['message']) . "', amount = '" . (float)$data['amount'] . "', voucher_theme_id = '" . (int)$data['voucher_theme_id'] . "', status = '1', date_added = '".date('Y-m-d H:i:s')."'");
	}

	public function getVoucher($code) {
		$status = true;

		$voucher_query = $this->db->query("SELECT *, vtd.name AS theme FROM r_voucher v LEFT JOIN r_voucher_theme vt ON (v.voucher_theme_id = vt.voucher_theme_id) LEFT JOIN r_voucher_theme_description vtd ON (vt.voucher_theme_id = vtd.voucher_theme_id) WHERE v.code = '" . stripslashes($code) . "' AND vtd.language_id = '" . (int)$_SESSION[Lang][language_id] . "' AND v.status = '1'");
		$voucher_query_row=$voucher_query->fetch();
		if ($voucher_query->rowCount()) {
			if ($voucher_query_row['order_id']) {
				$order_query = $this->db->query("SELECT * FROM `r_orders` WHERE orders_id = '" . (int)$voucher_query_row['order_id'] . "' AND orders_status = '" . (int)ORDER_COMPLETE_STATUS_ID . "'");

				if (!$order_query->rowCount()) {
					$status = false;
				}
			}

			$voucher_history_query = $this->db->query("SELECT SUM(amount) AS total FROM `r_voucher_history` vh WHERE vh.voucher_id = '" . (int)$voucher_query_row['voucher_id'] . "' GROUP BY vh.voucher_id");
			$voucher_history_query_row=$voucher_history_query->fetch();
			if ($voucher_history_query->rowCount()) {
				$amount = $voucher_query_row['amount'] + $voucher_history_query_row['total'];
			} else {
				$amount = $voucher_query_row['amount'];
			}

			if ($amount <= 0) {
				$status = false;
			}
		} else {
			$status = false;
		}

		if ($status) {
			return array(
				'voucher_id'       => $voucher_query_row['voucher_id'],
				'code'             => $voucher_query_row['code'],
				'from_name'        => $voucher_query_row['from_name'],
				'from_email'       => $voucher_query_row['from_email'],
				'to_name'          => $voucher_query_row['to_name'],
				'to_email'         => $voucher_query_row['to_email'],
				'message'          => $voucher_query_row['message'],
				'voucher_theme_id' => $voucher_query_row['voucher_theme_id'],
				'theme'            => $voucher_query_row['theme'],
				'image'            => $voucher_query_row['image'],
				'amount'           => $amount,
				'status'           => $voucher_query_row['status'],
				'date_added'       => $voucher_query_row['date_added']
			);
		}
	}

	public function confirm($order_id) {
		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info) {
			$this->load->model('localisation/language');

			$language = new Language($order_info['language_directory']);
			$language->load($order_info['language_filename']);
			$language->load('mail/voucher');

			$voucher_query = $this->db->query("SELECT *, vtd.name AS theme FROM `r_voucher` v LEFT JOIN r_voucher_theme vt ON (v.voucher_theme_id = vt.voucher_theme_id) LEFT JOIN r_voucher_theme_description vtd ON (vt.voucher_theme_id = vtd.voucher_theme_id) AND vtd.language_id = '" . (int)$order_info['language_id'] . "' WHERE order_id = '" . (int)$order_id . "'");

			foreach ($voucher_query->rows as $voucher) {
				// HTML Mail
				$template = new Template();

				$template->data['title'] = sprintf($language->get('text_subject'), $voucher['from_name']);

				$template->data['text_greeting'] = sprintf($language->get('text_greeting'), $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value']));
				$template->data['text_from'] = sprintf($language->get('text_from'), $voucher['from_name']);
				$template->data['text_message'] = $language->get('text_message');
				$template->data['text_redeem'] = sprintf($language->get('text_redeem'), $voucher['code']);
				$template->data['text_footer'] = $language->get('text_footer');

				if (file_exists(DIR_IMAGE . $voucher['image'])) {
					$template->data['image'] = 'cid:' . basename($voucher['image']);
				} else {
					$template->data['image'] = '';
				}

				$template->data['store_name'] = $order_info['store_name'];
				$template->data['store_url'] = $order_info['store_url'];
				$template->data['message'] = nl2br($voucher['message']);

				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/voucher.tpl')) {
					$html = $template->fetch($this->config->get('config_template') . '/template/mail/voucher.tpl');
				} else {
					$html = $template->fetch(SITE_DEFAULT_TEMPLATE.'/template/mail/voucher.tpl');
				}

				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->hostname = $this->config->get('config_smtp_host');
				$mail->username = $this->config->get('config_smtp_username');
				$mail->password = $this->config->get('config_smtp_password');
				$mail->port = $this->config->get('config_smtp_port');
				$mail->timeout = $this->config->get('config_smtp_timeout');
				$mail->setTo($voucher['to_email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($order_info['store_name']);
				$mail->setSubject(sprintf($language->get('text_subject'), $voucher['from_name']));
				$mail->setHtml($html);

				if (file_exists(DIR_IMAGE . $voucher['image'])) {
					$mail->addAttachment(DIR_IMAGE . $voucher['image']);
				}

				$mail->send();
			}
		}
	}

	public function redeem($voucher_id, $order_id, $amount) {
		$this->db->query("INSERT INTO `r_voucher_history` SET voucher_id = '" . (int)$voucher_id . "', order_id = '" . (int)$order_id . "', amount = '" . (float)$amount . "', date_added = '".date('Y-m-d H:i:s')."'");
	}

		public function getVoucherAdmin($voucher_id) {
      	$query = $this->db->query("SELECT DISTINCT * FROM r_voucher WHERE voucher_id = '" . (int)$voucher_id . "'");

		return $query->fetch();
	}

	public function getVouchersByOrderId($order_id) {
		$rows = $this->db->fetchAll("SELECT v.voucher_id, v.code, v.from_name, v.from_email, v.to_name, v.to_email, v.amount, vtd.name AS theme, v.status, v.date_added FROM r_voucher v LEFT JOIN r_voucher_theme_description vtd ON (v.voucher_theme_id = vtd.voucher_theme_id) WHERE v.order_id = '" . (int)$order_id . "' AND vtd.language_id = '1'");
				
		return $rows;
	}
	
	/*public function getDistinctVoucher($voucher_id) {
      	$row = $this->db->fetchRow("SELECT DISTINCT * FROM r_voucher WHERE voucher_id = '" . (int)$voucher_id . "'");
		return $row;
	}*/
	
	public function sendVoucher($order_id,$voucher_id) {
 		if($order_id!="")
		{
		
			$rows=$this->db->fetchAll("select *,(select r_voucher_theme.image from r_voucher_theme where r_voucher_theme.voucher_theme_id=r_voucher.voucher_theme_id) as theme from r_voucher where order_id='".$order_id."'");
		}else
		{
			$rows=$this->db->fetchAll("select *,(select r_voucher_theme.image from r_voucher_theme where r_voucher_theme.voucher_theme_id=r_voucher.voucher_theme_id) as theme from r_voucher where voucher_id='".$voucher_id."'");
		}
		/*start mail*/
		$mailObj=new Model_Mail();
		$currency=new Model_currencies();
		foreach($rows as $row)
		{
			$replace_array=array("%sender%"=>$row['from_name'],"%receiver%"=>$row['to_name'],"%voucher_amount%"=>$currency->format($row['amount']),"%message%"=>$row['message'],"%voucher_code%"=>$row['code']);

			$arrmc=$mailObj->getEmailContent(array('id'=>'8','lang'=>'1','replace'=>$replace_array));


			$array_mail=array('to'=>array('name'=>$row['to_name'],'email'=>trim($row['to_email'])),'html'=>array('content'=>$arrmc['content']),'subject'=>$arrmc['subject']);

			$mailObj->sendMail($array_mail);
		}
		/*end mail*/
		
	}


}
?>