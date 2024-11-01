<?php /*TODO: Make this panel - sticky to the top of the screen*/ ?>
<h3 class="nav-tab-wrapper tbsLangTabs" style="margin-bottom: 0px; margin-top: 12px;">
	<?php foreach($this->availableLangs as $langKey) { ?>
		<?php if(isset($this->allLangs[ $langKey ])) { ?>
			<?php
				$flagUrl = $this->mod->getLangFlagUrl( $langKey, 16 );
				// For WP5 we will switch whole page
				$hrefForLang = $this->useBlockEditor ?
					uriTbs::atach(array('lang' => $langKey, 'baseUrl' => get_admin_url(). 'post.php')) 
					: $langKey;
				$selected = (empty($this->currentLang) && $langKey == $this->defLang);
				if(!$selected && !empty($this->currentLang) && $this->currentLang == $langKey)
					$selected = true;
			?>
			<a class="tbsLangTabSwitch nav-tab <?php if($selected) { echo 'nav-tab-active'; }?>" 
			   href="<?php echo $hrefForLang; ?>"
			   data-lang="<?php echo $langKey; ?>"
			>
				<?php if(!empty($flagUrl)) { ?>
					<img src="<?php echo $flagUrl;?>" />
				<?php }?>
				<span class="tbsLangTabTitle"><?php echo $this->allLangs[ $langKey ];?></span>
			</a>
		<?php }?>
	<?php }?>
</h3>
