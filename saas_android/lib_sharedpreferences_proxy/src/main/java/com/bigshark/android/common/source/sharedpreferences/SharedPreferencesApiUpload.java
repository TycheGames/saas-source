package com.bigshark.android.common.source.sharedpreferences;

import com.bigshark.android.core.component.BaseApplication;

/**
 * Created by Administrator on 2017/3/13.
 * 上传数据：通讯录、短信、电话记录、APP列表、相册的meta数据
 */
public class SharedPreferencesApiUpload {

    private static final String NAME = "upload";

    private final SharedPreferencesUtils spHelper;

    private SharedPreferencesApiUpload() {
        this.spHelper = new SharedPreferencesUtils(BaseApplication.app, NAME);
    }

    private static final class Helper {
        private static final SharedPreferencesApiUpload INSTANCE = new SharedPreferencesApiUpload();
    }

    public static SharedPreferencesApiUpload instance() {
        return Helper.INSTANCE;
    }


    // CallLog的最后上传时间
    private static final String UPLOAD_CALLLOG_UPLOAD_TIME = /*BuildConfig.PACKAGE_NAME +*/ "upload_calllog_upload_time";

    public long getCallLogUploadTime() {
        return spHelper.sp().getLong(UPLOAD_CALLLOG_UPLOAD_TIME, 0);
    }

    public void uploadCallLogUploadTime() {
        spHelper.edit().putLong(UPLOAD_CALLLOG_UPLOAD_TIME, System.currentTimeMillis()).apply();
    }

    // SMS的最后上传时间
    private static final String UPLOAD_SMS_UPLOAD_TIME = /*BuildConfig.PACKAGE_NAME +*/ "upload_sms_upload_time";

    public long getSmsUploadTime() {
        return spHelper.sp().getLong(UPLOAD_SMS_UPLOAD_TIME, 0);
    }

    public void uploadSmsUploadTime() {
        spHelper.edit().putLong(UPLOAD_SMS_UPLOAD_TIME, System.currentTimeMillis()).apply();
    }
}
