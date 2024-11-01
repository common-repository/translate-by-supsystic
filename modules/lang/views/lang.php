<?php
class langViewTbs extends viewTbs {
	public function addTopSwitches( $item, $for = 'post' ) {
		frameTbs::_()->getModule('templates')->loadCoreJs();
		frameTbs::_()->getModule('templates')->loadAdminCoreJs();
		frameTbs::_()->addScript('admin.lang', $this->getModule()->getModPath(). 'js/admin.lang.js');
		frameTbs::_()->addStyle('admin.lang', $this->getModule()->getModPath(). 'css/admin.lang.css');
		$availableLangs = frameTbs::_()->getModule('options')->get('langs');
		$defLang = frameTbs::_()->getModule('options')->get('def_lang');
		$currentLang = reqTbs::getVar('lang', 'get');
		$availableFields = array();
		$availableMetaFields = array();
		$itemId = 0;
		switch( $for ) {
			case 'post':
				$availableFields = $this->getModule()->availablePostFields($item);
				$itemId = $item->ID;
				$availableMetaFields = $this->getModule()->availablePostMetaFields();
				break;
			case 'term_edit': case 'term':
				$availableFields = $this->getModule()->availableTermFields();
				if( $for == 'term_edit' ) {
					$itemId = $item->term_id;
				}
				break;
			case 'options-general':
				$availableFields = $this->getModule()->availableOptsFields();
				break;
			case 'nav-menus':
				$availableFields = $this->getModule()->availableMenuFields();
				break;
			case 'gmap':
				$availableFields = frameTbs::_()->getModule('gmap')->availableFields();
				break;
			case 'grid_gallery':
				$availableFields = frameTbs::_()->getModule('grid_gallery')->availableFields();
				break;
			case 'custom':
				$availableFields = $this->getModule()->availableCustomFields();
				$availableMetaFields = $availableFields;
				break;
			default:
				break;
		}
		frameTbs::_()->addJSVar('admin.lang', 'tbsAvailableFields', $availableFields);
		frameTbs::_()->addJSVar('admin.lang', 'tbsAvailableMetaFields', $availableMetaFields);
		frameTbs::_()->addJSVar('admin.lang', 'tbsLangData', array(
			'for' => $for,
			'post_type' => !empty($item->post_type) ? $item->post_type : '',
			'useBlockEditor' => $this->getModule()->useBlockEditor(),
		));
		$this->assign('availableLangs', $availableLangs);
		$this->assign('defLang', $defLang);
		$this->assign('allLangs', frameTbs::_()->getModule('options')->getAvailbaleLangsSelect());
		$this->assign('mod', $this->getModule());
		$this->assign('availableFields', $availableFields);
		$this->assign('itemId', $itemId);
		$this->assign('for', $for);
		$this->assign('currentLang', $currentLang);
		$this->assign('useBlockEditor', $this->getModule()->useBlockEditor());
		return parent::getContent('langTopSwitches');
	}
	/**
	 * This method should be called after addTopSwitches method call only!
	 * @return string
	 */
	public function addSwitchFields($neededFields = array(), $curItemId = 0, $presetValues = array()) {
		$itemId = !empty($curItemId) ? $curItemId : $this->itemId;
		$this->assign('itemId', $itemId);
		$this->assign('neededFields', $neededFields);
		$this->assign('presetValues', $presetValues);
		return parent::getContent('langSwitcheFields');
	}
}
