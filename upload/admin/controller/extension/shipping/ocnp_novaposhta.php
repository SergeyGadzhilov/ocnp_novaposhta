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

   public function saveSettings()
   {
      $request = $this->parseRequest();
      $this->OCNPNovaPoshtaSettings->setSettings($request);
      $this->OCNPNovaPoshtaSettings->saveSettings();
      $response = array(
         'success' => true,
      );

      $this->sendResponse($response);
   }

   public function uninstall()
   {
      $this->load->model($this->OCNPNovaPoshtaSettings->get('extension_path'));
      $this->model_extension_shipping_ocnp_novaposhta->uninstall();
   }

   public function addWarehouses()
   {
      $this->load->language($this->OCNPNovaPoshtaSettings->get('extension_path'));
      $response = array(
         'success' => false,
         'timestamp' => 0,
         'count' => 0,
         'message' => ''
      );

      $request =  $this->parseRequest();
      if (!is_array($request) || count($request) == 0)
      {
         $response['message'] = $this->language->get('ocnp_error_bad_request');
         $this->sendResponse($response);
         return;
      }

      $model = $this->loadModel();
      if (!$model->addWarehouses($request))
      {
         $response['message'] = $this->language->get('ocnp_error_add_data');
         $this->sendResponse($response);
         return;
      }
      $model->updateWarehousesSync();
      $WarehouseTable = $model->getWarehousesTableInfo();

      $response['success'] = true;
      $response['timestamp'] = $WarehouseTable['Timestamp'];
      $response['count'] = $WarehouseTable['RecordsCount'];
      $response['message'] = $this->language->get('ocnp_text_sync_success_warehouses');

      $this->sendResponse($response);
   }

   public function addAreas()
   {
      $this->load->language($this->OCNPNovaPoshtaSettings->get('extension_path'));

      $request = $this->parseRequest();
      $response = array(
         'success' => false,
         'timestamp' => 0,
         'count' => 0,
         'message' => ''
      );

      if (!is_array($request) || count($request) == 0)
      {
         $response['message'] = $this->language->get('ocnp_error_bad_request');
      }

      $model = $this->loadModel();
      if (!$model->addAreas($request))
      {
         $response['message'] = $this->language->get('ocnp_error_add_data');
         $this->sendResponse($response);
         return;
      }
      $model->updateAreasSync();
      $AreasTable = $model->getAreasTableInfo();

      $response['success'] = true;
      $response['timestamp'] = $AreasTable['Timestamp'];
      $response['count'] = $AreasTable['RecordsCount'];
      $response['message'] = $this->language->get('ocnp_text_sync_success_areas');

      $this->sendResponse($response);
   }

   public function clearWarehouses()
   {
      $model = $this->loadModel();
      $model->clearWarehouses();
      $model->updateWarehousesSync();
      $info = $model->getWarehousesTableInfo();

      $response = array (
         'success' => true,
         'timestamp' => $info['Timestamp'],
         'count' => $info["RecordsCount"],
         'message' => ''
      );

      $this->sendResponse($response);
   }

   public function clearAreas()
   {
      $model = $this->loadModel();
      $model->clearAreas();
      $model->updateAreasSync();
      $info = $model->getAreasTableInfo();

      $response = array (
         'success' => true,
         'timestamp' => $info['Timestamp'],
         'count' => $info["RecordsCount"],
         'message' => ''
      );

      $this->sendResponse($response);
   }

   public function clearCities()
   {
      $model = $this->loadModel();
      $model->clearCities();
      $model->updateCitiesSync();
      $info = $model->getCitiesTableInfo();

      $response = array (
         'success' => true,
         'timestamp' => $info['Timestamp'],
         'count' => $info["RecordsCount"],
         'message' => ''
      );

      $this->sendResponse($response);
   }

   public function addCities() {
      $this->load->language($this->OCNPNovaPoshtaSettings->get('extension_path'));

      $response = array(
         'success' => false,
         'timestamp' => 0,
         'count' => 0,
         'message' => ""
      );

      $request = $this->parseRequest();

      if (!is_array($request) || count($request) == 0)
      {
         $response['message'] = $this->language->get('ocnp_error_bad_request');
         $this->sendResponse($response);
         return;
      }

      $model = $this->loadModel();
      if (!$model->addCities($request))
      {
         $response['message'] = $this->language->get('ocnp_error_add_data');
         $this->sendResponse($response);
         return;
      }

      $model->updateCitiesSync();
      $CitiesTable = $model->getCitiesTableInfo();

      $response['success'] = true;
      $response['timestamp'] = $CitiesTable['Timestamp'];
      $response['count'] = $CitiesTable['RecordsCount'];
      $response['message'] = $this->language->get('ocnp_text_sync_success_cities');

      $this->sendResponse($response);
   }

   private function loadResources()
   {
      $this->load->language($this->OCNPNovaPoshtaSettings->get('extension_path'));
      $this->document->addScript('view/javascript/ocnp/ocnp_novaposhta.js?47');

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

   private function parseRequest()
   {
      return json_decode(file_get_contents( 'php://input' ), true);
   }

   private function loadModel()
   {
      $this->load->model($this->OCNPNovaPoshtaSettings->get('extension_path'));
      return $this->model_extension_shipping_ocnp_novaposhta;
   }

   private function sendResponse($response)
   {
      $this->response->addHeader('Content-Type: application/json');
      $this->response->setOutput(json_encode($response));
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