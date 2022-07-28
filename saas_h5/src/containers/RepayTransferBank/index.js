/**
 * Created by yaer on 2019/10/4;
 * @Email 740905172@qq.com
 * */
import { useState, useEffect } from "react";
import { useDocumentTitle } from "../../hooks";
import ShowPage from "../../components/Show-page";
import OrderDialog from "../../components/OrderDialog";
import { copy } from "../../nativeMethod";
import { getUrlData } from "../../utils/utils";

import { color, borderColor } from "../../vest";
import { getRepayTransferBankData } from "../../api";

import "./index.less";

/**
 * 线下还款
 * @param props
 * @returns {*}
 * @constructor
 *
 * query
 *  amount  剩余还款金额（元）
 * id 订单id
 */
const RepayTransferBank = props => {
  useDocumentTitle(props);

  const { amount, id } = getUrlData(props);

  const [show, setShow] = useState(false);
  const [orderDialogShow, setOrderDialogShow] = useState(true);

  const [data, setData] = useState({
    beneficiaryName: "",
    bankName: "",
    accountNumber: "",
    ifsc: ""
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
                <p>{data.accountNumber}</p>
              </div>
              <div
                className="copy"
                style={_setCopyStyle()}
                onClick={() => copyData(data.accountNumber)}
              >
                COPY
              </div>
            </div>
            <div className="repay-transfer-bank-con-item">
              <div className="repay-transfer-bank-con-item-info">
                <h3>Beneficiary Name</h3>
                <p>{data.beneficiaryName}</p>
              </div>
              <div
                className="copy"
                style={_setCopyStyle()}
                onClick={() => copyData(data.beneficiaryName)}
              >
                COPY
              </div>
            </div>
            <div className="repay-transfer-bank-con-item">
              <div className="repay-transfer-bank-con-item-info">
                <h3>IFSC Code</h3>
                <p>{data.ifsc}</p>
              </div>
              <div
                className="copy"
                style={_setCopyStyle()}
                onClick={() => copyData(data.ifsc)}
              >
                COPY
              </div>
            </div>
            <div className="repay-transfer-bank-con-item">
              <div className="repay-transfer-bank-con-item-info">
                <h3>Bank Name</h3>
                <p>{data.bankName}</p>
              </div>
            </div>
          </div>
        </div>
        <OrderDialog
          show={orderDialogShow}
          title=""
          message={`<p>The following repayment account is only used for repayment of the
          current loan order. After the order is successfully repaid, do not
          transfer money again to this account!</p>`}
          callback={() => setOrderDialogShow(false)}
        />
      </div>
    </ShowPage>
  );

  function getData() {
    getRepayTransferBankData({ id }).then(res => {
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
