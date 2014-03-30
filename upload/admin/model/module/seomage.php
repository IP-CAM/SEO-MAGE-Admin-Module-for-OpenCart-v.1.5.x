<?php
class ModelModuleSeomage extends Model {

	public function getCategoryByProduct($product_id, $language) {
		$query = $this->db->query('SELECT `category_id` FROM ' . DB_PREFIX . 'product_to_category WHERE product_id = "'.(int)$product_id.'"');
		if (isset($query->rows[0]['category_id'])) {
			$category_id = $query->rows[0]['category_id'];

			$query = $this->db->query('SELECT `name` FROM ' . DB_PREFIX . 'category_description WHERE category_id = "'.(int)$category_id.'" AND language_id = "'.(int)$language.'"');
			return $query->rows;			
		}
	}

	public function getAllKeywords() {
		$result = array(
			"seomage" => array(),
			"default" => array()
			);

		$query = $this->db->query('SELECT `keyword` FROM ' . DB_PREFIX . 'seomage');
		foreach ($query->rows as $key => $value) {
			$result['seomage'][] = $value['keyword'];
		}

		$query = $this->db->query('SELECT `keyword` FROM ' . DB_PREFIX . 'url_alias');
		foreach ($query->rows as $key => $value) {
			$result['default'][] = $value['keyword'];
		}

		return $result;
	}

	public function deleteProductURL($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id. "'");
	}
	
	public function setProductURL($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$data['id'] . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
	}

	public function deleteCategoryURL($category_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'category_id=" . (int)$category_id. "'");
	}
	
	public function setCategoryURL($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'category_id=" . (int)$data['id'] . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
	}

	public function deleteManufacturerURL($manufacturer_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id. "'");
	}
	
	public function setManufacturerURL($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'manufacturer_id=" . (int)$data['id'] . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
	}

	public function deleteInformationURL($information_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'information_id=" . (int)$information_id. "'");
	}
	
	public function setInformationURL($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'information_id=" . (int)$data['id'] . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
	}

	public function getKeywords() {
		$query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'seomage');
		return $query->rows;
	}	

	public function saveKeywords($data) {
		$this->db->query('TRUNCATE TABLE ' . DB_PREFIX . 'seomage');
		foreach ($data as $key => $value) {
			$this->db->query('INSERT INTO ' . DB_PREFIX . 'seomage SET query = "' . $this->db->escape($value['query']) . '", keyword = "' . $this->db->escape($value['keyword']) . '"');
		}
	}

	public function clearKeywords() {
		$this->db->query('TRUNCATE TABLE ' . DB_PREFIX . 'seomage');
	}

	public function clearLog() {
		$this->db->query('TRUNCATE TABLE ' . DB_PREFIX . 'seomage_log');
	}

	public function getLogs() {
		$query = $this->db->query('SELECT * FROM ' . DB_PREFIX . 'seomage_log');
		return $query->rows;
	}	

	public function install() {
		$query = 'CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'seomage_log` (' . 
  				'`id` int(11) NOT NULL AUTO_INCREMENT,' .
  				'`message` varchar(255) NOT NULL,' .
  				'PRIMARY KEY (`id`)' .
				') ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
		$this->db->query($query);

		$query = 'CREATE TABLE IF NOT EXISTS `' . DB_PREFIX . 'seomage` (' . 
  				'`url_alias_id` int(11) NOT NULL AUTO_INCREMENT,' .
  				'`query` varchar(255) NOT NULL,' .
  				'`keyword` varchar(255) NOT NULL,' .
  				'PRIMARY KEY (`url_alias_id`)' .
				') ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
		$this->db->query($query);

		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "common/home", keyword = ""');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/login", keyword = "login"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/logout", keyword = "logout"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/register", keyword = "register"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/account", keyword = "account"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/forgotten", keyword = "password_forgotten"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/edit", keyword = "account/edit"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/password", keyword = "account/password"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/address", keyword = "account/address"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/wishlist", keyword = "account/wishlist"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/order", keyword = "account/order"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/transaction", keyword = "account/transaction"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/newsletter", keyword = "account/newsletter"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/order/info", keyword = "account/order/info"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "checkout/cart", keyword = "cart"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "checkout/checkout", keyword = "checkout"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "checkout/success", keyword = "checkout_success"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "information/contact", keyword = "information/contact"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "information/sitemap", keyword = "information/sitemap"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "product/manufacturer", keyword = "manufacturers"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "account/voucher", keyword = "account/voucher"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "affiliate/account", keyword = "affiliate/account"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "product/special", keyword = "promo"');
		$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "module/currency", keyword = "module/currency"');
		//$this->db->query('INSERT INTO `' . DB_PREFIX . 'seomage` SET query = "", keyword = ""');
	}

	public function uninstall() {
		$this->db->query('DROP TABLE `' . DB_PREFIX . 'seomage`');
		$this->db->query('DROP TABLE `' . DB_PREFIX . 'seomage_log`');
	}
}
?>