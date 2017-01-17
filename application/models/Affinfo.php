<?php
final class Model_Affinfo {
	private $affiliate_id;
	private $firstname;
	private $lastname;
	private $email;
	private $telephone;
	private $fax;
	private $code;

  	public function __construct() {
				$this->db = Zend_Db_Table::getDefaultAdapter();

			if (isset($_SESSION['affiliate_id'])) {
			$affiliate_query = $this->db->query("SELECT * FROM r_affiliate WHERE affiliate_id = '" . (int)$_SESSION['affiliate_id'] . "' AND status = '1'");
				$affiliate_query->row=$affiliate_query->fetch();
			if ($affiliate_query->rowCount()) {
				$this->affiliate_id = $affiliate_query->row['affiliate_id'];
				$this->firstname = $affiliate_query->row['firstname'];
				$this->lastname = $affiliate_query->row['lastname'];
				$this->email = $affiliate_query->row['email'];
				$this->telephone = $affiliate_query->row['telephone'];
				$this->fax = $affiliate_query->row['fax'];
				$this->code = $affiliate_query->row['code'];

      			$this->db->query("UPDATE r_affiliate SET ip = '" . stripslashes($_SERVER['REMOTE_ADDR']) . "' WHERE affiliate_id = '" . (int)$_SESSION['affiliate_id'] . "'");
			} else {
				$this->logout();
			}
  		}
	}

  	public function login($email, $password) {
		$password=Model_Adminextaction::setEncryptPassword($password);
		$affiliate_query = $this->db->query("SELECT * FROM r_affiliate WHERE email = '" . stripslashes($email) . "' AND password = '" . stripslashes($password) . "' AND status = '1' AND approved = '1'");
		$affiliate_query->row=$affiliate_query->fetch();
		if ($affiliate_query->rowCount()) {
			$_SESSION['affiliate_id'] = $affiliate_query->row['affiliate_id'];

			$this->affiliate_id = $affiliate_query->row['affiliate_id'];
			$this->firstname = $affiliate_query->row['firstname'];
			$this->lastname = $affiliate_query->row['lastname'];
			$this->email = $affiliate_query->row['email'];
			$this->telephone = $affiliate_query->row['telephone'];
			$this->fax = $affiliate_query->row['fax'];
      		$this->code = $affiliate_query->row['code'];

	  		return true;
    	} else {
      		return false;
    	}
  	}

  	public function logout() {
		unset($_SESSION['affiliate_id']);

		$this->affiliate_id = '';
		$this->firstname = '';
		$this->lastname = '';
		$this->email = '';
		$this->telephone = '';
		$this->fax = '';

		session_destroy();
  	}

  	public function isLogged() {
    	return $this->affiliate_id;
  	}

  	public function getId() {
    	return $this->affiliate_id;
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

  	public function getCode() {
		return $this->code;
  	}
}
?>