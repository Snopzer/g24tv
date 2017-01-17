<?php
class Model_Information {

	public function __construct() {
		$this->db = Zend_Db_Table::getDefaultAdapter();
		}

	public function getInformation($information_id) {
            $information_data=Model_Cache::getCache(array("id"=>"information_".$information_id));
            if(!$information_data)
            {
		$query = $this->db->query("SELECT DISTINCT * FROM r_cms i LEFT JOIN r_cms_description id ON (i.page_id = id.page_id)  WHERE i.page_id = '" . (int)$information_id . "' AND id.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'  AND i.status = '1'");
                $information_data=$query->fetch();
                Model_Cache::getCache(array("id"=>"information_".$information_id,"input"=>$information_data,"tags"=>array("information","general")));
            }    
            return $information_data;
	}

	public function getInformations() {
            $information_data=Model_Cache::getCache(array("id"=>"informations"));
            if(!$information_data)
            {
		$query = $this->db->query("SELECT * FROM r_cms i LEFT JOIN r_cms_description id ON (i.page_id = id.page_id)  WHERE id.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND i.status = '1' AND i.sort_order <> '-1' ORDER BY i.sort_order, LCASE(id.title) ASC");

		$information_data=$query->fetchAll();
                Model_Cache::getCache(array("id"=>"informations","input"=>$information_data,"tags"=>array("information","general")));
            }    
            return $information_data;
	}

	public function getContents() {

		$query = $this->db->query("SELECT * FROM r_cms i LEFT JOIN r_cms_description id ON (i.page_id = id.page_id)  WHERE id.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND i.status = '1' AND i.sort_order = '-1' ORDER BY i.sort_order, LCASE(id.title) ASC");

		$information_data=$query->fetchAll();
	    return $information_data;
	}

	public function getInformationLayoutId($information_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "information_to_layout WHERE information_id = '" . (int)$information_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return $this->config->get('config_layout_information');
		}
	}
}
?>