/**
 * api配置文件
 * Created by yaer on 2019/3/5;
 * @Email 740905172@qq.com
 * */

export default function getDomainName() {
  // 各种环境域名
  const box = {};
  const {protocol, host} = window.location;
  if (process.env.NODE_ENV === "development") {
    box.FRONTEND = "/frontend/web";
    box.BI = "/bi";
  } else {
    switch (process.env.REQUEST_ENV) {
      case "production":
        box.FRONTEND = `${protocol}//${host}`;
        break;
      case "test":
        box.FRONTEND = `${protocol}//${host}`;
        break;
      default:
        box.FRONTEND = `${protocol}//${host}`;
    }
  }
  return box;
}
