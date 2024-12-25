<?php
class ModelExtensionShippingOcnpNovaposhta extends Model {

   const CITIES_TABLE = 'ocnp_novaposhta_cities';
   const AREAS_TABLE = 'ocnp_novaposhta_areas';
   const SYNC_TABLE = 'ocnp_novaposhta_sync';
   const WAREHOUSES_TABLE = 'ocnp_novaposhta_warehouses';

   public function addAreas($areas) {
      if(!is_array($areas) || count($areas) == 0)
      {
         return false;
      }

      $sql = "insert into ".DB_PREFIX.self::AREAS_TABLE."(Description, DescriptionRu, Ref, AreasCenter) values ";
      for ($i = 0; $i < count($areas); ++$i)
      {
         if ($i > 0)
         {
            $sql .= ",";
         }

         $sql .= "(";
         $sql .= "'".$this->db->escape($areas[$i]['Description'])."',";
         $sql .= "'".$this->db->escape($areas[$i]['DescriptionRu'])."',";
         $sql .= "'".$this->db->escape($areas[$i]['Ref'])."',";
         $sql .= "'".$this->db->escape($areas[$i]['AreasCenter'])."')";
      }
      $sql .= ";";
      return $this->db->query($sql);
   }

   public function clearAreas()
   {
      $this->clearTable(self::AREAS_TABLE);
   }

   public function updateAreasSync()
   {
      $this->updateSync(self::AREAS_TABLE);
   }

   public function addWarehouses($warehouses)
   {
      if(!is_array($warehouses) || count($warehouses) == 0)
      {
         return false;
      }

      $sql = "insert into ".DB_PREFIX.self::WAREHOUSES_TABLE."( ";
      $sql .= "SiteKey, Description, DescriptionRu, Ref, Phone, TypeOfWarehouse, ";
      $sql .= "Number, CityRef, TotalMaxWeightAllowed, PlaceMaxWeightAllowed) values ";

      for ($i = 0; $i < count($warehouses); ++$i)
      {
         if ($i > 0)
         {
            $sql .= ",";
         }

         $sql .= "(";
         $sql .= "'".$this->db->escape($warehouses[$i]['SiteKey'])."',";
         $sql .= "'".$this->db->escape($warehouses[$i]['Description'])."',";
         $sql .= "'".$this->db->escape($warehouses[$i]['DescriptionRu'])."',";
         $sql .= "'".$this->db->escape($warehouses[$i]['Ref'])."',";
         $sql .= "'".$this->db->escape($warehouses[$i]['Phone'])."',";
         $sql .= "'".$this->db->escape($warehouses[$i]['TypeOfWarehouse'])."',";
         $sql .= "'".$this->db->escape($warehouses[$i]['Number'])."',";
         $sql .= "'".$this->db->escape($warehouses[$i]['CityRef'])."',";
         $sql .= "'".$this->db->escape($warehouses[$i]['TotalMaxWeightAllowed'])."',";
         $sql .= "'".$this->db->escape($warehouses[$i]['PlaceMaxWeightAllowed'])."')";
      }
      $sql .= ";";

      return $this->db->query($sql);
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
      $query = "truncate ".DB_PREFIX.$Table;
      $this->db->query($query);
   }

   public function addCities($cities)
   {
      if(!is_array($cities) || count($cities) == 0)
      {
         return false;
      }

      $sql = "insert into ".DB_PREFIX.self::CITIES_TABLE."(Description, DescriptionRu, Ref, Area, CityID) values ";

      for($i = 0; $i < count($cities); ++$i)
      {
         if ($i > 0){
            $sql .= ",";
         }

         $sql .= "\n(";
         $sql .= "'".$this->db->escape($cities[$i]['Description'])."',";
         $sql .= "'".$this->db->escape($cities[$i]['DescriptionRu'])."',";
         $sql .= "'".$this->db->escape($cities[$i]['Ref'])."',";
         $sql .= "'".$this->db->escape($cities[$i]['Area'])."',";
         $sql .= "'".$this->db->escape($cities[$i]['CityID'])."')";
      }

      $sql .= ";";

      return $this->db->query($sql);
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
      $query = $this->db->query("select count(*) records_count from ".DB_PREFIX.$TableName.";");
      return $query->row['records_count'];
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
      $this->db->query("CREATE TABLE IF NOT EXISTS `" .DB_PREFIX.self::CITIES_TABLE ."` (
         `AA_ID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
         `Description` VARCHAR(250) NOT NULL,
         `DescriptionRu` VARCHAR(250) NOT NULL,
         `Ref` VARCHAR(36) NOT NULL,
         `Area` VARCHAR(36) NOT NULL,
         `CityID` INT UNSIGNED
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

      $this->db->query("CREATE TABLE IF NOT EXISTS `" .DB_PREFIX.self::SYNC_TABLE ."` (
         `AA_ID`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
         `TableName`     VARCHAR(50) NOT NULL,
         `RecordsCount`  INT(30) UNSIGNED,
         `Timestamp`     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

      $this->db->query("CREATE TABLE IF NOT EXISTS `" .DB_PREFIX.self::AREAS_TABLE ."` (
         `AA_ID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
         `Description` VARCHAR(250) NOT NULL,
         `DescriptionRu` VARCHAR(250) NOT NULL,
         `Ref` VARCHAR(36) NOT NULL,
         `AreasCenter` VARCHAR(36) NOT NULL
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8");

      $this->db->query("CREATE TABLE IF NOT EXISTS `" .DB_PREFIX.self::WAREHOUSES_TABLE ."` (
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
         $this->db->query("insert into ".DB_PREFIX.self::SYNC_TABLE."(TableName, RecordsCount) values ('".DB_PREFIX.$table."', 0);");
      }
   }

   private function updateSync($TableName)
   {
      $RecordsCount = $this->getRecordsCount($TableName);
      $this->db->query("update ".DB_PREFIX.self::SYNC_TABLE." set RecordsCount = ".$RecordsCount.", Timestamp = CURRENT_TIMESTAMP where TableName = '".DB_PREFIX.$TableName."';");
   }

   private function getSync($TableName)
   {
      $query = $this->db->query("select * from ".DB_PREFIX.self::SYNC_TABLE." where TableName = '".DB_PREFIX.$TableName."'");
      return $query->row;
   }

   public function uninstall()
   {
      $this->db->query("DROP TABLE IF EXISTS `" .DB_PREFIX.self::CITIES_TABLE."` ;");
      $this->db->query("DROP TABLE IF EXISTS `" .DB_PREFIX.self::AREAS_TABLE."` ;");
      $this->db->query("DROP TABLE IF EXISTS `" .DB_PREFIX.self::SYNC_TABLE."` ;");
      $this->db->query("DROP TABLE IF EXISTS `" .DB_PREFIX.self::WAREHOUSES_TABLE."` ;");
   }
}