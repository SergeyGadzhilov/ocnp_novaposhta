<?php

class ModelExtensionShippingOcnpNovaposhta extends Model {

   const MODULE_NAME = 'shipping_ocnp_novaposhta';
   const MODULE_PATH = 'extension/shipping/ocnp_novaposhta';
   const CITIES_TABLE = 'ocnp_novaposhta_cities';
   const AREAS_TABLE =  'ocnp_novaposhta_areas';
   const WAREHOUSES_TABLE = 'ocnp_novaposhta_warehouses';

   private $m_data = array();

   public function getQuote($address)
   {
      $this->load->library('ocnp/novaposhta/OCNPNovaPoshtaSettings');
      if ($this->OCNPNovaPoshtaSettings->get('status'))
      {
         $this->load->language(self::MODULE_PATH);
         $title = $this->OCNPNovaPoshtaSettings->get('name');

         $this->m_data = array(
            'code' => self::MODULE_NAME,
            'title' => $title[$this->config->get('config_language_id')],
            'sort_order' => $this->OCNPNovaPoshtaSettings->get('sort_order'),
            'error' => FALSE,
            'quote' => array()
         );

         $this->m_data['quote'] = array(
            'warehouse' => array(
               'code' => 'ocnp_novaposhta.warehouse',
               'title' => $title[$this->config->get('config_language_id')],
               'cost' => $this->getCost(),
               'tax_class_id' => 0,
               'text' => $this->currency->format($this->getCost(), $this->session->data['currency'])
            )
         );
      }

      return $this->m_data;
   }

   private function getCost()
   {
      $cost = 0;
      $free_delivery = $this->OCNPNovaPoshtaSettings->get('free_delivery');

      if ($free_delivery == 0 || $free_delivery > $this->cart->getTotal())
      {
         $cost = $this->OCNPNovaPoshtaSettings->get('fixed_price');
      }

      return $cost;
   }

   public function getForm()
   {
      $data = array('areas' => $this->getAreas());
      return $this->load->view(self::MODULE_PATH, $data);
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