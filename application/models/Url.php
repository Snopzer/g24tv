<?php
	class Model_Url {
		private $url;
		private $ssl;
		private $hook = array();
        public $path;
        public $db;
		
		public function __construct($url, $ssl) {
			$this->url = $url;
			$this->ssl = $ssl;
			
		}
		
		public function link($route, $args = '', $connection = 'NONSSL') {
			if ($connection ==  'NONSSL') {
				$url = $this->url;
				} else {
				$url = $this->ssl;
			}
			
			//$url .= 'index.php?route=' . $route;
			$url .= 'index.php?route=' . $route;
			
			if ($args) {
				$url .= str_replace('&', '&amp;', '&' . ltrim($args, '&'));
			}
			
			return $this->rewrite($url);
		}
		
		public function addRewrite($hook) {
			$this->hook[] = $hook;
		}
		
		public function rewrite($url) {
			foreach ($this->hook as $hook) {
				$url = $hook->rewrite($url);
			}
			
			return $url;
		}
		
        public function getUrlId()
        {
            $url_id_data=Model_Cache::getCache(array("id"=>"url_id"));
            if (!$url_id_data)
            {
                $this->db=Zend_Db_Table::getDefaultAdapter();
                $query=$this->db->query("select * from r_url_alias");
                $query->rows=$query->fetchAll();
                foreach ($query->rows as $result)
                {
                    $this->path="";
					if($result['query']=='category')
                    {
						
						//$url_id_data[$result['query']][$result['keyword']] =$result['id'];
                        //echo "in cat";
						$path=$this->getPath($result['id']);
						//$url_id_data[$result['query']][$result['keyword']][$result['id']] =$path;
						$url_id_data[$result['query']][$result['keyword']]=array("category_id"=>$result['id'],"path"=>$path);
						
						
					}else
                    {
                        $url_id_data[$result['query']][$result['keyword']] =$result['id'];
					}
                    //$url_id_data[$result['query']][$result['keyword']] =$result['id'];
				}
                Model_Cache::getCache(array("id"=>"url_id","input"=>$url_id_data));
			}
            return $url_id_data;
		}
		
        public function getUrlKeyword()
        {
            $url_keyword_data=Model_Cache::getCache(array("id"=>"url_keyword"));
            if (!$url_keyword_data)
            {
                $this->db=Zend_Db_Table::getDefaultAdapter();
                $query=$this->db->query("select * from r_url_alias");
                $query->rows=$query->fetchAll();
                foreach ($query->rows as $result)
                {
                    $url_keyword_data[$result['query']][$result['id']] =$result['keyword'];
				}
                Model_Cache::getCache(array("id"=>"url_keyword","input"=>$url_keyword_data));
			}
			return $url_keyword_data;
		}
		
		
        /*public function getPathExt($id)
			{
			$return= $this->getPath($id);
			return $return;
		}*/
		
		public function getPath($id)
        {
			
            $this->path=$this->path!=""?$id."_".$this->path:$id;
            $this->db=Zend_Db_Table::getDefaultAdapter();
            $row=$this->db->fetchRow("select parent_id from r_categories where categories_id='".$id."'");
            if($row['parent_id']!=0)
            {
				$this->getPath($row['parent_id']);
			}
			return $this->path;
		}
		
		public function getUrlParams($arr)
		{
 			if($arr[path]=="" && $arr['category']!="")
			{
				$return=array();
				$url_data=$this->getUrlId();
				/*echo "<pre>";
					print_r($url_data);
					echo "</pre>";
				exit;*/
				
				switch($arr['controller']."_".$arr['action'])
				{
					case 'product_category':
					$return[path]=$url_data['category'][$arr['category']]['path'];
					$return=array_merge($arr,$return);
					/*
						echo "<pre>";    
						print_r($arr);
						print_r($return);
						echo "</pre>";
					exit;*/
					break;
					case 'product_product-details':
					$return[path]=$url_data['category'][$arr['category']]['path'];
					$return[product_id]=$url_data['product'][$arr['product']];
					break;
				}
			}else
			{
				$return=$arr;
			}
			return $return;
		}
		
		public function getProductPathforModule($productId)
		{
			 $this->db=Zend_Db_Table::getDefaultAdapter();
			 $query=$this->db->query("select CONCAT(c.parent_id,'_',c.categories_id) as path from r_products_to_categories pc,r_categories c  where c.categories_id=pc.categories_id and pc.products_id=".$productId);
             $path=$query->fetch();
			 return $path['path'];
		}
		public function getLink($route_arr,$args="",$connection='false')
		{
			/*start ssl*/
			if ($connection == 'false') {
				$url = HTTP_SERVER;
				} else {
				$url = HTTPS_SERVER;
			}
			/*end ssl*/
			
			/*start route*/
			switch($route_arr['controller']."_".$route_arr['action'])
			{
				case 'product_category':
				if($route_arr['path']!="")
				{
					$exp=explode("_",$route_arr['path']);
					$path= end($exp);
					$seo=$this->getUrlKeyword();
					if($seo['category'][$path]!="")
					{
						$route=$seo['category'][$path];
						//$args="";
					}else
					{
						$route="product/category";
					}
				}
				break;
				case 'product_product-details':
				$seo=$this->getUrlKeyword();
				$exp=explode("_",$route_arr['path']);
				$path= end($exp);
				if($seo['category'][$path]!="" && $seo['product'][$route_arr['product_id']]!="")
				{
					$route=$seo['category'][$path]."/".$seo['product'][$route_arr['product_id']];
					$args="";
				}else
				{
					$route="product/product-details";
				}
				break;
				default:
				$route=$route_arr['controller']."/".$route_arr['action'];
				
			}
			
			//echo "value of ".$route;
			/*end route*/
			
			$url .=$route;
			
			/*start args*/
			if ($args)
			{
				//$url .= "/".$args;
				$url .=str_replace("//","/","/".$args);
			}
			/*end args*/
			
			return $url;
		}
	}
?>