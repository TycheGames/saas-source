<?php

namespace common\models\question;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%question_list}}".
 *
 * @property int $id
 * @property string $question_title 问题标题
 * @property string $question_content 问题内容
 * @property string $question_img 问题图片
 * @property string $question_option 问题选项
 * @property string $answer 问题答案
 * @property int $is_used 是否启用 0:停用 1：启用
 * @property int $created_at
 * @property int $updated_at
 */
class QuestionList extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%question_list}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_used', 'created_at', 'updated_at'], 'integer'],
            [['question_title', 'question_img', 'answer'], 'string', 'max' => 255],
            [['question_content', 'question_option'], 'string', 'max' => 1024],
            [['answer', 'question_content', 'question_option'], 'trim'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'               => 'ID',
            'question_title'   => 'Question Title',
            'question_content' => 'Question Content',
            'question_img'     => 'Question Img',
            'question_option'  => 'Question Option',
            'answer'           => 'Answer',
            'is_used'          => 'Is Used',
            'created_at'       => 'Created At',
            'updated_at'       => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }
}
