<?php
  final class Model_Customer {
	private $customer_id;
	private $firstname;
	private $lastname;
	private $email;
	private $telephone;
	private $fax;
	private $newsletter;
	private $customer_group_id;
	private $address_id;

  	public function __construct() {
		$this->db = Zend_Db_Table::getDefaultAdapter();
		if (isset($_SESSION['customer_id'])) {
			$customer_query = $this->db->query("SELECT * FROM r_customers WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "' AND customers_status = '1'");
			$row=$customer_query->fetch();
			if ($customer_query->rowCount()) {
				$this->customer_id = $row['customers_id'];
				$this->firstname = $row['customers_firstname'];
				$this->lastname = $row['customers_lastname'];
				$this->email = $row['customers_email_address'];
				$this->telephone = $row['customers_telephone'];
				$this->fax = $row['customers_fax'];
				$this->newsletter = $row['customers_newsletter'];
				$this->customer_group_id = $row['customer_group_id'];
				$this->address_id = $row['customers_default_address_id'];

      			$this->db->query("UPDATE r_customers SET cart = '" . stripslashes(isset($_SESSION['cart']) ? serialize($_SESSION['cart']) : '') . "', wishlist = '" . stripslashes(isset($_SESSION['wishlist']) ? serialize($_SESSION['wishlist']) : '') . "', ip = '" . stripslashes($_SERVER['REMOTE_ADDR']) . "' WHERE customers_id = '" . (int)$_SESSION['customer_id'] . "'");

				$query = $this->db->query("SELECT * FROM r_customer_ip WHERE customer_id = '" . (int)$_SESSION['customer_id'] . "' AND ip = '" . stripslashes($_SERVER['REMOTE_ADDR']) . "'");

				if (!$query->rowCount()) {
					$this->db->query("INSERT INTO r_customer_ip SET customer_id = '" . (int)$_SESSION['customer_id'] . "', ip = '" . stripslashes($_SERVER['REMOTE_ADDR']) . "', date_added = NOW()");
				}
			} else {
 				$this->logout();
			}
  		}
	}

  	public function login($email, $password) {
		//$adextObj=new Model_Adminextaction();
		$password=Model_Adminextaction::setEncryptPassword($password);
		if (!constant(APPROVE_NEW_CUSTOMER)) {

			$customer_query = $this->db->query("SELECT * FROM r_customers WHERE LOWER(customers_email_address) = '" . stripslashes(strtolower($email)) . "' AND customers_password = '" . stripslashes($password) . "' AND customers_status = '1'");
			/*echo "SELECT * FROM r_customers WHERE LOWER(customers_email_address) = '" . stripslashes(strtolower($email)) . "' AND customers_password = '" . stripslashes($password) . "' AND customers_status = '1'";
			exit;*/
		} else {
			$customer_query = $this->db->query("SELECT * FROM r_customers WHERE LOWER(customers_email_address) = '" . stripslashes(strtolower($email)) . "' AND customers_password = '" . stripslashes($password) . "' AND customers_status = '1' AND customers_approved = '1'");
			/*echo "SELECT * FROM r_customers WHERE LOWER(customers_email_address) = '" . stripslashes(strtolower($email)) . "' AND customers_password = '" . stripslashes($password) . "' AND customers_status = '1' AND customers_approved = '1'";
			exit;*/
		}
		$row=$customer_query->fetch();
		//echo "customersid".$row['customers_id'];
		/*echo "<pre>";
		print_r($customer_query);
		echo "</pre>";*/
 		if ($customer_query->rowCount()) {
			//echo "value of ".$row['customers_id'];
				$_SESSION['customer_id'] = $row['customers_id'];//$customer_query->row['customers_id'];
 			/*if (($customer_query->row['cart']) && (is_string($customer_query->row['cart']))) {
				$cart = unserialize($customer_query->row['cart']);*/
				if (($row['cart']) && (is_string($row['cart']))) {
				$cart = unserialize($row['cart']);

				foreach ($cart as $key => $value) {
					if (!array_key_exists($key, $_SESSION['cart'])) {
						$_SESSION['cart'][$key] = $value;
					} else {
						$_SESSION['cart'][$key] += $value;
					}
				}
			}

			//if (($customer_query->row['wishlist']) && (is_string($customer_query->row['wishlist']))) {
				if (($row['wishlist']) && (is_string($row['wishlist']))) {
				if (!isset($_SESSION['wishlist'])) {
					$_SESSION['wishlist'] = array();
				}

				$wishlist = unserialize($row['wishlist']);

				foreach ($wishlist as $product_id) {
					if (!in_array($product_id, $_SESSION['wishlist'])) {
						$_SESSION['wishlist'][] = $product_id;
					}
				}
			}

			$this->customer_id = $row['customers_id'];
			$this->firstname = $row['customers_firstname'];
			$this->lastname = $row['customers_lastname'];
			$this->email = $row['customers_email_address'];
			$this->telephone = $row['customers_telephone'];
			$this->fax = $row['customers_fax'];
			$this->newsletter = $row['customers_newsletter'];
			$this->customer_group_id = $row['customer_group_id'];
			$this->address_id = $row['customers_default_address_id'];

			$this->db->query("UPDATE r_customers SET ip = '" . stripslashes($_SERVER['REMOTE_ADDR']) . "' WHERE customers_id = '" . (int)$row['customers_id'] . "'");

	  		return true;
    	} else {
			return false;
    	}
   	}

  	public function logout() {
		unset($_SESSION['customer_id']);

		$this->customer_id = '';
		$this->firstname = '';
		$this->lastname = '';
		$this->email = '';
		$this->telephone = '';
		$this->fax = '';
		$this->newsletter = '';
		$this->customer_group_id = '';
		$this->address_id = '';

		session_destroy();
  	}

  	public function isLogged() {
  	return $this->customer_id;
  	}

  	public function getId() {
    	return $this->customer_id;
  	}

  	public function getFirstName() {
		return $this->firstname;
  	}

  	public function getLastName() {
		return $this->lastname;
  	}

  	public function getEmail() {
		return $this->email;
  	}

  	public function getTelephone() {
		return $this->telephone;
  	}

  	public function getFax() {
		return $this->fax;
  	}

  	public function getNewsletter() {
		return $this->newsletter;
  	}

  	public function getCustomerGroupId() {
		return $this->customer_group_id;
  	}

  	public function getAddressId() {
		return $this->address_id;
  	}

  	public function getBalance() {
		$query = $this->db->query("SELECT SUM(amount) AS total FROM r_customer_transaction WHERE customer_id = '" . (int)$this->customer_id . "'");

		//return $query->row['total'];
		return $query->fetchColumn(0);
  	}

  	public function getRewardPoints() {
		$query = $this->db->query("SELECT SUM(points) AS total FROM r_customer_reward WHERE customer_id = '" . (int)$this->customer_id . "'");

		//return $query->row['total'];
		return $query->fetchColumn(0);
  	}
}
?>