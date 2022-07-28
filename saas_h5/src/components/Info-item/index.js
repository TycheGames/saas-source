/**
 * Created by yaer on 2019/6/24;
 * @Email 740905172@qq.com
 * 信息输入以及选择
 * */

import PropTypes from "prop-types";
import { DatePicker, List } from "antd-mobile";
import { setDate } from "../../utils/utils";
import RootTemplate from "../../template/RootTemplate";
import "./index.less";
import BasicInfo from "../../containers/BasicInfo/index1";

export default class InfoItem extends RootTemplate {
  constructor(props) {
    super(props);

    this.dateReverse = InfoItem.dateReverse.bind(this);
  }

  static defaultProps = {
    inputType: "input",
    inputFn: () => {},
    inputDisabled: true // true 能输入 false不能输入
  };
  render() {
    const {
      inputType,
      inputFn,
      placeholder,
      label,
      value,
      inputDisabled,
      onBlur,
      onFocus,
      onClick
    } = this.props;
    const isText = !(
      inputType === "input" ||
      inputType === "number" ||
      inputType === "password"
    ); // 是否可输入
    return (
      <div className="info-item" onClick={onClick}>
        <div className="info-item-con">
        <span>{label}</span>
        <input
          value={inputType === "selectDate" ? this.dateReverse(value) : value}
          type={inputType}
          placeholder={placeholder}
          onChange={inputFn.bind(this)}
          disabled={isText || !inputDisabled}
          onBlur={(onBlur && onBlur) || (() => {})}
          onFocus={(onFocus && onFocus) || (() => {})}
        />
        <i
          className="iconfont icon-youjiantou"
          style={{ display: isText ? "block" : "none" }}
        />
        </div>
      </div>
    );
  }

  /**
   * 时间反转
   * @param date 时间字符串
   * @returns {String}
   */
  static dateReverse(date) {
    let dateArr = date.split("-");
    let arr = [...dateArr];
    return arr.reverse().join("/");
  }
}

InfoItem.propTypes = {
  label: PropTypes.string.isRequired,
  inputType: PropTypes.string.isRequired,
  inputFn: PropTypes.func,
  inputDisabled: PropTypes.bool,
  placeholder: PropTypes.string.isRequired,
  value: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
  onBlur: PropTypes.func,
  onFocus: PropTypes.func
};
