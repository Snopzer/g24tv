<?php
	session_start();
	class Model_Products
	{
		public $_arrObj=array();
		public $_date;
		public function __construct($lng='')
		{
			$this->db = Zend_Db_Table::getDefaultAdapter();
			$this->_arrObj['custObj']=new Model_Customer();
			$this->_arrObj['catObj']=new Model_Categories();
			$this->_date=date('Y-m-d H:i:s');
			
			
		}
		
		public function updateViewed($product_id) {
			$this->db->query("UPDATE r_products SET viewed = (viewed + 1) WHERE products_id = '" . (int)$product_id . "'");
			//echo "UPDATE r_products SET viewed = (viewed + 1) WHERE products_id = '" . (int)$product_id . "'";
		}
		
		public function productNameShort($name)
        {
			//echo $name." ".strlen($name)."<br/>";
			$return=strlen($name)>@constant('LIMIT_PRODUCT_NAME')?substr($name,0,@constant('LIMIT_PRODUCT_NAME'))."..":stripslashes($name);
			return $return;
		}
		
		public function getProduct($product_id) {
			
			
			if ($this->_arrObj['custObj']->isLogged()) {
				$customer_group_id = $this->_arrObj['custObj']->getCustomerGroupId();
				} else {
				$customer_group_id = DEFAULT_CGROUP;
			}
			
			$query = $this->db->query("SELECT DISTINCT *, pd.products_name AS name, p.products_image, m.manufacturers_name AS manufacturer, (SELECT price FROM r_product_discount pd2 WHERE pd2.product_id = p.products_id  AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < '".$this->_date."') AND (pd2.date_end = '0000-00-00' OR pd2.date_end > '".$this->_date."')) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT specials_new_products_price FROM r_products_specials ps WHERE ps.products_id = p.products_id     AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.start_date = '0000-00-00' OR ps.start_date < '".$this->_date."') AND (ps.expires_date = '0000-00-00' OR ps.expires_date > '".$this->_date."')) ORDER BY ps.priority ASC, ps.specials_new_products_price ASC LIMIT 1) AS special, (SELECT points FROM r_product_reward pr WHERE pr.product_id = p.products_id  and customer_group_id = '" . (int)$customer_group_id . "') AS reward, (SELECT ss.name FROM r_stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "') AS stock_status, (SELECT wcd.unit FROM r_weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "') AS weight_class, (SELECT lcd.unit FROM r_length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "') AS length_class, (SELECT AVG(reviews_rating) AS total FROM r_reviews r1 WHERE r1.products_id = p.products_id AND      r1.reviews_status = '1' GROUP BY r1.products_id) AS rating, (SELECT COUNT(*) AS total FROM r_reviews r2 WHERE r2.products_id = p.products_id AND r2.reviews_status = '1'   GROUP BY r2.products_id) AS reviews FROM r_products p LEFT JOIN r_products_description pd ON (p.products_id = pd.products_id) LEFT JOIN r_manufacturers m ON (p.manufacturers_id = m.manufacturers_id) WHERE p.products_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$_SESSION['Lang']['language_id']."' AND p.products_status = '1' AND p.del = '0' AND p.products_date_available <= '".$this->_date."'");
			
			$rows=$query->fetch();
			$product_name=$this->productNameShort($rows['products_name']);
			if (count($rows)) {
				//ECHO $rows[products_price];
				//echo $rows[special];
				//			echo "in if";
				return array(
				'products_id'       => $rows['products_id'],
				'products_name'             => $product_name,//$rows['products_name'],
				'products_name_full'             => $rows['products_name'],
				'products_description'      => $rows['products_description'],
				'meta_description' => $rows['meta_description'],
				'meta_keyword'     => $rows['meta_keyword'],
				'products_model'            => $rows['products_model'],
				'sku'              => $rows['sku'],
				//'location'         => $rows['location'],
				'products_quantity'         => $rows['products_quantity'],
				'stock_status_id'     => $rows['stock_status'],
				'image'            => $rows['products_image'],
				'manufacturers_id'  => $rows['manufacturers_id'],
				'manufacturers_name'     => $rows['manufacturers_name'],
				'price'            => ($rows['discount'] ? $rows['discount'] : $rows['products_price']),
				'special'          => $rows['special'],
				'reward'           => $rows['reward'],
				'points'           => $rows['products_points'],
				'products_tax_class_id'     => $rows['products_tax_class_id'],
				'products_date_available'   => $rows['products_date_available'],
				'weight'           => $rows['products_weight'],
				'weight_class'     => $rows['weight_class'],
				'length'           => $rows['length'],
				'width'            => $rows['width'],
				'height'           => $rows['height'],
				'length_class'     => $rows['length_class'],
				'subtract_stock'         => $rows['substract_stock'],
				'rating'           => (int)$rows['rating'],
				'reviews'          => $rows['reviews'],
				'products_minimum_quantity'          => $rows['products_minimum_quantity'],
				'sort_order'       => $rows['sort_order'],
				'products_status'           => $rows['products_status'],
				'products_date_added'       => $rows['products_date_added'],
				'products_date_modified'    => $rows['products_last_modified'],
				'products_viewed'           => $rows['products_viewed']
				
				);
				} else {
				//	echo "in else";
				return false;
			}
		}
		
		public function getProducts($data = array()) {
			/*echo "<pre>";
				print_r($data);
			echo "</pre>";*/
			
			if ($this->_arrObj['custObj']->isLogged()) {
				$customer_group_id = $this->_arrObj['custObj']->getCustomerGroupId();
				//echo "cg id ".$customer_group_id;
				} else {
				$customer_group_id = DEFAULT_CGROUP;
			}
			$cache = md5(http_build_query($data));
			//echo "value of ".$cache;
			$product_data = Model_Cache::getCache(array('id'=>'product_' .$_SESSION['Lang']['language_code'] . '_' . (int)$customer_group_id . '_' . $cache));
			
			if(!$product_data)
			{
				
				if($data['option_filter']=='')
				{
					$sql = "SELECT p.products_id, (SELECT AVG(reviews_rating) AS total FROM r_reviews r1 WHERE r1.products_id = p.products_id AND r1.reviews_status = '1' GROUP BY r1.products_id) AS rating FROM r_products p LEFT JOIN r_products_description pd ON (p.products_id = pd.products_id)  WHERE pd.language_id = '" . (int)$_SESSION['Lang']['language_id']. "' AND p.products_status = '1' and p.del='0' AND p.products_date_available <= '".$this->_date."'";
				}else
				{
					/*$sql = "SELECT distinct(p.products_id), (SELECT AVG(reviews_rating) AS total FROM r_reviews r1 WHERE r1.products_id = p.products_id AND r1.reviews_status = '1' GROUP BY r1.products_id) AS rating FROM r_products p LEFT JOIN r_products_description pd ON (p.products_id = pd.products_id)
						left join r_products_option_value pov on p.products_id=pov.product_id
					WHERE pd.language_id = '" . (int)$_SESSION['Lang']['language_id']. "' AND p.products_status = '1' AND p.products_date_available <= '".$this->_date."' and pov.option_value_id in (".$data[option_filter].")";*/
					
					//echo "value of option filter ".$data[option_filter]."<br/>";
					$exp_url_filter=explode(",",$data[option_filter]);
					$exp_url_filter=array_unique($exp_url_filter);
					//print_r($exp_url_filter);
					$count=sizeof($exp_url_filter);
					
					$f=$this->db->fetchCol("select product_id from r_products_option_value where option_value_id  in (".$data[option_filter].")");
					$arr=array_count_values($f);
					foreach($arr as $k=>$v)
					{
						if($v==$count)
						{
							$pid=$pid.$pre.$k;
							$pre=",";
						}
					}
					if($pid=="")
					{
						$pid=0;
					}
					//echo $pid;
					//exit;
					$sql = "SELECT distinct(p.products_id), (SELECT AVG(reviews_rating) AS total FROM r_reviews r1 WHERE r1.products_id = p.products_id AND r1.reviews_status = '1' GROUP BY r1.products_id) AS rating FROM r_products p LEFT JOIN r_products_description pd ON (p.products_id = pd.products_id)
					left join r_products_option_value pov on p.products_id=pov.product_id
					WHERE pd.language_id = '" . (int)$_SESSION['Lang']['language_id']. "' AND p.products_status = '1' and p.del='0' AND p.products_date_available <= '".$this->_date."' and pov.product_id in (".$pid.")";
				}
				
				if (isset($data['manu_filter']) && $data['manu_filter']) {
					$sql .= " AND p.manufacturers_id='".$data['manu_filter']."'";
				}
				
				
				if (isset($data['filter_name']) && $data['filter_name']) {
					if (isset($data['filter_description']) && $data['filter_description']) {
						$sql .= " AND (LCASE(pd.name) LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%' OR p.products_id IN (SELECT pt.product_id FROM " . DB_PREFIX . "product_tag pt WHERE pt.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pt.tag LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%') OR LCASE(pd.description) LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%')";
						} else {
						$sql .= " AND (LCASE(pd.products_name) LIKE '%" . stripslashes(strtolower($data['filter_name'])) . "%' OR p.products_id IN (SELECT pt.products_id FROM r_product_tag pt WHERE pt.language_id = '" . (int)$_SESSION['Lang']['language_id']. "' AND pt.tag LIKE '%" . stripslashes(strtolower($data['filter_name'])) . "%'))";
					}
				}
				
				if (isset($data['filter_tag']) && $data['filter_tag']) {
					$sql .= " AND p.products_id IN (SELECT pt.product_id FROM " . DB_PREFIX . "product_tag pt WHERE pt.language_id = '" . (int)$this->config->get('config_language_id') . "' AND LOWER(pt.tag) LIKE '%" . $this->db->escape(strtolower($data['filter_tag'])) . "%')";
				}
				
				if (isset($data['filter_category_id']) && $data['filter_category_id']) {
					if (isset($data['filter_sub_category']) && $data['filter_sub_category']) {
						$implode_data = array();
						//$this->_arrObj['catObj']=new Model_Categories();
						$categories = $this->_arrObj['catObj']->getCategoriesByParentId($data['filter_category_id']);
						
						foreach ($categories as $category_id) {
							$implode_data[] = "p2c.categories_id = '" . (int)$category_id . "'";
						}
						
						$sql .= " AND p.products_id IN (SELECT p2c.products_id FROM r_products_to_categories p2c WHERE " . implode(' OR ', $implode_data) . ")";
						} else {
						$sql .= " AND p.products_id IN (SELECT p2c.products_id FROM r_products_to_categories p2c WHERE p2c.categories_id = '" . (int)$data['filter_category_id'] . "')";
					}
				}
				/*start price filter*/
				
				if (isset($data['price_filter']) && $data['price_filter']) {
					
					//echo "<br/>".$sql."<br/>";
					$sql .= " AND p.products_price ".$data['price_filter'];
					
				}
				
				/*end price filter*/
				
				if (isset($data['filter_manufacturer_id'])) {
					$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
				}
				
				$sort_data = array(
				'pd.products_name',
				'p.products_model',
				'p.products_quantity',
				'p.products_price',
				'rating',
				'p.sort_order',
				'p.products_date_added'
				);
				
				if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
					if ($data['sort'] == 'pd.products_name' || $data['sort'] == 'p.products_model') {
						$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
						} else {
						$sql .= " ORDER BY " . $data['sort'];
					}
					} else {
					$sql .= " ORDER BY p.sort_order";
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
				
				//echo $sql;
				
				$product_data = array();
				
				$query = $this->db->query($sql);
				
				foreach ($query->fetchAll() as $result) {
					$product_data[$result['products_id']] = $this->getProduct($result['products_id']);
				}
				Model_Cache::getCache(array('id'=>'product_' .$_SESSION['Lang']['language_code'] . '_' . (int)$customer_group_id . '_' . $cache,"input"=>$product_data));
			}
			//echo "<pre>";
			//print_r($product_data);
			//echo "products query : ".$sql;
			//exit;
			
			return $product_data;
		}
		
		public function getProductSpecials($data = array()) {
			$this->customer=new Model_Customer();
			if ($this->customer->isLogged()) {
				$customer_group_id = $this->customer->getCustomerGroupId();
				} else {
				$customer_group_id = constant('DEFAULT_CGROUP');
			}
			
			$sql = "SELECT DISTINCT ps.products_id, (SELECT AVG(reviews_rating) FROM r_reviews r1 WHERE r1.products_id = ps.products_id AND r1.reviews_status = '1' GROUP BY r1.products_id) AS rating FROM r_products_specials ps LEFT JOIN r_products p ON (ps.products_id = p.products_id) LEFT JOIN r_products_description pd ON (p.products_id = pd.products_id)  WHERE p.products_status = '1' and p.del='0' AND p.products_date_available <= '".$this->_date."'  AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.start_date = '0000-00-00' OR ps.start_date < '".$this->_date."') AND (ps.expires_date = '0000-00-00' OR ps.expires_date > '".$this->_date."')) GROUP BY ps.products_id";
			
			$sort_data = array(
			'pd.products_name',
			'p.products_model',
			'ps.specials_new_products_price',
			'rating',
			'p.sort_order'
			);
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
					$sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
					} else {
					$sql .= " ORDER BY " . $data['sort'];
				}
				} else {
				$sql .= " ORDER BY p.sort_order";
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
			//echo $sql;
			
			$product_data = array();
			
			$query = $this->db->query($sql);
			$query->rows=$query->fetchAll();
			foreach ($query->rows as $result) {
				$product_data[$result['products_id']] = $this->getProduct($result['products_id']);
			}
			
			return $product_data;
		}
		
		public function getTopRatedProducts($limit) {
			$product_data = $this->cache->get('product.latest.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $limit);
			
			if (!$product_data) {
				$query = $this->db->query("SELECT products_id FROM r_reviews WHERE reviews_status =  '1' ORDER BY reviews_rating DESC LIMIT " . (int)$limit);
				$query_rows=$query->fetchAll();
				foreach ($query_rows as $result) {
					$product_data[$result['products_id']] = $this->getProduct($result['products_id']);
				}
				
				//$this->cache->set('product.latest.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $limit, $product_data);
			}
			
			return $product_data;
		}
		
		public function getLatestProducts($limit) {
			$product_data=Model_Cache::getCache(array("id"=>"product_latest_".$_SESSION['Lang']['language_code']."_".$limit));
			//$product_data = $this->cache->get('product.latest.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $limit);
			
			if (!$product_data) {
				$query = $this->db->query("SELECT p.products_id FROM r_products p  WHERE p.products_status = '1' AND p.del='0' and p.products_date_available <= '".$this->_date."' ORDER BY p.products_date_added DESC LIMIT " . (int)$limit);
				$query->rows=$query->fetchAll();
				foreach ($query->rows as $result) {
					$product_data[$result['products_id']] = $this->getProduct($result['products_id']);
				}
				
				//$this->cache->set('product.latest.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $limit, $product_data);
				Model_Cache::getCache(array("id"=>"product_latest_".$_SESSION['Lang']['language_code']."_".$limit,"input"=>$product_data));
			}
			
			return $product_data;
		}
		
		public function getLatestCategoryProducts($array)
		{
			$cat_prod_array=Model_Cache::getCache(array("id"=>"category_product_latest_".$_SESSION['Lang']['language_code']."_".$array[limit]."_".str_replace(',','_',$array['categories_id'])));
			if(!$cat_prod_array)
			{
				$cat_prod_array=array();
				$query = $this->db->query("select categories_name,categories_id from r_categories_description where categories_id in (".$array['categories_id'].") and language_id='".(int)$_SESSION['Lang']['language_id']."'");
				$cat_rows=$query->fetchAll();
				foreach($cat_rows as $k)
				{
					$query = $this->db->query("SELECT p.products_id FROM r_products p,r_products_to_categories p2c  WHERE p.products_status = '1' AND p.products_date_available <= '".$this->_date."' and p.del='0' and p2c.products_id=p.products_id and p2c.categories_id='".$k['categories_id']."' ORDER BY p.products_date_added DESC LIMIT " . (int)$array[limit]);
					$query->rows=$query->fetchAll();
					foreach ($query->rows as $result) 
					{
						$cat_prod_array[$k['categories_name']."#".$k['categories_id']][]= $this->getProduct($result['products_id']);
					}	
				}
				
				Model_Cache::getCache(array("id"=>"category_product_latest_".$_SESSION['Lang']['language_code']."_".$array[limit]."_".str_replace(',','_',$array['categories_id']),"input"=>$cat_prod_array));
			}
			return $cat_prod_array;
		}
		
		/*public function getLatestCategoryProducts($array) {
			//$product_data=Model_Cache::getCache(array("id"=>"category_product_latest_".$_SESSION['Lang']['language_code']."_".$array[limit]."_".str_replace(',','_',$array['categories_id'])));
			if (!$product_data) {
			
			$query = $this->db->query("SELECT p.products_id FROM r_products p,r_products_to_categories p2c  WHERE p.products_status = '1' AND p.products_date_available <= '".$this->_date."' and p2c.products_id= p.products_id and p2c.categories_id in('".$array['categories_id']."') ORDER BY p.products_date_added DESC LIMIT " . (int)$array[limit]);
			echo "SELECT p.products_id, FROM r_products p,r_products_to_categories p2c  WHERE p.products_status = '1' AND p.products_date_available <= '".$this->_date."' and p2c.products_id= p.products_id and p2c.categories_id in('".$array['categories_id']."') ORDER BY p.products_date_added DESC LIMIT " . (int)$array[limit];
			$query->rows=$query->fetchAll();
			foreach ($query->rows as $result) {
			$product_data[$result['products_id']] = $this->getProduct($result['products_id']);
			}
			//Model_Cache::getCache(array("id"=>"category_product_latest_".$_SESSION['Lang']['language_code']."_".$array[limit]."_".str_replace(',','_',$array['categories_id']),"input"=>$product_data));
			}
			
			return $product_data;
		}*/
		
		public function getPopularProducts($limit) {
			$product_data = array();
			
			$query = $this->db->query("SELECT p.products_id FROM r_products p  WHERE p.products_status = '1' AND p.products_date_available <= '".$this->_date."'  ORDER BY p.viewed, p.products_date_added DESC LIMIT " . (int)$limit);
			$query->rows=$query->fetchAll();
			foreach ($query->rows as $result) {
				$product_data[$result['products_id']] = $this->getProduct($result['products_id']);
			}
			return $product_data;
		}
		
		public function getRecentlyViewedProducts($limit) {
			if(sizeof($_SESSION['rvp'])>0)
			{
				$product_data=Model_Cache::getCache(array("id"=>"product_recentlyviewed_".$_SESSION['Lang']['language_code']."_".$limit.implode("_",$_SESSION['rvp'])));
				if (!$product_data) 
				{
					$product_data = array();
					$i=1;
					foreach (array_reverse($_SESSION['rvp']) as $k=>$v) 
					{
						if($i>$limit)
						{
							continue;
						}
						$product_data[$v] = $this->getProduct($v);
						$i++;
					}
					Model_Cache::getCache(array("id"=>"product_recentlyviewed_".$_SESSION['Lang']['language_code']."_".$limit.implode("_",$_SESSION['rvp']),"input"=>$product_data));
				}
			}else
			{
				$product_data=array();
			}
			return $product_data;
		}
		
		public function getBestSellerProducts($limit) {
			/*		$product_data = $this->cache->get('product.bestseller.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $limit);*/
			//echo $limit;
			//exit;
			$product_data=Model_Cache::getCache(array("id"=>"product_bestseller_".$_SESSION['Lang']['language_code']."_".$limit));
			if (!$product_data) 
			{
				$product_data = array();
				
				$query = $this->db->query("SELECT op.products_id, COUNT(*) AS total FROM r_orders_products op LEFT JOIN `r_orders` o ON (op.orders_id = o.orders_id) LEFT JOIN `r_products` p ON (op.products_id = p.products_id)  WHERE o.orders_status > '0' AND p.products_status = '1' and p.del='0' AND p.products_date_available <= '".$this->_date."'  GROUP BY op.products_id ORDER BY total DESC LIMIT " . (int)$limit);
				//echo "SELECT op.products_id, COUNT(*) AS total FROM r_orders_products op LEFT JOIN `r_orders` o ON (op.orders_id = o.orders_id) LEFT JOIN `r_products` p ON (op.products_id = p.products_id)  WHERE o.orders_status > '0' AND p.products_status = '1' AND p.products_date_available <= '".$this->_date."'  GROUP BY op.products_id ORDER BY total DESC LIMIT " . (int)$limit;
				//exit;
				$query->rows=$query->fetchAll();
				foreach ($query->rows as $result) {
					//echo $result['product_id'];
					
					$product_data[$result['products_id']] = $this->getProduct($result['products_id']);
				}
				
				/*$this->cache->set('product.bestseller.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $limit, $product_data);*/
				Model_Cache::getCache(array("id"=>"product_bestseller_".$_SESSION['Lang']['language_code']."_".$limit,"input"=>$product_data));
			}
			
			return $product_data;
		}
		
		public function getProductAttributes($product_id) {
			$product_attribute_group_data = array();
			
			$product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM r_product_attribute_group pa LEFT JOIN r_attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN r_attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN r_attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$_SESSION['Lang']['language_id']. "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");
			
			/*$pag_rows=$product_attribute_group_query->fetch();
			$pag_rows=$pag_rows==""?array():$pag_rows;*/
			$pag_rows=$product_attribute_group_query->fetchAll();
			foreach ($pag_rows as $product_attribute_group) {
				$product_attribute_data = array();
				
				$product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM r_product_attribute_group pa LEFT JOIN r_attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN r_attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND pa.language_id = '" . (int)$_SESSION['Lang']['language_id']. "' ORDER BY a.sort_order, ad.name");
				
				/*$pa_rows=$product_attribute_query->fetch();
				$pa_rows=$pa_rows==""?array():$pa_rows;*/
				$pa_rows=$product_attribute_query->fetchAll();
				
				foreach ($pa_rows as $product_attribute) {
					$product_attribute_data[] = array(
					'attribute_id' => $product_attribute['attribute_id'],
					'name'         => $product_attribute['name'],
					'text'         => $product_attribute['text']
					);
				}
				
				$product_attribute_group_data[] = array(
				'attribute_group_id' => $product_attribute_group['attribute_group_id'],
				'name'               => $product_attribute_group['name'],
				'attribute'          => $product_attribute_data
				);
			}
			
			return $product_attribute_group_data;
		}
		
		public function getProductOptions($product_id) {
			$product_option_data = array();
			
			$product_option_query = $this->db->query("SELECT * FROM r_products_option po LEFT JOIN `r_option` o ON (po.option_id = o.option_id) LEFT JOIN r_option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$_SESSION['Lang']['language_id']. "' ORDER BY o.sort_order");
			
			$rows=$product_option_query->fetchAll();
			
			
			foreach ($rows as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox') {
					$product_option_value_data = array();
					
					$product_option_value_query = $this->db->query("SELECT * FROM r_products_option_value pov LEFT JOIN r_option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN r_option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$_SESSION['Lang']['language_id']. "' ORDER BY ov.sort_order");
					
					$pov_rows=$product_option_value_query->fetchAll();
					foreach ($pov_rows as $product_option_value) {
						$product_option_value_data[] = array(
						'product_option_value_id' => $product_option_value['product_option_value_id'],
						'option_value_id'         => $product_option_value['option_value_id'],
						'name'                    => $product_option_value['name'],
						'quantity'                => $product_option_value['quantity'],
						'subtract'                => $product_option_value['subtract'],
						'price'                   => $product_option_value['price'],
						'price_prefix'            => $product_option_value['price_prefix'],
						'weight'                  => $product_option_value['weight'],
						'weight_prefix'           => $product_option_value['weight_prefix']
						);
					}
					$product_option_data[] = array(
					'product_option_id' => $product_option['product_option_id'],
					'option_id'         => $product_option['option_id'],
					'dependent_option'         => $product_option['dependent_option'],
					'child'         => $product_option['child'],
					'name'              => $product_option['name'],
					'type'              => $product_option['type'],
					'option_value'      => $product_option_value_data,
					'required'          => $product_option['required']
					);
					} else {
					$product_option_data[] = array(
					'product_option_id' => $product_option['product_option_id'],
					'option_id'         => $product_option['option_id'],
					'name'              => $product_option['name'],
					'type'              => $product_option['type'],
					'option_value'      => $product_option['option_value'],
					'required'          => $product_option['required']
					);
				}
			}
			
			return $product_option_data;
		}
		
		/*public function getProductOptions($product_id) {
			$product_option_data = array();
			
			$product_option_query = $this->db->query("SELECT * FROM r_products_option po LEFT JOIN `r_option` o ON (po.option_id = o.option_id) LEFT JOIN r_option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$_SESSION['Lang']['language_id']. "' ORDER BY o.sort_order");
			
			$rows=$product_option_query->fetchAll();
			foreach ($rows as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox') {
			$product_option_value_data = array();
			
			$product_option_value_query = $this->db->query("SELECT * FROM r_products_option_value pov LEFT JOIN r_option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN r_option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$_SESSION['Lang']['language_id']. "' ORDER BY ov.sort_order");
			
			$pov_rows=$product_option_value_query->fetchAll();
			foreach ($pov_rows as $product_option_value) {
			$product_option_value_data[] = array(
			'product_option_value_id' => $product_option_value['product_option_value_id'],
			'option_value_id'         => $product_option_value['option_value_id'],
			'name'                    => $product_option_value['name'],
			'quantity'                => $product_option_value['quantity'],
			'subtract'                => $product_option_value['subtract'],
			'price'                   => $product_option_value['price'],
			'price_prefix'            => $product_option_value['price_prefix'],
			'weight'                  => $product_option_value['weight'],
			'weight_prefix'           => $product_option_value['weight_prefix']
			);
			}
			
			$product_option_data[] = array(
			'product_option_id' => $product_option['product_option_id'],
			'option_id'         => $product_option['option_id'],
			'name'              => $product_option['name'],
			'type'              => $product_option['type'],
			'option_value'      => $product_option_value_data,
			'required'          => $product_option['required']
			);
			} else {
			$product_option_data[] = array(
			'product_option_id' => $product_option['product_option_id'],
			'option_id'         => $product_option['option_id'],
			'name'              => $product_option['name'],
			'type'              => $product_option['type'],
			'option_value'      => $product_option['option_value'],
			'required'          => $product_option['required']
			);
			}
			}
			
			return $product_option_data;
		}*/
		
		public function getProductDiscounts($product_id) {
			
			if ($this->_arrObj['custObj']->isLogged()) {
				$customer_group_id = $this->_arrObj['custObj']->getCustomerGroupId();
				} else {
				$customer_group_id = DEFAULT_CGROUP;
			}
			
			$query = $this->db->query("SELECT * FROM r_product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < '".$this->_date."') AND (date_end = '0000-00-00' OR date_end > '".$this->_date."')) ORDER BY quantity ASC, priority ASC, price ASC");
			
			/*$rows=$query->fetch();
				$rows=$rows==""?array():$rows;
			return $rows;*/
			return $query->fetchAll();
		}
		
		public function getProductImages($product_id) {
			$query = $this->db->query("SELECT * FROM r_products_images WHERE products_id = '" . (int)$product_id . "'");
			$rows=$query->fetchAll();
			
			return $rows;
		}
		
		public function getProductRelated($product_id) {
			$product_data = array();
			
			$query = $this->db->query("SELECT * FROM r_product_related pr LEFT JOIN r_products p ON (pr.related_id = p.products_id) WHERE pr.product_id = '" . (int)$product_id . "' AND p.products_status = '1' and p.del='0' AND p.products_date_available <= '".$this->_date."'");
			
			/*$rows=$query->fetch();
			$rows=$rows==""?array():$rows;*/
			$rows=$query->fetchAll();
			foreach ($rows as $result) {
				$product_data[$result['related_id']] = $this->getProduct($result['related_id']);
			}
			
			return $product_data;
		}
		
		public function getProductTags($product_id) {
			$query = $this->db->query("SELECT * FROM r_product_tag WHERE products_id = '" . (int)$product_id . "' AND language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'");
			
			$rows=$query->fetch();
			$rows=$rows==""?array():$rows;
			
			return $rows;
		}
		
		public function getProductLayoutId($product_id) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");
			
			if ($query->num_rows) {
				return $query->row['layout_id'];
				} else {
				return  $this->config->get('config_layout_product');
			}
		}
		
		public function getCategories($product_id) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
			
			return $query->rows;
		}
		
		public function getTotalProducts($data = array()) {
			//		echo "<pre>";
			//		print_r($data);
			//		exit;
			if($data['option_filter']=="")
			{
				$sql = "SELECT COUNT(*) AS total FROM r_products p LEFT JOIN r_products_description pd ON (p.products_id = pd.products_id)  WHERE pd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND p.products_status = '1' and p.del='0' AND p.products_date_available <= '".$this->_date."'";
			}else
			{
				//start filter
				$exp_url_filter=explode(",",$data[option_filter]);
				$exp_url_filter=array_unique($exp_url_filter);
				$count=sizeof($exp_url_filter);
				$f=$this->db->fetchCol("select product_id from r_products_option_value where option_value_id  in (".$data[option_filter].")");
				$arr=array_count_values($f);
				foreach($arr as $k=>$v)
				{
					//echo $v."==".$count."<br/>"; 
					if($v==$count)
					{
						$pid=$pid.$pre.$k;
						$pre=",";
					}
				}
				if($pid=="")
				{
					$pid=0;
				}
				//end filter
				$sql = "SELECT COUNT(*) AS total FROM r_products p ,r_products_description pd where p.products_id = pd.products_id  and pd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND p.products_status = '1' and p.del='0' AND p.products_date_available <= '".$this->_date."' and p.products_id in (".$pid.") ";
				
			}
			
			if (isset($data['manu_filter']) && $data['manu_filter'])
			{
				$sql .= " AND p.manufacturers_id='".$data['manu_filter']."'";
			}
			
			//exit;
			if (isset($data['filter_name'])) {
				if (isset($data['filter_description']) && $data['filter_description']) {
					$sql .= " AND (LCASE(pd.products_name) LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%' OR p.products_id IN (SELECT pt.products_id FROM r_product_tag pt WHERE pt.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND pt.tag LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%') OR LCASE(pd.description) LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%')";
					} else {
					$sql .= " AND (LCASE(pd.products_name) LIKE '%" . stripslashes(strtolower($data['filter_name'])) . "%' OR p.products_id IN (SELECT pt.products_id FROM r_product_tag pt WHERE pt.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND pt.tag LIKE '%" . stripslashes(strtolower($data['filter_name'])) . "%'))";
				}
			}
			//echo $sql;
			if (isset($data['filter_tag']) && $data['filter_tag']) {
				$sql .= " AND p.products_id IN (SELECT pt.products_id FROM r_product_tag pt WHERE pt.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND pt.tag LIKE '%" . $this->db->escape(strtolower($data['filter_tag'])) . "%')";
			}
			
			if (isset($data['filter_category_id']) && $data['filter_category_id']) {
				if (isset($data['filter_sub_category']) && $data['filter_sub_category']) {
					$implode_data = array();
					
					//$this->load->model('catalog/category');
					
					//$categories = $this->model_catalog_category->getCategoriesByParentId($data['filter_category_id']);
					$catObj=new Model_Categories();
					$categories = $catObj->getCategoriesByParentId($data['filter_category_id']);
					
					foreach ($categories as $category_id) {
						$implode_data[] = "p2c.categories_id = '" . (int)$category_id . "'";
					}
					
					$sql .= " AND p.products_id IN (SELECT p2c.products_id FROM r_products_to_categories p2c WHERE " . implode(' OR ', $implode_data) . ")";
					} else {
					$sql .= " AND p.products_id IN (SELECT p2c.products_id FROM r_products_to_categories p2c WHERE p2c.categories_id = '" . (int)$data['filter_category_id'] . "')";
				}
			}
			
			/*start price filter*/
			
			if (isset($data['price_filter']) && $data['price_filter']) {
				
				//echo "<br/>Total Poducts ".$sql."<br/>";
				$sql .= " AND p.products_price ".$data['price_filter'];
				
			}
			
			/*end price filter*/
			
			if (isset($data['filter_manufacturer_id'])) {
				$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
			}
			//	echo "sql".$sql."<br/>";
			$query = $this->db->query($sql);
			//echo "<pre>";
			//$res=$query->fetchColumn(0);
			//print_r($res);
			//exit;
			//return $query->row['total'];
			//echo "total products ".$query->fetchColumn(0);
			return $query->fetchColumn(0);
		}
		
		public function getTotalProductSpecials() {
			$this->customer=new Model_Customer();
			if ($this->customer->isLogged()) {
				$customer_group_id = $this->customer->getCustomerGroupId();
				} else {
				$customer_group_id = constant('DEFAULT_CGROUP');
			}
			
			$query = $this->db->query("SELECT COUNT(DISTINCT ps.products_id) AS total FROM r_products_specials ps LEFT JOIN r_products p ON (ps.products_id = p.products_id)  WHERE p.products_status = '1' and p.del='0' AND p.products_date_available <= '".$this->_date."'  AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.start_date = '0000-00-00' OR ps.start_date < '".$this->_date."') AND (ps.expires_date = '0000-00-00' OR ps.expires_date > '".$this->_date."'))");
			
			$query->row=$query->fetch();
			if (isset($query->row['total'])) {
				return $query->row['total'];
				} else {
				return 0;
			}
		}
		
		/*suresh added in jan 24 2012*/
		public function getProductsPriceLimits($data = array()) {
			$sql = "SELECT min(round(p.products_price)) as min_price,max(ceil(p.products_price)) as max_price FROM r_products p LEFT JOIN r_products_description pd ON (p.products_id = pd.products_id)  WHERE pd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND p.products_status = '1' and p.del='0' AND p.products_date_available <= '".$this->_date."'";
			
			if (isset($data['filter_name'])) {
				if (isset($data['filter_description']) && $data['filter_description']) {
					$sql .= " AND (LCASE(pd.products_name) LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%' OR p.products_id IN (SELECT pt.products_id FROM r_product_tag pt WHERE pt.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND pt.tag LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%') OR LCASE(pd.description) LIKE '%" . $this->db->escape(strtolower($data['filter_name'])) . "%')";
					} else {
					$sql .= " AND (LCASE(pd.products_name) LIKE '%" . stripslashes(strtolower($data['filter_name'])) . "%' OR p.products_id IN (SELECT pt.products_id FROM r_product_tag pt WHERE pt.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND pt.tag LIKE '%" . stripslashes(strtolower($data['filter_name'])) . "%'))";
				}
			}
			
			if (isset($data['filter_tag']) && $data['filter_tag']) {
				$sql .= " AND p.products_id IN (SELECT pt.products_id FROM r_product_tag pt WHERE pt.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND pt.tag LIKE '%" . $this->db->escape(strtolower($data['filter_tag'])) . "%')";
			}
			
			if (isset($data['filter_category_id']) && $data['filter_category_id']) {
				if (isset($data['filter_sub_category']) && $data['filter_sub_category']) {
					$implode_data = array();
					
					$catObj=new Model_Categories();
					$categories = $catObj->getCategoriesByParentId($data['filter_category_id']);
					
					foreach ($categories as $category_id) {
						$implode_data[] = "p2c.categories_id = '" . (int)$category_id . "'";
					}
					
					$sql .= " AND p.products_id IN (SELECT p2c.products_id FROM r_products_to_categories p2c WHERE " . implode(' OR ', $implode_data) . ")";
					} else {
					$sql .= " AND p.products_id IN (SELECT p2c.products_id FROM r_products_to_categories p2c WHERE p2c.categories_id = '" . (int)$data['filter_category_id'] . "')";
				}
			}
			
			if (isset($data['filter_manufacturer_id'])) {
				$sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
			}
			$query = $this->db->query($sql);
			//print_r($query->fetch());
			//return $query->fetchColumn(0);
			return $query->fetch();
		}
		/*suresh end*/
		
		
		/*start filter option*/
		public function getOptionValue($oid,$arr)
		{
			$fch=$this->db->fetchAll("select * from r_option_value_description where option_id='".(int)$oid."' and language_id='".(int)$_SESSION['Lang']['language_id']."'");
			$return_arr=array();
			if($arr['option_filter']!="")
			{
				$ext=",".$arr['option_filter'];
				$exp=array_unique(explode(",",$ext));
				
				$ext=implode(",",$exp);
				$rev_ext=$exp;
				array_shift($rev_ext);
				$rev_ext=array_flip($rev_ext);
				
			}else
			{
				$ext="";
			}
			foreach($fch as $f)
			{
				$return_arr[]=array('text'  => $f['name'],
				'value' => $f['option_value_id'],
				//'href'  =>HTTP_SERVER."product/category/path/".$arr[path]."/option_filter/".$f['option_value_id'].$ext.$arr[url]
				'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$arr[path]."/option_filter/".$f['option_value_id'].$ext.$arr[url])
				
				);
				if($arr['option_filter']!="")
				{
					if (array_key_exists($f[option_value_id],$rev_ext))
					{
						unset($rev_ext[$f[option_value_id]]);
					}
				}
			}
			if($arr['option_filter']!="")
			{
				$clear=implode(",",array_flip($rev_ext));
				$str=$clear==""?'':"/option_filter/".$clear;
				$return_arr[]=array('text'  => 'Clear',
				'value' => '',
				//'href'  =>HTTP_SERVER."product/category/path/".$arr[path]."/option_filter/".$clear.$arr[url]
				
				
				'href'  =>HTTP_SERVER.str_replace("//","/","product/category/path/".$arr[path].$str.$arr[url])
				);
			}
			return $return_arr;
		}
		
		public function getOption($oid)
		{
			$fch=$this->db->fetchCol("select name from r_option_description where option_id='".(int)$oid."' and language_id='".(int)$_SESSION['Lang']['language_id']."'");
			return $fch[0];
		}
		
		/*end filter option*/
		
		public function updateSearchKeyword($key)
		{
			if($key!="")
			{
				$query=$this->db->query("select * from r_search_keywords where keyword like '".addslashes($key)."'");
				if($query->rowCount()>0)
				{
					
					$query_row=$query->fetch();
					$this->db->query("update r_search_keywords set hits=hits+1 where search_keywords_id='".$query_row[search_keywords_id]."'");
				}else
				{
					$this->db->query("insert into r_search_keywords(keyword,hits) values('".addslashes($key)."','1')");
				}
			}
		}
		
		/*public function getFeaturedProducts($prod,$limit) {
			//$product_data = $this->cache->get('product.latest.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $limit);
			//echo "prod ".$prod." ".$limit." ".md5($prod.$limit);
			if ($prod!="") {
			$prod=explode(",",$prod);
			//print_r($prod);
			//exit;
			foreach ($prod as $k=>$v) {
			//echo $v;
			$product_data[$v] = $this->getProduct($v);
			}
			
			//$this->cache->set('product.latest.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $limit, $product_data);
			}
			return $product_data;
			}
			//before cache july 13 2012 
		*/
		public function getFeaturedProducts($prod,$limit) {
			$id="product_featured_".md5($prod.$limit);
			$product_data=Model_Cache::getCache(array("id"=>$id));
			if(!$product_data)
			{
				if ($prod!="") {
					$prod=explode(",",$prod);
					foreach ($prod as $k=>$v) {
						$product_data[$v] = $this->getProduct($v);
					}
					Model_Cache::getCache(array("id"=>$id,"input"=>$product_data,"tags"=>array("modules","general")));
				}
			}
			return $product_data;
		}
		
        public function getStockStatus($stock_id) {
            $stock=Model_Cache::getCache(array("id"=>"stock_".$stock_id."_".$_SESSION['Lang']['language_id']));
            if(!$stock)
            {
				
				$query = $this->db->query("SELECT name FROM r_stock_status WHERE stock_status_id = '" . (int)$stock_id . "' AND language_id = '".(int)$_SESSION['Lang']['language_id']."'");
				$stock=$query->fetch();
				
				Model_Cache::getCache(array("id"=>"stock_".$stock_id."_".$_SESSION['Lang']['language_id'],"input"=>$stock['name']));
				$stock=$stock['name'];
			}
            return $stock;
		}
		
		public function getAjaxSize($pid,$povid)
		{
			$query=$this->db->fetchRow("select option_value_id from r_products_option_value where product_option_value_id='".$povid."'");
			$ovid=$query[option_value_id];
			$fch=$this->db->fetchAll("SELECT * FROM r_products_option_value pov
			LEFT JOIN r_option_value ov ON ( pov.option_value_id = ov.option_value_id )
			LEFT JOIN r_option_value_description ovd ON ( ov.option_value_id = ovd.option_value_id )
			WHERE pov.product_id =  '".$pid."'
			and pov.quantity!='0'
			AND ovd.language_id =  '1'
			AND pov.base_option_value_id =  '".$ovid."'
			ORDER BY ov.sort_order
			");
			$opt="";
			$opt="<option value=''> --- Please Select --- </option>";
			/*if(sizeof($fch->rows)>0)
				{
				foreach ($fch->rows as $option)
				{
				$opt.="<option value='".$option['product_option_value_id']."'>".$option['name']."</option>";
				}
			}*/
			
			if(sizeof($fch)>0)
			{
				foreach ($fch as $k=>$option)
				{
					
					$opt.="<option value='".$option['product_option_value_id']."'>".$option['name'];
					if ($option['price']!=0) { 
						$opt.="(".$option['price_prefix'].$option['price'].")"; 
					}
					$opt.="</option>";
				}
			}
			
			return $opt;
		}
		
		public function getProductImagesColor($product_id,$color_id) {
			
			$row=$this->db->fetchRow("select option_value_id from r_products_option_value where product_option_value_id='".$color_id."'");
			$query = $this->db->fetchAll("SELECT * FROM r_products_images WHERE products_id = '" . (int)$product_id . "' and product_option_value_id='".(int)$row['option_value_id']."' order by sort_order asc");
			
			return $query;
		}
        
	}
?>
