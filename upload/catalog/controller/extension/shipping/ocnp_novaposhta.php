<?php

class ControllerExtensionShippingOcnpNovaposhta extends Controller
{
   const EXTENSION_PATH = 'extension/shipping/ocnp_novaposhta';

   public function getCities()
   {
      $response_data = array('success' => false);

      if (isset($this->request->post['area_id'])){
         $area_id = $this->request->post['area_id'];

         $this->load->model(self::EXTENSION_PATH);
         $cities = $this->model_extension_shipping_ocnp_novaposhta->getCitiesByAreaID($area_id);
         
         $response_data['success'] = true;
         $response_data['cities'] = $cities;
      }

      $this->sendResponse($response_data);
   }

   private function sendResponse($data)
   {
      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($data));
   }
}