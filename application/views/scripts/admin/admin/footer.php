 <div id="footer">
        	<div class="container_12">
            	<div class="grid_12">
                	<p>&copy; 2016 <a href="http://snopzer.com" title="Snopzer" target="_blank"> Snopzer Software Private  </a></p>
        		</div>
            </div>
            <div style="clear:both;"></div>
        </div> <!-- End #footer -->
          </div> <!-- End #footer -->
<?php if($_REQUEST['type']!=""){?>
		 <script type="text/javascript">
<!--
var TabbedPanels1 = new Spry.Widget.TabbedPanels("TabbedPanels1");
//-->
        </script>

		<script type="text/javascript">
var tooltipObj = new DHTMLgoodies_formTooltip();
tooltipObj.setTooltipPosition('right');
tooltipObj.setPageBgColor('#EEEEEE');
tooltipObj.setTooltipCornerSize(15);
tooltipObj.initFormFieldTooltip();
</script>
<?}?>

<script type="text/javascript">
$('input[name=\'search_page\']').autocomplete({
	source: [<?php echo $this->searchResult;?>/*{ label: 'My First Item', value: '/items/1' }, { label: 'My Second Item', value: '/items/2'}*/]
	,focus: function (event, ui) {
		$(event.target).val(ui.item.label);
		return false;
	}
	,select: function (event, ui) {
		$(event.target).val(ui.item.label);
		window.location = ui.item.value;
		return false;
	}
});

	/*$().ready(function() {
	$("#search_page").autocomplete("<?=HTTP_SERVER?>admin/searchfileauto?format=json", {
		width: 260,
		matchContains: true,
		,select: function (event, ui) {
		$(event.target).val(ui.item.label);
		window.location = ui.item.value;
		return false;
	}
	})
});*/
 
</script>
<script type='text/javascript' src='<?=PATH_TO_ADMIN_JS?>jquery.autocomplete.js'></script>
<link rel="stylesheet" type="text/css" href="<?=PATH_TO_ADMIN_JS?>jquery.autocomplete.css" />
<!--menu-fixed-js-code-start-->
	<script src="<?echo PATH_TO_ADMIN_JS;?>jquery.fixed.js"></script>
	<script>
		$(document).ready(function(){
		
			$('.main-menu .main-menu-div').fixed({
				'top'	: '0'
			});
				
		});
	</script> 
    <!--menu-fixed-js-code-End-->
	</body>
</html>
<?php //echo (memory_get_usage()/(1024*1024));
//echo "<pre>";
//print_r($_SESSION);
?>