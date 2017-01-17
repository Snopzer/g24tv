<?php
class Model_Banner{
	public $view=null;
	public function __construct()
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$this->view=new Zend_View();
	}

	public function addBanner($data) {
		$this->db->query("INSERT INTO r_banner SET name = '" . $this->view->escape($data['name']) . "', status = '" . (int)$data['status'] . "'");

		$banner_id = $this->db->lastInsertId();//$this->db->getLastId();

		if (isset($data['banner_image'])) {
			foreach ($data['banner_image'] as $banner_image) {
				$this->db->query("INSERT INTO r_banner_image SET banner_id = '" . (int)$banner_id . "', link = '" .  $this->view->escape($banner_image['link']) . "', image = '" .  $this->view->escape($banner_image['image']) . "'");

				$banner_image_id = $this->db->lastInsertId();//$this->db->getLastId();

				foreach ($banner_image['banner_image_description'] as $language_id => $banner_image_description) {
					$this->db->query("INSERT INTO r_banner_image_description SET banner_image_id = '" . (int)$banner_image_id . "', language_id = '" . (int)$language_id . "', banner_id = '" . (int)$banner_id . "', title = '" .  $this->view->escape($banner_image_description['title']) . "'");
				}
			}
		}
	}

	public function editBanner($banner_id, $data) {
		$this->db->query("UPDATE r_banner SET name = '" . $this->view->escape($data['name']) . "', status = '" . (int)$data['status'] . "' WHERE banner_id = '" . (int)$banner_id . "'");

		$this->db->query("DELETE FROM r_banner_image WHERE banner_id = '" . (int)$banner_id . "'");
		$this->db->query("DELETE FROM r_banner_image_description WHERE banner_id = '" . (int)$banner_id . "'");

		if (isset($data['banner_image'])) {
			foreach ($data['banner_image'] as $banner_image) {
				$this->db->query("INSERT INTO r_banner_image SET banner_id = '" . (int)$banner_id . "', link = '" .  $this->view->escape($banner_image['link']) . "', image = '" .  $this->view->escape($banner_image['image']) . "'");

				$banner_image_id = $this->db->lastInsertId();//$this->db->getLastId();

				foreach ($banner_image['banner_image_description'] as $language_id => $banner_image_description) {
					$this->db->query("INSERT INTO r_banner_image_description SET banner_image_id = '" . (int)$banner_image_id . "', language_id = '" . (int)$language_id . "', banner_id = '" . (int)$banner_id . "', title = '" .  $this->view->escape($banner_image_description['title']) . "'");
				}
			}
		}
	}

	public function deleteBanner($banner_id) {
		$this->db->query("DELETE FROM r_banner WHERE banner_id = '" . (int)$banner_id . "'");
		$this->db->query("DELETE FROM r_banner_image WHERE banner_id = '" . (int)$banner_id . "'");
		$this->db->query("DELETE FROM r_banner_image_description WHERE banner_id = '" . (int)$banner_id . "'");
	}

	public function getBanner($banner_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM r_banner WHERE banner_id = '" . (int)$banner_id . "'");

		//return $query->row;
		return $query->fetchAll();
	}

	public function getBanners($data = array()) {
		$sql = "SELECT * FROM r_banner";

		$sort_data = array(
			'name',
			'status'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		//return $query->rows;
		return $query->fetchAll();
	}

	public function getBannerImages($banner_id) {
		$banner_image_data = array();

		$banner_image_query = $this->db->query("SELECT * FROM r_banner_image WHERE banner_id = '" . (int)$banner_id . "'");
		$banner_image_query->rows=$banner_image_query->fetchAll();

		foreach ($banner_image_query->rows as $banner_image) {
			$banner_image_description_data = array();

			$banner_image_description_query = $this->db->query("SELECT * FROM r_banner_image_description WHERE banner_image_id = '" . (int)$banner_image['banner_image_id'] . "' AND banner_id = '" . (int)$banner_id . "'");
			$banner_image_description_query->rows=$banner_image_description_query->fetchAll();

			foreach ($banner_image_description_query->rows as $banner_image_description) {
				$banner_image_description_data[$banner_image_description['language_id']] = array('title' => $banner_image_description['title']);
			}

			$banner_image_data[] = array(
				'banner_image_description' => $banner_image_description_data,
				'link'                     => $banner_image['link'],
				'image'                    => $banner_image['image']
			);
		}

		return $banner_image_data;
	}

	public function getTotalBanners() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM r_banner");

		//return $query->row['total'];
		return $query->fetchColumn(0);
	}

	public function getSlideShow($banner_id) {
		$banner_data=Model_Cache::getCache(array("id"=>"banner_".$banner_id));
		if(!$banner_data)
		{
			$query = $this->db->query("SELECT * FROM r_banner_image bi LEFT JOIN r_banner_image_description bid ON (bi.banner_image_id  = bid.banner_image_id) WHERE bi.banner_id = '" . (int)$banner_id . "' AND bid.language_id = '" . (int)$_SESSION['Lang']['language_id'] . "'");
			$banner_data=$query->fetchAll();
			Model_Cache::getCache(array("id"=>"banner_".$banner_id,"input"=>$banner_data));
		}
		return $banner_data;
	}
}
?>