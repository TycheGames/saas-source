package com.bigshark.android.activities.home;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.TextView;

import com.bigshark.android.BuildConfig;
import com.bigshark.android.R;
import com.bigshark.android.adapters.home.SelectUrlAdapter;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.jump.JumpOperationHandler;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.thirdsdk.FirebaseAnalyticsUtils;
import com.bigshark.android.utils.thirdsdk.FirebaseUtils;

import java.lang.reflect.Constructor;
import java.lang.reflect.Field;
import java.lang.reflect.Method;
import java.util.Arrays;

public class ApplicationEnterActivity extends com.bigshark.android.display.DisplayBaseActivity {

    @Override
    protected int getLayoutId() {
        return R.layout.activity_application_enter;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        closeDialog4AndroidP();
        FirebaseUtils.fetchFirebaseMessageToken();
        FirebaseAnalyticsUtils.init(this);

        findViewById(R.id.app_startup_root).setVisibility(BuildConfig.DEBUG ? View.VISIBLE : View.GONE);

        if (BuildConfig.SELECT_BASE_URLS.length > 0) {
            initSelectPage();
        } else {
            gotoGuidePage(BuildConfig.NETWORK_URL_PRODUCT_LIST);
        }
    }

    private void gotoGuidePage(String[] baseUrls) {
        HttpConfig.setCurrentServiceBaseUrl(baseUrls);

        Intent intent = new Intent(ApplicationEnterActivity.this, ApplicationSplashActivity.class);
        startActivity(intent);
        finish();
    }

    private void initSelectPage() {
        ListView selectUrlListView = findViewById(R.id.application_enter_urls_list);
        SelectUrlAdapter adapter = new SelectUrlAdapter(this, data -> gotoGuidePageForDeveloperType(new String[]{data}));
        selectUrlListView.setAdapter(adapter);
        adapter.append(Arrays.asList(BuildConfig.SELECT_BASE_URLS));

        TextView productUrlText = findViewById(R.id.application_enter_product_url);
        productUrlText.setText(BuildConfig.NETWORK_URL_PRODUCT_LIST[0]);
        productUrlText.setOnClickListener(view -> gotoGuidePageForDeveloperType(BuildConfig.NETWORK_URL_PRODUCT_LIST));

        final EditText developerUrlEditText = findViewById(R.id.app_startup_dev_edit);
        developerUrlEditText.setText(MmkvGroup.app().getConfigUrl());
        findViewById(R.id.application_enter_developer_url_EditText).setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String developerWebsiteEditUrl = developerUrlEditText.getText().toString().trim();
                MmkvGroup.app().setConfigUrl(developerWebsiteEditUrl);

                gotoGuidePageForDeveloperType(new String[]{developerWebsiteEditUrl});
            }
        });

        final EditText browserEdit = findViewById(R.id.app_startup_gotobrowser_edit);
        browserEdit.setText(MmkvGroup.app().getWebviewUrl());
        findViewById(R.id.application_enter_browser_edit).setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String browserUrl = browserEdit.getText().toString().trim();
                MmkvGroup.app().setBrowserUrl(browserUrl);

                BrowserActivity.goIntent(ApplicationEnterActivity.this, browserUrl);
            }
        });
    }

    private void gotoGuidePageForDeveloperType(String[] baseUrls) {
        showToast(baseUrls[0]);
        gotoGuidePage(baseUrls);
    }


    @Override
    public void bindListeners(Bundle savedInstanceState) {
    }


    @Override
    public void setupDatas() {
        Intent intent = getIntent();
        if (intent != null && intent.getExtras() != null) {
            for (String key : getIntent().getExtras().keySet()) {
                Object value = getIntent().getExtras().get(key);
                if ("jumpData".equals(key)) {
                    JumpOperationHandler.setJumpOperationData(value.toString());
                }
            }
        }
    }


    private void closeDialog4AndroidP() {
        try {
            Class aClass = Class.forName("android.content.pm.PackageParser$Package");
            Constructor declaredConstructor = aClass.getDeclaredConstructor(String.class);
            declaredConstructor.setAccessible(true);
        } catch (Exception e) {
            e.printStackTrace();
        }

        try {
            Class cls = Class.forName("android.app.ActivityThread");
            Method declaredMethod = cls.getDeclaredMethod("currentActivityThread");
            declaredMethod.setAccessible(true);
            Object activityThread = declaredMethod.invoke(null);
            Field mHiddenApiWarningShown = cls.getDeclaredField("mHiddenApiWarningShown");
            mHiddenApiWarningShown.setAccessible(true);
            mHiddenApiWarningShown.setBoolean(activityThread, true);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
