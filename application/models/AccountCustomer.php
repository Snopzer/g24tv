<?php
class Model_AccountCustomer {
	  	public function __construct() {
		$this->db = Zend_Db_Table::getDefaultAdapter();
		}
	public function addCustomer($data) {
		$password=Model_Adminextaction::setEncryptPassword($data[password]);
      	$this->db->query("INSERT INTO r_customers SET  customers_firstname = '" . stripslashes($data['firstname']) . "', customers_lastname = '" . stripslashes($data['lastname']) . "', customers_email_address = '" . stripslashes($data['email']) . "', customers_telephone = '" . stripslashes($data['telephone']) . "', customers_fax = '" . stripslashes($data['fax']) . "', customers_password = '" . stripslashes($password) . "', customers_newsletter = '" . (isset($data['newsletter']) ? (int)$data['newsletter'] : 0) . "', customer_group_id = '" . (int)DEFAULT_CGROUP . "', customers_status = '1', customers_date_account_created = '".date('Y-m-d H:i:s')."'");

		$customer_id = $this->db->lastInsertId();

      	$this->db->query("INSERT INTO r_address_book SET customers_id = '" . (int)$customer_id . "', entry_firstname = '" . stripslashes($data['firstname']) . "', entry_lastname = '" . stripslashes($data['lastname']) . "', entry_company = '" . stripslashes($data['company']) . "', entry_street_address = '" . stripslashes($data['address_1']) . "', entry_suburb = '" . stripslashes($data['address_2']) . "', entry_city = '" . stripslashes($data['city']) . "', entry_postcode = '" . stripslashes($data['postcode']) . "', entry_country_id = '" . (int)$data['country_id'] . "', entry_zone_id = '" . (int)$data['zone_id'] . "'");

	$address_id = $this->db->lastInsertId();

      	$this->db->query("UPDATE r_customers SET customers_default_address_id = '" . (int)$address_id . "' WHERE customers_id = '" . (int)$customer_id . "'");

		if (!APPROVE_NEW_CUSTOMER) {
			$this->db->query("UPDATE r_customers SET customers_approved = '1' WHERE customers_id = '" . (int)$customer_id . "'");
		}
		/*start email*/
		$mailObj=new Model_Mail();
		if(@constant('APPROVE_NEW_CUSTOMER')=='true') //Account Registration Before Approval
		{
			$replace_array=array('%customer_name%'=>$data['firstname']." ".$data['lastname']);
			$return=$mailObj->getEmailContent(array('lang'=>$_SESSION['Lang']['language_id'],'id'=>'1','replace'=>$replace_array));	//request approval
			
		}else //Account Registration General
		{
			$replace_array=array('%customer_name%'=>$data['firstname']." ".$data['lastname']);
			$return=$mailObj->getEmailContent(array('lang'=>$_SESSION['Lang']['language_id'],'id'=>'14','replace'=>$replace_array)); 	
		}
	
		/*start additional alert emails*/
		if(@constant('EMAIL_NEW_ACCOUNT_ALERT')=='true')
		{
			$alert_replace_array=array('%customer_name%'=>$data['firstname']." ".$data['lastname'],'%customer_email%'=>$data['email']);
			$alert_return=$mailObj->getEmailContent(array('lang'=>$_SESSION['Lang']['language_id'],'id'=>'15','replace'=>$alert_replace_array));

			// Send to additional alert emails if new account email is enabled
			$emails = explode(',', SEND_EXTRA_EMAILS_TO);
			foreach ($emails as $email) 
			{
				if (strlen($email) > 0 && preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i', $email)) {
				$users['bcc'][]=array("name"=>STORE_NAME,"email"=>$email);
				}
			}
					
			$alert_array_mail=array('to'=>array('name'=>STORE_OWNER,'email'=>STORE_OWNER_EMAIL_ADDRESS),'html'=>array('content'=>$alert_return['content']),'subject'=>$alert_return['subject'],"bcc"=>$users['bcc']);

			$mailObj->sendMail($alert_array_mail);
		}
		/*end additonal alert emails*/

		//$array_mail=array('to'=>array('name'=>$data['firstname']." ".$data['lastname'],'email'=>trim($data['email'])),'html'=>array('content'=>$return['content']),'subject'=>$return['subject'],"bcc"=>$users['bcc']);//dec 16 2012

		$array_mail=array('to'=>array('name'=>$data['firstname']." ".$data['lastname'],'email'=>trim($data['email'])),'html'=>array('content'=>$return['content']),'subject'=>$return['subject']);

		$mailObj->sendMail($array_mail);
		/*end email*/
		 
	}

	public function editCustomer($data) {
		$custObj=new Model_Customer();
		$this->db->query("UPDATE r_customers SET customers_firstname = '" . stripslashes($data['firstname']) . "', customers_lastname = '" . stripslashes($data['lastname']) . "', customers_email_address = '" . stripslashes($data['email']) . "', customers_telephone = '" . stripslashes($data['telephone']) . "', customers_fax = '" . stripslashes($data['fax']) . "' WHERE customers_id = '" . (int)$custObj->getId() . "'");
	}

	public function editPassword($email, $password) {
		//echo "vlaue of ".$password;
		$password=Model_Adminextaction::setEncryptPassword($password);
      	$this->db->query("UPDATE r_customers SET customers_password = '" . stripslashes($password) . "' WHERE customers_email_address = '" . stripslashes($email) . "'");
		/*echo "UPDATE r_customers SET customers_password = '" . stripslashes($password) . "' WHERE customers_email_address = '" . stripslashes($email) . "'";
		exit;*/
	}

	public function editNewsletter($newsletter) {
		$custObj=new Model_Customer();
		$this->db->query("UPDATE r_customers SET customers_newsletter = '" . (int)$newsletter . "' WHERE customers_id = '" . (int)$custObj->getId() . "'");
	}

	public function getCustomer($customer_id) {
		$query = $this->db->query("SELECT * FROM r_customers WHERE customers_id = '" . (int)$customer_id . "'");

		return $query->fetch();
	}

	public function getCustomers($data = array()) {
 		$sql = "SELECT *, CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS name, cg.name AS customer_group FROM r_customers c LEFT JOIN r_customer_group cg ON (c.customer_group_id = cg.customer_group_id) ";

		$implode = array();

		if (isset($data['filter_name']) && !is_null($data['filter_name'])) {
			$implode[] = "LCASE(CONCAT(c.customers_firstname, ' ', c.customers_lastname)) LIKE '" . stripslashes(strtolower($data['filter_name'])) . "%'";
		}

		if (isset($data['filter_email']) && !is_null($data['filter_email'])) {
			$implode[] = "c.customers_email_address = '" . stripslashes($data['filter_email']) . "'";
		}

		if (isset($data['filter_customer_group_id']) && !is_null($data['filter_customer_group_id'])) {
			$implode[] = "cg.customer_group_id = '" . stripslashes($data['filter_customer_group_id']) . "'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "c.customers_status = '" . (int)$data['filter_status'] . "'";
		}

		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "c.customers_approved = '" . (int)$data['filter_approved'] . "'";
		}

		if (isset($data['filter_ip']) && !is_null($data['filter_ip'])) {
			$implode[] = "c.customers_id IN (SELECT customer_id FROM r_customer_ip WHERE ip = '" . stripslashes($data['filter_ip']) . "')";
		}

		if (isset($data['filter_date_added']) && !is_null($data['filter_date_added'])) {
			$implode[] = "DATE(c.customers_date_account_created) = DATE('" . stripslashes($data['filter_date_added']) . "')";
		}

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'name',
			'c.customers_email_address',
			'customer_group',
			'c.customers_status',
			'c.ip',
			'c.customers_date_account_created'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
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
//echo "value of ".$sql;
//exit;
		$rows = $this->db->fetchAll($sql);

		return $rows;
	}

	public function getTotalCustomersByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM r_customers WHERE customers_email_address	 = '" . stripslashes($email) . "'");
		//print_r($query->fetchColumn(0));
		//exit;
		return $query->fetchColumn(0);
	}
}
?>