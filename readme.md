
电信支持公网ip，但是是动态的，此脚本可以读取dnspodapi，更新指定域名主机记录为当前的公网ip。

操作步骤。
# 1. 配置 env.php(依赖php7)
1)env.example.php 重命名为 env.example.php
2)需要一个域名,并添加到dnspod上,设置一个用于访问的a记录
3)配置env.php login_token, 根据页面提示获取token  https://support.dnspod.cn/Kb/showarticle/tsid/227/
3)也可以使用账号密码方式简单快捷(login_email,login_password)
5.php upip.php show  获取到你添加的记录id和域名id，配置到 env.php

# 2.在服务器上设置定时脚本
建立 一个cron的文本文件(如 upcron)，添加以下内容
*/1 * * * * /opt/upip/upip.php
启动 crontab upcron
查看 crontab -l


