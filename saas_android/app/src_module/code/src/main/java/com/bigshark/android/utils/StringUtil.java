package com.bigshark.android.utils;

import android.content.ClipData;
import android.content.Context;
import android.widget.TextView;

import java.util.regex.Matcher;
import java.util.regex.Pattern;


public class StringUtil {

    // 判断EditText内容为null或者""
    public static boolean isBlankEdit(TextView view) {
        return (view == null || view.getText() == null || view.getText().length() == 0);
    }

    // 判断字符串对象为null或者""
    public static boolean isBlank(String str) {
        return (str == null || str.length() == 0 || "null".equals(str));
    }

    // 判断是否是手机号码
    public static boolean isMobileNO(String mobiles) {
        if (isBlank(mobiles))
            return false;
        Pattern p = Pattern.compile("^1[0-9]{10}$");
        // ^((13[0-9])|(15[^4,\\D])|(18[0,5-9]))\\d{8}$
        Matcher m = p.matcher(mobiles);
        return m.matches();
    }

    // 判断是否是登录密码6~16位
    public static boolean isLoginPassword(String password) {
        if (isBlank(password)) {
            return false;
        }
        if (password.length() < 6 || password.length() > 16) {
            return false;
        }
        return true;
    }


    public static void copyText2Clipboard(Context context, String text) {
        if (android.os.Build.VERSION.SDK_INT > 11) {
            android.content.ClipboardManager c = (android.content.ClipboardManager) context.getSystemService(Context.CLIPBOARD_SERVICE);
            c.setPrimaryClip(ClipData.newPlainText("text", text));
        } else {
            android.text.ClipboardManager c = (android.text.ClipboardManager) context.getSystemService(Context.CLIPBOARD_SERVICE);
            c.setText(text);
        }
    }

    /**
     * 给url拼接上use_rid和sex
     *
     * @param url
     * @return
     */
    public static String buildUrlUserIdAndSex(String url) {
//        UserInfoModel userInfoBean = UserCenter.instance().getUserInfo();
//        return url + "?user_id=" + userInfoBean.getUser_id() + "&sex=" + UserCenter.instance().getUserGender();
        return "";
    }
}
