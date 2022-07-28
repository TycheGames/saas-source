/* eslint-disable */
/**
 * GID生成
 * Created by yaer on 2018/12/27;
 * @Email 740905172@qq.com
 * */

export default class {
  constructor(localName) {
    if (!localName) throw new Error("请设置localName");
    this.localName = localName;

    // 判断是否存在缓存
    if (!getLocal(this.localName)) {
      setLocal(this.localName, {});
    }
  }

  // 插入gid
  saveLocalGid() {
    const gid = Math.random().toString(36).substr(2).substr(0, 8);
    const time = new Date(new Date().toLocaleDateString()).getTime() + 24 * 60 * 60 * 1000 - 1; // 当天23:59:59时间戳
    setLocal(this.localName, JSON.stringify({gid, time}));
  }

  // 获取gid
  getLocalGid() {
    return getLocal(this.localName);
  }

  // 重置GID
  resetLocalGid() {
    const gid = this.getLocalGid();

    // 判断是否为空对象
    if (Object.keys(gid).length === 0) {
      this.saveLocalGid();
      return;
    }

    if (new Date().getTime() >= gid.time) {
      this.saveLocalGid();
    }
  }
}

function setLocal(localName, params) {
  window.localStorage.setItem(localName, params);
}

/**
 * 获取local缓存
 * @param localName
 */
function getLocal(localName) {
  const local = window.localStorage.getItem(localName);
  return local && JSON.parse(local) || {};
}
