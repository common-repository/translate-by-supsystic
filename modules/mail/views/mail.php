<?php
class mailViewTbs extends viewTbs {
	public function getTabContent() {
		frameTbs::_()->getModule('templates')->loadJqueryUi();
		frameTbs::_()->addScript('admin.'. $this->getCode(), $this->getModule()->getModPath(). 'js/admin.'. $this->getCode(). '.js');
		
		$this->assign('options', frameTbs::_()->getModule('options')->getCatOpts( $this->getCode() ));
		$this->assign('testEmail', frameTbs::_()->getModule('options')->get('notify_email'));
		return parent::getContent('mailAdmin');
	}
}
