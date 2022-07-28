package com.bigshark.android.core.component;

import android.os.Handler;
import android.os.HandlerThread;
import android.support.multidex.MultiDexApplication;

public class BaseApplication extends MultiDexApplication {

    public static boolean isDebug = false;
    public static BaseApplication app;


    @Override
    public void onCreate() {
        super.onCreate();
        BaseApplication.app = this;
    }


    private static Handler workHandler;

    static {
        HandlerThread handlerThread = new HandlerThread("app-work-thread");
        handlerThread.start();
        workHandler = new Handler(handlerThread.getLooper());
    }

    public static Handler getWorkHandler() {
        return workHandler;
    }

}
