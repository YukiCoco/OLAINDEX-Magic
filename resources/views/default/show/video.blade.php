@extends('default.layouts.main')
@section('title',$file['name'])
@section('css')
    <link rel="stylesheet" href="https://cdn.staticfile.org/dplayer/1.25.0/DPlayer.min.css">
@stop
@section('js')
    <script src="https://cdn.staticfile.org/dplayer/1.25.0/DPlayer.min.js"></script>
    <script src="{{asset('js/subtitles-octopus.js')}}"></script>
    <script>
        $(function () {
            const dp = new DPlayer({
                container: document.getElementById('video-player'),
                lang: 'zh-cn',
                video: {
                    url: "{!! $file['download'] !!}",
                    pic: "{!! $file['thumb'] !!}",
                    type: 'auto'
                },
                autoplay: true
            });
            // 防止出现401 token过期
            dp.on('error', function () {
                console.log('获取资源错误，开始重新加载！');
                let last = dp.video.currentTime;
                dp.video.src = "{!! $file['download'] !!}";
                dp.video.load();
                dp.video.currentTime = last;
                dp.play();
            });

	        //字幕加载
	        Swal.fire({
		        title: "是否添加字幕",
		        showCancelButton: true,
		        confirmButtonText: '添 加',
		        cancelButtonText: '取 消'
	        }).then((result) => {
		        if (result.value) {
			        Swal.fire({
				        title: "字幕链接",
				        text: "本站字幕链接可以在文件列表上点击类似粘贴板的按钮复制",
				        input: 'text',
				        showCancelButton: true,
				        confirmButtonText: '添 加',
				        cancelButtonText: '取 消'
			        }).then((result) => {
				        if (result.value) {
					        fetch(result.value, {
						        redirect: 'follow'
					        }).then(function (response) {
						        return response.text();
					        }).then(function (text) {
						        const video = document.getElementsByTagName('video')[0];
						        window.SubtitlesOctopusOnLoad = function () {
							        const options = {
								        video: video,
								        subContent: text,
								        fonts: ["//gapis.geekzu.org/g-fonts/ea/notosanssc/v1/NotoSansSC-Regular.otf", "//gapis.geekzu.org/g-fonts/ea/notosanstc/v1/NotoSansTC-Regular.otf", "//gapis.geekzu.org/g-fonts/ea/notosansjapanese/v6/NotoSansJP-Regular.otf"],
								        workerUrl: '{{asset('js/subtitles-octopus-worker.js')}}',
								        legacyWorkerUrl: '{{asset('js/subtitles-octopus-worker-legacy.js')}}',
							        };
							        window.octopusInstance = new SubtitlesOctopus(options);
						        };
						        if (SubtitlesOctopus) {
							        SubtitlesOctopusOnLoad();
						        }
					        })
					        dp.video.load();
				        }
			        });
		        }
	        })

            // 如果是播放状态 & 没有播放完 每25分钟重载视频防止卡死
            setInterval(function () {
                if (!dp.video.paused && !dp.video.ended) {
                    console.log('开始重新加载！');
                    let last = dp.video.currentTime;
                    dp.video.src = "{!! $file['download'] !!}";
                    dp.video.load();
                    dp.video.currentTime = last;
                    dp.play();
                }
            }, 1000 * 60 * 25)
        });

    </script>

@stop
@section('content')
    @include('default.breadcrumb')
    <div class="card border-light mb-3">
        <div class="card-header">{{ $file['name'] }}</div>
        <div class="card-body">
            <div class="text-center">
                <div id="video-player"></div>
                <br>
                <div class="text-center">
                    <a href="{{ route('download',['clientId' => $clientId,'query' => \App\Utils\Tool::encodeUrl($originPath)]) }}" class="btn btn-success">
                        <i class="fa fa-download"></i>下载</a>
                </div>
                <br>
                <p class="text-danger">如无法播放或格式不受支持，推荐使用 <a href="https://pan.lanzou.com/b112173" target="_blank">potplayer</a>
                    播放器在线播放
                </p>
                <label class="control-label">下载链接</label>
                <div class="form-group">
                    <div class="input-group mb-3">
                        <input type="text" id="link1" class="form-control"
                               value="{{ route('download',['clientId' => $clientId,'query' => \App\Utils\Tool::encodeUrl($originPath)]) }}">
                        <div class="input-group-append">
                            <a href="javascript:void(0)" style="text-decoration: none" data-toggle="tooltip"
                               data-placement="right" data-clipboard-target="#link1" class="clipboard">
                                <span class="input-group-text">复制</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

