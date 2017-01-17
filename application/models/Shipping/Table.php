<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce

  Released under the GNU General Public License
*/

  class Model_Shipping_Table {
    var $code, $title, $description, $icon, $enabled;

// class constructor
    function Model_Shipping_Table() {
      //global $order;
	  //$order=$_SESSION['order'];
      $this->code = 'Table';
      $this->title = @constant('MODULE_SHIPPING_TABLE_MAIN_TITLE')==""?"Table Rate":MODULE_SHIPPING_TABLE_MAIN_TITLE;//MODULE_SHIPPING_TABLE_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_TABLE_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_TABLE_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = MODULE_SHIPPING_TABLE_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_TABLE_STATUS == 'True') ? true : false);
	  $this->db = Zend_Db_Table::getDefaultAdapter();	
      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_TABLE_ZONE > 0) ) {
        $check_flag = false;
		$check_flag_country=false;
		$check_flag_zone=false;
		$addr=$this->getAddress();
		//print_r($addr);
        $check_query = $this->db->query("select zone_id,zone_country_id  from r_zones_to_geo_zones where geo_zone_id='" . MODULE_SHIPPING_TABLE_ZONE . "'");
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
		//echo "value of".$this->enabled;
    }

// class methods
    function quote($method = '') {
      global $order, $shipping_weight, $shipping_num_boxes;
	  $cartObj=new Model_Cart();
	  $shipping_weight=$cartObj->getWeight();
      if (MODULE_SHIPPING_TABLE_MODE == 'price') {
		//  echo "in price";
        $cartObj=new Model_Cart();
		$order_total =$cartObj->getTotal();
		//$order_total = $this->getShippableTotal();
      } else {
		 // echo "in weight";
        $order_total = $shipping_weight;
      }

      $table_cost = preg_split("/[:,]/" , MODULE_SHIPPING_TABLE_COST);
	/*echo "<pre>";
	print_r($table_cost);
	echo "</pre>";*/
	  $size = sizeof($table_cost);
      for ($i=0, $n=$size; $i<$n; $i+=2) {
	  if ($order_total <= $table_cost[$i]) {
          $shipping = $table_cost[$i+1];
          break;
        }
      }

      if (MODULE_SHIPPING_TABLE_MODE == 'weight') {
        //$shipping = $shipping * $shipping_num_boxes;
		$shipping = $shipping;
      }
		//echo "shipping value ".$shipping;
      $this->quotes = array('id' => $this->code,
                            'module' => @constant('MODULE_SHIPPING_TABLE_MAIN_TITLE'),
                            'methods' => array(array('id' => $this->code,
                                                     'title' => @constant('MODULE_SHIPPING_TABLE_SUB_TITLE'),
                                                     'cost' => $shipping + MODULE_SHIPPING_TABLE_HANDLING)));

      if ($this->tax_class > 0) {
        //$this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
   	    $this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $_SESSION['shipping_country_id'], $_SESSION['shipping_zone_id']);


	  }

      if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);
//print_r($this->quotes);
	//exit;
      return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = mysql_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_SHIPPING_TABLE_STATUS'");
        $this->_check = mysql_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {

		$this->db->insert('r_configuration',array('configuration_title'=>'Main Title','configuration_key'=>'MODULE_SHIPPING_TABLE_MAIN_TITLE','configuration_value'=>'Table Rate','configuration_description'=>'enter main title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Sub Title','configuration_key'=>'MODULE_SHIPPING_TABLE_SUB_TITLE','configuration_value'=>'Best Way','configuration_description'=>'enter sub title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'1','date_added'=>new Zend_Db_Expr('NOW()')));

	  $this->db->insert('r_configuration',array('configuration_title'=>'Enable Table Method','configuration_key'=>'MODULE_SHIPPING_TABLE_STATUS','configuration_value'=>'True','configuration_description'=>'Do you want to offer table rate shipping?','configuration_group_id'=>'6','sort_order'=>'0','set_function'=>'tep_cfg_select_option(array(\'True\', \'False\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

      $this->db->insert('r_configuration',array('configuration_title'=>'Shipping Table','configuration_key'=>'MODULE_SHIPPING_TABLE_COST','configuration_value'=>'25:8.50,50:5.50,10000:0.00','configuration_description'=>'The shipping cost is based on the total cost or weight of items. Example: 25:8.50,50:5.50,etc.. Up to 25 charge 8.50, from there to 50 charge 5.50, etc','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

      $this->db->insert('r_configuration',array('configuration_title'=>'Table Methos','configuration_key'=>'MODULE_SHIPPING_TABLE_MODE','configuration_value'=>'weight','configuration_description'=>'The shipping cost is based on the order total or the total weight of the items ordered.','configuration_group_id'=>'6','sort_order'=>'0','set_function'=>'tep_cfg_select_option(array(\'weight\', \'price\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

     $this->db->insert('r_configuration',array('configuration_title'=>'Handling Fee','configuration_key'=>'MODULE_SHIPPING_TABLE_HANDLING','configuration_value'=>'0','configuration_description'=>'Handling fee for this shipping method.','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

      $this->db->insert('r_configuration',array('configuration_title'=>'Tax Class','configuration_key'=>'MODULE_SHIPPING_TABLE_TAX_CLASS','configuration_value'=>'0','configuration_description'=>'Use the following tax class on the shipping fee.','configuration_group_id'=>'6','sort_order'=>'0','use_function'=>'tep_get_tax_class_title','set_function'=>'tep_cfg_pull_down_tax_classes(','date_added'=>new Zend_Db_Expr('NOW()')));

	 $this->db->insert('r_configuration',array('configuration_title'=>'Shipping Zone','configuration_key'=>'MODULE_SHIPPING_TABLE_ZONE','configuration_value'=>'0','configuration_description'=>'If a zone is selected, only enable this shipping method for that zone.','configuration_group_id'=>'6','sort_order'=>'0','use_function'=>'tep_get_zone_class_title','set_function'=>'tep_cfg_pull_down_zone_classes(','date_added'=>new Zend_Db_Expr('NOW()')));

     $this->db->insert('r_configuration',array('configuration_title'=>'Sort Order','configuration_key'=>'MODULE_SHIPPING_TABLE_SORT_ORDER','configuration_value'=>'0','configuration_description'=>'Sort order of display.','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

    }

    function remove() {
		$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");
       }

    function keys() {
      return array('MODULE_SHIPPING_TABLE_MAIN_TITLE','MODULE_SHIPPING_TABLE_SUB_TITLE','MODULE_SHIPPING_TABLE_STATUS', 'MODULE_SHIPPING_TABLE_COST', 'MODULE_SHIPPING_TABLE_MODE', 'MODULE_SHIPPING_TABLE_HANDLING', 'MODULE_SHIPPING_TABLE_TAX_CLASS', 'MODULE_SHIPPING_TABLE_ZONE', 'MODULE_SHIPPING_TABLE_SORT_ORDER');
    }

    function getShippableTotal() {
      global $order, $cart, $currencies;

	//  $order=$_SESSION['order'];
	 // $cart=$_SESSION['order'];
	  // $currencies=$_SESSION['currencies'];

      $order_total = $cart->show_total();
//echo "order total 12/20/2011 ".$order_total;
//exit;
      if ($order->content_type == 'mixed') {
        $order_total = 0;

        for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
          $order_total += $currencies->calculate_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']);

          if (isset($order->products[$i]['attributes'])) {
            reset($order->products[$i]['attributes']);
            while (list($option, $value) = each($order->products[$i]['attributes'])) {
              $virtual_check_query = mysql_query("select count(*) as total from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad where pa.products_id = '" . (int)$order->products[$i]['id'] . "' and pa.options_values_id = '" . (int)$value['value_id'] . "' and pa.products_attributes_id = pad.products_attributes_id");
              $virtual_check = tep_db_fetch_array($virtual_check_query);

              if ($virtual_check['total'] > 0) {
                $order_total -= $currencies->calculate_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']);
              }
            }
          }
        }
      }

      return $order_total;
    }

	public function getAddress()
	{
		$row=array();
		if($_SESSION['customer_id']=="" && $_SESSION['guest']!="")
		{
			//$row['zone_id']=$_SESSION['guest']['shipping']['zone'];
			//$row['country_id']=$_SESSION['guest']['shipping']['country'];
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
