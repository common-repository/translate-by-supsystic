<?php
class mailControllerTbs extends controllerTbs {
	public function testEmail() {
		$res = new responseTbs();
		$email = reqTbs::getVar('test_email', 'post');
		if($this->getModel()->testEmail($email)) {
			$res->addMessage(__('Now check your email inbox / spam folders for test mail.'));
		} else 
			$res->pushError ($this->getModel()->getErrors());
		$res->ajaxExec();
	}
	public function saveMailTestRes() {
		$res = new responseTbs();
		$result = (int) reqTbs::getVar('result', 'post');
		frameTbs::_()->getModule('options')->getModel()->save('mail_function_work', $result);
		$res->ajaxExec();
	}
	public function saveOptions() {
		$res = new responseTbs();
		$optsModel = frameTbs::_()->getModule('options')->getModel();
		$submitData = reqTbs::get('post');
		if($optsModel->saveGroup($submitData)) {
			$res->addMessage(__('Done', TBS_LANG_CODE));
		} else
			$res->pushError ($optsModel->getErrors());
		$res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			TBS_USERLEVELS => array(
				TBS_ADMIN => array('testEmail', 'saveMailTestRes', 'saveOptions')
			),
		);
	}
}
