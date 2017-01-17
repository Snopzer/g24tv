<?php
  class Model_Languages
  {
	public $_languageId=null;
	public $_languageCode=null;
	public $_languageDirectory=null;
	public $_languages;
	public function __construct($lng='')
	{
		$this->db = Zend_Db_Table::getDefaultAdapter();
		$result = $this->db->fetchAll("select * from r_languages where status='1'");
		$arrLang=array();
		$this->_languages=Model_Cache::getCache(array("id"=>"langauge"));
		if(!$this->_languages)
		{
			foreach($result as $k)
			{
				$arrLang[$k[code]][languages_id]=$k[languages_id];
				$arrLang[$k[code]][name]=$k[name];
				$arrLang[$k[code]][code]=$k[code];
				$arrLang[$k[code]][image]=$k[image];
				$arrLang[$k[code]][directory]=$k[directory];
				$arrLang[$k[code]][sort_order]=$k[sort_order];
			}
			$this->_languages=Model_Cache::getCache(array("id"=>"langauge","input"=>$arrLang));
		}
		$arrLang=$this->_languages;
		/*ECHO "<pre>";
		print_r($arrLang);
		echo "</pre>";*/
		$lang=new Zend_Session_Namespace('Lang');
		$lang->languages=$arrLang;
		if(!isset($lang->language_id))  //at first set to default language
			{
 			$this->_languageId=$arrLang[DEFAULT_LANGUAGE][languages_id];
			$this->_languageCode=DEFAULT_LANGUAGE;

			$lang->language_id=$this->_languageId;
			$lang->language_code=DEFAULT_LANGUAGE;
			$lang->language_directory=$arrLang[DEFAULT_LANGUAGE][directory];
		}else   //at next set to selected language
		{
			if($lng=="")
			{
				$this->_languageId=$lang->language_id;
				$this->_languageCode=$lang->language_code;
				$this->_languageDirectory=$lang->language_directory;
			}else
			{
				Model_Cache::removeAllCache();
				//echo "in language change";
				//exit;
				$this->setLanguage($lng);

			}

		}
	}

	public function setLanguage($slang) //set session languageid and languagecode to seleceted language
	{
		$lang=new Zend_Session_Namespace('Lang');
		$lang->language_id=$this->_languages[$slang][languages_id];
		$lang->language_code=$slang;
		$this->_languageId=$lang->language_id;
		$this->_languageCode=$lang->language_code;
		$this->_languageDirectory=$lang->language_directory;
		header("location:".$_SERVER['HTTP_REFERER']);
	}

}
?>
