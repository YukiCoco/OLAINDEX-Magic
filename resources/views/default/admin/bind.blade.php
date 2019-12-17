@extends('default.layouts.admin')
@section('title','绑定设置')
@section('js')
<script>
function deleteItem(){
    $("input[name='type']").prop('value','delete'); //更改类型为解除绑定
}
</script>
@stop
@section('content')
@foreach (getOnedriveAccounts() as $item)
<form action="" method="POST">
    @csrf
    <input type="hidden" name="type" value="update">
    <input type="hidden" name="id" value="{{ $item->id }}">
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text">{{ $item->account_email }}</span>
        </div>
        <input type="text" name="nick_name" class="form-control" placeholder="显示名称" aria-label="Nick Name" aria-describedby="basic-addon1" value="{{ $item->nick_name }}">
        <div class="input-group-append">
            <button class="btn btn-secondary" type="subbmit">更新</button>
            <button class="btn btn-danger" type="subbmit" onclick="deleteItem()">解除绑定</button>
        </div>
    </div>
</form>
@endforeach
<a href="{{ route('admin.bind.newbind')}}" class="btn btn-primary" role="button">绑定新账户</a>
@stop
