<?php

class ModelShippingOcnpNovaposhta extends Model {

   private $m_data = array();

   public function __construct()
   {
      $this->m_data = array(
         'code' => 'ocnp_novaposhta',
         'title' => $this->language->get('text_title'),
         'sort_order' => $this->config->get('ocnp_novaposhta_sort_order'),
         'error' => FALSE,
         'quote' => array()
      );
   }

   public function getQuote($address)
   {
      if ($this->config->get('ocnp_novaposhta_status'))
      {
         $this->load->language('shipping/ocnp_novaposhta');
         $cost = $this->getCost($address['city']);
         
         $this->m_data['quote'] = array(
            'warehouse' => array(
               'code' => 'ocnp_novaposhta.warehouse',
               'title' => $this->language->get('text_description'),
               'cost' => $cost,
               'tax_class_id' => 0,
               'text' => $this->currency->format($cost)
            )
         );
      }

      return $this->m_data;
   }

   private function isNotFreeDelivery()
   {
      $MinFreeTotal = $this->config->get('ocnp_novaposhta_min_total_for_free_delivery');
      return ($MinFreeTotal < $this->getSubtotal());
   }

   private function getCost($City)
   {
      $Cost = 0;

      if ($this->isNotFreeDelivery())
      {
         $CityID = $this->getCityID($City);
         if ($CityID)
         {
            $Cost = $this->getCostFromApi($CityID);
         }
      }

      return $Cost;
   }

   private function getWeight()
   {
      return $this->weight->convert($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->config->get('auspost_weight_class_id'));
   }

   private function getSubtotal()
   {
      return $this->currency->convert($this->cart->getSubTotal(),'','UAH');
   }

   private function getApiUrl()
   {
      $url = "https://api.novaposhta.ua/v2.0/json/";

      if ($this->config->get("ocnp_novaposhta_api_url"))
      {
         $url = $this->config->get("ocnp_novaposhta_api_url");
      }

      return $url;
   }

   private function sendRequest($request)
   {
      $request["apiKey"] = $this->config->get('ocnp_novaposhta_api_key');

      $conection = curl_init();

      curl_setopt($conection, CURLOPT_POST, 1);
      curl_setopt($conection, CURLOPT_HEADER, 0);
      curl_setopt($conection, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($conection, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($conection, CURLOPT_URL, $this->getApiUrl());
      curl_setopt($conection, CURLOPT_HTTPHEADER, Array("Content-Type: text/plain"));
      curl_setopt($conection, CURLOPT_POSTFIELDS, json_encode($request));

      $response = json_decode(curl_exec($conection), TRUE);

      curl_close($conection);

      return $response;
   }

   private function getCityID($CityName) 
   {
      $response = $this->sendRequest(
         array(
            "modelName" => "Address",
            "calledMethod" => "getCities",
            "methodProperties" => array(
               "FindByString" => $city
            )
         )
      );

      if ($response['success'] == 1 && count($response['data']) == 1)
      {
         return $response['data'][0]['Ref'];
      }
      else
      {
         $this->m_data['error'] = $this->language->get('error_get_city');
         return FALSE;
      }
   }

   private function getCostFromApi($city_to)
   {
      $response = $this->sendRequest(
         array(
            "modelName" => "InternetDocument",
            "calledMethod" => "getDocumentPrice",
            "methodProperties" => array(
               "DateTime" => date("d.m.Y"),
               "ServiceType" => "WarehouseWarehouse",
               "Weight" => $this->getWeight(),
               "Cost" => $this->getSubtotal(),
               "CitySender" => $this->config->get('ocnp_novaposhta_city_from'),
               "CityRecipient" => $city_to
            )
         )
      );

      if ($response['success'] == 1 && count($response['data']) == 1)
      {
         return $this->currency->convert($response['data'][0]['Cost'],'UAH','');
      }
      else
      {
         $this->m_data['error'] = $this->language->get('error_get_cost');
         return FALSE;
      }
   }
}