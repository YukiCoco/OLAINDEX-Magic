@extends('default.layouts.admin')
@section('title','离线下载')
@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('admin.offlineDownload')}}" method="post">
            @csrf
            <div class="form-group">
                <select class="custom-select" name="client_id" required>
                    @foreach (getOnedriveAccounts() as $item)
                    <option value="{{ $item->id }}">{{ $item->nick_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group mb-3 form-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">/</span>
                  </div>
                <input type="text" class="form-control" placeholder="保存到网盘中的路径" name="path">
            </div>
            <div class="input-group mb-3 form-group">
                <input type="text" name="url" class="form-control" placeholder="下载链接（支持磁力）"
                    aria-describedby="button-addon2">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submmit" id="button-addon2">下载</button>
                </div>
            </div>
        </form>
        @foreach ($offlineDlfiles as $item)
        <div class="shadow-sm bg-light rounded border p-2 mt-3">
            <div class="row">
                <div class="col-12">
                    <span class="text-primary">{{$item['name']}}</span>
                    {{-- <a href="" class="float-right text-danger">删除任务</a> --}}
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                            style="width: {{ $item['progress']}}">
                            {{ $item['status']}} {{ $item['progress']}} {{ $item['speed'] }}/s
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@stop
