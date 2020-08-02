# ddns
电信支持公网ip，但是是动态的，此脚本可以读取dnspodapi，更新指定域名主机记录为当前的公网ip。  

## 依赖
1.有一个自己到域名，并使用dnspod解析  
使用 DNSPod的解析（无缝迁移） https://support.dnspod.cn/Kb/showarticle/tsid/28/  
2. 有独立ip 目前知道电信有独立ip，但是新用户默认没有，需要自己打客服要，就说自己家需要装监控用到   
## 操作步骤。
### 1. 下载 并安装依赖
```
git clone git@github.com:ccq18/ddns.git
pip install requests 
 ```
### 2.安装依赖 
### 1. 配置 env.json
1.env.example.json 重命名为 env.json  
2.需要一个域名,并添加到dnspod上,设置一个用于访问的a记录  
3.配置env.json login_token, 根据页面提示获取token  
https://support.dnspod.cn/Kb/showarticle/tsid/227/  
4.也可以使用账号密码方式简单快捷(login_email,login_password)  
5. ./upip.py show  获取到你添加的记录id和域名id，配置到 env.json  

### 2.在服务器上设置定时脚本
建立 一个cron的文本文件(如 upcron)，添加以下内容  
```
*/1 * * * * {项目绝对路径}/upip.py upip
```
启动 crontab upcron  
查看 crontab -l  


env.json
```
{
    "login_token" :"",
    "domain_id" : 0,
    "record_id":0
}
```