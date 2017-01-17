<?php
 
//static $config = NULL;
//static $log = NULL;

 //Error Handler
function error_handler_for_export($errno, $errstr, $errfile, $errline) {
	global $config;
	global $log;
	
	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$errors = "Notice";
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$errors = "Warning";
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$errors = "Fatal Error";
			break;
		default:
			$errors = "Unknown";
			break;
	}
		
	if (($errors=='Warning') || ($errors=='Unknown')) {
		return true;
	}

	echo '<b>' . $errors . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>';
	
	


	return true;
}


function fatal_error_shutdown_handler_for_export()
{
	$last_error = error_get_last();
	if ($last_error['type'] === E_ERROR) {
		// fatal error
		error_handler_for_export(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
	}
}

 
class Model_Export {
	public $db;
	public function Model_Export()
	{
			$this->db = Zend_Db_Table::getDefaultAdapter();
	}

	public function getArrayIds($type)
	{
		$array=array();
		$return_array=array();
		switch($type)
		{
			case 'category':
							$array=$this->db->fetchAll("select concat(categories_id,'#',language_id) as product_id from r_categories_description");
							break;
			case 'product':
							$array=$this->db->fetchAll("select concat(products_id,'#',language_id) as product_id from r_products_description");
							break;
			case 'option':
							$array=$this->db->fetchAll("select distinct product_id  from r_products_option");
							break;
			case 'option_value':
							$array=$this->db->fetchAll("select distinct product_id  from r_products_option_value");
							break;
			case 'attribute':
							$array=$this->db->fetchAll("select distinct concat(product_id,'#',language_id,'#',attribute_id) as product_id from r_product_attribute_group");
							break;
			case 'special':
							$array=$this->db->fetchAll("select concat(products_id,'#',customer_group_id) as product_id from r_products_specials");
							break;
			case 'additional_images':
							$array=$this->db->fetchAll("select distinct products_id as product_id from r_products_images");
							break;
			case 'discount':	
							$array=$this->db->fetchAll("select  concat(product_id,'#',customer_group_id) as product_id from r_product_discount");
							break;
			 case 'product_keyword':
							 $array=$this->db->fetchAll("select keyword as product_id from r_url_alias where query='product'");
							break;
			 case 'category_keyword':
							 $array=$this->db->fetchAll("select keyword as product_id from r_url_alias where query='category'");	
							break;
		}
		//echo "size of ".sizeof($array);
		if(sizeof($array)>0)
		{
			foreach($array as $k=>$v)
			{
				$return_array[]=$v['product_id'];
			}
		}
		//echo "<pre>";
		//print_r($return_array);
		return $return_array;
	}

	function clean( &$str, $allowBlanks=FALSE ) {
		$result = "";
		$n = strlen( $str );
		for ($m=0; $m<$n; $m++) {
			$ch = substr( $str, $m, 1 );
			if (($ch==" ") && (!$allowBlanks) || ($ch=="\n") || ($ch=="\r") || ($ch=="\t") || ($ch=="\0") || ($ch=="\x0B")) {
				continue;
			}
			$result .= $ch;
		}
		return $result;
	}


	function import( &$database, $sql ) {
		foreach (explode(";\n", $sql) as $sql) {
			$sql = trim($sql);
			if ($sql) {
				//$this->db->query($sql);
				$this->db->query($sql);
			}
		}
	}


	protected function getDefaultLanguageId( &$database ) {
		$code = @constant('DEFAULT_LANGUAGE');
		$sql = "SELECT languages_id FROM `r_languages` WHERE code = '$code'";
		/*$result = $this->db->query( $sql );
		$languageId = 1;
		if ($result->rows) {
			foreach ($result->rows as $row) {
				$languageId = $row['language_id'];
				break;
			}
		}*/

		$result=$this->db->fetchRow($sql);
 		return $result['languages_id'];
	}


	protected function getDefaultWeightUnit() {
		$weightUnit = @constant('DEFAULT_WEIGHT_CLASS');
		return $weightUnit;
	}


	protected function getDefaultMeasurementUnit() {
		$measurementUnit = @constant('DEFAULT_LENGTH_CLASS');
		return $measurementUnit;
	}



	function storeManufacturersIntoDatabase( &$database, &$products, &$manufacturerIds ) {
		// find all manufacturers already stored in the database
		$sql = "SELECT `manufacturers_id`, `manufacturers_name` FROM `r_manufacturers`";
		$result_rows = $this->db->fetchAll( $sql );
		if ($result_rows) {
			foreach ($result_rows as $row) {
				$manufacturerId = $row['manufacturers_id'];
				$name = $row['manufacturers_name'];
				if (!isset($manufacturerIds[$name])) {
					$manufacturerIds[$name] = $manufacturerId;
				} else if ($manufacturerIds[$name] < $manufacturerId) {
					$manufacturerIds[$name] = $manufacturerId;
				}
			}
		}

		// add newly introduced manufacturers to the database
		$maxManufacturerId=0;
		foreach ($manufacturerIds as $manufacturerId) {
			$maxManufacturerId = max( $maxManufacturerId, $manufacturerId );
		}
		$sql = "INSERT INTO `r_manufacturers` (`manufacturers_id`, `manufacturers_name`, `manufacturers_image`, `sort_order`) VALUES "; 
		$k = strlen( $sql );
		$first = TRUE;
		foreach ($products as $product) {
			$manufacturerName = $product['manufacturer'];
			if ($manufacturerName=="") {
				continue;
			}
			if (!isset($manufacturerIds[$manufacturerName])) {
				$maxManufacturerId += 1;
				$manufacturerId = $maxManufacturerId;
				$manufacturerIds[$manufacturerName] = $manufacturerId;
				$sql .= ($first) ? "\n" : ",\n";
				$first = FALSE;
				$sql .= "($manufacturerId, '".addslashes($manufacturerName)."', '', 0)";
			}
		}
		
		 
		if (strlen( $sql ) > $k+2) {
			$this->db->query( $sql );
		}
		
	 
		return TRUE;
	}


	function getWeightClassIds( &$database ) {
		// find the default language id
		$languageId = $this->getDefaultLanguageId($database);
		
		// find all weight classes already stored in the database
		$weightClassIds = array();
		$sql = "SELECT `weight_class_id`, `unit` FROM `r_weight_class_description` WHERE `language_id`=$languageId;";
		$result_rows = $this->db->fetchAll( $sql );
		if ($result_rows) {
			foreach ($result_rows as $row) {
				$weightClassId = $row['weight_class_id'];
				$unit = $row['unit'];
				if (!isset($weightClassIds[$unit])) {
					$weightClassIds[$unit] = $weightClassId;
				}
			}
		}

		return $weightClassIds;
	}


	function getLengthClassIds( &$database ) {
		// find the default language id
		$languageId = $this->getDefaultLanguageId($database);
		
		// find all length classes already stored in the database
		$lengthClassIds = array();
		$sql = "SELECT `length_class_id`, `unit` FROM `r_length_class_description` WHERE `language_id`=$languageId;";
		$result_rows = $this->db->fetchAll( $sql );
		if ($result_rows) {
			foreach ($result_rows as $row) {
				$lengthClassId = $row['length_class_id'];
				$unit = $row['unit'];
				if (!isset($lengthClassIds[$unit])) {
					$lengthClassIds[$unit] = $lengthClassId;
				}
			}
		}

		return $lengthClassIds;
	}


	 


	function storeProductsIntoDatabase( &$database, &$products ) 
	{
		$product_keyword_array=$this->getArrayIds("product_keyword");
		// find the default language id
		$languageId = "1";//$this->getDefaultLanguageId($database);
		
	 
		$manufacturerIds = array();
		$ok = $this->storeManufacturersIntoDatabase( $database, $products, $manufacturerIds );
		if (!$ok) {
			$this->db->query( 'ROLLBACK;' );
			return FALSE;
		}
		
		// get weight classes
		$weightClassIds = $this->getWeightClassIds( $database );
		
		// get length classes
		$lengthClassIds = $this->getLengthClassIds( $database );
		

		 
		foreach ($products as $product) {

			
			//for downloads
			if($product['downloads']!="")
			{
				$d=@explode(",",$product['downloads']);
				foreach($d as $k=>$v)
				{
					$drow=$this->db->fetchRow("select download_id from r_download_description  where lower(name) like  '".strtolower($v)."'");
					if($drow['download_id']!="")
					{
						$this->db->insert("r_product_to_download",array("product_id"=>$product['product_id'],"download_id"=>$drow['download_id']));
					}
				}
			}

			$productId = $product['product_id'];
			$productName = addslashes($product['name']);
			$categories = $product['categories'];
			$quantity = $product['quantity'];
			$model = addslashes($product['model']);
			$manufacturerName = $product['manufacturer'];
			$manufacturerId = ($manufacturerName=="") ? 0 : $manufacturerIds[$manufacturerName];
			$imageName = $product['image'];
			$shipping = $product['shipping'];
			$shipping = ((strtoupper($shipping)=="YES") || (strtoupper($shipping)=="Y") || (strtoupper($shipping)=="TRUE")) ? 1 : 0;
			$price = trim($product['price']);
			$points = $product['points'];
			$dateAdded = $product['date_added'];
			$dateModified = $product['date_modified'];
			$dateAvailable = $product['date_available'];
			$weight = ($product['weight']=="") ? 0 : $product['weight'];
			$unit = $product['unit'];
			$weightClassId = (isset($weightClassIds[$unit])) ? $weightClassIds[$unit] : 0;
			$status = $product['status'];
			$status = ((strtoupper($status)=="TRUE") || (strtoupper($status)=="YES") || (strtoupper($status)=="ENABLED")) ? 1 : 0;
			$taxClassId = $product['tax_class_id'];
			$viewed = $product['viewed'];
			$productDescription = addslashes($product['description']);
			$stockStatusId = $product['stock_status_id'];
			$meta_description = addslashes($product['meta_description']);
			$length = $product['length'];
			$width = $product['width'];
			$height = $product['height'];
			$keyword = addslashes($product['seo_keyword']);
			$lengthUnit = $product['measurement_unit'];
			$lengthClassId = (isset($lengthClassIds[$lengthUnit])) ? $lengthClassIds[$lengthUnit] : 0;
			$sku = addslashes($product['sku']);
			$upc = addslashes($product['upc']);
			//$location = addslashes($product['location']);
			//$storeIds = $product['store_ids'];
			//$layout = $product['layout'];
			$related = $product['related_ids'];
			$tags = array();
			foreach ($product['tags'] as $tag) {
				$tags[] = addslashes($tag);
			}
			$subtract = $product['subtract'];
			$subtract = ((strtoupper($subtract)=="TRUE") || (strtoupper($subtract)=="YES") || (strtoupper($subtract)=="ENABLED")) ? 1 : 0;
			$minimum = $product['minimum'];
			$meta_keywords = addslashes($product['meta_keywords']);
			$sort_order = $product['sort_order'];
			$sql  = "INSERT INTO `r_products` (`products_id`,`products_quantity`,`sku`,`upc`,";
			$sql .= "`stock_status_id`,`products_model`,`manufacturers_id`,`products_image`,`shipping`,`products_price`,`products_points`,`products_date_added`,`products_last_modified`,`products_date_available`,`products_weight`,`weight_class_id`,`products_status`,";
			$sql .= "`products_tax_class_id`,`viewed`,`length`,`width`,`height`,`length_class_id`,`sort_order`,`substract_stock`,`products_minimum_quantity`) VALUES ";
			$sql .= "($productId,$quantity,'$sku','$upc',";
			$sql .= "$stockStatusId,'$model',$manufacturerId,'$imageName',$shipping,$price,$points,";
			$sql .= ($dateAdded=='NOW()') ? "$dateAdded," : "'$dateAdded',";
			$sql .= ($dateModified=='NOW()') ? "$dateModified," : "'$dateModified',";
			$sql .= ($dateAvailable=='NOW()') ? "$dateAvailable," : "'$dateAvailable',";
			$sql .= "$weight,$weightClassId,$status,";
			$sql .= "$taxClassId,$viewed,$length,$width,$height,'$lengthClassId','$sort_order','$subtract','$minimum');";
			$this->db->query($sql);
			
		
			$this->db->insert('r_products_description',array("products_id"=>$productId,"language_id"=>$languageId,"products_name"=>$productName,"products_description"=>$productDescription,"meta_description"=>$meta_description,"meta_keywords"=>$meta_keywords));
		
			if (count($categories) > 0) {
				$sql = "INSERT INTO `r_products_to_categories` (`products_id`,`categories_id`) VALUES ";
				$first = TRUE;
				foreach ($categories as $categoryId) {
					$sql .= ($first) ? "\n" : ",\n";
					$first = FALSE;
					$sql .= "($productId,$categoryId)";
				}
				//$sql .= ";";
				$this->db->query($sql);
			}

 			/*if ($keyword) 
			{
				if(in_array($keyword,$product_keyword_array))
				{

					$_SESSION['EXCEL_ERROR_MESSAGE'][]="Products Tab:product id ".$productId." with seo keyword already exists.update the keyword manually from admin->catalog->products";
				}else
				{
					$sql4 = "INSERT INTO `r_url_alias` (`query`,`keyword`,`id`) VALUES ('product','$keyword','$productId')";
 					$this->db->query($sql4);
				}
			}*/
		
//exit;		
			if($keyword!="")
			{
				$sql4 = "INSERT INTO `r_url_alias` (`query`,`keyword`,`id`) VALUES ('product','$keyword','$productId')";
				$this->exeQuery(array("query"=>$sql4,"warning"=>"Products Tab:product id ".$productId." with seo keyword already exists.update the keyword manually from admin->catalog->products"));
			}
			if (count($related) > 0) {
				$sql = "INSERT INTO `r_product_related` (`product_id`,`related_id`) VALUES ";
				$first = TRUE;
				foreach ($related as $relatedId) {
					$sql .= ($first) ? "\n" : ",\n";
					$first = FALSE;
					$sql .= "($productId,$relatedId)";
				}
				//$sql .= ";";
				$this->db->query($sql);
			}

			if (count($tags) > 0) {
				$sql = "INSERT INTO `r_product_tag` (`products_id`,`tag`,`language_id`) VALUES ";
				$first = TRUE;
				$inserted_tags = array();
				foreach ($tags as $tag) {
					if ($tag == '') {
						continue;
					}
					if (in_array($tag,$inserted_tags)) {
						continue;
					}
					$sql .= ($first) ? "\n" : ",\n";
					$first = FALSE;
					$sql .= "($productId,'".addslashes($tag)."',$languageId)";
					$inserted_tags[] = $tag;
				}
				//$sql .= ";";
				if (count($inserted_tags)>0) {
					$this->db->query($sql);
				}
			} 

		}


	 //exit;
		// final commit
		$this->db->query("COMMIT;");
		return TRUE;
	}


	protected function detect_encoding( $str ) {
		// auto detect the character encoding of a string
		return mb_detect_encoding( $str, 'UTF-8,ISO-8859-15,ISO-8859-1,cp1251,KOI8-R' );
	}


	function uploadProducts( &$reader, &$database ) {
		// find the default language id and default units
		
		//product id already exits if lesser then
		//$row_max=$this->db->fetchRow("select max(products_id)+1 as max_product_id from r_products");
		
		$languageId = "1";//$this->getDefaultLanguageId($database);
		$defaultWeightUnit = $this->getDefaultWeightUnit();
		$defaultMeasurementUnit = $this->getDefaultMeasurementUnit();
		$defaultStockStatusId = @constant('DEFAULT_AVAILABILITY_STOCK_STATUS_ID');//$this->config->get('config_stock_status_id');
		//$productArray=$this->getArrayIds('product');
		$data = $reader->getSheet(1);
		$products = array();
		$product = array();
		$isFirstRow = TRUE;
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {

			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}

			$productId = trim($this->getCell($data,$i,1));
			/*if($productId<$row_max['max_product_id']) //insert only new records
			{
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Products Tab:product id ".$productId." ignored as this product id is smaller than ".$row_max['max_product_id'];
				continue;
			}*/

			//$langId = $this->getCell($data,$i,25,'1');			
			$langId = '1';			
			/*$search_key=$productId."#".$langId;
			if(in_array($search_key,$productArray)) //ignore duplicates
			{
				continue;
			}*/
 
			$name = $this->getCell($data,$i,2);
			$name = htmlentities( $name, ENT_QUOTES, $this->detect_encoding($name) );
			$categories = $this->getCell($data,$i,3);
			$quantity = $this->getCell($data,$i,4,'0');
			$minimum = $this->getCell($data,$i,5,'1');
			$model = $this->getCell($data,$i,8,'');
			if ($productId=="") {
					$_SESSION['EXCEL_ERROR_MESSAGE'][]="Products Tab:product ".$name." is ignored as this product id is missing id";
				continue;
			}
			//echo "Model : ".$model." <br/>";
			if ($model=="") {
				$model=$name;
				
			}

			/*if ($categories=="") { //commented on june 17 2013 as some products may not have categories
					$_SESSION['EXCEL_ERROR_MESSAGE'][]="Products Tab:product ".$productId." is ignored as this product id is missing category";
				continue;
			}*/

			if ($quantity=="") {
				$quantity="1";
					$_SESSION['EXCEL_ERROR_MESSAGE'][]="Products Tab:product ".$productId." quantity is missing.default quantity 1 has been taken";
				//continue;
			}
			
			if ($minimum=="") {
					$minimum=1;
			}
			
			$sku = $this->getCell($data,$i,24,'');
			$upc = $this->getCell($data,$i,25,'');
			//$location = $this->getCell($data,$i,6,'');
			
 			$manufacturer = $this->getCell($data,$i,9);
			$imageName = $this->getCell($data,$i,10);
			$shipping = $this->getCell($data,$i,21,'yes');
			$price = $this->getCell($data,$i,13,'0.00');
			$points = $this->getCell($data,$i,32,'0');
			$dateAdded = $this->getCell($data,$i,35);
			$dateAdded = ((is_string($dateAdded)) && (strlen($dateAdded)>0)) ? $dateAdded : "NOW()";
			$dateModified = $this->getCell($data,$i,36);
			$dateModified = ((is_string($dateModified)) && (strlen($dateModified)>0)) ? $dateModified : "NOW()";
			$dateAvailable = $this->getCell($data,$i,15);
			$dateAvailable = ((is_string($dateAvailable)) && (strlen($dateAvailable)>0)) ? $dateAvailable : "NOW()";
			$weight = $this->getCell($data,$i,26,'0');
			$unit = $this->getCell($data,$i,27,$defaultWeightUnit);
			$length = $this->getCell($data,$i,28,'0');
			$width = $this->getCell($data,$i,29,'0');
			$height = $this->getCell($data,$i,30,'0');
			$measurementUnit = $this->getCell($data,$i,31,$defaultMeasurementUnit);
			$status = $this->getCell($data,$i,16,'true');
			$taxClassId = $this->getCell($data,$i,19,'0');
			$viewed = $this->getCell($data,$i,37,'0');
			
			$keyword = $this->getCell($data,$i,6);
			$description = $this->getCell($data,$i,7);
			$description = htmlentities( $description, ENT_QUOTES, $this->detect_encoding($description) );
			$meta_description = $this->getCell($data,$i,34);
			$meta_description = htmlentities( $meta_description, ENT_QUOTES, $this->detect_encoding($meta_description) );
			$meta_keywords = $this->getCell($data,$i,33);
			$meta_keywords = htmlentities( $meta_keywords, ENT_QUOTES, $this->detect_encoding($meta_keywords) );
			$additionalImageNames = $this->getCell($data,$i,11);
			$stockStatusId = $this->getCell($data,$i,18,$defaultStockStatusId);
			$related = $this->getCell($data,$i,22);
			$tags = $this->getCell($data,$i,23);
			$sort_order = $this->getCell($data,$i,17,'0');
			$subtract = $this->getCell($data,$i,14,'true');
			$downloads=$this->getCell($data,$i,20);
			$product = array();
			$product['product_id'] = $productId;
			$product['name'] = $name;
			$categories = trim( $this->clean($categories, FALSE) );
			$product['categories'] = ($categories=="") ? array() : explode( ",", $categories );
			if ($product['categories']===FALSE) {
				$product['categories'] = array();
			}
			$product['quantity'] = $quantity;
			$product['model'] = $model;
			$product['manufacturer'] = $manufacturer;
			$product['image'] = $imageName;
			$product['shipping'] = $shipping;
			$product['price'] = $price;
			$product['points'] = $points;
			$product['date_added'] = $dateAdded;
			$product['date_modified'] = $dateModified;
			$product['date_available'] = $dateAvailable;
			$product['weight'] = $weight;
			$product['unit'] = $unit;
			$product['status'] = $status;
			$product['tax_class_id'] = $taxClassId;
			$product['viewed'] = $viewed;
			$product['language_id'] = $languageId;
			$product['description'] = $description;
			$product['stock_status_id'] = $stockStatusId;
			$product['meta_description'] = $meta_description;
			$product['length'] = $length;
			$product['width'] = $width;
			$product['height'] = $height;
			$product['seo_keyword'] = $keyword;
			$product['measurement_unit'] = $measurementUnit;
			$product['sku'] = $sku;
			$product['upc'] = $upc;
	
			$product['related_ids'] = ($related=="") ? array() : explode( ",", $related );
			if ($product['related_ids']===FALSE) {
				$product['related_ids'] = array();
			}
		
			$product['tags'] = ($tags=="") ? array() : explode( ",", $tags );
			if ($product['tags']===FALSE) {
				$product['tags'] = array();
			}
			$product['subtract'] = $subtract;
			$product['minimum'] = $minimum;
			$product['meta_keywords'] = $meta_keywords;
			$product['sort_order'] = $sort_order;
			$product['downloads']=$downloads;
			$products[$productId] = $product;


			
		}
		/*echo "<pre>";
		print_r($products);
		print_r($_SESSION['EXCEL_ERROR_MESSAGE']);
		exit;*/
		return $this->storeProductsIntoDatabase( $database, $products );
	}


	function storeCategoriesIntoDatabase( &$database, &$categories ,$getOptionLabelArray) 
	{
		//$category_keyword_array=$this->getArrayIds("category_keyword");
		
		$languageId = '1';//$this->getDefaultLanguageId($database);

		// start transaction, remove categories
		$sql = "START TRANSACTION;\n";

		$this->import( $database, $sql );
		
 	   // generate and execute SQL for inserting the categories
	 
		foreach ($categories as $category) {
			$categoryId = $category['category_id'];
			$imageName = $category['image'];
			$parentId = $category['parent_id'];
			$top = $category['top'];
			$top = ((strtoupper($top)=="TRUE") || (strtoupper($top)=="YES") || (strtoupper($top)=="ENABLED")) ? 1 : 0;
			$columns = $category['columns'];
			$sortOrder = $category['sort_order'];
			$dateAdded = $category['date_added'];
			$dateModified = $category['date_modified'];
			$languageId = $category['language_id'];
			$name = addslashes($category['name']);
			$description = addslashes($category['description']);
		 

 
			$meta_description = addslashes($category['meta_description']);
			$meta_keywords = addslashes($category['meta_keywords']);
			$keyword = addslashes($category['seo_keyword']);
			$status = $category['status'];
			$status = ((strtoupper($status)=="TRUE") || (strtoupper($status)=="YES") || (strtoupper($status)=="ENABLED")) ? 1 : 0;
			
			$filters=$this->setFilters($category['filters'],$getOptionLabelArray);
			$sql2 = "INSERT INTO `r_categories` (`categories_id`, `categories_image`, `parent_id`, `top`, `column`, `sort_order`, `date_added`, `last_modified`, `status`, `filters`) VALUES ";
			$sql2 .= "( $categoryId, '$imageName', $parentId, $top, $columns, $sortOrder, ";
			$sql2 .= ($dateAdded==new Zend_Db_Expr('NOW()')) ? "$dateAdded," : "'$dateAdded',";
			$sql2 .= ($dateModified==new Zend_Db_Expr('NOW()')) ? "$dateModified," : "'$dateModified',";
			$sql2 .= " $status,'$filters')";
		 
			 
			$this->db->query( $sql2 );

			 
			
			$sql3 = "INSERT INTO `r_categories_description` (`categories_id`, `language_id`, `categories_name`, `categories_description`, `meta_description`, `meta_keywords`) VALUES ";
			$sql3 .= "( $categoryId, $languageId, '$name', '$description', '$meta_description', '$meta_keywords' )";
			//echo $sql3."<br/>";
			$this->db->insert('r_categories_description',array("categories_id"=>$categoryId,"language_id"=>$languageId,"categories_name"=>$name,"categories_description"=>$description,"meta_description"=>$meta_description,"meta_keywords"=>$meta_keywords));
			//$this->db->query($sql3);
			//exit;
			/*if ($keyword) {
				if(in_array($keyword,$category_keyword_array))
				{

					$_SESSION['EXCEL_ERROR_MESSAGE'][]="Categories Tab:category id ".$categoryId." with seo keyword already exists.update the keyword manually from admin->catalog->categorie";
				}else
				{
					$sql5 = "INSERT INTO `r_url_alias` (`query`,`keyword`,`id`) VALUES ('category','$keyword','$categoryId')";
					//	echo $sql5."<br/>";
					$this->db->query($sql5);
				}
			}*/
			
			if($keyword!="")
			{
				$sql5 = "INSERT INTO `r_url_alias` (`query`,`keyword`,`id`) VALUES ('category','$keyword','$categoryId')";
				$this->exeQuery(array("query"=>$sql5,"warning"=>"Categories Tab:category id ".$categoryId." with seo keyword already exists.update the keyword manually from admin->catalog->categorie"));
			}
		}
		 
		// final commit
		$this->db->query( "COMMIT;" );
		//exit; 
		return TRUE;
	}


	function uploadCategories( &$reader, &$database,$getOptionLabelArray ) 
	{
		$categoryArray=$this->getArrayIds('category');//existing array of category ids
		// find the default language id
		$languageId = "1";//$this->getDefaultLanguageId($database);
		
		$data = $reader->getSheet(0);
		$categories = array();
		$isFirstRow = TRUE;
		$i = 0;
		$k = $data->getHighestRow();
		for ($i=0; $i<$k; $i+=1) {
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$categoryId = trim($this->getCell($data,$i,1));
			$langId = '1';
			$parentId = $this->getCell($data,$i,2,'0');
			$name = $this->getCell($data,$i,3);
			$name = htmlentities( $name, ENT_QUOTES, $this->detect_encoding($name) );
			/*if ($langId != $languageId) { //language id missing
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Categories Tab:category id ".$categoryId." ignored as this category is missing language_id";
				continue;
			}*/

			/*$search_key=$categoryId."#".$langId;
			if(in_array($search_key,$categoryArray))//ignore duplicates
			{
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Categories Tab:category id ".$categoryId." ignored as this category already exists with language id ".$langId;
				continue;
			}*/

		
			
			if ($categoryId=="") { //category id missing
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Categories Tab:category ".$name." ignored as this category is missing id ";
				continue;
			}
						
			if ($name=="") { //category namem missing
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Categories Tab:category id ".$categoryId." ignored as this category is missing name";
				continue;
			}


			$top = $this->getCell($data,$i,7,($parentId=='0')?'true':'false');
			$columns = $this->getCell($data,$i,8,($parentId=='0')?'1':'0');
			$sortOrder = $this->getCell($data,$i,9,'0');
			$imageName = trim($this->getCell($data,$i,6));
			$dateAdded = trim($this->getCell($data,$i,14));
			$dateAdded = ((is_string($dateAdded)) && (strlen($dateAdded)>0)) ? $dateAdded : "NOW()";
			$dateModified = trim($this->getCell($data,$i,15));
			$dateModified = ((is_string($dateModified)) && (strlen($dateModified)>0)) ? $dateModified : "NOW()";
			
			
			$keyword = $this->getCell($data,$i,4);
			$description = $this->getCell($data,$i,5);
			$description = htmlentities( $description, ENT_QUOTES, $this->detect_encoding($description) );
			$meta_description = $this->getCell($data,$i,13);
			$meta_description = htmlentities( $meta_description, ENT_QUOTES, $this->detect_encoding($meta_description) );
			$meta_keywords = $this->getCell($data,$i,12);
			$meta_keywords = htmlentities( $meta_keywords, ENT_QUOTES, $this->detect_encoding($meta_keywords) );
			$status = $this->getCell($data,$i,10,'true');
			$filters = $this->getCell($data,$i,11,'true');
			$category = array();
			$category['category_id'] = $categoryId;
			$category['image'] = $imageName;
			$category['parent_id'] = $parentId;
			$category['sort_order'] = $sortOrder;
			$category['date_added'] = $dateAdded;
			$category['date_modified'] = $dateModified;
			$category['language_id'] = $languageId;
			$category['name'] = $name;
			$category['top'] = $top;
			$category['columns'] = $columns;
			$category['description'] = $description;
			$category['meta_description'] = $meta_description;
			$category['meta_keywords'] = $meta_keywords;
			$category['seo_keyword'] = $keyword;
			$category['status'] = $status;
			$category['filters'] = $filters;
			$categories[$categoryId] = $category;
		}

		return $this->storeCategoriesIntoDatabase( $database, $categories,$getOptionLabelArray );
	}


	function storeOptionsIntoDatabase( &$database, &$options ) 
	{
		$aoid=$this->getArrayOptionId();
		$aoivd=$this->getArrayOptionValueId();
		$getSelectOVId=$this->getSelectOVId();
	
		// find the default language id
		$languageId = "1";//$this->getDefaultLanguageId($database);
		$i=1;
		//echo "<pre>";
		//print_r($aoid);
		//print_r($aoivd);
		//print_r($getSelectOVId);
		//print_r($options);
		//print_r($_SESSION['EXCEL_ERROR_MESSAGE']);
		//echo "</pre>";
		foreach ($options as $option) {
			$productId = $option['product_id'];
			//test
			
			$optionId=$aoid[trim(strtolower($option['option']))][trim(strtolower($option['type']))];
			$optionValueId=$aoivd[$optionId][trim(strtolower($option['value']))];
			

			if($optionId=="")
			{
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Options Tab:invalid option name or type ,product id : ".$productId." insertion ignored !!";
				continue;
			}

			//echo "<br/>before product id ".$productId."<br/>";
			/*if($productId!="") //41
			{
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Options Tab:product id null , insertion ignored !!";
				//echo "<br/> product id ".$productId."<br/>";
				continue;
			}else
			{
				echo "<br/> product id ".$productId."<br/>";
			}*/
			/*echo "<br/>option ".trim(strtolower($option['option']))." type ".trim(strtolower($option['type']))." ".trim(strtolower($option['value']))."<br/>";
			echo "option id".$optionId." option value ".$optionValueId;
			echo "inside";
			exit;
			echo "<pre>";
			print_r($option);
			echo "</pre>";*/
 			$productOptionId=$i++;
			$langId = '1';
			$name = $option['option'];
			$type = $option['type'];
			$value = $option['value'];
			$required = $option['required'];
			$base_option_value_id = $getSelectOVId[$option['base_option']];
			//echo "option id".$optionId." base optoin ".$option['base_option']."base option vale id".$base_option_value_id."<br/>";
			$required = ((strtoupper($required)=="TRUE") || (strtoupper($required)=="YES") || (strtoupper($required)=="ENABLED")) ? 1 : 0;
 			 
				if (($type!='select') && ($type!='checkbox') && ($type!='radio')) {
					$productOptionValue = $value;
				} else {
					$productOptionValue = '';
				}
				
				$string_poi=trim($option['product_id'])."_".trim(md5($option['option']))."_".trim($option['type']);
				if($_SESSION['exp'][$string_poi]=="")
				{
					$sql  = "INSERT INTO `r_products_option` (`product_option_id`,`product_id`,`option_id`,`option_value`,`required`) VALUES ";
					$sql .= "($productOptionId,$productId,$optionId,'".addslashes($productOptionValue)."',$required);";
					$this->exeQuery(array("query"=>$sql,"warning"=>"Options Tab:product id ".$productId." contains invalid data.insertion ignored"));
					$_SESSION['exp'][$string_poi]=$productOptionId;
					$poi=$_SESSION['exp'][$string_poi];
				}else
				{
					$poi=$_SESSION['exp'][$string_poi];
				}

				/*$sql  = "INSERT INTO `r_products_option` (`product_option_id`,`product_id`,`option_id`,`option_value`,`required`) VALUES ";
				$sql .= "($productOptionId,$productId,$optionId,'".addslashes($productOptionValue)."',$required);";
				$this->exeQuery(array("query"=>$sql,"warning"=>"Options Tab:product id ".$productId." contains invalid data.insertion ignored"));*/
		 
			if (($type=='select') || ($type=='checkbox') || ($type=='radio')) {
				$quantity = $option['quantity'];
				$subtract = $option['subtract'];
				$subtract = ((strtoupper($subtract)=="TRUE") || (strtoupper($subtract)=="YES") || (strtoupper($subtract)=="ENABLED")) ? 1 : 0;
				$price = $option['price'];
				$pricePrefix = $option['price_prefix'];
				$points = $option['points'];
				$pointsPrefix = $option['points_prefix'];
				$weight = $option['weight'];
				$weightPrefix = $option['weight_prefix'];
			 
				 
				/*$sql  = "INSERT INTO `r_products_option_value` (`product_option_value_id`,`product_option_id`,`product_id`,`option_id`,`option_value_id`,`quantity`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`,`base_option_value_id`) VALUES ";
				$sql .= "($productOptionId,$productOptionId,$productId,$optionId,$optionValueId,$quantity,$subtract,$price,'$pricePrefix','$points','$pointsPrefix','$weight','$weightPrefix','$base_option_value_id');";*/
				$sql  = "INSERT INTO `r_products_option_value` (`product_option_value_id`,`product_option_id`,`product_id`,`option_id`,`option_value_id`,`quantity`,`subtract`,`price`,`price_prefix`,`points`,`points_prefix`,`weight`,`weight_prefix`,`base_option_value_id`) VALUES ";
				$sql .= "($productOptionId,$poi,$productId,$optionId,$optionValueId,$quantity,$subtract,$price,'$pricePrefix','$points','$pointsPrefix','$weight','$weightPrefix','$base_option_value_id');";

				//echo $sql."<br/>";
				//$this->db->query( $sql );
				$this->exeQuery(array("query"=>$sql,"warning"=>"Options Tab:product id ".$productId." contains invalid data.insertion ignored"));
			}

		}
		//echo "here";
		//exit;
		
		$this->db->query("COMMIT;");
		return TRUE;
	}



	function uploadOptions( &$reader, &$database,$optionArray ) 
	{
		// find the default language id
		
		$languageId = "1";//$this->getDefaultLanguageId($database);
		
		$data = $reader->getSheet(2);
		$options = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			
			$productId = trim($this->getCell($data,$i,1));
			if ($productId=="") {
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Options Tab:product id missing ,row insertion ignored !!";
				continue;
			}
			
			$langId = '1';//$this->getCell($data,$i,2);
		

			$option = $this->getCell($data,$i,2);
			$value = $this->getCell($data,$i,4,'');
			$type = $this->getCell($data,$i,3,'select');
			if ($type=="select" || $type=="radio" || $type=="checkbox") 
			{
				if ($value=="" || $option=="") 
				{	
					$_SESSION['EXCEL_ERROR_MESSAGE'][]="Options Tab:product id ".$productId." is ignored as option/value is missing!!";
					continue;
				}
			}

			
			$required = $this->getCell($data,$i,6,'true');
			$quantity = $this->getCell($data,$i,7,'0');
			$subtract = $this->getCell($data,$i,8,'false');
			$price = $this->getCell($data,$i,9,'0');
			$pricePrefix = $this->getCell($data,$i,10,'+');
			$points = $this->getCell($data,$i,11,'0');
			$pointsPrefix = $this->getCell($data,$i,12,'+');
			$weight = $this->getCell($data,$i,13,'0.00');
			$weightPrefix = $this->getCell($data,$i,14,'+');
			$base_option = $this->getCell($data,$i,5);
			//echo "bo".$base_option."<br/>";
			$options[$i] = array();
			$options[$i]['product_id'] = $productId;
			$options[$i]['language_id'] = $languageId;
			$options[$i]['option'] = $option;
			$options[$i]['type'] = $type;
			$options[$i]['value'] = $value;
			$options[$i]['required'] = $required;
			if (($type=='select') || ($type=='checkbox') || ($type=='radio')) {
				$options[$i]['quantity'] = $quantity;
				$options[$i]['subtract'] = $subtract;
				$options[$i]['price'] = $price;
				$options[$i]['price_prefix'] = $pricePrefix;
				$options[$i]['points'] = $points;
				$options[$i]['points_prefix'] = $pointsPrefix;
				$options[$i]['weight'] = $weight;
				$options[$i]['weight_prefix'] = $weightPrefix;
				$options[$i]['sort_order'] = $sortOrder;
				$options[$i]['base_option'] = strtolower(trim($base_option));
			}
		}
		//echo "base option ".$base_option."<br/>";
		//exit;
		return $this->storeOptionsIntoDatabase( $database, $options );
	}


	function storeAttributesIntoDatabase( &$database, &$attributes ) {
		$attributeArray=$this->getAttributeArray();
		$uniqueArrayId=$this->getArrayIds('attribute');
		 
		$languageId = "1";//$this->getDefaultLanguageId($database);
	 
		/*echo "<pre>";
		print_r($attributeArray);
		echo "</pre>";*/
		foreach ($attributes as $attribute) {

			$productId = $attribute['product_id'];
			$langId = '1';//$attribute['language_id'];
			$group = $attribute['group'];
			$name = $attribute['name'];
			$text = $attribute['text'];
		 
			$attributeId =$attributeArray[trim(strtolower($group))][trim(strtolower($name))];// $newAttributeIds[$group][$name];
			$search_key=$productId."#".$langId."#".$attributeId;
			//echo $search_key."<br/>";
			/*if(in_array($search_key,$uniqueArrayId)) //ignore duplicates
			{
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Attributes Tab:product id ".$productId." ignored as ".$group." ".$name." already exits!!";
				continue; 
			}*/
			//echo "group ".trim(strtolower($group))." name ".trim(strtolower($name))."<br/>";
			//echo "attr id".$attributeId."<br/>";
			if($attributeId!="")
			{
				
				$sql  = "INSERT INTO `r_product_attribute_group` (`product_id`,`attribute_id`,`language_id`,`text`) VALUES ";
				$sql .= "($productId,$attributeId,$langId,'".addslashes( $text )."');";
			 //echo $sql."<br/>";
				//$this->db->query( $sql );
				try {
				$this->db->query( $sql );
				} catch (Zend_Db_Exception $e) {
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Attributes Tab:product id ".$productId." ignored as ".$group." ".$name." already exits!!";
				}
			}else
			{
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Attributes Tab:Invalid Attribute group/name for product id ".$productId." insertion ignored!!";
			} 
			//echo $sql."<br/>";			
			
			 
		}
		//exit;
		$this->db->query("COMMIT;");
		return TRUE;
	}


	function uploadAttributes( &$reader, &$database ) 
	{
		// find the default language id
		$languageId ="1";// $this->getDefaultLanguageId($database);
		
		$data = $reader->getSheet(3);
		$attributes = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$productId = trim($this->getCell($data,$i,1));
			if ($productId=="") {
			$_SESSION['EXCEL_ERROR_MESSAGE'][]="Attributes Tab:product id missing!!";

				continue;
			}

			$group = trim($this->getCell($data,$i,2));
			if ($group=='') {
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Attributes Tab:product id ".$productId." ignored as attribute_group missing!!";
				continue;
			}
			$name = trim($this->getCell($data,$i,3));

			if ($name=='') {
			$_SESSION['EXCEL_ERROR_MESSAGE'][]="Attributes Tab:product id ".$productId." ignored as attribute_name missing!!";
				continue;
			}
			$text = $this->getCell($data,$i,4);
			
			if ($text=='') {
			$_SESSION['EXCEL_ERROR_MESSAGE'][]="Attributes Tab:product id ".$productId." ignored as text missing!!";
				continue;
			}

			//$sortOrder = $this->getCell($data,$i,6);
			$attributes[$i] = array();
			$attributes[$i]['product_id'] = $productId;
			$attributes[$i]['language_id'] = $languageId;
			$attributes[$i]['group'] = $group;
			$attributes[$i]['name'] = $name;
			$attributes[$i]['text'] = $text;
			//$attributes[$i]['sort_order'] = $sortOrder;
		}

		//echo "<pre>";
		//print_r($attributes);
	
 		return $this->storeAttributesIntoDatabase( $database, $attributes );
	}


	function storeSpecialsIntoDatabase( &$database, &$specials )
	{
		$specialArray=$this->getArrayIds('special');
		//echo "<pre>";
		//print_r($specialArray);
		//exit;

		$custgrouparray=$this->getCustomerGroupArray();
		
		//$sql = "START TRANSACTION;\n";
		//$sql .= "DELETE FROM `r_products_specials`;\n";
		//$this->import( $database, $sql );

		// find existing customer groups from the database
		/*$sql = "SELECT * FROM `r_customer_group`";
		$result_rows = $this->db->fetchAll( $sql );
		$maxCustomerGroupId = 0;
		$customerGroups = array();
		foreach ($result_rows as $row) {
			$customerGroupId = $row['customer_group_id'];
			$name = $row['name'];
			if (!isset($customerGroups[$name])) {
				$customerGroups[$name] = $customerGroupId;
			}
			if ($maxCustomerGroupId < $customerGroupId) {
				$maxCustomerGroupId = $customerGroupId;
			}
		}

		// add additional customer groups into the database
		foreach ($specials as $special) {
			$name = $special['customer_group'];
			if (!isset($customerGroups[$name])) {
				$maxCustomerGroupId += 1;
				$sql  = "INSERT INTO `r_customer_group` (`customer_group_id`, `name`) VALUES "; 
				$sql .= "($maxCustomerGroupId, '$name')";
				$sql .= ";\n";
				$this->db->query($sql);
				$customerGroups[$name] = $maxCustomerGroupId;
			}
		}*/

		// store product specials into the database
	
		
			$rows=$this->db->fetchRow("select max(specials_id) as id from r_products_specials");
			$productSpecialId = $rows['id'];//0;
			$first = TRUE;
			$sql = "INSERT INTO `r_products_specials` (`specials_id`,`products_id`,`customer_group_id`,`priority`,`specials_new_products_price`,`start_date`,`expires_date` ) VALUES "; 
			foreach ($specials as $special) {
			 
				$productSpecialId += 1;
				$productId = $special['product_id'];
				$name = trim(strtolower($special['customer_group']));
				$customerGroupId = $custgrouparray[$name];
				$search_key=$productId."#".$customerGroupId;
				if(in_array($search_key,$specialArray))
				{
					$_SESSION['EXCEL_ERROR_MESSAGE'][]="Specials Tab:Product id ".$productId." ignored as product already exits!!";

					continue;
				}
				$priority = $special['priority'];
				$price = $special['price'];
				$dateStart = $special['date_start'];
				$dateEnd = $special['date_end'];
				$sql .= ($first) ? "\n" : ",\n";
				$first = FALSE;
				$sql .= "($productSpecialId,$productId,$customerGroupId,$priority,$price,'$dateStart','$dateEnd')";

				//echo $sql."<br/>";
			}
			if (!$first) {
				$this->db->query($sql);
			}

			$this->db->query("COMMIT;");
 		return TRUE;
	}

	protected function getCustomerGroupArray()
	{
		$rows=$this->db->fetchAll("select lower(name) as name,customer_group_id from r_customer_group");
		$array=array();
		foreach($rows as $k=>$v)
		{
			$array[$v[name]]=$v[customer_group_id];
		}
		return $array;
	}


	function uploadSpecials( &$reader, &$database ) 
	{
		$custgrouparray=$this->getCustomerGroupArray();
		$data = $reader->getSheet(4);
		$specials = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$productId = trim($this->getCell($data,$i,1));

			if ($productId=="") {
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Specials Tab:Product id is missing!!";
				continue;
			}

			$customerGroup = trim($this->getCell($data,$i,2));
			if ($customerGroup=="") {
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Specials Tab:Product id ".$productId." ignored as customer_group is missing!!";
				continue;
			}

			if($custgrouparray[trim(strtolower($customerGroup))]=="")
			{
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Specials Tab:Product id ".$productId." ignored as customer_group is invalid!!";
				continue;
			}
			
			$price = $this->getCell($data,$i,4,'');
 			if ($price=="") {
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Specials Tab:Product id ".$productId." ignored as price is missing!!";
				continue;
			}
			$priority = $this->getCell($data,$i,3,'0');
			$dateStart = $this->getCell($data,$i,5,'0000-00-00');
			$dateEnd = $this->getCell($data,$i,6,'0000-00-00');
			$specials[$i] = array();
			$specials[$i]['product_id'] = $productId;
			$specials[$i]['customer_group'] = $customerGroup;
			$specials[$i]['priority'] = $priority;
			$specials[$i]['price'] = $price;
			$specials[$i]['date_start'] = $dateStart;
			$specials[$i]['date_end'] = $dateEnd;
		}
		return $this->storeSpecialsIntoDatabase( $database, $specials );
	}


	function storeDiscountsIntoDatabase( &$database, &$discounts )
	{
		 
		$discountArray=$this->getArrayIds('discount');



		$custgrouparray=$this->getCustomerGroupArray();
		// store product discounts into the database
		//$row=$this->db->fetchRow("select max(product_discount_id) as id from r_product_discount");
		//$productDiscountId = $row['id'];//0;
		$first = TRUE;
		$sql = "INSERT INTO `r_product_discount` (`product_id`,`customer_group_id`,`quantity`,`priority`,`price`,`date_start`,`date_end` ) VALUES "; 
		foreach ($discounts as $discount) {
			
			//$productDiscountId += 1;
			$productId = $discount['product_id'];
			$name = trim(strtolower($discount['customer_group']));
			$customerGroupId = $custgrouparray[$name];
			
			$search_key=$productId."#".$customerGroupId;
			
			if(in_array($search_key,$discountArray))
			{
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Discounts Tab:Product id ".$productId." already exists!!";
				continue;
			}
			
			$quantity = $discount['quantity'];
			$priority = $discount['priority'];
			$price = $discount['price'];
			$dateStart = $discount['date_start'];
			$dateEnd = $discount['date_end'];
			$sql .= ($first) ? "\n" : ",\n";
			$first = FALSE;
			$sql .= "($productId,$customerGroupId,$quantity,$priority,$price,'$dateStart','$dateEnd')";
		}
		if (!$first) {
			$this->db->query($sql);
		}

		$this->db->query("COMMIT;");
		return TRUE;
	}


	function uploadDiscounts( &$reader, &$database ) 
	{
		 	$custgrouparray=$this->getCustomerGroupArray();
		$data = $reader->getSheet(5);
		$discounts = array();
		$i = 0;
		$k = $data->getHighestRow();
		$isFirstRow = TRUE;
		for ($i=0; $i<$k; $i+=1) {
			if ($isFirstRow) {
				$isFirstRow = FALSE;
				continue;
			}
			$productId = trim($this->getCell($data,$i,1));
			
			if ($productId=="") {
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Discounts Tab:Product id  is missing!!";
				continue;
			}
	
			$customerGroup = trim($this->getCell($data,$i,2));
			if ($customerGroup=="") {
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Discounts Tab:Product id ".$productId." ignored as customer_group is missing!!";
				continue;
			}

			if($custgrouparray[trim(strtolower($customerGroup))]=="")
			{
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Discounts Tab:Product id ".$productId." ignored as customer_group is invalid!!";
				continue;
			}

			$price = $this->getCell($data,$i,5,'');
 			if ($price=="") {
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Discounts Tab:Product id ".$productId." ignored as price is missing!!";
				continue;
			}
			
			$quantity = $this->getCell($data,$i,3,'');
			
			if ($quantity=="") {
				$_SESSION['EXCEL_ERROR_MESSAGE'][]="Discounts Tab:Product id ".$productId." ignored as quantity is missing!!";
				continue;
			}

			$priority = $this->getCell($data,$i,4,'0');
			
			$dateStart = $this->getCell($data,$i,6,'0000-00-00');
			$dateEnd = $this->getCell($data,$i,7,'0000-00-00');
			$discounts[$i] = array();
			$discounts[$i]['product_id'] = $productId;
			$discounts[$i]['customer_group'] = $customerGroup;
			$discounts[$i]['quantity'] = $quantity;
			$discounts[$i]['priority'] = $priority;
			$discounts[$i]['price'] = $price;
			$discounts[$i]['date_start'] = $dateStart;
			$discounts[$i]['date_end'] = $dateEnd;
		}

		return $this->storeDiscountsIntoDatabase( $database, $discounts );
	}

	protected function getOptionArray()
	{
		$fch=$this->db->fetchAll("select option_value_id,lower(name) as name from r_option_value_description where language_id='1'");
		$return=array();
		if(sizeof($fch)>0)
		{
			foreach($fch as $k=>$v)
			{
				//$return[$v['option_value_id']]=$v['name'];
				$return[$v['name']]=$v['option_value_id'];

			} 
		}
		//$return['price']="p";
		//$return['manufacturer']="m";
		return $return;
	}

	protected function getOptionLabelArray()
	{
		$fch=$this->db->fetchAll("select option_id,lower(name) as name from r_option_description where language_id='1'");
		$return=array();
		if(sizeof($fch)>0)
		{
			foreach($fch as $k=>$v)
			{
				//$return[$v['option_value_id']]=$v['name'];
				$return[$v['name']]=$v['option_id'];

			} 
		}
		$return['price']="p";
		$return['manufacturer']="m";

		//echo "here in getOptionLabelarray<pre>";
		//print_r($return);
		return $return;
	}

	function storeAdditionalImagesIntoDatabase( &$reader, &$database ,$optionArray)
	{

		$getSelectOVId=$this->getSelectOVId();
		//exit;
		// start transaction
		$sql = "START TRANSACTION;\n";
		
		// insert new additional product images into database
		$data =& $reader->getSheet(1); // Products worksheet
		//$row=$this->db->fetchRow("select max(id) as id from r_products_images");
		//$maxImageId = $row['id'];//0;
		
		$k = $data->getHighestRow();
		for ($i=1; $i<$k; $i+=1) {
			$productId = trim($this->getCell($data,$i,1));
			if ($productId=="") {
				continue;
			}
			$imageNames = trim($this->getCell($data,$i,11));
			$optionNames = trim($this->getCell($data,$i,12));
			$imageNames = trim( $this->clean($imageNames, TRUE) );
			
			$imageNames = ($imageNames=="") ? array() : explode( ",", $imageNames );
			$optionNames = ($optionNames=="") ? array() : explode( ",", $optionNames );
			$array=array("image"=>$imageNames,"option"=>$optionNames);

			foreach ($imageNames as $k1=>$v1) {
				$maxImageId += 1;
				//$sql = "INSERT INTO `r_products_images` (`id`, products_id, `image`, `product_option_value_id`) VALUES ";
				//$sql .= "($maxImageId,$productId,'".$v1."','".$optionArray[trim($optionNames[$k1])]."');";

				$sql = "INSERT INTO `r_products_images` (  products_id,`image`, `product_option_value_id`) VALUES ";
				$sql .= "($productId,'".$v1."','".$getSelectOVId[strtolower(trim($optionNames[$k1]))]."');";
				
				//echo $sql."<br>";
				$this->db->query( $sql );
			}
			
		}
		//exit;
		$this->db->query( "COMMIT;" );
		return TRUE;
	}


	function uploadImages( &$reader, &$database,$optionArray )
	{
		$ok = $this->storeAdditionalImagesIntoDatabase( $reader, $database,$optionArray );
		return $ok;
	}

	function getCell(&$worksheet,$row,$col,$default_val='') {
		$col -= 1; // we use 1-based, PHPExcel uses 0-based column index
		$row += 1; // we use 0-based, PHPExcel used 1-based row index
		return ($worksheet->cellExistsByColumnAndRow($col,$row)) ? $worksheet->getCellByColumnAndRow($col,$row)->getValue() : $default_val;
	}

	function validateHeading( &$data, &$expected ) {
		$heading = array();
		$k = PHPExcel_Cell::columnIndexFromString( $data->getHighestColumn() );
		if ($k != count($expected)) {
			return FALSE;
		}
		$i = 0;
		for ($j=1; $j <= $k; $j+=1) {
			$heading[] = $this->getCell($data,$i,$j);
		}
		$valid = TRUE;
		for ($i=0; $i < count($expected); $i+=1) {
			if (!isset($heading[$i])) {
				$valid = FALSE;
				break;
			}
			if (strtolower($heading[$i]) != strtolower($expected[$i])) {
				$valid = FALSE;
				break;
			}
		}
		return $valid;
	}


	function validateCategories( &$reader )
	{
		$expectedCategoryHeading = array
		( "category_id", "parent_id", "name", "seo_keyword", "description","image_name","top", "columns", "sort_order",  "status", "filters", "meta_keywords", "meta_description", "date_added", "date_modified" );
		$data =& $reader->getSheet(0);
		return $this->validateHeading( $data, $expectedCategoryHeading );
	}


	function validateProducts( &$reader )
	{
		$expectedProductHeading = array
		( "product_id", "name", "categories",  "quantity","minimum","seo_keyword",  "description","model", "manufacturer", "image_name","additional image names","additional_image_option", "price","subtract", "date_available", "status","sort_order", "stock_status_id","tax_class_id",  "downloads","shipping","related_products",     "tags","sku", "upc", "weight", "unit", "length", "width", "height", "length_unit", "points", "meta_keywords",   "meta_description", "date_added", "date_modified",   "viewed");
		$data =& $reader->getSheet(1);
		return $this->validateHeading( $data, $expectedProductHeading );
	}


	function validateOptions( &$reader )
	{
		$expectedOptionHeading = array
		( "product_id", "option_name", "type", "value", "base_option", "required", "quantity", "subtract", "price", "price\nprefix", "points", "points\nprefix", "weight", "weight\nprefix" );
		$data =& $reader->getSheet(2);
		return $this->validateHeading( $data, $expectedOptionHeading );
	}


	function validateAttributes( &$reader )
	{
		$expectedAttributeHeading = array
		( "product_id",  "attribute_group", "attribute_name", "text");
		$data =& $reader->getSheet(3);
		return $this->validateHeading( $data, $expectedAttributeHeading );
	}


	function validateSpecials( &$reader )
	{
		$expectedSpecialsHeading = array
		( "product_id", "customer_group", "priority", "price", "date_start", "date_end" );
		$data =& $reader->getSheet(4);
		return $this->validateHeading( $data, $expectedSpecialsHeading );
	}


	function validateDiscounts( &$reader )
	{
		$expectedDiscountsHeading = array
		( "product_id", "customer_group", "quantity", "priority", "price", "date_start", "date_end" );
		$data =& $reader->getSheet(5);
		return $this->validateHeading( $data, $expectedDiscountsHeading );
	}


	function validateUpload( &$reader )
	{
		if ($reader->getSheetCount() != 6) {
			echo "Work Sheet Missing.6 Work Sheets expected!!";
			$_SESSION['EXCEL_ERROR_MESSAGE'][]="Work Sheet Missing.6 Work Sheets expected!!";
			return FALSE;
		}
		if (!$this->validateCategories( $reader )) {
			echo "Invalid Categories Header!!";
			$_SESSION['EXCEL_ERROR_MESSAGE'][]="Invalid Categories Header!!";
			return FALSE;
		}
		if (!$this->validateProducts( $reader )) {
			echo "Invalid Products Header!!";
			$_SESSION['EXCEL_ERROR_MESSAGE'][]="Invalid Products Header!!";
			
			return FALSE;
		}
		if (!$this->validateOptions( $reader )) {
			echo "Invalid Options Header!!";
			$_SESSION['EXCEL_ERROR_MESSAGE'][]="Invalid Options Header!!";
			return FALSE;
		}
		if (!$this->validateAttributes( $reader )) {
			echo "Invalid Attributes Header!!";
			$_SESSION['EXCEL_ERROR_MESSAGE'][]="Invalid Attributes Header!!";
			return FALSE;
		}
		if (!$this->validateSpecials( $reader )) {
			echo "Invalid Specials Header!!";
			$_SESSION['EXCEL_ERROR_MESSAGE'][]="Invalid Specials Header!!";
			return FALSE;
		}
		if (!$this->validateDiscounts( $reader )) {
			echo "Invalid Discounts Header!!";
			$_SESSION['EXCEL_ERROR_MESSAGE'][]="Invalid Discounts Header!!";
			return FALSE;
		}
		return TRUE;
	}


	function clearCache() {
	 Model_Cache::removeAllCache();
 	}

	function clearCatalog()
	{
		$this->db->query("TRUNCATE `r_categories`");
		$this->db->query("TRUNCATE `r_categories_description`");
		$this->db->query("TRUNCATE `r_products`");
		$this->db->query("TRUNCATE `r_products_description`");
		$this->db->query("TRUNCATE `r_products_images`");
		$this->db->query("TRUNCATE `r_products_option`");
		$this->db->query("TRUNCATE `r_products_option_value`");
		$this->db->query("TRUNCATE `r_products_specials`");
		$this->db->query("TRUNCATE `r_products_to_categories`");
		$this->db->query("TRUNCATE `r_product_attribute_group`");
		$this->db->query("TRUNCATE `r_product_discount`");
		$this->db->query("TRUNCATE `r_product_related`");
		$this->db->query("TRUNCATE `r_product_reward`");
		$this->db->query("TRUNCATE `r_product_tag`");
		$this->db->query("TRUNCATE `r_product_to_download`");
		$this->db->query("TRUNCATE `r_url_alias`");
	}

	function getArrayOptionId()
{
		
	$rows=$this->db->fetchAll("select o.option_id, trim(lower(o.type)) as type ,trim(lower(od.name)) as name from r_option o,r_option_description od where o.option_id=od.option_id");
	$optionId=array();
	foreach($rows as $row)
	{
		$optionId[$row['name']][$row['type']]=$row['option_id'];
	}
	return $optionId;
}

public function getSelectOVId()
{
	$rows=$this->db->fetchAll("SELECT ovd.option_value_id, ovd.option_id, ovd.name FROM r_option o, r_option_value_description ovd WHERE o.option_id = ovd.option_id AND ovd.language_id =1 AND o.type =  'select'");
	$array=array();
	foreach($rows as $k=>$v)
	{
		$array[strtolower(trim($v['name']))]=$v['option_value_id'];
	}

	return $array;
}

function getArrayOptionValueId()
{
	$rows=$this->db->fetchAll("select ovd.option_id,ovd.option_value_id,trim(lower(ovd.name)) as value,(select trim(lower(type))  from r_option o where o.option_id=ovd.option_id) as type,(select trim(lower(name))  from r_option_description od where od.option_id=ovd.option_id) as name from r_option_value_description ovd");
	//echo "select ovd.option_id,ovd.option_value_id,trim(lower(ovd.name)) as value,(select trim(lower(type))  from r_option o where o.option_id=ovd.option_id) as type,(select trim(lower(name))  from r_option_description od where od.option_id=ovd.option_id) as name from r_option_value_description ovd";
	
	$optionValueId=array();
	foreach($rows as $row)
	{
		$optionValueId[$row['option_id']][$row['value']]=$row['option_value_id'];
	}
	return $optionValueId;
}

	function upload( $filename ) {
	 
		/*echo "<pre>";
		print_r($this->getArrayOptionId());
		print_r($this->getArrayOptionValueId());
		echo "</pre>";
		echo "here ";
		exit;*/
		unset($_SESSION['EXCEL_ERROR_MESSAGE']);
		$this->clearCatalog();

		//$optionArray=$this->getOptionArray();
		//$getOptionLabelArray=$this->getOptionLabelArray();

		/*echo "<pre>";
		print_r($optionArray);
		print_r($getOptionLabelArray);
		echo "</pre>";*/
		//exit;
		ini_set("memory_limit","512M");
		ini_set("max_execution_time",1800);
	
		
		//set_time_limit( 60 );
		chdir(@constant('DOCUMENT_ROOT').'/library/PHPExcel');
		require_once( 'Classes/PHPExcel.php' );
		chdir( '../../application' );
		$inputFileType = PHPExcel_IOFactory::identify($filename);
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		$objReader->setReadDataOnly(true);
		$reader = $objReader->load($filename);
		
		$ok = $this->validateUpload( $reader );
		//echo "after validation".$ok;
		//EXIT;
		if (!$ok) {
			return FALSE;
		}
		//exit;
		 $this->clearCache();
		
		 $ok = $this->uploadImages( $reader, $database,$optionArray );
		if (!$ok) {
			return FALSE;
		}



		$ok = $this->uploadCategories( $reader, $database,$getOptionLabelArray );
		if (!$ok) {
			return FALSE;
		}
 		

		$ok = $this->uploadProducts( $reader, $database );

		if (!$ok) {
			return FALSE;
		}

		 
		
		$ok = $this->uploadOptions( $reader, $database,$optionArray);
		
		if (!$ok) {
			return FALSE;
		}

		
		
		$ok = $this->uploadAttributes( $reader, $database );
		
		if (!$ok) {
			return FALSE;
		}

		//exit;

			
			
		$ok = $this->uploadSpecials( $reader, $database );
		if (!$ok) {
			return FALSE;
		} 
		
		
		$ok = $this->uploadDiscounts( $reader, $database );
		
		if (!$ok) {
			return FALSE;
		}

		unset($_SESSION['exp']);
 
 		chdir( '../../..' );
		return $ok;
	}

	protected function getAttributeArray()
	{
		$rows=$this->db->fetchAll("SELECT ag.attribute_group_id, lower(agd.name) AS `group`, ad.attribute_id, lower(ad.name) as name FROM `r_attribute_group` ag INNER JOIN `r_attribute_group_description` agd ON agd.attribute_group_id=ag.attribute_group_id AND agd.language_id=1 LEFT JOIN `r_attribute` a ON a.attribute_group_id=ag.attribute_group_id INNER JOIN `r_attribute_description` ad ON ad.attribute_id=a.attribute_id AND ad.language_id=1");
		$attribute=array();
		//echo "<pre>";
		//print_r($rows);
		foreach($rows as $k=>$v)
		{
			//print_r($r);
			$attribute[trim($v[group])][trim($v[name])]=$v[attribute_id];
		}
		//echo "<pre>";
		//print_r($attribute);
		return $attribute;
	}

	/*function getStoreIdsForCategories( &$database ) {
		$sql =  "SELECT category_id, store_id FROM `r_category_to_store` cs;";
		$storeIds = array();
		$result = $this->db->query( $sql );
		foreach ($result->rows as $row) {
			$categoryId = $row['category_id'];
			$storeId = $row['store_id'];
			if (!isset($storeIds[$categoryId])) {
				$storeIds[$categoryId] = array();
			}
			if (!in_array($storeId,$storeIds[$categoryId])) {
				$storeIds[$categoryId][] = $storeId;
			}
		}
		return $storeIds;
	}*/


	/*function getLayoutsForCategories( &$database ) {
		$sql  = "SELECT cl.*, l.name FROM `r_category_to_layout` cl ";
		$sql .= "LEFT JOIN `r_layout` l ON cl.layout_id = l.layout_id ";
		$sql .= "ORDER BY cl.category_id, cl.store_id;";
		$result = $this->db->query( $sql );
		$layouts = array();
		foreach ($result->rows as $row) {
			$categoryId = $row['category_id'];
			$storeId = $row['store_id'];
			$name = $row['name'];
			if (!isset($layouts[$categoryId])) {
				$layouts[$categoryId] = array();
			}
			$layouts[$categoryId][$storeId] = $name;
		}
		return $layouts;
	}*/


	function populateCategoriesWorksheet( &$worksheet, &$database, $languageId, &$boxFormat, &$textFormat )
	{
 
		// Set the column widths
		$j = 0;
		$worksheet->setColumn($j,$j++,strlen('category_id')+1);
		$worksheet->setColumn($j,$j++,strlen('parent_id')+1);
		$worksheet->setColumn($j,$j++,max(strlen('name'),32)+1);
		$worksheet->setColumn($j,$j++,max(strlen('seo_keyword'),16)+1);
		$worksheet->setColumn($j,$j++,max(strlen('description'),32)+1);
		$worksheet->setColumn($j,$j++,max(strlen('image_name'),12)+1);
		$worksheet->setColumn($j,$j++,max(strlen('top'),5)+1);
		$worksheet->setColumn($j,$j++,strlen('columns')+1);
		$worksheet->setColumn($j,$j++,strlen('sort_order')+1);;
		$worksheet->setColumn($j,$j++,max(strlen('status'),5)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('filters'),32)+1);
		$worksheet->setColumn($j,$j++,max(strlen('meta_keywords'),32)+1);
		$worksheet->setColumn($j,$j++,max(strlen('meta_description'),32)+1);
		$worksheet->setColumn($j,$j++,max(strlen('date_added'),19)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('date_modified'),19)+1,$textFormat);
		
		// The heading row
		$i = 0;
		$j = 0;
		$worksheet->writeString( $i, $j++, 'category_id', $boxFormat );
		$worksheet->writeString( $i, $j++, 'parent_id', $boxFormat );
		$worksheet->writeString( $i, $j++, 'name', $boxFormat );
		$worksheet->writeString( $i, $j++, 'seo_keyword', $boxFormat );
		$worksheet->writeString( $i, $j++, 'description', $boxFormat );
		$worksheet->writeString( $i, $j++, 'image_name', $boxFormat );
		$worksheet->writeString( $i, $j++, 'top', $boxFormat );
		$worksheet->writeString( $i, $j++, 'columns', $boxFormat );
		$worksheet->writeString( $i, $j++, 'sort_order', $boxFormat );
		$worksheet->writeString( $i, $j++, "status", $boxFormat );
		$worksheet->writeString( $i, $j++, 'filters', $boxFormat );
		$worksheet->writeString( $i, $j++, 'meta_keywords', $boxFormat );
		$worksheet->writeString( $i, $j++, 'meta_description', $boxFormat );
		$worksheet->writeString( $i, $j++, 'date_added', $boxFormat );
		$worksheet->writeString( $i, $j++, 'date_modified', $boxFormat );
		$worksheet->setRow( $i, 30, $boxFormat );
		
		// The actual categories data
		$i += 1;
		$j = 0;
		
		$result_rows=$this->db->query("SELECT c.* , cd.*, ua.keyword FROM `r_categories` c INNER JOIN `r_categories_description` cd ON cd.categories_id = c.categories_id AND cd.language_id='".$languageId."' and c.del='0' LEFT JOIN `r_url_alias` ua ON ua.id=c.categories_id and ua.query='category' ORDER BY c.`parent_id`, `sort_order`, c.`categories_id`");
		
		foreach ($result_rows as $row) {
			$worksheet->setRow( $i, 26 );
			$worksheet->write( $i, $j++, $row['categories_id'] );
			$worksheet->write( $i, $j++, $row['parent_id'] );
			$worksheet->writeString( $i, $j++, html_entity_decode($row['categories_name'],ENT_QUOTES,'UTF-8') );
			$worksheet->writeString( $i, $j++, ($row['keyword']) ? $row['keyword'] : '' );
			$worksheet->writeString( $i, $j++, html_entity_decode($row['categories_description'],ENT_QUOTES,'UTF-8') );
			$worksheet->write( $i, $j++, $row['categories_image'] );
			$worksheet->write( $i, $j++, ($row['top']==0) ? "false" : "true", $textFormat );
			$worksheet->write( $i, $j++, $row['column'] );
			$worksheet->write( $i, $j++, $row['sort_order'] );
			$worksheet->write( $i, $j++, ($row['status']==0) ? "false" : "true", $textFormat );
			$filters=$this->getFilters($row['filters']);
			$worksheet->writeString( $i, $j++, html_entity_decode($filters,ENT_QUOTES,'UTF-8') );
			$worksheet->writeString( $i, $j++, html_entity_decode($row['meta_keywords'],ENT_QUOTES,'UTF-8') );
			$worksheet->writeString( $i, $j++, html_entity_decode($row['meta_description'],ENT_QUOTES,'UTF-8') );
			$worksheet->write( $i, $j++, $row['date_added'], $textFormat );
			$worksheet->write( $i, $j++, $row['last_modified'], $textFormat );
			$i += 1;
			$j = 0;
		}
	}

	function populateProductsWorksheet( &$worksheet, &$database, &$imageNames, $languageId, &$priceFormat, &$boxFormat, &$weightFormat, &$textFormat )
	{
		// Set the column widths
		$j = 0;

		$worksheet->setColumn($j,$j++,max(strlen('product_id'),4)+1);
		$worksheet->setColumn($j,$j++,max(strlen('name'),30)+1);
		$worksheet->setColumn($j,$j++,max(strlen('categories'),12)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('quantity'),4)+1);
		$worksheet->setColumn($j,$j++,max(strlen('minimum'),8)+1);
		$worksheet->setColumn($j,$j++,max(strlen('seo_keyword'),16)+1);
		$worksheet->setColumn($j,$j++,max(strlen('description'),32)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('model'),8)+1);
		$worksheet->setColumn($j,$j++,max(strlen('manufacturer'),10)+1);
		$worksheet->setColumn($j,$j++,max(strlen('image_name'),12)+1);
		$worksheet->setColumn($j,$j++,max(strlen('additional image names'),24)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('additional_image_option'),24)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('price'),10)+1,$priceFormat);
		$worksheet->setColumn($j,$j++,max(strlen('subtract'),5)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('date_available'),10)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('status'),5)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('sort_order'),8)+1);
		$worksheet->setColumn($j,$j++,max(strlen('stock_status_id'),3)+1);
		$worksheet->setColumn($j,$j++,max(strlen('tax_class_id'),2)+1);
		$worksheet->setColumn($j,$j++,max(strlen('downloads'),24)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('shipping'),5)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('related_products'),16)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('tags'),32)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('sku'),10)+1);
		$worksheet->setColumn($j,$j++,max(strlen('upc'),10)+1);
		$worksheet->setColumn($j,$j++,max(strlen('weight'),6)+1,$weightFormat);
		$worksheet->setColumn($j,$j++,max(strlen('unit'),3)+1);
		$worksheet->setColumn($j,$j++,max(strlen('length'),8)+1);
		$worksheet->setColumn($j,$j++,max(strlen('width'),8)+1);
		$worksheet->setColumn($j,$j++,max(strlen('height'),8)+1);
		$worksheet->setColumn($j,$j++,max(strlen('length_unit'),3)+1);
		$worksheet->setColumn($j,$j++,max(strlen('points'),5)+1);		
		$worksheet->setColumn($j,$j++,max(strlen('meta_keywords'),32)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('meta_description'),32)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('date_added'),19)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('date_modified'),19)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('viewed'),5)+1);		
		
		

		// The product headings row
		$i = 0;
		$j = 0;
		$worksheet->writeString( $i, $j++, 'product_id', $boxFormat );
		$worksheet->writeString( $i, $j++, 'name', $boxFormat );
		$worksheet->writeString( $i, $j++, 'categories', $boxFormat );
		$worksheet->writeString( $i, $j++, 'quantity', $boxFormat );
		$worksheet->writeString( $i, $j++, 'minimum', $boxFormat );
		$worksheet->writeString( $i, $j++, 'seo_keyword', $boxFormat );
		$worksheet->writeString( $i, $j++, 'description', $boxFormat );
		$worksheet->writeString( $i, $j++, 'model', $boxFormat );
		$worksheet->writeString( $i, $j++, 'manufacturer', $boxFormat );
		$worksheet->writeString( $i, $j++, 'image_name', $boxFormat );
		$worksheet->writeString( $i, $j++, 'additional image names', $boxFormat );
		$worksheet->writeString( $i, $j++, 'additional_image_option', $boxFormat );
		$worksheet->writeString( $i, $j++, 'price', $boxFormat );
		$worksheet->writeString( $i, $j++, "subtract", $boxFormat );
		$worksheet->writeString( $i, $j++, 'date_available', $boxFormat );
		$worksheet->writeString( $i, $j++, "status", $boxFormat );
		$worksheet->writeString( $i, $j++, 'sort_order', $boxFormat );
		$worksheet->writeString( $i, $j++, 'stock_status_id', $boxFormat );
		$worksheet->writeString( $i, $j++, 'tax_class_id', $boxFormat );
		$worksheet->writeString( $i, $j++, 'downloads', $boxFormat );
		$worksheet->writeString( $i, $j++, "shipping", $boxFormat );
		$worksheet->writeString( $i, $j++, 'related_products', $boxFormat );
		$worksheet->writeString( $i, $j++, 'tags', $boxFormat );
		$worksheet->writeString( $i, $j++, 'sku', $boxFormat );
		$worksheet->writeString( $i, $j++, 'upc', $boxFormat );
		$worksheet->writeString( $i, $j++, 'weight', $boxFormat );
		$worksheet->writeString( $i, $j++, 'unit', $boxFormat );
		$worksheet->writeString( $i, $j++, 'length', $boxFormat );
		$worksheet->writeString( $i, $j++, 'width', $boxFormat );
		$worksheet->writeString( $i, $j++, 'height', $boxFormat );
		$worksheet->writeString( $i, $j++, "length_unit", $boxFormat );
		$worksheet->writeString( $i, $j++, 'points', $boxFormat );
		$worksheet->writeString( $i, $j++, 'meta_keywords', $boxFormat );
		$worksheet->writeString( $i, $j++, 'meta_description', $boxFormat );
		$worksheet->writeString( $i, $j++, 'date_added', $boxFormat );
		$worksheet->writeString( $i, $j++, 'date_modified', $boxFormat );
		$worksheet->writeString( $i, $j++, 'viewed', $boxFormat );
				
		$worksheet->setRow( $i, 30, $boxFormat );
		
	
		$i += 1;
		$j = 0;
		


		//$result_rows=$this->db->fetchAll("SELECT p.del,p.products_id, pd.products_name, GROUP_CONCAT( DISTINCT CAST(pc.categories_id AS CHAR(11)) SEPARATOR ',' ) AS categories, p.sku, p.upc, p.products_quantity, p.products_model, m.manufacturers_name AS manufacturer, p.products_image AS image_name, p.shipping, p.products_price, p.products_points, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_weight, wc.unit, p.length, p.width, p.height, p.products_status, p.products_tax_class_id, p.viewed, p.sort_order, pd.language_id, ua.keyword, pd.products_description, pd.meta_description, pd.meta_keywords, p.stock_status_id, mc.unit AS length_unit, p.substract_stock, p.products_minimum_quantity, GROUP_CONCAT( DISTINCT CAST(pr.related_id AS CHAR(11)) SEPARATOR ',' ) AS related, GROUP_CONCAT( DISTINCT pt.tag SEPARATOR ',' ) AS tags FROM `r_products` p INNER JOIN `r_products_description` pd ON p.products_id=pd.products_id AND p.del='0' AND pd.language_id='".$languageId."' LEFT JOIN `r_products_to_categories` pc ON p.products_id=pc.products_id LEFT JOIN `r_url_alias` ua ON ua.id=p.products_id and ua.query='product' LEFT JOIN `r_manufacturers` m ON m.manufacturers_id = p.manufacturers_id LEFT JOIN `r_weight_class_description` wc ON wc.weight_class_id = p.weight_class_id AND wc.language_id='".$languageId."' LEFT JOIN `r_length_class_description` mc ON mc.length_class_id=p.length_class_id AND mc.language_id='".$languageId."' LEFT JOIN `r_product_related` pr ON pr.product_id=p.products_id LEFT JOIN `r_product_tag` pt ON pt.products_id=p.products_id AND pt.language_id='".$languageId."' GROUP BY p.products_id ORDER BY p.products_id, pc.categories_id"); //before tags removed for langauge id

		$result_rows=$this->db->fetchAll("SELECT p.del,p.products_id, pd.products_name, GROUP_CONCAT( DISTINCT CAST(pc.categories_id AS CHAR(11)) SEPARATOR ',' ) AS categories, p.sku, p.upc, p.products_quantity, p.products_model, m.manufacturers_name AS manufacturer, p.products_image AS image_name, p.shipping, p.products_price, p.products_points, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_weight, wc.unit, p.length, p.width, p.height, p.products_status, p.products_tax_class_id, p.viewed, p.sort_order, pd.language_id, ua.keyword, pd.products_description, pd.meta_description, pd.meta_keywords, p.stock_status_id, mc.unit AS length_unit, p.substract_stock, p.products_minimum_quantity, GROUP_CONCAT( DISTINCT CAST(pr.related_id AS CHAR(11)) SEPARATOR ',' ) AS related, GROUP_CONCAT( DISTINCT pt.tag SEPARATOR ',' ) AS tags FROM `r_products` p INNER JOIN `r_products_description` pd ON p.products_id=pd.products_id AND p.del='0' AND pd.language_id='".$languageId."' LEFT JOIN `r_products_to_categories` pc ON p.products_id=pc.products_id LEFT JOIN `r_url_alias` ua ON ua.id=p.products_id and ua.query='product' LEFT JOIN `r_manufacturers` m ON m.manufacturers_id = p.manufacturers_id LEFT JOIN `r_weight_class_description` wc ON wc.weight_class_id = p.weight_class_id AND wc.language_id='".$languageId."' LEFT JOIN `r_length_class_description` mc ON mc.length_class_id=p.length_class_id AND mc.language_id='".$languageId."' LEFT JOIN `r_product_related` pr ON pr.product_id=p.products_id LEFT JOIN `r_product_tag` pt ON pt.products_id=p.products_id GROUP BY p.products_id ORDER BY p.products_id, pc.categories_id");
		

		foreach ($result_rows as $row) {
			if($row['del']=='1')//ignore deleted products ie del=1
			{
				continue;
			}

			//downloads
			$ds=$this->db->fetchAll("select name from r_download_description dd,r_product_to_download pd where pd.download_id=dd.download_id and pd.product_id='".(int)$row['products_id']."'");
			$dname="";
			$pre="";
			foreach($ds as $k=>$v)
			{
				$dname.=$pre.$v['name'];
					$pre=",";
			}

			
			$worksheet->setRow( $i, 26 );
			$productId = $row['products_id'];
			$worksheet->write( $i, $j++, $productId );
			$worksheet->writeString( $i, $j++, html_entity_decode($row['products_name'],ENT_QUOTES,'UTF-8') );
			$worksheet->write( $i, $j++, $row['categories'], $textFormat );
			$worksheet->write( $i, $j++, $row['products_quantity'] );
			$worksheet->write( $i, $j++, $row['products_minimum_quantity'] );
			$worksheet->writeString( $i, $j++, ($row['keyword']) ? $row['keyword'] : '' );
			$worksheet->writeString( $i, $j++, html_entity_decode($row['products_description'],ENT_QUOTES,'UTF-8'), $textFormat, TRUE );
			$worksheet->writeString( $i, $j++, $row['products_model'] );
			$worksheet->writeString( $i, $j++, $row['manufacturer'] );
			$worksheet->writeString( $i, $j++, $row['image_name'] );
				$names = "";
			$opt = "";
			 
			if (isset($imageNames[$productId])) {
				$first = TRUE;
				foreach ($imageNames[$productId] AS $name) {
					if (!$first) {
						$names .= ",\n";
						$opt .= ",\n";
					}
					$first = FALSE;
					//$names .= $name;
					$names .= $name['image'];
					$opt .= $name['opt'];
				}
			}
			 
			$worksheet->write( $i, $j++, $names, $textFormat );
			$worksheet->write( $i, $j++, $opt, $textFormat );
			$worksheet->write( $i, $j++, $row['products_price'], $priceFormat );
			$worksheet->write( $i, $j++, ($row['substract_stock']==0) ? "false" : "true", $textFormat );
			$worksheet->write( $i, $j++, $row['products_date_available'], $textFormat );
			$worksheet->write( $i, $j++, ($row['products_status']==0) ? "false" : "true", $textFormat );
			$worksheet->write( $i, $j++, $row['sort_order'] );
			$worksheet->write( $i, $j++, $row['stock_status_id'] );
			$worksheet->write( $i, $j++, $row['products_tax_class_id']);
			$worksheet->write( $i, $j++, $dname, $textFormat );
			$worksheet->write( $i, $j++, ($row['shipping']==0) ? "false" : "true", $textFormat );
			$worksheet->write( $i, $j++, $row['related'], $textFormat );
			$worksheet->write( $i, $j++, $row['tags'], $textFormat );
			$worksheet->writeString( $i, $j++, $row['sku']);
			$worksheet->writeString( $i, $j++, $row['upc']);
			$worksheet->write( $i, $j++, $row['products_weight'], $weightFormat );
			$worksheet->writeString( $i, $j++, $row['unit'] );
			$worksheet->write( $i, $j++, $row['length'] );
			$worksheet->write( $i, $j++, $row['width'] );
			$worksheet->write( $i, $j++, $row['height'] );
			$worksheet->writeString( $i, $j++, $row['length_unit'] );
			$worksheet->write( $i, $j++, $row['products_points'] );
			$worksheet->write( $i, $j++, html_entity_decode($row['meta_keywords'],ENT_QUOTES,'UTF-8'), $textFormat );
			$worksheet->write( $i, $j++, html_entity_decode($row['meta_description'],ENT_QUOTES,'UTF-8'), $textFormat );
			$worksheet->write( $i, $j++, $row['products_date_added'], $textFormat );
			$worksheet->write( $i, $j++, $row['products_last_modified'], $textFormat );
			$worksheet->write( $i, $j++, $row['viewed'] );
			$i += 1;
			$j = 0;
		}
	}


	function populateOptionsWorksheet( &$worksheet, &$database, $languageId, &$priceFormat, &$boxFormat, &$weightFormat, $textFormat )
	{
		// Set the column widths
		$j = 0;
		$worksheet->setColumn($j,$j++,max(strlen('product_id'),4)+1);
		$worksheet->setColumn($j,$j++,max(strlen('option_name'),30)+1);
		$worksheet->setColumn($j,$j++,max(strlen('type'),10)+1);
		$worksheet->setColumn($j,$j++,max(strlen('value'),30)+1);
		$worksheet->setColumn($j,$j++,max(strlen('base_option'),5)+1);
		$worksheet->setColumn($j,$j++,max(strlen('required'),5)+1);
		$worksheet->setColumn($j,$j++,max(strlen('quantity'),4)+1);
		$worksheet->setColumn($j,$j++,max(strlen('subtract'),5)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('price'),10)+1,$priceFormat);
		$worksheet->setColumn($j,$j++,max(strlen('price'),5)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('points'),10)+1,$priceFormat);
		$worksheet->setColumn($j,$j++,max(strlen('points'),5)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('weight'),10)+1,$priceFormat);
		$worksheet->setColumn($j,$j++,max(strlen('weight'),5)+1,$textFormat);
		
		
		// The options headings row
		$i = 0;
		$j = 0;
		$worksheet->writeString( $i, $j++, 'product_id', $boxFormat );
		$worksheet->writeString( $i, $j++, 'option_name', $boxFormat  );
		$worksheet->writeString( $i, $j++, 'type', $boxFormat  );
		$worksheet->writeString( $i, $j++, 'value', $boxFormat  );
		$worksheet->writeString( $i, $j++, 'base_option', $boxFormat  );
		$worksheet->writeString( $i, $j++, 'required', $boxFormat  );
		$worksheet->writeString( $i, $j++, 'quantity', $boxFormat  );
		$worksheet->writeString( $i, $j++, 'subtract', $boxFormat  );
		$worksheet->writeString( $i, $j++, 'price', $boxFormat  );
		$worksheet->writeString( $i, $j++, "price\nprefix", $boxFormat  );
		$worksheet->writeString( $i, $j++, 'points', $boxFormat  );
		$worksheet->writeString( $i, $j++, "points\nprefix", $boxFormat  );
		$worksheet->writeString( $i, $j++, 'weight', $boxFormat  );
		$worksheet->writeString( $i, $j++, "weight\nprefix", $boxFormat  );
	
		$worksheet->setRow( $i, 30, $boxFormat );
		
		// The actual options data
		$i += 1;
		$j = 0;
		 
		
		$result_rows=$this->db->fetchAll("SELECT po.product_id,(select ovd.name from r_option_value_description ovd where ovd.option_value_id=pov.base_option_value_id and ovd.language_id=1) as base_option,(select p.del from r_products p where p.products_id=po.product_id) as del, po.option_id, po.option_value AS default_value, po.required, pov.option_value_id, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix, ovd.name AS option_value, ov.sort_order, od.name AS option_name, o.type FROM `r_products_option` po LEFT JOIN `r_option` o ON o.option_id=po.option_id LEFT JOIN `r_products_option_value` pov ON pov.product_option_id = po.product_option_id LEFT JOIN `r_option_value` ov ON ov.option_value_id=pov.option_value_id LEFT JOIN `r_option_value_description` ovd ON ovd.option_value_id=ov.option_value_id AND ovd.language_id='".$languageId."' LEFT JOIN `r_option_description` od ON od.option_id=o.option_id AND od.language_id='".$languageId."' ORDER BY po.product_id, po.option_id, pov.option_value_id");
		foreach ($result_rows as $row) {
			if($row['del']=='1' || $row['del']=='')
			{
				continue;
			}
			$worksheet->setRow( $i, 13 );
			$worksheet->write( $i, $j++, $row['product_id'] );
 			$worksheet->writeString( $i, $j++, $row['option_name'] );
			$worksheet->writeString( $i, $j++, $row['type'] );
			$worksheet->writeString( $i, $j++, ($row['default_value']) ? $row['default_value'] : $row['option_value'] );
			$worksheet->write( $i, $j++, strtolower($row['base_option']));
			$worksheet->write( $i, $j++, ($row['required']==0) ? "false" : "true", $textFormat );
			$worksheet->write( $i, $j++, $row['quantity'] );
			if (is_null($row['option_value_id'])) {
				$subtract = '';
			} else {
				$subtract = ($row['subtract']==0) ? "false" : "true";
			}
			$worksheet->write( $i, $j++, $subtract, $textFormat );
			$worksheet->write( $i, $j++, $row['price'], $priceFormat );
			$worksheet->writeString( $i, $j++, $row['price_prefix'], $textFormat );
			$worksheet->write( $i, $j++, $row['points'] );
			$worksheet->writeString( $i, $j++, $row['points_prefix'], $textFormat );
			$worksheet->write( $i, $j++, $row['weight'], $weightFormat );
			$worksheet->writeString( $i, $j++, $row['weight_prefix'], $textFormat );
 			
			$i += 1;
			$j = 0;
		}
	}


	function populateAttributesWorksheet( &$worksheet, &$database, $languageId, &$boxFormat, $textFormat )
	{
		// Set the column widths
		$j = 0;
		$worksheet->setColumn($j,$j++,max(strlen('product_id'),4)+1);
		//$worksheet->setColumn($j,$j++,max(strlen('language_id'),2)+1);
		$worksheet->setColumn($j,$j++,max(strlen('attribute_group'),30)+1);
		$worksheet->setColumn($j,$j++,max(strlen('attribute_name'),30)+1);
		$worksheet->setColumn($j,$j++,max(strlen('text'),30)+1);
		//$worksheet->setColumn($j,$j++,max(strlen('sort_order'),5)+1);
		
		// The attributes headings row
		$i = 0;
		$j = 0;
		$worksheet->writeString( $i, $j++, 'product_id', $boxFormat );
		//$worksheet->writeString( $i, $j++, 'language_id', $boxFormat  );
		$worksheet->writeString( $i, $j++, 'attribute_group', $boxFormat  );
		$worksheet->writeString( $i, $j++, 'attribute_name', $boxFormat  );
		$worksheet->writeString( $i, $j++, 'text', $boxFormat  );
		//$worksheet->writeString( $i, $j++, 'sort_order', $boxFormat  );
		$worksheet->setRow( $i, 30, $boxFormat );
		
		// The actual attributes data
		$i += 1;
		$j = 0;
		/*$query  = "SELECT pa.*, a.attribute_group_id, ad.name AS attribute_name, a.sort_order, agd.name AS attribute_group ";
		$query .= "FROM `r_product_attribute_group` pa ";
		$query .= "LEFT JOIN `r_attribute` a ON a.attribute_id=pa.attribute_id ";
		$query .= "LEFT JOIN `r_attribute_description` ad ON ad.attribute_id=a.attribute_id AND ad.language_id=$languageId ";
		$query .= "LEFT JOIN `r_attribute_group_description` agd ON agd.attribute_group_id=a.attribute_group_id AND agd.language_id=$languageId ";
		$query .= "WHERE pa.language_id=$languageId ";
		$query .= "ORDER BY pa.product_id, a.attribute_group_id, a.attribute_id;";
		$result = $this->db->query( $query );*/
		
		$result_rows=$this->db->fetchAll("SELECT pa.*,(select p.del from r_products p where p.products_id=pa.product_id) as del, a.attribute_group_id, ad.name AS attribute_name, a.sort_order, agd.name AS attribute_group FROM `r_product_attribute_group` pa LEFT JOIN `r_attribute` a ON a.attribute_id=pa.attribute_id LEFT JOIN `r_attribute_description` ad ON ad.attribute_id=a.attribute_id AND ad.language_id='".(int)$languageId."' LEFT JOIN `r_attribute_group_description` agd ON agd.attribute_group_id=a.attribute_group_id AND agd.language_id='".(int)$languageId."' WHERE pa.language_id='".(int)$languageId."' ORDER BY pa.product_id, a.attribute_group_id, a.attribute_id");
		
		foreach ($result_rows as $row) {
			if($row['del']=='1' || $row['del']=='')
			{
				continue;
			}
			$worksheet->setRow( $i, 13 );
			$worksheet->write( $i, $j++, $row['product_id'] );
			//$worksheet->write( $i, $j++, $languageId );
			$worksheet->writeString( $i, $j++, $row['attribute_group'] );
			$worksheet->writeString( $i, $j++, $row['attribute_name'] );
			$worksheet->writeString( $i, $j++, $row['text'] );
			//$worksheet->write( $i, $j++, $row['sort_order'] );
			$i += 1;
			$j = 0;
		}
	}


	function populateSpecialsWorksheet( &$worksheet, &$database, &$priceFormat, &$boxFormat, &$textFormat )
	{
		// Set the column widths
		$j = 0;
		$worksheet->setColumn($j,$j++,strlen('product_id')+1);
		$worksheet->setColumn($j,$j++,strlen('customer_group')+1);
		$worksheet->setColumn($j,$j++,strlen('priority')+1);
		$worksheet->setColumn($j,$j++,max(strlen('price'),10)+1,$priceFormat);
		$worksheet->setColumn($j,$j++,max(strlen('date_start'),19)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('date_end'),19)+1,$textFormat);
		
		// The heading row
		$i = 0;
		$j = 0;
		$worksheet->writeString( $i, $j++, 'product_id', $boxFormat );
		$worksheet->writeString( $i, $j++, 'customer_group', $boxFormat );
		$worksheet->writeString( $i, $j++, 'priority', $boxFormat );
		$worksheet->writeString( $i, $j++, 'price', $boxFormat );
		$worksheet->writeString( $i, $j++, 'date_start', $boxFormat );
		$worksheet->writeString( $i, $j++, 'date_end', $boxFormat );
		$worksheet->setRow( $i, 30, $boxFormat );
		
		// The actual product specials data
		$i += 1;
		$j = 0;
		/*$query  = "SELECT ps.*, cg.name FROM `r_products_specials` ps ";
		$query .= "LEFT JOIN `r_customer_group` cg ON cg.customer_group_id=ps.customer_group_id ";
		$query .= "ORDER BY ps.product_id, cg.name";
		$result = $this->db->query( $query );*/
		$result_rows=$this->db->fetchAll("SELECT ps . * ,(select p.del from r_products p where p.products_id=ps.products_id) as del, cg.name FROM `r_products_specials` ps LEFT JOIN `r_customer_group` cg ON cg.customer_group_id=ps.customer_group_id ORDER BY ps.products_id, cg.name");
		foreach ($result_rows as $row) {

			if($row['del']=='1' ||	$row['del']=='')
			{
				continue;
			}

			$worksheet->setRow( $i, 13 );
			$worksheet->write( $i, $j++, $row['products_id'] );
			$worksheet->write( $i, $j++, $row['name'] );
			$worksheet->write( $i, $j++, $row['priority'] );
			$worksheet->write( $i, $j++, $row['specials_new_products_price'], $priceFormat );
			$worksheet->write( $i, $j++, $row['start_date'], $textFormat );
			$worksheet->write( $i, $j++, $row['expires_date'], $textFormat );
			$i += 1;
			$j = 0;
		}
	}


	function populateDiscountsWorksheet( &$worksheet, &$database, &$priceFormat, &$boxFormat, &$textFormat )
	{
		// Set the column widths
		$j = 0;
		$worksheet->setColumn($j,$j++,strlen('product_id')+1);
		$worksheet->setColumn($j,$j++,strlen('customer_group')+1);
		$worksheet->setColumn($j,$j++,strlen('quantity')+1);
		$worksheet->setColumn($j,$j++,strlen('priority')+1);
		$worksheet->setColumn($j,$j++,max(strlen('price'),10)+1,$priceFormat);
		$worksheet->setColumn($j,$j++,max(strlen('date_start'),19)+1,$textFormat);
		$worksheet->setColumn($j,$j++,max(strlen('date_end'),19)+1,$textFormat);
		
		// The heading row
		$i = 0;
		$j = 0;
		$worksheet->writeString( $i, $j++, 'product_id', $boxFormat );
		$worksheet->writeString( $i, $j++, 'customer_group', $boxFormat );
		$worksheet->writeString( $i, $j++, 'quantity', $boxFormat );
		$worksheet->writeString( $i, $j++, 'priority', $boxFormat );
		$worksheet->writeString( $i, $j++, 'price', $boxFormat );
		$worksheet->writeString( $i, $j++, 'date_start', $boxFormat );
		$worksheet->writeString( $i, $j++, 'date_end', $boxFormat );
		$worksheet->setRow( $i, 30, $boxFormat );
		
		// The actual product discounts data
		$i += 1;
		$j = 0;
		/*$query  = "SELECT pd.*, cg.name FROM `r_product_discount` pd ";
		$query .= "LEFT JOIN `r_customer_group` cg ON cg.customer_group_id=pd.customer_group_id ";
		$query .= "ORDER BY pd.product_id, cg.name";
		$result = $this->db->query( $query );*/

		$result_rows=$this->db->fetchAll("SELECT pd.*,(select p.del from r_products p where p.products_id=pd.product_id) as del, cg.name FROM `r_product_discount` pd LEFT JOIN `r_customer_group` cg ON cg.customer_group_id=pd.customer_group_id ORDER BY pd.product_id, cg.name");
		foreach ($result_rows as $row) {

			if($row['del']=='1' || $row['del']=='')
			{
				continue;
			}

			$worksheet->setRow( $i, 13 );
			$worksheet->write( $i, $j++, $row['product_id'] );
			$worksheet->write( $i, $j++, $row['name'] );
			$worksheet->write( $i, $j++, $row['quantity'] );
			$worksheet->write( $i, $j++, $row['priority'] );
			$worksheet->write( $i, $j++, $row['price'], $priceFormat );
			$worksheet->write( $i, $j++, $row['date_start'], $textFormat );
			$worksheet->write( $i, $j++, $row['date_end'], $textFormat );
			$i += 1;
			$j = 0;
		}
	}


	protected function clearSpreadsheetCache() {
		$files = glob(@constant('DOCUMENT_ROOT')."/cache" . 'Spreadsheet_Excel_Writer' . '*');
		
		if ($files) {
			foreach ($files as $file) {
				if (file_exists($file)) {
					@unlink($file);
					clearstatcache();
				}
			}
		}
	}

		protected function setFilters($str,$optionArray)
	{
		
		if($str!="")
		{
			$exp=explode("&",$str);
			//print_r($exp);
			if(sizeof($exp>0))
			{
				foreach($exp as $k=>$v)
				{
					$exp1=explode("#",$v);
					$optionArray[$exp1[0]];
					$filter.=$pre.$optionArray[$exp1[0]]."#".$exp1[1];
					$pre="&";
					/*if($exp1[0]=='price' || $exp1[0]=='manufacturer')
					{
						if($exp1[0]=='price')
						{
							$label="p";
						}else if($exp1[0]=='manufacturer')
						{
							$label="m";
						}
					}else
					{
						$fch=$this->db->fetchRow("select name from r_option_description where language_id='1' and option_id='".$exp1[0]."'");
						$label=strtolower($fch['name']);
						//$label=strtolower($fch[$exp1[0]]);	
					}
					$filter.=$pre.$label."#".$exp1[1];
						$pre="&";*/
					
				}
			}
		}
		/*echo "<pre>";
		print_r($filter);
		echo "</pre>";
		exit;*/
		return $filter;
	}

	protected function getFilters($str)
	{
		if($str!="")
		{
			$exp=explode("&",$str);
			if(sizeof($exp>0))
			{
				foreach($exp as $k=>$v)
				{
					$exp1=explode("#",$v);
					if($exp1[0]=='p' || $exp1[0]=='m')
					{
						if($exp1[0]=='p')
						{
							$label="price";
						}else if($exp1[0]=='m')
						{
							$label="manufacturer";
						}
					}else
					{
						$fch=$this->db->fetchRow("select name from r_option_description where language_id='1' and option_id='".(int)$exp1[0]."'");
						$label=strtolower($fch['name']);
						//$label=strtolower($fch[$exp1[0]]);	
					}
					$filter.=$pre.$label."#".$exp1[1];
						$pre="&";
					
				}
			}
		}
		return $filter;
	}

	function exeQuery($data)
	{
		try 
		{
			$this->db->query( $data['query'] );
		} 
		catch (Zend_Db_Exception $e) 
		{
			$_SESSION['EXCEL_ERROR_MESSAGE'][]=$data['warning'];
		}
	}


	function download() {
		$languageId='1';
		// We use the package from http://pear.php.net/package/Spreadsheet_Excel_Writer/
		chdir(@constant('DOCUMENT_ROOT').'/library/pear' );
		require_once "Spreadsheet/Excel/Writer.php";
		chdir('../../application');
		
		// Creating a workbook
		$workbook = new Spreadsheet_Excel_Writer();
		$workbook->setTempDir(@constant('DOCUMENT_ROOT')."/cache");
		$workbook->setVersion(8); // Use Excel97/2000 Format
		$priceFormat =& $workbook->addFormat(array('Size' => 10,'Align' => 'right','NumFormat' => '######0.00'));
		$boxFormat =& $workbook->addFormat(array('Size' => 10,'vAlign' => 'vequal_space' ));
		$weightFormat =& $workbook->addFormat(array('Size' => 10,'Align' => 'right','NumFormat' => '##0.00'));
		$textFormat =& $workbook->addFormat(array('Size' => 10, 'NumFormat' => "@" ));
		
		// sending HTTP headers
		$workbook->send('backup_categories_products.xls');
		
		// Creating the categories worksheet
		$worksheet =& $workbook->addWorksheet('Categories');
		$worksheet->setInputEncoding ( 'UTF-8' );
	
		$this->populateCategoriesWorksheet( $worksheet, $database, $languageId, $boxFormat, $textFormat );
		$worksheet->freezePanes(array(1, 1, 1, 1));
		
		$result_rows=$this->db->fetchAll("SELECT DISTINCT p.products_id, pi.id AS image_id, pi.image AS filename,pi.product_option_value_id,(select ovd.name from r_option_value_description ovd where ovd.option_value_id=pi.product_option_value_id and ovd.language_id='1') as opt 	 FROM `r_products` p INNER JOIN `r_products_images` pi ON pi.products_id=p.products_id and p.del='0' ORDER BY products_id, image_id");
		
		foreach ($result_rows as $row) {
			$productId = $row['products_id'];
			$imageId = $row['image_id'];
			$imageName = $row['filename'];
			$opt = strtolower($row['opt']);
			if (!isset($imageNames[$productId])) {
				$imageNames[$productId] = array();
				$imageNames[$productId][$imageId] = array("image"=>$imageName,"opt"=>$opt);
			}
			else {
				$imageNames[$productId][$imageId] = array("image"=>$imageName,"opt"=>$opt);
			}
		}
 		
		// Creating the products worksheet
		$worksheet =& $workbook->addWorksheet('Products');
		$worksheet->setInputEncoding ( 'UTF-8' );
		$this->populateProductsWorksheet( $worksheet, $database, $imageNames, $languageId, $priceFormat, $boxFormat, $weightFormat, $textFormat );
		$worksheet->freezePanes(array(1, 1, 1, 1));
		//exit;		
		// Creating the options worksheet
		$worksheet =& $workbook->addWorksheet('Options');
		$worksheet->setInputEncoding ( 'UTF-8' );
		$this->populateOptionsWorksheet( $worksheet, $database, $languageId, $priceFormat, $boxFormat, $weightFormat, $textFormat );
		$worksheet->freezePanes(array(1, 1, 1, 1));
		 
		// Creating the attributes worksheet
		$worksheet =& $workbook->addWorksheet('Attributes');
		$worksheet->setInputEncoding ( 'UTF-8' );
		$this->populateAttributesWorksheet( $worksheet, $database, $languageId, $boxFormat, $textFormat );
		$worksheet->freezePanes(array(1, 1, 1, 1));
		
		// Creating the specials worksheet
		$worksheet =& $workbook->addWorksheet('Specials');
		$worksheet->setInputEncoding ( 'UTF-8' );
		$this->populateSpecialsWorksheet( $worksheet, $database, $priceFormat, $boxFormat, $textFormat );
		$worksheet->freezePanes(array(1, 1, 1, 1));
		
		// Creating the discounts worksheet
		$worksheet =& $workbook->addWorksheet('Discounts');
		$worksheet->setInputEncoding ( 'UTF-8' );
		$this->populateDiscountsWorksheet( $worksheet, $database, $priceFormat, $boxFormat, $textFormat );
		$worksheet->freezePanes(array(1, 1, 1, 1));
		
		// Let's send the file
		
		$workbook->close();
		
		// Clear the spreadsheet caches
		$this->clearSpreadsheetCache();
		exit;
	}


}
?>