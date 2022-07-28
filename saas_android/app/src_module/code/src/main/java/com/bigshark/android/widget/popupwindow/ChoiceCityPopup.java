package com.bigshark.android.widget.popupwindow;

import android.content.Context;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.app.ProvinceModel;
import com.bigshark.android.listener.OnSelectCityListener;
import com.bigshark.android.widget.spinnerwheel.AbstractWheel;
import com.bigshark.android.widget.spinnerwheel.OnWheelChangedListener;
import com.bigshark.android.widget.spinnerwheel.WheelVerticalView;
import com.bigshark.android.widget.spinnerwheel.adapters.ListWheelAdapter;

import java.util.ArrayList;
import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/23 17:04
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class ChoiceCityPopup extends PopupWindow {

    private TextView tv_popup_cancel, tv_popup_confirm, tv_popup_unlimited;
    private WheelVerticalView province_picker, city_picker;

    private List<ProvinceModel> cityJson;
    private List<String> provinces, citys;
    private int proSelect, citySelect;
    private Context mContext;
    private OnSelectCityListener mOnSelectCityListener;
    private String cityStr = "";

    public ChoiceCityPopup(@NonNull Context context, List<ProvinceModel> provinceList) {
        super(context);
        this.mContext = context;
        this.cityJson = provinceList;
        init(context);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_choice_city, null);
        //设置View
        setContentView(mPopView);
        tv_popup_cancel = mPopView.findViewById(R.id.tv_popup_cancel);
        tv_popup_confirm = mPopView.findViewById(R.id.tv_popup_confirm);
        tv_popup_unlimited = mPopView.findViewById(R.id.tv_popup_unlimited);
        province_picker = mPopView.findViewById(R.id.province_picker);
        city_picker = mPopView.findViewById(R.id.city_picker);
        initListener();
        provinces = new ArrayList<>();
        for (int i = 0; i < cityJson.size(); i++) {
            provinces.add(cityJson.get(i).getName());
        }
        province_picker.setViewAdapter(new ListWheelAdapter<String>(mContext, provinces));
        province_picker.setCurrentItem(0);

        citys = new ArrayList<>();
        for (int i = 0; i < cityJson.get(0).getCity().size(); i++) {
            citys.add(cityJson.get(0).getCity().get(i));
        }
        city_picker.setViewAdapter(new ListWheelAdapter<String>(mContext, citys));
        city_picker.setCurrentItem(0);

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
                cityStr = citys.get(citySelect);
                if (mOnSelectCityListener != null) {
                    mOnSelectCityListener.onConfirmClick(cityStr);
                }
            }
        });

        tv_popup_unlimited.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnSelectCityListener != null) {
                    mOnSelectCityListener.onUnlimitedClick("Unlimited");
                }
            }
        });

        province_picker.addChangingListener(new OnWheelChangedListener() {
            @Override
            public void onChanged(AbstractWheel wheel, int oldValue, int newValue) {
                proSelect = newValue;
                citySelect = 0;
                citys = new ArrayList<>();
                for (int i = 0; i < cityJson.get(proSelect).getCity().size(); i++) {
                    citys.add(cityJson.get(proSelect).getCity().get(i));
                }
                city_picker.setViewAdapter(new ListWheelAdapter<String>(mContext, citys));
                city_picker.setCurrentItem(0);
            }
        });
        city_picker.addChangingListener(new OnWheelChangedListener() {
            @Override
            public void onChanged(AbstractWheel wheel, int oldValue, int newValue) {
                citySelect = newValue;
            }
        });
    }

    public void setOnSelectCityListener(OnSelectCityListener onSelectCityListener) {
        mOnSelectCityListener = onSelectCityListener;
    }

}
