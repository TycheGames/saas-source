/**
 * Created by yaer on 2019/9/17;
 * @Email 740905172@qq.com
 * */

import { useState, useEffect } from "react";
import "./index.less";

import ShowPage from "../../components/Show-page";
import { getSanctionLetter } from "../../api";
import { useDocumentTitle } from "../../hooks";

import { getUrlData } from "../../utils/utils";
import aglow from "../../images/sanctionLetter/aglow.png";
import pawan from "../../images/sanctionLetter/pawan.png";

const SanctionLetter = (props) => {
  useDocumentTitle(props);

  const [show, setShow] = useState(false);

  const [data, setData] = useState({
    productName: "",
    headImg: "", // 1 aglow 2 pawan
    date: "",
    customerId: "",
    loanApplicationDate: "",
    sanctionLetterDetail: "",
    borrowerDetail: "",
    lenderDetail: "",
    offerValidityPeriod: "",
    loanPurpose: "",
    loanAmountSanctioned: "",
    availabilityPeriod: "",
    term: "",
    interest: "",
    totalInterestAmount: "",
    processingFees: "",
    repayment: "",
    monthlyInstallmentAmount: "",
    prepaymentCharges: "",
    delayedPaymentCharges: "",
    ECSDishonourCharges: "",
    otherCharges: "",
    documentation: "",
    loanDisbursement: "",
    technicalServiceProviders: "",
    name: "",
    DPNReferenceNO: "",
    termsAcceptedAt: "",
    device: "",
    deviceId: "",
  });

  useEffect(() => {
    getSanctionLetter(getUrlData(props)).then((res) => {
      if (res.code === 0) {
        setData(res.data);
        setShow(true);
      }
    });
  }, []);

  return (
    <ShowPage show={show}>
      <div className="sanction-wrapper pd20">
        {Boolean(data.headImg) && (
          <img
            src={data.headImg === 1 ? aglow : pawan}
            alt=""
            className="header"
          />
        )}
        <p className="lh30 fw">Date and Time: {data.date}</p>
        <p className="lh30 fw">Loan Account Number: {data.DPNReferenceNO}</p>
        <h1 className="title">Subject: Sanction Letter/Approval for Loan</h1>
        <p className="fs24 lh30">Dear Customer,</p>
        <p className="fs24 lh30 fw">
          We are pleased to inform that you are eligible for a loan facility
          from us as per following terms:
        </p>
        <div className="table-wrapper">
          <table>
            <tbody>
              <tr>
                <td>
                  <p>1.</p>
                </td>
                <td>
                  <p>Loan application date</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: data.loanApplicationDate,
                    }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>2.</p>
                </td>
                <td>
                  <p>Details of the Digital/App Partner</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: data.sanctionLetterDetail,
                    }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>3.</p>
                </td>
                <td>
                  <p>Details of Borrower(s)</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{ __html: data.borrowerDetail }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>4.</p>
                </td>
                <td>
                  <p>Details of Lender</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: data.lenderDetail,
                    }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>5.</p>
                </td>
                <td>
                  <p>Validity period of this offer</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: data.offerValidityPeriod,
                    }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>6.</p>
                </td>
                <td>
                  <p>Purpose of the loan</p>
                </td>
                <td>
                  <p dangerouslySetInnerHTML={{ __html: data.loanPurpose }} />
                </td>
              </tr>
              <tr>
                <td>
                  <p>7.</p>
                </td>
                <td>
                  <p>Loan amount sanctioned</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: data.loanAmountSanctioned,
                    }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>8.</p>
                </td>
                <td>
                  <p>Availability period</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: data.availabilityPeriod,
                    }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>9.</p>
                </td>
                <td>
                  <p>Term</p>
                </td>
                <td>
                  <p dangerouslySetInnerHTML={{ __html: data.term }} />
                </td>
              </tr>
              <tr>
                <td>
                  <p>10.</p>
                </td>
                <td>
                  <p>Rate of Interest</p>
                </td>
                <td>
                  <p dangerouslySetInnerHTML={{ __html: data.interest }} />
                </td>
              </tr>
              <tr>
                <td>
                  <p>11.</p>
                </td>
                <td>
                  <p>Total Interest Amount</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: data.totalInterestAmount,
                    }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>12.</p>
                </td>
                <td>
                  <p>Processing fees charged by Digital/App Partner</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{ __html: data.processingFees }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>13.</p>
                </td>
                <td>
                  <p>Repayment</p>
                </td>
                <td>
                  <p dangerouslySetInnerHTML={{ __html: data.repayment }} />
                </td>
              </tr>
              <tr>
                <td>
                  <p>14.</p>
                </td>
                <td>
                  <p>Monthly installment amount</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: data.monthlyInstallmentAmount,
                    }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>15.</p>
                </td>
                <td>
                  <p>Prepayment charges</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{ __html: data.prepaymentCharges }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>16.</p>
                </td>
                <td>
                  <p>Overdue Panel Interest</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: data.delayedPaymentCharges,
                    }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>17.</p>
                </td>
                <td>
                  <p>Cheque /ECS dishonour charges</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{
                      __html: data.ECSDishonourCharges,
                    }}
                  />
                </td>
              </tr>
              <tr>
                <td>
                  <p>18.</p>
                </td>
                <td>
                  <p>Other charges</p>
                </td>
                <td>
                  <p dangerouslySetInnerHTML={{ __html: data.otherCharges }} />
                </td>
              </tr>
              <tr>
                <td>
                  <p>19.</p>
                </td>
                <td>
                  <p>Documentation</p>
                </td>
                <td>
                  <p dangerouslySetInnerHTML={{ __html: data.documentation }} />
                </td>
              </tr>
              <tr>
                <td>
                  <p>20.</p>
                </td>
                <td>
                  <p>Loan disbursement</p>
                </td>
                <td>
                  <p
                    dangerouslySetInnerHTML={{ __html: data.loanDisbursement }}
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <p className="lh30 fs24 mt20">
          The terms of this loan sanction shall also be governed by General
          Terms and Conditions, copies of which is also available on{" "}
          {data.technicalServiceProviders}&nbsp; website, which you may kindly
          read before confirming your acceptance. The said documents are
          incorporated here. The Borrower's acceptance to the terms of this
          letter and the General Terms and Conditions should be informed to the
          Lender ({data.technicalServiceProviders}) by submission of a Most
          Important Documents (KYC) with the terms understood by the Borrower.
          Further, each of the Borrower shall be jointly and severally
          responsible for compliance to thhhe terms of this loan sanction and
          for repayment of the loan amount disbursed.
        </p>
        <p className="lh30 fs24 mt20">
          This sanction letter will only be a letter of offer and shall stand
          revoked and cancelled, if there are any material changes in the
          proposal for which the Loan is sanctioned or; If any event occurs
          which, in the&nbsp;
          {data.technicalServiceProviders} sole opinion is prejudicial to
          the&nbsp;
          {data.technicalServiceProviders} interest or is likely to affect the
          financial condition of the Borrower or his / her/ their ability to
          perform any obligations under the loan or; any statement made in the
          loan application or representation made is found to be incorrect or
          untrue or material fact has concealed or; upon completion of the
          validity period of this offer unless extended by us in writing.
        </p>
        <p className="lh30 fs24 mt20 fw">
          We are pleased to inform that you are eligible for a loan facility
          from us as per following terms:
        </p>
        <p className="lh30 fs24 mt20 fw">
          Agreed and Accepted by the Borrower:
        </p>
        <p className="lh30 fs24 mt20 fw">Name: {data.name}</p>
        <p className="lh30 fs24 mt20 fw">
          DPN Reference No: {data.DPNReferenceNO}
        </p>
        <p className="lh30 fs24 mt20 fw">
          Terms Accepted at: {data.termsAcceptedAt}
        </p>
        <p className="lh30 fs24 mt20 fw">Device: {data.device}</p>
        <p className="lh30 fs24 mt20 fw">Device ID: {data.deviceId}</p>
        {data.headImg === 1 && (
          <div className="footer">
            <h2>Aglow Fintrade Private Limited</h2>
            <p>
              Digital Lending Branch: - 205, D R Chambers, 12/56, Desh Bandu
              Gupta Road, Karol Bagh, New Delhi-110005
            </p>
            {/* <p>Email ID: admin@aglowfin.com</p> */}
            <p>UID:- U67190DL1994PTC060061</p>
            <p>Website: www.aglowfin.com</p>
          </div>
        )}
      </div>
    </ShowPage>
  );
};

export default SanctionLetter;
