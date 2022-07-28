package com.bigshark.android.common.source.sharedpreferences;

import android.content.Context;
import android.content.SharedPreferences;

/**
 * Created by Administrator on 2017/4/20.
 */

public class SharedPreferencesApiBase {

    protected SharedPreferences sp;

    public SharedPreferencesApiBase(Context context, String name) {
        this.sp = context.getSharedPreferences(name, Context.MODE_PRIVATE);
    }

    protected SharedPreferences.Editor edit() {
        return sp.edit();
    }

    /**
     * 只能是在测试时使用，用于查看数据，别瞎几把使用这个方法
     */
    @Deprecated
    public SharedPreferences getSp() {
        return sp;
    }

    public void clear() {
        sp.edit().clear().apply();
    }
}
