package com.bigshark.android.activities.authenticate;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.core.common.RequestCodeType;
import com.bigshark.android.core.common.event.UserLoginedEvent;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.authenticate.EmergencyContactResponseModel;
import com.bigshark.android.http.model.contact.ContactInfoData;
import com.bigshark.android.http.model.contact.ContactRelationshipConfig;
import com.bigshark.android.http.model.contact.EmergencyContactDataResponseModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.jump.JumpOperationHandler;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.thirdsdk.MaiDianUploaderUtils;
import com.bigshark.android.vh.authenticate.emergencycontact.EmergencyContactChooseDialog;
import com.bigshark.android.vh.authenticate.emergencycontact.EmergencyContactChooseVh;

import java.util.List;

import butterknife.ButterKnife;
import butterknife.OnClick;
import de.greenrobot.event.EventBus;

/**
 * 认证：紧急联系人+各种第三方账号输入
 */
public class EmergencyContactActivity extends DisplayBaseActivity {

    private TextView urgentRelationshipText;
    private EmergencyContactChooseVh urgentChooseVh;
    private TextView otherRelationshipText;
    private EmergencyContactChooseVh otherChooseVh;

    private View optionalView;
    private EditText facebookEdit, whatsAppEdit, skypeEdit;

    private TextView submitView;

    private EmergencyContactDataResponseModel contactData = null;
    /**
     * 选择与本人的关系
     */
    private int curUrgentRelationshipPosition = 0;
    private int curOtherRelationshipPosition = 0;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_emergency_contact;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        EventBus.getDefault().register(this);

        initUrgentViews();
        initOtherViews();

        optionalView = findViewById(R.id.authenticate_contact_optional_root);
        optionalView.setVisibility(View.GONE);
        facebookEdit = findViewById(R.id.authenticate_contact_facebook_edit);
        whatsAppEdit = findViewById(R.id.authenticate_contact_whats_app_edit);
        skypeEdit = findViewById(R.id.authenticate_contact_skype_edit);

        submitView = findViewById(R.id.authenticate_contact_submit);
    }

    private void initUrgentViews() {
        urgentRelationshipText = findViewById(R.id.authenticate_contact_urgent_relation_text);
        View nameRoot = findViewById(R.id.authenticate_contact_urgent_name_root);
        TextView urgentNameText = findViewById(R.id.authenticate_contact_urgent_name_text);
        TextView urgentPhoneText = findViewById(R.id.authenticate_contact_urgent_phone_text);
        urgentChooseVh = new EmergencyContactChooseVh(this, true, nameRoot, urgentNameText, urgentPhoneText,
                new EmergencyContactChooseVh.Callback() {
                    @Override
                    public boolean haveSameInfo(String selectName, String selectPhone) {
                        if (contactData == null) {
                            return true;
                        }
                        String otherName = contactData.getGetData().getOtherName();
                        String otherPhone = contactData.getGetData().getOtherPhone();
                        boolean isSameContactName = !StringUtil.isBlank(selectName) && !StringUtil.isBlank(otherName) && selectName.equals(otherName);
                        boolean isSameContactPhone = !StringUtil.isBlank(selectPhone) && !StringUtil.isBlank(otherPhone) && selectPhone.indexOf(otherPhone) != -1;
                        return isSameContactName || isSameContactPhone;
                    }

                    @Override
                    public void setNewContactInfo(String newName, String newPhone) {
                        if (contactData == null) {
                            return;
                        }
                        contactData.getGetData().setName(newName);
                        contactData.getGetData().setPhone(newPhone);
                    }
                }
        );
    }

    private void initOtherViews() {
        otherRelationshipText = findViewById(R.id.authenticate_contact_other_relation_text);
        View otherNameRoot = findViewById(R.id.authenticate_contact_other_name_root);
        TextView otherNameText = findViewById(R.id.authenticate_contact_other_name_text);
        TextView otherPhoneText = findViewById(R.id.authenticate_contact_other_phone_text);
        otherChooseVh = new EmergencyContactChooseVh(this, false, otherNameRoot, otherNameText, otherPhoneText,
                new EmergencyContactChooseVh.Callback() {
                    @Override
                    public boolean haveSameInfo(String selectName, String selectPhone) {
                        if (contactData == null) {
                            return true;
                        }
                        String urgentName = contactData.getGetData().getName();
                        String urgentPhone = contactData.getGetData().getPhone();
                        boolean isSameContactName = !StringUtil.isBlank(selectName) && !StringUtil.isBlank(urgentName) && selectName.equals(urgentName);
                        boolean isSameContactPhone = !StringUtil.isBlank(selectPhone) && !StringUtil.isBlank(urgentPhone) && selectPhone.indexOf(urgentPhone) != -1;
                        return isSameContactName || isSameContactPhone;
                    }

                    @Override
                    public void setNewContactInfo(String newName, String newPhone) {
                        if (contactData == null) {
                            return;
                        }
                        contactData.getGetData().setOtherName(newName);
                        contactData.getGetData().setOtherPhone(newPhone);
                    }
                }
        );
    }


    @Override
    public void bindListeners(Bundle savedInstanceState) {
    }

    private void selectRelationship(final int requestCodeType) {
        if (contactData == null || contactData.getSelectData() == null) {
            return;
        }

        if (requestCodeType == RequestCodeType.CONTACT_URGENT_CONTACT) {
            new EmergencyContactChooseDialog(this)
                    .builder().setCallback(new EmergencyContactChooseDialog.Callback() {
                @Override
                public void onSelected(ContactRelationshipConfig.RelationItem value, int pos) {
                    curUrgentRelationshipPosition = pos;
                    contactData.getGetData().setRelativeContactPersonId(value.getId());
                    urgentRelationshipText.setText(value.getLabel());
                }
            }).setData(contactData.getSelectData().getRelativeList(), curUrgentRelationshipPosition).show();
        } else if (requestCodeType == RequestCodeType.CONTACT_OTHER_CONTACT) {
            new EmergencyContactChooseDialog(this)
                    .builder().setCallback(new EmergencyContactChooseDialog.Callback() {
                @Override
                public void onSelected(ContactRelationshipConfig.RelationItem value, int pos) {
                    curOtherRelationshipPosition = pos;
                    contactData.getGetData().setOtherRelativeContactPersonId(value.getId());
                    otherRelationshipText.setText(value.getLabel());
                }
            }).setData(contactData.getSelectData().getRelativeList(), curOtherRelationshipPosition).show();
        }
    }

    @Override
    public void setupDatas() {
        getData();
    }

    private void getData() {
        HttpSender.get(new CommonResponsePendingCallback<EmergencyContactDataResponseModel>(display()) {

            @Override
            public CommonRequestParams createRequestParams() {
                // 获取紧急联系人
                String getContactRelationUrl = HttpConfig.getRealUrl(StringConstant.HTTP_AUTHENTICATE_GET_CONTACT_INFO);
                return new CommonRequestParams(getContactRelationUrl);
            }

            @Override
            public void handleSuccess(EmergencyContactDataResponseModel resultData, int resultCode, String resultMessage) {
                if (resultData == null) {
                    showToast(resultMessage);
                    return;
                }
                contactData = resultData;
                refreshViews();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }

            @Override
            public void onCancelled(CancelledException cex) {
                super.onCancelled(cex);
            }
        });
    }

    private void refreshViews() {
        ContactInfoData contactInfo = contactData.getGetData();
        if (contactInfo != null) {
            urgentRelationshipText.setText(contactInfo.getRelativeContactPersonVal());
            urgentChooseVh.refresh(contactInfo.getName(), contactInfo.getPhone());

            otherRelationshipText.setText(contactInfo.getOtherRelativeContactPersonVal());
            otherChooseVh.refresh(contactInfo.getOtherName(), contactInfo.getOtherPhone());

            facebookEdit.setText(contactInfo.getFacebookAccount());
            whatsAppEdit.setText(contactInfo.getWhatsAppAccount());
            skypeEdit.setText(contactInfo.getSkypeAccount());
        }

        ContactRelationshipConfig relationConfig = contactData.getSelectData();
        if ((relationConfig != null && relationConfig.getRelativeList() != null)) {
            List<ContactRelationshipConfig.RelationItem> relativeList = relationConfig.getRelativeList();

            int urgentRelationshipId = contactData.getGetData().getRelativeContactPersonId();
            for (int i = 0; i < relativeList.size(); i++) {
                if (urgentRelationshipId == relativeList.get(i).getId()) {
                    curUrgentRelationshipPosition = i;
                    break;
                }
            }

            int otherRelactionshipId = contactData.getGetData().getOtherRelativeContactPersonId();
            for (int i = 0; i < relativeList.size(); i++) {
                if (otherRelactionshipId == relativeList.get(i).getId()) {
                    curOtherRelationshipPosition = i;
                    break;
                }
            }
        }
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (urgentChooseVh.onActivityResult(requestCode, resultCode, data)) {
            return;
        }
        if (otherChooseVh.onActivityResult(requestCode, resultCode, data)) {
            return;
        }
        super.onActivityResult(requestCode, resultCode, data);
    }


    @Override
    protected void onDestroy() {
        EventBus.getDefault().unregister(this);
        super.onDestroy();
    }

    public void onEventMainThread(UserLoginedEvent event) {
        getData();
    }

    @OnClick({R.id.authenticate_contact_back_icon, R.id.authenticate_contact_urgent_relationship_root, R.id.authenticate_contact_other_relationship_root, R.id.authenticate_contact_submit})
    public void onViewClicked(View view) {
        switch (view.getId()) {
            case R.id.authenticate_contact_back_icon:
                finish();
                break;
            case R.id.authenticate_contact_urgent_relationship_root:
                selectRelationship(RequestCodeType.CONTACT_URGENT_CONTACT);
                break;
            case R.id.authenticate_contact_other_relationship_root:
                selectRelationship(RequestCodeType.CONTACT_OTHER_CONTACT);
                break;
            case R.id.authenticate_contact_submit:
                //submit
                if (contactData == null || contactData.getSelectData() == null || contactData.getSelectData().getRelativeList() == null) {
                    showToast(R.string.emerygency_get_data_exception);
                    return;
                }

                if (contactData.getGetData().getRelativeContactPersonId() == 0) {
                    showToast(R.string.emerygency_please_select_contact_one_relation);
                    return;
                }

                String selectedUrgentName = urgentChooseVh.getCurrentName();
                String selectedUrgentPhone = urgentChooseVh.getCurrentPhone();
                if (StringUtil.isBlank(selectedUrgentName) || StringUtil.isBlank(selectedUrgentPhone)) {
                    showToast(R.string.emerygency_please_select_contact_one_phone);
                    return;
                }

                if (contactData.getGetData().getOtherRelativeContactPersonId() == 0) {
                    showToast(R.string.emerygency_please_select_contact_two_relation);
                    return;
                }

                String selectedOtherName = otherChooseVh.getCurrentName();
                String selectedOtherPhone = otherChooseVh.getCurrentPhone();
                if (StringUtil.isBlank(selectedOtherName) || StringUtil.isBlank(selectedOtherPhone)) {
                    showToast(R.string.emerygency_please_select_contact_two_phone);
                    return;
                }

                HttpSender.post(new CommonResponsePendingCallback<EmergencyContactResponseModel>(display()) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        // 保存紧急联系人
                        String saveContactInfoUrl = HttpConfig.getRealUrl(StringConstant.HTTP_AUTHENTICATE_SAVE_CONTACT_INFO);
                        CommonRequestParams requestParams = new CommonRequestParams(saveContactInfoUrl);

                        //关系
                        requestParams.addBodyParameter("relativeContactPerson", contactData.getGetData().getRelativeContactPersonId());
                        //姓名
                        requestParams.addBodyParameter("name", selectedUrgentName.trim());
                        //手机号
                        requestParams.addBodyParameter("phone", selectedUrgentPhone.trim());
                        // 联系人关系备用,值为：5、6、7、8
                        requestParams.addBodyParameter("otherRelativeContactPerson", contactData.getGetData().getOtherRelativeContactPersonId());
                        //姓名备用
                        requestParams.addBodyParameter("otherName", selectedOtherName.trim());
                        // 手机号备用
                        requestParams.addBodyParameter("otherPhone", selectedOtherPhone.trim());

                        requestParams.addBodyParameter("facebookAccount", facebookEdit.getText().toString().trim());
                        requestParams.addBodyParameter("whatsAppAccount", whatsAppEdit.getText().toString().trim());
                        requestParams.addBodyParameter("skypeAccount", skypeEdit.getText().toString().trim());
                        return requestParams;
                    }

                    @Override
                    public void handleUi(boolean isStart) {
                        super.handleUi(isStart);
                        if (isStart) {
                            MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_AUTH_CONTACT_SUCCESS).build();
                        }
                    }

                    @Override
                    public void handleSuccess(EmergencyContactResponseModel resultData, int resultCode, String resultMessage) {
                        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_AUTH_CONTACT_SAVE_SUCCESS).build();
                        if (resultData != null && !StringUtil.isBlank(resultData.getJump())) {
                            JumpOperationHandler.convert(resultData.getJump()).createRequest().setDisplay(EmergencyContactActivity.this).jump();
                            return;
                        }
                        showToast(resultMessage);
                    }

                    @Override
                    public void handleFailed(int resultCode, String resultMessage) {
                        showToast(resultMessage);
                    }
                });
                break;
            default:
                break;
        }
    }
}
