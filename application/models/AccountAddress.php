<?php
class Model_AccountAddress{

	public $_arrObj=array();
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$this->_arrObj['custObj']=new Model_Customer();
		$this->_arrObj['catObj']=new Model_Categories();
	}
	public function addAddress($data) {
		$this->db->query("INSERT INTO r_address_book SET customers_id = '" . (int)$this->_arrObj['custObj']->getId() . "', entry_company = '" . stripslashes($data['company']) . "', entry_firstname = '" . stripslashes($data['firstname']) . "', entry_lastname = '" . stripslashes($data['lastname']) . "', entry_street_address = '" . stripslashes($data['address_1']) . "', entry_suburb = '" . stripslashes($data['address_2']) . "', entry_postcode = '" . stripslashes($data['postcode']) . "', entry_city = '" . stripslashes($data['city']) . "', entry_zone_id = '" . (int)$data['zone_id'] . "', entry_country_id = '" . (int)$data['country_id'] . "'");

		$address_id = $this->db->lastInsertId();


		if (isset($data['default']) && $data['default'] == '1') {
			$this->db->query("UPDATE r_customers SET customers_default_address_id = '" . (int)$address_id . "' WHERE customers_id = '" . (int)$this->_arrObj['custObj']->getId() . "'");
		}

		return $address_id;
	}

	public function editAddress($address_id, $data) {
		$this->db->query("UPDATE r_address_book SET entry_company = '" . stripslashes($data['company']) . "', entry_firstname = '" . stripslashes($data['firstname']) . "', entry_lastname = '" . stripslashes($data['lastname']) . "', entry_street_address = '" . stripslashes($data['address_1']) . "', entry_suburb = '" . stripslashes($data['address_2']) . "', entry_postcode = '" . stripslashes($data['postcode']) . "', entry_city = '" . stripslashes($data['city']) . "', entry_zone_id = '" . (int)$data['zone_id'] . "', entry_country_id = '" . (int)$data['country_id'] . "' WHERE address_book_id  = '" . (int)$address_id . "' AND customers_id = '" . (int)$this->_arrObj['custObj']->getId() . "'");

		if (isset($data['default']) && $data['default'] == '1') {
			$this->db->query("UPDATE r_customers SET customers_default_address_id = '" . (int)$address_id . "' WHERE customers_id = '" . (int)$this->_arrObj['custObj']->getId() . "'");
		}

	}

	public function deleteAddress($address_id) {
		$this->db->query("DELETE FROM r_address_book WHERE address_book_id = '" . (int)$address_id . "' AND customers_id = '" . (int)$this->_arrObj['custObj']->getId() . "'");
	}

	public function getAddress($address_id) {

		if($_SESSION['admin_id']=="") //for customers
		{
			$address_query = $this->db->query("SELECT DISTINCT * FROM r_address_book WHERE address_book_id = '" . (int)$address_id . "' AND customers_id = '" . (int)$this->_arrObj['custObj']->getId() . "'");
		}else //for admin
		{
			$address_query = $this->db->query("SELECT DISTINCT * FROM r_address_book WHERE address_book_id = '" . (int)$address_id . "'");
		}	

		$address_query_row=$address_query->fetch();
		if ($address_query->rowCount()) {
			$country_query = $this->db->query("SELECT * FROM `r_countries` WHERE countries_id = '" . (int)$address_query_row['entry_country_id'] . "'");
			$country_query_row=$country_query->fetch();
			if ($country_query->rowCount()) {
				$country = $country_query_row['countries_name'];
				$iso_code_2 = $country_query_row['countries_iso_code_2'];
				$iso_code_3 = $country_query_row['countries_iso_code_3'];
				$address_format = $country_query_row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `r_zones` WHERE zone_id = '" . (int)$address_query_row['entry_zone_id'] . "'");
			$zone_query_row=$zone_query->fetch();
			if ($zone_query->rowCount()) {
				$zone = $zone_query_row['zone_name'];
				$code = $zone_query_row['zone_code'];
			} else {
				$zone = '';
				$code = '';
			}

			$address_data = array(
				'firstname'      => $address_query_row['entry_firstname'],
				'lastname'       => $address_query_row['entry_lastname'],
				'company'        => $address_query_row['entry_company'],
				'address_1'      => $address_query_row['entry_street_address'],
				'address_2'      => $address_query_row['entry_suburb'],
				'postcode'       => $address_query_row['entry_postcode'],
				'city'     => $address_query_row['entry_city'],
				'zone_id'        => $address_query_row['entry_zone_id'],
				'zone'           => $zone,
				'zone_code'      => $code,
				'country_id'     => $address_query_row['entry_country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);


			return $address_data;
		} else {
			return false;
		}
	}

	public function getAddresses() {
		$address_data = array();
		$query = $this->db->query("SELECT * FROM r_address_book WHERE customers_id = '" . (int)$this->_arrObj['custObj']->getId() . "'");

		foreach ($query->fetchAll() as $result) {
			$country_query = $this->db->query("SELECT * FROM `r_countries` WHERE countries_id = '" . (int)$result['entry_country_id'] . "'");
			$country_query->row=$country_query->fetch();
			if ($country_query->rowCount()) {
				$country = $country_query->row['countries_name'];
				$iso_code_2 = $country_query->row['countries_iso_code_2'];
				$iso_code_3 = $country_query->row['countries_iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `r_zones` WHERE zone_id = '" . (int)$result['entry_zone_id'] . "'");
			$zone_query->row=$zone_query->fetch();
			if ($zone_query->rowCount()) {
				$zone = $zone_query->row['zone_name'];
				$code = $zone_query->row['zone_code'];
			} else {
				$zone = '';
				$code = '';
			}

			$address_data[] = array(
				'address_id'     => $result['address_book_id'],
				'firstname'      => $result['entry_firstname'],
				'lastname'       => $result['entry_lastname'],
				'company'        => $result['entry_company'],
				'address_1'      => $result['entry_street_address'],
				'address_2'      => $result['entry_suburb'],
				'postcode'       => $result['entry_postcode'],
				'entry_city'           => $result['entry_city'],
				'zone_id'        => $result['entry_zone_id'],
				'zone'           => $zone,
				'zone_code'      => $code,
				'country_id'     => $result['entry_country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);
		}
		return $address_data;
	}

	public function getOrderAddresses($id) {
		$address_data = array();

		$query = $this->db->query("SELECT * FROM r_address_book WHERE customers_id = '" . (int)$id. "'");

		foreach ($query->fetchAll() as $result) {
			$country_query = $this->db->query("SELECT * FROM `r_countries` WHERE countries_id = '" . (int)$result['entry_country_id'] . "'");
			$country_query->row=$country_query->fetch();
			if ($country_query->rowCount()) {
				$country = $country_query->row['countries_name'];
				$iso_code_2 = $country_query->row['countries_iso_code_2'];
				$iso_code_3 = $country_query->row['countries_iso_code_3'];
				$address_format = $country_query->row['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->query("SELECT * FROM `r_zones` WHERE zone_id = '" . (int)$result['entry_zone_id'] . "'");
			$zone_query->row=$zone_query->fetch();
			if ($zone_query->rowCount()) {
				$zone = $zone_query->row['zone_name'];
				$code = $zone_query->row['zone_code'];
			} else {
				$zone = '';
				$code = '';
			}

			$address_data[] = array(
				'address_id'     => $result['address_book_id'],
				'firstname'      => $result['entry_firstname'],
				'lastname'       => $result['entry_lastname'],
				'company'        => $result['entry_company'],
				'address_1'      => $result['entry_street_address'],
				'address_2'      => $result['entry_suburb'],
				'postcode'       => $result['entry_postcode'],
				'entry_city'           => $result['entry_city'],
				'zone_id'        => $result['entry_zone_id'],
				'zone'           => $zone,
				'zone_code'      => $code,
				'country_id'     => $result['entry_country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format
			);
		}
		return $address_data;
	}

	public function getTotalAddresses() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM r_address_book WHERE customers_id = '" . (int)$this->_arrObj['custObj']->getId() . "'");


		//return $query->row['total'];
		return $query->fetchColumn(0);
	}
}
?>