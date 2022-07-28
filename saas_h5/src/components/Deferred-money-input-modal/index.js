/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2020-03-26 13:37:30
 * @LastEditTime: 2020-03-27 16:14:26
 * @FilePath: /india_loan/src/components/Deferred-money-input-modal/index.js
 */
import { Modal, Toast } from "antd-mobile";
import PropTypes from "prop-types";
import NextClick from "../Next-click";
import "./index.less";
import { useState, useEffect } from "react";
import { getNum } from "../../utils/utils";

// 延期还款金额输入
const DeferredMoneyInputModal = ({
  show,
  closeModal,
  money,
  confirmRepayment,
  clearNowSelectData
}) => {
  const [value, setValue] = useState(money);

  useEffect(() => {
    setValue(money);
  }, [money]);
  return (
    <Modal
      visible={show}
      maskClosable={false}
      transparent
      onClose={closeModal}
      className="deferred-money-input-modal-wrapper"
    >
      <div className="deferred-money-input-modal-con">
        <div className="deferred-money-input-info">
          <h1>Repayment Amount</h1>
          <div className="deferred-money-input-info-con">
            <div className="deferred-money-input-info-con-operation">
              <span
                onClick={() => {
                  const v = getNum(value, "sub");
                  if (v < Number(money)) return;
                  setValue(() => v);
                }}
              >
                -
              </span>
              <span
                onClick={() => {
                  const v = getNum(value, "add");
                  setValue(() => (v > 10000 ? 10000 : v));
                }}
              >
                +
              </span>
            </div>
            <input
              type="number"
              value={value}
              onChange={e => {
                const v = Number(e.target.value);
                setValue(v > 10000 ? 10000 : v === 0 ? "" : v);
              }}
            />
          </div>
        </div>
        <div className="deferred-money-input-btn-wrapper">
          <NextClick
            className="btn-cancel btn next on"
            text="Cancel"
            hasStyle={false}
            clickFn={() => {
              clearNowSelectData && clearNowSelectData();
              closeModal();
            }}
          />
          <NextClick
            className="btn-repayment btn next on"
            text="REPAYMENT"
            clickFn={() => {
              if (Number(value) < Number(money)) {
                Toast.info(`The amount cannot be less than ${money}`);
                setValue(money);
                return;
              }
              confirmRepayment(value);
              closeModal();
            }}
            hasBg={false}
          />
        </div>
      </div>
    </Modal>
  );
};

DeferredMoneyInputModal.propTypes = {
  show: PropTypes.bool.isRequired,
  money: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
  confirmRepayment: PropTypes.func.isRequired,
  closeModal: PropTypes.func.isRequired,
  clearNowSelectData: PropTypes.func
};

export default DeferredMoneyInputModal;
