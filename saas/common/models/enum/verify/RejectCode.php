<?php


namespace common\models\enum\verify;


class RejectCode
{
    private const PASS = 1;
    private const NOT_PASS = 2;

    public static $passMap = [
        self::PASS        => 'pass',
        self::NOT_PASS    => 'not pass',
    ];



    //拒绝码
    public static $rejectCode = [
        'Module1' => [
            'MR' => [ //用户真实性
                'name'=>'Identity Verification',
                'child'=>[
                    '001' => ['name' => 'Face photos don\'t match'],
                    '002' => ['name' => 'Bank statement info doesn\'t match'],
                    '003' => ['name' => 'Company docs info doesn\'t match'],
                    '004' => [
                        'name'     => 'Fail to pass personal telephone questions',
                        'question' => [
                            'Q1' => 'When is your birthday?',
                            'Q2' => 'What\'s your education level? What\'s the school name of your highest education level?',
                            'Q3' => 'What\'s your father\'s and mother\'s name?',
                            'Q4' => 'What\'s your current industry and position? Are you currently engaged as a journalist / worker / doctor / teacher / business staff? ',
                            'Q5' => 'Whats your monthly salary?',
                            'Q6' => 'How many children do you have?'
                        ]
                    ],
                ]
            ],
            'MC' => [ //还款能力
                'name'=>'Repayment Ability',
                'child'=>[
                    '001' => ['name' => 'Low salary in bank statememt'],
                ]
            ],
            'MN' => [  //负面信息
                'name'=>'Negative information',
                'child'=>[
                    '001' => ['name' => 'Currently in lawsuit case'],
                    '002' => [
                        'name' => 'Involved in criminal activities',
                        'question' => [
                            'Q1' => 'Have you ever had criminal records?'
                        ]
                    ],
                    '003' => [
                        'name' => 'involved in litigation as a defendant',
                        'question' => [
                            'Q1' => 'Have you ever been involved in litigation as a defendant; if in an economic suit, is the amount more than Rs. 300,000 ( > Rs. 300,000 ) ?',
                        ]
                    ],
                    '004' => [
                            'name' => 'Involved in compulsory execution by the court',
                        'question' => [
                            'Q1' => 'Have you or your property ever gone through compulsory execution by the court?',
                        ]
                    ],

                ]
            ]
        ],
        'Module2' => [
            'M2' => [  //姓名比较
                'name'=>'Name Match',
                'child'=>[
                    '010' => [
                        'name' => 'Name doesn\'t Match',
                        'question' => [
                            'Q1' => 'Do the listed names show the same person\'s name?'
                        ]
                    ],
                ]
            ]
        ]
    ];

    //根据moudule 获取code list
    public static function getAllList($modules){
        if(is_string($modules)){
            $modules = [$modules];
        }
        $reject_list = [];
        foreach ($modules as $module){
            $reject_list = array_merge($reject_list, self::$rejectCode[$module]);
        }
        return $reject_list;
    }

    //备注码
    public static function getRemarkCode($modules)
    {
        $reject_list = self::getAllList($modules);
        $reject_tmp = [];
        foreach($reject_list as $k=>$v){
            foreach($v['child'] as $key => $value){
                $reject_tmp[$k.'o'.$key] = $v['name']." / ".$value['name'];
            }
        }
        return $reject_tmp;
    }

    //电核问题
    public static function getQuestion($modules)
    {
        $reject_list = self::getAllList($modules);
        $question_tmp = [];
        foreach($reject_list as $k=>$v){
            foreach($v['child'] as $key => $value){
                if(isset($value['question'])){
                    $question_tmp[$k.'o'.$key] = ['code'=> $key,'name'=>$v['name'],'question' => $value['question']];
                }
            }
        }
        return $question_tmp;
    }

    //check
    public static function checkCode($lineKey,$modules)
    {
        $reject_list = self::getAllList($modules);
        if(!isset($lineKey[0]) || !isset($lineKey[1])){
            return false;
        }
        if(!isset($reject_list[$lineKey[0]]['child'][$lineKey[1]])){
            return false;
        }
        return true;
    }

    //check
    public static function checkQuestion($question,$modules)
    {
        $reject_list = self::getAllList($modules);
        foreach ($question as $key => $item){
            $lineKey = explode('o',$key);
            if(!$reject_list[$lineKey[0]]['child'][$lineKey[1]]['question'][$lineKey[2]]){
                return false;
            }
            if(!in_array($item,array_keys(self::$passMap))){
                return false;
            }
        }
        return true;
    }

    //check提交问题数据 通过数 拒绝数 总数
    public static function checkQuestionDetail($question,$modules)
    {
        $result = [];
        $reject_list = self::getAllList($modules);
        foreach($reject_list as $k=>$v){
            foreach($v['child'] as $key => $value){
                if(isset($value['question'])){
                    $passCount = 0;
                    $rejectCount = 0;
                    $totalCount  = 0;
                    foreach ($value['question'] as $qKey => $qVal){
                        $lineKey = $k.'o'.$key.'o'.$qKey;
                        foreach ($question as $_key => $_val){
                            if($lineKey == $_key){
                                $totalCount++;
                                if($_val == self::PASS) $passCount++;
                                if($_val == self::NOT_PASS) $rejectCount++;
                            }
                        }
                    }
                    $result[$k][$key] = ['passCount' => $passCount, 'rejectCount' => $rejectCount, 'totalCount' => $totalCount];
                }
            }
        }
        return $result;
    }

    //解析存储数据
    public static function analysisQuestionResult($result){
        $res = [];
        $questionArr = json_decode($result,true);
        if(empty($questionArr)){
            return $res;
        }
        $rejectList = [];
        foreach (self::$rejectCode as $item){
            $rejectList = array_merge($rejectList,$item);
        }
        foreach ($questionArr as $key => $value){
            $keyList = explode('o',$key);
            $res[$keyList[0]]['name'] = $rejectList[$keyList[0]]['name'];
            $res[$keyList[0]]['list'][] = ['question' => $rejectList[$keyList[0]]['child'][$keyList[1]]['question'][$keyList[2]], 'result' => $value];

        }
        return $res;
    }
}