@extends('default.layouts.main')
@section('title',setting('name','OLAINDEX'))
@section('css')
<link rel="stylesheet" href="{{asset('css/mod.css')}}">
@stop
@section('js')
    <script src="https://cdn.staticfile.org/marked/0.6.2/marked.min.js"></script>
    <script>
        $(function () {
            @if (!blank($head))
            document.getElementById('head').innerHTML = marked(`{!! $head !!}`);
            @endif
            @if (!blank($readme))
            document.getElementById('readme').innerHTML = marked(`{!! $readme !!}`);
            @endif
            $('.view').popover({
                trigger: 'hover',
                html: true,
            });
        });

        function getDirect() {
            $("#dl").val('');
            $(".download_url").each(function () {
                let dl = decodeURI($(this).attr("href"));
                let url = dl + "\n";
                let origin = $("#dl").val();
                $("#dl").val(origin + url);
            });
        }
        function getAllDLLinks() {
            var links = "";
            @foreach($items as $item)
            @if(! \Illuminate\Support\Arr::has($item,'folder'))
            links += "{{ route('download', ['clientId' => $clientId,'query' => \App\Utils\Tool::encodeUrl($originPath ? $originPath.'/'.$item['name'] : $item['name'])]) }}" +'\r\n';
            @endif
            @endforeach
            copyText(links, function () {
                Swal({
                    type: "success",
                    text: "已复制到剪贴板！"
                })
             })
        }
        // 复制的方法
        function copyText(text, callback){ // text: 要复制的内容， callback: 回调
        var tag = document.createElement('textarea');
        tag.setAttribute('id', 'cp_hgz_input');
        tag.value = text;
        document.getElementsByTagName('body')[0].appendChild(tag);
        document.getElementById('cp_hgz_input').select();
        document.execCommand('copy');
        document.getElementById('cp_hgz_input').remove();
        if(callback) {callback(text)}
        }

        // 点击按钮调用复制
        document.getElementById('btn').onclick = function (){
        copyText( '123456', function (){console.log('复制成功')})
        }
        @auth
        function deleteItem(sign,fileName) {
            swal({
                showLoaderOnConfirm : true,
                title: '确定删除吗？',
                text: "删除后无法恢复",
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: '确定删除',
                cancelButtonText: '取消',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    var data = {
                        '_token' : '{{csrf_token()}}',
                        'clientId' : '{{ $clientId }}',
                        'sign' : sign
                    }
                    $.post("{{ route('delete') }}", data,
                        function (data, textStatus, jqXHR) {
                            console.log(data);
                            if(data.errno == 0){
                                swal({
                                    title: '删除成功',
                                    type: 'success'
                                });
                                $("a[title='" + fileName + "']").parent().parent().parent().remove();
                            } else{
                                swal({
                                    title: '删除失败',
                                    type: 'error',
                                    text: data.msg
                                });
                            }
                        },
                        "json"
                    );
                } else if (result.dismiss === swal.DismissReason.cancel) {
                    swal('已取消', '文件安全 :)', 'error');
                }
            })
        }
        @endauth
    </script>
@stop
@section('content')
    @include('default.breadcrumb')

    @if (!blank($head))
        <div class="card border-light mb-3">
            <div class="card-header"><i class="fa fa-leaf"></i> HEAD</div>
            <div class="card-body markdown-body" id="head">
            </div>
        </div>
    @endif
    <div class="card border-light mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-8 col-sm-6">
                    文件&nbsp;
                    @if(\App\Utils\Tool::getOrderByStatus('name'))
                        <a href="?orderBy=name,asc"><i class="fa fa-arrow-down"></i></a>
                    @else
                        <a href="?orderBy=name,desc"><i class="fa fa-arrow-up"></i></a>
                    @endif
                </div>
                <div class="col-sm-2 d-none d-md-block d-md-none">
                    <span class="pull-right">
                        修改日期&nbsp;
                        @if(\App\Utils\Tool::getOrderByStatus('lastModifiedDateTime'))
                            <a href="?orderBy=lastModifiedDateTime,asc"><i class="fa fa-arrow-down"></i></a>
                        @else
                            <a href="?orderBy=lastModifiedDateTime,desc"><i class="fa fa-arrow-up"></i></a>
                        @endif
                    </span>
                </div>
                <div class="col-sm-2 d-none d-md-block d-md-none">
                    <span class="pull-right">
                        大小&nbsp;
                        @if(\App\Utils\Tool::getOrderByStatus('size'))
                            <a href="?orderBy=size,asc"><i class="fa fa-arrow-down"></i></a>
                        @else
                            <a href="?orderBy=size,desc"><i class="fa fa-arrow-up"></i></a>
                        @endif
                    </span>
                </div>
                    <div class="col-4 col-sm-2">
                    <a class="pull-right dropdown-toggle btn btn-sm btn-primary" href="#" id="actionDropdownLink" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">操作</a>
                    <div class="dropdown-menu" aria-labelledby="actionDropdownLink">
                        @if(auth()->user())
                        @if (array_key_exists('README.md', $originItems))
                        <a class="dropdown-item" href="{{ route('admin.file.update',['clientId' => $clientId,'id' => $originItems['README.md']['id']]) }}"><i
                                class="fa fa-pencil-square-o"></i> 编辑 README</a>
                        @else
                        <a class="dropdown-item"
                            href="{{ route('admin.file.create',['clientId' => $clientId,'name' => 'README', 'path' => encrypt($originPath)]) }}"><i
                                class="fa fa-plus-circle"></i> 添加 README</a>
                        @endif
                        @if (array_key_exists('HEAD.md', $originItems))
                        <a class="dropdown-item" href="{{ route('admin.file.update',['clientId' => $clientId,'id' => $originItems['HEAD.md']['id']]) }}"><i
                                class="fa fa-pencil-square-o"></i> 编辑 HEAD</a>
                        @else
                        <a class="dropdown-item"
                            href="{{ route('admin.file.create',['clientId' => $clientId, 'name' => 'HEAD', 'path' => encrypt($originPath)]) }}"><i
                                class="fa fa-plus-circle"></i> 添加 HEAD</a>
                        @endif
                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#newFolderModal"><i
                                class="fa fa-plus-circle"></i> 新建目录</a>
                        @endif
                        @if (setting('export_download'))
                        <a class="dropdown-item" href="javascript:void(0)" data-toggle="modal" data-target="#directLinkModal"><i
                            class="fa fa-link"></i> 导出直链</a>
                        @endif
                        <a class="dropdown-item" href="javascript:void(0);" onclick="getAllDLLinks()"><i class="fa fa-clipboard"></i> 复制所有下载链接</a>
                    </div>
                    <div class="modal fade" id="newFolderModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form action="{{ route('admin.folder.create') }}" method="post">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="fa fa-plus-circle"></i> 新建目录</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="text-danger">请确保目录名的唯一性，如果存在相同名称，服务器会自动选择新的名称。</p>
                                        <p class="text-danger">文件夹名不能以点开始或结束，且不能包含以下任意字符: " * : <>? / \ |。</p>
                                        <div class="form-group">
                                            <input type="text" name="name" class="form-control" placeholder="请输入目录名" required>
                                            <input type="hidden" name="path" value="{{ encrypt($originPath) }}">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">确定</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal fade" id="directLinkModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="fa fa-link"></i> 导出直链</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p class="text-danger">
                                        链接将在 {{ setting('access_token_expires') }}
                                        后失效</p>
                                    <p><a href="javascript:void(0)" style="text-decoration: none" data-toggle="tooltip"
                                            data-placement="right" data-clipboard-target="#dl" class="clipboard">点击复制</a></p>
                                    <label for="dl"><textarea name="urls" id="dl" class="form-control" cols="60"
                                            rows="15"></textarea></label>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" onclick="getDirect()" class="btn btn-primary">点击获取
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">取消
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="list-group item-list">
            @if(!blank($pathArray))
                <li class="list-group-item list-group-item-action">
                    <div class="row">
                        <div class="col-8 col-sm-6">
                                <a href="{{ route('home',['clientId' => $clientId,'query' => \App\Utils\Tool::encodeUrl(\App\Utils\Tool::getParentUrl($pathArray))]) }}"><i
                                    class="fa fa-level-up"></i> 返回上一层</a>
                        </div>
                    </div>
                </li>
            @endif
            @foreach($items as $item)
                <li class="list-group-item list-group-item-action">
                    <div class="row">
                        <div class="col-8 col-sm-6" style="text-overflow:ellipsis;overflow:hidden;white-space:nowrap;">
                            @if( \Illuminate\Support\Arr::has($item,'folder'))
                                <a href="{{ route('home',['clientId' => $clientId,'query' => \App\Utils\Tool::encodeUrl($originPath ? $originPath.'/'.$item['name'] : $item['name'])]) }}"
                                   title="{{ $item['name'] }}">
                                    <i class="fa fa-folder"></i> {{ $item['name'] }}
                                </a>
                            @else
                                <a href="{{ route('show',['clientId' => $clientId,'query' => \App\Utils\Tool::encodeUrl($originPath ? $originPath.'/'.$item['name'] : $item['name'])]) }}"
                                   title="{{ $item['name'] }}">
                                    <i class="fa {{ \App\Utils\Tool::getExtIcon($item['ext'] ?? '') }}"></i> {{ $item['name'] }}
                                </a>
                            @endif
                        </div>
                        <div class="col-sm-2 d-none d-md-block d-md-none">
                            <span
                                class="pull-right">{{ date('M d H:i',strtotime($item['lastModifiedDateTime'])) }}</span>
                        </div>
                        <div class="col-sm-2 d-none d-md-block d-md-none">
                            <span
                                class="pull-right">{{ \Illuminate\Support\Arr::has($item,'folder')? '-' : \App\Utils\Tool::convertSize($item['size']) }}</span>
                        </div>
                        <div class="col-4 col-sm-2">
                            <span class="pull-right">
                                @if(! \Illuminate\Support\Arr::has($item,'folder'))
                                    @if( \Illuminate\Support\Arr::has($item,'image'))
                                        <a href="{{ route('view',['clientId' => $clientId,'query' => \App\Utils\Tool::encodeUrl($originPath ? $originPath.'/'.$item['name'] : $item['name'])]) }}"
                                           data-fancybox="image-list" data-caption="{{ $item['name'] }}"
                                           data-toggle="popover" data-placement="bottom"
                                           data-content="<img src='{{  \Illuminate\Support\Arr::get($item,'thumbnails.0.small.url','') }}' alt='{{ $item['name'] }}' class='img-fluid'>"
                                           class="view"><i
                                                class="fa fa-eye"></i></a>&nbsp;&nbsp;
                                    @endif
                                    @if(Auth::user() && \App\Utils\Tool::canEdit($item) )
                                        <a href="{{ route('admin.file.update',['clientId' => $clientId,'id' => $item['id']]) }}"><i
                                                class="fa fa-pencil"></i></a>&nbsp;&nbsp;
                                    @endif
                                    <a class="download_url"
                                       href="{{ route('download',['clientId' => $clientId,'query' => \App\Utils\Tool::encodeUrl($originPath ? $originPath.'/'.$item['name'] : $item['name'])]) }}"><i
                                            class="fa fa-download"
                                            title="下载"></i></a>&nbsp;&nbsp;
                                @else
                                    <a href="{{ route('home',['clientId' => $clientId,'query' => \App\Utils\Tool::encodeUrl($originPath ? $originPath.'/'.$item['name'] : $item['name'])]) }}"
                                       title="{{ $item['name'] }}"><i class="fa fa-folder-open"></i></a>&nbsp;&nbsp;
                                @endif
                                @auth
                                    <a onclick="deleteItem('{{ encrypt($item['id'] . '.' . encrypt($item['eTag'])) }}','{{ $item['name'] }}')"
                                       href="javascript:void(0)"><i class="fa fa-trash"
                                                                    title="删除"></i></a>&nbsp;
                                    &nbsp;
                                @endauth
                            </span>
                        </div>
                    </div>
                </li>
            @endforeach
            <li class="list-group-item list-group-item-action border-0">
                <div class="row">
                    <div class="col-8 col-sm-6" style="text-overflow:ellipsis;overflow:hidden;white-space:nowrap;">
                            <span class="text-muted font-weight-light">
                                共 {{ count($originItems) }} 个项目
                                @auth
                                    {{ \App\Utils\Tool::convertSize($size) }}
                                @endauth
                            </span>
                    </div>
                </div>
            </li>
        </div>
    </div>
    <div>
        {{ $items->appends(['limit' => request()->get('limit'),'orderBy'=> request()->get('orderBy')])->links('default.page') }}
    </div>
    @if (!blank($readme))
        <div class="card border-light mb-3">
            <div class="card-header"><i class="fa fa-bookmark"></i> README</div>
            <div class="card-body markdown-body" id="readme">
            </div>
        </div>
    @endif
@stop

