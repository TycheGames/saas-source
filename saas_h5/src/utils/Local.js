/* eslint-disable */
/**
 *
 * 产品排序类
 * */


export default class {
  /**
   *
   * @param localName 储存名称（命名规范：页面路径，如果一个页面含有两个储存写为xxx_1）
   * @param num 排序每次增长字段
   */
  constructor(localName, num = 200) {
    if (!localName) {
      throw new Error("请传递储存名称");
    }
    this.localName = localName;
    this.num = Number(num);
    if (!getStorage(localName)) {
      window.localStorage.setItem(localName, JSON.stringify([]));
    }
  }

  /**
   * 设置参数
   * @param data  传递的数据
   * @param id  查询的主键（必须为data里面的某个唯一字段）
   * @param sort_num  排序字段
   */
  setItem(data, id, sort_num) {
    const storage = getStorage(this.localName);
    let
        index;
    // 查询下标
    storage.forEach((item, i) => {
      if (item[id] === data[id]) {
        index = i;
      }
    });

    // 排序字段++ 或者插入
    if (index !== undefined) {
      storage[index][sort_num] = Number(data[sort_num]) + this.num;
    } else {
      data[sort_num] = Number(data[sort_num]) + this.num;
      storage.push(data);
    }
    setStorage(storage, this.localName);
  }


  /**
   * 排序
   * @param data  数据
   * @param type  {String}  desc  降序
   * @param type  {String}  ascend  升序
   * @param sort_num  排序字段
   */
  sortData(data, sort_num, type) {
    switch (type) {
      case "desc":
        return data.sort((a, b) => Number(b[sort_num]) - Number(a[sort_num]));
      case "ascend":
        return data.sort((a, b) => Number(a[sort_num]) - Number(b[sort_num]));
      default:
        return data.sort((a, b) => Number(a[sort_num]) - Number(b[sort_num]));
    }
  }

  /**
   * 二维排序
   * @param data  数据
   * @param cb  排序回调方法
   */
  manyDimensionsSortData(data, cb) {
    return data.sort((a, b) => cb(a, b));
  }

  /**
   * 查看储存
   * @returns {string}
   */
  getLocalData() {
    return getStorage(this.localName);
  }
}

/**
 * 存储数据
 * @param arr 存储数组
 * @param localName 储存名称
 * @private
 */
function setStorage(arr, localName) {
  window.localStorage.setItem(localName, JSON.stringify(arr));
}

/**
 * 获取数据
 * @param localName 储存名称
 * @returns {string | null}
 * @private
 */
function getStorage(localName) {
  const storage = window.localStorage.getItem(localName);
  return storage && JSON.parse(storage) || null;
}
