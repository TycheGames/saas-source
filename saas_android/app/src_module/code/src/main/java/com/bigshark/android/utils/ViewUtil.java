package com.bigshark.android.utils;

import android.app.Activity;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.content.pm.PackageInfo;
import android.content.pm.PackageManager;
import android.content.pm.PackageManager.NameNotFoundException;
import android.content.pm.ResolveInfo;
import android.support.annotation.NonNull;
import android.support.v4.app.Fragment;
import android.view.inputmethod.InputMethodManager;

import com.bigshark.android.contexts.AppContext;

import java.util.List;

public class ViewUtil {


    public static int getScreenHeight() {
        return AppContext.app.getResources().getDisplayMetrics().heightPixels;
    }


    //获取当前app的版本号
    public static String getAppVersion(Context context) {
        try {
            PackageManager packageManager = context.getPackageManager();
            PackageInfo packInfo;
            packInfo = packageManager.getPackageInfo(context.getPackageName(), 0);
            return packInfo.versionName;
        } catch (NameNotFoundException e) {
            e.printStackTrace();
            return "";
        }
    }

    /**
     * 唤醒第三方APP
     *
     * @param context     上下文
     * @param packageName 唤醒的APP的包名
     * @return
     */
    public static boolean openAppByPackageName(Context context, String packageName) {
        // 通过包名获取此APP详细信息，包括Activities、services、versioncode、name等等
        PackageInfo packageInfo;
        try {
            packageInfo = context.getPackageManager().getPackageInfo(packageName, 0);

            // 创建一个类别为CATEGORY_LAUNCHER的该包名的Intent
            Intent resolveIntent = new Intent(Intent.ACTION_MAIN, null);
            resolveIntent.addCategory(Intent.CATEGORY_LAUNCHER);
            resolveIntent.setPackage(packageInfo.packageName);

            // 通过getPackageManager()的queryIntentActivities方法遍历
            List<ResolveInfo> apps = context.getPackageManager().queryIntentActivities(resolveIntent, 0);
            ResolveInfo ri = apps.iterator().next();
            if (ri != null) {
                Intent intent = new Intent(Intent.ACTION_MAIN);// LAUNCHER Intent
                intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_RESET_TASK_IF_NEEDED);//重点是加这个
                // cls: 这个就是我们要找的该APP的LAUNCHER的Activity[组织形式：packagename.mainActivityname]
                ComponentName cn = new ComponentName(ri.activityInfo.packageName, ri.activityInfo.name);
                intent.setComponent(cn);
                context.startActivity(intent);
                return true;
            }
        } catch (PackageManager.NameNotFoundException e) {
            e.printStackTrace();
        }
        return false;
    }

    public static boolean isFinished(@NonNull Fragment fragment) {
        Activity activity = fragment.getActivity();
        return activity == null || isFinished(activity);
    }

    public static boolean isFinished(@NonNull Activity activity) {
        boolean isFinishing = activity.isFinishing();
        boolean isDestroyed = false;
        if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.JELLY_BEAN_MR1) {
            isDestroyed = activity.isDestroyed();
        }
        return isFinishing || isDestroyed;
    }

    public static void showKeyboard(Activity activity, boolean isShow) {
        InputMethodManager imm = (InputMethodManager) activity.getSystemService(Context.INPUT_METHOD_SERVICE);
        if (isShow) {
            if (activity.getCurrentFocus() == null) {
                imm.toggleSoftInput(InputMethodManager.SHOW_FORCED, 0);
            } else {
                imm.showSoftInput(activity.getCurrentFocus(), 0);
            }
        } else {
            if (activity.getCurrentFocus() != null) {
                imm.hideSoftInputFromWindow(activity.getCurrentFocus().getWindowToken(), InputMethodManager.HIDE_NOT_ALWAYS);
            }
        }
    }

}
