/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2020-03-30 14:25:09
 * @LastEditTime: 2020-03-30 14:30:21
 * @FilePath: /india_loan/src/containers/SuspendLending/index.js
 */

import suspendLendingImg from "../../images/suspendLending/suspendLending_img.png";

import "./index.less";

/**
 * 暂停借款
 */
const SuspendLending = () => {
  return (
    <div className="suspend-lending-wrapper">
      <img src={suspendLendingImg} alt="" />
    </div>
  );
};

export default SuspendLending;
