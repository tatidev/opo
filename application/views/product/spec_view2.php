
<?php
  //  echo "<pre>"; var_dump($info); echo "</pre>";

  include_pdf_library();

  if( isset($product_id) ){
    
    switch($specType){
      case 'fabrics':
        // Fabrics Spec Sheet
        $filename = url_title('Opuzen-'.$info['product_name'].'-Spec-Sheet');
        $pdf = new PDF($product_id, 'P', 'mm', array(216, 279) );

        $pdf->AddPage();
        $pdf->HeaderFabric($info['product_name'], $img_url);
        $pdf->CreateBody($specType, $spec, $colors_arr);
        $pdf->Output('I', $filename . '.pdf');
        break;
        
        
      case 'items':
        // Item Spec Sheet
				//var_dump($item_data);
        $filename = url_title('Opuzen-'.$item_data['code'].'-'.$info['product_name'].'-'.$item_data['color'].'-Spec-Sheet');
        $pdf = new PDF($product_id, 'P', 'mm', array(216, 279) );

        $pdf->AddPage();
        $pdf->HeaderItem($info['product_name']);
        $pdf->CreateBody($specType, $spec, $colors_arr, $item_data);
        $pdf->Output('I', $filename . '.pdf');
        break;
        
        
      default:
        break;
    }
    

    
  } else {
    
  }

?>