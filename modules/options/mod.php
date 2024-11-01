<?php
class optionsTbs extends moduleTbs {
	private $_tabs = array();
	private $_options = array();
	private $_optionsToCategoires = array();	// For faster search
	private $_allLanguages = array();

	public function init() {
		//dispatcherTbs::addAction('afterModulesInit', array($this, 'initAllOptValues'));
		$this->getAlllanguages();
		add_action('init', array($this, 'initAllOptValues'), 99);	// It should be init after all languages was inited (frame::connectLang)
		dispatcherTbs::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
	}
	public function initAllOptValues() {
		// Just to make sure - that we loaded all default options values
		$this->getAll();
	}
    /**
     * This method provides fast access to options model method get
     * @see optionsModel::get($d)
     */
    public function get($code) {
        return $this->getModel()->get($code);
    }
	/**
     * This method provides fast access to options model method get
     * @see optionsModel::get($d)
     */
	public function isEmpty($code) {
		return $this->getModel()->isEmpty($code);
	}
	public function getAllowedPublicOptions() {
		$allowKeys = array('add_love_link', 'disable_autosave');
		$res = array();
		foreach($allowKeys as $k) {
			$res[ $k ] = $this->get($k);
		}
		return $res;
	}
	public function getAdminPage() {
		if(installerTbs::isUsed()) {
			return $this->getView()->getAdminPage();
		} else {
			installerTbs::setUsed();	// Show this welcome page - only one time
			return frameTbs::_()->getModule('supsystic_promo')->showWelcomePage();
		}
	}
	public function addAdminTab($tabs) {
		$tabs['settings'] = array(
			'label' => __('Settings', TBS_LANG_CODE), 'callback' => array($this, 'getSettingsTabContent'), 'fa_icon' => 'fa-gear', 'sort_order' => 30,
		);
		return $tabs;
	}
	public function getSettingsTabContent() {
		return $this->getView()->getSettingsTabContent();
	}
	public function getTabs() {
		if(empty($this->_tabs)) {
			$this->_tabs = dispatcherTbs::applyFilters('mainAdminTabs', array(
				//'main_page' => array('label' => __('Main Page', TBS_LANG_CODE), 'callback' => array($this, 'getTabContent'), 'wp_icon' => 'dashicons-admin-home', 'sort_order' => 0), 
			));
			foreach($this->_tabs as $tabKey => $tab) {
				if(!isset($this->_tabs[ $tabKey ]['url'])) {
					$this->_tabs[ $tabKey ]['url'] = $this->getTabUrl( $tabKey );
				}
			}
			uasort($this->_tabs, array($this, 'sortTabsClb'));
		}
		return $this->_tabs;
	}
	public function sortTabsClb($a, $b) {
		if(isset($a['sort_order']) && isset($b['sort_order'])) {
			if($a['sort_order'] > $b['sort_order'])
				return 1;
			if($a['sort_order'] < $b['sort_order'])
				return -1;
		}
		return 0;
	}
	public function getTab($tabKey) {
		$this->getTabs();
		return isset($this->_tabs[ $tabKey ]) ? $this->_tabs[ $tabKey ] : false;
	}
	public function getTabContent() {
		return $this->getView()->getTabContent();
	}
	public function getActiveTab() {
		$reqTab = reqTbs::getVar('tab');
		return empty($reqTab) ? TBS_PLUG_MAIN_TAB : $reqTab;
	}
	public function getTabUrl($tab = '') {
		static $mainUrl;
		if(empty($mainUrl)) {
			$mainUrl = frameTbs::_()->getModule('adminmenu')->getMainLink();
		}
		return empty($tab) ? $mainUrl : $mainUrl. '&tab='. $tab;
	}
	public function getRolesList() {
		if(!function_exists('get_editable_roles')) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );
		}
		return get_editable_roles();
	}
	public function getAvailableUserRolesSelect() {
		$rolesList = $this->getRolesList();
		$rolesListForSelect = array();
		foreach($rolesList as $rKey => $rData) {
			$rolesListForSelect[ $rKey ] = $rData['name'];
		}
		return $rolesListForSelect;
	}
	public function getAlllanguages() {
		if(empty($this->_allLanguages)) {
			if(!function_exists('wp_get_available_translations') && file_exists(ABSPATH . 'wp-admin/includes/translation-install.php')) {
				require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
			}
			if(function_exists('wp_get_available_translations')) {	// As it was included only from version 4.0.0
				$this->_allLanguages = @wp_get_available_translations();
			}
			if(empty($this->_allLanguages)) {
				$this->_allLanguages = json_decode(file_get_contents($this->getModPath() . '/languages.json'), true);
				$this->_allLanguages = $this->_allLanguages['translations'];
			}
		}
		return $this->_allLanguages;
	}
	public function getAvailbaleLangsSelect() {
		// TODO: Remeber those 2 vars in this object - for future usage
		$currentLanguageCode = utilsTbs::getBrowserLangCode();
		$currentLanguage = '';
		$languagesForSelect = array('en_US' => 'English (United States)');
		$allLanguages = $this->getAlllanguages();

		foreach($allLanguages as $l) {
			if(!isset($l['iso']) || !isset($l['iso'][1])) {
				$isoCode = $l['language'];
			} else {
				$isoCode = $l['iso'][1];
			}
			if(isset($languagesForSelect[ $isoCode ])) {
				$isoCode = isset($l['iso'][2]) ? $l['iso'][2] : ( isset($l['iso'][3]) ? $l['iso'][3] : $l['language'] );
			}
			if(isset( $languagesForSelect[ $isoCode ]) ) {
				$isoCode = $l['language'];
			}
			$languagesForSelect[ $isoCode ] = $l['native_name'];
			if($currentLanguageCode == $isoCode) {
				$currentLanguage = $l['native_name'];
			}
		}
		return $languagesForSelect;
	}
	public function getAll() {
		if(empty($this->_options)) {
			$defLang = get_locale();
			$this->_options = dispatcherTbs::applyFilters('optionsDefine', array(
				'general' => array(
					'label' => __('General', TBS_LANG_CODE),
					'opts' => array(
						'def_lang' => array('label' => __('Default Language', TBS_LANG_CODE), 'desc' => __('Default language for your site.', TBS_LANG_CODE), 'def' => $defLang, 'html' => 'selectbox', 'options' => array($this, 'getAvailbaleLangsSelect')),
						'langs' => array('label' => __('Available Languages', TBS_LANG_CODE), 'desc' => __('List with available languages for your site.', TBS_LANG_CODE), 'def' => array( $defLang ), 'html' => 'selectlist', 'options' => array($this, 'getAvailbaleLangsSelect')),
						'url_mode' => array('label' => __('URL mode for Language', TBS_LANG_CODE), 'desc' => __('Define your site address with selected language in browser.', TBS_LANG_CODE), 'def' => 'pre_path', 'html' => 'radiobuttons', 'options' => array(
							'query' => __('Query Mode (not recomended, will add ?lang=en to your site address)', TBS_LANG_CODE),
							'pre_path' => __('Pre-Path Mode (recomended, will add /en/ to your site address)', TBS_LANG_CODE),
						)),
						'no_translation_act' => array('label' => __('If no Translation Available', TBS_LANG_CODE), 'desc' => __('If no translation for example exist for selected language - what we need to do?', TBS_LANG_CODE), 'def' => 'show_def', 'html' => 'selectbox', 'options' => array(
							'show_def' => __('Show default language content', TBS_LANG_CODE),
							'show_empty' => __('Show empty content', TBS_LANG_CODE),
						)),
						'hide_def_lang' => array('label' => __('Hide Language for Default', TBS_LANG_CODE), 'desc' => __('Hide language in URL address for the default language.', TBS_LANG_CODE), 'def' => '0', 'html' => 'checkboxHiddenVal'),
						'detect_lang' => array('label' => __('Detect User Language', TBS_LANG_CODE), 'desc' => __('Automatically detect user language and switch site content to its language if it exists.', TBS_LANG_CODE), 'def' => '0', 'html' => 'checkboxHiddenVal'),
						'trans_meta_fields' => array('label' => __('Names of Translatable Meta Fields', TBS_LANG_CODE), 'desc' => __('Paste here the names of post meta fields, that need to be translated. Separate them by comma.', TBS_LANG_CODE), 'def' => '', 'html' => 'textarea'),
						'lang_to_ajax' => array('label' => __('Add language to AJAX requests', TBS_LANG_CODE), 'desc' => __('Add GET parameter with current language value for all AJAX requests. If you do not know for what it can be needed - do not enable this option.', TBS_LANG_CODE), 'def' => '0', 'html' => 'checkboxHiddenVal'),
						'send_stats' => array('label' => __('Send Usage Statistics', TBS_LANG_CODE), 'desc' => __('Send information about what plugin options you prefer to use, this will help us make our solution better for You.', TBS_LANG_CODE), 'def' => '0', 'html' => 'checkboxHiddenVal'),
						'add_love_link' => array('label' => __('Enable Promo Link', TBS_LANG_CODE), 'desc' => __('We are trying to make our plugin better for you, and you can help us with this. Just check this option - and small promotion link will be added in the bottom of your PopUp. This is easy for you - but very helpful for us!', TBS_LANG_CODE), 'def' => '0', 'html' => 'checkboxHiddenVal'),
						'access_roles' => array('label' => __('User Role can use Plugin', TBS_LANG_CODE), 'desc' => __('User with next roles will have access to whole plugin from admin area.', TBS_LANG_CODE), 'def' => 'administrator', 'html' => 'selectlist', 'options' => array($this, 'getAvailableUserRolesSelect'), 'pro' => ''),
					),
				),
			));
			$isPro = frameTbs::_()->getModule('supsystic_promo')->isPro();
			foreach($this->_options as $catKey => $cData) {
				foreach($cData['opts'] as $optKey => $opt) {
					$this->_optionsToCategoires[ $optKey ] = $catKey;
					if(isset($opt['pro']) && !$isPro) {
						$this->_options[ $catKey ]['opts'][ $optKey ]['pro'] = frameTbs::_()->getModule('supsystic_promo')->generateMainLink('utm_source=plugin&utm_medium='. $optKey. '&utm_campaign=popup');
					}
				}
			}
			$this->getModel()->fillInValues( $this->_options );
		}
		return $this->_options;
	}
	public function getFullCat($cat) {
		$this->getAll();
		return isset($this->_options[ $cat ]) ? $this->_options[ $cat ] : false;
	}
	public function getCatOpts($cat) {
		$opts = $this->getFullCat($cat);
		return $opts ? $opts['opts'] : false;
	}
}

