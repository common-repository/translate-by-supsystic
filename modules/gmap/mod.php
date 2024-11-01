<?php
class gmapTbs extends moduleTbs {
	public function init() {
		if(is_admin()) {
			add_action('gmp_lang_tabs', array($this, 'addTopSwitches'));
			add_filter('gmp_before_marker_save', array($this, 'updateData'));
			add_action('gmp_save_lang_data', array($this, 'saveData'));
		}
		add_filter('gmp_after_simple_get', array($this, 'getFields'));
	}
	public function availableFields() {
		return array('marker_title', 'marker_description');
	}
	public function addTopSwitches() {
		$langMod = frameTbs::_()->getModule('lang');
		echo $langMod->getView()->addTopSwitches( null, 'gmap' )
			. $langMod->getView()->addSwitchFields();
	}
	public function getFields($map) {
		$langData = get_option( "tbs_gmp_{$map['id']}_markers" );
		$langData = $this->_afterSimpleOptionGet($langData);
		if(!empty($langData)) {
			$locale = frameTbs::_()->getModule('lang')->getLocale();
			if(is_admin()) {
				$map['params']['_tbs_lang_data'] = $langData;
			} else {
				if(!empty($map['markers'])) {
					foreach($map['markers'] as &$marker) {
						if(!empty($langData[$marker['id']]) && !empty($langData[$marker['id']][$locale])) {
							$cutTitle = $langData[$marker['id']][$locale]['marker_title'];
							$cutDesc = $langData[$marker['id']][$locale]['marker_description'];
							$marker['title'] = $cutTitle ? $cutTitle : $marker['title'];
							$marker['description'] = $cutDesc ? $cutDesc : $marker['description'];
						}
					}
				}
			}
		}
		return $map;
	}
	public function saveData($data) {
		if(!empty($data['map'])) {
			$transData = reqTbs::getVar('tbs', 'post');
			if(!empty($transData)) {
				$optName = "tbs_gmp_{$data['map']['id']}_{$data['type']}";
				$langData = get_option( $optName );
				if(!empty($langData)) {
					$langData[$data['marker_id']] = $transData;
					update_option( $optName, $langData );
				} else {
					update_option( $optName, array($data['marker_id'] => $transData) );
				}
			}
		}
	}
	public function updateData($marker) {
		$transData = reqTbs::getVar('tbs', 'post');
		$defLang = frameTbs::_()->getModule('options')->get('def_lang');
		$availableFields = $this->availableFields();
		if(!empty($transData) && !empty($transData[$defLang])) {
			// Save translatable fields as it was set for default language
			foreach($availableFields as $f) {
				if(!empty($transData[$defLang][$f])) {
					$marker[str_replace('marker_', '', $f)] = $transData[$defLang][$f];
				}
			}
		}
		return $marker;
	}
	private function _afterSimpleOptionGet($langData) {
		if(!empty($langData)) {
			foreach($langData as $m => $md) {
				if(!empty($md)) {
					foreach($md as $l => $ld) {
						if(!empty($ld)) {
							foreach($ld as $f => $fd) {
								if(!empty($fd)) {
									$langData[$m][$l][$f] = stripslashes($langData[$m][$l][$f]);
								}
							}
						}
					}
				}
			}
		}
		return $langData;
	}
}