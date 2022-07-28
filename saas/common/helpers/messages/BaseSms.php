<?php
namespace common\helpers\messages;


abstract class BaseSms implements InterfaceSms {

    #请求路径
    protected $_baseUrl;

    #用户名
    protected $_userName;

    #密码
    protected $_password;

    #私钥
    protected $_privateKey;

    #时间戳
    protected $_timeStamp;

    #运营商短信id
    protected $_smsId;

    #运营商数据返回
    protected $_return;

    #签名数据
    protected $_sign;

    #扩展参数
    protected $_extArr;

    protected $_raw;

    public $_batchMax = 100;

    // 所有短信接口超时时间
    public $timeout = 5;
    public static $ctx_params = array(
        'http' => array(
            'timeout' => 5
        )
    );

    public function __construct($baseUrl, $userName, $password, $extArr = []) {
        $this->_baseUrl    = $baseUrl;
        $this->_userName   = $userName;
        $this->_password   = $password;
        $this->_extArr     = $extArr;
        $this->_privateKey = isset($extArr['privateKey']) ? $extArr['privateKey'] : '';

        $this->_timeStamp = date('YmdHis');
        $this->_smsId  = 0;
    }
}
