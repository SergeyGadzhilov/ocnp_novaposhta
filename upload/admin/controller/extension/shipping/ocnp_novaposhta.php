<?php

class ControllerExtensionShippingOcnpNovaposhta extends Controller
{
   const EXTENSION_NAME = 'shipping_ocnp_novaposhta';
   const EXTENSION_PATH = 'extension/shipping/ocnp_novaposhta';
   private $m_data = array();

   public function install()
   {
      $this->load->model(self::EXTENSION_PATH);
      $this->model_extension_shipping_ocnp_novaposhta->install();
   }

   public function uninstall()
   {
      $this->load->model(self::EXTENSION_PATH);
      $this->model_extension_shipping_ocnp_novaposhta->uninstall();
   }

   public function syncAreas()
   {
      $this->saveSettings();
      $this->load->language(self::EXTENSION_PATH);

      $respose = array(
         'success' => true,
         'timestamp' => "",
         'count' => 0,
         'message' => $this->language->get('ocnp_text_sync_success')
      );

      $this->load->model(self::EXTENSION_PATH);

      $areas = $this->model_extension_shipping_ocnp_novaposhta->getAreasFromApi();
      if ($areas["success"])
      {
         $this->model_extension_shipping_ocnp_novaposhta->clearAreas();
         foreach ($areas["data"] as $area) {
            set_time_limit(30);
            $this->model_extension_shipping_ocnp_novaposhta->addArea($area);
         }
         $this->model_extension_shipping_ocnp_novaposhta->updateAreasSync();
      }
      else{
         $respose["success"] = false;
         $respose["message"] = $areas["errors"][0];
      }

      $AreasTable = $this->model_extension_shipping_ocnp_novaposhta->getAreasTableInfo();
      $respose["timestamp"] = $AreasTable['Timestamp'];
      $respose["count"] =  $AreasTable['RecordsCount'];

      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($respose));
   }

   public function syncCities()
   {
      $this->saveSettings();
      $this->load->language(self::EXTENSION_PATH);

      $respose = array(
         'success' => true,
         'timestamp' => "",
         'count' => 0,
         'message' => $this->language->get('ocnp_text_sync_success')
      );

      $this->load->model(self::EXTENSION_PATH);

      $cities = $this->model_extension_shipping_ocnp_novaposhta->getCitiesFromApi();
      if ($cities["success"])
      {
         $this->model_extension_shipping_ocnp_novaposhta->clearCities();
         foreach ($cities["data"] as $city) {
            set_time_limit(30);
            $this->model_extension_shipping_ocnp_novaposhta->addCity($city);
         }
         $this->model_extension_shipping_ocnp_novaposhta->updateCitiesSync();
      }
      else{
         $respose["success"] = false;
         $respose["message"] = $cities["errors"][0];
      }

      $CitiesTable = $this->model_extension_shipping_ocnp_novaposhta->getCitiesTableInfo();
      $respose["timestamp"] = $CitiesTable['Timestamp'];
      $respose["count"] =  $CitiesTable['RecordsCount'];

      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($respose));
   }

   private function loadResources()
   {
      $this->load->language(self::EXTENSION_PATH);
      $this->document->addScript('view/javascript/ocnp/ocnp_novaposhta.js?19');

      $this->document->setTitle($this->language->get('heading_title'));
      $this->m_data['heading_title'] = $this->language->get('heading_title');
      $this->m_data['text_edit'] = $this->language->get('text_edit');
      $this->m_data['text_enabled'] = $this->language->get('text_enabled');
      $this->m_data['text_disabled'] = $this->language->get('text_disabled');
      $this->m_data['entry_status'] = $this->language->get('entry_status');
      $this->m_data['ocnp_entry_api_url'] = $this->language->get('entry_api_url');
      $this->m_data['ocnp_entry_api_key'] = $this->language->get('entry_api_key');
      $this->m_data['ocnp_entry_sort_order'] = $this->language->get('entry_sort_order');
      $this->m_data['button_save'] = $this->language->get('button_save');
      $this->m_data['button_cancel'] = $this->language->get('button_cancel');
   }

   private function setBreadcrumbs()
   {
      $this->m_data['breadcrumbs'][] = array(
         'href' => $this->getLink('common/home'),
         'text' => $this->language->get('text_home')
      );

      $this->m_data['breadcrumbs'][] = array(
         'href' => $this->getLink('marketplace/extension'),
         'text' => $this->language->get('text_extension')
      );

      $this->m_data['breadcrumbs'][] = array(
         'href' => $this->getLink(self::EXTENSION_PATH),
         'text' => $this->language->get('heading_title')
      );
   }

   private function settingName($setting)
   {
      return self::EXTENSION_NAME.'_'.$setting;
   }

   private function loadSettings()
   {
      $settings = array(
         $this->settingName('status') => '0',
         $this->settingName('api_url') => 'https://api.novaposhta.ua/v2.0/json/',
         $this->settingName('api_key') => '',
         $this->settingName('sort_order') => '0'
      );

      foreach($settings as $setting => $defaultValue)
      {
         $value = $this->config->get($setting);
         if (isset($this->request->post[$setting]))
         {
            $value = $this->request->post[$setting];
         }

         if (empty($value))
         {
            $value = $defaultValue;
         }

         $this->m_data[$setting] = $value;
      }
   }

   private function getLink($route)
   {
      return HTTPS_SERVER."index.php?route=".$route."&user_token=".$this->session->data['user_token'];
   }

   private function saveSettings()
   {
      $this->load->model('setting/setting');
      $this->model_setting_setting->editSetting(self::EXTENSION_NAME, $this->request->post);
      $this->config->set('shipping_ocnp_novaposhta_api_key', $this->request->post['shipping_ocnp_novaposhta_api_key']);
   }

   public function index()
   {
      $this->loadResources();
      if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate())
      {
         $this->saveSettings();
         $this->session->data['success'] = $this->language->get('text_success');
         $this->response->redirect($this->getLink('marketplace/extension'));
      }
      else
      {
         $this->setBreadcrumbs();
         $this->loadSettings();
         $this->setSyncTableInfo();

         $this->m_data['action'] = $this->getLink(self::EXTENSION_PATH);
         $this->m_data['cancel'] = $this->getLink('marketplace/extension');
         $this->m_data['header'] = $this->load->controller('common/header');
         $this->m_data['column_left'] = $this->load->controller('common/column_left');
         $this->m_data['footer'] = $this->load->controller('common/footer');
         $this->response->setOutput($this->load->view(self::EXTENSION_PATH, $this->m_data));
      }
   }

   private function setSyncTableInfo()
   {
      $this->load->model(self::EXTENSION_PATH);
      $cities = $this->model_extension_shipping_ocnp_novaposhta->getCitiesTableInfo();
      $this->m_data['ocnp_sync_table_cities_timestamp'] = $cities['Timestamp'];
      $this->m_data['ocnp_sync_table_cities_count'] = $cities['RecordsCount'];

      $areas = $this->model_extension_shipping_ocnp_novaposhta->getAreasTableInfo();
      $this->m_data['ocnp_sync_table_areas_timestamp'] = $areas['Timestamp'];
      $this->m_data['ocnp_sync_table_areas_count'] = $areas['RecordsCount'];
   }

   private function validate()
   {
      $IsValid = true;

      if (!$this->user->hasPermission('modify', self::EXTENSION_PATH))
      {
         $this->m_data['warning'] = $this->language->get('error_permission');
         $IsValid = false;
      }
      else
      {
         $RequiredFields = array(
            'api_url',
            'api_key'
         );

         foreach($RequiredFields as $field)
         {
            $name = $this->settingName($field);
            if (empty($this->request->post[$name]))
            {
               $this->m_data['error_'.$name] = $this->language->get('error_'.$field);
               $IsValid = false;
            }
         }
      }

      return $IsValid;
   }
}