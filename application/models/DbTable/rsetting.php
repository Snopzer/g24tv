<?php

/**
 * Artilces Model
 *
 * @version v0.1
 * @category   Zend
 * @url http://www.rsoftindia.com
 * @package    Model_DbTable_raddressbook
 * @author     Suresh babu k
 */
class Model_DbTable_rsetting extends Zend_Db_Table_Abstract
{
    /** Table name */
    protected $_name = 'r_setting';
	protected $_id = 'setting_id';

	function getSetting()
	{
		$rconfig=new Model_DbTable_rsetting();
		$con=$rconfig->fetchAll();
		foreach ($con as $results)
		{
			define($results['key'],$results['value']);
		}
		//echo MODULE_SHIPPING_INSTALLED;
	}

	function returnSetting()
	{
		$rconfig=new Model_DbTable_rsetting();
		$con=$rconfig->fetchAll();
		return $con;	
		//echo MODULE_SHIPPING_INSTALLED;
	}
}
