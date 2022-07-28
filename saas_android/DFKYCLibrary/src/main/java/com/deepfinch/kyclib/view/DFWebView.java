package com.deepfinch.kyclib.view;

import android.content.Context;
import android.util.AttributeSet;
import android.webkit.WebView;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFWebView extends WebView {
    public DFWebView(Context context) {
        super(context);
    }

    public DFWebView(Context context, AttributeSet attrs) {
        super(context, attrs);
    }

    public DFWebView(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
    }

    public int getVerticalScrollRange(){
        return super.computeVerticalScrollRange();
    }

}
