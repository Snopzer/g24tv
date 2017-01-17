<?php
/**
 * Handling Errors in the application
 *
 * @category   Zend
 * @package    OrderTotalController
 * @author     suresh babu k
 */
class OrderTotalController extends Zend_Controller_Action {
        public $cart=null;
        public $tr=null;
	
        public function init()
	{
               Zend_Session::start();
		$this->rConfig=new Model_DbTable_rconfiguration();
		$this->rConfig->getConfiguration();
                $this->cart=new Model_Cart();
                /* echo "<pre>";
                print_r($_SESSION['OBJ']['tr']);
                echo "</pre>";*/
	}

	public function vouchercalculateAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$vouObj=new Model_CheckoutVoucher();
		$json = array();
			if (!$this->cart->hasProducts()) {
			$json['redirect'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart');
		}

		if (isset($this->_request->voucher)) {
			$voucher_info = $vouObj->getVoucher($this->_request->voucher);
			if ($voucher_info) {
				$_SESSION['voucher'] = $this->_request->voucher;

				$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_success_total_voucher');

				$json['redirect'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart', '', 'SSL');
			} else {
				$json['error'] = $_SESSION['OBJ']['tr']->translate('error_voucher_total_voucher');
			}
		}
		echo Model_Json::encode($json);
	}

	 public function rewardcalculateAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		$json = array();
		$custObj=new Model_Customer();

		if (isset($this->_request->reward)) {
			if (!$this->_request->reward) {
				$json['error'] = $_SESSION['OBJ']['tr']->translate('error_empty_total_reward');
			}

			$points = $custObj->getRewardPoints();

			if ($this->_request->reward > $points) {
				$json['error'] = sprintf($_SESSION['OBJ']['tr']->translate('error_points_total_reward'), $this->_request->reward);
			}

			$points_total = 0;

			foreach ($this->cart->getProducts() as $product) {
				if ($product['points']) {
					$points_total += $product['points'];
				}
			}

			if ($this->_request->reward > $points_total) {
				$json['error'] = sprintf($_SESSION['OBJ']['tr']->translate('error_maximum_total_reward'), $points_total);
			}

			if (!isset($json['error'])) {
				$_SESSION['reward'] = $this->_request->reward;
				$_SESSION['success'] = $_SESSION['OBJ']['tr']->translate('text_success_total_reward');

				$json['redirect'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart', '', 'SSL');
			}
		}
	echo Model_Json::encode($json);
	}

	public function couponcalculateAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$json = array();

		if (!$this->cart->hasProducts()) {
			$json['redirect'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart');
		}

		if (isset($this->_request->coupon)) {
			$chckCoupObj=new Model_CheckoutCoupon();
			$coupon_info = $chckCoupObj->getCoupon($this->_request->coupon);

			if ($coupon_info) {
				$_SESSION['coupon'] = $this->_request->coupon;

				$_SESSION['success'] = stripslashes($_SESSION['OBJ']['tr']->translate('text_success_total_coupon'));

				$json['redirect'] = HTTP_SERVER.'checkout/cart';//$this->url->link('checkout/cart', '', 'SSL');
			} else {
				$json['error'] = stripslashes($_SESSION['OBJ']['tr']->translate('error_coupon_total_coupon'));
			}
		}
		echo Model_Json::encode($json);
	}
}

