<?php
    global $wpdb;
    if (!defined('WPLANG') || WPLANG == '') {
        define('TBS_WPLANG', 'en_GB');
    } else {
        define('TBS_WPLANG', WPLANG);
    }
    if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

    define('TBS_PLUG_NAME', basename(dirname(__FILE__)));
    define('TBS_DIR', WP_PLUGIN_DIR. DS. TBS_PLUG_NAME. DS);
    define('TBS_TPL_DIR', TBS_DIR. 'tpl'. DS);
    define('TBS_CLASSES_DIR', TBS_DIR. 'classes'. DS);
    define('TBS_TABLES_DIR', TBS_CLASSES_DIR. 'tables'. DS);
	define('TBS_HELPERS_DIR', TBS_CLASSES_DIR. 'helpers'. DS);
    define('TBS_LANG_DIR', TBS_DIR. 'lang'. DS);
    define('TBS_IMG_DIR', TBS_DIR. 'img'. DS);
    define('TBS_TEMPLATES_DIR', TBS_DIR. 'templates'. DS);
    define('TBS_MODULES_DIR', TBS_DIR. 'modules'. DS);
    define('TBS_FILES_DIR', TBS_DIR. 'files'. DS);
    define('TBS_ADMIN_DIR', ABSPATH. 'wp-admin'. DS);

	define('TBS_PLUGINS_URL', plugins_url());
    define('TBS_SITE_URL', get_bloginfo('wpurl'). '/');
    define('TBS_JS_PATH', TBS_PLUGINS_URL. '/'. TBS_PLUG_NAME. '/js/');
    define('TBS_CSS_PATH', TBS_PLUGINS_URL. '/'. TBS_PLUG_NAME. '/css/');
    define('TBS_IMG_PATH', TBS_PLUGINS_URL. '/'. TBS_PLUG_NAME. '/img/');
    define('TBS_MODULES_PATH', TBS_PLUGINS_URL. '/'. TBS_PLUG_NAME. '/modules/');
    define('TBS_TEMPLATES_PATH', TBS_PLUGINS_URL. '/'. TBS_PLUG_NAME. '/templates/');
    define('TBS_JS_DIR', TBS_DIR. 'js/');

    define('TBS_URL', TBS_SITE_URL);

    define('TBS_LOADER_IMG', TBS_IMG_PATH. 'loading.gif');
	define('TBS_TIME_FORMAT', 'H:i:s');
    define('TBS_DATE_DL', '/');
    define('TBS_DATE_FORMAT', 'm/d/Y');
    define('TBS_DATE_FORMAT_HIS', 'm/d/Y ('. TBS_TIME_FORMAT. ')');
    define('TBS_DATE_FORMAT_JS', 'mm/dd/yy');
    define('TBS_DATE_FORMAT_CONVERT', '%m/%d/%Y');
    define('TBS_WPDB_PREF', $wpdb->prefix);
    define('TBS_DB_PREF', 'tbs_');
    define('TBS_MAIN_FILE', 'tbs.php');

    define('TBS_DEFAULT', 'default');
    define('TBS_CURRENT', 'current');
	
	define('TBS_EOL', "\n");    
    
    define('TBS_PLUGIN_INSTALLED', true);
    define('TBS_VERSION', '1.2.5');
    define('TBS_USER', 'user');
    
    define('TBS_CLASS_PREFIX', 'tbsc');     
    define('TBS_FREE_VERSION', false);
	define('TBS_TEST_MODE', true);
    
    define('TBS_SUCCESS', 'Success');
    define('TBS_FAILED', 'Failed');
	define('TBS_ERRORS', 'tbsErrors');
	
	define('TBS_ADMIN',	'admin');
	define('TBS_LOGGED','logged');
	define('TBS_GUEST',	'guest');
	
	define('TBS_ALL',		'all');
	
	define('TBS_METHODS',		'methods');
	define('TBS_USERLEVELS',	'userlevels');
	/**
	 * Framework instance code
	 */
	define('TBS_CODE', 'tbs');

	define('TBS_LANG_CODE', 'tbs_lng');
	/**
	 * Plugin name
	 */
	define('TBS_WP_PLUGIN_NAME', 'Translate by Supsystic');
	define('TBS_PLUG_SLUG', 'translate-by-supsystic');
	define('TBS_PLUG_MAIN_TAB', 'settings');
	define('TBS_PLUG_MENU_ICON', 'dashicons-translation');
	/**
	 * Custom defined for plugin
	 */
