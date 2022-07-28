<?php

namespace console\controllers;


use backend\models\Merchant;
use callcenter\models\AdminUser;
use callcenter\models\CollectionOrderDispatchLog;
use callcenter\models\CollectorCallData;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionStatusChangeLog;
use callcenter\models\OrderStatistics;
use common\models\ClientInfoLog;
use common\models\manual_credit\ManualCreditLog;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;
use common\models\user\UserActiveTime;
use common\models\user\UserBankAccount;
use common\models\user\UserContact;
use common\models\user\UserRegisterInfo;
use common\models\user\UserVerification;
use common\services\FileStorageService;
use common\services\order\OrderService;

class WangPengController extends BaseController {

    public function actionGetCollection(){
        ini_set('memory_limit', '215M');
        $this->printMessage('start');
        $ossAccessKeyId = 'xxxx';
        $ossAccessKeySecret = 'xxxx';
        $ossBucket = 'ytwl-test';
        $ossEndpoint = 'oss-cn-shanghai.aliyuncs.com';
        $today = strtotime('today');
        $leftTime = $today - 86400 * 17;
        $data = [];
        while ($leftTime < $today){
            $date = date('Y-m-d',$leftTime);
            $this->printMessage($date);
            $list = CollectorCallData::find()
                ->select(['A.date','A.user_id','A.type','A.phone','A.name','A.is_valid','A.times','A.duration'])
                ->from(CollectorCallData::tableName().' A')
                ->leftJoin(AdminUser::tableName().' B','A.user_id = B.id')
                ->where(['A.date' => $date,'B.merchant_id' => 2])
                ->orderBy(['A.id' => SORT_ASC])
                ->asArray()->all();
            if($list){
                foreach ($list as $item){
                    $userId = $item['user_id'];
                    $phone = $item['phone'];
                    $this->printMessage($userId.'_'.$phone);
                    if($item['type'] == $type = CollectorCallData::TYPE_ONE_SELF){
                        $isOneSelfNumber = LoanCollectionOrder::find()
                            ->select(['max_overdue_day' => 'MAX(C.overdue_day)','max_dispatch_time' => 'MAX(C.created_at)'])
                            ->from(LoanCollectionOrder::tableName().' A')
                            ->leftJoin(LoanPerson::getDbName().'.'.LoanPerson::tableName(). ' B','A.user_id = B.id')
                            ->leftJoin(CollectionOrderDispatchLog::tableName(). ' C','A.id = C.collection_order_id')
                            ->where(['C.admin_user_id' => $userId, 'B.phone' => $phone, 'A.merchant_id' => 2])
                            ->andWhere(['<','C.created_at',$leftTime + 86400])
                            ->asArray()->one();
                        if($isOneSelfNumber){
                            $c_overdue_day = ($leftTime - strtotime(date('Y-m-d',$isOneSelfNumber['max_dispatch_time']))) / 86400 + $isOneSelfNumber['max_overdue_day'];
                            $data[] = array_merge($item, ['overdue_day' => $c_overdue_day]);
                        }else{
                            $this->printMessage('本人未匹配');
                        }
                    }elseif ($item['type'] == $type = CollectorCallData::TYPE_CONTACT){
                        $isContactNumber = LoanCollectionOrder::find()
                            ->select(['max_overdue_day' => 'MAX(D.overdue_day)','max_dispatch_time' => 'MAX(D.created_at)'])
                            ->from(LoanCollectionOrder::tableName().' A')
                            ->leftJoin(UserLoanOrderExtraRelation::getDbName().'.'.UserLoanOrderExtraRelation::tableName().' B', 'B.order_id = A.user_loan_order_id')
                            ->leftJoin(UserContact::getDbName().'.'.UserContact::tableName().' C','B.user_contact_id = C.id')
                            ->leftJoin(CollectionOrderDispatchLog::tableName(). ' D','A.id = D.collection_order_id')
                            ->where(['D.admin_user_id' => $userId, 'A.merchant_id' => 2])
                            ->andWhere(['<','D.created_at',$leftTime + 86400])
                            ->andWhere(['OR',['C.phone' => $phone],['C.other_phone' => $phone]])
                            ->asArray()->one();
                        if($isContactNumber){
                            $c_overdue_day = ($leftTime - strtotime(date('Y-m-d',$isContactNumber['max_dispatch_time']))) / 86400 + $isContactNumber['max_overdue_day'];
                            $data[] = array_merge($item, ['overdue_day' => $c_overdue_day]);
                        }else{
                            $this->printMessage('联系人未匹配');
                        }
                    }elseif ($item['type'] == $type = CollectorCallData::TYPE_ADDRESS_BOOK){
                        $this->printMessage('通讯录类型跳过');
                    }
                }
            }
            $leftTime = $leftTime + 86400;
        }

        if($data){
            //上传数据
            $file = 'saas_overdue_day_call' . time() . '.csv';
            $path = '/tmp/';
            $csv_file = $path . $file;
            $this->_array2csv($data, $csv_file);

            $service = new FileStorageService(true,$ossBucket,$ossAccessKeyId,$ossAccessKeySecret,$ossEndpoint);
            $service->uploadFileByPath(
                'india/yunyin/'.$file,
                $csv_file
            );

            $url = 'india/yunyin/'.$file;
            $fileStorageService = new FileStorageService(false,$ossBucket,$ossAccessKeyId,$ossAccessKeySecret,$ossEndpoint);
            $url = $fileStorageService->getSignedUrl($url,86400);
            $html = "moneyclick逾期天数通时通次： <a href='".$url."'>下载</a><br>";

            $to = ["978010084@qq.com"];
            $subject = "moneyclick逾期天数通时通次!";
            $mailer = \Yii::$app->mailer->compose();
            $mailer->setTo($to);
            $mailer->setSubject($subject);
            $mailer->setHtmlBody($html);
            $status = $mailer->send();

            $this->printMessage('end');
        }

    }
//    function actionAddMobileContacts(){
//        $model = new MgUserMobileContacts();
//        $model->_id = '2182_' . md5(4);
//        $model->user_id = 2182;
//        $model->mobile = '9451145555';
//        $model->name = 'nsdjkfds';
//        $model->contactedTimes = 0;
//        $model->contactedLastTime = 0;
//        $model->contactLastUpdatedTimestamp = 1583409216;
//        $model->save();
//    }

    protected function _array2csv(array &$array, $file)
    {
        if (count($array) == 0) {
            return null;
        }
        // set_time_limit(0);//响应时间改为60秒
        // ini_set('memory_limit', '512M');
        ob_start();
        // $df = fopen("php://output", 'w');
        $df = fopen($file, 'w');
        fwrite($df,chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }


    public function actionGetSaasData(){
        ini_set('memory_limit', '512M');
        $this->printMessage('start');
        $limit = 5000;
        $t = 0;
        $data = ['phone_count' => 0,'ver_count' => 0,'apply_count' => 0,'loan_count' => 0];
        $saasUsers = LoanPerson::find()
            ->alias('user')
            ->select(['user.phone','min_created_time' => 'min(user.created_at)'])
            ->leftJoin(UserRegisterInfo::tableName().' reg','user.id = reg.user_id')
            ->where(['NOT LIKE','reg.appMarket','external%',false])
            ->groupBy(['user.phone'])
            ->orderBy(['user.id' => SORT_ASC])
            ->limit($limit)
            ->offset($limit*$t)
            ->asArray()
            ->all();
        while ($saasUsers){
            $this->printMessage($t);
            $phoneArr = array_column($saasUsers,'phone');


            $loanUsers = LoanPerson::find()
                ->select(['phone','min_created_time' => 'min(created_at)'])
                ->where(['phone' => $phoneArr])
                ->groupBy(['phone'])
                ->asArray()
                ->indexBy(['phone'])
                ->all(\Yii::$app->get('db_loan'));

            if($loanUsers){
                $xtPhone = [];
                foreach ($saasUsers as $val){
                    if(isset($loanUsers[$val['phone']]) && $loanUsers[$val['phone']]['min_created_time'] > $val['min_created_time']){
                        $data['phone_count']++;
                        $xtPhone[] = $val['phone'];
                    }
                }

                if($xtPhone){
                    $userVer = UserVerification::find()
                        ->select(['ver_count' => 'count(DISTINCT(user.phone))'])
                        ->alias('ver')
                        ->leftJoin(LoanPerson::tableName().' user','ver.user_id = user.id')
                        ->leftJoin(UserRegisterInfo::tableName().' reg','ver.user_id = reg.user_id')
                        ->where(['user.phone' => $xtPhone,'ver.real_contact_status' => 1])
                        ->andWhere(['NOT LIKE','reg.appMarket','external%',false])
                        ->asArray()
                        ->one();

                    $userVerCount = $userVer['ver_count'] ?? 0;
                    $data['ver_count'] += $userVerCount;


                    $userApply = UserLoanOrder::find()
                        ->select(['apply_count' => 'count(DISTINCT(user.phone))'])
                        ->alias('order')
                        ->leftJoin(LoanPerson::tableName().' user','order.user_id = user.id')
                        ->leftJoin(UserRegisterInfo::tableName().' reg','order.user_id = reg.user_id')
                        ->where(['user.phone' => $xtPhone])
                        ->andWhere(['NOT LIKE','reg.appMarket','external%',false])
                        ->asArray()
                        ->one();

                    $userApplyCount = $userApply['apply_count'] ?? 0;
                    $data['apply_count'] += $userApplyCount;


                    $userLoan = UserLoanOrderRepayment::find()
                        ->select(['loan_count' => 'count(DISTINCT(user.phone))'])
                        ->alias('repayment')
                        ->leftJoin(LoanPerson::tableName().' user','repayment.user_id = user.id')
                        ->leftJoin(UserRegisterInfo::tableName().' reg','repayment.user_id = reg.user_id')
                        ->where(['user.phone' => $xtPhone])
                        ->andWhere(['NOT LIKE','reg.appMarket','external%',false])
                        ->asArray()
                        ->one();

                    $userLoanCount = $userLoan['loan_count'] ?? 0;
                    $data['loan_count'] += $userLoanCount;
                }
            }

            $t++;
            $saasUsers = LoanPerson::find()
                ->alias('user')
                ->select(['user.phone','min_created_time' => 'min(user.created_at)'])
                ->leftJoin(UserRegisterInfo::tableName().' reg','user.id = reg.user_id')
                ->where(['NOT LIKE','reg.appMarket','external%',false])
                ->groupBy(['user.phone'])
                ->orderBy(['user.id' => SORT_ASC])
                ->limit($limit)
                ->offset($limit*$t)
                ->asArray()
                ->all();
        }

        $this->printMessage('end');
        var_dump($data);
    }

    public function actionBb(){
        $order = UserLoanOrder::findOne(64);
        $orderService = new OrderService($order);
        $res = $orderService->autoCheckManual('手动转人工', 1, 2);
        var_dump($res);
    }

    public function actionUpdateCreditLog(){
        $this->printMessage('START');
        $maxId = 92261;
        $query = ManualCreditLog::find()->select(['id','order_id','action','type']);



        $list = $query->where(['>','id',$maxId])->limit(5000)->orderBy(['id' => SORT_ASC])->asArray()->all();
        while ($list){
            $orderIds = [];
            foreach ($list as $value){
                $orderIds[] = $value['order_id'];
            }

            $orderInfo = UserLoanOrder::find()
                ->select(['order.id','user.pan_code','order.is_export','client.package_name','client.app_market','bank.account'])
                ->alias('order')
                ->leftJoin(LoanPerson::tableName().' user','order.user_id = user.id')
                ->leftJoin(UserBankAccount::tableName().' bank','order.card_id = bank.id')
                ->leftJoin(ClientInfoLog::tableName().' client','client.event_id = order.id AND client.event = '.ClientInfoLog::EVENT_APPLY_ORDER)
                ->where(['order.id' => $orderIds])
                ->indexBy(['id'])
                ->asArray()
                ->all();

            foreach ($list as $value){
                $maxId = $value['id'];
                $orderId = $value['order_id'];
                $action = $value['action'];
                $type = $value['type'];
                if(isset($orderInfo[$orderId])){
                    $info = $orderInfo[$orderId];
                    $model = ManualCreditLog::findOne($maxId);
                    $model->pan_code = $info['pan_code'];
                    $model->package_name = $info['is_export'] == UserLoanOrder::IS_EXPORT_YES ? explode('_',$info['app_market'])[1] : $info['package_name'];
                    if($action == ManualCreditLog::ACTION_AUDIT_BANK && $type == ManualCreditLog::TYPE_PASS){
                        $model->bank_account = $info['account'];
                    }
                    $model->save();
                }else{
                    $this->printMessage('不存在order_id:'.$orderId);
                }
            }
            $this->printMessage($maxId);
            $list = $query->where(['>','id',$maxId])->limit(5000)->orderBy(['id' => SORT_ASC])->asArray()->all();

        }

        $this->printMessage('END');
    }


    /**
     * 订单总的状况每日统计
     * @param string $start_time
     */
    public function actionOrderStatistics(){
        if(!$this->lock()){
            return;
        }

        $startTime = strtotime('2020-01-29');
        $endTime = strtotime('2020-12-15');

        while ($startTime <= $endTime)
        {
            $date = date('Y-m-d',$endTime);
            $this->printMessage($date);


            //出催单数
            $loanCollectionStatusChangeLog = LoanCollectionStatusChangeLog::find()
                ->select(['merchant_id','c' => 'COUNT(1)'])
                ->where(['after_status' => LoanCollectionOrder::STATUS_COLLECTION_FINISH])
                ->andWhere(['>=','created_at',$endTime])
                ->andWhere(['<','created_at',$endTime + 86400])
                ->groupBy(['merchant_id'])
                ->indexBy('merchant_id')
                ->asArray()
                ->all(\Yii::$app->db_assist_read);

            $orderStatisticss = OrderStatistics::find()->select(['id'])->where(['date' => $date])->asArray()->all();

            foreach ($orderStatisticss as $value){
                /** @var OrderStatistics $orderStatistics */
                $orderStatistics = OrderStatistics::findOne($value['id']);
                if($orderStatistics && isset($loanCollectionStatusChangeLog[$orderStatistics->merchant_id])){
                    $orderStatistics->repay_num = $loanCollectionStatusChangeLog[$orderStatistics->merchant_id]['c'] ?? 0;
                    $orderStatistics->save();
                }
            }

            $endTime = $endTime - 86400;
        }

    }


    //最近3日访问app未结清
    public function actionAccessAppNoRepay(){
        ini_set('memory_limit', '512M');
        $before3Day = strtotime('today') - 86400 * 3;

        $query = UserLoanOrderRepayment::find()->alias('r')
            ->select(['r.merchant_id','p.phone','r.overdue_day'])
            ->leftJoin(UserActiveTime::tableName().' t', 'r.user_id = t.user_id')
            ->leftJoin(LoanPerson::tableName().' p', 'r.user_id = p.id' )
            ->where([
                'r.status' => UserLoanOrderRepayment::STATUS_NORAML,
                'r.is_overdue' => UserLoanOrderRepayment::IS_OVERDUE_YES,
            ]);

        $dataList = [
            'last_pay' => '最近3日创建支付订单且订单未结清',
            'last_active' => '最近3日进入app，且订单未结清',
        ];
        $html = '';
        foreach ($dataList as $key => $message){
            $this->printMessage($key);
            switch ($key){
                case 'last_pay':
                    $lastPayQuery = clone $query;
                    $result = $lastPayQuery->andWhere(['>','t.last_pay_time', $before3Day])->asArray()->all(\Yii::$app->db_read_1);
                    break;
                case 'last_active':
                    $orderListDetailQuery = clone $query;
                    $result = $orderListDetailQuery->andWhere(['>','t.last_active_time', $before3Day])->asArray()->all(\Yii::$app->db_read_1);
                    break;
                default:
                    continue;
            }
            if($result){
                $this->printMessage('开始写入文件');
                //上传数据
                $file = $key.'_' . time() . '.csv';
                $path = '/tmp/';
                $csv_file = $path . $file;
                $this->_array2csv($result, $csv_file);
                $this->printMessage('开始上传文件');
                $service = new FileStorageService();
                $url = $service->uploadFileByPath(
                    'india/yunyin/'.$file,
                    $csv_file
                );
                $url = $service->getSignedUrl($url,86400);
                $html .= "{$message}： <a href='".$url."'>下载</a><br>";
            }else{
                $this->printMessage($message.':无数据');
            }
        }

        $merchantList = Merchant::find()
            ->select(['id','name'])
            ->where(['status' => Merchant::STATUS_ON])
            ->asArray()
            ->all();

        $html .= "<table cellpadding='0' cellspacing='2' border='1' style='width:600px;text-align:center;font:12px arial;color:#000000;margin:0 auto;'>
<th>merchant_id</th><th>商户名</th></thead>
<tbody>";
        foreach ($merchantList as $v){
            $html .= "<tr><td>{$v['id']}</td><td>{$v['name']}</td></tr>";
        }
        $html .= "</tbody></table>";
        $this->printMessage('开始发送邮箱');
        if(YII_ENV_PROD){
            $to = ['product@vedatlas.com'];
        }else{
            $to = ['978010084@qq.com'];
        }

        $subject = "最近3日访问app未结清!【saas】";
        $mailer = \Yii::$app->mailer->compose();
        $mailer->setTo($to);
        $mailer->setSubject($subject);
        $mailer->setHtmlBody($html);
        $status = $mailer->send();
    }
}

