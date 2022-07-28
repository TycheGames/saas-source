package com.bigshark.android.mmkv;

/**
 * SharedPreferences的帮助类
 */
public class MmkvGroup {

    private MmkvGroup() {
    }

    public static MmkvApp app() {
        return MmkvApp.instance();
    }

    public static MmkvGlobal global() {
        return MmkvGlobal.instance();
    }

    public static MmkvData data() {
        return MmkvData.instance();
    }

    public static MmkvLoginInfo loginInfo() {
        return MmkvLoginInfo.instance();
    }

    public static MmkvSzlmeng szlmeng() {
        return MmkvSzlmeng.instance();
    }
}
