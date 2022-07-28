/**
 * Created by yaer on 2019/7/10;
 * @Email 740905172@qq.com
 * 关于我们
 * */

import { useState } from "react";
import "./index.less";
import { useDocumentTitle } from "../../hooks";
import {color} from "../../vest";
import {getAppAttributes} from "../../nativeMethod";





const AboutUs = (props) => {
  const {packageName,website,logo} = window.appInfo;
  const data = getAppAttributes();
  useDocumentTitle(props);

  return (
    <div className="about-us-wrapper">
      <img src={logo} alt="" />
      <h1>{packageName}</h1>
      <p>
        We, at {packageName} understand your ever-changing

        needs & uncertainties life can throw at you like,

        medical emergencies, repairs, travel, meeting

        cash shortfalls for big-ticket purchases

        etc & hence we have designed this simple personal

        loan product with a loan limit of up to INR 50,000 keeping

        your needs in mind. Compared to the complicated and long

        application process in banks & other financial institutions,

        {packageName} is quick and as easy as ordering a pizza!</p>
      <a style={color()} href={website}>{website}</a>
      <a style={color()} href={`${window.originPath}agreement/${data.packageName}/privacy`}>Privacy Policy</a>
    </div>
  );
};

export default AboutUs;
