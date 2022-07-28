/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2019-11-08 11:05:17
 * @LastEditTime: 2020-12-21 16:10:05
 * @FilePath: /saas_h5/src/containers/Test/index.js
 */
import { useState } from "react";
import * as clipboard from "clipboard-polyfill";

import "../../checkout";
import { getTestData } from "../../api";
import { copy } from "../../nativeMethod";

const Test = (props) => {
  return (
    <div>
      <p>SAAS</p>
      <button onClick={click}>payment</button>
      <br />
      <br />
      <br />
      <br />
      <br />
      <br />
      <br />
      <button
        onClick={() => {
          window.open("http://www.google.com");
        }}
      >
        open
      </button>
      <br />
      <br />
      <br />
      <br />
      <br />
      <br />
      <br />
      <button onClick={copyFn}>copy</button>
    </div>
  );

  function copyFn() {
    copy("123456", "copied");
  }
  function click() {
    getTestData().then((res) => {
      if (res.code === 0) {
        const { amount, image, orderId, key } = res.data;
        const options = {
          key,
          amount,
          currency: "INR",
          name: window.appInfo.packageName,
          image,
          order_id: orderId,
          handler(response) {
            alert(`success:${JSON.stringify(response)}`);
          },
          theme: {
            color: "#F37254",
          },
        };
        const rzp = new Razorpay(options);
        rzp.open();
      }
    });
  }
};

export default Test;
