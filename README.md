
# 一键部署环境及应用

> 维护了一些项目，每次都要ssh 登录服务器，安装环境，部署项目，在申请证书，支持https,一连串下来，好累。

[dnmp](https://github.com/yeszao/dnmp.git) 配合 [部署工具 deployer](https://deployer.org/)，可以达到这一效果

 运行环境
* [dnmp](https://github.com/yeszao/dnmp.git) 
* [部署工具 deployer](https://deployer.org/)

## 快速开始

### 要求

* 服务器 可以ssh免密码登录
* 用到了80,3306端口，有应用占用的可以停掉或者在docker.env中换一个端口,
* deploy.php 和 server.php 中有必填项，换成自己的


### 1 安装基础环境

```
php vendor/bin/dep -f server.php environment:install -vvv
```


### 2 发布

发布了一个laravel demo

```
php vendor/bin/dep -f deploy.php deploy -vvv
```

### 3 生成证书

不生成证书跳过该步骤进入第四步骤
```
php vendor/bin/dep -f deploy.php docker:nginx:certbot -vvv
```
### 4 nginx配置及重启

```
php vendor/bin/dep -f deploy.php docker:nginx:conf -vvv

```

### 5 访问

访问 你配置 domain.com

### 证书定时任务开启
进到服务器

```
crontab -e
0 0 * 1/1 * docker exec nginx certbot renew
```

### 6 说明
* server.php 是 服务其环境安装
* deploy.php 是 应用安装（laravel），可以自己定义
* docker.env和docker-compose.yml 是安装环境的基础配置。.env 是laravel的配置文件
* domain.conf.tpl 是nginx 配置模版文件，可以去掉模版参数
* deploy.php 和 server.php 中有必填项，可以换成自己的

### 其他
* services/nginx 下增加了一个certbot用于存放certbot增加的证书，不使用certbot的证书话，可以将自有证书放在ssl下
* services/nginx 下的Dockerfile 增加了certbot-nginx 其他和[dnmp](https://github.com/yeszao/dnmp.git)一样
* 为了在我的nas中运行（配置很低），默认带了 vendor。 可删除掉 运行 composer install


### 问题
* docker启动不起来 可以 docker logs containerId 查看容器日志
* 应用启动不起来，可在 ～/dnmp/logs 下查看相关日志文件
* 应用启动起来了，可查看应用的日志，定位相关问题
* 更多请参考 [dnmp](https://github.com/yeszao/dnmp.git)