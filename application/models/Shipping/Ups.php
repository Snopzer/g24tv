<?php

  class Model_Shipping_Ups {
    var $code, $title, $descrption, $icon, $enabled, $types;

// class constructor
    function Model_Shipping_Ups() {
      global $order;

      $this->code = 'Ups';
      $this->title = @constant('MODULE_SHIPPING_UPS_MAIN_TITLE')==""?"UPS Shipping Services":MODULE_SHIPPING_UPS_MAIN_TITLE;//MODULE_SHIPPING_UPS_TEXT_TITLE;
      $this->description = MODULE_SHIPPING_UPS_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_SHIPPING_UPS_SORT_ORDER;
      $this->icon = DIR_WS_ICONS . 'shipping_ups.gif';
	  $this->tax_class = MODULE_SHIPPING_UPS_TAX_CLASS;
      $this->enabled = ((MODULE_SHIPPING_UPS_STATUS == 'True') ? true : false);

	  $this->db = Zend_Db_Table::getDefaultAdapter();
	  /*START ENABLE SHIPPING TO ZONE*/
	    if ( ($this->enabled == true) && ((int)MODULE_SHIPPING_UPS_ZONE > 0) ) {
        $check_flag = false;
		$check_flag_country=false;
		$check_flag_zone=false;
		$addr=$this->getEnableAddress();
		//print_r($addr);
        $check_query = $this->db->query("select zone_id,zone_country_id  from r_zones_to_geo_zones where geo_zone_id='" . MODULE_SHIPPING_UPS_ZONE . "'");
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
	  /*END ENABLE SHIPPING TO ZONE*/
    }

// class methods
    function quote($method = '') {
		/*if($method!="")
		{
		echo "value of ".$method;
			exit;
		}*/
		$addr=$this->getAddress();
		$address=$addr['shipping'];
		/*echo "<pre>";
		print_r($address);
		echo "</pre>";*/
		
			$wtObj=new Model_Weight();
			$cartObj=new Model_Cart();
			$currObj=new Model_currencies();
			$weight = $wtObj->convert($cartObj->getWeight(), @constant('DEFAULT_WEIGHT_CLASS'), @constant('MODULE_SHIPPING_UPS_WEIGHT_CLASS_ID'));
			$weight_code = strtoupper($wtObj->getUnit(@constant('MODULE_SHIPPING_UPS_WEIGHT_CLASS_ID')));
	
			if ($weight_code == 'KG') {
				$weight_code = 'KGS';
			} elseif ($weight_code == 'LB') {
				$weight_code = 'LBS';
			}
			$dim=explode("*",@constant('MODULE_SHIPPING_UPS_DIMENSIONS'));//l*w*h
			$weight = ($weight < 0.1 ? 0.1 : $weight);
			$ltObj=new Model_Length();
			$length = $ltObj->convert($dim[0], @constant('DEFAULT_LENGTH_CLASS'), @constant('MODULE_SHIPPING_UPS_LENGTH_CLASS_ID'));
			$width = $ltObj->convert($dim[1], @constant('DEFAULT_LENGTH_CLASS'), @constant('MODULE_SHIPPING_UPS_LENGTH_CLASS_ID'));
			$height = $ltObj->convert($dim[2], @constant('DEFAULT_LENGTH_CLASS'), @constant('MODULE_SHIPPING_UPS_LENGTH_CLASS_ID'));

			$length_code = strtoupper($ltObj->getUnit(@constant('MODULE_SHIPPING_UPS_LENGTH_CLASS_ID')));
			//echo "length code:".$length_code;			
			$service_code = array(
				// US Origin
				'US' => array(
					'01' => 'UPS Next Day Air',
					'02' => 'UPS 2nd Day Air',
					'03' => 'UPS Ground',
					'07' => 'UPS Worldwide Express',
					'08' => 'UPS Worldwide Expedited',
					'11' => 'UPS Standard',
					'12' => 'UPS 3 Day Select',
					'13' => 'UPS Next Day Air Saver',
					'14' => 'UPS Next Day Air Early A.M.',
					'54' => 'UPS Worldwide Express Plus',
					'59' => 'UPS 2nd Day Air A.M.',
					'65' => 'UPS Saver')
				,
				// Canada Origin
				'CA' => array(
					'01' => 'UPS Express',
					'02' => 'UPS Expedited',
					'07' => 'UPS Worldwide Express',
					'08' => 'UPS Worldwide Expedited',
					'11' => 'UPS Standard',
					'12' => 'UPS 3 Day Select',
					'13' => 'UPS Saver',
					'14' => 'UPS Express Early A.M.',
					'54' => 'UPS Worldwide Express Plus',
					'65' => 'UPS Saver')
				,
				// European Union Origin
				'EU' => array(
					'07' => 'UPS Express',
					'08' => 'UPS Expedited',
					'11' => 'UPS Standard',
					'54' => 'UPS Worldwide Express Plus',
					'65' => 'UPS Saver',
					// next five services Poland domestic only
					'82' => 'UPS Today Standard',
					'83' => 'UPS Today Dedicated Courier',
					'84' =>'UPS Today Intercity',
					'85' => 'UPS Today Express',
					'86' => 'UPS Today Express Saver')
				,
				// Puerto Rico Origin
				'PR' => array(
					'01' => '	',
					'02' => 'text_pr_origin_02',
					'03' => 'text_pr_origin_03',
					'07' => 'text_pr_origin_07',
					'08' => 'text_pr_origin_08',
					'14' => 'text_pr_origin_14',
					'54' => 'text_pr_origin_54',
					'65' => 'text_pr_origin_65')
				,
				// Mexico Origin
				'MX' => array(
					'07' => 'UPS Express',
					'08' => 'UPS Expedited',
					'54' => 'UPS Express Plus',
					'65' => 'UPS Saver')
				,
				// All other origins
				'other' => array(
					// service code 7 seems to be gone after January 2, 2007
					'07' => 'UPS Express',
					'08' => 'UPS Worldwide Expedited',
					'11' => 'UPS Standard',
					'54' => 'UPS Worldwide Express Plus',
					'65' => 'UPS Saver')
				);
			
			$xml  = '<?xml version="1.0"?>';  
			$xml .= '<AccessRequest xml:lang="en-US">';  
			$xml .= '	<AccessLicenseNumber>' . @constant('MODULE_SHIPPING_UPS_KEY') . '</AccessLicenseNumber>';
			$xml .= '	<UserId>' . @constant('MODULE_SHIPPING_UPS_USERNAME') . '</UserId>';
			$xml .= '	<Password>' . @constant('MODULE_SHIPPING_UPS_PASSWORD') . '</Password>';
			$xml .= '</AccessRequest>';
			$xml .= '<?xml version="1.0"?>';
			$xml .= '<RatingServiceSelectionRequest xml:lang="en-US">';
			$xml .= '	<Request>';  
			$xml .= '		<TransactionReference>'; 
			$xml .= '			<CustomerContext>Bare Bones Rate Request</CustomerContext>';  
			$xml .= '			<XpciVersion>1.0001</XpciVersion>';  
			$xml .= '		</TransactionReference>'; 
			$xml .= '		<RequestAction>Rate</RequestAction>';  
			$xml .= '		<RequestOption>shop</RequestOption>';  
			$xml .= '	</Request>';  
			$xml .= '   <PickupType>';
			$xml .= '       <Code>' . @constant('MODULE_SHIPPING_UPS_PICKUP') . '</Code>';
			$xml .= '   </PickupType>';
				
			if (@constant('MODULE_SHIPPING_UPS_COUNTRY') == 'US' && @constant('MODULE_SHIPPING_UPS_PICKUP') == '11') {	
				$xml .= '   <CustomerClassification>';
				$xml .= '       <Code>' . @constant('MODULE_SHIPPING_UPS_CLASSIFICATION') . '</Code>';
				$xml .= '   </CustomerClassification>';		
			}
			
			$xml .= '	<Shipment>';  
			$xml .= '		<Shipper>';  
			$xml .= '			<Address>';  
			$xml .= '				<City>' . @constant('MODULE_SHIPPING_UPS_CITY') . '</City>';
			$xml .= '				<StateProvinceCode>'. @constant('MODULE_SHIPPING_UPS_STATE') . '</StateProvinceCode>';
			$xml .= '				<CountryCode>' . @constant('MODULE_SHIPPING_UPS_COUNTRY') . '</CountryCode>';
			$xml .= '				<PostalCode>' . @constant('MODULE_SHIPPING_UPS_POSTCODE') . '</PostalCode>';
			$xml .= '			</Address>'; 
			$xml .= '		</Shipper>'; 
			$xml .= '		<ShipTo>'; 
			$xml .= '			<Address>'; 
			$xml .= ' 				<City>' . $address['city'] . '</City>';
			$xml .= '				<StateProvinceCode>' . $address['zone_code'] . '</StateProvinceCode>';
			$xml .= '				<CountryCode>' . $address['iso_code_2'] . '</CountryCode>';
			$xml .= '				<PostalCode>' . $address['postcode'] . '</PostalCode>';
			
			if (@constant('MODULE_SHIPPING_UPS_RES') == 'RES') {
		
				 $xml .= '<ResidentialAddressIndicator />';
			}
			
			$xml .= '			</Address>'; 
			$xml .= '		</ShipTo>';
			$xml .= '		<ShipFrom>'; 
			$xml .= '			<Address>'; 
			$xml .= '				<City>' . @constant('MODULE_SHIPPING_UPS_CITY') . '</City>';
			$xml .= '				<StateProvinceCode>'. @constant('MODULE_SHIPPING_UPS_STATE') . '</StateProvinceCode>';
			$xml .= '				<CountryCode>' . @constant('MODULE_SHIPPING_UPS_COUNTRY') . '</CountryCode>';
			$xml .= '				<PostalCode>' . @constant('MODULE_SHIPPING_UPS_POSTCODE') . '</PostalCode>';
			$xml .= '			</Address>'; 
			$xml .= '		</ShipFrom>'; 
	
			$xml .= '		<Package>';
			$xml .= '			<PackagingType>';
			$xml .= '				<Code>' . @constant('MODULE_SHIPPING_UPS_PACKAGE') . '</Code>';
			$xml .= '			</PackagingType>';

			$xml .= '		    <Dimensions>';
    		$xml .= '				<UnitOfMeasurement>';
    		$xml .= '					<Code>' . $length_code . '</Code>';
    		$xml .= '				</UnitOfMeasurement>';
    		$xml .= '				<Length>' . $length . '</Length>';
    		$xml .= '				<Width>' . $width . '</Width>';
    		$xml .= '				<Height>' . $height . '</Height>';
    		$xml .= '			</Dimensions>';
			
			$xml .= '			<PackageWeight>';
			$xml .= '				<UnitOfMeasurement>';
			$xml .= '					<Code>' . $weight_code . '</Code>';
			$xml .= '				</UnitOfMeasurement>';
			$xml .= '				<Weight>' . $weight . '</Weight>';
			$xml .= '			</PackageWeight>';
			
			if (@constant('MODULE_SHIPPING_UPS_INSURANCE')=='true') {
				$xml .= '           <PackageServiceOptions>';
				$xml .= '               <InsuredValue>';
				$xml .= '                   <CurrencyCode>' . $currObj->getCode() . '</CurrencyCode>';
				$xml .= '                   <MonetaryValue>' . $currObj->format($cartObj->getTotal(), false, false, false) . '</MonetaryValue>';
				$xml .= '               </InsuredValue>';
				$xml .= '           </PackageServiceOptions>';
			}
			
			$xml .= '		</Package>';
        	
			$xml .= '	</Shipment>';
			$xml .= '</RatingServiceSelectionRequest>';
			
			if (@constant('MODULE_SHIPPING_UPS_TEST')=='false') {
				$url = 'https://www.ups.com/ups.app/xml/Rate';
			} else {
				$url = 'https://wwwcie.ups.com/ups.app/xml/Rate';
			}
			
			$ch = curl_init($url);  
			
			curl_setopt($ch, CURLOPT_HEADER, 0);  
			curl_setopt($ch, CURLOPT_POST, 1);  
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);  
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);  
			
			$result = curl_exec($ch);  
			
			curl_close($ch); 
					
			$error = '';
			
			$error_msg = '';
			
			$quote_data = array();
			/*echo "<pre>";
			print_r($result);
			echo "</pre>";*/

			if ($result) {
				if (@constant('MODULE_SHIPPING_UPS_DEBUG_MODE')) {
					//$this->log->write("UPS DATA SENT: " . $xml);
					//$this->log->write("UPS DATA RECV: " . $result);
					//echo "ups data sent".$xml."<br/>";
					//echo "ups data recv".$result;
				}
				
				$dom = new DOMDocument('1.0', 'UTF-8');
				$dom->loadXml($result);	
				
				$rating_service_selection_response = $dom->getElementsByTagName('RatingServiceSelectionResponse')->item(0);
				
				$response = $rating_service_selection_response->getElementsByTagName('Response')->item(0);
				
				$response_status_code = $response->getElementsByTagName('ResponseStatusCode');
				$arr=explode(",",@constant('MODULE_SHIPPING_UPS_TYPES'));
						/*echo "<pre>";
						print_r($arr);
						echo "</pre>";*/
						foreach($arr as $k=>$v)
						{
							$array_ship_methods[]=trim($v);
						}

						/*echo "<pre>";
						print_r($array_ship_methods);
						echo "</pre>";*/
				if ($response_status_code->item(0)->nodeValue != '1') {
					$error = $response->getElementsByTagName('Error')->item(0);
					
					$error_msg = $error->getElementsByTagName('ErrorCode')->item(0)->nodeValue;

					$error_msg .= ': ' . $error->getElementsByTagName('ErrorDescription')->item(0)->nodeValue;
				} else {
					$rated_shipments = $rating_service_selection_response->getElementsByTagName('RatedShipment');
	
					foreach ($rated_shipments as $rated_shipment) {
						$service = $rated_shipment->getElementsByTagName('Service')->item(0);
							
						$code = $service->getElementsByTagName('Code')->item(0)->nodeValue;

						$total_charges = $rated_shipment->getElementsByTagName('TotalCharges')->item(0);
							
						$cost = $total_charges->getElementsByTagName('MonetaryValue')->item(0)->nodeValue;	
						
						$currency = $total_charges->getElementsByTagName('CurrencyCode')->item(0)->nodeValue;
						
						if (!($code && $cost)) {
							continue;
						}

						//echo 'ups_' . strtolower(@constant('MODULE_SHIPPING_UPS_ORIGIN')) . '_' . $code."<br/>";
						$this->currency	=new Model_currencies();	
						$this->tax=new Model_Tax();
						

						//echo "value of ".@constant('ups_' . strtolower(@constant('MODULE_SHIPPING_UPS_ORIGIN')) . '_' . $code)."<br/>";
						
						//if (@constant('ups_' . strtolower(@constant('MODULE_SHIPPING_UPS_ORIGIN')) . '_' . $code)) {

						if (in_array(trim('ups_' . strtolower(@constant('MODULE_SHIPPING_UPS_ORIGIN')) . '_' . $code),$array_ship_methods)) {
							//echo "inside <br/>";
							if($method=="")
							{
							$quote_data[] = array(
								//'id'         => 'ups.' . $code,
								'id'         => $code,
								'title'        => $service_code[@constant('MODULE_SHIPPING_UPS_ORIGIN')][$code],
								'cost'         => $this->currency->convert($cost, $currency, @constant('DEFAULT_CURRENCY'))//,
								//'tax_class_id' => '',//$this->config->get('ups_tax_class_id'),
								//'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($cost, $currency, $this->currency->getCode()),							@constant('MODULE_SHIPPING_UPS_TAX_CLASS'), @constant('DISPLAY_PRICE_WITH_TAX')), $this->currency->getCode(), 1.0000000)
							);
							}else if(($method!="") && ($code==$method))
							{
								//echo $code."in else";
														$quote_data[] = array(
								//'id'         => 'ups.' . $code,
								'id'         => $code,
								'title'        => $service_code[@constant('MODULE_SHIPPING_UPS_ORIGIN')][$code],
								'cost'         => $this->currency->convert($cost, $currency, @constant('DEFAULT_CURRENCY'))//,
								//'tax_class_id' => '',//$this->config->get('ups_tax_class_id'),
								//'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($cost, $currency, $this->currency->getCode()),							@constant('MODULE_SHIPPING_UPS_TAX_CLASS'), @constant('DISPLAY_PRICE_WITH_TAX')), $this->currency->getCode(), 1.0000000)
							);
							
							}
						}
						/*if ($this->config->get('ups_' . strtolower($this->config->get('ups_origin')) . '_' . $code)) {
							$quote_data[$code] = array(
								'code'         => 'ups.' . $code,
								'title'        => $service_code[$this->config->get('ups_origin')][$code],
								'cost'         => $this->currency->convert($cost, $currency, $this->config->get('config_currency')),
								'tax_class_id' => $this->config->get('ups_tax_class_id'),
								'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($cost, $currency, $this->currency->getCode()), $this->config->get('ups_tax_class_id'), $this->config->get('config_tax')), $this->currency->getCode(), 1.0000000)
							);
						}*/
					}
				}
			}
			
			//$title = $this->language->get('text_title');
			
			if (@constant('MODULE_SHIPPING_UPS_DISPLAY_WEIGHT')=='True') {
				//echo "in side";
				//exit;
				$title .= ' (Weight :  ' . $wtObj->format($weight, @constant('MODULE_SHIPPING_UPS_WEIGHT_CLASS_ID')) . ')';
			}
		//echo $title;
			/*$method_data = array(
				'code'       => 'ups',
				'title'      => $title,
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('ups_sort_order'),
				'error'      => $error_msg
			);
		  return $this->quotes;*/

		
		$this->quotes = array('id' => $this->code,
		'module' => @constant('MODULE_SHIPPING_UPS_MAIN_TITLE').$title ,
		'methods' => $quote_data);

		if ($error_msg == true) $this->quotes['error'] = $error_msg;
		  /*echo "<pre>";
		  print_r($this->quotes);
		  echo "</pre>";
		  exit;*/
		return $this->quotes;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = $this->db->query("select configuration_value from r_configuration where configuration_key = 'MODULE_SHIPPING_UPS_STATUS'");
		$check=$check_query->fetch();
        //$this->_check = tep_db_num_rows($check_query);
		$this->_check = $check->rowCount();
      }
      return $this->_check;
    }

    function install() {
	
	$this->db->insert('r_configuration',array('configuration_title'=>'Main Title','configuration_key'=>'MODULE_SHIPPING_UPS_MAIN_TITLE','configuration_value'=>'UPS Shipping Services','configuration_description'=>'enter main title to be displayed in the front end','configuration_group_id'=>'6','sort_order'=>'0','date_added'=>new Zend_Db_Expr('NOW()')));

      $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Access Key', 'MODULE_SHIPPING_UPS_KEY', '0', 'Enter the XML rates access key assigned to you by UPS.', '6', '0', now())");

	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Username', 'MODULE_SHIPPING_UPS_USERNAME', '0', 'Enter your UPS Services account username.', '6', '0', now())");

 	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Password', 'MODULE_SHIPPING_UPS_PASSWORD', '0', 'Enter your UPS Services account password', '6', '0', now())");

  	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Customer Classification Code', 'MODULE_SHIPPING_UPS_CLASSIFICATION', '0', '01 - If you are billing to a UPS account and have a daily UPS pickup, 03 - If you do not have a UPS account or you are billing to a UPS account but do not have a daily pickup, 04 - If you are shipping from a retail outlet (only used when origin is US)', '6', '0', now())");

   	  
	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Shipping Origin Code', 'MODULE_SHIPPING_UPS_ORIGIN', '0', 'What origin point should be used (this setting affects only what UPS product names are shown to the user)[US:US Origin,CA:Canada Origin,EU:European Union Origin,PR:Puerto Rico Origin,MX:Mexico Origin,other:All Other Origins]', '6', '0', now())");
	  
   	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin City', 'MODULE_SHIPPING_UPS_CITY', '0', 'Enter the name of the origin city', '6', '0', now())");

   	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin State/Provice', 'MODULE_SHIPPING_UPS_STATE', '0', 'Enter the two-letter code for your origin state/province.', '6', '0', now())");

   	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin Country', 'MODULE_SHIPPING_UPS_COUNTRY', '0', 'Enter the two-letter code for your origin country.', '6', '0', now())");

   	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Origin Zip/Postal Code', 'MODULE_SHIPPING_UPS_POSTCODE', '0', 'Enter your origin zip/postalcode.', '6', '0', now())");
	  
	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Test Mode', 'MODULE_SHIPPING_UPS_TEST', 'True', 'Use this module in Test (YES) or Production mode (NO)?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Insurance', 'MODULE_SHIPPING_UPS_INSURANCE', 'True', 'Enables insurance with product total as the value?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
		
	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Display Delivery Weight', 'MODULE_SHIPPING_UPS_DISPLAY_WEIGHT', 'True', 'Do you want to display the shipping weight? (e.g. Delivery Weight : 2.7674 Kg\'s)', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");


	  	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order,use_function,set_function, date_added) values ('Weight Class', 'MODULE_SHIPPING_UPS_WEIGHT_CLASS_ID', 'True', 'Set to kilograms or pounds.', '6', '0','MODULE_SHIPPING_UPS_LENGTH_CLASS_ID', 'tep_cfg_pull_down_weight_class_list(\'configuration[MODULE_SHIPPING_UPS_WEIGHT_CLASS_ID]\',', now())");

  	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order,use_function, set_function, date_added) values ('Length Class', 'MODULE_SHIPPING_UPS_LENGTH_CLASS_ID', 'True', 'Set to centimeters or inches.', '6', '0', 'tep_cfg_get_lclass_name','tep_cfg_pull_down_length_class_list(\'configuration[MODULE_SHIPPING_UPS_LENGTH_CLASS_ID\',', now())");

	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Dimensions (L x W x H)', 'MODULE_SHIPPING_UPS_DIMENSIONS', '0', 'This is assumed to be your average packing box size. Individual item dimensions are not supported at this time so you must enter average dimensions like 5x5x5', '6', '0', now())");

	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Debug Mode', 'MODULE_SHIPPING_UPS_DEBUG_MODE', 'True', 'Saves send/recv data to the system log', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

 	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable UPS Shipping', 'MODULE_SHIPPING_UPS_STATUS', 'True', 'Do you want to offer UPS shipping?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

      $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('UPS Pickup Method', 'MODULE_SHIPPING_UPS_PICKUP', 'CC', 'How do you give packages to UPS?Enter numeric code as described.01 - Daily Pickup,03 - Customer Counter,06 - One Time Pickup,07 - On Call Air Pickup,19 - Letter Center,20 - Air Service Center,11 - Suggested Retail Rates (UPS Store)', '6', '0', now())");

      $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('UPS Packaging?', 'MODULE_SHIPPING_UPS_PACKAGE', 'CP', 'What kind of packaging do you use?enter the numeric code as described 02 - Package,01 - UPS Letter,03 - UPS Tube,04 - UPS Pak,21 - UPS Express Box,24 - UPS 25kg box,25 - UPS 10kg box', '6', '0', now())");
      $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Residential Delivery?', 'MODULE_SHIPPING_UPS_RES', 'RES', 'Quote for Residential (RES) or Commercial Delivery (COM)', '6', '0', now())");

      /*$this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Handling Fee', 'MODULE_SHIPPING_UPS_HANDLING', '0', 'Handling fee for this shipping method.', '6', '0', now())");*/

	  /*$this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Tax Class', 'MODULE_SHIPPING_UPS_TAX_CLASS', '0', 'Use the following tax class on the shipping fee.', '6', '0', 'tep_get_tax_class_title', 'tep_cfg_pull_down_tax_classes(', now())");*/

      $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Shipping Zone', 'MODULE_SHIPPING_UPS_ZONE', '0', 'If a zone is selected, only enable this shipping method for that zone.', '6', '0', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");

      $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_SHIPPING_UPS_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");

      /*$this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ( 'Shipping Methods', 'MODULE_SHIPPING_UPS_TYPES', '1DM, 1DML, 1DA, 1DAL, 1DAPI, 1DP, 1DPL, 2DM, 2DML, 2DA, 2DAL, 3DS, GND, STD, XPR, XPRL, XDM, XDML, XPD', 'Select the USPS services to be offered.', '6', '13', 'tep_cfg_select_multioption(array(\'1DM\',\'1DML\', \'1DA\', \'1DAL\', \'1DAPI\', \'1DP\', \'1DPL\', \'2DM\', \'2DML\', \'2DA\', \'2DAL\', \'3DS\',\'GND\', \'STD\', \'XPR\', \'XPRL\', \'XDM\', \'XDML\', \'XPD\'), ', now() )");*/
	  $this->db->query("insert into r_configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ( 'Shipping Methods', 'MODULE_SHIPPING_UPS_TYPES', '', 'Select the USPS services to be offered.', '6', '13', 'tep_cfg_select_multioption(array(\'ups_other_07\'=>\'UPS Express\',\'ups_other_11\'=>\'UPS Standard\',\'ups_other_54\'=>\'UPS WorlWide Express Plus\',\'ups_other_65\'=>\'UPS Saver\',\'ups_other_08\'=>\'UPS Expedited\'), ', now() )");
    }

    function remove() {
      $this->db->query("delete from r_configuration where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      /*return array('MODULE_SHIPPING_UPS_KEY','MODULE_SHIPPING_UPS_USERNAME','MODULE_SHIPPING_UPS_PASSWORD','MODULE_SHIPPING_UPS_CLASSIFICATION','MODULE_SHIPPING_UPS_ORIGIN','MODULE_SHIPPING_UPS_CITY','MODULE_SHIPPING_UPS_STATE','MODULE_SHIPPING_UPS_COUNTRY','MODULE_SHIPPING_UPS_POSTCODE','MODULE_SHIPPING_UPS_TEST','MODULE_SHIPPING_UPS_INSURANCE','MODULE_SHIPPING_UPS_STATUS','MODULE_SHIPPING_UPS_DISPLAY_WEIGHT','MODULE_SHIPPING_UPS_WEIGHT_CLASS_ID','MODULE_SHIPPING_UPS_LENGTH_CLASS_ID','MODULE_SHIPPING_UPS_DIMENSIONS','MODULE_SHIPPING_UPS_DEBUG_MODE', 'MODULE_SHIPPING_UPS_PICKUP', 'MODULE_SHIPPING_UPS_PACKAGE', 'MODULE_SHIPPING_UPS_RES', 'MODULE_SHIPPING_UPS_HANDLING', 'MODULE_SHIPPING_UPS_TAX_CLASS', 'MODULE_SHIPPING_UPS_ZONE', 'MODULE_SHIPPING_UPS_SORT_ORDER', 'MODULE_SHIPPING_UPS_TYPES');*/

	  return array('MODULE_SHIPPING_UPS_MAIN_TITLE','MODULE_SHIPPING_UPS_KEY','MODULE_SHIPPING_UPS_USERNAME','MODULE_SHIPPING_UPS_PASSWORD','MODULE_SHIPPING_UPS_CLASSIFICATION','MODULE_SHIPPING_UPS_ORIGIN','MODULE_SHIPPING_UPS_CITY','MODULE_SHIPPING_UPS_STATE','MODULE_SHIPPING_UPS_COUNTRY','MODULE_SHIPPING_UPS_POSTCODE','MODULE_SHIPPING_UPS_TEST','MODULE_SHIPPING_UPS_INSURANCE','MODULE_SHIPPING_UPS_STATUS','MODULE_SHIPPING_UPS_DISPLAY_WEIGHT','MODULE_SHIPPING_UPS_WEIGHT_CLASS_ID','MODULE_SHIPPING_UPS_LENGTH_CLASS_ID','MODULE_SHIPPING_UPS_DIMENSIONS','MODULE_SHIPPING_UPS_DEBUG_MODE', 'MODULE_SHIPPING_UPS_PICKUP', 'MODULE_SHIPPING_UPS_PACKAGE', 'MODULE_SHIPPING_UPS_RES','MODULE_SHIPPING_UPS_ZONE', 'MODULE_SHIPPING_UPS_SORT_ORDER', 'MODULE_SHIPPING_UPS_TYPES');
    }

 
		public function getAddress()
		{
			if($_SESSION['customer_id']=="" && $_SESSION['guest']!="")
			{
				$row['shipping']['address_1']=$_SESSION['guest']['shipping']['address_1'];
				$row['shipping']['address_2']=$_SESSION['guest']['shipping']['address_2'];
				$row['shipping']['postcode']=$_SESSION['guest']['shipping']['postcode'];
				$row['shipping']['city']=$_SESSION['guest']['shipping']['city'];
				$row['shipping']['state']=$_SESSION['guest']['shipping']['zone'];
				$row['shipping']['country']=$_SESSION['guest']['shipping']['country'];
				$row['shipping']['iso_code_2']=$_SESSION['guest']['shipping']['iso_code_2'];
				$row['shipping']['iso_code_3']=$_SESSION['guest']['shipping']['iso_code_3'];
			}else
			{		$row=array();
					$row['shipping']=$this->db->fetchRow("SELECT  a.entry_street_address AS address_1, entry_suburb AS address_2, a.entry_city AS city, a.entry_postcode AS postcode, c.countries_iso_code_2 AS iso_code_2, c.countries_iso_code_3 AS iso_code_3, c.countries_name AS country, z.zone_code, z.zone_name AS state FROM r_address_book a, r_countries c, r_zones z WHERE a.entry_zone_id = z.zone_id
					AND a.entry_country_id = c.countries_id AND a.address_book_id='".$_SESSION['shipping_address_id']."'");
			}
			return $row;
		}
		
		public function getEnableAddress()
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

		public function getCountry($id)
	    {
			$row=$this->db->fetchRow("select * from r_countries where countries_id='".$id."'");
			return $row;
		}
  }
?>
