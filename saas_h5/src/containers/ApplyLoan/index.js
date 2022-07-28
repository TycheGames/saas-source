import { useState, useEffect, useRef, Fragment } from "react";
import { Modal } from "antd-mobile";
import {
  getBankAccountNumber,
  getLoanConfirmData,
  saveApplyLoan,
} from "../../api";
import NextClick from "../../components/Next-click";
import SelectBankCard from "../../components/Select-bank-card";
import ProgressModal from "./component/ProgressModal";
import SelectModal from "../../components/Select-modal";
import RepaymentDataBigshark from "../Withdrawals/components/RepaymentData-bigshark";
import RepaymentData from "../Withdrawals/components/RepaymentData";

import { setClassName, getUrlData } from "../../utils/utils";
import {
  nativeType,
  nativeCustomMethod,
  nativeCustomMethodNoAGS,
} from "../../nativeMethod";
import { BIG_SHARK } from "../../enum/packageNameEnum";
import { useDocumentTitle, useRefreshStatus } from "../../hooks";
import { borderColor, specialFontColor } from "../../vest";

import arrowRight from "../../images/icon/arrow_right.png";
import "./index.less";

/**
 * @param (query) productId 产品id
 * @param (query) target   来源
 */
export default (props) => {
  const { packageName } = window.appInfo;

  useDocumentTitle(props);
  // 获取传递进来的产品id
  const { productId, target } = getUrlData(props);
  const [showPage, setShowPage] = useState(false);
  const [nextType, setNextType] = useState(false);
  const [showSelectBankModal, setShowSelectBankModal] = useState(false);
  const [showAddBankCard, setShowAddBankCard] = useState(true);
  const [progressModalShowType, setProgressModalShowType] = useState(false); // 进度条显示状态
  const [confirmAgr, setConfirmAgr] = useState(false);

  const progressRef = useRef(); // 存储进度条定时器

  const [orderId, setOrderId] = useState(null); // 下单后的订单id
  const [startStatus, setStartStatus] = useState(null); // 下单后的订单初始状态
  const [repaymentDetailModal, setRepaymentDetailModal] = useState(false);

  /**
   * 刷新订单状态hooks
   */
  useRefreshStatus(
    "list",
    orderId,
    null,
    progressModalShowType,
    startStatus,
    () => {
      // 当订单状态刷新后关闭定时器/返回首页/关闭模态框
      setProgressModalShowType(false);
      progressRef.current && clearInterval(progressRef.current);
      nativeType({
        path: "/main/tab",
        tag: 1,
        isFinishPage: false,
      });
    },
    3000
  );

  const [data, setData] = useState({
    repaymentAmount: "",
    duration: "",
    disbursalAmountList: [],
    repaymentDate: "",
    agreementList: [],
    amount: "",
    covidPop: "", // 遮罩图
    repaymentDetail: {
      interest: "",
      fee: "",
      gst: "",
    },
  });

  const [showDisbursalAmountType, setShowDisbursalAmountType] = useState(false);

  const [bankList, setBankList] = useState([]);

  const [phoneData, setPhoneData] = useState(null); // 用户手机信息

  const [selectBankData, setSelectBankData] = useState(null);

  useEffect(() => {
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      getData();
      getBankListData();
    };

    // 获取手机参数
    nativeType({
      path: "/app/phoneparams",
      callbackName: "getPhoneData",
    });
    window.getPhoneData = (data) => {
      setPhoneData(JSON.stringify(data));
    };

    // 页面进入埋点
    nativeCustomMethod("reportAppsFlyerTrackEvent", () => [
      "applyLoanIn",
      null,
    ]);
    getData();
    getBankListData();

    return () => progressRef.current && clearInterval(progressRef.current);
  }, []);

  useEffect(() => {
    setNextType(!!selectBankData && confirmAgr);
  }, [selectBankData, confirmAgr]);

  useEffect(() => {
    if (bankList.length) {
      setSelectBankData(bankList[0]);
    }
  }, [bankList]);

  useEffect(() => {
    // 客户端权限校验cb
    window["authorizationCb"] = (type) => {
      type && uploadData();
    };
    return () => {
      window["authorizationCb"] = null;
    };
  }, [data, selectBankData]);
  const amount = _showDisbursalAmount(data.disbursalAmountList);
  return (
    showPage && (
      <div className="apply-loan-wrapper">
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

        <div
          className="select-bank-card"
          onClick={bankCardClick}
          style={borderColor()}
        >
          <span className="label">Select Bank Card</span>
          <div
            className="select"
            style={{ display: bankList.length ? "flex" : "none" }}
          >
            <div className="info">
              <span className="val">
                {selectBankData && selectBankData.ifsc}
              </span>
              <span className="val">
                {selectBankData && selectBankData.account}
              </span>
            </div>
            <img src={arrowRight} alt="" />
          </div>
          <div
            className="select"
            style={{ display: bankList.length ? "none" : "flex" }}
          >
            Add Bank
          </div>
        </div>
        {/* 协议 */}
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
              style={specialFontColor("#2B9444")}
            />
          </div>
          <p className="agr-list">
            I hereby confirm that I have read and understood the{" "}
            {data.agreementList.map((item, index) => (
              <Fragment key={index}>
                {index ? " & " : ""}
                <a
                  href={_updateLink(item.url)}
                  style={specialFontColor("#2B9444")}
                >
                  {item.title}
                </a>
              </Fragment>
            ))}
          </p>
        </div>

        <NextClick
          text="Apply"
          clickFn={submit}
          className={setClassName(["next", nextType ? "on" : ""])}
        />



        {/*显示选择借款金额modal*/}
        <SelectModal
          CloseFn={() => setShowDisbursalAmountType(false)}
          modal_list={data.disbursalAmountList}
          confirmDataClickFn={disbursalAmountModalConfirm}
          modal_title="Select Disbursal Amount"
          modal_type={showDisbursalAmountType}
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

        {/* 选择银行卡 */}
        <SelectBankCard
          {...props}
          show={showSelectBankModal}
          changeShow={(type) => setShowSelectBankModal(type)}
          bankCardList={bankList}
          defaultSelectBankCardId={selectBankData && selectBankData.id}
          selectBankCardFn={selectBankCardFn}
          showAddBtn={showAddBankCard}
        />

        {/* 进度条弹窗  */}
        <ProgressModal
          show={progressModalShowType}
          cancelAnimationFrame={cancelAnimationFrameFn}
        />

        {Boolean(data.covidPop) && (
          <div className="covid-pop-wrapper">
            <img src={data.covidPop} alt="" />
          </div>
        )}
      </div>
    )
  );

  /**
   * 获取银行卡列表
   */
  function getBankListData() {
    getBankAccountNumber().then((res) => {
      setShowAddBankCard(res.data.showAddBankCard);
      if (res.data.list.length) {
        setBankList(res.data.list);
        // setBankList([]);
      }
    });
  }
  /**
   * 获取数据
   * @param disbursalAmount 当切换了借款金额的时候，才会传递这个字段
   * @param list  借款金额列表
   */
  function getData(disbursalAmount, list) {
    getLoanConfirmData({ disbursalAmount, productId }).then((res) => {
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
        setShowPage(true);
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
   * 进度条关闭
   * @param {*} type
   */
  function cancelAnimationFrameFn(type) {
    if (type !== "cancelAnimationFrame") return;
    setProgressModalShowType(false);

    // 判断是否为产品列表进入
    if (target === "list") {
      nativeType({
        path: "/h5/webview",
        url: `${window.originPath}audit/0?id=${orderId}&target=${target}`,
        isFinishPage: true,
      });
    } else {
      nativeType({
        path: "/main/tab",
        tag: 1,
      });
      nativeType({ path: "/main/refresh_tablist" });
    }
  }

  /**
   * 点击选择银行卡
   */
  function bankCardClick() {
    if (bankList.length) {
      setShowSelectBankModal(true);
    } else {
      nativeType({
        path: "/h5/webview",
        url: `${window.originPath}bankAccountInfo/1?from=apply`,
        isFinishPage: false,
      });
    }
  }

  /**
   * 选中的银行卡回调
   * @param {*} data 银行卡数据
   */
  function selectBankCardFn(data) {
    setSelectBankData(data);
    setShowSelectBankModal(false);
  }

  /**
   * 申请借款
   */
  function submit() {
    // 判断是否添加银行卡
    if (!nextType) return;
    // 判断是否开了权限
    if (nativeCustomMethodNoAGS("mustPermissionsHaveBeenApplied")) {
      uploadData();
    } else {
      nativeCustomMethod("applyMustPermissions", () => ["authorizationCb"]);
    }
  }

  function uploadData() {
    const params = {
      repaymentAmount: data.repaymentAmount,
      days: data.duration,
      productId: data.productId,
      bankCardId: selectBankData.id,
      amount: Number(_showDisbursalAmount(data.disbursalAmountList)),
      phoneParams: phoneData,
    };

    console.log(params);

    saveApplyLoan(params).then((res) => {
      if (res.code === 0) {
        try {
          nativeCustomMethodNoAGS("uploadDataAfterApplyMustPermissions");
          nativeType({
            path: "/upload/set_order_id",
            orderId: res.data.orderUUid,
          });
          // 申请成功埋点
          nativeCustomMethod("reportAppsFlyerTrackEvent", () => [
            "loan",
            JSON.stringify({
              money: data.repaymentAmount,
              day: data.duration,
              productId: data.productId,
            }),
          ]);
        } catch (e) {}
        setOrderId(res.data.orderId);
        setStartStatus(res.data.status);
        setProgressModalShowType(true);
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
};
