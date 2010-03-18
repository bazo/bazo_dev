<?php
class Admin_ThemesPresenter extends Admin_SecurePresenter
{
	
	public function actionDefault()
	{
		$this->view = 'visuals_admin';
	}
    
    public function actionSite()
    {
        $this->view = 'visuals_site';
    }
	
	public function createComponentPageAdminThemes($name)
	{
		$page = new Page($this,$name);
		
        $item = $page->addItem("Tabs");
        $item->contentFactory = array($this,'createTabs');
        
		//Tab Admin Themes
		$item = $page->addItem("admin_themes");
        $item->contentFactory = array($this,'createTabAdminThemes');
	}
    
    public function createComponentPageSiteThemes($name)
    {
        $page = new Page($this,$name);
        
        $item = $page->addItem("Tabs");
        $item->contentFactory = array($this,'createTabs');
        
        //Tab Site Themes
        $item = $page->addItem("site_themes");
        $item->contentFactory = array($this,'createTabSiteThemes');
    }
	
    public function createTabs($name, $page)
    {
        $params = $this->getRequest()->getParams();
        $action = $params['action'];
        $linkMeta = Html::el('a')->href($this->link('default'))->add(Html::el('span')->add('Admin Themes'));
        $linkAdvanced = Html::el('a')->href($this->link('site'))->add(Html::el('span')->add('Site Themes'));
        $liMeta = Html::el('li')->class('ui-tabs-panel ui-widget-content ui-state-default ui-corner-top');
        $liAdvanced = Html::el('li')->class('ui-tabs-panel ui-widget-content ui-state-default ui-corner-top');
        if($action == 'site')
            $liAdvanced->class('ui-tabs-panel ui-widget-content ui-state-default ui-corner-top ui-tabs-selected ui-state-active'); 
        else
            $liMeta->class('ui-tabs-panel ui-widget-content ui-state-default ui-corner-top ui-tabs-selected ui-state-active');
        $tabs = Html::el('div')->class('tabs ui-tabs ui-widget ui-widget-content ui-corner-all')->add(Html::el('ul')->class('ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all')
            ->add($liMeta->add($linkMeta))
            ->add($liAdvanced->add($linkAdvanced))); 
        return $tabs;
    }
    
	public function createTabAdminThemes($name, item $item)
	{
		$appDir = Environment::getVariable('appDir');
		$moduleDir = Environment::getVariable('moduleDir');
		$themesDir = Environment::getVariable('themesDir');
		$activeTheme = Environment::getVariable('theme');
		$themesPath = $appDir.'/'.$moduleDir.'/'.$themesDir;
		$activeThemeInfoFile = $themesPath.'/'.$activeTheme.'/theme.xml';
		$activeThemeInfo = simplexml_load_file($activeThemeInfoFile);
		
		$availableThemes = array();
		foreach(scandir($themesPath) as $path)
		{
			if( $path != $activeTheme and $path != '.' and $path != '..' and is_dir($themesPath.'/'.$path) )
			{
				$themeInfo = simplexml_load_file($themesPath.'/'.$path.'/theme.xml');
				$themeInfo->folder = $path;
				$availableThemes[] = (array)$themeInfo;
			} 
		}
		$template = $this->createTemplate();
		$template->activeTheme = $activeThemeInfo;
		$template->themes = $availableThemes;
		$template->mode = 'admin';
		$template->setFile($themesPath.'/'.$activeTheme.'/templates/Themes/themes.phtml');
		return $template;
	}
	
	public function handleActivateAdminTheme($theme)
	{
		$data = ConfigAdapterIni::load(APP_DIR.'/config/admin.ini', 'admin');
		$data['theme'] = $theme;
		$themeConfig = new Config();
		$themeConfig->import($data);
		$themeConfig->save(APP_DIR.'/config/admin.ini', 'admin');
		$this->flash('Admin theme '.$theme.' activated');
		$this->redirect('this');
	}
	
	public function createTabSiteThemes($name, item $item)
	{
		$appDir = Environment::getVariable('appDir');
		$moduleDir = 'FrontModule';
		$adminModuleDir = Environment::getVariable('moduleDir');
		$themesDir = Environment::getVariable('themesDir');
		$data = ConfigAdapterIni::load(APP_DIR.'/config/site.ini', 'site');
		$activeTheme = $data['theme'];
		$activeAdminTheme = Environment::getVariable('theme');
		$adminThemesPath = $appDir.'/'.$adminModuleDir.'/'.$themesDir;
		$themesPath = $appDir.'/'.$moduleDir.'/'.$themesDir;
		$activeThemeInfoFile = $themesPath.'/'.$activeTheme.'/theme.xml';
		$activeThemeInfo = simplexml_load_file($activeThemeInfoFile);
		
		$availableThemes = array();
		foreach(scandir($themesPath) as $path)
		{
			if( $path != $activeTheme and $path != '.' and $path != '..' and is_dir($themesPath.'/'.$path) )
			{
				$themeInfo = simplexml_load_file($themesPath.'/'.$path.'/theme.xml');
				$themeInfo->folder = $path;
				$availableThemes[] = (array)$themeInfo;
			} 
		}
		$template = $this->createTemplate();
		$template->activeTheme = $activeThemeInfo;
		$template->themes = $availableThemes;
		$template->mode = 'site';
		$template->setFile($adminThemesPath.'/'.$activeAdminTheme.'/templates/Themes/themes.phtml');
		return $template;
	}
	
	public function handleActivateSiteTheme($theme)
	{
		$data = ConfigAdapterIni::load(APP_DIR.'/config/site.ini', 'site');
		$data['theme'] = $theme;
		$themeConfig = new Config();
		$themeConfig->import($data);
		$themeConfig->save(APP_DIR.'/config/site.ini', 'site');
		$this->flash('Site theme '.$theme.' activated');
	}
}
?>