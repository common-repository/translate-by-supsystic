<style type="text/css">
	.tbsAdminMainLeftSide {
		width: 56%;
		float: left;
	}
	.tbsAdminMainRightSide {
		width: <?php echo (empty($this->optsDisplayOnMainPage) ? 100 : 40)?>%;
		float: left;
		text-align: center;
	}
	#tbsMainOccupancy {
		box-shadow: none !important;
	}
</style>
<section>
	<div class="supsystic-item supsystic-panel">
		<div id="containerWrapper">
			<?php _e('Main page Go here!!!!', TBS_LANG_CODE)?>
		</div>
		<div style="clear: both;"></div>
	</div>
</section>