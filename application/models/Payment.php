<?php
  class Model_Payment {
    var $modules, $selected_module;

// class constructor
    function Model_Payment($module = '') {
	  $payment=$_SESSION['payment'];
      if (defined('MODULE_PAYMENT_INSTALLED') && tep_not_null(MODULE_PAYMENT_INSTALLED)!="") {
        $this->modules = explode(';', MODULE_PAYMENT_INSTALLED);

	        $include_modules = array();
			if ( (tep_not_null($module)!="") && (in_array($module . '.' . 'php', $this->modules)) ) {

		  $this->selected_module = $module;

          $include_modules[] = array('class' => $module, 'file' => $module . '.php');
        } else {
		 reset($this->modules);
          while (list(, $value) = each($this->modules)) {
            $class = substr($value, 0, strrpos($value, '.'));
            $include_modules[] = array('class' => $class, 'file' => $value);
          }
        }
        for ($i=0, $n=sizeof($include_modules); $i<$n; $i++) {
		  include_once(APPLICATION_PATH.'/models/Payment/' . $include_modules[$i]['file']);
		$class="Model_Payment_".$include_modules[$i]['class'];
        $_SESSION[$include_modules[$i]['class']] = new $class;
		}

        if ( (tep_count_payment_modules() == 1) && (!isset($_SESSION[$payment]) || (isset($_SESSION[$payment]) && !is_object($_SESSION[$payment]))) ) {
          $_SESSION['payment'] = $include_modules[0]['class'];
        }

        if ( (tep_not_null($module)) && (in_array($module, $this->modules)) && (isset($_SESSION[$module]->form_action_url)) ) {
          $this->form_action_url = $_SESSION[$module]->form_action_url;
        }
      }
    }

// class methods
/* The following method is needed in the checkout_confirmation.php page
   due to a chicken and egg problem with the payment class and order class.
   The payment modules needs the order destination data for the dynamic status
   feature, and the order class needs the payment module title.
   The following method is a work-around to implementing the method in all
   payment modules available which would break the modules in the contributions
   section. This should be looked into again post 2.2.
*/
    function update_status() {
      if (is_array($this->modules)) {
        if (is_object($_SESSION[$this->selected_module])) {
          if (function_exists('method_exists')) {
            if (method_exists($_SESSION[$this->selected_module], 'update_status')) {
              $_SESSION[$this->selected_module]->update_status();
            }
          } else { // PHP3 compatibility
            @call_user_method('update_status', $_SESSION[$this->selected_module]);
          }
        }
      }
    }

    function javascript_validation() {

      $js = '';
      if (is_array($this->modules)) {
        $js = '<script type="text/javascript"><!-- ' . "\n" .
              'function check_form() {' . "\n" .
              '  var error = 0;' . "\n" .
              '  var error_message = "' . JS_ERROR . '";' . "\n" .
              '  var payment_value = null;' . "\n" .
              '  if (document.checkout_payment.payment.length) {' . "\n" .
              '    for (var i=0; i<document.checkout_payment.payment.length; i++) {' . "\n" .
              '      if (document.checkout_payment.payment[i].checked) {' . "\n" .
              '        payment_value = document.checkout_payment.payment[i].value;' . "\n" .
              '      }' . "\n" .
              '    }' . "\n" .
              '  } else if (document.checkout_payment.payment.checked) {' . "\n" .
              '    payment_value = document.checkout_payment.payment.value;' . "\n" .
              '  } else if (document.checkout_payment.payment.value) {' . "\n" .
              '    payment_value = document.checkout_payment.payment.value;' . "\n" .
              '  }' . "\n\n";

        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($_SESSION[$class]->enabled) {
            $js .= $_SESSION[$class]->javascript_validation();
          }
        }

        $js .= "\n" . '  if (payment_value == null) {' . "\n" .
               '    error_message = error_message + "' . JS_ERROR_NO_PAYMENT_MODULE_SELECTED . '";' . "\n" .
               '    error = 1;' . "\n" .
               '  }' . "\n\n" .
               '  if (error == 1) {' . "\n" .
               '    alert(error_message);' . "\n" .
               '    return false;' . "\n" .
               '  } else {' . "\n" .
               '    return true;' . "\n" .
               '  }' . "\n" .
               '}' . "\n" .
               '//--></script>' . "\n";
      }

      return $js;
    }

    function checkout_initialization_method() {
      $initialize_array = array();

      if (is_array($this->modules)) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($_SESSION[$class]->enabled && method_exists($_SESSION[$class], 'checkout_initialization_method')) {
            $initialize_array[] = $_SESSION[$class]->checkout_initialization_method();
          }
        }
      }

      return $initialize_array;
    }

    function selection() {
      $selection_array = array();
      if (is_array($this->modules)) {
        reset($this->modules);
		
		//start giftvoucher free checkout	
		$cartObj=new Model_Cart();	
		$taxes=array_sum($cartObj->getTaxes());
		$complete_total=$cartObj->getTotal()+$_SESSION['shipping_method']['cost']+$taxes;
		if(isset($_SESSION['voucher']) && $_SESSION['voucher']!="")
		{
			$vou=$cartObj->getVoucherDiscount($complete_total,$_SESSION['voucher']);
			$complete_total=$complete_total-$vou;
		}
		//echo "vlue of ".$complete_total;
		//end giftvoucher free checkout	

        while (list(, $value) = each($this->modules)) {
		//start giftvoucher free checkout
		if($complete_total==0 && $value!='Freecheckout.php') //if total is 0 then only freecheckout disable other
		{
			continue;
		}else if($value=='Freecheckout.php' && $complete_total!=0) //if total not zero  then no free checkout
		{
			continue;
		}
		//end giftvoucher free checkout	
          $class = substr($value, 0, strrpos($value, '.'));
          if ($_SESSION[$class]->enabled) {
            $selection = $_SESSION[$class]->selection();
            if (is_array($selection)) $selection_array[] = $selection;
          }
        }
      }

      return $selection_array;
    }

	function selection_format() {
      $selection_array = array();
       if (is_array($this->modules)) {
        reset($this->modules);
        while (list(, $value) = each($this->modules)) {
          $class = substr($value, 0, strrpos($value, '.'));
          if ($_SESSION[$class]->enabled) {
            $selection = $_SESSION[$class]->selection();
            if (is_array($selection)) $selection_array[$selection['id']] = $selection;
          }
        }
      }

      return $selection_array;
    }

    function pre_confirmation_check() {
      if (is_array($this->modules)) {
        if (is_object($_SESSION[$this->selected_module]) && ($_SESSION[$this->selected_module]->enabled) ) {
          $_SESSION[$this->selected_module]->pre_confirmation_check();
        }
      }
    }

    function confirmation() {
      if (is_array($this->modules)) {
        if (is_object($_SESSION[$this->selected_module]) && ($_SESSION[$this->selected_module]->enabled) ) {
          return $_SESSION[$this->selected_module]->confirmation();
        }
      }
    }

    function process_button() {
      if (is_array($this->modules)) {
        if (is_object($_SESSION[$this->selected_module]) && ($_SESSION[$this->selected_module]->enabled) ) {
          return $_SESSION[$this->selected_module]->process_button();
        }
      }
    }

    function before_process() {
      if (is_array($this->modules)) {
        if (is_object($_SESSION[$this->selected_module]) && ($_SESSION[$this->selected_module]->enabled) ) {
          return $_SESSION[$this->selected_module]->before_process();
        }
      }
    }

    function after_process() {
      if (is_array($this->modules)) {
        if (is_object($_SESSION[$this->selected_module]) && ($_SESSION[$this->selected_module]->enabled) ) {
          return $_SESSION[$this->selected_module]->after_process();
        }
      }
    }

    function get_error() {
      if (is_array($this->modules)) {
        if (is_object($_SESSION[$this->selected_module]) && ($_SESSION[$this->selected_module]->enabled) ) {
          return $_SESSION[$this->selected_module]->get_error();
        }
      }
    }
  }
?>
