<?php
final class Model_Cart {
    public $db;
  	public function __construct() {
          $this->db = Zend_Db_Table::getDefaultAdapter();
		if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
      		$_SESSION['cart'] = array();
    	}
	}

  	public function getProducts() {
        $product_data = array();

    	foreach ($_SESSION['cart'] as $key => $quantity) {
      		$product = explode(':', $key);
      		$product_id = $product[0];
			$stock = true;

			// Options
      		if (isset($product[1])) {
        		$options = unserialize(base64_decode($product[1]));
      		} else {
        		$options = array();
      		}

      		$product_query = $this->db->query("SELECT *, wcd.unit AS weight_class, mcd.unit AS length_class FROM r_products p LEFT JOIN r_products_description pd ON (p.products_id = pd.products_id) LEFT JOIN r_weight_class wc ON (p.weight_class_id = wc.weight_class_id) LEFT JOIN r_weight_class_description wcd ON (wc.weight_class_id = wcd.weight_class_id) LEFT JOIN r_length_class mc ON (p.length_class_id = mc.length_class_id) LEFT JOIN r_length_class_description mcd ON (mc.length_class_id = mcd.length_class_id) WHERE p.products_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND wcd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' AND p.products_date_available <= '".date('Y-m-d H:i:s')."' AND p.products_status = '1'");
		
			$product_query->row=$product_query->fetch();
			if ($product_query->rowCount()) {
      			$option_price = 0;
				$option_points = 0;
				$option_weight = 0;

      			$option_data = array();

      			foreach ($options as $product_option_id => $option_value) {
					$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, od.name, o.type FROM r_products_option po LEFT JOIN `r_option` o ON (po.option_id = o.option_id) LEFT JOIN r_option_description od ON (o.option_id = od.option_id) WHERE po.product_option_id = '" . (int)$product_option_id . "' AND po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'");
					$option_query->row=$option_query->fetch();
					if ($option_query->rowCount()) {
						if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio') {
							$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM r_products_option_value pov LEFT JOIN r_option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN r_option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$option_value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'");

							$option_value_query->row=$option_value_query->fetch();
							if ($option_value_query->rowCount()) {
								if ($option_value_query->row['price_prefix'] == '+') {
									$option_price += $option_value_query->row['price'];
								} elseif ($option_value_query->row['price_prefix'] == '-') {
									$option_price -= $option_value_query->row['price'];
								}

								if ($option_value_query->row['points_prefix'] == '+') {
									$option_points += $option_value_query->row['points'];
								} elseif ($option_value_query->row['points_prefix'] == '-') {
									$option_points -= $option_value_query->row['points'];
								}

								if ($option_value_query->row['weight_prefix'] == '+') {
									$option_weight += $option_value_query->row['weight'];
								} elseif ($option_value_query->row['weight_prefix'] == '-') {
									$option_weight -= $option_value_query->row['weight'];
								}

								if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $quantity))) {
									$stock = false;
								}

								$option_data[] = array(
									'product_option_id'       => $product_option_id,
									'product_option_value_id' => $option_value,
									'option_id'               => $option_query->row['option_id'],
									'option_value_id'         => $option_value_query->row['option_value_id'],
									'name'                    => $option_query->row['name'],
									'option_value'            => $option_value_query->row['name'],
									'type'                    => $option_query->row['type'],
									'quantity'                => $option_value_query->row['quantity'],
									'subtract'                => $option_value_query->row['subtract'],
									'price'                   => $option_value_query->row['price'],
									'price_prefix'            => $option_value_query->row['price_prefix'],
									'points'                  => $option_value_query->row['points'],
									'points_prefix'           => $option_value_query->row['points_prefix'],
									'weight'                  => $option_value_query->row['weight'],
									'weight_prefix'           => $option_value_query->row['weight_prefix']
								);
							}
						} elseif ($option_query->row['type'] == 'checkbox' && is_array($option_value)) {
							foreach ($option_value as $product_option_value_id) {
								$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM r_products_option_value pov LEFT JOIN r_option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN r_option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'");

								$option_value_query->row=$option_value_query->fetch();
								if ($option_value_query->rowCount()) {
									if ($option_value_query->row['price_prefix'] == '+') {
										$option_price += $option_value_query->row['price'];
									} elseif ($option_value_query->row['price_prefix'] == '-') {
										$option_price -= $option_value_query->row['price'];
									}

									if ($option_value_query->row['points_prefix'] == '+') {
										$option_points += $option_value_query->row['points'];
									} elseif ($option_value_query->row['points_prefix'] == '-') {
										$option_points -= $option_value_query->row['points'];
									}

									if ($option_value_query->row['weight_prefix'] == '+') {
										$option_weight += $option_value_query->row['weight'];
									} elseif ($option_value_query->row['weight_prefix'] == '-') {
										$option_weight -= $option_value_query->row['weight'];
									}

									if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $quantity))) {
										$stock = false;
									}

									$option_data[] = array(
										'product_option_id'       => $product_option_id,
										'product_option_value_id' => $product_option_value_id,
										'option_id'               => $option_query->row['option_id'],
										'option_value_id'         => $option_value_query->row['option_value_id'],
										'name'                    => $option_query->row['name'],
										'option_value'            => $option_value_query->row['name'],
										'type'                    => $option_query->row['type'],
										'quantity'                => $option_value_query->row['quantity'],
										'subtract'                => $option_value_query->row['subtract'],
										'price'                   => $option_value_query->row['price'],
										'price_prefix'            => $option_value_query->row['price_prefix'],
										'points'                  => $option_value_query->row['points'],
										'points_prefix'           => $option_value_query->row['points_prefix'],
										'weight'                  => $option_value_query->row['weight'],
										'weight_prefix'           => $option_value_query->row['weight_prefix']
									);
								}
							}
						} elseif ($option_query->row['type'] == 'text' || $option_query->row['type'] == 'textarea' || $option_query->row['type'] == 'file' || $option_query->row['type'] == 'date' || $option_query->row['type'] == 'datetime' || $option_query->row['type'] == 'time') {
							$option_data[] = array(
								'product_option_id'       => $product_option_id,
								'product_option_value_id' => '',
								'option_id'               => $option_query->row['option_id'],
								'option_value_id'         => '',
								'name'                    => $option_query->row['name'],
								'option_value'            => $option_value,
								'type'                    => $option_query->row['type'],
								'quantity'                => '',
								'subtract'                => '',
								'price'                   => '',
								'price_prefix'            => '',
								'points'                  => '',
								'points_prefix'           => '',
								'weight'                  => '',
								'weight_prefix'           => ''
							);
						}
					}
      			}

				$custObj=new Model_Customer();
				if ($custObj->isLogged()) {
					$customer_group_id = $custObj->getCustomerGroupId();
				} else {
					$customer_group_id = DEFAULT_CGROUP;//$this->config->get('config_customer_group_id');
				}

				$price = $product_query->row['products_price'];

				// Product Discounts
				$discount_quantity = 0;

				foreach ($_SESSION['cart'] as $key_2 => $quantity_2) {
					$product_2 = explode(':', $key_2);

					if ($product_2[0] == $product_id) {
						$discount_quantity += $quantity_2;
					}
				}

				$product_discount_query = $this->db->query("SELECT price FROM r_product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "' AND quantity <= '" . (int)$discount_quantity . "' AND ((date_start = '0000-00-00' OR date_start < '".date('Y-m-d H:i:s')."') AND (date_end = '0000-00-00' OR date_end >'".date('Y-m-d H:i:s')."')) ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1");
				$product_discount_query->row=$product_discount_query->fetch();
				if ($product_discount_query->rowCount()) {
					$price = $product_discount_query->row['price'];
				}

				// Product Specials
				$product_special_query = $this->db->query("SELECT specials_new_products_price FROM r_products_specials WHERE products_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "' AND ((start_date = '0000-00-00' OR start_date < '".date('Y-m-d H:i:s')."') AND (expires_date = '0000-00-00' OR expires_date > '".date('Y-m-d H:i:s')."')) ORDER BY priority ASC, specials_new_products_price ASC LIMIT 1");
				$product_special_query->row=$product_special_query->fetch();
				if ($product_special_query->rowCount()) {
					$price = $product_special_query->row['specials_new_products_price'];
				}

				// Reward Points
				$query = $this->db->query("SELECT points FROM r_product_reward WHERE product_id = '" . $product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "'");

				if ($query->rowCount()) {
					$query_row=$query->fetch();
					$reward = $query_row['points'];
				} else {
					$reward = 0;
				}

				// Downloads
				$download_data = array();

				$download_query = $this->db->query("SELECT * FROM r_product_to_download p2d LEFT JOIN r_download d ON (p2d.download_id = d.download_id) LEFT JOIN r_download_description dd ON (d.download_id = dd.download_id) WHERE p2d.product_id = '" . (int)$product_id . "' AND dd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'");
				$download_query->rows=$download_query->fetchAll();
				foreach ($download_query->rows as $download) {
        			$download_data[] = array(
          				'download_id' => $download['download_id'],
						'name'        => $download['name'],
						'filename'    => $download['filename'],
						'mask'        => $download['mask'],
						'remaining'   => $download['remaining']
        			);
				}

				// Stock
				if (!$product_query->row['products_quantity'] || ($product_query->row['products_quantity'] < $quantity)) {
					$stock = false;
				}

      			$product_data[$key] = array(
        			'key'          => $key,
        			'product_id'   => $product_query->row['products_id'],
        			'name'         => $product_query->row['products_name'],
        			'model'        => $product_query->row['products_model'],
					'shipping'     => $product_query->row['shipping'],
        			'image'        => $product_query->row['products_image'],
        			'option'       => $option_data,
					'download'     => $download_data,
        			'quantity'     => $quantity,
        			'minimum'      => $product_query->row['products_minimum_quantity'],
					'subtract'     => $product_query->row['substract_stock'],
					'stock'        => $stock,
        			'price'        => ($price + $option_price),
        			'total'        => ($price + $option_price) * $quantity,
					'reward'       => $reward * $quantity,
					'points'       => ($product_query->row['products_points'] + $option_points) * $quantity,
					'tax_class_id' => $product_query->row['products_tax_class_id'],
        			'weight'       => ($product_query->row['products_weight'] + $option_weight) * $quantity,
        			'weight_class' => $product_query->row['weight_class'],
        			'length'       => $product_query->row['length'],
					'width'        => $product_query->row['width'],
					'height'       => $product_query->row['height'],
        			'length_class' => $product_query->row['length_class']
      			);
			} else {
				$this->remove($key);
			}
    	}

		return $product_data;
  	}

  	public function add($product_id, $qty = 1, $options = array()) {
	  	if (!$options) {
      		$key = $product_id;
    	} else {
      		$key = $product_id . ':' . base64_encode(serialize($options));
    	}
		if ((int)$qty && ((int)$qty > 0)) {
    		if (!isset($_SESSION['cart'][$key])) {
      			$_SESSION['cart'][$key] = (int)$qty;
    		} else {
      			$_SESSION['cart'][$key] += (int)$qty;
    		}
		}
  	}

  	public function update($key, $qty) {
    	if ((int)$qty && ((int)$qty > 0)) {
      		$_SESSION['cart'][$key] = (int)$qty;
    	} else {
	  		$this->remove($key);
		}
  	}

  	public function remove($key) {
		if (isset($_SESSION['cart'][$key])) {
     		unset($_SESSION['cart'][$key]);
  		}
	}

  	public function clear() {
		$_SESSION['cart'] = array();
  	}

  	public function getWeight() {
		$weight = 0;
		$wtObj=new Model_Weight();

    	foreach ($this->getProducts() as $product) {
			if ($product['shipping']) {
      			$weight += $wtObj->convert($product['weight'], $product['weight_class'],DEFAULT_WEIGHT_CLASS);
			}
		}

		return $weight;
	}

  	public function getSubTotal() {
		$total = 0;

		foreach ($this->getProducts() as $product) {
			$total += $product['total'];
		}

		return $total;
  	}

	public function getTaxes() {

 		$taxes = array();
		$taxObj=new Model_Tax();
		foreach ($this->getProducts() as $product) {
			if ($product['tax_class_id']) {
				if (!isset($taxes[$product['tax_class_id']])) {
					$taxes[$product['tax_class_id']] = $product['total'] / 100 * $taxObj->getRate($product['tax_class_id']);
				} else {
					$taxes[$product['tax_class_id']] += $product['total'] / 100 * $taxObj->getRate($product['tax_class_id']);
				}
			}
		}

		return $taxes;
  	}

  	public function getTotal() {
		$total = 0;
		$taxObj=new Model_Tax();
		foreach ($this->getProducts() as $product) {
			$total += $taxObj->calculate($product['total'], $product['tax_class_id'], DISPLAY_PRICE_WITH_TAX);
		}

		return $total;
  	}

	public function getTotalRewardPoints() {
		$total = 0;
		foreach ($this->getProducts() as $product) {
			$total += $product['reward'];
		}
 
		return $total;
  	}

  	public function countProducts() {
		$product_total = 0;

		$products = $this->getProducts();

		foreach ($products as $product) {
			//$product_total += $product['quantity'];
			$product_total += $product['quantity'];
		}

		return $product_total;
	}

  	public function hasProducts() {
    	return count($_SESSION['cart']);
  	}

  	public function hasStock() {
		$stock = true;

		foreach ($this->getProducts() as $product) {
			if (!$product['stock']) {
	    		$stock = false;
			}
		}

    	return $stock;
  	}

  	public function hasShipping() {
		$shipping = false;

		foreach ($this->getProducts() as $product) {
	  		if ($product['shipping']) {
	    		$shipping = true;

				break;
	  		}
		}

		return $shipping;
	}

  	public function hasDownload() {
		$download = false;

		foreach ($this->getProducts() as $product) {
	  		if ($product['download']) {
	    		$download = true;

				break;
	  		}
		}

		return $download;
	}

	public function getVoucherDiscount($total,$voucher)
	{
		//$voucher_info=$this->db->fetchRow("select * from r_voucher where code like '".$voucher."'");
		$vouObj=new Model_CheckoutVoucher();
		$voucher_info=$vouObj->getVoucher($voucher);
			$total_percent=@constant('VOUCHER_USAGE_PERCENT');
				$remain_percent=100-@constant('VOUCHER_USAGE_PERCENT');
				if($voucher_info['amount']<($total*$total_percent/100))
				{
					$remain=($total*$total_percent/100)-$voucher_info['amount'];
					$amount=$voucher_info['amount'];
					}else
				{
					$remain=0;
					$amount=($total*$total_percent/100);//$voucher_info['amount']-($total*20/100);
				}
			return $amount;
	}

	public function recentlyViewedProducts($id)
	{
			$_SESSION['rvp'][$id]=$id;
	}
}
?>