<?php
class Admin_BasePresenter extends Presenter
{
    public $oldLayoutMode = false;
    public $oldModuleMode = false;
    protected $langs = array();
    /** @persistent */
     public $lang;
    
    protected $translator, $pathToTheme;
    
    public function startup()
    {
            parent::startup();
            $cache = Environment::getCache('langs');
            $langs = $cache->offsetGet('langs');
            if($langs == NULL)
            {
                $this->langs = $this->model('lang')->getAll();
                $cache->save('langs', $this->langs);
            }
            else $this->langs = $langs;
            if (!isset($this->lang))
            {

              $this->lang = $this->getHttpRequest()->detectLanguage($this->langs);
              if($this->lang == null) $this->lang = 'en';
              $this->canonicalize();
            }
            $this->refreshConfig();
    }
    
    protected function refreshConfig()
    {
            $user = Environment::getUser();
            $this->template->user = $user;
            if($user->getIdentity() != null) $this->template->user_data = $user->getIdentity()->getData();
            $this->translator = new Admin_Translator($this->lang);
            $this->template->setTranslator($this->translator);
            $this->template->website = Environment::getVariable('website');
            $this->template->domain = 'http://'.Environment::getVariable('website');
            $this->template->presenter = $this;
            $admin_config = ConfigAdapterIni::load(APP_DIR.'/config/admin.ini');
            foreach($admin_config['admin'] as $var => $value)
            {
                    Environment::setVariable($var, $value);
            }
            Environment::setVariable('themesDir', 'themes');
    }
        protected function compileMenuItems($menu, $module_items, $parent = null)
        {
            foreach ($module_items as $label => $link) {
                if(!is_array($link))
                    {
                        if($parent == null) $menu->add($label, $link);
                        else $menu->getR($parent)->add($label, $link);
                    }
                else
                    {
                        if($parent == null) $menu->add($label, $link[0]);
                        else $menu->getR($parent)->add($label, $link[0]);
                        unset($link[0]);
                        $this->compileMenuItems($menu, $link, $label);
                    }
                }
            return $menu;
        }

    public function createComponentMenu($name)
    {
            $menu = new AdminNavigationBuilder($this, $name);
            $menu->setTranslator($this->translator);
            $menu->add('Dashboard',':Admin:Dashboard:');
            $menu->add('Pages', ':Admin:Pages:');
            $menu->add('Categories', ':Admin:Categories:');
            $menu->add('Menus', ':Admin:Menus:');

            $menu->add('Themes', ':Admin:Themes:');
            $module_items = Environment::getApplication()->getAdminNodes();
            $menu = $this->compileMenuItems($menu, $module_items);
            $menu->add('Modules', ':Admin:Modules:');
            $menu->add('Settings', ':Admin:Options:');
            $menu->template->presenter = $this;
            return $menu;
           
    }
        
    /**
         * @param string Model name
         * @return Model
         */
    protected function model($model_name)
    {
            static $model_instances;
            $model_class = 'Admin_'.ucfirst($model_name).'Model';
            $model_instances[$model_name] = new $model_class();
            return $model_instances[$model_name];                        
    }
    
    protected function createComponentCss($name)
      {
            $theme = Environment::getVariable('theme');
            $css = new CssLoader($this, $name);
            $css->setModule('admin');
            $css->absolutizeUrls = true;
            $css->media = 'screen, tv, projection';
            $css->sourcePath = APP_DIR.$this->pathToTheme . "/css";
            //$css->sourceUri = Environment::getVariable("baseUri") . "css/admin/$theme";
            $css->tempUri = Environment::getVariable("baseUri") . "css/admin";
            $css->tempPath = WWW_DIR . "/css/admin";
            $css->joinFiles = false;
            $css->sourceUri = APP_DIR.$this->pathToTheme;
            $css->filters[] = array($this, "encodeImages");
            //$css->filters[] = array($this, "tidyCSS");
            return $css;
      }

    protected function createComponentJs($name)
      {
            $theme = Environment::getVariable('theme');
            $js = new JavaScriptLoader($this, $name);
            $js->setModule('admin');
            $js->tempUri = Environment::getVariable("baseUri")."js/admin";
            $js->tempPath = WWW_DIR . "/js/admin";
            $js->sourcePath = APP_DIR.$this->pathToTheme . "/js";
            $filter = new VariablesFilter();
            $session_conf = Environment::getVariable('session');
            $filter->setVariable("session_expiration", $session_conf['expiration']);
            $filter->setVariable("login_url", $this->link(':Admin:Login:Json'));
            $js->filters[] = array($filter, "apply");
            //$js->filters[] = array($this, "packJs");
            //$js->joinFiles = Environment::isProduction();
            $js->joinFiles = true;
            return $js;
      }
    
    protected function createComponentLangSelector()
    {
        $form = new LiveForm($this, 'langSelector');
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = null;;
        $renderer->wrappers['pair']['container'] = null;
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = null;
        
        $form->addSelect('lang', 'language', $this->langs)->setDefaultValue($this->lang);
        $form->addSubmit('ok', 'OK')->onClick[] = array($this, 'changeLang');
        return $form;
    }
    
    public function changeLang(Button $button)
    {
        $form = $button->getForm();
        $values = $form->getValues();
        $lang = $values['lang'];
        $this->redirect('this', array('lang' => $lang));
    }

    public function createComponentFormSearch($name)
    {
        $form = new LiveForm($this, $name);
        $renderer = $form->getRenderer();
        $renderer->wrappers['form']['container'] = Html::el('div')->id('search-form');
        $renderer->wrappers['controls']['container'] = null;;
        $renderer->wrappers['pair']['container'] = null;
        $renderer->wrappers['label']['container'] = null;
        $renderer->wrappers['control']['container'] = null;
        $form->addGroup('Search')->setOption('container', Html::el('fieldset')->id('search'));
        $form->addText('search_query','');
        $form->addSubmit('ok', 'GO!')->onClick[] = array($this, 'getSearchResults');
    }

    public function getSearchResults(Button $button)
    {
        $form = $button->getForm();
        $values = $form->getValues();
        $form->parent->flash('Search not implemented');
        $form->parent->end();
    }

    public function packJs($code)
      {
        $packer = new JavaScriptPacker($code, 'None');
        return $packer->pack();
      }
    
    public function encodeImages($code)
    {
        $encoder = new DataURIFilter();
        return $encoder->convert($code);
    }
    
    public function tidyCSS($code)
    {
        $tidy = new Css_Tidy();
        return $tidy->parse($code);
    }
    
    public function handleLogout()
    {
        Environment::getUser()->signOut();
        $session = $this->getSession('backlink');
        $session['backlink'] = null;
        $this->flashMessage('You have been logged off because: '.Environment::getUser()->getSignOutReason());
        $this->redirect(':Admin:Login:default');
    }

    public function formatLayoutTemplateFiles($presenter, $layout)
    {
        $themesDir = Environment::getVariable('themesDir');
        $theme = Environment::getVariable('theme');
        $appDir = Environment::getVariable('appDir');
        $path = '/' . str_replace(':', 'Module/', $presenter);
        $pathP = substr_replace($path, '/'.$themesDir.'/'.$theme.'/templates', strrpos($path, '/'), 0);
        $list = array(
            "$appDir$pathP/@$layout.phtml",
            "$appDir$pathP.@$layout.phtml",
        );
        while (($path = substr($path, 0, strrpos($path, '/'))) !== FALSE) {
            $list[] = "$appDir$path".'/'.$themesDir.'/'.$theme.'/templates/'."@$layout.phtml";
        }
        return $list;
    }

    public function formatTemplateFiles($presenter, $view)
    {
        
        $parts = explode(':', $presenter);
        Environment::setVariable('moduleDir', $parts[0].'Module');
        $themesDir = Environment::getVariable('themesDir');
        $theme = Environment::getVariable('theme');
        $appDir = Environment::getVariable('appDir');
        $path = '/' . str_replace(':', 'Module/', $presenter);
        $pathP = substr_replace($path, '/'.$themesDir.'/'.$theme.'/templates', strrpos($path, '/'), 0);
        $this->pathToTheme = substr($pathP,0,  strrpos($pathP, '/'));
        $this->pathToTheme = substr($this->pathToTheme,0,  strrpos($this->pathToTheme, '/'));
        $path = substr_replace($path, '/'.$themesDir.'/'.$theme.'/templates', strrpos($path, '/'));
        return array(
            "$appDir$pathP/$view.phtml",
            "$appDir$pathP.$view.phtml",
            "$appDir$path/@global.$view.phtml",
        );
    }    
    
    public function flash($message, $type = 'info')
    {
        $this->payload->flashes[] = array('msg' => $message, 'type' => $type);
        parent::flashMessage($message, $type);
        $this->invalidateControl('flash');
    }
    
    public function flashCmd($cmd)
    {
        $this->payload->cmds[] = $cmd;
    }
    
    public function end()
    {
        $this->sendPayload();
        $this->terminate();
    }
}
?>