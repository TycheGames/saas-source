package com.bigshark.android.http.xutils;

import android.support.annotation.Nullable;
import android.text.TextUtils;
import android.util.Log;

import com.alibaba.fastjson.JSON;
import com.alibaba.fastjson.JSONArray;
import com.alibaba.fastjson.JSONException;
import com.alibaba.fastjson.JSONObject;
import com.bigshark.android.core.BuildConfig;
import com.bigshark.android.core.common.event.NetWorkWrapperEvent;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.utils.ViewUtil;
import com.bigshark.android.jump.JumpOperationRequest;
import com.bigshark.android.utils.StringConstant;
import com.socks.library.klog.JsonLog;

import org.xutils.common.Callback;
import org.xutils.common.util.KeyValue;
import org.xutils.http.BaseParams;
import org.xutils.http.HttpMethod;
import org.xutils.http.app.RequestInterceptListener;
import org.xutils.http.body.FileBody;
import org.xutils.http.body.RequestBody;
import org.xutils.http.request.HttpRequest;
import org.xutils.http.request.UriRequest;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.lang.reflect.ParameterizedType;
import java.lang.reflect.Type;
import java.net.URLEncoder;
import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

import de.greenrobot.event.EventBus;

/**
 * 对响应体进行封装
 * TODO 贷超包添加：build模式、addFile方法等
 *
 * @param <T> data的数据类型
 */
public abstract class CommonResponseCallback<T> implements Callback.PrepareCallback<String, T>, RequestInterceptListener {

    protected final IDisplay display;
    // 日志打印的行tag
    private String lineTag;

    public CommonResponseCallback(@Nullable IDisplay display) {
        this.display = display;
    }


    public CommonRequestParams createRequestParams() {
        return null;
    }
//    public abstract CommonRequestParams createRequestParams();


    // 用于取消任务
    private Cancelable cancelable;

    public void setCancelable(Cancelable cancelable) {
        this.cancelable = cancelable;
    }


    // 处理UI逻辑
    public void onStarted(HttpMethod method, CommonRequestParams entity) {
        this.lineTag = createRequestLineTag(6);
        printRequestLog(method, entity);
        handleUi(true);
    }

    @Override
    public void onFinished() {
        if (cancelable != null) {
            HttpSender.cancel(cancelable);
            cancelable = null;
        }
        this.lineTag = null;
        handleUi(false);
    }

    public abstract void handleUi(boolean isStart);


    // 处理数据转换逻辑
    @Override
    public T prepare(String rawData) throws Throwable {
        this.rawData = rawData;
        parseResultData(rawData);
        return data;
    }


    //判断是否要重新登录
    private static final int HTTP_RESULT_CODE_UNLOGIN = -2;
    // 获取数据正确
    private static final int HTTP_RESULT_CODE_SUCCESS = 0;

    @Override
    public void onSuccess(T result) {
        if (display != null && ViewUtil.isFinishedForDisplay(display)) {
            return;
        }

        if (display != null) {
            // 处理模拟推送数据
            if (!TextUtils.isEmpty(resultCommand)) {
                new JumpOperationRequest(resultCommand).setDisplay(display).jump();
            }
            if (resultCommands != null && !resultCommands.isEmpty()) {
                for (String resultCommandText : resultCommands) {
                    new JumpOperationRequest(resultCommandText).setDisplay(display).jump();
                }
            }
        }

        // 处理登录态失效
        if (HTTP_RESULT_CODE_UNLOGIN == resultCode) {
            printResponseLog(rawData);
            EventBus.getDefault().post(new NetWorkWrapperEvent(NetWorkWrapperEvent.NETWORK_ERROR_NEED_LOGIN));
            handleFailed(resultCode, resultMessage);
        } else if (HTTP_RESULT_CODE_SUCCESS == resultCode) {
            try {
                printResponseLog(rawData);
                handleSuccess(data, resultCode, resultMessage);
            } catch (Exception e) {
                e.printStackTrace();
            }
        } else {
            try {
                printResponseErrorLog(rawData);
                handleFailed(resultCode, resultMessage);
            } catch (Exception e) {
                e.printStackTrace();
            }
        }
    }

    public abstract void handleSuccess(T resultData, int resultCode, String resultMessage);

    public abstract void handleFailed(int resultCode, String resultMessage);


    @Override
    public void onError(Throwable ex, boolean isOnCallback) {
        if (display != null && ViewUtil.isFinishedForDisplay(display)) {
            return;
        }
        try {
            printResponseErrorLog(ex, isOnCallback);
            handleFailed(resultCode, ex.getMessage());
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    @Override
    public void onCancelled(CancelledException cex) {
    }


    // RequestInterceptListener

    private volatile HttpRequest httpRequest;

    @Override
    public void beforeRequest(UriRequest request) throws Throwable {
        if (request instanceof HttpRequest) {
            this.httpRequest = (HttpRequest) request;
        }
    }

    @Override
    public void afterRequest(UriRequest request) throws Throwable {

    }


    //<editor-fold desc='parse raw data'>

    private volatile String rawData;
    private volatile int resultCode = -111111;
    private volatile String resultMessage;
    private volatile String resultCommand;
    private volatile List<String> resultCommands;
    private volatile T data;

    private void parseResultData(String rawData) {
        //        KLog.json(rawData);
        JSONObject resultObj = JSON.parseObject(rawData);

        resultCode = resultObj.getInteger("code");
        resultMessage = resultObj.getString("message");
        resultCommand = resultObj.getString("extra_command");
        JSONArray extraCommandsJsonArr = resultObj.getJSONArray("extra_commands");
        if (extraCommandsJsonArr == null || extraCommandsJsonArr.isEmpty()) {
            resultCommands = Collections.emptyList();
        } else {
            resultCommands = new ArrayList<>(extraCommandsJsonArr.size());
            for (int i = 0; i < extraCommandsJsonArr.size(); i++) {
                resultCommands.add(extraCommandsJsonArr.getString(i));
            }
        }
//        KLog.d(resultCode);
//        KLog.d(resultMessage);
//        KLog.d(resultCommand);
//        KLog.d(resultCommands);


//        Object data;
        String dataStr = resultObj.getString("data");
//        //判断整个数据是不是由一个List包裹起来的
//        if (resultClass == List.class) {
//            data = JSON.parseArray(dataStr, (Class<?>) ParameterizedTypeUtil.getParameterizedType(resultType, List.class, 0));
//        } else {  //如果不是用List组成的则直接将Json进行解析成vo实体类
//            data = JSON.parseObject(dataStr, resultClass);
//        }

        Type type;
        Type[] types = getClass().getGenericInterfaces();
        if (types.length == 0) {
            type = getClass().getGenericSuperclass();
        } else {
            type = types[0];
        }
//        KLog.d(type);

        ParameterizedType paramType = (ParameterizedType) type;
        type = paramType.getActualTypeArguments()[0];
//        KLog.d(type);

        if ("class java.lang.String".equals(type.toString())) {
            data = (T) dataStr;
        } else {
            try {
                data = JSON.parseObject(dataStr, type);
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    }

    //</editor-fold desc='parse raw data'>


    //<editor-fold desc='print log'>

    private String createRequestLineTag(int stackTraceIndex) {
        if (!BuildConfig.DEBUG) {
            return "RequestLineTag";
        }

        StackTraceElement[] stackTrace = Thread.currentThread().getStackTrace();

        StackTraceElement targetElement = stackTrace[stackTraceIndex];
        String className = targetElement.getClassName();
        String[] classNameInfo = className.split("\\.");
        if (classNameInfo.length > 0) {
            className = classNameInfo[classNameInfo.length - 1] + StringConstant.COMMON_RESPONSE_CALLBACK_JAVA_SUFFIX;
        }

        if (className.contains("$")) {
            className = className.split("\\$")[0] + StringConstant.COMMON_RESPONSE_CALLBACK_JAVA_SUFFIX;
        }

        String methodName = targetElement.getMethodName();
        int lineNumber = targetElement.getLineNumber();

        if (lineNumber < 0) {
            lineNumber = 0;
        }
        return "[ (" + className + ":" + lineNumber + ")#" + methodName + " ] ";
    }

    private void printRequestLog(HttpMethod method, CommonRequestParams entity) {
        if (!BuildConfig.DEBUG) {
            return;
        }

        try {
            String tag = "HttpLog-->Request";
            Log.d(tag, "╔═════════════════════════════════════════════════════════════");
            Log.d(tag, "║" + lineTag);
            Log.d(tag, "║method-->" + method + ", requestUri-->" + entity.getUri());
            printHeaders(tag, entity);
            if (!entity.getQueryStringParams().isEmpty()) {
                printQueryParams(tag, entity);
            }
            if (HttpMethod.permitsRequestBody(method)) {
                printBodyParams(tag, entity);
            }
            Log.d(tag, "╚═════════════════════════════════════════════════════════════");
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private void printHeaders(String tag, CommonRequestParams entity) {
        Log.d(tag, "║═══════════════════════ headers ═══════════════════════");
        for (BaseParams.Header header : entity.getHeaders()) {
            Log.d(tag, "║" + header.key + ":" + header.value.toString());
        }
    }

    private void printQueryParams(String tag, CommonRequestParams entity) {
        Log.d(tag, "║════════════════════════ querys ════════════════════════");
        for (KeyValue queryStringParam : entity.getQueryStringParams()) {
            Log.d(tag, "║" + queryStringParam.key + ":" + queryStringParam.value.toString());
        }
//        RequestBody requestBody = request.body();
//        if (requestBody == null) {
//            return;
//        }
//
//        if (requestBody instanceof FormBody) {
//            printFormBody(tag, (FormBody) requestBody);
//        } else if (requestBody instanceof MultipartBody) {
//            printMultipartBody(tag, (MultipartBody) requestBody);
//        }
    }

    private void printBodyParams(String tag, CommonRequestParams entity) {
        Log.d(tag, "║═════════════════════ body ════════════════════════");
        try {
            RequestBody requestBody = entity.getRequestBody();
            Log.d(tag, "║ requestBodyType-->" + requestBody.getClass());
            Log.d(tag, "║ contentType-->" + requestBody.getContentType());
            Log.d(tag, "║ conetntLenght-->" + requestBody.getContentLength());
        } catch (IOException e) {
            e.printStackTrace();
        }

//        RequestBody result = null;
        if (!TextUtils.isEmpty(entity.getBodyContent())) {
//            result = new StringBody(entity.getBodyContent(), entity.getCharset());
//            result.setContentType(entity.getBodyContentType());
            Log.d(tag, "║StringBody-->" + entity.getBodyContent());
        } else if (entity.isMultipart()) {
//            result = new MultipartBody(entity.getBodyParams(), entity.getCharset());
//            result.setContentType(entity.getBodyContentType());
            printMultipart(tag, entity.getBodyParams(), entity.getCharset());
        } else if (entity.getBodyParams().size() == 1) {
            KeyValue kv = entity.getBodyParams().get(0);
            String name = kv.key;
            Object value = kv.value;
//            String contentType = null;
//            if (kv instanceof BaseParams.BodyItemWrapper) {
//                BaseParams.BodyItemWrapper wrapper = (BaseParams.BodyItemWrapper) kv;
//                contentType = wrapper.contentType;
//            }
//            if (TextUtils.isEmpty(contentType)) {
//                contentType = entity.getBodyContentType();
//            }
            if (value instanceof File) {
//                result = new FileBody((File) value, contentType);
                File file = (File) value;
                Log.d(tag, "║FileBody-->" + name + ":" + file.getAbsolutePath());
            } else if (value instanceof InputStream) {
//                result = new InputStreamBody((InputStream) value, contentType);
                Log.d(tag, "║InputStreamBody-->" + name + ": inputStream");
            } else if (value instanceof byte[]) {
//                result = new InputStreamBody(new ByteArrayInputStream((byte[]) value), contentType);
                Log.d(tag, "║InputStreamBody-->" + name + ": byte[]");
            } else if (TextUtils.isEmpty(name)) {
//                result = new StringBody(kv.getValueStrOrEmpty(), entity.getCharset());
//                result.setContentType(contentType);
                Log.d(tag, "║StringBody-->" + name + ":" + kv.getValueStrOrEmpty());
            } else {
//                result = new UrlEncodedBody(entity.getBodyParams(), entity.getCharset());
//                result.setContentType(contentType);
                Log.d(tag, "║UrlEncodedBody-->" + convertBodyParams2String(entity.getBodyParams(), entity.getCharset()));
            }
        } else if (entity.getBodyParams().size() > 1) {
//            result = new UrlEncodedBody(entity.getBodyParams(), entity.getCharset());
//            result.setContentType(entity.getBodyContentType());
            Log.d(tag, "║UrlEncodedBody-->" + convertBodyParams2String(entity.getBodyParams(), entity.getCharset()));
        } else {
            Log.d(tag, "║EmptyBody");
        }
    }

    private static void printMultipart(String tag, List<KeyValue> multipartParams, String charset) {
        for (KeyValue entry : multipartParams) {
            String name = entry.key;
            Object value = entry.value;
            if (TextUtils.isEmpty(name) || value == null) {
                continue;
            }

            String fileName = "";
            String contentType = null;
            if (entry instanceof BaseParams.BodyItemWrapper) {
                BaseParams.BodyItemWrapper wrapper = (BaseParams.BodyItemWrapper) entry;
                fileName = wrapper.fileName;
                contentType = wrapper.contentType;
            }

            if (value instanceof File) {
                File file = (File) value;
                if (TextUtils.isEmpty(fileName)) {
                    fileName = file.getName();
                }
                if (TextUtils.isEmpty(contentType)) {
                    contentType = FileBody.getFileContentType(file);
                }
                Log.d(tag, buildContentDisposition(name, fileName, charset));
                Log.d(tag, buildContentType(value, contentType, charset));
                Log.d(tag, "print file");
            } else {
                Log.d(tag, buildContentDisposition(name, fileName, charset));
                Log.d(tag, buildContentType(value, contentType, charset));
                if (value instanceof InputStream) {
                    Log.d(tag, "print InputStream");
                } else {
                    byte[] content;
                    if (value instanceof byte[]) {
                        content = (byte[]) value;
                        Log.d(tag, "print byte[]");
                    } else {
                        Log.d(tag, entry.getValueStrOrEmpty());
                    }
                }
            }
        }
    }

    private static String buildContentDisposition(String name, String fileName, String charset) {
        StringBuilder result = new StringBuilder("Content-Disposition: form-data");
        result.append("; name=\"").append(name.replace("\"", "\\\"")).append("\"");
        if (!TextUtils.isEmpty(fileName)) {
            result.append("; filename=\"").append(fileName.replace("\"", "\\\"")).append("\"");
        }
        return result.toString();
    }

    private static String buildContentType(Object value, String contentType, String charset) {
        StringBuilder result = new StringBuilder("Content-Type: ");
        if (TextUtils.isEmpty(contentType)) {
            if (value instanceof String) {
                contentType = "text/plain; charset=" + charset;
            } else {
                contentType = "application/octet-stream";
            }
        } else {
            contentType = contentType.replaceFirst("\\/jpg$", "/jpeg");
        }
        result.append(contentType);
        return result.toString();
    }

    private static String convertBodyParams2String(List<KeyValue> params, String charset) {
        try {
            StringBuilder contentSb = new StringBuilder();
            if (params != null) {
                for (KeyValue kv : params) {
                    String name = kv.key;
                    String value = kv.getValueStrOrNull();
                    if (!TextUtils.isEmpty(name) && value != null) {
                        if (contentSb.length() > 0) {
                            contentSb.append("&");
                        }
                        contentSb.append(URLEncoder.encode(name, charset).replaceAll("\\+", "%20"))
                                .append("=")
                                .append(URLEncoder.encode(value, charset).replaceAll("\\+", "%20"));
                    }
                }
            }
            return contentSb.toString();
        } catch (Exception e) {
            e.printStackTrace();
            return "";
        }
    }


    private void printResponseLog(String rawData) {
        if (!BuildConfig.DEBUG) {
            return;
        }

        try {
            String tag = "HttpLog-->Response";
            String headerStr;
            if (httpRequest == null) {
                headerStr = lineTag;
            } else {
                headerStr = lineTag + " responseCode:" + httpRequest.getResponseCode() + ", responseMsg:" + httpRequest.getResponseMessage()
                        + "\nurl:" + httpRequest.getParams().getUri();
            }
            JsonLog.printJson(tag, rawData, headerStr);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    private void printResponseErrorLog(Throwable e, boolean isOnCallback) {
        if (!BuildConfig.DEBUG) {
            return;
        }

        try {
            String tag = "HttpLog-->ResponseError";
            Log.d(tag, "╔═════════════════════════════════════════════════════════════");
            Log.d(tag, "║ " + lineTag + " isOnCallback:" + isOnCallback);
            if (httpRequest != null) {
                Log.d(tag, "║responseCode:" + httpRequest.getResponseCode() + ", responseMsg:" + httpRequest.getResponseMessage());
                Log.d(tag, "║url:" + httpRequest.getParams().getUri());
            }
            Log.d(tag, tag, e);
            Log.d(tag, "╚═════════════════════════════════════════════════════════════");
        } catch (Exception ignore) {
            ignore.printStackTrace();
        }
    }

    private void printResponseErrorLog(String rawData) {
        if (!BuildConfig.DEBUG) {
            return;
        }

        try {
            String tag = "HttpLog-->ResponseError";
            String headerStr;
            if (httpRequest == null) {
                headerStr = lineTag;
            } else {
                headerStr = lineTag + " responseCode:" + httpRequest.getResponseCode() + ", responseMsg:" + httpRequest.getResponseMessage()
                        + "\nurl:" + httpRequest.getParams().getUri();
            }
            JsonLog.printJson(tag, rawData, headerStr);
        } catch (Exception ignore) {
            ignore.printStackTrace();
        }
    }

    //</editor-fold desc='print log'>


}
