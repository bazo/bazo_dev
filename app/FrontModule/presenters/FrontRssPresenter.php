<?php
/**
 * Photon CMS
 *
 * @copyright  Copyright (c) 2009 Martin Bazik
 * @package    Receptar
 */
/**
 * Homepage presenter.
 *
 * @author     Martin Bazik
 * @package    Receptar
 */
class Front_RssPresenter extends Front_BasePresenter
{
	public function actionRss($category)
	{
		try{
			if($category == null) 
			{
				$data = $this->model('pages')->getAll();
				$this->view = 'rss_all';
			}
			else 
			{
				$data = $this->model('pages')->getByCategory($category);
				$this->view = 'rss';
			}
			$this->template->data = $data;
			$this->template->now = time();
			$this->template->lang = $this->lang;
		}
		catch(Exception $e)
		{
			$this->template->error = $e->getMessage();
			$this->view= 'error';
		}
		
	}
}
?>