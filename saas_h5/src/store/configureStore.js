/**
 * Created by yaer on 2019/3/4;
 * @Email 740905172@qq.com
 * */

import { createStore } from "redux";

import { composeWithDevTools } from "redux-devtools-extension";
import rootReduer from "../reducers";

// 生成redux
function create(initialState) {
  return createStore(rootReduer, initialState,
    composeWithDevTools());
}

export default create();
