/**
 * Created by yaer on 2019/9/18;
 * @Email 740905172@qq.com
 * */
import {useState, useEffect, useRef} from "react";
import {findDOMNode} from "react-dom";
import {Modal, List, Radio, Toast} from "antd-mobile";
import PropTypes from "prop-types";

import "./index.less";
import {setClassName} from "../../utils/utils";
import {background} from "../../vest";

import Scroll from 'react-bscroll'
import 'react-bscroll/lib/react-scroll.css'

const {RadioItem} = Radio;

/**
 * 日期选择器
 * @param props
 * @constructor
 */
const DAY = 31; // 天数

const MONTH = 12; // 月数

const YEAR = new Date().getFullYear();  // 当前年

const MIN_YEAR = YEAR - 60; // 最小年

const CHECK_MONTH_IS_31 = [2, 4, 6, 9, 11]; //日期为31时月份不能选择的情况


// 判断是平年还是闰年 true=闰年
const isLeapYear = year => (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);

const DatePicker = props => {

  const [datePickerClass, setDatePickerClass] = useState(""); // 控制列表样式

  const [dateTab, setDateTab] = useState([]); // tabbar显示数组[num|str,num|str,num|str]

  const [value, setValue] = useState([]); // 选中的日月年值 [num,num,num]

  const [nowStep, setNowStep] = useState(0);  // 当前步数

  const [list, setList] = useState([]); // 年月日 选择数组

  const scroll = useRef(null);

  useEffect(() => {

    if (nowStep === 2) {
      scrollMove();
    }
    let list = [];  // 设置年月日数组
    let tabBarList = [];
    let key = "";
    switch (nowStep) {
      case 0:
        for (let i = 1; i < DAY + 1; i++) {
          list.push(i)
        }
        key = "Day";
        break;
      case 1:
        for (let i = 1; i < MONTH + 1; i++) {
          list.push(i)
        }
        key = "Month";
        break;
      case 2:
        for (let i = MIN_YEAR; i < YEAR + 1; i++) {
          list.push(i)
        }
        key = "Year";
        break;
      default:
        list = [];
    }

    tabBarList = dateTab[nowStep] ? dateTab : dateTab.concat([key]);

    setList(list);
    setDatePickerClass("hide-date-picker");
    setDateTab(tabBarList)
  }, [nowStep]);


  useEffect(() => {
    // 设置选中后淡出淡入的动画
    if (datePickerClass === "hide-date-picker") {
      let timer = setTimeout(() => {
        setDatePickerClass("show-date-picker");
        clearTimeout(timer);
      }, 500)
    }

  }, [datePickerClass]);


  const row = (rowData, sectionID, rowID) => {
    return <RadioItem
      disabled={isDisabled(rowData)}
      key={`${rowData}${Math.random() * 10 | 0}`}
      onChange={e => onChange(rowData)}>
      {rowData}
    </RadioItem>
  };

  return (
    <Modal
      transparent
      popup
      onClose={onClose}
      animationType="slide-up"
      visible={props.show}>
      <div className="date-picker-popup-con">
        <div className="date-tab">
          {dateTab.map((item, index) => (
            <span
              className={_dateTabItemClassName(index)}
              style={_dateTabItemStyle(index)}
              key={index}
              onClick={() => setNowStep(index)}
            >{item}</span>
          ))}
        </div>
        <div className={setClassName(["date-picker-wrapper", datePickerClass])}>
          <Scroll ref={scroll}>
            <List>
              {list.map(item => (
                <RadioItem
                  disabled={isDisabled(item)}
                  key={`${item}${Math.random() * 10 | 0}`}
                  onChange={() => onChange(item)}>
                  {item}
                </RadioItem>
              ))}
            </List>
          </Scroll>

        </div>
      </div>
    </Modal>
  );

  /**
   * 设置tab项类名
   * @param index
   * @returns {string}
   */
  function _dateTabItemClassName(index) {
    return nowStep === index ? "on" : "";
  }

  /**
   * 设置tab项马甲包颜色
   * @param index
   */
  function _dateTabItemStyle(index) {
    return _dateTabItemClassName(index) === "on" ? background() : {};
  }

  /**
   * 当选中是年的时候需要跳转到某个位置
   */
  function scrollMove() {
    let params = scroll.current.getScrollObj();
    let hei = document.documentElement.getElementsByClassName("am-list-item")[0].clientHeight;

    setTimeout(() => {
      params.scrollTo(0, -(hei * 30));
    }, 500)


  }

  function onClose() {
    resetData();
  }

  /**
   * 是否禁用
   */
  function isDisabled(item) {

    let isError = false;
    // 格式校验
    switch (nowStep) {
      case 0:
        break;
      case 1:
        // 代表月份选择 此时校验是否为31日
        isError = Number(value[0]) === 31 && !!CHECK_MONTH_IS_31.filter(i => i === item).length;
        break;
      case 2:
        // 判断是否选中的日期为29 月份为2 如果是的话那只能选中闰年
        isError = Number(value[0]) === 29 && Number(value[1]) === 2 && !isLeapYear(item);
        break;
      default:
        isError = false;
    }

    return isError;
  }


  function onChange(item) {
    let list = dateTab.splice(0);
    let valList = value.splice(0);

    // 设置dateTab的格式
    list[nowStep] = `${item} ${nowStep === 0 ? "Day" : nowStep === 1 ? "Month" : "Year"}`;

    // 存储数据
    valList[nowStep] = item < 10 ? `0${item}` : item;

    // 防止选择日期和月份后再返回来选择日期
    if (nowStep === 0 && valList[nowStep + 1]) {
      list[nowStep + 1] = "Month";
      valList.splice(nowStep + 1, 1);
    }


    setDateTab(list);
    // 控制步数每次++
    setNowStep(nowStep + 1 % 3);
    setValue(valList);


    // 最后一个选项 直接跳出组件并且清空数据
    if (nowStep === 2) {
      props.confirmDateFn(valList);
      resetData();
    }
  }

  /**
   * 重置数据
   */
  function resetData() {
    setDateTab(["Day"]);
    setValue([]);
    setNowStep(0);
    props.showFn();
  }
};

DatePicker.propTypes = {
  show: PropTypes.bool.isRequired,
  showFn: PropTypes.func.isRequired,
  confirmDateFn: PropTypes.func.isRequired,
};
DatePicker.defaultProps = {
  show: false
};


export default DatePicker;