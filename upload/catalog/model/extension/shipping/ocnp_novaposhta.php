<?php

class ModelExtensionShippingOcnpNovaposhta extends Model {

   const MODULE_NAME = 'shipping_ocnp_novaposhta';
   const MODULE_PATH = 'extension/shipping/ocnp_novaposhta';
   private $m_data = array();

   public function getQuote($address)
   {
      if ($this->config->get($this->settingName('status')))
      {
         $this->load->language(self::MODULE_PATH);

         $this->m_data = array(
            'code' => self::MODULE_NAME,
            'title' => $this->language->get('text_description'),
            'sort_order' => $this->config->get($this->settingName('sort_order')),
            'error' => FALSE,
            'quote' => array()
         );

         $this->m_data['quote'] = array(
            'warehouse' => array(
               'code' => 'ocnp_novaposhta.warehouse',
               'title' => $this->language->get('text_description'),
               'cost' => 0,
               'tax_class_id' => 0,
               'text' => ''
            )
         );
      }

      return $this->m_data;
   }

   private function settingName($setting)
   {
      return self::MODULE_NAME.'_'.$setting;
   }

   private function getApiUrl()
   {
      $url = "https://api.novaposhta.ua/v2.0/json/";

      if ($this->config->get($this->settingName('api_url')))
      {
         $url = $this->config->get($this->settingName('api_url'));
      }

      return $url;
   }

   private function sendRequest($request)
   {
      $request["apiKey"] = $this->config->get($this->settingName('api_key'));

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
}