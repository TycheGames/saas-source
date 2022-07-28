/**
 * Created by yaer on 2019/7/8;
 * @Email 740905172@qq.com
 * */
import { useState, useEffect } from "react";
import { Toast } from "antd-mobile";
import "./index.less";
import { useDocumentTitle } from "../../hooks";
import { getBankAccountNumber, changeMainCard } from "../../api";
import ShowPage from "../../components/Show-page";
import { nativeCustomMethod, pageJump } from "../../nativeMethod";
import { setClassName } from "../../utils/utils";

/**
 * 银行卡列表
 * @param props
 * @param (props) isInput 0不能输入/1能输入
 * @returns {*}
 * @constructor
 */
const BankAccountNumber = (props) => {
  useDocumentTitle(props);
  const [showPage, setShowPage] = useState(false);
  const [data, setData] = useState({
    list: [],
    showAddBankCard: false,
  });

  useEffect(() => {
    getData();
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      getData();
    };
  }, []);
  return (
    <ShowPage show={showPage}>
      <div className="bank-account-number-wrapper">
        <ul>
          {
            data.list.map((item, index) => (
              <li className={setClassName([item.canUse ? "" : "no-use"])} key={index}>
                <h1>{item.bankName}</h1>
                <p>{item.account}</p>
              </li>
            ))
          }
        </ul>

        <ShowPage show={data.showAddBankCard}>
          <div className="add" onClick={() => addBankAccount()}>add bank account</div>
        </ShowPage>
      </div>
    </ShowPage>

  );

  function getData() {
    getBankAccountNumber().then((res) => {
      setData(res.data);
      setShowPage(true);
    });
  }

  /**
   * 添加银行卡
   */
  function addBankAccount() {
    if (!_isInput()) return;
    props.history.push("/bankAccountInfo/1?from=bankList");
  }

  /**
   * 是否可输入
   * @returns {boolean}
   * @private
   */
  function _isInput() {
    return !!Number(props.match.params.isInput);
  }
};

export default BankAccountNumber;
