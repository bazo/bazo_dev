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
class Front_PagePresenter extends Front_BasePresenter
{
    
    public function createComponent($name)
    {
        try
        {
           $factory_name = 'createComponent'.String::capitalize($name);
           return $this->$factory_name($name); 
        }
        catch(MemberAccessException $e)
        {
            try
           {
               $refl = new ReflectionClass($name);
               return new $name($this, $name);     
           } 
           catch(ReflectionException $e)
           {
                return new DummyComponent($this, $name, $e->getMessage());
           }
        }
        
        
    }
    
    private function template($text) {
        $template = new StringTemplate();
        $template->presenter = $template->control = Environment::getApplication()->getPresenter(); 
        $template->registerFilter(new LatteFilter);
        $template->content = $text;
        return $template->__toString();
    }
    
	public function actionCategoryView($category)
	{
		try{
			$data = $this->model('pages')->getBySlug($category);
			if ($data == false) {
				$data = $this->model('pages')->getByCategory($category);
				$this->view = 'category-'.$data->category->template;
			}
			else
			{
				$this->view = $data->content_type.'-'.$data->template;
			}
			$this->template->data = $data;
		}
		catch(Exception $e)
		{
			$this->template->error = $e->getMessage();
			$this->view= 'error';
		}
	}
	
	public function actionPageView($category, $page)
	{
		try{
			$data = $this->model('pages')->getBySlug($page);
			$this->template->data = $data;
			$this->view = $data->content_type.'-'.$data->template;
		}
		catch(Exception $e)
		{
			$this->template->error = $e->getMessage();
			$this->view= 'error';
		}
	}
	
	public function actionIdView()
	{
		$data = $this->getRequest()->getParams();
		$id = (int)$data['id'];
		try{
			$data = $this->model('pages')->getById($id);
			$this->template->data = $data;
			$this->view = $data->content_type.'-'.$data->template;
		}
		catch(Exception $e)
		{
			$this->template->error = $e->getMessage();
			$this->view= 'error';
		}
	}
    
    public function beforeRender()
    {   
            if(isset($this->template->data->has_widgets) and $this->template->data->has_widgets == (bool)true)
            {
                $this->template->data->content = $this->template($this->template->data->content);    
            }    
    }
}