package com.bigshark.android.utils;

import java.util.ArrayList;
import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/24 19:54
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class DateDataUtil {

    private static String[] months = {"1M", "2M", "3M", "4M", "5M", "6M", "7M", "8M", "9M", "10M", "11M", "12M"};
    private static String[] days   = {"1D", "2D", "3D", "4D", "5D", "6D", "7D", "8D", "9D", "10D", "11D", "12D", "13D", "14D", "15D"
            , "16D", "17D", "18D", "19D", "20D", "21D", "22D", "23D", "24D", "25D", "26D", "27D", "28D", "29D", "30D", "31D"};

    public static List<String> buildYearData() {
        List<String> list = new ArrayList<>();
        list.add("2019");
        list.add("2020");
        return list;
    }

    public static List<String> buildMonthData() {
        List<String> list = new ArrayList<>();
        for (int i = 0; i < months.length; i++) {
            list.add(months[i]);
        }
        return list;
    }

    public static List<String> buildDayData() {
        List<String> list = new ArrayList<>();
        for (int i = 0; i < days.length; i++) {
            list.add(days[i]);
        }
        return list;
    }
}
