<?php
class Tbs_Walker_Nav_Menu_Edit extends Walker_Nav_Menu_Edit {
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		parent::start_el( $output, $item, $depth, $args, $id );
		$availableLangs = frameTbs::_()->getModule('options')->get('langs');
		$availableFields = frameTbs::_()->getModule('lang')->availableMenuFields();
		foreach($availableLangs as $langKey) {
			foreach($availableFields as $f) {
				$value = frameTbs::_()->getModule('lang')->getPostField( $item->ID, $f, $langKey );
				$output .= htmlTbs::hidden("tbs[{$item->ID}][$langKey][$f]", array(
					'value' => $value,
				));
			}
		}
	}
}