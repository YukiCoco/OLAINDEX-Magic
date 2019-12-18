@extends('default.layouts.admin')
@section('title','使用情况')
@section('content')
@foreach (getOnedriveAccounts() as $item)
<div class="bg-white">
    <p class="text-center">
        <span class="text-center text-muted">账号: {{ $item->account_email }}</span>
        <span class="text-info">状态: {{ one_info('state',$item->id) }} &nbsp;&nbsp;</span>
        <span class="text-danger">已使用: {{ one_info('used',$item->id) }} &nbsp;&nbsp;</span>
        <span class="text-warning">剩余: {{ one_info('remaining',$item->id) }} &nbsp;&nbsp;</span>
        <span class="text-success">全部: {{ one_info('total',$item->id) }} &nbsp;&nbsp;</span>
    </p>
</div>
@endforeach
@stop
