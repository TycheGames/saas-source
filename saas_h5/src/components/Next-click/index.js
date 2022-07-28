/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2019-11-21 14:21:32
 * @LastEditTime: 2020-04-01 10:45:46
 * @FilePath: /saas_h5/src/components/Next-click/index.js
 */
/**
 * Created by yaer on 2019/7/5;
 * @Email 740905172@qq.com
 * */

import PropTypes from "prop-types";
import { Button } from "antd-mobile";

import "./index.less";

import { bgAndFc, color } from "../../vest";

const NextClick = props => {
  const { className, clickFn, btnIcon, hasBg, hasStyle } = props;
  return (
    <Button
      activeClassName="button-active"
      className={className}
      style={_style()}
      onClick={clickFn}
    >
      {props.text}
      {(btnIcon && <i className={`iconfont ${btnIcon}`} />) || ""}
    </Button>
  );

  function _style() {
    return className.indexOf("on") > -1 && hasStyle
      ? hasBg
        ? bgAndFc()
        : color()
      : {};
  }
};

NextClick.propTypes = {
  clickFn: PropTypes.func.isRequired,
  className: PropTypes.string.isRequired,
  text: PropTypes.string,
  btnIcon: PropTypes.string,
  hasBg: PropTypes.bool, //是否显示背景主题色
  hasStyle: PropTypes.bool // 是否需要马甲包样式
};

NextClick.defaultProps = {
  text: "NEXT STEP",
  hasBg: true,
  hasStyle: true
};

export default NextClick;
