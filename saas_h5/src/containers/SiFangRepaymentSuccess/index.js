/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-12-03 17:38:50
 * @LastEditTime: 2021-01-15 14:58:57
 * @FilePath: /india_loan/src/containers/SiFangRepaymentSuccess/index.js
 */
import React from "react";
import { Icon } from "antd-mobile";
import { nativeType } from "../../nativeMethod";
import NextClick from "../../components/Next-click";
import { useDocumentTitle } from "../../hooks";
import "./index.less";
export default (props) => {
  useDocumentTitle(props);
  return (
    <div className="success-wrapper">
      <Icon type="check-circle" className="success-icon" />
      <p className="msg">
        Your repayment is successfully committed. Please check your order
        detail.
      </p>
      <NextClick
        clickFn={() => {
          nativeType({
            path: "/app/back",
          });
        }}
        className="next on"
        hasBg={true}
        text="
        View Order Detail"
        hasStyle={true}
      />
    </div>
  );
};
