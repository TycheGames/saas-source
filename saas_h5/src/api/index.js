/* eslint-disable import/prefer-default-export */
/**
 * api接口
 * Created by yaer on 2019/3/5;
 * @Email 740905172@qq.com
 * */

import http, { biHttp } from "../axios";
import getDomainName from "./conf";
import { getAppAttributes } from "../nativeMethod";

const { FRONTEND, BI } = getDomainName();
const { packageName } = getAppAttributes();

const radiomLink = packageName ? `${packageName}!` : "";

/**
 * @param params
 * @returns {*}
 */
export function reportBi(params) {
  return biHttp("post", `${BI}/${radiomLink}report-landing-page`, params);
}

/**
 * 获取基本信息数据
 * @returns {*}
 */
export function getBasicInfo() {
  return http("get", `${FRONTEND}/user/${radiomLink}get-user-basic-info`);
}

/**
 * 保存用户基本信息
 * @param params
 * @returns {*}
 */
export function setBasicInfo(params) {
  return http(
    "post",
    `${FRONTEND}/user/${radiomLink}save-user-basic-info`,
    params
  );
}

/**
 * 获取工作信息
 * @returns {*}
 */
export function getWorkInfo() {
  return http("get", `${FRONTEND}/user/${radiomLink}get-user-work-info`);
}

/**
 * 保存工作信息
 * @param params
 * @returns {*}
 */
export function setWorkInfo(params) {
  return http(
    "post",
    `${FRONTEND}/user/${radiomLink}save-user-work-info`,
    params
  );
}

/**
 * 获取还款方式
 * @returns {*}
 */
export function getRepaymentMethod() {
  return http("get", `/india/${radiomLink}repaymentMethod`);
}

/**
 * 获取银行卡列表
 * @returns {*}
 */
export function getBankAccountNumber() {
  return http(
    "get",
    `${FRONTEND}/user/${radiomLink}get-bank-account-list`,
    {},
    null,
    false
  );
}

/**
 * 获取借款订单列表
 * @param page  页数
 * @param recordsType 还款订单(repayRecords)/借款订单(loanRecords)
 * @returns {*}
 */
export function getLoanOrderList(page, recordsType) {
  return http(
    "post",
    `${FRONTEND}/loan/${radiomLink}loan-order-list`,
    { page },
    "application/x-www-form-urlencoded",
    page === 1
  );
}

/**
 * 获取订单详情
 * @param id  订单id
 * @param showLoading 是否显示loading
 * @returns {*}
 */
export function getOrderDetail(id, showLoading = true) {
  return http(
    "post",
    `${FRONTEND}/loan/${radiomLink}loan-detail`,
    { id },
    "form-data",
    showLoading
  );
  // return http("post",`/india/detail`)
}

/**
 * 保存银行卡信息
 * @param params
 * @returns {*}
 */
export function saveBankAccount(params) {
  return http(
    "post",
    `${FRONTEND}/user/${radiomLink}save-bank-account`,
    params
  );
}

/**
 * 获取征信报告
 */
export function getCreditReport() {
  return http("get", `${FRONTEND}/user/${radiomLink}credit-report-user-info`);
}

/**
 * 获取征信报告OTP验证码
 */
export function getCreditReportOTP() {
  return http("get", `${FRONTEND}/user/${radiomLink}credit-report-get-otp`);
}

/**
 * 提交征信otp
 * @param params  otp
 * @param params  code
 * @returns {*}
 */
export function saveCreditReport(params) {
  return http(
    "post",
    `${FRONTEND}/user/${radiomLink}credit-report-post-otp`,
    params
  );
}

/**
 * 获取税单输入公司页面数据
 */
export function getTaxBillInputCompanyInfo() {
  return http("get", `${FRONTEND}/user/${radiomLink}user-tax-config`);
}

/**
 * 提交税单输入公司页面数据
 * @param param
 * @returns {*}
 */
export function setTaxBillInputCompanyInfo(param) {
  return http(
    "post",
    `${FRONTEND}/user/${radiomLink}collection-user-tax`,
    param
  );
}

/**
 * 税单提交验证码
 * @param params
 * @returns {*}
 */
export function taxBillCheckCode(params) {
  return http("post", `${FRONTEND}/user/${radiomLink}user-tax-otp`, params);
}

/**
 * 税单提交选择的公司
 * @param params
 * @returns {*}
 */
export function setTaxBillCompanyData(params) {
  return http("post", `${FRONTEND}/user/${radiomLink}user-tax-company`, params);
}

/**
 * 税单轮询查税单信息
 * @param params
 * @returns {*}
 */
export function getTaxBillLoopStatus(params) {
  return http(
    "post",
    `${FRONTEND}/user/${radiomLink}user-tax-status`,
    params,
    "application/x-www-form-urlencoded",
    false
  );
}

/**
 * 获取认证状态
 * @returns {*}
 */
export function getAuthStatus() {
  return http("get", `${FRONTEND}/user/${radiomLink}user-identity-auth-status`);
}

/**
 * 生成还款订单
 * @param params
 * @returns {*}
 */
export function saveRepaymentOrder(params) {
  return http("post", `${FRONTEND}/loan/${radiomLink}repayment-apply`, params);
}

/**
 * 生成还款订单 razorpay  payment link
 * @param params
 * @returns {*}
 */
export function saveRepaymentOrderInRazorpayPaymentLink(params) {
  return http(
    "post",
    `${FRONTEND}/loan/${radiomLink}repayment-apply-payment-link`,
    params
  );
}

/**
 * 检验订单状态
 * @param razorpayPaymentId
 * @param razorpayOrderId
 * @param razorpaySignature
 * @returns {*}
 */
export function orderCheck(
  razorpayPaymentId,
  razorpayOrderId,
  razorpaySignature
) {
  return http("post", `${FRONTEND}/loan/${radiomLink}repayment-result`, {
    razorpayPaymentId,
    razorpayOrderId,
    razorpaySignature,
  });
}

/**
 * 获取借款被拒时间
 */
export function getLoanRejectedTime() {
  return http("get", `${FRONTEND}/app/${radiomLink}reject-page`);
}

/**
 * 获取用户委托协议接口
 * @returns {*}
 */
export function getUserCommissionedData() {
  return http("get", `${FRONTEND}/agreement/${radiomLink}user-commissioned`);
}

/**
 * 获取贷款服务合同
 * @param params
 * @returns {*}
 */
export function getLoanServiceData(params) {
  return http(
    "post",
    `${FRONTEND}/agreement/${radiomLink}loan-service`,
    params
  );
}

/**
 * 获取提示下载页面数据
 * @returns {*}
 */
export function getDownloadData() {
  return http("get", `${FRONTEND}/app/${radiomLink}old-version-msg`);
}

/**
 * 获取借款确认页信息
 * @param params
 */
export function getLoanConfirmData(params) {
  return http("post", `${FRONTEND}/loan/${radiomLink}loan-confirm-v2`, params);
}

/**
 * 提交借款信息
 * @returns {*}
 */
export function saveApplyLoan(params) {
  return http("post", `${FRONTEND}/loan/${radiomLink}apply-loan`, params);
}

/**
 * 订单绑定银行卡
 */
export function orderBindCard(params) {
  return http("post", `${FRONTEND}/loan/${radiomLink}order-bind-card`, params);
}

/**
 * 获取ekyc数据
 * @returns {*}
 */
export function getEkycInfo() {
  return http("get", `${FRONTEND}/user/${radiomLink}get-user-ekyc-info`);
}

/**
 * 获取ekyc的otp验证码
 * @returns {*}
 */
export function getEkycOTP(params) {
  return http("post", `${FRONTEND}/user/${radiomLink}get-ekyc-code`, params);
}

/**
 * 保存ekyc数据
 * @param params
 */
export function submitEkycInfo(params) {
  return http("post", `${FRONTEND}/user/${radiomLink}save-user-ekyc`, params);
}

export function getDemandPromissoryNote(params) {
  return http(
    "post",
    `${FRONTEND}/agreement/${radiomLink}demand-promissory-note`,
    params
  );
}

export function getSanctionLetter(params) {
  return http(
    "post",
    `${FRONTEND}/agreement/${radiomLink}sanction-letter`,
    params
  );
}

export function getTestData(params) {
  return http("post", `${FRONTEND}/loan/repayment-apply-test`, params);
}

/**
 * 获取线下还款数据
 * @returns {*}
 */
export function getRepayTransferBankData(params) {
  return http(
    "get",
    `${FRONTEND}/loan/${radiomLink}repay-by-bank-transfer`,
    params
  );
}

/**
 * 获取优惠券信息
 * @returns {*}
 */
export function getCouponList() {
  return http("get", `${FRONTEND}/loan/${radiomLink}coupon-list`);
}

/**
 * 提交代扣信息
 * @returns {*}
 */
export function postWithholding(params) {
  return http("post", `${FRONTEND}/loan/${radiomLink}repay-auth`, params);
}

/**
 * 查询订单状态
 */
export function getOrderStatus(params) {
  return http(
    "post",
    `${FRONTEND}/loan/${radiomLink}order-status`,
    params,
    "application/x-www-form-urlencoded",
    false
  );
}

/**
 * 申请提现接口
 * @param {*} params
 */
export function updateWithdrawals(params) {
  return http("post", `${FRONTEND}/loan/${radiomLink}apply-draw`, params);
}

/**
 * 获取问题认证数据
 */
export function getProblemAuthData() {
  return http("get", `${FRONTEND}/user/${radiomLink}get-user-question-list`);
}

/**
 * 提交问题认证数据
 * @param {*} params
 */
export function saveProblemAuthData(params) {
  return http(
    "post",
    `${FRONTEND}/user/${radiomLink}save-user-question-answer`,
    params,
    "json"
  );
}

/**
 * 获取个人中心页面信息
 */
export function getPersonalCenterInfo() {
  return http("get", `${FRONTEND}/user/get-personal-center-info`);
}

/**
 * 获取用户的信息
 * @param (params) {number}  params.orderId?  // 订单id
 */
export function getUserInfo(params) {
  return http("post", `${FRONTEND}/user/get-user-info`, params);
}

/**
 * 获取投诉页面-》投诉问题
 */
export function getComplaintsProblemList() {
  return http("get", `${FRONTEND}/user/get-complaints-problem`);
}

/**
 * 获取投诉页面=》投诉记录
 */
export function getComplaintsRecordsList() {
  return http(
    "get",
    `${FRONTEND}/user/get-complaints-records`,
    {},
    "application/x-www-form-urlencoded",
    false
  );
}

/**
 * 提交投诉记录
 */
export function saveComplaintsRecords(params) {
  return http("post", `${FRONTEND}/user/save-complaints-records`, params);
}

/**  提交减免信息
 * @param {*} params {string} reductionFee
 * @param {*} params {string} repaymentDate
 * @param {*} params {string} reasons
 * @param {*} params {string} contact
 * @param {*} params {string} orderId
 */
export function submitReduction(params) {
  return http("post", `${FRONTEND}/loan/submit-apply-reduction`, params);
}

/**
 *生成还款订单  cashFree
 * @param {*} params  orderId
 * @param {*} params  amount
 * @param {*} params  customerEmail
 * @param {*} params  customerPhone
 * @param {*} params  paymentType
 */
export function saveRepaymentOrderInCashFree(params) {
  return http(
    "post",
    `${FRONTEND}/loan/${radiomLink}repayment-apply-cash-free`,
    params
  );
}

/**
 * 生成还款订单 mpurse
 * @param {*} params
 */
export const saveRepaymentOrderInMpurse = (params) =>
  http("post", `${FRONTEND}/loan/${radiomLink}repayment-apply-mpurse`, params);

/**
 * 生成还款订单 mpurse upi方式
 * @param {*} params
 */
export const saveRepaymentOrderInMpurseUpi = (params) =>
  http(
    "post",
    `${FRONTEND}/loan/${radiomLink}repayment-apply-mpurse-upi`,
    params
  );

/**
 * 生成还款订单 mojo方式
 * @param {*} params
 */
export const saveRepaymentOrderInMojo = (params) =>
  http("post", `${FRONTEND}/loan/${radiomLink}repayment-apply-mojo`, params);

/**
 * 生成还款订单 sifang方式
 * @param {*} params
 */
export const saveRepaymentOrderInSiFang = (params) =>
  http("post", `${FRONTEND}/loan/${radiomLink}repayment-apply-sifang`, params);
/**
 * 生成还款订单 启明方式
 * @param {*} params
 */
export const saveRepaymentOrderInQiMing = (params) =>
  http("post", `${FRONTEND}/loan/${radiomLink}repayment-apply-qiming`, params);
/**
 * 生成还款订单 quan qiu pay
 * @param {*} params
 */
export const saveRepaymentOrderInQuanQiuPay = (params) =>
  http(
    "post",
    `${FRONTEND}/loan/${radiomLink}repayment-apply-quanqiupay`,
    params
  );

/**
 * 生成还款订单 四方
 * @param {*} params
 */
export const saveRepaymentOrderInRpay = (params) =>
  http("post", `${FRONTEND}/loan/${radiomLink}repayment-apply-rpay`, params);

/**
 * 生成还款订单 Jpay方式
 * @param {*} params
 */
export const saveRepaymentOrderInJpay = (params) =>
  http("post", `${FRONTEND}/loan/${radiomLink}repayment-apply-jpay`, params);

/**
 * 获取银行卡状态
 * @param {*} params
 */
export function getBankCardStatus(params) {
  return http(
    "post",
    `${FRONTEND}/user/${radiomLink}get-bank-account-status`,
    params,
    "form",
    false
  );
}

/**
 * 获取transfer还款数据
 * @returns {*}
 */
export function getTransferData(params) {
  return http(
    "post",
    `${FRONTEND}/loan/${radiomLink}get-transfer-data`,
    params
  );
}
