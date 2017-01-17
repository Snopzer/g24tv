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
class Model_DbTable_rEmailFormat extends Zend_Db_Table_Abstract
{
    /** Table name */
    protected $_name = 'r_email_format';
	protected $_id = 'email_format_id';

	public function getEmailFormat($id,$lang)
	{
		return $this->getAdapter()->fetchAll("select * from r_email_format where email_format_id='".$id."' and language_id='".$lang."'");
	}

}
