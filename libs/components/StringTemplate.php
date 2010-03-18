<?php
class StringTemplatekjh extends BaseTemplate
{
        public $content;

        /**
         * Renders template to output.
         * @return void
         */
        public function render()
        {
                $cache = Environment::getCache('StringTemplate');
                $key = md5($this->content);
                $content = $cache[$key];
                if ($content === NULL) { // not cached
                        if (!$this->getFilters()) {
                                $this->onPrepareFilters($this);
                        }

                        $cache[$key] = $content = $this->compile($this->content);
                }

                $this->__set('template', $this);
                /*Nette\Loaders\*/LimitedScope::evaluate($content, $this->getParams());
        }
}
?>