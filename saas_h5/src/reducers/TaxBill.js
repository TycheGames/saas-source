/**
 * Created by yaer on 2019/7/22;
 * @Email 740905172@qq.com
 * */

import * as actionType from "../constants";

/**
 * 设置税单是否重复输入信息
 * @param state
 * @param action
 * @returns {*}
 */
export function taxBillRepeatInput(state = false, action) {
  switch (action.type) {
  case actionType.IS_TAX_BILL_REPEAT_INPUT:
    return action.value;
  default:
    return state;
  }
}

/**
 * 设置税单是否重复输入过
 * @param state
 * @param action
 * @returns {*}
 */
export function taxBillRepeatInputTwo(state = false, action) {
  switch (action.type) {
  case actionType.IS_TAX_BILL_REPEAT_INPUT_TWO:
    return action.value;
  default:
    return state;
  }
}


/**
 * 设置reportid
 * @param state
 * @param action
 * @returns {*}
 */
export function reportId(state = "", action) {
  switch (action.type) {
  case actionType.REPORT_ID:
    return action.value;
  default:
    return state;
  }
}

/**
 * 公司信息
 * @param state
 * @param action
 * @returns {*}
 */
export function companyData(state = {}, action) {
  switch (action.type) {
  case actionType.SAVE_COMPANY_DATA:
    return action.data;
  default:
    return state;
  }
}
