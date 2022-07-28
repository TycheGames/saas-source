/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2019-11-15 16:24:37
 * @LastEditTime: 2020-06-04 14:19:33
 * @FilePath: /saas_h5/src/hooks/index.js
 */

import { useEffect, useRef, useState } from "react";
import { getOrderStatus } from "../api";
import { nativeType } from "../nativeMethod";
import { STATUS_PAYMENT_COMPLETE } from "../enum/orderStatusEnum";

export function useDocumentTitle(props) {
  useEffect(() => {
    document.title = props.params.title;
  }, []);
}

/**
 * 轮询订单状态
 * @param {*} orderId   订单id
 * @param {*} targetStatus  目标状态
 * @param {*} startRequest  是否开始请求
 * @param {*} startStatus 初始状态
 * @param {*} callback  状态刷新后的回调
 * @param {*} timeOutNum  延迟时间
 */
export function useRefreshStatus(
  orderId,
  targetStatus = null,
  startRequest = true,
  startStatus = null,
  callback,
  timeOutNum = 5000
) {
  const AUDIT_TIMER = useRef(null); // 定时器
  const [orderStatus, setOrderStatus] = useState(null); // 订单状态

  // 根据传入的初始值进行设置默认status
  useEffect(() => {
    setOrderStatus(startStatus);
  }, [startStatus]);

  useEffect(() => {
    // 判断是否需要请求
    if (startRequest) {
      refreshStatus();
    }

    return () => {
      AUDIT_TIMER.current && clearTimeout(AUDIT_TIMER.current);
    };
  }, [orderStatus, startRequest]);

  function refreshStatus() {
    AUDIT_TIMER.current = setInterval(getStatus(), timeOutNum);
  }

  /**
   * 获取当前状态
   */
  function getStatus() {
    getOrderStatus({ id: orderId }).then((res) => {
      // 如果当前状态是还款成功，则直接清除定时器
      const status = res.data.status;
      if (orderStatus === null && status === STATUS_PAYMENT_COMPLETE) {
        clearInterval(AUDIT_TIMER.current);
        return;
      }
      if (orderStatus !== null) {
        /**
         * 有两种情况：
         * 1.没有目标状态，则如果当前的状态跟我缓存的状态不一样，代表要刷新tabbar并且停止定时器
         * 2.有目标状态，如果当前状态跟我缓存的状态不一样，并且当前状态等于目标状态，代表要刷新tabbar并且停止定时器
         */
        if (
          status !== orderStatus &&
          (targetStatus ? status === targetStatus : 1)
        ) {
          nativeType({ path: "/main/refresh_tablist" });
          callback && callback();
          clearTimeout(AUDIT_TIMER.current);
        }
      } else {
        setOrderStatus(status);
      }
    });
    return getStatus;
  }
}

/**
 * 请求最新的状态
 * @param {*} orderId
 * @param {*} target
 */
export function requestOrderStatus(orderId, target) {
  return new Promise((response) => {
    getOrderStatus({ id: orderId, target }).then((res) => {
      response(res);
    });
  });
}


/**
 * 倒计时hooks
 * @param {*} type 基准值（例如某个弹窗的显示状态）
 * @param {*} callback 倒计时执行函数
 * @param {*} delay 执行间隔
 */
export function useInterval(type, callback, delay = 1000) {
  const cb = useRef();
  const timer = useRef();

  // 保存上次要执行的cb
  useEffect(() => {
    cb.current = callback;
  });

  useEffect(() => {
    timer.current = setInterval(() => {
      // 执行cb传递定时器id
      cb.current(timer.current);
    }, delay);
    return () => clearInterval(timer.current);
  }, [delay, type]);

  return timer.current;
}
