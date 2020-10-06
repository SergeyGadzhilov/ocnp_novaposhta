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

   self.selectedIndex = function(){
      return m_select.selectedIndex;
   }
}

function OCNP_ShowCity(){
   var area = new OCNP_Select("ocnp_novaposhta_area");
   var city = new OCNP_Select("ocnp_novaposhta_city");

   if (area.selectedIndex() > 0){
      city.show();
   }
   else{
      city.hide();
   }
}
