/*
 * @Author: Always
 * @LastEditors  : Always
 * @email: 740905172@qq.com
 * @Date: 2019-12-26 11:26:21
 * @LastEditTime : 2020-02-19 01:19:10
 * @FilePath: /india_loan/src/containers/PersonalCenter/index.js
 */
/**
 * Created by yaer on 2019/7/3;
 * @Email 740905172@qq.com
 * 用户信息页面 v2
 * */

import { useState, useEffect } from "react";
import { List } from "antd-mobile";
import ShowPage from "../../components/Show-page";
import { useDocumentTitle } from "../../hooks";
import { getPersonalCenterInfo } from "../../api";
import { nativeType, goLogin, nativeCustomMethod } from "../../nativeMethod";
import {
  personalCenterBg,
  personalCenterLoginIcon,
  personalCenterNoLoginIcon
} from "../../vest";

import "./index.less";

const { Item } = List;

const PersonalCenter = props => {
  useDocumentTitle(props);
  const [showPage] = useState(true);

  const [data, setData] = useState({
    phone: "",
    menuList: [],
    clickHeaderLogin: false
  });

  /**
   * 渲染菜单
   * @param {*} param0
   */
  const RenderList = ({ data }) => {
    // 是否为数组
    const isArr = Object.prototype.toString.call(data).slice(8, -1) === "Array";
    if (isArr) {
      return (
        <div className="menu-list">
          <List>
            {data.map(item => (
              <RenderItem {...item} key={item.title} />
            ))}
          </List>
        </div>
      );
    } else {
      return (
        <div className="menu-list">
          <List>
            <RenderItem {...data} />
          </List>
        </div>
      );
    }
  };

  /**
   * 渲染菜单项
   * @param {*} data
   */
  const RenderItem = data => (
    <Item
      extra={<i className="iconfont icon-youjiantou" />}
      platform="android"
      thumb={data.icon}
      multipleLine
      onClick={() => navItemClick(data)}
    >
      {data.title}
    </Item>
  );

  useEffect(() => {
    nativeCustomMethod("onShow", () => "htmlOnShow");
    window.htmlOnShow = () => {
      getData();
    };
    getData();
  }, []);

  return (
    <ShowPage show={showPage}>
      <div className="user-info-wrapper">
        <div className="user-info-bg" style={personalCenterBg()}>
          <h1 className="user-info-title">Personal Center</h1>
          <div className="user-info-con" onClick={_checkLogin}>
            <img src={_checkUserIcon()} alt="" className="header-img" />
            <p className="phone">{_checkUserPhone()}</p>
          </div>
        </div>
        <div className="menu-list-wrapper">
          {data.menuList.map((item, index) => (
            <RenderList data={item} key={index} />
          ))}
        </div>
      </div>
    </ShowPage>
  );

  function getData() {
    getPersonalCenterInfo().then(res => {
      setData(res.data);
    });
  }

  /**
   * 导航点击
   * @param item
   */
  function navItemClick(item) {
    const { jump } = item;
    nativeType(jump);
  }

  /**
   * 根据登录情况选择头像
   */
  function _checkUserIcon() {
    return data.phone ? personalCenterLoginIcon() : personalCenterNoLoginIcon();
  }

  /**
   * 根据登录情况显示手机号
   */
  function _checkUserPhone() {
    return data.phone ? `Hi, ${data.phone}` : "LOGIN / REGISTER";
  }

  /**
   * 判断是否登录
   */
  function _checkLogin() {
    // 点击头像是否可以登录
    // if (!data.clickHeaderLogin) return;
    if (!data.phone) {
      goLogin();
    }
  }
};

export default PersonalCenter;
