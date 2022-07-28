package com.bigshark.android.core.utils;

import android.content.Context;

import com.alibaba.fastjson.JSONObject;

import java.lang.reflect.Type;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

public class ConvertUtils {

//    /********
//     * 反射获取bean的属性
//     */
//    public static HashMap<String, String> getRequestParams(RequestData bean) {
//        HashMap<String, String> map = new HashMap<String, String>();
//        Field[] fileds = bean.getClass().getDeclaredFields();
//        for (Field field : fileds) {
//            try {
//                getField(map, field, bean);
//            } catch (Exception e) {
//                e.printStackTrace();
//            }
//        }
//        return map;
//    }
//
//    /**
//     * 反射类中所有属性
//     */
//    private static void getField(HashMap<String, String> map, Field field, RequestData model) throws NoSuchMethodException,
//            IllegalAccessException, IllegalArgumentException, InvocationTargetException {
//        String name = field.getName();//获取属性名字
//        String type = field.getGenericType().toString();//获取属性类型
//        if ("class java.lang.String".equals(type)) {
//            try {
//                Class myclass = Class.forName(model.getClassName());
//                Method m = myclass.cast(model).getClass().getMethod("get" + captureName(name));
////				Method m  = Class.forName(model.getClassName()).getClass().cast(model).getMethod("get"+captureName(name));
////				Method m = (Class.forName(model.getClassName()))model.getClass().getMethod("get"+name);
////				Method m = ((Class.forName(model.getClassName())model.getClass()).getMethod("get"+name);
//                String value = (String) m.invoke(model);    //调用getter方法获取属性值
//                if (value != null) {
//                    map.put(name, value);
//                }
//            } catch (Exception e) {
//                e.printStackTrace();
//            }
//        } else if (type.equals("int")) {
//            try {
//                Class myclass = Class.forName(model.getClassName());
//                Method m = myclass.cast(model).getClass().getMethod("get" + captureName(name));
////				Method m  = Class.forName(model.getClassName()).getClass().cast(model).getMethod("get"+captureName(name));
////				Method m = (Class.forName(model.getClassName()))model.getClass().getMethod("get"+name);
////				Method m = ((Class.forName(model.getClassName())model.getClass()).getMethod("get"+name);
//                String value = m.invoke(model) + "";    //调用getter方法获取属性值
//                if (value != null) {
//                    map.put(name, value);
//                }
//            } catch (Exception e) {
//                e.printStackTrace();
//            }
//        }
////		 if(type.equals("class java.lang.Integer")){
////             Method m = model.getClass().getMethod("get"+name);
////             Integer value = (Integer) m.invoke(model);
////             if(value != null){
////                 System.out.println("attribute value:"+value);
////             }
////         }
////         if(type.equals("class java.lang.Short")){
////             Method m = model.getClass().getMethod("get"+name);
////             Short value = (Short) m.invoke(model);
////             if(value != null){
////                 System.out.println("attribute value:"+value);                    }
////         }
////         if(type.equals("class java.lang.Double")){
////             Method m = model.getClass().getMethod("get"+name);
////             Double value = (Double) m.invoke(model);
////             if(value != null){
////                 System.out.println("attribute value:"+value);
////             }
////         }
////         if(type.equals("class java.lang.Boolean")){
////             Method m = model.getClass().getMethod("get"+name);
////             Boolean value = (Boolean) m.invoke(model);
////             if(value != null){
////                 System.out.println("attribute value:"+value);
////             }
////         }
//        else if (type.equals("class java.util.List")) {
//            Method m = model.getClass().getMethod("get" + captureName(name));
//            List<?> value = (List<?>) m.invoke(model);
//            if (value != null) {
//                map.put(name, JSON.toJSONString(value));
//            }
//        } else {
//            Method m = model.getClass().getMethod("get" + captureName(name));
//            Object value = m.invoke(model);
//            if (value != null) {
//                map.put(name, JSON.toJSONString(value));
//            }
//        }
//    }
//
//    /**
//     * 首字母大写
//     */
//    private static String captureName(String name) {
//        name = name.substring(0, 1).toUpperCase() + name.substring(1);
//        return name;
//
//    }


    /**
     * json与bean对象互转
     */
    public static String toString(Object object) {
        String result = "";
        try {
            result = JSONObject.toJSONString(object);
        } catch (Exception e) {
            e.printStackTrace();
        }
        return result;
    }

    public static <T> T toObject(String json, Class<T> clazz) {
        T instanceClass = null;
        try {
            instanceClass = JSONObject.parseObject(json, clazz);
        } catch (Exception e) {
            e.printStackTrace();
        }
        return instanceClass;
    }

    public static <T> T toObject(String json, Type type) {
        T instanceClass = null;
        try {
            instanceClass = JSONObject.parseObject(json, type);
        } catch (Exception e) {
            e.printStackTrace();
        }
        return instanceClass;
    }

    public static <T> List<T> toList(String json, Class<T> clazz) {
        List<T> instanceClass = null;
        try {
            instanceClass = JSONObject.parseArray(json, clazz);
        } catch (Exception e) {
            e.printStackTrace();
        }
        return instanceClass;
    }

    public static Map<String, Object> toMap(String json) {
        try {
            return JSONObject.parseObject(json);
        } catch (Exception e) {
            e.printStackTrace();
            return new HashMap<>();
        }
    }


    /**
     * 根据手机的分辨率从 dp 的单位 转成为 px(像素)
     */
    public static int dip2px(Context context, float dpValue) {
        final float scale = context.getResources().getDisplayMetrics().density;
        return (int) (dpValue * scale + 0.5f);
    }
}
