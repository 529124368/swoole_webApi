<?php
require_once __DIR__.'/vendor/autoload.php';
use Swoole\Coroutine;
use Swoole\Coroutine\Http\Server;
use Swoole\Process;

use function Swoole\Coroutine\run;
use Swoole\Database\PDOPool;
use Swoole\Database\PDOConfig;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;
Swoole\Process::daemon();
cli_set_process_title("swoole_web_api.php");
Coroutine::set(['hook_flags' => SWOOLE_HOOK_ALL]);

$pools = new Swoole\Process\Pool(2);  
//让每个OnWorkerStart回调都自动创建一个协程
$pools->set(['enable_coroutine' => true]);
$pools->on("workerStart", function ($pool, $id) {

//容器
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
    $server = new Server('127.0.0.1', 8089, false,true);
    $server->set([
        'daemonize' => 1,
        'log_file' => __DIR__.'/swoole.log',
        'log_date_format' => '%Y-%m-%d %H:%M:%S',
        'log_level' => 2, //日志级别 范围是0-5，0-DEBUG，1-TRACE，2-INFO，3-NOTICE，4-WARNING，5-ERROR
    ]);
    //路由设置
    $server->handle('/swoole', function ($request, $response) {
        $response->end("<h1>welcome swoole</h1>");
    });
    //注册
    $server->handle('/register', function ($request, $response) use($pool){
        try {
            apiHandle\RegisterHandle::register_method($request, $response,$pool);
        }catch(\Throwable $e) {
            echo $e;
        }
    });
    //登录
    $server->handle('/checkAccount', function ($request, $response)use($pool, $redisPool){
        try {
            echo "ok4";
            apiHandle\AccountHandle::checkAccount_method($request, $response,$pool, $redisPool);
        }catch(\Throwable $e) {
            echo $e;
        }
    });
    //判断是否登录过
    $server->handle('/checkislogined', function ($request, $response)use($redisPool){
        try {
            echo "ok3";
            apiHandle\AccountHandle::checkislogined($request, $response,$redisPool);
        }catch(\Throwable $e) {
            echo $e;
        }
        
    });
    //登出账号
    $server->handle('/outlogin', function ($request, $response)use($redisPool){
        try{
            echo "ok2";
            apiHandle\AccountHandle::outAccount($request, $response,$redisPool);
        }catch(\Throwable $e) {
            echo $e;
        }
    });
    //验证码生成
    $server->handle('/code', function ($request, $response){
	    try{
            echo "ok1";
            $response->header("Content-Type", "image/png");
            $img_datas = tools\Tools::getCodeKey();
            $response->end($img_datas);
        }catch(\Throwable $e) {
            echo $e;
        }
    });
    //启动
    $server->start();
});
$pools->start();






