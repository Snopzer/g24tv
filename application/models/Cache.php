<?php
  class Model_Cache
  {
	public function __construct()
	{

	}

	public function getLangCache($param="")
	{
		if(!is_object($_SESSION['OBJ']['tr']))
		{
			$lcode=$param->_languageCode;
			//$ldirectory=$param->_languages[$lcode][directory];
			
			$frontendOptions = array(
				'lifetime' => '86400', //7200 cache lifetime of 2 hours
				'automatic_serialization' => true
			);

			$backendOptions = array(
				'cache_dir' => PATH_TO_LANGUAGECACHE // Directory where to put the cache files
			);

			$cache = Zend_Cache::factory('Core',
				'File',
				$frontendOptions,
				$backendOptions);
			// set up translation adapter
			 Zend_Translate::setCache($cache);
			// $cache->clean(Zend_Cache::CLEANING_MODE_ALL);

			//start lang settings
			//if userdefined language file not available in upload file then template default file will be loaded
			if(file_exists(PATH_TO_LANGUAGE.'language.php'))
			//if(file_exists(PATH_TO_LANGUAGE.'language.csv'))
			{
				$path=PATH_TO_LANGUAGE;
			} else
			{
				$path=PATH_TO_TEMPLATES.'/'.SITE_DEFAULT_TEMPLATE.'/language/';
			}
			//end lang settings
			$tr = new Zend_Translate('array', $path, $lcode, array('scan' => Zend_Translate::LOCALE_DIRECTORY));
			//$options = array('delimiter' => ',');
			//$tr = new Zend_Translate('csv',$path.'language.csv','en',$options);
			$_SESSION['OBJ']['tr']=$tr;
			
			/*echo "<pre>";
			print_r($tr);
			echo "</pre>";*/
			//setcookie('tr', '', time() - 3600 , '/');
			//setcookie('lang', '', time() - 3600 , '/');
			//setcookie('tr', $tr, time() + 3600 * 24 * 1, '/');
			//setcookie('lang', $_SESSION['Lang']['language_code'], time() + 3600 * 24 * 1, '/');
		}else
		{
			$tr=$_SESSION['OBJ']['tr'];
		}
		return $tr;
		

	}

	public function getCache($arr)
	{
		$duration=$arr['time']==""?constant('CACHE_LIFE_TIME'):$arr['time'];
		$frontendOptions = array('lifeTime' =>$duration,"automatic_serialization" => true );
		$backendOptions = array('cache_dir' => @constant('PATH_TO_SITECACHE'));

		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);

		if (!$result = $cache->load($arr['id']) ){
			//print_r($arr['input']);
                        /*if($arr['tags']=="") //modified on mar 22 2013 as it is create empty cache file first
                        {
                            //$cache->save($arr['input'], @constant('GLOBAL_DOMAIN_KEY').$arr['id']);
							$cache->save($arr['input'], $arr['id']);
                        }else
                        {
                            //$cache->save($arr['input'], @constant('GLOBAL_DOMAIN_KEY').$arr['id'],$arr['tags']); 
							$cache->save($arr['input'],$arr['id'],$arr['tags']); 
                        }*/
			if($arr['tags']=="" && $arr['input']!="")
			{
				$cache->save($arr['input'], $arr['id']);
			}else if($arr['tags']!="" && $arr['input']!="")
			{
				$cache->save($arr['input'],$arr['id'],$arr['tags']); 
			}
			//return $cache->load(@constant('GLOBAL_DOMAIN_KEY').$arr['id']);
			return $cache->load($arr['id']);
		} else
		{
			//return $cache->load(@constant('GLOBAL_DOMAIN_KEY').$arr['id']);
			return $cache->load($arr['id']);
		}
	}

	public function removeCache($arr)
	{

		$duration=$arr['time']==""?constant('CACHE_LIFE_TIME'):$arr['time'];
		$frontendOptions = array('lifeTime' =>$duration,"automatic_serialization" => true );
		$backendOptions = array('cache_dir' => @constant('PATH_TO_SITECACHE'));

		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
		$cache->remove($arr[id]);
	}

	public function removeAllCache()
	{
		$duration=$arr['time']==""?constant('CACHE_LIFE_TIME'):$arr['time'];
		$frontendOptions = array('lifeTime' =>$duration,"automatic_serialization" => true );
		$backendOptions = array('cache_dir' => @constant('PATH_TO_SITECACHE'));

		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
		$cache->clean(Zend_Cache::CLEANING_MODE_ALL);
	}

	public function removeOldCache()
	{
		$duration=$arr['time']==""?constant('CACHE_LIFE_TIME'):$arr['time'];
		$frontendOptions = array('lifeTime' =>$duration,"automatic_serialization" => true );
		$backendOptions = array('cache_dir' => @constant('PATH_TO_SITECACHE'));

		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
		$cache->clean(Zend_Cache::CLEANING_MODE_OLD);
	}

	public function removeMTCache($arr)
	{
		$duration=$arr['time']==""?constant('CACHE_LIFE_TIME'):$arr['time'];
		$frontendOptions = array('lifeTime' =>$duration,"automatic_serialization" => true );
		$backendOptions = array('cache_dir' => @constant('PATH_TO_SITECACHE'));

		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG,$arr);
	}

	public function removeNMTCache($arr)
	{
		$duration=$arr['time']==""?constant('CACHE_LIFE_TIME'):$arr['time'];
		$frontendOptions = array('lifeTime' =>$duration,"automatic_serialization" => true );
		$backendOptions = array('cache_dir' => @constant('PATH_TO_SITECACHE'));

		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
		$cache->clean(Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,$arr);
	}

	public function removeMATCache($arr)
	{
            //print_r($arr);
            //exit;   
		$duration=$arr['time']==""?constant('CACHE_LIFE_TIME'):$arr['time'];
		$frontendOptions = array('lifeTime' =>$duration,"automatic_serialization" => true );
		$backendOptions = array('cache_dir' => @constant('PATH_TO_SITECACHE'));

		$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
		$cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,$arr);
   
	}
}
?>
