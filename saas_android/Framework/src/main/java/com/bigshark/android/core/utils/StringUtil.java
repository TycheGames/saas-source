package com.bigshark.android.core.utils;

import android.text.Html;
import android.text.SpannableString;
import android.text.Spanned;
import android.text.TextUtils;

import java.util.regex.Matcher;
import java.util.regex.Pattern;


public class StringUtil {

    // 判断字符串对象为null或者""
    public static boolean isBlank(String str) {
        return str == null || str.length() == 0 || "null".equals(str);
    }


    // 电话号码过滤特殊字符
    public static String convertToPhoneNumber(String telephone) {
        if (telephone == null) {
            return null;
        }
        String regEx = "[^0-9]";
        Pattern p = Pattern.compile(regEx);
        Matcher m = p.matcher(telephone);
        String realTelephone = m.replaceAll("");
        return realTelephone == null ? null : realTelephone.trim();
    }

    // 判断是否是手机号码
    public static boolean isMobileNO(String mobiles) {
        if (isBlank(mobiles)) {
            return false;
        }
        Pattern p = Pattern.compile("^1[0-9]{10}$");
        // ^((13[0-9])|(15[^4,\\D])|(18[0,5-9]))\\d{8}$
        Matcher m = p.matcher(mobiles);
        return m.matches();
    }

    /**
     * html String
     *
     * @param htmlStr
     * @return
     */
    public static Spanned getHtml(String htmlStr) {
        if (TextUtils.isEmpty(htmlStr)) {
            return new SpannableString("");
        } else {
            return Html.fromHtml(htmlStr);
        }
    }
}
