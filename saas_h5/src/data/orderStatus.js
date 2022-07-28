/**
 * Created by yaer on 2019/7/9;
 * @Email 740905172@qq.com
 * 订单状态对应标题
 * */


import * as orderStatus from "../enum/orderStatusEnum";

export default [
  {
    status: orderStatus.STATUS_CHECK,
    value: "Under review",
    className: "",
    message:"",
  },
  {
    status: orderStatus.STATUS_WAIT_DEPOSIT,
    value: "Wait Deposit",
    className: "",
    message:"",
  },
  {
    status: orderStatus.STATUS_LOANING,
    value: "In lending",
    className: "",
    message:"",
  },
  {
    status: orderStatus.STATUS_LOAN_COMPLETE,
    value: "Pending Repayment",
    className: "repay",
    message:"",
  },
  {
    status: orderStatus.STATUS_PAYMENT_COMPLETE,
    value: "Success",
    className: "success",
    message:"The repayment is successfully submitted!",
  },
  {
    status: orderStatus.STATUS_OVERDUE,
    value: "Overdue",
    className: "overdue",
    message:"The loan repayment is overdue！",
  },
  {
    status: orderStatus.STATUS_CHECK_REJECT,
    value: "rejected",
    className: "rejected",
    message:"The loan application is rejected!",
  },
  {
    status: orderStatus.STATUS_WITHDRAWAL_TIMEOUT,
    value: "rejected",
    className: "rejected",
    message:"The loan application is rejected!",
  },
  {
    status: orderStatus.STATUS_DEPOSIT_REJECT,
    value: "rejected",
    className: "rejected",
    message:"The loan application is rejected!",
  },
  {
    status: orderStatus.STATUS_LOAN_REJECT,
    value: "rejected",
    className: "rejected",
    message:"The loan application is rejected!",
  },
];
