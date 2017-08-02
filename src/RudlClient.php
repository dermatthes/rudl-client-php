<?php
/**
 * Created by PhpStorm.
 * User: matthes
 * Date: 8/2/17
 * Time: 5:06 AM
 */

namespace Rudl\Client;


class RudlClient
{

    private $mSock = null;
    private $mServerIp;
    private $mServerPort;
    private $mSysId;
    private $mAccountId = null;

    public function __construct($logIp, $logPort=62111)
    {
        $this->mSock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $this->mServerIp = $logIp;
        $this->mServerPort = $logPort;
    }


    public function setSysId ($id) {
        $this->mSysId = $id;
    }

    public function setAccountId ($id) {
        $this->mAccountId = $id;
    }


    public function sendMessage ($sysid) {
        $ru = getrusage();
        $rr = [
            11,
            $sysid,
            gethostname(),
            $this->mAccountId,
            @$_SERVER["HTTP_X_FORWARDED_FOR"],
            memory_get_peak_usage(),
            $ru["ru_utime.tv_sec"] + ($ru["ru_utime.tv_usec"] * 0.000001) + 0.001,
            $ru["ru_stime.tv_sec"] + ($ru["ru_utime.tv_usec"] * 0.000001) + 0.001
        ];
        $msg = json_encode($rr);
        socket_sendto($this->mSock, $msg, strlen($msg), 0, $this->mServerIp, $this->mServerPort);
    }


    public function registerResourceLogging () {
        register_shutdown_function(function () {
            $ru = getrusage();
            $rr = [
                11,
                $this->mSysId,
                gethostname(),
                $this->mAccountId,
                @$_SERVER["HTTP_X_FORWARDED_FOR"],
                memory_get_peak_usage(),
                $ru["ru_utime.tv_sec"] + ($ru["ru_utime.tv_usec"] * 0.000001) + 0.001,
                $ru["ru_stime.tv_sec"] + ($ru["ru_utime.tv_usec"] * 0.000001) + 0.001
            ];
            $msg = json_encode($rr);
            socket_sendto($this->mSock, $msg, strlen($msg), 0, $this->mServerIp, $this->mServerPort);
        });
    }


}