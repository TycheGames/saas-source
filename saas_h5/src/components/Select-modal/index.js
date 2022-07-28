/**
 * Created by yaer on 2019/6/26;
 * @Email 740905172@qq.com
 * 选择modal
 * */

import "./index.less";
import PropTypes from "prop-types";
import {useState, useEffect} from "react";
import {Modal} from "antd-mobile";
import {setClassName} from "../../utils/utils";

import Scroll from 'react-bscroll'
import 'react-bscroll/lib/react-scroll.css'
import {color} from "../../vest";

const SelectModal = (props) => {
  const {
    modal_list, modal_title, confirmDataClickFn, modal_type, CloseFn
  } = props;

  const [list, setList] = useState([]);

  const [height, setHeight] = useState(0);


  useEffect(() => {
    setList(modal_list);
  }, [modal_list]);

  useEffect(() => {
    setHeight(list.length * 40 > 260 ? 260 : list.length * 40);
  }, [list]);


  return (
    <div>
      <Modal
        onClose={CloseFn}
        transparent
        visible={modal_type}>
        <div className="select-modal">
          <h3>{modal_title}</h3>
          <ul className="list" style={{height: `${height}px`}}>
            <Scroll click>
              {list && list.map((item, index) => (
                <li
                  onClick={() => modalItemClick(index)}
                  key={index}
                  className="select-item">
                  <i className={_itemSelectClassName(item)} style={_itemSelectStyle(item)}/>
                  <span>
                  {item.label}
                </span>
                </li>
              ))}
            </Scroll>
          </ul>
        </div>
      </Modal>
    </div>
  );

  /**
   * 确认按钮
   */
  function modalConfirm() {
    const data = list.filter(item => (item.isSelect))[0];
    confirmDataClickFn(data, list);
  }

  /**
   * select选择按钮
   * @param index 选择下标
   */
  function modalItemClick(index) {
    const arr = list.map((item, i) => {
      return Object.assign({}, item, {
        isSelect: i===index,
      });
    });
    // setList(arr);

    // 点击直接选中
    confirmDataClickFn(arr[index], arr);
  }

  /**
   * 项选择类名
   * @param item
   * @returns {string}
   * @private
   */
  function _itemSelectClassName(item) {
    return setClassName([
      "iconfont",
      item.isSelect ? "icon-xuanzhong" : "icon-xuanze1",
    ])
  }

  /**
   * 根据类名生成颜色
   * @param item
   * @private
   */
  function _itemSelectStyle(item) {
    return _itemSelectClassName(item).indexOf("icon-xuanzhong") > -1 ? color() : {};
  }
};

SelectModal.propTypes = {
  modal_list: PropTypes.array.isRequired,
  confirmDataClickFn: PropTypes.func.isRequired,
  modal_title: PropTypes.string.isRequired,
  modal_type: PropTypes.bool.isRequired,
  CloseFn: PropTypes.func,
};


export default SelectModal;
