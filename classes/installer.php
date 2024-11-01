<?php
class installerTbs {
	static public $update_to_version_method = '';
	static private $_firstTimeActivated = false;
	static public function init( $isUpdate = false ) {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Versiom */
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$current_version = get_option($wpPrefix. TBS_DB_PREF. 'db_version', 0);
		if(!$current_version)
			self::$_firstTimeActivated = true;
		/**
		 * modules 
		 */
		if (!dbTbs::exist("@__modules")) {
			dbDelta(dbTbs::prepareQuery("CREATE TABLE IF NOT EXISTS `@__modules` (
			  `id` smallint(3) NOT NULL AUTO_INCREMENT,
			  `code` varchar(32) NOT NULL,
			  `active` tinyint(1) NOT NULL DEFAULT '0',
			  `type_id` tinyint(1) NOT NULL DEFAULT '0',
			  `label` varchar(64) DEFAULT NULL,
			  `ex_plug_dir` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `code` (`code`)
			) DEFAULT CHARSET=utf8;"));
			dbTbs::query("INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES
				(NULL, 'adminmenu',1,1,'Admin Menu'),
				(NULL, 'options',1,1,'Options'),
				(NULL, 'user',1,1,'Users'),
				(NULL, 'pages',1,1,'Pages'),
				(NULL, 'templates',1,1,'templates'),
				(NULL, 'supsystic_promo',1,1,'supsystic_promo'),
				(NULL, 'admin_nav',1,1,'admin_nav'),
				
				(NULL, 'lang',1,1,'lang'),
				(NULL, 'lang_widget',1,1,'lang_widget'),
				
				(NULL, 'mail',1,1,'mail');");
		}
		if(!dbTbs::exist('@__modules', 'code', 'gmap')) {
			dbTbs::query("INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES
				(NULL, 'gmap', 1, 1, 'Google Maps Easy Integration');");
		}
		if(!dbTbs::exist('@__modules', 'code', 'grid_gallery')) {
			dbTbs::query("INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES
				(NULL, 'grid_gallery', 1, 1, 'Photo Gallery by Supsystic Integration');");
		}
		/**
		 *  modules_type 
		 */
		if(!dbTbs::exist("@__modules_type")) {
			dbDelta(dbTbs::prepareQuery("CREATE TABLE IF NOT EXISTS `@__modules_type` (
			  `id` smallint(3) NOT NULL AUTO_INCREMENT,
			  `label` varchar(32) NOT NULL,
			  PRIMARY KEY (`id`)
			) AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;"));
			dbTbs::query("INSERT INTO `@__modules_type` VALUES
				(1,'system'),
				(6,'addons');");
		}
		/**
		* Plugin usage statistics
		*/
		if(!dbTbs::exist("@__usage_stat")) {
			dbDelta(dbTbs::prepareQuery("CREATE TABLE `@__usage_stat` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `code` varchar(64) NOT NULL,
			  `visits` int(11) NOT NULL DEFAULT '0',
			  `spent_time` int(11) NOT NULL DEFAULT '0',
			  `modify_timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			  UNIQUE INDEX `code` (`code`),
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8"));
			dbTbs::query("INSERT INTO `@__usage_stat` (code, visits) VALUES ('installed', 1)");
		}
		if($current_version && !self::$_firstTimeActivated) {
			self::setUsed();
			// For users that just updated our plugin - don't need tp show step-by-step tutorial
			update_user_meta(get_current_user_id(), TBS_CODE . '-tour-hst', array('closed' => 1));
		}
		update_option($wpPrefix. TBS_DB_PREF. 'db_version', TBS_VERSION);
		add_option($wpPrefix. TBS_DB_PREF. 'db_installed', 1);
	}
	static public function setUsed() {
		update_option(TBS_DB_PREF. 'plug_was_used', 1);
	}
	static public function isUsed() {
		return true;	// No welcome page for now
		//return 0;
		return (int) get_option(TBS_DB_PREF. 'plug_was_used');
	}
	static public function delete() {
		self::_checkSendStat('delete');
		global $wpdb;
		$wpPrefix = $wpdb->prefix;
		$wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.TBS_DB_PREF."modules`");
		$wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.TBS_DB_PREF."modules_type`");
		$wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.TBS_DB_PREF."usage_stat`");
		delete_option($wpPrefix. TBS_DB_PREF. 'db_version');
		delete_option($wpPrefix. TBS_DB_PREF. 'db_installed');
	}
	static public function deactivate() {
		self::_checkSendStat('deactivate');
	}
	static private function _checkSendStat($statCode) {
		if(class_exists('frameTbs') 
			&& frameTbs::_()->getModule('supsystic_promo')
			&& frameTbs::_()->getModule('options')
		) {
			frameTbs::_()->getModule('supsystic_promo')->getModel()->saveUsageStat( $statCode );
			frameTbs::_()->getModule('supsystic_promo')->getModel()->checkAndSend( true );
		}
	}
	static public function update() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Versiom */
		$currentVersion = get_option($wpPrefix. TBS_DB_PREF. 'db_version', 0);
		if(!$currentVersion || version_compare(TBS_VERSION, $currentVersion, '>')) {
			self::init( true );
			update_option($wpPrefix. TBS_DB_PREF. 'db_version', TBS_VERSION);
		}
	}
}
