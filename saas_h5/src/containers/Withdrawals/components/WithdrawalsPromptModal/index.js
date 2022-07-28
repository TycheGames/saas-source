/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2019-12-17 11:37:48
 * @LastEditTime: 2020-06-06 18:51:50
 * @FilePath: /saas_h5/src/containers/Withdrawals/components/WithdrawalsPromptModal/index.js
 */
import { useState, useEffect, useRef } from "react";
import { Modal } from "antd-mobile";
import PropTypes from "prop-types";
import NextClick from "../../../../components/Next-click";
import { setClassName } from "../../../../utils/utils";
import { useInterval } from "../../../../hooks";

import "./index.less";

const WithdrawalsPromptModal = (props) => {
  const {
    show,
    confirmClickFn,
    cancelClickFn,
    day,
    amount,
    repaymentDate,
  } = props;
  const { packageName } = window.appInfo;

  const [countDownNum, setCountDownNum] = useState(5);

  // 倒计时hooks
  const timer = useInterval(show, (timer) => {
    if (!show) return;
    if (countDownNum === 0) {
      clearInterval(timer);
      return;
    }
    setCountDownNum(countDownNum - 1);
  });

  useEffect(() => {
    if (!show) {
      setCountDownNum(5);
    }
  }, [show]);

  return (
    <Modal
      visible={show}
      transparent
      maskClosable={false}
      className="withdrawals-prompt-modal"
    >
      <div className="withdrawals-prompt-modal-wrapper">
        <h1 className="title">Loan Confirmation</h1>
        <div className="container">
          <p>Dear Customer:</p>
          <p>
            You are now applying for {packageName}’s {day ? `${day}-day` : ""}{" "}
            Loan Product. For further loan service,{" "}
            <span>
              you must open {packageName} App to Repay Rs {amount} before{" "}
              {repaymentDate}.
            </span>
          </p>
          <p>You will repay in time, right?</p>
        </div>

        <NextClick
          className={setClassName(["next", countDownNum ? "" : "on"])}
          text={`Yes,I Confirm ${countDownNum ? `( ${countDownNum} S )` : ""}`}
          clickFn={() => {
            if (countDownNum) return;
            confirmClickFn();
          }}
        />

        <NextClick
          className="next"
          text="No, Let me think"
          clickFn={cancelClickFn}
        />
      </div>
    </Modal>
  );
};

WithdrawalsPromptModal.propTypes = {
  show: PropTypes.bool.isRequired,
  confirmClickFn: PropTypes.func.isRequired,
  cancelClickFn: PropTypes.func.isRequired,
  day: PropTypes.string.isRequired,
  amount: PropTypes.string.isRequired,
  repaymentDate: PropTypes.string.isRequired,
};

export default WithdrawalsPromptModal;
