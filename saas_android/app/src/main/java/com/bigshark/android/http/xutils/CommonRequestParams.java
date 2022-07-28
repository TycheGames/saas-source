package com.bigshark.android.http.xutils;

import com.bigshark.android.core.xutilshttp.RequestHeaderUtils;

import org.xutils.http.RequestParams;
import org.xutils.http.app.ParamsBuilder;

import java.util.Map;


/**
 * 请求
 */
public class CommonRequestParams extends RequestParams {


    public CommonRequestParams() {
        super();
        initCommonData();
    }

    public CommonRequestParams(String uri) {
        super(uri);
        initCommonData();
    }

    public CommonRequestParams(String uri, ParamsBuilder builder, String[] signs, String[] cacheKeys) {
        super(uri, builder, signs, cacheKeys);
        initCommonData();
    }

    private void initCommonData() {
        setConnectTimeout(1000 * 30);
        setReadTimeout(1000 * 30);

        setUseCookie(false);
        for (Map.Entry<String, String> cookieEntry : RequestHeaderUtils.getCookies().entrySet()) {
            setHeader(cookieEntry.getKey(), cookieEntry.getValue());
        }

        for (Map.Entry<String, String> cookieEntry : RequestHeaderUtils.getHeaders().entrySet()) {
            setHeader(cookieEntry.getKey(), cookieEntry.getValue());
        }
    }


    private String commonBodyContentType;

    public String getBodyContentType() {
        return commonBodyContentType;
    }

    @Override
    public void setBodyContentType(String bodyContentType) {
        super.setBodyContentType(bodyContentType);
        this.commonBodyContentType = bodyContentType;
    }
}
