<?php
namespace apiHandle;
use tools\Tools;
class RegisterHandle {
    public function  __constract(){}
    //注册用户
    public static function register_method($request, $response,$pool) {
        if(!Tools::routeCheck($request,$response)) {
            //结果返回
            $res = array(
                "state" =>"error",
                "data"=> "",
                "message"=>"注册失败"
            );
            $response->end(json_encode($res));
            return;
        }
        $response->header("Content-Type", "text/html;charset=UTF-8"); 
        if(!isset($request->post["name"]) || !isset($request->post["account"]) || !isset($request->post["password"])) {
            $response->end(json_encode(array("msg"=>"账号信息输入不正确","status"=>"error")));
            return;
        }
        $db = $pool->get();
        $stmt = $db->prepare('INSERT INTO  user_account(name, account, password,register_time) values(?,?,?,current_timestamp())');
        if ($stmt == false){
            $pool->put( $db);
            //结果返回
            $res = array(
                "state" =>"error",
                "data"=> "",
                "message"=>"注册失败"
            );
            $response->end(json_encode($res));
            return;
        }
        //data 
        $param = array($request->post["name"],$request->post["account"],$request->post["password"]);
        if($stmt->execute($param)) {
            $pool->put($db);
            //结果返回
            $res = array(
                "state" =>"ok",
                "data"=> "",
                "message"=>"注册成功"
            );
            $response->end(json_encode($res));
        }else {
            $pool->put($db);
            //结果返回
            $res = array(
                "state" =>"error",
                "data"=> "",
                "message"=>"注册失败"
            );
            $response->end(json_encode($res));
        }
        
    }

}