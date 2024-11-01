<?php
class optionsControllerTbs extends controllerTbs {
	public function saveGroup() {
		$res = new responseTbs();
		if($this->getModel()->saveGroup(reqTbs::get('post'))) {
			$res->addMessage(__('Done', TBS_LANG_CODE));
		} else
			$res->pushError ($this->getModel('options')->getErrors());
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			TBS_USERLEVELS => array(
				TBS_ADMIN => array('saveGroup')
			),
		);
	}
}

