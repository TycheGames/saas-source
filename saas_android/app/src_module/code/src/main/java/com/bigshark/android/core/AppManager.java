package com.bigshark.android.core;

import android.app.Activity;
import android.content.Context;
import android.view.View;
import android.view.inputmethod.InputMethodManager;

import java.util.ArrayList;
import java.util.List;

/**
 * @author geyifeng
 * @date 2019-04-23 13:46
 */
public class AppManager {

    private List<Activity> mActivities = new ArrayList<>();

    private static class Holder {
        private static final AppManager INSTANCE = new AppManager();
    }

    public static AppManager getInstance() {
        return Holder.INSTANCE;
    }

    public void addActivity(Activity activity) {
        mActivities.add(activity);
    }

    public void removeActivity(Activity activity) {
//        hideSoftKeyBoard(activity);
        mActivities.remove(activity);
    }

    public List<Activity> getActivities() {
        return mActivities;
    }

    private void hideSoftKeyBoard(Activity activity) {
        View localView = activity.getCurrentFocus();
        InputMethodManager imm = (InputMethodManager) activity.getSystemService(Context.INPUT_METHOD_SERVICE);
        if (localView != null && imm != null) {
            imm.hideSoftInputFromWindow(localView.getWindowToken(), 2);
        }
    }
}
