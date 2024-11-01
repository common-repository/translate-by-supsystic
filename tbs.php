<?php
/**
 * Plugin Name: Translate by Supsystic
 * Plugin URI: https://supsystic.com
 * Description: Translate your website in multiple languages, in a minutes with Translate by Supsystic
 * Version: 1.2.5
 * Author: supsystic.com
 * Author URI: https://supsystic.com
 **/
	/**
	 * Base config constants and functions
	 */
    require_once(dirname(__FILE__). DIRECTORY_SEPARATOR. 'config.php');
    require_once(dirname(__FILE__). DIRECTORY_SEPARATOR. 'functions.php');
	/**
	 * Connect all required core classes
	 */
    importClassTbs('dbTbs');
    importClassTbs('installerTbs');
    importClassTbs('baseObjectTbs');
    importClassTbs('moduleTbs');
    importClassTbs('modelTbs');
    importClassTbs('viewTbs');
    importClassTbs('controllerTbs');
    importClassTbs('helperTbs');
    importClassTbs('dispatcherTbs');
    importClassTbs('fieldTbs');
    importClassTbs('tableTbs');
    importClassTbs('frameTbs');
    importClassTbs('reqTbs');
    importClassTbs('uriTbs');
    importClassTbs('htmlTbs');
    importClassTbs('responseTbs');
    importClassTbs('fieldAdapterTbs');
    importClassTbs('validatorTbs');
    importClassTbs('errorsTbs');
    importClassTbs('utilsTbs');
    importClassTbs('modInstallerTbs');
	importClassTbs('dateTbs');
	/**
	 * Check plugin version - maybe we need to update database, and check global errors in request
	 */
    installerTbs::update();
    errorsTbs::init();
    /**
	 * Start application
	 */
    frameTbs::_()->parseRoute();
    frameTbs::_()->init();
    frameTbs::_()->exec();
	
	//var_dump(frameTbs::_()->getActivationErrors()); exit();
	
	// Auto update test
