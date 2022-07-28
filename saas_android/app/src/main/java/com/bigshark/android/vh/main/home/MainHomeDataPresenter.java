package com.bigshark.android.vh.main.home;

import com.bigshark.android.fragments.home.MainFragment;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.home.MainHomeResponseModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.utils.StringConstant;
import com.socks.library.KLog;

/**
 * 首页数据层
 * Created by ytxu on 2019/9/19.
 */
public class MainHomeDataPresenter {

    private MainFragment mMainFragment;
    private PageDataCallback pageDataCallback;

    private MainHomeResponseModel pageData;

    public MainHomeDataPresenter(MainFragment fragment) {
        this.mMainFragment = fragment;
    }

    public void setPageDataCallback(PageDataCallback pageDataCallback) {
        this.pageDataCallback = pageDataCallback;
    }

    public void getPageData() {
        HttpSender.get(new CommonResponsePendingCallback<MainHomeResponseModel>(mMainFragment) {

            @Override
            public CommonRequestParams createRequestParams() {
                String getHomeDataUrl = HttpConfig.getRealUrl(StringConstant.HTTP_APP_GET_HOME_DATA);
                return new CommonRequestParams(getHomeDataUrl);
            }

            @Override
            public void handleSuccess(MainHomeResponseModel resultData, int resultCode, String resultMessage) {
                pageData = resultData;
                pageDataCallback.onSuccess(resultCode, resultMessage, resultData);
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                mMainFragment.showToast(resultMessage);
                pageDataCallback.onFailed();
            }

            @Override
            public void onCancelled(CancelledException cex) {
                super.onCancelled(cex);
                KLog.d(cex);
            }
        });
    }


    public interface PageDataCallback {
        void onSuccess(int code, String message, MainHomeResponseModel data);

        void onFailed();
    }
}
