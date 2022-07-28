<?php

namespace common\models\tab_bar_icon;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%tab_bar_icon}}".
 *
 * @property int $id
 * @property string $title 菜单标题
 * @property string $type 类型
 * @property string $normal_img 未选中状态Icon
 * @property string $select_img 已选中状态Icon
 * @property string $normal_color 未选中状态Color
 * @property string $select_color 已选中状态Color
 * @property int $package_setting_id 包ID
 * @property int $is_google_review 谷歌商店审核 0:否 1:是
 * @property int $created_at
 * @property int $updated_at
 */
class TabBarIcon extends ActiveRecord
{
    const GOOGLE_REVIEW_OPEN = 1;
    const GOOGLE_REVIEW_CLOSE = 0;

    public static function tableName()
    {
        return '{{%tab_bar_icon}}';

    }// END tableName


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'normal_color', 'select_color'], 'required'],
            [['is_google_review', 'created_at', 'updated_at'], 'integer'],
            [['normal_img', 'select_img', 'type', 'package_setting_id'], 'safe'],
        ];

    }// END rules


    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'title'              => 'Title',
            'type'               => 'Type',
            'normal_img'         => 'Normal icon',
            'select_img'         => 'Select icon',
            'normal_color'       => 'Normal color',
            'select_color'       => 'Select color',
            'package_setting_id' => 'Package name',
            'is_google_review'   => 'Is Google Review',
            'created_at'         => 'Created at',
            'updated_at'         => 'Updated at',
        ];

    }// END attributeLabels


    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

}// END CLASS