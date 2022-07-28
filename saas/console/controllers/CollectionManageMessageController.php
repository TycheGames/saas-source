<?php

namespace console\controllers;

use callcenter\models\AdminManagerRelation;
use callcenter\models\AdminMessage;
use callcenter\models\AdminMessageTask;
use callcenter\models\AdminUser;
use callcenter\models\AdminUserRole;
use callcenter\models\CollectorBackMoney;
use callcenter\models\CompanyTeam;
use callcenter\models\loan_collection\LoanCollectionOrder;
use callcenter\models\loan_collection\LoanCollectionRecord;
use common\helpers\RedisQueue;
use common\models\order\UserLoanOrderRepayment;
use common\models\user\LoanPerson;

class CollectionManageMessageController extends BaseController{

    /**
     * 目标下达 8:55
     */
    public function actionSendTodayTask() {
        $this->printMessage( "开始执行");
        $this->todayTotalTask();
        $this->printMessage( "执行完毕");
    }

    /**
     *  上报失联/旷工/请假人员 9:30
     */
    public function actionReportPersonnel() {
        $this->printMessage( "开始执行");

        $smallRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
        $bigRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_BIG_TEAM_MANAGER);
        $message = 'Please report to the manager at 9:45 for those who lost contact and absenteeism today, and those who temporarily ask for leave';


        $adminMessageTask = AdminMessageTask::find()
            ->select(['outside','group'])
            ->where([
                'status' => AdminMessageTask::STATUS_OPEN,
            ])
            ->groupBy(['outside','group'])
            ->asArray()
            ->all();

        $adminIds = [];
        foreach ($adminMessageTask as $value){
            $smallTeams = AdminUser::find()
                ->select(['id'])
                ->where([
                    'role' => $smallRoles,
                    'outside' => $value['outside'],
                    'group' => $value['group'],
                    'open_status' => AdminUser::$usable_status
                ])->asArray()->all();
            if($smallTeams){
                $adminIds = array_merge($adminIds,array_column($smallTeams,'id'));
            }

            $bigTeams = AdminManagerRelation::find()
                ->alias('m')
                ->select(['m.admin_id'])
                ->leftJoin(AdminUser::tableName().' u','m.admin_id = u.id')
                ->where([
                    'u.role' => $bigRoles,
                    'u.outside' => $value['outside'],
                    'm.group' => $value['group'],
                    'u.open_status' => AdminUser::$usable_status
                ])->asArray()->all();
            if($bigTeams){
                $adminIds = array_merge($adminIds,array_column($smallTeams,'id'));
            }
        }
        $adminIds = array_unique($adminIds);
        if($adminIds){
            foreach ($adminIds as $adminId){
                $adminMessage = new AdminMessage();
                $adminMessage->admin_id = $adminId;
                $adminMessage->content = $message;
                $adminMessage->save();
                RedisQueue::addSet(RedisQueue::COLLECTION_NEW_MESSAGE_TEAM_TL_UID,$adminId);
            }
        }

        $this->printMessage( "执行完毕");
    }

    /**
     * 开案 10:30
     */
    public function actionOpenCase(){
        $this->printMessage( "开始执行");

        $smallRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
        $bigRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_BIG_TEAM_MANAGER);

        $today = strtotime('today');
        //$todayEnd = strtotime('today') + 86400;

        $adminMessageTask = AdminMessageTask::find()
            ->select(['outside','group'])
            ->where([
                'status' => AdminMessageTask::STATUS_OPEN,
                'task_type' => array_merge(AdminMessageTask::$task_type_overdue_day,AdminMessageTask::$task_type_new)
            ])
            ->groupBy(['outside','group'])
            ->asArray()
            ->all();

        foreach ($adminMessageTask as $value){
            $arr =  LoanCollectionOrder::find()
                ->alias('cOrder')
                ->select(['cOrder.current_collection_admin_user_id','user.username','user.group_game','c' => 'COUNT(1)'])
                ->leftJoin(AdminUser::tableName().' user',' cOrder.current_collection_admin_user_id = user.id')
                ->where([
                    'cOrder.status' => LoanCollectionOrder::$collection_status,
                    'user.outside' => $value['outside'],
                    'user.group' => $value['group'],
                ])
                ->andWhere(['>','cOrder.current_collection_admin_user_id',0])
                ->andWhere(['<','cOrder.last_collection_time',$today])
                ->groupBy(['cOrder.current_collection_admin_user_id'])
                ->asArray()
                ->all();

            $day = array_flip(LoanCollectionOrder::$reset_overdue_days)[$value['group']];
            $newCaseArr =  LoanCollectionOrder::find()
                ->alias('cOrder')
                ->select(['cOrder.current_collection_admin_user_id','user.username','user.group_game','c' => 'COUNT(1)'])
                ->leftJoin(AdminUser::tableName().' user',' cOrder.current_collection_admin_user_id = user.id')
                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','cOrder.user_loan_order_repayment_id = repayment.id')
                ->where([
                    'cOrder.status' => LoanCollectionOrder::$collection_status,
                    'user.outside' => $value['outside'],
                    'user.group' => $value['group'],
                    'repayment.overdue_day' => $day
                ])
                ->andWhere(['>','cOrder.current_collection_admin_user_id',0])
                ->andWhere(['<','cOrder.last_collection_time',$today])
                ->groupBy(['cOrder.current_collection_admin_user_id'])
                ->asArray()
                ->all();

            $countArr = [];

            foreach ($arr as $item){
                $countArr[$item['group_game']][$item['current_collection_admin_user_id']] = ['totalCount' => $item['c'],'name' => $item['username'],'newCount' => 0];
            }
            foreach ($newCaseArr as $item){
                if(isset($countArr[$item['group_game']][$item['current_collection_admin_user_id']])){
                    $countArr[$item['group_game']][$item['current_collection_admin_user_id']]['newCount'] = $item['c'];
                }
            }

            foreach ($countArr as $groupGame => $groupGameData){
                foreach ($groupGameData as $uid => $uData){
                    $message = "Your team member {$uData['name']} still has {$uData['totalCount']} orders in hand and has not followed up today, and today’s new sub-cases have {$uData['newCount']} orders No case opened, please follow up as soon as possible";
                    $this->saveAdminManageMessage($smallRoles,$bigRoles,$value['outside'],$value['group'],$groupGame,$message);
                }
            }
        }

        //后手
        $adminMessageTask = AdminMessageTask::find()
            ->select(['outside','group'])
            ->where([
                'status' => AdminMessageTask::STATUS_OPEN,
                'task_type' => AdminMessageTask::$task_type_m
            ])
            ->asArray()
            ->all();

        foreach ($adminMessageTask as $value){
            $newCaseArr =  LoanCollectionOrder::find()
                ->alias('cOrder')
                ->select(['cOrder.current_collection_admin_user_id','user.username','user.group_game','c' => 'COUNT(1)'])
                ->leftJoin(AdminUser::tableName().' user',' cOrder.current_collection_admin_user_id = user.id')
                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','cOrder.user_loan_order_repayment_id = repayment.id')
                ->where([
                    'cOrder.status' => LoanCollectionOrder::$collection_status,
                    'user.outside' => $value['outside'],
                    'user.group' => $value['group'],
                ])
                ->andWhere(['>=','cOrder.dispatch_time',strtotime('today')])
                ->andWhere(['<','cOrder.dispatch_time',strtotime('today')+86400])
                ->andWhere(['>','cOrder.current_collection_admin_user_id',0])
                ->andWhere(['<','cOrder.last_collection_time',$today])
                ->groupBy(['cOrder.current_collection_admin_user_id'])
                ->asArray()
                ->all();

            $countArr = [];

            foreach ($newCaseArr as $item){
                $countArr[$item['group_game']][$item['current_collection_admin_user_id']] = ['name' => $item['username'],'newCount' => 0];
            }

            foreach ($countArr as $groupGame => $groupGameData){
                foreach ($groupGameData as $uid => $uData){
                    $message = "Your team members {$uData['name']} today’s new sub-cases and {$uData['newCount']} have not followed up today, please follow up as soon as possible";
                    $this->saveAdminManageMessage($smallRoles,$bigRoles,$value['outside'],$value['group'],$groupGame,$message);
                }
            }
        }

        $this->printMessage( "执行完毕");
    }

    /**
     * 结果追踪 11:00-16:00整点
     */
    public function actionResultTracking(){
        $this->printMessage( "开始执行");
        $this->todayTotalTask(true);
        $this->printMessage( "执行完毕");
    }

    /**
     * 1小时更新有无还款结果追踪
     */
    public function actionNoRepayTracking(){
        $this->printMessage( "开始执行");

        $smallRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
        $bigRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_BIG_TEAM_MANAGER);
        $finishStatus = LoanCollectionOrder::STATUS_COLLECTION_FINISH;
        $hour = date('H');

        $adminMessageTask = AdminMessageTask::find()
            ->select(['outside','group'])
            ->where([
                'status' => AdminMessageTask::STATUS_OPEN,
                'task_type' => array_merge(AdminMessageTask::$task_type_overdue_day,AdminMessageTask::$task_type_new)
            ])
            ->groupBy(['outside','group'])
            ->asArray()
            ->all();

        $arr = [];
        foreach ($adminMessageTask as $value){
            $arr[$value['outside']][] = $value['group'];
        }

        foreach ($arr as $outside => $groupData){
            foreach ($groupData as $group){
                $countArr = [];
                $dCountArr = LoanCollectionOrder::find()
                    ->alias('order')
                    ->select(['order.current_collection_admin_user_id','user.group_game','user.username','finishCount' => "SUM(IF(order.status = {$finishStatus},1,0))"])
                    ->leftJoin(AdminUser::tableName().' user','order.current_collection_admin_user_id = user.id')
                    ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','order.user_loan_order_repayment_id = repayment.id')
                    ->where([
                        'user.outside' => $outside,
                        'user.group' => $group
                    ])
                    ->andWhere([
                        'OR',
                        ['repayment.status' => UserLoanOrderRepayment::STATUS_NORAML],
                        ['AND',['>=','repayment.closing_time',strtotime('today')],['<','repayment.closing_time',strtotime('today') + 86400]]
                    ])
                    ->groupBy(['order.current_collection_admin_user_id'])
                    ->having(['finishCount' => 0])
                    ->asArray()
                    ->all();
                foreach ($dCountArr as $item){
                    $countArr[$item['group_game']][$item['current_collection_admin_user_id']] = $item['username'];
                }

                foreach ($countArr as $groupGame => $groupGameData){
                    foreach ($groupGameData as $uid => $username){
                        $message = "Your team member {$username} has not repaid the payment by {$hour} o'clock, please follow up";
                        $this->saveAdminManageMessage($smallRoles,$bigRoles,$outside,$group,$groupGame,$message);
                    }
                }
            }
        }

        //后手
        if($hour == '18'){
            $adminMessageTask = AdminMessageTask::find()
                ->select(['outside','group'])
                ->where([
                    'status' => AdminMessageTask::STATUS_OPEN,
                    'task_type' => AdminMessageTask::$task_type_m
                ])
                ->asArray()
                ->all();

            $arr = [];
            foreach ($adminMessageTask as $value){
                $arr[$value['outside']][] = $value['group'];
            }

            foreach ($arr as $outside => $groupData){
                foreach ($groupData as $group){
                    $countArr = [];
                    $dCountArr = LoanCollectionOrder::find()
                        ->alias('order')
                        ->select(['order.current_collection_admin_user_id','user.group_game','user.username','finishCount' => "SUM(IF(order.status = {$finishStatus},1,0))"])
                        ->leftJoin(AdminUser::tableName().' user','order.current_collection_admin_user_id = user.id')
                        ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','order.user_loan_order_repayment_id = repayment.id')
                        ->where([
                            'user.outside' => $outside,
                            'user.group' => $group
                        ])
                        ->andWhere([
                            'OR',
                            ['repayment.status' => UserLoanOrderRepayment::STATUS_NORAML],
                            ['AND',['>=','repayment.closing_time',strtotime('today')],['<','repayment.closing_time',strtotime('today') + 86400]]
                        ])
                        ->groupBy(['order.current_collection_admin_user_id'])
                        ->having(['finishCount' => 0])
                        ->asArray()
                        ->all();
                    foreach ($dCountArr as $item){
                        $countArr[$item['group_game']][$item['current_collection_admin_user_id']] = $item['username'];
                    }

                    foreach ($countArr as $groupGame => $groupGameData){
                        foreach ($groupGameData as $uid => $username){
                            $message = "Your team member {$username} has not repaid the payment by {$hour} o'clock, please follow up";
                            $this->saveAdminManageMessage($smallRoles,$bigRoles,$outside,$group,$groupGame,$message);
                        }
                    }
                }
            }
        }

        $this->printMessage( "执行完毕");
    }

    /**
     * 时间段内无还款结果追踪11-18
     */
    public function actionInHourNoRepayTracking(){
        $cTime = time();
        $hourTime = strtotime(date('Y-m-d 10:00:00'));
        if($cTime < $hourTime){
            return;
        }
        $hours = min(intval(($cTime - $hourTime) / 3600),4);
        $this->printMessage( "开始执行");
        $smallRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
        $bigRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_BIG_TEAM_MANAGER);
        $finishStatus = LoanCollectionOrder::STATUS_COLLECTION_FINISH;

        $adminMessageTask = AdminMessageTask::find()
            ->select(['outside','group'])
            ->where([
                'status' => AdminMessageTask::STATUS_OPEN,
                'task_type' => array_merge(AdminMessageTask::$task_type_overdue_day,AdminMessageTask::$task_type_new)
            ])
            ->groupBy(['outside','group'])
            ->asArray()
            ->all();

        $arr = [];
        foreach ($adminMessageTask as $value){
            $arr[$value['outside']][] = $value['group'];
        }

        foreach ($arr as $outside => $groupData){
            foreach ($groupData as $group){
                if($hours > 0){
                    $removeArr = [];
                    for ($i = $hours;$i > 0; $i--){
                        $countArr = [];
                        $dCountArr = LoanCollectionOrder::find()
                            ->alias('order')
                            ->select(['order.current_collection_admin_user_id','user.group_game','user.username','finishCount' => "SUM(IF(order.status = {$finishStatus},1,0))"])
                            ->leftJoin(AdminUser::tableName().' user','order.current_collection_admin_user_id = user.id')
                            ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','order.user_loan_order_repayment_id = repayment.id')
                            ->where([
                                'user.outside' => $outside,
                                'user.group' => $group
                            ])
                            ->andWhere([
                                'OR',
                                ['repayment.status' => UserLoanOrderRepayment::STATUS_NORAML],
                                ['AND',['>=','repayment.closing_time',$cTime - 3600 * $i],['<','repayment.closing_time',$cTime]]
                            ])
                            ->groupBy(['order.current_collection_admin_user_id'])
                            ->having(['finishCount' => 0])
                            ->asArray()
                            ->all();
                        foreach ($dCountArr as $item){
                            if(!in_array($item['current_collection_admin_user_id'],$removeArr)){
                                $countArr[$item['group_game']][$item['current_collection_admin_user_id']] = $item['username'];
                            }
                            $removeArr[] = $item['current_collection_admin_user_id'];
                        }

                        foreach ($countArr as $groupGame => $groupGameData){
                            foreach ($groupGameData as $uid => $username){
                                $startMessage = "Your group member {$username} has no new repayments within {$i} hours,";
                                $endMessage = '';
                                switch ($i){
                                    case 1:
                                        $endMessage = 'Please follow';
                                        break;
                                    case 2:
                                        $endMessage = 'Please follow up and help';
                                        break;
                                    case 3:
                                        $endMessage = 'Its non-committed repayment cases will be withdrawn. Please follow up this person to set the status of the case and report the poor performance case';
                                        break;
                                    case 4:
                                        $endMessage = 'All of its cases will be withdrawn, please follow up for training, and all cases will be assigned on the day of being reported and withdrawn';
                                        break;
                                }
                                $message = $startMessage.$endMessage;
                                $this->saveAdminManageMessage($smallRoles,$bigRoles,$outside,$group,$groupGame,$message);
                            }
                        }
                    }
                }
            }
        }

        $hours2 = min(intval(($cTime - $hourTime) / 3600),8);
        if($hours2 % 2 == 0){
            $adminMessageTask = AdminMessageTask::find()
                ->select(['outside','group'])
                ->where([
                    'status' => AdminMessageTask::STATUS_OPEN,
                    'task_type' => AdminMessageTask::$task_type_m
                ])
                ->groupBy(['outside','group'])
                ->asArray()
                ->all();

            $arr = [];
            foreach ($adminMessageTask as $value){
                $arr[$value['outside']][] = $value['group'];
            }

            foreach ($arr as $outside => $groupData){
                foreach ($groupData as $group){
                    if($hours2 > 0){
                        $removeArr = [];
                        for ($i = $hours2;$i > 0; $i-=2){
                            $countArr = [];
                            $dCountArr = LoanCollectionOrder::find()
                                ->alias('order')
                                ->select(['order.current_collection_admin_user_id','user.group_game','user.username','finishCount' => "SUM(IF(order.status = {$finishStatus},1,0))"])
                                ->leftJoin(AdminUser::tableName().' user','order.current_collection_admin_user_id = user.id')
                                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','order.user_loan_order_repayment_id = repayment.id')
                                ->where([
                                    'user.outside' => $outside,
                                    'user.group' => $group
                                ])
                                ->andWhere([
                                    'OR',
                                    ['repayment.status' => UserLoanOrderRepayment::STATUS_NORAML],
                                    ['AND',['>=','repayment.closing_time',$cTime - 3600 * $i],['<','repayment.closing_time',$cTime]]
                                ])
                                ->groupBy(['order.current_collection_admin_user_id'])
                                ->having(['finishCount' => 0])
                                ->asArray()
                                ->all();

                            foreach ($dCountArr as $item){
                                if(!in_array($item['current_collection_admin_user_id'],$removeArr)){
                                    $countArr[$item['group_game']][$item['current_collection_admin_user_id']] = $item['username'];
                                }
                                $removeArr[] = $item['current_collection_admin_user_id'];
                            }


                            foreach ($countArr as $groupGame => $groupGameData){
                                foreach ($groupGameData as $uid => $username){
                                    $startMessage = "Your group member {$username} has no new repayments within {$i} hours,";
                                    $endMessage = '';
                                    switch ($i){
                                        case 2:
                                            $endMessage = 'Please follow';
                                            break;
                                        case 4:
                                            $endMessage = 'Please follow up and help';
                                            break;
                                        case 6:
                                            $endMessage = 'Please follow up and help, report to the manager with poor performance';
                                            break;
                                        case 8:
                                            $endMessage = 'If you warn him once, please report to the person in charge of the workplace to send a warning letter. If three warnings occur, all his cases will be withdrawn and counted as a flow of people';
                                            break;
                                    }
                                    $message = $startMessage.$endMessage;
                                    $this->saveAdminManageMessage($smallRoles,$bigRoles,$outside,$group,$groupGame,$message);
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->printMessage( "执行完毕");
    }


    /**
     * 17:30 存在有部分还款的订单通知
     */
    public function actionHasPartRepayment(){
        $this->printMessage( "开始执行");
        $smallRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
        $bigRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_BIG_TEAM_MANAGER);

        $adminMessageTask = AdminMessageTask::find()
            ->select(['outside','group'])
            ->where([
                'status' => AdminMessageTask::STATUS_OPEN,
            ])
            ->groupBy(['outside','group'])
            ->asArray()
            ->all();

        $arr = [];
        foreach ($adminMessageTask as $value){
            $arr[$value['outside']][] = $value['group'];
        }

        foreach ($arr as $outside => $groupData){
            foreach ($groupData as $group){
                $countArr = [];
                $dCountArr = LoanCollectionOrder::find()
                    ->alias('order')
                    ->select([
                        'loanPerson.phone',
                        'user.group_game',
                        'user.username',
                        'repayment.true_total_money',
                        'scheduledPayment' => '(repayment.total_money - repayment.true_total_money - repayment.coupon_money - repayment.delay_reduce_amount)'
                    ])
                    ->leftJoin(AdminUser::tableName().' user','order.current_collection_admin_user_id = user.id')
                    ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','order.user_loan_order_repayment_id = repayment.id')
                    ->leftJoin(LoanPerson::getDbName().'.'.LoanPerson::tableName().' loanPerson','order.user_id = loanPerson.id')
                    ->where([
                        'user.outside' => $outside,
                        'user.group' => $group,
                        'order.status' => LoanCollectionOrder::$collection_status,
                        'repayment.status' => UserLoanOrderRepayment::STATUS_NORAML
                    ])
                    ->andWhere(['>','repayment.true_total_money',0])
                    ->asArray()
                    ->all();

                foreach ($dCountArr as $item){
                    $countArr[$item['group_game']][$item['username']][] = ['phone' => $item['phone'], 'true_total_money' => sprintf("%0.2f",$item['true_total_money']/100), 'scheduled_payment' => sprintf("%0.2f",$item['scheduledPayment']/100)];
                }

                foreach ($countArr as $groupGame => $groupGameData){
                    foreach ($groupGameData as $username => $val){
                        $message =  "In the hands of your team member {$username}, ";
                        foreach ($val as $v){
                            $message .= "Case {$v['phone']} A has partially repaid {$v['true_total_money']}Rs, and {$v['scheduled_payment']}Rs can be settled.";
                        }
                        $message .= 'Please remind team members to follow up';
                        $this->saveAdminManageMessage($smallRoles,$bigRoles,$outside,$group,$groupGame,$message);
                    }
                }


            }
        }

        $this->printMessage( "执行完毕");
    }

    /**
     * 18:00 存在有减免的订单通知
     */
    public function actionHasCanReduce(){
        $this->printMessage( "开始执行");
        $smallRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
        $bigRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_BIG_TEAM_MANAGER);

        $adminMessageTask = AdminMessageTask::find()
            ->select(['outside','group'])
            ->where([
                'status' => AdminMessageTask::STATUS_OPEN,
            ])
            ->groupBy(['outside','group'])
            ->asArray()
            ->all();

        $arr = [];
        foreach ($adminMessageTask as $value){
            $arr[$value['outside']][] = $value['group'];
        }

        foreach ($arr as $outside => $groupData){
            foreach ($groupData as $group){
                $countArr = [];
                $dCountArr = LoanCollectionOrder::find()
                    ->alias('order')
                    ->select([
                        'loanPerson.phone',
                        'user.group_game',
                        'user.username',
                    ])
                    ->leftJoin(AdminUser::tableName().' user','order.current_collection_admin_user_id = user.id')
                    ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','order.user_loan_order_repayment_id = repayment.id')
                    ->leftJoin(LoanPerson::getDbName().'.'.LoanPerson::tableName().' loanPerson','order.user_id = loanPerson.id')
                    ->where([
                        'user.outside' => $outside,
                        'user.group' => $group,
                        'order.status' => LoanCollectionOrder::$collection_status,
                        'repayment.status' => UserLoanOrderRepayment::STATUS_NORAML
                    ])
                    ->andWhere('repayment.true_total_money >= (repayment.principal + repayment.interests)')
                    ->asArray()
                    ->all();
                foreach ($dCountArr as $item){
                    $countArr[$item['group_game']][$item['username']][] = $item['phone'];
                }

                foreach ($countArr as $groupGame => $groupGameData){
                    foreach ($groupGameData as $username => $val){
                        $message = "Your team member {$username} has cases that can be reduced or cleared. These cases include ";
                        foreach ($val as $phone){
                            $message.= ($phone .',');
                        }
                        $message .= "Please remind team members to follow up";
                        $this->saveAdminManageMessage($smallRoles,$bigRoles,$outside,$group,$groupGame,$message);
                    }
                }
            }
        }

        $this->printMessage( "执行完毕");
    }

    /**
     * 半小时是否有承诺还款
     */
    public function actionHasNoPromiseOfRepayment(){
        $i = intval(date('i'));
        $this->printMessage( "开始执行");
        $smallRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
        $bigRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_BIG_TEAM_MANAGER);

        $now = time();

        //前手
        $beforeTime = $now - 1800;
        $adminMessageTask = AdminMessageTask::find()
            ->select(['outside','group'])
            ->where([
                'status' => AdminMessageTask::STATUS_OPEN,
                'task_type' => array_merge(AdminMessageTask::$task_type_overdue_day,AdminMessageTask::$task_type_new)
            ])
            ->groupBy(['outside','group'])
            ->asArray()
            ->all();
        $riskControl = LoanCollectionRecord::RISK_CONTROL_PROMISED_PAYMENT;
        foreach ($adminMessageTask as $value) {
            $arr = LoanCollectionOrder::find()
                ->alias('cOrder')
                ->select(['cOrder.current_collection_admin_user_id', 'user.username', 'user.group_game','c' => "SUM(IF(cRecord.risk_control = {$riskControl},1,0))"])
                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','cOrder.user_loan_order_repayment_id = repayment.id')
                ->leftJoin(LoanCollectionRecord::tableName() . ' cRecord', 'cRecord.order_id = cOrder.id')
                ->leftJoin(AdminUser::tableName() . ' user', ' cOrder.current_collection_admin_user_id = user.id')
                ->where([
                    'user.outside'  => $value['outside'],
                    'user.group'    => $value['group'],
                ])
                ->andWhere([
                    'OR',
                    ['repayment.status' => UserLoanOrderRepayment::STATUS_NORAML],
                    ['AND',['>=','repayment.closing_time',strtotime('today')],['<','repayment.closing_time',strtotime('today') + 86400]]
                ])
                ->andWhere(['>', 'cOrder.current_collection_admin_user_id', 0])
                ->andWhere(['>=', 'cRecord.created_at', $beforeTime])
                ->andWhere(['<', 'cRecord.created_at', $now])
                ->groupBy(['cOrder.current_collection_admin_user_id'])
                ->having(['c' => 0])
                ->asArray()
                ->all();
            $countArr = [];
            foreach ($arr as $item){
                $countArr[$item['group_game']][] = $item['username'];
            }

            foreach ($countArr as $groupGame => $groupGameData){
                foreach ($groupGameData as $username){
                    $message = "Your team member {$username} has no new promise to repay within half an hour, please follow up";
                    $this->saveAdminManageMessage($smallRoles,$bigRoles,$value['outside'],$value['group'],$groupGame,$message);
                }
            }
        }

        //后手
        if($i == 0){
            $beforeTime = $now - 3600;
            $adminMessageTask = AdminMessageTask::find()
                ->select(['outside','group'])
                ->where([
                    'status' => AdminMessageTask::STATUS_OPEN,
                    'task_type' => AdminMessageTask::$task_type_m
                ])
                ->asArray()
                ->all();
            foreach ($adminMessageTask as $value) {
                $arr = LoanCollectionOrder::find()
                    ->alias('cOrder')
                    ->select(['cOrder.current_collection_admin_user_id', 'user.username', 'user.group_game','c' => "SUM(IF(cRecord.risk_control = {$riskControl},1,0))"])
                    ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','cOrder.user_loan_order_repayment_id = repayment.id')
                    ->leftJoin(LoanCollectionRecord::tableName() . ' cRecord', 'cRecord.order_id = cOrder.id')
                    ->leftJoin(AdminUser::tableName() . ' user', ' cOrder.current_collection_admin_user_id = user.id')
                    ->where([
                        'user.outside'  => $value['outside'],
                        'user.group'    => $value['group'],
                    ])
                    ->andWhere([
                        'OR',
                        ['repayment.status' => UserLoanOrderRepayment::STATUS_NORAML],
                        ['AND',['>=','repayment.closing_time',strtotime('today')],['<','repayment.closing_time',strtotime('today') + 86400]]
                    ])
                    ->andWhere(['>', 'cOrder.current_collection_admin_user_id', 0])
                    ->andWhere(['>=', 'cRecord.created_at', $beforeTime])
                    ->andWhere(['<', 'cRecord.created_at', $now])
                    ->groupBy(['cOrder.current_collection_admin_user_id'])
                    ->having(['c' => 0])
                    ->asArray()
                    ->all();

                $countArr = [];
                foreach ($arr as $item){
                    $countArr[$item['group_game']][] = $item['username'];
                }

                foreach ($countArr as $groupGame => $groupGameData){
                    foreach ($groupGameData as $username){
                        $message = "Your team member {$username} has no new promise to repay within one hour, please follow up";
                        $this->saveAdminManageMessage($smallRoles,$bigRoles,$value['outside'],$value['group'],$groupGame,$message);
                    }
                }
            }
        }
        $this->printMessage( "执行完毕");
    }


    //保存消息
    private function saveAdminManageMessage($smallRoles,$bigRoles,$outside,$group,$groupGame,$message){
        $smallTeams = AdminUser::find()
            ->select(['id'])
            ->where([
                'role' => $smallRoles,
                'outside' => $outside,
                'group' => $group,
                'group_game' => $groupGame,
                'open_status' => AdminUser::$usable_status
            ])->asArray()->all();
        if($smallTeams){
            foreach ($smallTeams as $val){
                $adminMessage = new AdminMessage();
                $adminMessage->admin_id = $val['id'];
                $adminMessage->content = $message;
                $adminMessage->save();
                RedisQueue::addSet(RedisQueue::COLLECTION_NEW_MESSAGE_TEAM_TL_UID,$val['id']);
            }
        }

        $bigTeams = AdminManagerRelation::find()
            ->alias('m')
            ->select(['m.admin_id'])
            ->leftJoin(AdminUser::tableName().' u','m.admin_id = u.id')
            ->where([
                'u.role' => $bigRoles,
                'u.outside' => $outside,
                'm.group' => $group,
                'm.group_game' => $groupGame,
                'u.open_status' => AdminUser::$usable_status
            ])->asArray()->all();
        if($bigTeams){
            foreach ($bigTeams as $val){
                $adminMessage = new AdminMessage();
                $adminMessage->admin_id = $val['admin_id'];
                $adminMessage->content = $message;
                $adminMessage->save();
                RedisQueue::addSet(RedisQueue::COLLECTION_NEW_MESSAGE_TEAM_TL_UID,$val['admin_id']);
            }
        }
    }


    private function todayTotalTask($finishApr = false){
        $collectorRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_COLLECTION);
        $smallRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_SMALL_TEAM_MANAGER);
        $bigRoles = AdminUserRole::getRolesByGroup(AdminUserRole::TYPE_BIG_TEAM_MANAGER);
        $finishStatus = LoanCollectionOrder::STATUS_COLLECTION_FINISH;
        $hour = date('H');
        //前手S1  1-3
        $adminMessageTask = AdminMessageTask::find()
            ->where([
                'status' => AdminMessageTask::STATUS_OPEN,
                'task_type' => AdminMessageTask::$task_type_overdue_day
            ])
            ->asArray()->all();

        $arr = [];
        foreach ($adminMessageTask as $value){
            $arr[$value['outside']][$value['group']][$value['task_type']] = $value['task_value'];
        }

        foreach ($arr as $outside => $groupData){
            foreach ($groupData as $group => $value){
                if($group != LoanCollectionOrder::LEVEL_S1_1_3DAY){
                    continue;
                }

                $countArr = [];
                //S1 1-3 账龄
                foreach ($value as $taskType => $taskValue){
                    switch ($taskType){
                        case AdminMessageTask::TASK_TYPE_S_D1:
                            $d1CountArr = LoanCollectionOrder::find()
                                ->alias('order')
                                ->select(['user.group_game','totalCount' => 'COUNT(1)','finishCount' => "SUM(IF(order.status = {$finishStatus},1,0))"])
                                ->leftJoin(AdminUser::tableName().' user','order.current_collection_admin_user_id = user.id')
                                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','order.user_loan_order_repayment_id = repayment.id')
                                ->where(['user.outside' => $outside,'user.group' => $group])
                                ->andWhere(['>=','order.dispatch_time',strtotime('today')])
                                ->andWhere(['<','order.dispatch_time',strtotime('today')+86400])
                                ->andWhere(['repayment.overdue_day' => 1])
                                ->groupBy(['user.group_game'])
                                ->asArray()
                                ->all();
                            foreach ($d1CountArr as $item){
                                $countArr[$item['group_game']][1] = ['total' => $item['totalCount'],'task' => intval($item['totalCount'] * $taskValue / 100),'finish' => $item['finishCount']];
                            }
                            break;
                        case AdminMessageTask::TASK_TYPE_S_D2:
                            $d2CountArr = LoanCollectionOrder::find()
                                ->alias('order')
                                ->select(['user.group_game','totalCount' => 'COUNT(1)','finishCount' => "SUM(IF(order.status = {$finishStatus},1,0))"])
                                ->leftJoin(AdminUser::tableName().' user','order.current_collection_admin_user_id = user.id')
                                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','order.user_loan_order_repayment_id = repayment.id')
                                ->where([
                                    'user.outside' => $outside,
                                    'user.group' => $group,
                                    'repayment.overdue_day' => 2
                                ])
                                ->andWhere([
                                    'OR',
                                    ['repayment.status' => UserLoanOrderRepayment::STATUS_NORAML],
                                    ['AND',['>=','repayment.closing_time',strtotime('today')],['<','repayment.closing_time',strtotime('today') + 86400]]
                                ])
                                ->groupBy(['user.group_game'])
                                ->asArray()
                                ->all();
                            foreach ($d2CountArr as $item){
                                $countArr[$item['group_game']][2] = ['total' => $item['totalCount'],'task' => intval($item['totalCount'] * $taskValue / 100),'finish' => $item['finishCount']];
                            }

                            break;
                        case AdminMessageTask::TASK_TYPE_S_D3:
                            $d3CountArr = LoanCollectionOrder::find()
                                ->alias('order')
                                ->select(['user.group_game','totalCount' => 'COUNT(1)','finishCount' => "SUM(IF(order.status = {$finishStatus},1,0))"])
                                ->leftJoin(AdminUser::tableName().' user','order.current_collection_admin_user_id = user.id')
                                ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','order.user_loan_order_repayment_id = repayment.id')
                                ->where([
                                    'user.outside' => $outside,
                                    'user.group' => $group,
                                    'repayment.overdue_day' => 3
                                ])
                                ->andWhere([
                                    'OR',
                                    ['repayment.status' => UserLoanOrderRepayment::STATUS_NORAML],
                                    ['AND',['>=','repayment.closing_time',strtotime('today')],['<','repayment.closing_time',strtotime('today') + 86400]]
                                ])
                                ->groupBy(['user.group_game'])
                                ->asArray()
                                ->all();
                            foreach ($d3CountArr as $item){
                                $countArr[$item['group_game']][3] = ['total' => $item['totalCount'],'task' => intval($item['totalCount'] * $taskValue / 100),'finish' => $item['finishCount']];
                            }
                            break;
                    }

                }


                foreach ($countArr as $groupGame => $groupGameData){
                    $teamInfo = CompanyTeam::find()->where(['outside' => $outside,'team' => $groupGame])->one();
                    $teamName = AdminUser::$group_games[$groupGame];
                    if($teamInfo){
                        $teamName .= ':'.$teamInfo['alias'];
                    }
                    $startMessage = "Your group {$teamName},today's goal is ";
                    $endMessage = ".Please hold a morning meeting and give your goals";
                    if($finishApr){
                        $startMessage = "As of {$hour} o’clock,Your group {$teamName}";
                        $endMessage = ".To reach today’s goal,";
                    }
                    foreach ($groupGameData as $day => $data){
                        $dCount = $data['task'] ?? 0;
                        if($finishApr){
                            $dFinish = $data['finish'] ?? 0;
                            $dTotal = $data['total'] ?? 0;
                            $dFinishApr = $dTotal > 0 ? sprintf("%0.2f",$dFinish / $dTotal * 100) : '-';

                            $dLess = max($dCount-$dFinish,0);

                            $startMessage .= ",D{$day} repayment rate is {$dFinishApr}";
                            $endMessage .=  ",D{$day} is still {$dLess} orders";
                        }else{
                            $startMessage .= ",D{$day}:{$dCount} order";
                        }
                    }
                    $message = $startMessage.$endMessage;
                    $this->saveAdminManageMessage($smallRoles,$bigRoles,$outside,$group,$groupGame,$message);
                }
            }
        }

        //前手S1 4-7 S2 8-15
        $adminMessageTask = AdminMessageTask::find()
            ->where([
                'status' => AdminMessageTask::STATUS_OPEN,
                'task_type' => AdminMessageTask::$task_type_new
            ])
            ->asArray()->all();

        $arr = [];
        foreach ($adminMessageTask as $value){
            $arr[$value['outside']][$value['group']][$value['task_type']] = $value['task_value'];
        }

        foreach ($arr as $outside => $groupData){
            foreach ($groupData as $group => $value){

                if(!in_array($group, [LoanCollectionOrder::LEVEL_S1_4_7DAY,LoanCollectionOrder::LEVEL_S2])){
                    continue;
                }

                foreach ($value as $taskType => $taskValue){
                    //S1 4-7  s2 账龄
                    $overdueDayLeft =  $group == LoanCollectionOrder::LEVEL_S1_4_7DAY ? 4 : 8;
                    $overdueDayRight =  $group == LoanCollectionOrder::LEVEL_S1_4_7DAY ? 7 : 15;
                    $countArr = [];
                    $dCountArr = LoanCollectionOrder::find()
                        ->alias('order')
                        ->select(['user.group_game','totalCount' => 'COUNT(1)','finishCount' => "SUM(IF(order.status = {$finishStatus},1,0))"])
                        ->leftJoin(AdminUser::tableName().' user','order.current_collection_admin_user_id = user.id')
                        ->leftJoin(UserLoanOrderRepayment::getDbName().'.'.UserLoanOrderRepayment::tableName().' repayment','order.user_loan_order_repayment_id = repayment.id')
                        ->where([
                            'user.outside' => $outside,
                            'user.group' => $group
                        ])
                        ->andWhere(['>=','repayment.overdue_day',$overdueDayLeft])
                        ->andWhere(['<','repayment.overdue_day',$overdueDayRight])
                        ->andWhere([
                            'OR',
                            ['repayment.status' => UserLoanOrderRepayment::STATUS_NORAML],
                            ['AND',['>=','repayment.closing_time',strtotime('today')],['<','repayment.closing_time',strtotime('today') + 86400]]
                        ])
                        ->groupBy(['user.group_game'])
                        ->asArray()
                        ->all();

                    foreach ($dCountArr as $item){
                        $countArr[$item['group_game']] = ['total' => $item['totalCount'],'task' => intval($item['totalCount'] * $taskValue / 100),'finish' => $item['finishCount']];
                    }

                    foreach ($countArr as $groupGame => $groupGameData){
                        $dCount = $groupGameData['task'] ?? 0;
                        $teamInfo = CompanyTeam::find()->where(['outside' => $outside,'team' => $groupGame])->one();
                        $teamName = AdminUser::$group_games[$groupGame];
                        if($teamInfo){
                            $teamName .= ':'.$teamInfo['alias'];
                        }

                        if($finishApr){
                            $dFinish = $groupGameData['finish'] ?? 0;
                            //$dTotal = $groupGameData['total'] ?? 0;
                            //$dFinishApr = $dTotal > 0 ? sprintf("%0.2f",$dFinish / $dTotal * 100) : 0;
                            $dLess = max($dCount-$dFinish,0);

                            $message = "As of {$hour} o’clock,Your group {$teamName} has {$dFinish} repayment orders,{$dLess} order short of today's goal";
                        }else{
                            $message = "Your group {$teamName}, today's goal is {$dCount} orders. Please hold a morning meeting and give your goals";
                        }

                        $this->saveAdminManageMessage($smallRoles,$bigRoles,$outside,$group,$groupGame,$message);
                    }
                }

            }
        }

        //后手M1-7
        $adminMessageTask = AdminMessageTask::find()
            ->where([
                'status' => AdminMessageTask::STATUS_OPEN,
                'task_type' => AdminMessageTask::$task_type_m
            ])
            ->asArray()->all();

        $arr = [];
        foreach ($adminMessageTask as $value){
            $arr[$value['outside']][$value['group']][$value['task_type']] = $value['task_value'];
        }

        foreach ($arr as $outside => $groupData){
            foreach ($groupData as $group => $value){
                if(!in_array($group, LoanCollectionOrder::$after_level)){
                    continue;
                }

                foreach ($value as $taskType => $taskValue){
                    //S1 4-7  s2 账龄

                    $countArr = [];
                    $uCountArr = AdminUser::find()
                        ->select(['group_game','c' => 'COUNT(1)'])
                        ->where([
                            'role' => $collectorRoles,
                            'outside' => $outside,
                            'group' => $group,
                        ])
                        ->groupBy(['group_game'])
                        ->asArray()
                        ->all();

                    foreach ($uCountArr as $item){
                        $countArr[$item['group_game']] = intval($item['c'] * $taskValue);
                    }

                    foreach ($countArr as $groupGame => $dMoney){
                        $teamInfo = CompanyTeam::find()->where(['outside' => $outside,'team' => $groupGame])->one();
                        $teamName = AdminUser::$group_games[$groupGame];
                        if($teamInfo){
                            $teamName .= ':'.$teamInfo['alias'];
                        }
                        if($finishApr){
                            $collectorBackMoney = CollectorBackMoney::find()
                                ->select(['totalMoney' => 'SUM(bm.back_money)'])
                                ->alias('bm')
                                ->leftJoin(AdminUser::tableName().' user','bm.admin_user_id = user.id')
                                ->where(['bm.date' => date('Y-m-d'),'user.outside' => $outside,'user.group' => $group,'user.group_game' => $groupGame])
                                ->asArray()
                                ->one();

                            $dFinish = ($collectorBackMoney['totalMoney'] ?? 0) / 100;
                            $lessMoney = max($dMoney - $dFinish,0);
                            $message = "As of {$hour} o’clock,Your group repayment amount is {$dFinish}Rs,There is still {$lessMoney} to reach today’s goal";
                        }else{
                            $message = "Your team {$teamName}, today’s goal is {$dMoney} amount. Please hold a morning meeting and give your goals";
                        }
                        $this->saveAdminManageMessage($smallRoles,$bigRoles,$outside,$group,$groupGame,$message);
                    }
                }
            }
        }
    }
}
