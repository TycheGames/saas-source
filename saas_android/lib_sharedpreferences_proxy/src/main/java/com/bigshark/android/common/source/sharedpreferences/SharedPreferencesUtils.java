package com.bigshark.android.common.source.sharedpreferences;

import android.content.Context;
import android.content.SharedPreferences;
import android.support.annotation.NonNull;

/**
 * SharedPreferences的帮助类
 */
public class SharedPreferencesUtils {

    private SharedPreferences sharedPreferences;

    public SharedPreferencesUtils(@NonNull Context context, @NonNull String spGroupName) {
        this.sharedPreferences = context.getSharedPreferences(spGroupName, Context.MODE_PRIVATE);
    }

    public SharedPreferences sp() {
        return sharedPreferences;
    }

    public SharedPreferences.Editor edit() {
        return sharedPreferences.edit();
    }

    public void clear() {
        sharedPreferences.edit().clear().apply();
    }
}
