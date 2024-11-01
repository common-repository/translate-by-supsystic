<div class="tbsAdminFooterShell">
	<div class="tbsAdminFooterCell">
		<?php echo TBS_WP_PLUGIN_NAME?>
		<?php _e('Version', TBS_LANG_CODE)?>:
		<a target="_blank" href="http://wordpress.org/plugins/popup-by-supsystic/changelog/"><?php echo TBS_VERSION?></a>
	</div>
	<div class="tbsAdminFooterCell">|</div>
	<?php  if(!frameTbs::_()->getModule(implode('', array('l','ic','e','ns','e')))) {?>
	<div class="tbsAdminFooterCell">
		<?php _e('Go', TBS_LANG_CODE)?>&nbsp;<a target="_blank" href="<?php echo $this->getModule()->getMainLink();?>"><?php _e('PRO', TBS_LANG_CODE)?></a>
	</div>
	<div class="tbsAdminFooterCell">|</div>
	<?php } ?>
	<div class="tbsAdminFooterCell">
		<a target="_blank" href="http://wordpress.org/support/plugin/popup-by-supsystic"><?php _e('Support', TBS_LANG_CODE)?></a>
	</div>
	<div class="tbsAdminFooterCell">|</div>
	<div class="tbsAdminFooterCell">
		Add your <a target="_blank" href="http://wordpress.org/support/view/plugin-reviews/popup-by-supsystic?filter=5#postform">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on wordpress.org.
	</div>
</div>