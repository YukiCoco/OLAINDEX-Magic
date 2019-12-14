@extends('mdui.layouts.main')
@section('css')
<link rel="stylesheet" href="{{ asset('css/filepond.min.css')}}">
<link rel="stylesheet" href="{{ asset('css/filepond-plugin-image-preview.min.css')}}">
<link rel="stylesheet" href="https://cdn.staticfile.org/limonte-sweetalert2/7.33.1/sweetalert2.min.css">
    <style>

        .link-container {
            border: solid 1px #dadada;
            word-wrap: break-word;
            background-color: #f7f7f7;
        }

        .link-container p {
            margin: 5px 0;
        }
    </style>
@stop
@section('js')
<script src="{{ asset('js/filepond.min.js')}}"></script>
<script
    src="{{ asset('js/filepond-plugin-image-preview.min.js')}}"></script>
<script
    src="{{asset('js/filepond-plugin-file-validate-size.min.js')}}"></script>
<script
    src="{{ asset('js/filepond-plugin-file-validate-type.min.js')}}"></script>
    <script>
        FilePond.registerPlugin(
            FilePondPluginImagePreview,
            FilePondPluginFileValidateSize,
            FilePondPluginFileValidateType
        );
        FilePond.setOptions({
            dropOnPage: true,
            dropOnElement: true,
            dropValidation: true,
            server: {
                url: Config.routes.upload_image,
                process: {
                    url: '/',
                    method: 'POST',
                    withCredentials: false,
                    headers: {},
                    timeout: 20000,
                    onload: (response) => {
                        let res = JSON.parse(response);
                        console.log(res);
                        if (res.errno === 200) {
                            $('#showUrl').removeClass('invisible');
                            $('#urlCode').prepend($('<p>' + res.data.url + '</p>').attr('data-section-type','urlCode'));
                            //$('<a></a>').attr('href','#').attr('onclick','').attr('data-section-urlCode','').text('[点击复制]');
                            $('#htmlCode').prepend($('<p>&lt;img src=\'' + res.data.url + '\' alt=\'' + res.data.filename + '\' title=\'' + res.data.filename + '\' /&gt;' + '</p>').attr('data-section-type','htmlCode'));
                            $('#bbCode').prepend($('<p>[img]' + res.data.url + '[/img]' + '</p>').attr('data-section-type','bbCode'));
                            $('#markdown').prepend($('<p>![' + res.data.filename + '](' + res.data.url + ')' + '</p>').attr('data-section-type','markdown'));
                            $('#markdownLinks').prepend($('<p>[![' + res.data.filename + '](' + res.data.url + ')]' + '(' + res.data.url + ')' + '</p>').attr('data-section-type','markdownLinks'));
                            $('#deleteCode').prepend($('<p>' + res.data.delete + '</p>').attr('data-section-type','deleteCode'));
                        }
                        return response.key
                    },
                    onerror: (response) => response.data,
                    ondata: (formData) => {
                        formData.append('_token', Config._token);
                        return formData;
                    }
                },
                revert: null,
                restore: null,
                load: null,
                fetch: null
            },
        });
        const pond = FilePond.create(document.querySelector('input[name=olaindex_img]'), {
            acceptedFileTypes: ['image/*'],
        });
        pond.on('processfile', (error, file) => {
            if (error) {
                Swal({
                    type: "error",
                    title: "出现错误",
                    text: error.message
                })
                return;
            }
            console.log('文件已上传', file);
        });
        pond.on('removefile', (file) => {
            console.log('文件已删除', file);
        });

        function onCopyBtnClicked(type) {
            var eles = $("[data-section-type$='" + type + "']");
            var text = "";
            if(eles.length > 1){
                for (let index = 0; index < eles.length; index++) {
                    const element = eles[index];
                    text += $(element).text() + "\r\n";
                }
            } else{
                text += eles.text();
            }
            copyText(text,function () {
                Swal({
                    type: "success",
                    text: "已复制到剪贴板！"
                })
             });
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
    </script>
@stop
@section('content')
    <div class="mdui-container-fluid mdui-p-a-2">
        <div class="mdui-row mdui-m-t-3">
            <div class="mdui-typo-headline-opacity">图床</div>
            <br>
            <div class="mdui-typo-title-opacity">您可以尝试文件拖拽或者点击虚线框进行文件上传，单张图片最大支持4MB.</div>
            <br>
            <input type="file" class="filepond" name="olaindex_img" multiple data-max-file-size="4MB"
                   data-max-files="5" data-instant-upload="false"/>
        </div>
        <div id="showUrl" class="mdui-hidden">
            <div class="mdui-tab" mdui-tab>
                <a class="mdui-ripple" href="#urlCode">URL</a>
                <a class="mdui-ripple" href="#htmlCode">HTML</a>
                <a class="mdui-ripple" href="#bbCode">BBCODE</a>
                <a class="mdui-ripple" href="#markdown">MD</a>
                <a class="mdui-ripple" href="#markdownLinks">MD LINK</a>
                <a class="mdui-ripple" href="#deleteCode">DEL Link</a>
            </div>
            <div class="mdui-p-a-2 mdui-m-t-2 link-container" id="urlCode">
                <a onclick="onCopyBtnClicked('urlCode')" href="#">[复制内容]</a>
            </div>
            <div class="mdui-p-a-2 mdui-m-t-2 link-container" id="htmlCode">
                <a onclick="onCopyBtnClicked('htmlCode')" href="#">[复制内容]</a>
            </div>
            <div class="mdui-p-a-2 mdui-m-t-2 link-container" id="bbCode">
                <a onclick="onCopyBtnClicked('bbCode')" href="#">[复制内容]</a>
            </div>
            <div class="mdui-p-a-2 mdui-m-t-2 link-container" id="markdown">
                <a onclick="onCopyBtnClicked('markdown')" href="#">[复制内容]</a>
            </div>
            <div class="mdui-p-a-2 mdui-m-t-2 link-container" id="markdownLinks">
                <a onclick="onCopyBtnClicked('markdownLinks')" href="#">[复制内容]</a>
            </div>
            <div class="mdui-p-a-2 mdui-m-t-2 link-container" id="deleteCode">
                <a onclick="onCopyBtnClicked('deleteCode')" href="#">[复制内容]</a>
            </div>
        </div>
    </div>
@stop
