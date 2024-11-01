<span class="tbsLangInputs" style="display: none;"><?php foreach($this->availableLangs as $langKey) {
	foreach($this->availableFields as $f) {
		$value = '';
		$attrs = '';
		if(!empty($this->itemId) || in_array($this->for, array('options-general', 'gmap', 'grid_gallery', 'custom'))) {
			switch( $this->for ) {
				case 'post':
					$value = $this->mod->getPostField( $this->itemId, $f, $langKey);
					break;
				case 'term_edit': case 'term':
					$value = $this->mod->getTermField( $this->itemId, $f, $langKey);
					break;
				case 'options-general':
					$value = $this->mod->getOptField( $f, $langKey );
					break;
				case 'gmap':
					$attrs = 'data-lang="' . $langKey . '" data-field="' . $f . '"';
					break;
				case 'grid_gallery':
					if(!empty($this->presetValues[$langKey][$f])) {
						$value = $this->presetValues[$langKey][$f];
					}
					break;
				case 'custom':
					$value = $this->mod->getOptField( $f, $langKey );
					break;
			}
		}
		if(empty($this->neededFields) || in_array($f, $this->neededFields)) {
			echo htmlTbs::hidden("tbs[$langKey][$f]", array(
				'value' => esc_html($value),
				'attrs' => $attrs,
			));
		}
	}
}?></span>