package com.bigshark.android.utils;

import com.alibaba.fastjson.JSONObject;

import java.lang.reflect.Type;
import java.util.List;

public class ConvertUtil {


    /************
     * json与bean对象互转
     */
    public static String toJsonString(Object object) {
        String result = "";
        try {
            result = JSONObject.toJSONString(object);
        } catch (Exception e) {
            e.printStackTrace();
        }
        return result;
    }

    public static <T> T toObject(String json, Class<T> clazz) {
        T instance_class = null;
        try {
            instance_class = JSONObject.parseObject(json, clazz);
        } catch (Exception e) {
            e.printStackTrace();
        }
        return instance_class;
    }

    public static <T> T toObject(String json, Type type) {
        T instance_class = null;
        try {
            instance_class = JSONObject.parseObject(json, type);
        } catch (Exception e) {
            e.printStackTrace();
        }
        return instance_class;
    }

    public static <T> List<T> toList(String json, Class<T> clazz) {
        List<T> instance_class = null;
        try {
            instance_class = JSONObject.parseArray(json, clazz);
        } catch (Exception e) {
            e.printStackTrace();
        }
        return instance_class;
    }

}
