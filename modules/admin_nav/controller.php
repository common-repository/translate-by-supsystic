<?php
class admin_navControllerTbs extends controllerTbs {
	public function getPermissions() {
		return array(
			TBS_USERLEVELS => array(
				TBS_ADMIN => array()
			),
		);
	}
}