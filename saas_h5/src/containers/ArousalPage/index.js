/**
 * Created by yaer on 2019/9/26;
 * @Email 740905172@qq.com
 * */
import "./index.less";

import {getUrlData, AppJump} from "../../utils/utils";

const ArousalPage = props => {

  const {name} = getUrlData(props);

  const downloadData = {
    iCredit: {
      downloadUrl: "http://res.i-credit.in/apk/icredit/icredit_offical.apk",
      openUrl: "icredit://com.jc.icredit/openapp"
    },
    sashaktRupee: {
      downloadUrl: "http://res.i-credit.in/apk/sashaktrupee/sashaktrupee_offical.apk",
      openUrl: "sashaktrupee://com.jinchengindia.loans/openapp"
    }
  };

  return (
    <div className="arousal-wrapper">
      <div className="open" onClick={open}/>
      <div className="download" onClick={download}/>
    </div>
  );

  function open() {
    const {downloadUrl, openUrl} = downloadData[name];
    AppJump(openUrl, downloadUrl);
  }

  function download() {
    let link = document.createElement('a');
    link.setAttribute("download", name);
    link.href = downloadData[name].downloadUrl;
    link.click();

  }
};


export default ArousalPage;