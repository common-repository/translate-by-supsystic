<?php
class lang_widgetViewTbs extends viewTbs {
	public function displayForm($data, $widget) {
		$this->assign('data', $data);
        $this->assign('widget', $widget);
		parent::display('langWidgetForm');
	}
	public function displayWidget($args, $instance) {
		$title = empty( $instance['title'] ) ? '' : $instance['title'];

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$availableLangs = frameTbs::_()->getModule('options')->get('langs');
		$loadScripts = array();
		if( !empty($availableLangs) ) {
			$showFlag = isset($instance['show_flag']) && $instance['show_flag'] && $instance['flag_size'];
			$showOnlyFlag = isset($instance['show_only_flag']) && $instance['show_only_flag'] && $instance['show_only_flag'];
			if($showOnlyFlag) $showFlag = true;	// Even if Show Flag is not checked - if Show Only Flag checked t should be enabled
			$flagSize = $showFlag ? $instance['flag_size'] : false;
			$langs = frameTbs::_()->getModule('lang')->fillInLangsInfo( $availableLangs, 
				($showFlag ? $flagSize : false)
			);
			$currLang = frameTbs::_()->getModule('lang')->getCurrLang();
			$cont = '<span class="tbsLangs" data-type="'. $instance['display_type']. '" data-flag-size="'. ($flagSize ? $flagSize : 0). '">';
			$parts = array();
			$currUrl = uriTbs::getFullUrl();
			switch( $instance['display_type'] ) {
				case 'links_list':
					foreach($langs as $l) {
						$line = '';
						if($l['code'] != $currLang) {
							$line .= '<a href="'. esc_url( frameTbs::_()->getModule('lang')->addLangToUrl( $currUrl, $l['code'] ) ). '">';
						}
						if( $showFlag ) {
							$line .= $this->_generateFlagImg( $l['icon'], $flagSize );
						}
						if(!$showOnlyFlag)
							$line .= $l['label'];
						if($l['code'] != $currLang) {
							$line .= '</a>';
						}
						$parts[] = $line;
					}
					break;
				case 'buttons_list':
					$parts[] = '<style type="text/css">'
					. '.tbsLangBtn {margin-top: 5px;}'
					. '</style>';
					foreach($langs as $l) {
						$line = '';
						$line .= '<button class="tbsLangBtn" data-url="'. esc_url( frameTbs::_()->getModule('lang')->addLangToUrl( $currUrl, $l['code'] ) ). '">';
						if( $showFlag ) {
							$line .= $this->_generateFlagImg( $l['icon'], $flagSize );
						}
						if($l['code'] == $currLang) {
							$line .= '<b>';
						}
						if(!$showOnlyFlag)
							$line .= $l['label'];
						if($l['code'] == $currLang) {
							$line .= '</b>';
						}
						$line .= '</button>';
						$parts[] = $line;
					}
					$loadScripts['base'] = 1;
					break;
				case 'selectbox': case 'selectbox_cust':
					$langsForSelect = array();
					$val = '';
					$optAttrs = array();
					foreach($langs as $l) {
						$url = esc_url( frameTbs::_()->getModule('lang')->addLangToUrl( $currUrl, $l['code'] ) );
						if($l['code'] == $currLang) {
							$val = $url;
						}
						$langsForSelect[ $url ] = $l['label'];
						if( $showFlag ) {
							$optAttrs[ $url ] = 'data-flag="'. $l['icon']. '"';
						}
					}
					$parts[] = htmlTbs::selectbox('tbs_lang', array(
						'options' => $langsForSelect,
						'value' => $val,
						'attrs' => 'class="tbsLangSel"',
						'opts_attrs' => $optAttrs,
					));
					if( $instance['display_type'] == 'selectbox_cust' ) {
						$loadScripts['chosen'] = 1;
					}
					$loadScripts['base'] = 1;
					break;
			}
			$cont .= implode(' ', $parts);
			$cont .= '</span>';
			echo $cont;
			if( !empty($loadScripts) ) {
				if(isset($loadScripts['chosen'])) {
					frameTbs::_()->getModule('templates')->loadChosenSelects();
				}
				frameTbs::_()->addScript(TBS_CODE. '.frontend.lang_widget', $this->getModule()->getModPath(). 'js/frontend.lang_widget.js');
				frameTbs::_()->addJSVar(TBS_CODE. '.frontend.lang_widget', 'g_tbsWidgetData', array(
					'isRtl' => is_rtl()
				));
			}
		}
		echo $args['after_widget'];
	}
	private function _generateFlagImg( $icon, $flagSize ) {
		return '<img style="max-width: '. $flagSize. 'px; height: auto; width: '. $flagSize. 'px; display: inline;" src="'. $icon. '" />';
	}
}
