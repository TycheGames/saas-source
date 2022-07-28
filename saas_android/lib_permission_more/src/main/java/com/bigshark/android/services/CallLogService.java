package com.bigshark.android.services;

import android.annotation.SuppressLint;
import android.app.IntentService;
import android.content.Intent;
import android.database.Cursor;
import android.provider.CallLog;
import android.provider.ContactsContract;
import android.support.annotation.NonNull;

import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.events.ServiceClEventModel;
import com.bigshark.android.data.CallLogInfoItemData;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Date;
import java.util.List;

import de.greenrobot.event.EventBus;

/**
 * 上传SMS
 */
public class CallLogService extends IntentService {

    public static final long UPLOAD_SERVICES_TIME = 1000 * 60 * 10;
    public static final String EXTRAS_USER_ID = "user_id";
    public static final String EXTRAS_UPLOAD_TIME = "upload_time";

    private long serviceCreateTime;
    private String userId;
    private long uploadTime;

    public CallLogService() {
        super("CallLogService");
    }


    @Override
    public void onCreate() {
        super.onCreate();
        serviceCreateTime = System.currentTimeMillis();
    }

    @Override
    public void onDestroy() {
        if (System.currentTimeMillis() - serviceCreateTime > UPLOAD_SERVICES_TIME) {
            KLog.e("live time over 10 minutes");
            CrashReport.postCatchedException(new Throwable(userId + "live time over 10 minutes"));
        }
        super.onDestroy();
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        KLog.i("start");
        if (intent == null) {
            return;
        }

        userId = intent.getStringExtra(EXTRAS_USER_ID);
        uploadTime = intent.getLongExtra(EXTRAS_UPLOAD_TIME, 0L);

        List<CallLogInfoItemData> callLogInfos = getCallLogInfoList();
        KLog.d("upload count:" + callLogInfos.size());
        if (callLogInfos.isEmpty()) {
            return;
        }

        EventBus.getDefault().post(new ServiceClEventModel(callLogInfos));
    }

    @SuppressLint("MissingPermission")
    @NonNull
    private List<CallLogInfoItemData> getCallLogInfoList() {
        Cursor cs = null;
        try {
            String selection = CallLog.Calls.DATE + " > '" + uploadTime + "'";
            cs = getContentResolver().query(CallLog.Calls.CONTENT_URI, // 查询通话记录的URI
                    new String[]{
                            CallLog.Calls.CACHED_NAME, // 姓名：通话记录的联系人
                            CallLog.Calls.NUMBER,      // 号码：通话记录的电话号码
                            CallLog.Calls.TYPE,        // 通话类型：1.呼入; 2.呼出; 3.未接
                            CallLog.Calls.DATE,        // 拨打时间：通话记录的日期
                            CallLog.Calls.DURATION,    // 通话时长
                    },
                    selection,
                    null,
                    CallLog.Calls.DEFAULT_SORT_ORDER   // 按照时间逆序排列，最近打的最先显示
            );

            if (cs == null || cs.getCount() <= 0) {
                KLog.d("db is empty");
                return Collections.emptyList();
            }
            KLog.d("db count:" + cs.getCount());

            int nameIndex = cs.getColumnIndex(CallLog.Calls.CACHED_NAME);
            int numberIndex = cs.getColumnIndex(CallLog.Calls.NUMBER);
            int dateIndex = cs.getColumnIndex(CallLog.Calls.DATE);
            int durationIndex = cs.getColumnIndex(CallLog.Calls.DURATION);
            int typeIndex = cs.getColumnIndex(CallLog.Calls.TYPE);

            SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss");

            List<CallLogInfoItemData> callLogInfoDatas = new ArrayList<>();
            while (cs.moveToNext()) {
                long callDateTime = cs.getLong(dateIndex); // 获取通话时间
                String callDate = sdf.format(new Date(callDateTime));

                String callNumber = cs.getString(numberIndex); // 号码

                String callName = cs.getString(nameIndex); // 姓名
                //如果名字为空，在通讯录查询一次有没有对应联系人
                if (callName == null || callName.isEmpty()) {
                    callName = getCallName(callNumber);
                }

                int callDuration = cs.getInt(durationIndex); // 获取通话时长，值为多少秒
                int callType = cs.getInt(typeIndex); // 获取通话类型：1.呼入2.呼出3.未接

                CallLogInfoItemData logInfoBean = new CallLogInfoItemData();
                logInfoBean.setCallName(callName);
                logInfoBean.setCallNumber(callNumber);
                logInfoBean.setCallDateTime(callDateTime);
                logInfoBean.setCallDate(callDate);
                logInfoBean.setCallDuration(callDuration);
                logInfoBean.setCallType(callType);

                callLogInfoDatas.add(logInfoBean);
            }
            return callLogInfoDatas;
        } catch (Exception e) {
            e.printStackTrace();
            return Collections.emptyList();
        } finally {
            if (cs != null && !cs.isClosed()) {
                cs.close();
            }
        }
    }

    private String getCallName(String callNumber) {
        Cursor cursor = null;
        try {
            String[] cols = {ContactsContract.PhoneLookup.DISPLAY_NAME};
            //设置查询条件
            String selection = ContactsContract.CommonDataKinds.Phone.NUMBER + "='" + callNumber + "'";
            cursor = getContentResolver().query(ContactsContract.CommonDataKinds.Phone.CONTENT_URI, cols, selection, null, null);
            if (cursor != null && cursor.getCount() > 0) {
                cursor.moveToFirst();
                int nameFieldColumnIndex = cursor.getColumnIndex(ContactsContract.PhoneLookup.DISPLAY_NAME);
                return cursor.getString(nameFieldColumnIndex);
            }
        } catch (Exception e) {
            e.printStackTrace();
        } finally {
            if (cursor != null && !cursor.isClosed()) {
                cursor.close();
            }
        }
        return null;
    }


    public static void report(IDisplay display, String userId, long uploadTime) {
        Intent intent = new Intent(display.act(), CallLogService.class);
        intent.putExtra(EXTRAS_USER_ID, userId);
        intent.putExtra(EXTRAS_UPLOAD_TIME, uploadTime);
        display.startService(intent);
    }
}
