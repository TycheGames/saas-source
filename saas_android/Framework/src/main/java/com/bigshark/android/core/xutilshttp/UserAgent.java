/*
 * Copyright 2015 Yan Zhenjie
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package com.bigshark.android.core.xutilshttp;

import android.os.Build;
import android.text.TextUtils;
import android.util.Log;

import java.util.Locale;

/**
 * Created in Oct 15, 2015 12:39:06 PM.
 *
 * @author Yan Zhenjie.
 */
public class UserAgent {

    public static final String KEY = "User-Agent";

    /**
     * UserAgent.
     */
    private static String userAgent;


    public static String get() {
        return userAgent == null ? "Android" : userAgent;
    }

    /**
     * Get the singleton UA.
     * Get User-Agent of System.
     *
     * @param uniqueIdentifier APP马甲的唯一识别符
     * @return UA.
     */
    public static void newInstance(String uniqueIdentifier, String versionName, Callback callback) {
        StringBuffer userAgentBuffer = new StringBuffer();
        // Add version
        userAgentBuffer = addAndroidVersion(userAgentBuffer, callback);
        userAgentBuffer.append("; ");
        userAgentBuffer = addLanguage(userAgentBuffer, callback);
        userAgentBuffer.append("; ");
        // add the model for the release build
        userAgentBuffer = addModel(userAgentBuffer, callback);
        userAgentBuffer.append("; ");
        userAgentBuffer = addReleaseBuild(userAgentBuffer, callback);
        userAgentBuffer.append("; ");
        userAgentBuffer = addAppVersion(uniqueIdentifier, versionName, userAgentBuffer, callback);
        userAgent = userAgentBuffer.toString();
        Log.d(KEY, userAgent);
    }

    private static StringBuffer addAndroidVersion(StringBuffer buffer, Callback callback) {
        StringBuffer newBuffer = new StringBuffer("Android ");
        final String version = Build.VERSION.RELEASE;
        newBuffer.append(version.length() > 0 ? version : "1.0");// default to "1.0"
        return callback.checkTargetSb(buffer, newBuffer);
    }

    private static StringBuffer addLanguage(StringBuffer buffer, Callback callback) {
        StringBuffer newBuffer = new StringBuffer(buffer);
        Locale locale = Locale.getDefault();
        final String language = locale.getLanguage();
        if (language != null) {
            newBuffer.append(language.toLowerCase(locale));
            final String country = locale.getCountry();
            if (!TextUtils.isEmpty(country)) {
                newBuffer.append("-");
                newBuffer.append(country.toLowerCase(locale));
            }
        } else {
            // default to "en"
            newBuffer.append("en");
        }
        return callback.checkTargetSb(buffer, newBuffer);
    }

    private static StringBuffer addModel(StringBuffer buffer, Callback callback) {
        StringBuffer newBuffer = new StringBuffer(buffer);
        if ("REL".equals(Build.VERSION.CODENAME)) {
            final String model = Build.MODEL;
            if (model.length() > 0) {
                newBuffer.append(model);
            }
        }
        return callback.checkTargetSb(buffer, newBuffer);
    }

    private static StringBuffer addReleaseBuild(StringBuffer buffer, Callback callback) {
        StringBuffer newBuffer = new StringBuffer(buffer);
        final String id = Build.ID;
        if (id.length() > 0) {
            newBuffer.append("Build/");
            newBuffer.append(id);
        }
        return callback.checkTargetSb(buffer, newBuffer);
    }

    private static StringBuffer addAppVersion(String uniqueIdentifier, String versionName, StringBuffer buffer, Callback callback) {
        StringBuffer newBuffer = new StringBuffer(buffer);
        newBuffer.append(uniqueIdentifier).append("/").append(versionName);
        return callback.checkTargetSb(buffer, newBuffer);
    }


    public interface Callback {
        StringBuffer checkTargetSb(StringBuffer buffer, StringBuffer newBuffer);
    }

}
