<?php
class Model_Review {
	public $db;
	public $_date;
	public function Model_Review()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$this->_date=date('Y-m-d H:i:s');
	}

	public function addReview($product_id, $data) {
		$custObj=new Model_Customer();
		$this->db->query("INSERT INTO r_reviews SET customers_name = '" . stripslashes($data['name']) . "', customers_id = '" . (int)$custObj->getId() . "', products_id = '" . (int)$product_id . "', reviews_text = '" . stripslashes(strip_tags($data['text'])) . "', reviews_rating = '" . (int)$data['rating'] . "', date_added = '".$this->_date."'");

	}

	public function getReviewsByProductId($product_id, $start = 0, $limit = 20) 
	{
		$query = $this->db->query("SELECT r.reviews_id, r.customers_name, r.reviews_rating, r.reviews_text, p.products_id, pd.products_name, p.products_price, p.products_image, r.date_added FROM r_reviews r LEFT JOIN r_products p ON (r.products_id = p.products_id) LEFT JOIN r_products_description pd ON (p.products_id = pd.products_id) WHERE p.products_id = '" . (int)$product_id . "' AND p.products_date_available <= '".$this->_date."' AND p.products_status = '1' AND r.reviews_status = '1' AND pd.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);
		$query->rows=$query->fetchAll();
		return $query->rows;
	}

	public function getAverageRating($product_id) {
		$query = $this->db->query("SELECT AVG(rating) AS total FROM r_reviews WHERE status = '1' AND products_id = '" . (int)$product_id . "' GROUP BY products_id");
		$query->row=$query->fetch();
		if (isset($query->row['total'])) {
			return (int)$query->row['total'];
		} else {
			return 0;
		}
	}

	public function getTotalReviews() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM r_reviews r LEFT JOIN r_product p ON (r.products_id = p.products_id) WHERE p.date_available <= '".$this->_date."' AND p.status = '1' AND r.status = '1'");
		return $query->fetchColumn(0);
		//return $query->row['total'];
	}

	public function getTotalReviewsByProductId($product_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM r_reviews r LEFT JOIN r_products p ON (r.products_id = p.products_id) LEFT JOIN r_products_description pd ON (p.products_id = pd.products_id) WHERE p.products_id = '" . (int)$product_id . "' AND p.products_date_available <= '".$this->_date."' AND p.products_status = '1' AND r.reviews_status = '1' AND pd.language_id = '" . (int)$_SESSION['Lang']['language_id']. "'");

		//$rows=$query->fetch();
		//return $rows['total'];
		return $query->fetchColumn(0);
	}
}
?>