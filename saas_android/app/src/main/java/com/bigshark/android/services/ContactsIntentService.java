package com.bigshark.android.services;

import android.app.IntentService;
import android.content.Intent;
import android.database.Cursor;
import android.provider.ContactsContract;

import com.bigshark.android.database.PhoneContactRecordModel;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.param.UpdateDataType;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

/**
 * 通讯录：全量上传
 */
public class ContactsIntentService extends IntentService {

    public static final long UPLOAD_CONTACTS_SERVICES_TIME = 1000 * 60 * 10;

    private String uid;

    public ContactsIntentService() {
        super("ContactsIntentService");
    }

    private long createTime;

    @Override
    public void onCreate() {
        super.onCreate();
        createTime = System.currentTimeMillis();
    }

    @Override
    public void onDestroy() {
        if (System.currentTimeMillis() - createTime > UPLOAD_CONTACTS_SERVICES_TIME) {
            KLog.e("live time over 10 minutes");
            CrashReport.postCatchedException(new Throwable(uid + "live time over 10 minutes"));
        }
        super.onDestroy();
    }

    @Override
    protected void onHandleIntent(Intent intent) {
        KLog.i(" start");
        uid = MmkvGroup.loginInfo().getUserName();

        List<PhoneContactRecordModel> currentContacts = getCurrentAllContacts();

        uploadToServer(currentContacts);
    }

    /**
     * 获取所有联系人
     */
    private List<PhoneContactRecordModel> getCurrentAllContacts() {
        List<PhoneContactRecordModel> contacts = new ArrayList<>();

        Cursor cursor = null;
        try {
            cursor = getContentResolver().query(ContactsContract.Contacts.CONTENT_URI, null, null, null, null);
            if (cursor == null || cursor.getCount() <= 0) {
                return contacts;
            }

            final int contactIdIndex = cursor.getColumnIndex(ContactsContract.Contacts._ID);
            final int nameIndex = cursor.getColumnIndex(ContactsContract.Contacts.DISPLAY_NAME);
            final int timesContactedIndex = cursor.getColumnIndex(ContactsContract.Contacts.TIMES_CONTACTED);
            final int lastTimeContactedIndex = cursor.getColumnIndex(ContactsContract.Contacts.LAST_TIME_CONTACTED);
            final int contactLastUpdatedTimestampIndex = cursor.getColumnIndex(ContactsContract.Contacts.CONTACT_LAST_UPDATED_TIMESTAMP);
            while (cursor.moveToNext()) {
                String contactId = cursor.getString(contactIdIndex);
                String name = cursor.getString(nameIndex);
                int contactedTimes = cursor.getInt(timesContactedIndex);
                long contactedLastTime = cursor.getLong(lastTimeContactedIndex);
                long contactLastUpdatedTimestamp = cursor.getLong(contactLastUpdatedTimestampIndex);
                List<PhoneContactRecordModel> contactBeans = getContacts(contactId, name, contactedTimes, contactedLastTime, contactLastUpdatedTimestamp);
                contacts.addAll(contactBeans);
            }
        } catch (Exception e) {
            e.printStackTrace();
        } finally {
            if (cursor != null && !cursor.isClosed()) {
                cursor.close();
            }
        }
        return contacts;
    }

    /**
     * 一个联系人，可以有多个手机号
     */
    private List<PhoneContactRecordModel> getContacts(String contactId, String contactName, int contactedTimes, long contactedLastTime, long contactLastUpdatedTimestamp) {
        Cursor phoneCursor = null;
        try {
            // 查找该联系人的phone信息
            phoneCursor = getContentResolver().query(ContactsContract.CommonDataKinds.Phone.CONTENT_URI,
                    null,
                    ContactsContract.CommonDataKinds.Phone.CONTACT_ID + "=" + contactId,
                    null, null);

            if (phoneCursor == null || phoneCursor.getCount() <= 0) {
                return Collections.emptyList();
            }

            List<PhoneContactRecordModel> contactBeans = new ArrayList<>();
            final int phoneIndex = phoneCursor.getColumnIndex(ContactsContract.CommonDataKinds.Phone.NUMBER);
            while (phoneCursor.moveToNext()) {
                String phoneNumber = StringUtil.convertToPhoneNumber(phoneCursor.getString(phoneIndex));

                PhoneContactRecordModel contact = new PhoneContactRecordModel();
                contact.setName(contactName);
                contact.setContactedTimes(contactedTimes);
                contact.setContactedLastTime(contactedLastTime);
                contact.setContactLastUpdatedTimestamp(contactLastUpdatedTimestamp);

                contact.setUserId(uid);
                contact.setMobile(phoneNumber);

                contactBeans.add(contact);
            }
            return contactBeans;
        } catch (Exception e) {
            e.printStackTrace();
            return Collections.emptyList();
        } finally {
            if (phoneCursor != null && !phoneCursor.isClosed()) {
                phoneCursor.close();
            }
        }
    }

    private void uploadToServer(final List<PhoneContactRecordModel> currentContacts) {
        if (currentContacts.isEmpty()) {
            KLog.d("empty contact");
            return;
        }
        HttpSender.post(new CommonResponseCallback<String>(null) {

            @Override
            public CommonRequestParams createRequestParams() {
                String uploadInfoUrl = HttpConfig.getRealUrl(StringConstant.HTTP_DATA_UPDATE_INFO);
                CommonRequestParams requestParams = new CommonRequestParams(uploadInfoUrl);
                String data = ConvertUtils.toString(currentContacts);
                requestParams.addBodyParameter("type", UpdateDataType.TYPE_CONTACT);
                requestParams.addBodyParameter("data", data);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {
            }

            @Override
            public void handleSuccess(String resultData, int resultCode, String resultMessage) {
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


    public static void report(IDisplay display) {
        display.startService(new Intent(display.act(), ContactsIntentService.class));
    }
}
