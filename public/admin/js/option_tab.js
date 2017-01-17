/*
css used in products option and home page inner tabs included in products and home view page	
this is used in combination with js/option_tab.js
*/
$(document).ready(function(){
$('#tabsjq div').hide();
$('#tabsjq div:first').show();
$('#tabsjq ul li:first').addClass('active');
$('#tabsjq ul li a').click(function(){ 
$('#tabsjq ul li').removeClass('active');
$(this).parent().addClass('active'); 
var currentTab = $(this).attr('href'); 
$('#tabsjq div').hide();
$(currentTab).show();
return false;
});
});

$(document).ready(function(){
$('#tabsjq1 div').hide();
$('#tabsjq1 div:first').show();
$('#tabsjq1 ul li:first').addClass('active');
$('#tabsjq1 ul li a').click(function(){ 
$('#tabsjq1 ul li').removeClass('active');
$(this).parent().addClass('active'); 
var currentTab = $(this).attr('href'); 
$('#tabsjq1 div').hide();
$(currentTab).show();
return false;
});
});

$(document).ready(function(){
$('#tabsjq2 div').hide();
$('#tabsjq2 div:first').show();
$('#tabsjq2 ul li:first').addClass('active');
$('#tabsjq2 ul li a').click(function(){ 
$('#tabsjq2 ul li').removeClass('active');
$(this).parent().addClass('active'); 
var currentTab = $(this).attr('href'); 
$('#tabsjq2 div').hide();
$(currentTab).show();
return false;
});
});

$(document).ready(function(){
$('#tabsjq3 div').hide();
$('#tabsjq3 div:first').show();
$('#tabsjq3 ul li:first').addClass('active');
$('#tabsjq3 ul li a').click(function(){ 
$('#tabsjq3 ul li').removeClass('active');
$(this).parent().addClass('active'); 
var currentTab = $(this).attr('href'); 
$('#tabsjq3 div').hide();
$(currentTab).show();
return false;
});
});