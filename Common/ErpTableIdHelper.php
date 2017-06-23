<?php

namespace Mll\Common;

/**
 * ErpTableIdHelper
 *
 * @package Mll\Common
 * @author wang zhou <zhouwang@mll.com>
 * @since 1.0
 */
class ErpTableIdHelper {
	private static $PK_INCREASE_STEP = 1; // 自增的步长
	private static $NEXT_ID_RULE_NAME = "ERP_BUV1_NextKeyID";
	private static $last_insert_id = - 1;
	
	/**
	 * ERP 需要用到主键存储过程进行设置的表的映射关系
	 *
	 * @var array
	 */
	public static $ERP_table_list = array (
			'BUDGET' => array (
					'Budget' => 'BUDGET_ID' 
			),
			'BUDGET_ITEM_TYPE' => array (
					'BudgetItemType' => 'BUDGET_ITEM_TYPE_ID' 
			),
			'BUDGET_REVIEW_RESULT_TYPE' => array (
					'BudgetReviewResultType' => 'BUDGET_REVIEW_RESULT_TYPE_ID' 
			),
			'BUDGET_SCENARIO' => array (
					'BudgetScenario' => 'BUDGET_SCENARIO_ID' 
			),
			'BUDGET_TYPE' => array (
					'BudgetType' => 'BUDGET_TYPE_ID' 
			),
			'FIN_ACCOUNT' => array (
					'FinAccount' => 'FIN_ACCOUNT_ID' 
			),
			'FIN_ACCOUNT_AUTH' => array (
					'FinAccountAuth' => 'FIN_ACCOUNT_AUTH_ID' 
			),
			'FIN_ACCOUNT_TRANS' => array (
					'FinAccountTrans' => 'FIN_ACCOUNT_TRANS_ID' 
			),
			'FIN_ACCOUNT_TRANS_TYPE' => array (
					'FinAccountTransType' => 'FIN_ACCOUNT_TRANS_TYPE_ID' 
			),
			'FIN_ACCOUNT_TYPE' => array (
					'FinAccountType' => 'FIN_ACCOUNT_TYPE_ID' 
			),
			'FIXED_ASSET' => array (
					'FixedAsset' => 'FIXED_ASSET_ID' 
			),
			'FIXED_ASSET_IDENT_TYPE' => array (
					'FixedAssetIdentType' => 'FIXED_ASSET_IDENT_TYPE_ID' 
			),
			'FIXED_ASSET_PRODUCT_TYPE' => array (
					'FixedAssetProductType' => 'FIXED_ASSET_PRODUCT_TYPE_ID' 
			),
			'FIXED_ASSET_STD_COST_TYPE' => array (
					'FixedAssetStdCostType' => 'FIXED_ASSET_STD_COST_TYPE_ID' 
			),
			'FIXED_ASSET_TYPE' => array (
					'FixedAssetType' => 'FIXED_ASSET_TYPE_ID' 
			),
			'ACCOMMODATION_CLASS' => array (
					'AccommodationClass' => 'ACCOMMODATION_CLASS_ID' 
			),
			'ACCOMMODATION_SPOT' => array (
					'AccommodationSpot' => 'ACCOMMODATION_SPOT_ID' 
			),
			'ACCOMMODATION_MAP' => array (
					'AccommodationMap' => 'ACCOMMODATION_MAP_ID' 
			),
			'ACCOMMODATION_MAP_TYPE' => array (
					'AccommodationMapType' => 'ACCOMMODATION_MAP_TYPE_ID' 
			),
			'INVOICE' => array (
					'Invoice' => 'INVOICE_ID' 
			),
			'INVOICE_ITEM_ASSOC_TYPE' => array (
					'InvoiceItemAssocType' => 'INVOICE_ITEM_ASSOC_TYPE_ID' 
			),
			'INVOICE_ITEM_TYPE' => array (
					'InvoiceItemType' => 'INVOICE_ITEM_TYPE_ID' 
			),
			'INVOICE_TERM' => array (
					'InvoiceTerm' => 'INVOICE_TERM_ID' 
			),
			'INVOICE_TYPE' => array (
					'InvoiceType' => 'INVOICE_TYPE_ID' 
			),
			'ACCTG_TRANS' => array (
					'AcctgTrans' => 'ACCTG_TRANS_ID' 
			),
			'ACCTG_TRANS_ENTRY_TYPE' => array (
					'AcctgTransEntryType' => 'ACCTG_TRANS_ENTRY_TYPE_ID' 
			),
			'ACCTG_TRANS_TYPE' => array (
					'AcctgTransType' => 'ACCTG_TRANS_TYPE_ID' 
			),
			'GL_ACCOUNT' => array (
					'GlAccount' => 'GL_ACCOUNT_ID' 
			),
			'GL_ACCOUNT_CLASS' => array (
					'GlAccountClass' => 'GL_ACCOUNT_CLASS_ID' 
			),
			'GL_ACCOUNT_GROUP' => array (
					'GlAccountGroup' => 'GL_ACCOUNT_GROUP_ID' 
			),
			'GL_ACCOUNT_GROUP_TYPE' => array (
					'GlAccountGroupType' => 'GL_ACCOUNT_GROUP_TYPE_ID' 
			),
			'GL_ACCOUNT_TYPE' => array (
					'GlAccountType' => 'GL_ACCOUNT_TYPE_ID' 
			),
			'GL_FISCAL_TYPE' => array (
					'GlFiscalType' => 'GL_FISCAL_TYPE_ID' 
			),
			'GL_JOURNAL' => array (
					'GlJournal' => 'GL_JOURNAL_ID' 
			),
			'GL_RECONCILIATION' => array (
					'GlReconciliation' => 'GL_RECONCILIATION_ID' 
			),
			'GL_RESOURCE_TYPE' => array (
					'GlResourceType' => 'GL_RESOURCE_TYPE_ID' 
			),
			'GL_XBRL_CLASS' => array (
					'GlXbrlClass' => 'GL_XBRL_CLASS_ID' 
			),
			'PARTY_ACCTG_PREFERENCE' => array (
					'PartyAcctgPreference' => 'PARTY_ID' 
			),
			'PRODUCT_AVERAGE_COST_TYPE' => array (
					'ProductAverageCostType' => 'PRODUCT_AVERAGE_COST_TYPE_ID' 
			),
			'SETTLEMENT_TERM' => array (
					'SettlementTerm' => 'SETTLEMENT_TERM_ID' 
			),
			'BILLING_ACCOUNT' => array (
					'BillingAccount' => 'BILLING_ACCOUNT_ID' 
			),
			'BILLING_ACCOUNT_TERM' => array (
					'BillingAccountTerm' => 'BILLING_ACCOUNT_TERM_ID' 
			),
			'CREDIT_CARD' => array (
					'CreditCard' => 'PAYMENT_METHOD_ID' 
			),
			'DEDUCTION' => array (
					'Deduction' => 'DEDUCTION_ID' 
			),
			'DEDUCTION_TYPE' => array (
					'DeductionType' => 'DEDUCTION_TYPE_ID' 
			),
			'EFT_ACCOUNT' => array (
					'EftAccount' => 'PAYMENT_METHOD_ID' 
			),
			'GIFT_CARD' => array (
					'GiftCard' => 'PAYMENT_METHOD_ID' 
			),
			'GIFT_CARD_FULFILLMENT' => array (
					'GiftCardFulfillment' => 'FULFILLMENT_ID' 
			),
			'PAYMENT' => array (
					'Payment' => 'PAYMENT_ID' 
			),
			'PAYMENT_APPLICATION' => array (
					'PaymentApplication' => 'PAYMENT_APPLICATION_ID' 
			),
			'PAYMENT_METHOD' => array (
					'PaymentMethod' => 'PAYMENT_METHOD_ID' 
			),
			'PAYMENT_METHOD_TYPE' => array (
					'PaymentMethodType' => 'PAYMENT_METHOD_TYPE_ID' 
			),
			'PAYMENT_TYPE' => array (
					'PaymentType' => 'PAYMENT_TYPE_ID' 
			),
			'PAYMENT_GATEWAY_CONFIG_TYPE' => array (
					'PaymentGatewayConfigType' => 'PAYMENT_GATEWAY_CONFIG_TYPE_ID' 
			),
			'PAYMENT_GATEWAY_CONFIG' => array (
					'PaymentGatewayConfig' => 'PAYMENT_GATEWAY_CONFIG_ID' 
			),
			'PAYMENT_GATEWAY_SAGE_PAY' => array (
					'PaymentGatewaySagePay' => 'PAYMENT_GATEWAY_CONFIG_ID' 
			),
			'PAYMENT_GATEWAY_AUTHORIZE_NET' => array (
					'PaymentGatewayAuthorizeNet' => 'PAYMENT_GATEWAY_CONFIG_ID' 
			),
			'PAYMENT_GATEWAY_CYBER_SOURCE' => array (
					'PaymentGatewayCyberSource' => 'PAYMENT_GATEWAY_CONFIG_ID' 
			),
			'PAYMENT_GATEWAY_PAYFLOW_PRO' => array (
					'PaymentGatewayPayflowPro' => 'PAYMENT_GATEWAY_CONFIG_ID' 
			),
			'PAYMENT_GATEWAY_PAY_PAL' => array (
					'PaymentGatewayPayPal' => 'PAYMENT_GATEWAY_CONFIG_ID' 
			),
			'PAYMENT_GATEWAY_CLEAR_COMMERCE' => array (
					'PaymentGatewayClearCommerce' => 'PAYMENT_GATEWAY_CONFIG_ID' 
			),
			'PAYMENT_GATEWAY_WORLD_PAY' => array (
					'PaymentGatewayWorldPay' => 'PAYMENT_GATEWAY_CONFIG_ID' 
			),
			'PAYMENT_GATEWAY_ORBITAL' => array (
					'PaymentGatewayOrbital' => 'PAYMENT_GATEWAY_CONFIG_ID' 
			),
			'PAYMENT_GATEWAY_RESP_MSG' => array (
					'PaymentGatewayRespMsg' => 'PAYMENT_GATEWAY_RESP_MSG_ID' 
			),
			'PAYMENT_GATEWAY_RESPONSE' => array (
					'PaymentGatewayResponse' => 'PAYMENT_GATEWAY_RESPONSE_ID' 
			),
			'PAYMENT_GROUP' => array (
					'PaymentGroup' => 'PAYMENT_GROUP_ID' 
			),
			'PAYMENT_GROUP_TYPE' => array (
					'PaymentGroupType' => 'PAYMENT_GROUP_TYPE_ID' 
			),
			'PAY_PAL_PAYMENT_METHOD' => array (
					'PayPalPaymentMethod' => 'PAYMENT_METHOD_ID' 
			),
			'VALUE_LINK_KEY' => array (
					'ValueLinkKey' => 'MERCHANT_ID' 
			),
			'TAX_AUTHORITY_ASSOC_TYPE' => array (
					'TaxAuthorityAssocType' => 'TAX_AUTHORITY_ASSOC_TYPE_ID' 
			),
			'TAX_AUTHORITY_RATE_PRODUCT' => array (
					'TaxAuthorityRateProduct' => 'TAX_AUTHORITY_RATE_SEQ_ID' 
			),
			'TAX_AUTHORITY_RATE_TYPE' => array (
					'TaxAuthorityRateType' => 'TAX_AUTHORITY_RATE_TYPE_ID' 
			),
			'RATE_TYPE' => array (
					'RateType' => 'RATE_TYPE_ID' 
			),
			'GL_ACCOUNT_CATEGORY' => array (
					'GlAccountCategory' => 'GL_ACCOUNT_CATEGORY_ID' 
			),
			'GL_ACCOUNT_CATEGORY_TYPE' => array (
					'GlAccountCategoryType' => 'GL_ACCOUNT_CATEGORY_TYPE_ID' 
			),
			'OLD_VALUE_LINK_FULFILLMENT' => array (
					'OldValueLinkFulfillment' => 'FULFILLMENT_ID' 
			),
			'CONTENT' => array (
					'Content' => 'CONTENT_ID' 
			),
			'CONTENT_APPROVAL' => array (
					'ContentApproval' => 'CONTENT_APPROVAL_ID' 
			),
			'CONTENT_ASSOC_PREDICATE' => array (
					'ContentAssocPredicate' => 'CONTENT_ASSOC_PREDICATE_ID' 
			),
			'CONTENT_ASSOC_TYPE' => array (
					'ContentAssocType' => 'CONTENT_ASSOC_TYPE_ID' 
			),
			'CONTENT_OPERATION' => array (
					'ContentOperation' => 'CONTENT_OPERATION_ID' 
			),
			'CONTENT_PURPOSE_TYPE' => array (
					'ContentPurposeType' => 'CONTENT_PURPOSE_TYPE_ID' 
			),
			'CONTENT_TYPE' => array (
					'ContentType' => 'CONTENT_TYPE_ID' 
			),
			'AUDIO_DATA_RESOURCE' => array (
					'AudioDataResource' => 'DATA_RESOURCE_ID' 
			),
			'CHARACTER_SET' => array (
					'CharacterSet' => 'CHARACTER_SET_ID' 
			),
			'DATA_CATEGORY' => array (
					'DataCategory' => 'DATA_CATEGORY_ID' 
			),
			'DATA_RESOURCE' => array (
					'DataResource' => 'DATA_RESOURCE_ID' 
			),
			'DATA_RESOURCE_TYPE' => array (
					'DataResourceType' => 'DATA_RESOURCE_TYPE_ID' 
			),
			'DATA_TEMPLATE_TYPE' => array (
					'DataTemplateType' => 'DATA_TEMPLATE_TYPE_ID' 
			),
			'ELECTRONIC_TEXT' => array (
					'ElectronicText' => 'DATA_RESOURCE_ID' 
			),
			'FILE_EXTENSION' => array (
					'FileExtension' => 'FILE_EXTENSION_ID' 
			),
			'IMAGE_DATA_RESOURCE' => array (
					'ImageDataResource' => 'DATA_RESOURCE_ID' 
			),
			'META_DATA_PREDICATE' => array (
					'MetaDataPredicate' => 'META_DATA_PREDICATE_ID' 
			),
			'MIME_TYPE' => array (
					'MimeType' => 'MIME_TYPE_ID' 
			),
			'MIME_TYPE_HTML_TEMPLATE' => array (
					'MimeTypeHtmlTemplate' => 'MIME_TYPE_ID' 
			),
			'OTHER_DATA_RESOURCE' => array (
					'OtherDataResource' => 'DATA_RESOURCE_ID' 
			),
			'VIDEO_DATA_RESOURCE' => array (
					'VideoDataResource' => 'DATA_RESOURCE_ID' 
			),
			'DOCUMENT' => array (
					'Document' => 'DOCUMENT_ID' 
			),
			'DOCUMENT_TYPE' => array (
					'DocumentType' => 'DOCUMENT_TYPE_ID' 
			),
			'WEB_PREFERENCE_TYPE' => array (
					'WebPreferenceType' => 'WEB_PREFERENCE_TYPE_ID' 
			),
			'SURVEY' => array (
					'Survey' => 'SURVEY_ID' 
			),
			'SURVEY_APPL_TYPE' => array (
					'SurveyApplType' => 'SURVEY_APPL_TYPE_ID' 
			),
			'SURVEY_QUESTION' => array (
					'SurveyQuestion' => 'SURVEY_QUESTION_ID' 
			),
			'SURVEY_QUESTION_CATEGORY' => array (
					'SurveyQuestionCategory' => 'SURVEY_QUESTION_CATEGORY_ID' 
			),
			'SURVEY_QUESTION_TYPE' => array (
					'SurveyQuestionType' => 'SURVEY_QUESTION_TYPE_ID' 
			),
			'SURVEY_RESPONSE' => array (
					'SurveyResponse' => 'SURVEY_RESPONSE_ID' 
			),
			'WEB_SITE_CONTENT_TYPE' => array (
					'WebSiteContentType' => 'WEB_SITE_CONTENT_TYPE_ID' 
			),
			'WEB_SITE_PUBLISH_POINT' => array (
					'WebSitePublishPoint' => 'CONTENT_ID' 
			),
			'PARTY_QUAL_TYPE' => array (
					'PartyQualType' => 'PARTY_QUAL_TYPE_ID' 
			),
			'PARTY_RESUME' => array (
					'PartyResume' => 'RESUME_ID' 
			),
			'PERF_RATING_TYPE' => array (
					'PerfRatingType' => 'PERF_RATING_TYPE_ID' 
			),
			'PERF_REVIEW_ITEM_TYPE' => array (
					'PerfReviewItemType' => 'PERF_REVIEW_ITEM_TYPE_ID' 
			),
			'RESPONSIBILITY_TYPE' => array (
					'ResponsibilityType' => 'RESPONSIBILITY_TYPE_ID' 
			),
			'SKILL_TYPE' => array (
					'SkillType' => 'SKILL_TYPE_ID' 
			),
			'TRAINING_CLASS_TYPE' => array (
					'TrainingClassType' => 'TRAINING_CLASS_TYPE_ID' 
			),
			'BENEFIT_TYPE' => array (
					'BenefitType' => 'BENEFIT_TYPE_ID' 
			),
			'EMPLOYMENT_APP' => array (
					'EmploymentApp' => 'APPLICATION_ID' 
			),
			'EMPLOYMENT_APP_SOURCE_TYPE' => array (
					'EmploymentAppSourceType' => 'EMPLOYMENT_APP_SOURCE_TYPE_ID' 
			),
			'EMPL_LEAVE_TYPE' => array (
					'EmplLeaveType' => 'LEAVE_TYPE_ID' 
			),
			'PAY_GRADE' => array (
					'PayGrade' => 'PAY_GRADE_ID' 
			),
			'TERMINATION_REASON' => array (
					'TerminationReason' => 'TERMINATION_REASON_ID' 
			),
			'TERMINATION_TYPE' => array (
					'TerminationType' => 'TERMINATION_TYPE_ID' 
			),
			'UNEMPLOYMENT_CLAIM' => array (
					'UnemploymentClaim' => 'UNEMPLOYMENT_CLAIM_ID' 
			),
			'EMPL_POSITION' => array (
					'EmplPosition' => 'EMPL_POSITION_ID' 
			),
			'EMPL_POSITION_CLASS_TYPE' => array (
					'EmplPositionClassType' => 'EMPL_POSITION_CLASS_TYPE_ID' 
			),
			'EMPL_POSITION_TYPE' => array (
					'EmplPositionType' => 'EMPL_POSITION_TYPE_ID' 
			),
			'JOB_REQUISITION' => array (
					'JobRequisition' => 'JOB_REQUISITION_ID' 
			),
			'JOB_INTERVIEW' => array (
					'JobInterview' => 'JOB_INTERVIEW_ID' 
			),
			'JOB_INTERVIEW_TYPE' => array (
					'JobInterviewType' => 'JOB_INTERVIEW_TYPE_ID' 
			),
			'TRAINING_REQUEST' => array (
					'TrainingRequest' => 'TRAINING_REQUEST_ID' 
			),
			'EMPL_LEAVE_REASON_TYPE' => array (
					'EmplLeaveReasonType' => 'EMPL_LEAVE_REASON_TYPE_ID' 
			),
			'PRODUCT_MANUFACTURING_RULE' => array (
					'ProductManufacturingRule' => 'RULE_ID' 
			),
			'TECH_DATA_CALENDAR' => array (
					'TechDataCalendar' => 'CALENDAR_ID' 
			),
			'TECH_DATA_CALENDAR_WEEK' => array (
					'TechDataCalendarWeek' => 'CALENDAR_WEEK_ID' 
			),
			'MRP_EVENT_TYPE' => array (
					'MrpEventType' => 'MRP_EVENT_TYPE_ID' 
			),
			'MARKETING_CAMPAIGN' => array (
					'MarketingCampaign' => 'MARKETING_CAMPAIGN_ID' 
			),
			'CONTACT_LIST' => array (
					'ContactList' => 'CONTACT_LIST_ID' 
			),
			'CONTACT_LIST_TYPE' => array (
					'ContactListType' => 'CONTACT_LIST_TYPE_ID' 
			),
			'SEGMENT_GROUP' => array (
					'SegmentGroup' => 'SEGMENT_GROUP_ID' 
			),
			'SEGMENT_GROUP_TYPE' => array (
					'SegmentGroupType' => 'SEGMENT_GROUP_TYPE_ID' 
			),
			'TRACKING_CODE' => array (
					'TrackingCode' => 'TRACKING_CODE_ID' 
			),
			'TRACKING_CODE_TYPE' => array (
					'TrackingCodeType' => 'TRACKING_CODE_TYPE_ID' 
			),
			'ORDER_ADJUSTMENT' => array (
					'OrderAdjustment' => 'ORDER_ADJUSTMENT_ID' 
			),
			'ORDER_ADJUSTMENT_TYPE' => array (
					'OrderAdjustmentType' => 'ORDER_ADJUSTMENT_TYPE_ID' 
			),
			'ORDER_BLACKLIST_TYPE' => array (
					'OrderBlacklistType' => 'ORDER_BLACKLIST_TYPE_ID' 
			),
			'ORDER_CONTENT_TYPE' => array (
					'OrderContentType' => 'ORDER_CONTENT_TYPE_ID' 
			),
			'ORDER_HEADER' => array (
					'OrderHeader' => 'ORDER_ID' 
			),
			'ORDER_ITEM' => array (
					'OrderItem' => 'ORDER_ITEM_SEQ_ID' 
			),
			'ORDER_ITEM_ASSOC_TYPE' => array (
					'OrderItemAssocType' => 'ORDER_ITEM_ASSOC_TYPE_ID' 
			),
			'ORDER_ITEM_CHANGE' => array (
					'OrderItemChange' => 'ORDER_ITEM_CHANGE_ID' 
			),
			'ORDER_ITEM_PRICE_INFO' => array (
					'OrderItemPriceInfo' => 'ORDER_ITEM_PRICE_INFO_ID' 
			),
			'ORDER_ITEM_TYPE' => array (
					'OrderItemType' => 'ORDER_ITEM_TYPE_ID' 
			),
			'ORDER_NOTIFICATION' => array (
					'OrderNotification' => 'ORDER_NOTIFICATION_ID' 
			),
			'ORDER_PAYMENT_PREFERENCE' => array (
					'OrderPaymentPreference' => 'ORDER_PAYMENT_PREFERENCE_ID' 
			),
			'ORDER_STATUS' => array (
					'OrderStatus' => 'ORDER_STATUS_ID' 
			),
			'ORDER_TYPE' => array (
					'OrderType' => 'ORDER_TYPE_ID' 
			),
			'QUOTE' => array (
					'Quote' => 'QUOTE_ID' 
			),
			'QUOTE_TYPE' => array (
					'QuoteType' => 'QUOTE_TYPE_ID' 
			),
			'QUOTE_ADJUSTMENT' => array (
					'QuoteAdjustment' => 'QUOTE_ADJUSTMENT_ID' 
			),
			'CUST_REQUEST' => array (
					'CustRequest' => 'CUST_REQUEST_ID' 
			),
			'CUST_REQUEST_CATEGORY' => array (
					'CustRequestCategory' => 'CUST_REQUEST_CATEGORY_ID' 
			),
			'CUST_REQUEST_RESOLUTION' => array (
					'CustRequestResolution' => 'CUST_REQUEST_RESOLUTION_ID' 
			),
			'CUST_REQUEST_STATUS' => array (
					'CustRequestStatus' => 'CUST_REQUEST_STATUS_ID' 
			),
			'CUST_REQUEST_TYPE' => array (
					'CustRequestType' => 'CUST_REQUEST_TYPE_ID' 
			),
			'REQUIREMENT' => array (
					'Requirement' => 'REQUIREMENT_ID' 
			),
			'REQUIREMENT_TYPE' => array (
					'RequirementType' => 'REQUIREMENT_TYPE_ID' 
			),
			'WORK_REQ_FULF_TYPE' => array (
					'WorkReqFulfType' => 'WORK_REQ_FULF_TYPE_ID' 
			),
			'RETURN_ADJUSTMENT' => array (
					'ReturnAdjustment' => 'RETURN_ADJUSTMENT_ID' 
			),
			'RETURN_ADJUSTMENT_TYPE' => array (
					'ReturnAdjustmentType' => 'RETURN_ADJUSTMENT_TYPE_ID' 
			),
			'RETURN_HEADER' => array (
					'ReturnHeader' => 'RETURN_ID' 
			),
			'RETURN_HEADER_TYPE' => array (
					'ReturnHeaderType' => 'RETURN_HEADER_TYPE_ID' 
			),
			'RETURN_ITEM_RESPONSE' => array (
					'ReturnItemResponse' => 'RETURN_ITEM_RESPONSE_ID' 
			),
			'RETURN_ITEM_TYPE' => array (
					'ReturnItemType' => 'RETURN_ITEM_TYPE_ID' 
			),
			'RETURN_REASON' => array (
					'ReturnReason' => 'RETURN_REASON_ID' 
			),
			'RETURN_STATUS' => array (
					'ReturnStatus' => 'RETURN_STATUS_ID' 
			),
			'RETURN_TYPE' => array (
					'ReturnType' => 'RETURN_TYPE_ID' 
			),
			'SHOPPING_LIST' => array (
					'ShoppingList' => 'SHOPPING_LIST_ID' 
			),
			'SHOPPING_LIST_TYPE' => array (
					'ShoppingListType' => 'SHOPPING_LIST_TYPE_ID' 
			),
			'SALES_OPPORTUNITY' => array (
					'SalesOpportunity' => 'SALES_OPPORTUNITY_ID' 
			),
			'SALES_OPPORTUNITY_HISTORY' => array (
					'SalesOpportunityHistory' => 'SALES_OPPORTUNITY_HISTORY_ID' 
			),
			'SALES_OPPORTUNITY_STAGE' => array (
					'SalesOpportunityStage' => 'OPPORTUNITY_STAGE_ID' 
			),
			'SALES_FORECAST' => array (
					'SalesForecast' => 'SALES_FORECAST_ID' 
			),
			'SALES_FORECAST_HISTORY' => array (
					'SalesForecastHistory' => 'SALES_FORECAST_HISTORY_ID' 
			),
			'ADDENDUM' => array (
					'Addendum' => 'ADDENDUM_ID' 
			),
			'AGREEMENT' => array (
					'Agreement' => 'AGREEMENT_ID' 
			),
			'AGREEMENT_ITEM_TYPE' => array (
					'AgreementItemType' => 'AGREEMENT_ITEM_TYPE_ID' 
			),
			'AGREEMENT_TERM' => array (
					'AgreementTerm' => 'AGREEMENT_TERM_ID' 
			),
			'AGREEMENT_TYPE' => array (
					'AgreementType' => 'AGREEMENT_TYPE_ID' 
			),
			'TERM_TYPE' => array (
					'TermType' => 'TERM_TYPE_ID' 
			),
			'COMM_CONTENT_ASSOC_TYPE' => array (
					'CommContentAssocType' => 'COMM_CONTENT_ASSOC_TYPE_ID' 
			),
			'COMMUNICATION_EVENT' => array (
					'CommunicationEvent' => 'COMMUNICATION_EVENT_ID' 
			),
			'COMMUNICATION_EVENT_PRP_TYP' => array (
					'CommunicationEventPrpTyp' => 'COMMUNICATION_EVENT_PRP_TYP_ID' 
			),
			'COMMUNICATION_EVENT_TYPE' => array (
					'CommunicationEventType' => 'COMMUNICATION_EVENT_TYPE_ID' 
			),
			'CONTACT_MECH' => array (
					'ContactMech' => 'CONTACT_MECH_ID' 
			),
			'CONTACT_MECH_PURPOSE_TYPE' => array (
					'ContactMechPurposeType' => 'CONTACT_MECH_PURPOSE_TYPE_ID' 
			),
			'CONTACT_MECH_TYPE' => array (
					'ContactMechType' => 'CONTACT_MECH_TYPE_ID' 
			),
			'EMAIL_ADDRESS_VERIFICATION' => array (
					'EmailAddressVerification' => 'EMAIL_ADDRESS' 
			),
			'POSTAL_ADDRESS' => array (
					'PostalAddress' => 'CONTACT_MECH_ID' 
			),
			'TELECOM_NUMBER' => array (
					'TelecomNumber' => 'CONTACT_MECH_ID' 
			),
			'NEED_TYPE' => array (
					'NeedType' => 'NEED_TYPE_ID' 
			),
			'AFFILIATE' => array (
					'Affiliate' => 'PARTY_ID' 
			),
			'PARTY_IDENTIFICATION_TYPE' => array (
					'PartyIdentificationType' => 'PARTY_IDENTIFICATION_TYPE_ID' 
			),
			'PARTY_CLASSIFICATION_GROUP' => array (
					'PartyClassificationGroup' => 'PARTY_CLASSIFICATION_GROUP_ID' 
			),
			'PARTY_CLASSIFICATION_TYPE' => array (
					'PartyClassificationType' => 'PARTY_CLASSIFICATION_TYPE_ID' 
			),
			'PARTY_CONTENT_TYPE' => array (
					'PartyContentType' => 'PARTY_CONTENT_TYPE_ID' 
			),
			'PARTY_GROUP' => array (
					'PartyGroup' => 'PARTY_ID' 
			),
			'PARTY_ICS_AVS_OVERRIDE' => array (
					'PartyIcsAvsOverride' => 'PARTY_ID' 
			),
			'PARTY_INVITATION' => array (
					'PartyInvitation' => 'PARTY_INVITATION_ID' 
			),
			'PARTY_RELATIONSHIP_TYPE' => array (
					'PartyRelationshipType' => 'PARTY_RELATIONSHIP_TYPE_ID' 
			),
			'PARTY_TYPE' => array (
					'PartyType' => 'PARTY_TYPE_ID' 
			),
			'PERSON' => array (
					'Person' => 'PARTY_ID' 
			),
			'PRIORITY_TYPE' => array (
					'PriorityType' => 'PRIORITY_TYPE_ID' 
			),
			'ROLE_TYPE' => array (
					'RoleType' => 'ROLE_TYPE_ID' 
			),
			'VENDOR' => array (
					'Vendor' => 'PARTY_ID' 
			),
			'PROD_CATALOG' => array (
					'ProdCatalog' => 'PROD_CATALOG_ID' 
			),
			'PROD_CATALOG_CATEGORY_TYPE' => array (
					'ProdCatalogCategoryType' => 'PROD_CATALOG_CATEGORY_TYPE_ID' 
			),
			'PRODUCT_CATEGORY' => array (
					'ProductCategory' => 'PRODUCT_CATEGORY_ID' 
			),
			'PRODUCT_CATEGORY_CONTENT_TYPE' => array (
					'ProductCategoryContentType' => 'PROD_CAT_CONTENT_TYPE_ID' 
			),
			'PRODUCT_CATEGORY_TYPE' => array (
					'ProductCategoryType' => 'PRODUCT_CATEGORY_TYPE_ID' 
			),
			'PRODUCT_CONFIG_ITEM' => array (
					'ProductConfigItem' => 'CONFIG_ITEM_ID' 
			),
			'PROD_CONF_ITEM_CONTENT_TYPE' => array (
					'ProdConfItemContentType' => 'CONF_ITEM_CONTENT_TYPE_ID' 
			),
			'COST_COMPONENT' => array (
					'CostComponent' => 'COST_COMPONENT_ID' 
			),
			'COST_COMPONENT_TYPE' => array (
					'CostComponentType' => 'COST_COMPONENT_TYPE_ID' 
			),
			'COST_COMPONENT_CALC' => array (
					'CostComponentCalc' => 'COST_COMPONENT_CALC_ID' 
			),
			'CONTAINER' => array (
					'Container' => 'CONTAINER_ID' 
			),
			'CONTAINER_TYPE' => array (
					'ContainerType' => 'CONTAINER_TYPE_ID' 
			),
			'FACILITY' => array (
					'Facility' => 'FACILITY_ID' 
			),
			'FACILITY_GROUP' => array (
					'FacilityGroup' => 'FACILITY_GROUP_ID' 
			),
			'FACILITY_GROUP_TYPE' => array (
					'FacilityGroupType' => 'FACILITY_GROUP_TYPE_ID' 
			),
			'FACILITY_TYPE' => array (
					'FacilityType' => 'FACILITY_TYPE_ID' 
			),
			'PRODUCT_FEATURE' => array (
					'ProductFeature' => 'PRODUCT_FEATURE_ID' 
			),
			'PRODUCT_FEATURE_APPL_TYPE' => array (
					'ProductFeatureApplType' => 'PRODUCT_FEATURE_APPL_TYPE_ID' 
			),
			'PRODUCT_FEATURE_CATEGORY' => array (
					'ProductFeatureCategory' => 'PRODUCT_FEATURE_CATEGORY_ID' 
			),
			'PRODUCT_FEATURE_GROUP' => array (
					'ProductFeatureGroup' => 'PRODUCT_FEATURE_GROUP_ID' 
			),
			'PRODUCT_FEATURE_IACTN_TYPE' => array (
					'ProductFeatureIactnType' => 'PRODUCT_FEATURE_IACTN_TYPE_ID' 
			),
			'PRODUCT_FEATURE_TYPE' => array (
					'ProductFeatureType' => 'PRODUCT_FEATURE_TYPE_ID' 
			),
			'INVENTORY_ITEM' => array (
					'InventoryItem' => 'INVENTORY_ITEM_ID' 
			),
			'INVENTORY_ITEM_TYPE' => array (
					'InventoryItemType' => 'INVENTORY_ITEM_TYPE_ID' 
			),
			'INVENTORY_ITEM_LABEL_TYPE' => array (
					'InventoryItemLabelType' => 'INVENTORY_ITEM_LABEL_TYPE_ID' 
			),
			'INVENTORY_ITEM_LABEL' => array (
					'InventoryItemLabel' => 'INVENTORY_ITEM_LABEL_ID' 
			),
			'INVENTORY_TRANSFER' => array (
					'InventoryTransfer' => 'INVENTORY_TRANSFER_ID' 
			),
			'LOT' => array (
					'Lot' => 'LOT_ID' 
			),
			'PHYSICAL_INVENTORY' => array (
					'PhysicalInventory' => 'PHYSICAL_INVENTORY_ID' 
			),
			'VARIANCE_REASON' => array (
					'VarianceReason' => 'VARIANCE_REASON_ID' 
			),
			'PRODUCT_PRICE_ACTION_TYPE' => array (
					'ProductPriceActionType' => 'PRODUCT_PRICE_ACTION_TYPE_ID' 
			),
			'PRODUCT_PRICE_AUTO_NOTICE' => array (
					'ProductPriceAutoNotice' => 'PRODUCT_PRICE_NOTICE_ID' 
			),
			'PRODUCT_PRICE_CHANGE' => array (
					'ProductPriceChange' => 'PRODUCT_PRICE_CHANGE_ID' 
			),
			'PRODUCT_PRICE_PURPOSE' => array (
					'ProductPricePurpose' => 'PRODUCT_PRICE_PURPOSE_ID' 
			),
			'PRODUCT_PRICE_RULE' => array (
					'ProductPriceRule' => 'PRODUCT_PRICE_RULE_ID' 
			),
			'PRODUCT_PRICE_TYPE' => array (
					'ProductPriceType' => 'PRODUCT_PRICE_TYPE_ID' 
			),
			'QUANTITY_BREAK' => array (
					'QuantityBreak' => 'QUANTITY_BREAK_ID' 
			),
			'QUANTITY_BREAK_TYPE' => array (
					'QuantityBreakType' => 'QUANTITY_BREAK_TYPE_ID' 
			),
			'SALE_TYPE' => array (
					'SaleType' => 'SALE_TYPE_ID' 
			),
			'GOOD_IDENTIFICATION_TYPE' => array (
					'GoodIdentificationType' => 'GOOD_IDENTIFICATION_TYPE_ID' 
			),
			'PRODUCT' => array (
					'Product' => 'PRODUCT_ID' 
			),
			'ECS_BRAND' => array (
					'EcsBrand' => 'BRAND_ID' 
			),
			'ECS_CATEGORY' => array (
					'EcsCategory' => 'CAT_ID' 
			),
			'PRODUCT_ASSOC_TYPE' => array (
					'ProductAssocType' => 'PRODUCT_ASSOC_TYPE_ID' 
			),
			'PRODUCT_CALCULATED_INFO' => array (
					'ProductCalculatedInfo' => 'PRODUCT_ID' 
			),
			'PRODUCT_CONTENT_TYPE' => array (
					'ProductContentType' => 'PRODUCT_CONTENT_TYPE_ID' 
			),
			'OLD_PRODUCT_KEYWORD_RESULT' => array (
					'OldProductKeywordResult' => 'PRODUCT_KEYWORD_RESULT_ID' 
			),
			'PRODUCT_METER_TYPE' => array (
					'ProductMeterType' => 'PRODUCT_METER_TYPE_ID' 
			),
			'PRODUCT_MAINT_TYPE' => array (
					'ProductMaintType' => 'PRODUCT_MAINT_TYPE_ID' 
			),
			'PRODUCT_REVIEW' => array (
					'ProductReview' => 'PRODUCT_REVIEW_ID' 
			),
			'PRODUCT_SEARCH_RESULT' => array (
					'ProductSearchResult' => 'PRODUCT_SEARCH_RESULT_ID' 
			),
			'PRODUCT_TYPE' => array (
					'ProductType' => 'PRODUCT_TYPE_ID' 
			),
			'PRODUCT_PROMO' => array (
					'ProductPromo' => 'PRODUCT_PROMO_ID' 
			),
			'PRODUCT_PROMO_CODE' => array (
					'ProductPromoCode' => 'PRODUCT_PROMO_CODE_ID' 
			),
			'PRODUCT_STORE_GROUP' => array (
					'ProductStoreGroup' => 'PRODUCT_STORE_GROUP_ID' 
			),
			'PRODUCT_STORE_GROUP_TYPE' => array (
					'ProductStoreGroupType' => 'PRODUCT_STORE_GROUP_TYPE_ID' 
			),
			'PRODUCT_STORE_SHIPMENT_METH' => array (
					'ProductStoreShipmentMeth' => 'PRODUCT_STORE_SHIP_METH_ID' 
			),
			'PRODUCT_STORE_SURVEY_APPL' => array (
					'ProductStoreSurveyAppl' => 'PRODUCT_STORE_SURVEY_ID' 
			),
			'SUBSCRIPTION' => array (
					'Subscription' => 'SUBSCRIPTION_ID' 
			),
			'SUBSCRIPTION_ACTIVITY' => array (
					'SubscriptionActivity' => 'SUBSCRIPTION_ACTIVITY_ID' 
			),
			'SUBSCRIPTION_RESOURCE' => array (
					'SubscriptionResource' => 'SUBSCRIPTION_RESOURCE_ID' 
			),
			'SUBSCRIPTION_TYPE' => array (
					'SubscriptionType' => 'SUBSCRIPTION_TYPE_ID' 
			),
			'REORDER_GUIDELINE' => array (
					'ReorderGuideline' => 'REORDER_GUIDELINE_ID' 
			),
			'SUPPLIER_PREF_ORDER' => array (
					'SupplierPrefOrder' => 'SUPPLIER_PREF_ORDER_ID' 
			),
			'SUPPLIER_RATING_TYPE' => array (
					'SupplierRatingType' => 'SUPPLIER_RATING_TYPE_ID' 
			),
			'WEB_ANALYTICS_TYPE' => array (
					'WebAnalyticsType' => 'WEB_ANALYTICS_TYPE_ID' 
			),
			'PRODUCT_DIMENSION' => array (
					'ProductDimension' => 'DIMENSION_ID' 
			),
			'INVENTORY_ITEM_FACT' => array (
					'InventoryItemFact' => 'INVENTORY_ITEM_ID' 
			),
			'ITEM_ISSUANCE' => array (
					'ItemIssuance' => 'ITEM_ISSUANCE_ID' 
			),
			'PICKLIST' => array (
					'Picklist' => 'PICKLIST_ID' 
			),
			'PICKLIST_BIN' => array (
					'PicklistBin' => 'PICKLIST_BIN_ID' 
			),
			'REJECTION_REASON' => array (
					'RejectionReason' => 'REJECTION_ID' 
			),
			'SHIPMENT_RECEIPT' => array (
					'ShipmentReceipt' => 'RECEIPT_ID' 
			),
			'DELIVERY' => array (
					'Delivery' => 'DELIVERY_ID' 
			),
			'SHIPMENT' => array (
					'Shipment' => 'SHIPMENT_ID' 
			),
			'SHIPMENT_BOX_TYPE' => array (
					'ShipmentBoxType' => 'SHIPMENT_BOX_TYPE_ID' 
			),
			'SHIPMENT_CONTACT_MECH_TYPE' => array (
					'ShipmentContactMechType' => 'SHIPMENT_CONTACT_MECH_TYPE_ID' 
			),
			'SHIPMENT_COST_ESTIMATE' => array (
					'ShipmentCostEstimate' => 'SHIPMENT_COST_ESTIMATE_ID' 
			),
			'SHIPMENT_GATEWAY_CONFIG_TYPE' => array (
					'ShipmentGatewayConfigType' => 'SHIPMENT_GATEWAY_CONF_TYPE_ID' 
			),
			'SHIPMENT_GATEWAY_CONFIG' => array (
					'ShipmentGatewayConfig' => 'SHIPMENT_GATEWAY_CONFIG_ID' 
			),
			'SHIPMENT_GATEWAY_DHL' => array (
					'ShipmentGatewayDhl' => 'SHIPMENT_GATEWAY_CONFIG_ID' 
			),
			'SHIPMENT_GATEWAY_FEDEX' => array (
					'ShipmentGatewayFedex' => 'SHIPMENT_GATEWAY_CONFIG_ID' 
			),
			'SHIPMENT_GATEWAY_UPS' => array (
					'ShipmentGatewayUps' => 'SHIPMENT_GATEWAY_CONFIG_ID' 
			),
			'SHIPMENT_GATEWAY_USPS' => array (
					'ShipmentGatewayUsps' => 'SHIPMENT_GATEWAY_CONFIG_ID' 
			),
			'SHIPMENT_METHOD_TYPE' => array (
					'ShipmentMethodType' => 'SHIPMENT_METHOD_TYPE_ID' 
			),
			'SHIPMENT_TYPE' => array (
					'ShipmentType' => 'SHIPMENT_TYPE_ID' 
			),
			'SHIPPING_DOCUMENT' => array (
					'ShippingDocument' => 'DOCUMENT_ID' 
			),
			'TIME_ENTRY' => array (
					'TimeEntry' => 'TIME_ENTRY_ID' 
			),
			'TIMESHEET' => array (
					'Timesheet' => 'TIMESHEET_ID' 
			),
			'APPLICATION_SANDBOX' => array (
					'ApplicationSandbox' => 'APPLICATION_ID' 
			),
			'DELIVERABLE' => array (
					'Deliverable' => 'DELIVERABLE_ID' 
			),
			'DELIVERABLE_TYPE' => array (
					'DeliverableType' => 'DELIVERABLE_TYPE_ID' 
			),
			'WORK_EFFORT' => array (
					'WorkEffort' => 'WORK_EFFORT_ID' 
			),
			'WORK_EFFORT_ASSOC_TYPE' => array (
					'WorkEffortAssocType' => 'WORK_EFFORT_ASSOC_TYPE_ID' 
			),
			'WORK_EFFORT_CONTENT_TYPE' => array (
					'WorkEffortContentType' => 'WORK_EFFORT_CONTENT_TYPE_ID' 
			),
			'WORK_EFFORT_GOOD_STANDARD_TYPE' => array (
					'WorkEffortGoodStandardType' => 'WORK_EFFORT_GOOD_STD_TYPE_ID' 
			),
			'WORK_EFFORT_ICAL_DATA' => array (
					'WorkEffortIcalData' => 'WORK_EFFORT_ID' 
			),
			'WORK_EFFORT_PURPOSE_TYPE' => array (
					'WorkEffortPurposeType' => 'WORK_EFFORT_PURPOSE_TYPE_ID' 
			),
			'WORK_EFFORT_SEARCH_RESULT' => array (
					'WorkEffortSearchResult' => 'WORK_EFFORT_SEARCH_RESULT_ID' 
			),
			'WORK_EFFORT_TYPE' => array (
					'WorkEffortType' => 'WORK_EFFORT_TYPE_ID' 
			),
			'CATALINA_SESSION' => array (
					'CatalinaSession' => 'SESSION_ID' 
			),
			'DATA_SOURCE' => array (
					'DataSource' => 'DATA_SOURCE_ID' 
			),
			'DATA_SOURCE_TYPE' => array (
					'DataSourceType' => 'DATA_SOURCE_TYPE_ID' 
			),
			'EMAIL_TEMPLATE_SETTING' => array (
					'EmailTemplateSetting' => 'EMAIL_TEMPLATE_SETTING_ID' 
			),
			'ENUMERATION' => array (
					'Enumeration' => 'ENUM_ID' 
			),
			'ENUMERATION_TYPE' => array (
					'EnumerationType' => 'ENUM_TYPE_ID' 
			),
			'COUNTRY_CAPITAL' => array (
					'CountryCapital' => 'COUNTRY_CODE' 
			),
			'COUNTRY_CODE' => array (
					'CountryCode' => 'COUNTRY_CODE' 
			),
			'COUNTRY_TELE_CODE' => array (
					'CountryTeleCode' => 'COUNTRY_CODE' 
			),
			'GEO' => array (
					'Geo' => 'GEO_ID' 
			),
			'GEO_ASSOC_TYPE' => array (
					'GeoAssocType' => 'GEO_ASSOC_TYPE_ID' 
			),
			'GEO_POINT' => array (
					'GeoPoint' => 'GEO_POINT_ID' 
			),
			'GEO_TYPE' => array (
					'GeoType' => 'GEO_TYPE_ID' 
			),
			'STANDARD_LANGUAGE' => array (
					'StandardLanguage' => 'STANDARD_LANGUAGE_ID' 
			),
			'CUSTOM_METHOD' => array (
					'CustomMethod' => 'CUSTOM_METHOD_ID' 
			),
			'CUSTOM_METHOD_TYPE' => array (
					'CustomMethodType' => 'CUSTOM_METHOD_TYPE_ID' 
			),
			'NOTE_DATA' => array (
					'NoteData' => 'NOTE_ID' 
			),
			'CUSTOM_TIME_PERIOD' => array (
					'CustomTimePeriod' => 'CUSTOM_TIME_PERIOD_ID' 
			),
			'PERIOD_TYPE' => array (
					'PeriodType' => 'PERIOD_TYPE_ID' 
			),
			'STANDARD_TIME_PERIOD' => array (
					'StandardTimePeriod' => 'STANDARD_TIME_PERIOD_ID' 
			),
			'STATUS_ITEM' => array (
					'StatusItem' => 'STATUS_ID' 
			),
			'STATUS_TYPE' => array (
					'StatusType' => 'STATUS_TYPE_ID' 
			),
			'UOM' => array (
					'Uom' => 'UOM_ID' 
			),
			'UOM_TYPE' => array (
					'UomType' => 'UOM_TYPE_ID' 
			),
			'USER_PREF_GROUP_TYPE' => array (
					'UserPrefGroupType' => 'USER_PREF_GROUP_TYPE_ID' 
			),
			'VISUAL_THEME_SET' => array (
					'VisualThemeSet' => 'VISUAL_THEME_SET_ID' 
			),
			'VISUAL_THEME' => array (
					'VisualTheme' => 'VISUAL_THEME_ID' 
			),
			'PORTAL_PORTLET' => array (
					'PortalPortlet' => 'PORTAL_PORTLET_ID' 
			),
			'PORTLET_CATEGORY' => array (
					'PortletCategory' => 'PORTLET_CATEGORY_ID' 
			),
			'PORTAL_PAGE' => array (
					'PortalPage' => 'PORTAL_PAGE_ID' 
			),
			'DATE_DIMENSION' => array (
					'DateDimension' => 'DIMENSION_ID' 
			),
			'CURRENCY_DIMENSION' => array (
					'CurrencyDimension' => 'DIMENSION_ID' 
			),
			'ENTITY_AUDIT_LOG' => array (
					'EntityAuditLog' => 'AUDIT_HISTORY_SEQ_ID' 
			),
			'ENTITY_KEY_STORE' => array (
					'EntityKeyStore' => 'KEY_NAME' 
			),
			'SEQUENCE_VALUE_ITEM' => array (
					'SequenceValueItem' => 'SEQ_NAME' 
			),
			'TENANT' => array (
					'Tenant' => 'TENANT_ID' 
			),
			'TESTING' => array (
					'Testing' => 'TESTING_ID' 
			),
			'TESTING_TYPE' => array (
					'TestingType' => 'TESTING_TYPE_ID' 
			),
			'TEST_BLOB' => array (
					'TestBlob' => 'TEST_BLOB_ID' 
			),
			'TEST_FIELD_TYPE' => array (
					'TestFieldType' => 'TEST_FIELD_TYPE_ID' 
			),
			'TESTING_NODE' => array (
					'TestingNode' => 'TESTING_NODE_ID' 
			),
			'ENTITY_GROUP' => array (
					'EntityGroup' => 'ENTITY_GROUP_ID' 
			),
			'ENTITY_SYNC' => array (
					'EntitySync' => 'ENTITY_SYNC_ID' 
			),
			'ENTITY_SYNC_REMOVE' => array (
					'EntitySyncRemove' => 'ENTITY_SYNC_REMOVE_ID' 
			),
			'EXAMPLE' => array (
					'Example' => 'EXAMPLE_ID' 
			),
			'EXAMPLE_TYPE' => array (
					'ExampleType' => 'EXAMPLE_TYPE_ID' 
			),
			'EXAMPLE_FEATURE' => array (
					'ExampleFeature' => 'EXAMPLE_FEATURE_ID' 
			),
			'EXAMPLE_FEATURE_APPL_TYPE' => array (
					'ExampleFeatureApplType' => 'EXAMPLE_FEATURE_APPL_TYPE_ID' 
			),
			'X509_ISSUER_PROVISION' => array (
					'X509IssuerProvision' => 'CERT_PROVISION_ID' 
			),
			'USER_LOGIN_SESSION' => array (
					'UserLoginSession' => 'USER_LOGIN_ID' 
			),
			'SECURITY_GROUP' => array (
					'SecurityGroup' => 'GROUP_ID' 
			),
			'SECURITY_PERMISSION' => array (
					'SecurityPermission' => 'PERMISSION_ID' 
			),
			'JOB_SANDBOX' => array (
					'JobSandbox' => 'JOB_ID' 
			),
			'RECURRENCE_INFO' => array (
					'RecurrenceInfo' => 'RECURRENCE_INFO_ID' 
			),
			'RECURRENCE_RULE' => array (
					'RecurrenceRule' => 'RECURRENCE_RULE_ID' 
			),
			'RUNTIME_DATA' => array (
					'RuntimeData' => 'RUNTIME_DATA_ID' 
			),
			'TEMPORAL_EXPRESSION' => array (
					'TemporalExpression' => 'TEMP_EXPR_ID' 
			),
			'SERVICE_SEMAPHORE' => array (
					'ServiceSemaphore' => 'SERVICE_NAME' 
			),
			'SELENIUM_TEST_SUITE_PATH' => array (
					'SeleniumTestSuitePath' => 'TEST_SUITE_ID' 
			),
			'BROWSER_TYPE' => array (
					'BrowserType' => 'BROWSER_TYPE_ID' 
			),
			'PLATFORM_TYPE' => array (
					'PlatformType' => 'PLATFORM_TYPE_ID' 
			),
			'PROTOCOL_TYPE' => array (
					'ProtocolType' => 'PROTOCOL_TYPE_ID' 
			),
			'SERVER_HIT_BIN' => array (
					'ServerHitBin' => 'SERVER_HIT_BIN_ID' 
			),
			'SERVER_HIT_TYPE' => array (
					'ServerHitType' => 'HIT_TYPE_ID' 
			),
			'USER_AGENT' => array (
					'UserAgent' => 'USER_AGENT_ID' 
			),
			'USER_AGENT_METHOD_TYPE' => array (
					'UserAgentMethodType' => 'USER_AGENT_METHOD_TYPE_ID' 
			),
			'USER_AGENT_TYPE' => array (
					'UserAgentType' => 'USER_AGENT_TYPE_ID' 
			),
			'VISIT' => array (
					'Visit' => 'VISIT_ID' 
			),
			'VISITOR' => array (
					'Visitor' => 'VISITOR_ID' 
			),
			'WEB_PAGE' => array (
					'WebPage' => 'WEB_PAGE_ID' 
			),
			'WEB_SITE' => array (
					'WebSite' => 'WEB_SITE_ID' 
			),
			'WEBSLINGER_SERVER' => array (
					'WebslingerServer' => 'WEBSLINGER_SERVER_ID' 
			),
			'WEBSLINGER_HOST_SUFFIX' => array (
					'WebslingerHostSuffix' => 'HOST_SUFFIX_ID' 
			),
			'MLL_WS_LOG' => array (
					'MllWsLog' => 'WS_ID' 
			),
			'MLL_WS_CLIENT_LOG' => array (
					'MllWsClientLog' => 'WS_ID' 
			),
			'MLL_INVENTORY_MOVE_HEADER' => array (
					'MllInventoryMoveHeader' => 'INVENTORY_MOVE_ID' 
			),
			'MLL_PRODUCT_ATTR' => array (
					'MllProductAttr' => 'PRODUCT_ATTR_ID' 
			),
			'MLL_PRODUCT_CAT' => array (
					'MllProductCat' => 'ID' 
			),
			'MLL_PRODUCT_PROPERTY_TYPE' => array (
					'MllProductPropertyType' => 'CAT_ID' 
			),
			'MLL_PRODUCT_ATTRIBUTE' => array (
					'MllProductAttribute' => 'ATTR_ID' 
			),
			'MLL_PRODUCT_ATTR_VALUE' => array (
					'MllProductAttrValue' => 'ID' 
			),
			'MLL_ALBUM_LIST' => array (
					'MllAlbumList' => 'ALBUM_ID' 
			),
			'MLL_MAIN_PICTURE' => array (
					'MllMainPicture' => 'MAIN_PICTURE_ID' 
			),
			'MLL_PROMOTION_IMAGE' => array (
					'MllPromotionImage' => 'PROMOTION_IMAGE_ID' 
			),
			'MLL_CUT_IN_FIGURE' => array (
					'MllCutInFigure' => 'CUT_IN_FIGURE_ID' 
			),
			'MLL_PRODUCT_DESIGN_INFO' => array (
					'MllProductDesignInfo' => 'PRODUCT_ID' 
			),
			'MLL_ZAHLUNG' => array (
					'MllZahlung' => 'ID' 
			),
			'MLL_ZAHLUNG_TYPE' => array (
					'MllZahlungType' => 'ID' 
			),
			'MLL_SHIP_SOON_PAY_RATE_SET' => array (
					'MllShipSoonPayRateSet' => 'ID' 
			),
			'MLL_ORDER_SHIP_TO_SET' => array (
					'MllOrderShipToSet' => 'ORDER_ID' 
			),
			'OS_RATIO_CONTROL' => array (
					'OsRatioControl' => 'ID' 
			),
			'ERP_MAP_PHP_BY_ORDER_ID' => array (
					'ErpMapPhpByOrderId' => 'PHP_ORDER_ID' 
			),
			'OS_RATIO_CONTROL_DF' => array (
					'OsRatioControlDf' => 'ID' 
			),
			'SUPPLIER_PRODUCT_LOG' => array (
					'SupplierProductLog' => 'ID' 
			),
			'ORDER_COMMISSIONLIST' => array (
					'OrderCommissionlist' => 'ID' 
			),
			'DAILYINF' => array (
					'Dailyinf' => 'ID' 
			),
			'DAILYINF_DETAIL' => array (
					'DailyinfDetail' => 'ID' 
			),
			'SOURCE_NAME' => array (
					'SourceName' => 'SOURCE_ID' 
			),
			'SOURCE_TYPE_NAME' => array (
					'SourceTypeName' => 'TYPE_ID' 
			),
			'STORE_NAME' => array (
					'StoreName' => 'STORE_ID' 
			),
			'CALL_STATUS' => array (
					'CallStatus' => 'STATUS_ID' 
			),
			'PHONE_SOURCE_NAME' => array (
					'PhoneSourceName' => 'PS_ID' 
			),
			'MLL_KEYWORDS_AGE' => array (
					'MllKeywordsAge' => 'AGE_ID' 
			),
			'MLL_DM_MOBILE' => array (
					'MllDmMobile' => 'ID' 
			),
			'FRANCHISEE_RECEIVED' => array (
					'FranchiseeReceived' => 'RECEIVED_ID' 
			),
			'FRANCHISEE_PAYOUT' => array (
					'FranchiseePayout' => 'PAYOUT_ID' 
			),
			'FRANCHISEE_RECEIVED_TOTAL' => array (
					'FranchiseeReceivedTotal' => 'GROUP_ID' 
			),
			'FRANCHISEE_RECEIVED_REBATE_DETAIL' => array (
					'FranchiseeReceivedRebateDetail' => 'TOTAL_ID' 
			),
			'FRANCHISEE_RECEIVED_REBATE_ORDER_DETAIL' => array (
					'FranchiseeReceivedRebateOrderDetail' => 'ID' 
			),
			'MLL_RED_PACKET_DETAIL' => array (
					'MllRedPacketDetail' => 'ID' 
			),
			'MLL_RED_PACKET_LOG' => array (
					'MllRedPacketLog' => 'ID' 
			),
			'MLL_RED_PACKET_USE' => array (
					'MllRedPacketUse' => 'ID' 
			),
			'MLL_RED_PACKET_USER' => array (
					'MllRedPacketUser' => 'ID' 
			),
			'MLL_RED_DISCOUNT_TYPE' => array (
					'MllRedDiscountType' => 'DISCOUNT_TYPE_ID' 
			),
			'MLL_OPERATE_RECORD' => array (
					'MllOperateRecord' => 'ID' 
			),
			'XML_SQL_INFO' => array (
					'XmlSqlInfo' => 'XML_ID' 
			),
			'XML_USER_OPERATE' => array (
					'XmlUserOperate' => 'RELATION_ID' 
			),
			'FTP_SPACE' => array (
					'FtpSpace' => 'ID' 
			),
			'ACTIVITY_FACT' => array (
					'ActivityFact' => 'ACTIVITY_FACT_ID' 
			),
			'AMAZON_ORDER_DOCUMENT' => array (
					'AmazonOrderDocument' => 'DOCUMENT_ID' 
			),
			'AMAZON_ORDER' => array (
					'AmazonOrder' => 'AMAZON_ORDER_ID' 
			),
			'AMAZON_ORDER_ITEM_TAX_JURISDTN' => array (
					'AmazonOrderItemTaxJurisdtn' => 'ITEM_TAX_JURIS_TYPE_ID' 
			),
			'AMAZON_ORDER_IMPORT' => array (
					'AmazonOrderImport' => 'AMAZON_ORDER_ID' 
			),
			'AMAZON_PARTY' => array (
					'AmazonParty' => 'BUYER_EMAIL_ADDRESS' 
			),
			'AMAZON_PRODUCT' => array (
					'AmazonProduct' => 'PRODUCT_ID' 
			),
			'AMAZON_NODE_MAPPING_TYPE' => array (
					'AmazonNodeMappingType' => 'NODE_MAPPING_TYPE_ID' 
			),
			'AMAZON_PRODUCT_BROWSE_NODE' => array (
					'AmazonProductBrowseNode' => 'NODE_ID' 
			),
			'AMAZON_PRODUCT_ITEM_TYPE' => array (
					'AmazonProductItemType' => 'ITEM_TYPE_ID' 
			),
			'AMAZON_PRODUCT_USED_FOR' => array (
					'AmazonProductUsedFor' => 'USED_FOR_ID' 
			),
			'AMAZON_PRODUCT_TARGET_AUDIENCE' => array (
					'AmazonProductTargetAudience' => 'TARGET_AUDIENCE_ID' 
			),
			'AMAZON_PRODUCT_OTHER_ITEM_ATTR' => array (
					'AmazonProductOtherItemAttr' => 'OTHER_ITEM_ATTR_ID' 
			),
			'AMAZON_PRODUCT_PRICE' => array (
					'AmazonProductPrice' => 'PRODUCT_ID' 
			),
			'AMAZON_PRODUCT_IMAGE' => array (
					'AmazonProductImage' => 'PRODUCT_ID' 
			),
			'AMAZON_PRODUCT_INVENTORY' => array (
					'AmazonProductInventory' => 'PRODUCT_ID' 
			),
			'AMAZON_PRODUCT_FEED_PROCESSING' => array (
					'AmazonProductFeedProcessing' => 'FEED_TYPE' 
			),
			'AMAZON_BATCH_UPDATE_HISTORY' => array (
					'AmazonBatchUpdateHistory' => 'HISTORY_ID' 
			),
			'CRM_RULE_RESULT_PERSIST_LOG' => array (
					'CrmRuleResultPersistLog' => 'ID' 
			),
			'CRM_RULE_RESULT_PERSIST' => array (
					'CrmRuleResultPersist' => 'CODE' 
			),
			'CRM_SALER_SALES_SCORE' => array (
					'CrmSalerSalesScore' => 'SALER_ID' 
			),
			'CRM_SURVEY' => array (
					'CrmSurvey' => 'ID' 
			),
			'CRM_EMAIL_TEMPLATE' => array (
					'CrmEmailTemplate' => 'CODE' 
			),
			'CRM_GENERAL_PULL_CONFIG' => array (
					'CrmGeneralPullConfig' => 'TAG' 
			),
			'CRM_GENERAL_PULL_LOG' => array (
					'CrmGeneralPullLog' => 'ID' 
			),
			'CRM_OLD_CUST_CARE_DETAIL' => array (
					'CrmOldCustCareDetail' => 'ID' 
			),
			'CRM_OLD_CUST_REVISIT' => array (
					'CrmOldCustRevisit' => 'ID' 
			),
			'CRM_SALES_QUANTITY_OP_LOG' => array (
					'CrmSalesQuantityOpLog' => 'ID' 
			),
			'CRM_EXPR_CONFIG' => array (
					'CrmExprConfig' => 'EXPR_ID' 
			),
			'CRM_ACTION_PULL_LOG' => array (
					'CrmActionPullLog' => 'ID' 
			),
			'CRM_ACTION_PULL_CONFIG' => array (
					'CrmActionPullConfig' => 'ID' 
			),
			'CRM_EXPR_SALER_LIMIT' => array (
					'CrmExprSalerLimit' => 'SHOP_ID' 
			),
			'CRM_SALER_LEVEL_RULE' => array (
					'CrmSalerLevelRule' => 'ID' 
			),
			'CRM_GIFT_STOCK' => array (
					'CrmGiftStock' => 'SHOP_ID' 
			),
			'CRM_CUSTOM_SMS_SEND_LOG' => array (
					'CrmCustomSmsSendLog' => 'ID' 
			),
			'CRM_TASK_RULE_MAPPING' => array (
					'CrmTaskRuleMapping' => 'TASK_TYPE_ID' 
			),
			'CRM_SMS_TEMPLATE_NEW' => array (
					'CrmSmsTemplateNew' => 'BIZ_AREA' 
			),
			'CRM_SMS_SEND_LOG_NEW' => array (
					'CrmSmsSendLogNew' => 'ID' 
			),
			'CRM_CUSTOMER_TYPE_CONFIG' => array (
					'CrmCustomerTypeConfig' => 'TYPE' 
			),
			'CRM_INSTRUCTION' => array (
					'CrmInstruction' => 'ID' 
			),
			'CRM_SALER_NOTE' => array (
					'CrmSalerNote' => 'NOTE_ID' 
			),
			'CRM_BLOCKED_CUSTOMER' => array (
					'CrmBlockedCustomer' => 'MOBILE' 
			),
			'CRM_NAMED_VALUE' => array (
					'CrmNamedValue' => 'ID' 
			),
			'CRM_ROLE' => array (
					'CrmRole' => 'ROLE_TAG' 
			),
			'CRM_SHOP_LOCATION' => array (
					'CrmShopLocation' => 'SHOP_ID' 
			),
			'CRM_PATROL_TRAINING_CHECK' => array (
					'CrmPatrolTrainingCheck' => 'PATROL_ID' 
			),
			'CRM_PATROL' => array (
					'CrmPatrol' => 'PATROL_ID' 
			),
			'CRM_PATROL_SCORE' => array (
					'CrmPatrolScore' => 'SCORE_ID' 
			),
			'CRM_PATROL_SCORE_ITEM' => array (
					'CrmPatrolScoreItem' => 'ITEM_ID' 
			),
			'CRM_TASK_FIELD_TYPE' => array (
					'CrmTaskFieldType' => 'FIELD_TYPE_ID' 
			),
			'CRM_TASK_CONFIG' => array (
					'CrmTaskConfig' => 'TASK_FIELD_ID' 
			),
			'CRM_TASK_BASE_INFO' => array (
					'CrmTaskBaseInfo' => 'MOBILE' 
			),
			'ECS_VENETO_PHPVIEW' => array (
					'EcsVenetoPhpview' => 'VENETO_ID' 
			),
			'ECS_EXPR_NATURE_PHPVIEW' => array (
					'EcsExprNaturePhpview' => 'EXPR_ID' 
			),
			'ORDER_HEADER_PHPVIEW' => array (
					'OrderHeaderPhpview' => 'ORDER_ID' 
			),
			'ORDER_ITEM_PHPVIEW' => array (
					'OrderItemPhpview' => 'REC_ID' 
			),
			'FRANCHISEE_SETTLEMENT' => array (
					'FranchiseeSettlement' => 'ALIANCE_ID' 
			),
			'SALES_TEAM_ROLE_SECURITY' => array (
					'SalesTeamRoleSecurity' => 'SECURITY_GROUP_ID' 
			),
			'CRM_TASK_REPAIR_LOG' => array (
					'CrmTaskRepairLog' => 'ID' 
			),
			'TRAINPLAN_GROUP' => array (
					'trainplanGroup' => 'ID' 
			),
			'USPS_BMC_CODE' => array (
					'UspsBmcCode' => 'BMC_CODE' 
			),
			'USPS_ZIP_TO_BMC_CODE' => array (
					'UspsZipToBmcCode' => 'ZIP3' 
			),
			'USPS_B_P_M_ZONE_MAP' => array (
					'UspsBPMZoneMap' => 'ZIP3' 
			),
			'USPS_B_P_M_RATES_BY_ZONE' => array (
					'UspsBPMRatesByZone' => 'USPS_B_P_M_RATE_ZONE' 
			),
			'USPS_CONTACT_LIST_SORT' => array (
					'UspsContactListSort' => 'USPS_CONTACT_LIST_SORT_ID' 
			),
			'ADDRESS_LABEL_SPECIFICATION' => array (
					'AddressLabelSpecification' => 'ADDRESS_LABEL_ID' 
			),
			'DATA_IMPORT_GL_ACCOUNT' => array (
					'DataImportGlAccount' => 'GL_ACCOUNT_ID' 
			),
			'DATA_IMPORT_CUSTOMER' => array (
					'DataImportCustomer' => 'CUSTOMER_ID' 
			),
			'DATA_IMPORT_CUSTOMER_PASSWORD' => array (
					'DataImportCustomerPassword' => 'USER_LOGIN_ID' 
			),
			'DATA_IMPORT_COMMISSION_RATES' => array (
					'DataImportCommissionRates' => 'CUSTOMER_ID' 
			),
			'MS_ACCOUNT_BASE' => array (
					'MsAccountBase' => 'ACCOUNT_ID' 
			),
			'MS_CONTACT_BASE' => array (
					'MsContactBase' => 'CONTACT_ID' 
			),
			'MS_LEAD_BASE' => array (
					'MsLeadBase' => 'LEAD_ID' 
			),
			'NET_SUITE_ITEM' => array (
					'NetSuiteItem' => 'ITEM_ID' 
			),
			'NET_SUITE_CUSTOMER' => array (
					'NetSuiteCustomer' => 'CUSTOMER_ID' 
			),
			'NET_SUITE_ADDRESS_BOOK' => array (
					'NetSuiteAddressBook' => 'ADDRESS_BOOK_ID' 
			),
			'NET_SUITE_CUSTOMER_TYPE' => array (
					'NetSuiteCustomerType' => 'CUSTOMER_TYPE_ID' 
			),
			'NET_SUITE_SALES_ORDER_TYPE' => array (
					'NetSuiteSalesOrderType' => 'LIST_ID' 
			),
			'NET_SUITE_PRICE_LIST' => array (
					'NetSuitePriceList' => 'PRICE_TYPE_ID' 
			),
			'NET_SUITE_ITEM_PRICE' => array (
					'NetSuiteItemPrice' => 'PRICE_ID' 
			),
			'NET_SUITE_PAYMENT_TERM' => array (
					'NetSuitePaymentTerm' => 'PAYMENT_TERMS_ID' 
			),
			'DATA_IMPORT_ORDER_HEADER' => array (
					'DataImportOrderHeader' => 'ORDER_ID' 
			),
			'DATA_IMPORT_PRODUCT' => array (
					'DataImportProduct' => 'PRODUCT_ID' 
			),
			'DATA_IMPORT_INVENTORY' => array (
					'DataImportInventory' => 'ITEM_ID' 
			),
			'DATA_IMPORT_SHOPPING_LIST' => array (
					'DataImportShoppingList' => 'PARTY_ID' 
			),
			'DATA_IMPORT_SUPPLIER' => array (
					'DataImportSupplier' => 'SUPPLIER_ID' 
			),
			'FINANCIAL_TMP_PAYOUT_MARK' => array (
					'FinancialTmpPayoutMark' => 'CREATED_BY' 
			),
			'FINANCIAL_PAYOUT_FORM_STATUS' => array (
					'FinancialPayoutFormStatus' => 'STATUS_ID' 
			),
			'FINANCIAL_PAYOUT_FORM_DATA' => array (
					'FinancialPayoutFormData' => 'DATA_ID' 
			),
			'FINANCIAL_PAYOUT_FORM_DATA_INFO' => array (
					'FinancialPayoutFormDataInfo' => 'INFO_ID' 
			),
			'FINANCIAL_PAYOUT_FORM_DETAIL' => array (
					'FinancialPayoutFormDetail' => 'LEVEL_CODE' 
			),
			'FIN_PROFIT_SETTLEMENT_PULL_LOG' => array (
					'FinProfitSettlementPullLog' => 'ID' 
			),
			'FINANCIAL_UNMATCH_FEE' => array (
					'FinancialUnmatchFee' => 'UNMATCH_ID' 
			),
			'PURCHASE_PAYBANK' => array (
					'PurchasePaybank' => 'PAY_BANK_ID' 
			),
			'PURCHASE_LIMIT_AMOUNT' => array (
					'PurchaseLimitAmount' => 'NOTE_DATE' 
			),
			'PURCHASE_INVOICE' => array (
					'PurchaseInvoice' => 'INVOICE_ID' 
			),
			'PURCHASE_BILL' => array (
					'PurchaseBill' => 'BILL_ID' 
			),
			'PURCHASE_BEGINNING_BALANCE' => array (
					'PurchaseBeginningBalance' => 'BEGIN_ID' 
			),
			'PURCHASE_POOL_SETTING' => array (
					'PurchasePoolSetting' => 'SETTING_ID' 
			),
			'PURCHASE_BEGINNING_POOL' => array (
					'PurchaseBeginningPool' => 'POOL_ID' 
			),
			'PURCHASE_BEGINNING_RELATION' => array (
					'PurchaseBeginningRelation' => 'RELATION_ID' 
			),
			'PURCHASE_DEDUCTION' => array (
					'PurchaseDeduction' => 'DEDUCTION_ID' 
			),
			'PURCHASE_DEDUCT_MONEY' => array (
					'PurchaseDeductMoney' => 'PDM_ID' 
			),
			'PURCHASE_PAYOUT_TYPE' => array (
					'PurchasePayoutType' => 'SALES_TYPE' 
			),
			'PURCHASE_FINANCIAL_PAYOUT_DETAIL' => array (
					'PurchaseFinancialPayoutDetail' => 'PFPD_ID' 
			),
			'ENCUMBRANCE_SNAPSHOT' => array (
					'EncumbranceSnapshot' => 'ENCUMBRANCE_SNAPSHOT_ID' 
			),
			'ENCUMBRANCE_DETAIL_TYPE' => array (
					'EncumbranceDetailType' => 'ENCUMBRANCE_DETAIL_TYPE_ID' 
			),
			'INVOICE_ADJUSTMENT_TYPE' => array (
					'InvoiceAdjustmentType' => 'INVOICE_ADJUSTMENT_TYPE_ID' 
			),
			'ACCOUNT_BALANCE_HISTORY' => array (
					'AccountBalanceHistory' => 'ACCOUNT_BALANCE_HISTORY_ID' 
			),
			'GL_ACCOUNT_CLASS_TYPE_MAP' => array (
					'GlAccountClassTypeMap' => 'GL_ACCOUNT_CLASS_TYPE_KEY' 
			),
			'INVOICE_ADJUSTMENT' => array (
					'InvoiceAdjustment' => 'INVOICE_ADJUSTMENT_ID' 
			),
			'INVOICE_ITEM_ASSOC' => array (
					'InvoiceItemAssoc' => 'INVOICE_ITEM_ASSOC_ID' 
			),
			'PRODUCT_AVERAGE_COST' => array (
					'ProductAverageCost' => 'PRODUCT_AVERAGE_COST_ID' 
			),
			'LOCKBOX_BATCH' => array (
					'LockboxBatch' => 'LOCKBOX_BATCH_ID' 
			),
			'PACTH_ORDER_RECORDS' => array (
					'PacthOrderRecords' => 'RECORD_ID' 
			),
			'PAYCHECK_ITEM_TYPE' => array (
					'PaycheckItemType' => 'PAYCHECK_ITEM_TYPE_ID' 
			),
			'PAYCHECK_ITEM_CLASS' => array (
					'PaycheckItemClass' => 'PAYCHECK_ITEM_CLASS_ID' 
			),
			'STORE_DIM' => array (
					'StoreDim' => 'STORE_DIM_ID' 
			),
			'TAX_AUTHORITY_DIM' => array (
					'TaxAuthorityDim' => 'TAX_AUTHORITY_DIM_ID' 
			),
			'DATE_DIM' => array (
					'DateDim' => 'DATE_DIM_ID' 
			),
			'CURRENCY_DIM' => array (
					'CurrencyDim' => 'CURRENCY_DIM_ID' 
			),
			'ORGANIZATION_DIM' => array (
					'OrganizationDim' => 'ORGANIZATION_DIM_ID' 
			),
			'SALES_INVOICE_ITEM_FACT' => array (
					'SalesInvoiceItemFact' => 'SALES_INV_ITEM_FACT_ID' 
			),
			'TAX_INVOICE_ITEM_FACT' => array (
					'TaxInvoiceItemFact' => 'TAX_INV_ITEM_FACT_ID' 
			),
			'GL_ACCOUNT_TRANS_ENTRY_FACT' => array (
					'GlAccountTransEntryFact' => 'GL_ACCOUNT_TRANS_ENTRY_FACT_ID' 
			),
			'INTERVIEW' => array (
					'Interview' => 'INV_ID' 
			),
			'INTERVIEW_RESULT' => array (
					'InterviewResult' => 'RESULT_ID' 
			),
			'INTERVIEW_WORKS' => array (
					'InterviewWorks' => 'ITEM_ID' 
			),
			'INTERVIEW_FILES' => array (
					'InterviewFiles' => 'FILE_ID' 
			),
			'INTERVIEW_LOG' => array (
					'InterviewLog' => 'LOG_ID' 
			),
			'ENTRY_PERSON_RELATION' => array (
					'EntryPersonRelation' => 'REL_ID' 
			),
			'ENTRY_PERSON_JOIN_DETAIL' => array (
					'EntryPersonJoinDetail' => 'JOIN_ID' 
			),
			'ENTRY_PERSON_JOIN_LOG' => array (
					'EntryPersonJoinLog' => 'LOG_ID' 
			),
			'ENTRY_PERSON_FILES' => array (
					'EntryPersonFiles' => 'FILE_ID' 
			),
			'ENTRY_PERSON_WORKS' => array (
					'EntryPersonWorks' => 'ITEM_ID' 
			),
			'ENTRY_PERSON_STUDY' => array (
					'EntryPersonStudy' => 'ITEM_ID' 
			),
			'ENTRY_PERSON_LOG' => array (
					'EntryPersonLog' => 'LOG_ID' 
			),
			'ENTRY_PERSON_GROUP_RULE_LOG' => array (
					'EntryPersonGroupRuleLog' => 'LOG_ID' 
			),
			'ACCTG_TAG_USAGE_TYPE' => array (
					'AcctgTagUsageType' => 'ACCTG_TAG_USAGE_TYPE_ID' 
			),
			'ACCTG_TAG_POSTING_CHECK' => array (
					'AcctgTagPostingCheck' => 'ORGANIZATION_PARTY_ID' 
			),
			'AGREEMENT_TERM_BILLING' => array (
					'AgreementTermBilling' => 'AGREEMENT_TERM_BILLING_ID' 
			),
			'AGREEMENT_DOCUMENT_TYPE_MAP' => array (
					'AgreementDocumentTypeMap' => 'DOCUMENT_TYPE_ID' 
			),
			'TERM_TYPE_FIELDS' => array (
					'TermTypeFields' => 'TERM_TYPE_ID' 
			),
			'FACILITY_ASSOC_TYPE' => array (
					'FacilityAssocType' => 'FACILITY_ASSOC_TYPE_ID' 
			),
			'MARKETING_CAMPAIGN_CONTACT_LIST' => array (
					'MarketingCampaignContactList' => 'CAMPAIGN_LIST_ID' 
			),
			'PARTY_SUPPLEMENTAL_DATA' => array (
					'PartySupplementalData' => 'PARTY_ID' 
			),
			'VIEW_PREF_TYPE' => array (
					'ViewPrefType' => 'VIEW_PREF_TYPE_ID' 
			),
			'VIEW_PREF_VALUE_TYPE' => array (
					'ViewPrefValueType' => 'VIEW_PREF_VALUE_TYPE_ID' 
			),
			'SALES_FORECAST_ITEM' => array (
					'SalesForecastItem' => 'SALES_FORECAST_ITEM_ID' 
			),
			'MERGE_FORM_CATEGORY' => array (
					'MergeFormCategory' => 'MERGE_FORM_CATEGORY_ID' 
			),
			'MERGE_FORM' => array (
					'MergeForm' => 'MERGE_FORM_ID' 
			),
			'CASH_DRAWER' => array (
					'CashDrawer' => 'CASH_DRAWER_ID' 
			),
			'FACILITY_TRANSFER_PLAN' => array (
					'FacilityTransferPlan' => 'FACILITY_TRANSFER_PLAN_ID' 
			),
			'CARRIER_RETURN_SERVICE' => array (
					'CarrierReturnService' => 'CARRIER_RETURN_SERVICE_ID' 
			),
			'INVENTORY_ITEM_VALUE_HISTORY' => array (
					'InventoryItemValueHistory' => 'INVENTORY_ITEM_VALUE_HIST_ID' 
			),
			'REPORT_REGISTRY' => array (
					'ReportRegistry' => 'REPORT_ID' 
			),
			'REPORT_GROUP' => array (
					'ReportGroup' => 'REPORT_GROUP_ID' 
			),
			'TEST_GEO_DATA' => array (
					'TestGeoData' => 'GEO_ROW_ID' 
			),
			'EXTERNAL_USER' => array (
					'ExternalUser' => 'AUTO_ID' 
			),
			'EXTERNAL_USER_TYPE' => array (
					'ExternalUserType' => 'EXTERNAL_USER_TYPE_ID' 
			),
			'VIEW_HISTORY' => array (
					'ViewHistory' => 'VIEW_HISTORY_ID' 
			),
			'CONTEXT_HELP_RESOURCE' => array (
					'ContextHelpResource' => 'CONTEXT_HELP_RESOURCE_ID' 
			),
			'KEYBOARD_SHORTCUT_HANDLER' => array (
					'KeyboardShortcutHandler' => 'ACTION_TYPE_ID' 
			),
			'KEYBOARD_SHORTCUT' => array (
					'KeyboardShortcut' => 'SHORTCUT_ID' 
			),
			'INVENTORY_EVENT_PLANNED_TYPE' => array (
					'InventoryEventPlannedType' => 'INVENTORY_EVENT_PLAN_TYPE_ID' 
			),
			'DATA_WAREHOUSE_TRANSFORM' => array (
					'DataWarehouseTransform' => 'TRANSFORM_ID' 
			),
			'OPENTAPS_CONFIGURATION' => array (
					'OpentapsConfiguration' => 'CONFIG_TYPE_ID' 
			),
			'OPENTAPS_CONFIGURATION_TYPE' => array (
					'OpentapsConfigurationType' => 'CONFIG_TYPE_ID' 
			),
			'AUTO_FORM_HEADER' => array (
					'AutoFormHeader' => 'FORM_ID' 
			),
			'AUTO_FORM_COMPONENT' => array (
					'AutoFormComponent' => 'COMPONENT_ID' 
			),
			'AUTO_COMPONENT_TYPE' => array (
					'AutoComponentType' => 'COM_TYPE_ID' 
			),
			'AUTO_FORM_ELEMENT' => array (
					'AutoFormElement' => 'ELEMENT_ID' 
			),
			'AUTO_FORM_COMPONENT_FUNCTION' => array (
					'AutoFormComponentFunction' => 'AFCF_ID' 
			),
			'AUTO_FORM_ELEMENT_FUNCTION' => array (
					'AutoFormElementFunction' => 'AFEF_ID' 
			),
			'AUTO_FORM_FUNCTION_AUTHORITY' => array (
					'AutoFormFunctionAuthority' => 'AUTHORITY_ID' 
			),
			'AUTO_FORM_MENU' => array (
					'AutoFormMenu' => 'MENU_ID' 
			),
			'OPENTAPS_WEB_APPS' => array (
					'OpentapsWebApps' => 'APPLICATION_ID' 
			),
			'OPENTAPS_SHORTCUT_GROUP' => array (
					'OpentapsShortcutGroup' => 'GROUP_ID' 
			),
			'TEST_ENTITY' => array (
					'TestEntity' => 'TEST_ID' 
			),
			'TEST_ENTITY_MODIFY_HISTORY' => array (
					'TestEntityModifyHistory' => 'TEST_ENTITY_HISTORY_ID' 
			),
			'SEQ_TEST' => array (
					'SeqTest' => 'ID' 
			),
			'PURCHASING_SUBCONTRACT_INFO' => array (
					'PurchasingSubcontractInfo' => 'SUBCONTRACT_NO' 
			),
			'PURCHASING_SUBCONTRACT_DETAIL' => array (
					'PurchasingSubcontractDetail' => 'DETAIL_ID' 
			),
			'PACTH_ORDER_DETAIL' => array (
					'PacthOrderDetail' => 'ORDER_ID' 
			),
			'PACTH_ORDER_REASON' => array (
					'PacthOrderReason' => 'REASON_ID' 
			),
			'MLL_GOODS_SALES' => array (
					'MllGoodsSales' => 'GOODS_ID' 
			),
			'GOODS_MAX_CONFIG_NUM' => array (
					'GoodsMaxConfigNum' => 'CONFIG_ID' 
			),
			'AVERAGE_GOODS_SALES_NUM_BAT' => array (
					'AverageGoodsSalesNumBat' => 'AGSN_ID' 
			),
			'ORDER_ITEM_EXTENSION' => array (
					'OrderItemExtension' => 'ORDER_ITEM_EXTENSION_ID' 
			),
			'IN_STOCK_FAIL_INFO' => array (
					'InStockFailInfo' => 'ISFI_ID' 
			),
			'IN_STOCK_INFO' => array (
					'InStockInfo' => 'ISI_ID' 
			),
			'PHP_TO_ERP_ORDER' => array (
					'PhpToErpOrder' => 'PTE_ID' 
			),
			'AUDIT_ORDER_ITEM' => array (
					'AuditOrderItem' => 'AUDIT_ID' 
			),
			'IMP_PUR_HEADER' => array (
					'ImpPurHeader' => 'HEADER_ID' 
			),
			'IMP_PUR_ITEM_ORDER' => array (
					'ImpPurItemOrder' => 'ITEM_ID' 
			),
			'PURCHASING_EXTRA_ORDER' => array (
					'PurchasingExtraOrder' => 'EXTRA_ID' 
			),
			'PURCHASING_NOTICE_ORDER' => array (
					'PurchasingNoticeOrder' => 'NOTICE_ID' 
			),
			'PURCHASING_NOTICE_ORDERITEMS' => array (
					'PurchasingNoticeOrderitems' => 'NOTICE_ITEM_ID' 
			),
			'SUPPLIER_WAREHOUSE_ADDRESS' => array (
					'SupplierWarehouseAddress' => 'ADDRESS_ID' 
			),
			'SUPPLIER_INFO' => array (
					'Party' => 'PARTY_ID' 
			),
			'SUPPLIER_DETAIL' => array (
					'SupplierDetail' => 'SUPPLIER_ID' 
			),
			'SUPPLIER_INFO_BACKUP' => array (
					'SupplierInfoBackup' => 'BACK_ID' 
			),
			'SUPPLIER_INFO_LOG' => array (
					'SupplierInfoLog' => 'LOG_ID' 
			),
			'SUPPLIER_FILE_ITEMS' => array (
					'SupplierFileItems' => 'FILE_ID' 
			),
			'SUPPLIER_ACCOUNT' => array (
					'SupplierAccount' => 'ACCOUNT_ID' 
			),
			'SUPPLIER_GOODS_BOM_VERSION' => array (
					'SupplierGoodsBomVersion' => 'GOODS_BOM_ID' 
			),
			'SUPPLIER_GOODS_BOM_PACKAGE' => array (
					'SupplierGoodsBomPackage' => 'PACK_ID' 
			),
			'PATCH_SUPPLIER_GOODS_PACKAGE' => array (
					'PatchSupplierGoodsPackage' => 'PACK_ID' 
			),
			'SUPPLIER_GOODS_BOM_PACK_TYPE' => array (
					'SupplierGoodsBomPackType' => 'TYPE_ID' 
			),
			'SUPPLIER_GOODS_BOM_FILE_ITEMS' => array (
					'SupplierGoodsBomFileItems' => 'BOM_FILE_ID' 
			),
			'SUPPLIER_GOODS_BOM_RELATION' => array (
					'SupplierGoodsBomRelation' => 'REL_ID' 
			),
			'MLL_SUPPLIER_RATING' => array (
					'MllSupplierRating' => 'ID' 
			),
			'PURCHASE_BATCH' => array (
					'PurchaseBatch' => 'PRODUCT_BATCH_NO' 
			),
			'CONTRACT_HEADER' => array (
					'ContractHeader' => 'CONTRACT_ID' 
			),
			'CONTRACT_ITEM' => array (
					'ContractItem' => 'ITEM_ID' 
			),
			'CONTRACT_FILES' => array (
					'ContractFiles' => 'FILE_ID' 
			),
			'CONTRACT_TEMPLATE' => array (
					'ContractTemplate' => 'TEMPLATE_ID' 
			),
			'CONTRACT_ADDITIONAL_RULE' => array (
					'ContractAdditionalRule' => 'ADD_ID' 
			),
			'PURCHASE_PROCESS' => array (
					'PurchaseProcess' => 'PROCESS_ID' 
			),
			'PRODUCT_PROCESS' => array (
					'ProductProcess' => 'PRODUCT_PROCESS_ID' 
			),
			'PRODUCT_PROCESS_DETAIL' => array (
					'ProductProcessDetail' => 'ITEMS_ID' 
			),
			'PRODUCT_PROCESS_LOG' => array (
					'ProductProcessLog' => 'SAVE_ID' 
			),
			'PRODUCT_PROCESS_DETAIL_LOG' => array (
					'ProductProcessDetailLog' => 'ITEMS_ID' 
			),
			'PRODUCT_PROCESS_ONLINE_QUANTITY_LOG' => array (
					'ProductProcessOnlineQuantityLog' => 'ID' 
			),
			'PROCESS_ORDER_ITEMS' => array (
					'ProcessOrderItems' => 'ITEM_SEQ_ID' 
			),
			'ORDER_PRODUCT_DELIVERY_PLAN' => array (
					'OrderProductDeliveryPlan' => 'PLAN_SEQ_ID' 
			),
			'ORDER_PRODUCT_DELIVERY_PLAN_TEST' => array (
					'OrderProductDeliveryPlanTest' => 'PLAN_SEQ_ID' 
			),
			'ORDER_FACTORY_PRODUCT_DELIVERY_PLAN' => array (
					'OrderFactoryProductDeliveryPlan' => 'PLAN_SEQ_ID' 
			),
			'AUDIT_NOTICE_HEADER' => array (
					'AuditNoticeHeader' => 'HEADER_ID' 
			),
			'AUDIT_NOTICE_PRODUCTS' => array (
					'AuditNoticeProducts' => 'NP_ID' 
			),
			'AUDIT_NOTICE_PRODUCTS_ITEM' => array (
					'AuditNoticeProductsItem' => 'ITEM_ID' 
			),
			'ORDER_CHANGE_RECORD' => array (
					'OrderChangeRecord' => 'ORDER_RECORD_ID' 
			),
			'OCEAN_FREIGHT_IMPORT_A' => array (
					'oceanFreightImportA' => 'ID' 
			),
			'OCEAN_FREIGHT_IMPORT_B' => array (
					'oceanFreightImportB' => 'ID' 
			),
			'OCEAN_FREIGHT_INVOICE' => array (
					'oceanFreightInvoice' => 'INVOICE_ID' 
			),
			'SUPPLIER_INSPECTION_REPORT_ITEMS' => array (
					'SupplierInspectionReportItems' => 'FILE_ID' 
			),
			'NOTICE_AND_INSPECTION_REPORT' => array (
					'NoticeAndInspectionReport' => 'ITEM_ID' 
			),
			'OCEAN_REPORT_ITEMS' => array (
					'OceanReportItems' => 'FILE_ID' 
			),
			'PURCHASE_V_A_T_INVOICE' => array (
					'PurchaseVATInvoice' => 'INVOICE_ID' 
			),
			'RECREATE_VAT_INVOICE_LOG' => array (
					'RecreateVatInvoiceLog' => 'LOG_ID' 
			),
			'PURCHASE_ORDER_CHANGE_LOG' => array (
					'PurchaseOrderChangeLog' => 'LOG_ID' 
			),
			'SHIPPING_BOOK_PLAN' => array (
					'ShippingBookPlan' => 'BOOK_ID' 
			),
			'SHIPPING_BOOK_PARAM' => array (
					'ShippingBookParam' => 'ORDER_ITEM_SEQ_ID' 
			),
			'PURCHASE_IN_STORAGE' => array (
					'PurchaseInStorage' => 'DELIVERY_ID' 
			),
			'PURCHASE_IN_STORAGE_DETAIL' => array (
					'PurchaseInStorageDetail' => 'DETAIL_ID' 
			),
			'PURCHASE_IN_STORAGE_FILE' => array (
					'PurchaseInStorageFile' => 'FILE_ID' 
			),
			'CONTRACT_PRODUCT_RELATION' => array (
					'ContractProductRelation' => 'RELATION_ID' 
			),
			'CONTRACT_BATCH' => array (
					'ContractBatch' => 'BATCH_NO' 
			),
			'SUBCON_STRUCTURE_DATA' => array (
					'SubconStructureData' => 'DATA_ID' 
			),
			'PRODUCT_LEVEL_TYPE' => array (
					'ProductLevelType' => 'PRODUCT_LEVEL' 
			),
			'ORDER_MONTH_PRODUCT_DELIVERY_PLAN' => array (
					'OrderMonthProductDeliveryPlan' => 'OMPDP_ID' 
			),
			'SUPPLIER_MATERIAL_ITEM' => array (
					'SupplierMaterialItem' => 'ITEM_ID' 
			),
			'MARKET_FACTORY' => array (
					'MarketFactory' => 'ID' 
			),
			'MARKET_FACTORY_MATERIAL_ITEM' => array (
					'MarketFactoryMaterialItem' => 'ITEM_ID' 
			),
			'INSPECTION_ITEM_FILES' => array (
					'InspectionItemFiles' => 'FILE_ID' 
			),
			'INSPECTION_ITEM' => array (
					'InspectionItem' => 'ISP_ID' 
			),
			'INSPECTION_AND_MATERIAL_ITEM' => array (
					'InspectionAndMaterialItem' => 'IM_ID' 
			),
			'PURCHASE_MATERIAL' => array (
					'PurchaseMaterial' => 'MT_ID' 
			),
			'PURCHASE_MATERIAL_ATTR' => array (
					'PurchaseMaterialAttr' => 'MTA_ID' 
			),
			'PURCHASE_MATERIAL_ATTR_ITEM' => array (
					'PurchaseMaterialAttrItem' => 'ITEM_ID' 
			),
			'PRODUCT_MATERIAL' => array (
					'ProductMaterial' => 'PM_ID' 
			),
			'PRODUCT_MATERIAL_DETAIL' => array (
					'ProductMaterialDetail' => 'ITEMS_ID' 
			),
			'MATERIAL_ORDER_ITEMS' => array (
					'MaterialOrderItems' => 'ITEM_SEQ_ID' 
			),
			'PROCESS_ORDER_REMARK' => array (
					'ProcessOrderRemark' => 'ITEM_ID' 
			),
			'IMPORTED_CONTRACT_ORDER' => array (
					'ImportedContractOrder' => 'ITEM_ID' 
			),
			'IMPORTED_AUTO_CONTRACT_ORDER' => array (
					'ImportedAutoContractOrder' => 'ITEM_ID' 
			),
			'PURCHASE_STORAGE_CAPACITY' => array (
					'PurchaseStorageCapacity' => 'WAREHOUSE_ID' 
			),
			'FACILITY_TEAM_ROLE_SECURITY' => array (
					'FacilityTeamRoleSecurity' => 'SECURITY_GROUP_ID' 
			),
			'WAREHOUSE_SUMMARY_DATA' => array (
					'WarehouseSummaryData' => 'FACILITY_ID' 
			),
			'WAREHOUSE_SUMMARY_PICK_MOVE' => array (
					'WarehouseSummaryPickMove' => 'WSPICKMOVE_ID' 
			),
			'INVENTORY_ITEM_TRACE' => array (
					'InventoryItemTrace' => 'INVENTORY_ITEM_TRACE_ID' 
			),
			'INVENTORY_ITEM_USAGE_TYPE' => array (
					'InventoryItemUsageType' => 'INVENTORY_ITEM_USAGE_TYPE_ID' 
			),
			'ALLOCATE_DIRECT' => array (
					'AllocateDirect' => 'ALLOCATE_DIRECT_ID' 
			),
			'EBAY_CONFIG' => array (
					'EbayConfig' => 'PRODUCT_STORE_ID' 
			),
			'EBAY_PRODUCT_LISTING' => array (
					'EbayProductListing' => 'PRODUCT_LISTING_ID' 
			),
			'GOOGLE_BASE_CONFIG' => array (
					'GoogleBaseConfig' => 'PRODUCT_STORE_ID' 
			),
			'GOOGLE_CO_CONFIGURATION' => array (
					'GoogleCoConfiguration' => 'PRODUCT_STORE_ID' 
			),
			'POS_TERMINAL' => array (
					'PosTerminal' => 'POS_TERMINAL_ID' 
			),
			'POS_TERMINAL_LOG' => array (
					'PosTerminalLog' => 'POS_TERMINAL_LOG_ID' 
			),
			'POS_TERMINAL_INTERN_TX' => array (
					'PosTerminalInternTx' => 'POS_TERMINAL_LOG_ID' 
			),
			'WF_ASSIGNMENT_EVENT_AUDIT' => array (
					'WfAssignmentEventAudit' => 'EVENT_AUDIT_ID' 
			),
			'WF_CREATE_PROCESS_EVENT_AUDIT' => array (
					'WfCreateProcessEventAudit' => 'EVENT_AUDIT_ID' 
			),
			'WF_DATA_EVENT_AUDIT' => array (
					'WfDataEventAudit' => 'EVENT_AUDIT_ID' 
			),
			'WF_EVENT_AUDIT' => array (
					'WfEventAudit' => 'EVENT_AUDIT_ID' 
			),
			'WF_STATE_EVENT_AUDIT' => array (
					'WfStateEventAudit' => 'EVENT_AUDIT_ID' 
			),
			'SHARK_GROUP' => array (
					'SharkGroup' => 'GROUP_NAME' 
			),
			'SHARK_USER' => array (
					'SharkUser' => 'USER_NAME' 
			),
			'WF_ACTIVITY' => array (
					'WfActivity' => 'ACTIVITY_ID' 
			),
			'WF_ACTIVITY_VARIABLE' => array (
					'WfActivityVariable' => 'ACTIVITY_VARIABLE_ID' 
			),
			'WF_AND_JOIN' => array (
					'WfAndJoin' => 'AND_JOIN_ID' 
			),
			'WF_DEADLINE' => array (
					'WfDeadline' => 'DEADLINE_ID' 
			),
			'WF_PROCESS' => array (
					'WfProcess' => 'PROCESS_ID' 
			),
			'WF_PROCESS_MGR' => array (
					'WfProcessMgr' => 'MGR_NAME' 
			),
			'WF_PROCESS_VARIABLE' => array (
					'WfProcessVariable' => 'PROCESS_VARIABLE_ID' 
			),
			'WF_RESOURCE' => array (
					'WfResource' => 'USER_NAME' 
			),
			'WF_PARTICIPANT_MAP' => array (
					'WfParticipantMap' => 'PARTICIPANT_MAP_ID' 
			),
			'WORKFLOW_COMPLEX_TYPE_INFO' => array (
					'WorkflowComplexTypeInfo' => 'COMPLEX_TYPE_INFO_ID' 
			),
			'WORKFLOW_PARTICIPANT_TYPE' => array (
					'WorkflowParticipantType' => 'PARTICIPANT_TYPE_ID' 
			),
			'WORKFLOW_SPECIFICATION' => array (
					'WorkflowSpecification' => 'SPECIFICATION_ID' 
			) 
	);
	
	/**
	 * 获取ERP表的下一个可用的主键ID（失败会自动尝试3次）
	 *
	 * @param string $ERP_real_tablename        	
	 * @param string $error
	 *        	错误信息提示会被写入该传入的变量
	 * @return 下一个可用于插入的存储主键ID， -1 表示不支持该表或未获得
	 */
	public static function getERPTableNextPrimaryId($ERP_real_tablename, &$error = '') {
		if (empty ( self::$ERP_table_list [$ERP_real_tablename] )) {
			$error = '不支持该表';
			return - 1;
		}
		$seq = key ( self::$ERP_table_list [$ERP_real_tablename] );
		$next_id = self::_get_next_id ( $seq, $error );
		return $next_id;
	}
	
	/**
	 * 获取上一个生成的主键ID
	 */
	public static function getLastInsertId() {
		return self::$last_insert_id;
	}
	
	/**
	 *
	 *
	 * 自动检测并设置ERP表可以用于插入的主键值
	 *
	 * @param string $service_name
	 *        	| 表名，用于检测是否为ERP序列主键表
	 * @param string $data
	 *        	(ref) | 插入时的字段参数， 如果为ERP需要获取序列主键的表，则会自动设置主键值
	 * @param string $type
	 *        	| 操作类型 仅支持 insert, batch_insert
	 * @param string $error
	 *        	(ref) | 错误消息会被设置在这个变量
	 * @return boolean | true if no error occur, false otherwise
	 */
	public static function detect_and_handle_ERP_table($service_name, &$data, $type, &$error = '') {
		if (isset ( self::$ERP_table_list [$service_name] )) {
			$rule_name = self::$NEXT_ID_RULE_NAME;
			$seq = key ( self::$ERP_table_list [$service_name] );
			$pk = self::$ERP_table_list [$service_name] [$seq];
			if (strtolower ( $type ) == 'batch_insert') {
				if (! is_array ( $data ) || ! is_array ( $data [0] )) {
					$error .= "参数格式不正确，请检查  \$data ";
					return false;
				}
				foreach ( $data as &$inner_data ) {
					$next_id = self::_get_next_id ( $seq, $error );
					if (empty ( $next_id ) || $next_id == - 1) {
						$error .= " 自动设置ERP表主键时，规则$rule_name返回异常 ";
						return false;
					} else {
						// 通过规则获取到ERP 的表的可用主键ID，设置到参数中
						if (empty ( $inner_data [$pk] )) {
							$inner_data [$pk] = $next_id;
						}
					}
				}
			} else if (strtolower ( $type ) == 'insert') {
				$next_id = self::_get_next_id ( $seq, $error );
				if (empty ( $next_id ) || $next_id == - 1) {
					$error .= " 自动设置ERP表主键时，规则$rule_name返回异常 ";
					return false;
				} else {
					// 通过规则获取到ERP 的表的可用主键ID，设置到参数中
					if (empty ( $data [$pk] )) {
						$data [$pk] = $next_id;
					}
				}
			} else {
				; // not surpoted type, do nothing
			}
		}
		return true;
	}
	
	/**
	 *
	 * @param string $service_name
	 *        	| table name
	 * @param string $error        	
	 * @return next available id for this table
	 */
	private static function _get_next_id($service_name, &$error = '') {
		$try_time = 2;
		$next_id = - 1;
		$rule_name = self::$NEXT_ID_RULE_NAME;
		
		// TODO
		$param = array (
				'table_name' => $service_name,
				'step' => self::$PK_INCREASE_STEP 
		);
		do {
			$rule_res = mainUniRE ( $rule_name, $param );
			if (empty ( $rule_res )) {
				$error .= "规则$rule_name返回为空";
			}
			$next_id = $rule_res ['msg'] [0] ['curr_val'];
			if (isset ( $next_id ) && $next_id != - 1) {
				break;
			}
			usleep(mt_rand(1000, 50000));
		} while ( 0 < $try_time -- );
		if (empty ( $next_id ) || $next_id == - 1) {
			$error = "未能获取有效ID：" . var_export ( array (
					$rule_res,
					$param 
			), true );
		}
		self::$last_insert_id = $next_id;
		return $next_id;
	}
}
