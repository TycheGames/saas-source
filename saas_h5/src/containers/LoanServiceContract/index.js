/* eslint-disable react/no-unescaped-entities */
/**
 * Created by yaer on 2019/7/26;
 * @Email 740905172@qq.com
 * */
import { useEffect, useState } from "react";
import { useDocumentTitle } from "../../hooks";
import { getLoanServiceData } from "../../api";
import "./index.less";
import { getUrlData } from "../../utils/utils";
import {getAppAttributes} from "../../nativeMethod";

const {packageName} = window.appInfo;
/**
 *
 * @param props
 * @returns {*}
 * @constructor
 *
 * query
 *  amount  金额
 *  days    天数
 *  productId 产品id
 */

const LoanServiceContract = (props) => {
  useDocumentTitle(props);

  const [data, setData] = useState({
    contract_number: "",
    borrower: "",
    aadhaar_number: "",
    phone: "",
    lender: "",
    service_provider: "",
    loan_amount: "",
    loan_interest_rate: "",
    term_of_loan: "",
    loan_start_date: "",
    loan_expiring_date: "",
    account_number: "",
    account_name: "",
    bank_of_deposit: "",
    signing_date: "",
  });

  useEffect(() => {
    getData();
  }, []);

  function getData() {
    getLoanServiceData(getUrlData(props)).then((res) => {
      setData(res.data);
    });
  }

  return (
    <div className="loan-service-contract-wrapper">
      <h1>Loan Service Contract</h1>
      <p>
Contract Number:
        {data.contract_number}
      </p>
      <p>
Party A (Borrower):
        {data.borrower}
      </p>
      <p>
Aadhaar number:
        {data.aadhaar_number}
      </p>
      <p>
Tel:
        {data.phone}
      </p>
      <p>
Party B (Lender):
        {data.lender}
      </p>
      <p>
Party C (Service provider):
        {data.service_provider}
      </p>
      <h3>Definitions:</h3>
      <p>The following words or expressions are defined as follows unless otherwise specified in the Contract:</p>
      <p>a. Borrower: It refers to the natural person audited and recommended by Party C and having full rights in civil affairs and capacity for action.</p>
      <p>b. Lender: It refers to the India non-bank financial institution who signs a cooperative agreement with Party C and has legal lending qualification to lend a certain amount of money to Borrower.</p>
      <p>
c. Service provider: It refers to the {packageName} website or {packageName} mobile client operated and managed by Party C, whose domain name is www.{packageName}.net.
        Whereas, Party C is the principal part which operates and manages {packageName} and Party A and Party B fully
        comprehend the content of the Contract, all parties, in accordance with the the principle of mutual equality and voluntarism, reach the Contract as follows on related matters that Party A (Borrower) borrows money from Party B (Lender) on the {packageName}:
      </p>
      <h3>Article 1 Borrowing Amount, Term, Interest Rate and Repayment Method</h3>
      <p>
1.1 Party A agrees to borrow money from Party B through {packageName} and Party B agrees to give out these loans to Party A through {packageName}, under this condition, it is still regarded that the loan is provided by Party B to Party
        A. The basic information of loan is shown as follows:
      </p>
      <p>
Loan start date:
        {data.loan_start_date}
      </p>
      <p>
Loan expiring date:
        {data.loan_expiring_date}
      </p>
      <p>
Term of loan (days):
        {data.term_of_loan}
      </p>
      <p>
Loan amount (INR):
        {data.loan_amount}
      </p>
      <p>
Loan interest rate: 
        {" "}
        {data.loan_interest_rate}
      </p>
      <p>1.2 Collection and repayment account designated by Party A (hereinafter referred as "collection and repayment account"):</p>
      <p>
Account name:
        {data.account_name}
      </p>
      <p>
Bank of deposit:
        {data.bank_of_deposit}
      </p>
      <p>
Account number:
        {data.account_number}
      </p>
      <h3>Article 2 Effectiveness of the Contract</h3>
      <p>2.1 Transfer of loan funds: Party B transfers the loan funds to the Party A's account. If the transfer is completed, the loan shall be deemed to be successfully given out, the interest and related expenses of the loan will be calculated, and this day shall be the value date.</p>
      <p>2.2 Effectiveness of the Contract: The Contract shall come into force if Party B has finished the transfer of all loan funds under the Contract.</p>
      <h3>Article 3 Fees and Expense of Taxation</h3>
      <p>3.1 Party C shall have right to collect the platform service fees from Party A about the service provided for the Contract. The standard of 7 days term loan service fees is shown as follows:</p>
      <div className="table-wrapper">
        <table>
          <tbody>
            <tr>
              <td>Credit Assessment Charges</td>
              <td>2.5 % of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Loan Processing Charges</td>
              <td>2.5 % of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Face Recognition Charges</td>
              <td>1.9 % of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Statement of Account Charges</td>
              <td>1.9 % of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Amortization Schedule Charges</td>
              <td>1.9 % of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Cheque Swapping Charges</td>
              <td>	1.3% of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Pre-payment Charges</td>
              <td>1.3% of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Legal and incidental Charges</td>
              <td>At actuals</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p>
        The standard of 14 days term loan loan service fees is shown as follows:
      </p>
      <div className="table-wrapper">
        <table>
          <tbody>
            <tr>
              <td>Credit Assessment Charges </td>
              <td>	3% of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Loan Processing Charges</td>
              <td>	3% of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Face Recognition Charges</td>
              <td>2.3% of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Statement of Account Charges</td>
              <td>	2.3% of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Amortization Schedule Charges</td>
              <td>	2.3% of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Cheque Swapping Charges</td>
              <td>	1.6% of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Pre-payment Charges</td>
              <td>	1.6% of the loan amount plus GST</td>
            </tr>
            <tr>
              <td>Legal and incidental Charges</td>
              <td>At actuals</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p>
        Party A agrees that Party C has right to collect 
        {" "}
        <span className="red">13.3%+GST of 7 day term loan amount and 16.1% +GST of 14 day term loan amount</span>
        {" "}
as the platform service fees from Party A, the fees among with the relevant expenses of
        taxation will be collected by Party B while the loan is given out in accordance
        with the repayment method and the interest in Article 1.1 of this Contract, Party
        B shall transfer the relevant fees belonging to Party C to Party C's
        account on the day of granting of loans and Party A agrees that Party C can adjust the charging method based on the actual situation.
      </p>

      <p>
3.2 Party C shall have right to collect the platform using fees from Party B about the service provided for the Contract. Party B shall agree that Party C has right to collect 10%
        of Party B's incomes from investment as the platform using fees. But currently Party C agrees that these fees are derated until Party C informs Party B to pay the platform using fees and gains the constant of Party B.
      </p>
      <p>
3.3 Party A and Party B agree that Party C has right to deduct the platform using fees and platform service fees from the accounts of Party A and Party B in accordance
          with the fee standards and payment method agreed in this Contract.
      </p>
      <p>
3.4 Party A and Party B shall bear expenses of taxation which they shall bear at their own expense and agree that Party C has right to deduct the relevant expenses of taxation from
            accounts of Party A and Party B as the tax withholding and remitting obligator in accordance with requirements of relevant laws and regulations.
      </p>
      <h3>Article 4 Repayment Method</h3>
      <p>
4.1 Party A must fully repay the loan principal to Party B in the expiry date of loan agreed in the Contract. Party A shall be deemed that it authorizes the cooperative payment institute
        to transfer the repayment funds to Party B's account when it signs the Contract. When the transfer is completed, Party B's repayment obligations shall be deemed to be fulfilled and Party B shall be also deemed to have successfully received the repayment.
      </p>
      <p>4.2 Even if the agreed repayment date is the legal holidays or public holidays, the repayment date shall be not postponed.</p>
      <p>4.3 If the agreed repayment date have no relevant date in one month, the repayment date shall be the last day in this month.</p>
      <p>4.4 The repayment order shall be: default interests (if any) first, loan principal next.</p>
      <p>4.5 Repayment methods shall be divided into voluntary repayment and automatic repayment.</p>
      <p>(1) Voluntary repayment: Party A may initiate the repayment in the APP, or choose to repay the loan principal through the Internet bank transfer or bank remittance to the account designated by Party B.</p>
      <p>
(2) Automatic repayment (withholding repayment): Party A shall deposit enough repayment amount
        in the repayment account in the day before the repayment date and irrevocably authorize the bank of deposit of Party B's repayment account or the cooperative payment institute to deduct the loan principal under the Contract from its repayment account.
        Party A promises that the above authorization instructions shall be deemed to be equivalent to Party B's own actions. The notice shall prevail for the deduction time, amount and other instructions and all responsibilities of deduction shall be borne by Party A.
      </p>
      <p>
4.6 Party A promises that its promise and instructions follow the Indian laws, regulations and it
        doesn't infringe the legal rights and interests of the third payment institute, Lender and other
        third party when it uses the authorized deduction or automatic repayment service, otherwise, the relevant institute has right to immediately and unilaterally terminate to provide this service and to not bear any responsibilities.
      </p>
      <p>
4.7 Party A herein affirms and authorize that Party B has right to reduce the deduction
        limitation and send out the reduction instructions through the bank of deposit of the repayment account or the cooperative payment institute for multiple times when the balance of Party B's account is insufficient.
      </p>
      <p>
4.8 Party A shall not alter and cancel Party A's repayment account except for special
        circumstances when Party A fails to settle all the loan fees, principal and interests. Under the special conditions, Party B's repayment account can be altered upon the examination and approval of Lender, but the loan function will be limited.
      </p>
      <h3>Article 5 Repayment in Advance</h3>
      <p>
5.1 Party A has right to ask to repay all remainder principal in advance and Party B shall authorize
        Party C to decide whether the application for repayment in advance asked by Party A is agreed. Where Party B's application for repayment in advance is permitted, Party A shall pay the loan principal for repayment in advance to Party B and the collected interests and service
        fees will not be reimbursed. At present, under the normal condition of loan, Party B doesn't accept the application for partial repayment in advance.
      </p>
      <h3>Article 6 Transfer of Creditor's Rights</h3>
      <p>
6.1 Party A acknowledges and agrees that Party B has right to transfer all or partial
          creditor's rights under the Contract at any time to any third party or buy back the creditor's rights which were transferred to Party B from the third party at any time.
      </p>
      <p>
        6.2 Where Party B transfers its all or partial creditor's rights to the third party, Party
        B shall authorize Party C to inform Party A in any written form, such as letters, mails, messages and APP messages. Party B shall authorize Party C that
        Party C or Party C's cooperative party gives an announcement to Party A if the transfer or
        buy-back behaviors occur. This transfer of creditor's rights has legal force for Party B since Party C or Party C's cooperative party makes an announcement to Party
        A and Party A shall fulfill the repayment obligations in accordance with the repayment method agreed in the Contract whether or not Party A sees the announcement of transfer of creditor's rights.
        6.3 In the contract where Party B transfers its all or partial creditors'
        rights to a third party, the corresponding rights and obligations will be transferred to the assignee of the creditors' rights, including but not limited to interests, service fees, penalty and other rights as well as other contractual obligations.
      </p>
      <h3>Article 7 Terms and Conditions of Authorization</h3>
      <p>
        7.1 Party A agrees and authorizes that Party C collects Party A's individual information and the credit information related to credit and loan under the premise
        of abidance by laws and regulations and can provide relevant information to various credit agencies.
      </p>
      <p>
7.2 Party B authorizes Party C to carry out the following-up management of this loan, including but not limited to the information of creditor's rights, keeping contract, monitoring repayment and other relevant work after loan (such as transferring this creditor's rights
        for Party B and overdue treatment when Party C regards it necessary) and Party C has sub-entrustment right.
      </p>
      <h3>Article 8 Information Inquiry and Disclosure</h3>
      <p>
8.1 Party A agrees and authorizes that Party C has right to upload the credit information produced at Party C's place to the third credit institutes, including but not limited to Aadhaar, CIBIL,Pan card or other credit database that relevant departments
        approve to build for the purposes specified in the relevant laws,
        regulations, articles and normative documents; And Party A agrees and authorizes Party C and its cooperative institutes to inquire about the individual credit information in the above said credit institutes from the credit institutes.
      </p>
      <p>8.2 Party A agrees that Party C can reasonably use and reveal Party A's partial or all information in the {packageName} or to Party A's cooperative institutes due to the demand of businesses.</p>
      <h3>Article 9 Overdue Repayment</h3>
      <p>9.1 Where Party B's account doesn't receive or doesn't fully receive Party A's due repayment in the agreed repayment date, it is deemed as Party A's overdue payment.</p>
      <p>
9.2 The daily overdue penalty of 2% of loan principal shall be paid
        during the overdue period. Both Party A and Party B agree it can be determined in accordance with relevant rules of {packageName} and the relevant adjustment can be made. Where there are changes in relevant rules, {packageName} will announce the change of rules in the APP.
      </p>
      <h3>Article 10 Responsibility for Breach of Contract</h3>
      <p>
10.1 Where Party A seriously violates or infringe the statement, guarantee
        and promise under the Contract, provides false information, purposely conceals important facts,
        or transfers the loan debt under the Contract without consent of Party B, Party B shall be deemed to deliberately break the Contract， Party B can authorize Party C to terminate the Contract in advance, and Party A shall pay the remainder loan
        principal at once to Party B's account within 5 working days since Party C puts forward to terminate the Contract.
      </p>
      <p>10.2 Where any one or several following situations occur, it is deemed that Party A seriously break the contract:</p>
      <p>(1) Any properties of Party A are forfeited, confiscated, sealed up and frozen or occur the adverse events which may influence its ability of performance and Party A can't provide effective remedial measures in time.</p>
      <p>(2) The financial conditions of Party A arise adverse changes that affect its performance ability and Party A can't provide effective remedial measures.</p>
      <p>
10.3 Where situations said in Article 10.2 arise, or Party C reasonably
        judge that the violation events said in Article 10.2 may arise, or events or situations which may produce adverse influences on the performance of repayment obligations of Party A under the Contract arise, and Party B regards that,
        in accordance of its reasonable judgement, above situations may cause losses, damages or adverse influences on the rights, inviolable rights or interests, Party B and Party C can make following one measure or several measures.
      </p>
      <p>(1) Immediately suspend or cancel the issuance of all loans;</p>
      <p>(2) Declare that all the loans issued expire in advance and Party A shall immediately repay all payables;</p>
      <p>(3) Terminate the Contract in advance;</p>
      <p>(4) Make other relief measures agreed in laws, regulations and the Contract.</p>
      <p>10.4 Party B authorizes Party C to reserve the right to disclose Party A's information about breach of contract and breaking promise to medias. The investigation and litigation expenses brought by Party A's failure of repayment shall be borne by Party A.</p>
      <h3>Article 11 Change Notice</h3>
      <p>
11.1 During the period from the date of signing the Contract to the
        date of total fulfillment of loans, where any information (including but not limited to Party A's name, ID card number, contact information, contact address, occupational information, etc.) that Party A provide to Party C and/or recommended loan
        agencies is changed, Party A shall provide updated information to Party C within 5 working days since the information is changed and submit the relevant certification documents.
      </p>
      <p>
11.2 The investigation and litigation expenses caused by the failure of Party
          A to provide the above change information shall be borne by Party A.
      </p>
      <h3>Article 12 Statements, Guarantee and Promises</h3>
      <p>12.1 Party A affirms that it is a main body which has full rights in civil affairs and full capacity for civil conduct and it has right to sign and perform the Contract, and it fully acknowledges and can undertake the possible risks of the loan behaviors.</p>
      <p>
12.2 Party A states and promises that: All information provided by Party A for
        the handling of personal loans is complete, true, accurate and non-misleading. There are no situations that may affect Party A's credit, such as Party A's lawsuit, arbitration, administrative procedures, etc., in any form, whether or not they are ongoing or potential;
        Any losses of Party B and Party C due to any untrue and inaccurate statements shall be fully compensated by Party A.
      </p>
      <p>12.3 Party A promises that it has the repayment ability matching with the loan amount and repay it in accordance of the agreement of the Contract.</p>
      <p>12.4 Party A promises that it will not embezzle the loan funds or use the loan funds for the following purposes and intentions:</p>
      <p>(1) Entering the securities market in any form or the equity capital investment;</p>
      <p>(2) The development of real estate project, purchase of houses, over-the-counter funding of real estate and other purposes;</p>
      <p>(3) Gambling, lending, etc.;</p>
      <p>(4) Other various activities clearly prohibited or limited by national laws and regulations.</p>
      <p>
For the avoidance of doubt, Party A hereby agrees and affirms that, even if
        Party C's cooperative is not the signatory party of this Agreement, it shall be deemed that Party
        B has issued authorization documents to the cooperative party of Party C when it signs the Contract and the cooperative party of Party C has right to exercise relevant rights in accordance with Party A's authorization under the Contract.
      </p>
      <h3>Article 13 Confidentiality</h3>
      <p>
13.1 Every party shall regard the relevant content gained in the Contract and its
        ancillary contracts as well as the signing and performance processes of documents and any documents, materials or information related to these content (hereinafter referred to as "Confidential information") as the confidential information.
      </p>
      <p>13.2 The confidential information can be disclosed under the following situations:;</p>
      <p>(1) The disclosure behaviors have been authorized;</p>
      <p>(2) The information has been known to the public;</p>
      <p>(3) The information are required to be disclosed by any applicable laws, or competent judiciary authorities, government authorities and regulatory agencies, or the adjudication of courts;</p>
      <p>(3) Gambling, lending, etc.;</p>
      <p>(4) The information is disclosed when the relevant party rightfully performs the Contract or is required to be disclosed in accordance with the Contract;</p>
      <p>(5) The information is gained by the relevant party from the third party in which the obligation of maintaining confidentiality need not be borne.</p>
      <p>13.3 Whether or not the Contract is effective or is fully performed, the obligation of maintaining confidentiality specified in this Article shall be not affected herein.</p>
      <p>13.4 The confidentiality clauses shall continue to be effective after the Contract is legally dissolved, terminated or fully performed.</p>
      <h3>Article 14 Notification and Delivery</h3>
      <p>14.1 When the Contract is signed, Party A shall submit or authorize Party C to submit its contact information to Party B, including but not limited to the residential address, telephone number, E-mail, name and telephone number of the emergency contact person, etc.</p>
      <p>
14.2 The notification and/or documents made by Party A, Party B and Party C in accordance
        with the Contract can be delivered by special person, or in means of mail delivery, EMS, fax, message,
        E-mail, APP announcement and APP information or the platform of Party C and/or its cooperative party.
        Specific address / code address shall be subject to this Contract or platform registration information
        or enrollment information. Where addresses / code addresses agreed this Contract or in the platform
        registration information or enrollment information are different, all of them shall be regarded as the effective delivery addresses. The above addresses shall be the document delivery addresses during all dispute resolution processes of every party.
      </p>
      <p>14.3 The notification shall be regarded to be delivered in the following dates:</p>
      <p>(1) The notification shall be regarded to be delivered when messages and E-mails are sent out;</p>
      <p>(2) The notification delivered by the special person shall be regarded to be effectively delivered on the delivery date of the person's delivery;</p>
      <p>(3) The notification delivered in means of registered mails (the postage is paid off) shall be regarded to be effectively delivered within five (5) working days after sending (postmark as the evidence);</p>
      <p>(4) The notification delivered in means of EMS (the postage is paid off) shall be regarded to be effectively delivered within three (3) working days after sending (postmark as the evidence);</p>
      <p>(5) The notification issued in the platform of Party C and/or its cooperative party shall be regarded to be effectively delivered in the insurance day.</p>
      <p>14.4 Where any party of the Contract changes the name, address and contact person or interrupts the communication, it shall notify the other party in written within 3 days after change, otherwise, the original delivery address shall be still deemed to be effective.</p>
      <p>14.5 Terms and condition for delivery and settlement of dispute of the Contract are independent terms and conditions, which are not affected by the effectiveness of whole Contract or other articles.</p>
      <h3>Article 15 Methods of Dispute Resolution</h3>
      <p>
15.1 If every party arises any disputes during the performance of the Contract,
        it shall settle the disputes through friendly negotiation; if the negotiation fails, the disputes shall be submitted to the people's court with jurisdiction in the site of Party C for the litigation settlement.
      </p>
      <h3>Article 16 Miscellaneous</h3>
      <p>
16.1 The Contract is created in forms of electronic text. The Contract shall be regarded
        as the agreement which Party A signs with real intention when Party A signs the Contract and have
        the legal force. Party A shall not deny the force of signed agreement or fail to fulfill the relevant obligations in accordance with these agreements because its account information is embezzled or due to other reasons.
      </p>
      <p>
16.2 Every party affirms that the service provided by Party C for the realization of loan
        relation between Party A and Party B shall not indicate or imply that Party C is a party of legal relation of loan contract between Party A and Party B and relevant risks shall be borne by each party itself to the transaction.
      </p>
      <p>
16.3 Every party affirms that the signing, effectiveness and performance of the Contract
        shall not violate the laws. Where any one or several articles in the Contract violate the applicable laws, this article shall be regarded as an invalid article and this invalid article will not affect the effectiveness of other articles in the Contract.
      </p>
      <p>16.4 Any modification and addition of this Contract shall be made in forms of electronic text of {packageName}.</p>
      <p>There is no text in the remainder of this page.</p>
      <p>
Party A (Signature and seal):
        {data.borrower}
      </p>
      <p>
Party B (Signature and seal):
        {data.lender}
      </p>
      <p>
Party C (Signature and seal):
        {data.service_provider}
      </p>
      <p>
Signing date:
        {data.signing_date}
      </p>
    </div>
  );
};

export default LoanServiceContract;
