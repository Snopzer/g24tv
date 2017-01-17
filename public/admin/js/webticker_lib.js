$(document).ready(function() {

	//this is the useful function to scroll a text inside an element...
	
	function startScrolling(scroller_obj, velocity, start_from){
		//bind animation to the children inside the scroller element
		scroller_obj.children().bind('marquee', function(event,c) {
			//text to scroll
			var ob = $(this);
			//scroller width
			var sw = parseInt(ob.parent().width());
			//text width
			var tw = parseInt(ob.width());
			//text left position relative to the offset parent
			var tl = parseInt(ob.position().left);	
			//velocity converted to calculate duration
			var v  = velocity>0 && velocity<144 ? (144-velocity)*144 : 10000;
			//same velocity for different text's length in relation with duration
			var dr = (v*tw/sw)+v;
			//is it scrolling from right or left?
			switch(start_from){
			case 'right':
				//is it the first time?
				if(typeof c == 'undefined'){
					//if yes, start from the absolute right
					ob.css({ left: sw });
					sw = -tw;
				}else{
					//else calculate destination position
					sw = tl - (tw + sw);
				};
				break;
			default:
				if(typeof c == 'undefined'){
					//start from the absolute left
					ob.css({ left: -tw });
				}else{
					//else calculate destination position
					sw += tl + tw;
				};
			}
			//attach animation to scroller element and start it by a trigger
			ob.animate(	{left:sw},	
						{	duration:dr, 
							easing:'linear', 
							complete:function(){ob.trigger('marquee');}, 
							step:function(){    
								//check if scroller limits are reached
								if(start_from == 'right'){
									if(parseInt(ob.position().left) < -parseInt(ob.width())){
										//we need to stop and restart animation
										ob.stop();
										ob.trigger('marquee');
									};
								}else{
									if(parseInt(ob.position().left) > parseInt(ob.parent().width())){
										ob.stop();
										ob.trigger('marquee');
									};
								};
							}
						});
		}).trigger('marquee');
		//pause scrolling animation on mouse over
		scroller_obj.mouseover(function(){
			$(this).children().stop(); 
		});
		//resume scrolling animation on mouse out
		scroller_obj.mouseout(function(){
			$(this).children().trigger('marquee',['resume']); 
		});
	};

	//the main app starts here...

	//change the cursor type for each scroller
	$('.scroller').css("cursor","pointer");

	//settings to pass to function
	var scroller			= $('.scroller');	// element(s) to scroll
	var scrolling_velocity 	= 1; 				// 1-99
	var scrolling_from 		= 'right';			// 'right' or 'left'
	
	//call the function and start to scroll..
	startScrolling( scroller, scrolling_velocity, scrolling_from );

	//create a new scroller but it starts from left...
	$('#fast_scroller').css("cursor","pointer");
	startScrolling( $('#fast_scroller'), 75, 'left');
	
});