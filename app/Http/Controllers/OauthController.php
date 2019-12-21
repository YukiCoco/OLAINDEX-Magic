<?php

namespace App\Http\Controllers;

use App\Utils\Tool;
use App\Models\Setting;
use App\Models\OnedriveAccount;
use App\Service\Authorize;
use App\Service\OneDrive;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use ErrorException;
use Illuminate\Support\Facades\Artisan;
use Log;
use Session;

/**
 * 授权操作
 * Class OauthController
 *
 * @package App\Http\Controllers
 */
class OauthController extends Controller
{

    /**
     * OauthController constructor.
     */
    public function __construct()
    {
        //$this->middleware('verify.installation');
    }

    /**
     * @param Request $request
     *
     * @return Factory|RedirectResponse|View
     * @throws ErrorException
     */
    public function oauth(Request $request)
    {
        // 检测是否已授权
        // if (Tool::hasBind()) {
        //     return redirect()->route('home');
        // }
        if ($request->isMethod('get')) {
            if (!$request->has('code')) {
                return $this->authorizeLogin(request()->getHttpHost());
            }
            if (empty($request->get('state')) || !Session::has('state')
                || ($request->get('state') !== Session::get('state'))) {
                Tool::showMessage('Invalid state', false);
                Session::forget('state');
                return view(config('olaindex.theme') . 'message');
            }
            Session::forget('state'); // 兼容下次登录
            $code = $request->get('code');
            $token = Authorize::getInstance(session('account_type'),null,true)->getAccessToken($code);
            $token = $token->toArray();
            Log::info('access_token', $token);
            $access_token = Arr::get($token, 'access_token');
            $refresh_token = Arr::get($token, 'refresh_token');
            $expires = Arr::get($token, 'expires_in') !== 0 ? time() + Arr::get($token, 'expires_in') : 0;

            $data = [
                'access_token' => $access_token,
                'refresh_token' => $refresh_token,
                'access_token_expires' => date('Y-m-d H:i:s', $expires),
                'client_id' => session('client_id'),
                'client_secret' => session('client_secret'),
                'redirect_uri' => session('redirect_uri'),
                'account_type' => session('account_type'),
                'nick_name' => '未命名'
            ];
            //Setting::batchUpdate($data);
            //session($data);
            $account = collect([
                'account_type' => session('account_type'),
                'access_token' => $access_token,
            ]);
            OnedriveAccount::firstOrCreate($data);
            Tool::refreshAccount($account);
            Tool::showMessage('绑定成功', true);
            if(!Tool::hasConfig()){ //第一次绑定
                setSetting('has_config','true');
            }
            return redirect()->route('admin.show');
        }
        Tool::showMessage('Invalid Request', false);

        return view(config('olaindex.theme') . 'message');
    }

    /**
     * @param string $url oahth Url
     * @return RedirectResponse
     */
    public function authorizeLogin($url = ''): RedirectResponse
    {
        // 跳转授权登录
        // $state = str_random(32);
        $state = urlencode($url ? 'http://' . $url : config('app.url')); // 添加中转
        Session::put('state', $state);
        //$authorizationUrl = Authorize::getInstance(setting('account_type'))->getAuthorizeUrl($state);
        $authorizationUrl = Authorize::getInstance(session('account_type','com'))->getAuthorizeUrl($state);

        return redirect()->away($authorizationUrl);
    }

    /**
     * @param bool $redirect
     * @return false|Factory|RedirectResponse|View|string
     * @throws ErrorException
     */
    public function refreshToken($redirect = true,$account)
    {
        $token = Authorize::getInstance($account['account_type'],$account['id'])->refreshAccessToken($account['refresh_token']);
        $token = $token->toArray();
        $expires = Arr::get($token, 'expires_in') !== 0 ? time() + Arr::get($token, 'expires_in') : 0;
        $account['access_token'] = Arr::get($token, 'access_token');
        $account['refresh_token'] = Arr::get($token, 'refresh_token');
        $account['access_token_expires'] = date('Y-m-d H:i:s', $expires);
        OnedriveAccount::where('id',$account['id'])->update($account);
        Tool::refreshAccount(getOnedriveAccount($account['id']));
        if ($redirect) {
            $redirect = Session::get('refresh_redirect', '/');
            return redirect()->away($redirect);
        }
        return json_encode(['code' => 200, 'msg' => 'ok']);
    }
}
