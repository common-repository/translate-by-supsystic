<?php
class admin_navViewTbs extends viewTbs {
	public function getBreadcrumbs() {
		$this->assign('breadcrumbsList', dispatcherTbs::applyFilters('mainBreadcrumbs', $this->getModule()->getBreadcrumbsList()));
		return parent::getContent('adminNavBreadcrumbs');
	}
}
