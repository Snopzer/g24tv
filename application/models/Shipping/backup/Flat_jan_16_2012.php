<?php


  class Model_Shipping_Flat {
    var $code, $title, $description, $icon, $enabled;
	public $db;
	//public $tr=$_SESSION['TObj'];
// class constructor
    function Model_Shipping_Flat() {
      //global $order;
	  //$order=$_SESSION['order'];

      $this->code = 'Flat';
      $this->title = MODULE_SHIPPING_FLAT_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_FLAT_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_FLAT_SORT_ORDER;
      $this->icon = '';
      $this->tax_class = MODULE_SHIPPING_FLAT_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_FLAT_STATUS == 'True') ? true : false);

      if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_FLAT_ZONE > 0) ) {
        $check_flag = false;
        /*$check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_SHIPPING_FLAT_ZONE . "' and zone_country_id = '" . $order->delivery['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->delivery['zone_id']) {
            $check_flag = true;
            break;
          }
        }*/

        if ($check_flag == false) {
          $this->enabled = false;
        }
      }
		$this->db = Zend_Db_Table::getDefaultAdapter();
		//$this->tr=$_SESSION['TObj'];
    }

// class methods
    function quote($method = '') {
      //global $order;
	 // $order=$_SESSION['order'];

      $this->quotes = array('id' => $this->code,
                            'module' => $_SESSION['OBJ']['tr']->translate('MODULE_SHIPPING_FLAT_TEXT_TITLE'),
                            'methods' => array(array('id' => $this->code,
                                                     'title' => $_SESSION['OBJ']['tr']->translate('MODULE_SHIPPING_FLAT_TEXT_WAY'),
                                                     'cost' => MODULE_SHIPPING_FLAT_COST)));

      if ($this->tax_class > 0) {
       // $this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $order->delivery['country']['id'], $order->delivery['zone_id']);
	    $this->quotes['tax'] = tep_get_tax_rate($this->tax_class, $_SESSION['shipping_country_id'], $_SESSION['shipping_zone_id']);
      }

      if (tep_not_null($this->icon)) $this->quotes['icon'] = tep_image($this->icon, $this->title);
	//print_r($this->quotes);
	//exit;
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

		$this->db->insert('r_configuration',array('configuration_title'=>'Enable Flat Shipping','configuration_key'=>'MODULE_SHIPPING_FLAT_STATUS','configuration_value'=>'True','configuration_description'=>'Do you want to offer flat rate shipping?','configuration_group_id'=>'6','sort_order'=>'0','set_function'=>'tep_cfg_select_option(array(\'True\', \'False\'), ','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Shipping Cost','configuration_key'=>'MODULE_SHIPPING_FLAT_COST','configuration_value'=>'5.00','configuration_description'=>'The shipping cost for all orders using this shipping method.','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Tax Class','configuration_key'=>'MODULE_SHIPPING_FLAT_TAX_CLASS','configuration_value'=>'0','configuration_description'=>'Use the following tax class on the shipping fee.','configuration_group_id'=>'6','sort_order'=>'0','use_function'=>'tep_get_tax_class_title','set_function'=>'tep_cfg_pull_down_tax_classes(','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Shipping Zone','configuration_key'=>'MODULE_SHIPPING_FLAT_ZONE','configuration_value'=>'0','configuration_description'=>'If a zone is selected, only enable this shipping method for that zone.','configuration_group_id'=>'6','sort_order'=>'0','use_function'=>'tep_get_zone_class_title','set_function'=>'tep_cfg_pull_down_zone_classes(','date_added'=>new Zend_Db_Expr('NOW()')));

		$this->db->insert('r_configuration',array('configuration_title'=>'Sort Order','configuration_key'=>'MODULE_SHIPPING_FLAT_SORT_ORDER','configuration_value'=>'0','configuration_description'=>'Sort order of display.','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));
   }

    function remove() {
		$this->db->delete('r_configuration',"configuration_key in ('" . implode("', '", $this->keys()) . "')");
	   //tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_SHIPPING_FLAT_STATUS', 'MODULE_SHIPPING_FLAT_COST', 'MODULE_SHIPPING_FLAT_TAX_CLASS', 'MODULE_SHIPPING_FLAT_ZONE', 'MODULE_SHIPPING_FLAT_SORT_ORDER');
    }
  }
?>
