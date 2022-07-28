package com.bigshark.android.jump.operations.main;

import android.content.Intent;

import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.model.main.AuthJumpModel;
import com.bigshark.android.activities.authenticate.AddressCardAuthActivity;
import com.bigshark.android.activities.authenticate.EmergencyContactActivity;
import com.bigshark.android.activities.authenticate.KycDocumentsActivity;
import com.bigshark.android.activities.authenticate.PersonalFaceAuthenticateActivity;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.utils.StringConstant;

/**
 * 最简单的跳转的功能
 *
 * @author Administrator
 * @date 2017/7/17
 */
public class AuthJumpOperation extends JumpOperation<AuthJumpModel> {
    static {
        JumpOperationBinder.bind(
                AuthJumpOperation.class,
                AuthJumpModel.class,
                StringConstant.JUMP_AUTHENTICATE_CONTACT,
                StringConstant.JUMP_AUTHENTICATE_LIVENESS,
                StringConstant.JUMP_AUTHENTICATE_ADDRESS_PROOF,
                StringConstant.JUMP_AUTHENTICATE_OCR_AUTH_CENTER
        );
    }

    @Override
    public void start() {
        switch (path()) {
            case StringConstant.JUMP_AUTHENTICATE_CONTACT:
                request.startActivity(new Intent(request.activity(), EmergencyContactActivity.class));
                break;
            case StringConstant.JUMP_AUTHENTICATE_LIVENESS:
                request.startActivity(new Intent(request.activity(), PersonalFaceAuthenticateActivity.class));
                break;
            case StringConstant.JUMP_AUTHENTICATE_OCR_AUTH_CENTER:
                request.startActivity(new Intent(request.activity(), KycDocumentsActivity.class));
                break;
            case StringConstant.JUMP_AUTHENTICATE_ADDRESS_PROOF:
                request.getDisplay().startActivity(new Intent(request.activity(), AddressCardAuthActivity.class));
                break;
            default:
                break;
        }
    }

}
