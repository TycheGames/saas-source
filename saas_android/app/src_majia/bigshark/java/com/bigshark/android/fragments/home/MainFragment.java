package com.bigshark.android.fragments.home;

import android.os.Bundle;
import android.view.View;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.display.DisplayBaseFragment;
import com.bigshark.android.events.BaseDisplayEventModel;
import com.bigshark.android.events.RefreshDisplayEventModel;
import com.bigshark.android.http.model.home.MainHomeResponseModel;
import com.bigshark.android.jump.JumpOperationHandler;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.thirdsdk.MaiDianUploaderUtils;
import com.bigshark.android.vh.main.home.MainFloatBannerViewHelper;
import com.bigshark.android.vh.main.home.MainHomeDataPresenter;
import com.bigshark.android.vh.main.home.MoneyDisplayerUtils;
import com.bigshark.android.vh.personal.PersonalProtocolUtils;
import com.scwang.smartrefresh.layout.SmartRefreshLayout;
import com.scwang.smartrefresh.layout.api.RefreshLayout;
import com.scwang.smartrefresh.layout.listener.OnRefreshListener;

import butterknife.BindView;
import butterknife.ButterKnife;
import de.greenrobot.event.EventBus;

/**
 * Created by hpzhan on 2019/6/27.
 */
public class MainFragment extends DisplayBaseFragment {

    @BindView(R.id.main_fragment_titleView)
    NavigationStatusLinearLayout titleView;
    private SmartRefreshLayout refreshLayout;

    @BindView(R.id.main_fragment_credit_msg_TextView)
    TextView main_fragment_credit_msg_TextView;
    @BindView(R.id.main_fragment_credit_limit_TextView)
    TextView main_fragment_credit_limit_TextView;

    @BindView(R.id.main_fragment_enter_btn_TextView)
    TextView main_fragment_enter_btn_TextView;

    private TextView main_fragment_protecol_TextView;
    /**
     * 悬浮Banner
     */
    private ImageView main_fragment_floatBanner_ImageView;

    private MainHomeDataPresenter homePresenter;
    private MainHomeResponseModel mainHomeResponseModel;


    private MainFloatBannerViewHelper floatBannerVh;
    private Runnable floatBannerCreater = new Runnable() {
        @Override
        public void run() {
            floatBannerVh = new MainFloatBannerViewHelper(MainFragment.this, main_fragment_floatBanner_ImageView, 0f, refreshLayout.getBottom());
        }
    };


    @Override
    protected int getLayoutId() {
        return R.layout.main_fragment;
    }

    @Override
    protected void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this, fragmentRoot);
        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_HOME_ENTER).build();

        homePresenter = new MainHomeDataPresenter(this);

        refreshLayout = fragmentRoot.findViewById(R.id.main_fragment_refresh);
        main_fragment_credit_limit_TextView.setText("");
        main_fragment_protecol_TextView = fragmentRoot.findViewById(R.id.main_fragment_protecol_TextView);
        PersonalProtocolUtils.resetMainPageText(getActivity(), main_fragment_protecol_TextView);

        main_fragment_floatBanner_ImageView = fragmentRoot.findViewById(R.id.main_fragment_floatimage_ImageView);
        main_fragment_floatBanner_ImageView.post(floatBannerCreater);
    }

    @Override
    protected void bindListeners() {
        super.bindListeners();
        homePresenter.setPageDataCallback(new MainHomeDataPresenter.PageDataCallback() {
            @Override
            public void onSuccess(int code, String message, MainHomeResponseModel data) {
                refreshLayout.finishRefresh();

                if (data == null) {
                    showToast(message);
                    return;
                }

                mainHomeResponseModel = data;
                refreshView();
            }

            @Override
            public void onFailed() {
                refreshLayout.finishRefresh();
            }
        });

        refreshLayout.setOnRefreshListener(new OnRefreshListener() {
            @Override
            public void onRefresh(RefreshLayout refreshLayout) {
                homePresenter.getPageData();
            }
        });

        main_fragment_enter_btn_TextView.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (mainHomeResponseModel == null) {
                    return;
                }
                MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_HOME).build();
                JumpOperationHandler.convert(mainHomeResponseModel.getJump()).createRequest().setDisplay(display()).jump();
            }
        });
    }

    private void refreshView() {
        if (mainHomeResponseModel == null) {
            return;
        }

        if (mainHomeResponseModel.isRefreshTabList()) {
            EventBus.getDefault().post(new RefreshDisplayEventModel(BaseDisplayEventModel.EVENT_REFRESH_MAIN_TAB_LIST));
        }

        titleView.setTitle(R.string.app_name);

        String moneyText = "₹ " + MoneyDisplayerUtils.formatMoneyText(mainHomeResponseModel.getMoneyAmount());
        main_fragment_credit_limit_TextView.setText(moneyText);

        main_fragment_enter_btn_TextView.setText(mainHomeResponseModel.getActionText());

        floatBannerVh.setData(mainHomeResponseModel.getFloatInfo());
    }


    @Override
    protected void setupDatas() {
    }


    @Override
    public void onResume() {
        super.onResume();
        refreshLayout.autoRefresh();
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        main_fragment_floatBanner_ImageView.removeCallbacks(floatBannerCreater);
    }
}
