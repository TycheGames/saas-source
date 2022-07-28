package com.bigshark.android.jump.operations.main;

import android.Manifest;
import android.content.DialogInterface;
import android.content.Intent;
import android.net.Uri;
import android.support.annotation.NonNull;
import android.support.v7.app.AlertDialog;

import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.model.main.CallPhoneJumpModel;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.utils.StringConstant;

/**
 * 弹出拨打电话的dialog
 *
 * @author Administrator
 * @date 2017/7/17
 */
public class CallPhoneJumpOperation extends JumpOperation<CallPhoneJumpModel> {
    static {
        JumpOperationBinder.bind(
                CallPhoneJumpOperation.class,
                CallPhoneJumpModel.class,
                // 弹出拨打电话的dialog
                StringConstant.JUMP_APP_CALL_PHONE
        );
    }

    @Override
    public void start() {
        final String tele = request.getData().getTele();
        if (tele == null || tele.trim().isEmpty()) {
            return;
        }

        PermissionsUtil.requestPermission(request.getDisplay().act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                showCallPhoneDialog(tele);
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                PermissionTipInfo tip = PermissionTipInfo.getTip("Call Phone");
                request.getDisplay().showToast(tip.getContent());
            }
        }, false, null, Manifest.permission.CALL_PHONE);
    }

    private void showCallPhoneDialog(final String tele) {
        new AlertDialog.Builder(request.getDisplay().act())
                .setCancelable(false)
                .setTitle("tel: " + tele)
                .setPositiveButton("Call", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        Uri uri = Uri.parse("tel:" + tele.replaceAll("-", "").trim());
                        Intent intent = new Intent(Intent.ACTION_CALL, uri);
                        request.getDisplay().startActivity(intent);
                    }
                })
                .setNeutralButton("Cancle", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                    }
                })
                .show();
    }

}
