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
class Model_DbTable_rproducts extends Zend_Db_Table_Abstract 
{
    /** Table name */
    protected $_name = 'r_products';
	protected $_id = 'products_id';
   protected $_dependentTables = array('r_products_description');
}
