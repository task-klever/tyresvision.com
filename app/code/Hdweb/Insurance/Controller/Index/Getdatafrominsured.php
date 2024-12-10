<?php

namespace Hdweb\Insurance\Controller\Index;

class Getdatafrominsured extends \Magento\Framework\App\Action\Action
{

	 public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Hdweb\Insurance\Helper\Data $insuranceHelper
	) {
		$this->resultJsonFactory 	= $resultJsonFactory;
        $this->insuranceHelper    = $insuranceHelper;
    	parent::__construct($context);
    }

	public function execute()
    {
        $apiCallBaseUrl = $this->insuranceHelper->getAPIcallBaseUrl();
    	$type = $this->getRequest()->getParam('type');

    	if($type == 'getmake'){
    		$year = $this->getRequest()->getParam('year');

    		$getMakeUrl = $apiCallBaseUrl.'api/v2/master/car/data/make/'.$year;
            $getMakeResult = $this->insuranceHelper->getDataFromAPI($getMakeUrl);
			$getMakeResultArray = json_decode($getMakeResult);
			$makes = $getMakeResultArray->response;

			$optionsHtml = '';
			foreach ($makes as $make) {
				$optionsHtml .= '<option value="'.$make->Name.'">'.$make->Name.'</option>';
			}
			$resultJson = $this->resultJsonFactory->create();
			return $resultJson->setData($optionsHtml);
    	}

    	if($type == 'getmodel'){
    		$make = $this->getRequest()->getParam('make');
    		$year = $this->getRequest()->getParam('year');
    		$make = str_replace(' ', '%20', $make);
    		$getModelUrl = $apiCallBaseUrl.'api/v2/master/car/data/model/'.$year.'/'.$make;
            $getModelResult = $this->insuranceHelper->getDataFromAPI($getModelUrl);
			$getModelResultArray = json_decode($getModelResult);
			$models = $getModelResultArray->response;

			$optionsHtml = '';
			foreach ($models as $model) {
				$optionsHtml .= '<option value="'.$model->Name.'">'.$model->Name.'</option>';
			}
			$resultJson = $this->resultJsonFactory->create();
			return $resultJson->setData($optionsHtml);
    	}

    	if($type == 'gettrim'){
    		$make = $this->getRequest()->getParam('make');
    		$year = $this->getRequest()->getParam('year');
    		$model = $this->getRequest()->getParam('model');
    		$make = str_replace(' ', '%20', $make);
    		$model = str_replace(' ', '%20', $model);
    		$getTrimUrl = $apiCallBaseUrl.'api/v2/master/car/data/trim/'.$year.'/'.$make.'/'.$model;
            $getTrimResult = $this->insuranceHelper->getDataFromAPI($getTrimUrl);
			$getTrimResultArray = json_decode($getTrimResult);
			$trims = $getTrimResultArray->response;

			$optionsHtml = '';
			foreach ($trims as $trim) {
				$optionsHtml .= '<option value="'.$trim->Name.'">'.$trim->Name.'</option>';
			}
			$resultJson = $this->resultJsonFactory->create();
			return $resultJson->setData($optionsHtml);
    	}

    	if($type == 'bodytype'){
    		$make = $this->getRequest()->getParam('make');
    		$year = $this->getRequest()->getParam('year');
    		$model = $this->getRequest()->getParam('model');
    		$trim = $this->getRequest()->getParam('trim');
    		$make = str_replace(' ', '%20', $make);
    		$model = str_replace(' ', '%20', $model);
    		$trim = str_replace(' ', '%20', $trim);
    		$getBodyTypeUrl = $apiCallBaseUrl.'api/v2/master/car/data/body_type/'.$year.'/'.$make.'/'.$model.'/'.$trim;
            $getBodyTypeResult = $this->insuranceHelper->getDataFromAPI($getBodyTypeUrl);
			$getBodyTypeResultArray = json_decode($getBodyTypeResult);
			$bodyTypes = $getBodyTypeResultArray->response;

			$optionsHtml = '';
			foreach ($bodyTypes as $bodyType) {
				$optionsHtml .= '<option value="'.$bodyType->Name.'">'.$bodyType->Name.'</option>';
			}
			$resultJson = $this->resultJsonFactory->create();
			return $resultJson->setData($optionsHtml);
    	}

    	if($type == 'enginesize'){
    		$make = $this->getRequest()->getParam('make');
    		$year = $this->getRequest()->getParam('year');
    		$model = $this->getRequest()->getParam('model');
    		$trim = $this->getRequest()->getParam('trim');
    		$bodytype = $this->getRequest()->getParam('bodytype');
    		$make = str_replace(' ', '%20', $make);
    		$model = str_replace(' ', '%20', $model);
    		$trim = str_replace(' ', '%20', $trim);
    		$bodytype = str_replace(' ', '%20', $bodytype);
    		$getEngineSizeUrl = $apiCallBaseUrl.'api/v2/master/car/data/engine_size/'.$year.'/'.$make.'/'.$model.'/'.$trim.'/'.$bodytype;
            $getEngineSizeResult = $this->insuranceHelper->getDataFromAPI($getEngineSizeUrl);
			$getEngineSizeResultArray = json_decode($getEngineSizeResult);
			$engineSizes = $getEngineSizeResultArray->response;

			$optionsHtml = '';
			foreach ($engineSizes as $engineSize) {
				$optionsHtml .= '<option value="'.$engineSize->Name.'">'.$engineSize->Name.'</option>';
			}
			$resultJson = $this->resultJsonFactory->create();
			return $resultJson->setData($optionsHtml);
    	}

    	if($type == 'gettransmission'){
    		$make = $this->getRequest()->getParam('make');
    		$year = $this->getRequest()->getParam('year');
    		$model = $this->getRequest()->getParam('model');
    		$trim = $this->getRequest()->getParam('trim');
    		$bodytype = $this->getRequest()->getParam('bodytype');
    		$enginesize = $this->getRequest()->getParam('enginesize');
    		$make = str_replace(' ', '%20', $make);
    		$model = str_replace(' ', '%20', $model);
    		$trim = str_replace(' ', '%20', $trim);
    		$bodytype = str_replace(' ', '%20', $bodytype);
    		$enginesize = str_replace(' ', '%20', $enginesize);
    		$getTransmissionUrl = $apiCallBaseUrl.'api/v2/master/car/data/transmission/'.$year.'/'.$make.'/'.$model.'/'.$trim.'/'.$bodytype.'/'.$enginesize;
            $getTransmissionResult = $this->insuranceHelper->getDataFromAPI($getTransmissionUrl);
			$getTransmissionResultArray = json_decode($getTransmissionResult);
			$transmissions = $getTransmissionResultArray->response;

			$optionsHtml = '';
			foreach ($transmissions as $transmission) {
				$optionsHtml .= '<option value="'.$transmission->Name.'">'.$transmission->Name.'</option>';
			}
			$resultJson = $this->resultJsonFactory->create();
			return $resultJson->setData($optionsHtml);
    	}
        
        if($type == 'getregion'){
    		$make = $this->getRequest()->getParam('make');
    		$year = $this->getRequest()->getParam('year');
    		$model = $this->getRequest()->getParam('model');
    		$trim = $this->getRequest()->getParam('trim');
    		$bodytype = $this->getRequest()->getParam('bodytype');
    		$enginesize = $this->getRequest()->getParam('enginesize');
    		$transmission = $this->getRequest()->getParam('transmission');
    		$make = str_replace(' ', '%20', $make);
    		$model = str_replace(' ', '%20', $model);
    		$trim = str_replace(' ', '%20', $trim);
    		$bodytype = str_replace(' ', '%20', $bodytype);
    		$enginesize = str_replace(' ', '%20', $enginesize);
    		$transmission = str_replace(' ', '%20', $transmission);
    		$getRegionUrl = $apiCallBaseUrl.'api/v2/master/car/data/region/'.$year.'/'.$make.'/'.$model.'/'.$trim.'/'.$bodytype.'/'.$enginesize.'/'.$transmission;
            $getRegionResult = $this->insuranceHelper->getDataFromAPI($getRegionUrl);
			$getRegionResultArray = json_decode($getRegionResult);
			$regions = $getRegionResultArray->response;

			$optionsHtml = '';
			foreach ($regions as $region) {
				$optionsHtml .= '<option value="'.$region->Name.'">'.$region->Name.'</option>';
			}
			$resultJson = $this->resultJsonFactory->create();
			return $resultJson->setData($optionsHtml);
    	}

        if($type == 'getcarvalue'){
            $make = $this->getRequest()->getParam('make');
            $year = $this->getRequest()->getParam('year');
            $model = $this->getRequest()->getParam('model');
            $trim = $this->getRequest()->getParam('trim');
            $bodytype = $this->getRequest()->getParam('bodytype');
            $enginesize = $this->getRequest()->getParam('enginesize');
            $transmission = $this->getRequest()->getParam('transmission');
            $carregion = $this->getRequest()->getParam('carregion');
            $make = str_replace(' ', '%20', $make);
            $model = str_replace(' ', '%20', $model);
            $trim = str_replace(' ', '%20', $trim);
            $bodytype = str_replace(' ', '%20', $bodytype);
            $enginesize = str_replace(' ', '%20', $enginesize);
            $transmission = str_replace(' ', '%20', $transmission);
            $carregion = str_replace(' ', '%20', $carregion);
            $getCarValuationUrl = $apiCallBaseUrl.'api/v2/master/car/data/valuation/'.$year.'/'.$make.'/'.$model.'/'.$trim.'/'.$bodytype.'/'.$enginesize.'/'.$transmission.'/'.$carregion;
            $getCarvaluationResult = $this->insuranceHelper->getDataFromAPI($getCarValuationUrl);
            $getCarValuationResultArray = json_decode($getCarvaluationResult);
            
            $high = $getCarValuationResultArray->response->Valuation->High;
            $medium = $getCarValuationResultArray->response->Valuation->Medium;
            $low = $getCarValuationResultArray->response->Valuation->Low;

            $response = [
                    'status' => 200,
                    'high' => $high,
                    'medium' => $medium,
                    'low' => $low
            ];
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData($response);
        }
    }
}
