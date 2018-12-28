<?php
	class wechat{
		 private $_appid;
		 private $_appsecret;
		 private $_token;
		 public function __construct($_appid,$_appsecret,$_token){
			 $this->_appid=$_appid;
			 $this->_appsecret=$_appsecret;
			 $this->_token=$_token; 
		 }
		 public function _request($curl,$https=true,$method='GET',$data=null){
			 $ch=curl_init();
			 //设置获取地址
			 curl_setopt($ch,CURLOPT_URL,$curl);
			 curl_setopt($ch,CURLOPT_HEADER,false);
			 curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			 if($https){
				 curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
				 curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
			 }
			 if($method=='POST'){
				 curl_setopt($ch,CURLOPT_POST,true);
				 curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
			 }
			 $content=curl_exec($ch);
			 curl_close($ch);
			 return $content;
		 }
		 public function _getAccessToken(){//获得token
			 $file='./accesstoken';
			 if(file_exists($file))
			 {
				 $content=file_get_contents($file);
				 $content=json_decode($content);
				 if(time()-filemtime($file)<$content->expires_in){
					return $content->access_token;
				 }
			 }
			 $curl='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->_appid.'&secret='.$this->_appsecret;
			 $content=$this->_request($curl);
			 file_put_contents($file,$content);
			 $content=json_decode($content);
			 //print_r($content);
			 return $content->access_token;
		 }
		 public function _getticket($sceneid,$type='temp',$expire_seconds=604800){//得到ticket
			 if($type=='temp')
			 {
				 $data='{"expire_seconds": %s, "action_name": "QR_STR_SCENE", "action_info":{"scene":{"scene_id": %s}}}';
				 $data=sprintf($data,$expire_seconds,$sceneid);
			 }else{
				 $data='{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_id": %s}}}';
				 $data=sprintf($data,$sceneid);
			 }
			 $curl='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$this->_getAccessToken();
			 $content=$this->_request($curl,true,'POST',$data);
			 $content=json_decode($content);
			 //var_dump($content);
			 return $content->ticket;
		 }
		public function _getQRCode($sceneid,$type='temp',$expire_seconds=60480){//用ticket得到验证码
			$ticket=$this->_getticket($sceneid,$type,$expire_seconds);
			$content=$this->_request('https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urldecode($ticket));
			return $content;
		}
	}
	$wechat=new wechat('wx9711e16f83cf38ad','04c34cfdc18186bb1c21233f69aa5793','weixin');
	//echo $wechat->_request('https://www.baidu.com');//测试request;
	//echo $wechat->_getAccessToken();//生成token
	header("Content-type:image/jpeg");
	echo $wechat->_getQRCode(30);//生成二维码
	//http://www.php.cn/course/669.html 网址
?>
