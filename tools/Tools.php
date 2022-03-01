<?php
namespace tools;
class Tools {
    public function  __constract(){}
    //检测
    public static function routeCheck($request,$response) {
        if($request->server["request_method"]=="POST") {
            //跨域
            $response->header("Content-Type", "text/html;charset=UTF-8");
            // $response->header("Access-Control-Allow-Origin","http://www.zimuge.tk");
            // $response->header("Access-Control-Allow-Credentials", "true");
            return true;
        }else {
            return false;
        }
        
    }
    //生成验证码
    public static function getCodeKey() {
        $img = imagecreatetruecolor(100, 40);
        $black = imagecolorallocate($img, 0x00, 0x00, 0x00);
        $green = imagecolorallocate($img, 0x00, 0xFF, 0x00);
        $white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);
        imagefill($img, 0, 0, $green);    //绘制底色为白色
        //绘制随机的验证码
        $code = '';
        $key="QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm0123456789";
        $code = $key[rand(0, strlen($key)-1)].$key[rand(0, strlen($key)-1)].$key[rand(0, strlen($key)-1)].$key[rand(0, strlen($key)-1)];
        imagestring($img, 6, 13, 10, $code, $black);
        //加入噪点干扰
        for ($i = 0; $i < 50; $i++) {
            imagesetpixel($img, rand(0, 100), rand(0, 100), $black);
            imagesetpixel($img, rand(0, 100), rand(0, 100), $white);
        }
        //输出验证码
        ob_start();
        imagepng($img);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    
    public static function  uuid() {  
        $chars = md5(uniqid(mt_rand(), true));  
        $uuid = substr ( $chars, 0, 8 ) . '-'
                . substr ( $chars, 8, 4 ) . '-' 
                . substr ( $chars, 12, 4 ) . '-'
                . substr ( $chars, 16, 4 ) . '-'
                . substr ( $chars, 20, 12 );  
        return $uuid ;  
    } 

}