<?php
class Model_AdminModuleSetting  {
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
	}

	public function getSetting($group, $store_id = 0) {
		$data = array();

		$query = $this->db->query("SELECT * FROM r_setting WHERE `group` = '" . stripslashes($group) . "'");
		$query->rows=$query->fetchAll();
		foreach ($query->rows as $result) {
			$data[$result['key']] = $result['value'];
		}

		return $data;
	}

	public function editSetting($group, $data, $store_id = 0) {
		$this->db->query("DELETE FROM r_setting WHERE `group` = '" . stripslashes($group) . "'");

		foreach ($data as $key => $value) {
			$this->db->query("INSERT INTO r_setting SET  `group` = '" . stripslashes($group) . "', `key` = '" . stripslashes($key) . "', `value` = '" . stripslashes($value) . "'");
		}
	}

	public function deleteSetting($group, $store_id = 0) {
		$this->db->query("DELETE FROM r_setting WHERE `group` = '" . stripslashes($group) . "'");
	}

	public function getConstant($group)
	{
		//echo "select * from r_setting where group like '".$group."'";
		//exit;
		$con=$this->db->fetchAll("select * from r_setting where `group` like '".$group."'");
		foreach ($con as $results)
		{
			define($results['key'],$results['value']);
		}
	}

	public function getLayouts($data = array()) {
		$sql = "SELECT * FROM r_layout";

		$sort_data = array('name');

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

		$query = $this->db->query($sql);

		return $query->fetchAll();
	}
}
?>