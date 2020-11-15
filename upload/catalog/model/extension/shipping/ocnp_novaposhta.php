<?php

class OCNPNovaPoshtaForm
{
   private $m_form;

   public function __construct($form)
   {
      $this->m_form = json_encode($form);
   }

    public function show()
    {
      return json_decode($this->m_form);
    }
}

class ModelExtensionShippingOcnpNovaposhta extends Model {

   const MODULE_NAME = 'shipping_ocnp_novaposhta';
   const MODULE_PATH = 'extension/shipping/ocnp_novaposhta';
   const CITIES_TABLE = 'ocnp_novaposhta_cities';
   const AREAS_TABLE =  'ocnp_novaposhta_areas';
   const WAREHOUSES_TABLE = 'ocnp_novaposhta_warehouses';

   private $m_data = array();

   public function getQuote($address)
   {
      if ($this->config->get($this->settingName('status')))
      {
         $this->load->language(self::MODULE_PATH);
         $title = $this->config->get($this->settingName('name'));

         $this->m_data = array(
            'code' => self::MODULE_NAME,
            'title' => $title[$this->config->get('config_language_id')],
            'sort_order' => $this->config->get($this->settingName('sort_order')),
            'error' => FALSE,
            'quote' => array()
         );

         $this->m_data['quote'] = array(
            'warehouse' => array(
               'code' => 'ocnp_novaposhta.warehouse',
               'title' => $title[$this->config->get('config_language_id')],
               'cost' => 0,
               'tax_class_id' => 0,
               'text' => '',
               'form' => $this->getForm()
            )
         );
      }

      return $this->m_data;
   }

   private function getForm()
   {
      $data = array(
         'areas'  => $this->getAreas()
      );

      return new OCNPNovaPoshtaForm($this->load->view(self::MODULE_PATH, $data));
   }

   public function getCitiesByAreaID($area)
   {
      $query = $this->db->query("SELECT * FROM ".DB_PREFIX.self::CITIES_TABLE." WHERE Area = '".$area."'");
      $this->updateDescriptions($query->rows);
      return $query->rows;
   }

   public function getWarehousesByCityID($city)
   {
      $query = $this->db->query("SELECT * FROM ".DB_PREFIX.self::WAREHOUSES_TABLE." WHERE CityRef = '".$city."'");
      $this->updateDescriptions($query->rows);

      return $query->rows;
   }

   private function getAreas()
   {
      $query = $this->db->query("SELECT * FROM ".DB_PREFIX.self::AREAS_TABLE);
      $this->updateDescriptions($query->rows);

      return $query->rows;
   }

   private function settingName($setting)
   {
      return self::MODULE_NAME.'_'.$setting;
   }

   private function updateDescriptions(&$rows)
   {
      if ($this->language->get('code') == 'ru')
      {
         foreach($rows as &$row)
         {
            $row['Description'] = $row['DescriptionRu'];
         }
      }
   }
}