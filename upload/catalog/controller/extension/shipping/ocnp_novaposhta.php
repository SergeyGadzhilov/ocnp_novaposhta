<?php

class ControllerExtensionShippingOcnpNovaposhta extends Controller
{
   const EXTENSION_PATH = 'extension/shipping/ocnp_novaposhta';

   public function getCities()
   {
      $response_data = array('success' => false);

      if (isset($this->request->post['area_id']))
      {
         $area_id = $this->request->post['area_id'];

         $this->load->model(self::EXTENSION_PATH);
         $cities = $this->model_extension_shipping_ocnp_novaposhta->getCitiesByAreaID($area_id);
         
         $response_data['success'] = true;
         $response_data['cities'] = $cities;
      }

      $this->sendResponse($response_data);
   }

   public function getWarehouses()
   {
      $response_data = array('success' => false);

      if (isset($this->request->post['city_id']))
      {
         $city_id = $this->request->post['city_id'];

         $this->load->model(self::EXTENSION_PATH);
         $warehouses = $this->model_extension_shipping_ocnp_novaposhta->getWarehousesByCityID($city_id);
         
         $response_data['success'] = true;
         $response_data['warehouses'] = $warehouses;
      }

      $this->sendResponse($response_data);
   }

   public function addScripts(&$route, &$data)
   {
      $this->document->addScript('catalog/view/javascript/ocnp_novaposhta/checkout.js');
   }

   public function addNovaPoshtaSaveAddress(&$route, &$data)
   {
      if ($this->request->post['shipping_method'] == 'ocnp_novaposhta.warehouse')
      {
         $this->session->data['shipping_address']['zone'] = $this->OCNP_getValue('ocnp_novaposhta_area', 'zone');
         $this->session->data['shipping_address']['city'] = $this->OCNP_getValue('ocnp_novaposhta_city', 'city');
         $this->session->data['shipping_address']['address_1'] = $this->OCNP_getValue('ocnp_novaposhta_warehouse', 'address_1');
      }
   }

   public function addNovaPoshtaForm(&$route, &$data, &$output)
   {
      $isAlreadyAdded = strpos($output, 'OCNP_ShowCity()');
      if ($isAlreadyAdded !== false) {
         return;
      }

      $label = strpos($output, 'ocnp_novaposhta.warehouse');
      if ($label === false) {
         return;
      }

      $formPosition = strpos($output, '</label>', $label) + 8;
      if ($formPosition === false) {
         return;
      }

      $this->load->model(self::EXTENSION_PATH);
      $form = $this->model_extension_shipping_ocnp_novaposhta->getForm();

      $output = $this->stringInsert($output, $form, $formPosition);
   }

   public function addNovaPoshtaCheckoutScript(&$route, &$data, &$output)
   {
      $output = str_replace(
         "data: $('#collapse-shipping-method input[type=\'radio\']:checked, #collapse-shipping-method textarea'),",
         "data: $('#collapse-shipping-method input[type=\'radio\']:checked, #collapse-shipping-method textarea, #collapse-shipping-method select'),",
         $output
      );
   }

   private function OCNP_getValue($name, $address_name)
   {
      $value = $this->session->data['shipping_address'][$address_name];

      if (isset($this->request->post[$name]))
      {
         $value = $this->request->post[$name];
      }

      return $value;
   }

   private function stringInsert($str, $insertstr, $pos)
   {
      $str = substr($str, 0, $pos) . $insertstr . substr($str, $pos);
      return $str;
   }

   private function sendResponse($data)
   {
      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($data));
   }
}