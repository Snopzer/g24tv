<?php
class Model_Mail 
{
	
	public function __construct() 
	{

  	}
	  
	public function sendMail($param)
	{
		/*
		array('replyTo'=>array('name'=>'Rcart','email'=>'reply@rcart.com'),
			  'text'=>array('content'=>$content),
			  'html'=>array('content'=>$content),
			  'from'=>array('name'=>'Rcart','email'=>'from@rcart.com'),
		   	  'bcc'=>array(array('name'=>'Rcart','email'=>'from@rcart.com'),array('name'=>'Rcart','email'=>'from@rcart.com')),
			  'cc'=>array(array('name'=>'Rcart','email'=>'from@rcart.com'),array('name'=>'Rcart','email'=>'from@rcart.com')),
			  'to'=>array(array('name'=>'Rcart','email'=>'from@rcart.com'),array('name'=>'Rcart','email'=>'from@rcart.com')),
			  'subject'=>$subject
			 );
		*/
		
		$mail = new Zend_Mail();

		if(REPLY_EMAIL!='')
		{
			Zend_Mail::setDefaultReplyTo(REPLY_EMAIL,STORE_NAME);
        }

		if($param['text']['content']!='')
		{
			$mail->setBodyText($param['text']['content']);
        }

		if($param['html']['content']!='')
		{
			$mail->setBodyHtml($param['html']['content']);
        }
		
		/*ECHO "<pre>";
		print_r($param['bcc']);
		echo "</pre>";*/

		if($param['from']['name']!='' && $param['from']['email']!='')
		{
			$mail->setFrom($param['from']['email'],$param['from']['name']);
		}else
		{   
			$mail->setFrom(EMAIL_FROM, STORE_NAME);
		}
                
        
		if(sizeof($param['bcc'])>0)
		{
			foreach($param['bcc'] as $k=>$v)
			{
				$mail->addBcc($v['email'], $v['name']);	
			}
        }

		if(sizeof($param['to'])>0)
		{
			/*foreach($param['to'] as $k=>$v)
			{
				$mail->addTo($param['to']['email'], $param['to']['name']);	
			}*/
			$mail->addTo($param['to']['email'], $param['to']['name']);	
        }

		if(sizeof($param['cc'])>0)
		{
			foreach($param['cc'] as $k=>$v)
			{
				$mail->addCc($v['email'], $v['name']);	
			}
	    }
		
		//$mail->addTo($data['email'], $data['name']);
		//$mail->addBcc('sureshbabu.kokkonda@gmail.com', 'Suresh');
		//$mail->addTo('suresh.k@rsoftindia.com', 'Suresh');
       	$mail->setSubject($param['subject']);
        
		try {
            $mail->send();
        } catch (Exception $e) {

        }
		//$mail->clearRecipients();//clear all recepients
	}

	public function getEmailContent($param)
	{
		$param['replace']['%store_name%']=@constant('STORE_NAME');
		$param['replace']['%store_url%']=@constant('HTTP_SERVER');//$this->url_to_site;
		$param['replace']['%store_owner%']=@constant('STORE_OWNER');
		$param['replace']['%store_address%']=@constant('STORE_NAME_ADDRESS');
		$param['replace']['%store_telephone%']=@constant('STORE_TELEPHONE');
		$param['replace']['%store_fax%']=@constant('STORE_FAX');
		$param['replace']['%store_email%']=@constant('STORE_OWNER_EMAIL_ADDRESS');
		$param['replace']['%store_from_email%']=@constant('EMAIL_FROM');
		$param['replace']['%store_logo_path%']=@constant('PATH_TO_UPLOADS')."image/".@constant('STORE_LOGO');
		$param['replace']['%store_customer_login_url%']=@constant('HTTP_SERVER').'account/login';
		$param['replace']['%store_customer_registration_url%']=@constant('HTTP_SERVER').'account/register';
		$param['replace']['%store_affiliate_registration_url%']=@constant('HTTP_SERVER').'affiliate/register';
		$param['replace']['%store_affiliate_login_url%']=@constant('HTTP_SERVER').'affiliate/login';
		$param['replace']['%store_cart_url%']=@constant('HTTP_SERVER').'checkout/cart';
		$param['replace']['%store_checkout_url%']=@constant('HTTP_SERVER')."checkout/checkout";
		
		/*exit;*/
		$act_ext=new Model_Adminextaction();
		$email=$act_ext->getEmailContent(array('lang'=>$param['lang'],'id'=>$param['id']));	
		
                //start content
                $exp=explode(",",$email['info']);
				$exp=array_merge($exp,array('%store_name%','%store_url%','%store_owner%','%store_address%','%store_telephone%','%store_fax%','%store_email%','%store_from_email%','%store_logo_path%','%store_customer_registration_url%','%store_affiliate_registration_url%','%store_affiliate_login_url%','%store_cart_url%','%store_checkout_url%'));


		//$replace_array=array('%password%'=>$password);
		$replace_array=$param['replace'];	
		//echo "<pre>";
		//print_r($exp);
		//print_r($replace_array);
        	//$content=nl2br($email['email_template']);
			$content=$email['html']=='1'?html_entity_decode($email['email_template'], ENT_QUOTES, 'UTF-8'):nl2br(strip_tags(html_entity_decode($email['email_template'], ENT_QUOTES, 'UTF-8')));
            $subject=$email['html']=='1'?$email['subject']:nl2br(strip_tags($email['subject']));//nl2br($email['subject']);
		foreach($exp as $k=>$v)
		{
			//echo $v." replace ".$replace_array[$v]."<br/>";
			$content=str_replace($v,$replace_array[$v],$content);
                        $subject=str_replace($v,$replace_array[$v],$subject);
		}
                //end content
                
		$ret_array=array();
		$ret_array['content']=$content;
		$ret_array['subject']=$subject;
	 
		//print_r($ret_array);
		//exit;
		return $ret_array;
	}
}
?>