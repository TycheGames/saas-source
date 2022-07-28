package com.liveness.dflivenesslibrary;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.app.Fragment;
import android.app.FragmentTransaction;
import android.content.Intent;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.View;
import android.view.Window;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.liveness.dflivenesslibrary.utils.StatusBarCompat;

@SuppressLint("NewApi")
public abstract class DFAcitivityBase extends Activity {
    private static final String TAG = "DFAcitivityBase";
    /**
     * No permission to access camera
     */
    public static final int RESULT_CAMERA_ERROR_NOPRERMISSION_OR_USED = 2;

    /**
     * activity title
     */
    public static final String KEY_ACTIVITY_TITLE = "key.activity.title";


    protected Fragment mFragment;

    protected RelativeLayout mRlytBack;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        getWindow().requestFeature(Window.FEATURE_NO_TITLE);
        setContentView(R.layout.layout_activity_main);
        mRlytBack = findViewById(R.id.id_ll_back);
        mRlytBack.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                finish();
            }
        });
        ((TextView) findViewById(R.id.id_tv_title)).setText(getActivityTitle());
        mFragment = getFrament();
        FragmentTransaction fragmentTransaction = getFragmentManager().beginTransaction();
        fragmentTransaction.add(R.id.fragment_container, mFragment);
        fragmentTransaction.commit();
        StatusBarCompat.translucentStatusBar(this, false);
    }

    protected abstract Fragment getFrament();

    private String getActivityTitle() {
        String title = getIntent().getStringExtra(KEY_ACTIVITY_TITLE);
        if (TextUtils.isEmpty(title)) {
            title = getString(getTitleString());
        }
        return title;
    }

    protected abstract int getTitleString();

    @Override
    public void onBackPressed() {
        Intent intent = new Intent();
        setResult(RESULT_CANCELED, intent);
        finish();
    }

    @Override
    protected void onDestroy() {
        // TODO Auto-generated method stub
        super.onDestroy();
    }

    public void onErrorHappen(int resultCode) {
        setResult(resultCode);
        finish();
    }

}
