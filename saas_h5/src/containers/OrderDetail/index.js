/**
 * Created by yaer on 2019/7/9;
 * @Email 740905172@qq.com
 * 订单详情
 * */
import { Toast, Modal, Button } from "antd-mobile";
import { useState, useEffect, useRef } from "react";
import ShowPage from "../../components/Show-page";
import NextClick from "../../components/Next-click";
import SelectMethodModalCom from "./components/SelectMethodModalCom";
import RepaymentInfoModalCom from "./components/RepaymentInfoModalCom";
import PraiseModalCom from "./components/PraiseModalCom";
import RepaymentCountDownModalCom from "./components/RepaymentCountDownModalCom";
import RepaymentDetailListCom from "./components/RepaymentDetailListCom";
import DeferredRepaymentNoticeModal from "../../components/Deferred-repayment-notice-modal";
import DeferredMoneyInputModal from "../../components/Deferred-money-input-modal";
import SelectMpurseRepaymentMethodModal from "./components/SelectMpurseRepaymentMethodModal";
import SelectSiFangRepaymentMethodModal from "./components/SelectSiFangRepaymentMethodModal";

import { useDocumentTitle, useRefreshStatus } from "../../hooks";
import orderStatus from "../../data/orderStatus";
import repaymentMethodListData from "./data/repaymentMethodListData";
import {
  STATUS_LOAN_COMPLETE,
  STATUS_PAYMENT_COMPLETE,
  STATUS_OVERDUE,
} from "../../enum/orderStatusEnum";

import { getUrlData, getTodayDeadline, setUrlParams } from "../../utils/utils";
import {
  getOrderDetail,
  saveRepaymentOrder,
  saveRepaymentOrderInCashFree,
  saveRepaymentOrderInMojo,
  saveRepaymentOrderInMpurse,
  orderCheck,
  getUserInfo,
  saveRepaymentOrderInRazorpayPaymentLink,
  saveRepaymentOrderInSiFang,
  saveRepaymentOrderInJpay,
  saveRepaymentOrderInQiMing,
  saveRepaymentOrderInRpay,
  saveRepaymentOrderInQuanQiuPay,
} from "../../api";
import { nativeCustomMethod, nativeType } from "../../nativeMethod";
import TouchFloat from "../../utils/TouchFloat";

import * as repaymentMethod from "../../enum/repaymentMethodEnum";

import {
  fontColor,
  orderDetailOrderDetailBg,
  orderDetailRepaymentBg,
  specialFontColor,
} from "../../vest";
import "./index.less";

import "../../checkout";

import repaymentFloatIcon from "../../images/orderDetail/order_detail_repayment_float.png";

const prompt = Modal.prompt;

/**
 * 获取状态
 * @param status
 * @returns {{value: string, className: string}}
 * @private
 */
const _getStatus = (status) =>
  orderStatus.filter((item) => item.status === status)[0];

/**
 *
 * @param props
 * @param (props) orderId 订单id
 * @returns {*}
 * @constructor
 *
 * query
 *  autoRepayment
 *    0 默认 不自动吊起还款
 *    1 自动吊起还款
 *  title
 *    string  页面标题
 */
const OrderDetail = (props) => {
  const { orderId } = props.match.params;
  useDocumentTitle(props);
  useRefreshStatus(orderId, STATUS_PAYMENT_COMPLETE);
  const { title } = getUrlData(props);

  // 还款弹窗缓存数据
  const ORDER_DETAIL_REPAYMENT_MODAL = "ORDER_DETAIL_REPAYMENT_MODAL";

  const { autoRepayment } = getUrlData(props);
  const [showPage, setShowPage] = useState(false);
  const [selectMethodShow, setSelectMethodShow] = useState(false); // 显示选择还款方式弹窗

  // 用户输入的还款信息
  const [userData, setUserData] = useState({
    email: "",
    contact: "",
  });

  const [userDataModal, setUserDataModal] = useState(false); // 用户信息输入弹窗

  // 当前选择的还款方式
  const [nowRepaymentMethod, setNowRepaymentMethod] = useState(null);

  const [data, setData] = useState({
    amount: "10000.00",
    repaymentAmount: "",
    days: 91,
    showDays: false,
    disbursalAmount: "9750.00",
    fees: "250.00",
    gst: 0,
    interests: "273.00",
    orderId: 2226,
    overdueDay: null,
    overdueFee: null,
    overdueFeeAmount: 0,
    overdueFeePercent: "2%",
    totalRepaymentAmount: "34.00",
    repaymentDate: "14/09/2019",
    status: 40,
    totalRate: 2.73,
    couponAmount: null,
    repaidAmount: 0,
    showPraise: false,
    showRepaymentModalType: false,
    id: "123123123", // 给用户展示的订单id
    overdueGST: "",
    delayMoney: "", // 延期金额
    delaySwitch: false, // 是否延期
    applyRelief: false, // 申请减免是否显示
    reliefAudit: false, // 减免是否审核中
    reduce: "", // 累计减免的逾期费
    delayDeductionMoney: "", // 延期还款减免滞纳金的最小金额
    delayDeductionSwitch: false, // 延期还款减免滞纳金开关
    extendMoney: "", // 展期金额
    extendSwitch: false, // 展期按钮是否显示
    showCashFree: false,
    extendDate: "", // 展期时间
    extendExpectDate: "", // 虚拟展期时间
    agreementList: [],
    showMpurse: false,
    showSifang: false,
  });

  // 还款方式
  const [repaymentMethodList, setRepaymentList] = useState(
    repaymentMethodListData
  );

  const [actualRepaymentAmount, setActualRepaymentAmount] = useState(0); // 实际还款金额

  const [repaymentModal, setRepaymentModal] = useState(false); // 促还款modal

  const [gotItCountDown, setGotItCountDown] = useState(8); // 促还款倒计时

  const [isDelay, setIsDelay] = useState(false); // 是否延期
  const [paymentType, setPaymentType] = useState(0); //  0正常 1延期部分还款 2延期部分还款并减免滞纳金
  const [repaymentNoticeTextType, setRepaymentNoticeTextType] = useState(
    "delay"
  ); // 还款描述文字类型

  const [
    showDeferredRepaymentNoticeModal,
    steShowDeferredRepaymentNoticeModal,
  ] = useState(false); // 延期信息modal

  const [
    showDeferredMoneyInputModal,
    setShowDeferredMoneyInputModal,
  ] = useState(false); // 是否显示展期输入金额modal

  const [delayMoney, setDelayMoney] = useState(""); // 最终的展期金额

  const gotItTimer = useRef(null); // 促还款定时器

  const [mpurseRepaymentMethod, setMpurseRepaymentMethod] = useState("");
  const [siFangRepaymentMethod, setSiFangRepaymentMethod] = useState("");
  const mpurseMethodRef = useRef();
  const siFangMethodRef = useRef();

  const touchFloatRef = useRef(null);

  useEffect(() => {
    getData();
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      getData(false);
      window.ORDER_DETAIL_TIMER && clearInterval(window.ORDER_DETAIL_TIMER);
      window.ORDER_DETAIL_TIMER = setInterval(() => {
        getData(false);
      }, 5000);
    };

    // 定义拖动浮窗事件
    touchFloatRef.current &&
      new TouchFloat({
        el: touchFloatRef.current,
        floatRight: true,
      });
  }, []);

  // 促还款弹窗倒计时
  useEffect(() => {
    gotItTimer.current = setInterval(() => {
      if (gotItCountDown > 0) {
        setGotItCountDown(gotItCountDown - 1);
      } else {
        clearInterval(gotItTimer.current);
      }
    }, 1000);

    return () => {
      gotItTimer.current && clearInterval(gotItTimer.current);
    };
  }, [gotItCountDown]);

  useEffect(() => {
    setActualRepaymentAmount(Number(data.totalRepaymentAmount));

    const repyamenDataInLocal = window.localStorage.getItem(
      ORDER_DETAIL_REPAYMENT_MODAL
    );
    // 判断是否显示促还款弹窗 要求显示并且当天没有显示过
    if (data.showRepaymentModalType) {
      // 判断当天是否显示过
      if (!repyamenDataInLocal || repyamenDataInLocal < new Date().getTime()) {
        window.localStorage.setItem(
          ORDER_DETAIL_REPAYMENT_MODAL,
          getTodayDeadline()
        );
        setRepaymentModal(true);
      } else {
        setGotItCountDown(0);
      }
    }
  }, [data]);

  useEffect(() => {
    setRepaymentList(
      JSON.parse(JSON.stringify(repaymentMethodListData)).map((item) => {
        if (Number(actualRepaymentAmount) >= 2000) {
          if (item.methodEnum === repaymentMethod.BANK_CARD) {
            item.weight = 50;
          }
        }
        // 判断还款大礼包以及延期功能只使用线上还款
        if (paymentType === 2 || paymentType === 1) {
          if (item.isOnline) {
            item.showMethod = true;
          } else {
            item.showMethod = false;
          }
        }

        // 判断是否展期
        if (paymentType === 3) {
          if (item.methodEnum === repaymentMethod.CASH_FREE) {
            item.showMethod = false;
          } else {
            item.showMethod = item.isOnline;
          }
        }
        // 是否显示cashFree
        if (item.methodEnum === repaymentMethod.CASH_FREE) {
          item.showMethod = paymentType === 3 ? false : data.showCashFree;
        }
        if (item.methodEnum === repaymentMethod.MPURSE) {
          item.showMethod = data.showMpurse;
        }
        if (item.methodEnum === repaymentMethod.SI_FANG) {
          item.showMethod = data.showSifang;
        }
        if (item.methodEnum === repaymentMethod.QI_MING) {
          item.showMethod = data.showQiming;
        }
        if (item.methodEnum === repaymentMethod.QUAN_QIU_PAY) {
          item.showMethod = data.showQuanqiupay;
        }
        if (item.methodEnum === repaymentMethod.QUICK_PAYMENT) {
          item.showMethod = data.showRpay;
        }
        if (item.methodEnum === repaymentMethod.MOJO) {
          item.showMethod = data.showMojo;
        }
        if (item.methodEnum === repaymentMethod.TRANSFER) {
          item.showMethod = data.showTransfer;
        }

        // 是否展示Jpay
        if (item.methodEnum === repaymentMethod.J_PAY) {
          item.showMethod = data.showJpay;
        }
        if (item.methodEnum === repaymentMethod.PAY_U_PLUS) {
          item.showMethod = data.showPayUplus;
        }
        // 如果是saas订单，并且有非razorpay的还款方式可用，则 需要关闭razorpay
        if (
          data.showSifang ||
          data.showMpurse ||
          data.showCashFree ||
          data.showMojo ||
          data.showJpay ||
          data.showTransfer ||
          data.showPayUplus ||
          data.showQiming ||
          data.showRpay
        ) {
          if (item.attr === repaymentMethod.RAZORPAY) {
            item.showMethod = false;
          }
        }
        return item;
      })
    );
  }, [actualRepaymentAmount, paymentType]);

  useEffect(() => {
    if (!mpurseRepaymentMethod) return;
    if (nowRepaymentMethod.showUserDataModal) {
      // 获取用户的信息后再显示用户信息输入框，如果已经获取了则不再请求
      if (userData.contact) {
        setUserDataModal(true);
      } else {
        getUserInfo({ orderId }).then((res) => {
          setUserData(res.data);
          setUserDataModal(true);
        });
      }
    } else {
      // 判断是否逾期并且是否延期 延期则不唤起部分还款弹窗
      if (data.status === STATUS_OVERDUE && !isDelay) {
        overduePrompt();
      } else {
        repayment(isDelay ? delayMoney : actualRepaymentAmount);
      }
    }
  }, [mpurseRepaymentMethod]);
  useEffect(() => {
    if (!siFangRepaymentMethod) return;
    if (nowRepaymentMethod.showUserDataModal) {
      // 获取用户的信息后再显示用户信息输入框，如果已经获取了则不再请求
      if (userData.contact) {
        setUserDataModal(true);
      } else {
        getUserInfo({ orderId }).then((res) => {
          setUserData(res.data);
          setUserDataModal(true);
        });
      }
    } else {
      // 判断是否逾期并且是否延期 延期则不唤起部分还款弹窗
      if (data.status === STATUS_OVERDUE && !isDelay) {
        overduePrompt();
      } else {
        repayment(isDelay ? delayMoney : actualRepaymentAmount);
      }
    }
  }, [siFangRepaymentMethod]);

  useEffect(() => {
    if (!nowRepaymentMethod) return;
    // 判断是否线上还款
    console.log(nowRepaymentMethod);
    const onlineMethodType = nowRepaymentMethod.isOnline;
    if (onlineMethodType) {
      switch (nowRepaymentMethod.methodEnum) {
        case repaymentMethod.MPURSE:
          mpurseMethodRef.current.changeShow(true);
          break;
        case repaymentMethod.SI_FANG:
          siFangMethodRef.current.changeShow(true);
          break;
        default:
          if (nowRepaymentMethod.showUserDataModal) {
            // 获取用户的信息后再显示用户信息输入框，如果已经获取了则不再请求
            if (userData.contact) {
              setUserDataModal(true);
            } else {
              getUserInfo({ orderId }).then((res) => {
                setUserData(res.data);
                setUserDataModal(true);
              });
            }
          } else {
            // 判断是否逾期并且是否延期 延期则不唤起部分还款弹窗
            if (data.status === STATUS_OVERDUE && !isDelay) {
              overduePrompt();
            } else {
              repayment(isDelay ? delayMoney : actualRepaymentAmount);
            }
          }
      }
    } else {
      nativeType({
        path: "/h5/webview",
        url: `${window.originPath}repayTransferBank?amount=${Number(
          data.totalRepaymentAmount
        )}&id=${data.orderId}`,
        isFinshPage: false,
      });
    }
    setSelectMethodShow(false);
  }, [nowRepaymentMethod]);

  useEffect(() => {
    if (selectMethodShow) {
      setMpurseRepaymentMethod("");
      setSiFangRepaymentMethod("");
    }
  }, [selectMethodShow]);

  const status = _getStatus(data.status);

  const deferredMoney = _setDeferredMoney(repaymentNoticeTextType);

  return (
    <ShowPage show={showPage}>
      <div className="order-detail-wrapper">
        {/*提示*/}
        <div className="message-wrapper">
          <i className="iconfont icon-informationoutline" />
          <p className="message">
            Attention: Do not transfer money to any private account
          </p>
        </div>
        {/*状态*/}
        <div className="status-bg" style={orderDetailOrderDetailBg()}>
          <h1>{(status && status.value) || ""}</h1>
        </div>
        {/*内容*/}
        <div className="repayment-detail">
          <div className="repayment-info" style={orderDetailRepaymentBg()}>
            <h2 className="order-id" style={fontColor()}>
              Loan ID: {data.id}
            </h2>
            <div className="repayment-info-con">
              <div className="repayment-amount">
                <h2 className="title" style={fontColor()}>
                  Repayment Amount
                </h2>
                <p className="amount" style={fontColor()}>
                  ₹{data.repaymentAmount}
                </p>
              </div>
              <div className="repayment-date">
                <h2 className="title" style={fontColor()}>
                  Repayment Date
                </h2>
                <p style={fontColor()}>{data.repaymentDate}</p>
              </div>
            </div>
          </div>

          {/* 列表数据 */}
          <RepaymentDetailListCom data={data} status={status} />
        </div>

        <ShowPage
          show={
            status &&
            (status.status === STATUS_LOAN_COMPLETE ||
              status.status === STATUS_OVERDUE)
          }
        >
          {/* 全额 */}
          <NextClick
            className="next on"
            text={`REPAYMENT ${actualRepaymentAmount}`}
            clickFn={() => {
              setPaymentType(0);
              setIsDelay(false);
              setSelectMethodShow(true);
            }}
          />
          {/* 展期 */}
          {data.extendSwitch && (
            <NextClick
              className="next on"
              text="Repayment Extension"
              clickFn={extendRepaymentClick}
            />
          )}
          {/* 还款大礼包 */}
          {data.delayDeductionSwitch && (
            <NextClick
              className="next on"
              text="Repayment Discount"
              clickFn={reliefOverdueRepayment}
            />
          )}
          {/* 延期 */}
          {data.delaySwitch && (
            <NextClick
              className="next on"
              text="Apply for Partial Deferral"
              clickFn={deferralRepaymentClick}
            />
          )}

          {/* 申请减免 */}
          {data.applyRelief && (
            <NextClick
              className="next on"
              text="Apply Reduction"
              clickFn={applyReduction}
            />
          )}
        </ShowPage>

        <div className="contact-us-wrapper">
          <h2 className="contact-us-title">Any Payment Issues?</h2>
          <Button
            style={specialFontColor("red")}
            activeClassName="button-active"
            className="contact-us"
            onClick={() =>
              nativeType({
                path: "/h5/webview",
                url: `${window.originPath}helpCenter`,
                isFinshPage: false,
              })
            }
          >
            CONTACT US
          </Button>
        </div>

        <div className="agreement-wrapper">
          {data.agreementList &&
            data.agreementList.map((item, index) => (
              <p
                className="agreement-item"
                key={index}
                onClick={() => {
                  nativeType({
                    path: "/h5/webview",
                    url: item.url,
                    isFinishPage: false,
                  });
                }}
              >
                {item.title}
              </p>
            ))}
        </div>

        {/*选择还款方式*/}
        <SelectMethodModalCom
          showType={selectMethodShow}
          closeFn={() => {
            setSelectMethodShow(false);
            setNowRepaymentMethod(null);
          }}
          repaymentMethodList={repaymentMethodList}
          selectRepaymentMethod={selectRepaymentMethod}
        />

        {/*还款信息输入*/}
        <RepaymentInfoModalCom
          showType={userDataModal}
          userData={userData}
          closeFn={() => setUserDataModal(false)}
          saveUserData={userDataContinue}
          setUserData={setUserData}
        />

        {/* 提示用户好评弹窗 */}
        <PraiseModalCom
          showType={data.showPraise}
          goBrowserFn={praiseClickGoBrowser}
          closeFn={() =>
            setData(Object.assign({}, data, { showPraise: false }))
          }
        />

        {/* 倒计时还款弹窗 */}
        <RepaymentCountDownModalCom
          showType={repaymentModal}
          closeFn={() => {
            gotItCountDown === 0 && setRepaymentModal(false);
          }}
          gotItCountDown={gotItCountDown}
          overdueFeeAmount={data.overdueFeeAmount}
        />

        {/* 倒计时还款浮窗 */}
        <img
          ref={touchFloatRef}
          style={{ display: data.showRepaymentModalType ? "block" : "none" }}
          src={repaymentFloatIcon}
          alt=""
          className="repayment-float"
          onClick={() => setRepaymentModal(true)}
        />

        {/* 展期-展期信息展示 */}
        <DeferredRepaymentNoticeModal
          textType={repaymentNoticeTextType}
          show={showDeferredRepaymentNoticeModal}
          closeModal={() => steShowDeferredRepaymentNoticeModal(false)}
          deferredRepaymentMoney={deferredMoney}
          confirmDeferredRepaymentFunc={confirmDeferredRepaymentFunc}
          extendDate={data.extendExpectDate || ""}
          days={data.days}
        />

        {/* 展期-输入展期金额 */}
        <DeferredMoneyInputModal
          show={showDeferredMoneyInputModal}
          closeModal={() => setShowDeferredMoneyInputModal(false)}
          money={deferredMoney}
          confirmRepayment={confirmRepayment}
        />

        {/* mpurse还款方式选择 */}
        <SelectMpurseRepaymentMethodModal
          cref={mpurseMethodRef}
          close={() => {
            setSelectMethodShow(false);
            setNowRepaymentMethod(null);
          }}
          selectRepaymentMethod={mpurseSelectRepaymentMethod}
        />
        {/* sifang还款方式选择 */}
        <SelectSiFangRepaymentMethodModal
          cref={siFangMethodRef}
          close={() => {
            setSelectMethodShow(false);
            setNowRepaymentMethod(null);
          }}
          selectRepaymentMethod={siFangSelectRepaymentMethod}
        />
      </div>
    </ShowPage>
  );

  /**
   * 获取信息
   * @param showLoading 是否显示loading
   */
  function getData(showLoading) {
    getOrderDetail(props.match.params.orderId, showLoading).then((res) => {
      setData(res.data);

      setShowPage(true);
    });
  }

  /**
   * 选择还款方式
   * @param type
   */
  function selectRepaymentMethod(type) {
    const repaymentMethodData = repaymentMethodList.filter(
      (item) => item.methodEnum === type
    )[0];
    setNowRepaymentMethod(repaymentMethodData);
  }

  // 申请减免
  function applyReduction() {
    // 判断是否提交过申请减免
    if (data.reliefAudit) {
      nativeType({
        path: "/h5/webview",
        url: `${window.originPath}applyReductionResult`,
        isFinishPage: false,
      });
    } else {
      nativeType({
        path: "/h5/webview",
        url: `${window.originPath}applyReduction?orderId=${data.orderId}`,
        isFinishPage: false,
      });
    }
  }

  // 展期弹窗
  function deferralRepaymentClick() {
    setIsDelay(true);
    setPaymentType(1);
    setRepaymentNoticeTextType("delay");
    steShowDeferredRepaymentNoticeModal(true);
  }
  // 减去逾期费还款方式
  function reliefOverdueRepayment() {
    setIsDelay(true);
    setPaymentType(2);
    setRepaymentNoticeTextType("reliefOverdue");
    steShowDeferredRepaymentNoticeModal(true);
  }

  // 展期
  function extendRepaymentClick() {
    setIsDelay(true);
    setPaymentType(3);
    setRepaymentNoticeTextType("extend");
    steShowDeferredRepaymentNoticeModal(true);
  }

  /**
   * 展期信息确认按钮
   */
  function confirmDeferredRepaymentFunc() {
    steShowDeferredRepaymentNoticeModal(false);
    setShowDeferredMoneyInputModal(true);
  }

  /**
   * 确认展期金额
   * @param {*} money
   */
  function confirmRepayment(money) {
    setDelayMoney(money);
    setSelectMethodShow(true);
  }

  /**
   * 用户信息modal确认按钮
   */
  function userDataContinue() {
    if (!userData.contact || !userData.email) {
      Toast.info("please input info!");
      return;
    }
    // 判断是否逾期 并且不是展期 才需要唤起部分还款
    if (status.status === STATUS_OVERDUE && !isDelay) {
      overduePrompt();
    } else {
      repayment(isDelay ? delayMoney : actualRepaymentAmount);
    }
    setNowRepaymentMethod(null);
    setUserDataModal(false);
  }

  /**
   * 逾期情况的还款
   */
  function overduePrompt() {
    prompt(
      "Repayment Amount",
      "",
      [
        {
          text: "Cancel",
          onPress: () => {
            setNowRepaymentMethod(null);
          },
        },
        { text: "REPAYMENT", onPress: modalConfirm() },
      ],
      "Number",
      actualRepaymentAmount
    );
  }

  /**
   * 逾期的情况确认还款弹窗按钮
   */
  function modalConfirm() {
    setTimeout(() => {
      document.querySelector(".am-modal-input label input").focus = () => {};
    }, 0);
    return function (amount) {
      // 判断是否有除了数字的内容
      if (isNaN(Number(amount))) {
        Toast.info("Please enter the exact number!");
        overduePrompt();
        return;
      }
      repayment(amount);
    };
  }

  function mpurseSelectRepaymentMethod(repaymentMethod) {
    setMpurseRepaymentMethod(repaymentMethod);
  }
  function siFangSelectRepaymentMethod(repaymentMethod) {
    setSiFangRepaymentMethod(repaymentMethod);
  }

  /**
   * 设置弹窗金额
   * @param {*} type  还款弹窗的点击按钮
   */
  function _setDeferredMoney(type) {
    let money;
    switch (type) {
      case "delay":
        money = data.delayMoney;
        break;
      case "reliefOverdue":
        money = data.delayDeductionMoney;
        break;
      case "extend":
        money = data.extendMoney;
        break;
      default:
        money = "";
    }
    return money;
  }

  /**
   * 还款
   * @param amount 还款金额
   */
  function repayment(amount) {
    let params = {
      orderId: data.orderId,
      amount: Number(amount),
      paymentType,
    };
    if (mpurseRepaymentMethod === "mpurse_upi") {
      nativeType({
        path: "/h5/webview",
        url: `${window.originPath}mpurseUpiRepayment?amount=${params.amount}&orderId=${params.orderId}&paymentType=${params.paymentType}`,
        isFinishPage: false,
      });
    } else {
      switch (nowRepaymentMethod.methodEnum) {
        case repaymentMethod.PAY_U_PLUS:
          nativeType({
            path: "/h5/webview",
            url: `${window.originPath}payU?method=payUplus`,
            isFinishPage: false,
          });
          break;
        case repaymentMethod.TRANSFER:
          nativeType({
            path: "/h5/webview",
            url: `${window.originPath}transferRepayment?orderId=${data.orderId}&amount=${amount}`,
            isFinishPage: false,
          });
          break;
        case repaymentMethod.CASH_FREE:
          saveRepaymentOrderInCashFree(
            Object.assign({}, params, {
              customerEmail: userData.email,
              customerPhone: userData.contact,
            })
          ).then((res) => {
            nativeType({
              path: "/h5/webview",
              url: res.data.url,
              isFinishPage: false,
            });
          });
          break;
        case repaymentMethod.MOJO:
          saveRepaymentOrderInMojo(params).then(({ data: { longurl } }) => {
            nativeType({
              path: "/h5/webview",
              url: longurl,
              isFinishPage: false,
            });
          });
          break;
        case repaymentMethod.SI_FANG:
          saveRepaymentOrderInSiFang({
            ...params,
            paymentChannel: siFangRepaymentMethod,
          }).then(({ data: { orderUrl } }) => {
            nativeType({
              path: "/h5/webview",
              url: orderUrl,
              isFinishPage: false,
            });
          });
          break;
        case repaymentMethod.QI_MING:
          saveRepaymentOrderInQiMing({
            ...params,
          }).then(({ data: { orderUrl } }) => {
            nativeType({
              path: "/h5/webview",
              url: orderUrl,
              isFinishPage: false,
            });
          });
          break;
        case repaymentMethod.QUAN_QIU_PAY:
          saveRepaymentOrderInQuanQiuPay({
            ...params,
          }).then(({ data: { orderUrl } }) => {
            nativeType({
              path: "/h5/webview",
              url: orderUrl,
              isFinishPage: false,
            });
          });
          break;
        case repaymentMethod.QUICK_PAYMENT:
          saveRepaymentOrderInRpay({
            ...params,
          }).then(({ data: { orderUrl } }) => {
            nativeType({
              path: "/h5/webview",
              url: orderUrl,
              isFinishPage: false,
            });
          });
          break;
        case repaymentMethod.J_PAY:
          saveRepaymentOrderInJpay(params).then(
            ({
              data: {
                app_key,
                business_order_num,
                amount,
                firstname,
                phone,
                productinfo,
                surl,
                furl,
                sign,
              },
            }) => {
              nativeType({
                path: "/h5/webview",
                url: `${setUrlParams(`${window.originPath}confirmRepayment`, {
                  app_key,
                  business_order_num,
                  amount,
                  firstname,
                  phone,
                  productinfo,
                  surl,
                  furl,
                  sign,
                  repaymentMethod: "jpay",
                })}`,
                isFinishPage: false,
              });
            }
          );
          break;
        case repaymentMethod.MPURSE:
          saveRepaymentOrderInMpurse({
            ...params,
          }).then(({ data: { hash, partnerId, mpQueryId, txnId } }) => {
            nativeType({
              path: "/h5/webview",
              url: `${setUrlParams(`${window.originPath}confirmRepayment`, {
                hash,
                partnerId,
                mpQueryId,
                txnId,
                amount,
                repaymentMethod: "mpurse",
              })}`,
              isFinishPage: false,
            });
          });
          break;
        case repaymentMethod.RAZORPAY_REPAYMENT_LINK:
          saveRepaymentOrderInRazorpayPaymentLink(params).then(
            ({ data: { url } }) => {
              nativeType({
                path: "/app/open_browser",
                url,
              });
            }
          );
          break;
        default:
          saveRepaymentOrder(params).then((res) => {
            if (res.code === 0) {
              const { amount, image, orderId, key } = res.data;
              const options = {
                key,
                amount,
                currency: "INR",
                name: window.appInfo.packageName,
                prefill: {
                  email: userData.email,
                  contact: userData.contact,
                  method: nowRepaymentMethod && nowRepaymentMethod.method,
                },
                order_id: orderId,
                handler(response) {
                  const {
                    razorpay_payment_id,
                    razorpay_order_id,
                    razorpay_signature,
                  } = response;
                  orderCheck(
                    razorpay_payment_id,
                    razorpay_order_id,
                    razorpay_signature
                  ).then((resData) => {
                    if (resData.code === 0) {
                      nativeType({
                        type: "/app/back",
                      });
                    }
                  });
                },
                theme: {
                  hide_topbar: true,
                },
              };
              const rzp = new Razorpay(options);
              rzp.open();
            }
          });
      }
    }
    setNowRepaymentMethod(null);
  }

  /**
   * 好评弹窗点击
   * @param {*} e
   */
  function praiseClickGoBrowser(e) {
    e.stopPropagation();
    setData(Object.assign({}, data, { showPraise: false }));
    nativeType({
      path: "/app/open_browser",
      url: window.appInfo.googleLink,
    });
  }
};

export default OrderDetail;
