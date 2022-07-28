<?php

namespace callcenter\models;

use Yii;
use yii\base\Event;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * AdminManagerRelation model
 *
 * @property int $id
 * @property int $admin_id
 * @property int $outside
 * @property int $group
 * @property int $group_game
 * @property int $created_at
 * @property int $updated_at
 */
class AdminManagerRelation extends ActiveRecord
{
    public $labelArr = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_manager_relation}}';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_assist');
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
    public function rules()
    {
        return [
            [['admin_id', 'group', 'group_game'], 'required'],
            ['group_game','validateGroupUnique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => 'admin_id',
            'group' => 'group',
            'group_game' => 'team',
            'created_at' => 'Created time',
            'updated_at' => 'Updated time',
        ];
    }

    function validateGroupUnique(){
        if(is_array($this->group_game)){
            if(!empty($this->outside)){
                $relationArr = [];
                foreach ($this->group_game as $key => $group_game){
                    $this->labelArr[$key] = ['outside' => $this->outside[$key], 'group' => $this->group[$key], 'group_game' => $group_game];
                    if(isset($relationArr[$this->outside[$key]][$this->group[$key]][$group_game])){
                        $this->addError('group_game'.$key,'group and team need unique');
                    }else{
                        $relationArr[$this->outside[$key]][$this->group[$key]][$group_game] = 1;
                    }
                }
            }
        }
    }
}