<?php
require_once __DIR__.'/vendor/autoload.php';
use Swoole\Process;
use Swoole\Database\PDOPool;
use Swoole\Database\PDOConfig;
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

cli_set_process_title("swoole_web_api.php");

Co::set(['hook_flags' => SWOOLE_HOOK_ALL]);
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
$pools = new Process\Pool(2);  
//让每个OnWorkerStart回调都自动创建一个协程
$pools->set(['enable_coroutine' => true]);
$pools->on("workerStart", function (Process\Pool $pools, $workerId)use($pool,$redisPool) {
    $server = new Co\Http\Server('127.0.0.1', 8089, false,true);
    //路由设置
    $server->handle('/swoole', function ($request, $response) {
        $response->end("<h1>welcome swoole</h1>");
    });
    //注册
    $server->handle('/register', function ($request, $response) use($pool,$redisPool){
        try {
            apiHandle\RegisterHandle::register_method($request, $response,$pool,$redisPool);
        }catch(\Throwable $e) {
            echo $e;
        }
    });
    //登录
    $server->handle('/checkAccount', function ($request, $response)use($pool, $redisPool){
        try {
            apiHandle\AccountHandle::checkAccount_method($request, $response,$pool, $redisPool);
        }catch(\Throwable $e) {
            echo $e;
        }
    });
    //判断是否登录过
    $server->handle('/checkislogined', function ($request, $response)use($redisPool){
        try {
            apiHandle\AccountHandle::checkislogined($request, $response,$redisPool);
        }catch(\Throwable $e) {
            echo $e;
        }
        
    });
    //登出账号
    $server->handle('/outlogin', function ($request, $response)use($redisPool){
        try{
            apiHandle\AccountHandle::outAccount($request, $response,$redisPool);
        }catch(\Throwable $e) {
            echo $e;
        }
    });
    //验证码生成
    $server->handle('/code', function ($request, $response)use($redisPool){
	    try{
            $datas = tools\Tools::getCodeKey();
            $redis=$redisPool->get();
            if(!isset($request->cookie["userId"]) || !$redis->exists($request->cookie["userId"])) {
                $userId = tools\Tools::uuid();
                $response->setcookie("userId",$userId,time()+3600*2,'/');
                $redis->set($userId, $datas[1]);
            }else if($redis->get($request->cookie["userId"]) != "ok") {
                $redis->set($request->cookie["userId"], $datas[1]);
            }
            $redisPool->put($redis);
            $response->header("Content-Type", "image/png");
            $img_datas = $datas[0];
            $response->end($img_datas);
        }catch(\Throwable $e) {
            echo $e;
        }
    });
    //启动
    $server->start();
});
$pools->start();






