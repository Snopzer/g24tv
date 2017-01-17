<?php
class Model_Template {
 	public $db;
	public function __construct() 
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
	}

	public function getTemplateText()
	{
		if($_SESSION['text']['in']!='true')
		{
			$_SESSION['text']['in']="true";
			$this->db = Zend_Db_Table::getDefaultAdapter();
			$row=$this->db->fetchRow("select count(*) as count from r_template");
			if($row['count']=='0')
			{
				$template=file(PATH_TO_TEMPLATES."/".@constant('SITE_DEFAULT_TEMPLATE')."/template.txt");		
				/*echo "<pre>";
				print_r($template);
				echo "</pre>";*/
				foreach($template as $k=>$v)
				{
					$arr=explode("==",$v);
					//echo substr($arr[0],-4,4)."<br/>";
					if(substr($arr[0],-4,4)=='Text')
					{
						$_SESSION['text'][$arr[0]]=$arr[1];
					}
				}
				/*echo "<pre>";
				print_r($_SESSION['text']);
				echo "</pre>";
				exit("from file");*/
			}else
			{
				$rows=$this->db->fetchAll("select * from r_template where `key` like '%_Text'");
				foreach($rows as $v=>$k)
				{
					$_SESSION['text'][$k['key']]=$k['value'];
				}

		/*echo "<pre>";
		print_r($_SESSION['text']);
		//print_r($rows);
		echo "</pre>";
		exit("from db");*/
			}
		}
	
	}


}
?>