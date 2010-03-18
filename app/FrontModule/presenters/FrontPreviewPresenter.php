<?php
class Front_PreviewPresenter extends Front_BasePresenter
{
	public function actionDefault($values)
	{
		//$this->template->data = (object)$values;
		//$this->view = 'preview';
	}
	
	public function actionPreview($values)
	{
		$this->template->data = (object)$values;
	}
	
	public function createComponentFormSave($name)
	{
		$form = new LiveForm($this, $name);
		$$form->addSubmit('btnClose', 'Preview')->onClick[] = array($this, 'formSaveClose');
		$form->addSubmit('btnSave', 'Save')->onClick[] = array($this, 'formSaveSave');
		return $form;
	}
	
	public function formSaveClose(Button $button)
	{
		$form = $button->getForm();
		$values = $form->getValues();
	}
	
	public function formSaveSave(Button $button)
	{
		$form = $button->getForm();
		$values = $form->getValues();
	}
}
?>