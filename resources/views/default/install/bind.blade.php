@extends('default.layouts.common')
@section('title','绑定帐号')
@section('content')
<div class="border-light mb-3">
    <form action="{{ route('bind')}}" method="post">
        @csrf
        <div class="form-group">
            <label class="form-control-label" for="redirect_uri">redirect_uri </label>
            <input type="text" class="form-control" id="redirect_uri" name="redirect_uri"
                   value="{{ session('redirect_uri')}}">
        </div>
        <div class="form-group">
            <label class="form-control-label" for="client_id"><b>client_id</b></label>
            <input type="text" class="form-control" id="client_id" name="client_id" value="{{ session('client_id')}}">
        </div>
        <div class="form-group">
            <label class="form-control-label" for="client_secret"><b>client_secret</b></label>
            <input type="text" class="form-control" id="client_secret" name="client_secret" value="{{ session('client_secret')}}">
        </div>
        <div class="form-group">
            <label class="form-control-label" for="account_type">账户类型</label>
            <input type="text" class="form-control" id="account_type" name="account_type" value="{{ session('account_type')}}">
        </div>
        <button type="submit" class="btn btn-primary">绑定</button>
        <a href="{{ route('reset') }}" class="btn btn-danger">返回更改</a>
    </form>
</div>
@stop
