<?php

class ControllerExtensionShippingOcnpNovaposhta extends Controller
{
   private $m_data = array();

   public function __construct($register)
   {
      parent::__construct($register);
      $this->load->library('ocnp/novaposhta/OCNPNovaPoshtaSettings');
   }

   public function install()
   {
      $this->load->model($this->OCNPNovaPoshtaSettings->get('extension_path'));
      $this->model_extension_shipping_ocnp_novaposhta->install();
   }

   public function uninstall()
   {
      $this->load->model($this->OCNPNovaPoshtaSettings->get('extension_path'));
      $this->model_extension_shipping_ocnp_novaposhta->uninstall();
   }

   public function syncWarehouses()
   {
      $this->OCNPNovaPoshtaSettings->setSettings($this->request->post);
      $this->OCNPNovaPoshtaSettings->saveSettings();

      $this->load->language($this->OCNPNovaPoshtaSettings->get('extension_path'));

      $respose = array(
         'success' => true,
         'timestamp' => "",
         'count' => 0,
         'message' => $this->language->get('ocnp_text_sync_success_warehouses')
      );

      $this->load->model($this->OCNPNovaPoshtaSettings->get('extension_path'));
      $model = $this->model_extension_shipping_ocnp_novaposhta;

      $warehouses = $model->getWarehousesFromApi();
      if ($warehouses["success"])
      {
         $model->clearWarehouses();
         $model->addWarehouses($warehouses["data"]);
         $model->updateWarehousesSync();
      }
      else{
         $respose["success"] = false;
         $respose["message"] = $warehouses["errors"][0];
      }

      $WarehouseTable = $model->getWarehousesTableInfo();
      $respose["timestamp"] = $WarehouseTable['Timestamp'];
      $respose["count"] =  $WarehouseTable['RecordsCount'];

      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($respose));
   }

   public function syncAreas()
   {
      $this->OCNPNovaPoshtaSettings->setSettings($this->request->post);
      $this->OCNPNovaPoshtaSettings->saveSettings();
      $this->load->language($this->OCNPNovaPoshtaSettings->get('extension_path'));

      $respose = array(
         'success' => true,
         'timestamp' => "",
         'count' => 0,
         'message' => $this->language->get('ocnp_text_sync_success_areas')
      );

      $this->load->model($this->OCNPNovaPoshtaSettings->get('extension_path'));

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
      $this->OCNPNovaPoshtaSettings->setSettings($this->request->post);
      $this->OCNPNovaPoshtaSettings->saveSettings();
      $this->load->language($this->OCNPNovaPoshtaSettings->get('extension_path'));

      $respose = array(
         'success' => true,
         'timestamp' => "",
         'count' => 0,
         'message' => $this->language->get('ocnp_text_sync_success_cities')
      );

      $this->load->model($this->OCNPNovaPoshtaSettings->get('extension_path'));
      $model = $this->model_extension_shipping_ocnp_novaposhta;

      $cities = $model->getCitiesFromApi();
      if ($cities["success"])
      {
         $model->clearCities();
         $model->addCities($cities["data"]);
         $model->updateCitiesSync();
      }
      else{
         $respose["success"] = false;
         $respose["message"] = $cities["errors"][0];
      }

      $CitiesTable = $model->getCitiesTableInfo();
      $respose["timestamp"] = $CitiesTable['Timestamp'];
      $respose["count"] =  $CitiesTable['RecordsCount'];

      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($respose));
   }

   private function loadResources()
   {
      $this->load->language($this->OCNPNovaPoshtaSettings->get('extension_path'));
      $this->document->addScript('view/javascript/ocnp/ocnp_novaposhta.js?19');

      $this->document->setTitle($this->language->get('heading_title'));
      $this->m_data['heading_title'] = $this->language->get('heading_title');
      $this->m_data['text_edit'] = $this->language->get('text_edit');
      $this->m_data['text_enabled'] = $this->language->get('text_enabled');
      $this->m_data['text_disabled'] = $this->language->get('text_disabled');
      $this->m_data['entry_status'] = $this->language->get('entry_status');
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
         'href' => $this->getLink($this->OCNPNovaPoshtaSettings->get('extension_path')),
         'text' => $this->language->get('heading_title')
      );
   }

   private function getLink($route)
   {
      return HTTPS_SERVER."index.php?route=".$route."&user_token=".$this->session->data['user_token'];
   }

   public function index()
   {
      $this->loadResources();
      $this->OCNPNovaPoshtaSettings->setSettings($this->request->post);
      if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate())
      {
         $this->OCNPNovaPoshtaSettings->saveSettings();
         $this->session->data['success'] = $this->language->get('text_success');
         $this->response->redirect($this->getLink('marketplace/extension'));
      }
      else
      {
         $this->setBreadcrumbs();
         $this->setSyncTableInfo();

         $this->m_data = array_merge($this->m_data, $this->OCNPNovaPoshtaSettings->getSettings());
         $this->m_data['languages'] = $this->model_localisation_language->getLanguages();
         $this->m_data['action'] = $this->getLink($this->OCNPNovaPoshtaSettings->get('extension_path'));
         $this->m_data['cancel'] = $this->getLink('marketplace/extension');
         $this->m_data['header'] = $this->load->controller('common/header');
         $this->m_data['column_left'] = $this->load->controller('common/column_left');
         $this->m_data['footer'] = $this->load->controller('common/footer');
         $this->response->setOutput($this->load->view($this->OCNPNovaPoshtaSettings->get('extension_path'), $this->m_data));
      }
   }

   private function setSyncTableInfo()
   {
      $this->load->model($this->OCNPNovaPoshtaSettings->get('extension_path'));
      $cities = $this->model_extension_shipping_ocnp_novaposhta->getCitiesTableInfo();
      $this->m_data['ocnp_sync_table_cities_timestamp'] = $cities['Timestamp'];
      $this->m_data['ocnp_sync_table_cities_count'] = $cities['RecordsCount'];

      $areas = $this->model_extension_shipping_ocnp_novaposhta->getAreasTableInfo();
      $this->m_data['ocnp_sync_table_areas_timestamp'] = $areas['Timestamp'];
      $this->m_data['ocnp_sync_table_areas_count'] = $areas['RecordsCount'];

      $warehouses = $this->model_extension_shipping_ocnp_novaposhta->getWarehousesTableInfo();
      $this->m_data['ocnp_sync_table_warehouses_timestamp'] = $warehouses['Timestamp'];
      $this->m_data['ocnp_sync_table_warehouses_count'] = $warehouses['RecordsCount'];
   }

   private function validate()
   {
      $IsValid = true;

      if (!$this->user->hasPermission('modify', $this->OCNPNovaPoshtaSettings->get('extension_path')))
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
            if (empty($this->OCNPNovaPoshtaSettings->get($field)))
            {
               $this->m_data['error_'.$field] = $this->language->get('error_'.$field);
               $IsValid = false;
            }
         }
      }

      return $IsValid;
   }
}