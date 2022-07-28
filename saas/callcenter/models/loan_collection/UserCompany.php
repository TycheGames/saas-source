<?php
namespace callcenter\models\loan_collection;

use callcenter\models\AdminUser;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserCompany
 * @package callcenter\models\loan_collection
 *
 * @property int $id
 * @property string $title
 * @property string $real_title
 * @property int $system 是否是自营团队，1是，0不是
 * @property int $merchant_id
 * @property int $status 状态，1启用，0删除
 * @property int $auto_dispatch
 * @property int $created_at
 * @property int $updated_at
 */
class UserCompany extends ActiveRecord 
{

    const USING = 1;//启用状态
    const DELETED = 0;//删除状态
    const IS_SELF_TEAM = 1;//是自营公司
    const IS_NOT_SELF_TEAM = 0;//不是自营公司

    const AUTO_DISPATCH = 1;//自动分派
    const NOT_AUTO_DISPATCH = 0;//不自动分派

    public function rules()
    {
        return [
            [['title','system', 'merchant_id', 'real_title', 'status'], 'required'],
            [['created_at','updated_at', 'id','auto_dispatch'], 'safe'],
            [['real_title'], 'unique'],
            [['title'], 'unique'],
        ];
    }


    /**
     * @return array
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
    public static function tableName()
    {
        return '{{%loan_collection_user_company}}';
    }
    public static function getDb()
    {
        return Yii::$app->get('db_assist');
    }
    public static function getDb_rd()
    {
        return Yii::$app->get('db_assist_read');
    }
    public static function rm($company_id, $merchantId){
       
        //(删除只是更改数据状态，不做真实数据库删除):
         $item = self::findOne(['id' => $company_id, 'merchant_id' => $merchantId]);
         $item->status = self::DELETED;
         $item->save();
         UserSchedule::clean_by_company($company_id); //删除公司信息的同时，删除相对应的分配规则
         return true;
    }

    /**
     * 返回催单公司信息列表
     * @param string $offset
     * @param string $limit
     * @param string $type
     * @return array
     */
    public static function lists($merchantId, $offset = '', $limit = '',$type =''){
        $query = self::find()->where([
                'status'=>self::USING,
                'merchant_id' => $merchantId
                ])->asArray();
        if(!empty($offset)) $query->offset($offset);
        if(!empty($limit)) $query->limit($limit);
        if($type){
            $lists = $query->orderBy('id desc')->all();
        }else{
            $lists = $query->all();
        }

        $res = array();
        if(!empty($lists)){
            foreach ($lists as $key => $item) {
                $res[$item['id']] = $item;
            }
        }
        return $res;
    }

    /**
     * @param string $company_id
     * @return array|mixed|string
     */
    public static function outsideRealName($merchantId, $company_id = ''){   //机构真实名称
        $lists = self::lists($merchantId);
        $list_arr = [];
        foreach ($lists as $key => $item) {
            $list_arr[$item['id']] = !empty($item['real_title'])  ?$item['real_title']:$item['title'];
        }
        return empty($company_id) ? $list_arr : (isset($list_arr[$company_id]) ? $list_arr[$company_id] : '--');
    }

    /**
     * @param string $company_id
     * @return array|mixed|string
     */
    public static function allOutsideRealName($merchantId, $company_id = ''){   //机构真实名称
        $lists = self::find()->where(['merchant_id' => $merchantId])->asArray()->all();
        $list_arr = [];
        foreach ($lists as $key => $item) {
            $list_arr[$item['id']] = !empty($item['real_title'])  ?$item['real_title']:$item['title'];
        }
        return empty($company_id) ? $list_arr : (isset($list_arr[$company_id]) ? $list_arr[$company_id] : '--');
    }

    /**
     *返回指定催单公司信息
     */
    public static function lists_id($ids){
        $query = self::find()->where(['status'=>self::USING,'id'=>$ids])->asArray();
        $lists = $query->all();
        $res = array();
        if(!empty($lists)){
            foreach ($lists as $key => $item) {
                $res[$item['id']] = $item;
            }
        }
        return $res;
    }
    public static function lists_not($ids){
        $condition = 'status=1 and id not in ('.implode(',',$ids).')';
        $query = self::find()->where($condition)->asArray();
        $lists = $query->all();
        $res = array();
        if(!empty($lists)){
            foreach ($lists as $key => $item) {
                $res[$item['id']] = $item;
            }
        }
        return $res;
    }
    /**
     *返回所有催单公司信息
     */
    public static function getAll($offset = '', $limit = ''){
        $query = self::find()->asArray();
        if(!empty($offset)) $query->offset($offset);
        if(!empty($limit)) $query->limit($limit);

        $lists = $query->all(Yii::$app->get('db_assist'));
        $res = array();
        if(!empty($lists)){
            foreach ($lists as $key => $item) {
                $res[$item['id']] = $item;
            }
        }
        return $res;
    }
    public static function getOutsideAll($offset = '', $limit = ''){
        $query = self::find()->asArray();
        if(!empty($offset)) $query->offset($offset);
        if(!empty($limit)) $query->limit($limit);

        $lists = $query->all();
        $res = array();
        if(!empty($lists)){
            foreach ($lists as $key => $item) {
                $res[$item['id']] = $item;
            }
        }
        return $res;
    }

    /**
     *根据公司ID，返回公司信息
     */
    public static function id($companyId){
        return self::find()->where(['id'=>$companyId])->one();
    }


    /**
     * y验证公司名唯一性
     */
    public static function unique_title($compamyTitle,$companyId=0,$type=0){
        if ($type==1) { //编辑 
            $condition = " id!={$companyId} and title='{$compamyTitle}' and status!=".self::DELETED;
            return self::find()->where($condition)->select('id')->scalar();
        }elseif($type == 2){
            $condition = " id!={$companyId} and real_title='{$compamyTitle}' and status!=".self::DELETED;
            return self::find()->where($condition)->select('id')->scalar();
        }elseif($compamyTitle =='title'){      //添加
            $condition = " title='{$compamyTitle}' and status!=".self::DELETED;
            return self::find()->where($condition)->one();
        }elseif($compamyTitle =='real_title')
        {
            $condition = " real_title='{$compamyTitle}' and status!=".self::DELETED;
            return self::find()->where($condition)->one();
        }
    }

    public static function self_id(){
        return self::find()->where(['system'=>self::IS_SELF_TEAM])->select('id')->scalar();
    }
    /**
     *判断给定的机构ID，是否是委外机构
     *@param int $companyId 要判断的机构ID
     *@return boolean true:是委外机构
     */
    public static function is_outside($companyId = 0){
        $one = self::find()->where(['id'=>$companyId])->asArray()->one();
        if(empty($one)) return false;
        if($one['system'] == self::IS_SELF_TEAM)    return false;
        return true;
    }

    /* 通过管理员id返回对应公司是否支持网络电话
    * */
    public static function getIsVoip($type=0){
        return false;
        $admin_id = \Yii::$app->user->id;
        $admin_info  = AdminUser::findOne(['admin_user_id'=>$admin_id]);
        if($admin_info){
            $company = UserCompany::findOne(['id'=>$admin_info['outside']]);
            if($type){
                $sub_ids = explode(',',$company['sub_id']);
                $sub_tokens = explode(',',$company['sub_token']);
                $small_group = $admin_info['group_game']-1;
                $id = isset($sub_ids[$small_group])?$sub_ids[$small_group]:'';
                $token = isset($sub_tokens[$small_group])?$sub_tokens[$small_group]:'';
                if(empty($id) || empty($token)){
                    return [];
                }
                return ['sub_id'=>$id,'sub_token'=>$token];
            }
            if($company['is_voip']){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    /* 获取工号   工号即对应登录手机号
  * */
    public static function getWorkNumber(){
        $admin_id = \Yii::$app->user->id;
        $admin_info  = AdminUser::findOne(['id'=>$admin_id]);
        return $admin_info['phone'];

    }

    public static function autoDispatchListByMerchant($merchantIds){
        $query = self::find()
            ->where(['status' => self::USING,'auto_dispatch' => self::AUTO_DISPATCH])
            ->andWhere(['merchant_id' => $merchantIds])
            ->asArray();
        $lists = $query->all();
        $res = array();
        if(!empty($lists)){
            foreach ($lists as $key => $item) {
                $res[$item['id']] = $item;
            }
        }
        return $res;
    }
}
