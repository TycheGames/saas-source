/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-12-03 15:35:09
 * @LastEditTime: 2020-12-03 16:00:10
 * @FilePath: /india_loan/src/containers/OrderDetail/components/SelectMpurseRepaymentMethodModal/index.js
 */
import React, { useImperativeHandle, useState } from "react";
import { Modal, Button } from "antd-mobile";
import "./index.less";
import { bgAndFc } from "../../../../vest";

const SelectMpurseRepaymentMethodModal = ({
  cref,
  selectRepaymentMethod,
  close,
}) => {
  useImperativeHandle(cref, () => ({
    changeShow: (type) => setShowType(type),
  }));
  const [showType, setShowType] = useState(false);
  const closeFn = () => {
    setShowType(false);
    close();
  };
  return (
    <Modal
      visible={showType}
      transparent
      className="select-mpurse-repayment-method-wrapper"
      onClose={closeFn}
    >
      <div className="method-wrapper">
        <h1 className="select-method-title">Select Method of Payment</h1>
        <div className="select-method">
          {[
            {
              label: "BANK",
              value: "mpurse_bank",
            },
            { label: "UPI", value: "mpurse_upi" },
          ].map((item, index) => (
            <Button
              key={index}
              style={Object.assign(bgAndFc())}
              activeClassName="button-active"
              className="select-method-item"
              onClick={() => {
                setShowType(false);
                selectRepaymentMethod(item.value);
              }}
            >
              {item.label}
            </Button>
          ))}
        </div>
      </div>
    </Modal>
  );
};

export default SelectMpurseRepaymentMethodModal;
