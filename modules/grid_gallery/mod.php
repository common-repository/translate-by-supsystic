<?php
class grid_galleryTbs extends moduleTbs {
	protected $_avaliableFields = array();
	protected $_avaliableFieldsValues = null;
	public function init() {
		if($this->isGridGalleryExists()) {
			if(is_admin()) {
				add_action('sg_before_gallery_photos_edit', array($this, 'getCheckAttachmentTranslationsAdmin'), 10, 3);
				add_action('sg_tbs_lang_tabs', array($this, 'addTopSwitches'));
				add_action('sg_after_photo_attachment_form', array($this, 'addSwitchFields'), 10, 3);
				add_filter('sg_before_update_photo_attachment', array($this, 'beforeUpdatePhotoAttachment'), 10, 2);
			} else {
				add_filter('sg_after_gallery_get', array($this, 'getCheckAttachmentTranslations'));
			}
		}
	}
	public function isGridGalleryExists() {
		return class_exists('SupsysticGallery');
	}
	public function availableFields($return = 'plugin') {
		if(empty($this->_avaliableFields)) {
			global $supsysticGallery;
			$this->_avaliableFields = array(
				'caption' => 'post_excerpt',
				'description' => 'post_content',
				'alt' => '_wp_attachment_image_alt',
			);
			if(!empty($supsysticGallery) && $supsysticGallery->getEnvironment()->isPro()) {
				$this->_avaliableFields = array_merge($this->_avaliableFields, array(
					'captionDescription' => '_grid_gallery_caption_description',
				));
			}
		}
		switch($return) {
			case 'all':
				$result = $this->_avaliableFields;
				break;
			case 'db':
				$result = array_values($this->_avaliableFields);
				break;
			case 'plugin': default:
				$result = array_keys($this->_avaliableFields);
				break;
		}
		return $result;
	}
	public function addTopSwitches() {
		$langMod = frameTbs::_()->getModule('lang');
		echo $langMod->getView()->addTopSwitches(null, 'grid_gallery');
	}
	public function addSwitchFields($neededFields, $itemId) {
		$langMod = frameTbs::_()->getModule('lang');
		$values = !empty($this->_avaliableFieldsValues[$itemId]) ? $this->_avaliableFieldsValues[$itemId] : array();
		echo $langMod->getView()->addSwitchFields($neededFields, $itemId, $values);
	}
	public function getCheckAttachmentTranslations($gallery) {
		if(!empty($gallery->photos)) {
			$adaptedFieldValues = $this->_getAvaliableFieldsValues($gallery->photos);
			if(!empty($adaptedFieldValues)) {
				foreach($gallery->photos as $pk => $pv) {
					if(!empty($adaptedFieldValues[$pv->attachment_id])) {
						foreach($adaptedFieldValues[$pv->attachment_id] as $afk => $afv) {
							if(isset($gallery->photos[$pk]->attachment[$afk]) && !empty($afv)) {
								$gallery->photos[$pk]->attachment[$afk] = $afv;
							}
						}
					}
				}
			}
		}
		return $gallery;
	}
	public function getCheckAttachmentTranslationsAdmin($photos) {
		if(!empty($photos)) {
			$this->_avaliableFieldsValues = $this->_getAvaliableFieldsValues($photos, true);
		}
	}
	public function _getAvaliableFieldsValues($imagesList, $allLangs = false) {
		$adaptedFieldValues = array();
		$attachmentIds = array();
		foreach($imagesList as $photo) {
			array_push($attachmentIds, $photo->attachment_id);
		}
		if(!empty($attachmentIds)) {
			$attachmentIds = implode(',', $attachmentIds);
			$availableLangs = $allLangs ? frameTbs::_()->getModule('options')->get('langs') : array(frameTbs::_()->getModule('lang')->getLocale());
			$availableDbFields = $this->availableFields('db');
			$availableFields = $this->availableFields('all');
			$fieldNames = array();
			foreach($availableLangs as $lang) {
				foreach($availableDbFields as $f) {
					array_push($fieldNames, "tbs_{$f}_{$lang}");
				}
			}
			$fieldNames = "'". implode("','", $fieldNames). "'";
			$fieldValues = dbTbs::get("SELECT post_id, meta_key, meta_value FROM #__postmeta WHERE meta_key IN (". $fieldNames. ") AND post_id IN (". $attachmentIds. ")");
			if(!empty($fieldValues)) {
				foreach($fieldValues as $fv) {
					$id = $fv['post_id'];
					if($allLangs) {
						foreach($availableFields as $k => $v) {
							foreach($availableLangs as $lang) {
								if(strpos($fv['meta_key'], $v. '_'. $lang) !== false) {
									$adaptedFieldValues[$id][$lang][$k] = $fv['meta_value'];
								}
							}
						}
					} else {
						foreach($availableFields as $k => $v) {
							if(strpos($fv['meta_key'], $v) !== false) {
								$adaptedFieldValues[$id][$k] = $fv['meta_value'];
							}
						}
					}
				}
			}
		}
		return $adaptedFieldValues;
	}
	public function beforeUpdatePhotoAttachment($attachment, $attachmentId) {
		$attachmentId = (int) $attachmentId;
		$transData = reqTbs::getVar('tbs', 'post');
		$defLang = frameTbs::_()->getModule('options')->get('def_lang');
		if(!empty($transData)) {
			foreach($transData as $lang => $values) {
				foreach($values as $k => $v) {
					switch($k) {
						case 'caption':
							update_post_meta($attachmentId, "tbs_post_excerpt_{$lang}", $v);
							break;
						case 'captionDescription':
							$captionDescription = stripslashes($v);
							update_post_meta($attachmentId, "tbs__grid_gallery_caption_description_{$lang}", $captionDescription);
							break;
						case 'alt':
							$alt = htmlspecialchars($v, ENT_QUOTES, get_bloginfo('charset'));
							update_post_meta($attachmentId, "tbs__wp_attachment_image_alt_{$lang}", $alt);
							break;
						case 'description':
							update_post_meta($attachmentId, "tbs_post_content_{$lang}", $v);
							break;

					}
					// Save translatable fields as it was set for default language
					if($lang == $defLang) {
						$attachment[$k] = $v;
					}
				}
			}
		}
		return $attachment;
	}
	private function _afterSimpleOptionGet($option) {
		$option = stripslashes($option);
		return $option;
	}
}