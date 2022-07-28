/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2019-11-21 14:21:32
 * @LastEditTime: 2020-12-08 20:25:58
 * @FilePath: /saas_h5/src/index.js
 */
import React from "react";
import ReactDOM from "react-dom";
import "./common/iconfont/iconfont.css";
import "./index.css";
import "./common/less/antReset.less";
import "./common/less/reset.less";

import { HashRouter } from "react-router-dom";

import { StoreContext } from "redux-react-hook";

import { LocaleProvider } from "antd-mobile";
import enUS from "antd-mobile/lib/locale-provider/en_US";

import * as serviceWorker from "./serviceWorker";

import store from "./store/configureStore";
import Router from "./router";

import config from "./config";

// 应用配置
config();
const DATE_FORMAT = {
  am: "AM",
  day: " Day",
  hour: "",
  minute: "",
  month: " Month",
  pm: "PM",
  year: " Year",
};

// 修改时间显示格式
const T_EN_US = Object.assign({}, enUS, {
  DatePickerView: DATE_FORMAT,
  DatePicker: Object.assign({}, enUS.DatePicker, {
    DatePickerLocale: DATE_FORMAT,
  }),
});

ReactDOM.render(
  <HashRouter>
    <StoreContext.Provider value={store}>
      <LocaleProvider locale={T_EN_US}>
        <Router />
      </LocaleProvider>
    </StoreContext.Provider>
  </HashRouter>,
  document.getElementById("root")
);

// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: http://bit.ly/CRA-PWA
serviceWorker.unregister();
