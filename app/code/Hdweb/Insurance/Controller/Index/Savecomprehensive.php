<?php
/**
 * @category   Hdweb
 * @package    Hdweb_Insurance
 * @author     vicky.hdit@gmail.com
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Hdweb\Insurance\Controller\Index;

use Magento\Framework\App\Action\Context;
use Hdweb\Insurance\Model\InsuranceFactory;

class Savecomprehensive extends \Magento\Framework\App\Action\Action
{
  /**
     * @var Insurance
     */
    protected $_insurance;
    protected $resultJsonFactory;
    protected $insuranceHelper;
    protected $insuranceModel;

    public function __construct(
    Context $context,
        InsuranceFactory $insurance,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Hdweb\Insurance\Helper\Data $insuranceHelper,
        \Hdweb\Insurance\Model\Insurance $insuranceModel
    ) {
        $this->_insurance = $insurance;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->insuranceHelper    = $insuranceHelper;
        $this->insuranceModel    = $insuranceModel;
        parent::__construct($context);
    }
    public function execute()
    {
        $apiCallBaseUrl = $this->insuranceHelper->getAPIcallBaseUrl();
        $data = $this->getRequest()->getParams();
        if($data['step'] == 'car_details'){
            $insurance = $this->_insurance->create();
            $insurance->setMake($data['car_make']);
            $insurance->setModel($data['car_model']);
            $insurance->setPolicyType($data['policy_type']);
            $dobDay = sprintf("%02d", $data['dob']['day']);
            $dob = $dobDay.'-'.$data['dob']['month'].'-'.$data['dob']['year'];
            $req_param_quote = [
             "policy_type" => $data['policy_type'],
             /*"license_start_date" => $data['license_start_date'],*/
             "any_claim" => $data['three_years_claim'],
             "date_of_birth" => $dob, 
             /*"nationality_id" => $data['nationality'], */
             "car" => [
                   "car_type" => $data['car_type'],
                   "year" => $data['car_model_year'], 
                   "make" => $data['car_make'], 
                   "model" => $data['car_model'], 
                   "trim" => $data['car_trim'], 
                   "body_type" => $data['car_body_type'], 
                   "engine_size" => $data['car_engine_size'], 
                   "transmission" => $data['car_transmission'], 
                   "region" => $data['car_region'], 
                   /*"is_brand_new" => $data['is_brand_new'], */
                   "car_value" => $data['car_value'],
                   /*"is_mortgage" => $data['is_mortgage'], */
                   "date_of_registration" => $data['date_of_registration'] 
                ] 
            ];

            $authResponse = $this->insuranceHelper->insuranceAuth();
            $authResponseArr = json_decode($authResponse);

            $access_token = $authResponseArr->response->access_token;
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => $apiCallBaseUrl.'api/v2/car/quote',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($req_param_quote),
            CURLOPT_HTTPHEADER => array(
              'Accept: application/json',
              'Authorization: Bearer '.$access_token,
              'Content-Type: application/json'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $res_param_quote = $response;
            $res_param_quote_data = json_decode($res_param_quote);
            $quoteId = $res_param_quote_data->response->quote_id;

            $step2HtmlAgency = '';
            if(isset($res_param_quote_data->response->plans->agency)){
              $agencyInsuranceCount = count($res_param_quote_data->response->plans->agency);
              if($agencyInsuranceCount > 0){
                foreach ($res_param_quote_data->response->plans->agency as $key => $agencyPlan) {
                    $agencyCoverBenefits = '';
                    $agencyBenefits = array_slice($agencyPlan->covers_benefits, 0, 2);
                    foreach ($agencyBenefits as $key => $agencyBenefit) {
                      if($agencyBenefit->include == 'yes'){
                        $agencyFaClassFor2 = 'fa fa-check';
                      } else {
                        $agencyFaClassFor2 = 'fa fa-close';
                      }
                      $agencyCoverBenefits .= '<p><i class="'.$agencyFaClassFor2.'" aria-hidden="true"></i>'.$agencyBenefit->name.'</p>'
                                        ;
                    }
                    $agencyAllBenefits = '';
                    foreach ($agencyPlan->covers_benefits as $agencyBenefitData) {
                      if($agencyBenefitData->include == 'yes'){
                        $agencyFaClass = 'fa fa-check';
                      } else {
                        $agencyFaClass = 'fa fa-close';
                      }
                      $agencyAllBenefits .= '<div class="list-mf"><i class="'.$agencyFaClass.'" aria-hidden="true"></i>
                                        <span>'.$agencyBenefitData->name.'</span>
                                        <p>'.$agencyBenefitData->description.'</p>
                                      </div>';
                    }

                    $agencyPopupLink = '<a class="" data-toggle="modal" data-target="#planModal'.$agencyPlan->id.'">
                                View all benefits
                              </a>';
                    $agencyPopup = '<div class="modal fade" id="planModal'.$agencyPlan->id.'" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                  <div class="modal-content">
                                    <div class="modal-header">
                                      <h5 class="modal-title">View plan details</h5>
                                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                      </button>
                                    </div>
                                    <div class="modal-body">
                                        
                                        <div class="list-mf-head">
                                          <h3>'.$agencyPlan->insurer->name.'</h3>
                                          <p>Insurer: '.$agencyPlan->insurer->name.'</p>
                                          <p>Plan type: '.$agencyPlan->name.'</p>
                                          <p>3rd Party Property Liability: '.$agencyPlan->third_party_liability.'</p>
                                        </div>
                                        '.$agencyAllBenefits.'
                                        <div class="list-mf"><i class="fa fa-check" aria-hidden="true"></i>
                                          <span>Terms & Conditions</span>
                                          <p><a href="'.$agencyPlan->terms_conditions->file_url.'" target="_blank" download>'.$agencyPlan->terms_conditions->description.'</a></p>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                              </div>';
                    $step2HtmlAgency .= '<div class="list-box"><div class="row">
                                <div class="col-sm-3 col-xs-12">
                                <div class="name">
                                  <img src="'.$agencyPlan->insurer->logo.'" class="plan-logo" alt="'.$agencyPlan->insurer->name.'">
                                  <span>'.$agencyPlan->insurer->name.'</span>
                                  <h4>'.$agencyPlan->name.'</h4>
                                </div>
                                </div>
                                <div class="col-sm-4 col-xs-12">
                                <div class="info">
                                  <p>3rd Party Property Liability: '.$agencyPlan->third_party_liability.' AED</p>
                                  '.$agencyCoverBenefits.'
                                  '.$agencyPopupLink.'
                                  '.$agencyPopup.'
                                </div>
                                </div>
                                <div class="col-sm-2 col-xs-12">
                                <div class="price">
                                  <h4>'.number_format((float)$agencyPlan->premium, 2, '.', '').' '.$agencyPlan->currency.'</h4>
                                  <span>+ VAT: '.number_format((float)$agencyPlan->vat, 2, '.', '').' '.$agencyPlan->currency.'</span>
                                  <span> TOTAL: '.number_format((float)$agencyPlan->plan_price, 2, '.', '').' '.$agencyPlan->currency.'</span>
                                </div>
                                </div>
                                <div class="col-sm-3 col-xs-12">
                                <div class="addto">
                                  <a href="javascript:void(0);" class="planContinue btn btn-primary" data-quoteid="'.$quoteId.'" data-planid="'.$agencyPlan->id.'" data-type="agency">Continue</a>
                                </div>
                                <div class="policy-issuance"><p>'.__("Policy issuance").': <span>'.__("2-4 working hours").'</span></p> <div class="policy-info" data-toggle="tooltip" title="'.__("Assuming all information you provided is correct, and you made the payment, we will issue the policy within the stated interval.").'"><span><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm169.8-90.7c7.9-22.3 29.1-37.3 52.8-37.3h58.3c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24V250.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1H222.6c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/></svg></span></div></div>
                                </div></div></div>
                              ';
                }
              }
            } else {
              $agencyInsuranceCount = 0;
            }

            $step2HtmlNonAgency = '';
            if(isset($res_param_quote_data->response->plans->non_agency)){
              $nonAgencyInsuranceCount = count($res_param_quote_data->response->plans->non_agency);
              if($nonAgencyInsuranceCount > 0){
                foreach ($res_param_quote_data->response->plans->non_agency as $key => $nonAgencyPlan) {
                    $nonAgencyCoverBenefits = '';
                    $nonAgencyBenefits = array_slice($nonAgencyPlan->covers_benefits, 0, 2);
                    foreach ($nonAgencyBenefits as $key => $nonAgencyBenefit) {
                      if($nonAgencyBenefit->include == 'yes'){
                        $nonAgencyFaClassFor2 = 'fa fa-check';
                      } else {
                        $nonAgencyFaClassFor2 = 'fa fa-close';
                      }
                      $nonAgencyCoverBenefits .= '<p><i class="'.$nonAgencyFaClassFor2.'" aria-hidden="true"></i>'.$nonAgencyBenefit->name.'</p>'
                                        ;
                    }
                    $nonAgencyAllBenefits = '';
                    foreach ($nonAgencyPlan->covers_benefits as $nonAgencyBenefitData) {
                      if($nonAgencyBenefitData->include == 'yes'){
                        $nonAgencyFaClass = 'fa fa-check';
                      } else {
                        $nonAgencyFaClass = 'fa fa-close';
                      }
                      $nonAgencyAllBenefits .= '<div class="list-mf"><i class="'.$nonAgencyFaClass.'" aria-hidden="true"></i>
                                        <span>'.$nonAgencyBenefitData->name.'</span>
                                        <p>'.$nonAgencyBenefitData->description.'</p>
                                      </div>';
                    }

                    $nonAgencyPopupLink = '<a class="" data-toggle="modal" data-target="#planModal'.$nonAgencyPlan->id.'">
                                View all benefits
                              </a>';
                    $nonAgencyPopup = '<div class="modal fade" id="planModal'.$nonAgencyPlan->id.'" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                  <div class="modal-content">
                                    <div class="modal-header">
                                      <h5 class="modal-title">View plan details</h5>
                                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                      </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="list-mf-head">
                                        <h3>'.$nonAgencyPlan->insurer->name.'</h3>
                                          <p>Insurer: '.$nonAgencyPlan->insurer->name.'</p>
                                          <p>Plan type: '.$nonAgencyPlan->name.'</p>
                                          <p>3rd Party Property Liability: '.$nonAgencyPlan->third_party_liability.'</p>
                                        </div>
                                        '.$nonAgencyAllBenefits.'
                                        <div class="list-mf"><i class="fa fa-check" aria-hidden="true"></i>
                                          <span>Terms & Conditions</span>
                                          <p><a href="'.$nonAgencyPlan->terms_conditions->file_url.'" target="_blank" download>'.$nonAgencyPlan->terms_conditions->description.'</a></p>
                                        </div>
                                      </div>
                                  </div>
                                </div>
                              </div>';
                    $step2HtmlNonAgency .= '
                                <div class="list-box"><div class="row"><div class="col-sm-3 col-xs-12">
                                  <div class="name">
                                  <img src="'.$nonAgencyPlan->insurer->logo.'" class="plan-logo" alt="'.$nonAgencyPlan->insurer->name.'">
                                  <span>'.$nonAgencyPlan->insurer->name.'</span>
                                  <h4>'.$nonAgencyPlan->name.'</h4>
                                  </div>
                                </div>
                                <div class="col-sm-4 col-xs-12">
                                 <div class="info">
                                  <p>3rd Party Property Liability: <strong>'.$nonAgencyPlan->third_party_liability.'</strong> AED</p>
                                  '.$nonAgencyCoverBenefits.'
                                  '.$nonAgencyPopupLink.'
                                  '.$nonAgencyPopup.'
                                </div>
                                </div>
                                <div class="col-sm-2 col-xs-12">
                                 <div class="price">
                                  <h4>'.number_format((float)$nonAgencyPlan->premium, 2, '.', '').' '.$nonAgencyPlan->currency.'</h4>
                                  <span>+ VAT: '.number_format((float)$nonAgencyPlan->vat, 2, '.', '').' '.$nonAgencyPlan->currency.'</span>
                                  <span> TOTAL: '.number_format((float)$nonAgencyPlan->plan_price, 2, '.', '').' '.$nonAgencyPlan->currency.'</span>
                                </div>
                                </div>
                                <div class="col-sm-3 col-xs-12">
                                 <div class="addto">
                                  <a href="javascript:void(0);" class="planContinue btn btn-primary" data-quoteid="'.$quoteId.'" data-planid="'.$nonAgencyPlan->id.'" data-type="non-agency">Continue</a>
                                  </div>
                                  <div class="policy-issuance"><p>'.__("Policy issuance").': <span>'.__("2-4 working hours").'</span></p> <div class="policy-info" data-toggle="tooltip" title="'.__("Assuming all information you provided is correct, and you made the payment, we will issue the policy within the stated interval.").'"><span><svg xmlns="http://www.w3.org/2000/svg" height="1em" viewBox="0 0 512 512"><path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm169.8-90.7c7.9-22.3 29.1-37.3 52.8-37.3h58.3c34.9 0 63.1 28.3 63.1 63.1c0 22.6-12.1 43.5-31.7 54.8L280 264.4c-.2 13-10.9 23.6-24 23.6c-13.3 0-24-10.7-24-24V250.5c0-8.6 4.6-16.5 12.1-20.8l44.3-25.4c4.7-2.7 7.6-7.7 7.6-13.1c0-8.4-6.8-15.1-15.1-15.1H222.6c-3.4 0-6.4 2.1-7.5 5.3l-.4 1.2c-4.4 12.5-18.2 19-30.6 14.6s-19-18.2-14.6-30.6l.4-1.2zM224 352a32 32 0 1 1 64 0 32 32 0 1 1 -64 0z"/></svg></span></div></div>
                                </div></div></div>
                              ';
                }
              }
            } else {
              $nonAgencyInsuranceCount = 0;
            }

            $insuranceCount = $agencyInsuranceCount + $nonAgencyInsuranceCount;

            $insurance->setQuoteId($quoteId);
            $insurance->setRequestParameterQuote(json_encode($req_param_quote));
            $insurance->setResponseParameterQuote($res_param_quote);
            $insurance->save();

            $lastSavedId = $insurance->getInsuranceId();

            $response = [
                    'status' => 200,
                    'car details' => 'Car details step done',
                    'step2HtmlAgency' => $step2HtmlAgency,
                    'step2HtmlNonAgency' => $step2HtmlNonAgency,
                    'insurance_count' => $insuranceCount,
                    'selected_year' => $data['car_model_year'],
                    'selected_make' => $data['car_make'],
                    'selected_model' => $data['car_model'],
                    'lastsavedid' => $lastSavedId,
                    'car_value' => $data['car_value'],
                    'agency_insurance_count' => $agencyInsuranceCount,
                    'non_agency_insurance_count' => $nonAgencyInsuranceCount
            ];
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        }

        if($data['step'] == 'application_form'){
          $licenseFrontTmpPath = $_FILES['driving_license_front']['tmp_name'];
          $licenseFrontData = file_get_contents($licenseFrontTmpPath);
          $licenseFrontBase64 = base64_encode($licenseFrontData);

          $licenseBackTmpPath = $_FILES['driving_license_back']['tmp_name'];
          $licenseBackData = file_get_contents($licenseBackTmpPath);
          $licenseBackBase64 = base64_encode($licenseBackData);

          $registrationFrontTmpPath = $_FILES['vehicle_registration_mulkiya_front']['tmp_name'];
          $registrationFrontData = file_get_contents($registrationFrontTmpPath);
          $registrationFrontBase64 = base64_encode($registrationFrontData);

          $registrationBackTmpPath = $_FILES['vehicle_registration_mulkiya_back']['tmp_name'];
          $registrationBackData = file_get_contents($registrationBackTmpPath);
          $registrationBackBase64 = base64_encode($registrationBackData);

          /*$emiratesIdTmpPath = $_FILES['emirates_id']['tmp_name'];
          $emiratesIdData = file_get_contents($emiratesIdTmpPath);
          $emiratesIdBase64 = base64_encode($emiratesIdData);*/

          $emiratesIdFrontTmpPath = $_FILES['emirates_id_front']['tmp_name'];
          $emiratesIdFrontData = file_get_contents($emiratesIdFrontTmpPath);
          $emiratesIdFrontBase64 = base64_encode($emiratesIdFrontData);

          $emiratesIdBackTmpPath = $_FILES['emirates_id_back']['tmp_name'];
          $emiratesIdBackData = file_get_contents($emiratesIdBackTmpPath);
          $emiratesIdBackBase64 = base64_encode($emiratesIdBackData);

          if($data['car_company_type'] == 'private'){
            $carDetail = 1;
          } else {
            $carDetail = 2;
          }

          if(isset($data['company_name'])){
            $companyName = $data['company_name'];
          } else {
            $companyName = '';
          }

          $req_param_application = [
             "quote_id" => $data['quote_id'], 
             "plan_id" => $data['plan_id'],
             "type" => $data['type'], 
             "policy_start_date" => $data['policy_start_date'], 
             "car_detail" => $carDetail, 
             "company_name" => $companyName, 
             "client_info" => [
                   "first_name" => $data['first_name'], 
                   "family_name" => $data['family_name'], 
                   "phone" => '+971'.$data['application_phone'], 
                   "email" => $data['email'], 
                   /*"traffic_number" => $data['traffic_number'], 
                   "license_number" => $data['license_number'], 
                   "license_expiry_date" => $data['license_expiry_date'],*/ 
                ], 
             "documents" => [
                      [
                         "type" => "driving-license-front", 
                         "file" => $licenseFrontBase64
                      ], 
                      [
                         "type" => "driving-license-back", 
                         "file" => $licenseBackBase64
                      ],
                      [
                         "type" => "vehicle-registration-front", 
                         "file" => $registrationFrontBase64
                      ],
                      [
                         "type" => "vehicle-registration-back", 
                         "file" => $registrationBackBase64
                      ],
                      [
                         "type" => "emirates-id-front", 
                         "file" => $emiratesIdFrontBase64
                      ],
                      [
                         "type" => "emirates-id-back", 
                         "file" => $emiratesIdBackBase64
                      ] 
                   ] 
          ];

          $req_param_application_custom = [
             "quote_id" => $data['quote_id'], 
             "plan_id" => $data['plan_id'], 
             "type" => $data['type'],
             "policy_start_date" => $data['policy_start_date'], 
             "car_detail" => $carDetail, 
             "company_name" => $companyName, 
             "client_info" => [
                   "first_name" => $data['first_name'], 
                   "family_name" => $data['family_name'], 
                   "phone" => '+971'.$data['application_phone'], 
                   "email" => $data['email'], 
                   /*"traffic_number" => $data['traffic_number'], 
                   "license_number" => $data['license_number'], 
                   "license_expiry_date" => $data['license_expiry_date'],*/ 
                ], 
             "documents" => [
                      [
                         "type" => "driving-license-front", 
                         "file" => "base64"
                      ], 
                      [
                         "type" => "driving-license-back", 
                         "file" => "base64"
                      ],
                      [
                         "type" => "vehicle-registration-front", 
                         "file" => "base64"
                      ],
                      [
                         "type" => "vehicle-registration-back", 
                         "file" => "base64"
                      ],
                      [
                         "type" => "emirates-id-front", 
                         "file" => "base64"
                      ],
                      [
                         "type" => "emirates-id-back", 
                         "file" => "base64"
                      ] 
                   ] 
          ];
          
          $authResponse = $this->insuranceHelper->insuranceAuth();
          $authResponseArr = json_decode($authResponse);

          $access_token = $authResponseArr->response->access_token;
          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL => $apiCallBaseUrl.'api/v2/car/application',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($req_param_application),
            CURLOPT_HTTPHEADER => array(
              'Accept: application/json',
              'Authorization: Bearer '.$access_token,
              'Content-Type: application/json'
            ),
          ));

          $response = curl_exec($curl);
          curl_close($curl);

          $res_param_application = $response;

          $insuranceModel = $this->insuranceModel->load($data['id']);
          $insuranceModel->setCompanyName($data['company_name']);
          $insuranceModel->setFirstName($data['first_name']);
          $insuranceModel->setFamilyName($data['family_name']);
          $insuranceModel->setPhone($data['application_phone']);
          $insuranceModel->setEmail($data['email']);
          $insuranceModel->setRequestParameterApplication(json_encode($req_param_application_custom));
          $insuranceModel->setResponseParameterApplication($res_param_application);
          $insuranceModel->save();

          $res_param_application_data = json_decode($res_param_application);
          $applicationId = $res_param_application_data->response->application_id;



          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL => $apiCallBaseUrl.'api/v2/car/payment-url',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
              "application_id": "'.$applicationId.'"
          }',
            CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$access_token
          ),
          ));

          $paymentUrlResponse = curl_exec($curl);

          curl_close($curl);

          $paymentUrlResponseArr = json_decode($paymentUrlResponse);
          $redirectUrl = $paymentUrlResponseArr->response->redirect_url;
          





          $response = [
                    'status' => 200,
                    'application_id' => $applicationId,
                    'redirect_url' => $redirectUrl
          ];
          $resultJson = $this->resultJsonFactory->create();
          return $resultJson->setData($response);
        }
    }
}
