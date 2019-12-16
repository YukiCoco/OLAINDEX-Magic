@extends('default.layouts.admin')
@section('title','绑定设置')
@section('content')
    <form action="" method="post">
        @csrf
        <div class="form-group">
            <label class="form-control-label" for="email">已绑定账户</label>
            <input type="text" class="form-control" id="email" name="email"
                   value="{{ setting('account_email') }}" disabled>
        </div>
        <button type="submit" class="btn btn-primary">解绑账户</button>
        <button type="button"class="btn btn-secondary">绑定新账户</button>
    </form>
@stop
