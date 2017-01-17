<?php
class Model_Manufacturer {
	  public $_arrObj=array();
	public function __construct($lng='')
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
	}
	
	public function getManufacturer($manufacturer_id) {
		$query = $this->db->query("SELECT m.*,mi.manufacturers_url FROM r_manufacturers m,r_manufacturers_info mi  WHERE m.manufacturers_id = '" . (int)$manufacturer_id . "' and m.manufacturers_id=mi.manufacturers_id and mi.language_id='1'");
		$rows=$query->fetch();
		return $rows;
	}

	public function getManufacturersByCategory($data)
	{
		$query = $this->db->query("SELECT * FROM r_manufacturers m  WHERE m.manufacturers_id in (select p.manufacturers_id from r_products p where p.products_id in (select products_id from r_products_to_categories where categories_id='".$data[path]."'))");
		$rows=$query->fetchAll();
		return $rows;
	}

	public function getManufacturers($data = array()) {
		if ($data) {
		//print_r($data);
		//exit;
			$sql = "SELECT * FROM r_manufacturers m";

			$sort_data = array(
				'name',
				'sort_order'
			);

			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY manufacturers_name";
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
			$rows=$query->fetchAll();
			return $rows;
		} else {
			//$manufacturer_data = $this->cache->get('manufacturer.' . (int)$this->config->get('config_store_id'));

			if (!$manufacturer_data) {
				$query = $this->db->query("SELECT * FROM r_manufacturers m ORDER BY manufacturers_name");
				$rows=$query->fetchAll();
				$manufacturer_data = $rows;

				//$this->cache->set('manufacturer.' . (int)$this->config->get('config_store_id'), $manufacturer_data);
			}

			return $manufacturer_data;
		}
	}
}
?>