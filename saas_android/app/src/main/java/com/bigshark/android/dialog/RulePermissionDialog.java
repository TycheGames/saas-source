package com.bigshark.android.dialog;

import android.support.annotation.NonNull;
import android.support.v7.app.AlertDialog;
import android.text.Spannable;
import android.text.SpannableString;
import android.text.SpannableStringBuilder;
import android.text.TextPaint;
import android.text.method.LinkMovementMethod;
import android.text.style.ClickableSpan;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.CheckBox;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.home.BrowserActivity;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.utils.StringConstant;
import com.socks.library.KLog;

import java.util.List;

/**
 * 请求权限
 * 提前请求权限
 * Created by ytxu on 2019/9/10.
 */
public class RulePermissionDialog {

    private final IDisplay display;
    private List<String> deniedPermissions;// 未授权的权限列表
    private final Callback callback;

    private AlertDialog dialog;

    private CheckBox protocalCheck;
    private TextView protocalText;
    private TextView startView;


    public RulePermissionDialog(IDisplay display, List<String> deniedPermissions, Callback callback) {
        this.display = display;
        this.deniedPermissions = deniedPermissions;
        this.callback = callback;
    }

    public void start() {
        View view = LayoutInflater.from(display.act()).inflate(R.layout.dialog_application_permission_rules, null);
        bindViews(view);
        initProtocol();
        bindListeners();

        dialog = new AlertDialog.Builder(display.act(), R.style.Dialog_NoTitle)
                .setView(view)
                .setCancelable(false)
                .show();

        if (dialog.getWindow() != null) {
            dialog.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
        }
    }

    private void bindViews(View view) {
        protocalCheck = view.findViewById(R.id.app_permission_applyer_protocol_checkbox);
        protocalText = view.findViewById(R.id.app_permission_applyer_protocol);
        startView = view.findViewById(R.id.app_permission_applyer_start);
    }

    private void initProtocol() {
        final SpannableStringBuilder style = new SpannableStringBuilder();

        SpannableString privacyPolicySpanClick = new SpannableString("Privacy Policy");
        privacyPolicySpanClick.setSpan(new ClickableSpan() {
            @Override
            public void onClick(@NonNull View widget) {
                BrowserActivity.goIntent(display.act(), HttpConfig.getUrl(StringConstant.HTTP_PERSONAL_PROTOCO_PRIVACY_POLICY));
            }

            @Override
            public void updateDrawState(@NonNull TextPaint ds) {
                super.updateDrawState(ds);
                ds.setColor(display.act().getResources().getColor(R.color.theme_secondary_color));
//                ds.setFakeBoldText(true);
                ds.setUnderlineText(true);
            }
        }, 0, privacyPolicySpanClick.length(), Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);

        SpannableString termServiceSpanClick = new SpannableString("Terms of Services");
        termServiceSpanClick.setSpan(new ClickableSpan() {
            @Override
            public void onClick(@NonNull View widget) {
                BrowserActivity.goIntent(display.act(), HttpConfig.getUrl(StringConstant.HTTP_PERSONAL_PROTOCO_TERMS_OF_USER));
            }

            @Override
            public void updateDrawState(@NonNull TextPaint ds) {
                super.updateDrawState(ds);
                ds.setColor(display.act().getResources().getColor(R.color.theme_secondary_color));
//                ds.setFakeBoldText(true);
                ds.setUnderlineText(true);
            }
        }, 0, termServiceSpanClick.length(), Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);

        SpannableString userProtocolSpanClick = new SpannableString("User Agreement");
        userProtocolSpanClick.setSpan(new ClickableSpan() {
            @Override
            public void onClick(@NonNull View widget) {
                BrowserActivity.goIntent(display.act(), HttpConfig.getUrl(StringConstant.HTTP_PERSONAL_PROTOCO_AGREEMENT));
            }

            @Override
            public void updateDrawState(@NonNull TextPaint ds) {
                super.updateDrawState(ds);
                ds.setColor(display.act().getResources().getColor(R.color.theme_secondary_color));
//                ds.setFakeBoldText(true);
                ds.setUnderlineText(true);
            }
        }, 0, userProtocolSpanClick.length(), Spannable.SPAN_EXCLUSIVE_EXCLUSIVE);

        style.append("By continuing you agree to our ")
                .append(privacyPolicySpanClick).append(" & ").append(termServiceSpanClick).append(" & ").append(userProtocolSpanClick)
                .append(" and receive communication from ").append(display.act().getString(R.string.app_name)).append(" via SMS, E-mail and WhatsApp");

        //配置给TextView
        protocalText.setMovementMethod(LinkMovementMethod.getInstance());
        protocalText.setLongClickable(false);
        protocalText.setText(style);
    }

    private void bindListeners() {
        startView.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (!protocalCheck.isChecked()) {
                    display.showToast("Please check the permission authorization agreement");
                    return;
                }
                request();
            }
        });
    }

    private void request() {
        final String[] permissions = deniedPermissions.toArray(new String[0]);
        KLog.json(ConvertUtils.toString(permissions));

        PermissionsUtil.requestPermission(display.act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                closeDialog();
                callback.onPermissionOperationFinish();
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                PermissionTipInfo tip = PermissionTipInfo.getTip("Camera", "Contacts", "Location", "Phone", "SMS", "Storage");
                display.showToast(tip.getContent());
                closeDialog();
                callback.onPermissionOperationFinish();
            }
        }, false, null, permissions);
    }

    private void closeDialog() {
        if (dialog != null && dialog.isShowing()) {
            dialog.dismiss();
        }
        dialog = null;
    }


    public interface Callback {
        void onPermissionOperationFinish();
    }

}
