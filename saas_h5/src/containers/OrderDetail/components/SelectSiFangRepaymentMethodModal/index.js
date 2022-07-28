/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-12-03 15:35:09
 * @LastEditTime: 2021-01-15 15:06:31
 * @FilePath: /saas_h5/src/containers/OrderDetail/components/SelectSiFangRepaymentMethodModal/index.js
 */
import React, { useImperativeHandle, useState } from "react";
import { Modal, Button } from "antd-mobile";
import "./index.less";
import { bgAndFc } from "../../../../vest";

const SelectSiFangRepaymentMethodModal = ({
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
      className="select-sifang-repayment-method-wrapper"
      onClose={closeFn}
    >
      <div className="method-wrapper">
        <h1 className="select-method-title">Select Method of Payment</h1>
        <div className="select-method">
          {[
            {
              label: "Paytm UPI Transfer",
              value: "5",
            },
            { label: "PhonePe UPI Transfer", value: "7" },
            { label: "India Bank Transfer", value: "8" },
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

export default SelectSiFangRepaymentMethodModal;
