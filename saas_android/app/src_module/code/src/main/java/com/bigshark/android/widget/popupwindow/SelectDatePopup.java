package com.bigshark.android.widget.popupwindow;

import android.content.Context;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.listener.OnSelectCityListener;
import com.bigshark.android.utils.DateDataUtil;
import com.bigshark.android.widget.spinnerwheel.AbstractWheel;
import com.bigshark.android.widget.spinnerwheel.OnWheelChangedListener;
import com.bigshark.android.widget.spinnerwheel.WheelVerticalView;
import com.bigshark.android.widget.spinnerwheel.adapters.ListWheelAdapter;

import java.util.Calendar;
import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/24 19:46
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class SelectDatePopup extends PopupWindow {

    private TextView tv_popup_cancel, tv_popup_confirm, tv_popup_unlimited;
    private WheelVerticalView wheel_year, wheel_month, wheel_day;

    private List<String> yearList, montyList, dayList;
    private Context mContext;
    private int yearSelect, monthSelect, daySelect;

    public SelectDatePopup(@NonNull Context context) {
        super(context);
        mContext = context;
        init(mContext);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_select_date, null);
        //设置View
        setContentView(mPopView);
        tv_popup_cancel = mPopView.findViewById(R.id.tv_popup_cancel);
        tv_popup_confirm = mPopView.findViewById(R.id.tv_popup_confirm);
        tv_popup_unlimited = mPopView.findViewById(R.id.tv_popup_unlimited);
        wheel_year = mPopView.findViewById(R.id.wheel_year);
        wheel_month = mPopView.findViewById(R.id.wheel_month);
        wheel_day = mPopView.findViewById(R.id.wheel_day);
        Calendar cal = Calendar.getInstance();
        yearList = DateDataUtil.buildYearData();
        wheel_year.setViewAdapter(new ListWheelAdapter<String>(mContext, yearList));
        wheel_year.setCurrentItem(0);
        montyList = DateDataUtil.buildMonthData();
        int month = cal.get(Calendar.MONTH) + 1;
        int monthIndex = 0;
        if (montyList.contains(month + "月")) {
            monthIndex = montyList.indexOf(month + "月");
            monthSelect = monthIndex;
        }
        wheel_month.setViewAdapter(new ListWheelAdapter<String>(mContext, montyList));
        wheel_month.setCurrentItem(monthIndex);
        dayList = DateDataUtil.buildDayData();
        int day = cal.get(Calendar.DAY_OF_MONTH);
        int dayIndex = 0;
        if (dayList.contains(day + "日")) {
            dayIndex = dayList.indexOf(day + "日");
            daySelect = dayIndex;
        }
        wheel_day.setViewAdapter(new ListWheelAdapter<String>(mContext, dayList));
        wheel_day.setCurrentItem(dayIndex);
        initListener();
    }

    private void initListener() {
        tv_popup_cancel.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                dismiss();
            }
        });
        tv_popup_confirm.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                String date = yearList.get(yearSelect) + montyList.get(monthSelect) + dayList.get(daySelect);
                if (mOnSelectCityListener != null) {
                    mOnSelectCityListener.onConfirmClick(date);
                }
            }
        });

        tv_popup_unlimited.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                if (mOnSelectCityListener != null) {
                    mOnSelectCityListener.onUnlimitedClick("Unlimited time");
                }
            }
        });
        wheel_year.addChangingListener(new OnWheelChangedListener() {
            @Override
            public void onChanged(AbstractWheel wheel, int oldValue, int newValue) {
                yearSelect = newValue;
            }
        });
        wheel_month.addChangingListener(new OnWheelChangedListener() {

            @Override
            public void onChanged(AbstractWheel wheel, int oldValue, int newValue) {
                monthSelect = newValue;
            }
        });
        wheel_day.addChangingListener(new OnWheelChangedListener() {

            @Override
            public void onChanged(AbstractWheel wheel, int oldValue, int newValue) {
                daySelect = newValue;
            }
        });

    }

    private OnSelectCityListener mOnSelectCityListener;

    public void setOnSelectCityListener(OnSelectCityListener onSelectCityListener) {
        mOnSelectCityListener = onSelectCityListener;
    }

}
