<?php

namespace callcenter\controllers;
use backend\models\Merchant;
use callcenter\models\AdminUser;
use callcenter\models\loan_collection\UserCompany;
use callcenter\models\loan_collection\UserSchedule;
use callcenter\models\loan_collection\LoanCollectionIp;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\Url;
use yii\web\Response;


class UserCompanyController extends  BaseController{

    /**
     *@name 新增公司信息
     */
    public function actionCompanyAdd(){
            $user_company = new UserCompany();

            if($this->request->isPost)
            {
                $user_company->load($this->request->post());
                if($this->isNotMerchantAdmin)
                {
                    $user_company->merchant_id = 0;
                }else{
                    $user_company->merchant_id = $this->merchantIds;
                }
                $user_company->status = UserCompany::USING;
                if($user_company->validate() && $user_company->save())
                {
                    //新增公司信息成功后，自动添加匹配规则记录：
                    UserSchedule::add_by_company($user_company);
                    return $this->redirectMessage('添加成功', self::MSG_SUCCESS, Url::toRoute(['user-company/company-lists']));
                }else{
                    return $this->redirectMessage('添加失败', self::MSG_ERROR);
                }

            }


        return $this->render('company-add', array(
            'user_company' => $user_company,
            'tip'=>0,
        ));
    }

    

    /**
     *@name 删除公司
     */
    public function actionDelCompany($company_id){
        if(AdminUser::HasPerson($company_id)){
            return json_encode(['code'=>1, 'msg'=>'该公司下有催收人员存在，不能删除']);
        }
        $res = UserCompany::rm($company_id, $this->merchantIds);
        return json_encode(['code'=>0, 'msg'=>'success']);
    }

     /**
     * @name 催收公司列表
     **/
    public function actionCompanyLists()
    {
        $oUserCompany = UserCompany::find();

        $nStatus    = UserCompany::USING;
        $sCondition[] = 'and';
        $sCondition[] = ['status' => $nStatus];

        // 搜索条件
        if ($this->request->get('search_submit')) {
            $arrSearch = $this->request->get();
            // 防止拼接参数来绕过判断
            if ($this->isNotMerchantAdmin) {
                if (isset($arrSearch['merchant_id']) && $arrSearch['merchant_id'] != '') {
                    $sCondition[] = ['merchant_id' => intval($arrSearch['merchant_id'])];
                }
            }
        } else {
            // 没有搜索条件，各看各的公司
            if (is_array($this->merchantIds)) {
                $sMerchantIds = $this->merchantIds;
            } else {
                $sMerchantIds = explode(',', $this->merchantIds);
            }
            $sCondition[] = ['merchant_id' => $sMerchantIds];
        }

        $oUserCompany     = $oUserCompany->where($sCondition)->asArray();
        $oUserCompanyCopy = clone $oUserCompany;
        $oPages = new Pagination(['totalCount' => $oUserCompanyCopy->count()]);
        $oPages->pageSize = 15;
        $company_lists = $oUserCompany->offset($oPages->offset)->limit($oPages->limit)->all();

        foreach ($company_lists as $key => $value)
        {
            $count = AdminUser::find()->where(['outside'=>$value['id'], 'merchant_id' => $value['merchant_id']])->count();
            $useCount = AdminUser::find()->where(['outside'=>$value['id'], 'merchant_id' => $value['merchant_id']])->andFilterWhere(['open_status'=> AdminUser::$usable_status])->count();
            $value['count'] = $count;
            $value['useCount'] = $useCount;
            $company_lists[$key] =$value;
        }

        // 获取所有商户，只能是商户管理员的时候才在页面上显示
        if ($this->isNotMerchantAdmin) {
            $arrMerchant = Merchant::getMerchantId();
        } else {
            $arrMerchant = [];
        }

        return $this->render('company-list', array(
            'user_collection' => $company_lists,
            'pages' => $oPages,
            'tip'=>1,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'arrMerchant' => $arrMerchant
        ));
    }

    /**
     *@name 更新分组每天最大接单量
     */
    public function actionScheduleUpdate($company_id, $group_id, $max_amount){
        $res = UserSchedule::schedule_update($company_id, $group_id, $max_amount);
        return json_encode(['code'=>0, 'msg'=>'success', 'max_amount'=>$max_amount]);
    }

    /**
     * @name UserCompanyController 更新机构是否可自动派单
     * @param $id
     * @param $status
     * @return array
     */
    public function actionUpdateAutoDispatch($id,$status){
        $this->response->format = Response::FORMAT_JSON;
        $model = UserCompany::find()->where(['id' => $id,'merchant_id' => $this->merchantIds])->one();
        /** @var UserCompany $model */
        if(!$model){
            return  [
                'code'=>-1,
                'message'=>'can\'t update',
            ];
        }
        if($status){
            $model->auto_dispatch = UserCompany::AUTO_DISPATCH;
        }else{
            $model->auto_dispatch = UserCompany::NOT_AUTO_DISPATCH;
        }
        if(!$model->save(false)){
            return  [
                'code'=>-1,
                'message'=>'save fail',
            ];
        }
        return  [
            'code'=>0,
            'message'=>'success',
        ];
    }

    /**
     * @name 编辑催收公司
     */
    public function actionCompanyEdit(){
        try{
            $user_company_id = $this->request->get('id');
            $user_company = UserCompany::id($user_company_id);
            $company_ip_list = LoanCollectionIp::outside($user_company_id);

            if($this->request->getIsPost()){
                $post = $this->request->post('UserCompany');
                $self_id = UserCompany::self_id();
                if($post['system'] && !empty($self_id) && $self_id != $post['id']){
                    throw new Exception("目前只支持一家自营团队");
                }
                if(UserCompany::unique_title($post['title'],$user_company_id,1)){
                    throw new Exception("机构代号已存在，不能重复");
                }
                if(UserCompany::unique_title($post['real_title'],$user_company_id,2)){
                    throw new Exception("机构名称已存在，不能重复");
                }
                $user_company->system = $post['system'];
                $user_company->title = $post['title'];
                $user_company->real_title = $post['real_title'];
                if(!$user_company->save()){
                    throw  new Exception("修改请稍后重试");
                }

                //编辑公司信息成功后，修改公司IP：
//                $ips = $this->request->post('ips');
//                foreach ($ips as $key => $item) {
//                    if(empty($item)){
//                        // throw new Exception("IP必填");
//                        // exit;
//                        unset($ips[$key]);
//                        continue;
//                    }
//                    if(!Util::is_ip($item)){
//                        throw new Exception("不是合法IP：".$item);
//                        exit;
//                    }
//                }
//                if(empty($ips)){
//                    throw new Exception("IP必填");
//                }
//
//                $ip_add_action = LoanCollectionIp::new_record(array('outside'=>$user_company->id, 'ip_list'=>$ips, 'remark'=>'修改机构'));
//                if(!$ip_add_action){
//                    throw new Exception("修改IP时失败");
//
//                }
                $page = $this->request->post('page');
                return $this->redirectMessage('编辑成功', self::MSG_SUCCESS, Url::toRoute(['user-company/company-lists','page'=>$page+1]));

            }
        }catch(Exception $e){
            return $this->redirectMessage($e->getMessage(), self::MSG_ERROR);
        }
        $page = $this->request->get('page');
        return $this->render('company-edit', array(
            'user_company' => $user_company,
            'ip_list' =>$company_ip_list,
            'tip'=>0,
            'page'=>$page
        ));
    }
}
