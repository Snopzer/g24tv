<?php
/**
 * Handles all the admin module actions like add,edit,delete,publish
 * @version 1.0
 * http://www.rsoftindia.com
 * @category   Zend
 * @package    IndexController
 * @author     Suresh Babu K
 */
class Model_Adminaction
{
	public $db;
	public $_date;
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$this->_date=date('Y-m-d H:i:s');
	
	}
	public function newscustomer($type)
	{
		if($type=='All')
		{
			  $result = $this->db->fetchAll("select customers_email_address from r_customers");

		}else if($type=='NSub')
		{
			 $result = $this->db->fetchAll("select customers_email_address from r_customers where customers_newsletter = '1'");
		}

		//$this->p($result,'1');
		return $result;
	}
	public function updateShipping($rid,$act)
	{
		if($act=='UnInst')
		{
			$exp_inst_mod=explode(";",MODULE_SHIPPING_INSTALLED);
			unset($exp_inst_mod[array_search($rid,$exp_inst_mod)]);
			$imp_inst_mod=implode(";",$exp_inst_mod);
			$this->db->update("r_configuration",array("configuration_value"=>$imp_inst_mod),"configuration_key ='MODULE_SHIPPING_INSTALLED'");
		}else if($act=='Inst')
		{
				//echo MODULE_SHIPPING_INSTALLED;
				$mod=MODULE_SHIPPING_INSTALLED==""?$rid:MODULE_SHIPPING_INSTALLED.";".$rid;
			//echo MODULE_SHIPPING_INSTALLED.";".$rid;
			//exit;
			$this->db->update("r_configuration",array("configuration_value"=>$mod),"configuration_key ='MODULE_SHIPPING_INSTALLED'");
		}
	}

	public function updateRow($arr){
	$n=$this->db->update($arr['table'],$arr['cols'],$arr['where']);
	return $n;
	}

	public function insertRow($arr){
	$n=$this->db->insert($arr['table'],$arr['cols']);
	return $n;
	}

	public function checkproductspec($rid)
	{
	}

	public function moduleorderstatus()
	{
		  $statuses_array = array(array('id' => '0', 'text' => TEXT_DEFAULT));
		  $result = $this->db->fetchAll("select orders_status_id, orders_status_name from r_orders_status where language_id = '1' order by orders_status_name");
			foreach ($result as $k)
			{
				$statuses_array[] = array('id' => $k['orders_status_id'],
                                'text' => $k['orders_status_name']);

			}
			return $statuses_array;
	/*$statuses_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by orders_status_name");
    while ($statuses = tep_db_fetch_array($statuses_query)) {
      $statuses_array[] = array('id' => $statuses['orders_status_id'],
                                'text' => $statuses['orders_status_name']);
    }*/

	}


	public function updatePayment($rid,$act)
	{
		if($act=='UnInst')
		{
			$exp_inst_mod=explode(";",MODULE_PAYMENT_INSTALLED);
			unset($exp_inst_mod[array_search($rid,$exp_inst_mod)]);
			$imp_inst_mod=implode(";",$exp_inst_mod);
			$this->db->update("r_configuration",array("configuration_value"=>$imp_inst_mod),"configuration_key ='MODULE_PAYMENT_INSTALLED'");
		}else if($act=='Inst')
		{
				//echo MODULE_SHIPPING_INSTALLED;
				$mod=MODULE_PAYMENT_INSTALLED==""?$rid:MODULE_PAYMENT_INSTALLED.";".$rid;
			//echo MODULE_SHIPPING_INSTALLED.";".$rid;
			//exit;
			$this->db->update("r_configuration",array("configuration_value"=>$mod),"configuration_key ='MODULE_PAYMENT_INSTALLED'");
		}
	}

	public function updateOrderTotal($rid,$act)
	{
		if($act=='UnInst')
		{
			$exp_inst_mod=explode(";",MODULE_ORDER_TOTAL_INSTALLED);
			unset($exp_inst_mod[array_search($rid,$exp_inst_mod)]);
			$imp_inst_mod=implode(";",$exp_inst_mod);
			$this->db->update("r_configuration",array("configuration_value"=>$imp_inst_mod),"configuration_key ='MODULE_ORDER_TOTAL_INSTALLED'");
		}else if($act=='Inst')
		{
							//echo MODULE_SHIPPING_INSTALLED;
				$mod=MODULE_ORDER_TOTAL_INSTALLED==""?$rid:MODULE_ORDER_TOTAL_INSTALLED.";".$rid;
			//echo MODULE_SHIPPING_INSTALLED.";".$rid;
			//exit;


			$this->db->update("r_configuration",array("configuration_value"=>$mod),"configuration_key ='MODULE_ORDER_TOTAL_INSTALLED'");
		}
	}

	public function RecordPublish($table,$data,$colum,$id)
	{
		$id=is_array($id)==true?$id:array($id);
		foreach($id as $k=>$v)
		{
			$n =  $this->db->update($table, $data, $colum."=".(int)$v);
			//PRINT_R($data);	ECHO $table." ". $data." ". $colum."=".$v;
		}
		//EXIT;

	}

	public function getFilter()
	{
		$qry=$this->db->query("select option_id,name from r_option_description where language_id='1' and option_id in (select option_id  from r_option where filter='1' and type=('select' or 'radio' or 'checkbox'))");
		return $qry->fetchAll();
	}

	public function RecordDelete($table,$colum,$id)
	{
		$id=is_array($id)==true?$id:array($id);
		foreach($id as $k=>$v)
		{
			$n = $this->db->delete($table, $colum."='".(int)$v."'");
		}
	}

	public function getcustomers($rid)
	{
		//echo 'SELECT a . * , c . * FROM r_customers c, r_address_book a WHERE a.customers_id = c.customers_id AND c.customers_id ='.$rid;
    	 $result = $this->db->fetchAll('SELECT a . * , c . * FROM r_customers c, r_address_book a WHERE a.customers_id = c.customers_id AND c.customers_id ='.(int)$rid);
      	return $result;
	}


	public function selectConfig($rid)
	{
		if($rid!='')
		{
    		$result = $this->db->fetchAll('SELECT * from r_configuration where configuration_key like "'.$rid.'"');
			return $result;
		}
	}

public function updateConfig($rid,$val)
	{
		if($rid!='')
		{
			$this->db->update("r_configuration",array('configuration_value'=>$val)," configuration_key like '".$rid."'");
		}
	}


	public function getcountrydetails($rid)
	{
		if($rid!='')
		{
    		$result = $this->db->fetchAll('SELECT * from r_countries where countries_id ='.(int)$rid);
			return $result;
		}
	}

	public function getEditdetails($table,$col,$rid)
	{
		if($rid!='')
		{
			
			//echo 'SELECT * from '.$table.' where '.$col.' ='.$rid;
			//echo 'SELECT * from '.$table.' where '.$col.' ='.htmlspecialchars($rid, ENT_COMPAT, 'UTF-8');
    		//$result = $this->db->fetchAll('SELECT * from '.$table.' where '.$col.' ='.(int)$rid);
			$result = $this->db->fetchAll('SELECT * from '.$table.' where '.$col.' ='.$rid);
			//$result = $this->db->fetchAll('SELECT * from '.$table.' where '.$col.' ='.htmlspecialchars($rid, ENT_COMPAT, 'UTF-8'));
    		return $result;
		}
	}

	public function getzonedetails($rid)
	{
		if($rid!='')
		{
    		$result = $this->db->fetchAll('SELECT * from r_zones where zone_id ='.(int)$rid);
			//print_r($result);
			//echo $result[0][zone_code];
			//exit;
			return $result;
		}
	}

	public function getgeozonecountrydetails($rid)
	{
		if($rid!='')
		{
    		$result = $this->db->fetchAll('SELECT * from r_zones_to_geo_zones where association_id ='.(int)$rid);
			return $result;
		}
	}

	public function getgeozonedetails($rid)
	{
		if($rid!='')
		{
    		$result = $this->db->fetchAll('SELECT * from r_geo_zones where geo_zone_id ='.(int)$rid);
			return $result;
		}
	}

	public function gettaxclassdetails($rid)
	{
		if($rid!='')
		{
    		$result = $this->db->fetchAll('SELECT * from r_tax_class where tax_class_id ='.(int)$rid);
			return $result;
		}
	}

	public function gettaxratesdetails($rid)
	{
		if($rid!='')
		{
    		$result = $this->db->fetchAll('SELECT * from r_tax_rates where tax_rates_id ='.(int)$rid);
			return $result;
		}
	}

	public function getcurrencydetails($rid)
	{
		if($rid!='')
		{
    		$result = $this->db->fetchAll('SELECT * from r_currencies where currencies_id ='.(int)$rid);
			return $result;
		}
	}

	public function getattributegroupdropdown($cid)
	{

		 $select = $this->db->fetchAll("select ag.attribute_group_id,agd.name from r_attribute_group ag,r_attribute_group_description agd
			where ag.attribute_group_id=agd.attribute_group_id and agd.language_id='1' order by ag.sort_order asc");
		 foreach($select as $v)
		 {
			$selected=$v['attribute_group_id']==$cid?'selected':'';

			echo "<option value='".$v['attribute_group_id']."' ".$selected." >".$v['name']."</option>";
		 }
	}

	public function getcountrydropdown($cid)
	{

		 $select = $this->db->fetchAll("select countries_id,countries_name from r_countries");
		 foreach($select as $v)
		 {
			$selected=$v['countries_id']==$cid?'selected':'';

			echo "<option value='".$v['countries_id']."' ".$selected." >".$v['countries_name']."</option>";
		 }
	}

	public function getCountries()
	{

		 $countries = $this->db->fetchAll("select countries_id,countries_name from r_countries");
		return $countries;
	}

	public function getNTDescription($tid)
	{
			if($tid!='')
			{
			$select = $this->db->fetchRow("select description from r_newsletter_template where newsletter_template_id='".(int)$tid."'");
			echo $select['description'];
			}
	}

	public function getNTemplatedropdown($cid)
	{

		 $select = $this->db->fetchAll("select newsletter_template_id,title from r_newsletter_template");
		 foreach($select as $v)
		 {
			$selected=$v['newsletter_template_id']==$cid?'selected':'';

			echo "<option value='".$v['newsletter_template_id']."' ".$selected." >".$v['title']."</option>";
		 }
	}

	public function getmanufacturerdropdown($cid)
	{
            $manufacturer_data=Model_Cache::getCache(array("id"=>"admin_manufacturer_".(int)$cid));
            if(!$manufacturer_data)
            {
                $sel= "<option value='0'>None</option>";
		 $select = $this->db->fetchAll("select manufacturers_id,manufacturers_name from r_manufacturers");
		 foreach($select as $v)
		 {
			$selected=$v['manufacturers_id']==$cid?'selected':'';

			$sel.= "<option value='".$v['manufacturers_id']."' ".$selected." >".$v['manufacturers_name']."</option>";
		 }
                 Model_Cache::getCache(array("id"=>"admin_manufacturer_".$cid,"input"=>$manufacturer_data,"tags"=>array("admin","manufacturer","general")));
            }
		 return $sel;
	}

public function option($type,$req)
	{
	/*echo "<pre>";
	print_r($_REQUEST);
	exit;*/
	$k=0;
		if(count($_REQUEST['sort'])>0)
			{
		if($type=='insert')
		{
		//foreach($_REQUEST['sort'] as $k=>$v)
		for($k;$k<$_REQUEST['attr_hid_val'];$k++)
			{
				//if($v!="" && $_REQUEST['attr_rem_'.$k]!='1')
				if($_REQUEST['attr_rem_'.$k]!='1')
				{
						$data=array("option_id"=>$req,"sort_order"=>$_REQUEST['sort'][$k]);
						$this->db->insert('r_option_value',$data);
						$insert_id=$this->db->lastInsertId();


				    $f=$this->db->fetchAll("select languages_id from r_languages");
					foreach($f as $g)
					{


						$data=array("option_value_id"=>$insert_id,"option_id"=>$req,"sort_order"=>$_REQUEST['sort'][$k],
						"language_id"=>$g['languages_id'],"name"=>$_REQUEST['attr_text_'.$k.'_'.$g['languages_id']]);
 						$this->db->insert('r_option_value_description',$data);
					}
				}
			}

		}
		else if($type=='update')
		{
			//$this->p($_REQUEST,'1');
 			//$this->db->delete('r_option_value_description',"option_id=".$_REQUEST['rid']);
 			//$this->db->delete('r_option_value',"option_id=".$_REQUEST['rid']);

 			//foreach($_REQUEST['sort'] as $k=>$v)
			for($k;$k<$_REQUEST['attr_hid_val'];$k++)
			{
				//if($v!="" && $_REQUEST['attr_rem_'.$k]!='1')
				if($_REQUEST['attr_rem_'.$k]!='1')
				{
						$data=array("option_id"=>$_REQUEST['rid'],"sort_order"=>$_REQUEST['sort'][$k]);
						if($_REQUEST['attr_hid_'.$k.'_1']=="")//insert
						{
							$this->db->insert('r_option_value',$data);
							$insert_id=$this->db->lastInsertId();
							$f=$this->db->fetchAll("select languages_id from r_languages");
							foreach($f as $g)
							{


								$data=array("option_value_id"=>$insert_id,"option_id"=>$_REQUEST['rid'],"sort_order"=>$_REQUEST['sort'][$k],
								"language_id"=>$g['languages_id'],"name"=>$_REQUEST['attr_text_'.$k.'_'.$g['languages_id']]);
		 						$this->db->insert('r_option_value_description',$data);
							}
							//echo "in insert<br/>";

						}else //update
						{
							$this->db->update('r_option_value',$data,'option_value_id='.$_REQUEST['attr_hid_'.$k.'_1']);
							$f=$this->db->fetchAll("select languages_id from r_languages");
							foreach($f as $g)
							{
								unset($where);
								$where[]='option_value_id='.$_REQUEST['attr_hid_'.$k.'_1'];
								$where[]='language_id='.(int)$g[languages_id];

								$data=array("sort_order"=>$_REQUEST['sort'][$k],"name"=>$_REQUEST['attr_text_'.$k.'_'.$g['languages_id']]);
							//	$this->p($data,'0');
							//	$this->p($where,'0');

		 						$this->db->update('r_option_value_description',$data,$where);
							}
						//	echo "in update<br/>";
						}



				}else
				{
					//echo $_REQUEST['attr_hid_'.$k.'_1']." removed<br/>";
					if($_REQUEST['attr_hid_'.$k.'_1']!="")
					{
						$f=$this->db->fetchRow("select count(*) as count from r_products_option_value where option_value_id='".$_REQUEST['attr_hid_'.$k.'_1']."'");
						//check in r_products_option_value table if these options are used for any products if yes then
						//dont delete
						if($f[count]=='0')
						{
							$this->db->delete('r_option_value_description',"option_value_id=".$_REQUEST['attr_hid_'.$k.'_1']);
 							$this->db->delete('r_option_value',"option_value_id=".$_REQUEST['attr_hid_'.$k.'_1']);
						}else
						{
							return "cannot delete it is being used!!";
							//$this->_redirect('admin/option?rid='.$_REQUEST['rid'].'&type=Edit&msg=cannot delete!!');
						}
 						//need to reomove from used product options that is in r_products_option_value
					}
				}
			}
 		}

	}

	}

	public function productattribute($type,$inst_id)
	{
		//echo $type." ".$inst_id;
		//exit;

		if(count($_REQUEST['attr_attribute_id'])>0)
			{
		if($type=='insert')
		{
			foreach($_REQUEST['attr_attribute_id'] as $k=>$v)
			{
				if(!in_array($v,$inserted))
				{	
					if($v!="" && $_REQUEST['attr_rem_'.$k]!='1')
					{
						$f=$this->db->fetchAll("select languages_id from r_languages");
						foreach($f as $g)
						{
							$data=array("product_id"=>$inst_id,"attribute_id"=>$_REQUEST['attr_attribute_id'][$k],
							"language_id"=>$g['languages_id'],
							"text"=>$_REQUEST['attr_text_'.$k.'_'.$g['languages_id']]);
							$this->db->insert('r_product_attribute_group',$data);
							$inserted[]=$v;
						}
					}
				}
			}

		}
		else if($type=='update')
		{
		/*echo "<pre>";
			print_r($_REQUEST['attr_attribute_id']);
			array_unique($_REQUEST['attr_attribute_id']);
			
		echo "</pre>";
		exit;*/
			$this->db->delete('r_product_attribute_group',"product_id=".(int)$_REQUEST['rid']);
			foreach($_REQUEST['attr_attribute_id'] as $k=>$v)
			{
				if(!in_array($v,$inserted))
				{
					if($v!="" && $_REQUEST['attr_rem_'.$k]!='1')
					{
					$f=$this->db->fetchAll("select languages_id from r_languages");
						foreach($f as $g)
						{
							$data=array("product_id"=>(int)$_REQUEST['rid'],"attribute_id"=>$_REQUEST['attr_attribute_id'][$k],
							"language_id"=>$g['languages_id'],
							"text"=>$_REQUEST['attr_text_'.$k.'_'.$g['languages_id']]);
							$this->db->insert('r_product_attribute_group',$data);
						}
						$inserted[]=$v;
					}
				}
				
			}
		}
	}

	}
//backup before dependent options can be found in Adminaction_before_dp_sep_12_2012.php
public function product_option_multiple($type,$inst_id,$field)
	{
		/*works for insert and update for both select,radio,checkbox of options*/
		//
		if($_REQUEST[$field.'_hid_val']!='0') //if($_REQUEST['select_hid_val']!='0')
			{
		if($type=='insert')
		{
			/*$opid=$this->db->fetchAll("select option_id from r_option where type='".$field."'");
			$str="";
			foreach($opid as $o){ $str.=$pre.$o['option_id'];$pre=",";}
			if($str!="")
			{
			$this->db->query("delete from r_products_option where product_id='".$inst_id."' and option_id in (".$str.")");
			$this->db->query("delete from r_products_option_value where product_id='".$inst_id."' and option_id in (".$str.")");
			}*/
			/*end delete*/

			/*start insert into products_option*/
			foreach($_REQUEST[$field.'_cg'] as $k=>$v)
			{
				if($_REQUEST[$field.'_rem_'.$k]!='1' && $v!='0')
				{
					$exp=explode("_",$v);
					$arr_opt_id[]=$exp[1];
					$arr_req[$exp[1]]=$arr_req[$exp[1]]+$_REQUEST[$field.'_req'][$k];
					$arr_opt_val_id[]=$exp[0];
				}
			}
			if(count($arr_opt_id)!='0')
			{
				$unq_arr_opt_id=array_unique($arr_opt_id);
                                //$unq_arr_opt_id=$arr_opt_id;
				foreach($unq_arr_opt_id as $k=>$v)
				{
					$r=$arr_req[$v]>0?'1':'0';
					$data=array("product_id"=>$inst_id,"option_id"=>$v,"required"=>$r);
					$this->db->insert('r_products_option',$data);
					$prod_option_id[$v]=$this->db->lastInsertId();
				}
			}
			//$this->p($prod_option_id,'1');
			/*end insert into products_option*/

			foreach($_REQUEST[$field.'_cg'] as $k=>$v)
			{
				if($v!="0" && $_REQUEST[$field.'_rem_'.$k]!='1')
				{
					$select_opt=explode("_",$v);

					$data=array("product_id"=>$inst_id,"product_option_id"=>$prod_option_id[$select_opt[1]],"option_id"=>$select_opt[1],
					"option_value_id"=>$select_opt[0],"quantity"=>$_REQUEST[$field.'_qty'][$k],"subtract"=>$_REQUEST[$field.'_sub_req'][$k],
					"price"=>preg_replace('/[^0-9.]/s', '', $_REQUEST[$field.'_price'][$k]),"price_prefix"=>$_REQUEST[$field.'_prsym'][$k],"points"=>$_REQUEST[$field.'_points'][$k],
					"points_prefix"=>$_REQUEST[$field.'_psym'][$k],"weight"=>$_REQUEST[$field.'_weight'][$k],"weight_prefix"=>$_REQUEST[$field.'_wsym'][$k]);
                                        if(@constant('DEPENDENT_OPTIONS')=='1' && $field=="select")
                                        {
                                            $data['base_option_value_id']=$_REQUEST[$field.'_parent'][$k];
                                        }
					$this->db->insert('r_products_option_value',$data);
				}
			}
		}
		else if($type=='update')
		{
			/*delete records from products_option,products_option_value*/
			$opid=$this->db->fetchAll("select option_id from r_option where type='".$field."'");
			$str="";
			foreach($opid as $o){ $str.=$pre.$o['option_id'];$pre=",";}
			if($str!="")
			{
			$this->db->query("delete from r_products_option where product_id='".(int)$_REQUEST[rid]."'
			and option_id in (".$str.")");
			$this->db->query("delete from r_products_option_value where product_id='".(int)$_REQUEST[rid]."'
			and option_id in (".$str.")");
			}
			/*end delete*/

			/*start insert into products_option*/
			foreach($_REQUEST[$field.'_cg'] as $k=>$v)
			{
				if($_REQUEST[$field.'_rem_'.$k]!='1' && $v!='0')
				{
					$exp=explode("_",$v);
					$arr_opt_id[]=$exp[1];
					$arr_req[$exp[1]]=$arr_req[$exp[1]]+$_REQUEST[$field.'_req'][$k];
					$arr_opt_val_id[]=$exp[0];
				}
			}
			if(count($arr_opt_id)!='0')
			{
				$unq_arr_opt_id=array_unique($arr_opt_id);
                            //$unq_arr_opt_id=$arr_opt_id;
				foreach($unq_arr_opt_id as $k=>$v)
				{
					if($_REQUEST[$field."_poid"][$v]!="") //update if povid is not null
					{
						$r=$arr_req[$v]>0?'1':'0';
						$data=array("product_option_id"=>$_REQUEST[$field."_poid"][$v],"product_id"=>(int)$_REQUEST['rid'],"option_id"=>$v,"required"=>$r);
						$this->db->insert('r_products_option',$data);
						$this->db->lastInsertId();
						$prod_option_id[$v]=$_REQUEST[$field."_poid"][$v];
						//$arr_option_id[$v]=$prod_option_id[$v];//used to update product_option_id in r_products_option_value
					}
					else
					{
						$r=$arr_req[$v]>0?'1':'0';
						$data=array("product_id"=>(int)$_REQUEST['rid'],"option_id"=>$v,"required"=>$r);
						$this->db->insert('r_products_option',$data);
						$prod_option_id[$v]=$this->db->lastInsertId();

					}
				}

			//$this->p($prod_option_id,'1');
			/*end insert into products_option*/

			foreach($_REQUEST[$field.'_cg'] as $k=>$v)
			{
				if($_REQUEST[$field."_povid"][$k]!="") //update if povid is not null
				{
					if($v!="0" && $_REQUEST[$field.'_rem_'.$k]!='1')
					{
						$select_opt=explode("_",$v);

						$data=array("product_option_value_id"=>$_REQUEST[$field."_povid"][$k],"product_id"=>(int)$_REQUEST['rid'],"product_option_id"=>$prod_option_id[$select_opt[1]],"option_id"=>$select_opt[1],
						"option_value_id"=>$select_opt[0],"quantity"=>$_REQUEST[$field.'_qty'][$k],"subtract"=>$_REQUEST[$field.'_sub_req'][$k],
						"price"=>preg_replace('/[^0-9.]/s', '', $_REQUEST[$field.'_price'][$k]),"price_prefix"=>$_REQUEST[$field.'_prsym'][$k],"points"=>$_REQUEST[$field.'_points'][$k],
						"points_prefix"=>$_REQUEST[$field.'_psym'][$k],"weight"=>$_REQUEST[$field.'_weight'][$k],"weight_prefix"=>$_REQUEST[$field.'_wsym'][$k]);
                                                if(@constant('DEPENDENT_OPTIONS')=='1' && $field=="select")
                                        {
                                            $data['base_option_value_id']=$_REQUEST[$field.'_parent'][$k];
                                        }
						//print_r($data);
						//exit;
						$this->db->insert('r_products_option_value',$data);
						//$where[]='product_option_value_id='.$_REQUEST[$field."_povid"][$k];
						//$this->db->update('r_products_option_value',$data,$where);
					}
				}else
				{	if($v!="0" && $_REQUEST[$field.'_rem_'.$k]!='1')
					{
						$select_opt=explode("_",$v);

						$data=array("product_id"=>(int)$_REQUEST['rid'],"product_option_id"=>$prod_option_id[$select_opt[1]],"option_id"=>$select_opt[1],
						"option_value_id"=>$select_opt[0],"quantity"=>$_REQUEST[$field.'_qty'][$k],"subtract"=>$_REQUEST[$field.'_sub_req'][$k],
						"price"=>preg_replace('/[^0-9.]/s', '', $_REQUEST[$field.'_price'][$k]),"price_prefix"=>$_REQUEST[$field.'_prsym'][$k],"points"=>$_REQUEST[$field.'_points'][$k],
						"points_prefix"=>$_REQUEST[$field.'_psym'][$k],"weight"=>$_REQUEST[$field.'_weight'][$k],"weight_prefix"=>$_REQUEST[$field.'_wsym'][$k]);
                                         //echo "vlaue of :".$field."<br>";       
                                        if(@constant('DEPENDENT_OPTIONS')=='1' && $field=="select")
                                        {
                                            $data['base_option_value_id']=$_REQUEST[$field.'_parent'][$k];
                                        }
						$this->db->insert('r_products_option_value',$data);
					}
				}
			}
		}
	}
	//$this->p($data,'0');

	}
        //exit;
	}
public function product_option_text($type,$inst_id,$field)
	{
		/*works for insert and update for both text,texarea of options*/
		if(count($_REQUEST[$field.'_cg'])>0)
			{
		if($type=='insert')
		{
			foreach($_REQUEST[$field.'_cg'] as $k=>$v)
			{
				if($v!="" && $_REQUEST[$field.'_rem_'.$k]!='1')
				{
					$data=array("product_id"=>$inst_id,"option_id"=>$_REQUEST[$field.'_cg'][$k],
					"option_value"=>$_REQUEST[$field.'_value'][$k],
					"required"=>$_REQUEST[$field.'_req'][$k]);
					$this->db->insert('r_products_option',$data);
				}
			}
		}
		else if($type=='update')
		{

			$opid=$this->db->fetchAll("select option_id from r_option where type='".$field."'");
			$str="";
			foreach($opid as $o){ $str.=$pre.$o['option_id'];$pre=",";}
			if($str!=""){
			$this->db->query("delete from r_products_option where product_id='".(int)$_REQUEST[rid]."' and option_id in (".$str.")");
			}
			//echo "delete from r_products_option where product_id='".$_REQUEST[rid]."' and option_id in (".$str.")";
			//exit;
			echo "<pre>";
			foreach($_REQUEST[$field.'_cg'] as $k=>$v)
			{
				if($_REQUEST[$field."_poid"][$v]!="") //update if povid is not null
				{
					if($v!="" && $_REQUEST[$field.'_rem_'.$k]!='1')
					{
						$data=array("product_option_id"=>$_REQUEST[$field."_poid"][$v],"product_id"=>(int)$_REQUEST['rid'],"option_id"=>$_REQUEST[$field.'_cg'][$k],
						"option_value"=>$_REQUEST[$field.'_value'][$k],
						"required"=>$_REQUEST[$field.'_req'][$k]);
						//print_r($data);
						$this->db->insert('r_products_option',$data);
					}
				}else
				{
					if($v!="" && $_REQUEST[$field.'_rem_'.$k]!='1')
					{
						$data=array("product_id"=>(int)$_REQUEST['rid'],"option_id"=>$_REQUEST[$field.'_cg'][$k],
						"option_value"=>$_REQUEST[$field.'_value'][$k],
						"required"=>$_REQUEST[$field.'_req'][$k]);
						$this->db->insert('r_products_option',$data);
					}
				}
			}
		}
	}
	//$this->p($data,'0');

	}

/*public function product_option_text($type,$inst_id)
	{
		if(count($_REQUEST['text_cg'])>0)
			{
		if($type=='insert')
		{
			foreach($_REQUEST['text_cg'] as $k=>$v)
			{
				if($v!="" && $_REQUEST['text_rem_'.$k]!='1')
				{
					$data=array("product_id"=>$inst_id,"option_id"=>$_REQUEST['text_cg'][$k],
					"option_value"=>$_REQUEST['text_value'][$k],
					"required"=>$_REQUEST['text_req'][$k]);
					$this->db->insert('r_products_option',$data);
				}
			}

		}
		else if($type=='update')
		{
			$opid=$this->db->fetchAll("select option_id from r_option where type='text'");
			$str="";
			foreach($opid as $o){ $str.=$pre.$o['option_id'];$pre=",";}
			//$opid=implode(",",$opid);
			//echo "vlue of implode".$str;
			//$arrdel=array("product_id="=>$_REQUEST['rid'],"option_id in"=>$str);
			$this->db->query("delete from r_products_option where product_id='".$_REQUEST[rid]."' and option_id in (".$str.")");
			//echo "delete from r_products_option where product_id='.$_REQUEST[rid].' and option_id in (".$str.")";
			//exit;
			//$this->p($arrdel,'1');
			//$this->db->delete('r_products_option',$arrdel);
			foreach($_REQUEST['text_cg'] as $k=>$v)
			{
				if($v!="" && $_REQUEST['text_rem_'.$k]!='1')
				{
					$data=array("product_id"=>$_REQUEST['rid'],"option_id"=>$_REQUEST['text_cg'][$k],
					"option_value"=>$_REQUEST['text_value'][$k],
					"required"=>$_REQUEST['text_req'][$k]);
					$this->db->insert('r_products_option',$data);
				}
			}
		}
	}

	}*/

	public function productspecial($type,$inst_id)
	{
		if(count($_REQUEST['spec_price'])>0)
		{
		if($type=='insert')
		{
			foreach($_REQUEST['spec_price'] as $k=>$v)
			{
				if($v!="" && $_REQUEST['spec_rem_'.$k]!='1')
				{
					/*$data=array("products_id"=>$inst_id,"customer_group_id"=>$_REQUEST['spec_cg'][$k],
					"priority"=>$_REQUEST['spec_priority'][$k],
					"specials_new_products_price"=>$_REQUEST['spec_price'][$k],"start_date"=>$_REQUEST['spec_sdate_'.$k],
					"expires_date"=>$_REQUEST['spec_edate_'.$k],"specials_date_added"=>new Zend_Db_Expr('NOW()'));*/
										
					$data=array("products_id"=>$inst_id,"customer_group_id"=>$_REQUEST['spec_cg'][$k],
					"priority"=>$_REQUEST['spec_priority'][$k],
					"specials_new_products_price"=>preg_replace('/[^0-9.]/s', '', $_REQUEST['spec_price'][$k]),"start_date"=>$_REQUEST['spec_sdate_'.$k],
					"expires_date"=>$_REQUEST['spec_edate_'.$k],"specials_date_added"=>$this->_date);
					
					$this->db->insert('r_products_specials',$data);
					
				}
			}

		}
		else if($type=='update')
		{
			$this->db->delete('r_products_specials',"products_id=".(int)$_REQUEST['rid']);
			foreach($_REQUEST['spec_price'] as $k=>$v)
			{
				if($v!="" && $_REQUEST['spec_rem_'.$k]!='1')
				{
					$data=array("products_id"=>(int)$_REQUEST['rid'],"customer_group_id"=>$_REQUEST['spec_cg'][$k],
					"priority"=>$_REQUEST['spec_priority'][$k],
					"specials_new_products_price"=>preg_replace('/[^0-9.]/s', '', $_REQUEST['spec_price'][$k]),"start_date"=>$_REQUEST['spec_sdate_'.$k],
					"expires_date"=>$_REQUEST['spec_edate_'.$k],"specials_date_added"=>$this->_date);
					$this->db->insert('r_products_specials',$data);
				}
			}
		}
	}

	}

	public function attributedropdown($id)
	{
            $opt=Model_Cache::getCache(array("id"=>"admin_attribute_".$id));
            if(!$opt)
            {
		$ag=$this->db->fetchAll("select attribute_group_id,name from r_attribute_group_description where language_id='1'");
		foreach($ag as $k)
		{
			$attr_group_name[$k['attribute_group_id']]=$k['name'];
		}

		$a=$this->db->fetchAll("select attribute_id,name from r_attribute_description where language_id='1'");
		foreach($a as $v)
		{
			$attr_name[$v['attribute_id']]=$v['name'];
		}

		$fch=$this->db->fetchAll("select attribute_id,attribute_group_id from r_attribute");

		$opt="";
		$opt.="<select name='attr_attribute_id[]' id='attr_attribute_id[]' class='input-medium' >";
		$opt.="<option value=''>Select</option>";
		foreach($fch as $m)
		{
			$sel=$m[attribute_id]==$id?'selected':'';
			$opt.="<option value='".$m[attribute_id]."' ".$sel." >".$attr_group_name[$m[attribute_group_id]]." --> ".$attr_name[$m[attribute_id]]."</option>";
		}
		$opt.="</select>";
                Model_Cache::getCache(array("id"=>"admin_attribute_".$id,"input"=>$opt,"tags"=>array("attribute","general")));
            }
		return $opt;
	}

	/*public function textdropdown($id)
	{
		$ag=$this->db->fetchAll("select od.name,od.option_id from r_option o,r_option_description od where
		od.language_id='1' and o.type='text' and o.option_id=od.option_id");

		$opt="";
		$opt.="<select name='text_cg[]' id='text_cg[]' class='input-medium' >";
		$opt.="<option value=''>Select</option>";
		foreach($ag as $m)
		{
			$sel=$m[option_id]==$id?'selected':'';
			$opt.="<option value='".$m[option_id]."' ".$sel." >".$m[name]."</option>";
		}
		$opt.="</select>";
		return $opt;
	}*/

	public function getRequiredArray($type,$rid)
	{
		$req=$this->db->fetchAll("select option_id,required from r_products_option where product_id='".(int)$rid."'
		and option_id in (select option_id from r_option where type='".$type."')");
		foreach($req as $rk)
		{
			$arr_req[$rk['option_id']]=$rk['required'];
		}
		return $arr_req;
	}

	
		public function multipledropdown($id,$type)
	{
	    $opt=Model_Cache::getCache(array("id"=>"admin_multipledropdown_".$id."_".$type));
            if(!$opt)
            {
                $ag=$this->db->fetchAll("select od.name,od.option_id from r_option o,r_option_description od where
		od.language_id='1' and o.type='".$type."' and o.option_id=od.option_id");
		$opt="";
		$opt.="<select name='".$type."_cg[]' id='".$type."_cg[]' class='input-medium' >";
		$opt.="<option value='0'>select</option>";
		foreach($ag as $m)
		{
			$opt.="<optgroup label='".$m[name]."'>";
			/*echo "select ovd.name,ovd.option_value_id,ovd.option_id from r_option_value ov,
			r_option_value_description ovd where 	ovd.language_id='1'	and  and ov.option_id=ovd.option_id
			and ov.option_id='".$m[option_id]."'";*/
 			$inner=$this->db->fetchAll("select ovd.name,ovd.option_value_id,ovd.option_id from
			r_option_value_description ovd where 	ovd.language_id='1'	and ovd.option_id='".$m[option_id]."'");
			foreach($inner as $n)
			{
				$sel=$n[option_value_id]."_".$n[option_id]==$id?'selected':'';
                                if(@constant('DEPENDENT_OPTIONS')=='1'){
                                    $opt.="<option value='".$n[option_value_id]."_".$n[option_id]."' ".$sel." >".$n[name]."->".$n[option_value_id]."</option>";
                                }else{
				$opt.="<option value='".$n[option_value_id]."_".$n[option_id]."' ".$sel." >".$n[name]."</option>";
                                }
			}
			$opt.="</optgroup>";
		}
		$opt.="</select>";
                Model_Cache::getCache(array("id"=>"admin_multipledropdown_".$id."_".$type,"input"=>$opt,"tags"=>array("multipledropdown","product","general")));
            }
		return $opt;
	}
	/*
	
	//before dependent options on sep 12 2012
	public function multipledropdown($id,$type)
	{
	    $opt=Model_Cache::getCache(array("id"=>"admin_multipledropdown_".$id."_".$type));
            if(!$opt)
            {
                $ag=$this->db->fetchAll("select od.name,od.option_id from r_option o,r_option_description od where
		od.language_id='1' and o.type='".$type."' and o.option_id=od.option_id");
		$opt="";
		$opt.="<select name='".$type."_cg[]' id='".$type."_cg[]' class='input-medium' >";
		$opt.="<option value='0'>select</option>";
		foreach($ag as $m)
		{
			$opt.="<optgroup label='".$m[name]."'>";
			$inner=$this->db->fetchAll("select ovd.name,ovd.option_value_id,ovd.option_id from
			r_option_value_description ovd where 	ovd.language_id='1'	and ovd.option_id='".$m[option_id]."'");
			foreach($inner as $n)
			{
				$sel=$n[option_value_id]."_".$n[option_id]==$id?'selected':'';
				$opt.="<option value='".$n[option_value_id]."_".$n[option_id]."' ".$sel." >".$n[name]."</option>";
			}
			$opt.="</optgroup>";
		}
		$opt.="</select>";
                Model_Cache::getCache(array("id"=>"admin_multipledropdown_".$id."_".$type,"input"=>$opt,"tags"=>array("multipledropdown","product","general")));
            }
		return $opt;
	}*/

	public function textdropdown($id,$type)
	{
		$ag=$this->db->fetchAll("select od.name,od.option_id from r_option o,r_option_description od where
		od.language_id='1' and o.type='".$type."' and o.option_id=od.option_id");

		$opt="";
		$opt.="<select name='".$type."_cg[]' id='".$type."_cg[]' class='input-medium' >";
		$opt.="<option value=''>Select</option>";
		foreach($ag as $m)
		{
			$sel=$m[option_id]==$id?'selected':'';
			$opt.="<option value='".$m[option_id]."' ".$sel." >".$m[name]."</option>";
		}
		$opt.="</select>";
		return $opt;
	}

	public function requiredropdown($id,$label)
	{
		$req_arr=array("1"=>"Yes","0"=>"No");
		$opt="";
		$opt.="<select name='".$label."_req[]' id='".$label."_req[]' class='input-medium' >";
		//$opt.="<option value=''>Select</option>";
		foreach($req_arr as $m=>$n)
		{
			$sel=$m==$id?'selected':'';
			$opt.="<option value='".$m."' ".$sel." >".$n."</option>";
		}
		$opt.="</select>";
		return $opt;
	}

public function symboldropdown($id,$label,$pre)
	{
		$req_arr=array("+"=>"+","-"=>"-");
		$opt="";
		$opt.="<select name='".$label."_".$pre."[]' id='".$label."_".$pre."[]'>";

		foreach($req_arr as $m=>$n)
		{
			$sel=$m==$id?'selected':'';
			$opt.="<option value='".$m."' ".$sel." >".$n."</option>";
		}
		$opt.="</select>";
		return $opt;
	}

	public function productdiscount($type,$inst_id)
	{
		if(count($_REQUEST['disc_price'])>0)
			{
		if($type=='insert')
		{
			foreach($_REQUEST['disc_price'] as $k=>$v)
			{
				if($v!="" && $_REQUEST['rem_'.$k]!='1')
				{
					$data=array("product_id"=>$inst_id,"customer_group_id"=>$_REQUEST['disc_cg'][$k],
					"quantity"=>$_REQUEST['disc_qty'][$k],"priority"=>$_REQUEST['disc_priority'][$k],
					"price"=>preg_replace('/[^0-9.]/s', '', $_REQUEST['disc_price'][$k]),"date_start"=>$_REQUEST['disc_sdate_'.$k],
					"date_end"=>$_REQUEST['disc_edate_'.$k]);
					$this->db->insert('r_product_discount',$data);
				}
			}
		}
		else if($type=='update')
		{
			$this->db->delete('r_product_discount',"product_id=".(int)$_REQUEST['rid']);
			foreach($_REQUEST['disc_price'] as $k=>$v)
			{
				if($v!="" && $_REQUEST['rem_'.$k]!='1')
				{
					$data=array("product_id"=>(int)$_REQUEST['rid'],"customer_group_id"=>$_REQUEST['disc_cg'][$k],
					"quantity"=>$_REQUEST['disc_qty'][$k],"priority"=>$_REQUEST['disc_priority'][$k],
					"price"=>preg_replace('/[^0-9.]/s', '', $_REQUEST['disc_price'][$k]),"date_start"=>$_REQUEST['disc_sdate_'.$k],
					"date_end"=>$_REQUEST['disc_edate_'.$k]);
					$this->db->insert('r_product_discount',$data);
				}
			}
		}
	}

	}

	public function getorderproductsdropdown($cid)
	{
		 $select = $this->db->fetchAll("select products_id,orders_id,products_name from r_orders_products group by products_id");
		 foreach($select as $v)
		 {
			//$selected=$v['customer_group_id']==$cid?'selected':'';
			$sel.= "<option value='".$v['products_id']."' ".$selected." >".$v['products_name']."</option>";
		 }
		 return $sel;
	}
	
	public function getcustomerdropdown($cid)
	{

		 $select = $this->db->fetchAll("select customers_id,concat(customers_firstname,' ',customers_lastname) as name,customers_email_address
		 from r_customers where customers_status='1' and customers_approved='1'");
		 foreach($select as $v)
		 {
			//$selected=$v['customer_group_id']==$cid?'selected':'';
			$sel.= "<option value='".$v['customers_id']."' ".$selected." >".$v['name']."</option>";
		 }
		 return $sel;
	}

	public function getcustomergroupdropdown($cid)
	{
                $sel=Model_Cache::getCache(array("id"=>"admin_customergroup_".$cid));
                if(!$sel)
                {
                    $select = $this->db->fetchAll("select customer_group_id,name from r_customer_group");
                    foreach($select as $v)
                    {
                            $selected=$v['customer_group_id']==$cid?'selected':'';

                            $sel.= "<option value='".$v['customer_group_id']."' ".$selected." >".$v['name']."</option>";
                    }
                    Model_Cache::getCache(array("id"=>"admin_customergroup_".$cid,"input"=>$sel,"tags"=>array("customergroup","general")));
                 }
		 return $sel;
	}

	public function reviewstatusdropdown($cid)
	{
            
		 $arr=array("1"=>STATUS_ENABLE_1,"0"=>STATUS_DISABLE_0);
		 foreach($arr as $k=>$v)
		 {			
                        if($cid!="")
                        {
                            $selected=$k==$cid?"selected":"";
                        }
			$sel.= "<option value='".$k."' ".$selected." >".$v."</option>";
		 }
		 return $sel;
	}

	public function getdropdown($arr,$id)
	{

		 foreach($arr as $k=>$v)
		 {
			 $selected=$k==$id?'selected':'';

			$sel.= "<option value='".$k."' ".$selected." >".$v."</option>";
		 }
		 return $sel;
	}

	public function modulegeozones()
	{
		 $select = $this->db->fetchAll("select geo_zone_id, geo_zone_name from r_geo_zones order by geo_zone_name");
		 $zone_class_array[] = array(array('id' => '0', 'text' => TEXT_NONE));
		 foreach($select as $v)
		 {
			$zone_class_array[] = array('id' => $v['geo_zone_id'],
			'text' => $v['geo_zone_name']);
		 }
		 return $zone_class_array;
	}

	public function moduletaxclass()
	{
		 $select = $this->db->fetchAll("select tax_class_id, tax_class_title from r_tax_class order by tax_class_title");
		 //$zone_class_array[] = array(array('id' => '0', 'text' => 'None'));
		 $zone_class_array[] = array('id' => '0', 'text' => 'None');
		 foreach($select as $v)
		 {
			$zone_class_array[] = array('id' => $v['tax_class_id'],
			'text' => $v['tax_class_title']);
		 }
		 return $zone_class_array;
	}

	public function tep_get_tax_rate_value($class_id) {

	 $tax_query = $this->db->fetchAll("select SUM(tax_rate) as tax_rate from r_tax_rates where tax_class_id = '" . (int)$class_id . "' group by tax_priority");

    if (count($tax_query)) {
      $tax_multiplier = 0;
      foreach ($tax_query as $tax) {
        $tax_multiplier += $tax['tax_rate'];
      }
      return $tax_multiplier;
    } else {
      return 0;
    }
  }

	public function getgeozonesdropdown($cid)
	{

		 $select = $this->db->fetchAll("select geo_zone_id,geo_zone_name from r_geo_zones");
		 foreach($select as $v)
		 {
			$selected=$v['geo_zone_id']==$cid?'selected':'';

			echo "<option value='".$v['geo_zone_id']."' ".$selected." >".$v['geo_zone_name']."</option>";
		 }
	}

	public function getzonedropdown($cid,$zid)
	{
		if($cid!="")
		{
			 $select = $this->db->fetchAll("select zone_id,zone_code,zone_name from r_zones where zone_country_id=".(int)$cid);
			 foreach($select as $v)
			 {
				$selected=$v['zone_id']==$zid?'selected':'';
				echo "<option value='".$v['zone_id']."' ".$selected." >".$v['zone_name']."</option>";
			 }
		}
	}

	public function getaddressformatdropdown($cid)
	{

		 $select = $this->db->fetchAll("select * from r_address_format");
		 foreach($select as $v)
		 {
			$selected=$v['address_format_id']==$cid?'selected':'';

			echo "<option value='".$v['address_format_id']."' ".$selected." >".$v['address_format_id']."</option>";
		 }
	}

	public function getstringmsg($str,$rep)
	{
		return base64_encode(str_replace('%row%',$rep,$str));
	}


	public function viewMultipleAction($arr)
	{
		echo '<select name="action" id="action" class="input-medium" onchange="fnaction(this.value);" ><option value="">Select action</option>';
		foreach($arr as $k=>$v)
		{
			 //$sel=$_REQUEST['action']==$k?'selected':'';
			echo "<option value='".$k."' ".$sel." >".$v."</option>";
		}
        echo '</select>';
	}

	public function fnArrayPar($arr)
	{

		//$arr=array("par1"=>"val1","par2"=>"val2","par3"=>"val3","par4"=>"val4","par5"=>"val5");
		if(sizeof($arr)>1)
		{
			foreach($arr as $k=>$v)
			{
				$par=$par.$join.$k."=".$v;
				$join="&";
			}
			$url="?".$par;

		}else if(sizeof($arr)==1)
		{
			foreach($arr as $k=>$v)
			$url="?".$k."=".$v;
		}else
		{
			$url="";
		}
		return $url;
	}

	public function viewAddB($act)
	{
		$actionname=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		if($_SESSION[arr_files_per][$actionname][Add]=='Y')
		{
		echo '<a href="'.$act.'?type=Add" class="button"><span>Add '.$actionname.'<img src="'.PATH_TO_ADMIN_IMAGES.'plus-small.gif"   width="12" height="9" alt="New article" /></span></a>';
		}
	}

	function fnmultipleactdropdown($arrmact)
	{
		if($_SESSION['role_id']=='1') //only global admin has access to multiple action dropdown
		{
			 echo '<div><span>Apply action to selected:</span>';
	         echo $this->viewMultipleAction($arrmact);
	     	 echo '</div>';
		}
	}

	public function viewDelB($act,$rid,$table,$field,$page)
	{
		$actionname=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		if($_SESSION[arr_files_per][$actionname][Del]=='Y')
		{
			$d='Del';
			echo	'<a href="javascript:fnSingleAction(\''.$d.'\',\''.$act.'\',\''.$rid.'\',\''.$table.'\',\'\'	,\''.$field.'\',\''.$page.'\');"><img src="'.PATH_TO_ADMIN_IMAGES.'bin.gif"   width="16" height="16" title="delete" /></a>';
		}
	}

	public function viewStatusB($val,$act,$rid,$table,$field,$field_comp,$page)
	{
		$status=$val=='0'?'Pub':'UnPub';
		$img=$status=='Pub'?'minus-circle.gif':'tick-circle.gif';
		echo	'<a href="javascript:fnSingleAction(\''.$status.'\',\''.$act.'\',\''.$rid.'\',\''.$table.'\',\''.$field.'\'	,\''.$field_comp.'\',\''.$page.'\');"><img src="'.PATH_TO_ADMIN_IMAGES.$img.'"   width="16" height="16" title="status" /></a>';
	}

	public function viewEditB($act,$rid,$page)
	{
		$actionname=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		if($_SESSION[arr_files_per][$actionname][Edit]=='Y' || $_SESSION[arr_files_per][$actionname][View]=='Y')
		{
		echo '<a href="'.$act.'?type=Edit&rid='.$rid.'&page='.$page.'"><img src="'.PATH_TO_ADMIN_IMAGES.'pencil.gif"  width="16" height="16" title="view/edit" /></a>';
		}
	}

/*start edit actions*/
	public function editSaveB($act,$rid,$type,$page)
	{
		$actionname=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		if($_SESSION[arr_files_per][$actionname][Edit]=='Y' || $_SESSION[arr_files_per][$actionname][Add]=='Y')
		{
			$url=$act.'?rid='.$rid.'&type='.$type.'&play=save&page='.$page;
			echo '<a href="javascript:fnSave(\''.$url.'\')"><img src="'.PATH_TO_ADMIN_IMAGES.'save_icon.jpg" alt="" />Save</a>';
		}
	}

	public function editApplyB($act,$rid,$type)
	{
		if($_REQUEST['type']=='Edit')
		{
			$actionname=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
			if($_SESSION[arr_files_per][$actionname][Edit]=='Y' || $_SESSION[arr_files_per][$actionname][Add]=='Y')
			{
				$url=$act.'?rid='.$rid.'&type='.$type.'&play=apply';
				echo '<li class="iconsec"><a href="javascript:fnSave(\''.$url.'\')"><img src="'.PATH_TO_ADMIN_IMAGES.'apply-icon.png" alt="" />Apply</a></li>';
			}
		}
	}

	public function editCancelB($act,$page)
	{		
		echo '<a href="javascript:fncancelpage(\''.$act.'?page='.$page.'\');"><img src="'.PATH_TO_ADMIN_IMAGES.'bin.gif" alt="" width="16" height="16" />Cancel</a>';
	}
/*end edit actions*/

/*start par buttons*/
	public function viewAddBP($act,$arr)
	{
		$actionname=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		if($_SESSION[arr_files_per][$actionname][Add]=='Y')
		{
		$url=$this->fnArrayPar($arr);
		$url=$act.$url;
		echo '<a href="'.$url.'" class="button"><span>Add <img src="'.PATH_TO_ADMIN_IMAGES.'plus-small.gif"   width="12" height="9" alt="New article" /></span></a>';
		}
	}

	public function editCancelBP($act,$arr)
	{

		$url=$this->fnArrayPar($arr);
		$url=$act.$url;
		echo '<a href="'.$url.'"><img src="'.PATH_TO_ADMIN_IMAGES.'bin.gif" alt="" width="16" height="16" />Cancel</a>';
	}

 	public function viewEditBP($act,$arr)
	{$actionname=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		if($_SESSION[arr_files_per][$actionname][Edit]=='Y' || $_SESSION[arr_files_per][$actionname][View]=='Y')
		{
			$url=$this->fnArrayPar($arr);
			$url=$act.$url;
			echo '<a href="'.$url.'"><img src="'.PATH_TO_ADMIN_IMAGES.'pencil.gif"  width="16" height="16" title="view/edit" /></a>';
		}
	}

	public function editApplyBP($act,$arr)
	{
		if($_REQUEST['type']=='Edit')
		{
			$actionname=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
			if($_SESSION[arr_files_per][$actionname][Edit]=='Y' || $_SESSION[arr_files_per][$actionname][Add]=='Y')
			{
				if($_REQUEST['type']=='Edit')
				{
					$url=$this->fnArrayPar($arr);
					$url=$act.$url;
					echo '<a href="javascript:fnSave(\''.$url.'\')"><img src="'.PATH_TO_ADMIN_IMAGES.'apply-icon.png" alt="" />Apply</a>';
				}
			}
		}
	}

	public function editSaveBP($act,$arr)
	{
		$actionname=Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		if($_SESSION[arr_files_per][$actionname][Add]=='Y' || $_SESSION[arr_files_per][$actionname][Edit]=='Y')
		{
			$url=$this->fnArrayPar($arr);
			$url=$act.$url;
			echo '<a href="javascript:fnSave(\''.$url.'\')"><img src="'.PATH_TO_ADMIN_IMAGES.'save_icon.jpg" alt="" />Save</a>';
		}
	}

	public function test()
	{
	echo "test function";
	}
/*end par buttons*/

    public function tep_generate_category_path($id, $from = 'category', $categories_array = '', $index = 0) {
    global $languages_id;

    if (!is_array($categories_array)) $categories_array = array();

    if ($from == 'product') {
		 $select = $this->db->fetchAll("select category_id from res_category_item where item_id = '" . (int)$id . "'");
		 $zone_class_array[] = array(array('id' => '0', 'text' => TEXT_NONE));
		 foreach($select as $v)
		 {
			if ($v['category_id'] == '0') {
			  $categories_array[$index][] = array('id' => '0', 'text' => 'Top');
			} else {
				$category = $this->db->fetchRow("select cd.name, cd.parent_id from res_categories cd where cd.categories_id = '" . (int)$v['category_id'] . "' and cd.status = '1'");
			  if ( ($this->tep_not_null($category['parent_id'])) && ($category['parent_id'] != '0') )
				  $categories_array = $this->tep_generate_category_path($category['parent_id'], 'category', $categories_array, $index);
			  $categories_array[$index] = array_reverse($categories_array[$index]);
			}
			$index++;
		 }
    } elseif ($from == 'category') {

      $category = $this->db->fetchAll("select cd.categories_name, c.parent_id from r_categories c, r_categories_description cd where c.del='0' and c.categories_id = '" . (int)$id . "' and c.categories_id = cd.categories_id and cd.language_id = '1'");
	 // echo "select cd.categories_name, c.parent_id from r_categories c, r_categories_description cd where c.categories_id = '" . (int)$id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'";
     // $category = tep_db_fetch_array($category_query);
      $categories_array[$index][] = array('id' => $id, 'text' => $category[0]['categories_name']);
      if ( ($this->tep_not_null($category[0]['parent_id'])) && ($category[0]['parent_id'] != '0') )
		  $categories_array = $this->tep_generate_category_path($category[0]['parent_id'], 'category', $categories_array, $index);
    }
	//echo "<pre>";
	//print_r($categories_array);

	return $categories_array;
  }


  function tep_output_generated_category_path($id, $from = 'category') {
    $calculated_category_path_string = '';
    $calculated_category_path = $this->tep_generate_category_path($id, $from);
    for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
		//$calculated_category_path_string .= $calculated_category_path[$i][$j]['text'] . '_';
      }
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -16) . '<br />';
    }
    $calculated_category_path_string = substr($calculated_category_path_string, 0, -6);

    if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = 'Top';

    return $calculated_category_path_string;
  }


function tep_not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if ( (is_string($value) || is_int($value)) && ($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }

  public function tep_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;
//echo $parameters;
//echo "<pre>";print_r($default);exit;
     $field = '<select name="' . $this->tep_output_string($name) . '" class="input-medium" ';

    if ($this->tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) ) {
      if (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) {
        $default = stripslashes($HTTP_GET_VARS[$name]);
      } elseif (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) {
        $default = stripslashes($HTTP_POST_VARS[$name]);
      }
    }

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . $this->tep_output_string($values[$i]['id']) . '"';

	  if($parameters=='multiple')
		{
 			if(is_array($default))
			{
				if (in_array($values[$i]['id'],$default))
				{
					$field .= ' selected="selected"';
				}
			}
	  }else
	  {
		  if ($default == $values[$i]['id'])
		  {
				$field .= ' selected="selected"';
		  }
	  }

      $field .= '>' . $this->tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

  public function tep_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      return htmlspecialchars($string);
    } else {
      if ($translate == false) {
        return $this->tep_parse_input_field_data($string, array('"' => '&quot;'));
      } else {
        return $this->tep_parse_input_field_data($string, $translate);
      }
    }
  }

  public  function tep_parse_input_field_data($data, $parse) {
    return strtr(trim($data), $parse);
  }
  
  public function tep_get_category_tree_cache()
  {
      $data=Model_Cache::getCache(array("id"=>"admin_category_tree"));
      if(!$data)
      {
            $data=$this->tep_get_category_tree();
            Model_Cache::getCache(array("id"=>"admin_category_tree","input"=>$data,"tags"=>array("category_dropdown","general")));
      }
      return $data;
  }

 public function tep_get_category_tree($parent_id = '0', $spacing = '', $exclude = '', $category_tree_array = '', $include_itself = false) {
    global $languages_id;

    if (!is_array($category_tree_array)) $category_tree_array = array();
    if ( (sizeof($category_tree_array) < 1) && ($exclude != '0') ) $category_tree_array[] = array('id' => '0', 'text' => 'Top');

    if ($include_itself) {
		$category = $this->db->fetchAll("select cd.categories_name from r_categories_description cd where cd.language_id = '1' and cd.categories_id = '" . (int)$parent_id . "'");

      $category_tree_array[] = array('id' => $parent_id, 'text' => $category[0]['categories_name']);
    }

$categories=$this->db->fetchAll("select c.categories_id, cd.categories_name, c.parent_id from r_categories c, r_categories_description cd where c.del='0' and c.categories_id = cd.categories_id and cd.language_id = '1' and c.parent_id = '" . (int)$parent_id . "' order by c.sort_order, cd.categories_name");

foreach($categories as $v)
		 {

      if ($exclude != $v['categories_id']) $category_tree_array[] = array('id' => $v['categories_id'], 'text' => $spacing . $v['categories_name']);

      $category_tree_array = $this->tep_get_category_tree($v['categories_id'], $spacing . '&nbsp;&nbsp;&nbsp;', $exclude, $category_tree_array);
    }
	//echo '<pre>';
	//print_r($category_tree_array);
    return $category_tree_array;
  }

  public function lang_mult_array($arr)
  {
  	$select = $this->db->fetchAll("select * from r_languages order by sort_order");
	foreach($select as $row)
	{
		foreach($arr as $k=>$v)
		{
			//$input_fields[$v]=$_REQUEST[$v."_".$row['languages_id']];
			//echo "value of ".$_REQUEST[$v."_".$row['languages_id']]."<br/>";
			//$input_fields[$v]= htmlspecialchars($_REQUEST[$v."_".$row['languages_id']], ENT_COMPAT, 'UTF-8');
			$input_fields[$v]= $_REQUEST[$v."_".$row['languages_id']];
			
		}
		//print_r($input_fields);
		//exit;
		$field[$row['languages_id']]=$input_fields;
	}
	return $field;
	//$this->p($field,'1');

  }
	public function lang_action($arr_cols,$arr_table,$act)
	{

		$mult_arr=$this->lang_mult_array($arr_cols);
		$select = $this->db->fetchAll("select * from r_languages order by sort_order");
		foreach($select as $row)
		{
			switch($act)
			{
				case 'update':
							  $n =  $this->db->update($arr_table['table'], $mult_arr[$row['languages_id']],		array($arr_table['comp_col_1']."=".$arr_table['rid'],$arr_table['comp_col_2']."=".(int)$row['languages_id']));
							  break;

				case 'insert':
			   				  $mrg=array_merge($mult_arr[$row['languages_id']],array($arr_table['comp_col_1']=>$arr_table['rid'],'language_id'=>$row['languages_id']));
							  //$this->p($mrg,'0');
							  //echo $arr_table['table']."<br/>";
							  $n=$this->db->insert($arr_table['table'],$mrg);
							  //exit;
				    		  break;
			}
		}
		//exit;
	}

	public function field($arr)
	{
		$field.='<p>';
		$validate=$arr['req']=='1'?"title='".$arr['lable']."'":''; //confirm validation
		$star=$arr['req']=='1'?'*':''; //to specify star symbol
		$field.='<label>'.$star.$arr['lable'].'</label>';
		//$field.='<label>'.$star.$arr['lable'].'</label>';
		switch($arr['input_type'])
		{
		case 'input_text':
						$field.='<input name="'.$arr['input_title'].'" id="'.$arr['input_title'].'" type="text" '.$validate.' class="input-medium" value="'.$arr['val'].'" tooltipText="'.$arr['desc'].'" '.$arr['readonly'].' '.$arr['maxlength'].'  />';
						break;
		case 'password':
						$field.='<input name="'.$arr['input_title'].'" id="'.$arr['input_title'].'" type="password" '.$validate.' class="input-medium" value="'.$arr['val'].'" tooltipText="'.$arr['desc'].'" '.$arr['readonly'].' '.$arr['maxlength'].'  />';
						break;

		case 'textarea':
						$field.='<textarea name="'.$arr['input_title'].'" id="'.$arr['input_title'].'" rows="5" cols="50" tooltipText="'.$arr['desc'].'" >'.$arr['val'].'</textarea>';
						break;

		case 'select':
						//echo $arr['val'];
						$field.='<select name="'.$arr['input_title'].'" id="'.$arr['input_title'].'" tooltipText="'.$arr['desc'].'" >'.$arr['val'].'</select>';
						break;
		}
		$field.='</p>';
		$i++;
		return $field;
	}
	public function getlangattri($at_id,$rid,$j)
	{
		$ret=$this->db->fetchAll("select text,language_id from r_product_attribute_group where
		 attribute_id='".(int)$at_id."' and product_id='".(int)$rid."'");

		foreach($ret as $res)
		{
		$lang[$res['language_id']]=array("attr_text_$j" =>$res['text']);
		}
 		echo $this->lang_field(array('lable'=>'','input_title'=>'attr_text_'.$j,'desc'=>'enter text',
		'input_type'=>'textarea','val'=>$lang,"req"=>'0'));
	}

	public function getlangoption($at_id,$rid,$j)
	{
		$ret=$this->db->fetchAll("select name,language_id from r_option_value_description where
		 option_value_id='".(int)$at_id."' and option_id='".(int)$rid."'");

		foreach($ret as $res)
		{
		$lang[$res['language_id']]=array("attr_text_$j" =>$res['name']);
		}
 		echo $this->lang_field(array('lable'=>'','input_title'=>'attr_text_'.$j,'desc'=>'enter name',
		'input_type'=>'input_text','val'=>$lang,"req"=>'0'));
	}

	public function getlangoptionhidden($at_id,$rid,$j)
	{
		$ret=$this->db->fetchAll("select option_value_id,language_id from r_option_value_description where
		 option_value_id='".(int)$at_id."' and option_id='".(int)$rid."'");

		foreach($ret as $res)
		{
		$lang[$res['language_id']]=array("attr_hid_$j" =>$res['option_value_id']);
		}
 		echo $this->lang_field(array('lable'=>'','input_title'=>'attr_hid_'.$j,'desc'=>'enter name',
		'input_type'=>'hidden','val'=>$lang,"req"=>'0'));
	}
public function getLanguages()
{
$language_data = array();
	$query = $this->db->query("SELECT * FROM r_languages ORDER BY sort_order, name");
	$query->rows=$query->fetchAll();
    			foreach ($query->rows as $result) {
      				$language_data[$result['code']] = array(
        				'languages_id' => $result['languages_id'],
        				'name'        => $result['name'],
        				'code'        => $result['code'],
 						'image'       => $result['image'],
						'directory'   => $result['directory'],
 						'sort_order'  => $result['sort_order'],
						'status'      => $result['status']
      				);
    			}
				return $language_data;
}
  /*public function lang_field($arr)
  {
	  //$this->p($arr);
	$select = $this->db->fetchAll("select * from r_languages order by sort_order");
	$i=0;
	foreach($select as $row)
	{
		$field.='<p>';
		$validate=$arr['req']=='1'?"title='".$arr['lable']."'":''; //confirm validation
		$star=$arr['req']=='1'?'*':''; //to specify star symbol
		$field.=$i=='0'?'<label>'.$star.$arr['lable'].'</label>':'';
		//$field.='<label>'.$star.$arr['lable'].'</label>';
		switch($arr['input_type'])
		{
			case 'input_text':
							$field.='<input name="'.$arr['input_title'].'_'.$row['languages_id'].'" id="'.$arr['input_title'].'_'.$row['languages_id'].'" type="text" '.$validate.' class="input-medium" value="'.$arr['val'][$row['languages_id']][$arr['input_title']].'" tooltipText="'.$arr['desc'].'"/><img src="'.PUBLIC_PATH.'languages/'.$row['directory'].'/'.$row['image'].'"/ title="'.$row['name'].'" alt="'.$row['name'].'" >';
							break;

			case 'hidden':
							$field.='<input name="'.$arr['input_title'].'_'.$row['languages_id'].'"
							id="'.$arr['input_title'].'_'.$row['languages_id'].'" type="hidden" '.$validate.'
							class="input-medium" value="'.$arr['val'][$row['languages_id']][$arr['input_title']].'"
							tooltipText="'.$arr['desc'].'"/>';
							break;

			case 'textarea':
							$field.='<textarea name="'.$arr['input_title'].'_'.$row['languages_id'].'" id="'.$arr['input_title'].'_'.$row['languages_id'].'" rows="5" cols="50" '.$validate.' tooltipText="'.$arr['desc'].'" >'.$arr['val'][$row['languages_id']][$arr['input_title']].'</textarea><img title="'.$row['name'].'" src="'.PUBLIC_PATH.'languages/'.$row['directory'].'/'.$row['image'].'" alt="'.$row['name'].'" />';
							break;
		}
		$field.='</p>';
    $i++;}
  return $field;
  }*/

  public function lang_field($arr)
  {
	$select = $this->db->fetchAll("select * from r_languages order by sort_order");
	$i=0;
	foreach($select as $row)
	{
		$field.='<p>';
		$validate=$arr['req']=='1'?"title='".$arr['lable']."'":''; //confirm validation
		$star=$arr['req']=='1'?'*':''; //to specify star symbol
		$field.=$i=='0'?'<label>'.$star.$arr['lable'].'</label>':'';
		//$field.='<label>'.$star.$arr['lable'].'</label>';
		switch($arr['input_type'])
		{
			case 'input_text':
							$field.='<input name="'.$arr['input_title'].'_'.$row['languages_id'].'" id="'.$arr['input_title'].'_'.$row['languages_id'].'" type="text" '.$validate.' class="input-medium" value="'.$arr['val'][$row['languages_id']][$arr['input_title']].'" tooltipText="'.$arr['desc'].'"/>';
							break;

			case 'hidden':
							$field.='<input name="'.$arr['input_title'].'_'.$row['languages_id'].'"
							id="'.$arr['input_title'].'_'.$row['languages_id'].'" type="hidden" '.$validate.'
							class="input-medium" value="'.$arr['val'][$row['languages_id']][$arr['input_title']].'"
							tooltipText="'.$arr['desc'].'"/>';
							break;

			case 'textarea':
							$field.='<textarea name="'.$arr['input_title'].'_'.$row['languages_id'].'" id="'.$arr['input_title'].'_'.$row['languages_id'].'" rows="5" cols="50" '.$validate.' tooltipText="'.$arr['desc'].'" >'.$arr['val'][$row['languages_id']][$arr['input_title']].'</textarea>';
							break;
		}
		$field.='</p>';
    $i++;}
  return $field;
  }

	public function p($arr,$e)
	{
		echo "<pre>";
		print_r($arr);
		if($e=='1')
		{
		exit();
		}
	}

	public function delete_category($rid)
	{
        if (isset($rid)) {
          $categories_id = $rid;
          $categories = $this->tep_get_category_tree($categories_id, '', '0', '', true);
          $products = array();
          $products_delete = array();

          for ($i=0, $n=sizeof($categories); $i<$n; $i++)
			 {
				$select = $this->db->fetchAll("select products_id from r_products_to_categories order by sort_order where categories_id = '" . (int)$categories[$i]['id'] . "'");
				foreach($select as $product_ids)
				{
		              $products[$product_ids['products_id']]['categories'][] = $categories[$i]['id'];
				}

          reset($products);
          while (list($key, $value) = each($products)) {
            $category_ids = '';

            for ($i=0, $n=sizeof($value['categories']); $i<$n; $i++) {
              $category_ids .= "'" . (int)$value['categories'][$i] . "', ";
            }
            $category_ids = substr($category_ids, 0, -2);

			$check_query = $this->db->fetchAll("select count(*) as total from r_products_to_categories where products_id = '" . (int)$key . "' and categories_id not in (" . $category_ids . ")");

            $check = $check_query;
            if ($check[0]['total'] < '1') {
              $products_delete[$key] = $key;
            }
          }

// removing categories can be a lengthy process
          $this->tep_set_time_limit(0);
          for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
            $this->tep_remove_category($categories[$i]['id']);
          }

          reset($products_delete);
          while (list($key) = each($products_delete)) {
             $this->tep_remove_product($key);
          }
        }

        /*if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath));*/
	}

	function tep_set_time_limit($limit) {
    if (!get_cfg_var('safe_mode')) {
      set_time_limit($limit);
    }
  }
}
	function tep_remove_category($category_id)
	{
	$category_image_query = $this->db->fetchAll("select categories_image from r_categories where categories_id = '" . (int)$category_id . "'");

    $category_image = $category_image_query;

	$duplicate_image_query = $this->db->fetchAll("select count(*) as total from r_categories where categories_image = '" .$category_image['categories_image'] . "'");
    $duplicate_image = $duplicate_image_query;

    if ($duplicate_image[0]['total'] < 2) {
      if (file_exists(DIR_FS_CATALOG_IMAGES . $category_image[0]['categories_image'])) {
        @unlink(DIR_FS_CATALOG_IMAGES . $category_image[0]['categories_image']);
      }
    }

	$this->db->delete('r_categories', 'categories_id='.(int)$category_id);
	$this->db->delete('r_categories_description', 'categories_id='.(int)$category_id);
	$this->db->delete('r_products_to_categories', 'categories_id='.(int)$category_id);

    /*if (USE_CACHE == 'true') {
      tep_reset_cache_block('categories');
      tep_reset_cache_block('also_purchased');
    }*/
  }

  function tep_remove_product($product_id)
	  {
    $product_image_query = tep_db_query("select products_image from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
    $product_image = tep_db_fetch_array($product_image_query);

    $duplicate_image_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image = '" . tep_db_input($product_image['products_image']) . "'");
    $duplicate_image = tep_db_fetch_array($duplicate_image_query);

    if ($duplicate_image['total'] < 2) {
      if (file_exists(DIR_FS_CATALOG_IMAGES . $product_image['products_image'])) {
        @unlink(DIR_FS_CATALOG_IMAGES . $product_image['products_image']);
      }
    }

    $product_images_query = tep_db_query("select image from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$product_id . "'");
    if (tep_db_num_rows($product_images_query)) {
      while ($product_images = tep_db_fetch_array($product_images_query)) {
        $duplicate_image_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_IMAGES . " where image = '" . tep_db_input($product_images['image']) . "'");
        $duplicate_image = tep_db_fetch_array($duplicate_image_query);

        if ($duplicate_image['total'] < 2) {
          if (file_exists(DIR_FS_CATALOG_IMAGES . $product_images['image'])) {
            @unlink(DIR_FS_CATALOG_IMAGES . $product_images['image']);
          }
        }
      }

      tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$product_id . "'");
    }

    tep_db_query("delete from " . TABLE_SPECIALS . " where products_id = '" . (int)$product_id . "'");
    tep_db_query("delete from " . TABLE_PRODUCTS . " where products_id = '" . (int)$product_id . "'");
    tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "'");
    tep_db_query("delete from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$product_id . "'");
    tep_db_query("delete from " . TABLE_PRODUCTS_ATTRIBUTES . " where products_id = '" . (int)$product_id . "'");
    tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where products_id = '" . (int)$product_id . "' or products_id like '" . (int)$product_id . "{%'");
    tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where products_id = '" . (int)$product_id . "' or products_id like '" . (int)$product_id . "{%'");

    $product_reviews_query = tep_db_query("select reviews_id from " . TABLE_REVIEWS . " where products_id = '" . (int)$product_id . "'");
    while ($product_reviews = tep_db_fetch_array($product_reviews_query)) {
      tep_db_query("delete from " . TABLE_REVIEWS_DESCRIPTION . " where reviews_id = '" . (int)$product_reviews['reviews_id'] . "'");
    }
    tep_db_query("delete from " . TABLE_REVIEWS . " where products_id = '" . (int)$product_id . "'");

    /*if (USE_CACHE == 'true') {
      tep_reset_cache_block('categories');
      tep_reset_cache_block('also_purchased');*/
    }

  }
?>