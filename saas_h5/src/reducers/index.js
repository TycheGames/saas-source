/**
 * redux规则出口
 * Created by yaer on 2019/3/4;
 * @Email 740905172@qq.com
 * */


import { combineReducers } from "redux";
import * as taxBill from "./TaxBill";
import * as coupon from "./coupon";


export default combineReducers(Object.assign({}, taxBill,coupon));
