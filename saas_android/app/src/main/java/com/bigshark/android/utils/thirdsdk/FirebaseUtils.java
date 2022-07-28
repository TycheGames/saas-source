package com.bigshark.android.utils.thirdsdk;

import android.support.annotation.NonNull;

import com.bigshark.android.core.xutilshttp.RequestHeaderUtils;
import com.google.android.gms.tasks.OnCompleteListener;
import com.google.android.gms.tasks.Task;
import com.google.firebase.iid.FirebaseInstanceId;
import com.google.firebase.iid.InstanceIdResult;
import com.socks.library.KLog;

/**
 * google 推送
 * Created by ytxu on 2019/9/22.
 */
public class FirebaseUtils {


    public static void fetchFirebaseMessageToken() {
        KLog.d("getInstanceId start");
        FirebaseInstanceId.getInstance().getInstanceId().addOnCompleteListener(new OnCompleteListener<InstanceIdResult>() {
            @Override
            public void onComplete(@NonNull Task<InstanceIdResult> task) {
                if (!task.isSuccessful()) {
                    KLog.d("getInstanceId failed：" + task.getException(), task.getException());
                    if (task.getException() != null) {
                        task.getException().printStackTrace();
                    }
                    return;
                }

                // Get new Instance ID token
                KLog.d("getInstanceId success：" + task.getResult().getToken());
                FirebaseUtils.setGooglePushToken(task.getResult().getToken());
            }
        });
    }

    static void setGooglePushToken(String googlePushToken) {
        KLog.d("googlePushToken:" + googlePushToken);
        RequestHeaderUtils.updateGooglePushToken(googlePushToken);
    }


}
