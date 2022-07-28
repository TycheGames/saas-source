/**
 * Created by yaer on 2019/11/12;
 * @Email 740905172@qq.com
 * 可拖动浮窗
 * */

export default class TouchFloat {
  /**
   *
   * @param {*} el dom
   * @param {*} floatRight 是否只吸右边
   * @param {*} startCB 按下cb
   * @param {*} moveCB 移动cb
   * @param {*} endCB 抬起cb
   */
  constructor({ el, floatRight, startCB, moveCB, endCB }) {
    this.el = el;

    // 点击默认值
    this.targetClickX;
    this.targetClickY;

    // 点击位置到图标边界的距离
    this.targetClickDiffX;
    this.targetClickDiffY;

    // 点击位置与移动的差值
    this.diffX;
    this.diffY;

    // 最大边界
    this.maxWidth;
    this.maxHeight;

    // 是否进行了移动
    this.isMove = false;

    // 默认只吸右边
    this.floatRight = floatRight || false;

    // 关键节点操作回调
    this.startCB = startCB || (() => {});
    this.moveCB = moveCB || (() => {});
    this.endCB = endCB || (() => {});

    if (this.el) {
      this.el.addEventListener("touchstart", this.touchStart.bind(this), false);
      this.el.addEventListener("touchmove", this.touchMove.bind(this), false);
      this.el.addEventListener("touchend", this.touchEnd.bind(this), false);
    }
  }

  touchStart(e) {
    this.el.style.transition = "";
    const { view, target } = e;
    const { offsetTop, offsetLeft, offsetHeight, offsetWidth } = target;
    const { clientX, clientY } = e.targetTouches[0];
    const { innerHeight, innerWidth } = view;

    // 计算最大边界
    this.maxWidth = innerWidth - offsetWidth;
    this.maxHeight = innerHeight - offsetHeight;

    // 获取点击位置
    this.targetClickX = clientX;
    this.targetClickY = clientY;

    // 获取点击位置到图标的距离
    this.targetClickDiffX = clientX - offsetLeft;
    this.targetClickDiffY = clientY - offsetTop;

    this.startCB();
  }

  touchMove(e) {
    this.isMove = true;
    const { clientX, clientY } = e.targetTouches[0];
    // 获取实时位置
    // 初始位置+(当前位置-初始位置)-点击位置距离图标边界的值
    this.diffX =
      this.targetClickX + (clientX - this.targetClickX) - this.targetClickDiffX;
    this.diffY =
      this.targetClickY + (clientY - this.targetClickY) - this.targetClickDiffY;

    //边界限制 min限制右边界/下边界，max限制左边界/上边界
    this.diffX = Math.max(Math.min(this.diffX, this.maxWidth), 0);
    this.diffY = Math.max(Math.min(this.diffY, this.maxHeight), 0);
    TouchFloat.updateStyle(this.diffX, this.diffY, this.el);

    // 禁止默认滚动
    e.preventDefault();

    this.moveCB();
  }

  touchEnd(e) {
    if (!this.isMove) return;
    const { innerWidth } = e.view;

    const direction = this.diffX > innerWidth / 2; // true 代表右边 false 代表左边
    // 自动吸边
    const x = this.floatRight ? this.maxWidth : direction ? this.maxWidth : 0;
    TouchFloat.updateStyle(x, this.diffY, this.el);
    this.el.style.transition = "all .2s linear";
    this.isMove = false;
    this.endCB(direction);
  }

  /**
   * 样式改变
   * @param {*} left
   * @param {*} top
   * @param {*} el
   */
  static updateStyle(left, top, el) {
    el.style.left = `${left}px`;
    el.style.top = `${top}px`;
  }

  removeEvent() {
    this.el.removeEventListener("touchstart", this.touchStart, false);
    this.el.removeEventListener("touchmove", this.touchMove, false);
    this.el.removeEventListener("touchend", this.touchEnd, false);
  }
}
