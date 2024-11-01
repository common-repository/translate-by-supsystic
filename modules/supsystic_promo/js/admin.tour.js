var g_tbsCurrTour = null
,	g_tbsTourOpenedWithTab = false
,	g_tbsAdminTourDissmissed = false;
jQuery(document).ready(function(){
	setTimeout(function(){
		if(typeof(tbsAdminTourData) !== 'undefined' && tbsAdminTourData.tour) {
			jQuery('body').append( tbsAdminTourData.html );
			tbsAdminTourData._$ = jQuery('#supsystic-admin-tour');
			for(var tourId in tbsAdminTourData.tour) {
				if(tbsAdminTourData.tour[ tourId ].points) {
					for(var pointId in tbsAdminTourData.tour[ tourId ].points) {
						_tbsOpenPointer(tourId, pointId);
						break;	// Open only first one
					}
				}
			}
			for(var tourId in tbsAdminTourData.tour) {
				if(tbsAdminTourData.tour[ tourId ].points) {
					for(var pointId in tbsAdminTourData.tour[ tourId ].points) {
						if(tbsAdminTourData.tour[ tourId ].points[ pointId ].sub_tab) {
							var subTab = tbsAdminTourData.tour[ tourId ].points[ pointId ].sub_tab;
							jQuery('a[href="'+ subTab+ '"]')
								.data('tourId', tourId)
								.data('pointId', pointId);
							var tabChangeEvt = str_replace(subTab, '#', '')+ '_tabSwitch';
							jQuery(document).bind(tabChangeEvt, function(event, selector) {
								if(!g_tbsTourOpenedWithTab && !g_tbsAdminTourDissmissed) {
									var $clickTab = jQuery('a[href="'+ selector+ '"]');
									_tbsOpenPointer($clickTab.data('tourId'), $clickTab.data('pointId'));
								}
							});
						}
					}
				}
			}
		}
	}, 500);
});

function _tbsOpenPointerAndPopupTab(tourId, pointId, tab) {
	g_tbsTourOpenedWithTab = true;
	jQuery('#tbsPopupEditTabs').wpTabs('activate', tab);
	_tbsOpenPointer(tourId, pointId);
	g_tbsTourOpenedWithTab = false;
}
function _tbsOpenPointer(tourId, pointId) {
	var pointer = tbsAdminTourData.tour[ tourId ].points[ pointId ];
	var $content = tbsAdminTourData._$.find('#supsystic-'+ tourId+ '-'+ pointId);
	if(!jQuery(pointer.target) || !jQuery(pointer.target).size())
		return;
	if(g_tbsCurrTour) {
		_tbsTourSendNext(g_tbsCurrTour._tourId, g_tbsCurrTour._pointId);
		g_tbsCurrTour.element.pointer('close');
		g_tbsCurrTour = null;
	}
	if(pointer.sub_tab && jQuery('#tbsPopupEditTabs').wpTabs('getActiveTab') != pointer.sub_tab) {
		return;
	}
	var options = jQuery.extend( pointer.options, {
		content: $content.find('.supsystic-tour-content').html()
	,	pointerClass: 'wp-pointer supsystic-pointer'
	,	close: function() {
			//console.log('closed');
		}
	,	buttons: function(event, t) {
			g_tbsCurrTour = t;
			g_tbsCurrTour._tourId = tourId;
			g_tbsCurrTour._pointId = pointId;
			var $btnsShell = $content.find('.supsystic-tour-btns')
			,	$closeBtn = $btnsShell.find('.close')
			,	$finishBtn = $btnsShell.find('.supsystic-tour-finish-btn');

			if($finishBtn && $finishBtn.size()) {
				$finishBtn.click(function(e){
					e.preventDefault();
					jQuery.sendFormTbs({
						msgElID: 'noMessages'
					,	data: {mod: 'supsystic_promo', action: 'addTourFinish', tourId: tourId, pointId: pointId}
					});
					g_tbsCurrTour.element.pointer('close');
				});
			}
			if($closeBtn && $closeBtn.size()) {
				$closeBtn.bind( 'click.pointer', function(e) {
					e.preventDefault();
					jQuery.sendFormTbs({
						msgElID: 'noMessages'
					,	data: {mod: 'supsystic_promo', action: 'closeTour', tourId: tourId, pointId: pointId}
					});
					t.element.pointer('close');
					g_tbsAdminTourDissmissed = true;
				});
			}
			return $btnsShell;
		}
	});
	jQuery(pointer.target).pointer( options ).pointer('open');
	var minTop = 10
	,	pointerTop = parseInt(g_tbsCurrTour.pointer.css('top'));
	if(!isNaN(pointerTop) && pointerTop < minTop) {
		g_tbsCurrTour.pointer.css('top', minTop+ 'px');
	}
}
function _tbsTourSendNext(tourId, pointId) {
	jQuery.sendFormTbs({
		msgElID: 'noMessages'
	,	data: {mod: 'supsystic_promo', action: 'addTourStep', tourId: tourId, pointId: pointId}
	});
}