package com.bigshark.android.vh.main.home;

import java.text.DecimalFormat;

/**
 * 金额文案格式化(显示)工具
 */
public class MoneyDisplayerUtils {

    public static String formatMoneyText(String money) {
        String[] moneys = money.split("\\.");
        String result = formatMoneyText(Integer.parseInt(moneys[0]));
        result += moneys.length > 1 ? result + moneys[1] : "";
        return result;
    }

    public static String formatMoneyText(int value) {
        return formatMoneyText("###,###", value);
    }

    /**
     * - format("###,###.##", 111222.34567)  ==> 111,222.35
     */
    private static String formatMoneyText(String pattern, int value) {
        DecimalFormat df = new DecimalFormat(pattern);
        return df.format(value);
    }
}
