<?php
namespace Deployer;

require 'recipe/laravel.php';

/** 必填项 */
set('domain','laravel.user.jc91715.top');//换成自己的域名
// Project name
set('application', 'laravel.user.jc91715.top'); //会部署在 /var/www/sites/laravel.deployer.user.jc91715.top下尽量domain一致
// Project repository
set('repository', 'https://gitee.com/wpjscc/laravel.git');//换成自己的
set('server_ip','121.40.65.228');
set('server_user','root');
set('identityFile','~/.ssh/id_rsa');
/** 必填项 */



// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server 
add('writable_dirs', []);


// Hosts

host(get('server_ip'))
    ->set('deploy_path', '/var/www/sites/{{application}}')->user(get('server_user'))->identityFile(get('identityFile'));  
set('dnmp_path', '~/dnmp');
set('docker_release_path', function(){
    $releasePath = get('release_path');

    return rtrim(str_replace('/var/www/', '/www/', $releasePath),'/');
});
set('docker_deploy_path', function(){
    $releasePath = get('deploy_path');

    return rtrim(str_replace('/var/www/', '/www/', $releasePath),'/');
});


// Tasks
//没用到
task('build', function () {
    run('cd {{release_path}} && build');
});

task('docker_path', function () {
    writeln(get('release_path'));
    writeln('{{deploy_path}}/shared');
    writeln(get('dnmp_path'));
    writeln(get('docker_release_path'));

});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

// before('deploy:symlink', 'artisan:migrate');

desc('Deploy your project');
task('docker:writable',function(){
    run('docker exec php sh  -c \'chmod -R  777  {{docker_release_path}}/bootstrap  {{docker_deploy_path}}/shared/storage\'');
});
task('docker:deploy:vendors',function(){
    run('docker exec php sh  -c \'cd  {{docker_release_path}} && composer {{composer_options}} -vvv\'');
});

task('docker:artisan:storage:link',function(){
    run('docker exec php php {{docker_release_path}}/artisan storage:link');
});
task('docker:artisan:view:cache',function(){
    run('docker exec php php {{docker_release_path}}/artisan view:cache');
});
task('docker:artisan:config:cache',function(){
    run('docker exec php php {{docker_release_path}}/artisan config:cache');
});
task('docker:artisan:optimize',function(){
    run('docker exec php php {{docker_release_path}}/artisan optimize');
});



task('upload:env',function(){
    upload('./laravel.env','{{deploy_path}}/shared/.env');
});

task('docker:nginx:certbot',function(){//第一次运行单独
    if (!test("[ -f {{dnmp_path}}/services/nginx/certbot/{{domain}} ]")) {
        run('docker exec nginx sh -c  \'rm -rf /var/lib/letsencrypt/temp_checkpoint && certbot --agree-tos -d {{domain}} --nginx --register-unsafely-without-email\'');
    }else{
        writeln('已经申请过证书了');
    }
}); 

//nginx 配置文件
task('upload:nginx:conf',function(){
    $tpl = file_get_contents('./domain.conf.tpl');
    $tpl = str_replace('{{application}}', get('application'), $tpl);
    $tpl = str_replace('{{domain}}', get('domain'), $tpl);
    
    if(test("[ -d {{dnmp_path}}/services/nginx/certbot/{{domain}} ]")){//是否支持证书
        $tpl = str_replace('{{ssl}}', 'listen 443 ssl;
        ssl_certificate /etc/letsencrypt/live/'.get('domain').'/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/'.get('domain').'/privkey.pem;
        ssl_protocols       TLSv1 TLSv1.1 TLSv1.2;
        ssl_ciphers         HIGH:!aNULL:!MD5;', $tpl);
    }else{
        $tpl = str_replace('{{ssl}}', '', $tpl);
    }
    file_put_contents('./domain.conf', $tpl);
    upload('./domain.conf','{{dnmp_path}}/services/nginx/conf.d/'.get('domain').'.conf');
});

task('docker:nginx:reload',function(){
    run('docker exec nginx sh -c \'if nginx -t 2>/dev/null;then nginx -s reload; else echo "domain.conf 配置有误"; fi\'');
});

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'upload:env',//env文件 可在服务器上手动
    'deploy:shared',
    // 'deploy:writable',
    'docker:writable',//777路径
    'docker:deploy:vendors',
    'docker:artisan:storage:link',
    'docker:artisan:view:cache',
    'docker:artisan:config:cache',
    'docker:artisan:config:cache',
    'docker:artisan:optimize',
    // 'deploy:vendors',
    // 'artisan:storage:link',
    // 'artisan:view:cache',
    // 'artisan:config:cache',
    // 'artisan:optimize',
    'deploy:symlink',//链接到current
    'deploy:unlock',
    'cleanup',
]);

task('docker:nginx:conf',[
    'upload:nginx:conf',
    'docker:nginx:reload'
]);


