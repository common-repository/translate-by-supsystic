<?php
class templatesTbs extends moduleTbs {
    protected $_styles = array();
	private $_cdnUrl = '';
	
	public function __construct($d) {
		parent::__construct($d);
		$this->getCdnUrl();	// Init CDN URL
	}
	public function getCdnUrl() {
		if(empty($this->_cdnUrl)) {
			if((int) frameTbs::_()->getModule('options')->get('use_local_cdn')) {
				$uploadsDir = wp_upload_dir( null, false );
				$this->_cdnUrl = $uploadsDir['baseurl']. '/'. TBS_CODE. '/';
				if(uriTbs::isHttps()) {
					$this->_cdnUrl = str_replace('http://', 'https://', $this->_cdnUrl);
				}
				dispatcherTbs::addFilter('externalCdnUrl', array($this, 'modifyExternalToLocalCdn'));
			} else {
				$this->_cdnUrl = (uriTbs::isHttps() ? 'https' : 'http'). '://supsystic-42d7.kxcdn.com/';
			}
		}
		return $this->_cdnUrl;
	}
	public function modifyExternalToLocalCdn( $url ) {
		$url = str_replace(
			array('https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css'), 
			array($this->_cdnUrl. 'lib/font-awesome'), 
			$url);
		return $url;
	}
    public function init() {
        if (is_admin()) {
			if($isAdminPlugOptsPage = frameTbs::_()->isAdminPlugOptsPage()) {
				$this->loadCoreJs();
				$this->loadAdminCoreJs();
				$this->loadCoreCss();
				$this->loadChosenSelects();
				
				add_action('admin_enqueue_scripts', array($this, 'loadMediaScripts'));
				add_action('init', array($this, 'connectAdditionalAdminAssets'));
			}
			// Some common styles - that need to be on all admin pages - be careful with them
			frameTbs::_()->addStyle('supsystic-for-all-admin-'. TBS_CODE, TBS_CSS_PATH. 'supsystic-for-all-admin.css');
		}
        parent::init();
    }
	public function connectAdditionalAdminAssets() {
		if(is_rtl()) {
			frameTbs::_()->addStyle('styleTbs-rtl', TBS_CSS_PATH. 'style-rtl.css');
		}
	}
	public function loadMediaScripts() {
		if(function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
	}
	public function loadAdminCoreJs() {
		frameTbs::_()->addScript('jquery-ui-dialog');
		frameTbs::_()->addScript('jquery-ui-slider');
		frameTbs::_()->addScript('wp-color-picker');
		frameTbs::_()->addScript('icheck', TBS_JS_PATH. 'icheck.min.js');
		$this->loadTooltipster();
		frameTbs::_()->addScript('adminOptionsTbs', TBS_JS_PATH. 'admin.options.js', array(), false, true);
	}
	public function loadCoreJs() {
		static $loaded = false;
		if(!$loaded) {
			frameTbs::_()->addScript('jquery');

			frameTbs::_()->addScript('commonTbs', TBS_JS_PATH. 'common.js');
			frameTbs::_()->addScript('coreTbs', TBS_JS_PATH. 'core.js');

			$ajaxurl = admin_url('admin-ajax.php');
			$jsData = array(
				'siteUrl'					=> TBS_SITE_URL,
				'imgPath'					=> TBS_IMG_PATH,
				'cssPath'					=> TBS_CSS_PATH,
				'loader'					=> TBS_LOADER_IMG, 
				'close'						=> TBS_IMG_PATH. 'cross.gif', 
				'ajaxurl'					=> $ajaxurl,
				'options'					=> frameTbs::_()->getModule('options')->getAllowedPublicOptions(),
				'TBS_CODE'					=> TBS_CODE,
				//'ball_loader'				=> TBS_IMG_PATH. 'ajax-loader-ball.gif',
				//'ok_icon'					=> TBS_IMG_PATH. 'ok-icon.png',
				'jsPath'					=> TBS_JS_PATH,
			);
			if(is_admin()) {
				$jsData['isPro'] = frameTbs::_()->getModule('supsystic_promo')->isPro();
				$jsData['isAdminPlugPage'] = frameTbs::_()->isAdminPlugOptsPage();
			}
			$jsData = dispatcherTbs::applyFilters('jsInitVariables', $jsData);
			frameTbs::_()->addJSVar('coreTbs', 'TBS_DATA', $jsData);
			$loaded = true;
		}
	}
	public function loadCodemirror() {
		// not in use
		frameTbs::_()->addStyle('tbsCodemirror', $this->_cdnUrl. 'lib/codemirror/codemirror.css');
		frameTbs::_()->addStyle('codemirror-addon-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/show-hint.css');
		frameTbs::_()->addScript('tbsCodemirror', $this->_cdnUrl. 'lib/codemirror/codemirror.js');
		frameTbs::_()->addScript('codemirror-addon-show-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/show-hint.js');
		frameTbs::_()->addScript('codemirror-addon-xml-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/xml-hint.js');
		frameTbs::_()->addScript('codemirror-addon-html-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/html-hint.js');
		frameTbs::_()->addScript('codemirror-mode-xml', $this->_cdnUrl. 'lib/codemirror/mode/xml/xml.js');
		frameTbs::_()->addScript('codemirror-mode-javascript', $this->_cdnUrl. 'lib/codemirror/mode/javascript/javascript.js');
		frameTbs::_()->addScript('codemirror-mode-css', $this->_cdnUrl. 'lib/codemirror/mode/css/css.js');
		frameTbs::_()->addScript('codemirror-mode-htmlmixed', $this->_cdnUrl. 'lib/codemirror/mode/htmlmixed/htmlmixed.js');
	}
	public function loadCoreCss() {
		$this->_styles = array(
			'styleTbs'			=> array('path' => TBS_CSS_PATH. 'style.css', 'for' => 'admin'), 
			'supsystic-uiTbs'	=> array('path' => TBS_CSS_PATH. 'supsystic-ui.css', 'for' => 'admin'), 
			'dashicons'			=> array('for' => 'admin'),
			'bootstrap-alerts'	=> array('path' => TBS_CSS_PATH. 'bootstrap-alerts.css', 'for' => 'admin'),
			'icheck'			=> array('path' => TBS_CSS_PATH. 'jquery.icheck.css', 'for' => 'admin'),
			//'uniform'			=> array('path' => TBS_CSS_PATH. 'uniform.default.css', 'for' => 'admin'),
			'wp-color-picker'	=> array('for' => 'admin'),
		);
		foreach($this->_styles as $s => $sInfo) {
			if(!empty($sInfo['path'])) {
				frameTbs::_()->addStyle($s, $sInfo['path']);
			} else {
				frameTbs::_()->addStyle($s);
			}
		}
		$this->loadFontAwesome();
	}
	public function loadJqueryUi() {
		static $loaded = false;
		if(!$loaded) {
			frameTbs::_()->addStyle('jquery-ui', TBS_CSS_PATH. 'jquery-ui.min.css');
			frameTbs::_()->addStyle('jquery-ui.structure', TBS_CSS_PATH. 'jquery-ui.structure.min.css');
			frameTbs::_()->addStyle('jquery-ui.theme', TBS_CSS_PATH. 'jquery-ui.theme.min.css');
			frameTbs::_()->addStyle('jquery-slider', TBS_CSS_PATH. 'jquery-slider.css');
			$loaded = true;
		}
	}
	public function loadJqGrid() {
		static $loaded = false;
		if(!$loaded) {
			$this->loadJqueryUi();
			frameTbs::_()->addScript('jq-grid', $this->_cdnUrl. 'lib/jqgrid/jquery.jqGrid.min.js');
			frameTbs::_()->addStyle('jq-grid', $this->_cdnUrl. 'lib/jqgrid/ui.jqgrid.css');
			$langToLoad = utilsTbs::getLangCode2Letter();
			$availableLocales = array('ar','bg','bg1251','cat','cn','cs','da','de','dk','el','en','es','fa','fi','fr','gl','he','hr','hr1250','hu','id','is','it','ja','kr','lt','mne','nl','no','pl','pt','pt','ro','ru','sk','sr','sr','sv','th','tr','tw','ua','vi');
			if(!in_array($langToLoad, $availableLocales)) {
				$langToLoad = 'en';
			}
			frameTbs::_()->addScript('jq-grid-lang', $this->_cdnUrl. 'lib/jqgrid/i18n/grid.locale-'. $langToLoad. '.js');
			$loaded = true;
		}
	}
	public function loadFontAwesome() {
		frameTbs::_()->addStyle('font-awesomeTbs', dispatcherTbs::applyFilters('externalCdnUrl', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css'));
	}
	public function loadChosenSelects() {
		// frameTbs::_()->addStyle('jquery.chosen', $this->_cdnUrl. 'lib/chosen/chosen.min.css');
		// frameTbs::_()->addScript('jquery.chosen', $this->_cdnUrl. 'lib/chosen/chosen.jquery.min.js');
		frameTbs::_()->addStyle('jquery.chosen', TBS_CSS_PATH. 'chosen.min.css');
		frameTbs::_()->addScript('jquery.chosen', TBS_JS_PATH. 'chosen.jquery.min.js');
	}
	public function loadTooltipster() {
		// frameTbs::_()->addScript('tooltipster', $this->_cdnUrl. 'lib/tooltipster/jquery.tooltipster.min.js');
		// frameTbs::_()->addStyle('tooltipster', $this->_cdnUrl. 'lib/tooltipster/tooltipster.css');
		frameTbs::_()->addScript('tooltipster', TBS_JS_PATH. 'jquery.tooltipster.min.js');
		frameTbs::_()->addStyle('tooltipster', TBS_CSS_PATH. 'tooltipster.css');
	}
	public function loadSlimscroll() {
		// not in use
		frameTbs::_()->addScript('jquery.slimscroll', $this->_cdnUrl. 'js/jquery.slimscroll.js');
	}
	public function loadDatePicker() {
		frameTbs::_()->addScript('jquery-ui-datepicker');
	}
	public function loadJqplot() {
		// not in use
		static $loaded = false;
		if(!$loaded) {
			$jqplotDir = $this->_cdnUrl. 'lib/jqplot/';

			frameTbs::_()->addStyle('jquery.jqplot', $jqplotDir. 'jquery.jqplot.min.css');

			frameTbs::_()->addScript('jplot', $jqplotDir. 'jquery.jqplot.min.js');
			frameTbs::_()->addScript('jqplot.canvasAxisLabelRenderer', $jqplotDir. 'jqplot.canvasAxisLabelRenderer.min.js');
			frameTbs::_()->addScript('jqplot.canvasTextRenderer', $jqplotDir. 'jqplot.canvasTextRenderer.min.js');
			frameTbs::_()->addScript('jqplot.dateAxisRenderer', $jqplotDir. 'jqplot.dateAxisRenderer.min.js');
			frameTbs::_()->addScript('jqplot.canvasAxisTickRenderer', $jqplotDir. 'jqplot.canvasAxisTickRenderer.min.js');
			frameTbs::_()->addScript('jqplot.highlighter', $jqplotDir. 'jqplot.highlighter.min.js');
			frameTbs::_()->addScript('jqplot.cursor', $jqplotDir. 'jqplot.cursor.min.js');
			frameTbs::_()->addScript('jqplot.barRenderer', $jqplotDir. 'jqplot.barRenderer.min.js');
			frameTbs::_()->addScript('jqplot.categoryAxisRenderer', $jqplotDir. 'jqplot.categoryAxisRenderer.min.js');
			frameTbs::_()->addScript('jqplot.pointLabels', $jqplotDir. 'jqplot.pointLabels.min.js');
			frameTbs::_()->addScript('jqplot.pieRenderer', $jqplotDir. 'jqplot.pieRenderer.min.js');
			$loaded = true;
		}
	}
	public function loadSortable() {
		static $loaded = false;
		if(!$loaded) {
			frameTbs::_()->addScript('jquery-ui-core');
			frameTbs::_()->addScript('jquery-ui-widget');
			frameTbs::_()->addScript('jquery-ui-mouse');

			frameTbs::_()->addScript('jquery-ui-draggable');
			frameTbs::_()->addScript('jquery-ui-sortable');
			$loaded = true;
		}
	}
	public function loadMagicAnims() {
		// not in use
		static $loaded = false;
		if(!$loaded) {
			frameTbs::_()->addStyle('magic.anim', $this->_cdnUrl. 'css/magic.min.css');
			$loaded = true;
		}
	}
	public function loadCssAnims() {
		static $loaded = false;
		if(!$loaded) {
			frameTbs::_()->addStyle('animate.styles', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.4.0/animate.min.css');
			$loaded = true;
		}
	}
	public function loadBootstrapSimple() {
		static $loaded = false;
		if(!$loaded) {
			frameTbs::_()->addStyle('bootstrap-simple', TBS_CSS_PATH. 'bootstrap-simple.css');
			$loaded = true;
		}
	}
	public function loadGoogleFont( $font ) {
		static $loaded = array();
		if(!isset($loaded[ $font ])) {
			frameTbs::_()->addStyle('google.font.'. str_replace(array(' '), '-', $font), 'https://fonts.googleapis.com/css?family='. urlencode($font));
			$loaded[ $font ] = 1;
		}
	}
}
