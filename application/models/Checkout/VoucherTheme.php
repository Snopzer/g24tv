<?php 
class Model_Checkout_VoucherTheme {
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
	}

	public function getVoucherTheme($voucher_theme_id) {
		$query = $this->db->query("SELECT * FROM r_voucher_theme vt LEFT JOIN r_voucher_theme_description vtd ON (vt.voucher_theme_id = vtd.voucher_theme_id) WHERE vt.voucher_theme_id = '" . (int)$voucher_theme_id . "' AND vtd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'");
		
		//return $query->row;
		return $query->fetch();
	}
		
	public function getVoucherThemes($data = array()) {
      	if ($data) {
			$sql = "SELECT * FROM r_voucher_theme vt LEFT JOIN r_voucher_theme_description vtd ON (vt.voucher_theme_id = vtd.voucher_theme_id) WHERE vtd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' ORDER BY vtd.name";	
			
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
			
			//return $query->rows;
			return $query->fetchAll();
		} else {
			//$voucher_theme_data = $this->cache->get('voucher_theme.' . $this->config->get('config_language_id'));
		
			if (!$voucher_theme_data) {
				$query = $this->db->query("SELECT * FROM r_voucher_theme vt LEFT JOIN r_voucher_theme_description vtd ON (vt.voucher_theme_id = vtd.voucher_theme_id) WHERE vtd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' ORDER BY vtd.name");
	
				//$voucher_theme_data = $query->rows;
				$voucher_theme_data = $query->fetchAll();
			
			//	$this->cache->set('voucher_theme.' . $this->config->get('config_language_id'), $voucher_theme_data);
			}	
	
			return $voucher_theme_data;				
		}
	}
}
?>