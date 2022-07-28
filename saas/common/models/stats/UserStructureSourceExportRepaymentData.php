<?php

namespace common\models\stats;
use yii;
use yii\db\ActiveRecord;

/**
 * Class UserStructureRepaymentData
 * @package common\models\stats
 * @property string $date
 * @property int $merchant_id
 * @property string $package_name
 * @property int $user_type
 * @property int $expire_num
 * @property int $expire_money
 * @property int $first_over_num
 * @property int $first_over_money
 */
class UserStructureSourceExportRepaymentData extends ActiveRecord
{

    public static function tableName(){

        return '{{%user_structure_source_export_repayment_data}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_stats');
    }
}