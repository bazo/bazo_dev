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
class Front_SearchPresenter extends Front_BasePresenter
{
	public function actionSearch($query)
	{
		$session = Environment::getSession('search');
		if($session->query != $query)
			$data = $this->model('search')->search($query);
		else 
			$data = $session->search_result;
		
		$this->template->query = $query;
		$this->template->data = $data;
		$this->view = 'results';
	}
}
?>