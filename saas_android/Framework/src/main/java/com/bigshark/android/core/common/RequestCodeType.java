package com.bigshark.android.core.common;

/**
 * startActivityForResult中的requestCode的枚举，所有的requestCode，都在这里
 *
 * @author Administrator
 * @date 2017/7/29
 */
public class RequestCodeType {

    private static final int BASE_RC = 2000;

    public static final int WEBVIEW_OPEN_FILE_CHOOSER_CAMERA = 7001;
    public static final int WEBVIEW_OPEN_FILE_CHOOSER_CHOOSE = 7002;

    public static final int GOTO_DF_FACE = BASE_RC + 5;// accuauth的人脸认证

    // 紧急联系人认证
    public static final int CONTACT_URGENT_CONTACT = BASE_RC + 7;
    public static final int CONTACT_OTHER_CONTACT = BASE_RC + 8;

    public static final int GET_GPS_LOCATION = BASE_RC + 9;


    // 认证
    public final static int PANCARD = BASE_RC + 111;
    public final static int AADHAAR_MUST = BASE_RC + 112;
    public final static int AADHAAR = BASE_RC + 113;
    public final static int VOTER_CARD = BASE_RC + 114;
    public final static int POSSPART = BASE_RC + 115;
    public final static int DRIVER = BASE_RC + 116;

}
