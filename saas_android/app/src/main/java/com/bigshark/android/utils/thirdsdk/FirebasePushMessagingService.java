package com.bigshark.android.utils.thirdsdk;

import android.app.NotificationManager;
import android.app.PendingIntent;
import android.content.Context;
import android.content.Intent;
import android.media.RingtoneManager;
import android.net.Uri;
import android.support.v4.app.NotificationCompat;

import com.bigshark.android.R;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.activities.home.MainActivity;
import com.bigshark.android.jump.JumpOperationHandler;
import com.google.firebase.messaging.FirebaseMessagingService;
import com.google.firebase.messaging.RemoteMessage;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

/**
 * @version V 1.0 xxxxxxxx
 * @author: L-BackPacker
 * @date: 2019.07.22 上午 9:48
 * @verdescript 版本号 修改时间  修改人 修改的概要说明
 * @Copyright: 2019
 */
public class FirebasePushMessagingService extends FirebaseMessagingService {


    @Override
    public void onMessageReceived(RemoteMessage remoteMessage) {
        // TODO(developer): Handle FCM messages here.
        KLog.d("push : " + ConvertUtils.toString(remoteMessage));
        CrashReport.postCatchedException(new Throwable("push ：" + ConvertUtils.toString(remoteMessage)));
        // Check if message contains a data payload.
        if (remoteMessage.getData().size() > 0) {
            JumpOperationHandler.setJumpOperationData(remoteMessage.getData().get("jumpData"));
        }
        // Check if message contains a notification payload.
        if (remoteMessage.getNotification() != null) {
            KLog.d("Message Notification Body: " + remoteMessage.getNotification().getBody());
            sendNotification(remoteMessage.getNotification().getTitle(), remoteMessage.getNotification().getBody());
        }
    }


    @Override
    public void onNewToken(String token) {
        KLog.d("Refreshed token: " + token);
        sendRegistrationToServer(token);
    }

    private void sendRegistrationToServer(String token) {
        // TODO: Implement this method to send token to your app server.
        FirebaseUtils.setGooglePushToken(token);
    }

    private void sendNotification(String title, String messageBody) {
        Intent intent = new Intent(this, MainActivity.class);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP);
        PendingIntent pendingIntent = PendingIntent.getActivity(this, 0, intent, PendingIntent.FLAG_ONE_SHOT);
        Uri defaultSoundUri = RingtoneManager.getDefaultUri(RingtoneManager.TYPE_NOTIFICATION);
        NotificationCompat.Builder notificationBuilder =
                new NotificationCompat.Builder(this)
                        .setAutoCancel(true)
                        .setContentTitle(title)
                        .setContentText(messageBody)
                        .setSmallIcon(R.drawable.application_logo)
                        .setSound(defaultSoundUri)
                        .setContentIntent(pendingIntent);

        NotificationManager notificationManager = (NotificationManager) getSystemService(Context.NOTIFICATION_SERVICE);
        if (notificationManager != null) {
            notificationManager.notify(0 /* ID of notification */, notificationBuilder.build());
        }
    }

}
