/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-12-03 17:38:50
 * @LastEditTime: 2020-12-17 14:53:46
 * @FilePath: /saas_h5/src/containers/MpurseUpiRepaymentSuccess/index.js
 */
import React from "react";
import { Icon } from "antd-mobile";
import { nativeType } from "../../nativeMethod";
import NextClick from "../../components/Next-click";
import "./index.less";
import { useDocumentTitle } from "../../hooks";
export default (props) => {
  useDocumentTitle(props);
  return (
    <div className="success-wrapper">
      <Icon type="check-circle" className="success-icon" />
      <p className="msg">
        Your payment is successful commit. Please check your SMS and pay
        immedialely.
      </p>
      <NextClick
        clickFn={() => {
          nativeType({
            path: "/app/back",
          });
        }}
        className="next on"
        hasBg={true}
        text="COUTINUE"
        hasStyle={true}
      />
    </div>
  );
};
