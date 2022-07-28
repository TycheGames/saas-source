package com.bigshark.android.mmkv;

import com.bigshark.android.utils.StringConstant;
import com.tencent.mmkv.MMKV;

/**
 * 数盟的
 */
public class MmkvSzlmeng {

    private final MMKV mmkv;

    private MmkvSzlmeng() {
        mmkv = MMKV.mmkvWithID(StringConstant.MMKV_GROUP_SZLMENG);
    }

    private static final class Helper {
        private static final MmkvSzlmeng INSTANCE = new MmkvSzlmeng();
    }

    public static MmkvSzlmeng instance() {
        return Helper.INSTANCE;
    }

    public void encodeQueryId(String queryId) {
        mmkv.encode(StringConstant.MMKV_API_SHUZILM_KEY_QUERY_ID, queryId);
    }

    public String getQueryId() {
        return mmkv.decodeString(StringConstant.MMKV_API_SHUZILM_KEY_QUERY_ID, "");
    }

}
