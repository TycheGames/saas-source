<?php

namespace callcenter\service;


use callcenter\models\CollectionCallRecords;
use callcenter\models\CollectorCallData;
use callcenter\models\loan_collection\LoanCollectionOrder;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderExternal;
use common\models\order\UserLoanOrderExtraRelation;
use common\models\user\LoanPerson;
use common\models\user\MgUserMobileContacts;
use common\models\user\UserContact;
use yii\helpers\ArrayHelper;

class CallStatisticsService
{

    function CallEventHandler($event){
        /** @var CollectionCallRecords $collectionCallRecords */
        $collectionCallRecords = $event->sender;
        $userId = $collectionCallRecords->user_id;
        switch ($event->name) {
            case CollectionCallRecords::EVENT_AFTER_APP_COLLECTOR_CALL_RECORDS_UPLOAD: //催收员手机通话记录上传添加记录触发
                $date = date('Y-m-d');
                if($collectionCallRecords->callType == CollectionCallRecords::CALL_TYPE_OUT
                    && date('Y-m-d',$collectionCallRecords->callDateTime) == $date){
                    //呼出类型即拨打
                    $callNumber = substr($collectionCallRecords->callNumber,-10);
                    $callDuration = $collectionCallRecords->callDuration;
                    $callName = $collectionCallRecords->callName;
                    $isValid = $collectionCallRecords->callDuration > 0 ? 1 : 0;
                    $type = 0;
                    $isOneSelfNumber = LoanCollectionOrder::find()
                        ->from(LoanCollectionOrder::tableName().' A')
                        ->leftJoin(LoanPerson::getDbName().'.'.LoanPerson::tableName(). ' B','A.user_id = B.id')
                        ->where([
                            'A.current_collection_admin_user_id' => $userId,
                            'A.status' => LoanCollectionOrder::$not_end_status,
                            'B.phone' => $callNumber
                        ])
                        ->exists();
                    if($isOneSelfNumber){
                        $type = CollectorCallData::TYPE_ONE_SELF;
                    }

                    if($type == 0){
                        $isContactNumber = LoanCollectionOrder::find()
                            ->from(LoanCollectionOrder::tableName().' A')
                            ->leftJoin(UserLoanOrderExtraRelation::getDbName().'.'.UserLoanOrderExtraRelation::tableName().' B', 'B.order_id = A.user_loan_order_id')
                            ->leftJoin(UserContact::getDbName().'.'.UserContact::tableName().' C','B.user_contact_id = C.id')
                            ->where([
                                'A.current_collection_admin_user_id' => $userId,
                                'A.status' => LoanCollectionOrder::$not_end_status,
                            ])
                            ->andWhere(['OR',['C.phone' => $callNumber],['C.other_phone' => $callNumber]])
                            ->exists();
                        if($isContactNumber){
                            $type = CollectorCallData::TYPE_CONTACT;
                        }
                    }

                    if($type == 0){
                        //非导流用户ID
                        $personIds = ArrayHelper::getColumn(LoanCollectionOrder::find()
                            ->select(['A.user_id'])
                            ->from(LoanCollectionOrder::tableName().' A')
                            ->leftJoin(UserLoanOrder::getDbName().'.'.UserLoanOrder::tableName().' B', 'B.id = A.user_loan_order_id')
                            ->where([
                                'A.current_collection_admin_user_id' => $userId,
                                'A.status' => LoanCollectionOrder::$not_end_status,
                                'B.is_export' => UserLoanOrder::IS_EXPORT_NO,
                            ])
                            ->asArray()->all(),'user_id');
                        foreach ($personIds as &$v){
                            $v = (int)$v;
                        }
                        $isNoExportAddressBookNumber = MgUserMobileContacts::find()
                            ->where(['user_id' => $personIds,'mobile' => $callNumber])->exists();
                        if($isNoExportAddressBookNumber){
                            $type = CollectorCallData::TYPE_ADDRESS_BOOK;
                        }

                        if($type == 0){
                            //导流order_uuid
                            $orderUuids = ArrayHelper::getColumn(LoanCollectionOrder::find()
                                ->select(['B.order_uuid'])
                                ->from(LoanCollectionOrder::tableName().' A')
                                ->leftJoin(UserLoanOrder::getDbName().'.'.UserLoanOrder::tableName().' B', 'B.id = A.user_loan_order_id')
                                ->where([
                                    'A.current_collection_admin_user_id' => $userId,
                                    'A.status' => LoanCollectionOrder::$not_end_status,
                                    'B.is_export' => UserLoanOrder::IS_EXPORT_YES,
                                ])
                                ->asArray()->all(),'order_uuid');
                            $personIds = ArrayHelper::getColumn(UserLoanOrderExternal::find()->select(['user_id'])
                                ->where(['order_uuid' => $orderUuids])->asArray()->all(),'user_id');
                            foreach ($personIds as &$v){
                                $v = (int)$v;
                            }

                            if(strlen($callNumber) == 10){
                                $isExportAddressBookNumber = MgUserMobileContacts::find()
                                    ->where(['user_id' => $personIds])
                                    ->andWhere(['like','mobile',$callNumber])
                                    ->exists(MgUserMobileContacts::getLoanDb());
                                if($isExportAddressBookNumber){
                                    $type = CollectorCallData::TYPE_ADDRESS_BOOK;
                                }
                            }
                        }
                    }

                    if($type > 0){
                        //添加或更新催收员当天拨打数据
                        /** @var CollectorCallData $collectorCallData */
                        $collectorCallData = CollectorCallData::find()->where([
                            'date' => $date,
                            'user_id' => $userId,
                            'phone' => $callNumber,
                            'type' => $type,
                            'phone_type' => CollectorCallData::NATIVE,
                        ])->one();
                        if (!$collectorCallData) {
                            $collectorCallData = new CollectorCallData();
                            $collectorCallData->date = $date;
                            $collectorCallData->user_id = $userId;
                            $collectorCallData->phone = $callNumber;
                            $collectorCallData->type = $type;
                            $collectorCallData->is_valid = $isValid;
                            $collectorCallData->phone_type = CollectorCallData::NATIVE;
                        }else{
                            $collectorCallData->is_valid = ($isValid || $collectorCallData->is_valid) ? CollectorCallData::VALID : CollectorCallData::INVALID;
                        }
                        $collectorCallData->name = $callName;
                        $collectorCallData->times += 1;
                        $collectorCallData->duration += $callDuration;
                        $collectorCallData->save();
                    }
                }
                break;
            default:
                break;
        }
    }

    /**
     * @param $userId 催收员id
     * @param $callNumber 拨打电话
     * @return int 电话类型
     */
    public function searchPhoneType($userId, $callNumber)
    {
        $type = 0;
        $isOneSelfNumber = LoanCollectionOrder::find()
            ->from(LoanCollectionOrder::tableName() . ' A')
            ->leftJoin(LoanPerson::getDbName() . '.' . LoanPerson::tableName() . ' B', 'A.user_id = B.id')
            ->where([
                'A.current_collection_admin_user_id' => $userId,
                'A.status' => LoanCollectionOrder::$not_end_status,
                'B.phone' => $callNumber
            ])
            ->exists();
        if($isOneSelfNumber){
            $type = CollectorCallData::TYPE_ONE_SELF;
        }

        if($type == 0){
            $isContactNumber = LoanCollectionOrder::find()
                ->from(LoanCollectionOrder::tableName() . ' A')
                ->leftJoin(UserLoanOrderExtraRelation::getDbName() . '.' . UserLoanOrderExtraRelation::tableName() . ' B', 'B.order_id = A.user_loan_order_id')
                ->leftJoin(UserContact::getDbName() . '.' . UserContact::tableName() . ' C', 'B.user_contact_id = C.id')
                ->where([
                    'A.current_collection_admin_user_id' => $userId,
                    'A.status' => LoanCollectionOrder::$not_end_status,
                ])
                ->andWhere(['OR', ['C.phone' => $callNumber], ['C.other_phone' => $callNumber]])
                ->exists();
            if($isContactNumber){
                $type = CollectorCallData::TYPE_CONTACT;
            }
        }

        if($type == 0){
            //非导流用户ID
            $personIds = ArrayHelper::getColumn(LoanCollectionOrder::find()
                ->select(['A.user_id'])
                ->from(LoanCollectionOrder::tableName() . ' A')
                ->leftJoin(UserLoanOrder::getDbName() . '.' . UserLoanOrder::tableName() . ' B', 'B.id = A.user_loan_order_id')
                ->where([
                    'A.current_collection_admin_user_id' => $userId,
                    'A.status' => LoanCollectionOrder::$not_end_status,
                    'B.is_export' => UserLoanOrder::IS_EXPORT_NO,
                ])
                ->asArray()->all(), 'user_id');
            foreach ($personIds as &$v) {
                $v = (int)$v;
            }
            $isNoExportAddressBookNumber = MgUserMobileContacts::find()
                ->where(['user_id' => $personIds, 'mobile' => $callNumber])->exists();
            if($isNoExportAddressBookNumber){
                $type = CollectorCallData::TYPE_ADDRESS_BOOK;
            }

            if($type == 0){
                //导流order_uuid
                $orderUuids = ArrayHelper::getColumn(LoanCollectionOrder::find()
                    ->select(['B.order_uuid'])
                    ->from(LoanCollectionOrder::tableName() . ' A')
                    ->leftJoin(UserLoanOrder::getDbName() . '.' . UserLoanOrder::tableName() . ' B', 'B.id = A.user_loan_order_id')
                    ->where([
                        'A.current_collection_admin_user_id' => $userId,
                        'A.status' => LoanCollectionOrder::$not_end_status,
                        'B.is_export' => UserLoanOrder::IS_EXPORT_YES,
                    ])
                    ->asArray()->all(), 'order_uuid');
                $personIds = ArrayHelper::getColumn(UserLoanOrderExternal::find()->select(['user_id'])
                    ->where(['order_uuid' => $orderUuids])->asArray()->all(), 'user_id');
                foreach ($personIds as &$v) {
                    $v = (int)$v;
                }
                $isExportAddressBookNumber = MgUserMobileContacts::find()
                    ->where(['user_id' => $personIds, 'mobile' => $callNumber])->exists(MgUserMobileContacts::getLoanDb());
                if($isExportAddressBookNumber){
                    $type = CollectorCallData::TYPE_ADDRESS_BOOK;
                }
            }
        }
        return $type;
    }
}