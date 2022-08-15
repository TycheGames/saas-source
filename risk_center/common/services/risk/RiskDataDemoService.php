<?php
namespace common\services\risk;

use Carbon\Carbon;
use common\helpers\CommonHelper;
use common\helpers\Util;
use common\models\InfoDevice;
use common\models\InfoOrder;
use common\models\InfoRepayment;
use common\models\InfoUser;
use common\models\LoanCollectionRecord;
use common\models\RemindLog;
use common\models\RemindOrder;
use common\models\risk\RiskDataContainer;
use common\models\third_data\ThirdDataGoogleMaps;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * RiskData Demo类
 * Class RiskDataDemoService
 * @package common\services
 * @property  RiskDataContainer $data
 */
class RiskDataDemoService extends RiskDataDemoServiceBase
{
    /**
     * 全平台催收的总订单量
     * @return int
     */
    public function checkCollectionCntTPF(){
        $count = LoanCollectionRecord::find()
            ->where(['pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->groupBy(['order_id', 'app_name'])
            ->count();

        return $count;
    }

    /**
     * 全平台电话催收的订单量
     * @return int
     */
    public function checkTeleCollectionCntTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->groupBy(['order_id', 'app_name'])
            ->count();

        return $count;
    }

    /**
     * 全平台短信催收的订单量
     * @return int
     */
    public function checkSMSCollectionCntTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 2
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->groupBy(['order_id', 'app_name'])
            ->count();

        return $count;
    }

    /**
     * 全平台催收的总次数
     * @return int
     */
    public function checkCollectionTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where(['pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台电话催收的次数
     * @return int
     */
    public function checkTeleCollectionTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台短信催收的次数
     * @return int
     */
    public function checkSMSCollectionTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 2
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收订单的平均催收次数(催收次数/催收订单量)
     * @return int
     */
    public function checkCollectionAvgTimesTPF(){
        $total = $this->checkCollectionCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkCollectionTimesTPF();

        return round($count / $total, 2);
    }

    /**
     * 全平台电话催收订单的平均催收次数(电话催收次数/电话催收订单量)
     * @return int
     */
    public function checkTeleCollectionAvgTimesTPF(){
        $total = $this->checkTeleCollectionCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionTimesTPF();

        return round($count / $total, 2);
    }

    /**
     * 全平台短信催收订单的平均催收次数(短信催收次数/短信催收订单量)
     * @return int
     */
    public function checkSMSCollectionAvgTimesTPF(){
        $total = $this->checkSMSCollectionCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkSMSCollectionTimesTPF();

        return round($count / $total, 2);
    }

    /**
     * 全平台电话催收次数的占比
     * @return int
     */
    public function checkTeleCollectionRateTPF(){
        $total = $this->checkCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台短信催收次数的占比
     * @return int
     */
    public function checkSMSCollectionRateTPF(){
        $total = $this->checkCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkSMSCollectionTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台单笔催收订单的最大催收次数
     * @return int
     */
    public function checkSingleCollectionMaxTimesTPF(){
        $data = LoanCollectionRecord::find()
            ->select(['count(id) as count'])
            ->where(['pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->groupBy(['order_id', 'app_name'])
            ->asArray()
            ->all();
        $arr = [0];
        foreach ($data as $v){
            $arr[] = $v['count'];
        }

        return max($arr);
    }

    /**
     * 全平台单笔电话催收订单的最大催收次数
     * @return int
     */
    public function checkSingleTeleCollectionMaxTimesTPF(){
        $data = LoanCollectionRecord::find()
            ->select(['count(id) as count'])
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->groupBy(['order_id', 'app_name'])
            ->asArray()
            ->all();
        $arr = [0];
        foreach ($data as $v){
            $arr[] = $v['count'];
        }

        return max($arr);
    }

    /**
     * 全平台单笔短信催收订单的最大催收次数
     * @return int
     */
    public function checkSingleSMSCollectionMaxTimesTPF(){
        $data = LoanCollectionRecord::find()
            ->select(['count(id) as count'])
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 2
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->groupBy(['order_id', 'app_name'])
            ->asArray()
            ->all();
        $arr = [0];
        foreach ($data as $v){
            $arr[] = $v['count'];
        }

        return max($arr);
    }

    /**
     * 全平台催收的订单下的电话接通次数
     * @return int
     */
    public function checkTeleCollectionConnectTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'is_connect' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收的订单下的电话未接通次数
     * @return int
     */
    public function checkTeleCollectionNotConnectTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'is_connect' => 2
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收的订单下的电话接通次数占比
     * @return int
     */
    public function checkTeleCollectionConnectRateTPF(){
        $total = $this->checkTeleCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionConnectTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收的订单下的电话未接通次数占比
     * @return int
     */
    public function checkTeleCollectionNotConnectRateTPF(){
        $total = $this->checkTeleCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionNotConnectTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收中的联系人是自己的次数
     * @return int
     */
    public function checkTeleCollectionSelfTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 0
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收中的联系人是自己的占比
     * @return int
     */
    public function checkTeleCollectionSelfRateTPF(){
        $total = $this->checkCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionSelfTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收中的联系人是自己且接通的次数
     * @return int
     */
    public function checkTeleCollectionSelfAndConnectTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 0,
                'is_connect' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收中的联系人是自己且未接通的次数
     * @return int
     */
    public function checkTeleCollectionSelfAndNotConnectTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 0,
                'is_connect' => 2
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收中的联系人是自己且接通的占比
     * @return int
     */
    public function checkTeleCollectionSelfAndConnectRateTPF(){
        $total = $this->checkCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionSelfAndConnectTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收中的联系人是自己且未接通的占比
     * @return int
     */
    public function checkTeleCollectionSelfAndNotConnectRateTPF(){
        $total = $this->checkCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionSelfAndNotConnectTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收中的联系人是亲人的次数
     * @return int
     */
    public function checkTeleCollectionRelativeTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收中的联系人是亲人的占比
     * @return int
     */
    public function checkTeleCollectionRelativeRateTPF(){
        $total = $this->checkCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionRelativeTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收中的联系人是亲人且接通的次数
     * @return int
     */
    public function checkTeleCollectionRelativeAndConnectTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 1,
                'is_connect' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收中的联系人是亲人且未接通的次数
     * @return int
     */
    public function checkTeleCollectionRelativeAndNotConnectTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 1,
                'is_connect' => 2
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收中的联系人是亲人且接通的占比
     * @return int
     */
    public function checkTeleCollectionRelativeAndConnectRateTPF(){
        $total = $this->checkCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionRelativeAndConnectTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收中的联系人是亲人且未接通的占比
     * @return int
     */
    public function checkTeleCollectionRelativeAndNotConnectRateTPF(){
        $total = $this->checkCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionRelativeAndNotConnectTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收中的联系人是其它的次数
     * @return int
     */
    public function checkTeleCollectionOtherTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 2
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收中的联系人是其它的占比
     * @return int
     */
    public function checkTeleCollectionOtherRateTPF(){
        $total = $this->checkCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionOtherTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收中的联系人是其它且接通的次数
     * @return int
     */
    public function checkTeleCollectionOtherAndConnectTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 2,
                'is_connect' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收中的联系人是其它且未接通的次数
     * @return int
     */
    public function checkTeleCollectionOtherAndNotConnectTimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 2,
                'is_connect' => 2
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台催收中的联系人是其它且接通的占比
     * @return int
     */
    public function checkTeleCollectionOtherAndConnectRateTPF(){
        $total = $this->checkCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionOtherAndConnectTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收中的联系人是其它且未接通的占比
     * @return int
     */
    public function checkTeleCollectionOtherAndNotConnectRateTPF(){
        $total = $this->checkCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionOtherAndNotConnectTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台接通状态下的联系人是自己的承诺还款次数
     * @return int
     */
    public function checkTeleCollectionConnectAndSelfRiskControl1TimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 0,
                'is_connect' => 1,
                'risk_control' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->andWhere(['>', 'promise_repayment_time', 0])
            ->count();

        return $count;
    }

    /**
     * 全平台接通状态下的联系人是自己的承诺还款次数占联系人是自己的催收次数的占比
     * @return int
     */
    public function checkTeleCollectionConnectAndSelfRiskControl1RateTPF(){
        $total = $this->checkTeleCollectionSelfTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionConnectAndSelfRiskControl1TimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台接通状态下的联系人是自己的有意向还款次数
     * @return int
     */
    public function checkTeleCollectionConnectAndSelfRiskControl2TimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 0,
                'is_connect' => 1,
                'risk_control' => 2
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台接通状态下的联系人是自己的有意向还款次数占联系人是自己的催收次数的占比
     * @return int
     */
    public function checkTeleCollectionConnectAndSelfRiskControl2RateTPF(){
        $total = $this->checkTeleCollectionSelfTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionConnectAndSelfRiskControl2TimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台接通状态下的联系人是自己的无力还款次数
     * @return int
     */
    public function checkTeleCollectionConnectAndSelfRiskControl3TimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 0,
                'is_connect' => 1,
                'risk_control' => 3
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台接通状态下的联系人是自己的无力还款次数占联系人是自己的催收次数的占比
     * @return int
     */
    public function checkTeleCollectionConnectAndSelfRiskControl3RateTPF(){
        $total = $this->checkTeleCollectionSelfTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionConnectAndSelfRiskControl3TimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台接通状态下的联系人是自己的拒绝还款次数
     * @return int
     */
    public function checkTeleCollectionConnectAndSelfRiskControl4TimesTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 0,
                'is_connect' => 1,
                'risk_control' => 4
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台接通状态下的联系人是自己的拒绝还款次数占联系人是自己的催收次数的占比
     * @return int
     */
    public function checkTeleCollectionConnectAndSelfRiskControl4RateTPF(){
        $total = $this->checkTeleCollectionSelfTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionConnectAndSelfRiskControl4TimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收订单的联系人是自己的承诺还款的订单量
     * @return int
     */
    public function checkTeleCollectionConnectAndSelfRiskControl1OrderCntTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 0,
                'is_connect' => 1,
                'risk_control' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->andWhere(['>', 'promise_repayment_time', 0])
            ->groupBy(['order_id', 'app_name'])
            ->count();

        return $count;
    }

    /**
     * 全平台联系人是自己的承诺还款且已还款的订单量占联系人是自己的承诺还款订单量的比例
     * @return int
     */
    public function checkTeleCollectionConnectAndSelfRiskControl1RepayRateTPF(){
        $total = $this->checkTeleCollectionConnectAndSelfRiskControl1OrderCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = LoanCollectionRecord::find()
            ->alias('c')
            ->leftJoin(InfoRepayment::tableName().' as r', 'r.user_id=c.user_id and r.order_id=c.order_id and r.app_name=c.app_name')
            ->where([
                'c.pan_code' => $this->data->infoUser->pan_code,
                'c.operate_type' => 1,
                'c.contact_type' => 0,
                'c.is_connect' => 1,
                'c.risk_control' => 1
            ])
            ->andWhere(['<', 'c.operate_at', $this->data->infoOrder->order_time])
            ->andWhere(['>', 'c.promise_repayment_time', 0])
            ->andWhere(['>', 'r.closing_time', 0])
            ->andWhere(['<', 'r.closing_time', $this->data->infoOrder->order_time])
            ->groupBy(['c.order_id', 'c.app_name'])
            ->count();

        return intval($count / $total * 100);
    }

    /**
     * 全平台催收订单的联系人是自己的承诺还款的订单量占联系人是自己的催收订单量的比例
     * @return int
     */
    public function checkTeleCollectionConnectAndSelfRiskControl1OrderCntRateTPF(){
        $total = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'contact_type' => 0
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->groupBy(['order_id', 'app_name'])
            ->count();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionConnectAndSelfRiskControl1OrderCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台未接通状态下的无应答次数
     * @return int
     */
    public function checkTeleCollectionNotConnectAndRiskControl11CntTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'is_connect' => 2,
                'risk_control' => 11
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台未接通状态下的无应答次数占催收次数的占比
     * @return int
     */
    public function checkTeleCollectionNotConnectAndRiskControl11RateTPF(){
        $total = $this->checkTeleCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionNotConnectAndRiskControl11CntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台未接通状态下的关机或空号次数
     * @return int
     */
    public function checkTeleCollectionNotConnectAndRiskControl12CntTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'is_connect' => 2,
                'risk_control' => 12
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台未接通状态下的关机或空号次数占催收次数的占比
     * @return int
     */
    public function checkTeleCollectionNotConnectAndRiskControl12RateTPF(){
        $total = $this->checkTeleCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionNotConnectAndRiskControl12CntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台联系人是自己的未接通状态下的无应答次数
     * @return int
     */
    public function checkTeleCollectionNotConnectAndSelfRiskControl11CntTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'is_connect' => 2,
                'risk_control' => 11,
                'contact_type' => 0
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台联系人是自己的未接通状态下的无应答次数占联系人是自己的催收次数的占比
     * @return int
     */
    public function checkTeleCollectionNotConnectAndSelfRiskControl11RateTPF(){
        $total = $this->checkTeleCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionNotConnectAndSelfRiskControl11CntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台联系人是自己的未接通状态下的关机或空号次数
     * @return int
     */
    public function checkTeleCollectionNotConnectAndSelfRiskControl12CntTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'is_connect' => 2,
                'risk_control' => 12,
                'contact_type' => 0
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台联系人是自己的未接通状态下的关机或空号次数占联系人是自己的催收次数的占比
     * @return int
     */
    public function checkTeleCollectionNotConnectAndSelfRiskControl12RateTPF(){
        $total = $this->checkTeleCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionNotConnectAndSelfRiskControl12CntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台联系人是亲人的未接通状态下的无应答次数
     * @return int
     */
    public function checkTeleCollectionNotConnectAndRelativeRiskControl11CntTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'is_connect' => 2,
                'risk_control' => 11,
                'contact_type' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台联系人是亲人的未接通状态下的无应答次数占联系人是亲人的催收次数的占比
     * @return int
     */
    public function checkTeleCollectionNotConnectAndRelativeRiskControl11RateTPF(){
        $total = $this->checkTeleCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionNotConnectAndRelativeRiskControl11CntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台联系人是亲人的未接通状态下的关机或空号次数
     * @return int
     */
    public function checkTeleCollectionNotConnectAndRelativeRiskControl12CntTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'is_connect' => 2,
                'risk_control' => 12,
                'contact_type' => 1
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台联系人是亲人的未接通状态下的关机或空号次数占联系人是亲人的催收次数的占比
     * @return int
     */
    public function checkTeleCollectionNotConnectAndRelativeRiskControl12RateTPF(){
        $total = $this->checkTeleCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionNotConnectAndRelativeRiskControl12CntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台联系人是它人的未接通状态下的无应答次数
     * @return int
     */
    public function checkTeleCollectionNotConnectAndOtherRiskControl11CntTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'is_connect' => 2,
                'risk_control' => 11,
                'contact_type' => 2
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台联系人是它人的未接通状态下的无应答次数占联系人是它人的催收次数的占比
     * @return int
     */
    public function checkTeleCollectionNotConnectAndOtherRiskControl11RateTPF(){
        $total = $this->checkTeleCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionNotConnectAndOtherRiskControl11CntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台联系人是它人的未接通状态下的关机或空号次数
     * @return int
     */
    public function checkTeleCollectionNotConnectAndOtherRiskControl12CntTPF(){
        $count = LoanCollectionRecord::find()
            ->where([
                'pan_code' => $this->data->infoUser->pan_code,
                'operate_type' => 1,
                'is_connect' => 2,
                'risk_control' => 12,
                'contact_type' => 2
            ])
            ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台联系人是它人的未接通状态下的关机或空号次数占联系人是它人的催收次数的占比
     * @return int
     */
    public function checkTeleCollectionNotConnectAndOtherRiskControl12RateTPF(){
        $total = $this->checkTeleCollectionTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkTeleCollectionNotConnectAndOtherRiskControl12CntTPF();

        return intval($count / $total * 100);
    }

    /**
     * @return array
     */
    protected function getTeleCollectionTime(){
        if(!is_null($this->teleCollectionTime)){
            return $this->teleCollectionTime;
        }else{
            $data = LoanCollectionRecord::find()
                ->select(['r.plan_repayment_time', 'max(c.operate_at) as operate_at', 'min(c.operate_at) as first_time'])
                ->alias('c')
                ->leftJoin(InfoRepayment::tableName().' as r', 'r.user_id=c.user_id and r.order_id=c.order_id and r.app_name=c.app_name')
                ->where([
                    'c.pan_code' => $this->data->infoUser->pan_code,
                ])
                ->andWhere(['<', 'c.operate_at', $this->data->infoOrder->order_time])
                ->groupBy(['c.order_id', 'c.app_name'])
                ->asArray()
                ->all();
            $arr = [];
            foreach ($data as $v){
                if(!empty($v['plan_repayment_time'])){
                    $arr[] = (strtotime(date('Y-m-d', $v['operate_at'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
                }else{
                    $arr[] = (strtotime(date('Y-m-d', $v['operate_at'])) - strtotime(date('Y-m-d', $v['first_time'])))/86400;
                }
            }

            $this->teleCollectionTime = $arr;
            return $this->teleCollectionTime;
        }
    }

    /**
     * 全平台催收订单的最大催收时长
     * @return int
     */
    public function checkTeleCollectionMaxTimeTPF(){
        $arr = $this->getTeleCollectionTime();

        if(empty($arr)){
            return -1;
        }

        return max($arr);
    }

    /**
     * 全平台催收订单的最小催收时长
     * @return int
     */
    public function checkTeleCollectionMinTimeTPF(){
        $arr = $this->getTeleCollectionTime();

        if(empty($arr)){
            return -1;
        }

        return min($arr);
    }

    /**
     * 全平台催收订单的平均催收时长
     * @return int
     */
    public function checkTeleCollectionAvgTimeTPF(){
        $arr = $this->getTeleCollectionTime();

        if(empty($arr)){
            return -1;
        }

        return round(array_sum($arr) / count($arr), 2);
    }

    /**
     * 全平台催收订单的催收时长的和
     * @return int
     */
    public function checkTeleCollectionSumTimeTPF(){
        $arr = $this->getTeleCollectionTime();

        if(empty($arr)){
            return -1;
        }

        return array_sum($arr);
    }

    /**
     * @return array
     */
    protected function getTeleCollectionLevel(){
        if(!is_null($this->teleCollectionLevel)){
            return $this->teleCollectionLevel;
        }else{
            $data = ArrayHelper::getColumn(
                LoanCollectionRecord::find()
                    ->select(['max(order_level) as order_level'])
                    ->where([
                        'pan_code' => $this->data->infoUser->pan_code,
                    ])
                    ->andWhere(['<', 'operate_at', $this->data->infoOrder->order_time])
                    ->groupBy(['order_id', 'app_name'])
                    ->asArray()
                    ->all(),
                'order_level'
            );

            $this->teleCollectionLevel = $data;
            return $this->teleCollectionLevel;
        }
    }

    /**
     * 全平台最大催收等级
     * @return int
     */
    public function checkTeleCollectionMaxLevelTPF(){
        $data = $this->getTeleCollectionLevel();

        if(empty($data)){
            return -1;
        }

        return max($data);
    }

    /**
     * 全平台最小催收等级
     * @return int
     */
    public function checkTeleCollectionMinLevelTPF(){
        $data = $this->getTeleCollectionLevel();

        if(empty($data)){
            return -1;
        }

        return min($data);
    }

    /**
     * 全平台平均催收等级
     * @return int
     */
    public function checkTeleCollectionAvgLevelTPF(){
        $data = $this->getTeleCollectionLevel();

        if(empty($data)){
            return -1;
        }

        return round(array_sum($data) / count($data), 2);
    }

    /**
     * 全平台催收等级的和
     * @return int
     */
    public function checkTeleCollectionSumLevelTPF(){
        $data = $this->getTeleCollectionLevel();

        if(empty($data)){
            return -1;
        }

        return array_sum($data);
    }

    /**
     * 全新本新用户模型分V5
     */
    public function checkQXBXUserModelV5(){
        $this->isGetData = false;
        $score = 0;
        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 4:
                $score += 22;
                break;
            case $v103 < 5:
                $score += 35;
                break;
            case $v103 < 6:
                $score += 48;
                break;
            case $v103 >= 6:
                $score += 60;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 28000:
                $score += 34;
                break;
            case $v206 < 42000:
                $score += 52;
                break;
            case $v206 < 51000:
                $score += 60;
                break;
            case $v206 >= 51000:
                $score += 88;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 100:
                $score += 15;
                break;
            case $v142 < 250:
                $score += 30;
                break;
            case $v142 < 650:
                $score += 41;
                break;
            case $v142 >= 650:
                $score += 57;
                break;
        }

        $v1219 = $this->checkIsBangaloreExperianCreditReportReturnedNormal();
        if($v1219 != 1){
            $score += 246;
        }else{
            $v1223 = $this->checkBangaloreExperianCreditAccountClosed();
            switch (true){
                case $v1223 < 0:
                    $score += 4;
                    break;
                case $v1223 < 9:
                    $score += 35;
                    break;
                case $v1223 < 13:
                    $score += 65;
                    break;
                case $v1223 < 23:
                    $score += 81;
                    break;
                case $v1223 >= 23:
                    $score += 143;
                    break;
            }

            $v1231 = $this->checkBangaloreExperianLast30dEnquiryCnt();
            switch (true){
                case $v1231 < 1:
                    $score += -3;
                    break;
                case $v1231 < 2:
                    $score += 58;
                    break;
                case $v1231 >= 2:
                    $score += 88;
                    break;
            }

            $v1237 = $this->checkBangaloreExperianHisMaxCreditAmt();
            switch (true){
                case $v1237 < 110000:
                    $score += 31;
                    break;
                case $v1237 < 270000:
                    $score += 55;
                    break;
                case $v1237 >= 270000:
                    $score += 70;
                    break;
            }

            $v1244 = $this->checkBangaloreExperianTimeOfFirstCreditTimeToNow();
            switch (true){
                case $v1244 < 1300:
                    $score += 35;
                    break;
                case $v1244 < 2000:
                    $score += 47;
                    break;
                case $v1244 < 2700:
                    $score += 66;
                    break;
                case $v1244 >= 2700:
                    $score += 57;
                    break;
            }

            $v1254 = $this->checkBangaloreExperianCreditScore();
            switch (true){
                case $v1254 < 710:
                    $score += 5;
                    break;
                case $v1254 < 810:
                    $score += 63;
                    break;
                case $v1254 >= 810:
                    $score += 91;
                    break;
            }

            $v1253 = $this->checkBangaloreExperianTimeOfLastPayMent();
            switch (true){
                case $v1253 < 0:
                    $score += 26;
                    break;
                case $v1253 < 70:
                    $score += 64;
                    break;
                case $v1253 < 80:
                    $score += 51;
                    break;
                case $v1253 < 140:
                    $score += 38;
                    break;
                case $v1253 >= 140:
                    $score += 25;
                    break;
            }

            $v1225 = $this->checkBangaloreExperianOutstandingBalanceSecuredPercentage();
            switch (true){
                case $v1225 < 0:
                    $score += 40;
                    break;
                case $v1225 < 26:
                    $score += 78;
                    break;
                case $v1225 < 48:
                    $score += 66;
                    break;
                case $v1225 < 96:
                    $score += 43;
                    break;
                case $v1225 >= 96:
                    $score += 26;
                    break;
            }
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 == 0){
            $score += 99;
        }else{
            $v1056 = $this->checkMaxOfSMSEMIAmtLast90DaysTPF();
            switch (true){
                case $v1056 < 0:
                    $score += 32;
                    break;
                case $v1056 < 5000:
                    $score += 39;
                    break;
                case $v1056 >= 5000:
                    $score += 62;
                    break;
            }

            $v968 = $this->checkSMSCntOfLoanApplicationSubmissionLast90DaysTPF();
            switch (true){
                case $v968 < 1:
                    $score += 31;
                    break;
                case $v968 < 4:
                    $score += 34;
                    break;
                case $v968 < 6:
                    $score += 56;
                    break;
                case $v968 >= 6:
                    $score += 88;
                    break;
            }
        }

        $v709 = $this->checkDateDiffOfOrderAndLastOrderApplyByPanTotPlatform();
        switch (true){
            case $v709 < 0:
                $score += 58;
                break;
            case $v709 < 22:
                $score += 45;
                break;
            case $v709 >= 22:
                $score += -4;
                break;
        }

        return $score;
    }

    /**
     * 本笔订单的下单appMarket
     * @return string
     */
    public function checkAppMarketOfOrder(){
        if(empty($this->data->infoDevice->app_market)){
            return -1;
        }

        return strtolower($this->data->infoDevice->app_market);
    }

    /**
     * 本笔订单用户的注册来源appMarket
     * @return string
     */
    public function checkAppMarketOfRegister(){
        if(empty($this->data->infoUser->app_market)){
            return -1;
        }

        return strtolower($this->data->infoUser->app_market);
    }

    /**
     * 本笔订单用户的注册来源mediaSource
     * @return string
     */
    public function checkMediaSourceOfRegister(){
        if(empty($this->data->infoUser->media_source)){
            return 'organic';
        }

        if($this->data->infoUser->media_source == 'external'){
            return 'external_organic';
        }

        return strtolower($this->data->infoUser->media_source);
    }

    /**
     * 全平台-近30天提前还款订单数量
     * @return int
     */
    public function checkLast30dTiqianOrderCntTPF(){
        $begin_time = strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - 30 * 86400;
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.order_id=u.order_id and r.user_id=u.user_id and r.app_name=u.app_name')
            ->select(['r.is_overdue'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'r.closing_time', $begin_time])
            ->andWhere(['<', 'r.closing_time', $this->data->infoOrder->order_time])
            ->asArray()
            ->all();

        $count = 0;
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_NO){
                $count++;
            }
        }

        return $count;
    }

    /**
     * 全老本新用户模型分V5
     * @return int
     */
    public function checkQLBXUserModelV5(){
        $score = 0;

        $v141 = $this->checkContactNameMobileHitOverdueUserContactMobileCnt();
        switch (true){
            case $v141 < 1:
                $score += 83;
                break;
            case $v141 >= 1:
                $score += 35;
                break;
        }

        $v705 = $this->checkHisLoanCntByPanTotPlatform();
        switch (true){
            case $v705 < 3:
                $score += 44;
                break;
            case $v705 < 10:
                $score += 56;
                break;
            case $v705 < 14:
                $score += 80;
                break;
            case $v705 >= 14:
                $score += 123;
                break;
        }

        $v101 = $this->checkUserAge();
        switch (true){
            case $v101 < 24:
                $score += 53;
                break;
            case $v101 < 25:
                $score += 27;
                break;
            case $v101 < 30:
                $score += 54;
                break;
            case $v101 < 35:
                $score += 59;
                break;
            case $v101 >= 35:
                $score += 82;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 == 0){
            $score += 341;
        }else{
            $v993 = $this->checkSMSCntOfLoanPayOffLast90DaysTPF();
            switch (true){
                case $v993 < 1:
                    $score += 40;
                    break;
                case $v993 < 4:
                    $score += 62;
                    break;
                case $v993 < 5:
                    $score += 94;
                    break;
                case $v993 >= 5:
                    $score += 130;
                    break;
            }

            $v969 = $this->checkHistSMSCntOfLoanRejectionTPF();
            switch (true){
                case $v969 < 9:
                    $score += 67;
                    break;
                case $v969 < 13:
                    $score += 51;
                    break;
                case $v969 < 24:
                    $score += 41;
                    break;
                case $v969 >= 24:
                    $score += 10;
                    break;
            }

            $v967 = $this->checkSMSCntOfLoanApplicationSubmissionLast60DaysTPF();
            switch (true){
                case $v967 < 1:
                    $score += 49;
                    break;
                case $v967 < 2:
                    $score += 57;
                    break;
                case $v967 < 3:
                    $score += 64;
                    break;
                case $v967 >= 3:
                    $score += 73;
                    break;
            }

            $v995 = $this->checkSMSCntOfOverdueRemindLast7DaysTPF();
            switch (true){
                case $v995 < 2:
                    $score += 64;
                    break;
                case $v995 < 3:
                    $score += 56;
                    break;
                case $v995 >= 3:
                    $score += 34;
                    break;
            }
        }

        $v1344 = $this->checkLast30dTiqianOrderCntTPF();
        switch (true){
            case $v1344 < 1:
                $score += 39;
                break;
            case $v1344 < 2:
                $score += 81;
                break;
            case $v1344 < 3:
                $score += 96;
                break;
            case $v1344 >= 3:
                $score += 135;
                break;
        }

        $this->isGetData = false;
        $v1219 = $this->checkIsBangaloreExperianCreditReportReturnedNormal();
        if($v1219 != 1){
            $score += 97;
        }else{
            $v1230 = $this->checkBangaloreExperianLast90dEnquiryCnt();
            switch (true){
                case $v1230 < 3:
                    $score += 48;
                    break;
                case $v1230 < 4:
                    $score += 54;
                    break;
                case $v1230 < 7:
                    $score += 63;
                    break;
                case $v1230 >= 7:
                    $score += 81;
                    break;
            }

            $v1231 = $this->checkBangaloreExperianLast30dEnquiryCnt();
            switch (true){
                case $v1231 < 0:
                    $score += 43;
                    break;
                case $v1231 < 2:
                    $score += 61;
                    break;
                case $v1231 < 4:
                    $score += 72;
                    break;
                case $v1231 >= 4:
                    $score += 89;
                    break;
            }
        }

        return $score;
    }

    /**
     * 全平台进入提醒还款订单量
     * @return int
     */
    public function checkRemindCntTPF(){
        return RemindOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName(). 'as u', 'o.app_name=u.app_name and o.user_id=u.user_id and o.order_id=u.order_id')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['<', 'o.updated_at', $this->data->infoOrder->order_time])
            ->count();
    }

    /**
     * 全平台提醒还款订单量占应还款订单量的比例
     * @return int
     */
    public function checkRemindCntPlanRepaymentCntRate(){
        $total = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName(). ' as u', 'u.app_name=r.app_name and u.order_id=r.order_id and u.user_id=r.user_id')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->count();

        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台进入提醒还款且被提醒订单量
     * @return int
     */
    public function checkRemindedCntTPF(){
        return RemindOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName(). 'as u', 'o.app_name=u.app_name and o.user_id=u.user_id and o.order_id=u.order_id')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code, 'o.status' => RemindOrder::STATUS_REMINDED])
            ->andWhere(['<', 'o.updated_at', $this->data->infoOrder->order_time])
            ->count();
    }

    /**
     * 全平台进入提醒还款但没被提醒的订单量
     * @return int
     */
    public function checkUnRemindedCntTPF(){
        return RemindOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName(). 'as u', 'o.app_name=u.app_name and o.user_id=u.user_id and o.order_id=u.order_id')
            ->where(['u.pan_code' => $this->data->infoUser->pan_code, 'o.status' => RemindOrder::STATUS_WAIT_REMIND])
            ->andWhere(['<', 'o.updated_at', $this->data->infoOrder->order_time])
            ->count();
    }

    /**
     * 全平台进入提醒还款且被提醒订单量的比例
     * @return int
     */
    public function checkRemindedRateTPF(){
        $total = $this->checkRemindCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedCntTPF();
        return intval($count / $total * 100);
    }

    /**
     * 全平台进入提醒还款但没被提醒的订单量的比例
     * @return int
     */
    public function checkUnRemindedRateTPF(){
        $total = $this->checkRemindCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkUnRemindedCntTPF();
        return intval($count / $total * 100);
    }

    /**
     * 全平台进入提醒还款且被提醒的次数
     * @return int
     */
    public function checkRemindedTimesTPF(){
        $data = RemindOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName(). 'as u', 'o.app_name=u.app_name and o.user_id=u.user_id and o.order_id=u.order_id')
            ->select(['o.remind_count as count'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code, 'o.status' => RemindOrder::STATUS_REMINDED])
            ->andWhere(['<', 'o.updated_at', $this->data->infoOrder->order_time])
            ->asArray()
            ->all();

        $arr = [0];
        foreach ($data as $v){
            $arr[] = $v['count'];
        }

        return array_sum($arr);
    }

    /**
     * 全平台提醒还款且被提醒的单笔平均次数
     * @return false|float|int
     */
    public function checkRemindedAvgTimesTPF(){
        $total = $this->checkRemindedCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedTimesTPF();

        return round($count / $total, 2);
    }

    /**
     * 全平台提醒还款且被提醒的单笔最大提醒次数
     * @return int
     */
    public function checkRemindedMaxTimesTPF(){
        $data = RemindOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName(). 'as u', 'o.app_name=u.app_name and o.user_id=u.user_id and o.order_id=u.order_id')
            ->select(['o.remind_count as count'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code, 'o.status' => RemindOrder::STATUS_REMINDED])
            ->andWhere(['<', 'o.updated_at', $this->data->infoOrder->order_time])
            ->asArray()
            ->all();

        $arr = [0];
        foreach ($data as $v){
            $arr[] = $v['count'];
        }

        return max($arr);
    }

    /**
     * 全平台提醒还款且被提醒的单笔最小提醒次数
     * @return int
     */
    public function checkRemindedMinTimesTPF(){
        $data = RemindOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName(). 'as u', 'o.app_name=u.app_name and o.user_id=u.user_id and o.order_id=u.order_id')
            ->select(['o.remind_count as count'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code, 'o.status' => RemindOrder::STATUS_REMINDED])
            ->andWhere(['<', 'o.updated_at', $this->data->infoOrder->order_time])
            ->asArray()
            ->all();

        $arr = [0];
        foreach ($data as $v){
            $arr[] = $v['count'];
        }

        return min($arr);
    }

    /**
     * 全平台进入提醒还款且被提醒且正常还款的订单量
     * @return int
     */
    public function checkRemindedAndNormalRepaymentCntTPF(){
        return RemindOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName(). 'as u', 'o.app_name=u.app_name and o.user_id=u.user_id and o.order_id=u.order_id')
            ->leftJoin(InfoRepayment::tableName().' as r', 'r.app_name=o.app_name and r.order_id=o.order_id and r.user_id=o.user_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'o.status' => RemindOrder::STATUS_REMINDED,
                'r.status' => InfoRepayment::STATUS_CLOSED,
                'r.is_overdue' => InfoRepayment::OVERDUE_NO
            ])
            ->andWhere(['<', 'o.updated_at', $this->data->infoOrder->order_time])
            ->count();
    }

    /**
     * 全平台进入提醒还款且被提醒且逾期还款的订单量
     * @return int
     */
    public function checkRemindedAndOverdueCntTPF(){
        return RemindOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName(). 'as u', 'o.app_name=u.app_name and o.user_id=u.user_id and o.order_id=u.order_id')
            ->leftJoin(InfoRepayment::tableName().' as r', 'r.app_name=o.app_name and r.order_id=o.order_id and r.user_id=o.user_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'o.status' => RemindOrder::STATUS_REMINDED,
                'r.status' => InfoRepayment::STATUS_CLOSED,
                'r.is_overdue' => InfoRepayment::OVERDUE_YES
            ])
            ->andWhere(['<', 'o.updated_at', $this->data->infoOrder->order_time])
            ->count();
    }

    /**
     * 全平台进入提醒还款且被提醒且正常还款的订单量的比例
     * @return int
     */
    public function checkRemindedAndNormalRepaymentRateTPF(){
        $total = $this->checkRemindedCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedAndNormalRepaymentCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的触达的订单量
     * @return int
     */
    public function checkRemindedReturnCntTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->andWhere(['>', 'l.remind_return', 0])
            ->groupBy(['l.app_name', 'l.order_id'])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的触达下已完成还款的订单量
     * @return int
     */
    public function checkRemindedReturnAndRepayCntTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => 1
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->groupBy(['l.app_name', 'l.order_id'])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的触达下承诺还款的订单量
     * @return int
     */
    public function checkRemindedReturnPromiseRepayCntTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => [2, 3]
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->groupBy(['l.app_name', 'l.order_id'])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的触达下未承诺还款的订单量
     * @return int
     */
    public function checkRemindedReturnUnPromiseRepayCntTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => [4, 5]
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->groupBy(['l.app_name', 'l.order_id'])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的触达下承诺还款且正常还款的订单量
     * @return int
     */
    public function checkRemindedReturnAndPromiseRepayAndNormalRepayCntTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->leftJoin(InfoRepayment::tableName().' as r', 'r.app_name=l.app_name and r.order_id=l.order_id and r.user_id=l.user_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => [2, 3],
                'r.status' => InfoRepayment::STATUS_CLOSED,
                'r.is_overdue' => InfoRepayment::OVERDUE_NO
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->groupBy(['l.app_name', 'l.order_id'])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的触达下承诺还款且逾期还款的订单量
     * @return int
     */
    public function checkRemindedReturnAndPromiseRepayAndOverdueRepayCntTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->leftJoin(InfoRepayment::tableName().' as r', 'r.app_name=l.app_name and r.order_id=l.order_id and r.user_id=l.user_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => [2, 3],
                'r.status' => InfoRepayment::STATUS_CLOSED,
                'r.is_overdue' => InfoRepayment::OVERDUE_YES
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->groupBy(['l.app_name', 'l.order_id'])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的触达下承诺还款且正常还款的订单量占比
     * @return int
     */
    public function checkRemindedReturnAndPromiseRepayAndNormalRepayRateTPF(){
        $total = $this->checkRemindedReturnPromiseRepayCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedReturnAndPromiseRepayAndNormalRepayCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的触达下承诺还款且逾期还款的订单量占比
     * @return int
     */
    public function checkRemindedReturnAndPromiseRepayAndOverdueRepayRateTPF(){
        $total = $this->checkRemindedReturnPromiseRepayCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedReturnAndPromiseRepayAndOverdueRepayCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的触达订单量的占比
     * @return int
     */
    public function checkRemindedReturnCntRateTPF(){
        $total = $this->checkRemindCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedReturnCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的触达下已完成还款的订单量占比
     * @return int
     */
    public function checkRemindedReturnAndRepayCntRateTPF(){
        $total = $this->checkRemindedReturnCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedReturnAndRepayCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的触达下承诺还款的订单量占比
     * @return int
     */
    public function checkRemindedReturnPromiseRepayCntRateTPF(){
        $total = $this->checkRemindedReturnCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedReturnPromiseRepayCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的触达下未承诺还款的订单量占比
     * @return int
     */
    public function checkRemindedReturnUnPromiseRepayCntRateTPF(){
        $total = $this->checkRemindedReturnCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedReturnUnPromiseRepayCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的触达的次数
     * @return int
     */
    public function checkRemindedReturnTimesTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->andWhere(['>', 'l.remind_return', 0])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的触达下已完成还款的次数
     * @return int
     */
    public function checkRemindedReturnAndRepayTimesTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => 1
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的触达下承诺还款的次数
     * @return int
     */
    public function checkRemindedReturnPromiseRepayTimesTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => [2, 3]
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的触达下未承诺还款的次数
     * @return int
     */
    public function checkRemindedReturnUnPromiseRepayTimesTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => [4, 5]
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的触达下已完成还款的次数占比
     * @return int
     */
    public function checkRemindedReturnAndRepayTimesRateTPF(){
        $total = $this->checkRemindedReturnTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedReturnAndRepayTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的触达下承诺还款的次数占比
     * @return int
     */
    public function checkRemindedReturnPromiseRepayTimesRateTPF(){
        $total = $this->checkRemindedReturnTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedReturnPromiseRepayTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的触达下未承诺还款的次数占比
     * @return int
     */
    public function checkRemindedReturnUnPromiseRepayTimesRateTPF(){
        $total = $this->checkRemindedReturnTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedReturnUnPromiseRepayTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的未触达订单量
     * @return int
     */
    public function checkRemindedUnReturnCntTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->andWhere(['<', 'l.remind_return', 0])
            ->groupBy(['l.app_name', 'l.order_id'])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的未触达下未接通的订单量
     * @return int
     */
    public function checkRemindedUnReturnAndUnPassCntTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => [-1, -2, -3, -7, -8, -9],
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->groupBy(['l.app_name', 'l.order_id'])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的未触达下关机的订单量
     * @return int
     */
    public function checkRemindedUnReturnAndClosedCntTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => -4,
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->groupBy(['l.app_name', 'l.order_id'])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的未触达下号码无效的订单量
     * @return int
     */
    public function checkRemindedUnReturnAndInValidCntTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => [-5, -6],
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->groupBy(['l.app_name', 'l.order_id'])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的未触达订单量的占比
     * @return int
     */
    public function checkRemindedUnReturnCntRateTPF(){
        $total = $this->checkRemindCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedUnReturnCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的未触达下未接通的订单量的占比
     * @return int
     */
    public function checkRemindedUnReturnAndUnPassCntRateTPF(){
        $total = $this->checkRemindedUnReturnCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedUnReturnAndUnPassCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的未触达下关机的订单量的占比
     * @return int
     */
    public function checkRemindedUnReturnAndClosedCntRateTPF(){
        $total = $this->checkRemindedUnReturnCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedUnReturnAndClosedCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的未触达下号码无效的订单量的占比
     * @return int
     */
    public function checkRemindedUnReturnAndInValidCntRateTPF(){
        $total = $this->checkRemindedUnReturnCntTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedUnReturnAndInValidCntTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的未触达次数
     * @return int
     */
    public function checkRemindedUnReturnTimesTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->andWhere(['<', 'l.remind_return', 0])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的未触达下未接通的次数
     * @return int
     */
    public function checkRemindedUnReturnAndUnPassTimesTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => [-1, -2, -3, -7, -8, -9],
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的未触达下关机的次数
     * @return int
     */
    public function checkRemindedUnReturnAndClosedTimesTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => -4,
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的未触达下号码无效的次数
     * @return int
     */
    public function checkRemindedUnReturnAndInValidTimesTPF(){
        return RemindLog::find()
            ->alias('l')
            ->leftJoin(InfoUser::tableName(). 'as u', 'l.app_name=u.app_name and l.user_id=u.user_id and l.order_id=u.order_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
                'l.remind_return' => [-5, -6],
            ])
            ->andWhere(['<', 'l.updated_at', $this->data->infoOrder->order_time])
            ->groupBy(['l.app_name', 'l.order_id'])
            ->count();
    }

    /**
     * 全平台提醒还款且被提醒的未触达下未接通的次数的占比
     * @return int
     */
    public function checkRemindedUnReturnAndUnPassTimesRateTPF(){
        $total = $this->checkRemindedUnReturnTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedUnReturnAndUnPassTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的未触达下关机的次数的占比
     * @return int
     */
    public function checkRemindedUnReturnAndClosedTimesRateTPF(){
        $total = $this->checkRemindedUnReturnTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedUnReturnAndClosedTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的未触达下号码无效的次数的占比
     * @return int
     */
    public function checkRemindedUnReturnAndInValidTimesRateTPF(){
        $total = $this->checkRemindedUnReturnTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedUnReturnAndInValidTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的未触达次数的占比
     * @return int
     */
    public function checkRemindedUnReturnTimesRateTPF(){
        $total = $this->checkRemindedTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedUnReturnTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全平台提醒还款且被提醒的触达次数的占比
     * @return int
     */
    public function checkRemindedReturnTimesRateTPF(){
        $total = $this->checkRemindedTimesTPF();
        if(empty($total)){
            return -1;
        }

        $count = $this->checkRemindedReturnTimesTPF();

        return intval($count / $total * 100);
    }

    /**
     * 全新本新-无征信-模型分V1
     * @return int
     */
    public function checkQXBXUserNullCIRModelV1(){
        $score = 0;
        $v101 = $this->checkUserAge();
        switch (true){
            case $v101 < 24:
                $score += 42;
                break;
            case $v101 < 25:
                $score += 12;
                break;
            case $v101 < 29:
                $score += 46;
                break;
            case $v101 < 40:
                $score += 67;
                break;
            case $v101 >= 40:
                $score += 51;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 1600:
                $score += 44;
                break;
            case $v323 < 2000:
                $score += 33;
                break;
            case $v323 < 7000:
                $score += 63;
                break;
            case $v323 >= 7000:
                $score += 94;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 27000:
                $score += 46;
                break;
            case $v206 < 41000:
                $score += 59;
                break;
            case $v206 < 52000:
                $score += 84;
                break;
            case $v206 >= 52000:
                $score += 103;
                break;
        }

        $v202 = $this->checkLoanAppCnt();
        switch (true){
            case $v202 < 2:
                $score += 26;
                break;
            case $v202 < 5:
                $score += 42;
                break;
            case $v202 < 6:
                $score += 56;
                break;
            case $v202 >= 6:
                $score += 65;
                break;
        }

        $v105 = $this->checkIndustry();
        switch (true){
            case $v105 < 2:
                $score += 76;
                break;
            case $v105 < 5:
                $score += 38;
                break;
            case $v105 < 6:
                $score += 8;
                break;
            case $v105 < 8:
                $score += 49;
                break;
            case $v105 >= 8:
                $score += 68;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 != 1){
            $score += 300;
        }else{
            $v1042 = $this->checkAvgOfHistSMSEMIAmtTPF();
            switch (true){
                case $v1042 < 1800:
                    $score += 47;
                    break;
                case $v1042 < 5000:
                    $score += 55;
                    break;
                case $v1042 < 8400:
                    $score += 79;
                    break;
                case $v1042 >= 8400:
                    $score += 66;
                    break;
            }

            $v1051 = $this->checkSumOfSMSEMIAmtLast60DaysTPF();
            switch (true){
                case $v1051 < 7000:
                    $score += 50;
                    break;
                case $v1051 < 18000:
                    $score += 60;
                    break;
                case $v1051 >= 18000:
                    $score += 65;
                    break;
            }

            $v967 = $this->checkSMSCntOfLoanApplicationSubmissionLast60DaysTPF();
            switch (true){
                case $v967 < 1:
                    $score += 38;
                    break;
                case $v967 < 2:
                    $score += 58;
                    break;
                case $v967 < 3:
                    $score += 65;
                    break;
                case $v967 >= 3:
                    $score += 107;
                    break;
            }

            $v961 = $this->checkSMSCntOfLoanApplicationTrialLast30DaysTPF();
            switch (true){
                case $v961 < 1:
                    $score += 41;
                    break;
                case $v961 < 2:
                    $score += 48;
                    break;
                case $v961 < 4:
                    $score += 55;
                    break;
                case $v961 < 12:
                    $score += 64;
                    break;
                case $v961 >= 12:
                    $score += 94;
                    break;
            }

            $v983 = $this->checkSMSCntOfLoanDisbursalLast90DaysTPF();
            switch (true){
                case $v983 < 1:
                    $score += 51;
                    break;
                case $v983 >= 1:
                    $score += 75;
                    break;
            }
        }

        $v334 = $this->checkNameMatchResultOfFillAndPanOCR();
        if($v334 < 1){
            $score += 52;
        }else{
            $v335 = $this->checkNameMatchResultOfFillAndPanVertify();
            switch (true){
                case $v335 < 2:
                    $score += 43;
                    break;
                case $v335 < 3:
                    $score += 54;
                    break;
                case $v335 >= 3:
                    $score += 66;
                    break;
            }
        }

        return $score;
    }

    /**
     * 全平台 -  近90天内所有正常还款的结清时间距离本次申请的时间差的最大值（天）
     * @return int
     */
    public function checkMaxDayDiffBtwLast90DaysNonOverdueClosingTimeAndThisApplyTimeInTPF(){
        $begin = strtotime(date('Y-m-d',$this->data->infoOrder->order_time)) - 90 * 86400;
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName(). ' as u', 'r.app_name=u.app_name and r.order_id=u.order_id and r.user_id=u.user_id')
            ->where([
                'r.status' => InfoRepayment::STATUS_CLOSED,
                'is_overdue' => InfoRepayment::OVERDUE_NO,
                'u.pan_code' => $this->data->infoUser->pan_code
            ])
            ->andWhere(['>=', 'r.closing_time', $begin])
            ->andWhere(['<', 'r.closing_time', $this->data->infoOrder->order_time])
            ->orderBy(['r.closing_time' => SORT_ASC])
            ->one();

        if(empty($data)){
            return -1;
        }

        return (strtotime(date('Y-m-d',$this->data->infoOrder->order_time)) - strtotime(date('Y-m-d',$data['closing_time']))) / 86400;
    }

    /**
     * 全平台  - 近90天内贷款次数
     * @return int
     */
    public function checkLoanCntLast90DaysInTPF(){
        $begin = strtotime(date('Y-m-d',$this->data->infoOrder->order_time)) - 90 * 86400;
        $count = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName(). ' as u', 'r.app_name=u.app_name and r.order_id=u.order_id and r.user_id=u.user_id')
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code
            ])
            ->andWhere(['>=', 'r.loan_time', $begin])
            ->andWhere(['<', 'r.loan_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台  - 近90天内正常还款时间距离应还款日期天数之和
     * @return int
     */
    public function checkSumOfOverdueDayOfNonOverdueOrderLast90DaysInTPF(){
        $begin = strtotime(date('Y-m-d',$this->data->infoOrder->order_time)) - 90 * 86400;
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName(). ' as u', 'r.app_name=u.app_name and r.order_id=u.order_id and r.user_id=u.user_id')
            ->where([
                'r.status' => InfoRepayment::STATUS_CLOSED,
                'is_overdue' => InfoRepayment::OVERDUE_NO,
                'u.pan_code' => $this->data->infoUser->pan_code
            ])
            ->andWhere(['>=', 'r.closing_time', $begin])
            ->andWhere(['<', 'r.closing_time', $this->data->infoOrder->order_time])
            ->all();

        if(empty($data)){
            return 999;
        }

        $count = 0;
        foreach ($data as $v){
            $count += (strtotime(date('Y-m-d', $v['closing_time'])) - strtotime(date('Y-m-d', $v['plan_repayment_time'])))/86400;
        }

        return $count;
    }

    /**
     * 全老本老模型分V7
     * @return int
     */
    public function checkQLBLModelScoreV7(){
        $score = 0;
        $v1399 = $this->checkMaxDayDiffBtwLast90DaysNonOverdueClosingTimeAndThisApplyTimeInTPF();
        switch (true){
            case $v1399 < 0:
                $score += 12;
                break;
            case $v1399 < 16:
                $score += 34;
                break;
            case $v1399 < 28:
                $score += 44;
                break;
            case $v1399 >= 28:
                $score += 52;
                break;
        }

        $v1400 = $this->checkLoanCntLast90DaysInTPF();
        switch (true){
            case $v1400 < 3:
                $score += 31;
                break;
            case $v1400 < 11:
                $score += 39;
                break;
            case $v1400 < 18:
                $score += 50;
                break;
            case $v1400 >= 18:
                $score += 62;
                break;
        }

        $v1401 = $this->checkSumOfOverdueDayOfNonOverdueOrderLast90DaysInTPF();
        switch (true){
            case $v1401 < -18:
                $score += 72;
                break;
            case $v1401 < -7:
                $score += 55;
                break;
            case $v1401 < -1:
                $score += 45;
                break;
            case $v1401 >= -1:
                $score += 26;
                break;
        }

        $v720 = $this->checkOldUserComplexRuleV1HisTiqianOrderCntTotPlatform();
        switch (true){
            case $v720 < 2:
                $score += 15;
                break;
            case $v720 < 11:
                $score += 31;
                break;
            case $v720 < 27:
                $score += 48;
                break;
            case $v720 < 39:
                $score += 63;
                break;
            case $v720 >= 39:
                $score += 96;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 != 1){
            $score += 119;
        }else{
            $v597 = $this->checkSMSCntOfLoanApplicationSubmissionLast30Days();
            switch (true){
                case $v597 < 2:
                    $score += 37;
                    break;
                case $v597 < 4:
                    $score += 40;
                    break;
                case $v597 < 5:
                    $score += 47;
                    break;
                case $v597 >= 5:
                    $score += 44;
                    break;
            }

            $v819 = $this->checkSumOfSMSLoanOverdueDaysLast30Days();
            switch (true){
                case $v819 < 0:
                    $score += 40;
                    break;
                case $v819 < 100:
                    $score += 37;
                    break;
                case $v819 >= 100:
                    $score += 33;
                    break;
            }

            $v1052 = $this->checkMaxOfSMSEMIAmtLast60DaysTPF();
            switch (true){
                case $v1052 < 6000:
                    $score += 38;
                    break;
                case $v1052 < 11500:
                    $score += 41;
                    break;
                case $v1052 >= 11500:
                    $score += 48;
                    break;
            }

            $v609 = $this->checkSMSCntOfLoanApprovalLast90Days();
            switch (true){
                case $v609 < 13:
                    $score += 38;
                    break;
                case $v609 < 27:
                    $score += 39;
                    break;
                case $v609 >= 27:
                    $score += 44;
                    break;
            }
        }

        $v1299 = $this->checkTeleCollectionOtherRateTPF();
        switch (true){
            case $v1299 < 0:
                $score += 41;
                break;
            case $v1299 < 2:
                $score += 38;
                break;
            case $v1299 >= 2:
                $score += 30;
                break;
        }

        $v675 = $this->checkHistMaxOverdueDaysByPanTotPlatform();
        switch (true){
            case $v675 < 2:
                $score += 45;
                break;
            case $v675 < 4:
                $score += 41;
                break;
            case $v675 < 8:
                $score += 30;
                break;
            case $v675 >= 8:
                $score += 0;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 5:
                $score += 34;
                break;
            case $v103 < 6:
                $score += 40;
                break;
            case $v103 >= 6:
                $score += 42;
                break;
        }

        $v263 = $this->checkApplyCnt500mAwayFromGPSlocLast7Days();
        switch (true){
            case $v263 < 2:
                $score += 37;
                break;
            case $v263 < 6:
                $score += 38;
                break;
            case $v263 < 10:
                $score += 40;
                break;
            case $v263 >= 10:
                $score += 39;
                break;
        }

        $v563 = $this->checkLast30dLoanAppRate();
        switch (true){
            case $v563 < 4:
                $score += 40;
                break;
            case $v563 < 40:
                $score += 37;
                break;
            case $v563 < 60:
                $score += 41;
                break;
            case $v563 >= 60:
                $score += 43;
                break;
        }

        $v727 = $this->checkLast30dCpDayMaxTotPlatform();
        switch (true){
            case $v727 < -7:
                $score += 18;
                break;
            case $v727 < 2:
                $score += 46;
                break;
            case $v727 < 4:
                $score += 31;
                break;
            case $v727 >= 4:
                $score += 12;
                break;
        }

        $v287 = $this->checkMaxDistAmongGPS();
        switch (true){
            case $v287 < 0:
                $score += 36;
                break;
            case $v287 < 120:
                $score += 40;
                break;
            case $v287 >= 120:
                $score += 42;
                break;
        }

        return $score;
    }

    /**
     * 全本本新-模型分V6
     * @return int
     */
    public function checkQXBXUserModelV6(){
        $score = 0;
        $v105 = $this->checkIndustry();
        switch (true){
            case $v105 < 2:
                $score += 48;
                break;
            case $v105 < 5:
                $score += 5;
                break;
            case $v105 < 6:
                $score += 34;
                break;
            case $v105 < 8:
                $score += 46;
                break;
            case $v105 >= 8:
                $score += 52;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 200:
                $score += 24;
                break;
            case $v142 < 400:
                $score += 36;
                break;
            case $v142 < 1250:
                $score += 48;
                break;
            case $v142 >= 1250:
                $score += 78;
                break;
        }

        $v324 = $this->checkLast30MobilePhotoAmount();
        switch (true){
            case $v324 < 250:
                $score += 31;
                break;
            case $v324 < 400:
                $score += 46;
                break;
            case $v324 < 800:
                $score += 66;
                break;
            case $v324 < 1200:
                $score += 90;
                break;
            case $v324 >= 1200:
                $score += 50;
                break;
        }

        $v563 = $this->checkLast30dLoanAppRate();
        switch (true){
            case $v563 < 12:
                $score += 26;
                break;
            case $v563 < 36:
                $score += 45;
                break;
            case $v563 < 62:
                $score += 56;
                break;
            case $v563 >= 62:
                $score += 68;
                break;
        }

        $v687 = $this->checkLast60dRejectCntByPanInTotPlatform();
        switch (true){
            case $v687 < 1:
                $score += 55;
                break;
            case $v687 < 3:
                $score += 4;
                break;
            case $v687 >= 3:
                $score += 17;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 != 1){
            $score += 109;
        }else{
            $v985 = $this->checkSMSCntOfLoanDueRemindLast7DaysTPF();
            switch (true){
                case $v985 < 2:
                    $score += 51;
                    break;
                case $v985 < 4:
                    $score += 47;
                    break;
                case $v985 < 6:
                    $score += 58;
                    break;
                case $v985 < 10:
                    $score += 50;
                    break;
                case $v985 >= 10:
                    $score += 36;
                    break;
            }

            $v995 = $this->checkSMSCntOfOverdueRemindLast7DaysTPF();
            switch (true){
                case $v995 < 2:
                    $score += 46;
                    break;
                case $v995 >= 2:
                    $score += 41;
                    break;
            }

            $v1082 = $this->checkHistAvgOfSMSLoanOverdueDaysTPF();
            switch (true){
                case $v1082 < 3:
                    $score += 65;
                    break;
                case $v1082 < 7:
                    $score += 21;
                    break;
                case $v1082 >= 7:
                    $score += 33;
                    break;
            }
        }

        $this->isGetData = false;
        $v1219 = $this->checkIsBangaloreExperianCreditReportReturnedNormal();
        if($v1219 != 1){
            $score += 111;
        }else{
            $v1223 = $this->checkBangaloreExperianCreditAccountClosed();
            switch (true){
                case $v1223 < 2:
                    $score += 30;
                    break;
                case $v1223 < 7:
                    $score += 42;
                    break;
                case $v1223 >= 7:
                    $score += 56;
                    break;
            }

            $v1226 = $this->checkBangaloreExperianOutstandingBalanceUnSecured();
            switch (true){
                case $v1226 < 30000:
                    $score += 37;
                    break;
                case $v1226 < 100000:
                    $score += 44;
                    break;
                case $v1226 < 430000:
                    $score += 56;
                    break;
                case $v1226 >= 430000:
                    $score += 76;
                    break;
            }

            $v1237 = $this->checkBangaloreExperianHisMaxCreditAmt();
            switch (true){
                case $v1237 < 0:
                    $score += 37;
                    break;
                case $v1237 < 80000:
                    $score += 41;
                    break;
                case $v1237 < 280000:
                    $score += 47;
                    break;
                case $v1237 >= 280000:
                    $score += 52;
                    break;
            }

            $v1230 = $this->checkBangaloreExperianLast90dEnquiryCnt();
            switch (true){
                case $v1230 < 0:
                    $score += 3;
                    break;
                case $v1230 < 2:
                    $score += 27;
                    break;
                case $v1230 < 4:
                    $score += 53;
                    break;
                case $v1230 >= 4:
                    $score += 101;
                    break;
            }

            $v1253 = $this->checkBangaloreExperianTimeOfLastPayMent();
            switch (true){
                case $v1253 < 20:
                    $score += 19;
                    break;
                case $v1253 < 85:
                    $score += 87;
                    break;
                case $v1253 < 115:
                    $score += 34;
                    break;
                case $v1253 >= 115:
                    $score += 11;
                    break;
            }
        }

        return $score;
    }

    /**
     * 全老本新-无征信-模型分V1
     * @return int
     */
    public function checkQLBXUserNullCIRModelV1(){
        $score = 0;
        $v141 = $this->checkContactNameMobileHitOverdueUserContactMobileCnt();
        switch (true){
            case $v141 < 1:
                $score += 71;
                break;
            case $v141 >= 1:
                $score += 73;
                break;
        }

        $v587 = $this->checkLast30RejectCntBySMDeviceIDInTotPlatporm();
        switch (true){
            case $v587 < 1:
                $score += 90;
                break;
            case $v587 < 3:
                $score += 56;
                break;
            case $v587 >= 3:
                $score += 33;
                break;
        }

        $v722 = $this->checkOldUserComplexRuleV1HisCpDaySumTotPlatform();
        switch (true){
            case $v722 < -9:
                $score += 126;
                break;
            case $v722 < -1:
                $score += 97;
                break;
            case $v722 < 4:
                $score += 65;
                break;
            case $v722 >= 4:
                $score += 27;
                break;
        }

        $v726 = $this->checkLastLoanOrderCpDayTotPlatform();
        switch (true){
            case $v726 < 0:
                $score += 86;
                break;
            case $v726 < 1:
                $score += 76;
                break;
            case $v726 < 3:
                $score += 60;
                break;
            case $v726 >= 3:
                $score += 31;
                break;
        }

        $v727 = $this->checkLast30dCpDayMaxTotPlatform();
        switch (true){
            case $v727 < -7:
                $score += 48;
                break;
            case $v727 < 1:
                $score += 84;
                break;
            case $v727 < 4:
                $score += 74;
                break;
            case $v727 >= 4:
                $score += 52;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 != 1){
            $score += 93;
        }else{
            $v968 = $this->checkSMSCntOfLoanApplicationSubmissionLast90DaysTPF();
            switch (true){
                case $v968 < 1:
                    $score += 60;
                    break;
                case $v968 < 5:
                    $score += 74;
                    break;
                case $v968 < 8:
                    $score += 89;
                    break;
                case $v968 >= 8:
                    $score += 98;
                    break;
            }

            $v979 = $this->checkHistSMSCntOfLoanDisbursalTPF();
            switch (true){
                case $v979 < 1:
                    $score += 69;
                    break;
                case $v979 < 6:
                    $score += 73;
                    break;
                case $v979 < 15:
                    $score += 79;
                    break;
                case $v979 >= 15:
                    $score += 92;
                    break;
            }
        }

        $v1218 = $this->checkHisSuccessClosingOrderCntPost20200501PlusOneTPF();
        switch (true){
            case $v1218 < 2:
                $score += 33;
                break;
            case $v1218 < 3:
                $score += 68;
                break;
            case $v1218 < 5:
                $score += 79;
                break;
            case $v1218 >= 5:
                $score += 110;
                break;
        }

        $v1263 = $this->checkProductName();
        switch ($v1263){
            case 'icredit':
            case 'rupeeplus':
                $score += 85;
                break;
            case 'rupeefanta':
                $score += 80;
                break;
            case 'bigshark':
                $score += 70;
                break;
            case 'moneyclick':
                $score += 49;
                break;
        }

        return $score;
    }

    /**
     * 是否为疫情期间可捞回黑名单用户
     * @return int
     */
    public function checkIsPotentialRecoveryBlacklistUserInLockdown(){
        $begin = strtotime('2020-03-23');
        $end = strtotime('2020-05-06');

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'u.app_name=r.app_name and u.order_id=r.order_id and u.user_id=r.user_id')
            ->select(['r.status'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->andWhere(['>=', 'r.plan_repayment_time', $begin])
            ->andWhere(['<', 'r.plan_repayment_time', $end])
            ->andWhere(['>=', 'r.overdue_day', 30])
            ->groupBy(['r.status'])
            ->asArray()
            ->column();

        if(empty($data)){
            return -1;
        }

        if(in_array(InfoRepayment::STATUS_PENDING, $data)){
            return 0;
        }else{
            return 1;
        }
    }

    /**
     * 全老本老模型分V8
     * @return int
     */
    public function checkQLBLModelScoreV8(){
        $score = 0;
        $v721 = $this->checkOldUserComplexRuleV1HisDueOrderCntTotPlatform();
        switch (true){
            case $v721 < 1:
                $score += 24;
                break;
            case $v721 < 5:
                $score += 23;
                break;
            case $v721 >= 5:
                $score += 22;
                break;
        }

        $v1278 = $this->checkSingleTeleCollectionMaxTimesTPF();
        switch (true){
            case $v1278 < 4:
                $score += 28;
                break;
            case $v1278 < 7:
                $score += 22;
                break;
            case $v1278 >= 7:
                $score += 8;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 32000:
                $score += 22;
                break;
            case $v206 < 36000:
                $score += 25;
                break;
            case $v206 < 50000:
                $score += 27;
                break;
            case $v206 >= 50000:
                $score += 32;
                break;
        }

        $v726 = $this->checkLastLoanOrderCpDayTotPlatform();
        switch (true){
            case $v726 < -1:
                $score += 29;
                break;
            case $v726 < 0:
                $score += 36;
                break;
            case $v726 < 1:
                $score += 25;
                break;
            case $v726 >= 1:
                $score += 1;
                break;
        }

        $v723 = $this->checkOldUserComplexRuleV1HisDueCpDaySumTotPlatform();
        switch (true){
            case $v723 < 2:
                $score += 26;
                break;
            case $v723 < 4:
                $score += 27;
                break;
            case $v723 < 12:
                $score += 21;
                break;
            case $v723 >= 12:
                $score += 11;
                break;
        }

        $v556 = $this->checkLast7dAppCnt();
        switch (true){
            case $v556 < 5:
                $score += 28;
                break;
            case $v556 < 6:
                $score += 24;
                break;
            case $v556 < 12:
                $score += 22;
                break;
            case $v556 >= 12:
                $score += 18;
                break;
        }

        $v727 = $this->checkLast30dCpDayMaxTotPlatform();
        switch (true){
            case $v727 < 2:
                $score += 29;
                break;
            case $v727 < 3:
                $score += 15;
                break;
            case $v727 >= 3:
                $score += -8;
                break;
        }

        $v696 = $this->checkLast90dRejectCntByPhoneTotPlatform();
        switch (true){
            case $v696 < 1:
                $score += 27;
                break;
            case $v696 < 2:
                $score += 22;
                break;
            case $v696 < 4:
                $score += 20;
                break;
            case $v696 >= 4:
                $score += 14;
                break;
        }

        $v105 = $this->checkIndustry();
        switch (true){
            case $v105 < 2:
                $score += 29;
                break;
            case $v105 < 5:
                $score += 8;
                break;
            case $v105 < 6:
                $score += 17;
                break;
            case $v105 < 7:
                $score += 24;
                break;
            case $v105 >= 7:
                $score += 27;
                break;
        }

        $v562 = $this->checkLast7dLoanAppRate();
        switch (true){
            case $v562 < 8:
                $score += 29;
                break;
            case $v562 < 26:
                $score += 21;
                break;
            case $v562 < 50:
                $score += 19;
                break;
            case $v562 >= 50:
                $score += 26;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 1500:
                $score += 21;
                break;
            case $v323 < 5000:
                $score += 25;
                break;
            case $v323 < 11500:
                $score += 28;
                break;
            case $v323 >= 11500:
                $score += 32;
                break;
        }

        $v1382 = $this->checkRemindedUnReturnCntRateTPF();
        switch (true){
            case $v1382 < 5:
                $score += 24;
                break;
            case $v1382 < 20:
                $score += 41;
                break;
            case $v1382 < 50:
                $score += 27;
                break;
            case $v1382 < 60:
                $score += 21;
                break;
            case $v1382 < 75:
                $score += 18;
                break;
            case $v1382 >= 75:
                $score += 11;
                break;
        }

        $v1399 = $this->checkMaxDayDiffBtwLast90DaysNonOverdueClosingTimeAndThisApplyTimeInTPF();
        switch (true){
            case $v1399 < 0:
                $score += -20;
                break;
            case $v1399 < 12:
                $score += 12;
                break;
            case $v1399 < 22:
                $score += 22;
                break;
            case $v1399 < 46:
                $score += 36;
                break;
            case $v1399 >= 46:
                $score += 52;
                break;
        }

        $v587 = $this->checkLast30RejectCntBySMDeviceIDInTotPlatporm();
        switch (true){
            case $v587 < 1:
                $score += 30;
                break;
            case $v587 < 2:
                $score += 17;
                break;
            case $v587 >= 2:
                $score += 8;
                break;
        }

        $v1198 = $this->checkMaxDateOfOrderToTodayTPF();
        switch (true){
            case $v1198 < 30:
                $score += 14;
                break;
            case $v1198 < 135:
                $score += 29;
                break;
            case $v1198 >= 135:
                $score += 36;
                break;
        }

        $v1353 = $this->checkRemindedAvgTimesTPF();
        switch (true){
            case $v1353 < 3:
                $score += 24;
                break;
            case $v1353 >= 3:
                $score += 21;
                break;
        }

        $v1401 = $this->checkSumOfOverdueDayOfNonOverdueOrderLast90DaysInTPF();
        switch (true){
            case $v1401 < -4:
                $score += 29;
                break;
            case $v1401 < 0:
                $score += 26;
                break;
            case $v1401 >= 0:
                $score += 15;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 4:
                $score += 15;
                break;
            case $v103 < 5:
                $score += 17;
                break;
            case $v103 < 6:
                $score += 25;
                break;
            case $v103 >= 6:
                $score += 32;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 != 1){
            $score += 122;
        }else{
            $v967 = $this->checkSMSCntOfLoanApplicationSubmissionLast60DaysTPF();
            switch (true){
                case $v967 < 1:
                    $score += 20;
                    break;
                case $v967 < 2:
                    $score += 23;
                    break;
                case $v967 < 5:
                    $score += 25;
                    break;
                case $v967 >= 5:
                    $score += 28;
                    break;
            }

            $v1013 = $this->checkMinOfSMSLoanCreditAmtLast60DaysTPF();
            switch (true){
                case $v1013 < 0:
                    $score += 23;
                    break;
                case $v1013 < 2000:
                    $score += 25;
                    break;
                case $v1013 < 14000:
                    $score += 28;
                    break;
                case $v1013 >= 14000:
                    $score += 22;
                    break;
            }

            $v989 = $this->checkHistSMSCntOfLoanPayOffTPF();
            switch (true){
                case $v989 < 5:
                    $score += 20;
                    break;
                case $v989 < 7:
                    $score += 21;
                    break;
                case $v989 < 12:
                    $score += 25;
                    break;
                case $v989 < 21:
                    $score += 33;
                    break;
                case $v989 >= 21:
                    $score += 42;
                    break;
            }

            $v1022 = $this->checkHistAvgOfSMSLoanDisburseAmtTPF();
            switch (true){
                case $v1022 < 0:
                    $score += 22;
                    break;
                case $v1022 < 3000:
                    $score += 23;
                    break;
                case $v1022 < 5800:
                    $score += 28;
                    break;
                case $v1022 >= 5800:
                    $score += 33;
                    break;
            }

            $v971 = $this->checkSMSCntOfLoanRejectionLast30DaysTPF();
            switch (true){
                case $v971 < 1:
                    $score += 25;
                    break;
                case $v971 < 5:
                    $score += 23;
                    break;
                case $v971 >= 5:
                    $score += 22;
                    break;
            }

            $v1102 = $this->checkHistAvgOfSMSLoanOverdueAmtTPF();
            switch (true){
                case $v1102 < 5000:
                    $score += 22;
                    break;
                case $v1102 < 6000:
                    $score += 29;
                    break;
                case $v1102 >= 6000:
                    $score += 35;
                    break;
            }
        }

        return $score;
    }

    /**
     * 定位地址城市是否准入
     * @return int
     */
    public function checkGPSCityHitWhiteList(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['district'])){
            return -1;
        }

        if(in_array(strtoupper($data['district']), $this->addressWhiteList)){
            return 1;
        }

        return 0;
    }

    /**
     * 本产品定位地址城市近1小时内的申请订单数
     * @return int
     */
    public function checkApplyOrderCntGPSCityLast1HourSelf(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['district'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 3600;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.district' => $data['district'],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 本产品定位地址城市近1天内的申请订单数
     * @return int
     */
    public function checkApplyOrderCntGPSCityLast1DaySelf(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['district'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.district' => $data['district'],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 本产品定位地址城市近1小时内的申请被拒订单数
     * @return int
     */
    public function checkRejectOrderCntGPSCityLast1HourSelf(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['district'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 3600;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.district' => $data['district'],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 本产品定位地址城市近1天内的申请被拒订单数
     * @return int
     */
    public function checkRejectOrderCntGPSCityLast1DaySelf(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['district'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.district' => $data['district'],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台定位地址城市近1小时内的申请订单数
     * @return int
     */
    public function checkApplyOrderCntGPSCityLast1HourTPF(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['district'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 3600;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.district' => $data['district']])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台定位地址城市近1天内的申请订单数
     * @return int
     */
    public function checkApplyOrderCntGPSCityLast1DayTPF(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['district'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.district' => $data['district']])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台定位地址城市近1小时内的申请被拒订单数
     * @return int
     */
    public function checkRejectOrderCntGPSCityLast1HourTPF(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['district'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 3600;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.district' => $data['district'],
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台定位地址城市近1天内的申请被拒订单数
     * @return int
     */
    public function checkRejectOrderCntGPSCityLast1DayTPF(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['district'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.district' => $data['district'],
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 本产品定位地址所在邦近1小时内的申请订单数
     * @return int
     */
    public function checkApplyOrderCntGPSStateLast1HourSelf(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['state'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 3600;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.state' => $data['state'],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 本产品定位地址所在邦近1天内的申请订单数
     * @return int
     */
    public function checkApplyOrderCntGPSStateLast1DaySelf(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['state'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.state' => $data['state'],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 本产品定位地址所在邦近1小时内的申请被拒订单数
     * @return int
     */
    public function checkRejectOrderCntGPSStateLast1HourSelf(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['state'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 3600;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.state' => $data['state'],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 本产品定位地址所在邦近1天内的申请被拒订单数
     * @return int
     */
    public function checkRejectOrderCntGPSStateLast1DaySelf(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['state'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.state' => $data['state'],
                     'o.product_id' => $this->data->infoOrder->product_id,
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT],
                     'o.app_name' => $this->data->order->app_name])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台定位地址所在邦近1小时内的申请订单数
     * @return int
     */
    public function checkApplyOrderCntGPSStateLast1HourTPF(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['state'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 3600;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.state' => $data['state']])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台定位地址所在邦近1天内的申请订单数
     * @return int
     */
    public function checkApplyOrderCntGPSStateLast1DayTPF(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['state'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.state' => $data['state']])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台定位地址所在邦近1小时内的申请被拒订单数
     * @return int
     */
    public function checkRejectOrderCntGPSStateLast1HourTPF(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['state'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 3600;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.state' => $data['state'],
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 全平台定位地址所在邦近1天内的申请被拒订单数
     * @return int
     */
    public function checkRejectOrderCntGPSStateLast1DayTPF(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['state'])){
            return -1;
        }

        $begin = $this->data->infoOrder->order_time - 86400;

        $count = InfoOrder::find()
            ->alias('o')
            ->leftJoin(ThirdDataGoogleMaps::tableName().' as g', 'o.order_id=g.order_id and o.user_id=g.user_id and o.app_name=g.app_name')
            ->where(['g.state' => $data['state'],
                     'o.status' => [InfoOrder::STATUS_RISK_AUTO_REJECT, InfoOrder::STATUS_RISK_MANUAL_REJECT]])
            ->andWhere(['>=', 'o.order_time', $begin])
            ->andWhere(['<=', 'o.order_time', $this->data->infoOrder->order_time])
            ->count();

        return $count;
    }

    /**
     * 定位地址是否在印度的精确判断
     * @return int
     */
    public function checkIsIndiaGPSAccurate(){
        $client_info = $this->data->order->infoDevice;
        if(empty($client_info->latitude) || empty($client_info->longitude)){
            return -1;
        }

        $data = $this->getGoogleMapsReport();
        if(empty($data['country'])){
            return -1;
        }

        if(strtolower($data['country']) == 'india'){
            return 1;
        }

        return 0;
    }

    /**
     * 全新本新-无征信-模型分V2
     * @return int
     */
    public function checkQXBXUserNullCIRModelV2(){
        $score = 0;
        $v203 = $this->checkLoanAppRatio();
        switch (true){
            case $v203 < 1:
                $score += 22;
                break;
            case $v203 < 2:
                $score += 42;
                break;
            case $v203 < 7:
                $score += 73;
                break;
            case $v203 >= 7:
                $score += 52;
                break;
        }

        $v101 = $this->checkUserAge();
        switch (true){
            case $v101 < 24:
                $score += 34;
                break;
            case $v101 < 30:
                $score += 49;
                break;
            case $v101 < 35:
                $score += 67;
                break;
            case $v101 >= 35:
                $score += 82;
                break;
        }

        $v105 = $this->checkIndustry();
        switch (true){
            case $v105 < 6:
                $score += 48;
                break;
            case $v105 < 8:
                $score += 54;
                break;
            case $v105 < 20:
                $score += 61;
                break;
            case $v105 >= 20:
                $score += 73;
                break;
        }

        $v325 = $this->checkLast90MobilePhotoAmount();
        switch (true){
            case $v325 < 200:
                $score += 49;
                break;
            case $v325 < 1000:
                $score += 55;
                break;
            case $v325 < 2800:
                $score += 68;
                break;
            case $v325 >= 2800:
                $score += 83;
                break;
        }

        $v679 = $this->checkPendingRepaymentTotAmtOfPanTotPlatform();
        switch (true){
            case $v679 < 1700:
                $score += 55;
                break;
            case $v679 < 2000:
                $score += 52;
                break;
            case $v679 >= 2000:
                $score += 87;
                break;
        }

        $v326 = $this->checkFirstPhotoTimeToNow();
        switch (true){
            case $v326 < 0:
                $score += 74;
                break;
            case $v326 < 250:
                $score += 47;
                break;
            case $v326 < 700:
                $score += 58;
                break;
            case $v326 < 2050:
                $score += 68;
                break;
            case $v326 >= 2050:
                $score += 50;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 4:
                $score += 46;
                break;
            case $v103 < 5:
                $score += 48;
                break;
            case $v103 >= 5:
                $score += 66;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 != 1){
            $score += 137;
        }else{
            $v1114 = $this->checkAvgOfSMSLoanOverdueAmtLast60DaysTPF();
            switch (true){
                case $v1114 < 1000:
                    $score += 56;
                    break;
                case $v1114 < 4000:
                    $score += 33;
                    break;
                case $v1114 < 11000:
                    $score += 72;
                    break;
                case $v1114 >= 11000:
                    $score += 110;
                    break;
            }

            $v1065 = $this->checkMinOfSMSDueRemindLoanAmtLast7DaysTPF();
            switch (true){
                case $v1065 < 1200:
                    $score += 50;
                    break;
                case $v1065 < 4000:
                    $score += 84;
                    break;
                case $v1065 < 8200:
                    $score += 102;
                    break;
                case $v1065 >= 8200:
                    $score += 56;
                    break;
            }
        }

        return $score;
    }

    /**
     * 该Pan卡号全平台历史放款时间距离本订单申请时间的天数差的平均值
     * @return int
     */
    public function checkAvgDayDiffBtwHistLoanTimeAndThisApplyTimeInTPF(){
        $data = InfoOrder::find()
            ->alias('o')
            ->leftJoin(InfoUser::tableName().' as u', 'u.app_name=o.app_name and u.order_id=o.order_id and u.user_id=o.user_id')
            ->select(['o.loan_time'])
            ->where([
                'u.pan_code' => $this->data->infoUser->pan_code,
            ])
            ->andWhere(['>', 'o.loan_time', 0])
            ->andWhere(['<=', 'o.loan_time', $this->data->infoOrder->order_time])
            ->all();
        $count = [];
        foreach ($data as $v){
            $count[] = (strtotime(date('Y-m-d', $this->data->infoOrder->order_time)) - strtotime(date('Y-m-d', $v['loan_time'])))/86400;
        }

        if(empty($count)){
            return -1;
        }

        return round(array_sum($count) / count($count), 2);
    }

    /**
     * 全新本新模型分V7
     * @return int
     */
    public function checkQXBXUserModelV7(){
        $score = 0;
        $v915 = $this->checkApplyCnt500mAwayFromGPSlocLast1DayAllPlatform();
        switch (true){
            case $v915 < 0:
                $score += 12;
                break;
            case $v915 < 1:
                $score += 38;
                break;
            case $v915 < 2:
                $score += 19;
                break;
            case $v915 >= 2:
                $score += 14;
                break;
        }

        $v324 = $this->checkLast30MobilePhotoAmount();
        switch (true){
            case $v324 < 100:
                $score += 14;
                break;
            case $v324 < 150:
                $score += 17;
                break;
            case $v324 < 1100:
                $score += 19;
                break;
            case $v324 >= 1100:
                $score += 25;
                break;
        }

        $v101 = $this->checkUserAge();
        switch (true){
            case $v101 < 26:
                $score += 12;
                break;
            case $v101 < 29:
                $score += 16;
                break;
            case $v101 < 37:
                $score += 21;
                break;
            case $v101 >= 37:
                $score += 25;
                break;
        }

        $v1466 = $this->checkAvgDayDiffBtwHistLoanTimeAndThisApplyTimeInTPF();
        switch (true){
            case $v1466 < 0:
                $score += 14;
                break;
            case $v1466 < 2:
                $score += 20;
                break;
            case $v1466 >= 2:
                $score += 28;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 1600:
                $score += 12;
                break;
            case $v323 < 5800:
                $score += 22;
                break;
            case $v323 < 10200:
                $score += 31;
                break;
            case $v323 >= 10200:
                $score += 42;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 4:
                $score += 10;
                break;
            case $v103 < 5:
                $score += 15;
                break;
            case $v103 >= 5:
                $score += 23;
                break;
        }

        $v557 = $this->checkLast30dAppCnt();
        switch (true){
            case $v557 < 6:
                $score += 13;
                break;
            case $v557 < 10:
                $score += 17;
                break;
            case $v557 < 20:
                $score += 21;
                break;
            case $v557 < 47:
                $score += 20;
                break;
            case $v557 >= 47:
                $score += 16;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 != 1){
            $score += 165;
        }else{
            $v804 = $this->checkMaxOfSMSDueRemindLoanAmtLast60Days();
            switch (true){
                case $v804 < 0:
                    $score += 20;
                    break;
                case $v804 < 160000:
                    $score += 18;
                    break;
                case $v804 >= 160000:
                    $score += 17;
                    break;
            }

            $v594 = $this->checkSMSCntOfLoanApplicationTrialLast90Days();
            switch (true){
                case $v594 < 4:
                    $score += 14;
                    break;
                case $v594 < 11:
                    $score += 20;
                    break;
                case $v594 < 18:
                    $score += 26;
                    break;
                case $v594 >= 18:
                    $score += 31;
                    break;
            }

            $v610 = $this->checkHistSMSCntOfLoanDisbursal();
            switch (true){
                case $v610 < 2:
                    $score += 18;
                    break;
                case $v610 < 6:
                    $score += 21;
                    break;
                case $v610 >= 6:
                    $score += 27;
                    break;
            }

            $v1017 = $this->checkMinOfSMSLoanCreditAmtLast90DaysTPF();
            switch (true){
                case $v1017 < 1000:
                    $score += 18;
                    break;
                case $v1017 < 7000:
                    $score += 22;
                    break;
                case $v1017 < 39000:
                    $score += 19;
                    break;
                case $v1017 >= 39000:
                    $score += 18;
                    break;
            }

            $v1114 = $this->checkAvgOfSMSLoanOverdueAmtLast60DaysTPF();
            switch (true){
                case $v1114 < 10000:
                    $score += 18;
                    break;
                case $v1114 >= 10000:
                    $score += 20;
                    break;
            }

            $v1100 = $this->checkHistMaxOfSMSLoanOverdueAmtTPF();
            switch (true){
                case $v1100 < 5600:
                    $score += 18;
                    break;
                case $v1100 < 11800:
                    $score += 19;
                    break;
                case $v1100 >= 11800:
                    $score += 20;
                    break;
            }

            $v780 = $this->checkMaxOfSMSEMIAmtLast30Days();
            switch (true){
                case $v780 < 1000:
                    $score += 18;
                    break;
                case $v780 < 2800:
                    $score += 19;
                    break;
                case $v780 >= 2800:
                    $score += 21;
                    break;
            }

            $v732 = $this->checkHistMaxOfSMSLoanCreditAmt();
            switch (true){
                case $v732 < 120000:
                    $score += 19;
                    break;
                case $v732 >= 120000:
                    $score += 18;
                    break;
            }
        }

        $this->isGetData = false;
        $v1219 = $this->checkIsBangaloreExperianCreditReportReturnedNormal();
        if($v1219 != 1){
            $score += 141;
        }else{
            $v1245 = $this->checkBangaloreExperianHisDueTotAmt();
            switch (true){
                case $v1245 < 1000:
                    $score += 22;
                    break;
                case $v1245 < 10000:
                    $score += 6;
                    break;
                case $v1245 < 18000:
                    $score += 13;
                    break;
                case $v1245 >= 18000:
                    $score += 22;
                    break;
            }

            $v1230 = $this->checkBangaloreExperianLast90dEnquiryCnt();
            switch (true){
                case $v1230 < 0:
                    $score += -8;
                    break;
                case $v1230 < 2:
                    $score += 7;
                    break;
                case $v1230 < 4:
                    $score += 31;
                    break;
                case $v1230 >= 4:
                    $score += 62;
                    break;
            }

            $v1254 = $this->checkBangaloreExperianCreditScore();
            switch (true){
                case $v1254 < 660:
                    $score += 10;
                    break;
                case $v1254 < 710:
                    $score += 16;
                    break;
                case $v1254 >= 710:
                    $score += 26;
                    break;
            }

            $v1223 = $this->checkBangaloreExperianCreditAccountClosed();
            switch (true){
                case $v1223 < 5:
                    $score += 12;
                    break;
                case $v1223 < 10:
                    $score += 23;
                    break;
                case $v1223 < 19:
                    $score += 30;
                    break;
                case $v1223 >= 19:
                    $score += 41;
                    break;
            }

            $v1240 = $this->checkBangaloreExperianHisAvgCreditAmt();
            switch (true){
                case $v1240 < 0:
                    $score += 13;
                    break;
                case $v1240 < 20000:
                    $score += 17;
                    break;
                case $v1240 < 50000:
                    $score += 20;
                    break;
                case $v1240 >= 50000:
                    $score += 23;
                    break;
            }

            $v1224 = $this->checkBangaloreExperianOutstandingBalanceSecured();
            switch (true){
                case $v1224 < 85000:
                    $score += 18;
                    break;
                case $v1224 >= 85000:
                    $score += 23;
                    break;
            }

            $v1226 = $this->checkBangaloreExperianOutstandingBalanceUnSecured();
            switch (true){
                case $v1226 < 0:
                    $score += 16;
                    break;
                case $v1226 < 30000:
                    $score += 11;
                    break;
                case $v1226 < 210000:
                    $score += 22;
                    break;
                case $v1226 >= 210000:
                    $score += 40;
                    break;
            }

            $v1244 = $this->checkBangaloreExperianTimeOfFirstCreditTimeToNow();
            switch (true){
                case $v1244 < 0:
                    $score += 12;
                    break;
                case $v1244 < 400:
                    $score += 16;
                    break;
                case $v1244 < 1600:
                    $score += 18;
                    break;
                case $v1244 < 2600:
                    $score += 22;
                    break;
                case $v1244 >= 2600:
                    $score += 27;
                    break;
            }

            $v1253 = $this->checkBangaloreExperianTimeOfLastPayMent();
            switch (true){
                case $v1253 < 10:
                    $score += 5;
                    break;
                case $v1253 < 90:
                    $score += 37;
                    break;
                case $v1253 < 120:
                    $score += 26;
                    break;
                case $v1253 < 130:
                    $score += 19;
                    break;
                case $v1253 >= 130:
                    $score += 7;
                    break;
            }

            $v1252 = $this->checkBangaloreExperianHisMaxDueDays();
            switch (true){
                case $v1252 < 0:
                    $score += 19;
                    break;
                case $v1252 < 100:
                    $score += 22;
                    break;
                case $v1252 < 220:
                    $score += 16;
                    break;
                case $v1252 < 540:
                    $score += 12;
                    break;
                case $v1252 >= 540:
                    $score += 21;
                    break;
            }
        }

        return $score;
    }

    /**
     * 全老本新-无征信-模型分V2
     * @return int
     */
    public function checkQLBXUserNullCIRModelV2(){
        $score = 0;
        $v1258 = $this->checkSelectedPreCreditLine();
        switch (true){
            case $v1258 < 2100:
                $score += 95;
                break;
            case $v1258 < 2300:
                $score += 61;
                break;
            case $v1258 < 2900:
                $score += 56;
                break;
            case $v1258 >= 2900:
                $score += 77;
                break;
        }

        $v720 = $this->checkOldUserComplexRuleV1HisTiqianOrderCntTotPlatform();
        switch (true){
            case $v720 < 1:
                $score += -28;
                break;
            case $v720 < 4:
                $score += 61;
                break;
            case $v720 < 9:
                $score += 87;
                break;
            case $v720 < 19:
                $score += 126;
                break;
            case $v720 >= 19:
                $score += 215;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 150:
                $score += 55;
                break;
            case $v142 < 350:
                $score += 67;
                break;
            case $v142 < 650:
                $score += 73;
                break;
            case $v142 < 1200:
                $score += 84;
                break;
            case $v142 >= 1200:
                $score += 72;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 1800:
                $score += 65;
                break;
            case $v323 < 4800:
                $score += 76;
                break;
            case $v323 < 10000:
                $score += 87;
                break;
            case $v323 >= 10000:
                $score += 99;
                break;
        }

        $v675 = $this->checkHistMaxOverdueDaysByPanTotPlatform();
        switch (true){
            case $v675 < 1:
                $score += 95;
                break;
            case $v675 < 2:
                $score += 70;
                break;
            case $v675 < 3:
                $score += 73;
                break;
            case $v675 >= 3:
                $score += 15;
                break;
        }

        $v949 = $this->checkDateDiffOfOrderAndLastOrderApplyByPanSelf();
        switch (true){
            case $v949 < 0:
                $score += 79;
                break;
            case $v949 < 10:
                $score += 42;
                break;
            case $v949 < 45:
                $score += 51;
                break;
            case $v949 >= 45:
                $score += 73;
                break;
        }

        $v325 = $this->checkLast90MobilePhotoAmount();
        switch (true){
            case $v325 < 400:
                $score += 65;
                break;
            case $v325 < 600:
                $score += 70;
                break;
            case $v325 < 2900:
                $score += 77;
                break;
            case $v325 >= 2900:
                $score += 101;
                break;
        }

        $v556 = $this->checkLast7dAppCnt();
        switch (true){
            case $v556 < 2:
                $score += 88;
                break;
            case $v556 < 9:
                $score += 75;
                break;
            case $v556 < 12:
                $score += 70;
                break;
            case $v556 >= 12:
                $score += 60;
                break;
        }

        $v561 = $this->checkLast3dLoanAppRate();
        switch (true){
            case $v561 < 8:
                $score += 86;
                break;
            case $v561 < 24:
                $score += 68;
                break;
            case $v561 >= 24:
                $score += 61;
                break;
        }

        return $score;
    }

    /**
     * 全老本老模型分V9
     * @return int
     */
    public function checkQLBLModelScoreV9(){
        $score = 0;
        $v557 = $this->checkLast30dAppCnt();
        switch (true){
            case $v557 < 10:
                $score += 50;
                break;
            case $v557 < 26:
                $score += 30;
                break;
            case $v557 < 34:
                $score += 21;
                break;
            case $v557 < 54:
                $score += 14;
                break;
            case $v557 >= 54:
                $score += 2;
                break;
        }

        $v1399 = $this->checkMaxDayDiffBtwLast90DaysNonOverdueClosingTimeAndThisApplyTimeInTPF();
        switch (true){
            case $v1399 < 38:
                $score += 15;
                break;
            case $v1399 < 52:
                $score += 40;
                break;
            case $v1399 >= 52:
                $score += 57;
                break;
        }

        $v585 = $this->checkHisSMDeviceIDApplyRejectCntInTotPlatporm();
        switch (true){
            case $v585 < 1:
                $score += 37;
                break;
            case $v585 < 2:
                $score += 31;
                break;
            case $v585 < 6:
                $score += 20;
                break;
            case $v585 >= 6:
                $score += 5;
                break;
        }

        $v202 = $this->checkLoanAppCnt();
        switch (true){
            case $v202 < 8:
                $score += 13;
                break;
            case $v202 < 24:
                $score += 23;
                break;
            case $v202 < 30:
                $score += 35;
                break;
            case $v202 >= 30:
                $score += 43;
                break;
        }

        $v1202 = $this->checkLast15dDueRepayCntLast30dCntRateTPF();
        switch (true){
            case $v1202 < 5:
                $score += 30;
                break;
            case $v1202 < 20:
                $score += 23;
                break;
            case $v1202 < 36:
                $score += 21;
                break;
            case $v1202 >= 36:
                $score += 12;
                break;
        }


        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 != 1){
            $score += 240;
        }else{
            $v821 = $this->checkMinOfSMSLoanOverdueDaysLast30Days();
            switch (true){
                case $v821 < 0:
                    $score += 29;
                    break;
                case $v821 < 2:
                    $score += 6;
                    break;
                case $v821 >= 2:
                    $score += 23;
                    break;
            }

            $v1102 = $this->checkHistAvgOfSMSLoanOverdueAmtTPF();
            switch (true){
                case $v1102 < 2500:
                    $score += 25;
                    break;
                case $v1102 < 4000:
                    $score += 22;
                    break;
                case $v1102 < 16000:
                    $score += 27;
                    break;
                case $v1102 >= 16000:
                    $score += 37;
                    break;
            }

            $v783 = $this->checkSumOfSMSEMIAmtLast60Days();
            switch (true){
                case $v783 < 4000:
                    $score += 24;
                    break;
                case $v783 < 18000:
                    $score += 26;
                    break;
                case $v783 < 42000:
                    $score += 28;
                    break;
                case $v783 >= 42000:
                    $score += 31;
                    break;
            }

            $v979 = $this->checkHistSMSCntOfLoanDisbursalTPF();
            switch (true){
                case $v979 < 3:
                    $score += 20;
                    break;
                case $v979 < 13:
                    $score += 31;
                    break;
                case $v979 < 22:
                    $score += 45;
                    break;
                case $v979 >= 22:
                    $score += 63;
                    break;
            }

            $v971 = $this->checkSMSCntOfLoanRejectionLast30DaysTPF();
            switch (true){
                case $v971 < 1:
                    $score += 30;
                    break;
                case $v971 < 2:
                    $score += 28;
                    break;
                case $v971 >= 2:
                    $score += 20;
                    break;
            }

            $v1076 = $this->checkMaxOfSMSDueRemindLoanAmtLast90DaysTPF();
            switch (true){
                case $v1076 < 10000:
                    $score += 22;
                    break;
                case $v1076 < 300000:
                    $score += 29;
                    break;
                case $v1076 >= 300000:
                    $score += 27;
                    break;
            }
        }

        $v726 = $this->checkLastLoanOrderCpDayTotPlatform();
        switch (true){
            case $v726 < 0:
                $score += 34;
                break;
            case $v726 < 1:
                $score += 28;
                break;
            case $v726 >= 1:
                $score += 6;
                break;
        }

        $v287 = $this->checkMaxDistAmongGPS();
        switch (true){
            case $v287 < 0:
                $score += 21;
                break;
            case $v287 < 18:
                $score += 48;
                break;
            case $v287 >= 18:
                $score += 35;
                break;
        }

        $v709 = $this->checkDateDiffOfOrderAndLastOrderApplyByPanTotPlatform();
        switch (true){
            case $v709 < 2:
                $score += 23;
                break;
            case $v709 < 4:
                $score += 28;
                break;
            case $v709 < 5:
                $score += 31;
                break;
            case $v709 < 7:
                $score += 34;
                break;
            case $v709 >= 7:
                $score += 41;
                break;
        }

        $v727 = $this->checkLast30dCpDayMaxTotPlatform();
        switch (true){
            case $v727 < 1:
                $score += 38;
                break;
            case $v727 < 2:
                $score += 27;
                break;
            case $v727 >= 2:
                $score += 9;
                break;
        }

        $v552 = $this->checkLastLoanOrderCpDay();
        switch (true){
            case $v552 < -1:
                $score += 30;
                break;
            case $v552 < 0:
                $score += 35;
                break;
            case $v552 < 1:
                $score += 27;
                break;
            case $v552 >= 1:
                $score += 11;
                break;
        }

        $v703 = $this->checkLast7dApplyCntBySMDeviceIDTotPlatform();
        switch (true){
            case $v703 < 3:
                $score += 40;
                break;
            case $v703 < 5:
                $score += 31;
                break;
            case $v703 < 6:
                $score += 27;
                break;
            case $v703 < 9:
                $score += 23;
                break;
            case $v703 >= 9:
                $score += 9;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 1000:
                $score += 15;
                break;
            case $v323 < 5500:
                $score += 25;
                break;
            case $v323 < 11000:
                $score += 49;
                break;
            case $v323 >= 11000:
                $score += 63;
                break;
        }

        $v203 = $this->checkLoanAppRatio();
        switch (true){
            case $v203 < 2:
                $score += 17;
                break;
            case $v203 < 5:
                $score += 23;
                break;
            case $v203 < 7:
                $score += 28;
                break;
            case $v203 < 11:
                $score += 35;
                break;
            case $v203 >= 11:
                $score += 32;
                break;
        }

        $v1344 = $this->checkLast30dTiqianOrderCntTPF();
        switch (true){
            case $v1344 < 2:
                $score += 15;
                break;
            case $v1344 < 6:
                $score += 21;
                break;
            case $v1344 < 13:
                $score += 25;
                break;
            case $v1344 < 15:
                $score += 32;
                break;
            case $v1344 >= 15:
                $score += 42;
                break;
        }

        $v1401 = $this->checkSumOfOverdueDayOfNonOverdueOrderLast90DaysInTPF();
        switch (true){
            case $v1401 < -24:
                $score += 38;
                break;
            case $v1401 < -9:
                $score += 31;
                break;
            case $v1401 < -1:
                $score += 27;
                break;
            case $v1401 >= -1:
                $score += 20;
                break;
        }

        $v588 = $this->checkPendingRepaymentCntOfPanInTotPlatporm();
        switch (true){
            case $v588 < 1:
                $score += 39;
                break;
            case $v588 < 3:
                $score += 61;
                break;
            case $v588 < 4:
                $score += 33;
                break;
            case $v588 >= 4:
                $score += 1;
                break;
        }

        $v957 = $this->checkHisDueAvgDayByPanSelf();
        switch (true){
            case $v957 < 1:
                $score += 29;
                break;
            case $v957 < 2:
                $score += 26;
                break;
            case $v957 >= 2:
                $score += 10;
                break;
        }

        $v1258 = $this->checkSelectedPreCreditLine();
        switch (true){
            case $v1258 < 2200:
                $score += 35;
                break;
            case $v1258 < 2600:
                $score += 21;
                break;
            case $v1258 < 4400:
                $score += 23;
                break;
            case $v1258 < 5000:
                $score += 28;
                break;
            case $v1258 >= 5000:
                $score += 44;
                break;
        }

        $v1196 = $this->checkLast7dRepayCntHisCntRateTPF();
        switch (true){
            case $v1196 < 18:
                $score += 52;
                break;
            case $v1196 < 24:
                $score += 36;
                break;
            case $v1196 < 32:
                $score += 23;
                break;
            case $v1196 >= 32:
                $score += 11;
                break;
        }

        return $score;
    }

    /**
     * 该Pan卡号是否命中催收API中的黑名单库
     * @return int
     */
    public function checkPanHitCollectionAPIBlackList(){
        $data = $this->getAssistData();
        return $data['isPanBlack'];
    }

    /**
     * 该Aadhaar卡号是否命中催收API中的黑名单库
     * @return int
     */
    public function checkAadhaarHitCollectionAPIBlackList(){
        $data = $this->getAssistData();
        return $data['isAadhaarBlack'];
    }

    /**
     * 该手机号是否命中催收API中的黑名单库
     * @return int
     */
    public function checkPhoneHitCollectionAPIBlackList(){
        $data = $this->getAssistData();
        return $data['isPhoneBlack'];
    }

    /**
     * 该设备IMEI号是否命中催收API中的黑名单库
     * @return int
     */
    public function checkIMEIHitCollectionAPIBlackList(){
        $data = $this->getAssistData();
        return $data['isImeiBlack'];
    }

    /**
     * 催收API中的已还款完结单数之和
     * @return int
     */
    public function checkSumOfRepayCountOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['repayCount'];
    }

    /**
     * 催收API中该Pan卡号的历史最大逾期天数
     * @return int
     */
    public function checkMaxOverdueDayOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['maxOverdueDay'];
    }

    /**
     * 催收API中该Pan卡号的历史逾期天数之和
     * @return int
     */
    public function checkSumOfOverdueDayOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['sumOverdueDay'];
    }

    /**
     * 催收API中该Pan卡号的历史平均逾期天数
     * @return int
     */
    public function checkAvgOverdueDayOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['avgOverdueDay'];
    }

    /**
     * 催收API中该Pan卡号的入催订单数之和
     * @return int
     */
    public function checkOrderCountOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['orderCount'];
    }

    /**
     * 催收API中该Pan卡号的催回订单数之和
     * @return int
     */
    public function checkClosedOrderCountOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['closeOrderCount'];
    }

    /**
     * 催收API中该Pan卡号的催回订单数占入催订单数的比例
     * @return int
     */
    public function checkClosedOrderRatioOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['closeOrderRatio'];
    }

    /**
     * 催收API中该Pan卡号的应还金额之和
     * @return int
     */
    public function checkSumOfPlanRepayAmtOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['sumTotalMoney'];
    }

    /**
     * 催收API中该Pan卡号的应还金额最大值
     * @return int
     */
    public function checkMaxPlanRepayAmtOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['maxTotalMoney'];
    }

    /**
     * 催收API中该Pan卡号的应还金额平均值
     * @return int
     */
    public function checkAvgPlanRepayAmtOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['avgTotalMoney'];
    }

    /**
     * 催收API中该Pan卡号的实还金额之和
     * @return int
     */
    public function checkSumOfActualRepayAmtOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['sumTrueTotalMoney'];
    }

    /**
     * 催收API中该Pan卡号的实还金额最大值
     * @return int
     */
    public function checkMaxActualRepayAmtOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['maxTrueTotalMoney'];
    }

    /**
     * 催收API中该Pan卡号的实还金额平均值
     * @return int
     */
    public function checkAvgActualRepayAmtOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['avgTrueTotalMoney'];
    }

    /**
     * 催收API中该Pan卡号各订单的实还金额占入催订单应还金额的比例
     * @return int
     */
    public function checkActualRepayAmtRatioOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['actualRepayAmtRatio'];
    }

    /**
     * 催收API中该Pan卡号是否为新客
     * @return int
     */
    public function checkIsFirstOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['isFirst'];
    }

    /**
     * 催收API中该Pan卡号中待还款的订单数
     * @return int
     */
    public function checkPendingRepaymentOrderCntOfCollectionAPI(){
        $data = $this->getAssistData();
        return $data['pendingOrderCount'];
    }

    /**
     * 全新本新无短信模型分V1
     */
    public function checkQXBXUserModelWoSmsV1(){
        $this->isGetData = false;
        $score = 0;
        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 600:
                $score += 27;
                break;
            case $v323 < 1800:
                $score += 34;
                break;
            case $v323 < 9800:
                $score += 43;
                break;
            case $v323 >= 9800:
                $score += 66;
                break;
        }

        $v1258 = $this->checkSelectedPreCreditLine();
        switch (true){
            case $v1258 < 1900:
                $score += 31;
                break;
            case $v1258 < 2086:
                $score += 62;
                break;
            case $v1258 >= 2086:
                $score += 26;
                break;
        }

        $v560 = $this->checkLast30dLoanAppCnt();
        switch (true){
            case $v560 < 1:
                $score += 47;
                break;
            case $v560 < 2:
                $score += 3;
                break;
            case $v560 < 3:
                $score += 24;
                break;
            case $v560 < 4:
                $score += 39;
                break;
            case $v560 >= 4:
                $score += 48;
                break;
        }

        $v324 = $this->checkLast30MobilePhotoAmount();
        switch (true){
            case $v324 < 350:
                $score += 32;
                break;
            case $v324 < 1600:
                $score += 44;
                break;
            case $v324 >= 1600:
                $score += 51;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 100:
                $score += 1;
                break;
            case $v142 < 200:
                $score += 30;
                break;
            case $v142 < 850:
                $score += 37;
                break;
            case $v142 >= 850:
                $score += 56;
                break;
        }

        $v683 = $this->checkHisRejectCntByPanInTotPlatform();
        switch (true){
            case $v683 < 1:
                $score += 48;
                break;
            case $v683 < 4:
                $score += 25;
                break;
            case $v683 >= 4:
                $score += 13;
                break;
        }

        $v1219 = $this->checkIsBangaloreExperianCreditReportReturnedNormal();
        if($v1219 != 1){
            $score += 167;
        }else{
            $v1253 = $this->checkBangaloreExperianTimeOfLastPayMent();
            switch (true){
                case $v1253 < 0:
                    $score += 22;
                    break;
                case $v1253 < 60:
                    $score += 54;
                    break;
                case $v1253 < 160:
                    $score += 45;
                    break;
                case $v1253 >= 160:
                    $score += 23;
                    break;
            }

            $v1241 = $this->checkBangaloreExperianLast6mAvgCreditAmt();
            switch (true){
                case $v1241 < 2000:
                    $score += 29;
                    break;
                case $v1241 < 15500:
                    $score += 53;
                    break;
                case $v1241 >= 15500:
                    $score += 45;
                    break;
            }

            $v1254 = $this->checkBangaloreExperianCreditScore();
            switch (true){
                case $v1254 < 530:
                    $score += 33;
                    break;
                case $v1254 < 650:
                    $score += 24;
                    break;
                case $v1254 < 710:
                    $score += 35;
                    break;
                case $v1254 >= 710:
                    $score += 46;
                    break;
            }

            $v1245 = $this->checkBangaloreExperianHisDueTotAmt();
            switch (true){
                case $v1245 < 1000:
                    $score += 56;
                    break;
                case $v1245 < 6000:
                    $score += 27;
                    break;
                case $v1245 < 52000:
                    $score += 5;
                    break;
                case $v1245 >= 52000:
                    $score += 40;
                    break;
            }

            $v1237 = $this->checkBangaloreExperianHisMaxCreditAmt();
            switch (true){
                case $v1237 < 0:
                    $score += 33;
                    break;
                case $v1237 < 100000:
                    $score += 36;
                    break;
                case $v1237 < 380000:
                    $score += 40;
                    break;
                case $v1237 >= 380000:
                    $score += 42;
                    break;
            }

            $v1229 = $this->checkBangaloreExperianLast180dEnquiryCnt();
            switch (true){
                case $v1229 < 0:
                    $score += -13;
                    break;
                case $v1229 < 4:
                    $score += 18;
                    break;
                case $v1229 < 8:
                    $score += 36;
                    break;
                case $v1229 >= 8:
                    $score += 82;
                    break;
            }

            $v1223 = $this->checkBangaloreExperianCreditAccountClosed();
            switch (true){
                case $v1223 < 3:
                    $score += 12;
                    break;
                case $v1223 < 6:
                    $score += 31;
                    break;
                case $v1223 < 27:
                    $score += 56;
                    break;
                case $v1223 >= 27:
                    $score += 101;
                    break;
            }

            $v1226 = $this->checkBangaloreExperianOutstandingBalanceUnSecured();
            switch (true){
                case $v1226 < 0:
                    $score += 34;
                    break;
                case $v1226 < 20000:
                    $score += 28;
                    break;
                case $v1226 < 100000:
                    $score += 38;
                    break;
                case $v1226 < 320000:
                    $score += 48;
                    break;
                case $v1226 >= 320000:
                    $score += 60;
                    break;
            }

            $v1244 = $this->checkBangaloreExperianTimeOfFirstCreditTimeToNow();
            switch (true){
                case $v1244 < 0:
                    $score += 25;
                    break;
                case $v1244 < 1800:
                    $score += 35;
                    break;
                case $v1244 < 2400:
                    $score += 43;
                    break;
                case $v1244 >= 2400:
                    $score += 51;
                    break;
            }
        }

        return $score;
    }

    /**
     * 全老本老无短信模型分V1
     */
    public function checkQLBLUserModelWoSmsV1(){
        $score = 0;
        $v325 = $this->checkLast90MobilePhotoAmount();
        switch (true){
            case $v325 < 100:
                $score += 40;
                break;
            case $v325 < 400:
                $score += 33;
                break;
            case $v325 < 900:
                $score += 42;
                break;
            case $v325 >= 900:
                $score += 50;
                break;
        }

        $v552 = $this->checkLastLoanOrderCpDay();
        switch (true){
            case $v552 < -1:
                $score += 41;
                break;
            case $v552 < 1:
                $score += 52;
                break;
            case $v552 >= 1:
                $score += 15;
                break;
        }

        $v581 = $this->checkApplyCntLast1MonthByMobileInTotPlatporm();
        switch (true){
            case $v581 < 16:
                $score += 27;
                break;
            case $v581 < 21:
                $score += 42;
                break;
            case $v581 >= 21:
                $score += 81;
                break;
        }

        $v563 = $this->checkLast30dLoanAppRate();
        switch (true){
            case $v563 < 2:
                $score += 66;
                break;
            case $v563 < 12:
                $score += 49;
                break;
            case $v563 < 30:
                $score += 35;
                break;
            case $v563 < 40:
                $score += 48;
                break;
            case $v563 >= 40:
                $score += 58;
                break;
        }

        $v1437 = $this->checkLast1dApplyCntBySMDeviceIDTPF();
        switch (true){
            case $v1437 < 2:
                $score += 53;
                break;
            case $v1437 < 3:
                $score += 44;
                break;
            case $v1437 < 4:
                $score += 35;
                break;
            case $v1437 >= 4:
                $score += 12;
                break;
        }

        $v1202 = $this->checkLast15dDueRepayCntLast30dCntRateTPF();
        switch (true){
            case $v1202 < 9:
                $score += 62;
                break;
            case $v1202 < 13:
                $score += 42;
                break;
            case $v1202 < 40:
                $score += 18;
                break;
            case $v1202 >= 40:
                $score += -12;
                break;
        }

        $v557 = $this->checkLast30dAppCnt();
        switch (true){
            case $v557 < 8:
                $score += 62;
                break;
            case $v557 < 12:
                $score += 55;
                break;
            case $v557 < 32:
                $score += 44;
                break;
            case $v557 >= 32:
                $score += 31;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 1500:
                $score += 38;
                break;
            case $v323 < 3500:
                $score += 43;
                break;
            case $v323 < 11000:
                $score += 49;
                break;
            case $v323 >= 11000:
                $score += 55;
                break;
        }

        $v1285 = $this->checkTeleCollectionNotConnectRateTPF();
        switch (true){
            case $v1285 < 34:
                $score += 49;
                break;
            case $v1285 < 68:
                $score += 39;
                break;
            case $v1285 < 78:
                $score += 31;
                break;
            case $v1285 < 100:
                $score += 21;
                break;
            case $v1285 >= 100:
                $score += 38;
                break;
        }

        $v556 = $this->checkLast7dAppCnt();
        switch (true){
            case $v556 < 2:
                $score += 61;
                break;
            case $v556 < 6:
                $score += 50;
                break;
            case $v556 < 21:
                $score += 34;
                break;
            case $v556 >= 21:
                $score += 18;
                break;
        }

        $v1316 = $this->checkTeleCollectionNotConnectAndRiskControl12CntTPF();
        switch (true){
            case $v1316 < 1:
                $score += 45;
                break;
            case $v1316 < 3:
                $score += 38;
                break;
            case $v1316 >= 3:
                $score += 32;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 4:
                $score += 22;
                break;
            case $v103 < 5:
                $score += 26;
                break;
            case $v103 < 6:
                $score += 47;
                break;
            case $v103 >= 6:
                $score += 62;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 350:
                $score += 28;
                break;
            case $v142 < 550:
                $score += 40;
                break;
            case $v142 < 1150:
                $score += 51;
                break;
            case $v142 >= 1150:
                $score += 60;
                break;
        }

        $v1277 = $this->checkCollectionAvgTimesTPF();
        switch (true){
            case $v1277 < 0:
                $score += 49;
                break;
            case $v1277 < 3:
                $score += 46;
                break;
            case $v1277 < 4:
                $score += 39;
                break;
            case $v1277 >= 4:
                $score += 30;
                break;
        }

        $v561 = $this->checkLast3dLoanAppRate();
        switch (true){
            case $v561 < 8:
                $score += 46;
                break;
            case $v561 < 24:
                $score += 34;
                break;
            case $v561 < 44:
                $score += 36;
                break;
            case $v561 >= 44:
                $score += 42;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 26000:
                $score += 35;
                break;
            case $v206 < 36000:
                $score += 45;
                break;
            case $v206 < 54000:
                $score += 65;
                break;
            case $v206 >= 54000:
                $score += 76;
                break;
        }

        return $score;
    }

    /**
     * 全老本老模型分V10
     */
    public function checkQLBLModelScoreV10(){
        $score = 0;
        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 200:
                $score += 22;
                break;
            case $v142 < 500:
                $score += 34;
                break;
            case $v142 < 800:
                $score += 46;
                break;
            case $v142 >= 800:
                $score += 57;
                break;
        }

        $v1414 = $this->checkMaxOverdueDayOfCollectionAPI();
        switch (true){
            case $v1414 < 2:
                $score += 46;
                break;
            case $v1414 < 3:
                $score += 37;
                break;
            case $v1414 >= 3:
                $score += 28;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 5:
                $score += 17;
                break;
            case $v103 < 6:
                $score += 50;
                break;
            case $v103 >= 6:
                $score += 62;
                break;
        }

        $v553 = $this->checkLast30dCpDayMax();
        switch (true){
            case $v553 < 1:
                $score += 45;
                break;
            case $v553 < 2:
                $score += 40;
                break;
            case $v553 >= 2:
                $score += 19;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 == 0){
            $score += 49;
        }else {
            $v811 = $this->checkHistSumOfSMSLoanOverdueDays();
            switch (true) {
                case $v811 < 0:
                    $score += 46;
                    break;
                case $v811 < 3:
                    $score += 41;
                    break;
                case $v811 < 23:
                    $score += 33;
                    break;
                case $v811 >= 23:
                    $score += 37;
                    break;
            }
        }

        $v1466 = $this->checkAvgDayDiffBtwHistLoanTimeAndThisApplyTimeInTPF();
        switch (true){
            case $v1466 < 20:
                $score += 33;
                break;
            case $v1466 < 35:
                $score += 46;
                break;
            case $v1466 >= 35:
                $score += 52;
                break;
        }

        $v1399 = $this->checkMaxDayDiffBtwLast90DaysNonOverdueClosingTimeAndThisApplyTimeInTPF();
        switch (true){
            case $v1399 < 22:
                $score += 21;
                break;
            case $v1399 < 42:
                $score += 41;
                break;
            case $v1399 < 70:
                $score += 53;
                break;
            case $v1399 >= 70:
                $score += 71;
                break;
        }

        $v702 = $this->checkLast60dRejectCntBySMDeviceIDTotPlatform();
        switch (true){
            case $v702 < 1:
                $score += 49;
                break;
            case $v702 < 2:
                $score += 36;
                break;
            case $v702 < 4:
                $score += 27;
                break;
            case $v702 >= 4:
                $score += 14;
                break;
        }

        $v727 = $this->checkLast30dCpDayMaxTotPlatform();
        switch (true){
            case $v727 < 2:
                $score += 50;
                break;
            case $v727 < 4:
                $score += 19;
                break;
            case $v727 >= 4:
                $score += 3;
                break;
        }

        $v557 = $this->checkLast30dAppCnt();
        switch (true){
            case $v557 < 14:
                $score += 74;
                break;
            case $v557 < 22:
                $score += 53;
                break;
            case $v557 < 56:
                $score += 32;
                break;
            case $v557 >= 56:
                $score += 3;
                break;
        }

        $v1426 = $this->checkActualRepayAmtRatioOfCollectionAPI();
        switch (true){
            case $v1426 < 0:
                $score += 53;
                break;
            case $v1426 < 95:
                $score += -11;
                break;
            case $v1426 >= 95:
                $score += 41;
                break;
        }

        $v701 = $this->checkLast60dApplyCntBySMDeviceIDTotPlatform();
        switch (true){
            case $v701 < 8:
                $score += 42;
                break;
            case $v701 < 26:
                $score += 40;
                break;
            case $v701 < 42:
                $score += 44;
                break;
            case $v701 >= 42:
                $score += 48;
                break;
        }

        $v1344 = $this->checkLast30dTiqianOrderCntTPF();
        switch (true){
            case $v1344 < 2:
                $score += 31;
                break;
            case $v1344 < 10:
                $score += 36;
                break;
            case $v1344 < 16:
                $score += 44;
                break;
            case $v1344 < 19:
                $score += 49;
                break;
            case $v1344 >= 19:
                $score += 59;
                break;
        }

        $v726 = $this->checkLastLoanOrderCpDayTotPlatform();
        switch (true){
            case $v726 < -3:
                $score += 32;
                break;
            case $v726 < 1:
                $score += 49;
                break;
            case $v726 >= 1:
                $score += 10;
                break;
        }

        $v1284 = $this->checkTeleCollectionConnectRateTPF();
        switch (true){
            case $v1284 < 0:
                $score += 46;
                break;
            case $v1284 < 2:
                $score += 38;
                break;
            case $v1284 < 24:
                $score += 22;
                break;
            case $v1284 < 92:
                $score += 41;
                break;
            case $v1284 >= 92:
                $score += 52;
                break;
        }

        $v713 = $this->checkHisRepayCntByPanTotPlatform();
        switch (true){
            case $v713 < 10:
                $score += 34;
                break;
            case $v713 < 20:
                $score += 39;
                break;
            case $v713 < 40:
                $score += 46;
                break;
            case $v713 >= 40:
                $score += 54;
                break;
        }

        return $score;
    }

    /**
     * 全老本新-无征信-模型分V3
     */
    public function checkQLBXUserNullCIRModelV3(){
        $score = 0;
        $v684 = $this->checkHisApplyCntByPanInTotPlatform();
        switch (true){
            case $v684 < 6:
                $score += 88;
                break;
            case $v684 < 8:
                $score += 63;
                break;
            case $v684 < 34:
                $score += 47;
                break;
            case $v684 >= 34:
                $score += 132;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 == 0){
            $score += 137;
        }else {
            $v1065 = $this->checkMinOfSMSDueRemindLoanAmtLast7DaysTPF();
            switch (true){
                case $v1065 < 4000:
                    $score += 61;
                    break;
                case $v1065 < 5000:
                    $score += 79;
                    break;
                case $v1065 < 10000:
                    $score += 96;
                    break;
                case $v1065 >= 10000:
                    $score += 56;
                    break;
            }

            $v626 = $this->checkSMSCntOfOverdueRemindLast7Days();
            switch (true) {
                case $v626 < 1:
                    $score += 70;
                    break;
                case $v626 < 3:
                    $score += 65;
                    break;
                case $v626 >= 3:
                    $score += 41;
                    break;
            }

            $v610 = $this->checkHistSMSCntOfLoanDisbursal();
            switch (true){
                case $v610 < 1:
                    $score += 55;
                    break;
                case $v610 < 3:
                    $score += 59;
                    break;
                case $v610 < 6:
                    $score += 70;
                    break;
                case $v610 < 18:
                    $score += 83;
                    break;
                case $v610 >= 18:
                    $score += 111;
                    break;
            }
        }

        $v1415 = $this->checkSumOfOverdueDayOfCollectionAPI();
        switch (true){
            case $v1415 < 0:
                $score += 88;
                break;
            case $v1415 < 4:
                $score += 68;
                break;
            case $v1415 < 10:
                $score += 30;
                break;
            case $v1415 >= 10:
                $score += -19;
                break;
        }

        $v556 = $this->checkLast7dAppCnt();
        switch (true){
            case $v556 < 1:
                $score += 99;
                break;
            case $v556 < 5:
                $score += 84;
                break;
            case $v556 < 15:
                $score += 63;
                break;
            case $v556 < 22:
                $score += 45;
                break;
            case $v556 >= 22:
                $score += 14;
                break;
        }

        $v1401 = $this->checkSumOfOverdueDayOfNonOverdueOrderLast90DaysInTPF();
        switch (true){
            case $v1401 < -13:
                $score += 112;
                break;
            case $v1401 < 0:
                $score += 83;
                break;
            case $v1401 < 5:
                $score += 58;
                break;
            case $v1401 >= 5:
                $score += -14;
                break;
        }

        $v202 = $this->checkLoanAppCnt();
        switch (true){
            case $v202 < 3:
                $score += 22;
                break;
            case $v202 < 15:
                $score += 73;
                break;
            case $v202 >= 15:
                $score += 66;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 500:
                $score += 48;
                break;
            case $v323 < 2000:
                $score += 59;
                break;
            case $v323 < 11000:
                $score += 74;
                break;
            case $v323 >= 11000:
                $score += 89;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 4:
                $score += 46;
                break;
            case $v103 < 5:
                $score += 50;
                break;
            case $v103 < 6:
                $score += 71;
                break;
            case $v103 >= 6:
                $score += 84;
                break;
        }

        return $score;
    }

    /**
     * 全新本新模型分V8
     */
    public function checkQXBXUserModelV8(){
        $this->isGetData = false;
        $score = 42;
        $v189 = $this->checkApplyCntLast1hourByIP();
        switch (true){
            case $v189 < 2:
                $score += 17;
                break;
            case $v189 >= 2:
                $score += 8;
                break;
        }

        $v326 = $this->checkFirstPhotoTimeToNow();
        switch (true){
            case $v326 < 0:
                $score += 15;
                break;
            case $v326 < 300:
                $score += 11;
                break;
            case $v326 < 500:
                $score += 13;
                break;
            case $v326 >= 500:
                $score += 16;
                break;
        }

        $v683 = $this->checkHisRejectCntByPanInTotPlatform();
        switch (true){
            case $v683 < 1:
                $score += 19;
                break;
            case $v683 < 2:
                $score += 9;
                break;
            case $v683 < 4:
                $score += 6;
                break;
            case $v683 >= 4:
                $score += -1;
                break;
        }

        $v681 = $this->checkMaxDateDiffOfRegisterAndOrderByPhoneTotPlatform();
        switch (true){
            case $v681 < 1:
                $score += 14;
                break;
            case $v681 < 2:
                $score += 17;
                break;
            case $v681 < 25:
                $score += 21;
                break;
            case $v681 >= 25:
                $score += 8;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 == 0){
            $score += 180;
        }else {
            $v1116 = $this->checkMaxOfSMSLoanOverdueAmtLast90DaysTPF();
            switch (true){
                case $v1116 < 1800:
                    $score += 15;
                    break;
                case $v1116 < 3600:
                    $score += 0;
                    break;
                case $v1116 < 5000:
                    $score += -7;
                    break;
                case $v1116 < 12000:
                    $score += 12;
                    break;
                case $v1116 >= 12000:
                    $score += 30;
                    break;
            }

            $v1040 = $this->checkMaxOfHistSMSEMIAmtTPF();
            switch (true){
                case $v1040 < 1500:
                    $score += 13;
                    break;
                case $v1040 < 4500:
                    $score += 14;
                    break;
                case $v1040 < 29500:
                    $score += 15;
                    break;
                case $v1040 >= 29500:
                    $score += 17;
                    break;
            }

            $v1092 = $this->checkMaxOfSMSLoanOverdueDaysLast60DaysTPF();
            switch (true){
                case $v1092 < 0:
                    $score += 21;
                    break;
                case $v1092 >= 0:
                    $score += -18;
                    break;
            }

            $v606 = $this->checkSMSCntOfLoanApprovalLast7Days();
            switch (true){
                case $v606 < 1:
                    $score += 11;
                    break;
                case $v606 < 5:
                    $score += 13;
                    break;
                case $v606 < 11:
                    $score += 18;
                    break;
                case $v606 >= 11:
                    $score += 25;
                    break;
            }

            $v733 = $this->checkHistMinOfSMSLoanCreditAmt();
            switch (true){
                case $v733 < 0:
                    $score += 12;
                    break;
                case $v733 < 2000:
                    $score += 16;
                    break;
                case $v733 < 5000:
                    $score += 18;
                    break;
                case $v733 >= 5000:
                    $score += 13;
                    break;
            }

            $v598 = $this->checkSMSCntOfLoanApplicationSubmissionLast60Days();
            switch (true){
                case $v598 < 2:
                    $score += 13;
                    break;
                case $v598 < 5:
                    $score += 15;
                    break;
                case $v598 < 8:
                    $score += 16;
                    break;
                case $v598 >= 8:
                    $score += 15;
                    break;
            }

            $v624 = $this->checkSMSCntOfLoanPayOffLast90Days();
            switch (true){
                case $v624 < 2:
                    $score += 10;
                    break;
                case $v624 >= 2:
                    $score += 53;
                    break;
            }

            $v626 = $this->checkSMSCntOfOverdueRemindLast7Days();
            switch (true) {
                case $v626 < 1:
                    $score += 17;
                    break;
                case $v626 < 2:
                    $score += 11;
                    break;
                case $v626 < 5:
                    $score += 6;
                    break;
                case $v626 >= 5:
                    $score += -16;
                    break;
            }

            $v960 = $this->checkSMSCntOfLoanApplicationTrialLast7DaysTPF();
            switch (true) {
                case $v960 < 5:
                    $score += 12;
                    break;
                case $v960 < 8:
                    $score += 15;
                    break;
                case $v960 < 14:
                    $score += 19;
                    break;
                case $v960 >= 14:
                    $score += 25;
                    break;
            }

            $v605 = $this->checkHistSMSCntOfLoanApproval();
            switch (true) {
                case $v605 < 4:
                    $score += 13;
                    break;
                case $v605 < 72:
                    $score += 14;
                    break;
                case $v605 >= 72:
                    $score += 15;
                    break;
            }

            $v981 = $this->checkSMSCntOfLoanDisbursalLast30DaysTPF();
            switch (true){
                case $v981 < 1:
                    $score += 5;
                    break;
                case $v981 < 2:
                    $score += 19;
                    break;
                case $v981 < 4:
                    $score += 42;
                    break;
                case $v981 >= 4:
                    $score += 70;
                    break;
            }

            $v1051 = $this->checkSumOfSMSEMIAmtLast60DaysTPF();
            switch (true){
                case $v1051 < 3000:
                    $score += 14;
                    break;
                case $v1051 >= 3000:
                    $score += 13;
                    break;
            }
        }

        $v185 = $this->checkApplyCntLast7daysByIP();
        switch (true){
            case $v185 < 2:
                $score += 15;
                break;
            case $v185 < 3:
                $score += 13;
                break;
            case $v185 >= 3:
                $score += 10;
                break;
        }

        $v584 = $this->checkLast30ApplyCntBySMDeviceIDInTotPlatporm();
        switch (true){
            case $v584 < 3:
                $score += 15;
                break;
            case $v584 < 5:
                $score += 7;
                break;
            case $v584 >= 5:
                $score += 5;
                break;
        }

        $v1219 = $this->checkIsBangaloreExperianCreditReportReturnedNormal();
        if($v1219 != 1){
            $score += 56;
        }else {
            $v1243 = $this->checkBangaloreExperianTimeOfLastCreditTimeToNow();
            switch (true) {
                case $v1243 < 0:
                    $score += 4;
                    break;
                case $v1243 < 80:
                    $score += 28;
                    break;
                case $v1243 < 200:
                    $score += 19;
                    break;
                case $v1243 < 240:
                    $score += 14;
                    break;
                case $v1243 >= 240:
                    $score += 6;
                    break;
            }

            $v1251 = $this->checkBangaloreExperianHisMaxDueDaysLevel();
            switch (true) {
                case $v1251 < 0:
                    $score += 11;
                    break;
                case $v1251 < 3:
                    $score += 15;
                    break;
                case $v1251 >= 3:
                    $score += 13;
                    break;
            }

            $v1241 = $this->checkBangaloreExperianLast6mAvgCreditAmt();
            switch (true){
                case $v1241 < 1200:
                    $score += 11;
                    break;
                case $v1241 < 4200:
                    $score += 19;
                    break;
                case $v1241 >= 4200:
                    $score += 22;
                    break;
            }

            $v1226 = $this->checkBangaloreExperianOutstandingBalanceUnSecured();
            switch (true){
                case $v1226 < 20000:
                    $score += 13;
                    break;
                case $v1226 < 110000:
                    $score += 14;
                    break;
                case $v1226 < 320000:
                    $score += 15;
                    break;
                case $v1226 >= 320000:
                    $score += 17;
                    break;
            }

            $v1244 = $this->checkBangaloreExperianTimeOfFirstCreditTimeToNow();
            switch (true){
                case $v1244 < 200:
                    $score += 10;
                    break;
                case $v1244 < 700:
                    $score += 13;
                    break;
                case $v1244 < 1500:
                    $score += 14;
                    break;
                case $v1244 < 2300:
                    $score += 15;
                    break;
                case $v1244 >= 2300:
                    $score += 18;
                    break;
            }

            $v1223 = $this->checkBangaloreExperianCreditAccountClosed();
            switch (true){
                case $v1223 < 0:
                    $score += -9;
                    break;
                case $v1223 < 3:
                    $score += 1;
                    break;
                case $v1223 < 9:
                    $score += 12;
                    break;
                case $v1223 < 18:
                    $score += 30;
                    break;
                case $v1223 >= 18:
                    $score += 51;
                    break;
            }
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 150:
                $score += 1;
                break;
            case $v142 < 400:
                $score += 12;
                break;
            case $v142 < 1200:
                $score += 17;
                break;
            case $v142 >= 1200:
                $score += 22;
                break;
        }

        $v193 = $this->checkApplyCnt500mAwayFromGPSlocLast1Hour();
        switch (true){
            case $v193 < 0:
                $score += 11;
                break;
            case $v193 < 2:
                $score += 15;
                break;
            case $v193 < 3:
                $score += 13;
                break;
            case $v193 >= 3:
                $score += 10;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 18000:
                $score += 11;
                break;
            case $v206 < 19000:
                $score += 9;
                break;
            case $v206 < 27000:
                $score += 13;
                break;
            case $v206 < 41000:
                $score += 16;
                break;
            case $v206 >= 41000:
                $score += 21;
                break;
        }

        $v692 = $this->checkLast7dApplyCntByPanInTotPlatform();
        switch (true){
            case $v692 < 3:
                $score += 14;
                break;
            case $v692 >= 3:
                $score += 11;
                break;
        }

        $v917 = $this->checkHisApplyCnt500mAwayFromGPSLocAllPlatform();
        switch (true){
            case $v917 < 0:
                $score += 5;
                break;
            case $v917 < 4:
                $score += 11;
                break;
            case $v917 < 9:
                $score += 16;
                break;
            case $v917 >= 9:
                $score += 20;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 1000:
                $score += 8;
                break;
            case $v323 < 2200:
                $score += 13;
                break;
            case $v323 < 3000:
                $score += 16;
                break;
            case $v323 >= 3000:
                $score += 20;
                break;
        }

        $v679 = $this->checkPendingRepaymentTotAmtOfPanTotPlatform();
        switch (true){
            case $v679 < 1500:
                $score += 13;
                break;
            case $v679 < 1900:
                $score += 11;
                break;
            case $v679 >= 1900:
                $score += 26;
                break;
        }

        $v202 = $this->checkLoanAppCnt();
        switch (true){
            case $v202 < 1:
                $score += 15;
                break;
            case $v202 < 2:
                $score += -2;
                break;
            case $v202 < 4:
                $score += 7;
                break;
            case $v202 < 6:
                $score += 16;
                break;
            case $v202 >= 6:
                $score += 19;
                break;
        }

        return $score;
    }

    /**
     * 全新本新-无征信-模型分V3
     */
    public function checkQXBXUserNullCIRModelV3(){
        $score = 0;
        $v189 = $this->checkApplyCntLast1hourByIP();
        switch (true){
            case $v189 < 2:
                $score += 34;
                break;
            case $v189 >= 2:
                $score += 23;
                break;
        }

        $v679 = $this->checkPendingRepaymentTotAmtOfPanTotPlatform();
        switch (true){
            case $v679 < 1500:
                $score += 29;
                break;
            case $v679 < 1900:
                $score += 27;
                break;
            case $v679 >= 1900:
                $score += 46;
                break;
        }

        $v326 = $this->checkFirstPhotoTimeToNow();
        switch (true){
            case $v326 < 0:
                $score += 33;
                break;
            case $v326 < 300:
                $score += 26;
                break;
            case $v326 < 500:
                $score += 29;
                break;
            case $v326 >= 500:
                $score += 34;
                break;
        }

        $v681 = $this->checkMaxDateDiffOfRegisterAndOrderByPhoneTotPlatform();
        switch (true){
            case $v681 < 1:
                $score += 31;
                break;
            case $v681 < 2:
                $score += 33;
                break;
            case $v681 < 25:
                $score += 36;
                break;
            case $v681 >= 25:
                $score += 26;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 18000:
                $score += 26;
                break;
            case $v206 < 19000:
                $score += 23;
                break;
            case $v206 < 27000:
                $score += 29;
                break;
            case $v206 < 41000:
                $score += 34;
                break;
            case $v206 >= 41000:
                $score += 41;
                break;
        }

        $v1258 = $this->checkSelectedPreCreditLine();
        switch (true){
            case $v1258 < 1900:
                $score += 26;
                break;
            case $v1258 >= 1900:
                $score += 44;
                break;
        }

        $v202 = $this->checkLoanAppCnt();
        switch (true){
            case $v202 < 1:
                $score += 31;
                break;
            case $v202 < 2:
                $score += 10;
                break;
            case $v202 < 4:
                $score += 22;
                break;
            case $v202 < 6:
                $score += 33;
                break;
            case $v202 >= 6:
                $score += 37;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 150:
                $score += 13;
                break;
            case $v142 < 400:
                $score += 28;
                break;
            case $v142 < 1200:
                $score += 34;
                break;
            case $v142 >= 1200:
                $score += 42;
                break;
        }

        $v582 = $this->checkHisSMDeviceIDApplyCntInTotPlatporm();
        switch (true){
            case $v582 < 3:
                $score += 34;
                break;
            case $v582 < 5:
                $score += 22;
                break;
            case $v582 >= 5:
                $score += 14;
                break;
        }

        $v685 = $this->checkLast90dRejectCntByPanInTotPlatform();
        switch (true){
            case $v685 < 1:
                $score += 35;
                break;
            case $v685 < 4:
                $score += 16;
                break;
            case $v685 >= 4:
                $score += 9;
                break;
        }

        $v917 = $this->checkHisApplyCnt500mAwayFromGPSLocAllPlatform();
        switch (true){
            case $v917 < 0:
                $score += 22;
                break;
            case $v917 < 4:
                $score += 28;
                break;
            case $v917 < 9:
                $score += 32;
                break;
            case $v917 >= 9:
                $score += 36;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 == 0){
            $score += 160;
        }else {
            $v1040 = $this->checkMaxOfHistSMSEMIAmtTPF();
            switch (true){
                case $v1040 < 1500:
                    $score += 26;
                    break;
                case $v1040 < 4500:
                    $score += 31;
                    break;
                case $v1040 < 29500:
                    $score += 35;
                    break;
                case $v1040 >= 29500:
                    $score += 40;
                    break;
            }

            $v617 = $this->checkSMSCntOfLoanDueRemindLast30Days();
            switch (true){
                case $v617 < 4:
                    $score += 27;
                    break;
                case $v617 < 10:
                    $score += 30;
                    break;
                case $v617 < 44:
                    $score += 33;
                    break;
                case $v617 >= 44:
                    $score += 37;
                    break;
            }

            $v733 = $this->checkHistMinOfSMSLoanCreditAmt();
            switch (true){
                case $v733 < 0:
                    $score += 28;
                    break;
                case $v733 < 2000:
                    $score += 33;
                    break;
                case $v733 < 5000:
                    $score += 36;
                    break;
                case $v733 >= 5000:
                    $score += 30;
                    break;
            }

            $v1079 = $this->checkHistSumOfSMSLoanOverdueDaysTPF();
            switch (true){
                case $v1079 < 0:
                    $score += 37;
                    break;
                case $v1079 >= 0:
                    $score += 11;
                    break;
            }

            $v981 = $this->checkSMSCntOfLoanDisbursalLast30DaysTPF();
            switch (true){
                case $v981 < 1:
                    $score += 20;
                    break;
                case $v981 < 2:
                    $score += 37;
                    break;
                case $v981 < 4:
                    $score += 65;
                    break;
                case $v981 >= 4:
                    $score += 98;
                    break;
            }
        }

        return $score;
    }

    /**
     * 全新本新-无征信无短信-模型分V1
     */
    public function checkQXBXUserNoCIRNoSMSModelV1(){
        $score = 0;
        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 18000:
                $score += 26;
                break;
            case $v206 < 19000:
                $score += 23;
                break;
            case $v206 < 27000:
                $score += 29;
                break;
            case $v206 < 41000:
                $score += 34;
                break;
            case $v206 >= 41000:
                $score += 41;
                break;
        }

        $v679 = $this->checkPendingRepaymentTotAmtOfPanTotPlatform();
        switch (true){
            case $v679 < 1500:
                $score += 29;
                break;
            case $v679 < 1900:
                $score += 27;
                break;
            case $v679 >= 1900:
                $score += 47;
                break;
        }

        $v1258 = $this->checkSelectedPreCreditLine();
        switch (true){
            case $v1258 < 1900:
                $score += 26;
                break;
            case $v1258 >= 1900:
                $score += 44;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 5:
                $score += 25;
                break;
            case $v103 < 6:
                $score += 34;
                break;
            case $v103 >= 6:
                $score += 35;
                break;
        }

        $v326 = $this->checkFirstPhotoTimeToNow();
        switch (true){
            case $v326 < 0:
                $score += 34;
                break;
            case $v326 < 300:
                $score += 25;
                break;
            case $v326 < 500:
                $score += 28;
                break;
            case $v326 >= 500:
                $score += 34;
                break;
        }

        $v583 = $this->checkLast90ApplyCntBySMDeviceIDInTotPlatporm();
        switch (true){
            case $v583 < 3:
                $score += 34;
                break;
            case $v583 < 5:
                $score += 18;
                break;
            case $v583 >= 5:
                $score += 11;
                break;
        }

        $v685 = $this->checkLast90dRejectCntByPanInTotPlatform();
        switch (true){
            case $v685 < 1:
                $score += 35;
                break;
            case $v685 < 4:
                $score += 17;
                break;
            case $v685 >= 4:
                $score += 11;
                break;
        }

        $v101 = $this->checkUserAge();
        switch (true){
            case $v101 < 23:
                $score += 18;
                break;
            case $v101 < 29:
                $score += 27;
                break;
            case $v101 < 33:
                $score += 32;
                break;
            case $v101 >= 33:
                $score += 35;
                break;
        }

        $v681 = $this->checkMaxDateDiffOfRegisterAndOrderByPhoneTotPlatform();
        switch (true){
            case $v681 < 1:
                $score += 31;
                break;
            case $v681 < 2:
                $score += 34;
                break;
            case $v681 < 25:
                $score += 38;
                break;
            case $v681 >= 25:
                $score += 24;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 150:
                $score += 12;
                break;
            case $v142 < 400:
                $score += 28;
                break;
            case $v142 < 1200:
                $score += 34;
                break;
            case $v142 >= 1200:
                $score += 43;
                break;
        }

        $v917 = $this->checkHisApplyCnt500mAwayFromGPSLocAllPlatform();
        switch (true){
            case $v917 < 0:
                $score += 21;
                break;
            case $v917 < 4:
                $score += 28;
                break;
            case $v917 < 9:
                $score += 32;
                break;
            case $v917 >= 9:
                $score += 37;
                break;
        }

        $v699 = $this->checkLast7dApplyCntByPhoneTotPlatform();
        switch (true){
            case $v699 < 3:
                $score += 31;
                break;
            case $v699 >= 3:
                $score += 27;
                break;
        }

        $v189 = $this->checkApplyCntLast1hourByIP();
        switch (true){
            case $v189 < 2:
                $score += 34;
                break;
            case $v189 >= 2:
                $score += 22;
                break;
        }

        $v356 = $this->checkIsSMSRecordGrabNormal();
        if($v356 == 0){
            $score += 94;
        }else {
            $v803 = $this->checkSumOfSMSDueRemindLoanAmtLast60Days();
            switch (true){
                case $v803 < 0:
                    $score += 26;
                    break;
                case $v803 < 60000:
                    $score += 30;
                    break;
                case $v803 < 160000:
                    $score += 33;
                    break;
                case $v803 < 820000:
                    $score += 35;
                    break;
                case $v803 >= 820000:
                    $score += 29;
                    break;
            }

            $v1072 = $this->checkMaxOfSMSDueRemindLoanAmtLast60DaysTPF();
            switch (true){
                case $v1072 < 0:
                    $score += 27;
                    break;
                case $v1072 < 10000:
                    $score += 31;
                    break;
                case $v1072 < 50000:
                    $score += 36;
                    break;
                case $v1072 < 120000:
                    $score += 33;
                    break;
                case $v1072 >= 120000:
                    $score += 30;
                    break;
            }

            $v1061 = $this->checkMinOfHistSMSDueRemindLoanAmtTPF();
            switch (true){
                case $v1061 < 0:
                    $score += 23;
                    break;
                case $v1061 < 2400:
                    $score += 32;
                    break;
                case $v1061 < 4400:
                    $score += 35;
                    break;
                case $v1061 < 8400:
                    $score += 40;
                    break;
                case $v1061 >= 8400:
                    $score += 24;
                    break;
            }
        }

        return $score;
    }

    /**
     * 全新本新无短信模型分V2
     */
    public function checkQXBXUserModelWoSmsV2(){
        $this->isGetData = false;
        $score = 0;
        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 600:
                $score += 20;
                break;
            case $v323 < 2200:
                $score += 23;
                break;
            case $v323 < 10000:
                $score += 28;
                break;
            case $v323 >= 10000:
                $score += 32;
                break;
        }

        $v688 = $this->checkLast60dApplyCntByPanInTotPlatform();
        switch (true){
            case $v688 < 3:
                $score += 26;
                break;
            case $v688 < 5:
                $score += 19;
                break;
            case $v688 >= 5:
                $score += 16;
                break;
        }

        $v1184 = $this->checkSMDeviceIDHisOrderMatchPhoneCntTotPlatform();
        switch (true){
            case $v1184 < 2:
                $score += 28;
                break;
            case $v1184 >= 2:
                $score += -20;
                break;
        }

        $v189 = $this->checkApplyCntLast1hourByIP();
        switch (true){
            case $v189 < 2:
                $score += 28;
                break;
            case $v189 >= 2:
                $score += 16;
                break;
        }

        $v326 = $this->checkFirstPhotoTimeToNow();
        switch (true){
            case $v326 < 0:
                $score += 30;
                break;
            case $v326 < 300:
                $score += 17;
                break;
            case $v326 < 500:
                $score += 22;
                break;
            case $v326 >= 500:
                $score += 30;
                break;
        }

        $v202 = $this->checkLoanAppCnt();
        switch (true){
            case $v202 < 1:
                $score += 25;
                break;
            case $v202 < 2:
                $score += -2;
                break;
            case $v202 < 4:
                $score += 14;
                break;
            case $v202 < 8:
                $score += 30;
                break;
            case $v202 >= 8:
                $score += 36;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 19000:
                $score += 19;
                break;
            case $v206 < 30000:
                $score += 23;
                break;
            case $v206 < 38000:
                $score += 27;
                break;
            case $v206 >= 38000:
                $score += 39;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 5:
                $score += 14;
                break;
            case $v103 < 6:
                $score += 31;
                break;
            case $v103 >= 6:
                $score += 37;
                break;
        }

        $v681 = $this->checkMaxDateDiffOfRegisterAndOrderByPhoneTotPlatform();
        switch (true){
            case $v681 < 2:
                $score += 28;
                break;
            case $v681 < 5:
                $score += 54;
                break;
            case $v681 < 22:
                $score += 40;
                break;
            case $v681 >= 22:
                $score += 4;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 100:
                $score += -11;
                break;
            case $v142 < 150:
                $score += 9;
                break;
            case $v142 < 600:
                $score += 23;
                break;
            case $v142 >= 600:
                $score += 36;
                break;
        }

        $v1258 = $this->checkSelectedPreCreditLine();
        switch (true){
            case $v1258 < 1900:
                $score += 18;
                break;
            case $v1258 >= 1900:
                $score += 47;
                break;
        }

        $v917 = $this->checkHisApplyCnt500mAwayFromGPSLocAllPlatform();
        switch (true){
            case $v917 < 3:
                $score += 19;
                break;
            case $v917 < 9:
                $score += 27;
                break;
            case $v917 < 25:
                $score += 39;
                break;
            case $v917 >= 25:
                $score += 32;
                break;
        }

        $v325 = $this->checkLast90MobilePhotoAmount();
        switch (true){
            case $v325 < 200:
                $score += 12;
                break;
            case $v325 < 800:
                $score += 20;
                break;
            case $v325 < 3400:
                $score += 33;
                break;
            case $v325 >= 3400:
                $score += 46;
                break;
        }

        $v679 = $this->checkPendingRepaymentTotAmtOfPanTotPlatform();
        switch (true){
            case $v679 < 1900:
                $score += 22;
                break;
            case $v679 >= 1900:
                $score += 50;
                break;
        }

        $v101 = $this->checkUserAge();
        switch (true){
            case $v101 < 23:
                $score += -4;
                break;
            case $v101 < 26:
                $score += 12;
                break;
            case $v101 < 33:
                $score += 25;
                break;
            case $v101 < 41:
                $score += 33;
                break;
            case $v101 >= 41:
                $score += 44;
                break;
        }

        $v1219 = $this->checkIsBangaloreExperianCreditReportReturnedNormal();
        if($v1219 != 1){
            $score += 112;
        }else{
            $v1223 = $this->checkBangaloreExperianCreditAccountClosed();
            switch (true){
                case $v1223 < 0:
                    $score += -4;
                    break;
                case $v1223 < 3:
                    $score += 9;
                    break;
                case $v1223 < 8:
                    $score += 23;
                    break;
                case $v1223 < 18:
                    $score += 43;
                    break;
                case $v1223 >= 18:
                    $score += 67;
                    break;
            }

            $v1229 = $this->checkBangaloreExperianLast180dEnquiryCnt();
            switch (true){
                case $v1229 < 6:
                    $score += 13;
                    break;
                case $v1229 < 9:
                    $score += 34;
                    break;
                case $v1229 < 12:
                    $score += 51;
                    break;
                case $v1229 >= 12:
                    $score += 66;
                    break;
            }

            $v1230 = $this->checkBangaloreExperianLast90dEnquiryCnt();
            switch (true){
                case $v1230 < 0:
                    $score += 7;
                    break;
                case $v1230 < 3:
                    $score += 17;
                    break;
                case $v1230 < 5:
                    $score += 23;
                    break;
                case $v1230 >= 5:
                    $score += 35;
                    break;
            }

            $v1254 = $this->checkBangaloreExperianCreditScore();
            switch (true){
                case $v1254 < 660:
                    $score += 2;
                    break;
                case $v1254 < 730:
                    $score += 30;
                    break;
                case $v1254 >= 730:
                    $score += 41;
                    break;
            }

            $v1253 = $this->checkBangaloreExperianTimeOfLastPayMent();
            switch (true){
                case $v1253 < 20:
                    $score += 11;
                    break;
                case $v1253 < 40:
                    $score += 46;
                    break;
                case $v1253 < 60:
                    $score += 35;
                    break;
                case $v1253 < 200:
                    $score += 28;
                    break;
                case $v1253 >= 200:
                    $score += 13;
                    break;
            }

            $v1244 = $this->checkBangaloreExperianTimeOfFirstCreditTimeToNow();
            switch (true){
                case $v1244 < 200:
                    $score += 19;
                    break;
                case $v1244 < 1500:
                    $score += 24;
                    break;
                case $v1244 < 2300:
                    $score += 27;
                    break;
                case $v1244 >= 2300:
                    $score += 30;
                    break;
            }

            $v1228 = $this->checkBangaloreExperianOutstandingBalanceAll();
            switch (true){
                case $v1228 < 40000:
                    $score += 22;
                    break;
                case $v1228 < 180000:
                    $score += 24;
                    break;
                case $v1228 >= 180000:
                    $score += 30;
                    break;
            }

            $v1243 = $this->checkBangaloreExperianTimeOfLastCreditTimeToNow();
            switch (true) {
                case $v1243 < 20:
                    $score += 17;
                    break;
                case $v1243 < 80:
                    $score += 34;
                    break;
                case $v1243 < 200:
                    $score += 28;
                    break;
                case $v1243 < 240:
                    $score += 25;
                    break;
                case $v1243 >= 240:
                    $score += 20;
                    break;
            }
        }

        return $score;
    }

    /**
     * 全老本新无短信模型分V1
     */
    public function checkQLBXUserModelWoSmsV1(){
        $score = 0;
        $v1425 = $this->checkAvgActualRepayAmtOfCollectionAPI();
        switch (true){
            case $v1425 < 0:
                $score += 46;
                break;
            case $v1425 < 300000:
                $score += 6;
                break;
            case $v1425 < 360000:
                $score += 29;
                break;
            case $v1425 < 480000:
                $score += 47;
                break;
            case $v1425 >= 480000:
                $score += 78;
                break;
        }

        $v556 = $this->checkLast7dAppCnt();
        switch (true){
            case $v556 < 2:
                $score += 71;
                break;
            case $v556 < 5:
                $score += 54;
                break;
            case $v556 < 22:
                $score += 33;
                break;
            case $v556 >= 22:
                $score += -3;
                break;
        }

        $v1423 = $this->checkSumOfActualRepayAmtOfCollectionAPI();
        switch (true){
            case $v1423 < 0:
                $score += 40;
                break;
            case $v1423 < 300000:
                $score += 34;
                break;
            case $v1423 < 1900000:
                $score += 38;
                break;
            case $v1423 >= 1900000:
                $score += 40;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 22000:
                $score += 30;
                break;
            case $v206 < 26000:
                $score += 36;
                break;
            case $v206 < 46000:
                $score += 47;
                break;
            case $v206 >= 46000:
                $score += 64;
                break;
        }

        $v718 = $this->checkHisDueAvgDayByPanTotPlatform();
        switch (true){
            case $v718 < 1:
                $score += 49;
                break;
            case $v718 < 2:
                $score += 45;
                break;
            case $v718 < 3:
                $score += 16;
                break;
            case $v718 >= 3:
                $score += -13;
                break;
        }

        $v326 = $this->checkFirstPhotoTimeToNow();
        switch (true){
            case $v326 < 0:
                $score += 44;
                break;
            case $v326 < 50:
                $score += 30;
                break;
            case $v326 < 500:
                $score += 35;
                break;
            case $v326 < 1000:
                $score += 38;
                break;
            case $v326 >= 1000:
                $score += 45;
                break;
        }

        $v1206 = $this->checkLast30dTiqianRepayCntHisCntRateTPF();
        switch (true){
            case $v1206 < 4:
                $score += 26;
                break;
            case $v1206 < 26:
                $score += 38;
                break;
            case $v1206 < 66:
                $score += 40;
                break;
            case $v1206 >= 66:
                $score += 44;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 150:
                $score += 22;
                break;
            case $v142 < 350:
                $score += 33;
                break;
            case $v142 < 600:
                $score += 39;
                break;
            case $v142 < 1050:
                $score += 44;
                break;
            case $v142 >= 1050:
                $score += 47;
                break;
        }

        $v703 = $this->checkLast7dApplyCntBySMDeviceIDTotPlatform();
        switch (true){
            case $v703 < 5:
                $score += 43;
                break;
            case $v703 < 9:
                $score += 39;
                break;
            case $v703 >= 9:
                $score += 11;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 4:
                $score += 22;
                break;
            case $v103 < 5:
                $score += 31;
                break;
            case $v103 < 6:
                $score += 44;
                break;
            case $v103 >= 6:
                $score += 55;
                break;
        }

        $v1399 = $this->checkMaxDayDiffBtwLast90DaysNonOverdueClosingTimeAndThisApplyTimeInTPF();
        switch (true){
            case $v1399 < 0:
                $score += -8;
                break;
            case $v1399 < 30:
                $score += 37;
                break;
            case $v1399 < 84:
                $score += 65;
                break;
            case $v1399 >= 84:
                $score += 97;
                break;
        }

        $v202 = $this->checkLoanAppCnt();
        switch (true){
            case $v202 < 3:
                $score += 8;
                break;
            case $v202 < 4:
                $score += 22;
                break;
            case $v202 < 15:
                $score += 39;
                break;
            case $v202 >= 15:
                $score += 52;
                break;
        }

        $v685 = $this->checkLast90dRejectCntByPanInTotPlatform();
        switch (true){
            case $v685 < 1:
                $score += 45;
                break;
            case $v685 < 2:
                $score += 31;
                break;
            case $v685 < 4:
                $score += 30;
                break;
            case $v685 >= 4:
                $score += 17;
                break;
        }

        $v690 = $this->checkLast30dApplyCntByPanInTotPlatform();
        switch (true){
            case $v690 < 5:
                $score += 40;
                break;
            case $v690 < 14:
                $score += 35;
                break;
            case $v690 < 17:
                $score += 38;
                break;
            case $v690 < 22:
                $score += 44;
                break;
            case $v690 >= 22:
                $score += 52;
                break;
        }

        $v722 = $this->checkOldUserComplexRuleV1HisCpDaySumTotPlatform();
        switch (true){
            case $v722 < -17:
                $score += 62;
                break;
            case $v722 < -9:
                $score += 52;
                break;
            case $v722 < 1:
                $score += 42;
                break;
            case $v722 >= 1:
                $score += 27;
                break;
        }

        $v189 = $this->checkApplyCntLast1hourByIP();
        switch (true){
            case $v189 < 2:
                $score += 43;
                break;
            case $v189 < 3:
                $score += 40;
                break;
            case $v189 < 4:
                $score += 29;
                break;
            case $v189 >= 4:
                $score += 16;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 1500:
                $score += 26;
                break;
            case $v323 < 2000:
                $score += 33;
                break;
            case $v323 < 3500:
                $score += 41;
                break;
            case $v323 >= 3500:
                $score += 56;
                break;
        }

        return $score;
    }

    /**
     * 全老本新无短信模型分V2
     */
    public function checkQLBXUserModelWoSmsV2(){
        $score = 0;
        $v580 = $this->checkRejectCntLast1MonthByMobileInTotPlatporm();
        switch (true){
            case $v580 < 1:
                $score += 46;
                break;
            case $v580 < 4:
                $score += 37;
                break;
            case $v580 >= 4:
                $score += 29;
                break;
        }

        $v1288 = $this->checkTeleCollectionSelfAndConnectTimesTPF();
        switch (true){
            case $v1288 < 2:
                $score += 42;
                break;
            case $v1288 < 4:
                $score += 37;
                break;
            case $v1288 >= 4:
                $score += 39;
                break;
        }

        $v1400 = $this->checkLoanCntLast90DaysInTPF();
        switch (true){
            case $v1400 < 13:
                $score += 42;
                break;
            case $v1400 < 19:
                $score += 38;
                break;
            case $v1400 < 37:
                $score += 40;
                break;
            case $v1400 >= 37:
                $score += 43;
                break;
        }

        $v101 = $this->checkUserAge();
        switch (true){
            case $v101 < 28:
                $score += 31;
                break;
            case $v101 < 39:
                $score += 43;
                break;
            case $v101 >= 39:
                $score += 55;
                break;
        }

        $v582 = $this->checkHisSMDeviceIDApplyCntInTotPlatporm();
        switch (true){
            case $v582 < 10:
                $score += 43;
                break;
            case $v582 < 22:
                $score += 39;
                break;
            case $v582 < 42:
                $score += 37;
                break;
            case $v582 >= 42:
                $score += 44;
                break;
        }

        $v324 = $this->checkLast30MobilePhotoAmount();
        switch (true){
            case $v324 < 50:
                $score += 41;
                break;
            case $v324 < 100:
                $score += 37;
                break;
            case $v324 < 750:
                $score += 41;
                break;
            case $v324 < 1650:
                $score += 42;
                break;
            case $v324 >= 1650:
                $score += 48;
                break;
        }

        $v1425 = $this->checkAvgActualRepayAmtOfCollectionAPI();
        switch (true){
            case $v1425 < 0:
                $score += 50;
                break;
            case $v1425 < 280000:
                $score += 13;
                break;
            case $v1425 < 500000:
                $score += 34;
                break;
            case $v1425 >= 500000:
                $score += 43;
                break;
        }

        $v724 = $this->checkOldUserComplexRuleV1HisDueOrderCntHisOrderCntRateTotPlatform();
        switch (true){
            case $v724 < 8:
                $score += 44;
                break;
            case $v724 < 16:
                $score += 36;
                break;
            case $v724 >= 16:
                $score += 31;
                break;
        }

        $v1276 = $this->checkSingleCollectionMaxTimesTPF();
        switch (true){
            case $v1276 < 1:
                $score += 46;
                break;
            case $v1276 < 2:
                $score += 36;
                break;
            case $v1276 >= 2:
                $score += 31;
                break;
        }

        $v1213 = $this->checkHistSMDeviceCntOfLoginAndOrderInTPF();
        switch (true){
            case $v1213 < 2:
                $score += 42;
                break;
            case $v1213 < 3:
                $score += 41;
                break;
            case $v1213 >= 3:
                $score += 36;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 100:
                $score += 25;
                break;
            case $v142 < 350:
                $score += 39;
                break;
            case $v142 < 400:
                $score += 58;
                break;
            case $v142 >= 400:
                $score += 43;
                break;
        }

        $v556 = $this->checkLast7dAppCnt();
        switch (true){
            case $v556 < 9:
                $score += 45;
                break;
            case $v556 < 11:
                $score += 59;
                break;
            case $v556 < 28:
                $score += 41;
                break;
            case $v556 < 37:
                $score += 30;
                break;
            case $v556 >= 37:
                $score += 20;
                break;
        }

        $v727 = $this->checkLast30dCpDayMaxTotPlatform();
        switch (true){
            case $v727 < -2:
                $score += 39;
                break;
            case $v727 < 0:
                $score += 51;
                break;
            case $v727 < 1:
                $score += 45;
                break;
            case $v727 < 2:
                $score += 32;
                break;
            case $v727 >= 2:
                $score += 21;
                break;
        }

        return $score;
    }

    /**
     * 全老本老无短信模型分V2
     */
    public function checkQLBLUserModelWoSmsV2(){
        $score = 0;
        $v1198 = $this->checkMaxDateOfOrderToTodayTPF();
        switch (true){
            case $v1198 < 10:
                $score += 62;
                break;
            case $v1198 < 70:
                $score += 38;
                break;
            case $v1198 < 110:
                $score += 56;
                break;
            case $v1198 >= 110:
                $score += 77;
                break;
        }

        $v726 = $this->checkLastLoanOrderCpDayTotPlatform();
        switch (true){
            case $v726 < -2:
                $score += 57;
                break;
            case $v726 < 0:
                $score += 75;
                break;
            case $v726 < 1:
                $score += 52;
                break;
            case $v726 >= 1:
                $score += 1;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 22000:
                $score += 44;
                break;
            case $v206 < 30000:
                $score += 35;
                break;
            case $v206 < 56000:
                $score += 64;
                break;
            case $v206 >= 56000:
                $score += 89;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 4:
                $score += 32;
                break;
            case $v103 < 6:
                $score += 58;
                break;
            case $v103 >= 6:
                $score += 55;
                break;
        }

        $v703 = $this->checkLast7dApplyCntBySMDeviceIDTotPlatform();
        switch (true){
            case $v703 < 2:
                $score += 60;
                break;
            case $v703 < 5:
                $score += 70;
                break;
            case $v703 < 13:
                $score += 45;
                break;
            case $v703 >= 13:
                $score += 31;
                break;
        }

        $v588 = $this->checkPendingRepaymentCntOfPanInTotPlatporm();
        switch (true){
            case $v588 < 2:
                $score += 71;
                break;
            case $v588 < 3:
                $score += 59;
                break;
            case $v588 < 6:
                $score += 48;
                break;
            case $v588 >= 6:
                $score += 41;
                break;
        }

        $v556 = $this->checkLast7dAppCnt();
        switch (true){
            case $v556 < 5:
                $score += 81;
                break;
            case $v556 < 13:
                $score += 72;
                break;
            case $v556 < 34:
                $score += 38;
                break;
            case $v556 >= 34:
                $score += -2;
                break;
        }

        $v1425 = $this->checkAvgActualRepayAmtOfCollectionAPI();
        switch (true){
            case $v1425 < 0:
                $score += 69;
                break;
            case $v1425 < 280000:
                $score += 16;
                break;
            case $v1425 < 500000:
                $score += 38;
                break;
            case $v1425 < 600000:
                $score += 57;
                break;
            case $v1425 >= 600000:
                $score += 79;
                break;
        }

        $v485 = $this->checkOldUserLastLoanOrderAmount();
        switch (true){
            case $v485 < 1900:
                $score += 55;
                break;
            case $v485 < 2100:
                $score += 52;
                break;
            case $v485 < 4200:
                $score += 48;
                break;
            case $v485 >= 4200:
                $score += 67;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 100:
                $score += 13;
                break;
            case $v142 < 350:
                $score += 45;
                break;
            case $v142 < 1150:
                $score += 55;
                break;
            case $v142 >= 1150:
                $score += 66;
                break;
        }

        $v1415 = $this->checkSumOfOverdueDayOfCollectionAPI();
        switch (true){
            case $v1415 < 2:
                $score += 72;
                break;
            case $v1415 < 5:
                $score += 52;
                break;
            case $v1415 >= 5:
                $score += 19;
                break;
        }

        $v580 = $this->checkRejectCntLast1MonthByMobileInTotPlatporm();
        switch (true){
            case $v580 < 1:
                $score += 57;
                break;
            case $v580 < 4:
                $score += 41;
                break;
            case $v580 >= 4:
                $score += 33;
                break;
        }

        return $score;
    }

    /**
     * 全新本新无短信模型分V3
     */
    public function checkQXBXUserModelWoSmsV3(){
        $this->isGetData = false;
        $score = 0;
        $v560 = $this->checkLast30dLoanAppCnt();
        switch (true){
            case $v560 < 1:
                $score += 28;
                break;
            case $v560 < 2:
                $score += 37;
                break;
            case $v560 < 9:
                $score += 53;
                break;
            case $v560 >= 9:
                $score += 31;
                break;
        }

        $v1437 = $this->checkLast1dApplyCntBySMDeviceIDTPF();
        switch (true){
            case $v1437 < 2:
                $score += 47;
                break;
            case $v1437 < 3:
                $score += 41;
                break;
            case $v1437 >= 3:
                $score += 24;
                break;
        }

        $v1416 = $this->checkAvgOverdueDayOfCollectionAPI();
        switch (true){
            case $v1416 < 0:
                $score += 50;
                break;
            case $v1416 < 4:
                $score += 80;
                break;
            case $v1416 < 24:
                $score += -36;
                break;
            case $v1416 >= 24:
                $score += -46;
                break;
        }

        $v1415 = $this->checkSumOfOverdueDayOfCollectionAPI();
        switch (true){
            case $v1415 < 0:
                $score += 46;
                break;
            case $v1415 < 10:
                $score += 56;
                break;
            case $v1415 >= 10:
                $score += 9;
                break;
        }

        $v203 = $this->checkLoanAppRatio();
        switch (true){
            case $v203 < 1:
                $score += 34;
                break;
            case $v203 < 2:
                $score += 43;
                break;
            case $v203 >= 2:
                $score += 46;
                break;
        }

        $v1427 = $this->checkIsFirstOfCollectionAPI();
        switch (true){
            case $v1427 < 0:
                $score += 47;
                break;
            case $v1427 < 1:
                $score += 19;
                break;
            case $v1427 >= 1:
                $score += 61;
                break;
        }

        $v1439 = $this->checkLast1hApplyCntBySMDeviceIDTPF();
        switch (true){
            case $v1439 < 2:
                $score += 48;
                break;
            case $v1439 < 3:
                $score += 36;
                break;
            case $v1439 >= 3:
                $score += 13;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 150:
                $score += 18;
                break;
            case $v142 < 400:
                $score += 38;
                break;
            case $v142 < 1000:
                $score += 46;
                break;
            case $v142 < 1500:
                $score += 51;
                break;
            case $v142 >= 1500:
                $score += 60;
                break;
        }

        $v206 = $this->checkAppFormSalaryBeforeTax();
        switch (true){
            case $v206 < 26000:
                $score += 38;
                break;
            case $v206 < 40000:
                $score += 43;
                break;
            case $v206 < 50000:
                $score += 50;
                break;
            case $v206 >= 50000:
                $score += 64;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 1500:
                $score += 38;
                break;
            case $v323 < 5000:
                $score += 42;
                break;
            case $v323 < 13500:
                $score += 51;
                break;
            case $v323 >= 13500:
                $score += 62;
                break;
        }

        $v1219 = $this->checkIsBangaloreExperianCreditReportReturnedNormal();
        if($v1219 != 1){
            $score += 116;
        }else{
            $v1243 = $this->checkBangaloreExperianTimeOfLastCreditTimeToNow();
            switch (true) {
                case $v1243 < 0:
                    $score += 15;
                    break;
                case $v1243 < 40:
                    $score += 87;
                    break;
                case $v1243 < 80:
                    $score += 61;
                    break;
                case $v1243 < 160:
                    $score += 46;
                    break;
                case $v1243 >= 160:
                    $score += 27;
                    break;
            }

            $v1223 = $this->checkBangaloreExperianCreditAccountClosed();
            switch (true){
                case $v1223 < 4:
                    $score += 19;
                    break;
                case $v1223 < 10:
                    $score += 42;
                    break;
                case $v1223 < 19:
                    $score += 65;
                    break;
                case $v1223 >= 19:
                    $score += 90;
                    break;
            }

            $v1244 = $this->checkBangaloreExperianTimeOfFirstCreditTimeToNow();
            switch (true){
                case $v1244 < 0:
                    $score += 22;
                    break;
                case $v1244 < 700:
                    $score += 33;
                    break;
                case $v1244 < 1400:
                    $score += 41;
                    break;
                case $v1244 < 3500:
                    $score += 52;
                    break;
                case $v1244 >= 3500:
                    $score += 74;
                    break;
            }
        }

        return $score;
    }

    /**
     * 居住邦
     */
    public function checkResidentialState(){
        return strtolower($this->data->infoUser->residential_address);
    }

    /**
     * 该Pan卡号近7天内在全平台应还订单数
     */
    public function checkOrderCntToRepayLast7DaysTPF(){
        $data = $this->getOrderData(7);
        return count($data);
    }

    /**
     * 该Pan卡号近7天内在全平台应还订单的应还金额之和
     */
    public function checkSumOfOrderAmtToRepayLast7DaysTPF(){
        $data = $this->getOrderData(7);

        return array_sum($data);
    }

    /**
     * 该Pan卡号近7天内在全平台应还订单的应还金额的最大值
     */
    public function checkMaxOfOrderAmtToRepayLast7DaysTPF(){
        $data = $this->getOrderData(7);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 该Pan卡号近7天内在全平台应还订单的应还金额的最小值
     */
    public function checkMinOfOrderAmtToRepayLast7DaysTPF(){
        $data = $this->getOrderData(7);

        if(empty($data)){
            return 0;
        }

        return min($data);
    }

    /**
     * 该Pan卡号近7天内在全平台应还订单的应还金额的平均值
     */
    public function checkAvgOfOrderAmtToRepayLast7DaysTPF(){
        $data = $this->getOrderData(7);

        if(empty($data)){
            return -1;
        }

        return intval(round(array_sum($data) / count($data)));
    }

    /**
     * 该Pan卡号近7天内在全平台逾期订单数
     */
    public function checkOverdueOrderCntLast7DaysTPF(){
        if(empty($this->getOrderData(7))){
            return -9999;
        }
        $data = $this->getOrderData(7, 1);

        return count($data);
    }

    /**
     * 该Pan卡号近7天内在全平台逾期订单的应还金额之和
     */
    public function checkSumOfOverdueOrderaAmtLast7DaysTPF(){
        if(empty($this->getOrderData(7))){
            return -9999;
        }
        $data = $this->getOrderData(7, 1);

        return array_sum($data);
    }

    /**
     * 该Pan卡号近7天内在全平台逾期订单的应还金额的最大值
     */
    public function checkMaxOfOverdueOrderaAmtLast7DaysTPF(){
        if(empty($this->getOrderData(7))){
            return -9999;
        }
        $data = $this->getOrderData(7, 1);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 该Pan卡号近7天内在全平台逾期订单的应还金额的最小值
     */
    public function checkMinOfOverdueOrderaAmtLast7DaysTPF(){
        if(empty($this->getOrderData(7))){
            return -9999;
        }
        $data = $this->getOrderData(7, 1);

        if(empty($data)){
            return 0;
        }

        return min($data);
    }

    /**
     * 该Pan卡号近7天内在全平台逾期订单的应还金额的平均值
     */
    public function checkAvgOfOverdueOrderaAmtLast7DaysTPF(){
        if(empty($this->getOrderData(7))){
            return -9999;
        }
        $data = $this->getOrderData(7, 1);

        if(empty($data)){
            return -1;
        }

        return intval(round(array_sum($data) / count($data)));
    }

    /**
     * 该Pan卡号近7天内在全平台逾期订单数占应还订单数的比例
     */
    public function checkOverdueOrderCntToOrderCntToRepayRatioLast7Days(){
        $data = $this->checkOrderCntToRepayLast7DaysTPF();
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdueOrderCntLast7DaysTPF();

        return round($count / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台逾期订单的应还金额之和占应还订单的应还金额之和的比例
     */
    public function checkSumOfOverdueOrderAmtToSumOfOrderAmtToRepayRatioLast7Days(){
        $data = $this->checkSumOfOrderAmtToRepayLast7DaysTPF();
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkSumOfOverdueOrderaAmtLast7DaysTPF();

        return round($count / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台应还订单数
     */
    public function checkOrderCntToRepayLast30DaysTPF(){
        $data = $this->getOrderData(30);
        return count($data);
    }

    /**
     * 该Pan卡号近30天内在全平台应还订单的应还金额之和
     */
    public function checkSumOfOrderAmtToRepayLast30DaysTPF(){
        $data = $this->getOrderData(30);

        return array_sum($data);
    }

    /**
     * 该Pan卡号近30天内在全平台应还订单的应还金额的最大值
     */
    public function checkMaxOfOrderAmtToRepayLast30DaysTPF(){
        $data = $this->getOrderData(30);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 该Pan卡号近30天内在全平台应还订单的应还金额的最小值
     */
    public function checkMinOfOrderAmtToRepayLast30DaysTPF(){
        $data = $this->getOrderData(30);

        if(empty($data)){
            return 0;
        }

        return min($data);
    }

    /**
     * 该Pan卡号近30天内在全平台应还订单的应还金额的平均值
     */
    public function checkAvgOfOrderAmtToRepayLast30DaysTPF(){
        $data = $this->getOrderData(30);

        if(empty($data)){
            return -1;
        }

        return intval(round(array_sum($data) / count($data)));
    }

    /**
     * 该Pan卡号近30天内在全平台逾期订单数
     */
    public function checkOverdueOrderCntLast30DaysTPF(){
        if(empty($this->getOrderData(30))){
            return -9999;
        }
        $data = $this->getOrderData(30, 1);

        return count($data);
    }

    /**
     * 该Pan卡号近30天内在全平台逾期订单的应还金额之和
     */
    public function checkSumOfOverdueOrderaAmtLast30DaysTPF(){
        if(empty($this->getOrderData(30))){
            return -9999;
        }
        $data = $this->getOrderData(30, 1);

        return array_sum($data);
    }

    /**
     * 该Pan卡号近30天内在全平台逾期订单的应还金额的最大值
     */
    public function checkMaxOfOverdueOrderaAmtLast30DaysTPF(){
        if(empty($this->getOrderData(30))){
            return -9999;
        }
        $data = $this->getOrderData(30, 1);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 该Pan卡号近30天内在全平台逾期订单的应还金额的最小值
     */
    public function checkMinOfOverdueOrderaAmtLast30DaysTPF(){
        if(empty($this->getOrderData(30))){
            return -9999;
        }
        $data = $this->getOrderData(30, 1);

        if(empty($data)){
            return 0;
        }

        return min($data);
    }

    /**
     * 该Pan卡号近30天内在全平台逾期订单的应还金额的平均值
     */
    public function checkAvgOfOverdueOrderaAmtLast30DaysTPF(){
        if(empty($this->getOrderData(30))){
            return -9999;
        }
        $data = $this->getOrderData(30, 1);

        if(empty($data)){
            return -1;
        }

        return intval(round(array_sum($data) / count($data)));
    }

    /**
     * 该Pan卡号近30天内在全平台逾期订单数占应还订单数的比例
     */
    public function checkOverdueOrderCntToOrderCntToRepayRatioLast30Days(){
        $data = $this->checkOrderCntToRepayLast30DaysTPF();
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdueOrderCntLast30DaysTPF();

        return round($count / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台逾期订单的应还金额之和占应还订单的应还金额之和的比例
     */
    public function checkSumOfOverdueOrderAmtToSumOfOrderAmtToRepayRatioLast30Days(){
        $data = $this->checkSumOfOrderAmtToRepayLast30DaysTPF();
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkSumOfOverdueOrderaAmtLast30DaysTPF();

        return round($count / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台应还订单数
     */
    public function checkOrderCntToRepayHistTPF(){
        $data = $this->getOrderData();
        return count($data);
    }

    /**
     * 该Pan卡号历史在全平台应还订单的应还金额之和
     */
    public function checkSumOfOrderAmtToRepayHistTPF(){
        $data = $this->getOrderData();

        return array_sum($data);
    }

    /**
     * 该Pan卡号历史在全平台应还订单的应还金额的最大值
     */
    public function checkMaxOfOrderAmtToRepayHistTPF(){
        $data = $this->getOrderData();

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台应还订单的应还金额的最小值
     */
    public function checkMinOfOrderAmtToRepayHistTPF(){
        $data = $this->getOrderData();

        if(empty($data)){
            return 0;
        }

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台应还订单的应还金额的平均值
     */
    public function checkAvgOfOrderAmtToRepayHistTPF(){
        $data = $this->getOrderData();

        if(empty($data)){
            return -1;
        }

        return intval(round(array_sum($data) / count($data)));
    }

    /**
     * 该Pan卡号历史在全平台逾期订单数
     */
    public function checkOverdueOrderCntHistTPF(){
        if(empty($this->getOrderData())){
            return -9999;
        }
        $data = $this->getOrderData(0, 1);

        return count($data);
    }

    /**
     * 该Pan卡号历史在全平台逾期订单的应还金额之和
     */
    public function checkSumOfOverdueOrderaAmtHistTPF(){
        if(empty($this->getOrderData())){
            return -9999;
        }
        $data = $this->getOrderData(0, 1);

        return array_sum($data);
    }

    /**
     * 该Pan卡号历史在全平台逾期订单的应还金额的最大值
     */
    public function checkMaxOfOverdueOrderaAmtHistTPF(){
        if(empty($this->getOrderData())){
            return -9999;
        }
        $data = $this->getOrderData(0, 1);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台逾期订单的应还金额的最小值
     */
    public function checkMinOfOverdueOrderaAmtHistTPF(){
        if(empty($this->getOrderData())){
            return -9999;
        }
        $data = $this->getOrderData(0, 1);

        if(empty($data)){
            return 0;
        }

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台逾期订单的应还金额的平均值
     */
    public function checkAvgOfOverdueOrderaAmtHistTPF(){
        if(empty($this->getOrderData())){
            return -9999;
        }
        $data = $this->getOrderData(0, 1);

        if(empty($data)){
            return -1;
        }

        return intval(round(array_sum($data) / count($data)));
    }

    /**
     * 该Pan卡号历史在全平台逾期订单数占应还订单数的比例
     */
    public function checkOverdueOrderCntToOrderCntToRepayRatioHist(){
        $data = $this->checkOrderCntToRepayHistTPF();
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdueOrderCntHistTPF();

        return round($count / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台逾期订单的应还金额之和占应还订单的应还金额之和的比例
     */
    public function checkSumOfOverdueOrderAmtToSumOfOrderAmtToRepayRatioHist(){
        $data = $this->checkSumOfOrderAmtToRepayHistTPF();
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkSumOfOverdueOrderaAmtHistTPF();

        return round($count / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在提醒API中的提醒订单数
     */
    public function checkRemindAPIRemindOrderCntLast7Days(){
        $data = $this->getRemindData();

        return $data['order_count_7'];
    }

    /**
     * 该Pan卡号近7天内在提醒API中的提醒订单的待还款金额之和
     */
    public function checkRemindAPIRemindSumOfOrderAmtToRepayLast7Days(){
        $data = $this->getRemindData();

        return $data['order_sum_7'];
    }

    /**
     * 该Pan卡号近7天内在提醒API中的提醒订单的待还款金额的最大值
     */
    public function checkRemindAPIRemindMaxOfOrderAmtToRepayLast7Days(){
        $data = $this->getRemindData();

        return $data['order_max_7'];
    }

    /**
     * 该Pan卡号近7天内在提醒API中的提醒订单的待还款金额的最小值
     */
    public function checkRemindAPIRemindMinOfOrderAmtToRepayLast7Days(){
        $data = $this->getRemindData();

        return $data['order_min_7'];
    }

    /**
     * 该Pan卡号近7天内在提醒API中的提醒订单的待还款金额的平均值
     */
    public function checkRemindAPIRemindAvgOfOrderAmtToRepayLast7Days(){
        $data = $this->getRemindData();

        return $data['order_avg_7'];
    }

    /**
     * 该Pan卡号近30天内在提醒API中的提醒订单数
     */
    public function checkRemindAPIRemindOrderCntLast30Days(){
        $data = $this->getRemindData();

        return $data['order_count_30'];
    }

    /**
     * 该Pan卡号近30天内在提醒API中的提醒订单的待还款金额之和
     */
    public function checkRemindAPIRemindSumOfOrderAmtToRepayLast30Days(){
        $data = $this->getRemindData();

        return $data['order_sum_30'];
    }

    /**
     * 该Pan卡号近30天内在提醒API中的提醒订单的待还款金额的最大值
     */
    public function checkRemindAPIRemindMaxOfOrderAmtToRepayLast30Days(){
        $data = $this->getRemindData();

        return $data['order_max_30'];
    }

    /**
     * 该Pan卡号近30天内在提醒API中的提醒订单的待还款金额的最小值
     */
    public function checkRemindAPIRemindMinOfOrderAmtToRepayLast30Days(){
        $data = $this->getRemindData();

        return $data['order_min_30'];
    }

    /**
     * 该Pan卡号近30天内在提醒API中的提醒订单的待还款金额的平均值
     */
    public function checkRemindAPIRemindAvgOfOrderAmtToRepayLast30Days(){
        $data = $this->getRemindData();

        return $data['order_avg_30'];
    }

    /**
     * 该Pan卡号历史在提醒API中的提醒订单数
     */
    public function checkRemindAPIRemindOrderCntHist(){
        $data = $this->getRemindData();

        return $data['order_count'];
    }

    /**
     * 该Pan卡号历史在提醒API中的提醒订单的应还款金额之和
     */
    public function checkRemindAPIRemindSumOfOrderAmtToRepayHist(){
        $data = $this->getRemindData();

        return $data['order_sum'];
    }

    /**
     * 该Pan卡号历史在提醒API中的提醒订单的待还款金额的最大值
     */
    public function checkRemindAPIRemindMaxOfOrderAmtToRepayHist(){
        $data = $this->getRemindData();

        return $data['order_max'];
    }

    /**
     * 该Pan卡号历史在提醒API中的提醒订单的待还款金额的最小值
     */
    public function checkRemindAPIRemindMinOfOrderAmtToRepayHist(){
        $data = $this->getRemindData();

        return $data['order_min'];
    }

    /**
     * 该Pan卡号历史在提醒API中的提醒订单的待还款金额的平均值
     */
    public function checkRemindAPIRemindAvgOfOrderAmtToRepayHist(){
        $data = $this->getRemindData();

        return $data['order_avg'];
    }

    /**
     * 该Pan卡号近7天内在提醒API中正常还款订单数
     */
    public function checkRemindAPIRepayOntimeOrderCntLast7Days(){
        $data = $this->getRemindData();

        return $data['order_repay_count_7'];
    }

    /**
     * 该Pan卡号近7天内在提醒API中正常还款订单的应还款金额之和
     */
    public function checkRemindAPIRepayOntimeSumOfOrderAmtLast7Days(){
        $data = $this->getRemindData();

        return $data['order_repay_sum_7'];
    }

    /**
     * 该Pan卡号近7天内在提醒API中正常还款订单的应还款金额的最大值
     */
    public function checkRemindAPIRepayOntimeMaxOfOrderAmtLast7Days(){
        $data = $this->getRemindData();

        return $data['order_repay_max_7'];
    }

    /**
     * 该Pan卡号近7天内在提醒API中正常还款订单的应还款金额的最小值
     */
    public function checkRemindAPIRepayOntimeMinOfOrderAmtLast7Days(){
        $data = $this->getRemindData();

        return $data['order_repay_min_7'];
    }

    /**
     * 该Pan卡号近7天内在提醒API中正常还款订单的应还款金额的平均值
     */
    public function checkRemindAPIRepayOntimeAvgOfOrderAmtLast7Days(){
        $data = $this->getRemindData();

        return $data['order_repay_avg_7'];
    }

    /**
     * 该Pan卡号近30天内在提醒API中正常还款订单数
     */
    public function checkRemindAPIRepayOntimeOrderCntLast30Days(){
        $data = $this->getRemindData();

        return $data['order_repay_count_30'];
    }

    /**
     * 该Pan卡号近30天内在提醒API中正常还款订单的应还款金额之和
     */
    public function checkRemindAPIRepayOntimeSumOfOrderAmtLast30Days(){
        $data = $this->getRemindData();

        return $data['order_repay_sum_30'];
    }

    /**
     * 该Pan卡号近30天内在提醒API中正常还款订单的应还款金额的最大值
     */
    public function checkRemindAPIRepayOntimeMaxOfOrderAmtLast30Days(){
        $data = $this->getRemindData();

        return $data['order_repay_max_30'];
    }

    /**
     * 该Pan卡号近30天内在提醒API中正常还款订单的应还款金额的最小值
     */
    public function checkRemindAPIRepayOntimeMinOfOrderAmtLast30Days(){
        $data = $this->getRemindData();

        return $data['order_repay_min_30'];
    }

    /**
     * 该Pan卡号近30天内在提醒API中正常还款订单的应还款金额的平均值
     */
    public function checkRemindAPIRepayOntimeAvgOfOrderAmtLast30Days(){
        $data = $this->getRemindData();

        return $data['order_repay_avg_30'];
    }

    /**
     * 该Pan卡号历史在提醒API中正常还款订单数
     */
    public function checkRemindAPIRepayOntimeOrderCntHist(){
        $data = $this->getRemindData();

        return $data['order_repay_count'];
    }

    /**
     * 该Pan卡号历史在提醒API中正常还款订单的应还款金额之和
     */
    public function checkRemindAPIRepayOntimeSumOfOrderAmtHist(){
        $data = $this->getRemindData();

        return $data['order_repay_sum'];
    }

    /**
     * 该Pan卡号历史在提醒API中正常还款订单的应还款金额的最大值
     */
    public function checkRemindAPIRepayOntimeMaxOfOrderAmtHist(){
        $data = $this->getRemindData();

        return $data['order_repay_max'];
    }

    /**
     * 该Pan卡号历史在提醒API中正常还款订单的应还款金额的最小值
     */
    public function checkRemindAPIRepayOntimeMinOfOrderAmtHist(){
        $data = $this->getRemindData();

        return $data['order_repay_min'];
    }

    /**
     * 该Pan卡号历史在提醒API中正常还款订单的应还款金额的平均值
     */
    public function checkRemindAPIRepayOntimeAvgOfOrderAmtHist(){
        $data = $this->getRemindData();

        return $data['order_repay_avg'];
    }

    /**
     * 该Pan卡号近7天内在提醒API中正常还款订单数占比
     */
    public function checkRemindAPIRepayOntimeOrderCntRatioLast7Days(){
        $data = $this->getRemindData();

        return $data['order_ratio_7'];
    }

    /**
     * 该Pan卡号近30天内在提醒API中正常还款订单数占比
     */
    public function checkRemindAPIRepayOntimeOrderCntRatioLast30Days(){
        $data = $this->getRemindData();

        return $data['order_ratio_30'];
    }

    /**
     * 该Pan卡号历史在提醒API中正常还款订单数占比
     */
    public function checkRemindAPIRepayOntimeOrderCntRatioHist(){
        $data = $this->getRemindData();

        return $data['order_ratio'];
    }

    /**
     * 该Pan卡号当前在全平台和催收API中的逾期待还款订单数（去重）
     */
    public function checkCollectionAPIAndTPFOverdueToRepayOrderCnt(){
        $data = $this->getOrderData(0, 2);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['pendingCount'];
    }

    /**
     * 该Pan卡号当前在全平台和催收API中的逾期待还款订单的待还款金额之和（去重）
     */
    public function checkCollectionAPIAndTPFOverdueToRepaySumOfOrderAmt(){
        $data = $this->getOrderData(0, 2);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['pendingSum'];
    }

    /**
     * 该Pan卡号当前在全平台和催收API中的逾期待还款订单的待还款金额的最大值（去重）
     */
    public function checkCollectionAPIAndTPFOverdueToRepayMaxOfOrderAmt(){
        $data = $this->getOrderData(0, 2);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['pendingMax'];

        return max($data);
    }

    /**
     * 该Pan卡号当前在全平台和催收API中的逾期待还款订单的待还款金额的最小值（去重）
     */
    public function checkCollectionAPIAndTPFOverdueToRepayMinOfOrderAmt(){
        $data = $this->getOrderData(0, 2);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['pendingMin'];

        return min($data);
    }

    /**
     * 该Pan卡号当前在全平台和催收API中的逾期待还款订单的待还款金额的平均值（去重）
     */
    public function checkCollectionAPIAndTPFOverdueToRepayAvgOfOrderAmt(){
        $data = $this->checkCollectionAPIAndTPFOverdueToRepayOrderCnt();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkCollectionAPIAndTPFOverdueToRepaySumOfOrderAmt();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号当前在全平台的逾期待还款订单数
     */
    public function checkCurrentOverdueOrderCnt(){
        if(empty($this->getOrderData())){
            return -9999;
        }
        $data = $this->getOrderData(0, 2);

        return count($data);
    }

    /**
     * 该Pan卡号当前在全平台的逾期待还款订单的待还款金额之和
     */
    public function checkSumOfCurrentOverdueOrderAmt(){
        if(empty($this->getOrderData())){
            return -9999;
        }
        $data = $this->getOrderData(0, 2);

        return array_sum($data);
    }

    /**
     * 该Pan卡号当前在全平台的逾期待还款订单的待还款金额的最大值
     */
    public function checkMaxOfCurrentOverdueOrderAmt(){
        if(empty($this->getOrderData())){
            return -9999;
        }
        $data = $this->getOrderData(0, 2);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 该Pan卡号当前在全平台的逾期待还款订单的待还款金额的最小值
     */
    public function checkMinOfCurrentOverdueOrderAmt(){
        if(empty($this->getOrderData())){
            return -9999;
        }
        $data = $this->getOrderData(0, 2);

        if(empty($data)){
            return 0;
        }

        return min($data);
    }

    /**
     * 该Pan卡号当前在全平台的逾期待还款订单的待还款金额的平均值
     */
    public function checkAvgOfCurrentOverdueOrderAmt(){
        if(empty($this->getOrderData())){
            return -9999;
        }
        $data = $this->getOrderData(0, 2);

        if(empty($data)){
            return -1;
        }

        return intval(round(array_sum($data) / count($data)));
    }

    /**
     * 该Pan卡号当前在催收API中非来源于全平台的逾期待还款订单数
     */
    public function checkCurrentOverdueOrderCntInCollectionAPIExcludeTPF(){
        $data = $this->getAssistData();

        return $data['pendingCount'];
    }

    /**
     * 该Pan卡号当前在催收API中非来源于全平台的逾期待还款订单的待还款金额之和
     */
    public function checkSumOfCurrentOverdueOrderAmtInCollectionAPIExcludeTPF(){
        $data = $this->getAssistData();

        return $data['pendingSum'];
    }

    /**
     * 该Pan卡号当前在催收API中非来源于全平台的逾期待还款订单的待还款金额的最大值
     */
    public function checkMaxOfCurrentOverdueOrderAmtInCollectionAPIExcludeTPF(){
        $data = $this->getAssistData();

        return $data['pendingMax'];
    }

    /**
     * 该Pan卡号当前在催收API中非来源于全平台的逾期待还款订单的待还款金额的最小值
     */
    public function checkMinOfCurrentOverdueOrderAmtInCollectionAPIExcludeTPF(){
        $data = $this->getAssistData();

        return $data['pendingMin'];
    }

    /**
     * 该Pan卡号当前在催收API中非来源于全平台的逾期待还款订单的待还款金额的平均值
     */
    public function checkAvgOfCurrentOverdueOrderAmtInCollectionAPIExcludeTPF(){
        $data = $this->getAssistData();

        return $data['pendingAvg'];
    }

    /**
     * 该Pan卡号当前在全平台和催收API中的所有待还款订单数（去重）
     */
    public function checkCurrentOrderCntToRepayInCollectionAPIAndTPF(){
        $data = $this->getOrderData(0, 3);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['pendingCount'];
    }

    /**
     * 该Pan卡号当前在全平台和催收API中的所有待还款订单的待还款金额之和（去重）
     */
    public function checkSumOfCurrentOrderAmtToRepayInCollectionAPIAndTPF(){
        $data = $this->getOrderData(0, 3);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['pendingSum'];
    }

    /**
     * 该Pan卡号当前在全平台和催收API中的所有待还款订单的待还款金额的最大值（去重）
     */
    public function checkMaxOfCurrentOrderAmtToRepayInCollectionAPIAndTPF(){
        $data = $this->getOrderData(0, 3);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['pendingMax'];

        return max($data);
    }

    /**
     * 该Pan卡号当前在全平台和催收API中的所有待还款订单的待还款金额的最小值（去重）
     */
    public function checkMinOfCurrentOrderAmtToRepayInCollectionAPIAndTPF(){
        $data = $this->getOrderData(0, 3);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['pendingMin'];

        return min($data);
    }

    /**
     * 该Pan卡号当前在全平台和催收API中的所有待还款订单的待还款金额的平均值（去重）
     */
    public function checkAvgOfCurrentOrderAmtToRepayInCollectionAPIAndTPF(){
        $data = $this->checkCurrentOrderCntToRepayInCollectionAPIAndTPF();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOrderAmtToRepayInCollectionAPIAndTPF();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号当前在全平台的所有待还款订单数
     */
    public function checkCurrentOrderCntToRepayTPF(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->select(['r.id'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }
        $data = $this->getOrderData(0, 3);

        return count($data);
    }

    /**
     * 该Pan卡号当前在全平台的所有待还款订单的待还款金额之和
     */
    public function checkSumOfCurrentOrderAmtToRepayTPF(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->select(['r.id'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }
        $data = $this->getOrderData(0, 3);

        return array_sum($data);
    }

    /**
     * 该Pan卡号当前在全平台的所有待还款订单的待还款金额的最大值
     */
    public function checkMaxOfCurrentOrderAmtToRepayTPF(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->select(['r.id'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }
        $data = $this->getOrderData(0, 3);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 该Pan卡号当前在全平台的所有待还款订单的待还款金额的最小值
     */
    public function checkMinOfCurrentOrderAmtToRepayTPF(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->select(['r.id'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }
        $data = $this->getOrderData(0, 3);

        if(empty($data)){
            return 0;
        }

        return min($data);
    }

    /**
     * 该Pan卡号当前在全平台的所有待还款订单的待还款金额的平均值
     */
    public function checkAvgOfCurrentOrderAmtToRepayTPF(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->select(['r.id'])
            ->where(['u.pan_code' => $this->data->infoUser->pan_code])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }
        $data = $this->getOrderData(0, 3);

        if(empty($data)){
            return -1;
        }

        return intval(round(array_sum($data) / count($data)));
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的入催订单数（去重）
     */
    public function checkIntoCollectOrderCntInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 1);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['assistCount7'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 1);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['assistSum7'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 1);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistMax7'];

        return max($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 1);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistMin7'];

        return min($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast7Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的入催订单数中当前仍待还款订单数的占比（去重）
     */
    public function checkCurrentToRepayOrderCntToIntoCollectOrderCntRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(7, 4);
        $assist_data = $this->getAssistData();

        return round((count($sum) + $assist_data['assistPendingCount7']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的入催订单的应还款金额之和中当前仍待还款订单的应还款金额之和的占比（去重）
     */
    public function checkSumOfCurrentToRepayOrderAmtToSumOfIntoCollectOrderAmtRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(7, 4);
        $assist_data = $this->getAssistData();

        return round((array_sum($sum) + $assist_data['assistPendingSum7']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在催收API中非来源于全平台的入催订单数
     */
    public function checkIntoCollectOrderCntInCollectionAPIExcludeTPFLast7Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCount7'];
    }

    /**
     * 该Pan卡号近7天内在催收API中非来源于全平台的入催订单的应还款金额之和
     */
    public function checkSumOfIntoCollectOrderAmtInCollectionAPIExcludeTPFLast7Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistSum7'];
    }

    /**
     * 该Pan卡号近7天内在催收API中非来源于全平台的入催订单的应还款金额的最大值
     */
    public function checkMaxOfIntoCollectOrderAmtInCollectionAPIExcludeTPFLast7Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistMax7'];
    }

    /**
     * 该Pan卡号近7天内在催收API中非来源于全平台的入催订单的应还款金额的最小值
     */
    public function checkMinOfIntoCollectOrderAmtInCollectionAPIExcludeTPFLast7Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistMin7'];
    }

    /**
     * 该Pan卡号近7天内在催收API中非来源于全平台的入催订单的应还款金额的平均值
     */
    public function checkAvgOfIntoCollectOrderAmtInCollectionAPIExcludeTPFLast7Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistAvg7'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的已结清订单数（去重）
     */
    public function checkClosedOrderCntInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 5);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['assistCloseCount7'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的已结清订单的已结清金额之和（去重）
     */
    public function checkSumOfClosedOrderAmtInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 5);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['assistCloseSum7'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的已结清订单的已结清金额的最大值（去重）
     */
    public function checkMaxOfClosedOrderAmtInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 5);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistCloseMax7'];

        return max($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的已结清订单的已结清金额的最小值（去重）
     */
    public function checkMinOfClosedOrderAmtInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 5);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistCloseMin7'];

        return min($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中的已结清订单的已结清金额的平均值（去重）
     */
    public function checkAvgOfClosedOrderAmtInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkClosedOrderCntInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfClosedOrderAmtInCollectionAPIAndTPFLast7Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近7天内在全平台的已结清订单数
     */
    public function checkClosedOrderCntTPFLast7Days(){
        $data = $this->getOrderData(7, 5);

        return count($data);
    }

    /**
     * 该Pan卡号近7天内在全平台的已结清订单的结清金额之和
     */
    public function checkSumOfClosedOrderAmtTPFLast7Days(){
        $data = $this->getOrderData(7, 5);

        return array_sum($data);
    }

    /**
     * 该Pan卡号近7天内在全平台的已结清订单的结清金额的最大值
     */
    public function checkMaxOfClosedOrderAmtTPFLast7Days(){
        $data = $this->getOrderData(7, 5);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 该Pan卡号近7天内在全平台的已结清订单的结清金额的最小值
     */
    public function checkMinOfClosedOrderAmtTPFLast7Days(){
        $data = $this->getOrderData(7, 5);

        if(empty($data)){
            return 0;
        }

        return min($data);
    }

    /**
     * 该Pan卡号近7天内在全平台的已结清订单的结清金额的平均值
     */
    public function checkAvgOfClosedOrderAmtTPFLast7Days(){
        $data = $this->getOrderData(7, 5);

        if(empty($data)){
            return -1;
        }

        return intval(round(array_sum($data) / count($data)));
    }

    /**
     * 该Pan卡号近7天内在催收API中的已结清订单数
     */
    public function checkClosedOrderCntInCollectionAPILast7Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseCount7'];
    }

    /**
     * 该Pan卡号近7天内在催收API中的已结清订单的结清金额之和
     */
    public function checkSumOfClosedOrderAmtInCollectionAPILast7Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseSum7'];
    }

    /**
     * 该Pan卡号近7天内在催收API中的已结清订单的结清金额的最大值
     */
    public function checkMaxOfClosedOrderAmtInCollectionAPILast7Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseMax7'];
    }

    /**
     * 该Pan卡号近7天内在催收API中的已结清订单的结清金额的最小值
     */
    public function checkMinOfClosedOrderAmtInCollectionAPILast7Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseMin7'];
    }

    /**
     * 该Pan卡号近7天内在催收API中的已结清订单的结清金额的平均值
     */
    public function checkAvgOfClosedOrderAmtInCollectionAPILast7Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseAvg7'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的入催订单数（去重）
     */
    public function checkIntoCollectOrderCntInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 1);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['assistCount30'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 1);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['assistSum30'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 1);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistMax30'];

        return max($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 1);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistMin30'];

        return min($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast30Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的入催订单数中当前仍待还款订单数的占比（去重）
     */
    public function checkCurrentToRepayOrderCntToIntoCollectOrderCntRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(30, 4);
        $assist_data = $this->getAssistData();

        return round((count($sum) + $assist_data['assistPendingCount30']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的入催订单的应还款金额之和中当前仍待还款订单的应还款金额之和的占比（去重）
     */
    public function checkSumOfCurrentToRepayOrderAmtToSumOfIntoCollectOrderAmtRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(30, 4);
        $assist_data = $this->getAssistData();

        return round((array_sum($sum) + $assist_data['assistPendingSum30']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在催收API中非来源于全平台的入催订单数
     */
    public function checkIntoCollectOrderCntInCollectionAPIExcludeTPFLast30Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCount30'];
    }

    /**
     * 该Pan卡号近30天内在催收API中非来源于全平台的入催订单的应还款金额之和
     */
    public function checkSumOfIntoCollectOrderAmtInCollectionAPIExcludeTPFLast30Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistSum30'];
    }

    /**
     * 该Pan卡号近30天内在催收API中非来源于全平台的入催订单的应还款金额的最大值
     */
    public function checkMaxOfIntoCollectOrderAmtInCollectionAPIExcludeTPFLast30Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistMax30'];
    }

    /**
     * 该Pan卡号近30天内在催收API中非来源于全平台的入催订单的应还款金额的最小值
     */
    public function checkMinOfIntoCollectOrderAmtInCollectionAPIExcludeTPFLast30Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistMin30'];
    }

    /**
     * 该Pan卡号近30天内在催收API中非来源于全平台的入催订单的应还款金额的平均值
     */
    public function checkAvgOfIntoCollectOrderAmtInCollectionAPIExcludeTPFLast30Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistAvg30'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的已结清订单数（去重）
     */
    public function checkClosedOrderCntInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 5);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['assistCloseCount30'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的已结清订单的已结清金额之和（去重）
     */
    public function checkSumOfClosedOrderAmtInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 5);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['assistCloseSum30'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的已结清订单的已结清金额的最大值（去重）
     */
    public function checkMaxOfClosedOrderAmtInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 5);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistCloseMax30'];

        return max($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的已结清订单的已结清金额的最小值（去重）
     */
    public function checkMinOfClosedOrderAmtInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 5);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistCloseMin30'];

        return min($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中的已结清订单的已结清金额的平均值（去重）
     */
    public function checkAvgOfClosedOrderAmtInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkClosedOrderCntInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfClosedOrderAmtInCollectionAPIAndTPFLast30Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近30天内在全平台的已结清订单数
     */
    public function checkClosedOrderCntTPFLast30Days(){
        $data = $this->getOrderData(30, 5);

        return count($data);
    }

    /**
     * 该Pan卡号近30天内在全平台的已结清订单的结清金额之和
     */
    public function checkSumOfClosedOrderAmtTPFLast30Days(){
        $data = $this->getOrderData(30, 5);

        return array_sum($data);
    }

    /**
     * 该Pan卡号近30天内在全平台的已结清订单的结清金额的最大值
     */
    public function checkMaxOfClosedOrderAmtTPFLast30Days(){
        $data = $this->getOrderData(30, 5);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 该Pan卡号近30天内在全平台的已结清订单的结清金额的最小值
     */
    public function checkMinOfClosedOrderAmtTPFLast30Days(){
        $data = $this->getOrderData(30, 5);

        if(empty($data)){
            return 0;
        }

        return min($data);
    }

    /**
     * 该Pan卡号近30天内在全平台的已结清订单的结清金额的平均值
     */
    public function checkAvgOfClosedOrderAmtTPFLast30Days(){
        $data = $this->getOrderData(30, 5);

        if(empty($data)){
            return -1;
        }

        return intval(round(array_sum($data) / count($data)));
    }

    /**
     * 该Pan卡号近30天内在催收API中的已结清订单数
     */
    public function checkClosedOrderCntInCollectionAPILast30Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseCount30'];
    }

    /**
     * 该Pan卡号近30天内在催收API中的已结清订单的结清金额之和
     */
    public function checkSumOfClosedOrderAmtInCollectionAPILast30Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseSum30'];
    }

    /**
     * 该Pan卡号近30天内在催收API中的已结清订单的结清金额的最大值
     */
    public function checkMaxOfClosedOrderAmtInCollectionAPILast30Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseMax30'];
    }

    /**
     * 该Pan卡号近30天内在催收API中的已结清订单的结清金额的最小值
     */
    public function checkMinOfClosedOrderAmtInCollectionAPILast30Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseMin30'];
    }

    /**
     * 该Pan卡号近30天内在催收API中的已结清订单的结清金额的平均值
     */
    public function checkAvgOfClosedOrderAmtInCollectionAPILast30Days(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseAvg30'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的入催订单数（去重）
     */
    public function checkIntoCollectOrderCntInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 1);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['assistCount'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 1);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['assistSum'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 1);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistMax'];

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 1);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistMin'];

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtInCollectionAPIAndTPFHist(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFHist();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的入催订单数中当前仍待还款订单数的占比（去重）
     */
    public function checkCurrentToRepayOrderCntToIntoCollectOrderCntRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(0, 4);
        $assist_data = $this->getAssistData();

        return round((count($sum) + $assist_data['assistPendingCount']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的入催订单的应还款金额之和中当前仍待还款订单的应还款金额之和的占比（去重）
     */
    public function checkSumOfCurrentToRepayOrderAmtToSumOfIntoCollectOrderAmtRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(0, 4);
        $assist_data = $this->getAssistData();

        return round((array_sum($sum) + $assist_data['assistPendingSum']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在催收API中非来源于全平台的入催订单数
     */
    public function checkIntoCollectOrderCntInCollectionAPIExcludeTPFHist(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCount'];
    }

    /**
     * 该Pan卡号历史在催收API中非来源于全平台的入催订单的应还款金额之和
     */
    public function checkSumOfIntoCollectOrderAmtInCollectionAPIExcludeTPFHist(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistSum'];
    }

    /**
     * 该Pan卡号历史在催收API中非来源于全平台的入催订单的应还款金额的最大值
     */
    public function checkMaxOfIntoCollectOrderAmtInCollectionAPIExcludeTPFHist(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistMax'];
    }

    /**
     * 该Pan卡号历史在催收API中非来源于全平台的入催订单的应还款金额的最小值
     */
    public function checkMinOfIntoCollectOrderAmtInCollectionAPIExcludeTPFHist(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistMin'];
    }

    /**
     * 该Pan卡号历史在催收API中非来源于全平台的入催订单的应还款金额的平均值
     */
    public function checkAvgOfIntoCollectOrderAmtInCollectionAPIExcludeTPFHist(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistAvg'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的已结清订单数（去重）
     */
    public function checkClosedOrderCntInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 5);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['assistCloseCount'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的已结清订单的已结清金额之和（去重）
     */
    public function checkSumOfClosedOrderAmtInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 5);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['assistCloseSum'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的已结清订单的已结清金额的最大值（去重）
     */
    public function checkMaxOfClosedOrderAmtInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 5);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistCloseMax'];

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的已结清订单的已结清金额的最小值（去重）
     */
    public function checkMinOfClosedOrderAmtInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 5);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistCloseMin'];

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中的已结清订单的已结清金额的平均值（去重）
     */
    public function checkAvgOfClosedOrderAmtInCollectionAPIAndTPFHist(){
        $data = $this->checkClosedOrderCntInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfClosedOrderAmtInCollectionAPIAndTPFHist();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号历史在全平台的已结清订单数
     */
    public function checkClosedOrderCntTPFHist(){
        $data = $this->getOrderData(0, 5);

        return count($data);
    }

    /**
     * 该Pan卡号历史在全平台的已结清订单的结清金额之和
     */
    public function checkSumOfClosedOrderAmtTPFHist(){
        $data = $this->getOrderData(0, 5);

        return array_sum($data);
    }

    /**
     * 该Pan卡号历史在全平台的已结清订单的结清金额的最大值
     */
    public function checkMaxOfClosedOrderAmtTPFHist(){
        $data = $this->getOrderData(0, 5);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台的已结清订单的结清金额的最小值
     */
    public function checkMinOfClosedOrderAmtTPFHist(){
        $data = $this->getOrderData(0, 5);

        if(empty($data)){
            return 0;
        }

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台的已结清订单的结清金额的平均值
     */
    public function checkAvgOfClosedOrderAmtTPFHist(){
        $data = $this->getOrderData(0, 5);

        if(empty($data)){
            return -1;
        }

        return intval(round(array_sum($data) / count($data)));
    }

    /**
     * 该Pan卡号历史在催收API中的已结清订单数
     */
    public function checkClosedOrderCntInCollectionAPIHist(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseCount'];
    }

    /**
     * 该Pan卡号历史在催收API中的已结清订单的结清金额之和
     */
    public function checkSumOfClosedOrderAmtInCollectionAPIHist(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseSum'];
    }

    /**
     * 该Pan卡号历史在催收API中的已结清订单的结清金额的最大值
     */
    public function checkMaxOfClosedOrderAmtInCollectionAPIHist(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseMax'];
    }

    /**
     * 该Pan卡号历史在催收API中的已结清订单的结清金额的最小值
     */
    public function checkMinOfClosedOrderAmtInCollectionAPIHist(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseMin'];
    }

    /**
     * 该Pan卡号历史在催收API中的已结清订单的结清金额的平均值
     */
    public function checkAvgOfClosedOrderAmtInCollectionAPIHist(){
        $assist_data = $this->getAssistData();

        return $assist_data['assistCloseAvg'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品中入催订单数（去重）
     */
    public function checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 6);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderLowPfCount7'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品中入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 6);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderLowPfSum7'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品中入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 6);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMax7'];

        return max($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品中入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 6);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMin7'];

        return min($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品中入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品中入催订单数（去重）
     */
    public function checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 7);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderMidPfCount7'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品中入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 7);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderMidPfSum7'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品中入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 7);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMax7'];

        return max($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品中入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 7);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMin7'];

        return min($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品中入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days();
        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品中入催订单数（去重）
     */
    public function checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 8);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderHighPfCount7'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品中入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 8);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderHighPfSum7'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品中入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 8);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderHighPfMax7'];

        return max($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品中入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 8);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMin7'];

        return min($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品中入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days();
        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品中入催订单数（去重）
     */
    public function checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 6);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderLowPfCount30'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品中入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 6);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderLowPfSum30'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品中入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 6);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMax30'];

        return max($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品中入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 6);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMin30'];

        return min($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品中入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品中入催订单数（去重）
     */
    public function checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 7);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderMidPfCount30'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品中入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 7);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderMidPfSum30'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品中入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 7);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMax30'];

        return max($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品中入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 7);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMin30'];

        return min($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品中入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days();
        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品中入催订单数（去重）
     */
    public function checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 8);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderHighPfCount30'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品中入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 8);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderHighPfSum30'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品中入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 8);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderHighPfMax30'];

        return max($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品中入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 8);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMin30'];

        return min($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品中入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days();
        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品中入催订单数（去重）
     */
    public function checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 6);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderLowPfCount'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品中入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 6);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderLowPfSum'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品中入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 6);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMax'];

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品中入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 6);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMin'];

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品中入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist(){
        $data = $this->checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品中入催订单数（去重）
     */
    public function checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 7);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderMidPfCount'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品中入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 7);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderMidPfSum'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品中入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 7);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMax'];

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品中入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 7);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMin'];

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品中入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist(){
        $data = $this->checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist();
        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品中入催订单数（去重）
     */
    public function checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 8);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderHighPfCount'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品中入催订单的应还款金额之和（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 8);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderHighPfSum'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品中入催订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 8);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderHighPfMax'];

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品中入催订单的应还款金额的最小值（去重）
     */
    public function checkMinOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 8);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMin'];

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品中入催订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist(){
        $data = $this->checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist();
        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单数（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 9);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderLowPfCount7_pending'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额之和（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 9);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderLowPfSum7_pending'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 9);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMax7_pending'];

        return max($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额的最小值（去重）
     */
    public function checkMinOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 9);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMin7_pending'];

        return min($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkCurrentOverdueIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单数（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 10);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderMidPfCount7_pending'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额之和（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 10);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderMidPfSum7_pending'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 10);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMax7_pending'];

        return max($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额的最小值（去重）
     */
    public function checkMinOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 10);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMin7_pending'];

        return min($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkCurrentOverdueIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单数（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 11);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderHighPfCount7_pending'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额之和（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 11);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderHighPfSum7_pending'];
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 11);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderHighPfMax7_pending'];

        return max($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额的最小值（去重）
     */
    public function checkMinOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->getOrderData(7, 11);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderHighPfMin7_pending'];

        return min($data);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkCurrentOverdueIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单数（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 9);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderLowPfCount30_pending'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额之和（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 9);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderLowPfSum30_pending'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 9);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMax30_pending'];

        return max($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额的最小值（去重）
     */
    public function checkMinOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 9);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMin30_pending'];

        return min($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkCurrentOverdueIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单数（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 10);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderMidPfCount30_pending'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额之和（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 10);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderMidPfSum30_pending'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 10);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMax30_pending'];

        return max($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额的最小值（去重）
     */
    public function checkMinOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 10);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMin30_pending'];

        return min($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkCurrentOverdueIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单数（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 11);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderHighPfCount30_pending'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额之和（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 11);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderHighPfSum30_pending'];
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 11);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderHighPfMax30_pending'];

        return max($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额的最小值（去重）
     */
    public function checkMinOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->getOrderData(30, 11);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderHighPfMin30_pending'];

        return min($data);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkCurrentOverdueIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单数（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 9);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderLowPfCount_pending'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额之和（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 9);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderLowPfSum_pending'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 9);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMax_pending'];

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额的最小值（去重）
     */
    public function checkMinOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 9);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderLowPfMin_pending'];

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品中入催订单中当前仍待还款的订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist(){
        $data = $this->checkCurrentOverdueIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单数（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 10);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderMidPfCount_pending'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额之和（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 10);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderMidPfSum_pending'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 10);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMax_pending'];

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额的最小值（去重）
     */
    public function checkMinOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 10);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderMidPfMin_pending'];

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品中入催订单中当前仍待还款的订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist(){
        $data = $this->checkCurrentOverdueIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单数（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 11);
        $assist_data = $this->getAssistData();

        return count($data) + $assist_data['orderHighPfCount_pending'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额之和（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 11);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['orderHighPfSum_pending'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额的最大值（去重）
     */
    public function checkMaxOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 11);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderHighPfMax_pending'];

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额的最小值（去重）
     */
    public function checkMinOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 11);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['orderHighPfMin_pending'];

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品中入催订单中当前仍待还款的订单的应还款金额的平均值（去重）
     */
    public function checkAvgOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist(){
        $data = $this->checkCurrentOverdueIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist();

        return intval(round($sum / $data));
    }

    /**
     * 该Pan卡号当前在全平台和催收API中低档息费产品的待还款订单数占所有待还款订单数的占比（去重）
     */
    public function checkCurrentOverdueOrderCntOfLowPFRTOAllRatioInCollectionAPIAndTPF(){
        $data = $this->checkCurrentOrderCntToRepayInCollectionAPIAndTPF();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(0, 12);
        $assist_data = $this->getAssistData();

        return round( (count($sum) + $assist_data['pendingLowPfCount']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号当前在全平台和催收API中低档息费产品的待还款订单的剩余待还款金额之和占所有待还款订单的剩余待还款金额之和的占比（去重）
     */
    public function checkSumOfCurrentOverdueOrderAmtOfLowPFRTOAllRatioInCollectionAPIAndTPF(){
        $data = $this->checkSumOfCurrentOrderAmtToRepayInCollectionAPIAndTPF();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(0, 12);
        $assist_data = $this->getAssistData();

        return round( (array_sum($sum) + $assist_data['pendingLowPfSum']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号当前在全平台和催收API中中档息费产品的待还款订单数占所有待还款订单数的占比（去重）
     */
    public function checkCurrentOverdueOrderCntOfMidPFRTOAllRatioInCollectionAPIAndTPF(){
        $data = $this->checkCurrentOrderCntToRepayInCollectionAPIAndTPF();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(0, 13);
        $assist_data = $this->getAssistData();

        return round( (count($sum) + $assist_data['pendingMidPfCount']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号当前在全平台和催收API中中档息费产品的待还款订单的剩余待还款金额之和占所有待还款订单的剩余待还款金额之和的占比（去重）
     */
    public function checkSumOfCurrentOverdueOrderAmtOfMidPFRTOAllRatioInCollectionAPIAndTPF(){
        $data = $this->checkSumOfCurrentOrderAmtToRepayInCollectionAPIAndTPF();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(0, 13);
        $assist_data = $this->getAssistData();

        return round( (array_sum($sum) + $assist_data['pendingMidPfSum']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号当前在全平台和催收API中高档息费产品的待还款订单数占所有待还款订单数的占比（去重）
     */
    public function checkCurrentOverdueOrderCntOfHighPFRTOAllRatioInCollectionAPIAndTPF(){
        $data = $this->checkCurrentOrderCntToRepayInCollectionAPIAndTPF();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(0, 14);
        $assist_data = $this->getAssistData();

        return round( (count($sum) + $assist_data['pendingHighPfCount']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号当前在全平台和催收API中高档息费产品的待还款订单的剩余待还款金额之和占所有待还款订单的剩余待还款金额之和的占比（去重）
     */
    public function checkSumOfCurrentOverdueOrderAmtOfHighPFRTOAllRatioInCollectionAPIAndTPF(){
        $data = $this->checkSumOfCurrentOrderAmtToRepayInCollectionAPIAndTPF();
        if(empty($data)){
            return -1;
        }

        $sum = $this->getOrderData(0, 14);
        $assist_data = $this->getAssistData();

        return round( (array_sum($sum) + $assist_data['pendingHighPfSum']) / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品入催订单数占所有入催订单数的比例（去重）
     */
    public function checkIntoCollectOrderCntOfLowPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品入催订单的应还款金额之和占所有入催订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfLowPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品入催订单中当前仍为待还款订单数的比例（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfLowPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkCurrentOverdueIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中低档息费产品入催订单的应还款金额之和中当前仍为待还款订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品入催订单数占所有入催订单数的比例（去重）
     */
    public function checkIntoCollectOrderCntOfLowPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品入催订单的应还款金额之和占所有入催订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfLowPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品入催订单中当前仍为待还款订单数的比例（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfLowPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkCurrentOverdueIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中低档息费产品入催订单的应还款金额之和中当前仍为待还款订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品入催订单数占所有入催订单数的比例（去重）
     */
    public function checkIntoCollectOrderCntOfLowPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品入催订单的应还款金额之和占所有入催订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfLowPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品入催订单中当前仍为待还款订单数的比例（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfLowPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkCurrentOverdueIntoCollectOrderCntOfLowPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中低档息费产品入催订单的应还款金额之和中当前仍为待还款订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkSumOfIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfLowPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品入催订单数占所有入催订单数的比例（去重）
     */
    public function checkIntoCollectOrderCntOfMidPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品入催订单的应还款金额之和占所有入催订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfMidPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品入催订单中当前仍为待还款订单数的比例（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfMidPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkCurrentOverdueIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中中档息费产品入催订单的应还款金额之和中当前仍为待还款订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品入催订单数占所有入催订单数的比例（去重）
     */
    public function checkIntoCollectOrderCntOfMidPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品入催订单的应还款金额之和占所有入催订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfMidPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品入催订单中当前仍为待还款订单数的比例（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfMidPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkCurrentOverdueIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中中档息费产品入催订单的应还款金额之和中当前仍为待还款订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品入催订单数占所有入催订单数的比例（去重）
     */
    public function checkIntoCollectOrderCntOfMidPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品入催订单的应还款金额之和占所有入催订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfMidPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品入催订单中当前仍为待还款订单数的比例（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfMidPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkCurrentOverdueIntoCollectOrderCntOfMidPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中中档息费产品入催订单的应还款金额之和中当前仍为待还款订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkSumOfIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfMidPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品入催订单数占所有入催订单数的比例（去重）
     */
    public function checkIntoCollectOrderCntOfHighPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品入催订单的应还款金额之和占所有入催订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfHighPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品入催订单中当前仍为待还款订单数的比例（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfHighPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkCurrentOverdueIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近7天内在全平台和催收API中高档息费产品入催订单的应还款金额之和中当前仍为待还款订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRToAllRatioInCollectionAPIAndTPFLast7Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast7Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品入催订单数占所有入催订单数的比例（去重）
     */
    public function checkIntoCollectOrderCntOfHighPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品入催订单的应还款金额之和占所有入催订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfHighPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品入催订单中当前仍为待还款订单数的比例（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfHighPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkCurrentOverdueIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号近30天内在全平台和催收API中高档息费产品入催订单的应还款金额之和中当前仍为待还款订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRToAllRatioInCollectionAPIAndTPFLast30Days(){
        $data = $this->checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFLast30Days();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品入催订单数占所有入催订单数的比例（去重）
     */
    public function checkIntoCollectOrderCntOfHighPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkIntoCollectOrderCntInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品入催订单的应还款金额之和占所有入催订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfIntoCollectOrderAmtOfHighPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkSumOfIntoCollectOrderAmtInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品入催订单中当前仍为待还款订单数的比例（去重）
     */
    public function checkCurrentOverdueIntoCollectOrderCntOfHighPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkCurrentOverdueIntoCollectOrderCntOfHighPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中高档息费产品入催订单的应还款金额之和中当前仍为待还款订单的应还款金额之和的比例（去重）
     */
    public function checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRToAllRatioInCollectionAPIAndTPFHist(){
        $data = $this->checkSumOfIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist();
        if(empty($data)){
            return -1;
        }

        $sum = $this->checkSumOfCurrentOverdueIntoCollectOrderAmtOfHighPFRInCollectionAPIAndTPFHist();

        return round($sum / $data * 100, 2);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中逾期订单的逾期天数之和（去重）
     */
    public function checkSumOfOverdueOrderOverdueDaysInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 15);
        $assist_data = $this->getAssistData();

        return array_sum($data) + $assist_data['assistOverdueDaySum'];
    }

    /**
     * 该Pan卡号历史在全平台和催收API中逾期订单的逾期天数的最大值（去重）
     */
    public function checkMaxOfOverdueOrderOverdueDaysInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 15);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistOverdueDayMax'];

        return max($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中逾期订单的逾期天数的最小值（去重）
     */
    public function checkMinOfOverdueOrderOverdueDaysInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 15);
        $assist_data = $this->getAssistData();

        $data[] = $assist_data['assistOverdueDayMin'];

        return min($data);
    }

    /**
     * 该Pan卡号历史在全平台和催收API中逾期订单的逾期天数的平均值（去重）
     */
    public function checkAvgOfOverdueOrderOverdueDaysInCollectionAPIAndTPFHist(){
        $data = $this->getOrderData(0, 15);
        $assist_data = $this->getAssistData();
        $count = count($data) + $assist_data['assistOverdueDayCount'];

        if(empty($count)){
            return -1;
        }

        $sum = $this->checkSumOfOverdueOrderOverdueDaysInCollectionAPIAndTPFHist();

        return intval(round($sum / $count));
    }

    /**
     * 全老本新无短信模型分V3
     */
    public function checkQLBXUserModelWoSmsV3(){
        $score = 0;
        $v1401 = $this->checkSumOfOverdueDayOfNonOverdueOrderLast90DaysInTPF();
        switch (true){
            case $v1401 < 0:
                $score += 35;
                break;
            case $v1401 < 50:
                $score += 32;
                break;
            case $v1401 >= 50:
                $score += 33;
                break;
        }

        $v325 = $this->checkLast90MobilePhotoAmount();
        switch (true){
            case $v325 < 100:
                $score += 25;
                break;
            case $v325 < 1600:
                $score += 32;
                break;
            case $v325 < 2100:
                $score += 45;
                break;
            case $v325 >= 2100:
                $score += 41;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 4:
                $score += 34;
                break;
            case $v103 < 5:
                $score += 31;
                break;
            case $v103 < 6:
                $score += 32;
                break;
            case $v103 >= 6:
                $score += 36;
                break;
        }

        $v562 = $this->checkLast7dLoanAppRate();
        switch (true){
            case $v562 < 2:
                $score += 33;
                break;
            case $v562 < 26:
                $score += 34;
                break;
            case $v562 >= 26:
                $score += 35;
                break;
        }

        $v705 = $this->checkHisLoanCntByPanTotPlatform();
        switch (true){
            case $v705 < 6:
                $score += 26;
                break;
            case $v705 < 24:
                $score += 34;
                break;
            case $v705 < 30:
                $score += 51;
                break;
            case $v705 >= 30:
                $score += 40;
                break;
        }

        $v563 = $this->checkLast30dLoanAppRate();
        switch (true){
            case $v563 < 16:
                $score += 29;
                break;
            case $v563 < 48:
                $score += 38;
                break;
            case $v563 >= 48:
                $score += 34;
                break;
        }

        $v142 = $this->checkAddressBookContactCnt();
        switch (true){
            case $v142 < 100:
                $score += 25;
                break;
            case $v142 < 700:
                $score += 32;
                break;
            case $v142 >= 700:
                $score += 37;
                break;
        }

        $v726 = $this->checkLastLoanOrderCpDayTotPlatform();
        switch (true){
            case $v726 < -1:
                $score += 33;
                break;
            case $v726 < 0:
                $score += 34;
                break;
            case $v726 >= 0:
                $score += 33;
                break;
        }

        $v323 = $this->checkHisMobilePhotoAmount();
        switch (true){
            case $v323 < 1000:
                $score += 31;
                break;
            case $v323 < 6000:
                $score += 33;
                break;
            case $v323 < 10000:
                $score += 36;
                break;
            case $v323 >= 10000:
                $score += 38;
                break;
        }

        $v583 = $this->checkLast90ApplyCntBySMDeviceIDInTotPlatporm();
        switch (true){
            case $v583 < 3:
                $score += 33;
                break;
            case $v583 < 5:
                $score += 31;
                break;
            case $v583 < 13:
                $score += 34;
                break;
            case $v583 >= 13:
                $score += 35;
                break;
        }

        $v555 = $this->checkLast3dAppCnt();
        switch (true){
            case $v555 < 1:
                $score += 29;
                break;
            case $v555 < 3:
                $score += 33;
                break;
            case $v555 < 4:
                $score += 37;
                break;
            case $v555 < 8:
                $score += 35;
                break;
            case $v555 >= 8:
                $score += 33;
                break;
        }

        $v560 = $this->checkLast30dLoanAppCnt();
        switch (true){
            case $v560 < 1:
                $score += 30;
                break;
            case $v560 < 2:
                $score += 32;
                break;
            case $v560 >= 2:
                $score += 37;
                break;
        }

        $v557 = $this->checkLast30dAppCnt();
        switch (true){
            case $v557 < 3:
                $score += 28;
                break;
            case $v557 < 4:
                $score += 30;
                break;
            case $v557 < 8:
                $score += 34;
                break;
            case $v557 < 10:
                $score += 39;
                break;
            case $v557 >= 10:
                $score += 33;
                break;
        }

        $v722 = $this->checkOldUserComplexRuleV1HisCpDaySumTotPlatform();
        switch (true){
            case $v722 < -34:
                $score += 54;
                break;
            case $v722 < -18:
                $score += 45;
                break;
            case $v722 < 0:
                $score += 32;
                break;
            case $v722 >= 0:
                $score += 26;
                break;
        }

        $v686 = $this->checkLast90dApplyCntByPanInTotPlatform();
        switch (true){
            case $v686 < 3:
                $score += 31;
                break;
            case $v686 < 5:
                $score += 25;
                break;
            case $v686 < 13:
                $score += 37;
                break;
            case $v686 < 17:
                $score += 42;
                break;
            case $v686 >= 17:
                $score += 54;
                break;
        }

        return $score;
    }

    /**
     * 全老本新无短信模型分V3
     */
    public function checkQLBXUserModelWoSmsV4(){
        $score = 0;
        $v1213 = $this->checkHistSMDeviceCntOfLoginAndOrderInTPF();
        switch (true){
            case $v1213 < 3:
                $score += 42;
                break;
            case $v1213 < 4:
                $score += 43;
                break;
            case $v1213 >= 4:
                $score += 46;
                break;
        }

        $v722 = $this->checkOldUserComplexRuleV1HisCpDaySumTotPlatform();
        switch (true){
            case $v722 < -50:
                $score += 73;
                break;
            case $v722 < -16:
                $score += 57;
                break;
            case $v722 < 0:
                $score += 39;
                break;
            case $v722 >= 0:
                $score += 33;
                break;
        }

        $v1636 = $this->checkAvgOfIntoCollectOrderAmtInCollectionAPIAndTPFHist();
        switch (true){
            case $v1636 < 0:
                $score += 43;
                break;
            case $v1636 < 350000:
                $score += 37;
                break;
            case $v1636 < 650000:
                $score += 37;
                break;
            case $v1636 >= 650000:
                $score += 50;
                break;
        }

        $v1466 = $this->checkAvgDayDiffBtwHistLoanTimeAndThisApplyTimeInTPF();
        switch (true){
            case $v1466 < 110:
                $score += 39;
                break;
            case $v1466 < 150:
                $score += 42;
                break;
            case $v1466 < 340:
                $score += 48;
                break;
            case $v1466 >= 340:
                $score += 34;
                break;
        }

        $v559 = $this->checkLast7dLoanAppCnt();
        switch (true){
            case $v559 < 1:
                $score += 40;
                break;
            case $v559 < 2:
                $score += 44;
                break;
            case $v559 >= 2:
                $score += 46;
                break;
        }

        $v325 = $this->checkLast90MobilePhotoAmount();
        switch (true){
            case $v325 < 100:
                $score += 37;
                break;
            case $v325 < 900:
                $score += 41;
                break;
            case $v325 < 2500:
                $score += 44;
                break;
            case $v325 < 3300:
                $score += 56;
                break;
            case $v325 >= 3300:
                $score += 46;
                break;
        }

        $v720 = $this->checkOldUserComplexRuleV1HisTiqianOrderCntTotPlatform();
        switch (true){
            case $v720 < 5:
                $score += 33;
                break;
            case $v720 < 25:
                $score += 42;
                break;
            case $v720 < 80:
                $score += 48;
                break;
            case $v720 >= 80:
                $score += 56;
                break;
        }

        $v103 = $this->checkHighEducationLevel();
        switch (true){
            case $v103 < 6:
                $score += 42;
                break;
            case $v103 >= 6:
                $score += 45;
                break;
        }

        $v563 = $this->checkLast30dLoanAppRate();
        switch (true){
            case $v563 < 16:
                $score += 39;
                break;
            case $v563 < 22:
                $score += 44;
                break;
            case $v563 >= 22:
                $score += 46;
                break;
        }

        $v560 = $this->checkLast30dLoanAppCnt();
        switch (true){
            case $v560 < 1:
                $score += 38;
                break;
            case $v560 < 2:
                $score += 40;
                break;
            case $v560 >= 2:
                $score += 47;
                break;
        }

        $v724 = $this->checkOldUserComplexRuleV1HisDueOrderCntHisOrderCntRateTotPlatform();
        switch (true){
            case $v724 < 18:
                $score += 43;
                break;
            case $v724 < 26:
                $score += 45;
                break;
            case $v724 >= 26:
                $score += 37;
                break;
        }

        $v695 = $this->checkLast90dApplyCntByPhoneTotPlatform();
        switch (true){
            case $v695 < 2:
                $score += 43;
                break;
            case $v695 < 3:
                $score += 41;
                break;
            case $v695 < 5:
                $score += 37;
                break;
            case $v695 >= 5:
                $score += 45;
                break;
        }

        return $score;
    }

    /**
     * 近30天内该笔订单的手机号码各次登录、下单时关联的不同数盟设备ID数量
     * @return int
     */
    public function checkPhoneNumberMatchSMDIDCntInLoginAndOrderLast30DaysTPF(){
        $before = $this->data->infoOrder->order_time - 30 * 86400;
        return $this->getPhoneMatchSMDIDCntInLoginAndOrder($before);
    }

    /**
     * 近60天内该笔订单的手机号码各次登录、下单时关联的不同数盟设备ID数量
     * @return int
     */
    public function checkPhoneNumberMatchSMDIDCntInLoginAndOrderLast60DaysTPF(){
        $before = $this->data->infoOrder->order_time - 60 * 86400;
        return $this->getPhoneMatchSMDIDCntInLoginAndOrder($before);
    }

    /**
     * 近90天内该笔订单的手机号码各次登录、下单时关联的不同数盟设备ID数量
     * @return int
     */
    public function checkPhoneNumberMatchSMDIDCntInLoginAndOrderLast90DaysTPF(){
        $before = $this->data->infoOrder->order_time - 90 * 86400;
        return $this->getPhoneMatchSMDIDCntInLoginAndOrder($before);
    }

    /**
     * 历史该笔订单的手机号码各次登录、下单时关联的不同数盟设备ID数量
     * @return int
     */
    public function checkHistPhoneNumberMatchSMDIDCntInLoginAndOrderTPF(){
        return $this->getPhoneMatchSMDIDCntInLoginAndOrder();
    }

    /**
     * 近30天内该笔订单的数盟设备ID在各次登录、下单时关联的不同身份证数量
     * @return int
     */
    public function checkSMDIDMatchIDCardCntInLoginAndOrderLast30DaysTPF(){
        $before = $this->data->infoOrder->order_time - 30 * 86400;
        return $this->getSMDIDMatchIDCardCntInLoginAndOrderTPF($before);
    }

    /**
     * 近60天内该笔订单的数盟设备ID在各次登录、下单时关联的不同身份证数量
     * @return int
     */
    public function checkSMDIDMatchIDCardCntInLoginAndOrderLast60DaysTPF(){
        $before = $this->data->infoOrder->order_time - 60 * 86400;
        return $this->getSMDIDMatchIDCardCntInLoginAndOrderTPF($before);
    }

    /**
     * 近90天内该笔订单的数盟设备ID在各次登录、下单时关联的不同身份证数量
     * @return int
     */
    public function checkSMDIDMatchIDCardCntInLoginAndOrderLast90DaysTPF(){
        $before = $this->data->infoOrder->order_time - 90 * 86400;
        return $this->getSMDIDMatchIDCardCntInLoginAndOrderTPF($before);
    }

    /**
     * 历史该笔订单的数盟设备ID在各次登录、下单时关联的不同身份证数量
     * @return int
     */
    public function checkHistSMDIDMatchIDCardCntInLoginAndOrderTPF(){
        return $this->getSMDIDMatchIDCardCntInLoginAndOrderTPF();
    }

    /**
     * 近30天内该笔订单的数盟设备ID在各次登录、下单时关联的不同手机号码数量
     * @return int
     */
    public function checkSMDIDMatchPhoneNumberCntInLoginAndOrderLast30DaysTPF(){
        $before = $this->data->infoOrder->order_time - 30 * 86400;
        return $this->getSMDIDMatchPhoneNumberCntInLoginAndOrderTPF($before);
    }

    /**
     * 近60天内该笔订单的数盟设备ID在各次登录、下单时关联的不同手机号码数量
     * @return int
     */
    public function checkSMDIDMatchPhoneNumberCntInLoginAndOrderLast60DaysTPF(){
        $before = $this->data->infoOrder->order_time - 60 * 86400;
        return $this->getSMDIDMatchPhoneNumberCntInLoginAndOrderTPF($before);
    }

    /**
     * 近90天内该笔订单的数盟设备ID在各次登录、下单时关联的不同手机号码数量
     * @return int
     */
    public function checkSMDIDMatchPhoneNumberCntInLoginAndOrderLast90DaysTPF(){
        $before = $this->data->infoOrder->order_time - 90 * 86400;
        return $this->getSMDIDMatchPhoneNumberCntInLoginAndOrderTPF($before);
    }

    /**
     * 历史该笔订单的数盟设备ID在各次登录、下单时关联的不同手机号码数量
     * @return int
     */
    public function checkHistSMDIDMatchPhoneNumberCntInLoginAndOrderTPF(){
        return $this->getSMDIDMatchPhoneNumberCntInLoginAndOrderTPF();
    }

    /**
     * 近30天内该笔订单的数盟设备ID在各次登录、下单时关联的不同身份证数量
     * @return int
     */
    public function checkSMDIDMatchIDCardCntInLoginAndOrderLast30DaysTP(){
        $before = $this->data->infoOrder->order_time - 30 * 86400;
        return $this->getSMDIDMatchIDCardCntInLoginAndOrderTP($before);
    }

    /**
     * 近60天内该笔订单的数盟设备ID在各次登录、下单时关联的不同身份证数量
     * @return int
     */
    public function checkSMDIDMatchIDCardCntInLoginAndOrderLast60DaysTP(){
        $before = $this->data->infoOrder->order_time - 60 * 86400;
        return $this->getSMDIDMatchIDCardCntInLoginAndOrderTP($before);
    }

    /**
     * 近90天内该笔订单的数盟设备ID在各次登录、下单时关联的不同身份证数量
     * @return int
     */
    public function checkSMDIDMatchIDCardCntInLoginAndOrderLast90DaysTP(){
        $before = $this->data->infoOrder->order_time - 90 * 86400;
        return $this->getSMDIDMatchIDCardCntInLoginAndOrderTP($before);
    }

    /**
     * 历史该笔订单的数盟设备ID在各次登录、下单时关联的不同身份证数量
     * @return int
     */
    public function checkHistSMDIDMatchIDCardCntInLoginAndOrderTP(){
        return $this->getSMDIDMatchIDCardCntInLoginAndOrderTP();
    }

    /**
     * 近30天内该笔订单的身份证号码各次登录、下单时关联的不同数盟设备ID数量
     * @return int
     */
    public function checkIDCardMatchSMDIDCntInLoginAndOrderLast30DaysTP(){
        $before = $this->data->infoOrder->order_time - 30 * 86400;
        return $this->getIDCardMatchSMDIDCntInLoginAndOrderTP($before);
    }

    /**
     * 近60天内该笔订单的身份证号码各次登录、下单时关联的不同数盟设备ID数量
     * @return int
     */
    public function checkIDCardMatchSMDIDCntInLoginAndOrderLast60DaysTP(){
        $before = $this->data->infoOrder->order_time - 60 * 86400;
        return $this->getIDCardMatchSMDIDCntInLoginAndOrderTP($before);
    }

    /**
     * 近90天内该笔订单的身份证号码各次登录、下单时关联的不同数盟设备ID数量
     * @return int
     */
    public function checkIDCardMatchSMDIDCntInLoginAndOrderLast90DaysTP(){
        $before = $this->data->infoOrder->order_time - 90 * 86400;
        return $this->getIDCardMatchSMDIDCntInLoginAndOrderTP($before);
    }

    /**
     * 历史该笔订单的身份证号码各次登录、下单时关联的不同数盟设备ID数量
     * @return int
     */
    public function checkHistIDCardMatchSMDIDCntInLoginAndOrderTP(){
        return $this->getIDCardMatchSMDIDCntInLoginAndOrderTP();
    }

    /**
     * 近7天内该身份证号在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByIDCardLast7DaysLargeTPF(){
        if(empty($this->getOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getOrderData(7, 21);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该身份证号在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByIDCardLast30DaysLargeTPF(){
        if(empty($this->getOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getOrderData(30, 21);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该身份证号在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByIDCardLast60DaysLargeTPF(){
        if(empty($this->getOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getOrderData(60, 21);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该身份证号在全平台的平均逾期天数
     * @return int
     */
    public function checkAvgOfOverdueDayByIDCardLast7DaysLargeTPF(){
        if(empty($this->getOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getOrderData(7, 21);

        if(empty($data)){
            return 0;
        }

        return round(array_sum($data) / count($data));
    }

    /**
     * 近30天内该身份证号在全平台的平均逾期天数
     * @return int
     */
    public function checkAvgOfOverdueDayByIDCardLast30DaysLargeTPF(){
        if(empty($this->getOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getOrderData(30, 21);

        if(empty($data)){
            return 0;
        }

        return round(array_sum($data) / count($data));
    }

    /**
     * 近60天内该身份证号在全平台的平均逾期天数
     * @return int
     */
    public function checkAvgOfOverdueDayByIDCardLast60DaysLargeTPF(){
        if(empty($this->getOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getOrderData(60, 21);

        if(empty($data)){
            return 0;
        }

        return round(array_sum($data) / count($data));
    }

    /**
     * 近7天内该身份证号在全平台的逾期天数之和
     * @return int
     */
    public function checkSumOfOverdueDayByIDCardLast7DaysLargeTPF(){
        if(empty($this->getOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getOrderData(7, 21);

        return array_sum($data);
    }

    /**
     * 近30天内该身份证号在全平台的逾期天数之和
     * @return int
     */
    public function checkSumOfOverdueDayByIDCardLast30DaysLargeTPF(){
        if(empty($this->getOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getOrderData(30, 21);

        return array_sum($data);
    }

    /**
     * 近60天内该身份证号在全平台的逾期天数之和
     * @return int
     */
    public function checkSumOfOverdueDayByIDCardLast60DaysLargeTPF(){
        if(empty($this->getOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getOrderData(60, 21);

        return array_sum($data);
    }

    /**
     * 近7天内该身份证号在全平台的逾期订单数
     * @return int
     */
    public function checkOverdueOrderCntByIDCardLast7DaysLargeTPF(){
        if(empty($this->getOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getOrderData(7, 21);

        return count($data);
    }

    /**
     * 近30天内该身份证号在全平台的逾期订单数
     * @return int
     */
    public function checkOverdueOrderCntByIDCardLast30DaysLargeTPF(){
        if(empty($this->getOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getOrderData(30, 21);

        return count($data);
    }

    /**
     * 近60天内该身份证号在全平台的逾期订单数
     * @return int
     */
    public function checkOverdueOrderCntByIDCardLast60DaysLargeTPF(){
        if(empty($this->getOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getOrderData(60, 21);

        return count($data);
    }

    /**
     * 近7天内该身份证号在全平台的逾期4天及以上的订单数
     * @return int
     */
    public function checkOverdue4DaysUpOrderCntByIDCardLast7DaysLargeTPF(){
        if(empty($this->getOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getOrderData(7, 21);
        $i = 0;
        foreach ($data as $v){
            if($v >= 4){
                $i++;
            }
        }

        return $i;
    }

    /**
     * 近30天内该身份证号在全平台的逾期4天及以上的订单数
     * @return int
     */
    public function checkOverdue4DaysUpOrderCntByIDCardLast30DaysLargeTPF(){
        if(empty($this->getOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getOrderData(30, 21);
        $i = 0;
        foreach ($data as $v){
            if($v >= 4){
                $i++;
            }
        }

        return $i;
    }

    /**
     * 近60天内该身份证号在全平台的逾期4天及以上的订单数
     * @return int
     */
    public function checkOverdue4DaysUpOrderCntByIDCardLast60DaysLargeTPF(){
        if(empty($this->getOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getOrderData(60, 21);
        $i = 0;
        foreach ($data as $v){
            if($v >= 4){
                $i++;
            }
        }

        return $i;
    }

    /**
     * 近7天内该身份证号在全平台逾期订单金额的最大值
     * @return int
     */
    public function checkMaxOverdueOrderAmtByIDCardLast7DaysLargeTPF(){
        if(empty($this->getOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getOrderData(7, 22);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该身份证号在全平台逾期订单金额的最大值
     * @return int
     */
    public function checkMaxOverdueOrderAmtByIDCardLast30DaysLargeTPF(){
        if(empty($this->getOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getOrderData(30, 22);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该身份证号在全平台逾期订单金额的最大值
     * @return int
     */
    public function checkMaxOverdueOrderAmtByIDCardLast60DaysLargeTPF(){
        if(empty($this->getOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getOrderData(60, 22);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该身份证号在全平台逾期订单金额之和
     * @return int
     */
    public function checkSumOfOverdueOrderAmtByIDCardLast7DaysLargeTPF(){
        if(empty($this->getOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getOrderData(7, 22);

        return array_sum($data);
    }

    /**
     * 近30天内该身份证号在全平台逾期订单金额之和
     * @return int
     */
    public function checkSumOfOverdueOrderAmtByIDCardLast30DaysLargeTPF(){
        if(empty($this->getOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getOrderData(30, 22);

        return array_sum($data);
    }

    /**
     * 近60天内该身份证号在全平台逾期订单金额之和
     * @return int
     */
    public function checkSumOfOverdueOrderAmtByIDCardLast60DaysLargeTPF(){
        if(empty($this->getOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getOrderData(60, 22);

        return array_sum($data);
    }

    /**
     * 近7天内该身份证号在全平台的结清日期与应还款日期天数差值之和
     */
    public function checkSumOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast7DaysLargeTPF(){
        $data = $this->getOrderData(7, 23);
        if(empty($data)){
            return -9999;
        }

        return array_sum($data);
    }

    /**
     * 近30天内该身份证号在全平台的结清日期与应还款日期天数差值之和
     */
    public function checkSumOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast30DaysLargeTPF(){
        $data = $this->getOrderData(30, 23);
        if(empty($data)){
            return -9999;
        }

        return array_sum($data);
    }

    /**
     * 近60天内该身份证号在全平台的结清日期与应还款日期天数差值之和
     */
    public function checkSumOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast60DaysLargeTPF(){
        $data = $this->getOrderData(60, 23);
        if(empty($data)){
            return -9999;
        }

        return array_sum($data);
    }

    /**
     * 历史累计该身份证号在全平台的结清日期与应还款日期天数差值之和
     */
    public function checkHistSumOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLargeTPF(){
        $data = $this->getOrderData(0, 23);
        if(empty($data)){
            return -9999;
        }

        return array_sum($data);
    }

    /**
     * 近7天内该身份证号在全平台的结清日期与应还款日期天数差值的最大值
     */
    public function checkMaxOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast7DaysLargeTPF(){
        $data = $this->getOrderData(7, 23);
        if(empty($data)){
            return -9999;
        }

        return max($data);
    }

    /**
     * 近30天内该身份证号在全平台的结清日期与应还款日期天数差值的最大值
     */
    public function checkMaxOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast30DaysLargeTPF(){
        $data = $this->getOrderData(30, 23);
        if(empty($data)){
            return -9999;
        }

        return max($data);
    }

    /**
     * 近60天内该身份证号在全平台的结清日期与应还款日期天数差值的最大值
     */
    public function checkMaxOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast60DaysLargeTPF(){
        $data = $this->getOrderData(60, 23);
        if(empty($data)){
            return -9999;
        }

        return max($data);
    }

    /**
     * 历史累计该身份证号在全平台的结清日期与应还款日期天数差值的最大值
     */
    public function checkHistMaxOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLargeTPF(){
        $data = $this->getOrderData(0, 23);
        if(empty($data)){
            return -9999;
        }

        return max($data);
    }

    /**
     * 近7天内该身份证号在全平台未逾期结清订单与应还款日期天数差值之和
     */
    public function checkSumOfNonOverdueClosedOrderDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast7DaysLargeTPF(){
        if(empty($this->getOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getOrderData(7, 24);
        return array_sum($data);
    }

    /**
     * 近30天内该身份证号在全平台未逾期结清订单与应还款日期天数差值之和
     */
    public function checkSumOfNonOverdueClosedOrderDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast30DaysLargeTPF(){
        if(empty($this->getOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getOrderData(30, 24);
        return array_sum($data);
    }

    /**
     * 近60天内该身份证号在全平台未逾期结清订单与应还款日期天数差值之和
     */
    public function checkSumOfNonOverdueClosedOrderDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast60DaysLargeTPF(){
        if(empty($this->getOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getOrderData(60, 24);
        return array_sum($data);
    }

    /**
     * 历史累计该身份证号在全平台未逾期结清订单与应还款日期天数差值之和
     */
    public function checkHistSumOfNonOverdueClosedOrderDayDiffBtwPlanRepayDateAndClosedDateByIDCardLargeTPF(){
        if(empty($this->getOrderData(0, 25))){
            return -9999;
        }
        $data = $this->getOrderData(0, 24);
        return array_sum($data);
    }

    /**
     * 近7天内该身份证号在全平台的逾期订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdueOrderCntToMaturedOrderCntByIDCardLast7DaysLargeTPF(){
        $data = $this->getOrderData(7);
        if(empty($data)){
            return -9999;
        }

        if(empty($this->getOrderData(7, 25))){
            return -9999;
        }

        $count = $this->getOrderData(7, 22);
        return round(count($count) / count($data) * 100, 2);
    }

    /**
     * 近30天内该身份证号在全平台的逾期订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdueOrderCntToMaturedOrderCntByIDCardLast30DaysLargeTPF(){
        $data = $this->getOrderData(30);
        if(empty($data)){
            return -9999;
        }

        if(empty($this->getOrderData(7, 25))){
            return -9999;
        }

        $count = $this->getOrderData(30, 22);
        return round(count($count) / count($data) * 100, 2);
    }

    /**
     * 近60天内该身份证号在全平台的逾期订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdueOrderCntToMaturedOrderCntByIDCardLast60DaysLargeTPF(){
        $data = $this->getOrderData(60);
        if(empty($data)){
            return -9999;
        }

        if(empty($this->getOrderData(7, 25))){
            return -9999;
        }

        $count = $this->getOrderData(60, 22);
        return round(count($count) / count($data) * 100, 2);
    }

    /**
     * 近7天内该身份证号在全平台的逾期4天及以上订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToMaturedOrderCntByIDCardLast7DaysLargeTPF(){
        $data = $this->getOrderData(7);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast7DaysLargeTPF();
        if($count == -9999){
            return -9999;
        }
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近30天内该身份证号在全平台的逾期4天及以上订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToMaturedOrderCntByIDCardLast30DaysLargeTPF(){
        $data = $this->getOrderData(30);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast30DaysLargeTPF();
        if($count == -9999){
            return -9999;
        }
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近60天内该身份证号在全平台的逾期4天及以上订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToMaturedOrderCntByIDCardLast60DaysLargeTPF(){
        $data = $this->getOrderData(60);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast60DaysLargeTPF();
        if($count == -9999){
            return -9999;
        }
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近7天内该身份证号在全平台的逾期4天及以上订单数占逾期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToOverdueOrderCntByIDCardLast7DaysLargeTPF(){
        $data = $this->getOrderData(7, 22);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast7DaysLargeTPF();
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近30天内该身份证号在全平台的逾期4天及以上订单数占逾期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToOverdueOrderCntByIDCardLast30DaysLargeTPF(){
        $data = $this->getOrderData(30, 22);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast30DaysLargeTPF();
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近60天内该身份证号在全平台的逾期4天及以上订单数占逾期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToOverdueOrderCntByIDCardLast60DaysLargeTPF(){
        $data = $this->getOrderData(60, 22);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast60DaysLargeTPF();
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近7天内该身份证号在本产品的最大逾期天数
     */
    public function checkMaxOverdueDayByIDCardLast7DaysLarge(){
        if(empty($this->getProductOrderData(7, 25))){
            return -9999;
        }

        $data = $this->getProductOrderData(7, 21);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该身份证号在本产品的最大逾期天数
     */
    public function checkMaxOverdueDayByIDCardLast30DaysLarge(){
        if(empty($this->getProductOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(30, 21);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该身份证号在本产品的最大逾期天数
     */
    public function checkMaxOverdueDayByIDCardLast60DaysLarge(){
        if(empty($this->getProductOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(60, 21);

        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该身份证号在本产品的平均逾期天数
     * @return int
     */
    public function checkAvgOfOverdueDayByIDCardLast7DaysLarge(){
        if(empty($this->getProductOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(7, 21);

        if(empty($data)){
            return 0;
        }

        return round(array_sum($data) / count($data));
    }

    /**
     * 近30天内该身份证号在本产品的平均逾期天数
     * @return int
     */
    public function checkAvgOfOverdueDayByIDCardLast30DaysLarge(){
        if(empty($this->getProductOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(30, 21);

        if(empty($data)){
            return 0;
        }

        return round(array_sum($data) / count($data));
    }

    /**
     * 近60天内该身份证号在本产品的平均逾期天数
     * @return int
     */
    public function checkAvgOfOverdueDayByIDCardLast60DaysLarge(){
        if(empty($this->getProductOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(60, 21);

        if(empty($data)){
            return 0;
        }

        return round(array_sum($data) / count($data));
    }

    /**
     * 近7天内该身份证号在本产品的逾期天数之和
     */
    public function checkSumOfOverdueDayByIDCardLast7DaysLarge(){
        if(empty($this->getProductOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(7, 21);
        return array_sum($data);
    }

    /**
     * 近30天内该身份证号在本产品的逾期天数之和
     */
    public function checkSumOfOverdueDayByIDCardLast30DaysLarge(){
        if(empty($this->getProductOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(30, 21);
        return array_sum($data);
    }

    /**
     * 近60天内该身份证号在本产品的逾期天数之和
     */
    public function checkSumOfOverdueDayByIDCardLast60DaysLarge(){
        if(empty($this->getProductOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(60, 21);
        return array_sum($data);
    }

    /**
     * 近7天内该身份证号在本产品的逾期订单数
     * @return int
     */
    public function checkOverdueOrderCntByIDCardLast7DaysLarge(){
        if(empty($this->getProductOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(7, 21);
        return count($data);
    }

    /**
     * 近30天内该身份证号在本产品的逾期订单数
     * @return int
     */
    public function checkOverdueOrderCntByIDCardLast30DaysLarge(){
        if(empty($this->getProductOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(30, 21);
        return count($data);
    }

    /**
     * 近60天内该身份证号在本产品的逾期订单数
     * @return int
     */
    public function checkOverdueOrderCntByIDCardLast60DaysLarge(){
        if(empty($this->getProductOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(60, 21);
        return count($data);
    }

    /**
     * 近7天内该身份证号在本产品的逾期4天及以上的订单数
     * @return int
     */
    public function checkOverdue4DaysUpOrderCntByIDCardLast7DaysLarge(){
        if(empty($this->getProductOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(7, 21);
        $i = 0;
        foreach ($data as $v){
            if($v >= 4){
                $i++;
            }
        }

        return $i;
    }

    /**
     * 近30天内该身份证号在本产品的逾期4天及以上的订单数
     * @return int
     */
    public function checkOverdue4DaysUpOrderCntByIDCardLast30DaysLarge(){
        if(empty($this->getProductOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(30, 21);
        $i = 0;
        foreach ($data as $v){
            if($v >= 4){
                $i++;
            }
        }

        return $i;
    }

    /**
     * 近60天内该身份证号在本产品的逾期4天及以上的订单数
     * @return int
     */
    public function checkOverdue4DaysUpOrderCntByIDCardLast60DaysLarge(){
        if(empty($this->getProductOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(60, 21);
        $i = 0;
        foreach ($data as $v){
            if($v >= 4){
                $i++;
            }
        }

        return $i;
    }

    /**
     * 近7天内该身份证号在本产品逾期订单金额的最大值
     * @return int
     */
    public function checkMaxOverdueOrderAmtByIDCardLast7DaysLarge(){
        if(empty($this->getProductOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(7, 22);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该身份证号在本产品逾期订单金额的最大值
     * @return int
     */
    public function checkMaxOverdueOrderAmtByIDCardLast30DaysLarge(){
        if(empty($this->getProductOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(30, 22);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该身份证号在本产品逾期订单金额的最大值
     * @return int
     */
    public function checkMaxOverdueOrderAmtByIDCardLast60DaysLarge(){
        if(empty($this->getProductOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(60, 22);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该身份证号在本产品逾期订单金额之和
     * @return int
     */
    public function checkSumOfOverdueOrderAmtByIDCardLast7DaysLarge(){
        if(empty($this->getProductOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(7, 22);
        return array_sum($data);
    }

    /**
     * 近30天内该身份证号在本产品逾期订单金额之和
     * @return int
     */
    public function checkSumOfOverdueOrderAmtByIDCardLast30DaysLarge(){
        if(empty($this->getProductOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(30, 22);
        return array_sum($data);
    }

    /**
     * 近60天内该身份证号在本产品逾期订单金额之和
     * @return int
     */
    public function checkSumOfOverdueOrderAmtByIDCardLast60DaysLarge(){
        if(empty($this->getProductOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(60, 22);
        return array_sum($data);
    }

    /**
     * 近7天内该身份证号在本产品的结清日期与应还款日期天数差值之和
     */
    public function checkSumOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast7DaysLarge(){
        $data = $this->getProductOrderData(7, 23);
        if(empty($data)){
            return -9999;
        }
        return array_sum($data);
    }

    /**
     * 近30天内该身份证号在本产品的结清日期与应还款日期天数差值之和
     */
    public function checkSumOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast30DaysLarge(){
        $data = $this->getProductOrderData(30, 23);
        if(empty($data)){
            return -9999;
        }
        return array_sum($data);
    }

    /**
     * 近60天内该身份证号在本产品的结清日期与应还款日期天数差值之和
     */
    public function checkSumOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast60DaysLarge(){
        $data = $this->getProductOrderData(60, 23);
        if(empty($data)){
            return -9999;
        }
        return array_sum($data);
    }

    /**
     * 历史累计该身份证号在本产品的结清日期与应还款日期天数差值之和
     */
    public function checkHistSumOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLarge(){
        $data = $this->getProductOrderData(0, 23);
        if(empty($data)){
            return -9999;
        }
        return array_sum($data);
    }

    /**
     * 近7天内该身份证号在本产品的结清日期与应还款日期天数差值的最大值
     */
    public function checkMaxOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast7DaysLarge(){
        $data = $this->getProductOrderData(7, 23);
        if(empty($data)){
            return -9999;
        }

        return max($data);
    }

    /**
     * 近30天内该身份证号在本产品的结清日期与应还款日期天数差值的最大值
     */
    public function checkMaxOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast30DaysLarge(){
        $data = $this->getProductOrderData(30, 23);
        if(empty($data)){
            return -9999;
        }

        return max($data);
    }

    /**
     * 近60天内该身份证号在本产品的结清日期与应还款日期天数差值的最大值
     */
    public function checkMaxOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast60DaysLarge(){
        $data = $this->getProductOrderData(60, 23);
        if(empty($data)){
            return -9999;
        }

        return max($data);
    }

    /**
     * 历史累计该身份证号在本产品的结清日期与应还款日期天数差值的最大值
     */
    public function checkHistMaxOfDayDiffBtwPlanRepayDateAndClosedDateByIDCardLarge(){
        $data = $this->getProductOrderData(0, 23);
        if(empty($data)){
            return -9999;
        }

        return max($data);
    }

    /**
     * 近7天内该身份证号在本产品未逾期结清订单与应还款日期天数差值之和
     */
    public function checkSumOfNonOverdueClosedOrderDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast7DaysLarge(){
        if(empty($this->getProductOrderData(7, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(7, 24);
        return array_sum($data);
    }

    /**
     * 近30天内该身份证号在本产品未逾期结清订单与应还款日期天数差值之和
     */
    public function checkSumOfNonOverdueClosedOrderDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast30DaysLarge(){
        if(empty($this->getProductOrderData(30, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(30, 24);
        return array_sum($data);
    }

    /**
     * 近60天内该身份证号在本产品未逾期结清订单与应还款日期天数差值之和
     */
    public function checkSumOfNonOverdueClosedOrderDayDiffBtwPlanRepayDateAndClosedDateByIDCardLast60DaysLarge(){
        if(empty($this->getProductOrderData(60, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(60, 24);
        return array_sum($data);
    }

    /**
     * 历史累计该身份证号在本产品未逾期结清订单与应还款日期天数差值之和
     */
    public function checkHistSumOfNonOverdueClosedOrderDayDiffBtwPlanRepayDateAndClosedDateByIDCardLarge(){
        if(empty($this->getProductOrderData(0, 25))){
            return -9999;
        }
        $data = $this->getProductOrderData(0, 24);
        return array_sum($data);
    }

    /**
     * 近7天内该身份证号在本产品的逾期订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdueOrderCntToMaturedOrderCntByIDCardLast7DaysLarge(){
        $data = $this->getProductOrderData(7);
        if(empty($data)){
            return -9999;
        }

        if(empty($this->getProductOrderData(7, 25))){
            return -9999;
        }

        $count = $this->getProductOrderData(7, 22);
        return round(count($count) / count($data) * 100, 2);
    }

    /**
     * 近30天内该身份证号在本产品的逾期订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdueOrderCntToMaturedOrderCntByIDCardLast30DaysLarge(){
        $data = $this->getProductOrderData(30);
        if(empty($data)){
            return -9999;
        }

        if(empty($this->getProductOrderData(30, 25))){
            return -9999;
        }

        $count = $this->getProductOrderData(30, 22);
        return round(count($count) / count($data) * 100, 2);
    }

    /**
     * 近60天内该身份证号在本产品的逾期订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdueOrderCntToMaturedOrderCntByIDCardLast60DaysLarge(){
        $data = $this->getProductOrderData(60);
        if(empty($data)){
            return -9999;
        }

        if(empty($this->getProductOrderData(60, 25))){
            return -9999;
        }

        $count = $this->getProductOrderData(60, 22);
        return round(count($count) / count($data) * 100, 2);
    }

    /**
     * 近7天内该身份证号在本产品的逾期4天及以上订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToMaturedOrderCntByIDCardLast7DaysLarge(){
        $data = $this->getProductOrderData(7);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast7DaysLarge();
        if($count == -9999){
            return -9999;
        }
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近30天内该身份证号在本产品的逾期4天及以上订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToMaturedOrderCntByIDCardLast30DaysLarge(){
        $data = $this->getProductOrderData(30);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast30DaysLarge();
        if($count == -9999){
            return -9999;
        }
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近60天内该身份证号在本产品的逾期4天及以上订单数占已到期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToMaturedOrderCntByIDCardLast60DaysLarge(){
        $data = $this->getProductOrderData(60);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast60DaysLarge();
        if($count == -9999){
            return -9999;
        }
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近7天内该身份证号在本产品的逾期4天及以上订单数占逾期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToOverdueOrderCntByIDCardLast7DaysLarge(){
        $data = $this->getProductOrderData(7, 22);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast7DaysLarge();
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近30天内该身份证号在本产品的逾期4天及以上订单数占逾期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToOverdueOrderCntByIDCardLast30DaysLarge(){
        $data = $this->getProductOrderData(30, 22);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast30DaysLarge();
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近60天内该身份证号在本产品的逾期4天及以上订单数占逾期订单数的比例
     */
    public function checkRatioOfOverdue4DaysUpOrderCntToOverdueOrderCntByIDCardLast60DaysLarge(){
        $data = $this->getProductOrderData(60, 22);
        if(empty($data)){
            return -9999;
        }

        $count = $this->checkOverdue4DaysUpOrderCntByIDCardLast60DaysLarge();
        return round($count / count($data) * 100, 2);
    }

    /**
     * 近7天内该数盟设备ID在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByDeviceIDLast7DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(7, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(7, 2);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByDeviceIDLast30DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(30, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(30, 2);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByDeviceIDLast60DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(60, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(60, 2);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该数盟设备ID在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByDeviceIDLast7DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(7, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(7, 2);
        return array_sum($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByDeviceIDLast30DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(30, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(30, 2);
        return array_sum($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByDeviceIDLast60DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(60, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(60, 2);
        return array_sum($data);
    }

    /**
     * 近7天内该数盟设备ID在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByDeviceIDLast7DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(7, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(7, 2);
        return count($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByDeviceIDLast30DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(30, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(30, 2);
        return count($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByDeviceIDLast60DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(60, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(60, 2);
        return count($data);
    }

    /**
     * 近7天内该数盟设备ID在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByDeviceIDLast7DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(7, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(7, 3);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByDeviceIDLast30DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(30, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(30, 3);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByDeviceIDLast60DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(60, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(60, 3);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该数盟设备ID在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByDeviceIDLast7DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(7, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(7, 3);
        return array_sum($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByDeviceIDLast30DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(30, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(30, 3);
        return array_sum($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByDeviceIDLast60DaysLargeTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(60, 5))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(60, 3);
        return array_sum($data);
    }

    /**
     * 近7天内该手机号码在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByPhoneNumberLast7DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(7, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(7, 2);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该手机号码在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByPhoneNumberLast30DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(30, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(30, 2);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该手机号码在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByPhoneNumberLast60DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(60, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(60, 2);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该手机号码在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByPhoneNumberLast7DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(7, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(7, 2);
        return array_sum($data);
    }

    /**
     * 近30天内该手机号码在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByPhoneNumberLast30DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(30, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(30, 2);
        return array_sum($data);
    }

    /**
     * 近60天内该手机号码在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByPhoneNumberLast60DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(60, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(60, 2);
        return array_sum($data);
    }

    /**
     * 近7天内该手机号码在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByPhoneNumberLast7DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(7, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(7, 2);
        return count($data);
    }

    /**
     * 近30天内该手机号码在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByPhoneNumberLast30DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(30, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(30, 2);
        return count($data);
    }

    /**
     * 近60天内该手机号码在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByPhoneNumberLast60DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(60, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(60, 2);
        return count($data);
    }

    /**
     * 近7天内该手机号码在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByPhoneNumberLast7DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(7, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(7, 3);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该手机号码在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByPhoneNumberLast30DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(30, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(30, 3);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该手机号码在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByPhoneNumberLast60DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(60, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(60, 3);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该手机号码在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByPhoneNumberLast7DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(7, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(7, 3);
        return array_sum($data);
    }

    /**
     * 近30天内该手机号码在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByPhoneNumberLast30DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(30, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(30, 3);
        return array_sum($data);
    }

    /**
     * 近60天内该手机号码在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByPhoneNumberLast60DaysLargeTPF(){
        if(empty($this->getPhoneOrderData(60, 5))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(60, 3);
        return array_sum($data);
    }

    /**
     * 通讯录中距下单时间近1天内更新的号码数量
     * @return int
     */
    public function checkContact1DayUpdatePhoneCnt(){
        return $this->getContactByUserId($this->data->infoUser->phone, 1);
    }

    /**
     * 通讯录中距下单时间近7天内更新的号码数量
     * @return int
     */
    public function checkContact7DayUpdatePhoneCnt(){
        return $this->getContactByUserId($this->data->infoUser->phone, 7);
    }

    /**
     * 通讯录中距下单时间近30天内更新的号码数量
     * @return int
     */
    public function checkContact30DayUpdatePhoneCnt(){
        return $this->getContactByUserId($this->data->infoUser->phone, 30);
    }

    /**
     * 通讯录中距下单时间近1天内更新的号码数量占比
     * @return false|float|int
     */
    public function checkContact1DayUpdatePhoneCntPro(){
        $data = $this->checkAddressBookContactCnt();
        if(empty($data)){
            return -999;
        }

        $count = $this->getContactByUserId($this->data->infoUser->phone, 1);
        return round($count / $data * 100);
    }

    /**
     * 通讯录中距下单时间近7天内更新的号码数量占比
     * @return false|float|int
     */
    public function checkContact7DayUpdatePhoneCntPro(){
        $data = $this->checkAddressBookContactCnt();
        if(empty($data)){
            return -999;
        }

        $count = $this->getContactByUserId($this->data->infoUser->phone, 7);
        return round($count / $data * 100);
    }

    /**
     * 通讯录中距下单时间近30天内更新的号码数量占比
     * @return false|float|int
     */
    public function checkContact30DayUpdatePhoneCntPro(){
        $data = $this->checkAddressBookContactCnt();
        if(empty($data)){
            return -999;
        }

        $count = $this->getContactByUserId($this->data->infoUser->phone, 30);
        return round($count / $data * 100);
    }

    /**
     * 通讯录中号码最早更新的时间距下单时间的天数
     */
    public function checkContactFirstUpdateTimeToNow(){
        $data = $this->getContactByUserId($this->data->infoUser->phone, 31);

        if(empty($data)){
            return -999;
        }
        return max($data);
    }

    /**
     * 通讯录中号码最晚更新的时间距下单时间的天数
     */
    public function checkContactLastUpdateTimeToNow(){
        $data = $this->getContactByUserId($this->data->infoUser->phone, 31);

        if(empty($data)){
            return -999;
        }
        return min($data);
    }

    /**
     * 通讯录中所有号码的更新时间距下单时间的平均天数
     */
    public function checkContactAvgUpdateTimeToNow(){
        $data = $this->getContactByUserId($this->data->infoUser->phone, 31);

        if(empty($data)){
            return -999;
        }
        return round(array_sum($data) / count($data));
    }

    /**
     * 紧急联系人A和B的号码更新时间距下单时间天数的最大值
     */
    public function checkMaxDayOfContactUpdateTimeToNow(){
        $data = $this->getContactByUserId($this->data->infoUser->phone, 32);

        if(empty($data)){
            return -999;
        }
        return max($data);
    }

    /**
     * 紧急联系人A和B的号码更新时间距下单时间天数的最小值
     */
    public function checkMinDayOfContactUpdateTimeToNow(){
        $data = $this->getContactByUserId($this->data->infoUser->phone, 32);

        if(empty($data)){
            return -999;
        }
        return min($data);
    }

    /**
     * 该数盟设备ID在全平台当前的逾期待还款订单数
     */
    public function checkCurrentOverduePendingToRepayOrderCntByDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoDevice::tableName().' as d', 'r.user_id=d.user_id and r.order_id=d.order_id and r.app_name=d.app_name')
            ->select(['r.is_overdue'])
            ->where([
                'd.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                'r.status' => InfoRepayment::STATUS_PENDING
            ])
            ->asArray()->all();
        if(empty($data)){
            return -9999;
        }

        $i = 0;
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                $i++;
            }
        }

        return $i;
    }

    /**
     * 该数盟设备ID在全平台当前的逾期待还款订单数占比
     */
    public function checkCurrentOverduePendingToRepayOrderCntProByDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoDevice::tableName().' as d', 'r.user_id=d.user_id and r.order_id=d.order_id and r.app_name=d.app_name')
            ->where([
                'd.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                'r.status' => InfoRepayment::STATUS_PENDING,
            ])
            ->count();

        if(empty($data)){
            return -9999;
        }

        $count = $this->checkCurrentOverduePendingToRepayOrderCntByDeviceIDTPF();

        return round($count / $data * 100, 2);
    }

    /**
     * 该数盟设备ID在全平台当前的待还款订单的最大逾期天数
     */
    public function checkMaxOverdueDayOfCurrentPendingToRepayOrderByDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoDevice::tableName().' as d', 'r.user_id=d.user_id and r.order_id=d.order_id and r.app_name=d.app_name')
            ->select(['r.overdue_day'])
            ->where([
                'd.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                'r.status' => InfoRepayment::STATUS_PENDING,
            ])
            ->orderBy(['r.overdue_day' => SORT_DESC])
            ->one();

        return $data['overdue_day'] ?? -9999;
    }

    /**
     * 该数盟设备ID在全平台当前的逾期待还款订单的应还金额之和
     */
    public function checkSumOfCurrentOverduePendingToRepayOrderAmtByDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoDevice::tableName().' as d', 'r.user_id=d.user_id and r.order_id=d.order_id and r.app_name=d.app_name')
            ->select(['r.total_money', 'is_overdue'])
            ->where([
                'd.szlm_query_id' => $this->data->infoDevice->szlm_query_id,
                'r.status' => InfoRepayment::STATUS_PENDING
            ])
            ->asArray()
            ->all();

        if(empty($data)){
            return -9999;
        }

        $totalMoney = 0;
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                $totalMoney += $v['total_money'];
            }
        }

        return $totalMoney;
    }

    /**
     * 近7天内该数盟设备ID在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByDeviceIDLast7DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(7, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(7);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByDeviceIDLast30DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(30, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(30);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByDeviceIDLast60DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(60, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(60);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 历史累计该数盟设备ID在全平台的最大逾期天数
     */
    public function checkHistMaxOverdueDayByDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(0, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData();
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该数盟设备ID在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByDeviceIDLast7DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(7, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(7);
        return array_sum($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByDeviceIDLast30DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(30, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(30);
        return array_sum($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByDeviceIDLast60DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(60, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(60);
        return array_sum($data);
    }

    /**
     * 历史累计该数盟设备ID在全平台的逾期天数之和
     */
    public function checkHistSumOfOverdueDayByDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(0, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData();
        return array_sum($data);
    }

    /**
     * 近7天内该数盟设备ID在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByDeviceIDLast7DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(7, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(7);
        return count($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByDeviceIDLast30DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(30, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(30);
        return count($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByDeviceIDLast60DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(60, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(60);
        return count($data);
    }

    /**
     * 历史累计该数盟设备ID在全平台的逾期订单数
     */
    public function checkHistOverdueOrderCntByDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(0, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData();
        return count($data);
    }

    /**
     * 近7天内该数盟设备ID在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByDeviceIDLast7DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(7, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(7, 1);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByDeviceIDLast30DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(30, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(30, 1);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByDeviceIDLast60DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(60, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(60, 1);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 历史累计该数盟设备ID在全平台逾期订单金额的最大值
     */
    public function checkHistMaxOverdueOrderAmtByDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(0, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(0, 1);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该数盟设备ID在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByDeviceIDLast7DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(7, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(7, 1);
        return array_sum($data);
    }

    /**
     * 近30天内该数盟设备ID在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByDeviceIDLast30DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(30, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(30, 1);
        return array_sum($data);
    }

    /**
     * 近60天内该数盟设备ID在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByDeviceIDLast60DaysTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }

        if(empty($this->getSzlmOrderData(60, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(60, 1);
        return array_sum($data);
    }

    /**
     * 历史累计该数盟设备ID在全平台逾期订单金额之和
     */
    public function checkHistSumOfOverdueOrderAmtByDeviceIDTPF(){
        if(empty($this->data->infoDevice->szlm_query_id)){
            return -999;
        }


        if(empty($this->getSzlmOrderData(0, 4))){
            return -9999;
        }

        $data = $this->getSzlmOrderData(0, 1);
        return array_sum($data);
    }

    /**
     * 该手机号码在全平台当前的逾期待还款订单数
     */
    public function checkCurrentOverduePendingToRepayOrderCntByPhoneNumberTPF(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->select(['r.is_overdue'])
            ->where([
                'u.phone' => $this->data->infoUser->phone,
                'r.status' => InfoRepayment::STATUS_PENDING
            ])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }

        $i = 0;
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                $i++;
            }
        }

        return $i;
    }

    /**
     * 该手机号码在全平台当前的逾期待还款订单数占比
     */
    public function checkCurrentOverduePendingToRepayOrderCntProByPhoneNumberTPF(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->where([
                'u.phone' => $this->data->infoUser->phone,
                'r.status' => InfoRepayment::STATUS_PENDING,
            ])
            ->count();

        if(empty($data)){
            return -9999;
        }

        $count = $this->checkCurrentOverduePendingToRepayOrderCntByPhoneNumberTPF();

        return round($count / $data * 100, 2);
    }

    /**
     * 该手机号码在全平台当前的待还款订单的最大逾期天数
     */
    public function checkMaxOverdueDayOfCurrentPendingToRepayOrderByPhoneNumberTPF(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->select(['r.overdue_day'])
            ->where([
                'u.phone' => $this->data->infoUser->phone,
                'r.status' => InfoRepayment::STATUS_PENDING,
            ])
            ->orderBy(['r.overdue_day' => SORT_DESC])
            ->one();

        return $data['overdue_day'] ?? -9999;
    }

    /**
     * 该手机号码在全平台当前的逾期待还款订单的应还金额之和
     */
    public function checkSumOfCurrentOverduePendingToRepayOrderAmtByPhoneNumberTPF(){
        $data = InfoRepayment::find()
            ->alias('r')
            ->leftJoin(InfoUser::tableName().' as u', 'r.user_id=u.user_id and r.order_id=u.order_id and r.app_name=u.app_name')
            ->select(['r.total_money', 'r.is_overdue'])
            ->where([
                'u.phone' => $this->data->infoUser->phone,
                'r.status' => InfoRepayment::STATUS_PENDING
            ])
            ->asArray()
            ->all();
        if(empty($data)){
            return -9999;
        }

        $totalMoney = 0;
        foreach ($data as $v){
            if($v['is_overdue'] == InfoRepayment::OVERDUE_YES){
                $totalMoney += $v['total_money'];
            }
        }

        return $totalMoney;
    }

    /**
     * 近7天内该手机号码在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByPhoneNumberLast7DaysTPF(){
        if(empty($this->getPhoneOrderData(7, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(7);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该手机号码在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByPhoneNumberLast30DaysTPF(){
        if(empty($this->getPhoneOrderData(30, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(30);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该手机号码在全平台的最大逾期天数
     */
    public function checkMaxOverdueDayByPhoneNumberLast60DaysTPF(){
        if(empty($this->getPhoneOrderData(60, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(60);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 历史累计该手机号码在全平台的最大逾期天数
     */
    public function checkHistMaxOverdueDayByPhoneNumberTPF(){
        if(empty($this->getPhoneOrderData(0, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData();
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该手机号码在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByPhoneNumberLast7DaysTPF(){
        if(empty($this->getPhoneOrderData(7, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(7);
        return array_sum($data);
    }

    /**
     * 近30天内该手机号码在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByPhoneNumberLast30DaysTPF(){
        if(empty($this->getPhoneOrderData(30, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(30);
        return array_sum($data);
    }

    /**
     * 近60天内该手机号码在全平台的逾期天数之和
     */
    public function checkSumOfOverdueDayByPhoneNumberLast60DaysTPF(){
        if(empty($this->getPhoneOrderData(60, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(60);
        return array_sum($data);
    }

    /**
     * 历史累计该手机号码在全平台的逾期天数之和
     */
    public function checkHistSumOfOverdueDayByPhoneNumberTPF(){
        if(empty($this->getPhoneOrderData(0, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData();
        return array_sum($data);
    }

    /**
     * 近7天内该手机号码在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByPhoneNumberLast7DaysTPF(){
        if(empty($this->getPhoneOrderData(7, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(7);
        return count($data);
    }

    /**
     * 近30天内该手机号码在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByPhoneNumberLast30DaysTPF(){
        if(empty($this->getPhoneOrderData(30, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(30);
        return count($data);
    }

    /**
     * 近60天内该手机号码在全平台的逾期订单数
     */
    public function checkOverdueOrderCntByPhoneNumberLast60DaysTPF(){
        if(empty($this->getPhoneOrderData(60, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(60);
        return count($data);
    }

    /**
     * 历史累计该手机号码在全平台的逾期订单数
     */
    public function checkHistOverdueOrderCntByPhoneNumberTPF(){
        if(empty($this->getPhoneOrderData(0, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData();
        return count($data);
    }

    /**
     * 近7天内该手机号码在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByPhoneNumberLast7DaysTPF(){
        if(empty($this->getPhoneOrderData(7, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(7, 1);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近30天内该手机号码在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByPhoneNumberLast30DaysTPF(){
        if(empty($this->getPhoneOrderData(30, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(30, 1);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近60天内该手机号码在全平台逾期订单金额的最大值
     */
    public function checkMaxOverdueOrderAmtByPhoneNumberLast60DaysTPF(){
        if(empty($this->getPhoneOrderData(60, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(60, 1);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 历史累计该手机号码在全平台逾期订单金额的最大值
     */
    public function checkHistMaxOverdueOrderAmtByPhoneNumberTPF(){
        if(empty($this->getPhoneOrderData(0, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(0, 1);
        if(empty($data)){
            return 0;
        }

        return max($data);
    }

    /**
     * 近7天内该手机号码在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByPhoneNumberLast7DaysTPF(){
        if(empty($this->getPhoneOrderData(7, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(7, 1);
        return array_sum($data);
    }

    /**
     * 近30天内该手机号码在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByPhoneNumberLast30DaysTPF(){
        if(empty($this->getPhoneOrderData(30, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(30, 1);
        return array_sum($data);
    }

    /**
     * 近60天内该手机号码在全平台逾期订单金额之和
     */
    public function checkSumOfOverdueOrderAmtByPhoneNumberLast60DaysTPF(){
        if(empty($this->getPhoneOrderData(60, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(60, 1);
        return array_sum($data);
    }

    /**
     * 历史累计该手机号码在全平台逾期订单金额之和
     */
    public function checkHistSumOfOverdueOrderAmtByPhoneNumberTPF(){
        if(empty($this->getPhoneOrderData(0, 4))){
            return -9999;
        }
        $data = $this->getPhoneOrderData(0, 1);
        return array_sum($data);
    }




}