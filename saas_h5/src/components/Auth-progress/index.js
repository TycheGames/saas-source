/**
 * Created by yaer on 2019/6/24;
 * @Email 740905172@qq.com
 * 认证进度条
 * */

import PropTypes from "prop-types";
import RootTemplate from "../../template/RootTemplate";

import "./index.less";
import ShowPage from "../Show-page";
import { setClassName } from "../../utils/utils";


export default class AuthProgress extends RootTemplate {
  constructor(props) {
    super(props);
  }

  static defaultProps = {
    allStep: 3,
    step: 0,
    stepMsg: "",
  }

  render() {
    const { allStep, step, stepMsg } = this.props;
    const stepArray = [];

    for (let i = 0; i < allStep; i++) {
      stepArray.push(
        <div
          key={i}
          className={this.$utils.setClassName([
            "step-item",
            i < step ? "actived" : "freeze", // 已经做过的步骤判断
            i === step - 1 ? "now" : "", // 当前正在做的步骤判断
          ])}>

          <span className="line" style={{ visibility: i === 0 ? "hidden" : "visible" }} />

          <i className={this.$utils.setClassName([
            "iconfont",
            i < step - 1 ? "icon-xuanzhong" : "icon-xuanze1",
          ])} />
          {/* <p className={setClassName(["step-msg", step === 6 ? "last" : ""])} style={{ display: i === step - 1 ? "block" : "none" }}>{stepMsg}</p> */}
          <span className="line" style={{ visibility: i === allStep - 1 ? "hidden" : "visible" }} />
        </div>,
      );
    }
    return (
      <div className="auth-progress-wrapper">
        {stepArray}
      </div>
    );
  }
}

AuthProgress.propTypes = {
  step: PropTypes.number.isRequired, // 当前步骤
  allStep: PropTypes.number.isRequired, // 所有步骤
  stepMsg: PropTypes.string.isRequired, // 当前步骤名
};
