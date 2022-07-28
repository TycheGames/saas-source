package com.bigshark.android.vh.authenticate.emergencycontact;

import android.app.Dialog;
import android.content.Context;
import android.view.Display;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.WindowManager;
import android.widget.FrameLayout;
import android.widget.LinearLayout.LayoutParams;
import android.widget.ListView;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.authenticate.EmergencyContactActivity;
import com.bigshark.android.adapters.authenticate.EmergencyContactChooseListAdapter;
import com.bigshark.android.http.model.contact.ContactRelationshipConfig;

import java.util.List;


public class EmergencyContactChooseDialog {
    private Dialog dialog;
    private RelativeLayout lLayout_bg;
    private ListView listView;
    private TextView okBtn;
    private TextView cancelBtn;
    private Display display;

    private Callback callback;

    private EmergencyContactChooseListAdapter adapter;

    private EmergencyContactActivity mEmergencyContactActivity;

    public EmergencyContactChooseDialog(EmergencyContactActivity activity) {
        this.mEmergencyContactActivity = activity;
        WindowManager windowManager = (WindowManager) mEmergencyContactActivity.getSystemService(Context.WINDOW_SERVICE);
        display = windowManager.getDefaultDisplay();
        builder();
    }

    public EmergencyContactChooseDialog builder() {
        // 获取Dialog布局
        View view = LayoutInflater.from(mEmergencyContactActivity).inflate(R.layout.dialog_authenticate_emergency_contact_selects, null);

        // 获取自定义Dialog布局中的控件
        lLayout_bg = view.findViewById(R.id.select_contact_relation_dialog_root);
        listView = view.findViewById(R.id.select_contact_relation_listview);
        okBtn = view.findViewById(R.id.select_contact_relation_ok);
        cancelBtn = view.findViewById(R.id.select_contact_relation_cancle);

        // 定义Dialog布局和参数
        dialog = new Dialog(mEmergencyContactActivity, R.style.AlertDialogStyle);
        dialog.setContentView(view);

        // 调整dialog背景大小
        lLayout_bg.setLayoutParams(new FrameLayout.LayoutParams((int) (display.getWidth() * 0.80), LayoutParams.WRAP_CONTENT));

        dialog.setCancelable(true);

        adapter = new EmergencyContactChooseListAdapter(mEmergencyContactActivity);

        listView.setAdapter(adapter);

        cancelBtn.setOnClickListener(new OnClickListener() {
            @Override
            public void onClick(View view) {
                dismiss();
            }
        });

        okBtn.setOnClickListener(new OnClickListener() {
            @Override
            public void onClick(View view) {
                if (callback != null && adapter.getCount() > 0) {
                    int checkPosition = adapter.getCheckPosition();
                    callback.onSelected(adapter.getItem(checkPosition), checkPosition);
                }
                dismiss();
            }
        });

        return this;
    }


    public void show() {
        if (dialog != null && !dialog.isShowing()) {
            dialog.show();
        }
    }

    public void dismiss() {
        if (dialog != null && dialog.isShowing()) {
            dialog.dismiss();
        }
    }

    public EmergencyContactChooseDialog setCallback(Callback callback) {
        this.callback = callback;
        return this;
    }

    public EmergencyContactChooseDialog setData(List<ContactRelationshipConfig.RelationItem> list, int cusIndex) {
        if (list == null || list.isEmpty()) {
            return this;
        }
        adapter.refresh(list);
        adapter.setCheckPosition(cusIndex);
        return this;
    }

    public interface Callback {
        void onSelected(ContactRelationshipConfig.RelationItem value, int pos);
    }
}
