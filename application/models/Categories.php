<?php
  class Model_Categories
  {
	public $_mainCategory=null;
	public $_languageCode=null;
	public $_languages;
	public $more=0; //0,1 value indicates to enable more link in navigation bar.
	public function __construct($lng='')
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
	}

	public function getCategory($category_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM r_categories c LEFT JOIN r_categories_description cd ON (c.categories_id = cd.categories_id)  WHERE c.categories_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'  AND c.status = '1' and del='0'");
		//return $query->fetchAll(); //modified to below on jan 24 2012 for productController.php 118
		return $query->fetch();
		//return $query->row;
	}

	public function getCategories($parent_id = 0)
	{
		$category_data = Model_Cache::getCache(array('id'=>'category_' .$parent_id));
		if(!$category_data)
		{
			$query = $this->db->query("SELECT * FROM r_categories c LEFT JOIN r_categories_description cd ON (c.categories_id = cd.categories_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '".(int)$_SESSION['Lang']['language_id']."'  AND c.status = '1' and del='0' ORDER BY c.sort_order, LCASE(cd.categories_name)");
			$category_data=$query->fetchAll();
			Model_Cache::getCache(array('id'=>'category_' .$parent_id,"input"=>$category_data));
		}
		return $category_data;
	}

	public function getCategoriesDropDown($parent_id, $level = 0) {  //userd for search dropdown
		$level++;

		$data = array();

		$results = $this->getCategories($parent_id);
		//print_r($results);
		//exit;
		foreach ($results as $result) {
			$data[] = array(
				'category_id' => $result['categories_id'],
				'name'        => str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . $result['categories_name']
			);

			$children = $this->getCategoriesDropDown($result['categories_id'], $level);

			if ($children) {
			  $data = array_merge($data, $children);
			}
		}

		return $data;
	}

	public function getParentCategoriesDropDown($parent_id, $level = 0) {  //userd for search dropdown
		$level++;

		$data = array();

		if($parent_id=='0')
		{//displays only parent categories
			$results = $this->getCategories($parent_id);
			foreach ($results as $result) {
				$data[] = array(
					'category_id' => $result['categories_id'],
					'name'        => str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level) . $result['categories_name']
				);

				$children = $this->getParentCategoriesDropDown($result['categories_id'], $level);

				if ($children) {
				  $data = array_merge($data, $children);
				}
			}
		}

		return $data;
	}

	public function getCatArray()
	{
 		$categories = array();

		$categories = $this->getCategories(0);
                $url_alias_data=Model_Url::getUrlKeyword();
		foreach ($categories as $category) {
			if ($category['top']) {
				$children_data = array();

				$children = $this->getCategories($category['categories_id']);
                                /*echo "<pre>";
                                print_r($url_alias_data);
                                echo "</pre>";*/
				foreach ($children as $child) {
					$data = array(
						'filter_category_id'  => $child['categories_id'],
						'filter_sub_category' => true
					);
					$prodObj=new Model_Products();
					$product_total = $prodObj->getTotalProducts($data); //will see later jan 6 2012
					//echo "ptotal".$product_total;
                                       // echo "cat : ".$url_alias_data['category'][$category['categories_id']]." ".$category['categories_id']."<br/>";
                                        $href=$url_alias_data['category'][$child['categories_id']]!=""?$url_alias_data['category'][$child['categories_id']]:"product/category/path/". $category['categories_id'] . '_' . $child['categories_id'];
					$children_data[] = array(
						'name'  => $child['categories_name'] . ' (' . $product_total . ')',
						'top'=>$child['top'],
					//'href'     => HTTP_SERVER."product/category/path/". $category['categories_id'] . '_' . $child['categories_id']
                                        'href'     => HTTP_SERVER.$href
					//'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
					);
				}

				// Level 1
                                $href1=$url_alias_data['category'][$category['categories_id']]!=""?$url_alias_data['category'][$category['categories_id']]:"product/category/path/". $category['categories_id'];
				$categories[] = array(
					'name'     => $category['categories_name'],
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'top'=>$category['top'],
					//'href'     => HTTP_SERVER."product/category/path/". $category['categories_id']);
                                    'href'     => HTTP_SERVER.$href1);
			}
		}
                Model_Cache::removeAllCache();
		return $categories;
	}

	public function getAllCatArray()
	{
		$categories = array();
		$categories = $this->getCategories(0);
                $url_alias_data=Model_Url::getUrlKeyword();
		foreach ($categories as $category) {
				if($category[top]=='0')
				{
					$this->more=1;
				}
 				$children_data = array();
				$children = $this->getCategories($category['categories_id']);

                                foreach ($children as $child) {
					$data = array(
						'filter_category_id'  => $child['categories_id'],
						'filter_sub_category' => true
					);

					$prodObj=new Model_Products();
					$product_total = $prodObj->getTotalProducts($data); //will see later jan 6 2012
					//echo "total".$product_total;
					$href=$url_alias_data['category'][$child['categories_id']]!=""?$url_alias_data['category'][$child['categories_id']]:"product/category/path/". $category['categories_id'] . '_' . $child['categories_id'];
                                        $children_data[] = array(
						'name'  => $child['categories_name'] . ' (' . $product_total . ')',
						'top'=>$category['top'],
						//'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
					//'href'  => HTTP_SERVER."product/category/path/". $category['categories_id'] . '_' . $child['categories_id']);
                                        'href'  => HTTP_SERVER.$href);

				}

				// Level 1
                                $href1=$url_alias_data['category'][$category['categories_id']]!=""?$url_alias_data['category'][$category['categories_id']]:"product/category/path/". $category['categories_id'];
				$categories[] = array(
					'name'     => $category['categories_name'],
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'top'=>$category['top'],
					//'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				//'href'     =>HTTP_SERVER."product/category/path/". $category['categories_id']);
                               'href'     =>HTTP_SERVER.$href1);
		}

		$categories['more']=$this->more;
		/*echo "<pre>";
		print_r($categories);
		echo "</pre>";*/
		return $categories;
	}

	public function getCategoriesByParentId($category_id) {
		$category_data = array();

		$category_data[] = $category_id;
//echo "SELECT categories_id FROM r_categories WHERE parent_id = '" . (int)$category_id . "'";
		 $cat_qry= $this->db->query("SELECT categories_id FROM r_categories WHERE parent_id = '" . (int)$category_id . "'");
$category_query=$cat_qry->fetchAll();
/*echo "<pre>";
print_r($category_query);
echo '</pre>';
echo "value of ".count($category_query);*/
		if(count($category_query)>0)
		{
		foreach ($category_query as $category) {
			$children = $this->getCategoriesByParentId($category['categories_id']);

			if ($children) {
				$category_data = array_merge($children, $category_data);
			}
		}
		}

		return $category_data;
	}
}
?>
