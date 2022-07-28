<?php

namespace common\models\fund;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * 资方操作日志
 * This is the model class for table "{{%loan_fund_operate_log}}".
 *
 * @property string $id
 * @property integer $fund_id 资方ID
 * @property integer $admin_id 操作人ID
 * @property string $admin_name 操作人用户名
 * @property string $params 参数
 * @property integer $status 状态
 * @property string $action 执行动作 [add, update, delete]
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class LoanFundOperateLog extends ActiveRecord
{
    public static $arrParams = null;


    const ACTION_ADD    = 'add';
    const ACTION_UPDATE = 'update';

    const STATUS_FUND_QUOTA = 0;
    const STATUS_FUND_DAY_QUOTA = 1;
    const STATUS_FUND_TOTAL_DAY_QUOTA = 2;


    public static $arrAction = [
        self::ACTION_ADD    => '新增',
        self::ACTION_UPDATE => '更新',
    ];


    public function rules()
    {
        return [
            [['fund_id', 'admin_id', 'admin_name'], 'required'],
            [['params', 'status', 'action'], 'safe']
        ];
    }// END rules


    /**
     * 表名
     * @return string
     */
    public static function tableName()
    {
        return '{{%loan_fund_operate_log}}';

    }// END tableName


    public function behaviors()
    {
        return [
            TimestampBehavior::class
        ];
    }// END behaviors


    /**
     * 新增资方操作日志
     * @param $nFundId
     * @param $arrParams
     * @param $nStatus
     * @param string $sAction
     */
    public static function addOperateLog($nFundId, $arrParams, $nStatus, $sAction = 'add')
    {
        $oFundOperateLog = new self();
        $oFundOperateLog->fund_id = $nFundId;
        $oFundOperateLog->admin_id = \Yii::$app->user->id;
        $oFundOperateLog->admin_name = \Yii::$app->user->identity->username;
        $oFundOperateLog->params = json_encode($arrParams);
        $oFundOperateLog->status = $nStatus;
        $oFundOperateLog->action = $sAction;
        $oFundOperateLog->save();

    }// END addOperateLog


    /**
     * 解析参数返回
     * @param $sParams
     * @param $id
     * @return mixed|null
     */
    public static function getParams($sParams, $id)
    {
        if (!empty(self::$arrParams))
        {
            if (self::$arrParams['id'] == $id) {
                return self::$arrParams;
            } else {
                $arrParams = json_decode($sParams, true);
                $arrParams['id'] = $id;

                self::$arrParams = $arrParams;
                return $arrParams;
            }
        }
        else
        {
            $arrParams = json_decode($sParams, true);
            $arrParams['id'] = $id;

            self::$arrParams = $arrParams;
            return $arrParams;
        }

    }// END getParams

}// END CLASS