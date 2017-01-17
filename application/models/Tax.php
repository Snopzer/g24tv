<?php
final class Model_Tax {
	private $taxes = array();
	private $shipping_address;
	private $payment_address;

	public function __construct() {
		if (isset($_SESSION['country_id']) && isset($_SESSION['zone_id'])) {
			$country_id = $_SESSION['country_id'];
			$zone_id = $_SESSION['zone_id'];
		} else {
			if (DISPLAY_PRICE_WITH_TAX) {
				$country_id = STORE_COUNTRY;
				$zone_id =STORE_ZONE;
			} else {
				$country_id = 0;
				$zone_id = 0;
			}
		}
		$this->db = Zend_Db_Table::getDefaultAdapter();
 		$this->setZone($country_id, $zone_id);
  	}

	public function setZone($country_id, $zone_id) {

		$this->taxes = array();
	 	$tax_rate_query = $this->db->query("SELECT tr.tax_class_id, tr.tax_rate as rate, tr.tax_description as description, tr.tax_priority as priority FROM r_tax_rates tr LEFT JOIN r_zones_to_geo_zones z2gz ON (tr.tax_zone_id = z2gz.geo_zone_id) LEFT JOIN r_geo_zones gz ON (tr.tax_zone_id = gz.geo_zone_id) WHERE (z2gz.zone_country_id = '0' OR z2gz.zone_country_id = '" . (int)$country_id . "') AND (z2gz.zone_id = '0' OR z2gz.zone_id = '" . (int)$zone_id . "') ORDER BY tr.tax_priority ASC");

		$rows=$tax_rate_query->fetchAll();

	if($rows!=""){
		foreach ($rows as $result) {
      		$this->taxes[$result['tax_class_id']][] = array(
        		'rate'        => $result['rate'],
        		'description' => $result['description'],
				'priority'    => $result['priority']
      		);
    	}
	}
		$_SESSION['country_id'] = $country_id;
		$_SESSION['zone_id'] = $zone_id;
	}

  	public function calculate($value, $tax_class_id, $calculate = true) {

		if (($calculate) && (isset($this->taxes[$tax_class_id])))  {
			$rate = $this->getRate($tax_class_id);
       		return $value + ($value * $rate / 100);
    	} else {
       		return $value;
    	}
  	}

  	public function getRate($tax_class_id) {
		if (isset($this->taxes[$tax_class_id])) {
			$rate = 0;

			foreach ($this->taxes[$tax_class_id] as $tax_rate) {
				$rate += $tax_rate['rate'];
			}

			return $rate;
		} else {
    		return 0;
		}
	}

  	public function getDescription($tax_class_id) {
		return (isset($this->taxes[$tax_class_id]) ? $this->taxes[$tax_class_id] : array());
  	}

  	public function has($tax_class_id) {
		return isset($this->taxes[$tax_class_id]);
  	}

	public function setShippingAddress($country_id, $zone_id) {
		$this->shipping_address = array(
			'country_id' => $country_id,
			'zone_id'    => $zone_id
		);				
	}

	public function setPaymentAddress($country_id, $zone_id) {
		$this->payment_address = array(
			'country_id' => $country_id,
			'zone_id'    => $zone_id
		);
	}
}
?>