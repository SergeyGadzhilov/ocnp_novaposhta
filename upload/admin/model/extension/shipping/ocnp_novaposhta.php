<?php
class ModelExtensionShippingOcnpNovaposhta extends Model {

   const CITIES_TABLE = DB_PREFIX . 'ocnp_novaposhta_cities';
   const SYNC_TABLE = DB_PREFIX. 'ocnp_novaposhta_sync';
   const EXTENSION_PATH = 'extension/shipping/ocnp_novaposhta';

   public function getCitiesFromApi()
   {
      $request = array(
         "modelName" => 'Address',
         "calledMethod" => "getCities"
      );

      return $this->sendRequest($request);
   }

   public function addCity($city)
   {
      $sql = "insert into ".self::CITIES_TABLE."(Description, DescriptionRu, Ref, Area, CityID) values (";
      $sql .= "'".$city['Description']."',";
      $sql .= "'".$city['DescriptionRu']."',";
      $sql .= "'".$city['Ref']."',";
      $sql .= "'".$city['Area']."',";
      $sql .= "'".$city['CityID']."');";

      $this->db->query($sql);
   }

   public function clearCities()
   {
      $query = "truncate ".self::CITIES_TABLE;
      $this->db->query($query);
   }

   public function updateCitiesSync()
   {
      $this->updateSync(self::CITIES_TABLE);
   }

   private function getRecordsCount($TableName)
   {
      $query = $this->db->query("select count(*) records_count from ".$TableName.";");
      return $query->row['records_count'];
   }

   private function getApiUrl()
   {
      $url = "https://api.novaposhta.ua/v2.0/json/";

      if ($this->config->get("shipping_ocnp_novaposhta_api_url"))
      {
         $url = $this->config->get("shipping_ocnp_novaposhta_api_url");
      }

      return $url;
   }

   private function sendRequest($request)
   {
      $request["apiKey"] = $this->config->get('shipping_ocnp_novaposhta_api_key');
      if ($request["apiKey"])
      {
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
      }
      else
      {
         $this->load->language(self::EXTENSION_PATH);
         $response = array(
            "success" => false,
            "errors" => array($this->language->get("error_api_key"))
         );
      }

      return $response;
   }

   public function getCitiesTableInfo()
   {
      return $this->getSync(self::CITIES_TABLE);
   }

   public function install()
   {
      $this->db->query("CREATE TABLE IF NOT EXISTS `" . self::CITIES_TABLE ."` (
         `AA_ID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
         `Description` VARCHAR(50) NOT NULL,
         `DescriptionRu` VARCHAR(50) NOT NULL,
         `Ref` VARCHAR(36) NOT NULL,
         `Area` VARCHAR(36) NOT NULL,
         `CityID` INT UNSIGNED
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

      $this->db->query("CREATE TABLE IF NOT EXISTS `" . self::SYNC_TABLE ."` (
         `AA_ID`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
         `TableName`     VARCHAR(50) NOT NULL,
         `RecordsCount`  INT(30) UNSIGNED,
         `Timestamp`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

      $this->installData();
   }

   private function installData()
   {
      $sync_tales = array(
         self::CITIES_TABLE
      );

      foreach($sync_tales as $table)
      {
         $this->db->query("insert into ".self::SYNC_TABLE."(TableName, RecordsCount) values ('".$table."', 0);");
      }
   }

   private function updateSync($TableName)
   {
      $RecordsCount = $this->getRecordsCount($TableName);
      $this->db->query("update ".self::SYNC_TABLE." set RecordsCount = ".$RecordsCount.", Timestamp = CURRENT_TIMESTAMP where TableName = '".$TableName."';");
   }

   private function getSync($TableName)
   {
      $query = $this->db->query("select * from ".self::SYNC_TABLE." where TableName = '".$TableName."'");
      return $query->row;
   }

   public function uninstall()
   {
      $this->db->query("DROP TABLE IF EXISTS `" . self::CITIES_TABLE."` ;");
      $this->db->query("DROP TABLE IF EXISTS `" . self::SYNC_TABLE."` ;");
   }
}