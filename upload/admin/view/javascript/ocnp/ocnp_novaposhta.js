function OCNP_Request(name){
   var m_token = 'user_token='+ getURLVar('user_token');
   var m_route = 'route=extension/shipping/ocnp_novaposhta/' + name;

   this.getURL = function(){
      return ('index.php?' + m_route + '&' + m_token);
   }
}

function OCNP_Server(){
   this.sendRequest = function(request, callback){
      $.ajax({
         url: request.getURL(),
         dataType: 'json',
         success: function(response){
            if (callback){
               callback(response);
            }
         }
      });
   }
}

function SyncCities(){
   var server = new OCNP_Server();
   server.sendRequest(new OCNP_Request('syncCities'), function(response){
      alert(response);
   });
}