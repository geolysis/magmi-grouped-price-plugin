<?php
class GrouppriceProcessor extends Magmi_ItemProcessor
{

	CONST WEBSITES_SEPARATOR = '|';

	protected $_groups = array();
	protected $_singleStore;
	protected $_priceScope;


	public function getPluginInfo()
	{
		$info = array(
			'name' => 'Group Price Importer',
			'author' => 'Tim Bezhashvyly; tweaked by Andreas Gerhards to work with different websites/stores',
			'version' => '1.0.1'
		);
		return $info;
	}


	public function processItemAfterId (&$item, $params = null)
	{
		$table_name = $this->tablename("catalog_product_entity_group_price");
		$groupColumns = array_intersect(array_keys($this->_groups), array_keys($item));

		if (!empty($groupColumns)) {
			$websiteIds = !$this->_singleStore && $this->_priceScope ? $this->getItemWebsites($item) : array(0);
			$groupIds = array();
			foreach ($groupColumns as $key) {
				if ($this->_groups[$key]['id']) {
					$groupIds[] = $this->_groups[$key]['id'];
				}
			}

			if (!empty($groupIds)) {
				// Deletes only data on the new website data and not lecgacy data
				$sql = 'DELETE FROM '.$table_name.' WHERE entity_id = ?'
				 .' AND customer_group_id IN ('.implode(', ', $groupIds).')'
				 .' AND website_id IN ('.implode(', ', $websiteIds).')';
				$this->delete($sql, array($params['product_id']));
			}

			foreach ($groupColumns as $key) {

				$price = explode(self::WEBSITES_SEPARATOR, $item[$key]);
				if (count($price)) {
					$group_id = $this->_groups[$key]['id'];
					$sql = 'INSERT INTO '.$table_name.' (entity_id, all_groups, customer_group_id, value, website_id) VALUES ';
					$inserts = array();
					$data = array();

					foreach ($websiteIds as $key=>$website_id) {
						if ($price[$key]) {
							$inserts[] = '(?,?,?,?,?)';
							$data[] = $params['product_id'];
							$data[] = 0;
							$data[] = $group_id;
							$data[] = (float) str_replace(",", ".", $price[$key]);
							$data[] = $website_id;
						}
					}

					if (!empty($data)) {
						$sql .=implode(', ', $inserts);
						$sql .=' ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)';
						$this->insert($sql, $data);
					}
				}
			}
		}

		return TRUE;
	}


	/**
	 * Inspect column list for group price columns info
	 * @param $columns
	 * @param null $params
	 * @return bool
	 */
	public function processColumnList (&$columns, $params = null)
	{
		foreach ($columns as $column) {

		  if (preg_match("|group_price:(.*)|", $column, $matches)) {
				$sql = 'SELECT customer_group_id FROM '.$this->tablename("customer_group").' WHERE customer_group_code = ?';

				if ($id = $this->selectone($sql, $matches[1], "customer_group_id")) {
					$this->_groups[$column] = array(
						'name'	=> $matches[1],
						'id'	=> $id
					);
				}
			}
		}

		return TRUE;
	}


	public function initialize ($params)
	{
		$sql = 'SELECT COUNT(store_id) as cnt FROM '.$this->tablename('core_store').' WHERE store_id != 0';
		$ns = $this->selectOne($sql, array(), "cnt");
		$this->_singleStore = $ns == 1;

		/* Check price scope in a general config (0 = global, 1 = website) */
		$sql = 'SELECT value FROM '.$this->tablename('core_config_data').' WHERE path = ?';
		$this->_priceScope = intval($this->selectone($sql, array('catalog/price/scope'), 'value'));

		return $this->_priceScope;
	}

}
