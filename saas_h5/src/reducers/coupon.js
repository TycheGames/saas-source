/**
 * Created by yaer on 2019/8/30;
 * @Email 740905172@qq.com
 * */
import * as actionType from "../constants";

/**
 * 设置优惠券信息
 * @param state null 代表没有优惠券  notUse 代表不使用 object 代表选用的优惠券数据
 * @param action
 * @returns {*}
 */
export function couponData(state = null,action) {
  switch (action.type) {
    case actionType.SAVE_COUPON_DATA:
      state =  action.data;
      break;
    default:
      state = null;
  }
  return state;
}