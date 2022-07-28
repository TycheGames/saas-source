package com.bigshark.android.jump.base;


import com.bigshark.android.jump.JumpOperationRequest;

/**
 * 错误的跳转action
 * 意外的跳转、未定义的跳转
 *
 * @author Administrator
 * @date 2017/7/25
 */
public class UnexpectedJumpOperation extends JumpOperation<JumpModel> {

    private final UnexpectedType unexpectedType;
    /**
     * 错误提示信息
     */
    private final String tipMessage;

    public UnexpectedJumpOperation(JumpOperationRequest<JumpModel> request, UnexpectedType unexpectedType, String tipMessage) {
        setRequest(request);
        this.unexpectedType = unexpectedType;
        this.tipMessage = tipMessage;
    }


    @Override
    public void start() {
        // do nothing...
    }

    public String getTipMessage() {
        return tipMessage;
    }

    /**
     * 错误类型
     */
    public enum UnexpectedType {
        /**
         * 跳转数据为null或空字符串等，不能转换为跳转对象
         */
        CanNotConvertToJumpOperationData,
        /**
         * 没有找到对应的跳转type
         */
        NotFindTargetJumpType,
        /**
         * Command反射异常
         */
        JumpOperationActionException,
        /**
         * 设置的指令数据与指令类型，不匹配
         */
        JumpTypeIsNotMatchJumpOperationData
    }

}
