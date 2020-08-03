#!/usr/bin/env python
# -*- coding: utf-8 -*-

import requests
import json
import time
import argparse
from pprint import pprint
import os
import sys
path =os.path.split(os.path.realpath(sys.argv[0]))[0]
def iprint(obj):
    print (json.dumps(obj, ensure_ascii=False, indent=2))


class DnspodApi:
    base_params = None
    def get(self, url, params={}):
        obj = requests.get(url, params).text
        return obj

    def getIp(self):
        return self.get("https://api.ipify.org/")

    def getJson(self, url, params={}):
        obj = requests.get(url, params).text
        # print(obj)
        # data = array_merge(self.base_params, data)
        return json.loads(obj)

    def postJson(self, url, data={}):
        return json.loads(requests.post(url, data=data).text)

    def postApi(self, url, data={}):
        if self.base_params is not  None:
            dict3 = dict(data , **self.base_params )
        else:
            dict3 = data
        data = dict3
        return self.postJson(url, data)

    '''
     * DnspodApi constructor.
     * @param base_params base_params = [
     * 'format' : 'json',
     * ]
     '''

    def __init__(self, base_params):
        self.base_params = base_params
        # self = Http()

    '''域名列表
     * @return mixed|null
     '''

    def getDomains(self, ):
        return self.postApi('https://dnsapi.cn/Domain.List', {})

    ''' 记录列表
     * @param domain_id
     * @return mixed|null
     '''

    def getRecords(self, domain_id):
        return self.postApi('https://dnsapi.cn/Record.List', {
            'domain_id': domain_id,
        })

    '''记录信息
     * @param domain_id
     * @param record_id
     * @return {
  "monitor_status": "", 
  "record_line_id": "0", 
  "weight": null, 
  "record_line": "默认", 
  "domain_id": "21010867", 
  "enabled": "1", 
  "remark": "", 
  "value": "183.193.38.82", 
  "mx": "0", 
  "record_type": "A", 
  "sub_domain": "home", 
  "ttl": "10", 
  "updated_on": "2019-12-07 02:03:49", 
  "id": "275429589"
}
     '''

    def getRecord(self, domain_id, record_id):
        return self.postApi('https://dnsapi.cn/Record.Info',
                            {
                                'domain_id': domain_id,
                                'record_id': record_id
                            })['record']

    def upRecord(self, domain_id, record_id, ip):
        record = self.getRecord(domain_id, record_id)
        return self.setRecord(record, ip)

    ''' 修改记录
     * @param domain_id
     * @param record
     * @param value
     * @return mixed|null
     '''

    def setRecord(self, record, value):
        return self.postApi('https://dnsapi.cn/Record.Modify',
                            {
                                'domain_id': record['domain_id'],
                                'record_id': record['id'],
                                'sub_domain': record['sub_domain'],
                                'record_type': record['record_type'],
                                'record_line': record['record_line'],
                                'record_line_id': record['record_line_id'],
                                'value': value,
                                'mx': record['mx'],
                                'ttl': record['ttl'],
                                'status': record['enabled'],
                                'weight': record['weight'],
                            }
                            )

    def addlog(self, s):
        with open(path+'/iplog.txt', 'a+') as f:
            f.write(time.strftime("%y%m%d%H%M%S", time.localtime()) + ":" + s + "\n")


parser = argparse.ArgumentParser(description="re create table")
parser.add_argument("cmd")
args = vars(parser.parse_args())

with open(path+'/env.json', 'r') as f:
    json_str = f.read()
env = json.loads(json_str)
if ('login_token' in env):
    auth = {
        'login_token': env['login_token'],
        'format': 'json',
    }
else:
    auth = {
        'login_email': env['login_email'],
        'login_password': env['login_password'],
        'format': 'json',
    }

dnspodapi = DnspodApi(auth)

if (args["cmd"] == 'upip'):
    domain_id = env['domain_id']  # 域名id
    record_ids = {env['record_id']}  # 域名记录id
    pprint(record_ids)
    # 取得当前ip

    now_clinet_ip = dnspodapi.getIp()
    dnspodapi.addlog('now_ip:' + now_clinet_ip)
    # # 当前ip和记录值不同 则更新记录
    #
    for record_id in record_ids:
        #     # 取得记录
        record = dnspodapi.getRecord(domain_id, record_id)
        iprint(record)
        # # 判断ip变更了 则修改记录
        l = "change domain. domain_id:{domain_id} record_id:{record_id} name:{sub_domain} old_ip:{old_ip} new_ip {now_clinet_ip}".format(
            domain_id=domain_id,
            record_id=record_id,
            sub_domain=record['sub_domain'],
            old_ip=record['value'],
            now_clinet_ip=now_clinet_ip
        )
        print (l)
        dnspodapi.addlog(l)
        if (now_clinet_ip != record['value'] and now_clinet_ip != None):
            print (now_clinet_ip, record['id'])
            r = dnspodapi.upRecord(domain_id, record_id, now_clinet_ip)
            l = "change domain. domain_id:{domain_id} record_id:{record_id} name:{sub_domain} old_ip:{old_ip} new_ip {now_clinet_ip}".format(
                domain_id=domain_id,
                record_id=record_id,
                sub_domain=record['sub_domain'],
                old_ip=record['value'],
                now_clinet_ip=now_clinet_ip
            )
            dnspodapi.addlog(l)

elif (args["cmd"] == 'show'):
    domains = dnspodapi.getDomains()
    for domain in domains['domains']:
        print("domain:\n")
        domaininfo = {
            'id': domain['id'],
            'grade_ns': domain['grade_ns'],
            'punycode': domain['punycode'],
            'ttl': domain['ttl'],
            'status': domain['status'],

        }
        iprint(domaininfo)
        records = dnspodapi.getRecords(domain['id'])
        print("records:\n")
        rs = []
        for record in records['records']:
            rs.append({
                'id': record['id'],
                'ttl': record['ttl'],
                'value': record['value'],
                'status': record['status'],
                'name': record['name'],
                'type': record['type'],
            })
        iprint(rs)
