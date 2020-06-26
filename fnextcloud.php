<?php
//Nextcloud相关代码Copy自ebsnextcloud
//Version 1.1
use WHMCS\Database\Capsule;

function fnextcloud_MetaData(){
    return array(
        'DisplayName' => 'FNextCloud',
        'APIVersion' => '1.1', 
        'RequiresServer' => true
    );
}

function fnextcloud_ConfigOptions(){
    return array(
        '最大配额(Gb)' => array(
            'Type' => 'text',
            'Size' => '500',
			'Default' => '10'
        )
    );
}

function fnextcloud_CreateAccount($params){
	$HashInfo = fnextcloud_gethashinfo($params['serveraccesshash']);
	if(substr($HashInfo["serveraddress"], -1) == '/'){
		$ServerAddress = substr($HashInfo["serveraddress"],0,-1);
	}else{
		$ServerAddress = $HashInfo["serveraddress"];
	}
	$ServerAddress = explode('://',$ServerAddress);
	if(count($ServerAddress) != 2){
		return '服务器地址错误,请包含协议';
	}
	$ServerAddress = $ServerAddress[0].'://'.urlencode($params['serverusername']).':'.urlencode($params['serverpassword']).'@'.$ServerAddress[1];
	if(@$params['configoptions']['maxquota']){
		$MaxQuota = $params['configoptions']['maxquota'];
	}elseif(@$params['customfields']['maxquota']){
		$MaxQuota = $params['customfields']['maxquota'];
	}elseif(@$params['configoption1']){
		$MaxQuota = trim($params['configoption1']);
	}else{
		$MaxQuota = '1';
	}
	if(empty(trim($params['username']))){
		$Username = fnextcloud_getRandomString(10);
	}else{
		$Username = $params['username'];
	}
    try{
		//Create User Start
        $CreateUserCurl = curl_init();
		curl_setopt($CreateUserCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($CreateUserCurl, CURLOPT_URL, $ServerAddress.'/ocs/v1.php/cloud/users?format=json');
		curl_setopt($CreateUserCurl, CURLOPT_POST, true);
		curl_setopt($CreateUserCurl, CURLOPT_POSTFIELDS, array('userid' => $Username,'password' => $params['password']));
		curl_setopt($CreateUserCurl, CURLOPT_HTTPHEADER, array("OCS-APIRequest: true"));
        $CreateUserResp = curl_exec($CreateUserCurl);
        curl_close($CreateUserCurl);
		$CreateUserRespArray = json_decode($CreateUserResp,true);
		if($CreateUserRespArray['ocs']['meta']['status'] != 'ok'){
			throw new Exception('创建账户失败['.$CreateUserRespArray['ocs']['meta']['message'].']');
		}
		//Create User End
		/**
        //Update Nextcloud account with user email Start
        $UpdateEmailCurl = curl_init();
		curl_setopt($UpdateEmailCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($UpdateEmailCurl, CURLOPT_URL, $ServerAddress.'/ocs/v1.php/cloud/users/'.$Username.'?format=json');
		curl_setopt($UpdateEmailCurl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($UpdateEmailCurl, CURLOPT_POSTFIELDS, http_build_query(array('key' => 'email','value' => $params['clientsdetails']['email'])));
		curl_setopt($UpdateEmailCurl, CURLOPT_HTTPHEADER, array("OCS-APIRequest: true"));
        $UpdateEmailResp = curl_exec($UpdateEmailCurl);
        curl_close($UpdateEmailCurl);
		$UpdateEmailRespArray = json_decode($UpdateEmailResp,true);
		if($UpdateEmailRespArray['ocs']['meta']['status'] != 'ok'){
			throw new Exception('设置账户邮箱失败['.$UpdateEmailRespArray['ocs']['meta']['message'].']');
		}
		//Update Nextcloud account with user email End
		**/
        //Update Nextcloud account display name with user firstname Start
        $UpdateNameCurl = curl_init();
		curl_setopt($UpdateNameCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($UpdateNameCurl, CURLOPT_URL, $ServerAddress.'/ocs/v1.php/cloud/users/'.$Username.'?format=json');
		curl_setopt($UpdateNameCurl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($UpdateNameCurl, CURLOPT_POSTFIELDS, http_build_query(array('key' => 'displayname','value' => $params['clientsdetails']['firstname']. " ".$params['clientsdetails']['lastname'])));
		curl_setopt($UpdateNameCurl, CURLOPT_HTTPHEADER, array("OCS-APIRequest: true"));
        $UpdateNameResp = curl_exec($UpdateNameCurl);
        curl_close($UpdateNameCurl);
		$UpdateNameRespArray = json_decode($UpdateNameResp,true);
		if($UpdateNameRespArray['ocs']['meta']['status'] != 'ok'){
			throw new Exception('设置账户显示名失败['.$UpdateNameRespArray['ocs']['meta']['message'].']');
		}
		//Update Nextcloud account display name with user firstname End
        //Set Nextcloud Max quota Start
        $SetMaxQuotaCurl = curl_init();
		curl_setopt($SetMaxQuotaCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($SetMaxQuotaCurl, CURLOPT_URL, $ServerAddress.'/ocs/v1.php/cloud/users/'.$Username.'?format=json');
		curl_setopt($SetMaxQuotaCurl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($SetMaxQuotaCurl, CURLOPT_POSTFIELDS, http_build_query(array('key' => 'quota','value' => $MaxQuota.'GB')));
		curl_setopt($SetMaxQuotaCurl, CURLOPT_HTTPHEADER, array("OCS-APIRequest: true"));
        $SetMaxQuotaResp = curl_exec($SetMaxQuotaCurl);
        curl_close($SetMaxQuotaCurl);
		$SetMaxQuotaRespArray = json_decode($SetMaxQuotaResp,true);
		if($SetMaxQuotaRespArray['ocs']['meta']['status'] != 'ok'){
			throw new Exception('设置账户配额失败['.$SetMaxQuotaRespArray['ocs']['meta']['message'].']');
		}
		//Set Nextcloud Max quota End
    }catch(Exception $e){
        return $e->getMessage();
    }
	Capsule::table('tblhosting')->where('id',$params['serviceid'])->update(['username' => $Username]);
    return 'success';
}
function fnextcloud_SuspendAccount($params){
	$HashInfo = fnextcloud_gethashinfo($params['serveraccesshash']);
	if(substr($HashInfo["serveraddress"], -1) == '/'){
		$ServerAddress = substr($HashInfo["serveraddress"],0,-1);
	}else{
		$ServerAddress = $HashInfo["serveraddress"];
	}
	$ServerAddress = explode('://',$ServerAddress);
	if(count($ServerAddress) != 2){
		return '服务器地址错误,请包含协议';
	}
	$ServerAddress = $ServerAddress[0].'://'.urlencode($params['serverusername']).':'.urlencode($params['serverpassword']).'@'.$ServerAddress[1];
	if(empty(trim($params['username']))){
		return '用户名不能为空';
	}else{
		$Username = $params['username'];
	}
    try{
		//Disable User Start
        $DisableUserCurl = curl_init();
		curl_setopt($DisableUserCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($DisableUserCurl, CURLOPT_URL, $ServerAddress.'/ocs/v1.php/cloud/users/'.$Username.'/disable?format=json');
		curl_setopt($DisableUserCurl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($DisableUserCurl, CURLOPT_HTTPHEADER, array("OCS-APIRequest: true"));
        $DisableUserResp = curl_exec($DisableUserCurl);		
        curl_close($DisableUserCurl);
		$DisableUserRespArray = json_decode($DisableUserResp,true);
		if($DisableUserRespArray['ocs']['meta']['status'] != 'ok'){
			throw new Exception('禁用账户失败['.$DisableUserRespArray['ocs']['meta']['message'].']');
		}
		//Disable User End
    }catch(Exception $e){
        return $e->getMessage();
    }
    return 'success';
}

function fnextcloud_UnsuspendAccount($params){
	$HashInfo = fnextcloud_gethashinfo($params['serveraccesshash']);
	if(substr($HashInfo["serveraddress"], -1) == '/'){
		$ServerAddress = substr($HashInfo["serveraddress"],0,-1);
	}else{
		$ServerAddress = $HashInfo["serveraddress"];
	}
	$ServerAddress = explode('://',$ServerAddress);
	if(count($ServerAddress) != 2){
		return '服务器地址错误,请包含协议';
	}
	$ServerAddress = $ServerAddress[0].'://'.urlencode($params['serverusername']).':'.urlencode($params['serverpassword']).'@'.$ServerAddress[1];
	if(empty(trim($params['username']))){
		return '用户名不能为空';
	}else{
		$Username = $params['username'];
	}
    try{
		//Enable User Start
        $EnableUserCurl = curl_init();
		curl_setopt($EnableUserCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($EnableUserCurl, CURLOPT_URL, $ServerAddress.'/ocs/v1.php/cloud/users/'.$Username.'/enable?format=json');
		curl_setopt($EnableUserCurl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($EnableUserCurl, CURLOPT_HTTPHEADER, array("OCS-APIRequest: true"));
        $EnableUserResp = curl_exec($EnableUserCurl);		
        curl_close($EnableUserCurl);
		$EnableUserRespArray = json_decode($EnableUserResp,true);
		if($EnableUserRespArray['ocs']['meta']['status'] != 'ok'){
			throw new Exception('启用账户失败['.$EnableUserRespArray['ocs']['meta']['message'].']');
		}
		//Enable User End
    }catch(Exception $e){
        return $e->getMessage();
    }
    return 'success';
}

function fnextcloud_TerminateAccount($params){
	$HashInfo = fnextcloud_gethashinfo($params['serveraccesshash']);
	if(substr($HashInfo["serveraddress"], -1) == '/'){
		$ServerAddress = substr($HashInfo["serveraddress"],0,-1);
	}else{
		$ServerAddress = $HashInfo["serveraddress"];
	}
	$ServerAddress = explode('://',$ServerAddress);
	if(count($ServerAddress) != 2){
		return '服务器地址错误,请包含协议';
	}
	$ServerAddress = $ServerAddress[0].'://'.urlencode($params['serverusername']).':'.urlencode($params['serverpassword']).'@'.$ServerAddress[1];
	if(empty(trim($params['username']))){
		return '用户名不能为空';
	}else{
		$Username = $params['username'];
	}
    try{
		//Delete User Start
        $DeleteUserCurl = curl_init();
		curl_setopt($DeleteUserCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($DeleteUserCurl, CURLOPT_URL, $ServerAddress.'/ocs/v1.php/cloud/users/'.$Username.'?format=json');
		curl_setopt($DeleteUserCurl, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($DeleteUserCurl, CURLOPT_HTTPHEADER, array("OCS-APIRequest: true"));
        $DeleteUserResp = curl_exec($DeleteUserCurl);	
        curl_close($DeleteUserCurl);
		$DeleteUserRespArray = json_decode($DeleteUserResp,true);
		if($DeleteUserRespArray['ocs']['meta']['status'] != 'ok'){
			throw new Exception('删除账户失败['.$DeleteUserRespArray['ocs']['meta']['message'].']');
		}
		//Delete User End
    }catch(Exception $e){
        return $e->getMessage();
    }
    return 'success';
}

function fnextcloud_ChangePassword($params){
	$HashInfo = fnextcloud_gethashinfo($params['serveraccesshash']);
	if(substr($HashInfo["serveraddress"], -1) == '/'){
		$ServerAddress = substr($HashInfo["serveraddress"],0,-1);
	}else{
		$ServerAddress = $HashInfo["serveraddress"];
	}
	$ServerAddress = explode('://',$ServerAddress);
	if(count($ServerAddress) != 2){
		return '服务器地址错误,请包含协议';
	}
	$ServerAddress = $ServerAddress[0].'://'.urlencode($params['serverusername']).':'.urlencode($params['serverpassword']).'@'.$ServerAddress[1];
	if(empty(trim($params['username']))){
		return '用户名不能为空';
	}else{
		$Username = $params['username'];
	}
    try{
		//Change User Password Start
        $ChangePasswordCurl = curl_init();
		curl_setopt($ChangePasswordCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ChangePasswordCurl, CURLOPT_URL, $ServerAddress.'/ocs/v1.php/cloud/users/'.$Username.'?format=json');
		curl_setopt($ChangePasswordCurl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ChangePasswordCurl, CURLOPT_POSTFIELDS, http_build_query(array('key' => 'password','value' => $params['password'])));
		curl_setopt($ChangePasswordCurl, CURLOPT_HTTPHEADER, array("OCS-APIRequest: true"));
        $ChangePasswordResp = curl_exec($ChangePasswordCurl);
        curl_close($ChangePasswordCurl);
		$ChangePasswordRespArray = json_decode($ChangePasswordResp,true);
		if($ChangePasswordRespArray['ocs']['meta']['status'] != 'ok'){
			throw new Exception('修改账户密码失败['.$ChangePasswordRespArray['ocs']['meta']['message'].']');
		}
		//Change User Password End
    }catch(Exception $e){
        return $e->getMessage();
    }
    return 'success';
}

function fnextcloud_AdminCustomButtonArray(){
    return array(
        "更新配额限制" => "MaxQuotaSync",
    );
}

function fnextcloud_MaxQuotaSync($params){
	$HashInfo = fnextcloud_gethashinfo($params['serveraccesshash']);
	if(substr($HashInfo["serveraddress"], -1) == '/'){
		$ServerAddress = substr($HashInfo["serveraddress"],0,-1);
	}else{
		$ServerAddress = $HashInfo["serveraddress"];
	}
	$ServerAddress = explode('://',$ServerAddress);
	if(count($ServerAddress) != 2){
		return '服务器地址错误,请包含协议';
	}
	$ServerAddress = $ServerAddress[0].'://'.urlencode($params['serverusername']).':'.urlencode($params['serverpassword']).'@'.$ServerAddress[1];
	if(empty(trim($params['username']))){
		return '用户名不能为空';
	}else{
		$Username = $params['username'];
	}
	if(@$params['configoptions']['maxquota']){
		$MaxQuota = $params['configoptions']['maxquota'];
	}elseif(@$params['customfields']['maxquota']){
		$MaxQuota = $params['customfields']['maxquota'];
	}elseif(@$params['configoption1']){
		$MaxQuota = trim($params['configoption1']);
	}else{
		$MaxQuota = '1';
	}
    try{
        //Set Nextcloud Max quota Start
        $SetMaxQuotaCurl = curl_init();
		curl_setopt($SetMaxQuotaCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($SetMaxQuotaCurl, CURLOPT_URL, $ServerAddress.'/ocs/v1.php/cloud/users/'.$Username.'?format=json');
		curl_setopt($SetMaxQuotaCurl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($SetMaxQuotaCurl, CURLOPT_POSTFIELDS, http_build_query(array('key' => 'quota','value' => $MaxQuota.'GB')));
		curl_setopt($SetMaxQuotaCurl, CURLOPT_HTTPHEADER, array("OCS-APIRequest: true"));
        $SetMaxQuotaResp = curl_exec($SetMaxQuotaCurl);
        curl_close($SetMaxQuotaCurl);
		$SetMaxQuotaRespArray = json_decode($SetMaxQuotaResp,true);
		if($SetMaxQuotaRespArray['ocs']['meta']['status'] != 'ok'){
			throw new Exception('设置账户配额失败['.$SetMaxQuotaRespArray['ocs']['meta']['message'].']');
		}
		//Set Nextcloud Max quota End
    }catch(Exception $e){
        return $e->getMessage();
    }
    return 'success';
}

function fnextcloud_ClientArea($params){
	//严格模式
	$StrictMode = true;
	$HashInfo = fnextcloud_gethashinfo($params['serveraccesshash']);
	if(substr($HashInfo["serveraddress"], -1) == '/'){
		$ServerAddressRaw = substr($HashInfo["serveraddress"],0,-1);
	}else{
		$ServerAddressRaw = $HashInfo["serveraddress"];
	}
	$ServerAddress = explode('://',$ServerAddressRaw);
	if(count($ServerAddress) != 2){
		return array('tabOverviewReplacementTemplate' => 'templates/error.tpl','templateVariables' => array('ErrorInfo' => '配置出现错误:服务器地址错误,请包含协议'));
	}
	$ServerAddress = $ServerAddress[0].'://'.urlencode($params['serverusername']).':'.urlencode($params['serverpassword']).'@'.$ServerAddress[1];
	if(empty(trim($params['username']))){
		return array('tabOverviewReplacementTemplate' => 'templates/error.tpl','templateVariables' => array('ErrorInfo' => '配置出现错误:用户名不能为空'));
	}else{
		$Username = $params['username'];
	}
	$QuotaData = array('used' => 0.00,'all' => 0.00);
    try{
        //Get Nextcloud quota Start
        $GetQuotaCurl = curl_init();
		curl_setopt($GetQuotaCurl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($GetQuotaCurl, CURLOPT_URL, $ServerAddress.'/ocs/v1.php/cloud/users/'.$Username.'?format=json');
		curl_setopt($GetQuotaCurl, CURLOPT_HTTPHEADER, array("OCS-APIRequest: true"));
        $GetQuotaResp = curl_exec($GetQuotaCurl);
        curl_close($GetQuotaCurl);
		$GetQuotaRespArray = json_decode($GetQuotaResp,true);
		if($GetQuotaRespArray['ocs']['meta']['status'] != 'ok'){
			throw new Exception('获取账户配额失败['.$GetQuotaRespArray['ocs']['meta']['message'].']');
		}
		$QuotaData['used'] = fnextcloud_convert_bytes_to_specified($GetQuotaRespArray['ocs']['data']['quota']['used'],'G',2);
		$QuotaData['all'] = fnextcloud_convert_bytes_to_specified($GetQuotaRespArray['ocs']['data']['quota']['quota'],'G',2);
		//Get Nextcloud quota End
    }catch(Exception $e){
		if($StrictMode){
			return array('tabOverviewReplacementTemplate' => 'templates/error.tpl','templateVariables' => array('ErrorInfo' => $e->getMessage()));
		}
    }
	return array(
        'tabOverviewReplacementTemplate' => 'templates/clientarea.tpl',
        'templateVariables' => array(
            'loginurl' => $ServerAddressRaw,
			//'username' => $params['username'],
            //'password' => $params['password'],
			'quota_used' => $QuotaData['used'],
            'quota_all' => $QuotaData['all']
        )
    );
}

function fnextcloud_gethashinfo($data){
    preg_match_all( '/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches );
    $result = array();
    foreach($matches[1] as $k => $v){
        $result[$v] = $matches[2][$k];
    }
	return $result;
}

function fnextcloud_getRandomString($len, $chars=null){  
    if (is_null($chars)) {  
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
    }  
    mt_srand(10000000*(double)microtime());  
    for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++) {  
        $str .= $chars[mt_rand(0, $lc)];  
    }  
    return $str;  
}

function fnextcloud_convert_bytes_to_specified($bytes, $to, $decimal_places = 1) {
    $formulas = array(
        'K' => number_format($bytes / 1024, $decimal_places),
        'M' => number_format($bytes / 1048576, $decimal_places),
        'G' => number_format($bytes / 1073741824, $decimal_places)
    );
    return isset($formulas[$to]) ? $formulas[$to] : 0;
}