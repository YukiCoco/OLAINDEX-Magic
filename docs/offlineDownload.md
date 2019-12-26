# 离线下载（已废除）

## Warning

由于 PHP 不能很好地持久化运行，测试过程中存在很多问题，此处废止。离线下载和上传方案建议采用 [OneDriveUploader](https://github.com/MoeClub/OneList/tree/master/OneDriveUploader), 支持国际版与世纪互联。

## 简介

**warning：测试功能，可能会出现各式问题，欢迎发issue**

OLAINDEX-Magic 与 Aria2 对接，支持下载 HTTP/FTP/SFTP/磁力链接，若下载种子文件会转换成目标文件进行下载。下载完毕后上传到 Onedrive 后将为您删除本地文件。

**下载和上传：大流量消耗警告！** 这意味着你需要一个磁盘空间比较大的vps。（根据下载的文件自己决定

## 开始配置

### 0.旧版用户更新
拉取代码后请执行一次 php artisan migrate

### 1.下载安装 aria2
推荐使用[一键安装脚本](https://github.com/P3TERX/aria2.sh)，下面的教程基于此安装脚本

将 url、端口和 token 填入后台配置中，注意：
+ URL: 应该被填写为 **127.0.0.1** ，目前还不支持非本地文件上传。
+ 端口: 默认为6800
+ token: 密钥

### 2.配置 aria2

#### （1）安装 curl
请确保机器安装了 curl ，在终端输入 curl 应该会有如下提示，若无，请查阅资料并安装 curl
```
curl: try 'curl --help' or 'curl --manual' for more information
```

#### （2）创建 success.sh
安装后进入 root/.aria2 目录，创建 success.sh 文件，填入以下内容，并修改
+ token: aria2 token
+ url: 你的网站URL
+ path: aria2 下载目录， **注意权限问题：** 若填写为 root/Download ,需要确保 root 文件夹有读写权限。这意味着你应该执行命令：chmod 777 /root
+ time: 最长上传时间，可以保持默认，不修改
```
#!/bin/sh
#修改下列内容
path=/root/Download
token=51b2b478586eb063862b
url=http://onedrive.test
time=7200 #最长上传时间
#修改到这里完成！
gid=$1
payload=${url}/admin/offlinedl/upload/${token}/$1
chmod -R 777 $path
curl $payload -m ${time}
```

#### （3）修改 aria2.conf
修改 aria2.conf 文件 on-download-complete（若无，请添加此项），去掉注释并修改为 success.sh 文件路径，修改完后重启 aria2
```
on-download-complete=/root/.aria2/success.sh
```

**至此，离线下载配置完毕。**
