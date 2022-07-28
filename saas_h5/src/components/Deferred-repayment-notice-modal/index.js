/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2020-03-26 11:33:30
 * @LastEditTime: 2020-05-13 18:21:02
 * @FilePath: /saas_h5/src/components/Deferred-repayment-notice-modal/index.js
 */
import { Modal } from "antd-mobile";
import PropTypes from "prop-types";
import NextClick from "../Next-click";
import "./index.less";

// 延期还款信息展示
const DeferredRepaymentNoticeModal = ({
  show,
  closeModal,
  confirmDeferredRepaymentFunc,
  deferredRepaymentMoney,
  textType,
  days,
  extendDate,
}) => {
  const { title, text, btn } = _setContent(textType);

  return (
    <Modal
      visible={show}
      transparent
      onClose={closeModal}
      className="deferred-repayment-notice-wrapper"
    >
      <div className="deferred-repayment-notice-con">
        <h1
          className="deferred-repayment-notice-con-title"
          dangerouslySetInnerHTML={{ __html: title }}
        />
        <div dangerouslySetInnerHTML={{ __html: text }}></div>
        <NextClick
          className="next on"
          clickFn={confirmDeferredRepaymentFunc}
          text={btn}
        />
      </div>
    </Modal>
  );

  function _setContent() {
    let title = "";
    let text = "";
    let btn = "";
    switch (textType) {
      case "delay":
        title = "Apply for Partial Deferral";
        text = `<p>
        Dear customer, in order to reduce your repayment pressure, you can now
        choose to repay partially of your total repayment.
      </p>
      <p>
        If you pay more than Rs.${deferredRepaymentMoney}, the platform will
        stop launch collection service to you for 7 days as to ease your
        financial pressure.
      </p>
      <p>We wish you a happy life!</p>`;
        btn = `CONFIRM, REPAY ${deferredRepaymentMoney}`;
        break;
      case "reliefOverdue":
        title = "Repayment Discount <span>(Waive 50% on overdue fee) </span>";
        text = `
        <p>Dear customer, here is repayment discount for you. Repay Rs.${deferredRepaymentMoney}, and get 50% discount on overdue fee.
        </p>
        <p>Collection without hassle, help us to serve you better.</p>
        <p>Please remember to repay on time after ${days} days!</p>
        `;
        btn = `Confirm, Repay Rs.${deferredRepaymentMoney}`;
        break;
      case "extend":
        title = "Repayment Extension";
        text = `<p>Base on your good repayment records, we welcome you to apply the loan extension service! </p>
          <p>Dear Customer, repay <span style="color:red;">Rs.${deferredRepaymentMoney}</span> to enjoy ${days} days’ Loan Extension Service! Your repayment due date will be extended to <span style="color:red;">${extendDate}</span>.</p>
          <p>During Loan Extension period, overdue will not be counted and overdue fees will not be calculated. Also, the platform will stop personal collection service during Loan Extension period.</p>`;
        btn = `Confirm, Repay Rs.${deferredRepaymentMoney}`;
        break;
      default:
        title = "";
        text = "";
        btn = "";
    }
    return { title, text, btn };
  }
};

DeferredRepaymentNoticeModal.propTypes = {
  show: PropTypes.bool.isRequired,
  closeModal: PropTypes.func.isRequired,
  deferredRepaymentMoney: PropTypes.oneOfType([
    PropTypes.string,
    PropTypes.number,
  ]).isRequired, // 延期还款金额
  confirmDeferredRepaymentFunc: PropTypes.func.isRequired, // 延期还款确认按钮
  textType: PropTypes.oneOf(["delay", "reliefOverdue", "default", "relief","extend", ""])
    .isRequired,
  extendDate: PropTypes.string.isRequired, // 展期后下次还款时间
  days: PropTypes.number.isRequired, // 展期天数
};

export default DeferredRepaymentNoticeModal;
