/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2019-12-11 11:37:47
 * @LastEditTime: 2019-12-11 17:26:28
 * @FilePath: /india_loan/src/containers/ApplyLoan/component/ProgressModal/index.js
 */
import { useEffect, useState, useRef } from "react";
import { Modal } from "antd-mobile";
import PropTypes from "prop-types";

import "./index.less";

window.requestAnimationFrame =
  window.requestAnimationFrame ||
  function(a) {
    return setTimeout(a, 1000 / 60);
  };
window.cancelAnimationFrame = window.cancelAnimationFrame || clearTimeout;

/**
 * 进度条弹窗
 * @param {*} props
 */
const ProgressModal = props => {
  const { show,cancelAnimationFrame } = props;
  
  const requestRef = useRef();

  const [progressVal, setProgressVal] = useState(0);

  let startTime = Date.now();

  /**
   * 动画效果
   * @param {*} totalTime 总耗时
   * @param {*} start 开始值
   * @param {*} end 结束值
   * @param {*} cb 结束后回调，可开启第二次定时器
   */
  const animate = (totalTime, start, end, cb) => {
    // 计算当前的时间距离开始时间的值，最大为限制的总耗时
    let delta = Math.min(totalTime, Date.now() - startTime);
    // 计算进度
    let value = (delta / totalTime) * (end - start) + start;
    setProgressVal(value);
    // 判断当前耗时是否达到阈值 达到了则清除定时器，否则继续运行
    if (delta < totalTime) {
      requestRef.current = requestAnimationFrame(
        animate.bind(this, totalTime, start, end, cb)
      );
    } else {
      cancelAnimationFrame(requestRef.current);
      // 清除定时器后，重置开始时间，为了给第二次定时器启用
      startTime = Date.now();
      typeof cb === "function" && cb("cancelAnimationFrame");
    }
  };

  useEffect(() => {
    // 根据是否显示来判断是否开启定时器
    if (show) {
      requestRef.current = requestAnimationFrame(
        animate.bind(this, 10000, 0, 80, () => {
          requestRef.current = requestAnimationFrame(
            animate.bind(this, 10000, 81, 100,cancelAnimationFrame)
          );
        })
      );
    } else {
      requestRef.current && cancelAnimationFrame(requestRef.current);
    }
    return () => cancelAnimationFrame(requestRef.current);
  }, [show]);

  return (
    <Modal
      transparent
      visible={show}
      maskClosable={false}
      className="progress-modal"
    >
      <div className="progress-modal-con">
        <h1 className="title">
          Checking Eligibilty... &nbsp;
          <span className="progress-val">{progressVal.toFixed(0)}%</span>
        </h1>
        <div className="progress-bg">
          <div className="progress-now" style={{ width: `${progressVal}%` }}>
            <div className="progress-all" />
          </div>
        </div>
        <p className="msg">
          The patform is checking your eligibilty now. Once approved, we will
          deposit funds into your bank account.{" "}
        </p>
      </div>
    </Modal>
  );
};

ProgressModal.propTypes = {
  show: PropTypes.bool.isRequired,
  cancelAnimationFrame:PropTypes.func.isRequired
};

export default ProgressModal;
