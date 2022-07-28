/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2020-05-18 14:58:17
 * @LastEditTime: 2020-05-18 17:34:02
 * @FilePath: /india_loan/src/containers/Withdrawals/components/AmountRaisedMsg/index.js
 */

import PropTypes from "prop-types";
import "./index.less";
import { useEffect, useState } from "react";
import { useInterval } from "../../../../hooks";
import { formatDuring, getUrlData } from "../../../../utils/utils";

const AmountRaisedMsg = ({ applyMoney, countdown, cb }) => {
  const [time, setTime] = useState(countdown);
  const timer = useInterval(null, () => {
    setTime(time - 1);
  });
  console.log(formatDuring(time * 1000));
  useEffect(() => {
    if (time === 0) {
      clearInterval(timer);
      cb();
    }
  }, [time]);
  return (
    <div className="amount-raised-msg-wrapper">
      <h1 className="count-down">{formatDuring(time * 1000)}</h1>
      <p className="apply-money">Disburse Rs.{applyMoney} in processing...</p>
      <p className="msg">
        You get the chance to increase disbursal amount within 1 hour.&nbsp;
        <span>
          If you give up to withdraw higher amount, the lender will
          automatically disburse Rs.{applyMoney} to your account within 1 hour!
        </span>
      </p>
    </div>
  );
};

AmountRaisedMsg.propTypes = {
  applyMoney: PropTypes.string.isRequired,
  countdown: PropTypes.number.isRequired,
  cb: PropTypes.func.isRequired,
};

export default AmountRaisedMsg;
