<?php
class Model_Addons_Allcategories{
	public $data;
	public $_array;
	public function __construct($params) {
		$front = Zend_Controller_Front::getInstance();
		//$ifpath=$front->getRequest()->getParam('path');
		$urlObj=new Model_Url('','');
        $urlParam=$urlObj->getUrlParams($front->getRequest()->getParams());
		$ifpath=$urlParam['path'];
		
		if (isset($ifpath)) {
			$parts = explode('_', (string)$ifpath);
		} else {
			$parts = array();
		}

		if (isset($parts[0])) {
			$this->data['categories_id'] = $parts[0];
		} else {
			$this->data['categories_id'] = 0;
		}

		if (isset($parts[1])) {
			$this->data['child_id'] = $parts[1];
		} else {
			$this->data['child_id'] = 0;
		}

		$catObj=new Model_Categories();
		//start
		//$sub_children1 = $catObj->getCategories('2'); commented on aug 6 2012 as found useless
		
		//end
		//$prodObj=new Model_Products();

		$this->data['categories'] = array();

		$categories = $catObj->getCategories(0);
		/*echo "<pre>";
		print_r($categories);
		echo "</pre>";*/
                $url_alias_data=Model_Url::getUrlKeyword();
           	foreach ($categories as $category) {
			$children_data = array();

			$children = $catObj->getCategories($category['categories_id']);

			foreach ($children as $child) {
				$data = array(
					'filter_category_id'  => $child['categories_id'],
					'filter_sub_category' => true
				);

				//$product_total = $prodObj->getTotalProducts($data); //commented on july 5 to remove count
                $href=$url_alias_data['category'][$child['categories_id']]!=""?$url_alias_data['category'][$child['categories_id']]:"product/category/path/". $category['categories_id'] . '_' . $child['categories_id'];
				/*start subchilder*/
				$sub_children_data = array();
$sub_children = $catObj->getCategories($child['categories_id']);
/*echo "<pre>here";
		print_r($sub_children);
		echo "</pre>";*/
//echo "size of ".sizeof($sub_children)."<br/>";
$limit=6;  //wiil show one less then the value
if(sizeof($sub_children)>=$limit)
{
	$view_more="1";
}else
{
	$view_more="0";
}
$limit_count=1;
foreach ($sub_children as $sub_child) 
{
	if($limit_count>=$limit)
	{
		break;
	}
	/*$data = array(
	'filter_category_id'  => $sub_child['categories_id'],
	'filter_sub_category' => true
	);*/

	//$product_total = $prodObj->getTotalProducts($data); //commented on july 5 to remove count
	$href_sub=$url_alias_data['category'][$sub_child['categories_id']]!=""?$url_alias_data['category'][$sub_child['categories_id']]:"product/category/path/". $category['categories_id'] . '_' . $child['categories_id'];
	$sub_children_data[] = array(
	'category_id' => $sub_child['categories_id'],
	//'name'        => $sub_child['categories_name'] . ' (' . $product_total . ')',
	'name'        => $sub_child['categories_name'],//commented on july 5 to remove count
	//'href'        => HTTP_SERVER."product/category/path/". $category['categories_id'] . '_' . $child['categories_id']);
	'href'        => HTTP_SERVER.$href_sub);
$limit_count++;
}
				/*end subchilder*/
				
				$children_data[] = array(
					'category_id' => $child['categories_id'],
					//'name'        => $child['categories_name'] . ' (' . $product_total . ')',
					'name'        => $child['categories_name'],//commented on july 5 to remove count
					//'href'        => HTTP_SERVER."product/category/path/". $category['categories_id'] . '_' . $child['categories_id']);
                                    'href'        => HTTP_SERVER.$href,
										'view_more'=>$view_more,
									'sub_child'=>$sub_children_data);
									
			}

			$data = array(
				'filter_category_id'  => $category['categories_id'],
				'filter_sub_category' => true
			);

			//$product_total = $prodObj->getTotalProducts($data); //commented on july 5 to remove count

                        $href1=$url_alias_data['category'][$category['categories_id']]!=""?$url_alias_data['category'][$category['categories_id']]:"product/category/path/". $category['categories_id'];

			$this->data['categories'][] = array(
				'category_id' => $category['categories_id'],
			//	'name'        => $category['categories_name'] . ' (' . $product_total . ')', //commented on july 5 to remove count
				'name'        => $category['categories_name'],
				'children'    => $children_data,
				//'href'        => HTTP_SERVER."product/category/path/". $category['categories_id']);
                                'href'        => HTTP_SERVER.$href1);
                    }
		$this->_array['data']=$this->data;
	}
}
?>