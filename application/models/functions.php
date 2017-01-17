<?php
function tep_cfg_pull_down_order_statuses($order_status_id, $key = '') {
    global $languages_id;

    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');
	$act=new Model_Adminaction();
    $statuses_array=$act->moduleorderstatus();
	/*$statuses_array = array(array('id' => '0', 'text' => TEXT_DEFAULT));
    $statuses_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "' order by orders_status_name");
    while ($statuses = tep_db_fetch_array($statuses_query)) {
      $statuses_array[] = array('id' => $statuses['orders_status_id'],
                                'text' => $statuses['orders_status_name']);
    }*/

    return tep_draw_pull_down_menu($name, $statuses_array, $order_status_id);
  }

function tep_generate_category_path($id, $from = 'category', $categories_array = '', $index = 0) {
    global $languages_id;

    if (!is_array($categories_array)) $categories_array = array();

    if ($from == 'product') {
      $categories_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$id . "'");
      while ($categories = tep_db_fetch_array($categories_query)) {
        if ($categories['categories_id'] == '0') {
          $categories_array[$index][] = array('id' => '0', 'text' => 'Top');
        } else {
          $category_query = tep_db_query("select cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$categories['categories_id'] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
          $category = tep_db_fetch_array($category_query);
          $categories_array[$index][] = array('id' => $categories['categories_id'], 'text' => $category['categories_name']);
          if ( (tep_not_null($category['parent_id'])) && ($category['parent_id'] != '0') ) $categories_array = tep_generate_category_path($category['parent_id'], 'category', $categories_array, $index);
          $categories_array[$index] = array_reverse($categories_array[$index]);
        }
        $index++;
      }
    } elseif ($from == 'category') {
      $category_query = tep_db_query("select cd.categories_name, c.parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
      $category = tep_db_fetch_array($category_query);
      $categories_array[$index][] = array('id' => $id, 'text' => $category['categories_name']);
      if ( (tep_not_null($category['parent_id'])) && ($category['parent_id'] != '0') ) $categories_array = tep_generate_category_path($category['parent_id'], 'category', $categories_array, $index);
    }

    return $categories_array;
  }

  function tep_cfg_pull_down_country_list($field,$country_id) {
	  //echo $field;
	//      function tep_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {

    return tep_draw_pull_down_menu($field,tep_get_countries(), $country_id,'onchange="ajax_call(this.value)"');
  }

 function tep_output_generated_category_path($id, $from = 'category') { //function for displaying category at top
//$id is id of sub category like $id=17
    $calculated_category_path_string = '';
    $calculated_category_path = tep_generate_category_path($id, $from);
      for ($i=0, $n=sizeof($calculated_category_path); $i<$n; $i++) {
      for ($j=0, $k=sizeof($calculated_category_path[$i]); $j<$k; $j++) {
        $calculated_category_path_string .= $calculated_category_path[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
      }
      $calculated_category_path_string = substr($calculated_category_path_string, 0, -16) . '<br />';
    }

    $calculated_category_path_string = substr($calculated_category_path_string, 0, -6);
     if (strlen($calculated_category_path_string) < 1) $calculated_category_path_string = 'Top'; //top will be displayed if no category and product added at top
    return $calculated_category_path_string;
  }



function  tep_cfg_select_option($select_array, $key_value, $key = '')
 {
    $string = '';
    for ($i=0, $n=sizeof($select_array); $i<$n; $i++) {
      $name = ((tep_not_null($key)) ? 'configuration[' . $key . ']' : 'configuration_value');

      $string .= '&nbsp;<input type="radio" name="' . $name . '"  value="' . $select_array[$i] . '"';

      if ($key_value == $select_array[$i]) $string .= ' checked="checked"';

      $string .= ' /> ' . $select_array[$i];
    }

    echo  $string;
  }
function tep_not_null($value) {
		if (is_array($value)) {
		  if (sizeof($value) > 0) {
			return true;
		  } else {
			return false;
		  }
		} else {
		  if (($value != '') && (strtolower($value) != 'null') && (strlen(trim($value)) > 0)) {
			return true;
		  } else {
			return false;
		  }
		}
	  }
  /*function tep_not_null($value)
	 {
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
  }*/

  function tep_cfg_pull_down_tax_classes($tax_class_id, $key = '') {
    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

	$act=new Model_Adminaction();
	$zone_class_array=$act->modulegeozones();
    //$tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
	$act=new Model_Adminaction();
	$tax_class_array=$act->moduletaxclass();
	//print_r($tax_class_array);
	//exit;
    /*$tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
    while ($tax_class = tep_db_fetch_array($tax_class_query)) {
      $tax_class_array[] = array('id' => $tax_class['tax_class_id'],
                                 'text' => $tax_class['tax_class_title']);
    }*/

    return tep_draw_pull_down_menu($name, $tax_class_array, $tax_class_id);
  }

   function tep_cfg_pull_down_zone_classes($zone_class_id, $key = '')
	{

    $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

    //$zone_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
	$act=new Model_Adminaction();
	$zone_class_array=$act->modulegeozones();
	//print_r($zone_class_array);
	//exit;
    /*$zone_class_query = tep_db_query("select geo_zone_id, geo_zone_name from " . TABLE_GEO_ZONES . " order by geo_zone_name");
    while ($zone_class = tep_db_fetch_array($zone_class_query)) {
      $zone_class_array[] = array('id' => $zone_class['geo_zone_id'],
                                  'text' => $zone_class['geo_zone_name']);
    }*/

    return tep_draw_pull_down_menu($name, $zone_class_array, $zone_class_id);
  }
	function tep_parse_input_field_data($data, $parse)
	{
	return strtr(trim($data), $parse);
	}

    function tep_output_string($string, $translate = false, $protected = false) {
    if ($protected == true) {
      return htmlspecialchars($string);
    } else {
      if ($translate == false) {
        return tep_parse_input_field_data($string, array('"' => '&quot;'));
      } else {
        return tep_parse_input_field_data($string, $translate);
      }
    }
  }

    function tep_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $field = '<select name="' . tep_output_string($name) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' class="input-medium" >';

    if (empty($default) && ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) ) {
      if (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) {
        $default = stripslashes($HTTP_GET_VARS[$name]);
      } elseif (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) {
        $default = stripslashes($HTTP_POST_VARS[$name]);
      }
    }

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . tep_output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    echo  $field;
  }

  function tep_get_countries($default = '') {
    $countries_array = array();
    if ($default) {
      $countries_array[] = array('id' => '',
                                 'text' => $default);
    }
    $act_exp=new Model_Adminextaction();
    $c=$act_exp->db->fetchAll("select countries_id, countries_name from r_countries order by countries_name");

    foreach ($c as $countries)
    {
    	$countries_array[] = array('id' => $countries['countries_id'],'text' => $countries['countries_name']);

    }
    return $countries_array;
  }
 function tep_cfg_pull_down_zone_list($field,$zone_id) {
    return tep_draw_pull_down_menu($field, tep_get_country_zones(STORE_COUNTRY), $zone_id,'id="zone_id"');
  }

  function tep_cfg_pull_down_timezone_list($field,$zone_id) {
	  $array_time_zone=array(array("id"=>"Africa/Abidjan","text"=>"Abidjan"),
array("id"=>"Africa/Accra","text"=>"Accra"),
array("id"=>"Africa/Addis_Ababa","text"=>"Addis Ababa"),
array("id"=>"Africa/Algiers","text"=>"Algiers"),
array("id"=>"Africa/Asmara","text"=>"Asmara"),
array("id"=>"Africa/Bamako","text"=>"Bamako"),
array("id"=>"Africa/Bangui","text"=>"Bangui"),
array("id"=>"Africa/Banjul","text"=>"Banjul"),
array("id"=>"Africa/Bissau","text"=>"Bissau"),
array("id"=>"Africa/Blantyre","text"=>"Blantyre"),
array("id"=>"Africa/Brazzaville","text"=>"Brazzaville"),
array("id"=>"Africa/Bujumbura","text"=>"Bujumbura"),
array("id"=>"Africa/Cairo","text"=>"Cairo"),
array("id"=>"Africa/Casablanca","text"=>"Casablanca"),
array("id"=>"Africa/Ceuta","text"=>"Ceuta"),
array("id"=>"Africa/Conakry","text"=>"Conakry"),
array("id"=>"Africa/Dakar","text"=>"Dakar"),
array("id"=>"Africa/Dar_es_Salaam","text"=>"Dar es Salaam"),
array("id"=>"Africa/Djibouti","text"=>"Djibouti"),
array("id"=>"Africa/Douala","text"=>"Douala"),
array("id"=>"Africa/El_Aaiun","text"=>"El Aaiun"),
array("id"=>"Africa/Freetown","text"=>"Freetown"),
array("id"=>"Africa/Gaborone","text"=>"Gaborone"),
array("id"=>"Africa/Harare","text"=>"Harare"),
array("id"=>"Africa/Johannesburg","text"=>"Johannesburg"),
array("id"=>"Africa/Juba","text"=>"Juba"),
array("id"=>"Africa/Kampala","text"=>"Kampala"),
array("id"=>"Africa/Khartoum","text"=>"Khartoum"),
array("id"=>"Africa/Kigali","text"=>"Kigali"),
array("id"=>"Africa/Kinshasa","text"=>"Kinshasa"),
array("id"=>"Africa/Lagos","text"=>"Lagos"),
array("id"=>"Africa/Libreville","text"=>"Libreville"),
array("id"=>"Africa/Lome","text"=>"Lome"),
array("id"=>"Africa/Luanda","text"=>"Luanda"),
array("id"=>"Africa/Lubumbashi","text"=>"Lubumbashi"),
array("id"=>"Africa/Lusaka","text"=>"Lusaka"),
array("id"=>"Africa/Malabo","text"=>"Malabo"),
array("id"=>"Africa/Maputo","text"=>"Maputo"),
array("id"=>"Africa/Maseru","text"=>"Maseru"),
array("id"=>"Africa/Mbabane","text"=>"Mbabane"),
array("id"=>"Africa/Mogadishu","text"=>"Mogadishu"),
array("id"=>"Africa/Monrovia","text"=>"Monrovia"),
array("id"=>"Africa/Nairobi","text"=>"Nairobi"),
array("id"=>"Africa/Ndjamena","text"=>"Ndjamena"),
array("id"=>"Africa/Niamey","text"=>"Niamey"),
array("id"=>"Africa/Nouakchott","text"=>"Nouakchott"),
array("id"=>"Africa/Ouagadougou","text"=>"Ouagadougou"),
array("id"=>"Africa/Porto-Novo","text"=>"Porto-Novo"),
array("id"=>"Africa/Sao_Tome","text"=>"Sao Tome"),
array("id"=>"Africa/Tripoli","text"=>"Tripoli"),
array("id"=>"Africa/Tunis","text"=>"Tunis"),
array("id"=>"Africa/Windhoek","text"=>"Windhoek"),
array("id"=>"America/Adak","text"=>"Adak"),
array("id"=>"America/Anchorage","text"=>"Anchorage"),
array("id"=>"America/Anguilla","text"=>"Anguilla"),
array("id"=>"America/Antigua","text"=>"Antigua"),
array("id"=>"America/Araguaina","text"=>"Araguaina"),
array("id"=>"America/Argentina/Buenos_Aires","text"=>"Argentina - Buenos Aires"),
array("id"=>"America/Argentina/Catamarca","text"=>"Argentina - Catamarca"),
array("id"=>"America/Argentina/Cordoba","text"=>"Argentina - Cordoba"),
array("id"=>"America/Argentina/Jujuy","text"=>"Argentina - Jujuy"),
array("id"=>"America/Argentina/La_Rioja","text"=>"Argentina - La Rioja"),
array("id"=>"America/Argentina/Mendoza","text"=>"Argentina - Mendoza"),
array("id"=>"America/Argentina/Rio_Gallegos","text"=>"Argentina - Rio Gallegos"),
array("id"=>"America/Argentina/Salta","text"=>"Argentina - Salta"),
array("id"=>"America/Argentina/San_Juan","text"=>"Argentina - San Juan"),
array("id"=>"America/Argentina/San_Luis","text"=>"Argentina - San Luis"),
array("id"=>"America/Argentina/Tucuman","text"=>"Argentina - Tucuman"),
array("id"=>"America/Argentina/Ushuaia","text"=>"Argentina - Ushuaia"),
array("id"=>"America/Aruba","text"=>"Aruba"),
array("id"=>"America/Asuncion","text"=>"Asuncion"),
array("id"=>"America/Atikokan","text"=>"Atikokan"),
array("id"=>"America/Bahia","text"=>"Bahia"),
array("id"=>"America/Bahia_Banderas","text"=>"Bahia Banderas"),
array("id"=>"America/Barbados","text"=>"Barbados"),
array("id"=>"America/Belem","text"=>"Belem"),
array("id"=>"America/Belize","text"=>"Belize"),
array("id"=>"America/Blanc-Sablon","text"=>"Blanc-Sablon"),
array("id"=>"America/Boa_Vista","text"=>"Boa Vista"),
array("id"=>"America/Bogota","text"=>"Bogota"),
array("id"=>"America/Boise","text"=>"Boise"),
array("id"=>"America/Cambridge_Bay","text"=>"Cambridge Bay"),
array("id"=>"America/Campo_Grande","text"=>"Campo Grande"),
array("id"=>"America/Cancun","text"=>"Cancun"),
array("id"=>"America/Caracas","text"=>"Caracas"),
array("id"=>"America/Cayenne","text"=>"Cayenne"),
array("id"=>"America/Cayman","text"=>"Cayman"),
array("id"=>"America/Chicago","text"=>"Chicago"),
array("id"=>"America/Chihuahua","text"=>"Chihuahua"),
array("id"=>"America/Costa_Rica","text"=>"Costa Rica"),
array("id"=>"America/Creston","text"=>"Creston"),
array("id"=>"America/Cuiaba","text"=>"Cuiaba"),
array("id"=>"America/Curacao","text"=>"Curacao"),
array("id"=>"America/Danmarkshavn","text"=>"Danmarkshavn"),
array("id"=>"America/Dawson","text"=>"Dawson"),
array("id"=>"America/Dawson_Creek","text"=>"Dawson Creek"),
array("id"=>"America/Denver","text"=>"Denver"),
array("id"=>"America/Detroit","text"=>"Detroit"),
array("id"=>"America/Dominica","text"=>"Dominica"),
array("id"=>"America/Edmonton","text"=>"Edmonton"),
array("id"=>"America/Eirunepe","text"=>"Eirunepe"),
array("id"=>"America/El_Salvador","text"=>"El Salvador"),
array("id"=>"America/Fortaleza","text"=>"Fortaleza"),
array("id"=>"America/Glace_Bay","text"=>"Glace Bay"),
array("id"=>"America/Godthab","text"=>"Godthab"),
array("id"=>"America/Goose_Bay","text"=>"Goose Bay"),
array("id"=>"America/Grand_Turk","text"=>"Grand Turk"),
array("id"=>"America/Grenada","text"=>"Grenada"),
array("id"=>"America/Guadeloupe","text"=>"Guadeloupe"),
array("id"=>"America/Guatemala","text"=>"Guatemala"),
array("id"=>"America/Guayaquil","text"=>"Guayaquil"),
array("id"=>"America/Guyana","text"=>"Guyana"),
array("id"=>"America/Halifax","text"=>"Halifax"),
array("id"=>"America/Havana","text"=>"Havana"),
array("id"=>"America/Hermosillo","text"=>"Hermosillo"),
array("id"=>"America/Indiana/Indianapolis","text"=>"Indiana - Indianapolis"),
array("id"=>"America/Indiana/Knox","text"=>"Indiana - Knox"),
array("id"=>"America/Indiana/Marengo","text"=>"Indiana - Marengo"),
array("id"=>"America/Indiana/Petersburg","text"=>"Indiana - Petersburg"),
array("id"=>"America/Indiana/Tell_City","text"=>"Indiana - Tell City"),
array("id"=>"America/Indiana/Vevay","text"=>"Indiana - Vevay"),
array("id"=>"America/Indiana/Vincennes","text"=>"Indiana - Vincennes"),
array("id"=>"America/Indiana/Winamac","text"=>"Indiana - Winamac"),
array("id"=>"America/Inuvik","text"=>"Inuvik"),
array("id"=>"America/Iqaluit","text"=>"Iqaluit"),
array("id"=>"America/Jamaica","text"=>"Jamaica"),
array("id"=>"America/Juneau","text"=>"Juneau"),
array("id"=>"America/Kentucky/Louisville","text"=>"Kentucky - Louisville"),
array("id"=>"America/Kentucky/Monticello","text"=>"Kentucky - Monticello"),
array("id"=>"America/Kralendijk","text"=>"Kralendijk"),
array("id"=>"America/La_Paz","text"=>"La Paz"),
array("id"=>"America/Lima","text"=>"Lima"),
array("id"=>"America/Los_Angeles","text"=>"Los Angeles"),
array("id"=>"America/Lower_Princes","text"=>"Lower Princes"),
array("id"=>"America/Maceio","text"=>"Maceio"),
array("id"=>"America/Managua","text"=>"Managua"),
array("id"=>"America/Manaus","text"=>"Manaus"),
array("id"=>"America/Marigot","text"=>"Marigot"),
array("id"=>"America/Martinique","text"=>"Martinique"),
array("id"=>"America/Matamoros","text"=>"Matamoros"),
array("id"=>"America/Mazatlan","text"=>"Mazatlan"),
array("id"=>"America/Menominee","text"=>"Menominee"),
array("id"=>"America/Merida","text"=>"Merida"),
array("id"=>"America/Metlakatla","text"=>"Metlakatla"),
array("id"=>"America/Mexico_City","text"=>"Mexico City"),
array("id"=>"America/Miquelon","text"=>"Miquelon"),
array("id"=>"America/Moncton","text"=>"Moncton"),
array("id"=>"America/Monterrey","text"=>"Monterrey"),
array("id"=>"America/Montevideo","text"=>"Montevideo"),
array("id"=>"America/Montreal","text"=>"Montreal"),
array("id"=>"America/Montserrat","text"=>"Montserrat"),
array("id"=>"America/Nassau","text"=>"Nassau"),
array("id"=>"America/New_York","text"=>"New York"),
array("id"=>"America/Nipigon","text"=>"Nipigon"),
array("id"=>"America/Nome","text"=>"Nome"),
array("id"=>"America/Noronha","text"=>"Noronha"),
array("id"=>"America/North_Dakota/Beulah","text"=>"North Dakota - Beulah"),
array("id"=>"America/North_Dakota/Center","text"=>"North Dakota - Center"),
array("id"=>"America/North_Dakota/New_Salem","text"=>"North Dakota - New Salem"),
array("id"=>"America/Ojinaga","text"=>"Ojinaga"),
array("id"=>"America/Panama","text"=>"Panama"),
array("id"=>"America/Pangnirtung","text"=>"Pangnirtung"),
array("id"=>"America/Paramaribo","text"=>"Paramaribo"),
array("id"=>"America/Phoenix","text"=>"Phoenix"),
array("id"=>"America/Port-au-Prince","text"=>"Port-au-Prince"),
array("id"=>"America/Port_of_Spain","text"=>"Port of Spain"),
array("id"=>"America/Porto_Velho","text"=>"Porto Velho"),
array("id"=>"America/Puerto_Rico","text"=>"Puerto Rico"),
array("id"=>"America/Rainy_River","text"=>"Rainy River"),
array("id"=>"America/Rankin_Inlet","text"=>"Rankin Inlet"),
array("id"=>"America/Recife","text"=>"Recife"),
array("id"=>"America/Regina","text"=>"Regina"),
array("id"=>"America/Resolute","text"=>"Resolute"),
array("id"=>"America/Rio_Branco","text"=>"Rio Branco"),
array("id"=>"America/Santa_Isabel","text"=>"Santa Isabel"),
array("id"=>"America/Santarem","text"=>"Santarem"),
array("id"=>"America/Santiago","text"=>"Santiago"),
array("id"=>"America/Santo_Domingo","text"=>"Santo Domingo"),
array("id"=>"America/Sao_Paulo","text"=>"Sao Paulo"),
array("id"=>"America/Scoresbysund","text"=>"Scoresbysund"),
array("id"=>"America/Shiprock","text"=>"Shiprock"),
array("id"=>"America/Sitka","text"=>"Sitka"),
array("id"=>"America/St_Barthelemy","text"=>"St Barthelemy"),
array("id"=>"America/St_Johns","text"=>"St Johns"),
array("id"=>"America/St_Kitts","text"=>"St Kitts"),
array("id"=>"America/St_Lucia","text"=>"St Lucia"),
array("id"=>"America/St_Thomas","text"=>"St Thomas"),
array("id"=>"America/St_Vincent","text"=>"St Vincent"),
array("id"=>"America/Swift_Current","text"=>"Swift Current"),
array("id"=>"America/Tegucigalpa","text"=>"Tegucigalpa"),
array("id"=>"America/Thule","text"=>"Thule"),
array("id"=>"America/Thunder_Bay","text"=>"Thunder Bay"),
array("id"=>"America/Tijuana","text"=>"Tijuana"),
array("id"=>"America/Toronto","text"=>"Toronto"),
array("id"=>"America/Tortola","text"=>"Tortola"),
array("id"=>"America/Vancouver","text"=>"Vancouver"),
array("id"=>"America/Whitehorse","text"=>"Whitehorse"),
array("id"=>"America/Winnipeg","text"=>"Winnipeg"),
array("id"=>"America/Yakutat","text"=>"Yakutat"),
array("id"=>"America/Yellowknife","text"=>"Yellowknife"),
array("id"=>"Antarctica/Casey","text"=>"Casey"),
array("id"=>"Antarctica/Davis","text"=>"Davis"),
array("id"=>"Antarctica/DumontDUrville","text"=>"DumontDUrville"),
array("id"=>"Antarctica/Macquarie","text"=>"Macquarie"),
array("id"=>"Antarctica/Mawson","text"=>"Mawson"),
array("id"=>"Antarctica/McMurdo","text"=>"McMurdo"),
array("id"=>"Antarctica/Palmer","text"=>"Palmer"),
array("id"=>"Antarctica/Rothera","text"=>"Rothera"),
array("id"=>"Antarctica/South_Pole","text"=>"South Pole"),
array("id"=>"Antarctica/Syowa","text"=>"Syowa"),
array("id"=>"Antarctica/Vostok","text"=>"Vostok"),
array("id"=>"Arctic/Longyearbyen","text"=>"Longyearbyen"),
array("id"=>"Asia/Aden","text"=>"Aden"),
array("id"=>"Asia/Almaty","text"=>"Almaty"),
array("id"=>"Asia/Amman","text"=>"Amman"),
array("id"=>"Asia/Anadyr","text"=>"Anadyr"),
array("id"=>"Asia/Aqtau","text"=>"Aqtau"),
array("id"=>"Asia/Aqtobe","text"=>"Aqtobe"),
array("id"=>"Asia/Ashgabat","text"=>"Ashgabat"),
array("id"=>"Asia/Baghdad","text"=>"Baghdad"),
array("id"=>"Asia/Bahrain","text"=>"Bahrain"),
array("id"=>"Asia/Baku","text"=>"Baku"),
array("id"=>"Asia/Bangkok","text"=>"Bangkok"),
array("id"=>"Asia/Beirut","text"=>"Beirut"),
array("id"=>"Asia/Bishkek","text"=>"Bishkek"),
array("id"=>"Asia/Brunei","text"=>"Brunei"),
array("id"=>"Asia/Choibalsan","text"=>"Choibalsan"),
array("id"=>"Asia/Chongqing","text"=>"Chongqing"),
array("id"=>"Asia/Colombo","text"=>"Colombo"),
array("id"=>"Asia/Damascus","text"=>"Damascus"),
array("id"=>"Asia/Dhaka","text"=>"Dhaka"),
array("id"=>"Asia/Dili","text"=>"Dili"),
array("id"=>"Asia/Dubai","text"=>"Dubai"),
array("id"=>"Asia/Dushanbe","text"=>"Dushanbe"),
array("id"=>"Asia/Gaza","text"=>"Gaza"),
array("id"=>"Asia/Harbin","text"=>"Harbin"),
array("id"=>"Asia/Hebron","text"=>"Hebron"),
array("id"=>"Asia/Ho_Chi_Minh","text"=>"Ho Chi Minh"),
array("id"=>"Asia/Hong_Kong","text"=>"Hong Kong"),
array("id"=>"Asia/Hovd","text"=>"Hovd"),
array("id"=>"Asia/Irkutsk","text"=>"Irkutsk"),
array("id"=>"Asia/Jakarta","text"=>"Jakarta"),
array("id"=>"Asia/Jayapura","text"=>"Jayapura"),
array("id"=>"Asia/Jerusalem","text"=>"Jerusalem"),
array("id"=>"Asia/Kabul","text"=>"Kabul"),
array("id"=>"Asia/Kamchatka","text"=>"Kamchatka"),
array("id"=>"Asia/Karachi","text"=>"Karachi"),
array("id"=>"Asia/Kashgar","text"=>"Kashgar"),
array("id"=>"Asia/Kathmandu","text"=>"Kathmandu"),
array("id"=>"Asia/Kolkata","text"=>"Kolkata"),
array("id"=>"Asia/Krasnoyarsk","text"=>"Krasnoyarsk"),
array("id"=>"Asia/Kuala_Lumpur","text"=>"Kuala Lumpur"),
array("id"=>"Asia/Kuching","text"=>"Kuching"),
array("id"=>"Asia/Kuwait","text"=>"Kuwait"),
array("id"=>"Asia/Macau","text"=>"Macau"),
array("id"=>"Asia/Magadan","text"=>"Magadan"),
array("id"=>"Asia/Makassar","text"=>"Makassar"),
array("id"=>"Asia/Manila","text"=>"Manila"),
array("id"=>"Asia/Muscat","text"=>"Muscat"),
array("id"=>"Asia/Nicosia","text"=>"Nicosia"),
array("id"=>"Asia/Novokuznetsk","text"=>"Novokuznetsk"),
array("id"=>"Asia/Novosibirsk","text"=>"Novosibirsk"),
array("id"=>"Asia/Omsk","text"=>"Omsk"),
array("id"=>"Asia/Oral","text"=>"Oral"),
array("id"=>"Asia/Phnom_Penh","text"=>"Phnom Penh"),
array("id"=>"Asia/Pontianak","text"=>"Pontianak"),
array("id"=>"Asia/Pyongyang","text"=>"Pyongyang"),
array("id"=>"Asia/Qatar","text"=>"Qatar"),
array("id"=>"Asia/Qyzylorda","text"=>"Qyzylorda"),
array("id"=>"Asia/Rangoon","text"=>"Rangoon"),
array("id"=>"Asia/Riyadh","text"=>"Riyadh"),
array("id"=>"Asia/Sakhalin","text"=>"Sakhalin"),
array("id"=>"Asia/Samarkand","text"=>"Samarkand"),
array("id"=>"Asia/Seoul","text"=>"Seoul"),
array("id"=>"Asia/Shanghai","text"=>"Shanghai"),
array("id"=>"Asia/Singapore","text"=>"Singapore"),
array("id"=>"Asia/Taipei","text"=>"Taipei"),
array("id"=>"Asia/Tashkent","text"=>"Tashkent"),
array("id"=>"Asia/Tbilisi","text"=>"Tbilisi"),
array("id"=>"Asia/Tehran","text"=>"Tehran"),
array("id"=>"Asia/Thimphu","text"=>"Thimphu"),
array("id"=>"Asia/Tokyo","text"=>"Tokyo"),
array("id"=>"Asia/Ulaanbaatar","text"=>"Ulaanbaatar"),
array("id"=>"Asia/Urumqi","text"=>"Urumqi"),
array("id"=>"Asia/Vientiane","text"=>"Vientiane"),
array("id"=>"Asia/Vladivostok","text"=>"Vladivostok"),
array("id"=>"Asia/Yakutsk","text"=>"Yakutsk"),
array("id"=>"Asia/Yekaterinburg","text"=>"Yekaterinburg"),
array("id"=>"Asia/Yerevan","text"=>"Yerevan"),
array("id"=>"Atlantic/Azores","text"=>"Azores"),
array("id"=>"Atlantic/Bermuda","text"=>"Bermuda"),
array("id"=>"Atlantic/Canary","text"=>"Canary"),
array("id"=>"Atlantic/Cape_Verde","text"=>"Cape Verde"),
array("id"=>"Atlantic/Faroe","text"=>"Faroe"),
array("id"=>"Atlantic/Madeira","text"=>"Madeira"),
array("id"=>"Atlantic/Reykjavik","text"=>"Reykjavik"),
array("id"=>"Atlantic/South_Georgia","text"=>"South Georgia"),
array("id"=>"Atlantic/Stanley","text"=>"Stanley"),
array("id"=>"Atlantic/St_Helena","text"=>"St Helena"),
array("id"=>"Australia/Adelaide","text"=>"Adelaide"),
array("id"=>"Australia/Brisbane","text"=>"Brisbane"),
array("id"=>"Australia/Broken_Hill","text"=>"Broken Hill"),
array("id"=>"Australia/Currie","text"=>"Currie"),
array("id"=>"Australia/Darwin","text"=>"Darwin"),
array("id"=>"Australia/Eucla","text"=>"Eucla"),
array("id"=>"Australia/Hobart","text"=>"Hobart"),
array("id"=>"Australia/Lindeman","text"=>"Lindeman"),
array("id"=>"Australia/Lord_Howe","text"=>"Lord Howe"),
array("id"=>"Australia/Melbourne","text"=>"Melbourne"),
array("id"=>"Australia/Perth","text"=>"Perth"),
array("id"=>"Australia/Sydney","text"=>"Sydney"),
array("id"=>"Europe/Amsterdam","text"=>"Amsterdam"),
array("id"=>"Europe/Andorra","text"=>"Andorra"),
array("id"=>"Europe/Athens","text"=>"Athens"),
array("id"=>"Europe/Belgrade","text"=>"Belgrade"),
array("id"=>"Europe/Berlin","text"=>"Berlin"),
array("id"=>"Europe/Bratislava","text"=>"Bratislava"),
array("id"=>"Europe/Brussels","text"=>"Brussels"),
array("id"=>"Europe/Bucharest","text"=>"Bucharest"),
array("id"=>"Europe/Budapest","text"=>"Budapest"),
array("id"=>"Europe/Chisinau","text"=>"Chisinau"),
array("id"=>"Europe/Copenhagen","text"=>"Copenhagen"),
array("id"=>"Europe/Dublin","text"=>"Dublin"),
array("id"=>"Europe/Gibraltar","text"=>"Gibraltar"),
array("id"=>"Europe/Guernsey","text"=>"Guernsey"),
array("id"=>"Europe/Helsinki","text"=>"Helsinki"),
array("id"=>"Europe/Isle_of_Man","text"=>"Isle of Man"),
array("id"=>"Europe/Istanbul","text"=>"Istanbul"),
array("id"=>"Europe/Jersey","text"=>"Jersey"),
array("id"=>"Europe/Kaliningrad","text"=>"Kaliningrad"),
array("id"=>"Europe/Kiev","text"=>"Kiev"),
array("id"=>"Europe/Lisbon","text"=>"Lisbon"),
array("id"=>"Europe/Ljubljana","text"=>"Ljubljana"),
array("id"=>"Europe/London","text"=>"London"),
array("id"=>"Europe/Luxembourg","text"=>"Luxembourg"),
array("id"=>"Europe/Madrid","text"=>"Madrid"),
array("id"=>"Europe/Malta","text"=>"Malta"),
array("id"=>"Europe/Mariehamn","text"=>"Mariehamn"),
array("id"=>"Europe/Minsk","text"=>"Minsk"),
array("id"=>"Europe/Monaco","text"=>"Monaco"),
array("id"=>"Europe/Moscow","text"=>"Moscow"),
array("id"=>"Europe/Oslo","text"=>"Oslo"),
array("id"=>"Europe/Paris","text"=>"Paris"),
array("id"=>"Europe/Podgorica","text"=>"Podgorica"),
array("id"=>"Europe/Prague","text"=>"Prague"),
array("id"=>"Europe/Riga","text"=>"Riga"),
array("id"=>"Europe/Rome","text"=>"Rome"),
array("id"=>"Europe/Samara","text"=>"Samara"),
array("id"=>"Europe/San_Marino","text"=>"San Marino"),
array("id"=>"Europe/Sarajevo","text"=>"Sarajevo"),
array("id"=>"Europe/Simferopol","text"=>"Simferopol"),
array("id"=>"Europe/Skopje","text"=>"Skopje"),
array("id"=>"Europe/Sofia","text"=>"Sofia"),
array("id"=>"Europe/Stockholm","text"=>"Stockholm"),
array("id"=>"Europe/Tallinn","text"=>"Tallinn"),
array("id"=>"Europe/Tirane","text"=>"Tirane"),
array("id"=>"Europe/Uzhgorod","text"=>"Uzhgorod"),
array("id"=>"Europe/Vaduz","text"=>"Vaduz"),
array("id"=>"Europe/Vatican","text"=>"Vatican"),
array("id"=>"Europe/Vienna","text"=>"Vienna"),
array("id"=>"Europe/Vilnius","text"=>"Vilnius"),
array("id"=>"Europe/Volgograd","text"=>"Volgograd"),
array("id"=>"Europe/Warsaw","text"=>"Warsaw"),
array("id"=>"Europe/Zagreb","text"=>"Zagreb"),
array("id"=>"Europe/Zaporozhye","text"=>"Zaporozhye"),
array("id"=>"Europe/Zurich","text"=>"Zurich"),
array("id"=>"Indian/Antananarivo","text"=>"Antananarivo"),
array("id"=>"Indian/Chagos","text"=>"Chagos"),
array("id"=>"Indian/Christmas","text"=>"Christmas"),
array("id"=>"Indian/Cocos","text"=>"Cocos"),
array("id"=>"Indian/Comoro","text"=>"Comoro"),
array("id"=>"Indian/Kerguelen","text"=>"Kerguelen"),
array("id"=>"Indian/Mahe","text"=>"Mahe"),
array("id"=>"Indian/Maldives","text"=>"Maldives"),
array("id"=>"Indian/Mauritius","text"=>"Mauritius"),
array("id"=>"Indian/Mayotte","text"=>"Mayotte"),
array("id"=>"Indian/Reunion","text"=>"Reunion"),
array("id"=>"Pacific/Apia","text"=>"Apia"),
array("id"=>"Pacific/Auckland","text"=>"Auckland"),
array("id"=>"Pacific/Chatham","text"=>"Chatham"),
array("id"=>"Pacific/Chuuk","text"=>"Chuuk"),
array("id"=>"Pacific/Easter","text"=>"Easter"),
array("id"=>"Pacific/Efate","text"=>"Efate"),
array("id"=>"Pacific/Enderbury","text"=>"Enderbury"),
array("id"=>"Pacific/Fakaofo","text"=>"Fakaofo"),
array("id"=>"Pacific/Fiji","text"=>"Fiji"),
array("id"=>"Pacific/Funafuti","text"=>"Funafuti"),
array("id"=>"Pacific/Galapagos","text"=>"Galapagos"),
array("id"=>"Pacific/Gambier","text"=>"Gambier"),
array("id"=>"Pacific/Guadalcanal","text"=>"Guadalcanal"),
array("id"=>"Pacific/Guam","text"=>"Guam"),
array("id"=>"Pacific/Honolulu","text"=>"Honolulu"),
array("id"=>"Pacific/Johnston","text"=>"Johnston"),
array("id"=>"Pacific/Kiritimati","text"=>"Kiritimati"),
array("id"=>"Pacific/Kosrae","text"=>"Kosrae"),
array("id"=>"Pacific/Kwajalein","text"=>"Kwajalein"),
array("id"=>"Pacific/Majuro","text"=>"Majuro"),
array("id"=>"Pacific/Marquesas","text"=>"Marquesas"),
array("id"=>"Pacific/Midway","text"=>"Midway"),
array("id"=>"Pacific/Nauru","text"=>"Nauru"),
array("id"=>"Pacific/Niue","text"=>"Niue"),
array("id"=>"Pacific/Norfolk","text"=>"Norfolk"),
array("id"=>"Pacific/Noumea","text"=>"Noumea"),
array("id"=>"Pacific/Pago_Pago","text"=>"Pago Pago"),
array("id"=>"Pacific/Palau","text"=>"Palau"),
array("id"=>"Pacific/Pitcairn","text"=>"Pitcairn"),
array("id"=>"Pacific/Pohnpei","text"=>"Pohnpei"),
array("id"=>"Pacific/Port_Moresby","text"=>"Port Moresby"),
array("id"=>"Pacific/Rarotonga","text"=>"Rarotonga"),
array("id"=>"Pacific/Saipan","text"=>"Saipan"),
array("id"=>"Pacific/Tahiti","text"=>"Tahiti"),
array("id"=>"Pacific/Tarawa","text"=>"Tarawa"),
array("id"=>"Pacific/Tongatapu","text"=>"Tongatapu"),
array("id"=>"Pacific/Wake","text"=>"Wake"),
array("id"=>"Pacific/Wallis","text"=>"Wallis"));
    return tep_draw_pull_down_menu($field, $array_time_zone, $zone_id,'id="zone_id"');
  }

  function tep_cfg_pull_down_customer_group_list($field,$zone_id) {
    return tep_draw_pull_down_menu($field, tep_get_customer_group(), $zone_id);
  }

    function tep_cfg_pull_down_weight_class_list($field,$zone_id) {
    return tep_draw_pull_down_menu($field, tep_get_weight_class(), $zone_id);
  }

      function tep_cfg_pull_down_length_class_list($field,$zone_id) {
    return tep_draw_pull_down_menu($field, tep_get_length_class(), $zone_id);
  }

  function tep_cfg_pull_down_out_stock_status_list($field,$zone_id) {
    return tep_draw_pull_down_menu($field, tep_get_out_stock_status(), $zone_id);
  }

   function tep_get_out_stock_status($default = '') {
    $countries_array = array();
    if ($default) {
      $countries_array[] = array('id' => '',
                                 'text' => $default);
    }
    $act_exp=new Model_Adminextaction();
    $c=$act_exp->db->fetchAll("select stock_status_id, name from r_stock_status where language_id='1' order by name");

    foreach ($c as $countries)
    {
    	$countries_array[] = array('id' => $countries['stock_status_id'],'text' => $countries['name']);

    }
    return $countries_array;
  }

/*function browse($field,$value)
{

 echo "<input type='file' name='$field' id='$field' />".$value;

}*/

function browse($field,$prev_img,$value)
{
	echo '<input name="'.$field.'" id="'.$field.'"  type="file" class="input-medium" value="'.$value.'" /><a href="#" style="color:black;cursor:pointer;text-decoration:underline;" class="tt">'.$value.'<span class="tooltip"><img src="'.PATH_TO_UPLOADS."image/".$value.'"  /></span></a><input name="'.$prev_img.'" id="'.$prev_img.'" type="hidden" value="'.$value.'" />';
}


  function tep_get_customer_group($default = '') {
    $countries_array = array();
    if ($default) {
      $countries_array[] = array('id' => '',
                                 'text' => $default);
    }
    $act_exp=new Model_Adminextaction();
    $c=$act_exp->db->fetchAll("select customer_group_id, name from r_customer_group order by name");

    foreach ($c as $countries)
    {
    	$countries_array[] = array('id' => $countries['customer_group_id'],'text' => $countries['name']);

    }
    return $countries_array;
  }

  function tep_get_weight_class($default = '') {
    $countries_array = array();
    if ($default) {
      $countries_array[] = array('id' => '',
                                 'text' => $default);
    }
    $act_exp=new Model_Adminextaction();
    $c=$act_exp->db->fetchAll("select weight_class_id,unit, title from r_weight_class_description where language_id='1' order by title");

    foreach ($c as $countries)
    {
    	$countries_array[] = array('id' => $countries['unit'],'text' => $countries['title']);

    }
    return $countries_array;
  }

    function tep_get_length_class($default = '') {
    $countries_array = array();
    if ($default) {
      $countries_array[] = array('id' => '',
                                 'text' => $default);
    }
    $act_exp=new Model_Adminextaction();
    $c=$act_exp->db->fetchAll("select length_class_id,unit, title from r_length_class_description where language_id='1' order by title");

    foreach ($c as $countries)
    {
    	$countries_array[] = array('id' => $countries['unit'],'text' => $countries['title']);

    }
    return $countries_array;
  }

function tep_get_country_zones($country_id) {
    $zones_array = array();
        $act_exp=new Model_Adminextaction();
    $c=$act_exp->db->fetchAll("select zone_id, zone_name from r_zones where zone_country_id = '" . (int)$country_id . "' order by zone_name");
    foreach ($c as $zones)
    {
    $zones_array[] = array('id' => $zones['zone_id'],'text' => $zones['zone_name']);
    }
    return $zones_array;
  }

  function tep_cfg_textarea($field,$text) {
    return tep_draw_textarea_field($field, false, 35, 5, $text);
  }

   function tep_draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $field = '<textarea name="' . tep_output_string($name) . '" cols="' . tep_output_string($width) . '" rows="' . tep_output_string($height) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ( ($reinsert_value == true) && ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) ) {
      if (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) {
        $field .= tep_output_string_protected(stripslashes($HTTP_GET_VARS[$name]));
      } elseif (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) {
        $field .= tep_output_string_protected(stripslashes($HTTP_POST_VARS[$name]));
      }
    } elseif (tep_not_null($text)) {
      $field .= tep_output_string_protected($text);
    }

    $field .= '</textarea>';
echo $field;
//    return $field;
  }
    function tep_output_string_protected($string) {
    return tep_output_string($string, false, true);
  }






  function tep_cfg_pull_down_order_status_list($field,$zone_id) {
	    return tep_draw_pull_down_menu($field, tep_get_order_status(), $zone_id);
  }

function tep_get_order_status($default = '') {
    $countries_array = array();
    if ($default) {
      $countries_array[] = array('id' => '',
                                 'text' => $default);
    }
    $act_exp=new Model_Adminextaction();
    $c=$act_exp->db->fetchAll("select orders_status_id, orders_status_name from r_orders_status where language_id='1' order by orders_status_name");

    foreach ($c as $countries)
    {
    	$countries_array[] = array('id' => $countries['orders_status_id'],'text' => $countries['orders_status_name']);

    }
    return $countries_array;
  }








  	  function tep_cfg_pull_down_page_list($field,$zone_id) {
		  $array=array();
		  $array=tep_get_page();
		 $array[]=array("id"=>0,"text"=>"None");
		//print_r($array);
		  //exit;
   return tep_draw_pull_down_menu($field, $array, $zone_id);
  }
function tep_get_page($default = '') {
    $countries_array = array();
    if ($default) {
      $countries_array[] = array('id' => '',
                                 'text' => $default);
    }
    $act_exp=new Model_Adminextaction();
    $c=$act_exp->db->fetchAll("select page_id, title from r_cms_description where language_id='1' order by title");

    foreach ($c as $countries)
    {
    	$countries_array[] = array('id' => $countries['page_id'],'text' => $countries['title']);

    }
    return $countries_array;
  }

  //////////////added on mar 27 2012
  /*start shipping functions*/
  function tep_create_random_value($length, $type = 'mixed')
  {
    if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) return false;

    $rand_value = '';
    while (strlen($rand_value) < $length) {
      if ($type == 'digits') {
        $char = tep_rand(0,9);
      } else {
        $char = chr(tep_rand(0,255));
      }
      if ($type == 'mixed') {
        if (preg_match('/^[a-z0-9]$/i', $char)) $rand_value .= $char;
      } elseif ($type == 'chars') {
        if (preg_match('/^[a-z]$/i', $char)) $rand_value .= $char;
      } elseif ($type == 'digits') {
        if (preg_match('/^[0-9]$/i', $char)) $rand_value .= $char;
      }
    }

    return $rand_value;
  }

    ////
// Return a random value
  function tep_rand($min = null, $max = null) {
    static $seeded;

    if (!isset($seeded)) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }

    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }

   function tep_get_tax_rate($class_id, $country_id = -1, $zone_id = -1) {
    global $customer_zone_id, $customer_country_id;
    static $tax_rates = array();

    if ( ($country_id == -1) && ($zone_id == -1) ) {
      if (!tep_session_is_registered('customer_id')) {
        $country_id = STORE_COUNTRY;
        $zone_id = STORE_ZONE;
      } else {
        $country_id = $customer_country_id;
        $zone_id = $customer_zone_id;
      }
    }

    if (!isset($tax_rates[$class_id][$country_id][$zone_id]['rate'])) {
      $tax_query = mysql_query("select sum(tax_rate) as tax_rate from r_tax_rates tr left join r_zones_to_geo_zones za on (tr.tax_zone_id = za.geo_zone_id) left join r_geo_zones tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int)$country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$zone_id . "') and tr.tax_class_id = '" . (int)$class_id . "' group by tr.tax_priority");
      if (mysql_num_rows($tax_query)) {
        $tax_multiplier = 1.0;
        while ($tax = mysql_fetch_array($tax_query)) {
          $tax_multiplier *= 1.0 + ($tax['tax_rate'] / 100);
        }

        $tax_rates[$class_id][$country_id][$zone_id]['rate'] = ($tax_multiplier - 1.0) * 100;
      } else {
        $tax_rates[$class_id][$country_id][$zone_id]['rate'] = 0;
      }
    }

    return $tax_rates[$class_id][$country_id][$zone_id]['rate'];
  }

   function tep_get_ip_address() {
   $HTTP_SERVER_VARS=$_SERVER;
	global $HTTP_SERVER_VARS;

    $ip_address = null;
    $ip_addresses = array();

    if (isset($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR']) && !empty($HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) {
      foreach ( array_reverse(explode(',', $HTTP_SERVER_VARS['HTTP_X_FORWARDED_FOR'])) as $x_ip ) {
        $x_ip = trim($x_ip);

        if (tep_validate_ip_address($x_ip)) {
          $ip_addresses[] = $x_ip;
        }
      }
    }

    if (isset($HTTP_SERVER_VARS['HTTP_CLIENT_IP']) && !empty($HTTP_SERVER_VARS['HTTP_CLIENT_IP'])) {
      $ip_addresses[] = $HTTP_SERVER_VARS['HTTP_CLIENT_IP'];
    }

    if (isset($HTTP_SERVER_VARS['HTTP_X_CLUSTER_CLIENT_IP']) && !empty($HTTP_SERVER_VARS['HTTP_X_CLUSTER_CLIENT_IP'])) {
      $ip_addresses[] = $HTTP_SERVER_VARS['HTTP_X_CLUSTER_CLIENT_IP'];
    }

    if (isset($HTTP_SERVER_VARS['HTTP_PROXY_USER']) && !empty($HTTP_SERVER_VARS['HTTP_PROXY_USER'])) {
      $ip_addresses[] = $HTTP_SERVER_VARS['HTTP_PROXY_USER'];
    }

    $ip_addresses[] = $HTTP_SERVER_VARS['REMOTE_ADDR'];

    foreach ( $ip_addresses as $ip ) {
      if (!empty($ip) && tep_validate_ip_address($ip)) {
        $ip_address = $ip;
        break;
      }
    }

    return $ip_address;
  }


////
// The HTML href link wrapper function
  function tep_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true) {
    global $request_type, $session_started, $SID;

    $page = tep_output_string($page);

    if (!tep_not_null($page)) {
      die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine the page link!<br /><br />');
    }

    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == true) {
        $link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
      } else {
        $link = HTTP_SERVER . DIR_WS_HTTP_CATALOG;
      }
    } else {
      die('</td></tr></table></td></tr></table><br /><br /><font color="#ff0000"><strong>Error!</strong></font><br /><br /><strong>Unable to determine connection method on a link!<br /><br />Known methods: NONSSL SSL</strong><br /><br />');
    }

    if (tep_not_null($parameters)) {
      $link .= $page . '?' . tep_output_string($parameters);
      $separator = '&';
    } else {
      $link .= $page;
      $separator = '?';
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

// Add the session ID when moving from different HTTP and HTTPS servers, or when SID is defined
    if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
      if (tep_not_null($SID)) {
        $_sid = $SID;
      } elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == true) ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
        if (HTTP_COOKIE_DOMAIN != HTTPS_COOKIE_DOMAIN) {
          $_sid = tep_session_name() . '=' . tep_session_id();
        }
      }
    }

    if (isset($_sid)) {
      $link .= $separator . tep_output_string($_sid);
    }

    while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);

    if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true) ) {
      $link = str_replace('?', '/', $link);
      $link = str_replace('&', '/', $link);
      $link = str_replace('=', '/', $link);
    } else {
      $link = str_replace('&', '&amp;', $link);
    }

    return $link;
  }

    function tep_round($number, $precision) {
    if (strpos($number, '.') && (strlen(substr($number, strpos($number, '.')+1)) > $precision)) {
      $number = substr($number, 0, strpos($number, '.') + 1 + $precision + 1);

      if (substr($number, -1) >= 5) {
        if ($precision > 1) {
          $number = substr($number, 0, -1) + ('0.' . str_repeat(0, $precision-1) . '1');
        } elseif ($precision == 1) {
          $number = substr($number, 0, -1) + 0.1;
        } else {
          $number = substr($number, 0, -1) + 1;
        }
      } else {
        $number = substr($number, 0, -1);
      }
    }

    return $number;
  }

  //copied from functions sessions.php
  function tep_session_is_registered($variable) {
    if (PHP_VERSION < 4.3) {
      return session_is_registered($variable); //returns true/false
    } else {
      return isset($_SESSION) && array_key_exists($variable, $_SESSION); //returns true/false
    }
  }

  // Calculates Tax rounding the result
  function tep_calculate_tax($price, $tax) {
    return $price * $tax / 100;
  }

    ////
// Add tax to a products price
  function tep_add_tax($price, $tax, $override = false) {
    if ( ( (DISPLAY_PRICE_WITH_TAX == 'true') || ($override == true) ) && ($tax > 0) ) {
      return $price + tep_calculate_tax($price, $tax);
    } else {
      return $price;
    }
  }


  function tep_count_modules($modules = '') {
    $count = 0;

    if (empty($modules)) return $count;

    $modules_array = explode(';', $modules);

    for ($i=0, $n=sizeof($modules_array); $i<$n; $i++) {
      $class = substr($modules_array[$i], 0, strrpos($modules_array[$i], '.'));

      /*if (isset($_SESSION[$class]) && is_object($_SESSION[$class])) {
        if ($_SESSION[$class]->enabled) {
          $count++;
        }
      }*/
	  if (isset($GLOBALS[$class]) && is_object($GLOBALS[$class])) {
        if ($GLOBALS[$class]->enabled) {
          $count++;
        }
      }
    }

    return $count;
  }

    function tep_count_payment_modules() {
    return tep_count_modules(MODULE_PAYMENT_INSTALLED);
  }

  function tep_count_shipping_modules() {
    return tep_count_modules(MODULE_SHIPPING_INSTALLED);
  }

   ////
// Output a form radio field
  function tep_draw_radio_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'radio', $value, $checked, $parameters);
  }

    ////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
  function tep_draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '') {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    //$selection = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';
	$selection = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '" id="' . tep_output_string($value) . '"';
    if (tep_not_null($value)) $selection .= ' value="' . tep_output_string($value) . '"';

    if ( ($checked == true) || (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name]) && (($HTTP_GET_VARS[$name] == 'on') || (stripslashes($HTTP_GET_VARS[$name]) == $value))) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name]) && (($HTTP_POST_VARS[$name] == 'on') || (stripslashes($HTTP_POST_VARS[$name]) == $value))) ) {
      $selection .= ' checked="checked"';
    }

    if (tep_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= ' />';
    return $selection;
  }


   ////
// Output a form hidden field
  function tep_draw_hidden_field($name, $value = '', $parameters = '') {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $field = '<input type="hidden" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    } elseif ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) {
      if ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) ) {
        $field .= ' value="' . tep_output_string(stripslashes($HTTP_GET_VARS[$name])) . '"';
      } elseif ( (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) {
        $field .= ' value="' . tep_output_string(stripslashes($HTTP_POST_VARS[$name])) . '"';
      }
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

   ////
// Return the tax description for a zone / class
// TABLES: tax_rates;
  function tep_get_tax_description($class_id, $country_id, $zone_id) {
    static $tax_rates = array();

    if (!isset($tax_rates[$class_id][$country_id][$zone_id]['description'])) {
      $tax_query = mysql_query("select tax_description from r_tax_rates  tr left join r_zones_to_geo_zones za on (tr.tax_zone_id = za.geo_zone_id) left join r_geo_zones tz on (tz.geo_zone_id = tr.tax_zone_id) where (za.zone_country_id is null or za.zone_country_id = '0' or za.zone_country_id = '" . (int)$country_id . "') and (za.zone_id is null or za.zone_id = '0' or za.zone_id = '" . (int)$zone_id . "') and tr.tax_class_id = '" . (int)$class_id . "' order by tr.tax_priority");
      if (mysql_num_rows($tax_query)) {
        $tax_description = '';
        while ($tax = mysql_fetch_array($tax_query)) {
          $tax_description .= $tax['tax_description'] . ' + ';
        }
        $tax_description = substr($tax_description, 0, -3);

        $tax_rates[$class_id][$country_id][$zone_id]['description'] = $tax_description;
      } else {
        $tax_rates[$class_id][$country_id][$zone_id]['description'] = TEXT_UNKNOWN_TAX_RATE;
      }
    }

    return $tax_rates[$class_id][$country_id][$zone_id]['description'];
  }

   function tep_session_name($name = '') {
    if (!empty($name)) {
      return session_name($name);
    } else {
      return session_name();
    }
  }

    function tep_session_id($sessid = '') {
    if (!empty($sessid)) {
      return session_id($sessid);
    } else {
      return session_id();
    }
  }

  function tep_draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true) {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $field = '<input type="' . tep_output_string($type) . '" name="' . tep_output_string($name) . '"';

    if ( ($reinsert_value == true) && ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) ) {
      if (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) {
        $value = stripslashes($HTTP_GET_VARS[$name]);
      } elseif (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) {
        $value = stripslashes($HTTP_POST_VARS[$name]);
      }
    }

    if (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= ' />';

    return $field;
  }

  ////
// Output a form pull down menu
  function tep_draw_pull_down_menu_payment($name, $values, $default = '', $parameters = '', $required = false) {
    global $HTTP_GET_VARS, $HTTP_POST_VARS;

    $field = '<select name="' . tep_output_string($name) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default) && ( (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) || (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) ) ) {
      if (isset($HTTP_GET_VARS[$name]) && is_string($HTTP_GET_VARS[$name])) {
        $default = stripslashes($HTTP_GET_VARS[$name]);
      } elseif (isset($HTTP_POST_VARS[$name]) && is_string($HTTP_POST_VARS[$name])) {
        $default = stripslashes($HTTP_POST_VARS[$name]);
      }
    }

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . tep_output_string($values[$i]['id']) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' selected="selected"';
      }

      $field .= '>' . tep_output_string($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

  ////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_black.gif', $width = '100%', $height = '1') {
    return tep_image(DIR_WS_IMAGES . $image, '', $width, $height);
  }

// The HTML image wrapper function
  function tep_image($src, $alt = '', $width = '', $height = '', $parameters = '') {
    if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
      return false;
    }

// alt is added to the img tag even if it is null to prevent browsers from outputting
// the image filename as default
    $image = '<img src="' . tep_output_string($src) . '" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) {
      $image .= ' title=" ' . tep_output_string($alt) . ' "';
    }

    if ( (CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height)) ) {
      if ($image_size = @getimagesize($src)) {
        if (empty($width) && tep_not_null($height)) {
          $ratio = $height / $image_size[1];
          $width = intval($image_size[0] * $ratio);
        } elseif (tep_not_null($width) && empty($height)) {
          $ratio = $width / $image_size[0];
          $height = intval($image_size[1] * $ratio);
        } elseif (empty($width) && empty($height)) {
          $width = $image_size[0];
          $height = $image_size[1];
        }
      } elseif (IMAGE_REQUIRED == 'false') {
        return false;
      }
    }

    if (tep_not_null($width) && tep_not_null($height)) {
      $image .= ' width="' . tep_output_string($width) . '" height="' . tep_output_string($height) . '"';
    }

    if (tep_not_null($parameters)) $image .= ' ' . $parameters;

    $image .= ' />';

    return $image;
  }

    ////
// Returns the zone (State/Province) code
// TABLES: zones
  function tep_get_zone_code($country_id, $zone_id, $default_zone) {
    $zone_query = mysql_query("select zone_code from r_zones  where zone_country_id = '" . (int)$country_id . "' and zone_id = '" . (int)$zone_id . "'");
    if (mysql_num_rows($zone_query)) {
      $zone = mysql_fetch_array($zone_query);
      return $zone['zone_code'];
    } else {
      return $default_zone;
    }
  }

  ////
// Redirect to another page or site
  function tep_redirect($url) {
    if ( (strstr($url, "\n") != false) || (strstr($url, "\r") != false) ) {
      tep_redirect(tep_href_link(FILENAME_DEFAULT, '', 'NONSSL', false));
    }

    if ( (ENABLE_SSL == true) && (getenv('HTTPS') == 'on') ) { // We are loading an SSL page
      if (substr($url, 0, strlen(HTTP_SERVER)) == HTTP_SERVER) { // NONSSL url
        $url = HTTPS_SERVER . substr($url, strlen(HTTP_SERVER)); // Change it to SSL
      }
    }

    header('Location: ' . $url);

    tep_exit();
  }

    ////
// Stop from parsing any further PHP code
  function tep_exit() {
   tep_session_close();
   exit();
  }

      function tep_session_close() {
    if (PHP_VERSION >= '4.0.4') {
      return session_write_close();
    } elseif (function_exists('session_close')) {
      return session_close();
    }
  }

  //copied fromn class/session.php
    function tep_session_register($variable) {
	 // echo "tep_session_register";
	  //exit;
    if (PHP_VERSION < 4.3) {
      return session_register($variable);
    } else {
      if (isset($GLOBALS[$variable])) {
		  echo "in if session";
        $_SESSION[$variable] =& $GLOBALS[$variable];
      } else {
		  //echo "in else session";
        $_SESSION[$variable] = null;
      }
    }

    return false;
  }
  
/*end shipping functions*/
  
  // Alias function for array of configuration values in the Administration Tool july 31 2012 used for ups shipping
  /*function tep_cfg_select_multioption($select_array, $key_value, $key = '') {
     echo "<pre>";
	 print_r($select_array);
	 print_r($key_value);
	 echo "</pre>";
    for ($i=0; $i<sizeof($select_array); $i++) {
      $name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
      $string .= '<br><input type="checkbox" name="' . $name . '" value="' . $select_array[$i] . '"';
      $key_values = explode( ", ", $key_value);
      if ( in_array($select_array[$i], $key_values) ) $string .= 'CHECKED';
      $string .= '> ' . $select_array[$i];
    } 
    echo $string;
  }*/

  function tep_cfg_select_multioption($select_array, $key_value, $key = '') {
    $key_values = explode( ", ", $key_value);
	foreach($select_array as $k=>$v)
	{
		$name = (($key) ? 'configuration[' . $key . '][]' : 'configuration_value');
		$string .= '<br><input type="checkbox" name="' . $name . '" value="' . $k . '"';
		if ( @in_array($k, $key_values) ) $string .= 'CHECKED';
		$string .= '> ' . $v;
	}
    echo $string;
  }
//


?>