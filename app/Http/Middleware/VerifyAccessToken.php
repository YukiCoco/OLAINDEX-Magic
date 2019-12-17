<?php

namespace App\Http\Middleware;

use App\Utils\Tool;
use App\Http\Controllers\OauthController;
use Closure;
use Session;
use App\Models\OnedriveAccount;

class VerifyAccessToken
{
    /**
     * @param         $request
     * @param Closure $next
     *
     * @return false|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|mixed|string
     * @throws \ErrorException
     */
    public function handle($request, Closure $next)
    {
        if (!Tool::hasBind()) {
            Tool::showMessage('请绑定帐号！', false);

            return redirect()->route('bind');
        }
        $onedriveAccounts = OnedriveAccount::all();
        foreach ($onedriveAccounts as $account) {
            // $expires = $account->access_token_expires;
            // $expires = strtotime($expires);
            // $hasExpired = $expires - time() <= 0;
            // if ($hasExpired) {
            //     $current = url()->current();
            //     Session::put('refresh_redirect', $current);
            //     $oauth = new OauthController();

            //     return $oauth->refreshToken(true,$account);
            // }
            refresh_token($account->id);
            refreshOnedriveAccounts();
        }
        return $next($request);
    }
}
