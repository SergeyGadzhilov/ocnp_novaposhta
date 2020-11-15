<?php

namespace ocnp\novaposhta;

class OCNPNovaPoshtaSettings extends \Controller
{
   private $m_settings = array(
      'extension_name' => 'shipping_ocnp_novaposhta',
      'extension_path' => 'extension/shipping/ocnp_novaposhta',
      'name' => array('Delivery Nova Poshta'),
      'status' => '0',
      'api_url' => 'https://api.novaposhta.ua/v2.0/json/',
      'api_key' => '',
      'sort_order' => '0',
      'fixed_price' => 0
   );

   public function __construct($registry)
   {
      parent::__construct($registry);
      $this->loadSettings();
   }

   private function loadSettings()
   {
      $this->loadNames();
      $this->loadFromDB();
   }

   public function setSettings($settings)
   {
      foreach(array_keys($this->m_settings) as $name)
      {
         if (key_exists($name, $settings))
         {
            $this->m_settings[$name] = $settings[$name];
         }
      }
   }

   private function loadNames()
   {
      $this->load->model('localisation/language');
      $languages = $this->model_localisation_language->getLanguages();

      $names = array();
      foreach($languages as $language)
      {
         $translation = new \Language($language['code']);
         $translation->load($this->get('extension_path'));
         $names[$language['language_id']] = $translation->get('heading_title');
      }

      $this->m_settings['name'] = $names;
   }

   private function loadFromDB()
   {
      $this->load->model('setting/setting');
      $db = $this->model_setting_setting->getSetting($this->get('extension_name'));
      foreach(array_keys($this->m_settings) as $name)
      {
         $nameInDB = $this->settingName($name);
         if (key_exists($nameInDB, $db))
         {
            $this->m_settings[$name] = $db[$nameInDB];
         }
      }
   }

   public function saveSettings()
   {
      $this->load->model('setting/setting');

      $values = array();
      foreach($this->m_settings as $name => $value)
      {
         $values[$this->settingName($name)] = $value;
      }
      $this->model_setting_setting->editSetting($this->get('extension_name'), $values);
   }

   public function get($setting)
   {
      $value = '';

      if (key_exists($setting, $this->m_settings))
      {
         $value = $this->m_settings[$setting];
      }

      return $value;
   }

   public function getSettings()
   {
      return $this->m_settings;
   }

   private function settingName($setting)
   {
      $extensionName = $this->get('extension_name');
      return $extensionName.'_'.$setting;
   }
}