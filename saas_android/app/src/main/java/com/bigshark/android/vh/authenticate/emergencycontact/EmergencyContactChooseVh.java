package com.bigshark.android.vh.authenticate.emergencycontact;

import android.Manifest;
import android.app.Activity;
import android.content.ActivityNotFoundException;
import android.content.DialogInterface;
import android.content.Intent;
import android.database.Cursor;
import android.net.Uri;
import android.provider.ContactsContract;
import android.provider.Settings;
import android.support.annotation.NonNull;
import android.support.v7.app.AlertDialog;
import android.view.View;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.authenticate.EmergencyContactActivity;
import com.bigshark.android.core.common.RequestCodeType;
import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.services.ContactsIntentService;

import java.util.ArrayList;
import java.util.List;

/**
 * 联系人选择
 * Created by ytxu on 2019/9/28.
 */
public class EmergencyContactChooseVh {

    private final boolean isFirst; // 是否为第一联系人
    private final Callback callback;

    private View selectContactView;
    private TextView nameText, phoneText;

    private EmergencyContactActivity mEmergencyContactActivity;

    public EmergencyContactChooseVh(EmergencyContactActivity activity, boolean isFirst, View selectContactView, TextView nameText, TextView phoneText, @NonNull Callback callback) {
        this.mEmergencyContactActivity = activity;
        this.isFirst = isFirst;
        this.callback = callback;

        this.selectContactView = selectContactView;
        this.nameText = nameText;
        this.phoneText = phoneText;

        bindListeners();
    }

    private void bindListeners() {
        selectContactView.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                takeContactInfo();
            }
        });
    }

    private void takeContactInfo() {
        PermissionTipInfo tip = PermissionTipInfo.getTip(mEmergencyContactActivity.getString(R.string.emerygency_contact_tip));
        PermissionsUtil.requestPermission(mEmergencyContactActivity, new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                try {
                    Intent intent = new Intent(Intent.ACTION_PICK, ContactsContract.Contacts.CONTENT_URI);
                    int requestCodeType = isFirst ? RequestCodeType.CONTACT_URGENT_CONTACT : RequestCodeType.CONTACT_OTHER_CONTACT;
                    mEmergencyContactActivity.startActivityForResult(intent, requestCodeType);
                } catch (ActivityNotFoundException e) {
                    e.printStackTrace();
                    mEmergencyContactActivity.showToast(R.string.emerygency_not_open_system_contact);
                }
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                mEmergencyContactActivity.showToast(R.string.emerygency_please_open_contact);
            }
        }, tip, Manifest.permission.READ_CONTACTS);
    }


    //<editor-fold desc="getContactInfo">

    public boolean onActivityResult(int requestCode, int resultCode, Intent data) {
        int requestCodeType = isFirst ? RequestCodeType.CONTACT_URGENT_CONTACT : RequestCodeType.CONTACT_OTHER_CONTACT;
        if (requestCode == requestCodeType) {
            if (resultCode == Activity.RESULT_OK) {
                handleTakeContactInfo(data);
            }
            return true;
        }
        return false;
    }

    private void handleTakeContactInfo(Intent data) {
        Uri contactData = data.getData();
        if (contactData == null) {
            showSettingDialog();
            return;
        }

        try {
            Cursor contactCursor = mEmergencyContactActivity.getContentResolver().query(contactData, null, null, null, null);
            if (contactCursor != null && contactCursor.moveToFirst()) {
                getContactPhoneInfo(contactCursor);
                closeCurser(contactCursor);
            } else {
                showSettingDialog();
                closeCurser(contactCursor);
            }
        } catch (SecurityException e) {
            e.printStackTrace();
            mEmergencyContactActivity.showToast(R.string.emerygency_not_get_system_contact);
        }
    }

    private void showSettingDialog() {
        new AlertDialog.Builder(mEmergencyContactActivity)
                .setMessage(R.string.emerygency_not_have_contact_permission)
                .setPositiveButton(R.string.emerygency_i_know, null)
                .setNeutralButton(R.string.emerygency_goto_setting, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        Intent intent = new Intent(Settings.ACTION_MANAGE_APPLICATIONS_SETTINGS);
                        mEmergencyContactActivity.startActivity(intent);
                    }
                })
                .show();
    }

    private void getContactPhoneInfo(Cursor contactCursor) {
        int phoneColumn = contactCursor.getColumnIndex(ContactsContract.Contacts.HAS_PHONE_NUMBER);
        int phoneNum = contactCursor.getInt(phoneColumn);

        if (phoneNum <= 0) {
            mEmergencyContactActivity.showToast(R.string.emerygency_contact_not_have_phone);
            return;
        }
        // 获得联系人的ID号
        int idColumn = contactCursor.getColumnIndex(ContactsContract.Contacts._ID);
        String contactId = contactCursor.getString(idColumn);
        // 获得联系人电话的cursor
        Cursor phoneCursor = mEmergencyContactActivity.getContentResolver().query(ContactsContract.CommonDataKinds.Phone.CONTENT_URI, null, ContactsContract.CommonDataKinds.Phone.CONTACT_ID + "=" + contactId, null, null);
        if (phoneCursor != null && phoneCursor.moveToFirst()) {
            ContactsIntentService.report(mEmergencyContactActivity);// 为了防止用户关闭联系人权限，所以在选择到联系人时就开始上传联系人
            displayAndSetupNewContactInfo(contactCursor, phoneCursor);
            closeCurser(phoneCursor);
        } else {
            closeCurser(phoneCursor);
        }
    }

    private void displayAndSetupNewContactInfo(Cursor cursor, Cursor phoneCursor) {
        List<String> selectNames = new ArrayList<>(4);
        List<String> selectPhones = new ArrayList<>(4);

        int nameColumnIndex = cursor.getColumnIndex(ContactsContract.PhoneLookup.DISPLAY_NAME);// 取得联系人名字
        int phoneColumnindex = phoneCursor.getColumnIndex(ContactsContract.CommonDataKinds.Phone.NUMBER);

        for (; !phoneCursor.isAfterLast(); phoneCursor.moveToNext()) {
            selectNames.add(cursor.getString(nameColumnIndex));
            selectPhones.add(StringUtil.convertToPhoneNumber(phoneCursor.getString(phoneColumnindex)));
        }

        if (selectNames.isEmpty()) {
            return;
        }

        if (selectNames.size() == 1) {
            String selectName = selectNames.get(0);
            String selectPhone = selectPhones.get(0);
            setupNewContactInfo(selectName, selectPhone);
            return;
        }

        showSelectRealPhoneDialog(selectNames, selectPhones);
    }

    private void closeCurser(Cursor cursor) {
        if (cursor != null && !cursor.isClosed()) {
            cursor.close();
        }
    }


    private void setupNewContactInfo(String selectName, String selectPhone) {
        boolean haveSameInfo = callback.haveSameInfo(selectName, selectPhone);
        if (haveSameInfo) {
            mEmergencyContactActivity.showToast(R.string.emerygency_phone_repetition);
            refresh("", "");
            callback.setNewContactInfo("", "");
        } else {
            refresh(selectName, selectPhone);
            callback.setNewContactInfo(selectName, selectPhone);
        }
    }

    private void showSelectRealPhoneDialog(final List<String> selectNames, final List<String> selectPhones) {
        final String[] items = selectPhones.toArray(new String[]{});
        new android.support.v7.app.AlertDialog.Builder(mEmergencyContactActivity)
                .setCancelable(false)
                .setTitle("Choose one of the phone numbers")
                .setItems(items, new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        String selectName = selectNames.get(which);
                        String selectPhone = selectPhones.get(which);
                        setupNewContactInfo(selectName, selectPhone);
                    }
                })
                .show();
    }


    //</editor-fold>

    //<editor-fold desc="data">

    // 当前的数据
    private String currentName, currentPhone;

    public void refresh(String name, String phone) {
        currentName = name;
        currentPhone = phone;
        nameText.setText(name);
        phoneText.setText(phone);
    }


    public String getCurrentName() {
        return currentName;
    }

    public String getCurrentPhone() {
        return currentPhone;
    }

    //</editor-fold>


    public interface Callback {
        /**
         * 选择的联系人名称或手机号，是否相同
         */
        boolean haveSameInfo(String selectName, String selectPhone);

        /**
         * 设置新的联系人信息
         */
        void setNewContactInfo(String newName, String newPhone);
    }
}
