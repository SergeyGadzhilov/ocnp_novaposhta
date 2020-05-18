<?php
class ModelExtensionShippingOcnpNovaposhta extends Model {

   public function getCitiesFromApi()
   {
      $request = array(
         "modelName" => 'Address',
         "calledMethod" => "getCities",
         "methodProperties" => array()
      );

      return $this->sendRequest($request);
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

   public function install()
   {
      $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ocnp_novaposhta_cities` (
         `AA_ID` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
         `Description` VARCHAR(50) NOT NULL,
         `DescriptionRu` VARCHAR(50) NOT NULL,
         `Ref` VARCHAR(36) NOT NULL,
         `Area` INT(11) UNSIGNED,
         `CityID` INT(11) UNSIGNED
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
   }

   public function uninstall()
   {
      $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ocnp_novaposhta_cities` ;");
   }
}