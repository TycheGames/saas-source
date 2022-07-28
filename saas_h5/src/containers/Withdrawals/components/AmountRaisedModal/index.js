/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-05-19 14:08:28
 * @LastEditTime: 2020-05-19 15:22:26
 * @FilePath: /india_loan/src/containers/Withdrawals/components/AmountRaisedModal/index.js
 */

import PropTypes from "prop-types";
import { useState, useEffect } from "react";
import { Modal } from "antd-mobile";
import NextClick from "../../../../components/Next-click";
import "./index.less";
/**
 * 提额弹窗
 * @param {*} param0 show   是否显示弹窗
 */
const AmountRaisedModal = ({
  show,
  maxMoney,
  applyMoney,
  withdrawalsNowClick,
}) => {
  const [isShow, setIsShow] = useState(show);

  useEffect(() => {
    setIsShow(show);
  }, [show]);
  return (
    <Modal
      visible={isShow}
      maskClosable={false}
      className="amount-raised-modal"
    >
      <div className="amount-raised-modal-con">
        <div className="amount-raised-modal-con-title">Congratulations</div>
        <p>
          Base on your good repayment records, your loan order’s credit amount
          has increased to Rs.{maxMoney}.
        </p>
        <p>Are you sure to increase the disbursal amount to Rs.{maxMoney}?</p>
        <NextClick
          className="next on"
          text={`Yes, Withdraw Rs.${maxMoney}`}
          clickFn={amountRaisedFn}
        />
        <NextClick
          className="next on"
          text={`Withdraw Rs.${applyMoney} Now`}
          clickFn={withdrawNowFn}
        />
      </div>
    </Modal>
  );

  // 提额按钮
  function amountRaisedFn() {
    setIsShow(false);
  }

  // 立即提现
  function withdrawNowFn() {
    withdrawalsNowClick();
  }
};

AmountRaisedModal.propTypes = {
  show: PropTypes.bool.isRequired,  // 是否显示
  maxMoney: PropTypes.string.isRequired,    // 风控通过后最大的金额
  applyMoney: PropTypes.string.isRequired,  // 申请的金额
  withdrawalsNowClick: PropTypes.func.isRequired,   // 立即提现点击
};
export default AmountRaisedModal;
