/**
 * Created by yaer on 2019/6/28;
 * @Email 740905172@qq.com
 * */

import { Modal, Radio, List } from "antd-mobile";
import { useState, useEffect, useRef } from "react";
import PropsType from "prop-types";
import "./index.less";

import Scroll from 'react-bscroll'
import 'react-bscroll/lib/react-scroll.css'

const { RadioItem } = Radio;

const RangePicker = (props) => {
  const {
    data, show, SelectFn, onCloseFn, 
  } = props;

  const [value, setValue] = useState("");
  const [rangeId, setRangeId] = useState("");

  const [now_picker_data, setNowPickerData] = useState([]);

  const [show_type, setshowType] = useState(false);

  const [className, setClassName] = useState("");

  // 防止点击过快
  const [is_click, setIsClick] = useState(true);

  useEffect(() => {
    setNowPickerData(data);
  }, [data]);

  useEffect(() => {
    setNowPickerData(data);
    setshowType(show);
  }, [show]);

  return (
    <div>
      <Modal
        transparent
        popup
        onClose={onClose}
        animationType="slide-up"
        visible={show_type}>
        <div className="popup-con">
          <Scroll>
            <List>
              {now_picker_data.map(item => (
                <RadioItem
                  className={className}
                  key={`${item.value}${Math.random()*10|0}`}
                  onChange={e => onChange(item)}>
                  {item.value}
                </RadioItem>
              ))}
            </List>
          </Scroll>
        </div>
      </Modal>
    </div>
  );

  /**
   * 点击的当前项
   * @param item
   */
  function onChange(item) {
    if (!is_click) return;
    setIsClick(false);
    setClassName("hiddlen");
    const { children } = item;
    const input_value = `${value ? `${value},` : value}${item.value}`;
    const input_id = `${rangeId ? `${rangeId},` : rangeId}${item.id}`;
    // 判断还有没有子级，如果有的话则需要继续遍历往下执行
    setValue(input_value);
    setRangeId(input_id);
    if (children && children.length) {
      const timer = setTimeout(() => {
        setNowPickerData(item.children);
        setClassName("");
        clearTimeout(timer);
      }, 500);
    } else {
      setshowType(!show_type);
      SelectFn(item, input_value, input_id);
      setValue("");
      setRangeId("");
      setClassName("");
    }
    const is_click_timer = setTimeout(() => {
      setIsClick(true);
    }, 800);
  }

  function onClose() {
    setValue("");
    setRangeId("");
    setClassName("");
    setshowType(!show_type);
    onCloseFn(!show_type);
  }
};


RangePicker.propsType = {
  data: PropsType.array.isRequired,
  show: PropsType.bool.isRequired,
  SelectFn: PropsType.func.isRequired,
  onCloseFn: PropsType.func.isRequired,
};

RangePicker.defaultProps = {
  data: [],
};

export default RangePicker;
