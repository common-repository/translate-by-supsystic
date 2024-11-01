if(typeof(g_tbsWidgetData) == 'undefined')
	var g_tbsWidgetData = {};
jQuery(document).ready(function(){
	var $shels = jQuery('.tbsLangs');
	if($shels && $shels.size()) {
		$shels.each(function(){
			var $this = jQuery( this )
			,	type = $this.data('type')
			,	flagSize = parseInt( $this.data('flag-size') );
			switch( type ) {
				case 'buttons_list':
					$this.find('.tbsLangBtn').click(function(){
						document.location.href = jQuery( this ).data('url');
						return false;
					});
					break;
				case 'selectbox': case 'selectbox_cust':
					var $sel = $this.find('.tbsLangSel');
					$sel.change(function(){
						document.location.href = jQuery( this ).val();
					});
					if( type == 'selectbox_cust' ) {
						$sel.chosen({
							disable_search_threshold: 10
						});
						var $custSel = $sel.next('.chosen-container');

						if( g_tbsWidgetData.isRtl && parseInt(g_tbsWidgetData.isRtl)) {
							$custSel.addClass('chosen-rtl');
							$custSel.find('.chosen-single').css({ padding: '5px 5px 0 8px' });
							$custSel.find('.chosen-single div').css({ top: '5px' });
						}
						if( flagSize ) {
							$custSel.find('.chosen-single span:first').prepend(
								_tbsGetFlagImgTag($sel.find('option:selected'), 
								$custSel.parents('.tbsLangs:first').data('flag-size')) 
							);
							$sel.bind('chosen:showing_dropdown', function(){
								var $sel = jQuery(this)
								,	$custSel = $sel.next('.chosen-container')
								,	i = 0;
								$sel.find('option').each(function(){
									$custSel.find('.chosen-results li:eq('+ i+ ')').prepend( 
										_tbsGetFlagImgTag(jQuery(this), 
										jQuery(this).parents('.tbsLangs:first').data('flag-size')) 
									);
									i++;
								});
							});
							$custSel.width( $custSel.width() + flagSize );
						}
					}
					break;
			}
		});
	}
});
function _tbsGetFlagImgTag( $option, flagSize ) {
	return '<img src="'+ $option.data('flag')+ '" style="max-width: '+ flagSize+ 'px; height: auto; margin-right: 5px; display: inline;" />';
}