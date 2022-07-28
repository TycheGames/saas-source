package com.bigshark.android.vh.personal;

import android.app.Activity;
import android.support.v4.content.ContextCompat;
import android.text.SpannableString;
import android.text.Spanned;
import android.text.TextPaint;
import android.text.method.LinkMovementMethod;
import android.text.style.ClickableSpan;
import android.view.View;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.home.BrowserActivity;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.utils.StringConstant;

/**
 * Created by ytxu on 2019/9/27.
 */
public class PersonalProtocolUtils {
    public static void resetText(Activity activity, TextView agreementText) {
        agreementText.setText("");

        agreementText.append("By logging in, you agree to our ");
        agreementText.append(createCickableSpan(activity, "Terms Of Use", HttpConfig.getUrl(StringConstant.HTTP_PERSONAL_PROTOCO_TERMS_OF_USER)));
        agreementText.append(" & ");
        agreementText.append(createCickableSpan(activity, "Privacy Policy", HttpConfig.getUrl(StringConstant.HTTP_PERSONAL_PROTOCO_PRIVACY_POLICY)));
        agreementText.append(" & ");
        agreementText.append(createCickableSpan(activity, "User Agreement", HttpConfig.getUrl(StringConstant.HTTP_PERSONAL_PROTOCO_AGREEMENT)));
        agreementText.append(".");
        agreementText.setMovementMethod(LinkMovementMethod.getInstance());
        agreementText.setLongClickable(false);
    }

    /**
     * 可点击超链接 文字
     */
    private static SpannableString createCickableSpan(final Activity activity, String title, final String href) {
        SpannableString spannable = new SpannableString(title);
        spannable.setSpan(new ClickableSpan() {
            @Override
            public void updateDrawState(TextPaint ds) {
                ds.setColor(ContextCompat.getColor(activity, R.color.theme_secondary_color));
                ds.setUnderlineText(true);
            }

            @Override
            public void onClick(View widget) {
                BrowserActivity.goIntent(activity, href);
            }
        }, 0, spannable.length(), Spanned.SPAN_EXCLUSIVE_EXCLUSIVE);
        return spannable;
    }


    public static void resetMainPageText(Activity activity, TextView agreementText) {
        agreementText.setText("");

        agreementText.append("By clicking it, I accept the ");
        agreementText.append(createCickableSpan(activity, "Terms Of Use", HttpConfig.getUrl(StringConstant.HTTP_PERSONAL_PROTOCO_TERMS_OF_USER)));
        agreementText.append(" & ");
        agreementText.append(createCickableSpan(activity, "Privacy Policy", HttpConfig.getUrl(StringConstant.HTTP_PERSONAL_PROTOCO_PRIVACY_POLICY)));
        agreementText.append(" & ");
        agreementText.append(createCickableSpan(activity, "User Agreement", HttpConfig.getUrl(StringConstant.HTTP_PERSONAL_PROTOCO_AGREEMENT)));
        agreementText.append(".");
        agreementText.setMovementMethod(LinkMovementMethod.getInstance());
        agreementText.setLongClickable(false);
    }

}
