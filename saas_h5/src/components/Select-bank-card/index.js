/**
 * Created by yaer on 2019/9/21;
 * @Email 740905172@qq.com
 * 选择银行卡弹窗
 * */
import {Modal, Radio, List} from "antd-mobile";
import {useState} from "react";
import PropTypes from "prop-types";
import {nativeType} from "../../nativeMethod";
import {color} from "../../vest";


import "./index.less";

const RadioItem = Radio.RadioItem;

const SelectBankCard = props => {

  const {show, changeShow, defaultSelectBankCardId, bankCardList, selectBankCardFn, showNoUseBtn, showAddBtn} = props;
  return (
    <Modal
      transparent
      visible={show}
      onClose={() => changeShow(false)}
    >
      <div className="select-bank-card-con">
        <h1 className="title">Select Bank Card</h1>
        <div className="container">
          <ul className="bank-list">
            <List>
              {
                bankCardList.map((item, index) => (
                  <RadioItem
                    disabled={isDisable(item.status)}
                    key={index}
                    checked={defaultSelectBankCardId === item.id}
                    onChange={() => selectBankCard(item)}
                    onClick={()=>changeShow(false)}
                  >
                    {item.ifsc}
                    <List.Item.Brief>{item.account}</List.Item.Brief>
                  </RadioItem>
                ))
              }
            </List>
          </ul>
          <div className="add-bank" onClick={addBankCard} style={{display: showAddBtn ? "flex" : "none"}}>
            <i className="iconfont icon-tianjia-xue" style={color()} />
            <p style={color()}>Add new bank card</p>
          </div>
        </div>
      </div>
    </Modal>
  );

  /**
   * 选中银行卡
   */
  function selectBankCard(item) {
    selectBankCardFn(item);
  }

  /**
   * 判断是否可点击
   * @param status
   */
  function isDisable(status) {
    return status === -1
  }


  /**
   * 添加银行卡
   */
  function addBankCard() {
    nativeType({
      path: "/h5/webview",
      url: `${window.originPath}bankAccountInfo/1?from=index`,
      isFinshPage: false,
    });
  }
};

SelectBankCard.propTypes = {
  show: PropTypes.bool.isRequired,  // 是否显示modal
  changeShow: PropTypes.func.isRequired,  // 显示状态变化
  bankCardList: PropTypes.array.isRequired, // 银行卡列表
  defaultSelectBankCardId: PropTypes.oneOfType([
    PropTypes.string,
    PropTypes.number,
    PropTypes.object
  ]), // 默认选择的卡id
  selectBankCardFn: PropTypes.func.isRequired,  //  选择方法
  showNoUseBtn: PropTypes.bool, // 是否显示不使用按钮
  showAddBtn: PropTypes.bool,
};

SelectBankCard.defaultProps = {
  showNoUseBtn: true,
  showAddBtn: true,
};


export default SelectBankCard;