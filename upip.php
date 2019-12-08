<?php

class DnspodApi
{
    protected $base_params;

    /**
     * DnspodApi constructor.
     * @param $base_params $base_params = [
     * 'format' => 'json',
     * ];
     */
    public function __construct($base_params)
    {
        $this->base_params = $base_params;
        $this->http = new  Http();

    }

    /**记录信息
     * @param $domain_id
     * @param $record_id
     * @return mixed|null
     */
    public function getRecord($domain_id, $record_id)
    {
        return $this->http->postApi('https://dnsapi.cn/Record.Info',
            array_merge($this->base_params, [
                'domain_id' => $domain_id,
                'record_id' => $record_id
            ])
        );
    }

    /**域名列表
     * @return mixed|null
     */
    public function getDomains()
    {
        return $this->http->postApi('https://dnsapi.cn/Domain.List',
            array_merge($this->base_params, [

            ])
        );
    }

    /** 记录列表
     * @param $domain_id
     * @return mixed|null
     */
    public function getRecords($domain_id)
    {
        return $this->http->postApi('https://dnsapi.cn/Record.List',
            array_merge($this->base_params, [
                'domain_id' => $domain_id,
            ])
        );
    }

    /** 修改记录
     * @param $domain_id
     * @param $record
     * @param $value
     * @return mixed|null
     */
    public function setRecord($domain_id, $record, $value)
    {
        return $this->http->postApi('https://dnsapi.cn/Record.Modify',
            array_merge($this->base_params, [
                'domain_id' => $domain_id,
                'record_id' => $record['id'],
                'sub_domain' => $record['sub_domain'],
                'record_type' => $record['record_type'],
                'record_line' => $record['record_line'],
                'record_line_id' => $record['record_line_id'],
                'value' => $value,
                'mx' => $record['mx'],
                'ttl' => $record['ttl'],
                'status' => $record['enabled'],
                'weight' => $record['weight'],
            ])
        );

    }

    public function addlog($s)
    {
        file_put_contents(__DIR__ . '/iplog.txt', date('Y-m-d H:i:s') . "  $s " . PHP_EOL, FILE_APPEND);

    }
}

class Http
{
    protected $url = '';
    protected $sleep_time, $interval, $now_num;

    public function __construct($interval = PHP_INT_MAX, $sleep_time = 0)
    {
        $this->sleep_time = $sleep_time;
        $this->interval = $interval;
        $this->now_num;
    }

    public function getUrl()
    {
        return $this->url;
    }

    private function to_url($path, $parameters = [])
    {
        $info = parse_url($path);
        $params = isset($info['query']) ? $info['query'] : "";
        parse_str($params, $output);
        $parm_str = http_build_query(array_merge($output, $parameters));
        return $this->clear_urlcan($path) . (empty($parm_str) ? "" : '?' . $parm_str);
    }

    private function clear_urlcan($url)
    {
        if (strpos($url, '?') !== false) {
            $url = substr($url, 0, strpos($url, '?'));
        }
        return $url;
    }

    protected function waitOrDo()
    {
        $this->now_num++;
        if ($this->now_num % $this->interval == 0) {
            sleep($this->sleep_time);
        }
    }

    public function get($url, $params = [], $headers = [], $timeout = 10)
    {
        $this->waitOrDo();
        if (!empty($param)) {
            $url .= '?' . http_build_query($params);
        }
        $this->url = $this->to_url($url, $params);
        $is_https = substr($url, 0, 8) == "https://" ? true : false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout - 2);
        if ($is_https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        }
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        //释放curl句柄


        if ($output === false) {
            $error = 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            throw new \Exception($error);
        } else {
            curl_close($ch);
            return $output;
        }
    }

    public function put($url, $data = [], $params = [], $headers = [], $timeout = 10)
    {

        $this->waitOrDo();
        $this->url = $this->to_url($url, $params);
        $is_https = substr($url, 0, 8) == "https://" ? true : false;
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        // Make the REST call, returning the result
        if ($is_https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        }
        $output = curl_exec($ch);
        if ($output === false) {
            $error = 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            throw new \Exception($error);
        } else {
            curl_close($ch);
            return $output;
        }

    }

    public function delete($url, $params = [], $headers = [], $timeout = 10)
    {
        $this->waitOrDo();
        $this->url = $this->to_url($url, $params);
        $is_https = substr($url, 0, 8) == "https://" ? true : false;
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if ($is_https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        }
        $output = curl_exec($ch);
        if ($output === false) {
            $error = 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            throw new \Exception($error);
        } else {
            curl_close($ch);
            return $output;
        }
    }

    public function post($url, $data = [], $params = [], $headers = [], $timeout = 10)
    {
        $this->waitOrDo();
        $this->url = $this->to_url($url, $params);
        $_data = http_build_query($data);
        $is_https = substr($url, 0, 8) == "https://" ? true : false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout - 2);
        if ($is_https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        }
        $output = curl_exec($ch);
        if ($output === false) {
            $error = 'Curl error: ' . curl_error($ch);
            curl_close($ch);
            throw new \Exception($error);
        } else {
            curl_close($ch);
            return $output;
        }
    }

    function getApi($url, $params = [], $headers = [], $timeout = 10)
    {
        try {
            return json_decode($this->get($url, $params, $headers, $timeout), true);
        } catch (\Exception $e) {
            return null;
        }

    }

    function postApi($url, $data = [], $params = [], $headers = [], $timeout = 10)
    {
        try {
            return json_decode($this->post($url, $data, $params, $headers, $timeout), true);
        } catch (\Exception $e) {
            return null;
        }

    }


}

$env = json_decode(file_get_contents('env.json'),true) ;
if (isset($env['login_token'])) {
    $auth = [
        'login_token' => $env['login_token'],
        'format' => 'json',
    ];
} else {
    $auth = [
        'login_email' => $env['login_email'],
        'login_password' => $env['login_password'],
        'format' => 'json',
    ];
}

$dnspodapi = new DnspodApi($auth);
if($argv[1] == 'upip'){
    $domain_id = $env['domain_id'];//域名id
    $record_ids = [$env['record_id']];//域名记录id
    $http = new Http();
    //取得当前ip
    $now_clinet_ip = $http->getApi("http://ip.taobao.com/service/getIpInfo2.php?ip=myip")['data']['ip'];
    $dnspodapi->addlog('now_ip:' . $now_clinet_ip);
    //当前ip和记录值不同 则更新记录
    foreach ($record_ids as $id) {
        //取得记录
        $rs = $dnspodapi->getRecord($domain_id, $id);
        $record = $rs['record'];
        if ($now_clinet_ip != $record['value'] && !empty($now_clinet_ip) && in_array($record['id'], $record_ids)) {//判断ip变更了 则修改记录
            $r = $dnspodapi->setRecord($domain_id, $record, $now_clinet_ip);
            $l = "change domain. domain_id:{$domain_id} record_id:{$record['id']} name:{$record['sub_domain']} old_ip:{$record['value']} new_ip {$now_clinet_ip} msg:" . PHP_EOL;
            $dnspodapi->addlog($l);
        }
    }

}else if($argv[1] == 'show'){
    $domains = $dnspodapi->getDomains();

    foreach ($domains['domains'] as $domain){
        echo "domain:".PHP_EOL;
        print_r($domain);
        $records = $dnspodapi->getRecords($domain['id']);
        echo "records:".PHP_EOL;
        print_r($records);
    }

}



