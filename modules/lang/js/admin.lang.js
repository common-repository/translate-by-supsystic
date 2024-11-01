var g_tbsLang = ''
,	g_tbsDefLang = ''
,	g_tbsSaved = false
,	g_tbsSavedTimeout = 0
,	g_tbsForm = null
,	g_tbsMetaFieldNamesToChange = {}
,	g_tbsCustomFormParams = {};

switch(tbsLangData.for) {
	// We need to add this callback before it will be added in tags.js by wp
	case 'term':
		if(jQuery('#submit').size()) {
			jQuery('#submit').click(function(){
				tbsReturnAllFormData();
			});
		}
		break;
	// We need to attach it before it will be attached in nav-menu.js
	case 'nav-menus':
		if(jQuery('#update-nav-menu').size()) {
			jQuery('#update-nav-menu').submit(function(){
				tbsReturnAllFormData();
			});
		}
		break;
}

jQuery(document).ready(function(){
	jQuery(document).trigger('tbsAddMetaFieldNamesToChange', g_tbsMetaFieldNamesToChange);
	tbsAddNewMetaFields();
	g_tbsLang = g_tbsDefLang = jQuery('.tbsLangTabs').find('.tbsLangTabSwitch.nav-tab-active').attr(
			tbsLangData.useBlockEditor ? 'data-lang' : 'href');
	if(tbsLangData.useBlockEditor && tbsLangData.for == 'post') {
		// TODO: Find hook in JS for this, somewhere in edit-post.js
		setTimeout(function(){
			jQuery('.tbsLangTabs').insertBefore(jQuery('.edit-post-layout__content:first'));
		}, 1000);
		/*jQuery.ajaxSetup({
			beforeSend: function(jqXHR, settings ) {
				console.log('lalala', jqXHR, settings);
			}
		});
		jQuery.ajaxPrefilter(function( options, originalOptions, jqXHR ) {
			console.log('ololo');
		  });*/
		  //wp.apiFetch.use(wp.apiFetch.createRootURLMiddleware("someme"));
		 // wp.apiFetch.createNonceMiddleware("lalala");
		  //wp.apiFetch.use( wp.apiFetch.createRootURLMiddleware("lalala") );
		return;
	}

	switch( tbsLangData.for ) {
		case 'post':
			g_tbsForm = jQuery('#post');
			break;
		case 'term':
			g_tbsForm = jQuery('#addtag');
			break;
		case 'term_edit':
			g_tbsForm = jQuery('#edittag');
			break;
		case 'nav-menus':
			g_tbsForm = jQuery('#update-nav-menu');
			jQuery('.tbsLangTabs').insertBefore( jQuery('#menu-to-edit') );
			break;
		case 'options-general':
			g_tbsForm = jQuery('#wpbody-content form:first');
			g_tbsForm.prepend( jQuery('.tbsLangTabs') );
			g_tbsForm.prepend( jQuery('.tbsLangInputs') );
			break;
		case 'gmap':
			g_tbsForm = jQuery('.supsystic-plugin');
			jQuery('.tbsLangInputs').prependTo( jQuery('#gmpMarkerForm') );
			jQuery(document).bind('gmpMarkerFormEdit', function(event, marker){
				updateGmapLangData(marker, 'edit');
			});
			jQuery(document).bind('gmpAfterMarkerSave', function(event, marker){
				updateGmapLangData(marker, 'save');
			});
			jQuery(document).bind('gmpAfterResetMarkerForm', function(event){
				updateGmapLangData(null, 'reset');
			});
			break;
		case 'grid_gallery':
			g_tbsForm = jQuery('.photo-editor');
			break;
		case 'custom':
			jQuery(document).trigger('tbsGetCustomFormId', g_tbsCustomFormParams);
			if(g_tbsCustomFormParams.formSelector) {
				g_tbsForm = jQuery(g_tbsCustomFormParams.formSelector);
			}
			if(g_tbsCustomFormParams.saveBtnSelector) {
				var saveBtn = jQuery(g_tbsCustomFormParams.saveBtnSelector);
				if(saveBtn.size()) {
					saveBtn.click(function(){
						tbsReturnAllFormData();
					});
				}
			}
			break;
	}
	tbsRestoreLangData( g_tbsLang );
	jQuery('.tbsLangTabs').find('.tbsLangTabSwitch').click(function(){
		var $this = jQuery(this)
		,	lang = $this.data('lang');

		$this.parents('.tbsLangTabs').find('.tbsLangTabSwitch').removeClass('nav-tab-active');
		$this.addClass('nav-tab-active');
		tbsSaveLangData( g_tbsLang );	// Save prev. edited data
		tbsRestoreLangData( lang );
		g_tbsLang = lang;
		return false;
	});
	// Update lang data when just editing post fields
	switch( tbsLangData.for ) {
		case 'post':
			g_tbsForm.find('[name="post_title"]').change(function(){
				_tbsSaveLangDataField( g_tbsLang, 'post_title' );
			});
			g_tbsForm.find('[name="excerpt"]').change(function(){
				_tbsSaveLangDataField( g_tbsLang, 'post_excerpt' );
			});
			// It will take some time for loading by tinyMce editor, so.....
			setTimeout(function(){
				var contentMceId = 'content';
				if(typeof(tinyMCE) !== 'undefined'
					&& tinyMCE.get( contentMceId )
				) {
					tinyMCE.get( contentMceId ).onChange.add(function(ed, e){
						if( g_tbsSaved ) {
							if( g_tbsSavedTimeout ) {
								clearTimeout( g_tbsSavedTimeout );
							}
							g_tbsSavedTimeout = setTimeout(function(){
								g_tbsSaved = false;
							}, 100);
							return;
						}
						_tbsSaveLangDataField( g_tbsLang, 'post_content' );
					});
				} else {
					jQuery('#'+ contentMceId).change(function(){
						_tbsSaveLangDataField( g_tbsLang, 'post_content' );
					});
				}
			}, 1000);
			break;
		case 'term':
			g_tbsForm.find('[name="tag-name"]').change(function(){
				_tbsSaveLangDataField( g_tbsLang, 'name' );
			});
			g_tbsForm.find('[name="description"]').change(function(){
				_tbsSaveLangDataField( g_tbsLang, 'description' );
			});
			break;
		case 'term_edit':
			g_tbsForm.find('[name="name"]').change(function(){
				_tbsSaveLangDataField( g_tbsLang, 'name' );
			});
			g_tbsForm.find('[name="description"]').change(function(){
				_tbsSaveLangDataField( g_tbsLang, 'description' );
			});
			break;
		case 'nav-menus':
			g_tbsForm.find('.edit-menu-item-title').change(function(){
				var idAttr = jQuery(this).attr('id').split('-')
				,	id = idAttr[ idAttr.length - 1 ];
				_tbsSaveLangDataField( g_tbsLang, 'title', id );
			});
			break;
		case 'options-general':
			g_tbsForm.find('[name="blogname"]').change(function(){
				_tbsSaveLangDataField( g_tbsLang, 'blogname' );
			});
			g_tbsForm.find('[name="blogdescription"]').change(function(){
				_tbsSaveLangDataField( g_tbsLang, 'blogdescription' );
			});
			break;
		case 'gmap':
			g_tbsForm.find('[name="marker_opts[title]"]').change(function(){
				_tbsSaveLangDataField( g_tbsLang, 'marker_title');
			});
			// It will take some time for loading by tinyMce editor, so.....
			setTimeout(function(){
				var markerDescMceId = 'markerDescription';
				if(typeof(tinyMCE) !== 'undefined' && tinyMCE.get( markerDescMceId )) {
					tinyMCE.get( markerDescMceId ).onChange.add(function(ed, e){
						if( g_tbsSaved ) {
							if( g_tbsSavedTimeout ) {
								clearTimeout( g_tbsSavedTimeout );
							}
							g_tbsSavedTimeout = setTimeout(function(){
								g_tbsSaved = false;
							}, 100);
							return;
						}
						_tbsSaveLangDataField( g_tbsLang, 'marker_description');
					});
				} else {
					jQuery('#'+ markerDescMceId).change(function(){
						_tbsSaveLangDataField( g_tbsLang, 'marker_description' );
					});
				}
			}, 500);
			break;
		case 'grid_gallery':
			for(var i = 0; i < tbsAvailableFields.length; i++) {
				g_tbsForm.each(function() {
					var form = jQuery(this),
						formId = form.attr('id');
					form.find('[name="'+ tbsAvailableFields[i]+ '"]').change(function(){
						_tbsSaveLangDataField( g_tbsLang, jQuery(this).attr('name'), formId );
					});
				});
			}
			break;
		default:
			break;
	}
	if( toeInArrayTbs(tbsLangData.for, ['post', 'term_edit', 'options-general']) ) {	// For term adding - "term" - it use onClick submit button bnding - so it's useless here
		g_tbsForm.submit(function(){
			tbsReturnAllFormData();
		});
	}
});
function tbsAddNewMetaFields() {
	if(tbsAvailableMetaFields.length) {
		for(var i = 0; i < tbsAvailableMetaFields.length; i++) {
			var field = jQuery('[name="'+ tbsAvailableMetaFields[i]+ '"]');
			if(field.length) {
				var langTabs = jQuery('.tbsLangTabs .tbsLangTabSwitch'),
					langInputs = jQuery('.tbsLangInputs'),
					fieldName = field.attr('name');
				if(tbsAvailableFields.indexOf(fieldName) == -1) {
					tbsAvailableFields.push(fieldName);
					if(langTabs.length) {
						for(var j = 0; j < langTabs.length; j++) {
							var lang = jQuery(langTabs[j]).attr('href');
							langInputs.append('<input type="hidden" name="tbs['+ lang+ ']['+ fieldName+ ']" />')
						}
					}
				}
				field.change(function(){
					var name = _tbsApplyChangesToMetaFieldName(jQuery(this).attr('name'), true);
					_tbsSaveLangDataField(g_tbsLang, name);
				});
			}
		}
	}
}
function tbsReturnAllFormData() {
	if(g_tbsDefLang != g_tbsLang) {
		// Save currently edited data
		tbsSaveLangData( g_tbsLang );
		// Restore orignal data in inputs - to make sure in wp_posts table will be saved default data
		tbsRestoreLangData( g_tbsDefLang );
		g_tbsLang = g_tbsDefLang;
	}
}
function tbsSaveLangData( lang ) {
	for(var i = 0; i < tbsAvailableFields.length; i++) {
		_tbsSaveLangDataField( lang, tbsAvailableFields[ i ] );
	}
	g_tbsSaved = true;
}
function _tbsApplyChangesToMetaFieldName(fieldName, revert) {
	if(revert) {
		for(var item in g_tbsMetaFieldNamesToChange) {
			if(g_tbsMetaFieldNamesToChange[item] == fieldName) {
				return item;
			}
		}
		return fieldName;
	} else {
		return g_tbsMetaFieldNamesToChange[fieldName] ? g_tbsMetaFieldNamesToChange[fieldName] : fieldName;
	}
}
function _tbsSaveLangDataField( lang, field, id ) {
	var $input = null,
		selector;
	if( tbsLangData.for == 'nav-menus' ) {
		if( id ) {
			selector = 'tbs['+ id+ ']['+ lang+ ']['+ field+ ']';
			$input = g_tbsForm.find('input[name="'+ selector+ '"]');
		} else {
			$input = g_tbsForm.find('input[name^="tbs["]').filter('[name*="['+ lang+ ']['+ field+ ']"]');
		}
	} else if( tbsLangData.for == 'grid_gallery' ) {
		selector = 'tbs['+ lang+ ']['+ field+ ']';
		$input = g_tbsForm.filter('#'+ id).find('input[name="'+ selector+ '"]');
		$input.val( g_tbsForm.filter('#'+ id).find('[name="'+ field+ '"]').val() );
		return true;	// to prevent use switch
	} else {
		selector = 'tbs'+ (id ? '['+ id+ ']' : '')+ '['+ lang+ ']['+ field+ ']';
		$input = g_tbsForm.find('input[name="'+ selector+ '"]');
	}
	switch( field ) {
		case 'post_title':
			$input.val( g_tbsForm.find('[name="post_title"]').val() );
			break;
		case 'post_content':
			var postContentId = tbsLangData.post_type == 'attachment' ? 'attachment_content' : 'content';
			$input.val( tbsGetTxtEditorVal(postContentId) );
			break;
		case 'post_excerpt':
			$input.val( g_tbsForm.find('[name="excerpt"]').val() );
			break;
		case 'name':
			var termInpName = tbsLangData.for == 'term' ? 'tag-name' : 'name';
			$input.val( g_tbsForm.find('[name="'+ termInpName+ '"]').val() );
			break;
		case 'description':
			$input.val( g_tbsForm.find('[name="description"]').val() );
			break;
		case 'title':	// Menus
			$input.each(function(){
				jQuery(this).val( jQuery(this).parents('.menu-item:first').find('.edit-menu-item-title').val() );
			});
			break;
		case 'blogname':
			$input.val( g_tbsForm.find('[name="blogname"]').val() );
			break;
		case 'blogdescription':
			$input.val( g_tbsForm.find('[name="blogdescription"]').val() );
			break;
		case 'marker_title':
			$input.val( g_tbsForm.find('[name="marker_opts[title]"]').val() );
			break;
		case 'marker_description':
			$input.val( tbsGetTxtEditorVal('markerDescription') );
			break;
		default:
			$input.val( g_tbsForm.find('[name="'+ _tbsApplyChangesToMetaFieldName(field)+ '"]').val() );
			break;
	}
}
function tbsRestoreLangData( lang ) {
	for(var i = 0; i < tbsAvailableFields.length; i++) {
		var f = tbsAvailableFields[ i ]
		,	value = '';
		if( tbsLangData.for == 'nav-menus' ) {
			value = true;	// Just to make next IF work
		} else if( tbsLangData.for == 'grid_gallery' ) {
			g_tbsForm.each(function() {
				var form = jQuery(this);
				value = form.find('input[name="tbs['+ lang+ ']['+ f+ ']"]').val();
				if(value) {
					form.find('[name="'+ f+ '"]').val( value );
				}
			});
			value = false; // to prevent use switch
		} else {
			value = g_tbsForm.find('input[name="tbs['+ lang+ ']['+ f+ ']"]').val();
		}
		if(value) {
			switch( f ) {
				case 'post_title':
					g_tbsForm.find('[name="post_title"]').val( value );
					break;
				case 'post_content':
					var postContentId = tbsLangData.post_type == 'attachment' ? 'attachment_content' : 'content';
					tbsSetTxtEditorVal(postContentId, value);
					break;
				case 'post_excerpt':
					g_tbsForm.find('[name="excerpt"]').val( value );
					break;
				case 'name':
					var termInpName = tbsLangData.for == 'term' ? 'tag-name' : 'name';
					g_tbsForm.find('[name="'+ termInpName+ '"]').val( value );
					break;
				case 'description':
					g_tbsForm.find('[name="description"]').val( value );
					break;
				case 'title':	// Menus
					var $input = g_tbsForm.find('input[name^="tbs["]').filter('[name*="['+ lang+ ']['+ f+ ']"]');
					$input.each(function(){
						var value = jQuery(this).val();
						if( value ) {
							jQuery(this).parents('.menu-item:first').find('.edit-menu-item-title').val( jQuery(this).val() ).trigger('input');
						}
					});
					break;
				case 'blogname':
					g_tbsForm.find('[name="blogname"]').val( value ).trigger('input');
					break;
				case 'blogdescription':
					g_tbsForm.find('[name="blogdescription"]').val( value );
					break;
				case 'marker_title':
					g_tbsForm.find('[name="marker_opts[title]"]').val(value);
					break;
				case 'marker_description':
					tbsSetTxtEditorVal('markerDescription', value);
					break;
				default:
					var field = g_tbsForm.find('[name="'+ _tbsApplyChangesToMetaFieldName(f)+ '"]');
					if(field.length) {
						switch(field.get(0).type) {
							case 'text':
								field.val(value);
								break;
							case 'textarea':
								var fieldId = field.attr('id');
								if(fieldId) {
									tbsSetTxtEditorVal(fieldId, value);
								} else {
									field.val(value);
								}
								break;
							default:
								break;
						}
					}
					break;
			}
		}
	}
}
function updateGmapLangData(marker, action){
	var fields = jQuery('.tbsLangInputs input')
	,	map = marker ? marker.getMap() : null
	,	langData = map ? map.getParam('_tbs_lang_data') : {};

	if(fields.length) {
		fields.each(function() {
			var $this = jQuery(this)
			,	mid = marker ? marker.getId() : 0
			,	lang = $this.data('lang')
			,	field = $this.data('field');
			switch(action) {
				case 'edit':
					if(langData && langData[mid] && langData[mid][lang] && langData[mid][lang][field]) {
						$this.val(langData[mid][lang][field]);
					} else {
						$this.val('');
					}
					break;
				case 'save':
					langData[mid] = langData[mid] ? langData[mid] : {};
					langData[mid][lang] = langData[mid][lang] ? langData[mid][lang] : {};
					langData[mid][lang][field] = $this.val();
					break;
				case 'reset':
					$this.val('');
					break;
				default:
					break;
			}
		});
	}
	switch(action) {
		case 'edit':
			break;
		case 'save':
			if(map) {
				map.setParam('_tbs_lang_data', langData);
			}
			break;
		default:
			break;
	}

}