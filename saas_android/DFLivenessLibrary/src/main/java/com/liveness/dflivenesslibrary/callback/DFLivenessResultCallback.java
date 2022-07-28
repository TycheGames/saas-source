package com.liveness.dflivenesslibrary.callback;

import com.deepfinch.liveness.DFLivenessSDK;

/**
 * Copyright (c) 2017-2019 DEEPFINCH Corporation. All rights reserved.
 **/
public interface DFLivenessResultCallback {
    void saveFinalEncrytFile(byte[] livenessEncryptResult, DFLivenessSDK.DFLivenessImageResult[] imageResult);

    void saveFile(byte[] livenessEncryptResult);

    void deleteLivenessFiles();
}
