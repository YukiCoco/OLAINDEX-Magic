@extends('default.layouts.admin')
@section('title','绑定新Onedrive')
@section('content')
<div class="border-light mb-3">
        <form action="{{ route('admin.bind.createNewbind')}}" method="post">
            @csrf
            <div class="form-group">
                <label class="form-control-label" for="redirect_uri">redirect_uri </label>
                <input type="text" class="form-control" id="redirect_uri" name="redirect_uri"
                       value="{{ trim(config('app.url'),'/').'/oauth' }}">
                <span class="form-text text-danger">确保回调地址格式为此形式 http(s)://you.domain/oauth，使用中转域名无需https协议（注意：如果通过CDN开启HTTPS而非配置SSL证书，部分回调CDN会跳转http地址，从而导致申请失败） </span>
            </div>
            <div class="form-group">
                <label class="form-control-label" for="client_id"><b>client_id</b></label>
                <input type="text" class="form-control" id="client_id" name="client_id">
            </div>
            <div class="form-group">
                <label class="form-control-label" for="client_secret"><b>client_secret</b></label>
                <input type="text" class="form-control" id="client_secret" name="client_secret">
            </div>
            <div class="form-group">
                <label class="form-control-label" for="account_type">账户类型</label>
                <select class="custom-select" name="account_type" id="account_type">
                    <option value="">选择账户类型</option>
                    <option value="com" selected>国际版</option>
                    <option value="cn">国内版（世纪互联）</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">绑定</button>
        </form>
</div>
@stop
