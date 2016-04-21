<?php
namespace AppBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DIContainer extends ContainerBuilder {

    public static $instance;

    public function __construct() {
      parent::__construct();
      $this -> register('posts', 'AppBundle\Services\PostService');
    }

    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new DIContainer();
        }
        return self::$instance;
    }

    public static function service($name) {
        return self::getInstance() -> get($name);
    }

}

?>