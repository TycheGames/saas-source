/**
 * Created by yaer on 2019/9/17;
 * @Email 740905172@qq.com
 * */

import {useState,useEffect} from "react";
import "./index.less";
import ShowPage from "../../components/Show-page";

import {getDemandPromissoryNote} from "../../api";

import {getUrlData} from "../../utils/utils";

import {useDocumentTitle} from "../../hooks";
/*
* query
*  amount  金额
*  days    天数
*  productId 产品id
*  */
const DemandPromissoryNote = props =>{

  useDocumentTitle(props);

  const [show,setShow] = useState(false);

  const [data,setData] = useState({
    name: "",
    company: "",
    money: "",
    interest: "",
    date: "",
    termsAcceptedAt: "",
    device: "",
    deviceId: "",
    ipAddress: ""
  });

  useEffect(()=>{
    getDemandPromissoryNote(getUrlData(props)).then(res=>{
      if (res.code === 0){
          setData(res.data);
        setShow(true);
      }
    })
  },[]);

  return (
    <ShowPage show={show}>
      <div className="demand-wrapper pd20">
        <p className="title">DEMAND PROMISSORY NOTE</p>
        <p className="fs24  lh30">
          On demand I <span className="fw">{data.name}</span> residing at <span className="fw">{data.company}</span> severally promise to pay Kudos Finance & Investments Pvt. Ltd., Pune a sum of Rs. <span className="fw">{data.money}</span> /- together with interest thereon @ <span className="fw">{data.interest}</span> % per annum (with Monthly) or at such other rate as Kudos Finance & Investments Pvt Ltd  may fix from time to time. Presentment for payment and protest of this Note are hereby unconditionally and irrevocably waived.
        </p>

        <p  className="fs24  lh30 fw mt20">Agreed and accepted by the Borrower:</p>
        <div className="table-wrapper">
          <table>
            <tbody>
            <tr>
              <td>Name:</td>
              <td>{data.name}</td>
            </tr>
            <tr>
              <td>Date:</td>
              <td>{data.date}</td>
            </tr>
            <tr>
              <td>Digitally signed by:</td>
              <td>{data.name}</td>
            </tr>
            <tr>
              <td>Terms Accepted at:</td>
              <td>{data.termsAcceptedAt}</td>
            </tr>
            <tr>
              <td>Device:</td>
              <td>{data.device}</td>
            </tr>
            <tr>
              <td>Device ID:</td>
              <td>{data.deviceId}</td>
            </tr>
            <tr>
              <td>IP Address:</td>
              <td>{data.ipAddress}</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>
    </ShowPage>

  )
};

export default DemandPromissoryNote;