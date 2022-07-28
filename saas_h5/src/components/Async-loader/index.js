/*
 * @Author: Always
 * @LastEditors: Always
 * @Date: 2019-11-06 19:11:12
 * @LastEditTime: 2020-12-09 11:41:06
 * @FilePath: /saas_h5/src/components/Async-loader/index.js
 */
/**
 * Created by yaer on 2019/8/8;
 * @Email 740905172@qq.com
 * */
import { useState, useEffect } from "react";
import axios from "axios";
export default (component) => {
  return (props) => {
    const [module, setModule] = useState(null);
    useEffect(() => {
      if (module) return;
      component()
        .then((m) => {
          const Module = m.default ? m.default : m;
          setModule(<Module {...props} />);
        })
        .catch((e) => {
          try {
            axios
              .post(
                "https://api.i-credit.in/alert/alert-msg",
                {
                  type: "h5",
                  message: e.stack,
                  project: `saas`,
                },
                {
                  headers: {
                    "Content-Type": "application/json",
                  },
                }
              )
          } catch (e) {
            console.log("report error", e.message);
          }
          setModule(null);
        });
    });
    return module;
  };
};
