/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2019-12-09 10:18:34
 * @LastEditTime: 2021-04-28 17:39:04
 * @FilePath: /saas_h5/src/containers/OrderDetail/data/repaymentMethodListData.js
 */
import * as repaymentMethod from "../../../enum/repaymentMethodEnum";

export default [
  {
    methodEnum: repaymentMethod.BANK_CARD,
    title: "Bank Card",
    showMethod: true, // 是否显示当前还款方式
    method: "card",
    showUserDataModal: true, // 是否显示填写用户信息modal
    weight: 100, // 还款方式排序权重值
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.RAZORPAY, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.RAZORPAY_REPAYMENT_LINK,
    title: "Payment Link",
    showMethod: true, // 是否显示当前还款方式
    method: "paymentLink",
    showUserDataModal: true, // 是否显示填写用户信息modal
    weight: 40, // 还款方式排序权重值,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.RAZORPAY, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.PAYTM_PHONEPE,
    title: "Paytm / PhonePe",
    showMethod: true,
    method: "upi",
    showUserDataModal: true,
    weight: 80,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.RAZORPAY, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.RAZORPAY,
    title: "Other Payment Methods",
    showMethod: true,
    method: false,
    showUserDataModal: false,
    weight: 40,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.RAZORPAY, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.OFFLINE,
    title: "Bank Transfer",
    showMethod: false,
    showUserDataModal: false,
    weight: 60,
    isOnline: false, // 是否线上还款
    attr: repaymentMethod.RAZORPAY, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.CASH_FREE,
    title: "CashFree",
    showMethod: true,
    method: "cashFree",
    showUserDataModal: true,
    weight: 30,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.CASH_FREE, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.MPURSE,
    title: "Mpurse",
    showMethod: true,
    method: "mpurse",
    showUserDataModal: false,
    weight: 30,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.MPURSE, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.SI_FANG,
    title: "UPI & Bank Transfer",
    showMethod: true, // 是否显示当前还款方式
    showUserDataModal: false, // 是否显示填写用户信息modal
    method: "sifang",
    weight: 100, // 还款方式排序权重值,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.SI_FANG, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.QI_MING,
    title: "Repayment Now",
    showMethod: true, // 是否显示当前还款方式
    showUserDataModal: false, // 是否显示填写用户信息modal
    method: "QiMing",
    weight: 100, // 还款方式排序权重值,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.QI_MING, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.QUAN_QIU_PAY,
    title: "Repayment Now.",
    showMethod: true, // 是否显示当前还款方式
    showUserDataModal: false, // 是否显示填写用户信息modal
    method: "QuanQiuPay",
    weight: 100, // 还款方式排序权重值,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.QUAN_QIU_PAY, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.QUICK_PAYMENT,
    title: "Quick payment",
    showMethod: true, // 是否显示当前还款方式
    showUserDataModal: false, // 是否显示填写用户信息modal
    method: "QuickPayment",
    weight: 100, // 还款方式排序权重值,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.QUICK_PAYMENT, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.MOJO,
    title: "instamojo",
    showMethod: true, // 是否显示当前还款方式
    showUserDataModal: false, // 是否显示填写用户信息modal
    method: "mojo",
    weight: 110, // 还款方式排序权重值,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.MOJO, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.J_PAY,
    title: "JPay",
    showMethod: true, // 是否显示当前还款方式
    showUserDataModal: false, // 是否显示填写用户信息modal
    method: "jpay",
    weight: 100, // 还款方式排序权重值,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.J_PAY, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.TRANSFER,
    title: "Transfer",
    showMethod: true, // 是否显示当前还款方式
    showUserDataModal: false, // 是否显示填写用户信息modal
    method: "transfer",
    weight: 110, // 还款方式排序权重值,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.TRANSFER, // 归属的还款方式
  },
  {
    methodEnum: repaymentMethod.PAY_U_PLUS,
    title: "PayU plus",
    showMethod: false, // 是否显示当前还款方式
    showUserDataModal: false, // 是否显示填写用户信息modal
    method: "payUplus",
    weight: 90, // 还款方式排序权重值,
    isOnline: true, // 是否线上还款
    attr: repaymentMethod.PAY_U_PLUS, // 归属的还款方式
  },
];
