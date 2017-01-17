<?php
class Model_Dbackup{
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
	}
	public function restore($sql) {
		ini_set("memory_limit","512M");
		ini_set("max_execution_time",1800);

		foreach (explode(";\n", $sql) as $sql) {
    		$sql = trim($sql);
    		
			if ($sql) {
      			$this->db->query($sql);
    		}
  		}
		
		Model_Cache::removeAllCache();
	}
	
	public function getTables() {
		$table_data = array();
		
		$query = $this->db->query("SHOW TABLES FROM `" . DB_DATABASE . "`");
		
		foreach ($query->rows as $result) {
			$table_data[] = $result['Tables_in_' . DB_DATABASE];
		}
		
		return $table_data;
	}
	
	public function backup() 
	{
		$products=array("r_products","r_products_description","r_products_images","r_products_option","r_products_option_value","r_products_specials","r_products_to_categories","r_product_attribute_group","r_product_discount","r_product_related","r_product_reward","r_product_tag","r_product_to_download","r_categories","r_categories_description","r_attribute","r_attribute_description","r_attribute_group","r_attribute_group_description","r_download","r_download_description","r_manufacturers","r_manufacturers_info","r_option","r_option_description","r_option_value","r_option_value_description","r_url_alias","r_reviews");

		$customers=array("r_address_book","r_address_format","r_customers","r_customer_group","r_customer_ip","r_customer_reward","r_customer_transaction");


		$offersaffiliates=array("r_coupon","r_coupon_history","r_coupon_product","r_voucher","r_voucher_history","r_voucher_theme","r_voucher_theme_description","r_affiliate","r_affiliate_transaction");

		$ordersreturns=array("r_orders","r_orders_products","r_orders_products_download","r_orders_products_option","r_orders_status_history","r_orders_total","r_return","r_return_action","r_return_history","r_return_product","r_return_reason","r_return_status");

		$admin=array("r_admin","r_admin_activity_log","r_admin_permissions","r_admin_roles");

		$configuration=array("r_banner","r_banners","r_banners_history","r_banner_image","r_banner_image_description","r_cms","r_cms_description","r_configuration","r_configuration_group","r_countries","r_currencies","r_email_format","r_extension","r_filters","r_geo_zones","r_languages","r_layout","r_length_class","r_length_class_description","r_orders_status","r_newsletter_template","r_search_keywords","r_setting","r_stock_status","r_tax_class","r_tax_rates","r_weight_class","r_weight_class_description","r_zones","r_zones_to_geo_zones");
		
		$database=array("products"=>$products,"customers"=>$customers,"offers_affiliates"=>$offersaffiliates,"orders_returns"=>$ordersreturns,"admin"=>$admin,"configuration"=>$configuration);
		$tabarray=array();
		//echo "<pre>";
		foreach($_REQUEST['tables'] as $a=>$b)
		{
			$tabarray=array_merge($database[$b],$tabarray);
		}
		//print_r($_REQUEST);
		//print_r($tabarray);

		//EXIT;
		/*$arrOptions = new Zend_Config_Ini(
    APPLICATION_PATH . '/configs/application.ini',
    APPLICATION_ENV
);

		$output = '';
		$tables=$this->db->fetchAll("show tables from ".$arrOptions->resources->db->params->dbname);
		//echo "<pre>";
		//print_r($tables);*/
		$tables=$tabarray;
		foreach ($tables as $k=>$v) 
			{
			//$table=$v['Tables_in_'.$arrOptions->resources->db->params->dbname];
			//echo "value of ".$table;
			//exit;
			/*if (DB_PREFIX) {
				if (strpos($table, DB_PREFIX) === FALSE) {
					$status = FALSE;
				} else {
					$status = TRUE;
				}
			} else {
				$status = TRUE;
			}*/
			$table=$v;
				$status = TRUE;
			if ($status) {
				//			echo "value of ".$status;
			//exit;
				$output .= 'TRUNCATE TABLE `' . $table . '`;' . "\n\n";
			//	echo $output;
			//exit;
				$query_rows = $this->db->fetchAll("SELECT * FROM `" . $table . "`");
				
				foreach ($query_rows as $result) {
					$fields = '';
					
					foreach (array_keys($result) as $value) {
						$fields .= '`' . $value . '`, ';
					}
					
					$values = '';
					
					foreach (array_values($result) as $value) {
						$value = str_replace(array("\x00", "\x0a", "\x0d", "\x1a"), array('\0', '\n', '\r', '\Z'), $value);
						$value = str_replace(array("\n", "\r", "\t"), array('\n', '\r', '\t'), $value);
						$value = str_replace('\\', '\\\\',	$value);
						$value = str_replace('\'', '\\\'',	$value);
						$value = str_replace('\\\n', '\n',	$value);
						$value = str_replace('\\\r', '\r',	$value);
						$value = str_replace('\\\t', '\t',	$value);			
						
						$values .= '\'' . $value . '\', ';
					}
					
					$output .= 'INSERT INTO `' . $table . '` (' . preg_replace('/, $/', '', $fields) . ') VALUES (' . preg_replace('/, $/', '', $values) . ');' . "\n";
				}
				
				$output .= "\n\n";
			}
		}

		return $output;	
	}
}
?>