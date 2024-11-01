<?php
class dateTbs {
	static public function _($time = NULL) {
		if(is_null($time)) {
			$time = time();
		}
		return date(TBS_DATE_FORMAT_HIS, $time);
	}
}