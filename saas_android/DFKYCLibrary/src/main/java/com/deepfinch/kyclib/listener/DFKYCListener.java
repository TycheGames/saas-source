package com.deepfinch.kyclib.listener;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public interface DFKYCListener<T> {
    void callbackResult(T result);

    void onBack();
}
