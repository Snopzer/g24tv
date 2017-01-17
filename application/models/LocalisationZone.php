<?php
class Model_LocalisationZone {
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
	}
	public function getZone($zone_id) {
	$zone=Model_Cache::getCache(array("id"=>"zone_".$zone_id));
		if(!$zone)
		{
			$query = $this->db->query("SELECT * FROM r_zones WHERE zone_id = '" . (int)$zone_id . "' AND status = '1'");
			$zone=$query->fetch();
			Model_Cache::getCache(array("id"=>"zone_".$zone_id,"input"=>$zone));
		}
		return $zone;
	}

	public function getZonesByCountryId($country_id) {
	
		$zone_data=Model_Cache::getCache(array("id"=>"zone_".$country_id));

		if (!$zone_data) {
			$query = $this->db->query("SELECT * FROM r_zones WHERE zone_country_id = '" . (int)$country_id . "' AND status = '1' ORDER BY zone_name");

			$zone_data = $query->fetchAll();
			Model_Cache::getCache(array("id"=>"zone_".$country_id,"input"=>$zone_data));
		}

		return $zone_data;
	}
}
?>