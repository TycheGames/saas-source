package com.bigshark.android.http.model.app;

import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/23 17:20
 * @描述 省 市 bean
 */
public class ProvinceModel {

    /**
     * name : 北京
     * city : ["北京"]
     */

    private String name;
    private List<String> city;

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public List<String> getCity() {
        return city;
    }

    public void setCity(List<String> city) {
        this.city = city;
    }
}
