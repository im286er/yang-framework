<?php
/**
 * Author: yangyang
 * Date  : 17-12-23
 * Time  : 下午3:46
 */

namespace yang;


use ReflectionClass, ReflectionFunction;
/*
 * 入口控制
 */
class App implements \ArrayAccess
{
    // 调用App的内置公共函数
    public static $instrace;

    private static $request, $route;

    public $container;

    public function __construct()
    {
        $this->container = Container::getInstance();
    }

    /**
     * 创建基础结构
     */
    public function create(Request $request = null)
    {
        // self::$app_debug = Env::get('app_debug');
        Fastload::includeFile(Env::get('root_path') . 'helper.php');
        Log::recore('DATE', date('Y-m-d H:i:s', time()));
        if (empty(self::$instrace)) {
            self::$instrace = new static();
        }
        self::$request = $request !== null ? $request : Request::create();
        self::$route = $this->route->create([], self::$request);
        // self::$instrace->start(); 测试一下
    }

    /**
     * 创建基础目录结构
     */
    private function createBase() {
    }

    /**
     * 监听应用
     */
    public function listen()
    {
        Fastload::includeFile(Env::get('app_path') . 'helper.php');
        Fastload::add(Env::get('app_name') . "\\", Env::get('app_path'));
        ob_start();
        $data = self::$route->listen('index/index/index');
        // 请求完毕
        // App::dump($data);
        self::send($data);
        if (Common::$app_debug) {
            Debug::create('end', 'run end');
        }
    }

    /**
     * 发送消息
     * @param $data
     */
    private static function send($data) {
        if (is_a($data, __NAMESPACE__ . '\\Response')) {
            $data->send();
        } else {
            Response::create($data, 200)->send();
        }
    }



    public function __set($name, $value)
    {
        $this->container->bind($name, $value);
    }

    public function __get($name)
    {
        return $this->container->make($name);
    }

    public function __isset($name)
    {
        return $this->container->bound($name);
    }

    public function __unset($name)
    {
        $this->container->__unset($name);
    }

    public function offsetExists($key)
    {
        return $this->__isset($key);
    }

    public function offsetGet($key)
    {
        return $this->__get($key);
    }

    public function offsetSet($key, $value)
    {
        $this->__set($key, $value);
    }

    public function offsetUnset($key)
    {
        $this->__unset($key);
    }
}