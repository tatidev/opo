<?php
class PDF extends FPDF {
  
  protected $OpuzenLogo = 'https://www.opuzen.com/assets/images/opuzen_blackonwhite_272.png';
  protected $AuthorCreator = 'Opuzen Design';
  protected $product_id;
  protected $description;
	
	protected $fontsize = array(
		// 'product_name' => is set depending on the length of the name
		'subtitle' => 13,
		'spec_list' => 10,
		'color_names' => 8,
		'footer' => 9,
		'footer_address' => 10
	);
  
  protected $leftmargin = 12;
  protected $topmargin = 8;
  protected $cell_width = 20;
  
  protected $main_image_width = 190;
  protected $item_big_image_width = 90;
	
	protected $thumbs_to_show = 17;
  protected $thumbsize = 15;
  
  // Second column distance from the left margin of the page
  // Is where the item information is
  protected $col2 = 54;
  
  function __construct($id, $orientation='P', $unit='mm', $size='A4'){
    parent::__construct($orientation, $unit, $size);
    $this->product_id = $id;
    $this->SetAuthor($this->AuthorCreator);
    $this->SetCreator($this->AuthorCreator);
  }
  
  function HeaderFabric($name, $imgUrl){
    $this->SetTitle($name.' Specification Sheet');
    // Logo (url, posX, posY, size)
    $this->Image($this->OpuzenLogo, 128, $this->topmargin, 70);
    // Font
    $this->AddFont('Karla', '', 'Karla-Regular.php');
    $this->AddFont('Karla', 'B', 'Karla-Bold.php');
    $this->SetXY($this->leftmargin, $this->topmargin+1);
    
    $length = strlen($name);
    if( $length < 25 ) {
      $titleFontSize = 23;
    } else if ( $length >= 25 && $length <= 34 ) {
      $titleFontSize = 18;
    } else if ( $length > 34 && $length < 40 ) {
      $titleFontSize = 16;
    } else if ( $length >= 40 ) {
      $titleFontSize = 14;
    }
    
    $this->SetFont('Karla', '', $titleFontSize);
    $this->SetTextColor(185, 167, 0);
	if($length > 40){
		$name = strtoupper($name);
		$n = explode(' ON ', $name);
// 		var_dump($n);
// 		$this->SetXY($this->GetX(), ($this->GetY() - 3));
		$this->Write(5, strtoupper($n[0]) );
		$this->Ln(5);
		$this->SetXY($this->leftmargin, $this->GetY());
		$this->Write(5, strtoupper('ON '. $n[1]) );
		$this->Ln(4);
	}
	else {
		$this->Write(10, strtoupper($name) );
		$this->Ln(7);
	}

    $this->SetXY($this->leftmargin, $this->GetY());
    $this->SetFont('Karla', '', $this->fontsize['subtitle']);
    $this->SetTextColor(79, 73, 73);
    $this->Write(10, 'PRODUCT SPECIFICATION' );
    
    // Image
    //$folder = $this->GetFolder($type);
    $imageX = $this->leftmargin;
    $imageY = $this->GetY() + 12;
    $this->existsImage = ( strlen($imgUrl) > 0 && $imgUrl !== 'noimage' && strpos($imgUrl, 'placeholder') === false );
    if( $this->existsImage ){
      $this->Image($imgUrl, $imageX, $imageY, $this->main_image_width);
    } else {
			$this->thumbs_to_show = 29;
      // Separation line
      $this->SetXY($this->leftmargin, $this->GetY()+10);
      $this->SetDrawColor(197, 174, 73);
      $this->Line( $this->GetX(), $this->GetY(), $this->GetX()+190, $this->GetY() ); // 190 is the width of the line
    }
  }
  
  function HeaderItem($name){
    $this->SetTitle($name.' Specification Sheet');
    // Logo (url, posX, posY, size)
    $this->Image($this->OpuzenLogo, 128, $this->topmargin, 70);
    // Font
    $this->AddFont('Karla', '', 'Karla-Regular.php');
    $this->AddFont('Karla', 'B', 'Karla-Bold.php');
    $this->SetXY($this->leftmargin, $this->topmargin+1);
		
    $length = strlen($name);
    if( $length < 25 ) {
      $titleFontSize = 23;
    } else if ( $length >= 25 && $length <= 34 ) {
      $titleFontSize = 18;
    } else if ( $length > 34 && $length < 40 ) {
      $titleFontSize = 16;
    } else if ( $length >= 40 ) {
      $titleFontSize = 14;
    }
		
    $this->SetFont('Karla', '', $titleFontSize);
    $this->SetTextColor(185, 167, 0);
    $this->Write(10, strtoupper($name) );
    //$this->Cell($this->cell_width, 8, strtoupper($name), 0, 0, 'L');
    $this->Ln(7);
    $this->SetXY($this->leftmargin, $this->GetY());
    $this->SetFont('Karla', '', $this->fontsize['subtitle']);
    $this->SetTextColor(79, 73, 73);
    $this->Write(10, 'PRODUCT SPECIFICATION' );
  }
  
  function CreateBody($specType, $data, $items=array(), $thisItem=array()){
    $ifSpecTypeItem = 0;
    
    if($specType == 'fabrics') {
      
      $this->SetXY($this->leftmargin + 5, $this->GetY() + ( $this->existsImage ? 100 : 0 ) );
      $initialY = $this->GetY();
      
    } else if($specType == 'items') {
      $ifSpecTypeItem++;
      // Separation line
      $this->SetXY($this->leftmargin, $this->GetY()+10);
      $this->SetDrawColor(197, 174, 73);
      $this->Line( $this->GetX(), $this->GetY(), $this->GetX()+190, $this->GetY() ); // 190 is the width of the line
      
      // Insert big thumb image on the right side
      $folder = $this->GetFolder($thisItem['type'], 'big');
      $imageX = 111;
      $imageY = $this->GetY() + 6;
      $this->Image($folder.$thisItem['id'].'.jpg', $imageX, $imageY, $this->item_big_image_width);
      
      // Special Spec List COLOR and ITEM #
      $this->SetXY($this->leftmargin + 5, $this->GetY()-4 );
      $this->AddSpecList('COLOR', array($thisItem['color']) );
      
      $extra = 0;
      ($thisItem['code'] !== '' ? $this->AddSpecList('ITEM #', array($thisItem['code']) ) : $extra = 7 );
      
      $initialY = $this->GetY() + 82 + $extra;
    }
    
    foreach($data as $d){
      $this->AddSpecList($d['text'], $d['data']);
    }
    
		( $specType === 'items' && count($items) > 0 ? $this->AddSpecList('AVAILABLE IN ADDITIONAL COLORS', array('') ) : '' );

    $this->ProcessColorItems($items, $initialY);
  }

  function AddSpecList($title, $infoArr/*, $field*/){
    $this->SetFontSize( $this->fontsize['spec_list'] );
    $cant = count($infoArr);
    if($cant>0){
      $this->AddSpacingBetweenInfo();
      $this->SetBoldFont();
      $this->SetX($this->leftmargin + 0);
      $this->Write(10, strtoupper($title) );
      $this->SetRegularFont();
      if($cant == 1){
        // only 1 content
        $this->SetX($this->col2);
        $text = str_replace('</nobr>', '', str_replace('<nobr>', '', $infoArr[0]) );
				$this->Write(10, $text );
      } else {
        // more than 1 content
        for($i = 0; $i < $cant; $i++){
          $this->SetX($this->col2);
          $text = str_replace('</nobr>', '', str_replace('<nobr>', '', $infoArr[$i]) );
					$this->Write(10, $text );
          if($i < $cant - 1){
            $this->SetY($this->GetY() + 5);
          }
        }
      }
    }
  }
    
  function ProcessColorItems($items, $initialY){
		
    // Vars
    $distanceBetweenColorsY = 27;
    $distanceBetweenColorsX = 7;
    
    // Starting point
    $posX = $this->GetX() + 58;
    $posY = $initialY + 8;
    $initialX = $posX;
    $more = false;
		
    if(count($items) > 0){
      $counter = 1;
      $this->SetFontSize(8);
			$folder = $this->GetFolder('fabrics_items');
      foreach($items as $i){
				$url = $i['pic_big_url'];
        if( $url !== '' ){
          if($counter == $this->thumbs_to_show){ $more = true; break; /*Exceeds max amount to show in the spec*/ }
          $this->InsertColorImage($posX, $posY, $i['id'], $i['code'], $i['color'], $url);
          $posX = $this->GetX() + $this->thumbsize + $distanceBetweenColorsX;
          if($counter%4 === 0){
            $posY = $this->GetY() + $distanceBetweenColorsY;
            $posX = $initialX;
          }
          $counter++;
        }
      }
      if($more){
        $this->SetFontSize(10);
        $this->SetXY($posX, $posY);
        $this->SetTextColor(185, 167, 0);
        $this->MultiCell(81, 5, 'For a complete line please visit www.opuzen.com', 0, 'J');
      }
    }
  }
  
  function InsertColorImage($x, $y, $id, $code, $color, $imgurl){
    // Set position for the image
    $this->SetXY($x, $y);
    
    $this->Image($imgurl, $x, $y, $this->thumbsize, $this->thumbsize);
	/*
=======
  function InsertColorImage($x, $y, $id, $code, $color){
    // Set position for the image
    $this->SetXY($x, $y);
    
    $folder = $this->GetFolder('fabrics_items');
    $this->Image($folder.$id.'.jpg', $x, $y, $this->thumbsize, $this->thumbsize);
>>>>>>> 9654484990e7e112b878a06c6bcb7981fa697ddd
	*/
    
    // Set position for the text
    $Ypos = $y+$this->thumbsize+3; $extra = 3;
    if($code !== ''){
      $code = explode('/', $code);
      $this->Text($x, $Ypos, trim($code[0]) );
      $Ypos += $extra;
      if( isset($code[1]) ) {
        $this->Text($x, $Ypos, trim($code[1]) );
        $Ypos += $extra;
      }
    }
    $color = str_replace(' / ', '/', $color);
    $color = ( strlen($color) > 15 ? substr($color, 0, 14).".." : $color );
    $this->Text($x, $Ypos, $color);
    
    $this->SetXY($x, $y);
  }
	
  function Footer(){
    // Separation line
    $this->SetXY($this->leftmargin+1, -35);
    $this->SetDrawColor(197, 174, 73);
    $this->Line($this->GetX(), $this->GetY(), $this->GetX()+190, $this->GetY() ); // 190 is the width of the line
		
    // Print date note
    $leftmargin = $this->leftmargin + 5;
    $textdistance = 4;
    $this->SetTextColor(138, 138, 138);
    $this->SetFontSize( $this->fontsize['footer'] );
    $this->SetXY($leftmargin, -15);
    $this->Cell(80, 4, 'Print date: ' . date("M-d-Y") , 0, 'L');
    
    // 'Color may vary' text
    $leftmargin = $this->leftmargin + 5;
    $textdistance = 4;
    $this->SetTextColor(138, 138, 138);
    $this->SetFontSize( $this->fontsize['footer'] );
    $this->SetXY($leftmargin, -30);
    $this->Cell(80, 4, 'Please note that color samples shown in this', 0, 'L');
    $this->SetXY($leftmargin, $this->GetY() + $textdistance );
    $this->Cell(80, 4, 'page may vary from actual colors.', 0, 'L');
    $this->SetXY($leftmargin, $this->GetY() + $textdistance );
    $this->Cell(80, 4, 'For 100% accuracy please order a sample.', 0, 'L');
    
    // How to order
    $leftmargin = 120;
    $textdistance = 4;
    $this->SetXY($leftmargin, -30);
    $this->SetTextColor(197, 174, 73);
    $this->Cell(80, 4, 'HOW TO ORDER', 0, 'L');
    $this->SetTextColor(138, 138, 138);
    $this->SetXY($leftmargin, $this->GetY() + $textdistance );
    $this->Cell(80, 4, 'To order samples please contact Opuzen at', 0, 'L');
    $this->SetXY($leftmargin, $this->GetY() + $textdistance );
    $this->Cell(80, 4, 'info@opuzen.com or call 323-549-3489.', 0, 'L');
    
    // Bottom line
    $this->SetXY($this->leftmargin+1, -10);
    $this->SetTextColor(255, 255, 255);
    $this->SetFillColor(197, 174, 73);
    $this->SetFontSize( $this->fontsize['footer_address'] );
    $this->Cell(190, 6, 'opuzen.com   |   t 323 549 3489   |   f 323 549 3494   |   e info@opuzen.com', 0, 0, 'C', true);
  }
  
  function GetFolder($type, $size='thumb'){
    $path = 'https://www.opuzen.com/showcase/images/';
    switch($type){
      case 'digital_grounds':
        $folder = 'images_dp_grounds_styles/';
        break;
      case 'dp_styles':
        $folder = 'images_dp_styles/';
        break;
      case 'fabrics':
        $folder = 'images_pattern/';
        break;
      case 'fabrics_items':
        switch($size){
          case 'thumb':
            $folder = 'images_items/';
            break;
          case 'big':
            $folder = 'images_items/big/';
            break;
        }
        break;
      default:
        $folder = 'images_pattern/';
        break;
    }
    return $path.$folder;
  }
  
  function AddSpacingBetweenInfo(){
    $this->SetY($this->GetY() + 7); // add positions down
  }
  
  function SetBoldFont($font='Karla'){
    $this->SetFont('Karla', 'B');
  }
  
  function SetRegularFont($font='Karla'){
    $this->SetFont('Karla', '');
  }
  
}

?>