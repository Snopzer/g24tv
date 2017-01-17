<?php
final class Model_Length {
	private $lengths = array();
	
	public function __construct() {
 		$this->db = Zend_Db_Table::getDefaultAdapter();
	
		$length_class_query = $this->db->query("SELECT * FROM r_length_class mc LEFT JOIN r_length_class_description mcd ON (mc.length_class_id = mcd.length_class_id) WHERE mcd.language_id = '".(int)$_SESSION['Lang']['language_id']. "'");
    $length_class_query->rows=$length_class_query->fetchAll();
    	foreach ($length_class_query->rows as $result) {
      		$this->lengths[strtolower($result['unit'])] = array(
				'length_class_id' => $result['length_class_id'],
        		'unit'            => $result['unit'],
        		'title'           => $result['title'],
				'value'           => $result['value']
      		);
    	}
  	}
	  
  	public function convert($value, $from, $to) {
		//echo "value ".$value. " from ".$from." to ".$to."<br/>";
		if ($from == $to) {
      		return $value;
		}
		
		if (isset($this->lengths[strtolower($from)])) {
			$from = $this->lengths[strtolower($from)]['value'];
		} else {
			$from = 0;
		}
		
		if (isset($this->lengths[strtolower($to)])) {
			$to = $this->lengths[strtolower($to)]['value'];
		} else {
			$to = 0;
		}		
		
      	return $value * ($to / $from);
  	}

	public function format($value, $unit, $decimal_point = '.', $thousand_point = ',') {
    	return number_format($value, 2, $decimal_point, $thousand_point) . $this->lengths[$unit]['unit'];
  	}

	public function getUnit($length_class_id) {
		if (isset($this->lengths[$length_class_id])) {
    		return $this->lengths[$length_class_id]['unit'];
		} else {
			return '';
		}
	}
}
?>