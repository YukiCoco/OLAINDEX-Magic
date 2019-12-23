<?php

use App\Http\Controllers\OauthController;
use App\Models\Setting;
use App\Models\OnedriveAccount;
use App\Service\OneDrive;
use App\Utils\Tool;

if (!function_exists('is_json')) {
    /**
     * 判断字符串是否是json
     *
     * @param $json
     * @return bool
     */
    function is_json($json)
    {
        json_decode($json, true);
        return (json_last_error() === JSON_ERROR_NONE);
    }
}

if (!function_exists('setting')) {
    /**
     * 获取设置或修改设置
     * @param $key
     * @param string $default
     * @return mixed
     */
    function setting($key = '', $default = '')
    {
        if($key){
            $setting = Setting::where('name',$key)->first();
            if($setting){
                return $setting->value;
            } else{
                return $default;
            }
        }
        try {
            $setting = Setting::all()->toArray();
        } catch (Exception $e) {
            return [];
        }
        $data = [];
        foreach ($setting as $detail) {
            $data[$detail['name']] = $detail['value'];
        }
        if(!array_key_exists($key,$data)){
            setSetting($key,'');
            $data[$key] = '';
        }
        $setting = collect($data);
        return $setting?:$default;
    }
}

if(!function_exists('setSetting')){

    function setSetting($key,$value){
        $setting = Setting::where('name',$key)->first();
        if($setting){
            $setting->value = $value;
        } else{
            $setting = new Setting;
            $setting->name = $key;
            $setting->value = $value;
        }
        $setting->save();
    }
}

if (!function_exists('one_account')) {

    /**
     * 获取绑定OneDrive用户信息
     *
     * @param string $key
     * @return \Illuminate\Support\Collection|mixed
     */
    function one_account($key = '')
    {
        $account = collect([
            //从缓存中取出
            'account_type' => setting('account_type'),
            'access_token' => setting('access_token'),
            'account_email' => setting('account_email'),
        ]);
        return $key ? $account->get($key, '') : $account->toArray();
    }
}

if(!function_exists('getOnedriveAccount')){
    /**
     * @description: 获取 Onedrive 账户
     * @param int $index 数据库中id
     * @param string $key
     * @return: array
     */
    function getOnedriveAccount($id = 1,$key = ''){
        $account = OnedriveAccount::where('id',$id)->first();
        return $key ? $account->get($key, '') : $account->toArray();
    }
}

if(!function_exists('getOnedriveAccounts')){

    /**
     * @description: 获取所有 Onedrive 账户
     * @param {type}
     * @return:
     */
    function getOnedriveAccounts(){
        $accounts = OnedriveAccount::all();
        return $accounts;
    }
}

// if(!function_exists('refreshOnedriveAccounts')){
//     /**
//      * @description: 从缓存中刷新所有onedrive账户
//      * @param {type}
//      * @return:
//      */
//     function refreshOnedriveAccounts(){
//         \Cache::forget('onedrive_accounts');
//         \Cache::put('onedrive_accounts', OnedriveAccount::all(), 60*60);
//     }
// }

if (!function_exists('one_info')) {

    /**
     * 获取绑定OneDrive信息
     * @param string $key
     * @return array|\Illuminate\Support\Collection|mixed
     * @throws ErrorException
     */
    function one_info($key = '',$clientId)
    {
        //return [];
        $quota = Cache::remember(
            'one:'. $clientId .':quota',
            setting('expires'),
            static function ()use($clientId) {
                $response = OneDrive::getInstance(getOnedriveAccount($clientId))->getDriveInfo();
                if ($response['errno'] === 0) {
                    $quota = $response['data']['quota'];
                    foreach ($quota as $k => $item) {
                        if (!is_string($item)) {
                            $quota[$k] = Tool::convertSize($item);
                        }
                    }
                    return $quota;
                }
                return [];
            }
        );
        $info = collect($quota);
        return $key ? $info->get($key, '') : $info;
    }
}

if (!function_exists('refresh_token')) {

    /**
     * 刷新token
     * @return bool
     * @throws ErrorException
     */
    function refresh_token($account)
    {
        $id = $account['id'];
        $expires = $account['access_token_expires'];
        $expires = strtotime($expires);
        $hasExpired = $expires - time() <= 0;
        if ($hasExpired) {
            Log::debug('Token过期，重新获取');
            $oauth = new OauthController();
            $res = json_decode($oauth->refreshToken(false,getOnedriveAccount($id)), true);
            return $res['code'] === 200;
        }
        return true;
    }
}

