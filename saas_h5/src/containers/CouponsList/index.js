/**
 * Created by yaer on 2019/8/29;
 * @Email 740905172@qq.com
 * */
import {useEffect, useState} from "react";
import {useDocumentTitle} from "../../hooks";

import ShowPage from "../../components/Show-page";
import {useMappedState, useDispatch} from "redux-react-hook";

import {setClassName} from "../../utils/utils";
import {saveCouponData} from "../../actions";
import {background} from "../../vest";
import {getCouponList} from "../../api";

import "./index.less";

import selectIcon from "../../images/couponsList/select.png";
import noSelectIcon from "../../images/couponsList/no_select.png";
import noCoupons from "../../images/couponsList/no_coupons.png";
import {nativeCustomMethod} from "../../nativeMethod";

/**
 * 优惠券列表
 * @param props
 * @param (props) isSelect 0/1  不可选/可选
 * @returns {*}
 * @constructor
 *
 * query
 *    couponId  选中的优惠券ID
 */
const CouponsList = (props) => {
  useDocumentTitle(props);

  const isSelect = !!(props.match.params.isSelect | 0);

  // 获取当前选择的优惠券信息
  const {couponData} = useMappedState(state => state);
  const dispatch = useDispatch();

  const [show, setShow] = useState(false);

  const [data, setData] = useState([]);

  useEffect(() => {
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      getData();
    };
    getData();
  }, []);

  return (
    <ShowPage show={show}>
      <div className="coupons-list-wrapper">

        <ShowPage show={!!(data.length && isSelect)}>
          <div className="not-using-coupon" style={background()} onClick={notUsingCoupon}>Not using coupon</div>
        </ShowPage>

        {
          data.length && <div className="coupons-list">
            {
              data.map((item, index) => (
                <div
                  className={setClassName([
                    "coupon-item",
                    isSelect ? "" : "no-select"
                  ])}
                  onClick={() => couponClick(item, index)}
                  key={index}>
                  <div className="info-wrapper" style={background()}>
                    <div className="info">
                      <h1 className="money">₹{item.money}</h1>
                      <p className="coupon-name">{item.couponName}</p>
                    </div>
                    <p className="time">{item.validityPeriod}</p>
                  </div>
                  <div className="select" style={{display: isSelect ? "block" : "none"}}>
                    <img src={item.isSelect ? selectIcon : noSelectIcon} alt=""/>
                  </div>
                </div>
              ))
            }
          </div>
          ||
          <div className="no-couponse">
            <img src={noCoupons} alt=""/>
            <p>No coupons available</p>
          </div>
        }
      </div>
    </ShowPage>
  );


  function getData() {
    getCouponList().then(res => {
      setData(_setDataSelect(res.data));
      setShow(true);
    })
  }

  /**
   * 优惠券选择
   * @param item
   * @param index
   */
  function couponClick(item, index) {
    if (!isSelect) return;

    setData(data.map((item, i) => Object.assign({}, item, {
      isSelect: index === i
    })));
    dispatch(saveCouponData(item));
    props.history.go(-1);
  }

  /**
   * 不使用优惠券
   */
  function notUsingCoupon() {
    dispatch(saveCouponData("notUse"));
    props.history.go(-1);
  }


  /**
   * 设置数组选中字段
   * @param arr
   * @returns {*}
   * @private
   */
  function _setDataSelect(arr) {
    return arr.map(item => Object.assign({}, item, {
      isSelect: couponData ? couponData.id === item.id : false,
      money: Number(item.money / 100).toFixed(2)
    }))
  }
};


export default CouponsList;
