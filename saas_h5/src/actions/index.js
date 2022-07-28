/* eslint-disable import/prefer-default-export */
/**
 * 规则执行方法
 * Created by yaer on 2019/3/4;
 * @Email 740905172@qq.com
 * */
import * as actionType from "../constants";

export function test(data) {
  return {
    type: actionType.TEXT,
    data,
  };
}


/**
 * 设置税单是否重复输入
 * @param value {boolean}
 * @returns {{type: string, value: boolean}}
 */
export function setIsTaxBillRepeatInput(value) {
  return {
    type: actionType.IS_TAX_BILL_REPEAT_INPUT,
    value,
  };
}

/**
 * 设置税单是否重复输入过
 * @param value {boolean}
 * @returns {{type: string, value: bollean}}
 */
export function setIsTaxBillRepeatInputTwo(value) {
  return {
    type: actionType.IS_TAX_BILL_REPEAT_INPUT_TWO,
    value,
  };
}

/**
 * 设置reportid
 * @param value
 * @returns {{type: string, value: *}}
 */
export function setReportId(value) {
  return {
    type: actionType.REPORT_ID,
    value,
  };
}

/**
 * 设置公司信息
 * @param data
 * @returns {{type: string, data: *}}
 */
export function setCompanyData(data) {
  return {
    type: actionType.SAVE_COMPANY_DATA,
    data,
  };
}

/**
 * 设置优惠券信息
 * @param data
 * @returns {{type: string, data: *}}
 */
export function saveCouponData(data) {
  return {
    type: actionType.SAVE_COUPON_DATA,
    data,
  }
}