<?php
namespace apiHandle;
use tools\Tools;
class AccountHandle {
    public function  __constract(){}
    //查询用户
    public static function checkAccount_method($request, $response,$pool, $redisPool) {
        if(!Tools::routeCheck($request,$response)) {
            //结果返回
            $res = array(
                "state" =>"error",
                "data"=> "",
                "message"=>"查询失败"
            );
            $response->end(json_encode($res));
            return;
        }
        if(!isset($request->post["account"]) || !isset($request->post["password"])) {
            //结果返回
            $res = array(
                "state" =>"error",
                "data"=> "",
                "message"=>"账号信息输入不正确1"
            );
            $response->end(json_encode($res));
            return;
        }
        $db = $pool->get();
        $stmt = $db->prepare('SELECT id from user_account where account = ? and password = ?');
        if ($stmt == false)
        {
            $pool->put( $db);
            //结果返回
            $res = array(
                "state" =>"error",
                "data"=> "",
                "message"=>"账号信息输入不正确2"
            );
            $response->end(json_encode($res));
            return;
        }
        //data 
        $param = array($request->post["account"],$request->post["password"]);
        $stmt->execute($param);
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if(count($res) == 0 || count($res) >1) {
            $pool->put( $db);
            //结果返回
            $res = array(
                "state" =>"error",
                "data"=> "",
                "message"=>"账号信息输入不正确3"
            );
            $response->end(json_encode($res));
            return;
        }
        //结果返回
        $res = array(
            "state" =>"ok",
            "data"=> $res,
            "message"=>"查询成功"
        );
        $redis=$redisPool->get();
        $userId = Tools::uuid();
        $redis->set($userId, "ok");
        $redisPool->put($redis);
        $response->setcookie("userId",$userId,time()+3600*2,'/');
        $response->end(json_encode($res));
        $pool->put( $db);
    }
     
    public static function checkislogined($request,$response,$redisPool) {
        if(!Tools::routeCheck($request,$response)) {
            //结果返回
            $res = array(
                "state" =>"error",
                "data"=> "",
                "message"=>"查询失败"
            );
            $response->end(json_encode($res));
            return;
        }
        $userId = isset($request->cookie["userId"])?$request->cookie["userId"]:"";
        $redis=$redisPool->get();
        if ($redis->get($userId)==="ok") {
            //结果返回
            $res = array(
                "state" =>"ok",
                "data"=> "",
                "message"=>"登录过"
            );
        }else {
             //结果返回
             $res = array(
                "state" =>"error",
                "data"=> "",
                "message"=>"没登录"
            );
        }
        $redisPool->put($redis);
        $response->end(json_encode($res));
    }

    public static function outAccount($request,$response,$redisPool) {
        if(!Tools::routeCheck($request,$response)) {
            //结果返回
            $res = array(
                "state" =>"error",
                "data"=> "",
                "message"=>"查询失败"
            );
            $response->end(json_encode($res));
            return;
        }
        $userId = $request->cookie["userId"];
        $redis=$redisPool->get();
        $redis->del($userId);
        $response->setcookie("userId","",-1,'/');
        //结果返回
        $res = array(
            "state" =>"ok",
            "data"=> "",
            "message"=>"删除成功"
        );
        $redisPool->put($redis);
        $response->end(json_encode($res));
    }
}