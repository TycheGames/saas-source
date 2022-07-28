/*
 * @Author: Always
 * @LastEditors: Always
 * @email: 740905172@qq.com
 * @Date: 2019-11-26 18:50:45
 * @LastEditTime: 2021-01-25 10:40:28
 * @FilePath: /saas_h5/src/setupProxy.js
 */
/**
 * proxy反向代理
 * Created by yaer on 2019/3/5;
 * @Email 740905172@qq.com
 * */

const proxy = require("http-proxy-middleware");

module.exports = function setupProxy(app) {
  app.use(
    proxy("/frontend/web", {
      // target: "http://localhost:8080",
      // target: "http://test-api.i-credit.in:8081",
      target: "http://test-saas-api.i-credit.in:8081",
      // target: "http://dev.saas.smallflyelephantsaas.com",
      // target: "http://test2-api.i-credit.in:8081",
      // target: "http://test3-api.i-credit.in:8081",
      // target: "http://api.i-credit.in",
      // target: "http://test-loan.mzkjbao.com",
      // target: "http://app.sashaktrupee.com",
      // target: "http://dev.loan.local",
      secure: false,
      changeOrigin: true,
      pathRewrite: {
        "^/frontend/web": "/",
      },
    })
  );
  app.use(
    proxy("/bi", {
      target: "https://test-service-base.dysdjie.com",
      secure: false,
      changeOrigin: true,
    })
  );
  app.use(
    proxy("/india", {
      target: "http://127.0.0.1:8080",
      secure: false,
      changeOrigin: true,
    })
  );
};
