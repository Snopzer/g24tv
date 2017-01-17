<?php
class Model_Affiliate {
	public $view=null;
	public $db=null;
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$this->view=new Zend_View();

	}

	public function addAffiliate($data) {
		$data['password']=Model_Adminextaction::setEncryptPassword($data['password']);
      	$this->db->query("INSERT INTO r_affiliate SET firstname = '" . $this->view->escape($data['firstname']) . "', lastname = '" . $this->view->escape($data['lastname']) . "', email = '" . $this->view->escape($data['email']) . "', telephone = '" . $this->view->escape($data['telephone']) . "', fax = '" . $this->view->escape($data['fax']) . "', password = '" . $this->view->escape($data['password']) . "', company = '" . $this->view->escape($data['company']) . "', address_1 = '" . $this->view->escape($data['address_1']) . "', address_2 = '" . $this->view->escape($data['address_2']) . "', city = '" . $this->view->escape($data['city']) . "', postcode = '" . $this->view->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "', code = '" . $this->view->escape(uniqid()) . "', commission = '" . (float)AFFLIATE_COMMISSION . "', tax = '" . $this->view->escape($data['tax']) . "', payment = '" . $this->view->escape($data['payment']) . "', cheque = '" . $this->view->escape($data['cheque']) . "', paypal = '" . $this->view->escape($data['paypal']) . "', bank_name = '" . $this->view->escape($data['bank_name']) . "', bank_branch_number = '" . $this->view->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->view->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->view->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->view->escape($data['bank_account_number']) . "', status = '1', date_added = '".date('Y-m-d H:i:s')."'");

                /*start email*/
		$mailObj=new Model_Mail();
		$replace_array=array('%affiliate_name%'=>$data['firstname']." ".$data['lastname']);
		$email_content=$mailObj->getEmailContent(array('lang'=>$_SESSION['Lang']['language_id'],'id'=>'7','replace'=>$replace_array)); //general mail
		//echo "<pre>";
		if(@constant('EMAIL_NEW_ACCOUNT_ALERT')=='true')//if(EMAIL_NEW_ACCOUNT_ALERT==true)
		{
                    $alert_replace_array=array('%affiliate_name%'=>$data['firstname']." ".$data['lastname'],'%affiliate_email%'=>$data['email']);
                    $alert_return=$mailObj->getEmailContent(array('lang'=>$_SESSION['Lang']['language_id'],'id'=>'17','replace'=>$alert_replace_array));

                    // Send to additional alert emails if new account email is enabled
                    $emails = explode(',', SEND_EXTRA_EMAILS_TO);
                    foreach ($emails as $email) 
                    {
                      if (strlen($email) > 0 && preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i', $email))                              {
                        $users['bcc'][]=array("name"=>STORE_NAME,"email"=>$email);
                     }
                    }

                    $alert_array_mail=array('to'=>array('name'=>STORE_OWNER,'email'=>STORE_OWNER_EMAIL_ADDRESS),'html'=>array('content'=>$alert_return['content']),'subject'=>$alert_return['subject'],"bcc"=>$users['bcc']);
					//print_r($alert_array_mail);
                    $mailObj->sendMail($alert_array_mail);
        }

		$array_mail=array('to'=>array('name'=>$data['firstname']." ".$data['lastname'],'email'=>trim($data['email'])),'html'=>array('content'=>$email_content['content']),'subject'=>$email_content['subject']);
		//print_r($array_mail);
		//exit;
		$mailObj->sendMail($array_mail);
		/*end email*/
	}

	public function editAffiliate($data) {//include website on feb 22 2012
		$affinfoObj=new Model_Affinfo();
		$this->db->query("UPDATE r_affiliate SET firstname = '" . $this->view->escape($data['firstname']) . "', lastname = '" . $this->view->escape($data['lastname']) . "', email = '" . $this->view->escape($data['email']) . "', telephone = '" . $this->view->escape($data['telephone']) . "', fax = '" . $this->view->escape($data['fax']) . "', company = '" . $this->view->escape($data['company']) . "',website = '" . $this->view->escape($data['website']) . "', address_1 = '" . $this->view->escape($data['address_1']) . "', address_2 = '" . $this->view->escape($data['address_2']) . "', city = '" . $this->view->escape($data['city']) . "', postcode = '" . $this->view->escape($data['postcode']) . "', country_id = '" . (int)$data['country_id'] . "', zone_id = '" . (int)$data['zone_id'] . "' WHERE affiliate_id = '" . (int)$affinfoObj->getId() . "'");
	}

	public function editPayment($data) {
		$this->affiliate=new Model_Affinfo();
      	$this->db->query("UPDATE r_affiliate SET tax = '" . $this->view->escape($data['tax']) . "', payment = '" . $this->view->escape($data['payment']) . "', cheque = '" . $this->view->escape($data['cheque']) . "', paypal = '" . $this->view->escape($data['paypal']) . "', bank_name = '" . $this->view->escape($data['bank_name']) . "', bank_branch_number = '" . $this->view->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->view->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->view->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->view->escape($data['bank_account_number']) . "' WHERE affiliate_id = '" . (int)$this->affiliate->getId() . "'");
	}

	public function editPassword($email, $password) {
		$password=Model_Adminextaction::setEncryptPassword($password);

      	$this->db->query("UPDATE r_affiliate SET password = '" . $this->view->escape($password) . "' WHERE email = '" . $this->view->escape($email) . "'");
	}

	public function getAffiliate($affiliate_id) {
		$query = $this->db->query("SELECT * FROM r_affiliate WHERE affiliate_id = '" . (int)$affiliate_id . "'");

		//return $query->row;
		return $query->fetch();
	}

	public function getAffiliateByCode($code) {
		$query = $this->db->query("SELECT * FROM r_affiliate WHERE code = '" . $this->view->escape($code) . "'");

		//return $query->row;
		return $query->fetch();
	}

	public function getTotalAffiliatesByEmail($email) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM r_affiliate WHERE email = '" . $this->view->escape($email) . "'");

		//return $query->row['total'];
		return $query->fetchColumn(0);
	}


	public function getAffiliates($data = array()) {
		$sql = "SELECT *, CONCAT(a.firstname, ' ', a.lastname) AS name, (SELECT SUM(at.amount) FROM r_affiliate_transaction at WHERE at.affiliate_id = a.affiliate_id GROUP BY at.affiliate_id) AS balance FROM r_affiliate a";

		$implode = array();
		
		if (!empty($data['filter_name'])) {
			$implode[] = "LCASE(CONCAT(a.firstname, ' ', a.lastname)) LIKE '" . $this->view->escape(strtolower($data['filter_name'])) . "%'";
		}

		if (!empty($data['filter_email'])) {
			$implode[] = "a.email = '" . $this->view->escape($data['filter_email']) . "'";
		}
		
		if (!empty($data['filter_code'])) {
			$implode[] = "a.code = '" . $this->view->escape($data['filter_code']) . "'";
		}
					
		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$implode[] = "a.status = '" . (int)$data['filter_status'] . "'";
		}	
		
		if (isset($data['filter_approved']) && !is_null($data['filter_approved'])) {
			$implode[] = "a.approved = '" . (int)$data['filter_approved'] . "'";
		}		
		
		if (!empty($data['filter_date_added'])) {
			$implode[] = "DATE(a.date_added) = DATE('" . $this->view->escape($data['filter_date_added']) . "')";
		}
		
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
		
		$sort_data = array(
			'name',
			'a.email',
			'a.code',
			'a.status',
			'a.approved',
			'a.date_added'
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
 		$rows = $this->db->fetchAll($sql);
		
		return $rows;	
	}
}
?>