<!DOCTYPE html>
<html lang="en">
  <head>
  	<title>Opuzen</title>
  	<meta charset="utf-8">
    <meta name="author" content="Ezequiel Donovan">
    <meta name="viewport" content="user-scalable=no, initial-scale=1.0, maximum-scale=1, minimum-scale=1, width=device-width, height=device-height, shrink-to-fit=no">
    <link rel='icon' type='image/ico' href='<?php echo asset_url()?>images/favicon_b_32x32.png'>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Karla">
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
    
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/af-2.2.2/b-1.5.1/b-colvis-1.5.1/b-flash-1.5.1/b-html5-1.5.1/b-print-1.5.1/cr-1.4.1/fc-3.2.4/fh-3.1.3/kt-2.3.2/r-2.2.1/rg-1.0.2/rr-1.2.3/sc-1.4.4/sl-1.2.5/datatables.min.css"/>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jszip-2.5.0/dt-1.10.16/af-2.2.2/b-1.5.1/b-colvis-1.5.1/b-flash-1.5.1/b-html5-1.5.1/b-print-1.5.1/cr-1.4.1/fc-3.2.4/fh-3.1.3/kt-2.3.2/r-2.2.1/rg-1.0.2/rr-1.2.3/sc-1.4.4/sl-1.2.5/datatables.min.js"></script>
    
    <script type="text/javascript" src="<?php echo asset_url()?>js/init_datatables.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo asset_url()?>css/my_datatables.css">
    
    <link rel="stylesheet" type="text/css" href="<?php echo asset_url()?>css/style_auth.css">
    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.9/css/all.css" integrity="sha384-5SOiIsAziJl6AWe0HWRKTXlfcSHKmYV4RBF18PPJ173Kzn7jzMyFuTtk8JA7QQG1" crossorigin="anonymous">
    
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css">
    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js'></script>
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js'></script>
    
    <script src='<?php echo asset_url()?>js/commons.js'></script>
    
    <script src='https://www.google.com/recaptcha/api.js' async defer></script>
  </head>
  <body class='container' style='background:black; color:white;'>
    <script>
      $(document).ready(function(){
        $("input[type='text'], input[type='password']").each(function(){
          $(this).addClass('form-control');
        });
      });
      
      $(document).on('keypress', 'form', function(e){
        if(e.which == 13){
          $(this).submit();
        }
      });
    </script>
    <div class='row py-5'>
      <div class='col-12 text-center'>
        <img src='<?php echo asset_url()?>images/opuzen_logo.jpg' onclick="javascript: window.location = '<?php echo site_url()?>';">
      </div>
    </div>