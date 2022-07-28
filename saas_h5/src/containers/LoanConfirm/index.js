/**
 * Created by yaer on 2019/9/18;
 * @Email 740905172@qq.com
 * 借款确认页面
 * */
import { useState, useEffect, Fragment } from "react";
import { Toast, Modal } from "antd-mobile";

import NextClick from "../../components/Next-click";
import ShowPage from "../../components/Show-page";
import SelectBankCard from "../../components/Select-bank-card";
import SelectModal from "../../components/Select-modal";

import {
  nativeType,
  nativeCustomMethodNoAGS,
  nativeCustomMethod,
} from "../../nativeMethod";
import { setClassName } from "../../utils/utils";
import { useDocumentTitle } from "../../hooks";
import {
  getLoanConfirmData,
  saveApplyLoan,
  getBankAccountNumber,
} from "../../api";

import { color, specialFontColor } from "../../vest";
import "./index.less";

import arrowRight from "../../images/icon/arrow_right.png";

const LoanConfirmation = (props) => {
  useDocumentTitle(props);

  const [show, setShow] = useState(false);

  const [showSelectBank, setShowSelectBank] = useState(false);

  const [nextType, setNextType] = useState(false);

  const [repaymentDetailModal, setRepaymentDetailModal] = useState(false);

  const [data, setData] = useState({
    repaymentAmount: "",
    duration: "",
    disbursalAmountList: [],
    repaymentDate: "",
    agreementList: [],
    amount: "",
    repaymentDetail: {
      interest: "",
      fee: "",
      gst: "",
    },
  });

  const [bankCardId, setBankCardId] = useState(null);

  const [selectBankCardData, setSelectBankCardData] = useState({
    ifsc: "",
    account: "",
    id: "",
  });

  const [showDisbursalAmountType, setShowDisbursalAmountType] = useState(false);

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

  const [confirmAgr, setConfirmAgr] = useState(false);

  useEffect(() => {
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      getData();
      getBankCardList();
    };

    getData();
    getBankCardList();
  }, []);

  useEffect(() => {
    if (bankCardList.length) {
      setBankCardId(bankCardList[0].id);
      setSelectBankCardData(bankCardList[0]);
    }
  }, [bankCardList]);

  useEffect(() => {
    // 客户端权限校验cb
    window["authorizationCb"] = (type) => {
      type && uploadData();
    };
    return () => {
      window["authorizationCb"] = null;
    };
  }, [data, bankCardId]);

  useEffect(() => {
    // 是否进行下一步由是否选择银行卡和是否同意协议来判断
    setNextType(bankCardId !== null && confirmAgr);
  }, [bankCardId, confirmAgr]);

  const amount = _showDisbursalAmount(data.disbursalAmountList);

  return (
    <ShowPage show={show}>
      <div className="loan-confirmation">
        <ul className="list">
          <li className="item" onClick={() => setShowDisbursalAmountType(true)}>
            <span className="label">Disbursal Amount</span>
            <span className="value">
              ₹ {amount}
              <img src={arrowRight} alt="" className="arrow-right" />
            </span>
          </li>
          <li className="item">
            <span className="label">Duration</span>
            <span className="value">{data.duration} days</span>
          </li>
          <li className="item">
            <span className="label">
              Repayment Amount
              <span
                style={{
                  display:
                    window.appInfo.packageName === "RupeePlus"
                      ? "none"
                      : "inline-block",
                }}
              >
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

        {/*<ShowPage show={!!bankCardList.length}>*/}
        <div onClick={selectBankShow} className="select-bank-card-wrapper">
          <div className="item">
            <span className="label">Bank Account</span>
            <div className="bank-card">
              <span className="value">
                {bankCardDOM(bankCardId, selectBankCardData)}
              </span>
              <img src={arrowRight} alt="" className="arrow-right" />
            </div>
          </div>
        </div>
        {/*</ShowPage>*/}

        <div className="agreement">
          <div
            onClick={() => setConfirmAgr(!confirmAgr)}
            className="confirm-agr"
          >
            <i
              className={setClassName([
                "iconfont",
                confirmAgr ? "icon-xuanzhong1" : "icon-weixuanzhong",
              ])}
              style={color()}
            />
          </div>
          <p className="agr-list">
            I hereby confirm that I have read and understood the{" "}
            {data.agreementList.map((item, index) => (
              <Fragment key={index}>
                {index ? " & " : ""}
                <a href={_updateLink(item.url)} style={color()}>
                  {item.title}
                </a>
              </Fragment>
            ))}
          </p>
        </div>

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
          <div className="repayment-modal">
            <div className="title">
              <h1>Repayment Details</h1>
              <div className="icon-wrapper">
                <i
                  onClick={() => setRepaymentDetailModal(false)}
                  className="iconfont icon-guanbi"
                />
              </div>
            </div>
            <ul>
              <li>
                <span>Repayment Amount</span>
                <span>₹ {data.repaymentAmount}</span>
              </li>
              <li>
                <span>Principal Amount</span>
                <span>₹ {data.repaymentDetail.principalAmount || 0}</span>
              </li>
              <li>
                <span>Disbursal Amount</span>
                <span>₹ {amount}</span>
              </li>
              <li>
                <span>Total Interest</span>
                <span>₹ {data.repaymentDetail.interest}</span>
              </li>
              <li>
                <span>Processing Fee</span>
                <span>₹ {data.repaymentDetail.fee}</span>
              </li>
              <li>
                <span>GST(18.0% on Processing Fee)</span>
                <span>₹ {data.repaymentDetail.gst}</span>
              </li>
            </ul>
          </div>
        </Modal>

        {/*显示选择借款金额modal*/}
        <SelectModal
          CloseFn={() => setShowDisbursalAmountType(false)}
          modal_list={data.disbursalAmountList}
          confirmDataClickFn={disbursalAmountModalConfirm}
          modal_title="Select Disbursal Amount"
          modal_type={showDisbursalAmountType}
        />

        <NextClick
          className={setClassName(["next", nextType ? "on" : ""])}
          text="APPLY"
          clickFn={nextClick}
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
    getLoanConfirmData({ disbursalAmount }).then((res) => {
      if (res.code === 0) {
        // 防止刷掉用户选择的借款金额，所以需要使用原本的借款金额列表
        list =
          (list && list) ||
          _disbursalAmountListFormat(res.data.disbursalAmountList || []);
        setData(
          Object.assign({}, res.data, {
            disbursalAmountList: list,
          })
        );
        setShow(true);
      }
    });
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
        isFinshPage: false,
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
   */
  function nextClick() {
    // 判断是否选择协议
    if (!nextType) return;
    // 判断是否开了权限
    if (nativeCustomMethodNoAGS("mustPermissionsHaveBeenApplied")) {
      uploadData();
    } else {
      nativeCustomMethod("applyMustPermissions", () => ["authorizationCb"]);
    }
  }

  /**
   * 上传数据
   */
  function uploadData() {
    const params = {
      repaymentAmount: data.repaymentAmount,
      days: data.duration,
      productId: data.productId,
      bankCardId: bankCardId,
      amount: Number(_showDisbursalAmount(data.disbursalAmountList)),
    };

    saveApplyLoan(params).then((res) => {
      if (res.code === 0) {
        Toast.info("success");

        nativeCustomMethodNoAGS("uploadDataAfterApplyMustPermissions");

        // 借款成功埋点
        nativeCustomMethod("reportAppsFlyerTrackEvent", () => [
          "loan",
          JSON.stringify({
            money: data.repaymentAmount,
            day: data.duration,
            productId: data.productId,
          }),
        ]);

        let timer = setTimeout(() => {
          nativeType({
            path: "/main/tab",
            tag: 1,
            isFinishPage: false,
          });
          nativeType({
            path: "/main/refresh_tablist",
            isFinishPage: false,
          });
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
