/* eslint-disable */
import CryptoJS from "Crypto-jS";

export function Encrypt3Des(str) {
  const aStrKey = "123456789012345678901234";
  const ivstr = "encry012";
  const KeyHex = CryptoJS.enc.Utf8.parse(aStrKey);
  const str2 = CryptoJS.enc.Utf8.parse(str);
  const encrypted = CryptoJS.TripleDES.encrypt(str2, KeyHex, {
    mode: CryptoJS.mode.CBC,
    padding: CryptoJS.pad.Pkcs7,
    iv: CryptoJS.enc.Utf8.parse(ivstr),
  });
  const hexstr = CryptoJS.enc.Base64.stringify(
      CryptoJS.enc.Hex.parse(
          encrypted.ciphertext.toString().toUpperCase(),
      ),
  );
  return hexstr;
}
