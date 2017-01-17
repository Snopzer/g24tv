function fnSections()
{
	var obj = document.frmEdit.sections;
	var secs = "";
	for(var i=0;i<(obj.length);i++){ 
		if(obj[i].checked == true){
			document.getElementById('SUB_'+obj[i].value).style.display = '';
			secs = secs+'@@@'+obj[i].value;
		}else if(obj[i].checked == false){
			document.getElementById('SUB_'+obj[i].value).style.display = 'none';
		}
	}
	document.getElementById('mainsections').value = secs.substr(3);
}

function fnCheckAllPermissions(fname){
	if(document.getElementById('All_'+fname).checked == true){
		if(document.getElementById('View_'+fname).value == 'Y'){
			document.getElementById('View_'+fname).checked = true;
		}

		if(document.getElementById('Publish_'+fname).value == 'Y'){
			document.getElementById('Publish_'+fname).disabled = false;
			document.getElementById('Publish_'+fname).checked = true;
		}

		if(document.getElementById('Add_'+fname).value == 'Y'){
			document.getElementById('Add_'+fname).disabled = false;
			document.getElementById('Add_'+fname).checked = true;
		}

		if(document.getElementById('Edit_'+fname).value == 'Y'){
			document.getElementById('Edit_'+fname).disabled = false;
			document.getElementById('Edit_'+fname).checked = true;
		}

		if(document.getElementById('Del_'+fname).value == 'Y'){
			document.getElementById('Del_'+fname).disabled = false;
			document.getElementById('Del_'+fname).checked = true;
		}

	}else if(document.getElementById('All_'+fname).checked == false){
		if(document.getElementById('View_'+fname).value == 'Y'){
			document.getElementById('View_'+fname).checked = false;
		}

		if(document.getElementById('Publish_'+fname).value == 'Y'){
			document.getElementById('Publish_'+fname).disabled = true;
			document.getElementById('Publish_'+fname).checked = false;
		}

		if(document.getElementById('Add_'+fname).value == 'Y'){
			document.getElementById('Add_'+fname).disabled = true;
			document.getElementById('Add_'+fname).checked = false;
		}

		if(document.getElementById('Edit_'+fname).value == 'Y'){
			document.getElementById('Edit_'+fname).disabled = true;
			document.getElementById('Edit_'+fname).checked = false;
		}

		if(document.getElementById('Del_'+fname).value == 'Y'){
			document.getElementById('Del_'+fname).disabled = true;
			document.getElementById('Del_'+fname).checked = false;
		}
	}
}

function fnViewPermissions(fname){
	var VIEW =  document.getElementById('View_'+fname);
	var PUBLISH =  document.getElementById('Publish_'+fname);
	var ADD =  document.getElementById('Add_'+fname);
	var EDIT =  document.getElementById('Edit_'+fname);
	var DEL =  document.getElementById('Del_'+fname);
	if(VIEW.checked == true){
		if(PUBLISH.value == 'Y'){PUBLISH.disabled = false;}
		if(ADD.value == 'Y'){ADD.disabled = false;}		
		if(EDIT.value == 'Y'){EDIT.disabled = false;}
		if(DEL.value == 'Y'){DEL.disabled = false;}
		if((ADD.value == 'N') && (EDIT.value == 'N') && (DEL.value == 'N')){
			document.getElementById('All_'+fname).checked = true;
		}
	}else if(VIEW.checked == false){
		if(PUBLISH.value == 'Y'){PUBLISH.disabled = true;PUBLISH.checked = false;}
		if(ADD.value == 'Y'){ADD.disabled = true;ADD.checked = false;}
		if(EDIT.value == 'Y'){EDIT.disabled = true;EDIT.checked = false;}
		if(DEL.value == 'Y'){DEL.disabled = true;DEL.checked = false;}
		document.getElementById('All_'+fname).checked = false
	}
}

function fnPublishPermissions(fname){
	var VIEW =  document.getElementById('View_'+fname);
	var PUBLISH =  document.getElementById('Publish_'+fname);
	var ADD =  document.getElementById('Add_'+fname);
	var EDIT =  document.getElementById('Edit_'+fname);
	var DEL =  document.getElementById('Del_'+fname);
	var VIEWTF = '';var PUBLISHTF = '';var ADDTF = '';var EDITTF = '';var DELTF = '';
	if(VIEW.value == 'Y'){VIEWTF = true;}else if(VIEW.value == 'N'){VIEWTF = false;}
	if(PUBLISH.value == 'Y'){PUBLISHTF = true;}else if(PUBLISH.value == 'N'){PUBLISHTF = false;}
	if(ADD.value == 'Y'){ADDTF = true;}else if(ADD.value == 'N'){ADDTF = false;}
	if(EDIT.value == 'Y'){EDITTF = true;}else if(EDIT.value == 'N'){EDITTF = false;}
	if(DEL.value == 'Y'){DELTF = true;}else if(DEL.value == 'N'){DELTF = false;}

	if(PUBLISH.checked == true){
		if((VIEW.checked == VIEWTF) && (ADD.checked == ADDTF) && (EDIT.checked == EDITTF) && (DEL.checked == DELTF)){
			document.getElementById('All_'+fname).checked = true;
		}
	}else if(PUBLISH.checked == false){
		document.getElementById('All_'+fname).checked = false;
	}
}

function fnAddPermissions(fname){
	var VIEW =  document.getElementById('View_'+fname);
	var PUBLISH =  document.getElementById('Publish_'+fname);
	var ADD =  document.getElementById('Add_'+fname);
	var EDIT =  document.getElementById('Edit_'+fname);
	var DEL =  document.getElementById('Del_'+fname);
	var VIEWTF = '';var PUBLISHTF = '';var ADDTF = '';var EDITTF = '';var DELTF = '';
	if(VIEW.value == 'Y'){VIEWTF = true;}else if(VIEW.value == 'N'){VIEWTF = false;}
	if(PUBLISH.value == 'Y'){PUBLISHTF = true;}else if(PUBLISH.value == 'N'){PUBLISHTF = false;}
	if(ADD.value == 'Y'){ADDTF = true;}else if(ADD.value == 'N'){ADDTF = false;}
	if(EDIT.value == 'Y'){EDITTF = true;}else if(EDIT.value == 'N'){EDITTF = false;}
	if(DEL.value == 'Y'){DELTF = true;}else if(DEL.value == 'N'){DELTF = false;}

	if(ADD.checked == true){
		if((VIEW.checked == VIEWTF) && (PUBLISH.checked == PUBLISHTF) && (EDIT.checked == EDITTF) && (DEL.checked == DELTF)){
			document.getElementById('All_'+fname).checked = true;
		}
	}else if(ADD.checked == false){
		document.getElementById('All_'+fname).checked = false;
	}
}

function fnEditPermissions(fname){
	var VIEW =  document.getElementById('View_'+fname);
	var PUBLISH =  document.getElementById('Publish_'+fname);
	var ADD =  document.getElementById('Add_'+fname);
	var EDIT =  document.getElementById('Edit_'+fname);
	var DEL =  document.getElementById('Del_'+fname);
	var VIEWTF = '';var PUBLISHTF = '';var ADDTF = '';var EDITTF = '';var DELTF = '';
	if(VIEW.value == 'Y'){VIEWTF = true;}else if(VIEW.value == 'N'){VIEWTF = false;}
	if(PUBLISH.value == 'Y'){PUBLISHTF = true;}else if(PUBLISH.value == 'N'){PUBLISHTF = false;}
	if(ADD.value == 'Y'){ADDTF = true;}else if(ADD.value == 'N'){ADDTF = false;}
	if(EDIT.value == 'Y'){EDITTF = true;}else if(EDIT.value == 'N'){EDITTF = false;}
	if(DEL.value == 'Y'){DELTF = true;}else if(DEL.value == 'N'){DELTF = false;}

	if(EDIT.chucked == true){
		if((VIEW.checked == VIEWTF) && (PUBLISH.checked == PUBLISHTF) && (ADD.checked == ADDTF) && (DEL.checked == DELTF)){
			document.getElementById('All_'+fname).checked = true;
		}
	}else if(EDIT.checked == false){
		document.getElementById('All_'+fname).checked = false;
	}
}

function fnDelPermissions(fname){
	var VIEW =  document.getElementById('View_'+fname);
	var PUBLISH =  document.getElementById('Publish_'+fname);
	var ADD =  document.getElementById('Add_'+fname);
	var EDIT =  document.getElementById('Edit_'+fname);
	var DEL =  document.getElementById('Del_'+fname);
	var VIEWTF = '';var PUBLISHTF = '';var ADDTF = '';var EDITTF = '';var DELTF = '';
	if(VIEW.value == 'Y'){VIEWTF = true;}else if(VIEW.value == 'N'){VIEWTF = false;}
	if(PUBLISH.value == 'Y'){PUBLISHTF = true;}else if(PUBLISH.value == 'N'){PUBLISHTF = false;}
	if(ADD.value == 'Y'){ADDTF = true;}else if(ADD.value == 'N'){ADDTF = false;}
	if(EDIT.value == 'Y'){EDITTF = true;}else if(EDIT.value == 'N'){EDITTF = false;}
	if(DEL.value == 'Y'){DELTF = true;}else if(DEL.value == 'N'){DELTF = false;}

	if(DEL.checked == true){
		if((VIEW.checked == VIEWTF) && (PUBLISH.checked == PUBLISHTF) && (ADD.checked == ADDTF) && (EDIT.checked == EDITTF)){
			document.getElementById('All_'+fname).checked = true;
		}
	}else if(DEL.checked == false){
		document.getElementById('All_'+fname).checked = false;
	}
}

function fnShowAttributeValues(val){
	var att_values = document.getElementById('att_values').value.split('###');
	var att  = document.getElementById('att').value.split('###');
	for(var i=0;i<att.length;i++){
		if(att[i] == val){						
			var att_vals = att_values[i].split(',');
			if(document.getElementById('attribute_'+att[i]).checked == true){
				document.getElementById(att[i]).style.display = '';
				document.getElementById(att[i]+'_OTH').style.display = '';
				for(var j=0;j<att_vals.length;j++){
					document.getElementById(att[i]+'_'+att_vals[j]).style.display = '';
				}
			}else{
			document.getElementById(att[i]).style.display = 'none';
			document.getElementById(att[i]+'_OTH').style.display = 'none';
				for(var j=0;j<att_vals.length;j++){
					document.getElementById(att[i]+'_'+att_vals[j]).style.display = 'none';
				}
			}
		}
	}
}

function fnEnableAttributeValues(val,attid){
	if(document.getElementById('attribute_value_'+val).checked == true){
		if(document.getElementById('sel_att_values').value == ''){
			document.getElementById('sel_att_values').value = attid+','+val;
		}else{
			document.getElementById('sel_att_values').value = document.getElementById('sel_att_values').value+'###'+attid+','+val;
		}
		document.getElementById('part_'+val).disabled = false;
		document.getElementById('prefix_'+val).disabled = false;
		document.getElementById('price_'+val).disabled = false;
		document.getElementById('image_'+val).disabled = false;
		document.getElementById('is_default_'+val).disabled = false;
		document.getElementById('sort_order_'+val).disabled = false;
	}else{
		var sel_att_vals = document.getElementById('sel_att_values').value.split('###');
		var tmp_vals ='';
		for(var i=0;i<sel_att_vals.length;i++){
			if(sel_att_vals[i] != attid+','+val){
				tmp_vals = tmp_vals+'###'+sel_att_vals[i];
			}
		}
		document.getElementById('sel_att_values').value = tmp_vals.substr(3);
		document.getElementById('part_'+val).disabled = true;
		document.getElementById('prefix_'+val).disabled = true;
		document.getElementById('price_'+val).disabled = true;
		document.getElementById('image_'+val).disabled = true;
		document.getElementById('is_default_'+val).disabled = true;
		document.getElementById('sort_order_'+val).disabled = true;
	}
}

function Checkall()
{
	if(document.frmMain.checkall.checked==true)
	{
		var obj=document.frmMain.elements;
		for(var i=0;i<obj.length;i++)
		{
			if((obj[i].id=="rid") && (obj[i].checked==false))
			{
			obj[i].checked=true;
			}
		}
	}
	else if(document.frmMain.checkall.checked==false)
	{
		var obj=document.frmMain.elements;
		for(var i=0;i<obj.length;i++)
		{
			if((obj[i].id=="rid") && (obj[i].checked==true))
			{
			obj[i].checked=false;
			}
		}
	}
}
function fnDelete()
{	
	var obj=document.frmMain.elements;
	flag=0;
    for(var i=0;i<obj.length;i++)
	{
	   if(obj[i].name=="selectcheck" && obj[i].checked)
	   {
			flag=1;
			break;
	   }
    }
	if(flag==0)
	{
		alert("Select Checkbox to Delete");
	}else if(flag==1)
	{
			var i,len,chkdelids,sep;
			  chkdelids="";
			  sep="";
				for(var i=0;i<document.frmMain.length;i++)
				{
					if(document.frmMain.elements[i].name=="selectcheck")
					{
						if(document.frmMain.elements[i].checked==true)
						{
							//alert(document.frmFinal.elements[i].value)
							chkdelids = chkdelids + sep + document.frmMain.elements[i].value;
							sep=",";
						}
					}
				}	
				document.frmMain.chkdelids.value=chkdelids
				document.frmMain.tType.value="Del"
				document.frmMain.action="submitcustomers.php";
				document.frmMain.submit()		 
     }
}

function fnMultiSelect()
{
	document.frmMain.action=""
}

function fnaction(act)
{
	//alert(act)
	//	return;
	var len=document.frmMain.rid.length;
	var selected;
	for(var i=0;i<len;i++)
	{
		if(document.frmMain.rid[i].checked==true)
		{
			selected=true;
			break;
		}else
		{
			selected=false;
		}
	}

	if(selected==false)
		{
			alert("No rows selected");
			return;
		}
	
	if(act=='Del')
	{
		
		if(confirm("Do you want to delete selected rows"))
		{
 			document.frmMain.submit();
		}
	}else if(act!='')
	{
		
		if(confirm("Do you want to perform this Action on these selected rows"))
		{
 			document.frmMain.submit();
		}
	}
}

function fnSingleAction(action,ctrlaction,rid,table,upd_col,comp_col,page)
{
	if(action=='Pub' || action=='UnPub')
	{
		/*single?action=Pub&ctrlaction=customers&rid=<?php echo $result['customers_id'];?>&table=r_customers&upd_col=customers_status&comp_col=customers_id&page=<?=$this->page?>

		single?action=UnPub&ctrlaction=customers&rid=<?php echo $result['customers_id'];?>&table=r_customers&upd_col=customers_status&comp_col=customers_id&page=<?=$this->page?>*/
		
		//window.location.href="single?action="+action+"&ctrlaction="+ctrlaction+"&rid="+rid+"&table="+table+"&upd_col="+upd_col+"&comp_col="+comp_col+"&page="+page;
		window.location.href="?action="+action+"&ctrlaction="+ctrlaction+"&rid="+rid+"&table="+table+"&upd_col="+upd_col+"&comp_col="+comp_col+"&page="+page;

	}else if(action=='Del')
	{
		if(confirm("Do you want to delete selected rows"))
		{
			//single?action=Del&ctrlaction=customers&rid=<?php echo $result['customers_id'];?>&table=r_customers&comp_col=customers_id&page=<?=$this->page?>
			//window.location.href="single?action="+action+"&ctrlaction="+ctrlaction+"&rid="+rid+"&table="+table+"&comp_col="+comp_col+"&page="+page;
			window.location.href="?action="+action+"&ctrlaction="+ctrlaction+"&rid="+rid+"&table="+table+"&comp_col="+comp_col+"&page="+page;
		}
	}/*else if(action=='Copy')
	{
		if(confirm("Do you want to copy selected rows"))
		{
			window.location.href="?action="+action+"&ctrlaction="+ctrlaction+"&rid="+rid+"&page="+page;
		}
	}*/
}

function fnValidation(frm){
	var frm = document.forms[frm];
	//alert(document.forms[frm].elements)	

	for(var i=0;i<frm.elements.length;i++){
		if(frm.elements[i].type == "text"){
			//if((frm.elements[i].value.trim() == '') && (frm.elements[i].title != '') && (!frm.elements[i].disabled)){
				if(((frm.elements[i].value == '') || (frm.elements[i].value.charAt(0) == ' ')) && (frm.elements[i].title != '') && (!frm.elements[i].disabled)){
				alert("Please Avoid Space and Enter "+(frm.elements[i].title).substr(0, 1).toUpperCase() + (frm.elements[i].title).substr(1));
				frm.elements[i].focus();
				return false;
			}
		}
		
		if(frm.elements[i].type == "password"){
			if((frm.elements[i].value == '') && (frm.elements[i].title != '') && (!frm.elements[i].disabled)){
								alert("Please Avoid Space and Enter "+(frm.elements[i].title).substr(0, 1).toUpperCase() + (frm.elements[i].title).substr(1));
				frm.elements[i].focus();
				return false;
			}
		}
		
		if(frm.elements[i].type == "textarea"){
				//alert("in textarea"+frm.elements[i].value+ " title: "+frm.elements[i].title+" "+frm.elements[i].disabled)
					//return;
			if((frm.elements[i].value == '') && (frm.elements[i].title != '') && (!frm.elements[i].disabled)){
								alert("Please Avoid Space and Enter "+(frm.elements[i].title).substr(0, 1).toUpperCase() + (frm.elements[i].title).substr(1));
				frm.elements[i].focus();
				return false;
			}
		}
		
		if(frm.elements[i].type == "select-one"){
			if((frm.elements[i].value == '') && (frm.elements[i].title != '') && (!frm.elements[i].disabled)){
				alert("Please Select "+(frm.elements[i].title).substr(0, 1).toUpperCase() + (frm.elements[i].title).substr(1));
				frm.elements[i].focus();
				return false;
			}
		}
		
		if(frm.elements[i].type == "select-multiple"){
			if((frm.elements[i].value == '') && (frm.elements[i].title != '') && (!frm.elements[i].disabled)){
				alert("Please Select "+(frm.elements[i].title).substr(0, 1).toUpperCase() + (frm.elements[i].title).substr(1));
				frm.elements[i].focus();
				return false;
			}
		}
		
		if(frm.elements[i].type == "checkbox"){
			if((!frm.elements[i].checked) && (frm.elements[i].title != '') && (!frm.elements[i].disabled)){
				alert("Please Check "+(frm.elements[i].title).substr(0, 1).toUpperCase() + (frm.elements[i].title).substr(1));
				frm.elements[i].focus();
				return false;
			}
		}
		
		if(frm.elements[i].type == "radio"){
			var radname = frm.elements[i].name;
			var obj = frm.elements[radname];
			flag=0;var title = '';
			for(var j=0;j<obj.length;j++){
			   if(obj[j].checked==true){
				   flag=1;
			   }else{
				   var title = obj[j].title
			   }
			}
			if((flag==0) && (title != '')){
				alert("Select "+title.substr(0, 1).toUpperCase() + title.substr(1));
				return false;
			}
		}
		
		/*if(frm.elements[i].type == "hidden"){
			return true;
		}*/
	}
	return true;
}

function fnSave(url){
	var val = fnValidation('frmEdit');
	if(val == true){
		fnSubmit('frmEdit',url);
	}
}

function fnSubmit(frm,url){
	document.forms[frm].action = url;
	document.forms[frm].submit();
}

function date_pick(val)
{
	$(document).ready(function(){
	$(val).datepicker({ yearRange: '2000:2020',showOn: 'both', buttonImageOnly: true, buttonImage: PATH_TO_ADMIN_IMAGES+'date/icon_cal.png',dateFormat:"yy-mm-dd"});
	});
 	/*alert(val)
 	//val="suresh@harish@naresh";
	arr=val.split("@");
	//arr[0]="#date_added";
	for(i=0;i<arr.length;i++)
	{
		alert("#"+arr[i])
		$(document).ready(function(){
		$("#"+arr[i]).datepicker({ showOn: 'both', buttonImageOnly: true, buttonImage: 'images/date/icon_cal.png',dateFormat:"yy-mm-dd"});
						});
	}*/
}

function fncancelpage(link)
{
		if(confirm('Do you want to cancel the page!!'))
		{
 			document.location.href=link;
		}
}