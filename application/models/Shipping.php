<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  class Model_Shipping {
    var $modules;

// class constructor
    function Model_Shipping($module = '') {
      //global $language, $PHP_SELF;
	 // $language=$_SESSION['language']; //this is used to language folder ,it is not needed her so commented/english
	  $PHP_SELF=$_SESSION['PHP_SELF'];

      if (defined('MODULE_SHIPPING_INSTALLED') && tep_not_null(MODULE_SHIPPING_INSTALLED)) {
        $this->modules = explode(';', MODULE_SHIPPING_INSTALLED);

        $include_modules = array();

		//print_r($this->modules);

        if ( (tep_not_null($module)) && (in_array(substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)), $this->modules)) ) {
          $include_modules[] = array('class' => substr($module['id'], 0, strpos($module['id'], '_')), 'file' => substr($module['id'], 0, strpos($module['id'], '_')) . '.' . substr($PHP_SELF, (strrpos($PHP_SELF, '.')+1)));
        } else {
          reset($this->modules);
          while (list(, $value) = each($this->modules)) {
            $class = substr($value, 0, strrpos($value, '.'));
            $include_modules[] = array('class' => $class, 'file' => $value);
          }
        }
		//print_r($include_modules);
        for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
         //include(DIR_WS_MODULES . 'shipping/' . $include_modules[$i]['file']);
          include('Shipping/' . $include_modules[$i]['file']);
			//echo $include_modules[$i]['class']."hello";
			//exit;
			$class="Model_Shipping_".$include_modules[$i]['class'];
          $GLOBALS[$include_modules[$i]['class']] = new $class;
		 // $_SESSION[$include_modules[$i]['class']]= new $include_modules[$i]['class'];
		  //ECHO "<BR>".$include_modules[$i]['class']."<BR>";
		//ECHO "<BR>".$GLOBALS[$include_modules[$i]['class']]."<BR>";

		  //exit;
		  //PRINT_R($_SESSION[$include_modules[$i]['class']]);
        }
      }
	 // print_r($_SESSION);
    }

    function quote($method = '', $module = '') {
      global $total_weight, $shipping_weight, $shipping_quoted, $shipping_num_boxes;

	//	$total_weight=$_SESSION['total_weight'];
	//	$shipping_weight=$_SESSION['shipping_weight'];
	//	$shipping_quoted=$_SESSION['shipping_quoted'];
	//	$shipping_num_boxes=$_SESSION['shipping_num_boxes'];
 //echo "total_weight ".$total_weight." shipping weight ". $shipping_weight." shipping quoted ".$shipping_quoted." shipping_num_boxes ". $shipping_num_boxes;      $quotes_array = array();

//exit;
//print_r($this->modules);
      if (is_array($this->modules)) {
        $shipping_quoted = '';
        $shipping_num_boxes = 1;
        $shipping_weight = $total_weight;

        if (SHIPPING_BOX_WEIGHT >= $shipping_weight*SHIPPING_BOX_PADDING/100) {
			//echo "<br/>in if <br/>";
	       $shipping_weight = $shipping_weight+SHIPPING_BOX_WEIGHT;
		  //echo $shipping_weight;
        } else {
 			echo($shipping_weight*SHIPPING_BOX_PADDING/100);
         $shipping_weight = $shipping_weight + ($shipping_weight*SHIPPING_BOX_PADDING/100);
        }
        if ($shipping_weight > SHIPPING_MAX_WEIGHT) { // Split into many boxes

		//  echo "ship box ".$shipping_num_boxes;
		  $shipping_num_boxes = ceil($shipping_weight/SHIPPING_MAX_WEIGHT);
        //  echo "ship box ".$shipping_num_boxes. " ".$shipping_weight;
		  $shipping_weight = $shipping_weight/$shipping_num_boxes;
        }
//echo "shipping weight in module ".$shipping_weight."<br/>";
        $include_quotes = array();
         reset($this->modules);
		//print_r($this->modules);
		 //exit;

        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          		 //echo $class;
		 //print_r($_SESSION[$class]);
		  if (tep_not_null($module)) {
			//  echo "in if ";
            if ( ($module == $class) && ($GLOBALS[$class]->enabled) ) {
              $include_quotes[] = $class;
            }
          } elseif ($GLOBALS[$class]->enabled) {
		  //echo "in else";
            $include_quotes[] = $class;
          }
        }
		//print_r($include_quotes);
        $size = sizeof($include_quotes);
        for ($i=0; $i<$size; $i++) {
			//echo "<br/>include quotes ".$include_quotes[$i]."<br>";
			//echo "<br/>method ".$method." value<br>";
			//print_r($_SESSION[$include_quotes[$i]]);
			//print_r($_SESSION[$include_quotes[$i]]->quote($method));
          $quotes = $GLOBALS[$include_quotes[$i]]->quote($method);
          if (is_array($quotes)) $quotes_array[] = $quotes;
        }
      }
	  //exit;
      return $quotes_array;
    }

    function cheapest() {
      if (is_array($this->modules)) {
        $rates = array();

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($GLOBALS[$class]->enabled) {
            $quotes = $GLOBALS[$class]->quotes;
            for ($i=0, $n=sizeof($quotes['methods']); $i<$n; $i++) {
              if (isset($quotes['methods'][$i]['cost']) && tep_not_null($quotes['methods'][$i]['cost'])) {
                $rates[] = array('id' => $quotes['id'] . '_' . $quotes['methods'][$i]['id'],
                                 'title' => $quotes['module'] . ' (' . $quotes['methods'][$i]['title'] . ')',
                                 'cost' => $quotes['methods'][$i]['cost']);
              }
            }
          }
        }

        $cheapest = false;
        for ($i=0, $n=sizeof($rates); $i<$n; $i++) {
          if (is_array($cheapest)) {
            if ($rates[$i]['cost'] < $cheapest['cost']) {
              $cheapest = $rates[$i];
            }
          } else {
            $cheapest = $rates[$i];
          }
        }

        return $cheapest;
      }
    }
  }
?>
