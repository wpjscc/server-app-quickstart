<?php
namespace Deployer;

require 'recipe/common.php';

/** 必填项 */
set('docker_version', '20.10.6');//一般不用动
set('docker_compose_version', '1.29.1');//一般不用动
set('server_ip','121.40.65.228');//换成自己的
set('server_user','root');//换成自己的
set('identityFile','~/.ssh/id_rsa');//换成自己的
/** 必填项 */



// Hosts
host(get('server_ip'))->user(get('server_user'))->identityFile(get('identityFile'));  
   
set('dnmp_path', '~/dnmp');


task('install:git',function(){
    if(!test('[ -x "$(command -v git)" ]')){
        run('sudo apt update && apt -y install git');
        // run('curl -sSL https://get.daocloud.io/docker | sh');
    }

});
task('install:docker',function(){
    if(!test('[ -x "$(command -v docker)" ]')){
        run('curl -fsSL https://get.docker.com | bash -s docker --mirror Aliyun');
        // run('curl -sSL https://get.daocloud.io/docker | sh');
    }

});
task('install:docker-compose',function(){
    if(!test('[ -x "$(command -v docker-compose)" ]')){
        run('curl -L https://get.daocloud.io/docker/compose/releases/download/{{docker_compose_version}}/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose && chmod +x /usr/local/bin/docker-compose');
    }
});



task('upload:dnmp',function(){
    if (!test("[ -d {{dnmp_path}} ]")) {
        upload('./dnmp/','{{dnmp_path}}');
    }
});
task('upload:docker-env',function(){
    upload('./docker.env','{{dnmp_path}}/.env');
});
task('upload:docker-compose',function(){
    upload('./docker-compose.yml','{{dnmp_path}}/docker-compose.yml');
});
task('docker:compose:up',function(){
    run('cd {{dnmp_path}} && docker-compose up -d');
});
task('docker:compose:restart',function(){
    run('cd {{dnmp_path}} && docker-compose restart');
});

task('docker:nginx:build',function(){
    run('cd {{dnmp_path}} && docker-compose build nginx');
});


## 环境安装 后访问404 说明安装成功
task('environment:install',[
    'install:git',
    'install:docker',
    'install:docker-compose',
    'upload:dnmp',
    'upload:docker-env',
    'upload:docker-compose',
    'docker:compose:up',
]);