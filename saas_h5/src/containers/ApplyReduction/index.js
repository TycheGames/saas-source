/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-04-17 14:10:30
 * @LastEditTime: 2020-04-24 15:43:57
 * @FilePath: /saas_h5/src/containers/ApplyReduction/index.js
 */

import { getUrlData } from "../../utils/utils";
import InputText from "./components/InputText";
import NextClick from "../../components/Next-click";
import {useDocumentTitle} from "../../hooks";
import { submitReduction } from "../../api";
import "./index.less";
import { useState, useEffect } from "react";
import { nativeType } from "../../nativeMethod";

/**
 * 减免申请
 * @param {*} query orderId 订单id
 */
const ApplyReduction = (props) => {
  useDocumentTitle(props);

  const { orderId } = getUrlData(props);

  const [reductionFee, setReductionFee] = useState("");
  const [repaymentDate, setRepaymentDate] = useState("");
  const [reasons, setReasons] = useState("");
  const [contact, setContact] = useState("");

  const [isSubmit, setIsSubmit] = useState(false);

  useEffect(() => {
    if (!reductionFee || !repaymentDate || !reasons) {
      setIsSubmit(false);
      return;
    }
    setIsSubmit(true);
  }, [reductionFee, repaymentDate, reasons]);

  const inputList = [
    {
      label: "Apply Reduction Fee",
      placeholder: "Fill reduction fee that you want to apply",
      type: "Number",
      value: reductionFee,
      onChange: (val) => setReductionFee(val),
    },
    {
      label: "Assume Repayment Date",
      placeholder: "Fill your promising repayment date",
      inputType: "date",
      value: repaymentDate,
      onChange: (date) => {
        if (date.getTime() < new Date().getTime() - 86400000) {
          date = new Date();
          return;
        }
        setRepaymentDate(
          `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()}`
        );
      },
    },
    {
      label: "My Reasons to Apply Reduction",
      placeholder: "Please Describe more than 10 words!",
      inputType: "textareal",
      value: reasons,
      onChange: (val) => setReasons(val),
    },
    {
      label: "Your Contact (Optional)",
      placeholder: "Fill your phone number or email address",
      type: "Number",
      value: contact,
      onChange: (val) => setContact(val),
    },
  ];

  return (
    <div className="apply-reduction-wrapper">
      {/* <InputText label="" type="input" value="" onchange={() => {}} /> */}
      {inputList.map((data, index) => (
        <InputText
          label={data.label}
          type={data.type}
          inputType={data.inputType}
          value={data.value}
          onChange={data.onChange}
          placeholder={data.placeholder}
          key={index}
        />
      ))}
      <NextClick
        className={`next ${isSubmit ? "on" : ""}`}
        text="SUBMIT"
        clickFn={submit}
      />
    </div>
  );

  function submit() {
    if (!isSubmit) return;

    submitReduction({
      reductionFee,
      repaymentDate,
      reasons,
      contact,
      orderId,
    }).then((res) => {
      nativeType({
        path: "/app/back",
      });
      nativeType({
        path: "/h5/webview",
        url: `${window.originPath}applyReductionResult`,
        isFinishPage: false,
      });
    });
  }
};

export default ApplyReduction;
