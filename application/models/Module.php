<?php
class Model_Module
{
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		/*$this->rSetting=new Model_DbTable_rsetting();
		$this->rSetting->getsetting();*/
	}

	public function getModules($arr)
	{
		/*echo "<pre>";
		print_r($arr);
		echo "</pre>";*/
		$module_data = Model_Cache::getCache(array('id'=>'module_' .$arr['page']));
		if(!$module_data)
		{
			$fetch=$this->db->fetchAll("SELECT * FROM  `r_setting` WHERE  `key` LIKE  '%_layout_id'  and value='".$arr['page']."'");
			foreach($fetch as $row)
			{
				$exp= explode("_",$row[key]);
				if(constant($exp[0]."_".$exp[1]."_status")=='1')
				{
					if(constant($exp[0]."_".$exp[1]."_position")=='top')
					{
						$arr[top][]=constant($exp[0]."_".$exp[1]."_sort_order")."_".$exp[0]."_".$exp[1];
					}else if(constant($exp[0]."_".$exp[1]."_position")=='bottom')
					{
						$arr[bottom][]=constant($exp[0]."_".$exp[1]."_sort_order")."_".$exp[0]."_".$exp[1];
					}else if(constant($exp[0]."_".$exp[1]."_position")=='right')
					{
						$arr[right][]=constant($exp[0]."_".$exp[1]."_sort_order")."_".$exp[0]."_".$exp[1];
					}else if(constant($exp[0]."_".$exp[1]."_position")=='left')
					{
						$arr[left][]=constant($exp[0]."_".$exp[1]."_sort_order")."_".$exp[0]."_".$exp[1];
					}
				}

			}
			$module_data=$arr;
			$module_data=Model_Cache::getCache(array('id'=>'module_' .$arr['page'],"input"=>$module_data));
		}
		return $module_data;
	}

	public function isModuleEnable($data)
	{
		$qry=$this->db->query("select * from r_extension where code like '".$data['module']."'");
		if($qry->rowCount()>0)
			return "1";
		else
			return "0";
	}
}
?>