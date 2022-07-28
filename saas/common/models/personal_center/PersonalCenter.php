<?php

namespace common\models\personal_center;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%personal_center}}".
 *
 * @property int $id
 * @property int $user_id
 * @property string $icon Icon地址
 * @property string $title 标题
 * @property string $path
 * @property int $is_finish_page 是否完成后跳转
 * @property string $jump_page 跳转路径
 * @property int $sorting 排序优先级
 * @property int $package_setting_id Packag name
 * @property int $is_google_review 谷歌商店审核 0:否 1:是
 * @property string $created_at
 * @property string $updated_at
 */
class PersonalCenter extends ActiveRecord
{
    const GOOGLE_REVIEW_OPEN = 1;
    const GOOGLE_REVIEW_CLOSE = 0;

    /**
     * 获取表名
     * @return string
     */
    public static function tableName()
    {
        return '{{%personal_center}}';

    }// END tableName


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            ['sorting', 'number'],
            ['path', 'default', 'value' => '/h5/webview'],
            ['sorting', 'default', 'value' => 0],
            ['updated_at', 'default', 'value' => date('Y-m-d H:i:s')],
            [['is_google_review'], 'integer'],
            [['icon', 'user_id', 'title', 'path', 'is_finish_page', 'jump_page', 'package_setting_id', 'created_at', 'updated_at'], 'safe']
        ];
    }


    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id'                 => 'ID',
            'icon'               => 'Icon',
            'title'              => 'Title',
            'path'               => 'Path',
            'is_finish_page'     => 'Is finish page',
            'jump_page'          => 'Jump page',
            'sorting'            => 'Sorting',
            'package_setting_id' => 'Package name',
            'is_google_review'   => 'Is Google Review',
            'created_at'         => 'Created at',
            'updated_at'         => 'Updated at',
        ];

    }// END attributeLabels

}// END CLASS