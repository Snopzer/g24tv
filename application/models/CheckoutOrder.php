<?php
class Model_CheckoutOrder{
	public function __construct()
    {
		$this->db = Zend_Db_Table::getDefaultAdapter();
	}

	public function create($data) {

		$this->db->query("INSERT INTO `r_orders` SET  customers_id = '" . (int)$data['customer_id'] . "', customer_group_id = '" . (int)$data['customer_group_id'] . "', customers_name = '" . addslashes($data['firstname'])."  ".addslashes($data['lastname']) . "', customers_email_address = '" . addslashes($data['email']) . "', customers_telephone = '" . addslashes($data['telephone']) . "', customers_fax = '" . addslashes($data['fax']) . "', delivery_name = '" . addslashes($data['shipping_firstname'])." ".addslashes($data['shipping_lastname']) . "', delivery_company = '" . addslashes($data['shipping_company']) . "', delivery_street_address = '" . addslashes($data['shipping_address_1']) . "', delivery_suburb = '" . addslashes($data['shipping_address_2']) . "', delivery_city = '" . addslashes($data['shipping_city']) . "', delivery_postcode = '" . addslashes($data['shipping_postcode']) . "', delivery_country = '" . addslashes($data['shipping_country']) . "', delivery_country_id = '" . (int)$data['shipping_country_id'] . "', delivery_zone = '" . addslashes($data['shipping_zone']) . "', delivery_zone_id = '" . (int)$data['shipping_zone_id'] . "', delivery_address_format_id = '" . addslashes($data['shipping_address_format']) . "', shipping_method = '" . addslashes($data['shipping_method']) . "', billing_name = '" . addslashes($data['payment_firstname'])." ".addslashes($data['payment_lastname']) . "', billing_company = '" . addslashes($data['payment_company']) . "', billing_street_address = '" . addslashes($data['payment_address_1']) . "', billing_suburb = '" . addslashes($data['payment_address_2']) . "', billing_city = '" . addslashes($data['payment_city']) . "', billing_postcode = '" . addslashes($data['payment_postcode']) . "', billing_country = '" . addslashes($data['payment_country']) . "', billing_country_id = '" . (int)$data['payment_country_id'] . "', billing_zone = '" . addslashes($data['payment_zone']) . "', billing_zone_id = '" . (int)$data['payment_zone_id'] . "', billing_address_format_id = '" . addslashes($data['payment_address_format']) . "', payment_method = '" . addslashes($data['payment_method']) . "',  total = '" . (float)$data['total'] . "', rewards = '" . (float)$data['reward'] . "', affiliate_id = '" . (int)$data['affiliate_id'] . "', commission = '" . (float)$data['commission'] . "', language_id = '" . (int)$data['language_id'] . "', currency_id = '" . (int)$data['currency_id'] . "', currency = '" . addslashes($data['currency_code']) . "', currency_value = '" . (float)$data['currency_value'] . "', ip_address = '" . addslashes($data['ip']) . "', date_purchased = '".date('Y-m-d H:i:s')."', last_modified = '".date('Y-m-d H:i:s')."'");

			$order_id = $this->db->lastInsertId();

		foreach ($data['products'] as $product) {
			$this->db->query("INSERT INTO r_orders_products SET orders_id = '" . (int)$order_id . "', products_id = '" . (int)$product['product_id'] . "', products_name = '" . addslashes($product['name']) . "', products_model = '" . addslashes($product['model']) . "', products_quantity = '" . (int)$product['quantity'] . "', products_price = '" . (float)$product['price'] . "', final_price = '" . (float)$product['total'] . "', products_tax = '" . (float)$product['tax'] . "'");

			$order_product_id = $this->db->lastInsertId();

			foreach ($product['option'] as $option) {
				$this->db->query("INSERT INTO r_orders_products_option SET order_id = '" . (int)$order_id . "', order_product_id = '" . (int)$order_product_id . "', product_option_id = '" . (int)$option['product_option_id'] . "', product_option_value_id = '" . (int)$option['product_option_value_id'] . "', name = '" . addslashes($option['name']) . "', `value` = '" . addslashes($option['value']) . "', `type` = '" . addslashes($option['type']) . "'");
			}

			foreach ($product['download'] as $download) {
				$this->db->query("INSERT INTO r_orders_products_download SET orders_id = '" . (int)$order_id . "', orders_products_id = '" . (int)$order_product_id . "', name = '" . addslashes($download['name']) . "', orders_products_filename = '" . addslashes($download['filename']) . "', mask = '" . addslashes($download['mask']) . "', remaining = '" . (int)($download['remaining'] * $product['quantity']) . "'");
			}
		}

		foreach ($data['totals'] as $total) {
			$this->db->query("INSERT INTO r_orders_total SET orders_id = '" . (int)$order_id . "', class = '" . addslashes($total['code']) . "', title = '" . addslashes($total['title']) . "', text = '" . addslashes($total['text']) . "', `value` = '" . (float)$total['value'] . "', sort_order = '" . (int)$total['sort_order'] . "'");
		}

		return $order_id;
	}

	public function getOrder($order_id) {

		$order_query = $this->db->query("SELECT *, (SELECT os.orders_status_name as name FROM `r_orders_status` os WHERE os.orders_status_id = o.orders_status AND os.language_id = o.language_id) AS order_status_name FROM `r_orders` o WHERE o.orders_id = '" . (int)$order_id . "'");
		$order_query_row=$order_query->fetch();

		if ($order_query->rowCount()) {
		$country_query = $this->db->query("SELECT * FROM `r_countries` WHERE countries_id = '" . (int)$order_query_row['delivery_country_id'] . "'");
		$country_query_row=$country_query->fetch();
			if ($country_query->rowCount()) {
				$shipping_iso_code_2 = $country_query_row['countries_iso_code_2'];
				$shipping_iso_code_3 = $country_query_row['countries_iso_code_3'];
			} else {
				$shipping_iso_code_2 = '';
				$shipping_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `r_zones` WHERE zone_id = '" . (int)$order_query_row['delivery_zone_id'] . "'");
			$zone_query_row=$zone_query->fetch();
			if ($zone_query->rowCount()) {
				$shipping_zone_code = $zone_query_row['zone_code'];
			} else {
				$shipping_zone_code = '';
			}

			$country_query = $this->db->query("SELECT * FROM `r_countries` WHERE countries_id = '" . (int)$order_query_row['billing_country_id'] . "'");
			$country_query_row=$country_query->fetch();
			if ($country_query->rowCount()) {
				$payment_iso_code_2 = $country_query_row['countries_iso_code_2'];
				$payment_iso_code_3 = $country_query_row['countries_iso_code_3'];
			} else {
				$payment_iso_code_2 = '';
				$payment_iso_code_3 = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `r_zones` WHERE zone_id = '" . (int)$order_query->row['payment_zone_id'] . "'");
			$zone_query_row=$zone_query->fetch();
			if ($zone_query->rowCount()) {
				$payment_zone_code = $zone_query_row['zone_code'];
			} else {
				$payment_zone_code = '';
			}





			if ($_SESSION[Lang]['language_id']!="") {
				$language_code = $_SESSION[Lang]['language_code'];
				$language_filename = $_SESSION[Lang]['language_directory'];//filename and directory name will be same
				$language_directory = $_SESSION[Lang]['language_directory'];
			} else {
				$language_code = '';
				$language_filename = '';
				$language_directory = '';
			}

			return array(
				'order_id'                => $order_query_row['orders_id'],
				'invoice_no'              => $order_query_row['invoice_id'],
				'invoice_prefix'          => $order_query_row['invoice_prefix'],
				//'store_id'                => $order_query->row['store_id'],
				'store_name'              => STORE_NAME,
				//'store_url'               => $order_query->row['store_url'],
				'customer_id'             => $order_query_row['customers_id'],
				'customers_name'          => $order_query_row['customers_name'],
				'telephone'               => $order_query_row['customers_telephone'],
				'fax'                     => $order_query_row['customers_fax'],
				'email'                   => $order_query_row['customers_email_address'],
				'shipping_name'      	  => $order_query_row['delivery_name'],
				'shipping_company'        => $order_query_row['delivery_company'],
				'shipping_address_1'      => $order_query_row['delivery_street_address'],
				'shipping_address_2'      => $order_query_row['delivery_suburb'],
				'shipping_postcode'       => $order_query_row['delivery_postcode'],
				'shipping_city'           => $order_query_row['delivery_city'],
				'shipping_zone_id'        => $order_query_row['delivery_zone_id'],
				'shipping_zone'           => $order_query_row['delivery_zone'],
				'shipping_zone_code'      => $shipping_zone_code,
				'shipping_country_id'     => $order_query_row['delivery_country_id'],
				'shipping_country'        => $order_query_row['delivery_country'],
				'shipping_iso_code_2'     => $shipping_iso_code_2,
				'shipping_iso_code_3'     => $shipping_iso_code_3,
				'shipping_address_format' => $order_query_row['delivery_address_format_id'],
				'shipping_method'         => $order_query_row['shipping_method'],
				'payment_name'       	  => $order_query_row['billing_name'],
				'payment_company'         => $order_query_row['billing_company'],
				'payment_address_1'       => $order_query_row['billing_street_address'],
				'payment_address_2'       => $order_query_row['billing_suburb'],
				'payment_postcode'        => $order_query_row['billing_postcode'],
				'payment_city'            => $order_query_row['billing_city'],
				'payment_zone_id'         => $order_query_row['billing_zone_id'],
				'payment_zone'            => $order_query_row['billing_zone'],
				'payment_zone_code'       => $payment_zone_code,
				'payment_country_id'      => $order_query_row['billing_country_id'],
				'payment_country'         => $order_query_row['billing_country'],
				'payment_iso_code_2'      => $payment_iso_code_2,
				'payment_iso_code_3'      => $payment_iso_code_3,
				'payment_address_format'  => $order_query_row['billing_address_format_id'],
				'payment_method'          => $order_query_row['payment_method'],
				'comment'                 => $order_query_row['comments'],
				'total'                   => $order_query_row['total'],
				'order_status_id'         => $order_query_row['orders_status'],
				'order_status'            => $order_query_row['order_status_name'],
				'language_id'             => $order_query_row['language_id'],
				'language_code'           => $language_code,
				'language_filename'       => $language_filename,
				'language_directory'      => $language_directory,
				'currency_id'             => $order_query_row['currency_id'],
				'currency_code'           => $order_query_row['currency'],
				'currency_value'          => $order_query_row['currency_value'],
				'date_modified'           => $order_query_row['last_modified'],
				'date_added'              => $order_query_row['date_purchased'],
				'ip'                      => $order_query_row['ip_address']
			);
		} else {
			return false;
		}
	}

	public function confirm($order_id, $order_status_id, $comment = '')
	{

 
		$lC=new Model_Cache();
		$lang=new Model_Languages('');
		$tr=$lC->getLangCache($lang);
		$_SESSION['TObj']=$tr;

		$order_info = $this->getOrder($order_id);
		$currObj=new Model_currencies();
		
		if (isset($order_info) && !$order_info['order_status_id'])
		{
			$query = $this->db->query("SELECT MAX(invoice_id) AS invoice_no FROM `r_orders` WHERE invoice_prefix = '" . addslashes($order_info['invoice_prefix']) . "'");
			$query_row=$query->fetch();

			if ($query_row['invoice_no']) {
				$invoice_id = (int)$query_row['invoice_no'] + 1;
			} else {
				$invoice_id = 1;
			}

			$this->db->query("UPDATE `r_orders` SET invoice_id = '" . (int)$invoice_id . "', invoice_prefix = '" . addslashes($order_info['invoice_prefix']) . "', orders_status = '" . (int)$order_status_id . "', last_modified = '".date('Y-m-d H:i:s')."' WHERE orders_id = '" . (int)$order_id . "'");
			
			/*$this->db->query("UPDATE `r_orders` SET invoice_id = '0', invoice_prefix = '" . addslashes($order_info['invoice_prefix']) . "', orders_status = '0', last_modified = NOW() WHERE orders_id = '" . (int)$order_id . "'");*/

			$this->db->query("INSERT INTO r_orders_status_history SET orders_id = '" . (int)$order_id . "', orders_status_id = '" . (int)$order_status_id . "', customer_notified = '1', comments = '" . addslashes($comment) . "', date_added = '".date('Y-m-d H:i:s')."'");

			$order_product_query = $this->db->query("SELECT * FROM r_orders_products WHERE orders_id = '" . (int)$order_id . "'");
			$order_product_query_rows=$order_product_query->fetchAll();
			foreach ($order_product_query_rows as $order_product) {
				$this->db->query("UPDATE r_products SET products_quantity = (products_quantity - " . (int)$order_product['products_quantity'] . ") WHERE products_id = '" . (int)$order_product['products_id'] . "' AND substract_stock = '1'");

				$order_option_query = $this->db->query("SELECT * FROM r_orders_products_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$order_product['orders_products_id'] . "'");
				$order_option_query_rows=$order_option_query->fetchAll();
				foreach ($order_option_query_rows as $option) {
					$this->db->query("UPDATE r_products_option_value SET quantity = (quantity - " . (int)$order_product['products_quantity'] . ") WHERE product_option_value_id = '" . (int)$option['product_option_value_id'] . "' AND subtract = '1'");
				}
			}

			//$this->cache->delete('product');

			$order_total_query = $this->db->query("SELECT * FROM `r_orders_total` WHERE orders_id = '" . (int)$order_id . "'");
			$order_total_query_rows=$order_total_query->fetchAll();
			foreach ($order_total_query_rows as $order_total) {
				//$this->load->model('total/' . $order_total['class']);
				$otclass="Model_OrderTotal_Ot".ucfirst($order_total['class']);
				$oTObj=new $otclass;
				/*if (method_exists($this->{'model_total_' . $order_total['code']}, 'confirm')) {
					$this->{'model_total_' . $order_total['code']}->confirm($order_info, $order_total);
				}*/

				if (method_exists($oTObj, 'confirm')) {
					$oTObj->confirm($order_info, $order_total);
				}
			}

			// Send out any gift voucher mails
			if (ORDER_COMPLETE_STATUS_ID == $order_status_id) {
				$this->load->model('checkout/voucher');
				$chkVouObj=new Model_CheckoutVoucher();
				$chkVouObj->confirm($order_id);
			}

			// Send out order confirmation mail

			$order_status_query = $this->db->query("SELECT * FROM r_orders_status WHERE orders_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");
			$order_status_query_row=$order_status_query->fetch();
			if ($order_status_query->rowCount()) {
				$order_status = $order_status_query_row['orders_status_name'];
			} else {
				$order_status = '';
			}

			$order_product_query = $this->db->query("SELECT * FROM r_orders_products WHERE orders_id = '" . (int)$order_id . "'");
			$order_total_query = $this->db->query("SELECT * FROM r_orders_total WHERE orders_id = '" . (int)$order_id . "' ORDER BY sort_order ASC");
			$order_download_query = $this->db->query("SELECT * FROM r_orders_products_download WHERE orders_id = '" . (int)$order_id . "'");

			$efObj=new Model_DbTable_rEmailFormat();
			$template=$efObj->getEmailFormat('3',$_SESSION['Lang']['language_id']);

			$subject = sprintf($template['subject'], $order_info['store_name'], $order_id);


			// HTML Mail

			$title = sprintf($template['subject'], html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'	), $order_id);

			//$template->data['text_greeting'] = sprintf($language->get('text_new_greeting'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'));

			$logo = HTTP_SERVER.'public/uploads/image/'.STORE_LOGO;
			$store_name = $order_info['store_name'];
			$store_url = $order_info['store_url'];
			$customer_id = $order_info['customer_id'];
			$link = HTTP_SERVER.'account/orderinfo/order_id/' . $order_id;

			if ($order_download_query->rowCount()) {
				$download = HTTP_SERVER.'account/download';//$order_info['store_url'] . 'index.php?route=account/download';
			} else {
				$download = '';
			}

			$invoice_no = $invoice_no;
			$order_id = $order_id;
			/*ECHO "<pre>";
			print_r($_SESSION['TObj']);
			ECHO "</pre>";*/
			//echo $_SESSION['TObj']->_('date_format_short');
			//EXIT;
			$date_added = date($_SESSION['TObj']->_('date_format_short'), strtotime($order_info['date_added']));
			$payment_method = $order_info['payment_method'];
			$shipping_method = $order_info['shipping_method'];
			$email = $order_info['email'];
			$telephone = $order_info['telephone'];
			$ip = $order_info['ip'];

			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_name'],
				'lastname'  => '',
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

			$shipping_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_name'],
				'lastname'  => '',
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

			$payment_address = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$products = array();

			$order_product_query_rows=$order_product_query->fetchAll();
			foreach ($order_product_query_rows as $product) {
				$option_data = array();

				$order_option_query = $this->db->query("SELECT * FROM r_orders_products_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['orders_products_id'] . "'");
				//echo "SELECT * FROM r_orders_products_option WHERE order_id = '" . (int)$order_id . "' AND order_product_id = '" . (int)$product['order_product_id'] . "'<br/>";
				$order_option_query_rows=$order_option_query->fetchAll();
				foreach ($order_option_query_rows as $option) {
					if ($option['type'] != 'file') {
						$option_data[] = array(
							'name'  => $option['name'],
							'value' => (strlen($option['value']) > 20 ? substr($option['value'], 0, 20) . '..' : $option['value'])
						);
					} else {
						$filename = substr($option['value'], 0, strrpos($option['value'], '.'));

						$option_data[] = array(
							'name'  => $option['name'],
							'value' => (strlen($filename) > 20 ? substr($filename, 0, 20) . '..' : $filename)
						);
					}
				}


				//echo $product['products_price']." ".$order_info['currency_code']." ".$order_info['currency_value']."<br/>";
				$products[] = array(
					'name'     => $product['products_name'],
					'model'    => $product['products_model'],
					'option'   => $option_data,
					'quantity' => $product['products_quantity'],
					'price'    => $currObj->format($product['products_price'], true, $order_info['currency_code'], $order_info['currency_value']),
					'total'    =>$currObj->format($product['final_price'], true, $order_info['currency_code'], $order_info['currency_value'])

				);
			}

			//$template->data['totals'] = $order_total_query->rows;
			$totals = $order_total_query_rows;

			/*if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/mail/order.tpl')) {
				$html = $template->fetch($this->config->get('config_template') . '/template/mail/order.tpl');
			} else {
				$html = $template->fetch(SITE_DEFAULT_TEMPLATE.'/template/mail/order.tpl');
			}*/


			ob_start();
			//include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/mail/order-'.$_SESSION['Lang']['language_code'].'.phtml';
			if (file_exists(PATH_TO_FILES.'mail/order-'.$_SESSION['Lang']['language_code'].'.phtml'))
			{
				include_once PATH_TO_FILES.'mail/order-'.$_SESSION['Lang']['language_code'].'.phtml';
			}else    
            if (file_exists(PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/mail/order-'.$_SESSION['Lang']['language_code'].'.phtml'))
			{
                 include_once PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/mail/order-'.$_SESSION['Lang']['language_code'].'.phtml';
			} else
			{
                 include_once PATH_TO_TEMPLATES.'/'.DEMO_TEMPALTE.'/mail/order-'.$_SESSION['Lang']['language_code'].'.phtml';
			}
                        
			$html = ob_get_contents();
			ob_end_clean();
			//print_r($template);
			echo $html;
			//exit;
                        $act_ext=new Model_Adminextaction();
			$email=$act_ext->getEmailContent(array('lang'=>$_SESSION['Lang']['language_id'],'id'=>'3'));
                        //start conversion
                        $html=str_replace('%order_id%',$order_id,$html);
                        $subject=str_replace('%order_id%',$order_id,$email['subject']);

                        $html=str_replace('%order_status%',$order_status,$html);
                        $subject=str_replace('%order_status%',$order_status,$subject);

                        $html=str_replace('%customer_name%',$order_info['customers_name'],$html);
                        $subject=str_replace('%customer_name%',$order_info['customers_name'],$subject);
                        //end conversion
                                                    
			$array_mail=array('to'=>array('name'=>$order_info['customers_name'],'email'=>trim($order_info['email'])),'html'=>array('content'=>$html),'subject'=>$subject);
			$mailObj=new Model_Mail();
			$mailObj->sendMail($array_mail);

		 		// Admin Alert Mail
				if (@constant('EMAIL_NEW_ORDER_ALERT')=="true") 
				{
					$emails = explode(',',@constant('SEND_EXTRA_EMAILS_TO'));
					foreach ($emails as $email)
					{
						if (strlen($email) > 0 && preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i', $email)) {
						$users['bcc'][]=array("name"=>@constant('STORE_OWNER'),"email"=>$email);
						}
					}
					$array_mail=array('to'=>array('name'=>STORE_OWNER,'email'=>STORE_OWNER_EMAIL_ADDRESS),'html'=>array('content'=>$html),'subject'=>$subject,'bcc'=>$users['bcc']);
					$mailObj->sendMail($array_mail);
				}
			}
			//exit;
			Model_Cache::removeAllCache();
                        //exit;
		}



	public function update($order_id, $order_status_id, $comment = '', $notify = false) {
		$order_info = $this->getOrder($order_id);

		if ($order_info && $order_info['order_status_id']) {
			$this->db->query("UPDATE `r_order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = '".date('Y-m-d H:i:s')."' WHERE order_id = '" . (int)$order_id . "'");

			$this->db->query("INSERT INTO r_order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '" . (int)$notify . "', comment = '" . addslashes($comment) . "', date_added = '".date('Y-m-d H:i:s')."'");

			// Send out any gift voucher mails
			if ($this->config->get('config_complete_status_id') == $order_status_id) {
				$this->load->model('checkout/voucher');

				$this->model_checkout_voucher->confirm($order_id);
			}

			if ($notify) {
				$language = new Language($order_info['language_directory']);
				$language->load($order_info['language_filename']);
				$language->load('mail/order');

				$subject = sprintf($language->get('text_update_subject'), html_entity_decode($order_info['store_name'], ENT_QUOTES, 'UTF-8'), $order_id);

				$message  = $language->get('text_update_order') . ' ' . $order_id . "\n";
				$message .= $language->get('text_update_date_added') . ' ' . date($language->get('date_format_short'), strtotime($order_info['date_added'])) . "\n\n";

				$order_status_query = $this->db->query("SELECT * FROM r_order_status WHERE order_status_id = '" . (int)$order_status_id . "' AND language_id = '" . (int)$order_info['language_id'] . "'");

				if ($order_status_query->num_rows) {
					$message .= $language->get('text_update_order_status') . "\n\n";
					$message .= $order_status_query->row['name'] . "\n\n";
				}

				if ($order_info['customer_id']) {
					$message .= $language->get('text_update_link') . "\n";
					$message .= $order_info['store_url'] . 'account/orderinfo/order_id/' . $order_id . "\n\n";
				}

				if ($comment) {
					$message .= $language->get('text_update_comment') . "\n\n";
					$message .= $comment . "\n\n";
				}

				$message .= $language->get('text_update_footer');

				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->hostname = $this->config->get('config_smtp_host');
				$mail->username = $this->config->get('config_smtp_username');
				$mail->password = $this->config->get('config_smtp_password');
				$mail->port = $this->config->get('config_smtp_port');
				$mail->timeout = $this->config->get('config_smtp_timeout');
				$mail->setTo($order_info['email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($order_info['store_name']);
				$mail->setSubject($subject);
				$mail->setText(html_entity_decode($message, ENT_QUOTES, 'UTF-8'));
				$mail->send();
			}
		}
	}
}
?>