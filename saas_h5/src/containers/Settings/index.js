/**
 * Created by yaer on 2019/8/31;
 * @Email 740905172@qq.com
 * */

import { useState } from "react";
import "./index.less";
import { Toast } from "antd-mobile";


import { nativeType } from "../../nativeMethod";
import {useDocumentTitle} from "../../hooks";

const ABOUT_US_ICON = require("../../images/icon/about_us_icon.png");


const Settings = (props) => {
  useDocumentTitle(props);
  const [navList, setNavList] = useState([
    {
      label: "",
      list: [
        {
          type: "client",
          icon: ABOUT_US_ICON,
          label: "About us",
          link: {
            path: "/h5/webview",
            url: `${window.originPath}aboutUs`,
            isFinshPage: false,
          },
        },
      ],
    },
  ]);


  return (
    <div className="settings-wrapper">
      <div className="settings-list">
        {
          navList.map((item, index) => (
            <div className="nav-group" key={index}>
              {item.label && <h1 className="label">{item.label}</h1>}
              <ul className="list-wrapper">
                {
                  item.list.map((i, n) => (
                    <li className="list-item" key={n} onClick={() => navItemClick(i)}>
                      <img src={i.icon} alt="" />
                      <p className="title">{i.label}</p>
                      <i className="iconfont icon-youjiantou" />
                    </li>
                  ))
                }
              </ul>
            </div>
          ))
        }
      </div>
      <div className="log-out" onClick={() => logOut()}>LOG OUT</div>
    </div>
  );

  /**
   * 导航点击
   * @param item
   */
  function navItemClick(item) {
    const { type, link } = item;
    if (type === "h5") {
      props.history.push(link);
    } else {
      nativeType(link);
    }
  }

  /**
   * 退出
   */
  function logOut() {
    nativeType({ path: "/user/logout" });
    Toast.info("log out success");
    const timer = setTimeout(() => {
      props.history.go(-1);
    }, 1000);
  }

  /**
   * 是否显示导航右箭头
   * @param label 根据导航label判断的
   */
  function _showRightIcon(label) {
    return label === "Payment Order";
  }
};

export default Settings;
