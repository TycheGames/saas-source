<?php
namespace common\services\user;

use common\models\coupon\UserCouponInfo;
use common\models\coupon\UserRedPacketsSlow;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;
use common\services\BaseService;
use Yii;
use yii\base\Exception;
use yii\base\UserException;



/**
 * 用户优惠券模块service
 */
class UserCouponService extends BaseService
{
    const LOAN_ORDER_CHECK_ERROR   = -1;
    const LOAN_ORDER_CHECK_SUCCESS = 0;
    const LOAN_ORDER_CHECK_AMOUNT  = 1;
    const LOAN_ORDER_CHECK_TERM    = 2;
    const LOAN_ORDER_CHECK_CHANNEL = 3;


    /**
     * 活动发券接口
     * @param $tmpl_id
     * @param $user_id
     * @param array $scene_info
     * @return bool
     */
    public static function sendCouponByAct($tmpl_id, $user_id, $merchantId){

        if(empty($tmpl_id) || empty($user_id) || empty($merchantId)){
            \yii::error( sprintf('sendCouponByAct params is empty: tmpl_id : %s,uid : %s', $tmpl_id, $user_id), 'coupon');
            return false;
        }

        //查询模板id是否存在
        $coupon_tmpl = UserRedPacketsSlow::find()
            ->where(['id'=> $tmpl_id, 'merchant_id' => $merchantId])
            ->andWhere(['status'=> UserRedPacketsSlow::STATUS_SUCCESS])
            ->one();
        if (empty($coupon_tmpl)) {
            \yii::error( sprintf('sendCouponByAct UserRedPacketsSlow is empty coupon_tpl: %s', $tmpl_id), 'coupon');
            return false;
        }
        $coupon_tmpl_arr = $coupon_tmpl->toArray();

        //查询用户信息
        $loan_person = LoanPerson::findOne(['id'=>$user_id]);
        if(empty($loan_person) || empty($loan_person->phone)){
            \yii::error( sprintf('_sendCutRate loan_person is empty: user_id : %s', $user_id), 'coupon');
            return false;
        }

        $phone = $loan_person->phone;

        return  self::_sendCoupon($user_id, $phone, $coupon_tmpl_arr);
    }

    /**
     * 优惠券发送
     * @param $user_id
     * @param $phone
     * @param $coupon_tmpl_arr
     * @return bool
     */
    private static function _sendCoupon($user_id, $phone, $coupon_tmpl_arr){
        $tmpl_id       = $coupon_tmpl_arr['id'];
        $code_pre      = $coupon_tmpl_arr['code_pre'] ?? 'yhq';
        $coupon_title  = $coupon_tmpl_arr['title'] ?? '优惠券';
        $coupon_amount = $coupon_tmpl_arr['amount'] ?? 0;
        $use_type      = $coupon_tmpl_arr['use_type'] ?? 0;
        $remark        = $coupon_tmpl_arr['remark'] ?? '';
        if($use_type){
            if($coupon_tmpl_arr['use_end_time'] < time()){
                \yii::error( sprintf('_sendCoupon UserRedPacketsSlow is expired coupon_tpl: %s', $tmpl_id), 'coupon');
                return false;
            }
            $start_time = $coupon_tmpl_arr['use_start_time'];
            $end_time   = $coupon_tmpl_arr['use_end_time'];
        }else{
            $start_time = strtotime("today");
            $valid_day  = $coupon_tmpl_arr['user_use_days'] ?? 15;
            $end_time   = $start_time + $valid_day * 86400 - 1;
        }
        $use_case = self::_getUseCaseByTmpl($coupon_tmpl_arr);

        $userCouponInfo = new UserCouponInfo();
        $userCouponInfo->user_id     = $user_id;
        $userCouponInfo->merchant_id = $coupon_tmpl_arr['merchant_id'];
        $userCouponInfo->phone       = $phone;
        $userCouponInfo->coupon_id   = $tmpl_id;
        $userCouponInfo->use_case    = $use_case;
        $userCouponInfo->use_type    = $use_type;
        $userCouponInfo->coupon_code = UserCouponInfo::makePacketCode($code_pre);
        $userCouponInfo->title       = $coupon_title;
        $userCouponInfo->is_use      = UserCouponInfo::STATUS_FALSE;
        $userCouponInfo->user_admin  = "auto_shell";
        $userCouponInfo->start_time  = $start_time;
        $userCouponInfo->end_time    = $end_time;
        $userCouponInfo->remark      = $remark;
        if(!empty($coupon_amount)){
            $userCouponInfo->amount = $coupon_amount;
        }

        if ($userCouponInfo->save()) {
            /* @var UserLoanOrderRepayment $userLoanOrderRepayment */
            $userLoanOrderRepayment = UserLoanOrderRepayment::find()->where(['user_id' => $user_id, 'merchant_id' => $coupon_tmpl_arr['merchant_id']])->andWhere(['!=', 'status', UserLoanOrderRepayment::STATUS_REPAY_COMPLETE])->one();
            if(!empty($userLoanOrderRepayment)){
                $userLoanOrderRepayment->coupon_money = $coupon_amount;
                $userLoanOrderRepayment->coupon_id = $userCouponInfo->id;
                if($userLoanOrderRepayment->save()){
                    return true;
                }
                \yii::error( sprintf('userLoanOrderRepayment save error: %s %s %s', $user_id, $phone, json_encode($coupon_tmpl_arr)), 'coupon');
                return false;
            }
            return true;
        } else {
            \yii::error( sprintf('_sendCoupon save error: %s %s %s', $user_id, $phone, json_encode($coupon_tmpl_arr)), 'coupon');
            return false;
        }
    }


    /**
     * 根据优惠券模板获取优惠券类型（场景coupon_info.use_case）
     * @param $coupon_tmpl_arr
     */
    private static function _getUseCaseByTmpl($coupon_tmpl_arr){
        $tmpl_use_case = $coupon_tmpl_arr['use_case'];
        switch ($tmpl_use_case){
            case  UserRedPacketsSlow::USE_CASE_FREE: //还款抵扣券
                $coupon_use_case = UserCouponInfo::COUPON_CASE_FREE;
                break;
            default://还款抵扣券
                $coupon_use_case = UserCouponInfo::COUPON_CASE_FREE;
                break;
        }
        return $coupon_use_case;
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function getUserCouponList($user_id){
        $list = UserCouponInfo::getUserCouponList($user_id);
        $data = [];
        foreach ($list as $k => $v){
            $data[$k]['id'] = $v['id'];
            $data[$k]['money'] = $v['amount'];
            $data[$k]['couponName'] = $v['title'];
            $data[$k]['validityPeriod'] = date('Y-m-d', $v['start_time']). ' to ' . date('Y-m-d',$v['end_time']);
        }
        $this->setResult($data);
        return true;
    }
}
