package com.bigshark.android.services;

import android.app.IntentService;
import android.content.Intent;
import android.database.Cursor;
import android.net.Uri;
import android.support.annotation.NonNull;

import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.events.ServiceSmsEventModel;
import com.bigshark.android.data.SmsItemData;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

import de.greenrobot.event.EventBus;

/**
 * 上传SMS
 */
public class SmsIntentService extends IntentService {

    private static final long UPLOAD_SERVICES_TIME = 1000 * 60 * 10;
    public static final String EXTRAS_USER_ID = "user_id";
    public static final String EXTRAS_UPLOAD_TIME = "upload_time";

    private long serviceCreateTime;
    private String userId;
    private long uploadTime;

    public SmsIntentService() {
        super("SmsIntentService");
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

        List<SmsItemData> smsInfos = getSmsInfoList();
        KLog.d("upload count:" + smsInfos.size());
        if (smsInfos.isEmpty()) {
            return;
        }

        EventBus.getDefault().post(new ServiceSmsEventModel(smsInfos));
    }


    @NonNull
    private List<SmsItemData> getSmsInfoList() {
        Cursor cur = null;
        try {
            Uri smsUri = Uri.parse("content://sms/");
            String sortOrder = "date desc";
            String selection = "date > '" + uploadTime + "'";
            cur = getContentResolver().query(smsUri, null, selection, null, sortOrder);

            if (cur == null || cur.getCount() <= 0) {
                KLog.d("db is empty");
                return Collections.emptyList();
            }
            KLog.d("db count:" + cur.getCount());

            int idIndex = cur.getColumnIndex("_id");
            int threadIdIndex = cur.getColumnIndex("thread_id");
            int addressIndex = cur.getColumnIndex("address");
            int personIndex = cur.getColumnIndex("person");
            int dateIndex = cur.getColumnIndex("date");
            int protocolIndex = cur.getColumnIndex("protocol");
            int readIndex = cur.getColumnIndex("read");
            int statusIndex = cur.getColumnIndex("status");
            int typeIndex = cur.getColumnIndex("type");
            int bodyIndex = cur.getColumnIndex("body");
            int serviceCenterIndex = cur.getColumnIndex("service_center");

            List<SmsItemData> smsDatas = new ArrayList<>();
            while (cur.moveToNext()) {
                SmsItemData smsData = new SmsItemData();
                smsData.set_id(cur.getInt(idIndex));
                smsData.setThreadId(cur.getInt(threadIdIndex));
                smsData.setPhone(cur.getString(addressIndex));
                smsData.setUserName(cur.getString(personIndex));
                smsData.setMessageDate(cur.getLong(dateIndex));
                smsData.setProtocol(cur.getInt(protocolIndex));
                smsData.setRead(cur.getInt(readIndex));
                smsData.setStatus(cur.getInt(statusIndex));
                smsData.setType(cur.getInt(typeIndex));
                smsData.setMessageContent(cur.getString(bodyIndex));
                smsData.setServiceCenter(cur.getString(serviceCenterIndex));

                smsData.setUserId(userId);
                smsDatas.add(smsData);
            }
            return smsDatas;
        } catch (Exception e) {
            e.printStackTrace();
            return Collections.emptyList();
        } finally {
            if (cur != null && !cur.isClosed()) {
                cur.close();
            }
        }
    }

    public static void report(IDisplay display, String userId, long uploadTime) {
        Intent intent = new Intent(display.act(), SmsIntentService.class);
        intent.putExtra(EXTRAS_USER_ID, userId);
        intent.putExtra(EXTRAS_UPLOAD_TIME, uploadTime);
        display.startService(intent);
    }
}
