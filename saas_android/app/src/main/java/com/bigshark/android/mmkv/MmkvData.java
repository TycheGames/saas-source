package com.bigshark.android.mmkv;

import com.bigshark.android.utils.StringConstant;
import com.tencent.mmkv.MMKV;

/**
 * Created by Administrator on 2017/3/13.
 * 上传数据：通讯录、短信、电话记录、APP列表、相册的meta数据
 */
public class MmkvData {

    private final MMKV mmkv;

    private MmkvData() {
        mmkv = MMKV.mmkvWithID(StringConstant.MMKV_GROUP_DATAS);
    }

    private static final class Helper {
        private static final MmkvData INSTANCE = new MmkvData();
    }

    public static MmkvData instance() {
        return Helper.INSTANCE;
    }

    public long getCallLogUploadTime() {
        return mmkv.decodeLong(StringConstant.MMKV_API_DATA_CALLLOG_UPLOAD_TIME, 0);
    }

    public void uploadCallLogUploadTime() {
        mmkv.encode(StringConstant.MMKV_API_DATA_CALLLOG_UPLOAD_TIME, System.currentTimeMillis());
    }

    public long getSmsUploadTime() {
        return mmkv.decodeLong(StringConstant.MMKV_API_DATA_SMS_UPLOAD_TIME, 0);
    }

    public void uploadSmsUploadTime() {
        mmkv.encode(StringConstant.MMKV_API_DATA_SMS_UPLOAD_TIME, System.currentTimeMillis());
    }
}
