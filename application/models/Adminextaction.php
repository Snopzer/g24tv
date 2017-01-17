<?php

// This is a helper class to make paginating
// records easy.
class 	 Model_Adminextaction
{
	public $db;
	public $act;
	public $_date;
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$this->_date=date('Y-m-d H:i:s');
	}

                
        public function setSeoKeyword($arr)
        {
            if($arr['type']=="select")
            {
                $result=$this->db->fetchRow("select keyword from r_url_alias where query like '".$arr[query]."' and id like '".(int)$arr[id]."'");

                return $result['keyword'];
            }else if($arr['type']=="insert")
            {
                $this->db->delete('r_url_alias',"query like '".$arr['query']."' and id like '".(int)$arr['id']."'");
                $result=$this->db->fetchRow("select count(keyword) as count from r_url_alias where keyword like '".trim($arr[keyword])."'");
                $arr['keyword']=$result['count']=="0"?$arr['keyword']:$arr['keyword']."_".strtotime(date("F j, Y, h:m:s"));

                $r_url_alias=array("id"=>$arr['id'],"query"=>$arr['query'],"keyword"=>trim(str_replace(" ","_",$arr['keyword'])));
                $inst_id=$this->db->insert('r_url_alias',$r_url_alias);
            }
        }
		
		/*public function setSeoKeyword($arr)
        {
            if($arr['type']=="select")
            {
                $result=$this->db->fetchRow("select keyword from r_url_alias where query like '".$arr[query]."' and id like '".$arr[id]."'");

                return $result['keyword'];
            }else if($arr['type']=="insert")
            {
                $this->db->delete('r_url_alias',"query like '".$arr['query']."' and id like '".$arr['id']."'");
                $result=$this->db->fetchRow("select count(keyword) as count from r_url_alias where keyword like '".trim($arr[keyword])."'");
                $arr['keyword']=$result['count']=="0"?$arr['keyword']:$arr['keyword']."_".strtotime(date("F j, Y, h:m:s"));

                $r_url_alias=array("id"=>$arr['id'],"query"=>$arr['query'],"keyword"=>trim(str_replace(" ","_",$arr['keyword'])));
                $inst_id=$this->db->insert('r_url_alias',$r_url_alias);
            }
        }*/

        /*public function setSeoKeyword($arr)
        {
            if($arr['type']=="select")
            {
                $result=$this->db->fetchRow("select keyword from r_url_alias where query like '".$arr[query]."'");
                return $result['keyword'];
            }else if($arr['type']=="insert")
            {
                $this->db->delete('r_url_alias',"query like'".$arr['query']."'");
                $result=$this->db->fetchRow("select count(keyword) as count from r_url_alias where keyword like '".trim($arr[keyword])."'");
                $arr['keyword']=$result['count']=="0"?$arr['keyword']:$arr['keyword']."_".strtotime(date("F j, Y, h:m:s"));

                $r_url_alias=array("query"=>$arr['query'],"keyword"=>trim(str_replace(" ","_",$arr['keyword'])));
                $inst_id=$this->db->insert('r_url_alias',$r_url_alias);

            }
        }*/

	public function getInstalled($type) {
		$query = $this->db->fetchAll("SELECT code FROM r_extension WHERE `type` = '" . stripslashes($type) . "'");
		$arr=array();
		foreach($query as $k=>$v)
		{
			$arr[]=ucfirst($v['code']);
		}
		return $arr;
	}

		//public function getCouponHistories($coupon_id, $start = 0, $limit = 10) {

		public function getCouponHistories($coupon_id) {

		$query = $this->db->fetchAll("SELECT ch.order_id, CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS customer, ch.amount, ch.date_added FROM r_coupon_history ch LEFT JOIN r_customers c ON (ch.customer_id = c.customers_id) WHERE ch.coupon_id = '" . (int)$coupon_id . "' ORDER BY ch.date_added ASC");// LIMIT " . (int)$start . "," . (int)$limit);

		//return $query->rows;
		return $query;
	}

		public function getCouponProducts($coupon_id) {
		$coupon_product_data = array();

		$query = $this->db->fetchAll("SELECT * FROM r_coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");

		foreach ($query as $result) {
			$coupon_product_data[] = $result['product_id'];
		}

		return $coupon_product_data;
	}


	public function getTotalCouponHistories($coupon_id) {
	  	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");

		return $query->row['total'];
	}

	function delMultiImage()
	{
		if($_REQUEST['multi_image_hidden']!="")
		{
			$exp=explode(",",$_REQUEST['multi_image_hidden']);
			foreach($exp as $k=>$v)
			{
				if($v!='')
				{
					$fch=$this->db->fetchRow('select image from r_products_images where id="'.(int)$v.'"');
					unlink(PATH_TO_UPLOADS_DIR."products/".$fch[image]);
					$this->db->delete('r_products_images',"id='".$v."'");
				}
			}
		}
	}

		function uploadMultipleImage($pid)
	{
		$count=$_REQUEST['multi_image_count'];//number of images uploaded
		for($i=0;$i<$count;$i++)
		{
			if (is_uploaded_file($_FILES['multi_image']['tmp_name'][$i]))
			{
				$imgfull  = $_FILES['multi_image']['name'][$i];
				$imgpos   = strrpos($imgfull, ".");
				//$imgname  = substr($imgfull,0,$imgpos);
                                $imgname  = str_replace(" ",'',substr($imgfull,0,$imgpos));
				$imgext   = substr($imgfull,$imgpos);

				if($_FILES['multi_image']['size'][$i]/1024<MAX_UPLOAD_FILE_SIZE  && in_array($imgext,explode(",",ALLOWED_FILE_EXTENSIONS)))
				{
					$broucher = $imgname."_mult_image_".$pid.$imgext;
					copy($_FILES['multi_image']['tmp_name'][$i],PATH_TO_UPLOADS_DIR."products/".$broucher);
					//$r_products_images=array("image"=>$broucher,"products_id"=>$pid,"htmlcontent"=>$_REQUEST['multi_image_text'][$i]);
                   $r_products_images=array("image"=>$broucher,"products_id"=>$pid,"htmlcontent"=>$_REQUEST['multi_image_text'][$i],"sort_order"=>$_REQUEST['sort_order'][$i]);
                   if(@constant('DEPENDENT_OPTIONS')=='1'){
                          $r_products_images["product_option_value_id"]=$_REQUEST['product_option_value_id'][$i];
                   }

				  // echo "<pre>";
				  // print_r($r_products_images);
				 //  exit;

					$inst_id=$this->db->insert('r_products_images',$r_products_images);
				}else
				{
					//images failed to upload due to invaid size and file extension
				}
			}
		}
	}

	function getUploadedImages($id)
	{
            $fch=Model_Cache::getCache(array("id"=>"admin_uploadedimages_".$id));
            if(!$fch)
            {
		$fch=$this->db->fetchAll("select image,id,htmlcontent,sort_order,product_option_value_id from r_products_images where products_id=	'".(int)$id."'");
                 Model_Cache::getCache(array("id"=>"admin_uploadedimages_".$id,"input"=>$fch,"tags"=>array("product_multiple_images_".$id,"product","general")));
            }
            return $fch;
	}

	public function getBaseDropDown()
	{
		$rows_option=$this->db->fetchAll("select od.option_id,od.name from r_option o,r_option_description od where o.dependent_option=1 and o.option_id=od.option_id and od.language_id='1'");
		$str.="<option value='0'>None</option>";
		if(sizeof($rows_option)>0)
		{	
			
			foreach($rows_option as $k=>$v)
			{
				$str.="<optgroup label='".$v[name]."'>";
				
					$rows_option_value=$this->db->fetchAll("select option_value_id,name from r_option_value_description  where option_id='".(int)$v[option_id]."' and language_id='1'");
					foreach($rows_option_value as $k1=>$v1)
					{
						$str.="<option value='".$v1[option_value_id]."'>".$v1[name]."</option>";
					}
				$str.="</optgroup>";

			}
				/*$str=array();
				foreach($rows_option as $k=>$v)
			{
				
				
					$rows_option_value=$this->db->fetchAll("select option_value_id,name from r_option_value_description  where option_id='".$v[option_id]."'");
					foreach($rows_option_value as $k1=>$v1)
					{
						//$str.="<option value='".$v1[option_value_id]."'>".$v1[name]."</option>";
										$values[]=array("name"=>$v[name],"id"=>$v1[option_value_id]);
					}
				$str[$v[name]]=$values;

			}*/
		}

		return $str;
	}
        
	public function getBaseResultArray()
	{
		$rows_option=$this->db->fetchAll("select od.option_id,od.name from r_option o,r_option_description od where o.dependent_option=1 and o.option_id=od.option_id and od.language_id='1'");
		$str=array();
		if(sizeof($rows_option)>0)
		{	
			foreach($rows_option as $k=>$v)
			{
				$rows_option_value=$this->db->fetchAll("select option_value_id,name from r_option_value_description  where option_id='".(int)$v[option_id]."' and language_id=1");
				foreach($rows_option_value as $k1=>$v1)
				{
					$str[$v1[option_value_id]]=$v[name].":".$v1[name];
				}
			
			}

		}
		return $str;
	}

	/*
	//before changes to dependent options sep 12 2012
	function uploadMultipleImage($pid)
	{
		$count=$_REQUEST['multi_image_count'];//number of images uploaded
		for($i=0;$i<$count;$i++)
		{
			if (is_uploaded_file($_FILES['multi_image']['tmp_name'][$i]))
			{
				$imgfull  = $_FILES['multi_image']['name'][$i];
				$imgpos   = strrpos($imgfull, ".");
				//$imgname  = substr($imgfull,0,$imgpos);
                                $imgname  = str_replace(" ",'',substr($imgfull,0,$imgpos));
				$imgext   = substr($imgfull,$imgpos);

				if($_FILES['multi_image']['size'][$i]/1024<MAX_UPLOAD_FILE_SIZE  && in_array($imgext,explode(",",ALLOWED_FILE_EXTENSIONS)))
				{
					$broucher = $imgname."_mult_image_".$pid.$imgext;
					copy($_FILES['multi_image']['tmp_name'][$i],PATH_TO_UPLOADS_DIR."products/".$broucher);
					$r_products_images=array("image"=>$broucher,"products_id"=>$pid,"htmlcontent"=>$_REQUEST['multi_image_text'][$i]);
					$inst_id=$this->db->insert('r_products_images',$r_products_images);
				}else
				{
					//images failed to upload due to invaid size and file extension
				}
			}
		}
	}*/

	/*
	//before changes to dependent options sep 12 2012
	function getUploadedImages($id)
	{
            $fch=Model_Cache::getCache(array("id"=>"admin_uploadedimages_".$id));
            if(!$fch)
            {
		$fch=$this->db->fetchAll("select image,id,htmlcontent from r_products_images where products_id=	'".$id."'");
                 Model_Cache::getCache(array("id"=>"admin_uploadedimages_".$id,"input"=>$fch,"tags"=>array("product_multiple_images_".$id,"product","general")));
            }
            return $fch;
	}*/

	function fndologin()
	{
			$row=$this->db->fetchRow("select present_visit_date from r_admin where admin_id='".(int)$_SESSION['admin_id']."'");
         	$udata = array( 'present_visit_date' => $this->_date,'last_visit_date' =>$row['present_visit_date']);
		$this->db->update('r_admin', $udata, "admin_id=".(int)$_SESSION['admin_id']);

		$mod=$this->db->fetchAll("select distinct module_name from r_admin_permissions where admin_roles_id='".(int)$_SESSION['role_id']."'
		 order by sortorder asc");
		unset($arr_per);
		unset($arr_access_files);
		$arr_access_files=array('index','home','logout','single','taxrate','geozone');//single for delete,pub,unpub actions
		$arr_per['taxrate']=array('Add'=>'Y','Edit'=>'Y','Del'=>'Y','View'=>'Y');
		$arr_per['geozone']=array('Add'=>'Y','Edit'=>'Y','Del'=>'Y','View'=>'Y');

		foreach($mod as $m)
		{
		//	echo "select * from r_admin_permissions where module_name like '".$m[module_name]."'
			// order by sortorder asc<br/>";
			$file=$this->db->fetchAll("select * from r_admin_permissions where module_name like '".$m[module_name]."' and
			 admin_roles_id='".(int)$_SESSION['role_id']."'
			 order by sortorder asc");
			unset($arr_files);

			foreach($file as $f)
			{
				//$arr_files[]=$f[file_name];
				$arr_access_files[]=$f[file_name];
				$arr_files[]=array("file"=>$f[file_name],"url"=>$f[url]);
				$arr_per[$f[file_name]]=array('Add'=>$f[add],'Edit'=>$f[edit],'Del'=>$f[trash],'View'=>$f[view]);
			}
			$arr_mod_files[$m[module_name]]=$arr_files;
		}
		$_SESSION['arr_mod_files']=$arr_mod_files;
		$_SESSION['arr_files_per']=$arr_per;
		$_SESSION['arr_access_files']=$arr_access_files;
	}

	public function addLang()
	{
		$language_id='1';
		$r_languages=array("name"=>$_REQUEST['name'],"code"=>$_REQUEST['code'],"image"=>$_REQUEST['image'],"directory"=>$_REQUEST['directory'],"sort_order"=>$_REQUEST['sort_order'],"status"=>$_REQUEST['status']);
		$this->db->insert('r_languages',$r_languages);
		$inst_id=$this->db->lastInsertId();
		//r_return_action
		$fch_rad=$this->db->fetchAssoc("select * from r_return_action where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_return_action',$arr);
		}

		//r_return_reason
		$fch_rad=$this->db->fetchAssoc("select * from r_return_reason where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_return_reason',$arr);
		}

		//r_return_status
		$fch_rad=$this->db->fetchAssoc("select * from r_return_status where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_return_status',$arr);
		}

		//r_attribute_description
		$fch_rad=$this->db->fetchAssoc("select * from r_attribute_description where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_attribute_description',$arr);
		}

		//r_attribute_group_description
		$fch_rad=$this->db->fetchAssoc("select * from r_attribute_group_description where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_attribute_group_description',$arr);
		}

		//r_categories_description
		$fch_rad=$this->db->fetchAssoc("select * from r_categories_description	 where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_categories_description',$arr);
		}

		//r_cms_description
		$fch_rad=$this->db->fetchAssoc("select * from r_cms_description	 where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_cms_description',$arr);
		}

		//r_length_class_description
		$fch_rad=$this->db->fetchAssoc("select * from r_length_class_description	 where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_length_class_description',$arr);
		}

		//r_manufacturers_info
		$fch_rad=$this->db->fetchAssoc("select * from r_manufacturers_info where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_manufacturers_info',$arr);
		}

		//r_option_description
		$fch_rad=$this->db->fetchAssoc("select * from r_option_description where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_option_description',$arr);
		}

		//r_option_value_description
		$fch_rad=$this->db->fetchAssoc("select * from r_option_value_description where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_option_value_description',$arr);
		}

		//r_orders_status
		$fch_rad=$this->db->fetchAssoc("select * from r_orders_status where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_orders_status',$arr);
		}

		//r_products_description
		$fch_rad=$this->db->fetchAssoc("select * from r_products_description where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_products_description',$arr);
		}

		//r_product_attribute_group
		$fch_rad=$this->db->fetchAssoc("select * from r_product_attribute_group where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_product_attribute_group',$arr);
		}

		//r_stock_status
		$fch_rad=$this->db->fetchAssoc("select * from r_stock_status where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_stock_status',$arr);
		}

		//r_voucher_theme_description
		$fch_rad=$this->db->fetchAssoc("select * from r_voucher_theme_description where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_voucher_theme_description',$arr);
		}

		//r_weight_class_description
		$fch_rad=$this->db->fetchAssoc("select * from r_weight_class_description where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_weight_class_description',$arr);
		}

		//r_banner_image_description
		$fch_rad=$this->db->fetchAssoc("select * from r_banner_image_description where language_id='".(int)$language_id."'");
		foreach($fch_rad as $arr)
		{
			$arr['language_id']=$inst_id;
			$this->db->insert('r_banner_image_description',$arr);
		}

		return $inst_id;

	}

	public function deleteLang($v)
	{
		//$this->db->delete('r_orders',"language_id='".$v."'"); //orders not deleted wantedly
		$this->db->delete('r_download_description',"language_id='".$v."'");
		$this->db->delete('r_return_action',"language_id='".$v."'");
		$this->db->delete('r_return_reason',"language_id='".$v."'");
		$this->db->delete('r_return_status',"language_id='".$v."'");
		$this->db->delete('r_email_format',"language_id='".$v."'");
		$this->db->delete('r_stock_status',"language_id='".$v."'");
		$this->db->delete('r_manufacturers_info',"language_id='".$v."'");
		$this->db->delete('r_cms_description',"language_id='".$v."'");
		$this->db->delete('r_languages',"languages_id='".$v."'");
		$this->db->delete('r_attribute_description',"language_id='".$v."'");
		$this->db->delete('r_attribute_group_description',"language_id='".$v."'");
		$this->db->delete('r_categories_description',"language_id='".$v."'");
		$this->db->delete('r_length_class_description',"language_id='".$v."'");
		$this->db->delete('r_option_description',"language_id='".$v."'");
		$this->db->delete('r_option_value_description',"language_id='".$v."'");
		$this->db->delete('r_orders_status',"language_id='".$v."'");
		$this->db->delete('r_product_attribute_group',"language_id='".$v."'");
		$this->db->delete('r_products_description',"language_id='".$v."'");
		$this->db->delete('r_voucher_theme_description',"language_id='".$v."'");
		$this->db->delete('r_weight_class_description',"language_id='".$v."'");
	}

	function fnupload($arr)
	{
		if (is_uploaded_file($_FILES[$arr[field]]['tmp_name']))
		{

			$imgfull  = $_FILES[$arr[field]]['name'];
			$imgpos   = strrpos($imgfull, ".");
			//$imgname  = substr($imgfull,0,$imgpos);
			$imgname  = str_replace(" ",'',substr($imgfull,0,$imgpos)).rand(10,10000);
			$imgext   = substr($imgfull,$imgpos);
//&& in_array($imgext,explode(",",constant(ALLOWED_FILE_EXTENSIONS)))
/*echo "<pre>";
print_r($arr);
echo MAX_UPLOAD_FILE_SIZE,ALLOWED_FILE_EXTENSIONS;*/
			if($_FILES[$arr[field]]['size']/1024<constant('MAX_UPLOAD_FILE_SIZE')  && in_array($imgext,explode(",",constant('ALLOWED_FILE_EXTENSIONS'))))
			{


				$broucher = $imgname."_".$arr[prefix].$imgext;
				//echo "tmp_name".$_FILES[$arr[field]]['tmp_name']." path ".$arr[path].$broucher;
				copy($_FILES[$arr[field]]['tmp_name'], $arr[path].$broucher);
				//exit;
				if(isset($arr[prev_img]) && file_exists($arr[path].$pre_img))
				{
					//exit("inside".$arr[path].$arr[prev_img]);
					@unlink($arr[path].$arr[prev_img]);
				}
				//exit;

				return $broucher;
			}else
			{
				//$_SESSION['Image_Not_Supported']="";
				//$_SESSION['Image_Not_Supported']="Image not supported!!";
				//echo "image not supported";
				//exit;
				//$this->_redirect('admin/'.$arr[action].'?type='.$arr[type].'&rid='.$_REQUEST['rid'].'&msg=image is not supported either invalid format or image size!!');
				header('location:'.$arr[action].'?type=Edit&rid='.$_REQUEST['rid'].'&msg='.base64_encode('file is not supported because of invalid format or size!!'));
				exit;
				//return $arr[prev_img];
			}
		}
		else
		{
			return $arr[prev_img];
		}
	}

	function administrator_permissions($uid = '1')
	{
		$stmt=$this->db->query("select * from r_admin_permissions where admin_roles_id='".(int)$uid."' order by sortorder");
		$stmt->setFetchMode(Zend_Db::FETCH_OBJ);
		$fch=$stmt->fetchAll();
		return $fch;
	}

	function fnAddPermissions()
	{
				$data = array('role' => $_REQUEST[r_admin_roles][role],
							  'date_added' => $this->_date,
							  'status' => $_REQUEST[r_admin_roles][status]);

				$this->db->insert('r_admin_roles', $data);
				$insert_id=$this->db->lastInsertId();

				$dir_list = array();

				$result = $this->administrator_permissions();

				foreach($result as $aprow)
				{
					$dir_list[$aprow->module_name][] = $aprow->file_name;
				}

				$arrmain = explode('@@@',$_REQUEST['mainsections']);

				foreach($dir_list as $dir => $files)
				{
					if(in_array($dir, $arrmain))
					{
						foreach($files as $k => $file)
						{
							if($_REQUEST['View_'.$dir.'/'.$file] == 'Y')
							{
								$all = (($_REQUEST['All_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['All_'.$dir.'/'.$file];

								$view = (($_REQUEST['View_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['View_'.$dir.'/'.$file];

								$publish = (($_REQUEST['Publish_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['Publish_'.$dir.'/'.$file];

								$add = (($_REQUEST['Add_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['Add_'.$dir.'/'.$file];

								$edit = (($_REQUEST['Edit_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['Edit_'.$dir.'/'.$file];

								$trash = (($_REQUEST['Del_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['Del_'.$dir.'/'.$file];

								$data1 = array( 'admin_roles_id' => $insert_id,
												'module_name' => $dir,
												'file_name' => $file,
												'all' => $all,
												'view' => $view,
												'publish' => $publish,
												'add' => $add,
												'edit' => $edit,
												'trash' => $trash,
												'sortorder' => $_REQUEST['Sort_'.$dir.'/'.$file]
											  );

								$this->db->insert('r_admin_permissions', $data1);
							}
						}
					}
				}
	}
	function fnEditPermissions()
	{
		if($_REQUEST['rid']!="")
		{
			$update_data = array('role' => $_REQUEST[r_admin_roles][role],
							  'last_modified' => $this->_date,
							  'status' => $_REQUEST[r_admin_roles][status]);
			$this->db->update('r_admin_roles',$update_data,'admin_roles_id="'.(int)$_REQUEST[rid].'"');
			$this->db->delete('r_admin_permissions','admin_roles_id="'.(int)$_REQUEST[rid].'"');

			$dir_list = array();
			$result = $this->administrator_permissions();
			foreach($result as $aprow)
			{
				$dir_list[$aprow->module_name][] = $aprow->file_name;
			}
			$arrmain = explode('@@@',$_REQUEST['mainsections']);
			foreach($dir_list as $dir => $files)
			{
				if(in_array($dir, $arrmain))
				{
					foreach($files as $k => $file)
					{
						if($_REQUEST['View_'.$dir.'/'.$file] == 'Y')
						{
							$all = (($_REQUEST['All_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['All_'.$dir.'/'.$file];
							$view = (($_REQUEST['View_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['View_'.$dir.'/'.$file];
							$publish = (($_REQUEST['Publish_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['Publish_'.$dir.'/'.$file];
							$add = (($_REQUEST['Add_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['Add_'.$dir.'/'.$file];
							$edit = (($_REQUEST['Edit_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['Edit_'.$dir.'/'.$file];
							$trash = (($_REQUEST['Del_'.$dir.'/'.$file] == '') || (!in_array($dir, $arrmain))) ? 'N' : $_REQUEST['Del_'.$dir.'/'.$file];
							$data1 = array(	'admin_roles_id' => (int)$_REQUEST['rid'],
											'module_name' => $dir,
											'file_name' => $file,
											'all' => $all,
											'view' => $view,
											'publish' => $publish,
											'add' => $add,
											'edit' => $edit,
											'trash' => $trash,
											'sortorder' => $_REQUEST['Sort_'.$dir.'/'.$file]
										  );
							$this->db->insert('r_admin_permissions', $data1);
						}
					}
				}
			}
		}
	}

function _permissions($uid = '')
	{
		//$mod = $this->mod;
		$ares = $this->administrator_permissions();
		//echo "<pre>";
		//print_r($ares);

		$dir_list = array();$str = '';$aall = array();$aview = array();$apublish = array();$aadd = array();
		$aedit = array();$atrash = array();$asort = array();$umodule = array();$uall = array();
		$uview = array();$upublish = array();$uadd = array();$uedit = array();$utrash = array();$tmpmod = '';

		foreach($ares as $aprow)
		{
			//echo $aprow->module_name;
			$dir_list[$aprow->module_name][] = $aprow->file_name;
			$aall[$aprow->module_name.'/'.$aprow->file_name]     = $aprow->all;
			$aview[$aprow->module_name.'/'.$aprow->file_name]    = $aprow->view;
			$apublish[$aprow->module_name.'/'.$aprow->file_name] = $aprow->publish;
			$aadd[$aprow->module_name.'/'.$aprow->file_name]     = $aprow->add;
			$aedit[$aprow->module_name.'/'.$aprow->file_name]    = $aprow->edit;
			$atrash[$aprow->module_name.'/'.$aprow->file_name]   = $aprow->trash;
			$asort[$aprow->module_name.'/'.$aprow->file_name]    = $aprow->sortorder;

			$uall[$aprow->module_name.'/'.$aprow->file_name] = ($aprow->all == 'N') ? 'disabled' : '';
			$uview[$aprow->module_name.'/'.$aprow->file_name] = ($aprow->view == 'N') ? 'disabled' : '';
			$upublish[$aprow->module_name.'/'.$aprow->file_name] = 'disabled';
			$uadd[$aprow->module_name.'/'.$aprow->file_name]     = 'disabled';
			$uedit[$aprow->module_name.'/'.$aprow->file_name]    = 'disabled';
			$utrash[$aprow->module_name.'/'.$aprow->file_name]   = 'disabled';
		}

		$ures = $ares = $this->administrator_permissions($uid);

		foreach($ures as $uprow)
		{
			if((!in_array($uprow->module_name, $umodule)) && ($uprow->view == 'Y'))
			{
				$umodule[] = $uprow->module_name;
			}

			if($uprow->all == 'Y')
			{
				$uall[$uprow->module_name.'/'.$uprow->file_name] = 'checked';
			}

			if($uprow->view == 'Y')
			{
				$uview[$uprow->module_name.'/'.$uprow->file_name] = 'checked';
			}

			if($uprow->publish == 'Y')
			{
				$upublish[$uprow->module_name.'/'.$uprow->file_name] = 'checked';
			}
			else if(($uprow->publish == 'N') || ($uprow->publish == ''))
			{
				$upublish[$uprow->module_name.'/'.$uprow->file_name] = 'disabled';
			}

			if($uprow->add == 'Y')
			{
				$uadd[$uprow->module_name.'/'.$uprow->file_name] = 'checked';
			}
			else if(($uprow->add == 'N') || ($uprow->add == ''))
			{
				$uadd[$uprow->module_name.'/'.$uprow->file_name] = 'disabled';
			}

			if($uprow->edit == 'Y')
			{
				$uedit[$uprow->module_name.'/'.$uprow->file_name] = 'checked';
			}
			else if(($uprow->edit == 'N') || ($uprow->edit == ''))
			{
				$uedit[$uprow->module_name.'/'.$uprow->file_name] = 'disabled';
			}

			if($uprow->trash == 'Y')
			{
				$utrash[$uprow->module_name.'/'.$uprow->file_name] = 'checked';
			}
			else if(($uprow->trash == 'N') || ($uprow->trash == ''))
			{
				$utrash[$uprow->module_name.'/'.$uprow->file_name] = 'disabled';
			}
		}

		$str = '<table width="100%" border="0" cellspacing="5" cellpadding="0" class="even" style="border: #dfdfdf solid 1px; ">';

			foreach($dir_list as $dir => $files)
			{
				if(@in_array($dir, @array_unique(@$umodule)))
				{
					$checked = 'checked';
					$tmpmod .= '@@@'.$dir;
				}
				else
				{
					$checked = '';
				}

				$display = (@!in_array($dir, @array_unique(@$umodule))) ? "style='display: none;'" : '';

				$str .= '<tr class="permissions-div">';
					$str .= '<td width="100%"><input type="checkbox" name="sections" id="sections" value="'.$dir.'" onclick="javascript: fnSections();" '.$checked.'><b>&nbsp;&nbsp;'.$dir.'</b></td>';
				$str .= '</tr>';

				$str .= '<tr id="SUB_'.$dir.'" '.$display.'>';
					$str .= '<td style="padding-left: 9px;"><table border="0" cellspacing="0" cellpadding="0" class="middle">';

						$str .= '<tr class="sub-title-div">';
							$str .= '<td class="admin-role-div-name" align="left" >Section Name</td>';
							$str .= '<td class="admin-role-div"align="center">Allow/Deny All</td>';
							$str .= '<td class="admin-role-div" align="center">View</td>';
							$str .= '<td class="admin-role-div" align="center">Publish</td>';
							$str .= '<td class="admin-role-div" align="center">Add</td>';
							$str .= '<td class="admin-role-div" align="center">Edit</td>';
							$str .= '<td class="admin-role-div" align="center">Delete</td>';
						$str .= '</tr>';

						foreach($files as $file)
						{
							$str .= '<tr class="sub-links-div">';

								$str .= '<td align="left" class="admin-role-div-name"  height="22">'.$file.'</td>';

								$str .= '<td align="center"  class="admin-role-div" height="22"><input type="checkbox" name="All_'.$dir.'/'.$file.'" id="All_'.$dir.'/'.$file.'" value="'.$aall[$dir.'/'.$file].'" onclick="javascript: fnCheckAllPermissions(\''.$dir.'/'.$file.'\');" '.$uall[$dir.'/'.$file].'></td>';

								$str .= '<td align="center" class="admin-role-div" height="22"><input type="checkbox" name="View_'.$dir.'/'.$file.'" id="View_'.$dir.'/'.$file.'" value="'.$aview[$dir.'/'.$file].'" onclick="javascript: fnViewPermissions(\''.$dir.'/'.$file.'\');" '.$uview[$dir.'/'.$file].'></td>';

								$str .= '<td align="center" class="admin-role-div"  height="22"><input type="checkbox" name="Publish_'.$dir.'/'.$file.'" id="Publish_'.$dir.'/'.$file.'" value="'.$apublish[$dir.'/'.$file].'" onclick="javascript: fnPublishPermissions(\''.$dir.'/'.$file.'\');" '.$upublish[$dir.'/'.$file].'></td>';

								$str .= '<td align="center" class="admin-role-div" height="22"><input type="checkbox" name="Add_'.$dir.'/'.$file.'" id="Add_'.$dir.'/'.$file.'" value="'.$aadd[$dir.'/'.$file].'" onclick="javascript: fnAddPermissions(\''.$dir.'/'.$file.'\');" '.$uadd[$dir.'/'.$file].'></td>';

								$str .= '<td align="center"  class="admin-role-div" height="22"><input type="checkbox" name="Edit_'.$dir.'/'.$file.'" id="Edit_'.$dir.'/'.$file.'" value="'.$aedit[$dir.'/'.$file].'" onclick="javascript: fnEditPermissions(\''.$dir.'/'.$file.'\');" '.$uedit[$dir.'/'.$file].'></td>';

								$str .= '<td align="center" class="admin-role-div" height="22"><input type="checkbox" name="Del_'.$dir.'/'.$file.'" id="Del_'.$dir.'/'.$file.'" value="'.$atrash[$dir.'/'.$file].'" onclick="javascript: fnDelPermissions(\''.$dir.'/'.$file.'\');" '.$utrash[$dir.'/'.$file].'>&nbsp;<input type="hidden" name="Sort_'.$dir.'/'.$file.'" id="Sort_'.$dir.'/'.$file.'" value="'.$asort[$dir.'/'.$file].'"></td>';

							$str .= '</tr>';
						}

					$str .= '</table></td>';

				$str .= '</tr>';
			}

		$str .= '</table><br />';

		$str .= '<input type="hidden" name="mainsections" id="mainsections" value="'.substr($tmpmod, 3).'">';

		return $str;
	}

	public function getadminroledropdown($cid)
	{

		 $select = $this->db->fetchAll("select admin_roles_id,role from r_admin_roles");
		 foreach($select as $v)
		 {
			$selected=$v['admin_roles_id']==$cid?'selected':'';

			echo "<option value='".$v['admin_roles_id']."' ".$selected." >".$v['role']."</option>";
		 }
	}

	public function getThemedropdown($cid)
	{

		 $select = $this->db->fetchAll("select voucher_theme_id,name from r_voucher_theme_description where language_id='1'");
		 foreach($select as $v)
		 {
			$selected=$v['voucher_theme_id']==$cid?'selected':'';

			echo "<option value='".$v['voucher_theme_id']."' ".$selected." >".$v['name']."</option>";
		 }
	}

	public function getorderstatsdropdown($cid)
	{

		 $select = $this->db->fetchAll("select orders_status_id,orders_status_name from r_orders_status where language_id='1'");
		 foreach($select as $v)
		 {
			$selected=$v['orders_status_id']==$cid?'selected':'';

			echo "<option value='".$v['orders_status_id']."' ".$selected." >".$v['orders_status_name']."</option>";
		 }
	}

	public function getReturnStatusDropdown($cid)
	{

		 $select = $this->db->fetchAll("select return_status_id,name from r_return_status where language_id='1'");
		 foreach($select as $v)
		 {
			$selected=$v['return_status_id']==$cid?'selected':'';

			echo "<option value='".$v['return_status_id']."' ".$selected." >".$v['name']."</option>";
		 }
	}

	public function getreturnstatsdropdown($cid)
	{

		 $select = $this->db->fetchAll("select return_status_id,name from r_return_status where language_id='1'");
		 foreach($select as $v)
		 {
			$selected=$v['return_status_id']==$cid?'selected':'';

			echo "<option value='".$v['return_status_id']."' ".$selected." >".$v['name']."</option>";
		 }
	}

	public function getreturnactiondropdown($cid)
	{

		 $select = $this->db->fetchAll("select return_action_id,name from r_return_action where language_id='1'");
		$str="";
		foreach($select as $v)
		 {
			$selected=$v['return_action_id']==$cid?'selected':'';

			$str.="<option value='".$v['return_action_id']."' ".$selected." >".$v['name']."</option>";
		 }
		 return $str;
	}

	public function getreturnreasondropdown($cid)
	{

		 $select = $this->db->fetchAll("select return_reason_id,name from r_return_reason where language_id='1'");
		$str="";
		 foreach($select as $v)
		 {
			$selected=$v['return_reason_id']==$cid?'selected':'';
			$str.="<option value='".$v['return_reason_id']."' ".$selected." >".$v['name']."</option>";
		 }
		 return $str;
	}

	public function getdownloaddropdown($cid)
	{
		 $select = $this->db->fetchAll("select download_id,name from r_download_description where language_id='1'");
		if(count($select)>0)
		{
			echo "<option value=''>None</option>";
			foreach($select as $v)
			 {
				$selected=in_array($v['download_id'],$cid)?'selected':'';
				echo "<option value='".$v['download_id']."' ".$selected." >".$v['name']."</option>";
			 }
		}
	}

	public function dashboardoverview($req,$currency)
	{
		switch($req)
		{
			case 'over_all_sales':$f=$this->db->fetchRow("SELECT SUM(total) AS total FROM `r_orders` WHERE orders_status > '0'");
									return $currency->format($f['total']);
									break;
			case 'total_sales_this_year':
									$query = $this->db->fetchRow("SELECT SUM(total) AS total FROM `r_orders` WHERE orders_status > '0' AND YEAR(date_purchased) = '" . date('Y') . "'");
									return $currency->format($query['total']);
										break;
			case 'total_orders':$f=$this->db->fetchRow("select count(orders_id) as count from r_orders where invoice_id!='0'");
								   return $f['count'];	  break;

			case 'no_of_customers':$f=$this->db->fetchRow("select count(customers_id) as count from r_customers");
								   return $f['count'];	break;

			case 'customers_approval':	$f=$this->db->fetchRow("select count(customers_id) as count from r_customers where customers_approved!='1'");
									  return $f['count'];	break;

			case 'reviews_approval':$f=$this->db->fetchRow("select count(reviews_id) as count from r_reviews where reviews_status!='1'");
									  return $f['count'];	  break;

			case 'no_of_affiliates':$f=$this->db->fetchRow("select count(affiliate_id) as count from r_affiliate");
								   return $f['count'];	break;

			case 'affiliate_approval':	$f=$this->db->fetchRow("select count(affiliate_id) as count from r_affiliate where status!='1'");
									  return $f['count'];	break;
		}

	}
	public function dashboardrevenue($type,$currency)
	{
		switch($type)
		{
			case 'today':
						$date=date(Y)."-".date(m)."-".date(d);
						$fr1=$this->db->fetchRow("SELECT sum( t.value ) as rev FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id AND date( o.date_purchased ) = '".$date."' AND t.class LIKE 'subtotal'");

						 $fr2=$this->db->fetchRow("SELECT sum( t.value ) as tax FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id AND date( o.date_purchased ) = '".$date."' AND t.class LIKE 'tax'");

						$fr3=$this->db->fetchRow("SELECT sum( t.value ) as ship FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id AND date( o.date_purchased ) = '".$date."' AND t.class LIKE 'shipping'");
						$total=$fr1[rev]+$fr2[tax]+$fr3[ship];
						$rev=$currency->format($fr1['rev']);$tax=$currency->format($fr2['tax']);$ship=$currency->format($fr3['ship']);$total=$currency->format($total);
						break;

			case 'week':
						$date=date(Y)."-".date(m)."-".date(d);
						$fch_int=$this->db->fetchRow("select weekday('".$date."') as i");
						$interval=$fch_int[i];
						$fch=$this->db->fetchRow("SELECT DATE_SUB('".$date."', INTERVAL ".$interval." DAY) as wsday");
						$wsday=$fch[wsday];

						$fr1=$this->db->fetchRow("SELECT sum( t.value ) as rev FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id AND date(date_purchased) between '".$wsday."' and '".$date."' AND t.class LIKE 'subtotal'");



						$fr2=$this->db->fetchRow("SELECT sum( t.value ) as tax FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id AND date(date_purchased) between '".$wsday."' and '".$date."' AND t.class LIKE 'tax'");

						$fr3=$this->db->fetchRow("SELECT sum( t.value ) as ship FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id AND date(date_purchased) between '".$wsday."' and '".$date."' AND t.class LIKE 'shipping'");

						$total=$fr1[rev]+$fr2[tax]+$fr3[ship];
						$rev=$currency->format($fr1['rev']);$tax=$currency->format($fr2['tax']);$ship=$currency->format($fr3['ship']);$total=$currency->format($total);
						break;

			case 'month':$year=date(Y);$month=date(m);
						$fr1=$this->db->fetchRow("SELECT sum( t.value ) as rev FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id and month(date_purchased)='".$month."' and year(date_purchased)='".$year."' AND t.class LIKE 'subtotal'");

 						$fr2=$this->db->fetchRow("SELECT sum( t.value ) as tax FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id AND month(date_purchased)='".$month."' and year(date_purchased)='".$year."' AND t.class LIKE 'tax'");

						$fr3=$this->db->fetchRow("SELECT sum( t.value ) as ship FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id AND month(date_purchased)='".$month."' and year(date_purchased)='".$year."' AND t.class LIKE 'shipping'");

						$total=$fr1[rev]+$fr2[tax]+$fr3[ship];
						$rev=$currency->format($fr1['rev']);$tax=$currency->format($fr2['tax']);$ship=$currency->format($fr3['ship']);$total=$currency->format($total);
						break;

			case 'year':$year=date(Y);
						$fr1=$this->db->fetchRow("SELECT sum( t.value ) as rev FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id AND year(date_purchased)='".$year."' AND t.class LIKE 'subtotal'");

						$fr2=$this->db->fetchRow("SELECT sum( t.value ) as tax FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id AND year(date_purchased)='".$year."' AND t.class LIKE 'tax'");

						$fr3=$this->db->fetchRow("SELECT sum( t.value ) as ship FROM r_orders_total t, r_orders o WHERE o.invoice_id!='0' and t.orders_id = o.orders_id AND year(date_purchased)='".$year."' AND t.class LIKE 'shipping'");

						$total=$fr1[rev]+$fr2[tax]+$fr3[ship];
						$rev=$currency->format($fr1['rev']);$tax=$currency->format($fr2['tax']);$ship=$currency->format($fr3['ship']);$total=$currency->format($total);
						break;
		}
						if($rev=="" && $tax=="")
						{
							echo "No records found!!";
						}else{
						echo '<table class="list"><thead><tr><td class="left"><b>Revenue</b></td><td class="left"><b>Tax</b></td><td class="left"><b>Shipping</b></td><td class="left"><b>Total</b></td></tr></thead><tbody><tr><td class="left">'.$rev.'</td><td class="left">'.$tax.'</td><td class="left">'.$ship.'</td><td class="left">'.$total.'</td></tr></tbody></table>';
						}
	}

	public function getSortLable($arr)
	{
		if($arr['sortby']!="")
		{
			$par=$arr;
			array_pop($par);
			array_pop($par);
			if(sizeof($par)>1)
			{	$str="";
				$par[disType]=$par[disType]==''?'asc':$par[disType];
				foreach($par as $k=>$v)
				{
					$str.=$pre.$k."=".$v;
					$pre="&";
				}
				$query_string="?".$str;
			}
			$img=$_REQUEST['sortby']==$arr['sortby']?" <img src='".PATH_TO_ADMIN_IMAGES.$arr['disType'].".png'>":"";
			return "<a href='".$arr['file'].$query_string."'>".$arr['label'].$img."</a>";
		}else
		{
			return $arr['label'];
		}
	}

	public function dashboardinventory($type)
	{
		switch($type)
		{
			case 'products':
							//$fch1=$this->db->fetchRow("select count(products_id) as total from r_products where del='0'");
							$fch1=$this->db->fetchRow("select count(p.products_id) as total from r_products p,r_products_description pd  where p.del='0' and p.products_id=pd.products_id");
							//$fch2=$this->db->fetchRow("select count(products_id) as in_stock from r_products where products_quantity>'0' and del='0'");

							$fch2=$this->db->fetchRow("select count(p.products_id) as in_stock from r_products p,r_products_description pd where p.products_quantity>'0' and p.del='0' and p.products_id=pd.products_id");

							//$fch3=$this->db->fetchRow("select count(products_id) as active from r_products where products_status='1' and del='0'");

							$fch3=$this->db->fetchRow("select count(p.products_id) as active from r_products p,r_products_description pd where p.products_status='1' and p.del='0' and p.products_id=pd.products_id");

							//$fch4=$this->db->fetchRow("select count(products_id) as out_stock from r_products where products_quantity<='0' and del='0'");

							$fch4=$this->db->fetchRow("select count(p.products_id) as out_stock from r_products p,r_products_description pd where p.products_quantity<='0' and p.del='0' and p.products_id=pd.products_id");
							
							$out_of_stock=$fch4['out_stock']!=0?'<a href="products?qty_sym=<&prodqty=1">'.$fch4['out_stock'].'</a>':'0';
							echo '<table class="list"><thead><tr><td class="center"><b>Total</b></td><td class="center"><b>In Stock</b></td><td class="center"><b>Active</b></td><td class="center"><b>Out of stock</b></td></tr></thead><tbody><tr><td class="center">'.$fch1['total'].'</td><td class="center">'.$fch2['in_stock'].'</td><td class="center">'.$fch3['active'].'</td><td class="center">'.$out_of_stock.'</td></tr></tbody></table>';
							break;

			case 'categories':
							$fch=$this->db->fetchAll("SELECT count( p.categories_id ) AS total, p.categories_id, c.status, cd.categories_name
							FROM r_products_to_categories p, r_categories_description cd, r_categories c WHERE c.del='0' and cd.categories_id = p.categories_id AND cd.categories_id = c.categories_id AND cd.language_id = '1' GROUP BY p.categories_id ORDER BY total DESC");

							//$fch1=$this->db->fetchRow("select count(*) as count from r_categories where del='0'");
							//$fch2=$this->db->fetchRow("select count(*) as count from r_categories where status='1' and del='0'");

							echo '<table class="list"><thead><tr><td class="left" colspan="5"><b>Top Categories</b><span style="float:right"></span></td></tr></thead><thead><tr><td class="left"><b>#</b></td><td class="left"><b>Categories</b></td><td class="left"><b>No of products</b></td><td class="left"><b>Status</b></td></tr></thead><tbody>';
							$i=1;
							//echo count($fch);
							if(count($fch)>0)
							{
								foreach($fch as $s)
								{
									$status=$s['status']=='1'?'Enable':'Disable';
									echo '<tr><td class="left">'.$i.'</td><td class="left"><a href="'.ADMIN_URL_CONTROLLER.'categories?type=Edit&rid='.$s[categories_id].'">'.$s['categories_name'].'</a></td><td class="left">'.$s['total'].'</td><td class="left">'.$status.'</td></tr>';
								$i++;
								}
							}else
							{
								echo "<tr><td class='left' colspan='5'>No records found!!</td></tr>";
							}
							echo '</tbody></table>';
							break;


			case 'manufacturers':

                   //$fch1=$this->db->fetchAll("SELECT count( products_id ) AS total, m.manufacturers_name,m.manufacturers_id FROM r_products p, r_manufacturers m WHERE p.del='0' and m.manufacturers_id = p.manufacturers_id GROUP BY p.manufacturers_id ORDER BY total DESC");
	$fch1=$this->db->fetchAll("SELECT count( p.products_id ) AS total, m.manufacturers_name, m.manufacturers_id FROM r_manufacturers m LEFT JOIN r_products p ON m.manufacturers_id = p.manufacturers_id AND p.del = '0' GROUP BY m.manufacturers_name
ORDER BY total DESC");


                                            $fch2=$this->db->fetchRow("select count(*) as count from r_manufacturers");

                                            echo '<table class="list"><thead><tr><td class="left" colspan="5"><b>Manufacturers['.$fch2['count'].']</b></td></tr></thead><thead><tr><td class="left"><b>#</b></td><td class="left"><b>Manufacturer</b></td><td class="left"><b>No of products</b></td></tr></thead><tbody>';
                                            $i=1;
                                            //echo count($fch);
                                            if(count($fch1)>0)
                                            {
                                                    foreach($fch1 as $s)
                                                    {
                                                            echo '<tr><td class="left">'.$i.'</td><td class="left"><a href="manufacturer?type=Edit&rid='.$s['manufacturers_id'].'">'.$s['manufacturers_name'].'</a></td><td class="left">'.$s['total'].'</td></tr>';
                                                    $i++;
                                                    }
                                            }else
                                            {
                                                    echo "<tr><td class='left' colspan='5'>No records found!!</td></tr>";
                                            }
                                            echo '</tbody></table>';
                                            break;
		}
	}

	public function dashboardu_p($type)
	{
		switch($type)
		{
			case 'most_viewed_products':
								/*$count=$this->db->fetchRow("SELECT sum( products_viewed ) AS sum, count( products_viewed ) AS count FROM r_products_description	WHERE products_viewed != '0'");

								$total_count = $count[count];$total_sum = $count[sum];

								$fch =$this->db->fetchAll("select l.name,l.image,p.products_name,p.products_viewed,round((p.products_viewed/".$total_sum.")*100,2) as percent
								from r_products_description p,r_languages l where p.products_viewed!='0' and p.language_id=l.languages_id order by	percent desc
								limit 5");*/
								$count=$this->db->fetchRow("SELECT sum( viewed ) AS sum, count( viewed ) AS count FROM r_products	WHERE viewed != '0'");

								$total_count = $count[count];$total_sum = $count[sum];
								if($total_sum>0)
								{
									$fch=$this->db->fetchAll("select pd.products_name,p.viewed,p.products_id,round((p.viewed/".$total_sum.")*100,2) as percent
									from r_products p left join r_products_description pd on p.products_id=pd.products_id  and p.viewed!='0' and pd.language_id ='1' order by percent desc limit 5");

									echo '<table class="list"><thead><tr><td class="left" colspan="5"><b>5 most viewed products</b></td></tr></thead><thead><tr><td class="left"><b>#</b></td><td class="left"><b>Product Title</b></td><td class="left"><b>Viewed</b></td><td class="left"><b>Percent</b></td></tr></thead><tbody>';
									$i=1;
									//echo count($fch);
									if(count($fch)>0)
									{
										foreach($fch as $s)
										{
											echo '<tr><td class="left">'.$i.'</td><td class="left"><a href="products?type=Edit&rid='.$s['products_id'].'">'.$s['products_name'].'</a></td><td class="left">'.$s['viewed'].'</td><td class="left">'.$s[percent].'%</td></tr>';
										$i++;
										}
									}else
									{
										echo "<tr><td class='left' colspan='5'>No records found!!</td></tr>";
									}
									echo '</tbody></table>';
								}
								break;

			case 'best_seller':
								$fch=$this->db->fetchAll("SELECT op.products_id, p.products_model, pd.products_name, sum(op.products_quantity) AS total FROM r_orders_products op LEFT JOIN `r_orders` o ON ( op.orders_id = o.orders_id ) LEFT JOIN `r_products` p ON ( op.products_id = p.products_id ) LEFT JOIN r_products_description pd ON ( op.products_id = pd.products_id ) WHERE pd.language_id = '1' AND o.orders_status > '0' AND p.products_status = '1' AND p.products_date_available <= '".$this->_date."' GROUP BY op.products_id ORDER BY total DESC LIMIT 5");

								echo '<table class="list"><thead><tr><td class="left" colspan="5"><b>5 Best selling products</b></td></tr></thead><thead><tr><td class="left"><b>Product Name</b></td><td class="left"><b>Model</b></td><td class="left"><b>Total Sales</b></td><td class="left"><b>View</b></td></tr></thead><tbody>';
								$i=1;
								//echo count($fch);
								if(count($fch)>0)
								{
									foreach($fch as $s)
									{
										echo '<tr><td class="left">'.$s['products_name'].'</td><td class="left">'.$s['products_model'].'</td><td class="left">'.$s['total'].'</td><td class="left"><a href="'.ADMIN_URL_CONTROLLER.'products?type=Edit&rid='.$s[products_id].'">View</a></td></tr>';
									$i++;
									}
								}else
								{
									echo "<tr><td class='left' colspan='5'>No records found!!</td></tr>";
								}
								echo '</tbody></table>';
								break;

			case 'new_customers':
								$fch =$this->db->fetchAll("select concat(c.customers_firstname,' ',c.customers_lastname) as name,c.customers_id,c.customers_status,customers_date_account_created as doc from r_customers c order by customers_date_account_created desc limit 5");

								echo '<table class="list"><thead><tr><td class="left" colspan="5"><b>newly Registered customers</b></td></tr></thead><thead><tr><td class="left"><b>Full Name</b></td><td class="left"><b>Date Registered</b></td><td class="left"><b>Status</b></td><td class="left"><b>View</b></td></tr></thead><tbody>';
								if(count($fch)>0)
								{
									foreach($fch as $s)
									{
										$status=$s['customers_status']=='1'?'Active':'Deactive';
										echo '<tr><td class="left">'.$s[name].'</td><td class="left">'.$s[doc].'</td><td class="left">'.$status.'</td><td class="left"><a href="'.ADMIN_URL_CONTROLLER.'customers?type=Edit&rid='.$s[customers_id].'">View</a></td></tr>';
									}
								}else
								{
									echo '<tr><td class="left" colspan="4">No Records Found!!</td></tr>';
								}
								echo '</tbody></table>';
								break;
		}
	}

 
	public function setEncryptPassword($val)
	{
		$pwd1=md5(strrev($val));
		$pwd2=str_rot13($val);
		return $pwd1.$pwd2;
	}

	public function getDecryptPassword($val)
	{
		return str_rot13(substr($val,32));
	}

	public function getUnique($type,$value)
	{
		switch($type)
		{
			case 'admin_username':
								$col=$this->db->fetchRow("select count(admin_name) as admin_name_count from r_admin where admin_name like '".$value."'");
								$return=$col[admin_name_count];
								break;

			case 'admin_email':
								$col=$this->db->fetchRow("select count(email) as admin_email_count from r_admin where email like '".$value."'");
								$return=$col[admin_email_count];
								break;
		}
		return $return;
	}

	public function dashboardorders($type,$currency)
	{
		switch($type)
		{
			case 'today':
						$date=date(Y)."-".date(m)."-".date(d);
						echo '<table class="list"><thead><tr><td class="left" colspan="5"><b>Latest 5 orders</b><span style="float:right">'.$date.'</span></td></tr></thead><thead><tr><td class="left"><b>Order Id</b></td><td class="left"><b>Customer</b></td><td class="left"><b>Date purchased</b></td><td class="left"><b>Total</b></td><td class="left"><b>Action</b></td></tr></thead><tbody>';

						$s=$this->db->fetchAll("select orders_id,customers_name,customers_id,date_purchased,total from r_orders where invoice_id!='0' and date(date_purchased)='".$date."' order by date_purchased desc limit 5");
 						if(count($s)>0)
						{
							foreach($s as $s)
							{
								echo '<tr><td class="left">'.$s['orders_id'].'</td><td class="left"><a href="customers?type=Edit&rid='.$s['customers_id'].'">'.$s['customers_name'].'</td><td class="left">'.$s['date_purchased'].'</td><td class="left">'.$currency->format($s[total]).'</td><td class="left"><a href="orders?type=Edit&rid='.$s['orders_id'].'" >view</a></td></tr>';
							}
							echo '</tbody></table>';
						}else
						{
							echo "<tr><td class='left' colspan='5'>No records found!!</td></tr></tbody></table>";
						}

						break;

			case 'last5':
						$s=$this->db->fetchAll("select orders_id,customers_id,customers_name,date_purchased,total from r_orders  where invoice_id!='0' order by date_purchased desc limit 5");
						echo '<table class="list"><thead><tr><td class="left" colspan="5"><b>Last 5 orders</b><span style="float:right">'.$date.'</span></td></tr></thead><thead><tr><td class="left"><b>Order Id</b></td><td class="left"><b>Customer</b></td><td class="left"><b>Date purchased</b></td><td class="left"><b>Total</b></td><td class="left"><b>Action</b></td></tr></thead><tbody>';

 						if(count($s)>0)
						{

							foreach($s as $s)
							{
								echo '<tr><td class="left">'.$s['orders_id'].'</td><td class="left"><a href="customers?type=Edit&rid='.$s['customers_id'].'">'.$s['customers_name'].'</a></td><td class="left">'.$s['date_purchased'].'</td><td class="left">'.$currency->format($s[total]).'</td><td class="left"><a href="orders?type=Edit&rid='.$s['orders_id'].'" >view</a></td></tr>';
							}
							echo '</tbody></table>';
						}else
						{
							echo "<tr><td class='left' colspan='5'>No records found!!</td></tr></tbody></table>";
						}

						break;

			case 'week':
						$date=date(Y)."-".date(m)."-".date(d);
						$fch_int=$this->db->fetchRow("select weekday('".$date."') as i");
						$interval=$fch_int[i];
						$fch=$this->db->fetchRow("SELECT DATE_SUB('".$date."', INTERVAL ".$interval." DAY) as wsday");
						$wsday=$fch[wsday];

						echo '<table class="list"><thead><tr><td class="left" colspan="5"><b>Latest 5 orders</b><span style="float:right">'.$wsday.' To '.$date.'</span></td></tr></thead><thead><tr><td class="left"><b>Order Id</b></td><td class="left"><b>Customer</b></td><td class="left"><b>Date purchased</b></td><td class="left"><b>Total</b></td><td class="left"><b>Action</b></td></tr></thead><tbody>';

						$s=$this->db->fetchAll("select orders_id,customers_name,date_purchased,total from r_orders where date(date_purchased) between '".$wsday."' and '".$date."' order by date_purchased desc limit 5");
						if(count($s)>0)
						{
							foreach($s as $s)
							{
								echo '<tr><td class="left">'.$s['orders_id'].'</td><td class="left">'.$s['customers_name'].'</td><td class="left">'.$s['date_purchased'].'</td><td class="left">'.$currency->format($s[total]).'</td><td class="left"><a href="orders?type=Edit&rid='.$s['orders_id'].'" >view</a></td></tr>';
							}
							echo '</tbody></table>';
						}else
						{
							echo "tr><td class='left' colspan='5'>No records found!!</td></tr></tbody></table>";
						}
						break;

			case 'month':
						$year=date(Y);$month=date(m);
						echo '<table class="list"><thead><tr><td class="left" colspan="5"><b>Latest 5 orders</b><span style="float:right">'.date(M).'/'.date(Y).'</span></td></tr></thead><thead><tr><td class="left"><b>Order Id</b></td><td class="left"><b>Customer</b></td><td class="left"><b>Date purchased</b></td><td class="left"><b>Total</b></td><td class="left"><b>Action</b></td></tr></thead><tbody>';
						$s=$this->db->fetchAll("select orders_id,customers_name,date_purchased,total from r_orders where month(date_purchased)='".$month."' and year(date_purchased)='".$year."' order by date_purchased desc limit 10");
						if(count($s)>0)
						{
							foreach($s as $s)
							{
								echo '<tr><td class="left">'.$s['orders_id'].'</td><td class="left">'.$s['customers_name'].'</td><td class="left">'.$s['date_purchased'].'</td><td class="left">'.$currency->format($s[total]).'</td><td class="left"><a href="orders?type=Edit&rid='.$s['orders_id'].'" >view</a></td></tr>';
							}
							echo '</tbody></table>';
						}else
						{
							echo "tr><td class='left' colspan='5'>No records found!!</td></tr></tbody></table>";
						}
						break;


			case 'year':$year=date(Y);
						echo '<table class="list"><thead><tr><td class="left" colspan="5"><b>Latest 5 orders</b><span style="float:right">'.$year.'</span></td></tr></thead><thead><tr><td class="left"><b>Order Id</b></td><td class="left"><b>Customer</b></td><td class="left"><b>Date purchased</b></td><td class="left"><b>Total</b></td><td class="left"><b>Action</b></td></tr></thead><tbody>';
						$s=$this->db->fetchAll("select orders_id,customers_name,date_purchased,total from r_orders where year(date_purchased)='".$year."' order by date_purchased desc limit 5");
						if(count($s)>0)
						{
							foreach($s as $s)
							{
								echo '<tr><td class="left">'.$s['orders_id'].'</td><td class="left">'.$s['customers_name'].'</td><td class="left">'.$s['date_purchased'].'</td><td class="left">'.$currency->format($s[total]).'</td><td class="left"><a href="orders?type=Edit&rid='.$s['orders_id'].'" >view</a></td></tr>';
							}
							echo '</tbody></table>';
						}else
						{
							echo "tr><td class='left' colspan='5'>No records found!!</td></tr></tbody></table>";
						}
						break;
		}
	}

	public function banners_history($rid)
	{
		$r=$this->db->fetchRow("select sum(banners_shown) as view,sum(banners_clicked) as click from r_banners_history where banners_id='".(int)$rid."'");
		return $r;
	}

	public function getAdminaction()
	{
		$this->act=new Model_Adminaction();
		return $this->act;
	}

	public function mystock_fields($id)
	{
		//all fields related to mystore will be here
		$this->getAdminaction();
		//echo "value of ".$id."<br/>";
		$cong1=$this->act->getEditdetails('r_configuration','configuration_group_id',$id);
		/*echo "<pre>";
		print_r($cong1);
		echo "</pre>";*/
		foreach($cong1 as $k)
		{
		 	if ($this->act->tep_not_null($k['use_function']))
		 	{
	      		$use_function = $k['use_function'];
	      		if (preg_match('/->/', $use_function))
	      		{
	        		$class_method = explode('->', $use_function);
			        if (!is_object(${$class_method[0]}))
			        {
	          			include(DIR_WS_CLASSES . $class_method[0] . '.php');
	          			${$class_method[0]} = new $class_method[0]();
	        		}
	        		$cfgValue = $this->tep_call_function($class_method[1], $k['configuration_value'], ${$class_method[0]});
	      		}
	      		 else
	      		{
	      			$cfgValue = $this->tep_call_function($use_function, $k['configuration_value'],'Model_Adminextaction');
	        	}
	      } else
	      {
	      $cfgValue = $k['configuration_value'];
	      }
			include_once APPLICATION_PATH.'/models/functions.php';
			if($k['set_function'])
			{
				/*echo '<p><label>'.$k['configuration_title'].'</label>';
				eval($k['set_function'] . "'" . $k['configuration_value'] . "', '" . $k['configuration_key'] . "');");
				echo '<br/><i>Note:'.$k['configuration_description'].'</i></p>';*/
				echo '<p><label>'.$k['configuration_title'].'</label>';
				 if($k['configuration_key']=='SERVER_GOOGLE_ANALYTICS')
                 {
					eval($k['set_function'] . '"' . addslashes($k['configuration_value']) . '", "' . $k['configuration_key'] . '");');
				 }else
				 { 
					 eval($k['set_function'] . '"' . $k['configuration_value'] . '", "' . $k['configuration_key'] . '");');
				 }
				echo '<br/><i>Note:'.$k['configuration_description'].'</i></p>';
			}else
			{
				echo $this->act->field(array('lable'=>$k['configuration_title'],'input_title'=>'configuration['.$k['configuration_key'].']',
				'desc'=>$k['configuration_description'],'input_type'=>'input_text','val'=>stripslashes($cfgValue),"req"=>'0'));
			}
		}
	}

  function tep_call_function($function, $parameter, $object = '') {
    if ($object == '') {
      return call_user_func('$this->'.$function, $parameter);
    } else {
      return call_user_func(array($object, $function), $parameter);
    }
  }

   function tep_get_country_name($country_id) {

  $country = $this->db->fetchRow("select countries_name from r_countries where countries_id = '" . (int)$country_id . "'");
    if (!count($country)) {
      return $country_id;
    } else {
      return $country['countries_name'];
    }
  }

  function tep_cfg_get_zone_name($zone_id) {
 	$zone = $this->db->fetchRow("select zone_name from r_zones where zone_id = '" . (int)$zone_id . "'");
    if (!count($zone)) {
      return $zone_id;
    } else {
      return $zone['zone_name'];
    }
  }

	function tep_cfg_get_timezone_name($tz)
	{
		return $tz;
	}

function tep_cfg_get_cgroup_name($zone_id) {
 	$zone = $this->db->fetchRow("select name from r_customer_group where customer_group_id = '" . (int)$zone_id . "'");
    if (!count($zone)) {
      return $zone_id;
    } else {
      return $zone['name'];
    }
  }

  function tep_cfg_get_lclass_name($zone_id) {
 	$zone = $this->db->fetchRow("select title from r_length_class_description where  language_id='1' and length_class_id = '" . (int)$zone_id . "'");
    if (!count($zone)) {
      return $zone_id;
    } else {
      return $zone['title'];
    }
  }

   function tep_cfg_get_out_stock_status_name($zone_id) {
 	$zone = $this->db->fetchRow("select name from r_stock_status where  language_id='1' and stock_status_id = '" . (int)$zone_id . "'");
    if (!count($zone)) {
      return $zone_id;
    } else {
      return $zone['name'];
    }
  }

   function tep_cfg_get_order_status_name($zone_id) {
 	$zone = $this->db->fetchRow("select orders_status_name as name from r_orders_status where  language_id='1' and orders_status_id = '" . (int)$zone_id . "'");
    if (!count($zone)) {
      return $zone_id;
    } else {
      return $zone['name'];
    }
  }

	function tep_cfg_get_wclass_name($zone_id) {
 	$zone = $this->db->fetchRow("select title from r_weight_class_description where language_id='1' and weight_class_id = '" . (int)$zone_id . "'");
    if (!count($zone)) {
      return $zone_id;
    } else {
      return $zone['title'];
    }
  }

  function tep_cfg_get_page_name($page_id) {
 	$page = $this->db->fetchRow("select title from r_cms_description where language_id='1' and page_id = '" . (int)$page_id . "'");

    if (!count($page)) {
      return $page_id;
    } else {
	  return $page['title'];
    }
  }

  function getWclassdropdown($cid)
  {
		$select = $this->db->fetchAll("select weight_class_id,title from r_weight_class_description where language_id=1");
        //echo "<option value='0'>None</option>"; //commented on aug 24 2012 as got issue while add to cart not added becoz of weight id 0
		foreach($select as $v)
		{
			$selected=$v['weight_class_id']==$cid?'selected':'';

			echo "<option value='".$v['weight_class_id']."' ".$selected." >".$v['title']."</option>";
		 }
  }
  function getstockstatusdropdown($cid)
  {
		$select = $this->db->fetchAll("select stock_status_id,name from r_stock_status where language_id=1");
		
                foreach($select as $v)
		{
			$selected=$v['stock_status_id']==$cid?'selected':'';

			echo "<option value='".$v['stock_status_id']."' ".$selected." >".$v['name']."</option>";
		 }
  }

  function getTags($id)
  {
	  if($id!="")
	  {
		  $str="";
			$select = $this->db->fetchAll("select tag from r_product_tag where products_id='".(int)$id."'");
			foreach($select as $v)
			{
				$str=$str.$pre.$v['tag'];
				$pre=",";
			}
			return $str;
	  }
 }

   function getLclassdropdown($cid)
  {
		$select = $this->db->fetchAll("select length_class_id,title from r_length_class_description where language_id=1");
		//echo "<option value='0'>None</option>";//commented on aug 24 2012 as got issue while add to cart not added becoz of length id 0
                foreach($select as $v)
		{
			$selected=$v['length_class_id']==$cid?'selected':'';

			echo "<option value='".$v['length_class_id']."' ".$selected." >".$v['title']."</option>";
		 }
  }

   static function tep_get_order_status_name($order_status_id, $language_id = '') {
    global $languages_id;

    if ($order_status_id < 1) return TEXT_DEFAULT;
    if (!is_numeric($language_id)) $language_id = $languages_id;
		$status = $this->db->fetchRow("select orders_status_name from r_orders_status where orders_status_id = '" . (int)$order_status_id . "' and language_id = '" . (int)$language_id . "'");
        return $status['orders_status_name'];
  }

  static function tep_get_zone_class_title($zone_class_id) {
    if ($zone_class_id == '0') {
      return TEXT_NONE;
    } else {
    	$classes = $this->db->fetchRow("select geo_zone_name from r_geo_zones where geo_zone_id = '" . (int)$zone_class_id . "'");
        return $classes['geo_zone_name'];
    }
  }

 static function tep_get_tax_class_title($tax_class_id) {
    if ($tax_class_id == '0') {
      return TEXT_NONE;
    } else {

      $classes = $this->db->fetchRow("select tax_class_title from r_tax_class where tax_class_id = '" . (int)$tax_class_id . "'");
      return $classes['tax_class_title'];
    }
  }

  public function getBannerImages($banner_id) {
		$banner_image_data = array();

		$banner_image_query = $this->db->query("SELECT * FROM r_banner_image WHERE banner_id = '" . (int)$banner_id . "'");
		$banner_image_query_rows=$banner_image_query->fetchAll();
		foreach ($banner_image_query_rows as $banner_image) {
			$banner_image_description_data = array();

			$banner_image_description_query = $this->db->query("SELECT * FROM r_banner_image_description WHERE banner_image_id = '" . (int)$banner_image['banner_image_id'] . "' AND banner_id = '" . (int)$banner_id . "'");
			$banner_image_description_query_rows=$banner_image_description_query->fetchAll();
			foreach ($banner_image_description_query_rows as $banner_image_description) {
				$banner_image_description_data[$banner_image_description['language_id']] = array('title' => $banner_image_description['title']);
			}

			$banner_image_data[] = array(
				'banner_image_description' => $banner_image_description_data,
				'link'                     => $banner_image['link'],
				'image'                    => $banner_image['image']
			);
		}

		return $banner_image_data;
	}

	public function getProduct($product_id) {
			$customer_group_id = DEFAULT_CGROUP;
		$query = $this->db->query("SELECT DISTINCT *, pd.products_name AS name, p.products_image, m.manufacturers_name AS manufacturer, (SELECT price FROM r_product_discount pd2 WHERE pd2.product_id = p.products_id  AND pd2.customer_group_id = '" . (int)$customer_group_id . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < '".$this->_date."') AND (pd2.date_end = '0000-00-00' OR pd2.date_end > '".$this->_date."')) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT specials_new_products_price FROM r_products_specials ps WHERE ps.products_id = p.products_id     AND ps.customer_group_id = '" . (int)$customer_group_id . "' AND ((ps.start_date = '0000-00-00' OR ps.start_date < '".$this->_date."') AND (ps.expires_date = '0000-00-00' OR ps.expires_date > '".$this->_date."')) ORDER BY ps.priority ASC, ps.specials_new_products_price ASC LIMIT 1) AS special, (SELECT points FROM r_product_reward pr WHERE pr.product_id = p.products_id  and customer_group_id = '" . (int)$customer_group_id . "') AS reward, (SELECT ss.name FROM r_stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '1') AS stock_status, (SELECT wcd.unit FROM r_weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '1') AS weight_class, (SELECT lcd.unit FROM r_length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '1') AS length_class, (SELECT AVG(reviews_rating) AS total FROM r_reviews r1 WHERE r1.products_id = p.products_id AND      r1.reviews_status = '1' GROUP BY r1.products_id) AS rating, (SELECT COUNT(*) AS total FROM r_reviews r2 WHERE r2.products_id = p.products_id AND r2.reviews_status = '1'   GROUP BY r2.products_id) AS reviews FROM r_products p LEFT JOIN r_products_description pd ON (p.products_id = pd.products_id) LEFT JOIN r_manufacturers m ON (p.manufacturers_id = m.manufacturers_id) WHERE p.products_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$_SESSION['Lang']['language_id']."' AND p.products_status = '1' AND p.del = '0' AND p.products_date_available <= '".$this->_date."'");


$rows=$query->fetch();

		if (count($rows)) {
			return array(
				'products_id'       => $rows['products_id'],
				'products_name'             => $rows['products_name'],
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
			return false;
		}
	}

	public function getTaxRates($tax_class_id) {
      	$query = $this->db->query("SELECT * FROM r_tax_rates WHERE tax_class_id = '" . (int)$tax_class_id . "'");
		return $query->FetchAll();
	}

	public function getGeoZones() {
		$query = $this->db->query("SELECT * FROM r_geo_zones ORDER BY geo_zone_name ASC");
		return $query->FetchAll();
	}

	public function getZoneToGeoZones($geo_zone_id) {
		$query = $this->db->query("SELECT * FROM r_zones_to_geo_zones WHERE geo_zone_id = '" . (int)$geo_zone_id . "'");

		return $query->fetchAll();
	}

	public function getAddresses($customer_id) {
		$address_data = array();
		//echo "SELECT address_book_id FROM r_address_book WHERE customers_id = '" . (int)$customer_id . "'";
		$rows = $this->db->fetchAll("SELECT address_book_id FROM r_address_book WHERE customers_id = '" . (int)$customer_id . "'");

		foreach ($rows as $result) {
			$address_info = $this->getAddress($result['address_book_id']);

			if ($address_info) {
				$address_data[] = $address_info;
			}
		}

		return $address_data;
	}
	public function getAddress($address_id) {
		$address_query = $this->db->fetchRow("SELECT * FROM r_address_book WHERE address_book_id = '" . (int)$address_id . "'");

		$default_query = $this->db->fetchRow("SELECT customers_default_address_id FROM r_customers WHERE customers_id = '" . (int)$address_query['customers_id'] . "'");

		if (count($address_query)) {
			$country_query = $this->db->fetchRow("SELECT * FROM `r_countries` WHERE countries_id = '" . (int)$address_query['countries_id'] . "'");

			if (count($country_query)) {
				$country = $country_query['countries_name'];
				$iso_code_2 = $country_query['countries_iso_code_2'];
				$iso_code_3 = $country_query['countries_iso_code_3'];
				$address_format = $country_query['address_format'];
			} else {
				$country = '';
				$iso_code_2 = '';
				$iso_code_3 = '';
				$address_format = '';
			}

			$zone_query = $this->db->fetchRow("SELECT * FROM `r_zones` WHERE zone_id = '" . (int)$address_query['zone_id'] . "'");

			if (count($zone_query)) {
				$zone = $zone_query['zone_name'];
				$code = $zone_query->row['zone_code'];
			} else {
				$zone = '';
				$code = '';
			}

			return array(
				'address_id'     => $address_query['address_book_id'],
				'customer_id'    => $address_query['customers_id'],
				'firstname'      => $address_query['entry_firstname'],
				'lastname'       => $address_query['entry_lastname'],
				'company'        => $address_query['entry_company'],
				'address_1'      => $address_query['entry_street_address'],
				'address_2'      => $address_query['entry_suburb'],
				'postcode'       => $address_query['entry_postcode'],
				'city'           => $address_query['entry_city'],
				'zone_id'        => $address_query['entry_zone_id'],
				'zone'           => $zone,
				'zone_code'      => $code,
				'country_id'     => $address_query['entry_country_id'],
				'country'        => $country,
				'iso_code_2'     => $iso_code_2,
				'iso_code_3'     => $iso_code_3,
				'address_format' => $address_format,
				'default'		 => ($default_query['customers_default_address_id'] == $address_query['address_book_id']) ? true : false
			);
		}
	}

	public function getEmailContent($param)
	{
		$row=$this->db->fetchRow("select * from r_email_format where language_id='".$param['lang']."' and email_format_id='".(int)$param['id']."'");
		return $row;
	}

	public function getReturn($return_id)
	{
		$query = $this->db->fetchAll("SELECT DISTINCT *, (SELECT CONCAT(c.customers_firstname, ' ', c.customers_lastname) FROM r_customers c WHERE c.customers_id = r.customer_id) AS customer,(select rr.name from r_return_reason rr where rr.language_id='1' and rr.return_reason_id=r.return_reason_id) as return_reason FROM `r_return` r WHERE r.return_id = '" . (int)$return_id . "'");

		return $query;
	}

	public function getReturnStatuses()
	{
		$fetch=$this->db->fetchAll("select * from r_return_action where language_id='1'");
		return $fetch;
	}

	public function editReturnAction($return_id, $return_action_id) {
	//echo "UPDATE `r_return` SET return_action_id = '" . (int)$return_action_id . "' WHERE return_id = '" . (int)$return_id . "'";
		$this->db->query("UPDATE `r_return` SET return_action_id = '" . (int)$return_action_id . "' WHERE return_id = '" . (int)$return_id . "'");
	}

	public function trashCategories()
	{	
		//echo "select categories_id,categories_image from r_categories where del=1 and date_add(last_modified, INTERVAL ".@constant('CLEAN_TRASH')." DAY ) < '".date('Y-m-d H:i:s')."'";
		//exit;
		$rows=$this->db->fetchAll("select categories_id,categories_image from r_categories where del=1 and date_add(last_modified, INTERVAL ".@constant('CLEAN_TRASH')." DAY ) < '".date('Y-m-d H:i:s')."'");
		//echo count($rows);
		if(count($rows)>0)
		{
			foreach($rows as $k=>$v)
			{
				//echo "delete from r_categories where categories_id='".$v['categories_id']."'<br/>";
				$this->db->query("delete from r_categories where categories_id='".$v['categories_id']."'");
				$this->db->query("delete from r_categories_description where categories_id='".$v['categories_id']."'");
				@unlink(@constant('PATH_TO_UPLOADS_CATEGORIES').$v['categories_image']);
			}
		}

	}

	public function trashProducts()
	{	
		//echo "select products_id,products_image from r_products where del=1 and date_add(products_last_modified, INTERVAL ".@constant('CLEAN_TRASH')." DAY ) < '".date('Y-m-d H:i:s')."'<br/>";
		$rows=$this->db->fetchAll("select products_id,products_image from r_products where del=1 and date_add(products_last_modified, INTERVAL ".@constant('CLEAN_TRASH')." DAY ) < '".date('Y-m-d H:i:s')."'");
		//echo count($rows);
		if(count($rows)>0)
		{
			foreach($rows as $k=>$v)
			{
				//echo "delete from r_categories where categories_id='".$v['categories_id']."'<br/>";
				$this->db->query("delete from r_products where products_id='".(int)$v['products_id']."'");
				$this->db->query("delete from r_products_description where products_id='".(int)$v['products_id']."'");
				$this->db->query("delete from r_products_option where product_id='".(int)$v['products_id']."'");
				$this->db->query("delete from r_products_option_value where product_id='".(int)$v['products_id']."'");
				$this->db->query("delete from r_products_specials where products_id='".(int)$v['products_id']."'");
				$this->db->query("delete from r_products_to_categories where products_id='".(int)$v['products_id']."'");
				$this->db->query("delete from r_product_attribute_group where product_id='".(int)$v['products_id']."'");
				$this->db->query("delete from r_product_discount where product_id='".(int)$v['products_id']."'");
				$this->db->query("delete from r_product_related where product_id='".(int)$v['products_id']."'");
				$this->db->query("delete from r_product_reward where product_id='".(int)$v['products_id']."'");
				$this->db->query("delete from r_product_tag where products_id='".(int)$v['products_id']."'");
				$this->db->query("delete from r_product_to_download where product_id='".(int)$v['products_id']."'");
				
				$rows_images=$this->db->fetchAll("select image from r_products_images where products_id='".(int)$v['products_id']."'");
				if(count($rows_images)>0)
				{
					foreach($rows_images as $k1=>$v1)
					{
						@unlink(@constant('PATH_TO_UPLOADS_DIR').'products/'.$v1['image']);
					}
				}
				$this->db->query("delete from r_products_images where products_id='".(int)$v['products_id']."'");
				@unlink(@constant('PATH_TO_UPLOADS_DIR').'products/'.$v['products_image']);
			}
		}

	}

	public function trashOrders()
	{
		$rows=$this->db->fetchAll("select orders_id from r_orders where invoice_id=0 and orders_status=0 and date_add(last_modified, INTERVAL 30 DAY ) < '".date('Y-m-d H:i:s')."'");
		//echo count($rows);
		if(count($rows)>0)
		{
			foreach($rows as $k=>$v)
			{
				$this->db->query("delete from r_orders where orders_id='".(int)$v['orders_id']."'");
				$this->db->query("delete from r_orders_products where orders_id='".(int)$v['orders_id']."'");
				$this->db->query("delete from r_orders_products_download where orders_id='".(int)$v['orders_id']."'");
				$this->db->query("delete from r_orders_products_option where order_id='".(int)$v['orders_id']."'");
				$this->db->query("delete from r_orders_status_history where orders_id='".(int)$v['orders_id']."'");
				$this->db->query("delete from r_orders_total where orders_id='".(int)$v['orders_id']."'");
			}
		}
	}

	function undoProductDelete()
	{
		$rows=$this->db->fetchAll("select products_id from r_products where del=1");
		if(count($rows)>0)
		{
			foreach($rows as $k=>$v)
			{
				$id.=$pre.$v['products_id'];
				$pre=",";
			}

			$this->db->query("update r_products set del=0 where del=1");
			return count($rows)." products retained with pid ".(int)$id;
		}else
		{
			return "No Recently Deleted Products Available!!";
		}
	}

	function undoCategoryDelete()
	{
		$rows=$this->db->fetchAll("select categories_id from r_categories where del=1");
		if(count($rows)>0)
		{
			foreach($rows as $k=>$v)
			{
				$id.=$pre.$v['categories_id'];
				$pre=",";
			}

			$this->db->query("update r_categories set del=0 where del=1");
			return count($rows)." categories retained with cat_id ".(int)$id;
		}else
		{
			return "No Recently Deleted Categories Available!!";
		}
	}

	public function getPageLinks()
	{
		return $this->db->fetchAll("select page_name,page_id from  r_cms where status='1' and status!='-1'");
	}

	public function checkThemeSelected()
	{
		$rows=$this->db->fetchAll("SELECT configuration_key,configuration_value FROM `r_configuration` WHERE configuration_key IN ('SITE_DEFAULT_TEMPLATE', 'STORE_FAVI_ICON', 'STORE_NO_IMAGE_ICON', 'STORE_LOGO')");
		
		$error=array("SITE_DEFAULT_TEMPLATE"=>"Theme not selected","STORE_FAVI_ICON"=>"Favi Icon not uploaded","STORE_NO_IMAGE_ICON"=>"No image icon not uploaded","STORE_LOGO"=>"Store logo not uploaded");
		$msg=array();
		$status=1;
		//echo "<pre>";
		//print_r($rows);
		//echo "</pre>";
		$i=3;
		$val=0;
		foreach($rows as $k=>$v)
		{
			if($v['configuration_value']=="")
			{
				$msg[]=$error[$v['configuration_key']];
				$status=0;
				$val=$i;
				$i+=3;
			}
			$points=12-$val;
			$txt=implode(",",$msg);			
		}
		return array("status"=>$status,"message"=>$txt,"points"=>$points);
	}

	public function checkProductUpload()
	{
		$row=$this->db->fetchRow("select count(*) as count from r_products p,r_products_description pd where p.products_id=pd.products_id and p.del=0");
		
		$status=$row['count']>0?"1":"0";
		$message=$status==""?"No products available!!":"products upload successfull!!";
		$points=$status=="1"?"20":"0";
		$return=array("status"=>$status,"message"=>$message,"points"=>$points);
		return $return;
	}

	public function checkPaymentMethod()
	{
		$checknull=$this->db->fetchRow("select configuration_value from r_configuration where configuration_key like 'MODULE_PAYMENT_INSTALLED'");
		
		if($checknull['configuration_value']!="")
		{
			$row=$this->db->fetchRow("SELECT count(*)  as  count from r_configuration WHERE substring( configuration_key, 1, 15 ) LIKE 'MODULE_PAYMENT_'
	AND substring( configuration_key , -7 ) LIKE '_STATUS' AND configuration_value = 'True'");
			if($row['count']>0)
			{
				$return=array("status"=>"1","message"=>"Payment Module installed and active!!","points"=>"15");
			}else
			{
				$return=array("status"=>"0","message"=>"Payment Module installed but not active!!","points"=>"0");
			}
			
		}else
		{
			$return=array("status"=>"0","message"=>"Payment Module not installed!!","points"=>"0");
		}
		return $return;
	}

	public function checkShippingMethod()
	{
		
		$checknull=$this->db->fetchRow("select configuration_value from r_configuration where configuration_key like 'MODULE_SHIPPING_INSTALLED'");
		
		if($checknull['configuration_value']!="")
		{
			$row=$this->db->fetchRow("SELECT count(*) as count from r_configuration WHERE substring( configuration_key, 1, 16 ) LIKE 'MODULE_SHIPPING_'
	AND substring( configuration_key , -7 ) LIKE '_STATUS'  AND configuration_value = 'True'");
			if($row['count']>0)
			{
				$return=array("status"=>"1","message"=>"Shipping Module installed and active!!","points"=>"15");
			}else
			{
				$return=array("status"=>"0","message"=>"Shipping Module installed but not active!!","points"=>"0");
			}
			
		}else
		{
			$return=array("status"=>"0","message"=>"Shipping Module not installed!!","points"=>"0");
		}
		return $return;
	}

	

	public function checkMystore()
	{
		$rows=$this->db->fetchAll("select configuration_key,configuration_value from r_configuration where configuration_key in('STORE_NAME','STORE_OWNER','STORE_OWNER_EMAIL_ADDRESS','EMAIL_FROM','REPLY_EMAIL','STORE_COUNTRY','STORE_ZONE','DEFAULT_TIME_ZONE','STORE_TELEPHONE','STORE_FAX','STORE_NAME_ADDRESS','IMAGE_CART_SIZE','IMAGE_WISHLIST_SIZE','IMAGE_COMPARE_SIZE','IMAGE_P_RELATED_SIZE','IMAGE_P_ADDITIONAL_SIZE','IMAGE_M_LIST_SIZE','IMAGE_C_LIST_SIZE','IMAGE_P_LIST_SIZE','IMAGE_P_POPUP_SIZE','IMAGE_P_THUMB_SIZE','SITE_MAINTENANCE_PAGE','STORE_META_TITLE','STORE_META_KEYWORDS','STORE_META_DESCRIPTION','LIMIT_PRODUCT_NAME','ADMIN_FRIENDLY_URL','PRODUCT_LISTING_LABELS','MAX_ITEMS_PER_PAGE_CATALOG','ADMIN_PAGE_LIMIT','ALLOWED_FILE_EXTENSIONS','PRODUCT_LIST_SHOW_LIMIT','MAX_UPLOAD_FILE_SIZE')");
		$missing=array();
		
		$names=array("STORE_NAME"=>"Store Name","STORE_OWNER"=>"Store Owner","STORE_OWNER_EMAIL_ADDRESS"=>"Email Address","EMAIL_FROM"=>"Email From","REPLY_EMAIL"=>"Reply Email","STORE_COUNTRY"=>"Country","STORE_ZONE"=>"Zone","DEFAULT_TIME_ZONE"=>"Default Time Zone","STORE_TELEPHONE"=>"Telephone","STORE_FAX"=>"Fax","STORE_NAME_ADDRESS"=>"Store Address and Phone","IMAGE_CART_SIZE"=>"Cart Image Size","IMAGE_WISHLIST_SIZE"=>"Wish List Size","IMAGE_COMPARE_SIZE"=>"Compare Image Size","IMAGE_P_RELATED_SIZE"=>"Products Related Image Size","IMAGE_P_ADDITIONAL_SIZE"=>"Product Additional Image Size","IMAGE_M_LIST_SIZE"=>"Manufacturer List Size","IMAGE_C_LIST_SIZE"=>"Category List Size","IMAGE_P_LIST_SIZE"=>"Produc List Size","IMAGE_P_POPUP_SIZE"=>"Product Details Image Popup Size","IMAGE_P_THUMB_SIZE"=>"Product Details Image Thumb Size","SITE_MAINTENANCE_PAGE"=>"Maintenance Content","STORE_META_TITLE"=>"Meta Title","STORE_META_KEYWORDS"=>"Meta Keywords","STORE_META_DESCRIPTION"=>"Meta Description","LIMIT_PRODUCT_NAME"=>"Limit Prouct Name","ADMIN_FRIENDLY_URL"=>"Admin Friendly Url","PRODUCT_LISTING_LABELS"=>"Product Listing Labels","MAX_ITEMS_PER_PAGE_CATALOG"=>"Default Items Per Page(catalog)","ADMIN_PAGE_LIMIT"=>"Default Items Per Page(Admin)","ALLOWED_FILE_EXTENSIONS"=>"Allowed Upload File Extensions","PRODUCT_LIST_SHOW_LIMIT"=>"Show Products Limit","MAX_UPLOAD_FILE_SIZE"=>"Allowed Uploaded File Size");
		$i=1;
		$status=1;
		$val=0;

		foreach($rows as $k=>$v)
		{
			//echo $v."<br/>";
			if($v['configuration_value']=="")
			{
				$missing[]=$names[$v['configuration_key']];
				$val=$i;
				$i++;
				$status=0;
			}
		}
		/*echo "<pre>";
		print_r($missing);
		echo "</pre>";*/
		$points=33-$val;
		if($val!="")
		{
			$labels=implode(",",$missing);
		}
		return array("status"=>$status,"message"=>$labels." is missing.update these values in my store!!","points"=>$points);
	}

	public function loginRedirection($rdate)
	{
		$row=$this->db->fetchRow("select datediff('".date('Y-m-d')."','".$rdate."') as diff");
		$return=$row[diff]>5?"home":"getting-started";
		return $return;
	}

	public function checkTax()
	{
		$row=$this->db->fetchRow("select count(*) as count from r_tax_class tc,r_tax_rates tr where tr.tax_class_id=tc.tax_class_id");
		if($row['count']>0)
		{
			$return=array("status"=>"1","message"=>"Tax setup successfull!!","points"=>"5");
		}else
		{
			$return=array("status"=>"0","message"=>"Tax is not setup!!","points"=>"0");
		}
		return $return;
	}
}

?>