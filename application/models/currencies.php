<?php

  class Model_currencies {
    var $currencies;
	public $_currency;
	var $db;
// class constructor
    function Model_currencies() {
	$this->db = Zend_Db_Table::getDefaultAdapter();

		$this->currencies = array();
		$this->currencies=Model_Cache::getCache(array("id"=>"currency"));
	
	if(!$this->currencies)
		{
		//exit("inside");
		//echo "in sql";
			  //$currencies_query = $this->db->fetchAll("select code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from r_currencies");
			  $currencies_query = $this->db->fetchAll("select currencies_id,code, title, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, value from r_currencies where status='1'");//included status verificationh on jan 6 2012
			  foreach ($currencies_query as $currencies) 
			  {
				$this->currencies[$currencies['code']] = array('id' => $currencies['currencies_id'],
																'title' => $currencies['title'],
															   'symbol_left' => $currencies['symbol_left'],
															   'symbol_right' => $currencies['symbol_right'],
															   'decimal_point' => $currencies['decimal_point'],
															   'thousands_point' => $currencies['thousands_point'],
															   'decimal_places' => $currencies['decimal_places'],
															   'value' => $currencies['value']);
			  }
			Model_Cache::getCache(array("id"=>"currency","input"=>$this->currencies));	
		}

    }

// class methods

  function format($number, $calculate_currency_value = true, $currency_type = DEFAULT_CURRENCY, $currency_value = '') {
//	function format($number, $calculate_currency_value = true, $currency_type = $_SESSION['Curr']['currency'], $currency_value = '') {
      $currency_type=$_SESSION['Curr']['currency']==""?DEFAULT_CURRENCY:$_SESSION['Curr']['currency'];
	  //echo "value of ".$_SESSION['Curr']['currency'];
	  //exit;

	  if ($calculate_currency_value) {
		  //echo $currency_type."<br/>";
	//  $currency_type='EUR';
        $rate = ($currency_value) ? $currency_value : $this->currencies[$currency_type]['value'];
		
		if($currency_type=="INR") //for indian rupee symbol
		{
			$format_string = '<span class="WebRupee">'.$this->currencies[$currency_type]['symbol_left'].'</span>'.number_format($number * $rate, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']);
		}else
		{
			$format_string = $this->currencies[$currency_type]['symbol_left'] . number_format($number * $rate, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
		}


// if the selected currency is in the european euro-conversion and the default currency is euro,
// the currency will displayed in the national currency and euro currency
        if ( (DEFAULT_CURRENCY == 'EUR') && ($currency_type == 'DEM' || $currency_type == 'BEF' || $currency_type == 'LUF' || $currency_type == 'ESP' || $currency_type == 'FRF' || $currency_type == 'IEP' || $currency_type == 'ITL' || $currency_type == 'NLG' || $currency_type == 'ATS' || $currency_type == 'PTE' || $currency_type == 'FIM' || $currency_type == 'GRD') ) {
          $format_string .= ' <small>[' . $this->format($number, true, 'EUR') . ']</small>';
        }
      //echo "in <br/>";
	  } else {
	  //echo "in else";
	//exit;
        $format_string = $this->currencies[$currency_type]['symbol_left'] . number_format($number, $this->currencies[$currency_type]['decimal_places'], $this->currencies[$currency_type]['decimal_point'], $this->currencies[$currency_type]['thousands_point']) . $this->currencies[$currency_type]['symbol_right'];
      //echo "else <br/>";
	  }

      return $format_string;
    }

    function get_value($code) {
      return $this->currencies[$code]['value'];
    }

    function display_price($products_price, $products_tax, $quantity = 1, $currency_type = DEFAULT_CURRENCY) {
      return $this->format(tep_round(tep_add_tax($products_price, $products_tax), $this->currencies[$currency_type]['decimal_places']) * $quantity);
    }

	    function calculate_price($products_price, $products_tax, $quantity = 1) {
      global $currency;

      return tep_round(tep_add_tax($products_price, $products_tax), $this->currencies[$currency]['decimal_places']) * $quantity;
    }

    function is_set($code) {
      if (isset($this->currencies[$code]) && tep_not_null($this->currencies[$code])) {
        return true;
      } else {
        return false;
      }
    }

	    function get_decimal_places($code) {
      return $this->currencies[$code]['decimal_places'];
    }

	function setCurrency($curr="")
	{

		$currSess=new Zend_Session_Namespace('Curr');
		if(!isset($currSess->currency))  //at first set to default language
		{
			$this->_currency=DEFAULT_CURRENCY;
			$currSess->currency=DEFAULT_CURRENCY;

		}else   //at next set to selected language
		{
			if($curr=="")
			{
				//echo "here".$currSess->currency;
 				//$this->_currency=DEFAULT_CURRENCY;
				//$currSess->currency=DEFAULT_CURRENCY;
				$this->_currency=$currSess->currency;
				$currSess->currency=$currSess->currency;


			}else		//if currency changes then session and method variable set to the selected currency
			{
 				$this->_currency=$curr;
				$currSess->currency=$curr;
				header("location:".$_SERVER['HTTP_REFERER']);
			}
		}
   }
		/*start from open cart on feb 21 2012*/
    	public function convert($value, $from, $to) {
//		echo "value ".$value." from ".$from." to ".$to."<br/>";
		//exit;
		if (isset($this->currencies[$from])) {
			$from = $this->currencies[$from]['value'];
		} else {
			$from = 0;
		}

		if (isset($this->currencies[$to])) {
			$to = $this->currencies[$to]['value'];
		} else {
			$to = 0;
		}
//$from=1;$to=1;

       //echo "from ".$from." to ".$to;
//			exit;
		return $value * ($to / $from);
  	}



	public function getCode() {
	//return $this->code;
	return $_SESSION['Curr'][currency];
	}

	public function getId($currency = '') {
		if (!$currency) {
			return $this->currencies[$_SESSION['Curr']['currency']]['id'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$_SESSION['Curr']['currency']]['id'];
		} else {
			return 0;
		}
  	}

	/*end from open cart on feb 21 2012*/
  }
?>
