function OCNP_Select(id){
   var self = this;
   var m_control = document.getElementById(id);
   var m_select = m_control.getElementsByTagName('select')[0];
   
   self.show = function(){
      if (m_control){
         m_control.style.display = 'block';
      }
   }

   self.hide = function(){
      if (m_control){
         m_control.style.display = 'none';
      }
   }

   function selectedItem(){
      return m_select.options[m_select.selectedIndex];
   }

   self.getSelectedValue = function(){
      return selectedItem().value;
   }

   self.getSelectedID = function(){
      return selectedItem().id;
   }

   self.addOption = function(option){
      m_select.add(option);
   }

   self.clear = function(){
      if (m_select.options.length > 1){
         for (i = (m_select.options.length - 1); i > 0; --i){
            m_select.remove(i);
         }
      }
   }
}

function OCNP_Request(name, data){
   var m_route = 'route=extension/shipping/ocnp_novaposhta/' + name;
   var m_data = data;

   this.getURL = function(){
      return ('index.php?' + m_route);
   }

   this.getData = function(){
      return m_data;
   }
}

function OCNP_Server(){
   this.sendRequest = function(request, callback){
      $.ajax({
         url: request.getURL(),
         type: "POST",
         dataType: 'json',
         data: request.getData(),
         success: function(response){
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
}

function OCNP_City(){
   var self = this;
   var m_control = new OCNP_Select("ocnp_novaposhta_city");

   self.show = function(area_id){
      if (area_id){
         showCities(area_id);
      }
      else{
         m_control.hide();
      }
   }

   function setCities(cities){
      m_control.clear();

      for (i = 1; i < cities.length; ++i){
         var option = document.createElement('option');
         option.id = cities[i].Ref;
         option.value = cities[i].Description;
         option.text = option.value;

         m_control.addOption(option);
      }
   }

   function showCities(area_id){
      var server = new OCNP_Server();
      var data = {'area_id': area_id};

      server.sendRequest(new OCNP_Request('getCities', data),{
            'success': function(response){
               if (response.cities.length > 0){
                  setCities(response.cities)
                  m_control.show();
               }
               else{
                  m_control.hide();
               }
            },
            'error' : function(response){
               m_control.hide();
            }
      });
   }
}

function OCNP_ShowCity(){
   var area = new OCNP_Select("ocnp_novaposhta_area");
   var city = new OCNP_City();

   city.show(area.getSelectedID());
}
