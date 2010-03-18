<?php
class Admin_OptionsPresenter extends Admin_SecurePresenter
{
	
	public function actionDefault()
	{
            $this->view = 'options';
	}
	
    public function actionAdvanced()
    {
            $this->view = 'advanced';
    }
    
	public function createComponentPageMetaInfo($name)
	{
            $page = new Page($this,$name);
            
            $item = $page->addItem("Tabs");
            $item->contentFactory = array($this,'createTabs');
            
            //Tab Meta Info
            $item = $page->addItem("metainfo");
            $item->contentFactory = array($this,'createTabMetaInfo');
	}
    
    public function createComponentPageAdvanced($name)
    {
            $page = new Page($this,$name);
            
            $item = $page->addItem("Tabs");
            $item->contentFactory = array($this,'createTabs');
            
            //Advanced
            $item = $page->addItem("advanced");
            $item->contentFactory = array($this,'createTabAdvanced');
    }
	
    public function createTabs($name, $page)
    {
        $params = $this->getRequest()->getParams();
        $action = $params['action'];
        $linkMeta = Html::el('a')->href($this->link('default'))->add(Html::el('span')->add('Options'));
        $linkAdvanced = Html::el('a')->href($this->link('advanced'))->add(Html::el('span')->add('Advanced'));
        $liMeta = Html::el('li')->class('ui-tabs-panel ui-widget-content ui-state-default ui-corner-top');
        $liAdvanced = Html::el('li')->class('ui-tabs-panel ui-widget-content ui-state-default ui-corner-top');
        if($action == 'advanced')
            $liAdvanced->class('ui-tabs-panel ui-widget-content ui-state-default ui-corner-top ui-tabs-selected ui-state-active'); 
        else
            $liMeta->class('ui-tabs-panel ui-widget-content ui-state-default ui-corner-top ui-tabs-selected ui-state-active');
        $tabs = Html::el('div')->class('tabs ui-tabs ui-widget ui-widget-content ui-corner-all')->add(Html::el('ul')->class('ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all')
            ->add($liMeta->add($linkMeta))
            ->add($liAdvanced->add($linkAdvanced))); 
        return $tabs;
    }
    
	public function createTabMetaInfo($name, $page)
	{
            $form = new LiveForm($page, $name);
            $data = ConfigAdapterIni::load(APP_DIR.'/config/site.ini', 'site');
            $form->addText("title", "Title");
            $form->addText("author", "Author");
            $form->addText("keywords", "Keywords");
            $form->addText("description", "Description");
            $form->addSubmit("odeslat", "Odeslat");
            $form->setDefaults($data);
            $form->onSubmit[] = array($this,"saveMetaData");
            return $form;
	}
	
	public function saveMetaData($form)
	{
            $values = $form->getValues();
            $data = ConfigAdapterIni::load(APP_DIR.'/config/site.ini', 'site');
            try{
                    foreach($values as $key => $value)
                    {
                            $data['key'] = $value;
                    }
                    $metaConfig = new Config();
                    $metaConfig->import($data);
                    $metaConfig->save(APP_DIR.'/config/site.ini', 'site');
                    $this->flash('Metadata saved');
            }
            catch(InvalidArgumentException $e )
            {
                    $this->flash($e->getMessage );
            }
	}

        public function createTabAdvanced($name, $page)
	{
            return Html::el('a')->href($this->link('ClearCache!'))->class('ajax')->add('Clear cache!');
	}

        public function handleClearCache()
        {
            Environment::getCache()->release();
            Environment::getCache()->clean(array(Cache::ALL => TRUE));
        }
}
?>