/**
 * Created by yaer on 2019/7/2;
 * @Email 740905172@qq.com
 * 借款认证->银行信息
 * */
import { useState, useEffect } from "react";
import { Toast } from "antd-mobile";
import ShowPage from "../../components/Show-page";
import "./index.less";
import InfoItem from "../../components/Info-item";
import { setClassName } from "../../utils/utils";
import { useDocumentTitle } from "../../hooks";
import NextClick from "../../components/Next-click";
import { saveBankAccount, getBankCardStatus } from "../../api";
import { getUrlData, inputForMat } from "../../utils/utils";
import { nativeType } from "../../nativeMethod";

/**
 * params
 *  isInput
 *    0 不能输入
 *    1 能输入
 *
 * query
 *   from
 *    bankList  银行卡列表
 *    addBank 绑卡提示页
 *    orderDetail
 *    index  首页绑卡
 *    apply 申请借款页面进入
 *   id
 *    *** 订单id
 */
const BankAccountInfo = (props) => {
  useDocumentTitle(props);
  const { id, from } = getUrlData(props);

  const [showPage, setShowPage] = useState(true);

  const [isInput, setIsInput] = useState(true);

  const [nextBtnType, setNextBtnType] = useState(true);

  const [inputData, setInputData] = useState({
    account: "",
    ifsc: "",
  });

  const [confirmAccountNumber, setConfirmAccountNumber] = useState("");

  const [showAccount, setShowAccount] = useState(true);

  const { account, ifsc } = inputData;

  useEffect(() => {
    setNextBtnType(_watchInputData());
  }, [inputData, confirmAccountNumber]);

  useEffect(() => {
    setIsInput(!!Number(props.match.params.isInput));
  }, []);

  return (
    <ShowPage show={showPage}>
      <div className="bank-account-info-wrapper">
        <p className="message">
          We need your Bank Account to Disburse the Loan. Please add your
          Savings Bank Account.
        </p>
        <div className="content">
          <InfoItem
            inputDisabled={isInput}
            value={ifsc}
            inputType="input"
            placeholder="input IFSC Code"
            label="IFSC Code"
            inputFn={(e) => inputChange(e, 3)}
          />
          <InfoItem
            inputDisabled={isInput}
            value={account}
            inputType={showAccount ? "input" : "password"}
            placeholder="Account Number"
            label="Account Number"
            inputFn={(e) => inputChange(e, 1)}
            onFocus={(e) => {
              setShowAccount(true);
            }}
          />
          <InfoItem
            inputDisabled={isInput}
            value={confirmAccountNumber}
            inputType={showAccount ? "password" : "input"}
            placeholder="Confirm Account"
            label="Confirm Account Number"
            inputFn={(e) =>
              setConfirmAccountNumber(inputForMat(e.target.value, 4))
            }
            onFocus={(e) => {
              setShowAccount(false);
            }}
          />

          <ShowPage show={isInput}>
            <NextClick
              text="SAVE"
              className={setClassName(["next", nextBtnType ? "" : "on"])}
              clickFn={nextClick}
            />
          </ShowPage>
        </div>
      </div>
    </ShowPage>
  );

  /**
   * 输入框输入
   * @param e
   * @param type
   */
  function inputChange(e, type) {
    let { value } = e.target;
    let label;
    switch (type) {
      case 1:
        label = "account";
        value = inputForMat(value, 4);
        break;
      case 3:
        label = "ifsc";
        value = value.toUpperCase().replace(/\s+/g, "");
        break;
      default:
        label = "";
    }
    setInputData({
      ...inputData,
      ...{ [label]: value },
    });
  }

  // 轮询查询卡状态
  function _getBankCardStatus(id) {
    return new Promise((res) => {
      setTimeout(() => {
        getBankCardStatus({
          id,
        }).then(({ data: { status, id: resultCardId } }) => {
          // status 0 银行卡可用  1 银行卡异常  2 银行卡绑卡中
          switch (status) {
            case 0:
              res(resultCardId);
              break;
            case 1:
              Toast.info("Please try again later");
              break;
            case 2:
              _getBankCardStatus(id).then((resultCardId) => {
                res(resultCardId);
              });
          }
        });
      }, 1000);
    });
  }

  /**
   * 下一步
   */
  function nextClick() {
    if (confirmAccountNumber !== inputData.account) {
      Toast.info("Numbers are not matched! Please submit again!", 5);
      return;
    }
    if (_watchInputData()) {
      Toast.info("Please complete the information!");
      return;
    }
    saveBankAccount(
      Object.assign({}, inputData, {
        source: from === "addBank" || from === "orderDetail" ? 1 : 2,
        account: inputData.account.replace(/\s+/g, ""),
      })
    ).then(({ data: { id } }) => {
      Toast.loading("Loading", 0);
      _getBankCardStatus(id).then((resultCardId) => {
        Toast.hide();
        Toast.success("Bank Account Successfully Verified!", 2, () => {
          // 根据来源进行跳转
          switch (from) {
            case "bankList":
              props.history.go(-1);
              break;
            case "addBank":
              props.history.push(`/orderDetail/${resultCardId}`);
              break;
            case "index":
            case "apply":
              // 保存当前的银行卡id，用来做后置绑卡的数据处理(只有从绑卡状态页面进入的时候才需要保存)
              if (from === "index") {
                window.localStorage.setItem(
                  "BANK_ACCOUNT_CARD_ID",
                  resultCardId
                );
              }
              nativeType({
                path: "/app/back",
                isFinishPage: false,
              });
              break;
            default:
              props.history.go(-1);
          }
        });
      });
    });
  }

  /**
   * 监听输入
   * @private
   */
  function _watchInputData() {
    let is_error = false;
    for (const key in inputData) {
      const value = inputData[key];
      if (!value) {
        is_error = true;
      }
    }
    if (confirmAccountNumber !== inputData.account) {
      is_error = true;
    }
    return is_error;
  }
};

export default BankAccountInfo;
