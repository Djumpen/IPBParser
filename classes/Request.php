<?php

namespace IPBParser\Classes;

class Request {

    private $registry;

    private $cookiefile = 'tmp/cookie.txt';

    private $agent = 'Mozilla/5.0 (MeeGo; NokiaN9) AppleWebKit/534.13 (KHTML, like Gecko) NokiaBrowser/8.5.0 Mobile Safari/534.13';

    private $started_at;

    private $last_request;

    public function __construct($registry){
        $this->registry = $registry;
    }

    function getDom($get_url, $just_html = false){
        $html = $this->_get($get_url);

        if($just_html)
            return $html;
        else
            return \Sunra\PhpSimple\HtmlDomParser::str_get_html($html);
    }

    private function _get($url){
        if(!$this->started_at){
            $this->started_at = time();
        }

        // Check delay
        $time_diff = time() - $this->last_request;
        if($time_diff < $this->registry->config->delay){
            sleep($this->registry->config->delay - $time_diff);
            $this->_get($url);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefile);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $html = curl_exec($ch);
        curl_close($ch);

        $this->last_request = time();

        // TODO: check answer and retry if needed

        return $html;
    }

    public function getCommonTime(){
        if($this->started_at){
            return gmdate('i:s', time() - $this->started_at);
        } else {
            return 'No request was fired';
        }
    }
}