import { Modal, Button } from "antd-mobile";
import PropTypes from "prop-types";

import { orderDetailRepaymentModalBg, bgAndFc } from "../../../../vest";

const RepaymentCountDownModalCom = props => {
  const {
    showType,
    closeFn,
    overdueFeeAmount,
    gotItCountDown
  } = props;
  return (
    <Modal
      visible={showType}
      transparent
      className="repayment-modal-reset"
      onClose={closeFn}
    >
      <div
        className="repayment-modal-wrapper"
        style={orderDetailRepaymentModalBg()}
      >
        <h1 className="repayment-modal-title">ATTENTION:</h1>
        <p>
          1. We have issued you the loan by your honesty and credibility, please
          remember to repay in time!
        </p>
        <p>
          2. Once you have overdue repayment, you will be charged
          <span> {overdueFeeAmount || 0} rupees</span> per day.
        </p>
        <div className="hinndi">
          एक बार जब आपके पास अतिदेय भुगतान हो जाता है, तो आपसे प्रति दिन
          {overdueFeeAmount} रुपये का शुल्क लिया जाएगा।
        </div>
        <Button
          className="got-it"
          style={_repaymentModalBtnStyle()}
          activeClassName="button-active"
          onClick={closeFn}
        >
          {gotItCountDown > 0 ? `${gotItCountDown} S` : "Got it!"}
        </Button>
      </div>
    </Modal>
  );

  function _repaymentModalBtnStyle() {
    return gotItCountDown > 0
      ? {
          background: "#999",
          color: "#fff"
        }
      : bgAndFc();
  }
};

RepaymentCountDownModalCom.propTypes = {
  showType: PropTypes.bool.isRequired,  // 显示状态
  gotItCountDown: PropTypes.number.isRequired,  // 倒计时秒数
  closeFn: PropTypes.func.isRequired,   // 关闭方法
  overdueFeeAmount: PropTypes.oneOfType([PropTypes.number, PropTypes.string])
    .isRequired
};

export default RepaymentCountDownModalCom;
