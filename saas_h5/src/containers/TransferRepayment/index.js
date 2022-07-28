/**
 * Created by yaer on 2019/10/4;
 * @Email 740905172@qq.com
 * */
import { useState, useEffect } from "react";
import { useDocumentTitle } from "../../hooks";
import ShowPage from "../../components/Show-page";
import { copy } from "../../nativeMethod";
import { getUrlData } from "../../utils/utils";
import { Modal } from "antd-mobile";

import { color, borderColor } from "../../vest";
import { getTransferData } from "../../api";

import "./index.less";

/**
 * 线下还款
 * @param props
 * @returns {*}
 * @constructor
 *
 * query
 *  amount  剩余还款金额（元）
 *  orderId
 */
const RepayTransferBank = (props) => {
  useDocumentTitle(props);

  const { amount, orderId } = getUrlData(props);

  const [show, setShow] = useState(false);
  const [showToast, setShowToast] = useState(true);

  const [data, setData] = useState({
    name: "",
    account_number: "",
    ifsc_code: "",
    orderUUId: "",
  });

  useEffect(() => {
    getData();
  }, []);

  return (
    <ShowPage show={show}>
      <div className="repay-transfer-bank-wrapper">
        <div className="repay-transfer-bank">
          <p className="msg">Repay by Bank Transfer</p>
          <div className="repayment-amount">
            <p className="repayment-title">Repayment Amount</p>
            <h1 style={color()}>₹ {_setAmountFormat(amount)}</h1>
          </div>
          <div className="line" />
          <div className="repay-transfer-bank-con">
            <div className="repay-transfer-bank-con-item">
              <div className="repay-transfer-bank-con-item-info">
                <h3>Account Number</h3>
                <p>{data.account_number}</p>
              </div>
              <div
                className="copy"
                style={_setCopyStyle()}
                onClick={() => copyData(data.account_number)}
              >
                COPY
              </div>
            </div>
            <div className="repay-transfer-bank-con-item">
              <div className="repay-transfer-bank-con-item-info">
                <h3>Beneficiary Name</h3>
                <p>{data.name}</p>
              </div>
              <div
                className="copy"
                style={_setCopyStyle()}
                onClick={() => copyData(data.name)}
              >
                COPY
              </div>
            </div>
            <div className="repay-transfer-bank-con-item">
              <div className="repay-transfer-bank-con-item-info">
                <h3>IFSC Code</h3>
                <p>{data.ifsc_code}</p>
              </div>
              <div
                className="copy"
                style={_setCopyStyle()}
                onClick={() => copyData(data.ifsc_code)}
              >
                COPY
              </div>
            </div>
            <div className="repay-transfer-bank-con-item">
              <div className="repay-transfer-bank-con-item-info">
                <h3>Remark</h3>
                <p>Order ID: {data.orderUUId || ""}</p>
              </div>
              <div
                className="copy"
                style={_setCopyStyle()}
                onClick={() => copyData(data.orderUUId)}
              >
                COPY
              </div>
            </div>
          </div>
        </div>
      </div>
      <Modal transparent visible={showToast} maskClosable={false}>
        <div className="default-modal toast-modal">
          <p className="msg">
            1. The bank card account number is only used for the repayment of
            the current order.
          </p>
          <p className="msg">
            2. Each loan order has a unique corresponding repayment account;
            please check the bank account in the order details of the App
          </p>
          <p className="msg">3. Do not transfer to the wrong bank account</p>
          <div
            className="btn"
            onClick={() => {
              setShowToast(false);
            }}
          >
            OK
          </div>
        </div>
      </Modal>
    </ShowPage>
  );

  function getData() {
    getTransferData({ orderId }).then((res) => {
      setData(res.data);
      setShow(true);
    });
  }

  /**
   * copy
   * @param text
   */
  function copyData(text) {
    copy(text, "copied");
  }

  /**
   * 设置显示金额样式
   * @param amount
   * @returns {string}
   * @private
   */
  function _setAmountFormat(amount) {
    return Number(amount).toFixed(2);
  }

  /**
   * 设置copy样式
   * @returns {{color, borderColor}}
   * @private
   */
  function _setCopyStyle() {
    return { ...color(), ...borderColor() };
  }
};

export default RepayTransferBank;
