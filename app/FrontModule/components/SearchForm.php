<?php
class SearchForm extends AppForm
{
	public function __construct($parent = null, $name = null)
	{
		parent::__construct($parent, $name);
		$this->addText('query', 'Query');
		$this->addSubmit('btnSubmit','Search');
		$this->onSubmit[] = array($this, 'handleSubmit');
	}
	
	public function handleSubmit($form)
	{
		$values = $form->getValues();
		$query = $values['query'];
		$data = $this->presenter->model('search')->search($query);
		$session = Environment::getSession('search');
		$session->query = $query;
		$session->search_result = $data;
		$this->getPresenter()->redirect('Search:search', array('query' => $query));
	}
}
?>