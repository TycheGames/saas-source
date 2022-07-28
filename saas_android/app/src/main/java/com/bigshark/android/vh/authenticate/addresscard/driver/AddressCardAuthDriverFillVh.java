package com.bigshark.android.vh.authenticate.addresscard.driver;

import android.app.AlertDialog;
import android.app.DatePickerDialog;
import android.content.DialogInterface;
import android.support.annotation.NonNull;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;
import android.widget.DatePicker;
import android.widget.EditText;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.authenticate.AddressCardAuthActivity;
import com.bigshark.android.display.DisplayBaseVh;

import java.util.Calendar;

/**
 * 地址证明--驾驶证: 填充信息
 */
public class AddressCardAuthDriverFillVh extends DisplayBaseVh<View, Void> {

    private Callback mCallback;
    private EditText mDriverLicenseEdit;
    private TextView mBirthdayText;

    private String mBirthday = null;

    private AddressCardAuthActivity mAddressCardAuthActivity;

    public AddressCardAuthDriverFillVh(AddressCardAuthActivity activity, View root, @NonNull Callback mCallback) {
        super(activity);
        this.mAddressCardAuthActivity = activity;
        this.mCallback = mCallback;
        initViews(root);
    }

    @Override
    protected void bindViews() {
        super.bindViews();
        mDriverLicenseEdit = findViewById(R.id.authenticate_address_driver_fill_license_edit);
        mBirthdayText = findViewById(R.id.authenticate_address_driver_fill_birthday_select);
    }

    @Override
    protected void bindListeners() {
        super.bindListeners();

        mDriverLicenseEdit.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {
            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
            }

            @Override
            public void afterTextChanged(Editable s) {
                mCallback.onChange(s.toString().trim(), mBirthday);
            }
        });

        mBirthdayText.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                showDatePickerDialog();
            }
        });
    }

    private DatePickerDialog datePickerDialog;

    private void showDatePickerDialog() {
        if (datePickerDialog == null) {
            createDatePickerDialog();
        }
        datePickerDialog.show();
    }

    private void createDatePickerDialog() {
        Calendar initCalendar = Calendar.getInstance();
        initCalendar.add(Calendar.YEAR, -21);
        int year = initCalendar.get(Calendar.YEAR);
        int month = initCalendar.get(Calendar.MONTH) + 1;
        int day = initCalendar.get(Calendar.DAY_OF_MONTH);
        datePickerDialog = new DatePickerDialog(mAddressCardAuthActivity, AlertDialog.THEME_HOLO_LIGHT, new DatePickerDialog.OnDateSetListener() {
            @Override
            public void onDateSet(DatePicker view, int year, int month, int dayOfMonth) {
                month = month + 1;//月份加一
                mBirthday = createFormatBirthday(year, month, dayOfMonth);
                mBirthdayText.setText(mBirthday);
                mCallback.onChange(mDriverLicenseEdit.getText().toString().trim(), mBirthday);
            }
        }, year, month - 1, day); // 月份减一

        datePickerDialog.setOnCancelListener(new DialogInterface.OnCancelListener() {
            @Override
            public void onCancel(DialogInterface dialog) {
            }
        });
        datePickerDialog.setTitle("Please Choose Your Birthday");
    }

    private String createFormatBirthday(int year, int month, int dayOfMonth) {
        // dd/MM/yyyy
        String formatText = "";
        formatText += dayOfMonth < 10 ? ("0" + dayOfMonth) : ("" + dayOfMonth);
        formatText += "/";
        formatText += month < 10 ? ("0" + month) : ("" + month);
        formatText += "/";
        formatText += year;
        return formatText;
    }


    public EditText getmDriverLicenseEdit() {
        return mDriverLicenseEdit;
    }

    public void restartVerify() {
        mDriverLicenseEdit.setText("");
        mBirthdayText.setText("");
    }

    public void refreshView(String driverFillLicenseText, String driverFillBirthday) {
        mDriverLicenseEdit.setText(driverFillLicenseText);
        mBirthday = driverFillBirthday;
        mBirthdayText.setText(mBirthday);
    }


    public interface Callback {
        void onChange(String driverLicense, String birthday);
    }

}
