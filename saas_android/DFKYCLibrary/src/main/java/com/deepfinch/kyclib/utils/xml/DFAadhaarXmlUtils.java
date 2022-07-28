package com.deepfinch.kyclib.utils.xml;

import android.app.Activity;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.text.TextUtils;
import android.util.Base64;

import com.deepfinch.kyclib.R;
import com.deepfinch.kyclib.model.DFKYCModel;
import com.deepfinch.kyclib.utils.DFKYCUtils;
import com.deepfinch.kyclib.utils.DFSDCardUtils;
import com.deepfinch.kyclib.utils.DFZipUtils;

import net.lingala.zip4j.exception.ZipException;

import org.xml.sax.Attributes;
import org.xml.sax.SAXException;
import org.xml.sax.helpers.DefaultHandler;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;

import javax.xml.parsers.ParserConfigurationException;
import javax.xml.parsers.SAXParser;
import javax.xml.parsers.SAXParserFactory;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFAadhaarXmlUtils {
    private static final String TAG = "DFAadhaarXmlUtils";

    private DFKYCModel mGetFaceModel;

    public DFAadhaarXmlUtils() {
    }

    public DFKYCModel parserXml(File parseFile) {
        mGetFaceModel = new DFKYCModel();
        SAXParserFactory saxfac = SAXParserFactory.newInstance();
        try {
            SAXParser saxparser = saxfac.newSAXParser();
            InputStream is = new FileInputStream(parseFile);
            saxparser.parse(is, new MySAXHandler());
        } catch (ParserConfigurationException e) {
            e.printStackTrace();
        } catch (SAXException e) {
            e.printStackTrace();
        } catch (FileNotFoundException e) {
            e.printStackTrace();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return mGetFaceModel;
    }

    class MySAXHandler extends DefaultHandler {
        boolean hasAttribute = false;
        Attributes attributes = null;
        private String mCurrentQName;

        public void startDocument() throws SAXException {
//            DFKYCUtils.logI(TAG, "startDocument");
        }

        public void endDocument() throws SAXException {
//            DFKYCUtils.logI(TAG, "endDocument");
        }

        public void startElement(String uri, String localName, String qName, Attributes attributes) throws SAXException {
//            DFKYCUtils.logI(TAG, "startElement", qName);
            mCurrentQName = qName;
            if (TextUtils.equals(qName, "Poi")) {
                if (attributes != null) {
                    for (int i = 0; i < attributes.getLength(); i++) {
                        // getQName()是获取属性名称，
                        String innerQName = attributes.getQName(i);
                        String innerValue = attributes.getValue(i);
//                        DFKYCUtils.logI(TAG, "startElement", "getQName", innerQName, "getValue", innerValue);
                        if (TextUtils.equals(innerQName, "name")) {
                            mGetFaceModel.setName(innerValue);
                        }
                        if (TextUtils.equals(innerQName, "gender")) {
                            mGetFaceModel.setGender(innerValue);
                        }
                        if (TextUtils.equals(innerQName, "dob")) {
                            mGetFaceModel.setDob(innerValue);
                        }
                    }
                }
            }

            if (TextUtils.equals(qName, "Poa")) {
                if (attributes != null) {
                    for (int i = 0; i < attributes.getLength(); i++) {
                        // getQName()是获取属性名称，
                        String innerQName = attributes.getQName(i);
                        String innerValue = attributes.getValue(i);
//                        DFKYCUtils.logI(TAG, "startElement", "getQName", innerQName, "getValue", innerValue);
                        if (TextUtils.equals(innerQName, "careof")) {
                            mGetFaceModel.setCareOf(innerValue);
                        }
                        if (TextUtils.equals(innerQName, "house")) {
                            mGetFaceModel.setHouse(innerValue);
                        }
                        if (TextUtils.equals(innerQName, "street")) {
                            mGetFaceModel.setStreet(innerValue);
                        }
                        if (TextUtils.equals(innerQName, "landmark")) {
                            mGetFaceModel.setLandMark(innerValue);
                        }
                        if (TextUtils.equals(innerQName, "loc")) {
                            mGetFaceModel.setLoc(innerValue);
                        }
                        if (TextUtils.equals(innerQName, "po")) {
                            mGetFaceModel.setPo(innerValue);
                        }
                        if (TextUtils.equals(innerQName, "subdist")) {
                            mGetFaceModel.setSubDist(innerValue);
                        }
                        if (TextUtils.equals(innerQName, "dist")) {
                            mGetFaceModel.setDist(innerValue);
                        }
                        if (TextUtils.equals(innerQName, "state")) {
                            mGetFaceModel.setState(innerValue);
                        }
                        if (TextUtils.equals(innerQName, "pc")) {
                            mGetFaceModel.setPc(innerValue);
                        }
                    }
                }
            }

            if (TextUtils.equals(qName, "Pht")) {

            }

            if (TextUtils.equals(qName, "name")) {

            }

            super.startElement(uri, localName, qName, attributes);
        }

        public void endElement(String uri, String localName, String qName) throws SAXException {
//            DFKYCUtils.logI(TAG, "endElement", qName);
            if (hasAttribute && (attributes != null)) {
                for (int i = 0; i < attributes.getLength(); i++) {
//                    DFKYCUtils.logI(TAG, "endElement", "getQName", attributes.getQName(i), "getValue", attributes.getValue(i));
                }
            }
        }

        public void characters(char[] ch, int start, int length) throws SAXException {
            if (TextUtils.equals(mCurrentQName, "Pht")) {
                String imageStr = new String(ch, start, length);
//                DFKYCUtils.logI(TAG, "characters", imageStr);
                byte[] decode = Base64.decode(imageStr, Base64.DEFAULT);
                mGetFaceModel.setAadhaarImage(decode);
            }
        }
    }


    public static DFKYCModel createTestModel(Activity activity) {
        String userInfoDir = DFSDCardUtils.getUserInfoDir(activity);
        String zipPath = DFSDCardUtils.copyAssetsToSD(activity, "result.zip", userInfoDir);
        File[] unZipFileList = null;
        try {
            unZipFileList = DFZipUtils.unzip(zipPath, userInfoDir, "1234");
        } catch (ZipException e) {
            e.printStackTrace();
        }
        DFKYCModel getFaceModel = null;
        if (unZipFileList != null && unZipFileList.length >= 1) {
            getFaceModel = new DFKYCModel();
            File unZipFile = unZipFileList[0];
            DFSDCardUtils.PATH_KEY_FILE = unZipFile.getAbsolutePath();
            DFAadhaarXmlUtils xmlUtils = new DFAadhaarXmlUtils();
            getFaceModel = xmlUtils.parserXml(unZipFile);
            DFKYCUtils.logI(TAG, "parserXml");
        }

        if (getFaceModel == null) {
            DFKYCUtils.logI(TAG, "createTestModel", "解析失败");
            getFaceModel = new DFKYCModel();
            Bitmap bitmap = BitmapFactory.decodeResource(activity.getResources(), R.mipmap.kyc_progress_dlg);
            byte[] image = DFKYCUtils.convertBmpToJpeg(bitmap);
            getFaceModel.setAadhaarImage(image);
            getFaceModel.setName("Vank hede Pankaj Bhausaheb");
            getFaceModel.setDob("12-01-1992");
            getFaceModel.setGender("Male");
        } else {
            DFKYCUtils.logI(TAG, "createTestModel", "解析成功");
        }
        return getFaceModel;
    }
}
