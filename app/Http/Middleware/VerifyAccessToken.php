<?php
/*
 * @Author: your name
 * @Date: 2019-12-19 00:03:57
 * @LastEditTime : 2019-12-20 17:39:57
 * @LastEditors  : Please set LastEditors
 * @Description: In User Settings Edit
 * @FilePath: /onedrive/app/Http/Middleware/VerifyAccessToken.php
 */
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
            refresh_token($account->toArray());
        }
        return $next($request);
    }
}
