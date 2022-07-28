import { useState } from "react";

import { setClassName } from "../../utils/utils";
import { useDocumentTitle } from "../../hooks";
import InfoItem from "../../components/Info-item";
import NextClick from "../../components/Next-click";

import { postWithholding } from "../../api";
import "./index.less";
import { nativeType } from "../../nativeMethod";

export default props => {
  useDocumentTitle(props);
  const [data, setData] = useState({
    name: "",
    email: "",
    phone: "",
    bankName: "",
    accountNumber: "",
    ifscCode: "",
    beneficiaryName: ""
  });
  const isError = _watchData();
  console.log(isError);
  return (
    <div className="withholding-wrapper">
      <div className="container">
        <div className="withholding-item">
          <InfoItem
            label="Name"
            inputType="input"
            inputFn={e => {
              inputChange(e.target.value, "name");
            }}
            placeholder="Input Name"
            value={data.name}
          />
        </div>
        <div className="withholding-item">
          <InfoItem
            label="Email"
            inputType="input"
            inputFn={e => {
              inputChange(e.target.value, "email");
            }}
            placeholder="Input Email"
            value={data.email}
          />
        </div>
        <div className="withholding-item">
          <InfoItem
            label="Phone"
            inputType="number"
            inputFn={e => {
              inputChange(e.target.value, "phone");
            }}
            placeholder="Input Phone"
            value={data.phone}
          />
        </div>
        <div className="withholding-item">
          <InfoItem
            label="Bank Name"
            inputType="input"
            inputFn={e => {
              inputChange(e.target.value, "bankName");
            }}
            placeholder="Input Bank Name"
            value={data.bankName}
          />
        </div>
        <div className="withholding-item">
          <InfoItem
            label="Account Number"
            inputType="number"
            inputFn={e => {
              inputChange(e.target.value, "accountNumber");
            }}
            placeholder="Input Account Number"
            value={data.accountNumber}
          />
        </div>
        <div className="withholding-item">
          <InfoItem
            label="IFSC Code"
            inputType="input"
            inputFn={e => {
              inputChange(e.target.value, "ifscCode");
            }}
            placeholder="Input IFSC Code"
            value={data.ifscCode}
          />
        </div>
        <div className="withholding-item">
          <InfoItem
            label="Beneficiary Name"
            inputType="input"
            inputFn={e => {
              inputChange(e.target.value, "beneficiaryName");
            }}
            placeholder="Input Beneficiary Name"
            value={data.beneficiaryName}
          />
        </div>
      </div>

      <NextClick
        clickFn={confirm}
        className={setClassName(["next", isError ? "" : "on"])}
      />
    </div>
  );

  function inputChange(value, type) {
    setData(
      Object.assign({}, data, {
        [type]: value
      })
    );
  }

  function confirm() {
    if (isError) return;
    postWithholding(data).then(res => {
      console.log(res);
      window.location.href = res.data.url;
    });
  }

  function _watchData() {
    let isError = false;
    for (const key in data) {
      let value = data[key];
      if (!value) {
        isError = true;
      }
    }
    return isError;
  }
};
