const SYNC_ACTIONS = {
   CITIES: 'syncCities',
   AREAS: 'syncAreas',
   WAREHOUSES: `syncWarehouses`
}
class DataIterator{
   constructor(params) {
      this._total = 0;
      this._data = null;
      this._loaded = -1;
      this._server = params.server;
      this._request = params.request;
   }

   async next() {
      if (this.hasNext()) {
         if (this._loaded == -1) {
            this._loaded = 0;
         }
         else {
            this._request.methodProperties.Page++;
         }
         return this.sendRequest(this._request);
      }
      return {success: false, message: 'no more items'};
   }

   async sendRequest(request) {
      const response = await this._server.sendRequest(request);
      if (response == null) {
         return { 
            success: false,
            message: `response for request ${JSON.stringify(request)} is null`
         }
      }

      const payload = await response.json();
      if (payload == null || !payload.success) {
         return {
            success: false,
            message: `request ${JSON.stringify(request)} failed with response ${JSON.stringify(payload)}`
         }
      }

      this._total = payload.info.totalCount;
      this._data = payload.data;
      this._loaded += payload.data.length;

      return { success: true, data: this._data };
   }

   get data() {
      return this._data;
   }

   get page() {
      return this._request.methodProperties.Page;
   }

   get limit() {
      return this._request.methodProperties.Limit;
   }

   hasNext() {
      return this._loaded == -1 || this._total > this._loaded;
   }
}

class OCNP_NovaPoshta{
   constructor(params) {
      this._url = params.api_url;
      this._key = params.api_key;
   }

   async getCities() {
      return new DataIterator({
         server: this,
         request: {
            apiKey: this._key,
            modelName: "Address",
            calledMethod: "getCities",
            methodProperties: {
               Page: 1,
               Limit: 500
            }
         }
      });
   }

   async getAreas() {
      return new DataIterator({
         server: this,
         request: {
            apiKey: this._key,
            modelName: "Address",
            calledMethod: "getAreas",
            methodProperties: {
               Page: 1,
               Limit: 150
            }
         }
      });
   }

   async getWarehouses() {
      return new DataIterator({
         server: this,
         request: {
            apiKey: this._key,
            modelName: "AddressGeneral",
            calledMethod: "getWarehouses",
            methodProperties: {
               Page: 1,
               Limit: 1000
            }
         }
      });
   }

   async sendRequest(request) {
      if (request == null) {
         return {success: false, message: 'request is null'};
      }

      await this.waitForTimeout(2000);

      return fetch(this._url, {
         method: "POST",
         body: JSON.stringify(request)
      });
   }

   async waitForTimeout(ms) {
      return new Promise(resolve => setTimeout(resolve, ms));
   }
}

function OCNP_UserMessage(parent){

   this.success = function(message){
      show(message, 'fa-check-circle', 'alert-success');
   }

   this.error = function(message){
      show(message, 'fa-exclamation-circle', 'alert-danger');
   }

   function show(message, icon_class, class_id){
      parent.insertBefore(createControl(message, icon_class, class_id), parent.firstChild);
   }

   function Icon(class_id){
      var icon = document.createElement('i');
      icon.classList.add('fa', class_id);
      return icon;
   }

   function createControl(message, icon_class, class_id){
      var control = document.createElement('div');
      control.appendChild(Icon(icon_class));
      control.appendChild(document.createTextNode(message));
      control.classList.add('alert', 'alert-dismissible', class_id);
      control.appendChild(closeButton());
      return control;
   }

   function closeButton(){
      var button = document.createElement('button');
      button.type = 'button';
      button.setAttribute('data-dismiss', 'alert');
      button.classList.add('close');
      button.innerText = '×';
      return button;
   }
}

class OCNP_Request {
   constructor(name, data) {
      this._name = name;
      this._data = data; 
   } 

   get url() {
      const token = 'user_token='+ getURLVar('user_token');
      const route = 'route=extension/shipping/ocnp_novaposhta/' + this._name;
      return ('index.php?' + route + '&' + token);
   }

   get data() {
      return JSON.stringify(this._data);
   }
}

class OCNP_Server {
   async clearCities() {
      return this.sendRequest(new OCNP_Request("clearCities", {}));
   }

   async saveSettings(settings) {
      return this.sendRequest(new OCNP_Request("saveSettings", {
         api_key: settings.api_key,
         api_url: settings.api_url
      }));
   }

   async addCities(cities) {
      if (cities == null) {
         return {success: false, message: "cities is null"};
      }
      return this.sendRequest(new OCNP_Request("addCities", cities));
   }

   async clearAreas() {
      return this.sendRequest(new OCNP_Request("clearAreas", {}));
   }

   async addAreas(areas) {
      if (areas == null) {
         return {success: false, message: "areas is null"};
      }
      return this.sendRequest(new OCNP_Request("addAreas", areas));
   }

   async clearWarehouses() {
      return this.sendRequest(new OCNP_Request("clearWarehouses", {}));
   }

   async addWarehouses(warehouses) {
      if (warehouses == null) {
         return {success: false, message: "warehouses is null"};
      }
      return this.sendRequest(new OCNP_Request("addWarehouses", warehouses));
   }

   async sendRequest(request) {
      if (request == null) {
         return {success: false, message: "request is null"};
      }

      try {
         const response = await fetch(request.url, {
            method: "POST",
            headers: {
               "Content-Type": "application/json"
            },
            body: request.data
         });
   
         if (response == null) {
            return {success: false, message: `response is null for the request ${JSON.stringify(request)}`};
         }

         return await response.json();
      }
      catch (err) {
         return { success: false, message: `request failed: ${err.message}`};
      }
   }
}

function OCNP_SyncItem(id){
   var m_row = document.getElementById(id);
   var m_timestamp = m_row.querySelector('.sync_item__timestamp');
   var m_count = m_row.querySelector('.sync_item__count');
   var m_icon = m_row.querySelector('.sync_item__icon');
   var m_btn = m_row.querySelector('.sync_item__btn');
   var m_click = m_btn.onclick;

   this.setTimestamp = function(timestamp){
      m_timestamp.innerText = timestamp;
   }

   this.setCount = function(count){
      m_count.innerText = count;
   }

   this.startSync = function(){
      m_icon.classList.add('fa-spin');
      m_btn.classList.add('btn-danger');
      m_btn.onclick = null;
   }

   this.endSync = function(){
      m_icon.classList.remove('fa-spin');
      m_btn.classList.remove('btn-danger');
      m_btn.onclick = m_click;
   }
}

class OCNP_ApiSettings {
   constructor() {
      this._key = document.querySelector('.ocnp_api_settings__key');
      this._url = document.querySelector('.ocnp_api_settings__url');
   }

   get api_key() {
      return this._key.value;
   }

   get api_url() {
      return this._url.value;
   }
}

async function SyncCities(params) {
   var response = await params.server.clearCities();
   if (!response?.success) {
      return response;
   }

   params.item.setCount(response.count);

   const cities = await params.novaposhta.getCities();
   while (cities.hasNext()) {
      response = await cities.next();
      if (!response?.success) {
         return response;
      }

      response = await params.server.addCities(response.data);
      if (!response?.success) {
         return response;
      }

      params.item.setCount(response.count);
   }

   return response;
}

async function SyncAreas(params) {
   var response = await params.server.clearAreas();
   if (!response?.success) {
      return response;
   }
   params.item.setCount(response.count);

   const areas = await params.novaposhta.getAreas();
   while (areas.hasNext()) {
      response = await areas.next();
      if (!response?.success) {
         return response;
      }

      response = await params.server.addAreas(response.data);
      if (!response.success) {
         return response;
      }

      params.item.setCount(response.count);
   }

   return response;
}

async function SyncWarehouses(params) {
   var response = await params.server.clearWarehouses();
   if (!response.success) {
      return response;
   }
   params.item.setCount(response.count);

   const warehouses = await params.novaposhta.getWarehouses();
   while (warehouses.hasNext()) {
      response = await warehouses.next();
      if (!response?.success) {
         return response;
      }

      response = await params.server.addWarehouses(response.data);
      if (!response.success) {
         return response;
      }
      params.item.setCount(response.count);
   }

   return response;
}

async function syncItem(id){
   const server = new OCNP_Server();
   const apiSettings = new OCNP_ApiSettings();
   const novaposhta = new OCNP_NovaPoshta(apiSettings);
   const uimessage = new OCNP_UserMessage(document.getElementsByClassName('panel-body')[0]);
   var item = new OCNP_SyncItem(id);

   try {
      item.startSync();

      var response = await server.saveSettings(apiSettings);
      if (!response?.success) {
         uimessage.error(`fail to save settings: ${response.message}`);
         item.endSync();
         return;
      }

      switch(id) {
         case SYNC_ACTIONS.CITIES:
            response = await SyncCities({server, novaposhta, item});
            break;
         case SYNC_ACTIONS.AREAS:
            response = await SyncAreas({server, novaposhta, item});
            break;
         case SYNC_ACTIONS.WAREHOUSES:
            response = await SyncWarehouses({server, novaposhta, item});
            break;
         default:
            uimessage.error(`unknown sync action ${id}`);
            return;
      }
   
      if (response?.success) {
         item.setTimestamp(response.timestamp);
         item.setCount(response.count);
         uimessage.success(response.message);
      }
      else {
         uimessage.error(`synchronization failed: ${response.message}`);
      }
   }
   catch(err)
   {
      uimessage.error(`synchronization failed with exception: ${err.message}`);
   }

   item.endSync();
}