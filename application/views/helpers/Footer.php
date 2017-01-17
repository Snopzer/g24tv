<?php
class Zend_View_Helper_Footer extends Zend_View_Helper_Abstract
{
    public function footer()
    {
		$footer=array();
		$footer['signin_link']=HTTP_SERVER.'account/login';
		$footer['affiliate_signin_link']=HTTP_SERVER.'affiliate/login';
		$footer['affiliate_register_link']=HTTP_SERVER.'affiliate/register';
		$footer['voucher_link']=HTTP_SERVER.'checkout/voucher';
		$footer['register_link']=HTTP_SERVER.'account/register';
		$footer['contactus_link']=HTTP_SERVER.'information/contact';
		$footer['sitemap_link']=HTTP_SERVER.'information/sitemap';
		$footer['specials_link']=HTTP_SERVER.'product/special';
		$footer['privacy_link']=HTTP_SERVER.'information/information/information_id/2';
		$footer['terms_link']=HTTP_SERVER.'information/information/information_id/4';
		$footer['location_link']=HTTP_SERVER.'information/information/information_id/10';
		$footer['protection_link']=HTTP_SERVER.'information/information/information_id/9';
		$footer['team_link']=HTTP_SERVER.'information/information/information_id/8';
		$footer['best_selling_brand_link']=HTTP_SERVER.'information/information/information_id/11';
		$footer['security_link']=HTTP_SERVER.'information/information/information_id/12';
		$footer['aboutus_link']=HTTP_SERVER.'information/information/information_id/1';
		$footer['our_promise_link']=HTTP_SERVER.'information/information/information_id/13';
		return $footer;

    }
}
?>