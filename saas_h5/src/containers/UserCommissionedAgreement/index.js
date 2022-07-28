/* eslint-disable react/no-unescaped-entities */

/**
 * Created by yaer on 2019/7/26;
 * @Email 740905172@qq.com
 * */
import { useState, useEffect } from "react";
import { useDocumentTitle } from "../../hooks";
import "./index.less";
import { getUserCommissionedData } from "../../api";
import {getAppAttributes} from "../../nativeMethod";

const {packageName} = window.appInfo;

const UserCommissionedAgreement = (props) => {
  useDocumentTitle(props);

  const [data, setData] = useState({
    bank_card_account_name: "",
    bank_card_opening_bank: "",
    bank_card_account_number: "",
    aadhaar_number: "",
    license_number: "",
    contact_phone: "",
  });

  useEffect(() => {
    getData();
  }, []);

  function getData() {
    getUserCommissionedData().then((res) => {
      setData(res.data);
    });
  }

  return (
    <div className="user-commissioned-agreement-wrapper">
      <h1>{packageName}</h1>
      <h2>Commissioned Deduction Agreement</h2>
      <p>
In view of the "Loan Agreement"(hereinafter

      referred to as“ Agreement”) between the

      authorizer and abc hereinafter referred to as
      “ authorized person 1"), the authorizer solemnly declares that he has carefully read, understood and agreed to comply with the following

      requirements.
      </p>
      <p>
1.The authorizer agrees that if he fails to repay the loan on the agreed repayment date, the

        authorized person may entrust the bank or its commissioned third-party payment institution to transfer the payment from the bank

        accounts(including the borrowing bank account,
        and any other bank account under the name of

        the authorized person), with the agreed tariff

        standard after the agreed repayment date(from

        12 o'clock on the repayment date). If the balance

        of the above bank account is insufficient the

        authorized person has the right to entrust the

        bank or its commissioned third-party payment

        institution to pursue the payment until the

        authorized person has paid off the money owed

        However, prior to the authorizer's arrears being

        paid, the entrusted transferring method does not

        affect the authorized persons use of other

        methods to remind the authorizer of the arrears.
      </p>
      <p>
2.The authorized person must have sufficient
        balance in the designated account. Otherwise,

        the responsibility lies with the authorized

        person, when it is the lack of balance or any

        reason for not being imputable to the authorized

        persons entrusting party that cause it impossible
        to promptly deduct the funds or deduct the

        wrong funds or fail.
      </p>
      <p>
3.After the effectiveness of the contract is suspended or terminated, the effectiveness of the

        authorization is suspended or terminated at the
        same time, and the authorized person suspends

        or terminates the entrusted payment. After the

        effectiveness of the contract is restored the

        power of this authorization will be restored.
      </p>
      <p>
        4.The power of this authorization takes effect

        from the date of confirmation by the authorizer

        and terminates upon the termination of the
        validity of the contract.
      </p>
      <p>
5. When the authorizer agrees to terminate the authorization or change the account or mailing address.
        he should submit a written notification to the authorized person in 5 working days before the date of delivery of the current payment. All risks and losses arising from the failure to notify the authorized person in a timely manner shall be assumed by the authorized person.
      </p>
      <p>
        6. The authorizer guarantees the authenticity,

        legality and validity of this letter of

        authorization. All legal disputes or risks arising

        from the commissioned deduction by the

        authorized person pursuant to this letter of

        authorization shall be borne or resolved by the

        authorizer independently. The copy, the scanned

        copy and the original of the authorization have

        the same legal effect.
      </p>
      <p>
        7.The information of the borrowing bank

        account that accepted by authorizer:
      </p>
      <p>
Bank card account name:
        {data.bank_card_account_name}
      </p>
      <p>
Bank Card Opening Bank:
        {data.bank_card_opening_bank}
      </p>
      <p>
Bank card account number:
        {data.bank_card_account_number}
      </p>
      <p>
License number:
        {data.license_number}
      </p>
      <p>
Contact phone:
        {data.contact_phone}
      </p>
      <p>
Authorizer confirmation:
        {data.Authorizer_confirmation}
      </p>
    </div>
  );
};

export default UserCommissionedAgreement;
