<?php


namespace frontend\models\user;


use frontend\models\BaseForm;

/**
 * Class UserPanFrom
 * @package frontend\models\user
 *
 * @property array $list
 * @property int $paperId
 * @property int $inPageTime
 * @property int $outPageTime
 * @property string $params 客户端信息
 */
class UserQuestionForm extends BaseForm
{
    public $inPageTime, $outPageTime;
    public $list, $paperId;
    public $params;

    function maps(): array
    {
        return [];
    }

    public function attributeLabels()
    {
        return [
            'list'        => 'list',
            'paperId'     => 'paperId',
            'inPageTime'  => 'inPageTime',
            'outPageTime' => 'outPageTime',
            'params'      => 'params',
        ];
    }

    public function rules()
    {
        return [
            [['list', 'paperId', 'inPageTime', 'outPageTime', 'params'], 'required'],
            [['inPageTime', 'outPageTime', 'paperId'], 'integer'],
        ];
    }
}