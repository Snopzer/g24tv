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
class Model_DbTable_rconfiguration extends Zend_Db_Table_Abstract 
{
    /** Table name */
    protected $_name = 'r_configuration';
	protected $_id = 'configuration_id';

	function getConfiguration()
	{
            $con=Model_Cache::getCache(array("id"=>"conf","time"=>"7200"));
            if(!$con)
            {
                $rconfig=new Model_DbTable_rconfiguration();
                $con=$rconfig->fetchAll();
                Model_Cache::getCache(array("id"=>"conf","input"=>$con,"time"=>"3600"));
            }
            foreach ($con as $results) 
            {
                    define($results['configuration_key'],$results['configuration_value']);
            }	
       }

	function returnConfiguration()
	{
		$rconfig=new Model_DbTable_rconfiguration();
		$con=$rconfig->fetchAll();
		return $con;
	}
}
