/**
 * Created by yaer on 2019/3/5;
 * @Email 740905172@qq.com
 * */
import { Component } from "react";
import PureRenderMixin from "react-addons-pure-render-mixin";
import * as utils from "../utils/utils";
import GenerateGid from "../utils/GenerateGid";

import { reportBi } from "../api";


export default class RootTemplate extends Component {
  constructor(props, contect) {
    super(props, contect);
    this.$utils = { ...utils };
    this.generateGid = new GenerateGid("GID");

    // 组件更新机制优化
    this.shouldComponentUpdate = PureRenderMixin.shouldComponentUpdate.bind(this);
  }

  /**
   * bi上报
   * @param params  params内容可以自己根据项目不同来传递
   * @returns {Promise<any>}
   */
  reportData(params) {
    this.generateGid.resetLocalGid();
    const { gid } = this.generateGid.getLocalGid();
    params = Object.assign({}, params, {
      source_tag: this.$utils.getUrlData.call(this).source_tag,
      page_id: 1,
      gid,
      type: 11,
      os: this.$utils.getSys(),
    });
    return new Promise((res) => {
      reportBi(params).then(() => {
        res();
      }).catch(() => {
        res();
      });
    });
  }
}
