/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-05-18 14:34:41
 * @LastEditTime: 2020-05-18 14:58:08
 * @FilePath: /india_loan/src/containers/Withdrawals/components/DecreaseMsg/index.js
 */

import PropTypes from "prop-types";
import "./index.less";

/* 降额提示 */
const DecreaseMsg = ({ money }) => {
  return (
    <div className="decrease-msg-wrapper">
      <p>
        Dear Customer, your current maximum credit amount is Rs.{money} after
        assessment!
      </p>
      <p> Click Withdraw, get money to account in 1 min!</p>
    </div>
  );
};

DecreaseMsg.propTypes = {
  money: PropTypes.string,
};

export default DecreaseMsg;
