<?php
class Model_Shipping_Vpp {
    var $code, $title, $description, $icon, $enabled;
	public $db;
    function Model_Shipping_Vpp() {
      $this->code = 'Vpp';
      $this->title = @constant('MODULE_SHIPPING_VPP_MAIN_TITLE')==""?"Vpp Shipping Services":MODULE_SHIPPING_VPP_MAIN_TITLE;
      $this->description = MODULE_SHIPPING_VPP_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_VPP_SORT_ORDER;
      $this->icon = '';
      //$this->tax_class = MODULE_SHIPPING_FLAT_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_VPP_STATUS == 'True') ? true : false);

     $this->db = Zend_Db_Table::getDefaultAdapter();

	     if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_VPP_ZONE > 0) ) {
        $check_flag = false;
		$check_flag_country=false;
		$check_flag_zone=false;
		$addr=$this->getAddress();
		//print_r($addr);
        $check_query = $this->db->query("select zone_id,zone_country_id  from r_zones_to_geo_zones where geo_zone_id='" . MODULE_SHIPPING_VPP_ZONE . "'");
        $fetch=$check_query->fetchAll();
		//print_r($fetch);
		foreach($fetch as $k=>$v)  //check country
		{
			//echo $v['zone_country_id']. "=".$addr['country_id']."<br/>";
			if($v['zone_country_id']==$addr['country_id'])
			{
				$check_flag_country = true;	
			}
		}
		
		if($check_flag_country==true)
		{
			foreach($fetch as $k=>$v) //check zone
			{
				if($v['zone_id']==$addr['zone_id'] || $v['zone_id']==0)
				{
					$check_flag_zone = true;	
				}
			}
		}
		
		if($check_flag_country==true && $check_flag_zone==true)
		  {
			$this->enabled = true;
		  }else
		  {
		  $this->enabled = false;
		  }
        }
    }

// class methods
    function quote($method = '') {
     $this->quotes = array('id' => $this->code,
                            'module' => $_SESSION['OBJ']['tr']->translate('MODULE_SHIPPING_VPP_TEXT_TITLE'),
                            'methods' => array(array('id' => $this->code,
                                                     'title' => @constant('MODULE_SHIPPING_VPP_DESCRIPTION'),
                                                     'cost' => '0')));

      if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);
	  return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = mysql_query("select configuration_value from r_configuration where configuration_key = 'MODULE_SHIPPING_FLAT_STATUS'");
        $this->_check = mysql_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
		$this->db->insert('r_configuration',array('configuration_title'=>'Main Title','configuration_key'=>'MODULE_SHIPPING_VPP_MAIN_TITLE','configuration_value'=>'Vpp Shipping Services','configuration_description'=>'enter main title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'1','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Sub Title','configuration_key'=>'MODULE_SHIPPING_VPP_SUB_TITLE','configuration_value'=>'Best Way','configuration_description'=>'enter sub title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'2','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Enable Flat Shipping','configuration_key'=>'MODULE_SHIPPING_VPP_STATUS','configuration_value'=>'True','configuration_description'=>'Do you want to offer vpp?','configuration_group_id'=>'6','sort_order'=>'0','set_function'=>'tep_cfg_select_option(array(\'True\', \'False\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Shipping Description','configuration_key'=>'MODULE_SHIPPING_VPP_DESCRIPTION','configuration_value'=>'','configuration_description'=>'The message enter here will be displayed at front end.','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

		 $this->db->insert('r_configuration',array('configuration_title'=>'Shipping Zone','configuration_key'=>'MODULE_SHIPPING_VPP_ZONE','configuration_value'=>'0','configuration_description'=>'If a zone is selected, only enable this shipping method for that zone.','configuration_group_id'=>'6','sort_order'=>'0','use_function'=>'tep_get_zone_class_title','set_function'=>'tep_cfg_pull_down_zone_classes(','date_added'=>new Zend_Db_Expr('NOW()')));


		$this->db->insert('r_configuration',array('configuration_title'=>'Sort Order','configuration_key'=>'MODULE_SHIPPING_VPP_SORT_ORDER','configuration_value'=>'0','configuration_description'=>'Sort order of display.','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));
   }

    function remove() 
	{
		$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");	   
    }

    function keys() {
      return array('MODULE_SHIPPING_VPP_MAIN_TITLE','MODULE_SHIPPING_VPP_SUB_TITLE','MODULE_SHIPPING_VPP_STATUS', 'MODULE_SHIPPING_VPP_DESCRIPTION','MODULE_SHIPPING_VPP_SORT_ORDER','MODULE_SHIPPING_VPP_ZONE');
    }

	public function getAddress()
	{
		$row=array();
		if($_SESSION['customer_id']=="" && $_SESSION['guest']!="")
		{
			$row['zone_id']=$_SESSION['guest']['shipping']['zone_id'];
			$row['country_id']=$_SESSION['guest']['shipping']['country_id'];
		}else
		{
			$row=$this->db->fetchRow("select a.entry_country_id AS country_id, a.entry_zone_id AS zone_id from r_address_book a,r_countries c,r_zones z where a.entry_zone_id=z.zone_id and a.entry_country_id=c.countries_id and a.address_book_id='".$_SESSION['shipping_address_id']."'");
			
		}
		return $row;
	}
  }
?>
