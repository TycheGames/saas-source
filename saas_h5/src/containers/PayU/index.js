/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2021-01-14 17:09:28
 * @LastEditTime: 2021-02-20 15:02:31
 * @FilePath: /saas_h5/src/containers/PayU/index.js
 */
import React from "react";
import { useDocumentTitle } from "../../hooks";
import "./index.less";
import { nativeType } from "../../nativeMethod";
import { getUrlData } from "../../utils/utils";

const PayU = (props) => {
  useDocumentTitle(props);
  const { method } = getUrlData(props);

  const url = () => {
    switch (method) {
      case "payUplus":
        return "https://pmny.in/3IDNJLuEqPKv";
    }
  };
  return (
    <div className="payu-wrapper">
      <img
        src={require("../../images/payU/payU_bg.png")}
        alt=""
        className="bg"
      />
      <p className="msg">
        Please enter your registered mobile phone number in the last block!
      </p>
      <div
        className="btn"
        onClick={() => {
          nativeType({
            path: "/app/open_browser",
            url: url(),
          });
        }}
      >
        REPAYMENT
      </div>
    </div>
  );
};
export default PayU;
