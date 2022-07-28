/*
 * @Author: Always
 * @LastEditors  : Always
 * @email: 740905172@qq.com
 * @Date: 2019-12-29 16:11:31
 * @LastEditTime : 2020-01-30 13:36:26
 * @FilePath: /india_loan/src/containers/Order/components/OrderDialog/index.js
 */

import PropTypes from "prop-types";
import { Modal } from "antd-mobile";
import NextClick from "../Next-click";

import "./index.less";

const OrderDialog = ({ show, title, message, callback }) => {
  return (
    <Modal
      visible={show}
      transparent
      className="order-dialog-wrapper"
      onClose={()=>callback(false)}
    >
      <div className="order-dialog">
        <h1 className="order-dialog-title">{title}</h1>
        <div
          className="order-dialog-message"
          dangerouslySetInnerHTML={{
            __html: message
          }}
        />
        <NextClick text="I GOT IT" className="next on" clickFn={callback} />
      </div>
    </Modal>
  );
};

OrderDialog.defaultProps = {
  callback: () => {}
};

OrderDialog.propTypes = {
  show: PropTypes.bool.isRequired,
  title: PropTypes.string.isRequired,
  message: PropTypes.string.isRequired,
  callback: PropTypes.func
};

export default OrderDialog;
