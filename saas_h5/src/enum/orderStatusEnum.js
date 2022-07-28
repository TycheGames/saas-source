/*
 * @Author: Always
 * @LastEditors  : Always
 * @email: 740905172@qq.com
 * @Date: 2019-09-27 21:11:26
 * @LastEditTime : 2020-01-06 10:41:37
 * @FilePath: /saas_h5/src/enum/orderStatusEnum.js
 */
/**
 * Created by yaer on 2019/9/21;
 * @Email 740905172@qq.com
 * */


export const STATUS_CHECK = 10; // 审核中

export const STATUS_WAIT_DEPOSIT = 20;  // 待绑卡

export const STATUS_WITHDRAWAL_TIMEOUT = -21;   // 提现驳回

export const STATUS_LOANING = 30; // 放款中

export const STATUS_LOAN_COMPLETE = 40; // 已放款

export const STATUS_PAYMENT_COMPLETE = 50; // 已还款

export const STATUS_OVERDUE = 100; // 逾期中

export const STATUS_CHECK_REJECT = -10; // 审核驳回

export const STATUS_DEPOSIT_REJECT = -20; // 提现驳回

export const STATUS_LOAN_REJECT = -30;  // 放款驳回