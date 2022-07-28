<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/4
 * Time: 14:46
 */
namespace common\models\manual_credit;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class ManualCreditModule
 * @property $id
 * @property $head_code
 * @property $head_name
 * @property $status
 * @property $created_at
 * @property $updated_at
 */

class ManualCreditModule extends ActiveRecord
{
    const STATUS_NO = 1;
    const STATUS_OFF = 0;

    public static $status_list = [
        self::STATUS_OFF => 'close',
        self::STATUS_NO => 'open'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%manual_credit_module}}';
    }


    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['head_code','head_name','status'], 'required'],
            ['status', 'in', 'range'=> array_keys(self::$status_list),'message'=> 'status error']
        ];
    }
}