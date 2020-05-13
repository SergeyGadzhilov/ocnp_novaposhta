<?php

class ControllerExtensionShippingOcnpNovaposhta extends Controller
{
   const EXTENSION_NAME = 'shipping_ocnp_novaposhta';
   const EXTENSION_PATH = 'extension/shipping/ocnp_novaposhta';
   private $m_data = array();

   private function loadResources()
   {
      $this->load->language(self::EXTENSION_PATH);
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
      $this->m_data['ocnp_text_main_settings'] = $this->config->get('ocnp_text_main_settings');
      $this->m_data['ocnp_text_api_settings'] = $this->config->get('ocnp_text_api_settings');
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

   public function index()
   {
      $this->loadResources();
      if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate())
      {
         $this->load->model('setting/setting');
         $this->model_setting_setting->editSetting(self::EXTENSION_NAME, $this->request->post);
         $this->session->data['success'] = $this->language->get('text_success');
         $this->response->redirect($this->getLink('marketplace/extension'));
      }
      else
      {
         $this->setBreadcrumbs();
         $this->loadSettings();
         $this->m_data['action'] = $this->getLink(self::EXTENSION_PATH);
         $this->m_data['cancel'] = $this->getLink('marketplace/extension');
         $this->m_data['header'] = $this->load->controller('common/header');
         $this->m_data['column_left'] = $this->load->controller('common/column_left');
         $this->m_data['footer'] = $this->load->controller('common/footer');

         $this->response->setOutput($this->load->view(self::EXTENSION_PATH, $this->m_data));
      }
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

         var_dump($this->request->post);

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