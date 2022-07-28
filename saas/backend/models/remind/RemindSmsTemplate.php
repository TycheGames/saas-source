<?php
namespace backend\models\remind;

use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class RemindSmsTemplate
 * @package backend\models\remind
 * @property int $id
 * @property int $merchant_id
 * @property string $package_name
 * @property int $status
 * @property string $name
 * @property string $content
 * @property int $created_at
 * @property int $updated_at
 */
class RemindSmsTemplate extends ActiveRecord
{
    const STATUS_INVALID = 0;
    const STATUS_USABLE = 1;

    public static $status_map = [
        self::STATUS_INVALID => 'INVALID',
        self::STATUS_USABLE => 'USABLE'
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%remind_sms_template}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name','content','status','package_name'], 'required'],
            [['merchant_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'template name',
            'content' => 'template content',
            'status' => 'template status',
            'merchant_id' => 'merchant Id',
            'package_name' => 'package name'
        ];
    }


    public static function getTemplate(UserLoanOrderRepayment $repayment, $merchantIds){
        $downList = [0 => 'don\'t send'];
        $contentList = [];
        if($repayment->userLoanOrder->is_export == UserLoanOrder::IS_EXPORT_YES){
            $packageName = explode('_',$repayment->userLoanOrder->clientInfoLog->app_market)[1];
        }else{
            $packageName = $repayment->userLoanOrder->clientInfoLog->package_name;
        }

        $template = self::find()->where(['status' => RemindSmsTemplate::STATUS_USABLE,'merchant_id' => $merchantIds, 'package_name' => $packageName])->all();
        /** @var RemindSmsTemplate $item */
        foreach ($template as $item){
            $downList[$item->id] = $item->name;
            $send_message = str_replace(['#username#','#total_money#','#should_repay_date#','#remind_date#'],
                [$repayment->loanPerson->name,$repayment->getAmountInExpiryDate() / 100,date('d/m/Y',$repayment->plan_repayment_time),date('d/m/Y')],$item->content); // 处理文案内容信息替换
            $contentList[$item->id] = $send_message;
        }
        return ['downList' => $downList, 'contentList' => $contentList];
    }
}