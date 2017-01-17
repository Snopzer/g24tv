/*
LazyKarl v1 - The ultimate cross-browser lazy load plugin for images with jQuery
(c) 2011 Karl Steltenpohl <http://www.karlsteltenpohl.com>
MIT-style license.
*/	
(function($) {
	$.fn.lazyKarl = function(options) {
		
		var defaults = {
			loadImg: 'images/loading.gif'
		};
		
		var options = $.extend(defaults, options);
		
		$(this).each(function() {			
			// Create the window
			$('body').prepend('<div class="lazy-window-top" style="position:fixed; background-color:transparent; opacity:0; height:1px; width:100%; top:1px; margin:0 auto;"></div>');
			$('body').append('<div class="lazy-window-bottom" style="position:fixed; background-color:transparent; opacity:0; height:1px; width:100%; bottom:1px; margin:0 auto;"></div>');
			
			//*** SETUP THE IMAGES ***//			
			var allImages = $(this).find('img');
			allImages.each(function(){	
				var imageSRC = $(this).attr("src"); 	// Get the images src
				var imageREL = $(this).attr("rel"); 	// Get the images rel
				if(imageSRC != ""){
					$(this).attr("rel", imageSRC); 		// Set the rel value to the src value if there was a src, which there shouldn't have been for proper usage.
				}
				$(this).prop("src", options.loadImg);	// Set the loading gifs		
			});
			//*** END SETUP ***//
			
			checkPos();									// Check the positions
			
			$(window).scroll(function(e) {	
				checkPos();								// Scroll event check positions
			});
			
			function checkPos(){
			
				var windowTopOffset =  $('.lazy-window-top').offset();					// Get the top boundary position
				var windowTopHeight = $('.lazy-window-top').height();					// Get the top boundary height
				var windowTop = parseInt(windowTopHeight + windowTopOffset.top);			// Get the top of the boundary
				
				var windowBottomOffset = $('.lazy-window-bottom').offset();				// Get the bottom boundary position
				var windowBottomHeight = $('.lazy-window-bottom').height();				// Get the bottom boundary height
				var windowBottom = parseInt(windowBottomHeight + windowBottomOffset.top);		// Get the bottom of the boundary
				
				allImages.each(function(){
					var imageOffset1 = $(this).offset();						// Get the image position
					var imageHeight = $(this).height();						// Get the image height
					var imageBottom = parseInt(imageOffset1.top + imageHeight);			// Get the bottom of the image
					
					// If the top of the image is above the bottom of the boundary
					// and the bottom of the image is below the top of the boundary
					if(imageOffset1.top < windowBottom && imageBottom > windowTop){
						imageSRC1 = $(this).attr("rel");
						$(this).attr("src", imageSRC1);
					}
				});	
			}				
		});
	}
})(jQuery);