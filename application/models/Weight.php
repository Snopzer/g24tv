<?php
final class Model_Weight {
	private $weights = array();
	
	public function __construct() {
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$weight_class_query = $this->db->query("SELECT * FROM r_weight_class wc LEFT JOIN r_weight_class_description wcd ON (wc.weight_class_id = wcd.weight_class_id) WHERE wcd.language_id = '" . (int)$_SESSION['Lang']['language_id']. "'");
        $weight_class_query->rows=$weight_class_query->fetchAll();
    	foreach ($weight_class_query->rows as $result) {
      		$this->weights[strtolower($result['unit'])] = array(
        		'weight_class_id' => $result['weight_class_id'],
        		'title'           => $result['title'],
				'unit'            => $result['unit'],
				'value'           => $result['value']
      		); 
    	}
  	}
	  
  	public function convert($value, $from, $to) {
		if ($from == $to) {
      		return $value;
		}
		
		if (!isset($this->weights[strtolower($from)]) || !isset($this->weights[strtolower($to)])) {
			return $value;
		} else {			
			$from = $this->weights[strtolower($from)]['value'];
			$to = $this->weights[strtolower($to)]['value'];
		
			return $value * ($to / $from);
		}
  	}

	public function format($value, $unit, $decimal_point = '.', $thousand_point = ',') {
		/*echo "value ".$value." unit ".$unit." decimal poitn ".$decimal_point." thousand ".$thousand_point;
		echo "<pre>";
		print_r($this->weights);
		echo "</pre>";*/
		if (isset($this->weights[strtolower($unit)])) {
    		return number_format($value, 2, $decimal_point, $thousand_point) . $this->weights[strtolower($unit)]['unit'];
		} else {
			return number_format($value, 2, $decimal_point, $thousand_point);
		}
	}

	public function getUnit($weight_class_id) {
		if (isset($this->weights[$weight_class_id])) {
    		return $this->weights[$weight_class_id]['unit'];
		} else {
			return '';
		}
	}
}
?>