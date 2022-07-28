/*
 * @Author: Always
 * @LastEditors  : Always
 * @email: 740905172@qq.com
 * @Date: 2019-11-21 14:21:32
 * @LastEditTime : 2020-01-06 10:22:16
 * @FilePath: /saas_h5/src/containers/OrderDetail/components/PraiseModalCom/index.js
 */
import { Button } from "antd-mobile";
import PropTypes from "prop-types";

const PraiseModalCom = props => {
  const { packageName } = window.appInfo;

  const { closeFn, goBrowserFn, showType } = props;
  return (
    <div
      className="praise-wrapper"
      style={_praiseShowStyle()}
      onClick={closeFn}
    >
      <div className="praise-container">
        <div className="praise-bg" onClick={goBrowserFn}>
          <div className="praise-con">
            <div className="text">
              Congratulations! You have succesfully applied the loan! Remember
              to give us a <span>5 - STAR - RATING</span>
            </div>
            <Button activeClassName="button-active" className="praise-btn">
              RATE NOW
            </Button>
          </div>
        </div>
        <div
          className="praise-close"
          onClick={e => {
            e.stopPropagation();
            closeFn();
          }}
        />
      </div>
    </div>
  );

  /**
   * 显示好评弹窗
   */
  function _praiseShowStyle() {
    return { display: showType ? "block" : "none" };
  }
};

PraiseModalCom.proptypes = {
  showType: PropTypes.bool.isRequired,
  closeFn: PropTypes.func.isRequired,
  goBrowserFn: PropTypes.func.isRequired
};

export default PraiseModalCom;
