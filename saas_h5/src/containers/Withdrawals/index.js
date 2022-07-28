/**
 * Created by yaer on 2019/9/18;
 * @Email 740905172@qq.com
 * 提现页面
 * */
import { useState, useEffect, Fragment } from "react";
import { Toast, Modal } from "antd-mobile";

import NextClick from "../../components/Next-click";
import ShowPage from "../../components/Show-page";
import SelectBankCard from "../../components/Select-bank-card";
import SelectModal from "../../components/Select-modal";
import WithdrawalsPromptModal from "./components/WithdrawalsPromptModal";
import DecreaseMsg from "./components/DecreaseMsg";
import AmountRaisedMsg from "./components/AmountRaisedMsg";
import AmountRaisedModal from "./components/AmountRaisedModal";
import RepaymentData from "./components/RepaymentData";
import RepaymentDataBigshark from "./components/RepaymentData-bigshark";
import {
  nativeType,
  nativeCustomMethodNoAGS,
  nativeCustomMethod,
} from "../../nativeMethod";
import { setClassName, getUrlData } from "../../utils/utils";
import { BIG_SHARK } from "../../enum/packageNameEnum";
import { DECREASE, AMOUNT_RAISED } from "../../enum/withdrawalsEnum";
import { useDocumentTitle, requestOrderStatus } from "../../hooks";
import {
  getLoanConfirmData,
  updateWithdrawals,
  getBankAccountNumber,
} from "../../api";

import { specialFontColor } from "../../vest";
import "./index.less";

import arrowRight from "../../images/icon/arrow_right.png";

/**
 * 提现页面
 * @param {*} props
 * @param {*} query
 * @param (query) orderId 订单id
 * @param (query) productId 产品id
 * @param (query) target  来源
 */
const LoanConfirmation = (props) => {
  const { packageName } = window.appInfo;
  useDocumentTitle(props);

  const { orderId, productId } = getUrlData(props);

  const [show, setShow] = useState(false);

  const [showSelectBank, setShowSelectBank] = useState(false);

  const [nextType, setNextType] = useState(false);

  const [repaymentDetailModal, setRepaymentDetailModal] = useState(false);

  const [phoneData, setPhoneData] = useState(null); // 用户手机信息

  const [
    withdrawalsPromoptModalShow,
    setWithdrawalsPromoptModalShow,
  ] = useState(false); // 提现页面提示modal

  const [data, setData] = useState({
    repaymentAmount: "",
    duration: "",
    disbursalAmountList: [],
    repaymentDate: "",
    agreementList: [],
    amount: "",
    withdrawalsType: DECREASE, // 提现状态
    applyMoney: "", // 申请的金额
    countdown: 0, // 一小时倒计时剩余时间
    repaymentDetail: {
      interest: "",
      fee: "",
      gst: "",
    },
  });

  const [showDisbursalAmountType, setShowDisbursalAmountType] = useState(false);

  const [bankCardId, setBankCardId] = useState(null);

  const [selectBankCardData, setSelectBankCardData] = useState({
    ifsc: "",
    account: "",
    id: "",
  });

  const [showAddBankCard, setShowAddBankCard] = useState(true);

  // status  0未验证 1可用 -1不可用
  const [bankCardList, setBankCardList] = useState([
    /*{
      "id": 3,
      "account": "916010060563575",
      "ifsc": "UTIB0000005",
      "status": 0
    }*/
  ]);

  // 是否确认勾选协议 默认勾选
  const [confirmAgr, setConfirmAgr] = useState(true);

  useEffect(() => {
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      nativeType({
        path: "/main/refresh_tablist",
        isFinishPage: false,
      });
      getData();
      // getBankCardList();
    };

    // 获取手机参数
    nativeType({
      path: "/app/phoneparams",
      callbackName: "getPhoneData",
    });
    window.getPhoneData = (data) => {
      setPhoneData(JSON.stringify(data));
    };

    getData();
    // 页面进入埋点
    nativeCustomMethod("reportAppsFlyerTrackEvent", () => [
      "withdrawalsIn",
      null,
    ]);
    // getBankCardList();
  }, []);

  useEffect(() => {
    if (bankCardList.length) {
      setBankCardId(bankCardList[0].id);
      setSelectBankCardData(bankCardList[0]);
    }
  }, [bankCardList]);

  useEffect(() => {
    // 是否进行下一步由是否选择银行卡和是否同意协议来判断
    // setNextType(bankCardId !== null && confirmAgr);
    setNextType(confirmAgr);
  }, [bankCardId, confirmAgr]);

  // 实际选择借款金额
  const amount = _showDisbursalAmount(data.disbursalAmountList);

  const maxMoney =
    (data.disbursalAmountList[0] && data.disbursalAmountList[0].label) || "";

  return (
    <ShowPage show={show}>
      <div className="loan-confirmation">
        {/* 头部提示 */}
        {data.withdrawalsType === DECREASE ? (
          <DecreaseMsg money={maxMoney} />
        ) : (
          <AmountRaisedMsg
            applyMoney={data.applyMoney}
            countdown={data.countdown}
            cb={countdownCb}
          />
        )}

        {/* 信息选择 */}
        <ul className="list">
          <li className="item" onClick={() => setShowDisbursalAmountType(true)}>
            <span className="label">Disbursal Amount</span>
            <span className="value">
              ₹ {amount}
              <img src={arrowRight} alt="" className="arrow-right" />
            </span>
          </li>
          {Boolean(data.duration) && (
            <li className="item">
              <span className="label">Duration</span>
              <span className="value">{data.duration} days</span>
            </li>
          )}
          <li className="item">
            <span className="label">
              Repayment Amount
              <span>
                <i
                  className="iconfont icon-information-line"
                  onClick={() => setRepaymentDetailModal(true)}
                />
              </span>
            </span>
            <span className="value">₹ {data.repaymentAmount}</span>
          </li>
          <li className="item">
            <span className="label">Repayment Date</span>
            <span className="value">{data.repaymentDate}</span>
          </li>
        </ul>

        {/* <div
          onClick={selectBankShow}
          className="select-bank-card-wrapper"
        >
          <div className="item">
            <span className="label">Bank Account</span>
            <div className="bank-card">
              <span className="value">
                {bankCardDOM(bankCardId, selectBankCardData)}
              </span>
              <i className="iconfont icon-youjiantou" />
            </div>
          </div>
        </div> */}

        {/*选择银行卡*/}
        <SelectBankCard
          {...props}
          show={showSelectBank}
          changeShow={(type) => setShowSelectBank(type)}
          bankCardList={bankCardList}
          defaultSelectBankCardId={bankCardId}
          selectBankCardFn={selectBankCardFn}
          showAddBtn={showAddBankCard}
        />

        {/*还款详情*/}
        <Modal
          visible={repaymentDetailModal}
          popup
          transparent
          animationType="slide-up"
          onClose={() => setRepaymentDetailModal(false)}
        >
          {(packageName === BIG_SHARK && (
            <RepaymentDataBigshark
              amount={amount}
              data={data}
              close={setRepaymentDetailModal}
            />
          )) || (
            <RepaymentData
              amount={amount}
              data={data}
              close={setRepaymentDetailModal}
            />
          )}
        </Modal>

        {/*显示选择借款金额modal*/}
        <SelectModal
          CloseFn={() => setShowDisbursalAmountType(false)}
          modal_list={data.disbursalAmountList}
          confirmDataClickFn={disbursalAmountModalConfirm}
          modal_title="Select Disbursal Amount"
          modal_type={showDisbursalAmountType}
        />

        {/* 提现提示弹窗 */}
        <WithdrawalsPromptModal
          show={withdrawalsPromoptModalShow}
          confirmClickFn={confirmClickFn}
          cancelClickFn={cancelClickFn}
          day={data.duration}
          amount={data.repaymentAmount}
          repaymentDate={data.repaymentDate}
        />

        {/* 提额弹窗 */}
        <AmountRaisedModal
          show={data.withdrawalsType === AMOUNT_RAISED}
          maxMoney={maxMoney}
          applyMoney={data.applyMoney}
          withdrawalsNowClick={withdrawalsNowClick}
        />

        <NextClick
          className={setClassName(["next", nextType ? "on" : ""])}
          text={`Withdraw Rs.${amount} Now`}
          clickFn={() => nextClick()}
        />
      </div>
    </ShowPage>
  );

  /**
   * 获取数据
   * @param disbursalAmount 当切换了借款金额的时候，才会传递这个字段
   * @param list  借款金额列表
   */
  function getData(disbursalAmount, list) {
    return new Promise((resolve) => {
      getLoanConfirmData({
        disbursalAmount,
        productId,
        orderId,
        productId,
      }).then((res) => {
        if (res.code === 0) {
          const { repaymentAmount, duration, disbursalAmountList } = res.data;
          // 防止刷掉用户选择的借款金额，所以需要使用原本的借款金额列表
          list =
            (list && list) ||
            _disbursalAmountListFormat(
              disbursalAmountList || [],
              disbursalAmount
            );
          setData(
            Object.assign({}, res.data, {
              disbursalAmountList: list,
            })
          );
          setShow(true);
          resolve({
            repaymentAmount: repaymentAmount,
            days: duration,
            productId: productId,
            amount: Number(_showDisbursalAmount(list)),
            orderId,
            phoneParams: phoneData,
          });
        }
      });
    });
  }

  /* 倒计时结束cb */
  function countdownCb() {
    const list = data.disbursalAmountList.map((item) =>
      Object.assign(item, {
        isSelect: Number(data.applyMoney) === Number(item.label),
      })
    );
    getData(data.applyMoney, list).then((data) => {
      nextClick(true, data);
    });
  }

  // 提额弹窗立即提现按钮
  function withdrawalsNowClick() {
    countdownCb();
  }

  /**
   * 借款金额选中
   * @param nowData
   * @param list
   */
  function disbursalAmountModalConfirm(nowData, list) {
    if (nowData.label !== _showDisbursalAmount(data.disbursalAmountList)) {
      getData(Number(nowData.label), list);
    }
    setShowDisbursalAmountType(false);
  }

  /* 提现提示弹窗确认按钮 */
  function confirmClickFn() {
    setWithdrawalsPromoptModalShow(false);
    uploadData();
  }

  /* 提现页面提示弹窗确认按钮 */
  function cancelClickFn() {
    setWithdrawalsPromoptModalShow(false);
  }
  /**
   * 获取银行卡列表
   */
  function getBankCardList() {
    getBankAccountNumber().then((res) => {
      if (res.code === 0) {
        setShowAddBankCard(res.data.showAddBankCard);
        if (res.data.list.length) {
          setBankCardList(res.data.list);
        }
      }
    });
  }

  /**
   * 显示银行卡列表
   */
  function selectBankShow() {
    if (bankCardList.length) {
      setShowSelectBank(true);
    } else {
      nativeType({
        path: "/h5/webview",
        url: `${window.originPath}bankAccountInfo/1?from=index`,
        isFinishPage: false,
      });
    }
  }

  /**
   * 选中的银行卡回调
   * @param data  银行卡数据
   */
  function selectBankCardFn(data) {
    setSelectBankCardData(data);
    setBankCardId(data.id);
    setShowSelectBank(false);
  }

  /**
   * 下一步
   * @param isSubmit  是否直接提交数据
   * @param data  以申请金额获取到的提交数据
   */
  function nextClick(isSubmit = false, data) {
    // 判断是否选择协议
    if (!nextType) return;
    if (isSubmit) {
      uploadData(data);
      return;
    }
    // 显示借款提示
    setWithdrawalsPromoptModalShow(true);
  }

  /**
   * 上传数据
   * @param data  以申请金额获取到的提交数据
   */
  function uploadData(d) {
    const params = d
      ? d
      : {
          repaymentAmount: data.repaymentAmount,
          days: data.duration,
          productId: data.productId,
          amount: Number(_showDisbursalAmount(data.disbursalAmountList)),
          orderId,
          phoneParams: phoneData,
        };

    updateWithdrawals(params).then((res) => {
      if (res.code === 0) {
        Toast.info("success");

        try {
          // 借款成功埋点
          nativeCustomMethod("reportAppsFlyerTrackEvent", () => [
            "submitWithdrawal",
            JSON.stringify({
              money: data.repaymentAmount,
              day: data.duration,
              productId: data.productId,
            }),
          ]);
        } catch (e) {}

        let timer = setTimeout(() => {
          // 根据订单状态进行跳转
          requestOrderStatus(orderId, "list").then((res) => {
            const { jump } = res.data;
            nativeType(jump);
          });
          /* nativeType({
            path: "/main/tab",
            tag: 1,
            isFinishPage: false
          });
          nativeType({
            path: "/main/refresh_tablist",
            isFinishPage: false
          }); */
          clearTimeout(timer);
        }, 1000);
      }
    });
  }

  /**
   * 协议连接添加参数
   * @param link
   * @returns {string}
   * @private
   */
  function _updateLink(link) {
    return `${link}?amount=${Number(
      _showDisbursalAmount(data.disbursalAmountList)
    )}&days=${data.duration}&productId=${data.productId}`;
  }

  /**
   * 显示选中的借款金额
   * @param list
   * @returns {*|string}
   * @private
   */
  function _showDisbursalAmount(list) {
    const amountData = list.filter((item) => item.isSelect)[0];
    return (amountData && amountData.label) || "";
  }

  /**
   * 借款金额格式转换
   * @param list
   * @returns {*}
   * @private
   */
  function _disbursalAmountListFormat(list) {
    return list.map((item, index) =>
      Object.assign(
        {},
        {
          label: item,
          isSelect: !index,
        }
      )
    );
  }
};

/**
 * 创建银行卡显示dom
 * @param bankCardId
 * @param bankCardData
 */
const bankCardDOM = (bankCardId, bankCardData) => {
  return (
    (bankCardId && (
      <Fragment>
        <span>{bankCardData.ifsc}</span>
        <span>{bankCardData.account}</span>
      </Fragment>
    )) ||
    "Add Bank Account"
  );
};

export default LoanConfirmation;
