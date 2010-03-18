<?php
class Admin_PublishPresenter extends Admin_BasePresenter
{
	public $oldLayoutMode = false;
	public $oldModuleMode = false;
	public $error;
	private $methods = array(
			// WordPress API
			
			'wp.getUsersBlogs'		=> 'this:getUsersBlogs',
			'wp.getPage'			=> 'this:getPage',
			'wp.getPages'			=> 'this:getPages',
			'wp.newPage'			=> 'this:newPage',
			'wp.deletePage'			=> 'this:deletePage',
			'wp.editPage'			=> 'this:editPage',
			'wp.getPageList'		=> 'this:getPageList',
			'wp.getAuthors'			=> 'this:getAuthors',
			'wp.getCategories'		=> 'this:getCategories',		// Alias
			'wp.getTags'			=> 'this:getTags',
			'wp.newCategory'		=> 'this:newCategory',
			'wp.deleteCategory'		=> 'this:deleteCategory',
			'wp.suggestCategories'	=> 'this:suggestCategories',
			'wp.uploadFile'			=> 'this:newMediaObject',	// Alias
			'wp.getCommentCount'	=> 'this:getCommentCount',
			'wp.getPostStatusList'	=> 'this:getPostStatusList',
			'wp.getPageStatusList'	=> 'this:getPageStatusList',
			'wp.getPageTemplates'	=> 'this:getPageTemplates',
			'wp.getOptions'			=> 'this:getOptions',
			'wp.setOptions'			=> 'this:setOptions',
			'wp.getComment'			=> 'this:getComment',
			'wp.getComments'		=> 'this:getComments',
			'wp.deleteComment'		=> 'this:deleteComment',
			'wp.editComment'		=> 'this:editComment',
			'wp.newComment'			=> 'this:newComment',
			'wp.getCommentStatusList' => 'this:getCommentStatusList',
			
			
			// Blogger API
			
			'blogger.getUsersBlogs' => 'this:getUsersBlogs',
			'blogger.getUserInfo' => 'this:getUserInfo',
			'blogger.getPost' => 'this:getPost',
			'blogger.getRecentPosts' => 'this:getRecentPosts',
			'blogger.getTemplate' => 'this:getTemplate',
			'blogger.setTemplate' => 'this:setTemplate',
			'blogger.newPost' => 'this:newPost',
			'blogger.editPost' => 'this:editPost',
			
			'blogger.deletePost' => 'this:deletePost',
			
			// MetaWeblog API (with MT extensions to structs)
			'metaWeblog.newPost' => 'this:newPost',
			'metaWeblog.editPost' => 'this:editPost',
			'metaWeblog.getPost' => 'this:getPost',
			'metaWeblog.getRecentPosts' => 'this:getRecentPosts',
			'metaWeblog.getCategories' => 'this:getCategories',
			'metaWeblog.newMediaObject' => 'this:newMediaObject',

			// MetaWeblog API aliases for Blogger API
			// see http://www.xmlrpc.com/stories/storyReader$2460
			'metaWeblog.deletePost' => 'this:deletePost',
			'metaWeblog.getTemplate' => 'this:getTemplate',
			'metaWeblog.setTemplate' => 'this:setTemplate',
			'metaWeblog.getUsersBlogs' => 'this:getUsersBlogs',
			
			// MovableType API
			'mt.getCategoryList' => 'getCategoryList',
			'mt.getRecentPostTitles' => 'getRecentPostTitles',
			'mt.getPostCategories' => 'getPostCategories',
			'mt.setPostCategories' => 'setPostCategories',
			'mt.supportedMethods' => 'supportedMethods',
			'mt.supportedTextFilters' => 'supportedTextFilters',
			'mt.getTrackbackPings' => 'getTrackbackPings',
			'mt.publishPost' => 'publishPost',
		);
	
	public function startup()
	{
		parent::startup();
		$this->absoluteUrls = TRUE;
		$admin_config = ConfigAdapterIni::load(APP_DIR.'/config/admin.ini');
		foreach($admin_config['admin'] as $var => $value)
		{
			Environment::setVariable($var, $value);
		}
		Environment::setVariable('themesDir', 'themes');
		$method = $this->getRequest()->getMethod();
		if($method == 'GET')
		{
			$data = $method = $this->getRequest()->getParams();
			if(isset($data['rsd']))
			{
				header('Content-Type: text/xml');
				$this->view = 'rsd';	
			}
			elseif(isset($data['wlw']))
			{
				header('Content-Type: text/xml');
				$this->view = 'wlw';
			}
		}
		else
		{
			if ( !isset( $HTTP_RAW_POST_DATA ) ) {
				$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
			}
			$data = $HTTP_RAW_POST_DATA;
			file_put_contents('xmlrpc.txt', $data , FILE_APPEND);
			
			$this->formatCallbacks($this->methods);
			
			$server = new IXR_Server($this->methods, $data);
			
		}
	}
	
	public function nullFilter($s)
	{
		return $s;
	}
	
	private function formatCallbacks(& $callbacks)
	{
		foreach($callbacks as $key => $callback)
		{
			$callbacks[$key] = $this->formatCallback($callback);
		}
	}
	
	private function formatCallback($callback)
	{
		$method = str_replace('this:', '', $callback);
		return array($this, $method);
	}
	
	private function login($username, $password)
	{
		$user = Environment::getUser();
		if( !$user->isAuthenticated())
		{
			$user->setAuthenticationHandler(new Admin_UserModel());
			try
			{
				$user->authenticate($username, $password);
				$session_conf = Environment::getVariable('session');
				$user->setExpiration($session_conf['expiration'], true);
				return $user;
			}
			catch (AuthenticationException $e) 
			{
				$this->error = new IXR_Error(403, $e->getMessage());
				return false;
			}
		}
		return $user;
	}
	/* API IMPLEMENTATIONS */
	
	/* BLOGGER API */
	public function getUsersBlogs($args)
	{
		$username = $args[1];
		$password  = $args[2];
		$user = $this->login($username, $password);
		if ( !$user ) {
			return $this->error;
		}
		
		$struct = array(
			'isAdmin'  => 1,
			'url'      =>  $this->link(':Front:Homepage:homepage'),
			'blogid'   => '1',
			'blogName' => 'Mokuji',
			'xmlrpc'   => $this->link(':Admin:Publish:default')
		);

		return array($struct);
	}
	
	public function deletePost($args)
	{
		$pageId = (int)$args[1];
		$username = $args[2];
		$password  = $args[3];
		$user = $this->login($username, $password);
		if ( !$user ) {
			return $this->error;
		}
		
		try
		{
			return $this->model('pages')->deleteById($pageId);
		}
		catch(Exception $e)
		{
			return new IXR_Error(500, $e->getMessage());
		}
	}

	public function newPost($args)
	{
		$values = $this->preparePost($args);
		try
		{
			$pageId = $this->model('pages')->save($values);
		}
		catch(Exception $e)
		{
			return new IXR_Error(500, $e->getMessage());
		}
		return (string)$pageId;
	}
	
	public function editPost($args)
	{
		$values = $this->preparePost($args);
		$values['id'] = $args[0];
		try
		{
			$this->model('pages')->update($values);
		}
		catch(Exception $e)
		{
			return new IXR_Error(500, $e->getMessage());
		}
		return true;
	}
	
	private function preparePost($args)
	{
		$blogId = (int)$args[0];
		$username = $args[1];
		$password  = $args[2];
		$user = $this->login($username, $password);
		if ( !$user ) {
			return $this->error;
		}
		$values = array();
		$values['author'] = $user->getIdentity()->getId();
		//$values['author'] = $user->getIdentity()->getName();
		$values['title'] = $args[3]['title'];
		$values['content'] = $args[3]['description'];
		if(isset($args[3]['date_created_gmt']))
		{
			$publish_time = $args[3]['date_created_gmt'];
			
			$timezone = new DateTimeZone((string)date_default_timezone_get());
			$datetime = new DateTime('now', $timezone);		
			$offset = date_offset_get($datetime);
			
			$values['publish_time'] = $publish_time->getTimestamp() + $offset;
		}
		$values['published'] = $args[4];
		if( isset($args[3]['categories'][0]) )
		{
			$category = $this->model('categories')->getByName($args[3]['categories'][0]);
			$values['category'] = (int)$category->id;
		} 
		else $values['category'] = 0;
		$values['content_type'] = 'page';
		return $values;
	}
	
	public function getPost($args)
	{
		$pageId = (int)$args[0];
		$username = $args[1];
		$password  = $args[2];
		$user = $this->login($username, $password);
		if ( !$user ) {
			return $this->error;
		}
		
		try
		{
			$page = $this->model('pages')->getById($pageId);
			if($page == false)
			{
				return new IXR_Error(500, 'No such post');
			}
		}
		catch(Exception $e)
		{
			return new IXR_Error(500, $e->getMessage());
		}
		
		return $this->fillPost($page, $user);
	}
	
	public function getRecentPosts($args)
	{
		$blogId = (int)$args[0];
		$username = $args[1];
		$password  = $args[2];
		$posts_count = (int)$args[3];
		$user = $this->login($username, $password);
		if ( !$user ) {
			return $this->error;
		}
		
		try
		{
			$pages = $this->model('pages')->getRecent($posts_count);
		}
		catch(Exception $e)
		{
			return new IXR_Error(500, $e->getMessage());
		}
		
		$struct = array();
		foreach ($pages as $page) {
			$struct[] = $this->fillPost($page, $user);
		}
		return $struct;
	}
	
	private function fillPost($page, $user)
	{
		$struct = array();
		try
		{
			if($page->category == 0) $struct['link'] = $this->link(':Front:Page:categoryView', array('category' => $page->slug));
			else $struct['link'] = $this->link(':Front:Page:pageView', array('category' => $page->category_slug, 'page' => $page->slug));
		}
		catch(Exception $e)
		{
			return new IXR_Error(500, $e->getMessage());
		}
		if($page->category != 0) $struct['link'] = $this->link(':Front:Page:categoryView', array('category' => $page->slug));
		else $struct['link'] = $this->link(':Front:Page:pageView', array('category' => $page->category_slug, 'page' => $page->slug));
		$struct['permaLink'] = $struct['link'];
		$struct['userid'] = $user->getIdentity()->getId();
		//$struct['userid'] = (int)$user->getIdentity()->getName();
		$struct['postid'] = (string)$page->id;
		$struct['dateCreated'] = new IXR_date($page->added);
		$struct['title'] = (string)$page->title;
		$struct['description'] = (string)$page->content;
		$struct['categories'] = array(0 => $page->category_title);
		$struct['publish'] = (bool)$page->published;
		return $struct;
	}
	
	public function newCategory($args)
	{
		$blogId = (int)$args[0];
		$username = $args[1];
		$password  = $args[2];
		$user = $this->login($username, $password);
		if ( !$user ) {
			return $this->error;
		}
		$values['title'] = $args[3]['name'];
		$values['parent'] = (int)$args[3]['parent_id'];
		$values['slug'] = String::webalize($values['title']);
		try
		{
			$cat = $this->model('categories')->save($values);
		}
		catch(Exception $e)
		{
			return new IXR_Error(500, $e->getMessage());
		}
		return $cat['id'];
	}
	
	public function getCategories($args)
	{
		$blogId = (int)$args[0];
		$username = $args[1];
		$password  = $args[2];
		$user = $this->login($username, $password);
		if ( !$user ) {
			return $this->error;
		}
		$categories_struct = array();
		try
		{
			$categories = $this->model('categories')->getAll();
		}
		catch(Exception $e)
		{
			return new IXR_Error(500, $e->getMessage());
		}
		
		foreach ( $categories as $cat )
		{
			$struct['categoryId'] = $cat->id;
			$struct['parentId'] = $cat->parent;
			$struct['description'] = $cat->title;
			$struct['categoryDescription'] = $cat->description;
			$struct['categoryName'] = $cat->title;
			$struct['htmlUrl'] = $this->link(':Front:Page:categoryView', array('category' => $cat->slug));
			$struct['rssUrl'] = $this->link(':Front:Rss:rss', array('category' => $cat->slug));
	
			$categories_struct[] = $struct;
		}
		return $categories_struct;
	}
	
	public function newMediaObject($args)
	{
		$blogId = (int)$args[0];
		$username = $args[1];
		$password  = $args[2];
		$user = $this->login($username, $password);
		if ( !$user ) {
			return $this->error;
		}
		$path = 'media/pictures/pages/';
		$filename = str_replace('/', '_', $args[3]['name']);
		$data = $args[3]['bits'];
		$type = $args[3]['type'];
		file_put_contents($path.$filename, $data);
		$struct = array();
		$struct['file'] = $filename;
		$uri = $this->getHttpRequest()->getUri();
		$uri->baseUri; 
		$uri->hostUri;
		$struct['url'] = $uri->baseUri.$path.$filename;
		$struct['type'] = $type;
		return $struct;
	}
	
	public function setPostCategories($args)
	{
		return true;
	}
	
	public function getPostCategories($args)
	{
		$pageId = (int)$args[0];
		$username = $args[1];
		$password  = $args[2];
		$user = $this->login($username, $password);
		if ( !$user ) {
			return $this->error;
		}
		try
		{
			$page = $this->model('pages')->getById($pageId);
		}
		catch(Exception $e)
		{
			return new IXR_Error(500, $e->getMessage());
		}
		
		$struct['categoryId'] = $page->category_id;
		$struct['categoryName'] = $page->category_title;
		return array($struct);
	}
	
	public function getTags($args)
	{
		$pageId = (int)$args[0];
		$username = $args[1];
		$password  = $args[2];
		$user = $this->login($username, $password);
		if ( !$user ) {
			return $this->error;
		}
		$tags = $this->model('tags')->getAll();
		$array = array();
		foreach($tags as $tag)
		{
			$struct = array();
			$struct['tag_id'] = (int)$tag->tag_id;
			$struct['name'] = (string)$tag->tag_name;
			$struct['count'] = 1;
			$struct['slug'] = String::webalize($struct['name']);
			$struct['html_url'] = '';
			$struct['rss_url'] = '';
			$array[] = $struct;
		}
		return $array;
	}
}
?>