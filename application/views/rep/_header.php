<html lang='en'>
  <head>
  	<meta charset="utf-8">
  	<meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, height=device-height">
  	<meta name="format-detection" content="telephone=no">
  	<title>Opuzen Roadkits</title>
    
<?php    // Libraries loading
      // Common libraries are set up on Core/MY_Controller
      // Then invidivual libraries are set up by each controller
      foreach($library_head as $lib){
        switch($lib['type']){
          case 'css':
?>
            <link type='text/css' rel="stylesheet" href="<?php echo $lib['url']?>"  />
<?php          break;
          case 'js':
?>
            <script type='text/javascript' src='<?php echo $lib['url']?>'></script>
<?php          break;
          default:
            echo $lib['url'];
            break;
        }
      }
?>

  </head>
  <body class='full-width page-condensed'>
    
        <?php include('_header_menu.php'); ?>
        <?php include('_header_mobile_menu.php'); ?>
    
    <div id='wrapper'>
      
      <div class='loading-div hide'>
      </div>
      <div id='page-content-wrapper' class='container'>
        
        