<?php
class Model_LocalisationCountry {

	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
	}

	public function getCountry($country_id) {
		$country=Model_Cache::getCache(array("id"=>"country_".$country_id));
		if(!$country)
		{

			$query = $this->db->query("SELECT * FROM r_countries WHERE countries_id = '" . (int)$country_id . "' AND status = '1'");
			$country=$query->fetch();
			Model_Cache::getCache(array("id"=>"country_".$country_id,"input"=>$country));
		}
		return $country;
	}

	public function getCountries() {
		$country_data=Model_Cache::getCache(array("id"=>"country"));
		if (!$country_data)
		{
			$query = $this->db->query("SELECT * FROM r_countries WHERE status = '1' ORDER BY countries_name ASC");
				$country_data = $query->fetchAll();
			Model_Cache::getCache(array("id"=>"country","input"=>$country_data));
		}
		return $country_data;
	}
}
?>