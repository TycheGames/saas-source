<?php

namespace console\controllers;

use common\models\InfoOrder;
use common\models\risk\RiskResultSnapshot;
use common\models\user\UserCreditReportBangaloreExperian;
use common\models\user\UserCreditReportExperian;
use common\models\user\UserCreditReportMobiExperian;
use common\services\message\WeWorkService;
use common\services\MonitorService;

class MonitorController extends BaseController {

    #####################申请监控预警START################
    //产品城市
    public function actionOrderWithProductAndCity(){
        $this->printMessage('start');
        $now = time();
        $m = date('i');

        //今天前15分钟至前5分钟的 10分钟内申请数据
        $startTimeBefore15 = $now - 900;
        $startTimeBefore5 = $now - 300;
        //昨天前15分钟至前5分钟的 10分钟内申请数据
        $yesterdayStartTimeBefore15 = $startTimeBefore15 - 86400;
        $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
        $this->sendProductWith('residential_city',false,$startTimeBefore15,$startTimeBefore5,$yesterdayStartTimeBefore15,$yesterdayStartTimeBefore5,'10分钟');

        //整点触发
        if($m == 0){
            //今天前65分钟至前5分钟的 一小时内申请数据
            $startTimeBefore65 = $now - 300 - 3600;
            //昨天前65分钟至前5分钟的 一小时内申请数据
            $yesterdayStartTimeBefore65 = $startTimeBefore65 - 86400;
            $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
            $this->sendProductWith('residential_city',false,$startTimeBefore65,$startTimeBefore5,$yesterdayStartTimeBefore65,$yesterdayStartTimeBefore5,'1小时');
        }
        $this->printMessage('end');
    }

    //产品邦
    public function actionOrderWithProductAndState(){
        $this->printMessage('start');
        $now = time();
        $m = date('i');

        //今天前15分钟至前5分钟的 10分钟内申请数据
        $startTimeBefore15 = $now - 900;
        $startTimeBefore5 = $now - 300;
        //昨天前15分钟至前5分钟的 10分钟内申请数据
        $yesterdayStartTimeBefore15 = $startTimeBefore15 - 86400;
        $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
        $this->sendProductWith('residential_address',false,$startTimeBefore15,$startTimeBefore5,$yesterdayStartTimeBefore15,$yesterdayStartTimeBefore5,'10分钟');

        //整点触发
        if($m == 0){
            //今天前65分钟至前5分钟的 一小时内申请数据
            $startTimeBefore65 = $now - 300 - 3600;
            //昨天前65分钟至前5分钟的 一小时内申请数据
            $yesterdayStartTimeBefore65 = $startTimeBefore65 - 86400;
            $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
            $this->sendProductWith('residential_address',false,$startTimeBefore65,$startTimeBefore5,$yesterdayStartTimeBefore65,$yesterdayStartTimeBefore5,'1小时');
        }
        $this->printMessage('end');
    }

    //产品渠道
    public function actionOrderWithProductAndMediaSource(){
        $this->printMessage('start');
        $now = time();
        $m = date('i');

        //今天前15分钟至前5分钟的 10分钟内申请数据
        $startTimeBefore15 = $now - 900;
        $startTimeBefore5 = $now - 300;
        //昨天前15分钟至前5分钟的 10分钟内申请数据
        $yesterdayStartTimeBefore15 = $startTimeBefore15 - 86400;
        $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
        $this->sendProductWith('media_source',false,$startTimeBefore15,$startTimeBefore5,$yesterdayStartTimeBefore15,$yesterdayStartTimeBefore5,'10分钟');

        //整点触发
        if($m == 0){
            //今天前65分钟至前5分钟的 一小时内申请数据
            $startTimeBefore65 = $now - 300 - 3600;
            //昨天前65分钟至前5分钟的 一小时内申请数据
            $yesterdayStartTimeBefore65 = $startTimeBefore65 - 86400;
            $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
            $this->sendProductWith('media_source',false,$startTimeBefore65,$startTimeBefore5,$yesterdayStartTimeBefore65,$yesterdayStartTimeBefore5,'1小时');
        }
        $this->printMessage('end');
    }

    //产品数盟设备ID
    public function actionOrderWithSzlmQueryId(){
        $this->printMessage('start');
        $now = time();

        $service = new WeWorkService();
        //今天前65分钟至前5分钟的 一小时内申请数据
        $startTimeBefore65 = $now - 300 - 3600;
        $startTimeBefore5 = $now - 300;
        $szlmQueryId = MonitorService::getAllNewCountGroupByFieldId($startTimeBefore65,$startTimeBefore5,'szlm_query_id',10);

        if($szlmQueryId){
            $message = '近1小时内全新本新订单数盟设备ID申请密度异常，近1小时内';
            $isFlag = 1;
            foreach ($szlmQueryId as $sid => $orderCount){
                if(empty($sid)){
                    $isFlag = 0;
                }
                $message .= sprintf('，%s全平台申请%d单',$sid,$orderCount);
            }

            if(YII_ENV_PROD && $isFlag){
                $service->send($message);
            }else{
                $this->printMessage($message);
            }
        }

        $this->printMessage('end');
    }
    #####################申请监控预警END################

    #####################放款监控预警START################
    //产品城市
    public function actionLoanWithProductAndCity(){
        $this->printMessage('start');
        $now = time();
        $m = date('i');

        //今天前15分钟至前5分钟的 10分钟内申请数据
        $startTimeBefore15 = $now - 900;
        $startTimeBefore5 = $now - 300;
        //昨天前15分钟至前5分钟的 10分钟内申请数据
        $yesterdayStartTimeBefore15 = $startTimeBefore15 - 86400;
        $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
        $this->sendProductWith('residential_city',true,$startTimeBefore15,$startTimeBefore5,$yesterdayStartTimeBefore15,$yesterdayStartTimeBefore5,'10分钟');

        //整点触发
        if($m == 0){
            //今天前65分钟至前5分钟的 一小时内申请数据
            $startTimeBefore65 = $now - 300 - 3600;
            //昨天前65分钟至前5分钟的 一小时内申请数据
            $yesterdayStartTimeBefore65 = $startTimeBefore65 - 86400;
            $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
            $this->sendProductWith('residential_city',true,$startTimeBefore65,$startTimeBefore5,$yesterdayStartTimeBefore65,$yesterdayStartTimeBefore5,'1小时');
        }
        $this->printMessage('end');
    }

    //产品邦
    public function actionLoanWithProductAndState(){
        $this->printMessage('start');
        $now = time();
        $m = date('i');

        //今天前15分钟至前5分钟的 10分钟内申请数据
        $startTimeBefore15 = $now - 900;
        $startTimeBefore5 = $now - 300;
        //昨天前15分钟至前5分钟的 10分钟内申请数据
        $yesterdayStartTimeBefore15 = $startTimeBefore15 - 86400;
        $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
        $this->sendProductWith('residential_address',true,$startTimeBefore15,$startTimeBefore5,$yesterdayStartTimeBefore15,$yesterdayStartTimeBefore5,'10分钟');

        //整点触发
        if($m == 0){
            //今天前65分钟至前5分钟的 一小时内申请数据
            $startTimeBefore65 = $now - 300 - 3600;
            //昨天前65分钟至前5分钟的 一小时内申请数据
            $yesterdayStartTimeBefore65 = $startTimeBefore65 - 86400;
            $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
            $this->sendProductWith('residential_address',true,$startTimeBefore65,$startTimeBefore5,$yesterdayStartTimeBefore65,$yesterdayStartTimeBefore5,'1小时');
        }
        $this->printMessage('end');
    }

    //产品渠道
    public function actionLoanWithProductAndMediaSource(){
        $this->printMessage('start');
        $now = time();
        $m = date('i');

        //今天前15分钟至前5分钟的 10分钟内申请数据
        $startTimeBefore15 = $now - 900;
        $startTimeBefore5 = $now - 300;
        //昨天前15分钟至前5分钟的 10分钟内申请数据
        $yesterdayStartTimeBefore15 = $startTimeBefore15 - 86400;
        $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
        $this->sendProductWith('media_source',true,$startTimeBefore15,$startTimeBefore5,$yesterdayStartTimeBefore15,$yesterdayStartTimeBefore5,'10分钟');

        //整点触发
        if($m == 0){
            //今天前65分钟至前5分钟的 一小时内申请数据
            $startTimeBefore65 = $now - 300 - 3600;
            //昨天前65分钟至前5分钟的 一小时内申请数据
            $yesterdayStartTimeBefore65 = $startTimeBefore65 - 86400;
            $yesterdayStartTimeBefore5 = $startTimeBefore5 - 86400;
            $this->sendProductWith('media_source',true,$startTimeBefore65,$startTimeBefore5,$yesterdayStartTimeBefore65,$yesterdayStartTimeBefore5,'1小时');
        }
        $this->printMessage('end');
    }

    //产品数盟设备ID
    public function actionLoanWithSzlmQueryId(){
        $this->printMessage('start');
        $now = time();

        $service = new WeWorkService();
        //今天前65分钟至前5分钟的 一小时内申请数据
        $startTimeBefore65 = $now - 300 - 3600;
        $startTimeBefore5 = $now - 300;
        $szlmQueryId = MonitorService::getAllNewCountGroupByFieldId($startTimeBefore65,$startTimeBefore5,'szlm_query_id',10,true);

        if($szlmQueryId){
            $message = '近1小时内全新本新订单数盟设备ID放款密度异常，近1小时内';
            $isFlag = 1;
            foreach ($szlmQueryId as $sid => $orderCount){
                if(empty($sid)){
                    $isFlag = 0;
                }
                $message .= sprintf('，%s全平台放款%d单',$sid,$orderCount);
            }
            if(YII_ENV_PROD && $isFlag){
                $service->send($message);
            }else{
                $this->printMessage($message);
            }
        }

        $this->printMessage('end');
    }

    private function sendProductWith($field,$isLoan,$startTimeBefore15,$startTimeBefore5,$yesterdayStartTimeBefore15,$yesterdayStartTimeBefore5,$timeStr){
        $productAnd = MonitorService::getAllNewCountByProductAnd($startTimeBefore15,$startTimeBefore5,$field,$isLoan);
        $yesterdayProductAnd = MonitorService::getAllNewCountByProductAnd($yesterdayStartTimeBefore15,$yesterdayStartTimeBefore5,$field,$isLoan);
        if($isLoan){
            $str = '申请';
        }else{
            $str = '放款';
        }
        $service = new WeWorkService();
        //对比
        $nameArr = ['residential_city' => '城市','residential_address' => '邦', 'media_source' => '渠道'];
        $name = $nameArr[$field] ?? '';
        foreach ($productAnd as $productName => $info){
            foreach ($info as $field => $orderCount){
                if($orderCount >= 20 && isset($yesterdayProductAnd[$productName][$field]) && $yesterdayProductAnd[$productName][$field] >= 20){
                    if($orderCount / $yesterdayProductAnd[$productName][$field] > 1.2){
                        //符合条件并发送
                        $message = sprintf("[产品%s][{$name}%s]近{$timeStr}内全新本新订单{$str}密度异常，近{$timeStr}内{$str}订单数为%d单，前一天同时间段内{$str}订单数为%d单，今日同时段订单数为昨日的%0.2f倍。",
                            $productName,
                            $field,
                            $orderCount,
                            $yesterdayProductAnd[$productName][$field],
                            $orderCount / $yesterdayProductAnd[$productName][$field]
                        );
                        if(YII_ENV_PROD){
                            $service->send($message);
                        }else{
                            $this->printMessage($message);
                        }
                    }
                }
            }
        }
    }
    #####################放款监控预警END################


    #####################异常关联预警START################
    //全平台：以整点时间为起点每10分钟更新，如果前10分钟内存在全新本新申请订单的某个数盟设备ID关
    //联的不同Pan / Aadhaar / 手机号数量 > 5 或 数盟设备ID关联的不同IMEI > 1，且近10分钟内
    //该数盟设备ID的全新本新申请订单数 > 3
    public function actionAbnormalAssociationSzlmQueryId(){
        $this->printMessage('start');
        $now = time();
        $m = date('i');
        //今天前15分钟至前5分钟的 10分钟内申请数据
        $startTimeBefore15 = $now - 900;
        $startTimeBefore5 = $now - 300;
        //该数盟设备ID的全新本新申请订单数 > 3
        $this->sendAbnormalAssociationFieldId($startTimeBefore15,$startTimeBefore5,'szlm_query_id',4,'10分钟');


        if($m == 0){
            //今天前65分钟至前5分钟的 一小时内申请数据
            $startTimeBefore65 = $now - 300 - 3600;
            $this->sendAbnormalAssociationFieldId($startTimeBefore65,$startTimeBefore5,'szlm_query_id',6,'1小时');
        }
        $this->printMessage('end');
    }

    //全平台：以整点时间为起点每10分钟更新，如果前10分钟内存在全新本新申请订单的Pan关联的不同数盟设
    //备ID / 手机号数量 > 5 或 Pan关联的不同Aadhaar数量 > 1，且近10分钟内该Pan的全新本新申请订
    //单数 > 3
    public function actionAbnormalAssociationPanCode(){
        $this->printMessage('start');
        $now = time();
        $m = date('i');
        //今天前15分钟至前5分钟的 10分钟内申请数据
        $startTimeBefore15 = $now - 900;
        $startTimeBefore5 = $now - 300;
        //全新本新申请订单数 > 3
        $this->sendAbnormalAssociationFieldId($startTimeBefore15,$startTimeBefore5,'pan_code',4,'10分钟');


        if($m == 0){
            //今天前65分钟至前5分钟的 一小时内申请数据
            $startTimeBefore65 = $now - 300 - 3600;
            $this->sendAbnormalAssociationFieldId($startTimeBefore65,$startTimeBefore5,'pan_code',6,'1小时');
        }
        $this->printMessage('end');
    }

    //全平台：以整点时间为起点每10分钟更新，如果前10分钟内存在全新本新申请订单的Aadhaar关联的不同数盟设
    //备ID / 手机号数量 > 5 或 Aadhaar关联的不同Pan数量 > 1，且近10分钟内该Aadhaar的全新本新申请订
    //单数 > 3
    public function actionAbnormalAssociationAadhaar(){
        $this->printMessage('start');
        $now = time();
        $m = date('i');
        //今天前15分钟至前5分钟的 10分钟内申请数据
        $startTimeBefore15 = $now - 900;
        $startTimeBefore5 = $now - 300;
        //全新本新申请订单数 > 3
        $this->sendAbnormalAssociationFieldId($startTimeBefore15,$startTimeBefore5,'aadhaar_md5',4,'10分钟');


        if($m == 0){
            //今天前65分钟至前5分钟的 一小时内申请数据
            $startTimeBefore65 = $now - 300 - 3600;
            $this->sendAbnormalAssociationFieldId($startTimeBefore65,$startTimeBefore5,'aadhaar_md5',6,'1小时');
        }
        $this->printMessage('end');
    }

    //全平台：以整点时间为起点每10分钟更新，如果前10分钟内存在全新本新申请订单的某个手机号关联
    //的不同Pan / Aadhaar / 数盟设备ID数量 > 5，且近10分钟内该手机号的全新本新申请订单数 > 3
    public function actionAbnormalAssociationPhone(){
        $this->printMessage('start');
        $now = time();
        $m = date('i');
        //今天前15分钟至前5分钟的 10分钟内申请数据
        $startTimeBefore15 = $now - 900;
        $startTimeBefore5 = $now - 300;
        //全新本新申请订单数 > 3
        $this->sendAbnormalAssociationFieldId($startTimeBefore15,$startTimeBefore5,'phone',4,'10分钟');


        if($m == 0){
            //今天前65分钟至前5分钟的 一小时内申请数据
            $startTimeBefore65 = $now - 300 - 3600;
            $this->sendAbnormalAssociationFieldId($startTimeBefore65,$startTimeBefore5,'phone',6,'1小时');
        }
        $this->printMessage('end');
    }


    private function sendAbnormalAssociationFieldId($startTime,$endTime,$field,$havingCount,$timeStr){
        $countOneArr = [
            'szlm_query_id' => 'device_id',
            'pan_code' => 'aadhaar_md5',
            'aadhaar_md5'=> 'pan_code'
        ];
        $fieldArr = [
            'szlm_query_id',
            'pan_code',
            'aadhaar_md5',
            'phone',
        ];
        if($field == 'szlm_query_id'){
            $fieldArr[] = 'device_id';
        }
        $fieldArr = array_merge(array_diff($fieldArr, array($field)));
        $fieldIds = array_keys(MonitorService::getAllNewCountGroupByFieldId($startTime,$endTime,$field,$havingCount));
        if($fieldIds){
            $res = MonitorService::getAllNewOrderByFieldIds($startTime,$endTime,$field,$fieldIds);
            if($res){
                $arr = [];
                foreach ($res as $item){
                    $orderIds = explode(',',$item['order_id_str']);
                    foreach ($fieldArr as $f){
                        if(isset($arr[$item[$field]][$f][$item[$f]])){
                            $arr[$item[$field]][$f][$item[$f]] = array_unique(array_merge($arr[$item[$field]][$f][$item[$f]],$orderIds));
                        }else{
                            $arr[$item[$field]][$f][$item[$f]] = $orderIds;
                        }
                    }
                }

                if($arr){
                    $service = new WeWorkService();
                    foreach ($arr as $fieldId => $data){
                        if(empty($fieldId)){
                            continue;
                        }
                        $flagArr = [];
                        $orderIdsArr = [];
                        foreach ($data as $fd => $value){
                            if(isset($countOneArr[$field]) && $countOneArr[$field] == $fd){
                                if(count($value) > 1){
                                    $flagArr[] = $fd;
                                    foreach ($value as $fieldV => $orderIds){
                                        $orderIdsArr = array_unique(array_merge($orderIdsArr,$orderIds));
                                    }
                                }
                            }else{
                                if(count($value) > 5){
                                    $flagArr[] = $fd;
                                    foreach ($value as $fieldV => $orderIds){
                                        $orderIdsArr = array_unique(array_merge($orderIdsArr,$orderIds));
                                    }
                                }
                            }
                        }
                        if($flagArr){
                            $strBefore = implode('/',$fieldArr);
                            $strAfter = '';
                            foreach ($fieldArr as $item){
                                if(isset($data[$item])){
                                    $strAfter .= count($data[$item]).'，';
                                }else{
                                    $strAfter .= '0，';
                                }
                            }

                            $message = sprintf("近{$timeStr}内存在%d笔全新本新申请订单发生数{$field}的异常关联，{$field}:[%s]关联的不同{$strBefore}数量分别为{$strAfter}异常关联订单号为%s",
                                count($orderIdsArr),
                                $fieldId,
                                implode(',',$orderIdsArr)
                            );
                            if(YII_ENV_PROD){
                                $service->send($message);
                            }else{
                                $this->printMessage($message);
                            }
                        }
                    }
                }
            }
        }
    }
    ######################异常关联预警END####################


    ######################Experian报告调用异常START################

    public function actionExperianAbnormalRateOfCalling(){
        //
        $result = [];
        $now = time();
        $beforeOneHour = $now - 3600;
        //mobi
        $mobiExperian = UserCreditReportMobiExperian::find()
            ->select(['all_count' => 'COUNT(1)','fail_count' => 'SUM(IF(data_status = 0,1,0))'])
            ->where(['>=','query_time',$beforeOneHour])
            ->andWhere(['<=','query_time',$now])
            ->asArray()
            ->one();

        if($mobiExperian['all_count'] > 0){
            if($mobiExperian['fail_count'] / $mobiExperian['all_count'] >= 0.25){
                $result['mobi'] = sprintf('%0.2f',$mobiExperian['fail_count'] / $mobiExperian['all_count']);
            }
        }
        //bangalore
        $bangaloreExperian = UserCreditReportBangaloreExperian::find()
            ->select(['all_count' => 'COUNT(1)','fail_count' => 'SUM(IF(data_status = 0,1,0))'])
            ->where(['>=','query_time',$beforeOneHour])
            ->andWhere(['<=','query_time',$now])
            ->asArray()
            ->one();

        if($bangaloreExperian['all_count'] > 0){
            if($bangaloreExperian['fail_count'] / $bangaloreExperian['all_count'] >= 0.25){
                $result['bangalore'] = sprintf('%0.2f',$bangaloreExperian['fail_count'] / $bangaloreExperian['all_count']);
            }
        }
        //kudos
        $kudosExperian = UserCreditReportExperian::find()
            ->select(['all_count' => 'COUNT(1)','fail_count' => 'SUM(IF(data_status = 0,1,0))'])
            ->where(['>=','query_time',$beforeOneHour])
            ->andWhere(['<=','query_time',$now])
            ->asArray()
            ->one();

        if($kudosExperian['all_count'] > 0){
            if($kudosExperian['fail_count'] / $kudosExperian['all_count'] >= 0.25){
                $result['kudos'] = sprintf('%0.2f',$kudosExperian['fail_count'] / $kudosExperian['all_count']);
            }
        }

        if($result){
            $service = new WeWorkService();
            $nameStr = '';
            $aprStr = '';
            foreach ($result as $name => $apr){
                $nameStr .= ($name.',');
                $aprStr .= ($apr.',');
            }
            $message = sprintf("近1小时内%s调用异常，调用异常率分别为%s",
                $nameStr,
                $aprStr
            );
            if(YII_ENV_PROD){
                $service->send($message);
            }else{
                $this->printMessage($message);
            }
        }
    }

    ######################Experian报告调用异常END################


    ######################通讯录数据落库异常预警###################

    public function actionAddressBookAbnormalData(){
        if(date('H:i') == '00:00'){
            $this->printMessage('0点跳过');
        }
        $today = strtotime('today');
        $count142 = InfoOrder::find()
            ->alias('o')
            ->leftJoin(RiskResultSnapshot::tableName(). 'r','o.order_id = r.order_id AND o.app_name = r.app_name AND o.user_id = r.user_id')
            ->where(['>=','o.order_time',$today])
            ->andWhere(['<','o.order_time',$today + 86400])
            ->andWhere(['o.is_all_first' => 'y'])
            ->andWhere(['o.app_name' => ['icredit','rupeeplus','needrupee','topcash','cashbowl']])
            ->andWhere('r.base_node->\'$."142"\' < 10')
            ->count();

        if($count142 > 0){
            $count = InfoOrder::find()
                ->where(['>=','order_time',$today])
                ->andWhere(['<','order_time',$today + 86400])
                ->andWhere(['is_all_first' => 'y'])
                ->andWhere(['app_name' => ['icredit','rupeeplus','needrupee','topcash','cashbowl']])
                ->count();


            if($count > 0){
                if(($count142 / $count) >= 0.025){
                    $message = sprintf("今日全新本新通讯录数据落库异常，通讯录号码数量 < 10的订单数占比为%0.2f", ($count142 / $count) * 100).'%';
                    if(YII_ENV_PROD){
                        $service = new WeWorkService();
                        $service->send($message);
                    }else{
                        $this->printMessage($message);
                    }
                }
            }
        }


    }
}

