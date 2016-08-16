#微信网页开发——认证获取用户信息及openid
1.需要微信公众号平台提供的参数分别为：
	AppID         //(应用ID)
	AppSecret	  //(应用密钥) 

   【登陆：https://mp.weixin.qq.com注册后在基本配置选项中可获得】




#小插曲：目前微信授权获取用户基本信息有两种模式
	模式1.snsapi_base     【不给用户任何提醒，自动跳转且只能获得openid】
	模式2.snsapi_userinfo 【提示用户可能需要的信息，并让用户点击确认，能得到openid，照片，归属地等信息】
	【注意：需要你】






#2.第一步：在网页做跳转配置，访问微信认证模块
	【注意：这个网页要在微信浏览器中打开，否者会提示请在微信浏览器中打开】
	    $AppID              <=如何获得请看第一步
	    $redirect_uri       <=填写你的回调地址，如http://www.A.com
	    $response_type      <=这个不用修改
	    $scope              <=这个你根据自己业务需求自己选择：snsapi_base  或是  snsapi_userinfo
	    $state=STATE        <=这个你可以自己随便填，因为有些网站回调时某些参数会被切掉，你可以填那个被切掉的参数
	    $#wechat_redirect   <=哇！！！这个超级重要，如果不填这个，请求肯定不能通过
	
	<a href=“https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect”>点击认证</a>

	（1）接下来在【微信浏览器中】点击这个超链接后，就会打开这个认证页面了。【只有选择了认证模式为2时才会出现这个页面】


	（2）用户点击确认后，认证成功会自动跳转到你填写的回调网址，这里假设是我上面填的那个www.A.com示例：
         【用户点确认后跳转到】http://www.a.com?code=[微信附加的code，重要]&state=[第3步中你填的参数]

#3.第二步：使用curl来请求微信认证服务器返回用户的个人信息[本文示例使用的是php开发]
		  $appid            <=和第2步一样
		  $secret           <=不用修改
		  $code  			<=第一步获得的code填进来
		  $grant_type       <=不用修改
         【请求链接，注意是：https的】https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code

      （1）微信返回json 
      		{
			   "access_token":"ACCESS_TOKEN",
			   "expires_in":7200,
			   "refresh_token":"REFRESH_TOKEN",
			   "openid":"OPENID",  //用户微信openid，具有唯一性
			   "scope":"SCOPE", 
			   "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"
			}

#4.第三步：进一步获取用户头像，昵称，性别，省份城市的【只有在第一步选择了模式2.snsapi_userinfo才能请求这个】
			$access_token       <=上一步获得的参数
			$openid             <=上一步获得的参数
			$lang               <=返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
		【请求链接，注意是：https的】https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN

	  （1）微信返回json
			{
			   "openid":" OPENID",
			   " nickname": NICKNAME,    //昵称
			   "sex":"1",				 //性别
			   "province":"PROVINCE"     //省份地区信息
			   "city":"CITY",
			   "country":"COUNTRY",
			    "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/46", 
				"privilege":[             //	用户特权信息，json 数组，如微信沃卡用户为（chinaunicom）
				"PRIVILEGE1"
				"PRIVILEGE2"
			    ],
			    "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL" //不知道干嘛的
			}
