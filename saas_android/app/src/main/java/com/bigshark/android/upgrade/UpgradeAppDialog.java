package com.bigshark.android.upgrade;

import android.support.v7.app.AlertDialog;
import android.text.Html;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.http.model.app.UpdateDataModel;
import com.bigshark.android.jump.model.main.UrlJumpModel;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;

/**
 * 更新apk的提示dialog
 */
public class UpgradeAppDialog {

    private final IDisplay display;
    private UpdateDataModel updateDataModel;
    private boolean isForcedUpdate = false;// 是否为强制更新

    private AlertDialog dialog;
    private TextView titleTxt;
    private TextView messageTxt;
    private TextView cancelTxt;
    private TextView updateTxt;
    private TextView forcedupdateTxt;


    public UpgradeAppDialog(IDisplay display, UpdateDataModel updateDataModel) {
        this.display = display;
        this.updateDataModel = updateDataModel;
        isForcedUpdate = updateDataModel.getIs_force_upgrade() == 1;
    }

    public void show() {
        View view = LayoutInflater.from(display.act()).inflate(R.layout.app_upgrade_app, null);
        bindViews(view);
        refreshViews();

        dialog = new AlertDialog.Builder(display.act(), R.style.Dialog_NoTitle)
                .setView(view)
                .setCancelable(false)
                .show();

        dialog.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
    }

    private void bindViews(View view) {
        titleTxt = view.findViewById(R.id.common_upgrade_app_title);
        messageTxt = view.findViewById(R.id.common_upgrade_app_message);
        cancelTxt = view.findViewById(R.id.common_upgrade_app_cancel);
        updateTxt = view.findViewById(R.id.common_upgrade_app_update);
        forcedupdateTxt = view.findViewById(R.id.common_upgrade_app_forcedupdate);
    }

    private void refreshViews() {
        titleTxt.setText("Tips");

        String message = "";
        if (updateDataModel.getNew_features() != null && !updateDataModel.getNew_features().trim().isEmpty()) {
            message += Html.fromHtml(updateDataModel.getNew_features());
        }
        messageTxt.setText(message);

        if (isForcedUpdate) {
            cancelTxt.setVisibility(View.GONE);
            updateTxt.setVisibility(View.GONE);
            forcedupdateTxt.setVisibility(View.VISIBLE);

            forcedupdateTxt.setOnClickListener(new android.view.View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    upgrade();
                }
            });
        } else {
            cancelTxt.setVisibility(View.VISIBLE);
            updateTxt.setVisibility(View.VISIBLE);
            forcedupdateTxt.setVisibility(View.GONE);

            cancelTxt.setOnClickListener(new android.view.View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    closeAlertDialog();
                    MmkvGroup.global().clearUpdateContent();
                }
            });
            updateTxt.setOnClickListener(new android.view.View.OnClickListener() {
                @Override
                public void onClick(View view) {
                    upgrade();
                }
            });
        }
    }

    private void upgrade() {
//        createRequest();
        if (!isForcedUpdate) {
            closeAlertDialog();
            MmkvGroup.global().clearUpdateContent();
        }
        UrlJumpModel data = new UrlJumpModel();
        data.setUrl(updateDataModel.getArd_url());
        data.setPath(StringConstant.JUMP_APP_OPEN_BROWSER);
        data.createRequest().setDisplay(display).jump();
        display.showToast("Please go to the browser to upgrade the app.");
    }

    private void closeAlertDialog() {
        if (dialog != null && dialog.isShowing()) {
            dialog.dismiss();
        }
        dialog = null;
    }
}
