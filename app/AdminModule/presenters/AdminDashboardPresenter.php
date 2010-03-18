<?php
class Admin_DashboardPresenter extends Admin_SecurePresenter
{
	public function actionDefault()
	{
            $this->view = 'dashboard';
            $this->template->registerHelper('count', 'count');
            $this->template->pages = $this->model('pages')->getAll();
            $this->template->categories = $this->model('categories')->getAll();
            $this->template->menus = $this->model('menu')->getAll();
            $this->template->modules = $this->model('modules')->getAll();
	}
}
?>