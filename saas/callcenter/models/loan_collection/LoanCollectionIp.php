<?php

namespace callcenter\models\loan_collection;

use callcenter\models\AdminUser;
use common\helpers\Util;
use Yii;
class LoanCollectionIp extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = -1;
    const STATUS_ACTIVE = 1;
    const STATUS_UNACTIVE = 0;

    const IP_XIANJINCARD = '116.231.35.34';//研发部IP
    const IP_MANUAL_OUTSIDE = 999;//手动添加的IP白名单，机构默认为999

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%loan_collection_ip}}';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }

    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist');
    }

    /**
     * 检查IP是否可用
     */
    public static function is_valid_ip($ip) {
        $func = Util::short(__CLASS__, __FUNCTION__);

        $item = self::find()->where([
            'ip' => $ip,
            'status' => self::STATUS_ACTIVE,
        ])->one();
        if (empty($item)) { //不属于IP白名单
            \yii::warning( "[{$func}] {$ip} 不属于IP白名单" );
            return false;
        }

        $uid = Yii::$app->user->id;
        $loan_collection = AdminUser::findIdentity($uid);
        if (!empty( $loan_collection )) { //是催收人，进一步判断机构IP与当前IP是否一致
            $outside_ip_record = self::find()->select('ip')->where([
                'outside' => $loan_collection['outside'],
                'status' => self::STATUS_ACTIVE,
            ])->asArray()->all();
            if (empty( $outside_ip_record )) { //所属机构无IP白名单
                \yii::warning( "[{$func}] {$loan_collection['outside']} 机构无IP白名单" );
                return FALSE;
            }

            $ip_list = array_column( $outside_ip_record, 'ip' );

            if (!in_array( $ip, $ip_list ) && $ip != self::IP_XIANJINCARD) { //用户当前IP不是所属机构备案的IP
                \yii::warning( "[{$func}] {$loan_collection['username']} 当前IP不是所属机构备案的IP" );
                return FALSE;
            }
        }
        return true;
    }

    /**
     *新增IP记录
     */
    public static function new_record($record = array('outside'=>-1, 'ip_list'=>array(), 'remark'=>'')){
        self::modify_outside($record['outside'], self::STATUS_DELETED);////先清除旧IP
        foreach ($record['ip_list'] as $key => $ip) {
            $item = new Self();

            $item->outside = $record['outside'];
            $item->ip = $ip;
            $item->remark = $record['remark'];
            if(!$item->save()){
                return false;
            }
        }
        return true;
    }

    /**
     *返回给定机构ID 的IP列表
     */
    public static function outside($id){
        return self::find()
            ->where(['outside' => $id])
            ->andWhere(['!=', 'status', 'self::STATUS_DELETED'])
            ->asArray()
            ->all();
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)){
            if($this->isNewRecord){
                if(empty($this->created_at)) $this->created_at = time();


            }
            $this->updated_at = time();
            if(empty($this->operator)) $this->operator = Yii::$app->user->id;

            return true;
        }else{
            return false;
        }
    }

    //列表（不包含已删除）
    public static function lists(){
        return self::find()->asArray()->where(" `status` !=".self::STATUS_DELETED)->orderBy(['updated_at'=>SORT_DESC])->all();
    }

    //删除：
    public static function del($id){
       return self::modify($id, self::STATUS_DELETED);
    }

    //启用
    public static function active($id){
        return self::modify($id, self::STATUS_ACTIVE);
    }

    //禁用
    public static function unactive($id){
        return self::modify($id, self::STATUS_UNACTIVE);
    }



    public static function modify($id, $status){
        $item = self::find()->where(['id'=>$id])->one();
        if(empty($item))    return false;
        $item->status = $status;
        return $item->save();
    }

    public static function modify_outside($outsideId, $status){
        $transaction= Yii::$app->db->beginTransaction();//创建事务
        $items = self::find()->where(['outside'=>$outsideId])->all();
        if(empty($items))    return false;
        foreach ($items as $key => $item) {
            $item->status = $status;
            $res = $item->save();
            if(!$res){
                $transaction->rollBack();
                return false;
            }
        }
        $transaction->commit();
        return true;
    }

    public static function modifyAll($ids, $status){
       foreach ($ids as $key => $id) {
           $res = self::modify($id, $status);
           if(!$res)    return false;
       }
       return true;
    }


}
