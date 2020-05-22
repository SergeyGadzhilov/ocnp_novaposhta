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

function OCNP_Request(name){
   var m_token = 'user_token='+ getURLVar('user_token');
   var m_route = 'route=extension/shipping/ocnp_novaposhta/' + name;

   this.getURL = function(){
      return ('index.php?' + m_route + '&' + m_token);
   }
}

function OCNP_Server(){
   var m_console = new OCNP_UserMessage(document.getElementsByClassName('panel-body')[0]);

   this.sendRequest = function(request, callback){
      $.ajax({
         url: request.getURL(),
         dataType: 'json',
         success: function(response){
            showServerMessage(response);
            processResponse(response, callback);
         }
      });
   }

   function processResponse(response, callback){
      if (response.success){
         if (callback.success){
            callback.success(response);
         }
      }
      else{
         if (callback.error){
            callback.error(response);
         }
      }
   }

   function showServerMessage(response){
      if (response.message){
         if (response.success){
            m_console.success(response.message);
         }
         else{
            m_console.error(response.message);
         }
      }
   }
}

function OCNP_SyncItem(id){
   var m_row = document.getElementById(id);
   var m_timestamp = m_row.querySelector('.sync_item__timestamp');
   var m_count = m_row.querySelector('.sync_item__count');
   var m_icon = m_row.querySelector('.sync_item__icon');
   var m_btn = m_row.querySelector('.sync_item__btn');

   this.setTimestamp = function(timestamp){
      m_timestamp.innerText = timestamp;
   }

   this.setCount = function(count){
      m_count.innerText = count;
   }

   this.startSync = function(){
      m_icon.classList.add('fa-spin');
      m_btn.classList.add('btn-danger');
   }

   this.endSync = function(){
      m_icon.classList.remove('fa-spin');
      m_btn.classList.remove('btn-danger');
   }
}


function syncCities(){
   var id = 'syncCities';
   var server = new OCNP_Server();
   var city = new OCNP_SyncItem(id);
   city.startSync();

   server.sendRequest(new OCNP_Request(id), {
      "success" : function(response){
         city.setTimestamp(response.timestamp);
         city.setCount(response.count);
         city.endSync();
      },
      "error" : function(response){
         city.endSync();
      }
   });
}