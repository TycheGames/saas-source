/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-12-03 16:10:54
 * @LastEditTime: 2020-12-17 14:47:53
 * @FilePath: /saas_h5/src/containers/MpurseUpiRepayment/index.js
 */
import React, { useState, useEffect } from "react";
import { getUrlData } from "../../utils/utils";
import { saveRepaymentOrderInMpurseUpi } from "../../api";
import NextClick from "../../components/Next-click";
import { nativeType } from "../../nativeMethod";
import "./index.less";
import { useDocumentTitle } from "../../hooks";

export default (props) => {
  useDocumentTitle(props);
  const { amount, orderId, paymentType } = getUrlData(props);
  const [phone, setPhone] = useState("");
  const [name, setName] = useState("");
  const [upiAccount, setUpiAccount] = useState("");

  const [canSubmit, setCanSubmit] = useState(false);

  const submit = () => {
    if (!canSubmit) return;
    saveRepaymentOrderInMpurseUpi({
      amount,
      paymentType,
      orderId,
      customerName: name,
      customerPhone: phone,
      customerUpiAccount: upiAccount,
    }).then(() => {
      nativeType({
        path: "/h5/webview",
        url: `${window.originPath}mpurseUpiRepaymentSuccess`,
        isFinishPage: true,
      });
    });
  };

  useEffect(() => {
    setCanSubmit(phone && name && upiAccount);
  }, [phone, name, upiAccount]);
  return (
    <div className="upi-repayment-wrapper">
      <div className="item">
        <span className="label">Mobile Number</span>
        <input
          type="number"
          className="value"
          value={phone}
          onChange={(e) => {
            setPhone(e.target.value);
          }}
        />
      </div>
      <div className="item">
        <span className="label">Actual Name</span>
        <input
          type="text"
          className="value"
          value={name}
          onChange={(e) => {
            setName(e.target.value);
          }}
        />
      </div>
      <div className="item">
        <span className="label">UPI Account</span>
        <input
          type="text"
          className="value"
          value={upiAccount}
          onChange={(e) => {
            setUpiAccount(e.target.value);
          }}
        />
      </div>

      <NextClick
        clickFn={submit}
        className={`next ${canSubmit ? "on" : ""}`}
        text="COUTINUE"
        hasBg={true}
        hasStyle={true}
      />
    </div>
  );
};
