<?php

class Panel {

  static public $version = '2.1.0';
  static public $instance;

  public $kirby;
  public $site;
  public $path;
  public $roots;
  public $routes = array();
  public $router = null;
  public $route  = null;
  public $language = null;
  public $languages = null;

  static public function instance() {
    return static::$instance;
  }

  static public function version() {
    return static::$version;
  }

  public function defaults() {

    return array(
      'panel.language' => 'en',
      'panel.widgets'  => array(
        'pages'   => true,
        'site'    => true,
        'account' => true,
        'history' => true
      ),
    );

  }

  public function __construct($kirby, $root) {

    static::$instance = $this;

    $this->kirby = $kirby;
    $this->site  = $kirby->site();
    $this->roots = new Panel\Roots($this, $root);
    $this->urls  = new Panel\Urls($this, $root);

    // add the panel default options
    $this->kirby->options = array_merge($this->defaults(), $this->kirby->options);

    // load all Kirby extensions (methods, tags, smartypants)
    $this->kirby->extensions();
    $this->kirby->plugins();

    $this->load();

    // setup the blueprint root
    blueprint::$root = $this->kirby->roots()->blueprints();

    // setup the form plugin
    form::setup($this->roots->fields, $this->kirby->roots()->fields());

    // load all available routes
    $this->routes = array_merge($this->routes, require($this->roots->routes . DS . 'api.php'));
    $this->routes = array_merge($this->routes, require($this->roots->routes . DS . 'views.php'));

    // start the router
    $this->router = new Router($this->routes);

    // register router filters
    $this->router->filter('auth', function() use($kirby) {

      $user = $kirby->site()->user();

      if(!$user or !$user->hasPanelAccess()) {
        if($user) $user->logout();
        go('panel/login');
      }
    });

    // check for a completed installation
    $this->router->filter('isInstalled', function() use($kirby) {
      if($kirby->site()->users()->count() == 0) {
        go('panel/install');
      }
    });

  }

  public function kirby() {
    return $this->kirby;
  }

  public function site() {
    return $this->site;
  }

  public function roots() {
    return $this->roots;
  }

  public function routes($routes = null) {
    if(is_null($routes)) return $this->routes;
    return $this->routes = array_merge($this->routes, (array)$routes);
  }

  public function urls() {
    return $this->urls;
  }

  public function form($id, $data = array()) {
    $fields = data::read($this->roots->forms . DS . $id . '.yaml', 'yaml');
    return new Form($fields, $data);
  }

  public function load() {

    load(array(

      // mvc
      'view'         => $this->roots->lib . DS . 'view.php',
      'controller'   => $this->roots->lib . DS . 'controller.php',
      'layout'       => $this->roots->lib . DS . 'layout.php',
      'snippet'      => $this->roots->lib . DS . 'snippet.php',

      // panel stuff
      'api'            => $this->roots->lib . DS . 'api.php',
      'assets'         => $this->roots->lib . DS . 'assets.php',
      'fieldoptions'   => $this->roots->lib . DS . 'fieldoptions.php',
      'filedata'       => $this->roots->lib . DS . 'filedata.php',
      'form'           => $this->roots->lib . DS . 'form.php',
      'history'        => $this->roots->lib . DS . 'history.php',
      'installation'   => $this->roots->lib . DS . 'installation.php',
      'pagedata'       => $this->roots->lib . DS . 'pagedata.php',
      'pagestore'      => $this->roots->lib . DS . 'pagestore.php',
      'pageeditor'     => $this->roots->lib . DS . 'pageeditor.php',
      'pageuploader'   => $this->roots->lib . DS . 'pageuploader.php',
      'structurestore' => $this->roots->lib . DS . 'structurestore.php',
      'subpages'       => $this->roots->lib . DS . 'subpages.php',
      'widgets'        => $this->roots->lib . DS . 'widgets.php',
      'topbar'         => $this->roots->lib . DS . 'topbar.php',

      // blueprint stuff
      'blueprint'         => $this->roots->lib . DS . 'blueprint.php',
      'blueprint\\pages'  => $this->roots->lib . DS . 'blueprint' . DS . 'pages.php',
      'blueprint\\files'  => $this->roots->lib . DS . 'blueprint' . DS . 'files.php',
      'blueprint\\fields' => $this->roots->lib . DS . 'blueprint' . DS . 'fields.php',
      'blueprint\\field'  => $this->roots->lib . DS . 'blueprint' . DS . 'field.php',

    ));

  }

  public function languages() {

    $languages = new Collection;
    $root      = $this->roots()->languages();

    foreach(dir::read($root) as $file) {

      // skip invalid language files
      if(f::extension($file) != 'php') continue;

      // fetch all strings from the language file
      $strings = require($root . DS . $file);

      // skip invalid language files
      if(!is_array($strings)) continue;

      // create the language object
      $language = new Obj($strings);
      $language->code = str_replace('.php', '', $file);
      $languages->set($language->code, $language);

    }

    return $languages;

  }

  public function language() {
    return $this->language;
  }

  public function i18n() {

    // load the interface language file
    if($user = $this->site()->user()) {
      $this->language = $user->language();
    } else {
      $this->language = $this->kirby()->option('panel.language', 'en');
    }

    $translation = require($this->roots()->languages() . DS . 'en.php');
    $translation = a::merge($translation, require($this->roots()->languages() . DS . $this->language . '.php'));

    // set all language variables
    l::$data = $translation['data'];

    // set language direction (ltr is default)
    if(isset($translation['direction']) and $translation['direction'] == 'rtl') {
      l::set('language.direction', 'rtl');
    } else {
      l::set('language.direction', 'ltr');
    }

  }

  public function multilang() {

    if(!$this->site->multilang()) {
      $this->site->visit('/');
      return true;
    }

    if($language = server::get('http_language') or $language = s::get('lang')) {
      $this->site->visit('/', $language);
    } else {
      $this->site->visit('/');
    }

    s::set('lang', $this->site->language()->code());

  }

  public function direction() {
    return l::get('language.direction');
  }

  public function launch($path = null) {

    // set the timezone for all date functions
    date_default_timezone_set($this->kirby->options['timezone']);

    $this->i18n();
    $this->multilang();

    $this->path  = $this->kirby->path();
    $this->route = $this->router->run($this->path);

    try {

      // react on invalid routes
      if(!$this->route) {
        throw new Exception('Invalid Panel URL');
      }

      if(is_callable($this->route->action())) {
        $response = call($this->route->action(), $this->route->arguments());
      } else {
        $response = $this->response();
      }

    } catch(Exception $e) {
      require_once($this->roots->controllers . DS . 'views' . DS . 'error.php');
      $controller = new ErrorController();
      $response   = $controller->index($e->getMessage());
    }

    // send custom header for ajax requests
    header('X-Panel-Location: ' . url::current());

    // set the username of the current user
    if($user = site()->user()) {
      header('X-Panel-User: ' . $user->username());      
    }

    ob_start();

    // check for a valid response object
    if(is_a($response, 'Response')) {
      echo $response;
    } else {
      echo new Response($response);
    }

    ob_end_flush();

  }

  public function response() {

    // let's find the controller and controller action
    $controllerParts  = str::split($this->route->action(), '::');
    $controllerUri    = $controllerParts[0];
    $controllerAction = $controllerParts[1];
    $controllerFile   = $this->roots->controllers . DS . strtolower(str_replace('Controller', '', $controllerUri)) . '.php';
    $controllerName   = basename($controllerUri);

    // react on missing controllers
    if(!file_exists($controllerFile)) {
      throw new Exception('Invalid controller');
    }

    // load the controller
    require_once($controllerFile);

    // check for the called action
    if(!method_exists($controllerName, $controllerAction)) {
      throw new Exception('Invalid action');
    }

    // run the controller
    $controller = new $controllerName;

    // call the action and pass all arguments from the router
    return call(array($controller, $controllerAction), $this->route->arguments());

  }

  public function license() {

    $key  = c::get('license');
    $type = 'trial';

    /**
     * Hey stranger, 
     * 
     * So this is the mysterious place where the panel checks for 
     * valid licenses. As you can see, this is not reporting
     * back to any server and the license keys are rather simple to 
     * hack. If you really feel like removing the warning in the panel
     * or tricking Kirby into believing you bought a valid license even 
     * if you didn't, go for it! But remember that literally thousands of 
     * hours of work have gone into Kirby in order to make your 
     * life as a developer, designer, publisher, etc. easier. If this 
     * doesn't mean anything to you, you are probably a lost case anyway. 
     * 
     * Have a great day! 
     * 
     * Bastian
     */
    if(str::startsWith($key, 'K2-PRO') and str::length($key) == 39) {
      $type = 'Kirby 2 Professional';
    } else if(str::startsWith($key, 'K2-PERSONAL') and str::length($key) == 44) {
      $type = 'Kirby 2 Personal';
    } else if(str::startsWith($key, 'MD-') and str::length($key) == 35) {
      $type = 'Kirby 1';
    } else if(str::startsWith($key, 'BETA') and str::length($key) == 9) {
      $type = 'Kirby 1';
    } else if(str::length($key) == 32) {
      $type = 'Kirby 1';
    } else {
      $key = null;
    }

    $localhosts = array('::1', '127.0.01', '0.0.0.0');

    return new Obj(array(
      'key'   => $key,
      'local' => (in_array(server::get('SERVER_ADDR'), $localhosts) or server::get('SERVER_NAME') == 'localhost'),
      'type'  => $type,
    ));

  }

  public function notify($text) {
    s::set('message', array(
      'type' => 'notification', 
      'text' => $text,
    ));
  }

  public function alert($text) {
    s::set('message', array(
      'type' => 'error', 
      'text' => $text,
    ));
  }

}