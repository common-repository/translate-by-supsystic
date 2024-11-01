<?php
class langTbs extends moduleTbs {
	private $_lang = '';
	private $_locale = '';
	private $_wpLangCode = '';
	private $_localeDetectedFromUrl = false;
	private $_server = array();
	private $_generalOptSaveChecked = false;
	private $_allowModifyHomeUrl = false;
	private $_useBlockEditor = false;
	private $_transPostMetaFieldsInited = false;
	private $_transPostMetaFields = array(
		'_wp_attachment_image_alt'
	);
	private $_registeredTaxonomies = array();

	public function getLocale() {
		// For using in another modules and compatibility with another supsystic plugins
		return $this->_locale;
	}

	public function init() {
		$this->isBlockEditorEnabled();
		dispatcherTbs::addAction('beforeSaveOpts', array($this, 'beforeOptionsSave'));
		if(is_admin()) {
			$this->_addAdminActions();
		}
		$postTypes = get_post_types();
		if($this->useBlockEditor()) {
			foreach($postTypes as $postType) {
				// is_admin() for those API requests will not work
				add_filter('rest_prepare_'.$postType, array($this, 'checkPostTranslationsAdmin'), 10, 3);
			}
			add_action('wp_insert_post_data', array($this, 'savePostMetaWp5'), 10, 2);
		}
		foreach($postTypes as $postType) {
			add_filter('get_'.$postType.'_metadata', array($this, 'checkPostsMetaTranslations'), 10, 4);
		}
		add_action('save_post', array($this, 'savePostMeta'), 10, 3);
		add_action('plugins_loaded', array($this, 'afterPluginsLoaded'));
		add_action('get_header', array($this, 'onGetHeader'));
		add_filter('admin_url', array($this, 'modifyAjaxUrl'), 10, 3);
		add_filter('page_link', array($this, 'modifyPageLink'), 10, 3);
		//add_filter('redirect_canonical', array($this, 'checkRedirectCanonical'), 10, 2);
		// TODO: Add translation for WP Meta Data
		// TODO: Add translations for WP Menu Items
		// TODO: Add translations for WP widgets
		// TODO: Add filter for all URLs on site - to add there lang attribute - like "redirect_canonical", URLs for menu items, posts, pages
		// TODO: CHeck it's work with pagenum, maybe will required "get_pagenum_link" filter usage
		add_filter('get_pagenum_link', array($this, 'pageNumFix'));
		// TODO: Add current lang icon + name in admin top bar - http://prntscr.com/eoebl8
		// TODO: Add translations for all categories and tags
		// TODO: Add option for detection browser code - and select language based on it
	}
	public function modifyAjaxUrl($url, $path, $blogId) {
		if(strpos($path, 'admin-ajax.php') !== false) {
			$langToAjax = frameTbs::_()->getModule('options')->get('lang_to_ajax');
			if(!empty($langToAjax) && (int) $langToAjax) {
				$this->collectServerData();
				$url = $this->addLangToUrl($url, false, 'query');
			}
		}
		return $url;
	}
	public function modifyPageLink($link, $post_id, $sample) {
		$lang = reqTbs::getVar('lang');
		if(!empty($lang)) {
			$this->collectServerData();
			if(uri::isHttps()) {
				$link = uri::makeHttps($link);
			}
			$link = $this->addLangToUrl($link, $lang);
		}
		return $link;
	}
	public function useBlockEditor() {
		return $this->_useBlockEditor;
	}
	public function isBlockEditorEnabled() {
		global $wp_version;
		$isWP5 = $wp_version && version_compare($wp_version, '5') >= 0;
		$classicEditorEnabled = class_exists('Classic_Editor');
		$defEditorMode = get_option('classic-editor-replace', 'classic');
		$allowUsersChooseEditor = get_option('classic-editor-allow-users', 'disallow');
		// It is not correct. Option classic-editor-allow-users allows to choose eitor type for each user. So wp5 actions
		if(!function_exists('get_userdata')) {
			include_once( ABSPATH . 'wp-includes/pluggable.php' );
		}
		$this->_useBlockEditor = is_admin()
			&& $isWP5
			&& (!$classicEditorEnabled
				|| ($classicEditorEnabled
					&& (($allowUsersChooseEditor === 'disallow' && $defEditorMode === 'block')
						|| ($allowUsersChooseEditor === 'allow' && get_user_option('classic-editor-settings') === 'block')
					)
				)
			);
	}
	public function beforeOptionsSave( $d ) {
		// Check if we need install additional language packages
		$langsToInstall = array();
		if($d['opt_values']['def_lang'] != frameTbs::_()->getModule('options')->get('def_lang')) {
			$langsToInstall[] = $d['opt_values']['def_lang'];
		}
		if(!empty($d['opt_values']['langs']) &&
			$d['opt_values']['langs'] != frameTbs::_()->getModule('options')->get('langs')
		) {
			$langsToInstall = array_merge($langsToInstall, $d['opt_values']['langs']);
		}
		if(!empty($langsToInstall)) {
			$langsToInstall = array_unique($langsToInstall);
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );

			if ( function_exists('wp_can_install_language_pack') && wp_can_install_language_pack() ) {
				$allLanguages = frameTbs::_()->getModule('options')->getAlllanguages();
				$allWpLangCodes = array();
				foreach($langsToInstall as $lang) {
					$wpLangCode = $this->langToWpCode($lang, $allLanguages);
					if($wpLangCode != $lang)
						$allWpLangCodes[] = $wpLangCode;
				}
				if(!empty($allWpLangCodes))
					$langsToInstall = array_merge($langsToInstall, $allWpLangCodes);

				foreach($langsToInstall as $lang) {
					wp_download_language_pack( $lang );
				}
			}
		}
	}
	public function langToWpCode($lang, $allLanguages = array()) {
		$allLanguages = empty($allLanguages) ? frameTbs::_()->getModule('options')->getAlllanguages() : $allLanguages;
		foreach($allLanguages as $lCode => $l) {
			if(isset($l['iso']) && !empty($l['iso'])) {
				foreach($l['iso'] as $iso) {
					if($iso == $lang) {
						return $lCode;
					}
				}
			}
		}
		return $lang;
	}
	private function _addAdminActions() {
		if($this->useBlockEditor()) {
			add_action('block_editor_no_javascript_message', array($this, 'addPostTopSwitches'));
		} else {
			add_action('edit_form_top', array($this, 'addPostTopSwitches'));
		}
		/*$taxonomies = get_taxonomies();
		if( !empty($taxonomies) ) {
			foreach($taxonomies as $taxonomy) {
				add_action("{$taxonomy}_pre_add_form", array($this, 'addTaxTopSwitches'));
				add_action("{$taxonomy}_add_form_fields", array($this, 'addTaxFieldsSwitches'));
				add_action("{$taxonomy}_term_edit_form_top", array($this, 'addTaxEditSwitches'));
			}
		}*/
		add_action('registered_taxonomy', array($this, 'addTaxonomiesActions'), 10, 3);
		add_action('create_term', array($this, 'saveTermMeta'));
		add_action('edited_terms', array($this, 'saveTermMeta'));
		add_action('admin_footer', array($this, 'addAdminFooterData'));
		add_filter('wp_edit_nav_menu_walker', array($this, 'checkAdminNavMenuWalker'));
		// General opts update
		$generalOptsFields = $this->availableOptsFields();
		foreach($generalOptsFields as $f) {
			add_filter("pre_update_option_{$f}", array($this, 'saveOptMeta'), 10, 3);
		}
	}
	public function saveOptMeta( $value, $old_value, $option ) {
		if(!$this->_generalOptSaveChecked
			&& in_array($option, $this->availableOptsFields())
		) {
			$transData = reqTbs::getVar('tbs', 'post');
			if(!empty($transData)) {
				foreach($transData as $lang => $termFields) {
					foreach($termFields as $field => $fData) {
						update_option( "tbs_{$field}_{$lang}", $fData );
					}
				}
			}
			$this->_generalOptSaveChecked = true;
		}
		return $value;
	}

	public function addTaxonomiesActions($taxonomy, $object_type, $taxonomy_object) {
		if(!in_array($taxonomy, $this->_registeredTaxonomies)) {
			array_push($this->_registeredTaxonomies, $taxonomy);
			add_action("{$taxonomy}_pre_add_form", array($this, 'addTaxTopSwitches'));
			add_action("{$taxonomy}_add_form_fields", array($this, 'addTaxFieldsSwitches'));
			add_action("{$taxonomy}_term_edit_form_top", array($this, 'addTaxEditSwitches'));
		}
	}
	public function addTaxEditSwitches( $tag ) {
		echo $this->getView()->addTopSwitches( $tag, 'term_edit' )
			. $this->getView()->addSwitchFields();
	}
	public function saveTermMeta( $termId ) {
		$transData = reqTbs::getVar('tbs', 'post');
		if( !empty($transData) ) {
			foreach($transData as $lang => $termFields) {
				foreach($termFields as $field => $fData) {
					update_term_meta( $termId, "tbs_{$field}_{$lang}", $fData );
				}
			}
		}
	}
	public function addTaxTopSwitches() {
		echo $this->getView()->addTopSwitches( null, 'term' );
	}
	public function addTaxFieldsSwitches() {
		echo $this->getView()->addSwitchFields();
	}
	private function _translateTerm( $term, $fields = array() ) {
		if(empty($fields))
			$fields = $this->availableTermFields();
		foreach($fields as $f) {
			if(isset($term->{ $f })) {
				$fieldTrans = $this->getTermField( $term->term_id, $f, $this->_locale );
				if(empty($fieldTrans)) {
					switch( frameTbs::_()->getModule('options')->get('no_translation_act') ) {
						case 'show_def':
							$fieldTrans = $term->{ $f };
							break;
						case 'show_empty':
							// It's already empty
							break;
					}
				}
				$term->{ $f } = $fieldTrans;
			}
		}
		return $term;
	}
	public function checkTermsTranslations( $terms ) {
		if(!empty($terms)
			&& $this->_locale
			&& $this->_locale != frameTbs::_()->getModule('options')->get('def_lang')
		) {
			if(is_array($terms)) {
				$fields = $this->availableTermFields();
				foreach($terms as $i => $t) {
					$this->_translateTerm( $t, $fields );
				}
			} else {
				$terms = $this->_translateTerm( $terms );
			}
		}
		return $terms;
	}
	public function checkPostsTranslations( $posts, $for = 'post' ) {
		if(!empty($posts)
			&& $this->_locale
			&& $this->_locale != frameTbs::_()->getModule('options')->get('def_lang')
		) {
			switch( $for ) {
				case 'nav-menus':
					$fields = $this->availableMenuFields();
					break;
				case 'post': default:
					$fields = $this->availablePostFields();
					break;
			}
			foreach($posts as $i => $p) {
				foreach($fields as $f) {
					if(isset($p->{ $f })) {
						$fieldTrans = $this->getPostField( $p->ID, $f, $this->_locale );
						if(empty($fieldTrans)) {
							switch( frameTbs::_()->getModule('options')->get('no_translation_act') ) {
								case 'show_def':
									$fieldTrans = $p->{ $f };
									break;
								case 'show_empty':
									// It's already empty
									break;
							}
						}
						$p->{ $f } = $fieldTrans;
					}
				}
			}
		}
		return $posts;
	}
	public function checkTitleTranslations($title, $postId) {
		if(!is_admin()) {
			$defLang = frameTbs::_()->getModule('options')->get('def_lang');
			if($this->_locale != $defLang) {
				$fieldTrans = $this->getPostField($postId, 'post_title', $this->_locale, true);
				if($fieldTrans === '') {
					switch( frameTbs::_()->getModule('options')->get('no_translation_act') ) {
						case 'show_def':
							$fieldTrans = $title;
							break;
						case 'show_empty':
							// it is already empty
							break;
					}
				}
				$title = $fieldTrans;
			}
		}
		return $title;
	}
	public function checkExcerptTranslations($excerpt, $post) {
		if(!is_admin()) {
			$defLang = frameTbs::_()->getModule('options')->get('def_lang');
			if($this->_locale != $defLang) {
				$fieldTrans = $this->getPostField($post->ID, 'post_excerpt', $this->_locale, true);
				if($fieldTrans === '') {
					switch( frameTbs::_()->getModule('options')->get('no_translation_act') ) {
						case 'show_def':
							$fieldTrans = $excerpt;
							break;
						case 'show_empty':
							// it is already empty
							break;
					}
				}
				$excerpt = $fieldTrans;
			}
		}
		return $excerpt;
	}
	public function checkPostsMetaTranslations($value, $postId, $metaKey, $single) {
		if(!is_admin()) {
			$defLang = frameTbs::_()->getModule('options')->get('def_lang');
			$transMetaFields = $this->availablePostMetaFields();
			if(strpos($metaKey, 'tbs_') !== 0 && in_array($metaKey, $transMetaFields) && $this->_locale != $defLang) {
				$fieldTrans = $this->getPostField($postId, $metaKey, $this->_locale, $single);
				if(($single && $fieldTrans === '') || (!$single && $fieldTrans === array())) {
					switch( frameTbs::_()->getModule('options')->get('no_translation_act') ) {
						case 'show_def':
							$fieldTrans = $value;
							break;
						case 'show_empty':
							$fieldTrans = '';
							break;
					}
				}
				$value = $single ? $fieldTrans : array($fieldTrans);
			}
		}
		return $value;
	}
	public function checkRedirectCanonicalLang() {
		global $wp_rewrite, $is_IIS, $wp_query, $wpdb, $wp;
		if ( is_trackback() || is_search() || is_admin() || is_preview() || is_robots() || ( $is_IIS && !iis7_supports_permalinks() ) ) {
			return;
		}
		// wp does same thing in it's redirect_canonical() function
		// Problem is that it is not recognize http://site.com/en
		// it should be exactly http://site.com/en/ - with final slash
		if(is_404() && $this->_localeDetectedFromUrl) {
			if(substr($this->_server['full_url'], -2) == $this->_lang) {
				wp_redirect($this->_server['full_url']. '/', 301);
				exit();
			}
		}
	}
	public function checkRedirectCanonical( $redirect_url, $requested_url ) {
		// TODO: Add here check for additional GET parameters
		if(trim($redirect_url, '/') == trim($requested_url, '/'))
			return false;
		return $redirect_url;
	}
	public function collectServerData() {
		$this->_server['sheme'] = is_ssl() ? 'https' : 'http';
		$this->_server['host'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		$this->_server['request'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		$this->_server['full_url'] = $this->_server['sheme']. '://'. $this->_server['host']. $this->_server['request'];
		$this->_server['wp_site_url'] = get_site_url();
		$this->_server['query'] = trim(str_replace($this->_server['wp_site_url'], '', $this->_server['full_url']), '/');
	}
	public function afterPluginsLoaded() {
		// TODO: Add here additional check for admin area and ajax requests
		if(is_admin()) {
			return;
		};
		global $pagenow;
		$this->collectServerData();
		$supportedLangs = frameTbs::_()->getModule('options')->get('langs');
		if( !empty($this->_server['query']) ) {
			$queryVars = explode('/', $this->_server['query']);
			if(isset($queryVars[ 0 ])) {
				switch( frameTbs::_()->getModule('options')->get('url_mode') ) {
					case 'query':
						foreach($queryVars as $q) {
							if(strpos($q, '?') === 0) {	// This can be GET params
								parse_str(substr($q, 1), $params);
								if(!empty($params['lang'])) {
									foreach($supportedLangs as $sl) {
										if(strpos($sl, $params['lang']) === 0) {
											$this->_lang = $params['lang'];
											$this->_locale = $sl;
											$this->_localeDetectedFromUrl = true;
											//$_SERVER['REQUEST_URI'] = str_replace('&lang='. $this->_lang, '', $_SERVER['REQUEST_URI']);
										}
									}
								}
							}
						}
						break;
					case 'pre_path':
						foreach($queryVars as $q) {
							if (strlen($q) == 2) {    // This can be lang
								foreach ($supportedLangs as $sl) {
									if (strpos($sl, $q) === 0) {
										$this->_lang = $q;
										$this->_locale = $sl;
										$this->_localeDetectedFromUrl = true;
										$_SERVER['REQUEST_URI'] = str_replace('/' . $this->_lang . '/', '/', $_SERVER['REQUEST_URI']);	// It does not work for plain permalinks
									}
								}
							}
						}
						break;
				}
			}
			$this->_server['query_vars'] = $queryVars;
		}
		if(empty($this->_lang)) {
			$detectUserLang = (int)frameTbs::_()->getModule('options')->get('detect_lang');

			if($detectUserLang) {
				$userLang = utilsTbs::getBrowserLangCode();

				foreach ($supportedLangs as $sl) {
					if (strpos($sl, $userLang) === 0) {
						$this->_lang = $userLang;
						$this->_locale = $sl;
					}
				}
			}
			if(empty($this->_lang)) {
				$defLang = frameTbs::_()->getModule('options')->get('def_lang');
				$this->_lang = $this->localeToLang( $defLang );
				$this->_locale =$defLang;
			}
		}
		$this->_addUrlModificationFilters();
	}
	public function onGetHeader() {
		add_filter('home_url', array($this, 'checkHomeUrl'));
	}
	private function _addUrlModificationFilters() {
		add_filter('post_link', array($this, 'modifyPostUrls'), 10, 2);
		add_filter('wp_nav_menu_objects', array($this, 'modifyMenuUrls'));
		add_filter('term_link', array($this, 'modifyTermsUrls'));

		add_filter('posts_results', array($this, 'checkPostsTranslations'));
		add_filter('the_title', array($this, 'checkTitleTranslations'), 10, 2);
		add_filter('get_the_excerpt', array($this, 'checkExcerptTranslations'), 10, 2);
		// Maybe we will need to add here get_terms filter with same callback - just try it if you will see that terms in some places is not translated correctly
		add_filter('get_the_terms', array($this, 'checkTermsTranslations'));
		add_filter('get_term', array($this, 'checkTermsTranslations'));
		// TODO: Complete those filters
		add_filter('template_redirect', array($this, 'checkRedirectCanonicalLang'));
		add_filter('wp', array($this, 'allowModifyHomeUrl'), 99);
		add_filter('locale', array($this, 'modifyLocale'));
		add_filter('bloginfo_url', array($this, 'checkBlogInfoUrls'), 10, 2);
		add_filter('wp_get_nav_menu_items', array($this, 'checkMenusTranslations'));
		// General opts translations
		$generalOptsFields = $this->availableOptsFields();
		foreach($generalOptsFields as $f) {
			add_filter("pre_option_{$f}", array($this, 'checkOptTranslation'), 10, 2);
		}
	}
	public function checkOptTranslation( $false, $option ) {
		if($this->_locale
			&& $this->_locale != frameTbs::_()->getModule('options')->get('def_lang')
		) {
			$fieldTrans = $this->getOptField( $option, $this->_locale );
			if( !empty($fieldTrans) )
				return $fieldTrans;
			if(empty($fieldTrans)) {
				switch( frameTbs::_()->getModule('options')->get('no_translation_act') ) {
					case 'show_def':
						return $false;	// Continue geting it from database
					case 'show_empty':
						// It's already empty
						break;
				}
			}
			return $fieldTrans;
		}
		return $false;
	}
	public function modifyTermsUrls( $termlink ) {
		if(!empty($termlink)) {
			$termlink = $this->addLangToUrl( $termlink );
		}
		return $termlink;
	}
	public function modifyMenuUrls( $items ) {
		if(!empty( $items )) {
			foreach($items as $i => $item) {
				if(isset($item->url)) {
					$items[ $i ]->url = $this->addLangToUrl( $item->url );
				}
			}
		}
		return $items;
	}
	public function modifyPostUrls( $permalink, $post ) {
		if(!empty($permalink)) {
			$permalink = $this->addLangToUrl( $permalink );
		}
		return $permalink;
	}
	public function addLangToUrl( $url, $lang = false, $urlMode = '' ) {
		$updateLang = $lang ? true : false;
		$lang = $lang ? $lang : $this->_lang;
		$defLang = $this->localeToLang(frameTbs::_()->getModule('options')->get('def_lang'));
		$hideDef = (int)frameTbs::_()->getModule('options')->get('hide_def_lang');
		$urlMode = !empty($urlMode) ? $urlMode : frameTbs::_()->getModule('options')->get('url_mode');
		if(!$hideDef || ($hideDef && $lang != $defLang) || $updateLang) {
			if(strpos($url, $this->_server['wp_site_url']) === 0) {
				$urlGet = explode('?', $url);
				$url = $urlGet[ 0 ];
				$hasLastSlash = strrpos($url, '/') == (strlen($url) - 1);
				$urlQuery = explode('/', trim(str_replace($this->_server['wp_site_url'], '', $url), '/'));
				if(is_array($urlQuery) && !empty($urlQuery))
					$urlQuery = array_filter($urlQuery);
				switch($urlMode) {
					case 'query':
						$url = $this->_server['wp_site_url']. '/'. implode('/', $urlQuery);
						if($urlGet && isset($urlGet[ 1 ])) {
							parse_str($urlGet[ 1 ], $params);
							if(empty($params['lang']) || (!empty($params['lang']) && $params['lang'] != $lang)) {
								$params['lang'] = $lang;
							}
						} else {
							$params = array('lang' => $lang);
						}
						if($updateLang && $hideDef && !empty($params['lang']) && $params['lang'] == $defLang) {
							unset($params['lang']);
						}
						if(!empty($params)) {
							$url .= '?'. http_build_query($params);
						}
						break;
					case 'pre_path':
						if($urlQuery && isset($urlQuery[ 0 ])) {
							if($urlQuery[ 0 ] != $lang) {
								array_unshift($urlQuery, $lang);
							}
						} else {
							$urlQuery = array($lang);
						}
						if($updateLang && $hideDef && !empty($urlQuery[0]) && $urlQuery[0] == $defLang) {
							unset($urlQuery[0]);
						}
						$url = $this->_server['wp_site_url']. '/'. implode('/', $urlQuery);
						if( $hasLastSlash && substr($url, -1) != '/') {
							$url .= '/';
						}
						if(isset($urlGet[ 1 ])) {
							$url .= '?'. $urlGet[ 1 ];
						}
						break;
				}
			}
		}
		return $url;
	}
	public function pageNumFix($url) {
		$url_fixed = preg_replace('#\?lang=[a-z]{2}/#i', '/', $url); //kind of ugly fix for function get_pagenum_link in /wp-includes/link-template.php. Maybe we should cancel filter 'bloginfo_url' instead?
		return $url_fixed;
	}
	public function modifyLocale( $locale ) {
		if(is_admin()) return $locale;
		if($this->_locale && !$this->_wpLangCode) {
			$this->_wpLangCode = $this->langToWpCode($this->_locale);
		}
		return $this->_wpLangCode ? $this->_wpLangCode : $locale;
	}
	public function allowModifyHomeUrl() {
		$this->_allowModifyHomeUrl = true;
	}
	public function checkHomeUrl( $url ) {
		if(!$this->_allowModifyHomeUrl)
			return $url;
		// TODO: Modify this if required for default or other languages by adding lang to it
		if(!empty($this->_lang)) {
			$url = $this->addLangToUrl( $url );
		}
		return $url;
	}
	public function checkBlogInfoUrls($url, $what) {
		switch($what) {
			case 'stylesheet_url':
			case 'template_url':
			case 'template_directory':
			case 'stylesheet_directory':
				return $url;
			default: return $this->addLangToUrl( $url );	// TODO: URL here mustbe converted with lang param!
		}
	}
	/**/
	public function addPostTopSwitches( $post ) {
		echo $this->getView()->addTopSwitches( $post, 'post' );
		if(!$this->useBlockEditor()) {
			echo $this->getView()->addSwitchFields();
		}
	}
	public function getLangFlagUrl( $code, $size = '' ) {
		$url = '';
		$size = empty($size) ? 16 : $size;
		$code = strtolower(str_replace('_', '-', $code));
		if(file_exists($this->getModDir(). 'flags'. DS. $size. DS. $code. '.png')) {
			$url = $this->getModPath(). 'flags/'. $size. '/'. $code. '.png';
		} else {
			if(strpos($code, '-')) {
				$codeSuf = explode('-', $code);
				$code = $codeSuf[ 0 ];
				if(file_exists($this->getModDir(). 'flags'. DS. $size. DS. $code. '.png')) {
					$url = $this->getModPath(). 'flags/'. $size. '/'. $code. '.png';
				}
			}
		}
		return $url;
	}
	/**
	 * Fields available for translation
	 * @return array
	 */
	public function availablePostFields($post = null) {
		$availablePostMetaFields = $this->availablePostMetaFields();
		if(!empty($post)) {
			$existedPostFields = array();
			foreach($availablePostMetaFields as $field) {
				$value = get_post_meta($post->ID, $field);
				if(!empty($value)) {
					array_push($existedPostFields, $field);
				}
			}
			$availablePostMetaFields = $existedPostFields;
		}
		return array_merge(array('post_title', 'post_content', 'post_excerpt'), $availablePostMetaFields);
	}
	public function availablePostMetaFields() {
		if(!$this->_transPostMetaFieldsInited) {
			$transMetaFields = frameTbs::_()->getModule('options')->get('trans_meta_fields');
			if(!empty($transMetaFields)) {
				$this->_transPostMetaFields = array_merge($this->_transPostMetaFields, array_filter(array_map('trim', explode(',', $transMetaFields))));
			}
			$this->_transPostMetaFieldsInited = true;
		}
		return $this->_transPostMetaFields;
	}
	public function availableTermFields() {
		return array('name', 'description');
	}
	public function availableMenuFields() {
		return array('title');
	}
	public function availableOptsFields() {
		return array('blogname', 'blogdescription');
	}
	public function availableCustomFields() {
		return apply_filters('tbs-custom-translatable-fields', array());
	}
	public function getTermField( $id, $field, $lang ) {
		return get_term_meta( $id, "tbs_{$field}_{$lang}", true );
	}
	public function getPostField( $id, $field, $lang, $single = true ) {
		return get_post_meta( $id, "tbs_{$field}_{$lang}", $single );
	}
	public function getOptField( $field, $lang ) {
		return get_option( "tbs_{$field}_{$lang}" );
	}
	public function checkPostTranslationsAdmin($response, $post, $request) {
		$lang = $this->getLangAdmin('REQUEST_URI');
		$isPassport = post_password_required($post);
		$title = get_post_meta($post->ID, 'tbs_post_title_'.$lang, true);
		if(!empty($title)) {
			$response->data['title']['raw'] =$title;
			$response->data['title']['rendered'] = apply_filters('the_title', $title);
		}
		$excerpt = get_post_meta($post->ID, 'tbs_post_excerpt_'.$lang, true);
		if(!empty($excerpt)) {
			$response->data['excerpt']['raw'] = $excerpt;
			$response->data['excerpt']['rendered'] = $isPassport ? '' :  apply_filters('the_excerpt', apply_filters('get_the_excerpt', $excerpt, $post));
		}
		$content = get_post_meta($post->ID, 'tbs_post_content_'.$lang, true);
		if(!empty($content)) {
			$response->data['content']['raw'] = $content;
			$response->data['content']['rendered'] = $isPassport ? '' : apply_filters('the_content', $content);
		}
		return $response;
	}
	public function savePostMetaWp5($data, $postArr) {
		if(!function_exists('use_block_editor_for_post')) {
			include_once( ABSPATH . 'wp-admin/includes/post.php' );
		}
		if(!wp_is_post_revision($postArr['ID']) && !isset($_GET['meta-box-loader']) && $this->useBlockEditor() && use_block_editor_for_post($postArr['ID'])) {
			$curLang = $this->getLangAdmin('HTTP_REFERER');
			$defLang = frameTbs::_()->getModule('options')->get('def_lang');
			$availablePostFields = $this->availablePostFields();
			foreach($availablePostFields as $field) {
				if(!empty($data[$field])) {
					update_post_meta( $postArr['ID'], "tbs_{$field}_{$curLang}", $data[$field] );
				}
			}
			if($curLang !== $defLang) {
				$title = get_post_meta($postArr['ID'], 'tbs_post_title_'.$defLang, true);
				if(!empty($title)) {
					$data['post_title'] = $title;
				}
				$excerpt = get_post_meta($postArr['ID'], 'tbs_post_excerpt_'.$defLang, true);
				if(!empty($excerpt)) {
					$data['post_excerpt'] = $excerpt;
				}
				$content = get_post_meta($postArr['ID'], 'tbs_post_content_'.$defLang, true);
				if(!empty($content)) {
					$data['post_content'] = $content;
				}
			}
		}
		return $data;
	}
	public function savePostMeta( $post_id, $post, $update ) {
		// If this is just a revision - don't update it's ID
		if(wp_is_post_revision($post_id) || isset($_GET['meta-box-loader'])) {
			return;
		}
		if(!function_exists('use_block_editor_for_post')) {
			include_once( ABSPATH . 'wp-admin/includes/post.php' );
		}
		if(($this->useBlockEditor() && use_block_editor_for_post($post))) {
			return;
		}
		$transData = reqTbs::getVar('tbs', 'post');
		if(!empty($transData)) {
			if($post->post_type == 'nav_menu_item') {
				$transData = $transData[ $post_id ];
			}
			foreach($transData as $lang => $postFields) {
				foreach($postFields as $field => $fData) {
					update_post_meta( $post_id, "tbs_{$field}_{$lang}", $fData );
				}
			}
		}
	}
	public function getLangAdmin($variable) {
		$reqData = array();
		parse_str(reqTbs::getVar($variable, 'server'), $reqData);
		$lang = $reqData && isset($reqData['lang']) && !empty($reqData['lang'])
			? $reqData['lang']
			: frameTbs::_()->getModule('options')->get('def_lang');
		return $lang;
	}
	public function localeToLang( $locale ) {
		return substr($locale, 0, 2);
	}
	public function fillInLangsInfo( $langs, $iconSize = false ) {
		$res = array();
		$langsLabels = frameTbs::_()->getModule('options')->get('lang_labels');
		$langsLabels = utilsTbs::decodeArrayTxt($langsLabels, true);
		$needReSetLangLabels = false;
		if($langsLabels && !empty($langsLabels)) {
			foreach($langs as $lCode) {
				if(!isset($langsLabels[ $lCode ])) {
					$needReSetLangLabels = true;
					break;
				}
			}
		} else
			$needReSetLangLabels = true;
		if($needReSetLangLabels) {
			$allLangs = frameTbs::_()->getModule('options')->getAvailbaleLangsSelect();
			if(empty( $langsLabels ))
				$langsLabels = array();
			foreach($langs as $lCode) {
				$langsLabels[ $lCode ] = $allLangs[ $lCode ];
			}
			$langsLabelsToSave = utilsTbs::encodeArrayTxt($langsLabels);
			frameTbs::_()->getModule('options')->getModel()->save('lang_labels', $langsLabelsToSave);
		}
		foreach($langs as $lCode) {
			$res[ $lCode ] = array(
				'code' => $this->localeToLang( $lCode ),
				'label' => $langsLabels[ $lCode ],
			);
			if( $iconSize ) {
				$res[ $lCode ]['icon'] = $this->getLangFlagUrl( $lCode );
			}
		}
		return $res;
	}
	public function getCurrLang() {
		return $this->_lang;
	}
	public function addAdminFooterData() {
		$screen = get_current_screen();
		if( $screen ) {
			switch( $screen->base ) {
				case 'nav-menus':
					echo $this->getView()->addTopSwitches( null, $screen->base );
					break;
				case 'options-general':
					echo $this->getView()->addTopSwitches( null, $screen->base )
						. $this->getView()->addSwitchFields( null, $screen->base );
					break;
			}
		}
	}
	public function checkAdminNavMenuWalker( $walkerClassName ) {
		$walkerClassName = 'Tbs_Walker_Nav_Menu_Edit';
		if(!class_exists( $walkerClassName )) {
			require_once($this->getModDir(). 'admin_nav_menu_walker.php');
		}
		return $walkerClassName;
	}
	public function checkMenusTranslations( $items ) {
		// Same as for posts for now
		return $this->checkPostsTranslations( $items, 'nav-menus' );
	}
}
