package com.deepfinch.kyclib.listener;

import com.deepfinch.kyclib.model.DFErrorModel;
import com.deepfinch.kyclib.model.DFKYCModel;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public interface DFKYCCallback {
    void callbackResult(DFKYCModel result, String aadhaarNumber);

    void onError(DFErrorModel errorModel);

    void onBack();
}
