<?php

//星外对接
//作责：地狱筱雨
//邮箱：2031464675@qq.com
//赞助：地狱云主机idc.netech.cc

function XiaoYu_XingWaiVPS_GETSESSION($params){
  	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $params['url']); 
	// 获取头部信息 
	curl_setopt($ch, CURLOPT_HEADER, 1); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params['data']));
	$content = curl_exec($ch);  
	curl_close($ch); 
	// 解析http数据流 
	list($header, $body) = explode("\r\n\r\n", $content); 
	// 解析cookie
	preg_match("/set\-cookie:([^\r\n]*)/i", $header, $matches); 
	$cookie = explode(';', $matches[1]);
	$cookies = $cookie[0];
  	return $cookies;
}

function XiaoYu_XingWaiVPS_POSTDATA($params){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $params['url']);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HEADER,1);
  	curl_setopt($ch, CURLOPT_TIMEOUT,600);   //只需要设置一个秒的数量就可以   
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_COOKIE,$params['cookie']);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params['data']));
	$content = curl_exec($ch);
	curl_close($ch);
	return iconv('GB2312', 'UTF-8', $content);
}

function XiaoYu_XingWaiVPS_GETDATA($params){
  	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $params['url']."?".http_build_query($params['data']));
	curl_setopt($ch, CURLOPT_HEADER,1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_COOKIE,$params['cookie']);
  	$content = curl_exec($ch);
  	curl_close($ch);
  	return iconv('GB2312', 'UTF-8', $content);
}

function XiaoYu_XingWaiVPS_ConfigOption(){
	return array(
		'serverlistid' => '机房编号',
		'productid' => '产品编号',
	);
}

function XiaoYu_XingWaiVPS_LoginService($params){
	return '<form action="'.$params['server']['servercpanel'].'vpsadm/login.asp" method="POST"><input type="hidden" value="'.$params['service']['username'].'" name="vpsname"><input type="hidden" value="'.$params['service']['password'].'" name="VPSpassword"><button type="submit">点击登陆</button></form>';
}

function XiaoYu_XingWaiVPS_CreateService($params){
	if($params['server']['serverport'] == "80"){
		$http = "http://";
	}else{
		$http = "https://";
	}
	$cookie = XiaoYu_XingWaiVPS_GETSESSION(array('url' => $http.$params['server']['serverip']."/user/userlogin.asp", 'data' => array('username' => $params['server']['serverusername'], 'password'=>$params['server']['serverpassword'])));
	
	if(empty($cookie)){
		return array(
			'status' => 'fail',
			'msg' => 'Cookie获取失败',
		);
	}
	$configoption = json_decode($params['product']['configoption'],true);
	$XiaoYu_XingWaiVPS_POSTDATA = array(
		'url' => $http . $params['server']['serverip'] . "/user/selfvps2.asp",
		'cookie' => $cookie,
		'data' => array(
			'vpsname' => $params['service']['username'],
			'vpspassword' => $params['service']['password'],
			'year' => $params['service']['time']/10,
			'id' => $configoption['productid'],
			'ServerlistID' => $configoption['serverlistid'],
		),
	);
	$content = XiaoYu_XingWaiVPS_POSTDATA($XiaoYu_XingWaiVPS_POSTDATA);
	if(strstr($content, "云服务器开通成功")){
		@preg_match('/id=(.*)&/iU', $content, $itemid);
		return array(
			'status' => 'success',
			'username' => $params['service']['username'],
			'password' => $params['service']['password'],
			'enddate' => date('Y-m-d',strtotime("+{$params['time']} months", time())),
			'configoption' => $itemid[1],
			'msg' => $params['service']['username'],
		);
	}else{
		@preg_match('/e=(.*)\\n/iU', $content, $errmsg);
		return array(
			'status' => 'fail',
			'msg' => $errmsg[1],
		);
	}
}

function XiaoYu_XingWaiVPS_RenewService($params){
	if($params['server']['serverport'] == "80"){
		$http = "http://";
	}else{
		$http = "https://";
	}
	$cookie = XiaoYu_XingWaiVPS_GETSESSION(array('url' => $http . $params['server']['serverip']."/user/userlogin.asp", 'data' => array('username' => $params['server']['serverusername'], 'password' => $params['server']['serverpassword'])));
	if(empty($cookie)){
		return array(
			'status' => 'fail',
			'msg' => 'Cookie获取失败',
		);
	}
  	$XiaoYu_XingWaiVPS_GETDATA = array(
      	'url' => $http . $params['server']['serverip'] . "/user/vpsadm2.asp",
      	'cookie' => $cookie,
      	'data' => array(
          	'id' => $params['service']['configoption'],
          	'go' => 'c',
        ),
    );
  	XiaoYu_XingWaiVPS_GETDATA($XiaoYu_XingWaiVPS_GETDATA);
	$year = $params['data']['time']/10;
	$XiaoYu_XingWaiVPS_POSTDATA = array(
		'url' => $http.$params['server']['serverip'] . "/user/vpsadmrepay2.asp",
		'cookie' => $cookie,
		'data' => array(
			'id' => $params['service']['configoption'],
			'year' => $year,
		),
	);
	$content = XiaoYu_XingWaiVPS_POSTDATA($XiaoYu_XingWaiVPS_POSTDATA);
	if(empty($content)){
		return array(
			'status' => 'fail',
			'msg' => 'server return empty',
		);
	}
	if(strstr($content, '服务器延期成功')){
		return array(
			'status' => 'success',
			'enddate' => date('Y-m-d', strtotime("+{$params['data']['time']} months", strtotime($params['service']['enddate']))),
		);
	}else{
		@preg_match('/e=(.*)\\n/iU', $content, $errmsg);
        return array(
        	'status' => 'fail',
        	'msg' => $errmsg[1],
        );
	}/*
  return array(
  	'status' => 'success',
  	'enddate' => date('Y-m-d', strtotime("+{$params['data']['time']} months", strtotime($params['service']['enddate']))),
  );*/
}


?>
