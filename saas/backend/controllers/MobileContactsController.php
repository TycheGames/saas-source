<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2019/8/13
 * Time: 11:59
 */
namespace backend\controllers;

use common\models\user\MgUserMobileContacts;
use yii\data\Pagination;

class MobileContactsController extends  BaseController{

    protected function getFilter() {
        $condition = [];
        if ($this->getRequest()->getIsGet()) {
            $search = $this->request->get();
            if(isset($search['user_id'])&&!empty($search['user_id'])){
                $condition[] = ['=','user_id',$search['user_id']] ;
                //$condition .= " AND user_id = " . intval($search['user_id']);
            }

            if(isset($search['mobile'])&&!empty($search['mobile'])){
                $condition[] = ['like','mobile',$search['mobile']] ;
//                $condition .= " AND mobile like '%" . $search['mobile']."%'";
            }

            if(isset($search['name'])&&!empty($search['name'])){
                $condition[] = ['like','name',$search['name']] ;
//                $condition .= " AND name like '%" . $search['name']."%'";
            }


        }

        return $condition;

    }



    /**
     * @name 用户管理-用户管理-用户通讯录
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function  actionMobileContactsList(){
        $conditions = $this->getFilter();
        if( !empty($conditions) && empty($this->request->get('user_id'))){
            return $this->redirectMessage(Yii::T('common', 'User ID is required'), self::MSG_ERROR);
        }
        $query = MgUserMobileContacts::find();
        foreach ($conditions as $condition){
            $query = $query->andWhere($condition);
        }
        $query = $query->orderBy([
            '_id' => SORT_DESC,
        ]);
        $count = 99999999;
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = 15;
        $loan_mobile_contacts_list = $query->offset($pages->offset)->limit($pages->limit)->all();
        return $this->render('mobile-contacts-list', array(
            'loan_mobile_contacts_list' => $loan_mobile_contacts_list,
            'loan_repayment_list'=>array(),
            'pages' => $pages,
        ));
    }
}