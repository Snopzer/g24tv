$(document).ready(function() {
	// Search 
	 $('.search-button').bind('click', function() {
			url = HTTP_SERVER+'product/search';
			
			var filter_name1 = $('input[name=\'q\']').attr('value')
			
			var filter_name2;
			if($('select[name=\'dd\']').attr('value')==undefined)
			{
				 filter_name2=0;
			}
			else
			{
				filter_name2 = $('select[name=\'dd\']').attr('value')
			}
			 			if (filter_name1) {
				url += '/q/' + encodeURIComponent(filter_name1)+'/dd/'+encodeURIComponent(filter_name2);
			}
			
			window.location.href = url;
		}); 
 
	
	$('input[name=\'q\']').keydown(function(e) {
		if (e.keyCode == 13) {
			//http://sun-network/mve_org_new/product/search/q/dell/dd/0
			url = HTTP_SERVER+'product/search';
			
			var filter_name1 = $('input[name=\'q\']').attr('value')
			//var filter_name2 = $('select[name=\'dd\']').attr('value')
						var filter_name2;
			//alert($('select[name=\'dd\']').attr('value'))
			if($('select[name=\'dd\']').attr('value')==undefined)
			{
				//alert("in")
				 filter_name2=0;
			}
			else
			{
				//alert("in else")
				filter_name2 = $('select[name=\'dd\']').attr('value')
			}
			
			if (filter_name1) {
				url += '/q/' + encodeURIComponent(filter_name1)+'/dd/'+encodeURIComponent(filter_name2);
			}

			window.location.href = url;
		}
	});
	
 // Ajax Cart  
	$('#cart > .heading a').bind('click', function() {
		$('#cart').addClass('active');
		
		$.ajax({
	//		url: 'index.php?route=checkout/cart/update',
			url: HTTP_SERVER+'ajax/cartupdate',
			//url: HTTP_SERVER+'checkout/cartupdate',
			dataType: 'json',
				beforeSend: function() {
 			$('.heading').before('<span class="wait">&nbsp;<img src="'+HTTP_SERVER_TEMPLATE+'/includes/images/loading.gif" alt="" /></span>');
		},
			complete: function() {
 			$('.wait').remove();
		},
			success: function(json) {
				if (json['output']) {
					$('#cart .content').html(json['output']);
				}
			}
		});			
		
		$('#cart').bind('mouseleave', function() {
			$(this).removeClass('active');
		});
	});

	/*	//product compare side block
			$.ajax({
			url: HTTP_SERVER+'ajax/ajaxcompareupdate',
			dataType: 'json',
					success: function(json) {
				if (json['output']) {
					$('#prod-compare').html(json['output']);
				}
			}
		});*/
	
	// Mega Menu  
	$('#menu ul > li > a + div').each(function(index, element) {
		// IE6 & IE7 Fixes
		if ($.browser.msie && ($.browser.version == 7 || $.browser.version == 6)) {
			var category = $(element).find('a');
			var columns = $(element).find('ul').length;
			
			$(element).css('width', (columns * 143) + 'px');
			$(element).find('ul').css('float', 'left');
		}		
		
		var menu = $('#menu').offset();
		var dropdown = $(this).parent().offset();
		
		i = (dropdown.left + $(this).outerWidth()) - (menu.left + $('#menu').outerWidth());
		
		if (i > 0) {
			$(this).css('margin-left', '-' + (i + 5) + 'px');
		}
	});

	// IE6 & IE7 Fixes
	if ($.browser.msie) {
		if ($.browser.version <= 6) {
			$('#column-left + #column-right + #content, #column-left + #content').css('margin-left', '195px');
			
			$('#column-right + #content').css('margin-right', '195px');
		
			$('.box-category ul li a.active + ul').css('display', 'block');	
		}
		
		if ($.browser.version <= 7) {
			$('#menu > ul > li').bind('mouseover', function() {
				$(this).addClass('active');
			});
				
			$('#menu > ul > li').bind('mouseout', function() {
				$(this).removeClass('active');
			});	
		}
	}
});
$('.success img, .warning img, .attention img, .information img').live('click', function() {
	$(this).parent().fadeOut('slow', function() {
		$(this).remove();
	});
});


function addToCart(product_id) {
	$('#cart').addClass('active');
	$.ajax({
	//	url: 'index.php?route=checkout/cart/update',
		//url: HTTP_SERVER+'checkout/cartupdate',
		url: HTTP_SERVER+'ajax/cartupdate',

		type: 'post',
		data: 'product_id=' + product_id,
		dataType: 'json',
			beforeSend: function() {
			 
			//$('.main-wraper').append("<div class='loading-div'><img src='"+HTTP_SERVER+"library/CommonFiles/image/ajax-loader.gif' id='loading' style='padding-left: 5px;' /></div>");
			$('.main-wraper').append("<div class='loading-div'><div>"+JAVASCRIPT_TEXT_ADDTOCART_POPUP+"</div></div>");
			 
			
		},
		complete:function(json)
		{
			$('.loading-div').remove();
		},
		success: function(json) {
			$('.success, .warning, .attention, .information, .error').remove();
 			if (json['redirect']) {
				location = json['redirect'];
			}
			
			if (json['error']) {
				if (json['error']['warning']) {
					$('#notification').html('<div class="warning" style="display: none;">' + json['error']['warning'] + '<img src="../images/close.png" alt="" class="close" /></div>');
				}
			}	 
						
			if (json['success']) {
				if(JAVASCRIPT_ADD_TO_CART_REDIRECTION=='false')
				{
					
					$('#notification').html('<div class="attention" style="display: none;">' + json['success'] + '<img src="'+json['close_url']+'" alt="" class="close" /></div>');
					
					$('.attention').fadeIn('slow');
					//$('.heading').fadeIn('slow');
					
					$('#cart_total').html(json['total']);
					
					$('html, body').animate({ scrollTop: 0 }, 'slow'); 
					
					$('#cart_block').html(json['output']); //enable when side block is used
					$('#cart .content').html(json['output']);
				}else
				{
					location=HTTP_SERVER+'checkout/cart';
				}
			}	
		}
	});
}

function removeCart(key) {
	$.ajax({
		//url: 'index.php?route=checkout/cart/update',
		//url: HTTP_SERVER+'checkout/cartupdate',
		url: HTTP_SERVER+'ajax/cartupdate',

		type: 'post',
		data: 'remove=' + key,
		dataType: 'json',
		success: function(json) {
			$('.success, .warning, .attention, .information').remove();
			
			if (json['output']) {
				$('#cart_total').html(json['total']);
				
				$('#cart .content').html(json['output']);
				$('#cart_block').html(json['output']);//enable when side block is used
			}			
		}
	});
}

function removeVoucher(key) {
	$.ajax({
		//url: 'index.php?route=checkout/cart/update',
		//url: HTTP_SERVER+'checkout/cartupdate',
		url: HTTP_SERVER+'ajax/cartupdate',

		type: 'post',
		data: 'voucher=' + key,
		dataType: 'json',
		success: function(json) {
			$('.success, .warning, .attention, .information').remove();
			
			if (json['output']) {
				$('#cart_total').html(json['total']);
				
				$('#cart .content').html(json['output']);
			}			
		}
	});
}

function addToWishList(product_id) {
	$.ajax({
		//url: HTTP_SERVER+'product/ajaxwishlistupdate',
		url: HTTP_SERVER+'ajax/ajaxwishlistupdate',
		type: 'post',
		data: 'product_id=' + product_id,
		dataType: 'json',
		success: function(json) {
			$('.success, .warning, .attention, .information').remove();
						
			if (json['success']) {
				$('#notification').html('<div class="attention" style="display: none;">' + json['success'] + '<img src="'+json['close_url']+'" alt="" class="close" /></div>');
				
				$('.attention').fadeIn('slow');
				
				$('#wishlist_total').html(json['total']);
				
				$('html, body').animate({ scrollTop: 0 }, 'slow'); 				
			}	
		}
	});
}

function addToCompare(product_id) { 
	 $.ajax({
		//url: HTTP_SERVER+'product/ajaxcompareupdate',
		url: HTTP_SERVER+'ajax/ajaxcompareupdate',
		type: 'post',
		data: 'product_id=' + product_id,
		dataType: 'json',
		success: function(json) {
			$('.success, .warning, .attention, .information').remove();
						
			if (json['success']) 
			{

				$('#notification').html('<div class="attention" style="display: none;">' + json['success'] + '<img src="'+json['close_url']+'" alt="" class="close" /></div>');
				
				$('.attention').fadeIn('slow');
				
				$('#compare_total').html(json['total']);
				
				$('html, body').animate({ scrollTop: 0 }, 'slow'); 
				$('#prod-compare').html(json['output']); //comment when compare module is uninstall
			}	
		}
	});
}

function removeCompare(product_id) { 
	 $.ajax({
		//url: HTTP_SERVER+'product/ajaxcompareupdate',
		url: HTTP_SERVER+'ajax/ajaxcompareupdate',
		type: 'post',
		data: 'remove=' + product_id,
		dataType: 'json',
		success: function(json) {
			$('.success, .warning, .attention, .information').remove();
						
			if (json['success']) {
 				$('#notification').html('<div class="attention" style="display: none;">' + json['success'] + '<img src="'+json['close_url']+'" alt="" class="close" /></div>');
				
				$('.attention').fadeIn('slow');
				
				$('#compare_total').html(json['total']);
				
				$('html, body').animate({ scrollTop: 0 }, 'slow'); 
				$('#prod-compare').html(json['output']);
			}	
		}
	});
}


