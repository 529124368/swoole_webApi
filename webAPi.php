<?php
require_once __DIR__.'/vendor/autoload.php';
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Server;

use function Swoole\Coroutine\run;
use Swoole\Database\PDOPool;
use Swoole\Database\PDOConfig;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);
//容器
run(function () {
    //mysql 链接
    $servername = '0.0.0.0';
    $dbname = 'mir3';
    $username = 'root';
    $password = '';
    $pool = new PDOPool((new PDOConfig)
        ->withHost($servername)
        ->withPort(3306)
        ->withDbName($dbname)
        ->withCharset('utf8mb4')
        ->withUsername($username)
        ->withPassword($password)
    );
    //redis 连接池
    $redisPool = new RedisPool((new RedisConfig)
        ->withHost('127.0.0.1')
        ->withPort(6379)
        ->withAuth('')
        ->withDbIndex(0)
        ->withTimeout(1)
    );
    $server = new Server('127.0.0.1', 8089, false);
    //路由设置
    $server->handle('/swoole', function ($request, $response) {
        $response->end("<h1>welcome swoole</h1>");
    });
    //注册
    $server->handle('/register', function ($request, $response) use($pool){
        apiHandle\RegisterHandle::register_method($request, $response,$pool);
    });
    //登录
    $server->handle('/checkAccount', function ($request, $response)use($pool, $redisPool){
        apiHandle\AccountHandle::checkAccount_method($request, $response,$pool, $redisPool);
    });
    //判断是否登录过
    $server->handle('/checkislogined', function ($request, $response)use($redisPool){
        apiHandle\AccountHandle::checkislogined($request, $response,$redisPool);
    });
    //登出账号
    $server->handle('/outlogin', function ($request, $response)use($redisPool){
        apiHandle\AccountHandle::outAccount($request, $response,$redisPool);
    });
    //验证码生成
    $server->handle('/code', function ($request, $response){
        $response->header("Content-Type", "image/png");
        $img_datas = tools\Tools::getCodeKey();
        $response->end($img_datas);
        
    });
    //启动
    $server->start();
});






