jQuery(document).ready(function(){
	jQuery('#tbsMailTestForm').submit(function(){
		jQuery(this).sendFormTbs({
			btn: jQuery(this).find('button:first')
		,	onSuccess: function(res) {
				if(!res.error) {
					jQuery('#tbsMailTestForm').slideUp( 300 );
					jQuery('#tbsMailTestResShell').slideDown( 300 );
				}
			}
		});
		return false;
	});
	jQuery('.tbsMailTestResBtn').click(function(){
		var result = parseInt(jQuery(this).data('res'));
		jQuery.sendFormTbs({
			btn: this
		,	data: {mod: 'mail', action: 'saveMailTestRes', result: result}
		,	onSuccess: function(res) {
				if(!res.error) {
					jQuery('#tbsMailTestResShell').slideUp( 300 );
					jQuery('#'+ (result ? 'tbsMailTestResSuccess' : 'tbsMailTestResFail')).slideDown( 300 );
				}
			}
		});
		return false;
	});
	jQuery('#tbsMailSettingsForm').submit(function(){
		jQuery(this).sendFormTbs({
			btn: jQuery(this).find('button:first')
		});
		return false; 
	});
});