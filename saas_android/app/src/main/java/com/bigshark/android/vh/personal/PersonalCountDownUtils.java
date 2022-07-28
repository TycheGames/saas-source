package com.bigshark.android.vh.personal;

import android.os.CountDownTimer;
import android.widget.TextView;

public class PersonalCountDownUtils {

    public static void startCountDownTimer(final TextView textView) {
        /** 倒计时60秒，一次1秒 */
        CountDownTimer timer = new CountDownTimer(60 * 1000, 1000) {
            @Override
            public void onTick(long millisUntilFinished) {
                textView.setText(millisUntilFinished / 1000 + "s");
                textView.setEnabled(false);
            }

            @Override
            public void onFinish() {
                textView.setText("Send OTP");
                textView.setEnabled(true);
            }
        }.start();
    }

}
