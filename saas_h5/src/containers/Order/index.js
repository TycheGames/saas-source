/**
 * Created by yaer on 2019/7/8;
 * @Email 740905172@qq.com
 * */

import { useState, useEffect } from "react";
import ShowPage from "../../components/Show-page";
import { useDocumentTitle } from "../../hooks";
import "./index.less";
import { getLoanOrderList } from "../../api";
import { setClassName } from "../../utils/utils";
import orderStatus from "../../data/orderStatus";
import * as orderStatusEnum from "../../enum/orderStatusEnum";
import { nativeCustomMethod } from "../../nativeMethod";

import NO_ORDER from "../../images/order/no_order.png";
import orderLogo from "../../images/order/order_logo.png";

import arrowRight from "../../images/icon/arrow_right.png";

/**
 * 获取状态
 * @param status
 * @returns {{value: string, className: string}}
 * @private
 */
const _getStatus = status =>
  orderStatus.filter(item => item.status === status)[0];

/**
 * 订单点击
 * @param item
 */
const orderClick = (item, props) => {
  const {
    STATUS_CHECK_REJECT,
    STATUS_DEPOSIT_REJECT,
    STATUS_LOAN_REJECT,
    STATUS_WITHDRAWAL_TIMEOUT
  } = orderStatusEnum;
  if (
    item.status === STATUS_CHECK_REJECT ||
    item.status === STATUS_DEPOSIT_REJECT ||
    item.status === STATUS_LOAN_REJECT ||
    item.status === STATUS_WITHDRAWAL_TIMEOUT
  ) {
    props.history.push(`/loanRejected`);
  } else {
    props.history.push(`/orderDetail/${item.id}`);
  }
};

/**
 * 无订单组件
 * @returns {*}
 * @constructor
 */
const NoOrder = () => (
  <div className="no-order">
    <div className="con">
      <img src={NO_ORDER} alt="" />
      <p>No Record~</p>
    </div>
  </div>
);

/**
 * loading状态组件
 * @param isLoading
 * @returns {*}
 */
const footer = isLoading => (
  <div style={{ padding: 10, textAlign: "center" }}>
    {isLoading ? "Loading..." : "Loaded"}
  </div>
);

/**
 * 订单项
 * @param i 数据
 * @param index 下标
 * @returns {*}
 * @constructor
 */
const Item = (i, index, props) => {
  const status = _getStatus(i.status);
  return (
    <div className="item" key={index} onClick={() => orderClick(i, props)}>
      <img src={orderLogo} alt="" className="logo" />
      <div className="info">
        <div className="left">
          <p className="money">₹ {i.amount}</p>
          <p className="time">{i.date}</p>
        </div>
        <div className="right">
          <span
            className={setClassName([
              "status",
              (status && status.className) || ""
            ])}
          >
            {(status && status.value) || ""}
          </span>
          <img src={arrowRight} alt="" className="arrow-right" />
        </div>
      </div>
    </div>
  );
};

/**
 * 订单页面
 * @param props
 * @param (props) recordsType 列表类型
 * @returns {*}
 * @constructor
 */
const Order = props => {
  useDocumentTitle(props);

  const [scrollChange, setScrollChange] = useState(0);
  const [showPage, setShowPage] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [page, setPage] = useState(1);
  const [data, setData] = useState({
    totalPage: 1,
    item: []
  });

  const [dataClone, setDataClone] = useState(null); // 应对webview onshow方法出现数据重复问题
  const { totalPage, item } = data;

  useEffect(() => {
    if (isLoading) return;
    // 判断是否还有数据
    if (page + 1 > totalPage) {
      return;
    }
    setPage(page + 1);
  }, [scrollChange]);

  useEffect(() => {
    window.addEventListener("scroll", scroll);
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      getData();
    };
    return () => {
      window.removeEventListener("scroll", scroll);
    };
  });

  useEffect(() => {
    nativeCustomMethod("onHide", () => "htmlOnHide");
    window.htmlOnHide = () => {
      console.log("on hide");
      setDataClone(data);
    };
  }, [data]);

  useEffect(() => {
    getData();
  }, [page]);
  return (
    <ShowPage show={showPage}>
      <div className="order-wrapper">
        {item.map((i, index) => Item(i, index, props))}
        {footer(isLoading)}
        {!item.length && NoOrder()}
      </div>
    </ShowPage>
  );

  function getData() {
    console.log("get data");
    setIsLoading(true);
    getLoanOrderList(page, props.match.params.recordsType).then(res => {
      if (res.data) {
        setIsLoading(false);

        // 如果dataclone有数据代表onshow执行，需要使用clone的数据
        if (dataClone && dataClone.item.length) {
          setData(dataClone);
          setDataClone(null);
          return;
        }
        setData({
          totalPage: res.data.totalPage,
          item: item.concat((res.data.item && res.data.item) || [])
        });
        setShowPage(true);
      }
    });
  }

  /**
   * 滚动事件
   */
  function scroll() {
    const scrollTop =
      document.documentElement.scrollTop || document.body.scrollTop;
    const clientHeight =
      document.documentElement.clientHeight || document.body.clientHeight;
    const scrollHeight =
      document.documentElement.scrollHeight || document.body.scrollHeight;
    // 接近文档底部100的位置进行加载
    if (scrollTop + clientHeight + 100 > scrollHeight) {
      setScrollChange(scrollTop + clientHeight + 100);
    }
  }
};

export default Order;
