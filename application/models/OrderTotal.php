<?php
final class Model_OrderTotal
{
	public $db=null;
	public $tr=null;
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$lang=new Model_Languages();
		$lC=new Model_Cache();
		$this->tr=$lC->getLangCache($lang);

	}

	public function reward()
	{
		$custObj=new Model_Customer();
		$cartObj=new Model_Cart();
		$points = $custObj->getRewardPoints();
		//$this->data['available_points']=$points;
		
		$points_total = 0;

		foreach ($cartObj->getProducts() as $product) {
			if ($product['points']) {
				$points_total += $product['points'];
			}
		}
		//echo "total points".$points;
		if ($points && $points_total && MODULE_ORDER_TOTAL_REWARDPOINTS_STATUS) {

			$this->data['heading_title_reward'] = sprintf($this->tr->translate('heading_title_total_reward'), $points);

			$this->data['entry_reward'] = sprintf($this->tr->translate('entry_reward_total_reward'), $points_total);

			$this->data['button_reward'] = $this->tr->translate('button_reward');

			if (isset($_SESSION['reward'])) {
				$this->data['reward'] = $_SESSION['reward'];
			} else {
				$this->data['reward'] = '';
			}
		}
		return $this->data;
	}

	public function coupon() {
		$this->data['heading_title_coupon'] = $this->tr->translate('heading_title_total_coupon');
		
		$this->data['entry_coupon'] = $this->tr->translate('entry_coupon_total_coupon');
		
		$this->data['button_coupon'] = $this->tr->translate('button_coupon');
				
		if (isset($_SESSION['coupon'])) {
			$this->data['coupon'] = $_SESSION['coupon'];
		} else {
			$this->data['coupon'] = '';
		}
		return $this->data;
  	}
}
?>