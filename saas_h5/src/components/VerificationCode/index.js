/**
 * Created by yaer on 2019/7/19;
 * @Email 740905172@qq.com
 * */

import { useState, useEffect } from "react";
import PropTypes from "prop-types";
import "./index.less";
import { setClassName } from "../../utils/utils";

const VerificationCode = (props) => {
  const { countDownTime, getCodeFn } = props;
  const [isClick, setIsClick] = useState(false);
  const [time, setTime] = useState(countDownTime);
  let timer = null;
  useEffect(() => {
    // countDown();
    return () => {
      timer && clearTimeout(timer);
    };
  }, []);
  return (
    <div
      className={setClassName([
        "verification-code",
        isClick ? "on" : "",
      ])}
      onClick={start}>
      {isClick ? `${time} S` : "SEND OTP"}
    </div>
  );

  function start() {
    if (isClick) return;
    getCodeFn(countDown);
  }

  function countDown() {
    if (isClick) return;
    setIsClick(true);
    let t = countDownTime - 1;
    timer = setInterval(() => {
      if (t < 0) {
        setIsClick(false);
        clearInterval(timer);
        setTime(countDownTime);
        t = null;
      } else {
        setTime(t--);
      }
    }, 1000);
  }
};

VerificationCode.propTypes = {
  countDownTime: PropTypes.number,
  getCodeFn: PropTypes.func.isRequired,
};

VerificationCode.defaultProps = {
  countDownTime: 60,
};

export default VerificationCode;
