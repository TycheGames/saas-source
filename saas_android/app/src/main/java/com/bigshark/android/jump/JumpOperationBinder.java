package com.bigshark.android.jump;

import android.support.annotation.NonNull;

import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.jump.base.JumpModel;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.base.UnexpectedJumpOperation;
import com.bigshark.android.utils.StringConstant;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

import java.lang.reflect.Constructor;
import java.lang.reflect.InvocationTargetException;
import java.util.Arrays;
import java.util.HashMap;
import java.util.Map;

/**
 * 指令的查找器，并转换为对应的数据类型
 * 保存各个ViewRouter与其处理的跳转path与type的映射关系
 * 1、以后以path为主，type不再添加
 *
 * @author Administrator
 * @date 2018/4/23
 */
public class JumpOperationBinder {

    public static boolean isDebug = false;

    /**
     * 保存各个ViewRouter与其处理的跳转path的映射关系
     */
    private static final Map<String, Entry> PATH_JUMPS = new HashMap<>(100);


    //********************* RouterHelper bind *********************

    /**
     * 使用反射，将指令注册到RouterRegistry中
     *
     * @param jumpClassArray 注册的指令class
     */
    @SafeVarargs
    public static void bindJumpOperations(Class<? extends JumpOperation>... jumpClassArray) {
        for (Class jumpClass : jumpClassArray) {
            if (jumpClass == null) {
                continue;
            }

            try {
                Class.forName(jumpClass.getName());
            } catch (ClassNotFoundException e) {
                e.printStackTrace();
            }
        }
    }


    //********************* jump saver *********************

    private static final class Entry<R extends JumpOperation, D extends JumpModel> {
        private final Class<R> jumpClass;
        private final Class<D> jumpDataClass;

        public Entry(Class<R> jumpClass, Class<D> jumpDataClass) {
            this.jumpClass = jumpClass;
            this.jumpDataClass = jumpDataClass;
        }

        @Override
        public String toString() {
            return "Entry{" +
                    "jumpClass=" + jumpClass.getName() +
                    ", jumpDataClass=" + jumpDataClass.getName() +
                    '}';
        }
    }

    /**
     * 注册路由指令：注入支持类型及指令数据结构
     *
     * @param jumpClass     Router的真实处理类
     * @param jumpDataClass Router的指令的数据结构class
     * @param supportPaths    routerClass支持的跳转path列表
     */
    public static <T extends JumpOperation, Data extends JumpModel> void bind(Class<T> jumpClass, Class<Data> jumpDataClass, String... supportPaths) {
        KLog.d("class:" + jumpClass.getName() + "， data Class:" + jumpDataClass.getName() + ", support Paths:" + Arrays.toString(supportPaths));
        Entry entry = new Entry<>(jumpClass, jumpDataClass);
        for (String supportPath : supportPaths) {
            abnormalDetection(entry, supportPath);
            PATH_JUMPS.put(supportPath, entry);
        }
    }

    /**
     * 异常检测，防止出现多个ViewRouter，都同时支持一个跳转path的问题
     */
    private static void abnormalDetection(Entry jumpEntry, String supportPath) {
        if (!isDebug) {
            // release环境，不需要检测
            return;
        }

        Entry cacheJumpEntry = PATH_JUMPS.get(supportPath);
        if (cacheJumpEntry != null) {
            String message = "跳转类型：" + supportPath + ", 支持类：" + cacheJumpEntry.toString() + ", 重复的类型：" + jumpEntry.toString();
            KLog.e(message);
            CrashReport.postCatchedException(new Throwable(message));
        }
    }

    //********************* jump data converter *********************

    /**
     * 转换为对应的数据类型
     *
     * @param jumpContent 指令的json格式字符串
     * @return 对应的数据类型
     */
    @NonNull
    static JumpModel convert(final String jumpContent) {
        if (jumpContent == null || jumpContent.trim().isEmpty()) {
            KLog.e("jump content is null or empty...");
            return new JumpModel();
        }

        JumpModel model = ConvertUtils.toObject(jumpContent, JumpModel.class);
        if (model == null) {
            String message = "jump json parse failed, jump content:" + jumpContent;
            KLog.e(message);
            CrashReport.postCatchedException(new Throwable(message));
            return new JumpModel();
        }

        final String path = model.getPath();
        Entry<?, ?> entry = PATH_JUMPS.get(path);
        if (entry == null) {
            // 没有找到对应的跳转type
            String message = "no target jump path:" + path + ", jump content:" + jumpContent;
            KLog.e(message);
            CrashReport.postCatchedException(new Throwable(message));
            return new JumpModel();
        }

        JumpModel jumpModel = ConvertUtils.toObject(jumpContent, entry.jumpDataClass);
        return jumpModel == null ? new JumpModel() : jumpModel;
    }


    //********************* jump finder *********************

    @NonNull
    static JumpOperation findJump(JumpOperationRequest request) {
        Entry<?, ?> entry;

        final String path = request.getData().getPath();
        entry = PATH_JUMPS.get(path);
        if (entry != null) {
            Class<? extends JumpModel> dataClass = entry.jumpDataClass;
            if (dataClass != request.getData().getClass()) {
                KLog.w("jump path warns：jump Helper Data:" + dataClass + ", request.data class:" + request.getData().getClass());
                if (!dataClass.isInstance(request.getData())) {
                    // 设置的指令数据与指令类型，不匹配
                    String message = "jump data class no match warns: path:" + path + ", jumpData class:" + dataClass + ", request.data class:" + request.getData().getClass();
                    KLog.e(message);
                    CrashReport.postCatchedException(new Throwable(message));
                    return new UnexpectedJumpOperation(request, UnexpectedJumpOperation.UnexpectedType.JumpTypeIsNotMatchJumpOperationData, message);
                }
            }
            return createJump(request, entry.jumpClass);
        }

        // 没有找到对应的跳转type和path
        String message = "unfind target jump path:" + path;
        KLog.e(message);
        // 默认错误码，不上报bugly
        if (!StringConstant.JUMP_OPERATION_BINDER_UNKNOWN.equals(path)) {
            CrashReport.postCatchedException(new Throwable(message));
        }
        return new UnexpectedJumpOperation(request, UnexpectedJumpOperation.UnexpectedType.NotFindTargetJumpType, message);
    }

    @NonNull
    private static JumpOperation createJump(JumpOperationRequest request, Class<? extends JumpOperation> jumpClass) {
        try {
            Constructor<? extends JumpOperation> con = jumpClass.getConstructor();
            JumpOperation jumpOperation = con.newInstance();
            jumpOperation.setRequest(request);
            return jumpOperation;
        } catch (NoSuchMethodException e) {
            e.printStackTrace();
        } catch (InstantiationException e) {
            e.printStackTrace();
        } catch (IllegalAccessException e) {
            e.printStackTrace();
        } catch (InvocationTargetException e) {
            e.printStackTrace();
        }
        String message = "jump exception:" + jumpClass.getName();
        KLog.e(message);
        CrashReport.postCatchedException(new Throwable(message));
        return new UnexpectedJumpOperation(request, UnexpectedJumpOperation.UnexpectedType.JumpOperationActionException, message);
    }


}
