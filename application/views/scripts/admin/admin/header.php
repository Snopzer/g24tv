<?php error_reporting(0); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<!-- <link href="<?echo PATH_TO_UPLOADS."image/".STORE_FAVI_ICON?>" rel="icon" /> -->
		<link rel="icon" href="<?echo PATH_TO_UPLOADS."image/".STORE_FAVI_ICON?>" type="image/x-icon" />
		<link rel="shortcut icon" href="<?echo PATH_TO_UPLOADS."image/".STORE_FAVI_ICON?>" type="image/x-icon" />
		<title><?echo STORE_META_TITLE;?></title>
		<meta name="description" content="<?echo STORE_META_DESCRIPTION;?>" />
		<meta name="keywords" content="<?echo STORE_META_KEYWORDS;?>">
		
		<?if(@constant('DEFAULT_CURRENCY')=='INR'){?>
			<!--Rupee start  -->
			<script src="http://cdn.webrupee.com/js" type="text/javascript"></script>
			<link rel="stylesheet" type="text/css" href="http://cdn.webrupee.com/font">
			<!--Rupee end  -->
		<?}?>
		
        <!-- CSS Reset -->
		<link href="http://fonts.googleapis.com/css?family=Oswald:400" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="<?php echo PUBLIC_PATH?>js/fckeditor/fckeditor.js" ></script>
        <link rel="stylesheet" type="text/css" href="<?echo PATH_TO_ADMIN_CSS;?>styles.css"  media="screen" />
		
		<!-- JQuery engine script-->
		<script type="text/javascript" src="<?echo PATH_TO_ADMIN_JS;?>jquery-1.3.2.min.js" ></script>
		
		<!-- JQuery thickbox plugin script -->
		<script type="text/javascript" src="<?echo PATH_TO_ADMIN_JS;?>thickbox.js" ></script>
		 
		<?php if($_REQUEST['type']!="" || $this->view_action=='newsletter'){ //required in edit or add pages?>
			<!-- popup stylesheet
			<link rel="stylesheet" type="text/css" href="<?echo PATH_TO_ADMIN_CSS;?>popup.css"  media="screen" /> -->
			<script src="<?echo PATH_TO_ADMIN_JS;?>../SpryAssets/SpryTabbedPanels.js" type="text/javascript"></script>
			<link href="<?echo PATH_TO_ADMIN_CSS;?>../SpryAssets/SpryTabbedPanels.css" rel="stylesheet" type="text/css" />
			
			<!-- start popup field msg popup 
			<link rel="stylesheet" href="<?echo PATH_TO_ADMIN_CSS;?>form-field-tooltip.css" media="screen" type="text/css">-->
			<script type="text/javascript" src="<?echo PATH_TO_ADMIN_JS;?>rounded-corners.js"></script>
			<script type="text/javascript" src="<?echo PATH_TO_ADMIN_JS;?>form-field-tooltip.js"></script>
			<!-- end popup field msg popup -->
		<?php }?>
		
		
		
		<script type="text/javascript">
			$(document).ready(function() {
				
				$("ul#topnav li").hover(function() { //Hover over event on list item
					//$(this).css({ 'background' : '#AAA url(topnav_active.gif) repeat-x'}); //Add background color + image on hovered list item
					$(this).css({ 'background' : '#AAA'}); //Add background color + image on hovered list item
					$(this).find("span").show(); //Show the subnav
					} , function() { //on hover out...
					$(this).css({ 'background' : 'none'}); //Ditch the background
					$(this).find("span").hide(); //Hide the subnav
				});
				
			});
		</script>
		
		
		<script type="text/javascript">
			var PATH_TO_ADMIN_IMAGES= '<?echo PATH_TO_ADMIN_IMAGES;?>';
		</script>
		<script type="text/javascript" src="<?echo PATH_TO_ADMIN_JS;?>validation.js"></script>
		<!--start date  -->
		<link rel="stylesheet" href="<?echo PATH_TO_ADMIN_CSS;?>ui.all.css" type="text/css" media="screen" />
		<script type="text/javascript" src="<?echo PATH_TO_ADMIN_JS;?>jquery.min.js"></script>
		<script type="text/javascript" src="<?echo PATH_TO_ADMIN_JS;?>jquery-ui.min.js"></script>
		<!--end date  -->
		
		<!--start oc  -->
		<script type="text/javascript" src="<?echo PATH_TO_ADMIN_JS;?>webticker_lib.js"></script>
		<!--end oc  -->
		<script type="text/javascript" src="<?php echo $this->url_to_commonfiles;?>js/javascript/jquery/jquery-1.6.1.min.js"></script>
		<script type="text/javascript" src="<?php echo $this->url_to_commonfiles;?>js/javascript/jquery/ui/jquery-ui-1.8.9.custom.min.js"></script>
		<link rel="stylesheet" type="text/css" href="<?php echo $this->url_to_commonfiles;?>js/javascript/jquery/ui/themes/ui-lightness/jquery-ui-1.8.9.custom.css" />
		
		
	</head>
	<body>
		
    	<!-- Header -->
        <div id="header">
            <!-- Header. Status part -->
            <div id="header-status">
                <div class="container_12">
					<div class="top-header">
						<div class="grid_8">
							
						</div>
						
						<div class="top-meanu">
							<ul>
								<li><a class="logout" href="<?php echo @constant('ADMIN_URL_CONTROLLER');?>logout" id="logout">Logout</a></li>
								
								<li><a class="help" href="<?php echo $this->url_to_site."library/Sporanzo_Manual_1.0.pdf"?>" target="_blank">Help</a></li>
								
							</ul>
						</div>
						
					</div>				
					
					
					
				</div>
                <div style="clear:both;"></div>
			</div> <!-- End #header-status -->
			
            <!-- Header. Main part -->
            <div id="header-main">
                <div class="container_12">
                    <div class="grid_12">
                        <div id="logo">
							
							<div class="header"><!--header end-->
								
								<div class="logo-main"><a href="<?echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL')."/home"?>">
									<?php /*?><img src="<?echo PATH_TO_UPLOADS."image/".STORE_LOGO?>" alt="" border="0" /><?php */?>
									<img src="<?php echo PATH_TO_ADMIN_IMAGES;?>Logo-login.png" alt="" width="200" height="30" border="0" />
								</a></div>
								<? $arr_settings=array();
									$st_array=array_merge($_SESSION['arr_mod_files']['settings'],$_SESSION['arr_mod_files']['tools']);
									
									foreach($st_array as $k=>$v)
									{
										$arr_settings[]=$v['file'];
									}
									
								?>
								<div class="right-div">
									<ul>
										<li class="sum-acount"><a class="settings" href="">Settings</a>
											<div class="style">
												<ul>
													<li <?php echo in_array('my-store',$arr_settings)?'':'style="display:none"';?>><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/my-store';?>">My Store</a></li>
													<li <?php echo in_array('payment',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/payment';?>">Payment</a></li>
													<li <?php echo in_array('payment',$arr_settings)?'':'style="display:none"';?>><a href="#" class="sub-line-arrow">Shipping </a>
														<div class="style-sub">
															<ul>
																<li <?php echo in_array('shipping',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/shipping';?>">Shipping</a></li>
																<li  <?php echo in_array('length-class',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/length-class';?>">Length Class</a></li>
																<li  <?php echo in_array('weight-class',$arr_settings)?'':'style="display:none"';?>><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/weight-class';?>">Weight Class</a></li>
															</ul>
														</div>
													</li>
													<li <?php echo in_array('payment',$arr_settings)?'':'style="display:none"';?>><a href="#" class="sub-line-arrow">Tax </a>
														<div class="style-sub">
															<ul>
																<li  <?php echo in_array('taxclass',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/taxclass';?>">Tax Class</a></li>
																<li  <?php echo in_array('taxzones',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/taxzones';?>">Tax Zones</a></li>
															</ul>
														</div>
													</li>
													<li  <?php echo in_array('modules',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/modules';?>">Modules</a></li>
													<li  <?php echo in_array('order-total',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/order-total';?>">Order Total</a></li>
													<li <?php echo in_array('payment',$arr_settings)?'':'style="display:none"';?>><a href="#" class="sub-line-arrow" >Localisation</a>
														<div class="style-sub">
															<ul>
																<li  <?php echo in_array('countries',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/countries';?>">Countries</a></li>
																<li  <?php echo in_array('zones',$arr_settings)?'':'style="display:none"';?>><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/zones';?>">Zones</a></li>
																<li  <?php echo in_array('currency',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/currency';?>">Currencies</a></li>
															</ul>
														</div>
													</li>
													
													
												</ul>
											</div>
											
										</li>
										<li class="sum-acount"><a class="tools" href="">Tools</a>
											<div class="style">
												<ul>
													<li <?php echo in_array('export',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/export';?>">Import & Export</a></li>
													<!-- <li><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/database-backup';?>">Database Backup</a></li>
														--><li><a href="#" class="sub-line-arrow">Newsletter </a>
														<div class="style-sub" <? array_intersect($arr_settings, array('newslettertemplate', 'newsletter'))?'':'style="display:none"'; ?>>
															<ul>
																<li <?php echo in_array('newslettertemplate',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/newslettertemplate';?>">Newsletter Template</a></li>
																<li <?php echo in_array('newsletter',$arr_settings)?'':'style="display:none"';?> ><a href="<?php echo $this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/newsletter';?>">Send Newsletter</a></li>
															</ul>
														</div>
													</li>
												</ul>
											</div>
										</li>
									</ul>
								</div>
							<!--header end--></div>
							
							<?//<!-- "search-terms-report"=>'<a href="'.$this->url_to_site.'admin/search-terms-report">Search Terms</a> |', -->
								$links=array(
								"search-terms-report"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/search-terms-report">Search Terms</a>',
								"categories"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/categories">Categories</a> ',
								"products"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/products">Articles</a> ',
								"attribute"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/attribute">Attributes</a> ',
								"attributegroup"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/attributegroup">Attribute Groups</a> ',
								"option"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/option">Options</a> ',
								"manufacturer"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/manufacturer">Manufacturer</a> ',
								"review"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/review">Reviews</a> ',
								"page"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/page">Pages</a> ',
								"coupon"=>' <a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/coupon">Coupon</a> ',
								"gift-voucher"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/gift-voucher">Gift Vouchers</a> ',
								"gift-voucher-theme"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/gift-voucher-theme">Gift Vouchers Theme</a> ',
								"affiliate"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/affiliate">Affiliates</a> ',
								"downloads"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/downloads">Downloads</a>',
								"customers"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/customers">Customers</a> ',
								"customergroup"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/customergroup">Customer Groups</a> ',
								"orders"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/orders">Orders</a> ',
								"products-returned"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/products-returned">Returns</a> ',
								"sales-report"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/sales-report">Sales</a> ',
								"products-viewed"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/products-viewed">Products Viewed</a> ',
								"products-purchased"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/products-purchased">Products Purchased</a> ',
								"tax-report"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/tax-report">Tax</a> ',
								"returns-report"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/returns-report">Returns</a> ',
								"coupons-report"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/coupons-report">Coupons</a> ',
								"products-download-report"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/products-download-report">Products Download</a> ',
								"customers-by-order-total-report"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/customers-by-order-total-report">Customers by order total</a> ',
								"product-reviews-report"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/product-reviews-report">Product reviews</a> ',
								"shipping-report"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/shipping-report">Shipping</a> ',
								"reward-points-report"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/reward-points-report">Reward Points</a> ',
								"affiliate-report"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/affiliate-report">Affiliate</a>',
								"return-status"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/return-status">Return Status</a> ',
								"return-action"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/return-action">Return Action</a> ',
								"return-reason"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/return-reason">Return Reason</a> ',
								"themes"=>'	<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/themes">Themes</a> ',
								"side-banner"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/side-banner">Banners</a> ',
								"email-templates"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/email-templates">Email Templates</a> ',
								"alert"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/alert">Alerts</a> ',
								"order-status"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/order-status">Order Status</a> ',
								"stock-status"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/stock-status">Stock Status</a> ',
								"administrator"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/administrator">Administrator</a> ',
								"admin-role"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/admin-role">Admin Roles</a> ',
								"admin-log-history"=>'<a href="'.$this->url_to_site.@constant('ADMIN_FRIENDLY_URL').'/admin-log-history">Admin Log History</a>');
								
								switch($this->actionTitle)
								{
									case 'categories':
									case 'attribute':
									case 'attributegroup':
									case 'option':
									case 'manufacturer':
									case 'review':
									case 'downloads':
									case 'products':
									$array_tab=array("categories"=>"categories","attribute"=>"attributes","attributegroup"=>"attribute groups","option"=>"options","manufacturer"=>"manufacturer","review"=>"reviews","downloads"=>"downloads","products"=>"Articles");
									$catalog=$array_tab;
									break;
									
									case 'customers':
									case 'customergroup':
									$array_tab=array("customers"=>"customers","customergroup"=>"customer groups");
									$customers=$array_tab;
									break;
									
									case 'return-action':
									case 'return-status':
									case 'return-reason':
									case 'stock-status':
									case 'order-status':
									case 'orders':
									case 'products-returned':
									$array_tab=array("return-action"=>"return action","return-status"=>"return stats","return-reason"=>"return reason","stock-status"=>"stock status","order-status"=>"order status","orders"=>"orders","products-returned"=>"returns");
									$orders=$array_tab;
									break;
									
									
									case 'sales-report':
									case 'search-terms-report':
									case 'products-viewed':
									case 'reward-points-report':
									case 'shipping-report':
									case 'product-reviews-report':
									case 'customers-by-order-total-report':
									case 'products-download-report':
									case 'affiliate-report':
									case 'products-purchased':
									case 'tax-report':
									case 'returns-report':
									case 'coupons-report':	
									$array_tab=array("sales-report"=>"sales","search-terms-report"=>"search terms","products-viewed"=>"products viewed","reward-points-report"=>"reward points","shipping-report"=>"shipping","product-reviews-report"=>"product reviews","customers-by-order-total-report"=>"customers by order total","products-download-report"=>"products download","affiliate-report"=>"affiliate","products-purchased"=>"products purchased","tax-report"=>"tax","returns-report"=>"returns","coupons-report"=>"coupons");
									$reports=$array_tab;
									
									break;
									
									case 'administrator':
									case 'admin-log-history':
									case 'admin-role':
									$array_tab=array("administrator"=>"administrator","admin-log-history"=>"admin log history","admin-role"=>"admin roles");
									$admin=$array_tab;
									break;
									
									case 'my-store':
									case 'payment':
									//case 'shipping':
									case 'modules':
									case 'order-total':
									$array_tab=array("my-store"=>"My Store","payment"=>"payment","shipping"=>"shipping","modules"=>"modules","order-total"=>"order total");
									$settings=$array_tab;
									
									break;
									
									case 'export':
									//case 'database-backup':
									
									//$array_tab=array("export"=>"import & export","database-backup"=>"Database Backup");
									$array_tab=array("export"=>"import & export");
									$tools=$array_tab;
									
									break;
									
									case 'newsletter':
									case 'newslettertemplate':
									$array_tab=array("newsletter"=>"newsletter","newslettertemplate"=>"Newsletter Template");
									$tools=$array_tab;
									//exit;
									break;
									
									case 'shipping':
									case 'weight-class':
									case 'length-class':
									$array_tab=array("shipping"=>"shipping","weight-class"=>"Weight Class","length-class"=>"Length Class");
									$settings=$array_tab;
									break;
									
									case 'taxclass':
									case 'taxzones':
									
									$array_tab=array("taxclass"=>"Tax Class","taxzones"=>"Tax Zones");
									$settings=$array_tab;
									break;
									
									case 'countries':
									case 'zones':
									case 'currency':
									//case 'language':
									//$array_tab=array("countries"=>"Countries","zones"=>"zones","currency"=>"currency","language"=>"language");
									$array_tab=array("countries"=>"Countries","zones"=>"zones","currency"=>"currency");
									$settings=$array_tab;
									break;
									
									
									case 'themes':
									case 'page':
									case 'side-banner':
									case 'banner':
									case 'email-templates':
									$array_tab=array("themes"=>"themes","page"=>"cms","side-banner"=>"banners","email-templates"=>"email templates");
									$design=$array_tab;
									break;
									
									case 'coupon':
									case 'gift-voucher':
									case 'gift-voucher-theme':
									case 'affiliate':
									$array_tab=array("coupon"=>"coupon","gift-voucher-theme"=>"gift vouchers theme","gift-voucher"=>"gift vouchers","affiliate"=>"affiliates");
									$featured=$array_tab;
									break;
								}
								
								/*echo "<pre>";
									print_r($reports);
								echo "</pre>"*/
							?>
							
							<!-- End. #Logo -->
						</div><!-- End. .grid_12-->
						
					</div><!-- End. .container_12 -->
				</div> <!-- End #header-main -->
				
				
			</div><!-- End. .container_12 -->
			
			
			<div class="main-menu"><!--main-menu srart-->
				<div class="main-menu-div">
					<div class="container_12">
						
						<div class="menu"><!--menu srart-->
							
							<div class="meanu-left"><!--menu-left srart-->
								<ul >
									<li ><a class="<?echo $classa=$this->actionTitle==""?"active-list":"";?>"  href="<?=$this->url_to_site.@constant('ADMIN_FRIENDLY_URL')?>/home"  >Dashboard</a></li>
									
									<?
										//echo "value of ".in_array('newslettertemplatesss',$_SESSION['arr_mod_files']['tools']);
										
										foreach ($_SESSION['arr_mod_files'] as $k=>$v)
										{
											//echo $k." ".$v;
											//exit;
											if($k=='settings' || $k=='tools' || $k=='home' )
											{
												continue;
											}
											/*echo $k;
												
												exit;
												echo $this->actionTitle."<pre>";
												print_r(array_keys($$k));
											echo "</pre>";*/
											//exit;*/
											/*echo "<pre>";
												print_r(${$k});
												echo "</pre>";
											echo $k."<br/>";*/
											//$class1=strtolower($main_cat)==strtolower($k)?"active-list":"";
											$class1=@in_array($this->actionTitle,array_keys($$k))?"active-list":"";
											
											//echo "<br/>".$class;
											echo '<li>
											<a href="#"  class="'.$class1.'" ><span>'.ucfirst($k).'</span></a>';
											$sizeofk=sizeof($_SESSION['arr_mod_files'][$k]);
											if($sizeofk>0)
											{
												echo '<div class="style">';
												echo '<ul>';
												
												for($i=0;$i<$sizeofk;$i++)
												{
													echo '<li>';
													echo $links[$_SESSION['arr_mod_files'][$k][$i]['file']];
													echo '</li>';
												}
												
												echo '</ul>';
												echo '</div>';
											}
											echo '</li>'; 
										}
										//echo '<pre>';
										//print_r($_SESSION['arr_mod_files']);
										//exit;
										//print_r($_SESSION['arr_files_per']);
									?>
									
								</ul><!--menu-left end--></div>
								
								
								<div class="meanu-right"><!--meanu-right start-->
									<div class="view-store"><a href="<?php echo HTTP_SERVER;?>"  target="_blank" ></a></div>
									<div class="search-top">
										<input type="text" onblur="if(this.value=='') this.value='Go To Page'" onfocus="if(this.value=='Go To Page') this.value=''"  class="search-icon" value="Go To Page" id="search_page" name="search_page">
									<div class="search-btn"><a href=""></a></div></div>
									
								<!--meanu-right end--></div>
						</div>
					<!--menu end--></div>
				</div>
			<!--main-menu end--></div>
			
			
		</div> <!-- End #subnav -->
	</div> <!-- End #header -->
</div>



<!-- <div style="margin-left:5%;" class="link-list">
	<p><?echo $main_cat;?> <samp>>></samp> <?echo str_replace("|","",$links[$this->actionTitle])?></p>
</div> -->

<div class="main-body-wapper">
	<div class="container_12">
		
		
		
        
		<?
			if($_REQUEST['type']=="" && $this->actionTitle!=""  && $this->actionTitle!="getting-started"){?>
			<div class="tabs-div"><!--tabs-div start-->
				<ul>
					<?
						if(is_array($array_tab) && sizeof($array_tab)>0)
						{
							foreach($array_tab as $k=>$v)
							{
								if(in_array($k,$_SESSION['arr_access_files']))
								{
									$class=$k==$this->actionTitle?'hover-e':'';
									echo '<li><a class="'.$class.'" href="'.$k.'">'.ucfirst($v).'</a></li>';
								}
							}
						}
					?>
					
					
				</ul>
			<!--tabs-div end--></div>
		<?}?>
		<?if($this->actionTitle=="getting-started" || $this->actionTitle=="")
			{
			?>
			<div class="tabs-div">
				<ul>
					<?php $class2="getting-started"==$this->actionTitle?'hover-e':'';?>
					<?php $class1=$this->actionTitle==""?'hover-e':'';?>
					<li><a class="<?php echo $class1?>" href="<?php echo ADMIN_URL_CONTROLLER?>home">Dashboard</a></li>
					<!--<li><a class="<?php echo $class2?>" href="<?php echo ADMIN_URL_CONTROLLER."getting-started"?>">Getting Started</a></li>-->
				</ul>
			</div>
		<?}?>
		
		
		
		
	</div>
	<?
		/*echo "<pre>";
			print_r($_SESSION['arr_access_files']);
		echo "</pre>";*/
	?>		