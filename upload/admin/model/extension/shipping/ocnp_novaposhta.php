<?php
class ModelExtensionShippingOcnpNovaposhta extends Model {

   const CITIES_TABLE = DB_PREFIX . 'ocnp_novaposhta_cities';
   const AREAS_TABLE = DB_PREFIX . 'ocnp_novaposhta_areas';
   const SYNC_TABLE = DB_PREFIX. 'ocnp_novaposhta_sync';
   const WAREHOUSES_TABLE = DB_PREFIX. 'ocnp_novaposhta_warehouses';
   const EXTENSION_PATH = 'extension/shipping/ocnp_novaposhta';


   public function getAreasFromApi()
   {
      $request = array(
         "modelName" => "Address",
         "calledMethod" => "getAreas"
      );

      return $this->sendRequest($request);
   }

   public function getCitiesFromApi()
   {
      $request = array(
         "modelName" => "Address",
         "calledMethod" => "getCities"
      );

      return $this->sendRequest($request);
   }

   public function getWarehousesFromApi()
   {
      $request = array(
         "modelName" => "AddressGeneral",
         "calledMethod" => "getWarehouses"
      );

      return $this->sendRequest($request);
   }

   public function addArea($area)
   {
      $sql = "insert into ".self::AREAS_TABLE."(Description, DescriptionRu, Ref, AreasCenter) values (";
      $sql .= "'".$area['Description']."',";
      $sql .= "'".$area['DescriptionRu']."',";
      $sql .= "'".$area['Ref']."',";
      $sql .= "'".$area['AreasCenter']."');";

      $this->db->query($sql);
   }

   public function clearAreas()
   {
      $this->clearTable(self::AREAS_TABLE);
   }

   public function updateAreasSync()
   {
      $this->updateSync(self::AREAS_TABLE);
   }

   public function addWarehous($warehous)
   {
      $sql = "insert into ".self::WAREHOUSES_TABLE."( ";
      $sql .= "SiteKey, Description, DescriptionRu, Ref, Phone, TypeOfWarehouse, ";
      $sql .= "Number, CityRef, TotalMaxWeightAllowed, PlaceMaxWeightAllowed) values (";
      $sql .= "'".$warehous['SiteKey']."',";
      $sql .= "'".$warehous['Description']."',";
      $sql .= "'".$warehous['DescriptionRu']."',";
      $sql .= "'".$warehous['Ref']."',";
      $sql .= "'".$warehous['Phone']."',";
      $sql .= "'".$warehous['TypeOfWarehouse']."',";
      $sql .= "'".$warehous['Number']."',";
      $sql .= "'".$warehous['CityRef']."',";
      $sql .= "'".$warehous['TotalMaxWeightAllowed']."',";
      $sql .= "'".$warehous['PlaceMaxWeightAllowed']."');";

      $this->db->query($sql);
   }

   public function clearWarehouses()
   {
      $this->clearTable(self::WAREHOUSES_TABLE);
   }

   public function updateWarehousesSync()
   {
      $this->updateSync(self::WAREHOUSES_TABLE);
   }

   private function clearTable($Table)
   {
      $query = "truncate ".$Table;
      $this->db->query($query);
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
      $this->clearTable(self::CITIES_TABLE);
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

   public function getWarehousesTableInfo()
   {
      return $this->getSync(self::WAREHOUSES_TABLE);
   }

   public function getAreasTableInfo()
   {
      return $this->getSync(self::AREAS_TABLE);
   }

   public function install()
   {
      $this->db->query("CREATE TABLE IF NOT EXISTS `" . self::CITIES_TABLE ."` (
         `AA_ID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
         `Description` VARCHAR(250) NOT NULL,
         `DescriptionRu` VARCHAR(250) NOT NULL,
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

      $this->db->query("CREATE TABLE IF NOT EXISTS `" . self::AREAS_TABLE ."` (
         `AA_ID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
         `Description` VARCHAR(250) NOT NULL,
         `DescriptionRu` VARCHAR(250) NOT NULL,
         `Ref` VARCHAR(36) NOT NULL,
         `AreasCenter` VARCHAR(36) NOT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

      $this->db->query("CREATE TABLE IF NOT EXISTS `" . self::WAREHOUSES_TABLE ."` (
         `AA_ID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
         `SiteKey` VARCHAR(36) NOT NULL,
         `Description` VARCHAR(250) NOT NULL,
         `DescriptionRu` VARCHAR(250) NOT NULL,
         `Ref` VARCHAR(36) NOT NULL,
         `Phone` VARCHAR(36) NOT NULL,
         `TypeOfWarehouse` VARCHAR(36) NOT NULL,
         `Number` VARCHAR(36) NOT NULL,
         `CityRef` VARCHAR(36) NOT NULL,
         `TotalMaxWeightAllowed` INT, 
         `PlaceMaxWeightAllowed` INT
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

      $this->installData();
   }

   private function installData()
   {
      $sync_tales = array(
         self::CITIES_TABLE,
         self::AREAS_TABLE,
         self::WAREHOUSES_TABLE
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
      $this->db->query("DROP TABLE IF EXISTS `" . self::AREAS_TABLE."` ;");
      $this->db->query("DROP TABLE IF EXISTS `" . self::SYNC_TABLE."` ;");
      $this->db->query("DROP TABLE IF EXISTS `" . self::WAREHOUSES_TABLE."` ;");
   }
}