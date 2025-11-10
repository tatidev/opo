<style>
  #mySidebar {
    background: black;
    padding: 6% 0!important;
  }
  
  #menu_logo {
    margin: 25px auto;
    height: 44px;
    width:auto;
    padding:0;
  }
  
  #btn-toggle-menu {
    cursor: pointer;
    position: fixed;
    font-size: 40px;
    top: 20px;
    left: 23px;
  }
  #btn-toggle-menu:hover {
    text-decoration: none;
  }
  
  .sidebar-active {
    color: white!important;
  }
  
  .w3-button:hover {
      color: #000!important;
      background-color: #fff!important;
      text-decoration: none;
  }
  
</style>
<a id="btn-toggle-menu" class='glyphicon glyphicon-align-justify' onclick="w3_open()"></a>

<div class="w3-sidebar w3-bar-block w3-card-2 w3-animate-left" style="display:none" id="mySidebar">
  <img id='menu_logo' class='w3-bar-item' src='<?php echo asset_url()?>images/opuzen_logo_50.png'>
  <button class="w3-bar-item w3-button w3-large" style='color: rgb(183, 183, 183);' onclick="w3_close()"> &times; Close</button>
  <a href="<?php echo site_url('roadkit')?>" class="w3-bar-item w3-button <?php//=(  ? "sidebar-active" : "" );?>"><span class='glyphicon glyphicon-briefcase'></span>&nbsp;&nbsp;Roadkits</a>
  <a href="<?php echo site_url('roadkit_order/view_all')?>" class="w3-bar-item w3-button <?php ?>"><span class='glyphicon glyphicon-inbox'></span>&nbsp;&nbsp;Orders</a>
  <a href="<?php echo site_url('address_book/')?>" class="w3-bar-item w3-button <?php ?>"><span class='glyphicon glyphicon-list-alt'></span>&nbsp;&nbsp;Address Book</a>
  <a class="w3-bar-item w3-button <?php  ?>" id='help-icon' style='color: #35e000;'><span class='glyphicon glyphicon-question-sign'></span>&nbsp;&nbsp;What can I do</a>
  <a class="w3-bar-item"></a>
  <a href="<?php echo site_url('auth/logout')?>" class="w3-bar-item w3-button" id='help-icon'><span class='glyphicon glyphicon-log-out'></span>&nbsp;&nbsp;Logout</a>
</div>


<script>
  
  function w3_open() {
    document.getElementById("wrapper").style.marginLeft = "20%";
    document.getElementById("mySidebar").style.width = "20%";
    document.getElementById("mySidebar").style.display = "block";
    $('#page-content-wrapper').css('padding-left', 0)
                              .css('padding-right', 0)
                              .css('margin-left', 0);
    //$('#btn-toggle-menu').addClass('hide');
  }
  function w3_close() {
    document.getElementById("wrapper").style.marginLeft = "0%";
    document.getElementById("mySidebar").style.width = "0%";
    document.getElementById("mySidebar").style.display = "none";
    $('#page-content-wrapper').css('padding-left', '15px')
                              .css('padding-right', '15px')
                              .css('margin-left', 'auto');
  }

</script>
