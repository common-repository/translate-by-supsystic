<?php
class mailModelTbs extends modelTbs {
	public function testEmail($email) {
		$email = trim($email);
		if(!empty($email)) {
			if($this->getModule()->send($email, 
				__('Test email functionality', TBS_LANG_CODE), 
				sprintf(__('This is a test email for testing email functionality on your site, %s.', TBS_LANG_CODE), TBS_SITE_URL))
			) {
				return true;
			} else {
				$this->pushError( $this->getModule()->getMailErrors() );
			}
		} else
			$this->pushError (__('Empty email address', TBS_LANG_CODE), 'test_email');
		return false;
	}
}