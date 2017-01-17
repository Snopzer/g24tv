

(function($) {

$.extend({
	metadata : {
		defaults : {
			type: 'class',
			name: 'metadata',
			cre: /({.*})/,
			single: 'metadata'
		},
		setType: function( type, name ){
			this.defaults.type = type;
			this.defaults.name = name;
		},
		get: function( elem, opts ){
			var settings = $.extend({},this.defaults,opts);
			// check for empty string in single property
			if ( !settings.single.length ) settings.single = 'metadata';
			
			var data = $.data(elem, settings.single);
			// returned cached data if it already exists
			if ( data ) return data;
			
			data = "{}";
			
			if ( settings.type == "class" ) {
				var m = settings.cre.exec( elem.className );
				if ( m )
					data = m[1];
			} else if ( settings.type == "elem" ) {
				if( !elem.getElementsByTagName )
					return undefined;
				var e = elem.getElementsByTagName(settings.name);
				if ( e.length )
					data = $.trim(e[0].innerHTML);
			} else if ( elem.getAttribute != undefined ) {
				var attr = elem.getAttribute( settings.name );
				if ( attr )
					data = attr;
			}
			
			if ( data.indexOf( '{' ) <0 )
			data = "{" + data + "}";
			
			data = eval("(" + data + ")");
			
			$.data( elem, settings.single, data );
			return data;
		}
	}
});


$.fn.metadata = function( opts ){
	return $.metadata.get( this[0], opts );
};

})(jQuery);


;(function(d,b,a,e){var c=function(g,f){this.$elem=d(g);this.options=f;this.metadata=d.fn.metadata?this.$elem.metadata():{}};c.prototype={defaults:{percent:false,value:0,maxValue:100,duration:1000,label:"",fillColor:"#e15656"},init:function(){this.config=d.extend({},this.defaults,this.options,this.metadata);if(this._build()){var f=d(".da-circular-progress canvas",this.$elem).get(0);f.width=d(".da-circular-progress",this.$elem).width();f.height=d(".da-circular-progress",this.$elem).height();this.data={startAngle:-(Math.PI/2),endAngle:((this.config.value/this.config.maxValue)*2*Math.PI)-(Math.PI/2),startValue:0,endValue:this.config.value,centerX:f.width/2,centerY:f.height/2,radius:d(".da-circular-progress",this.$elem).width()/2};this.canvas=f;this.context=f.getContext("2d");this.valueEl=d(".da-circular-front .da-circular-digit",this.$elem).get(0);this.start()}return this},start:function(){var f=this.data.radius;this.context.fillStyle=this.config.fillColor;this._update(10,true)},_build:function(){var g=d("<span></span>"),f=a.createElement("canvas");this.$elem.append(g.clone().addClass("da-circular-front").append(g.clone().addClass("da-circular-digit")).append(g.clone().addClass("da-circular-label").text(this.config.label))).append(g.clone().addClass("da-circular-progress").append(d(f))).addClass("da-circular-stat");if(!f.getContext){if(typeof(b.G_vmlCanvasManager)!=="undefined"){f=b.G_vmlCanvasManager.initElement(f)}else{console.log("Your browser does not support HTML5 Canvas, or excanvas is missing on IE");this.$elem.hide();return false}}return true},_getVal:function(f){if(this.config.percent){return Math.ceil(Math.min(f,1)*(this.config.value/this.config.maxValue)*100)}else{return f>1?this.data.endValue:Math.ceil(f*(this.data.endValue-this.data.startValue))}},_update:function(h,k){var j=this.data;if(k){var f=this;j.startTime=new Date().getTime();j.timer=b.setInterval(function(){f._update(h,false)},h)}else{var g=Math.min((new Date().getTime()-j.startTime)/this.config.duration,1),i=this._getVal(g),j=this.data;if(g>=1){i=this._getVal(g);b.clearInterval(j.timer)}this.context.clearRect(0,0,this.canvas.width,this.canvas.height);this.context.beginPath();this.context.moveTo(j.centerX,j.centerY);this.context.lineTo(j.centerX,0);this.context.arc(j.centerX,j.centerY,j.radius,j.startAngle,j.startAngle+((j.endAngle-j.startAngle)*g),false);this.context.closePath();this.context.fill();this.valueEl.innerHTML=this.config.percent?("<span>"+i+"%</span>"):("<span>"+i+"</span>/"+this.config.maxValue)}}};c.defaults=c.prototype.defaults;d.fn.daCircularStat=function(f){return this.each(function(){new c(this,f).init()})}})(jQuery,window,document);




(function(a){a(document).ready(function(d){a(".da-circular-stat").daCircularStat();var c=a("#da-ex-wizard-form").validate({onsubmit:false});a("#da-ex-wizard-form").daWizard({forwardOnly:false,onLeaveStep:function(e,g){return c.form()},onBeforeSubmit:function(){return c.form()}});a("#da-ex-calendar-gcal").fullCalendar({events:"http://www.google.com/calendar/feeds/usa__en%40holiday.calendar.google.com/public/basic",eventClick:function(e){window.open(e.url,"gcalevent","width=700,height=600");return false}});google.setOnLoadCallback(f);function f(){b()}function b(){var h=google.visualization.arrayToDataTable([["Month","Seeds","Plants"],["Jan",1000,400],["Feb",1170,460],["Mar",660,1120],["Apr",1030,540]]);var e={};var g=new google.visualization.LineChart(a("#da-ex-gchart-line").get(0));a(window).on("debouncedresize",function(){g.draw(h,e)});g.draw(h,e)}})})(jQuery);