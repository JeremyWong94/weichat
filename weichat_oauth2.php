<?php session_start();defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * 直接把这个文件放到codeigniter的控制器【controller里】
 * 访问路径是：localhost:80/codeigniter/index.php/weichat_oauth2/OpenAccess
 * User: wangzhenjiang
 * Date: 2016/8/10
 * Time: 10:00
 */
class weichat_oauth2 extends CI_Controller
{
    //用户id
    public $openid = 'dsadadadas';
    //用户img的src
    public $user_img = '';
    //公众号信息部分
    public $appid = "wxf90a45fd49c3981d";
    public $appsecret = "10257ff5aa1c75d3befd589d46225578";
    public $redirect_uri = "【删除这个并填入你的回调地址，例如：http://www.a.com】";
    public $scope = '【删除这个并填入请求类型，例如：snsapi_userinfo】';

    public function __construct()
    {
        parent::__construct();
        //加载session类方法
        $this->load->library('session');
    }

    /*
     * 接入入口
     * 开放到微信菜单中调用
     * @param $dir_url 来源url
     * @since 1.0
     * @return void
     */
    public function OpenAccess()
    {
        //判断session不存在
        if (!isset($_SESSION['openid'])) {
            //加载CI辅助方法-URL
            $this->load->helper('url');
            //认证第一步：重定向跳转至认证网址
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appid&redirect_uri=$this->redirect_uri&&response_type=code&scope=snsapi_userinfo&m=oauth2#wechat_redirect";
            redirect("$url");
        }
        //判断session存在
        else {
            //跳转到前端页面.html
            $this->load->helper('url');
            redirect("http://mt.mangocity.com/activity/weixin/index.php");
        }
    }


    /*
     *微信认证获取openid部分：
     * @param $code 临时认证code
     * @since 1.0
     * @return $openid
     */
    public function index()
    {
        //微信认证部分：第二步    获得code
        $code = $this->input->get('code');
        if(!isset($code))
        {
            //如果code没获取成功，重新拉取一遍
            $this->OpenAccess();
        }
        //微信认证部分：第三步    获得openid
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appid&secret=$this->appsecret&code=$code&grant_type=authorization_code";
        $result = $this->do_curl($url); //做curl的方法
        $output_array = json_decode($result, true);//将json转为数组
        
        //微信认证部分：第四步   获得更多信息
        $access-token = $output_array['access_token'];
        $openid = $output_array['openid'];
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access-token&openid=$openid&lang=zh_CN";
        $result1 = $this->do_curl($url);//做curl的方法
        $output_array1 = json_decode($result1, true);//将json转为数组

        //以下是第四步获得的信息：       
        $nickname = $output_array['nickname']; //昵称
        $sex = $output_array['sex']; //性别什么的
        $headimgurl = $output_array['headimgurl']; //头像url

        //将获得的用户信息填入到session中
        $_SESSION['openid'] = $output_array['openid'];
        //转向回入口
        $this->OpenAccess();
    }



   
    /*
     * ==========================================================
     * 以下是私有的方法
     */

    /*
     * curl请求用临时code换取openid
     * @param string $url 做url请求的url
     * @param string $url 最大请求时间
     * @since 1.0
     * @return array
     */
    private function do_curl($url = '',$timeout=30)
    {
        //初始化
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);//最大请求等待时间
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        //释放curl句柄
        curl_close($ch);
        //打印获得的数据
        return $output;
    }
    /*
     * 输出成json格式
     * @param array $array
     * @since 1.0
     * @return void
     */
    private function echo_json($array)
    {
        header("Access-Control-Allow-Origin:*");//跨域ajax访问不拦截
        header('Content-type: application/json');//头部类型定义为json
        echo json_encode($array);
    }

}