<?php

/**
 * ALTO File Viewer
 *
 * @package    AltoViewer
 * @author     Dan Field <dof@llgc.org.uk>
 * @copyright  Copyright (c) 2010 National Library of Wales / Llyfrgell Genedlaethol Cymru. 
 * @link       http://www.llgc.org.uk
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License 3
 * @version    $Id$
 * @link       http://www.loc.gov/standards/alto/
 * 
 **/

require_once 'lib/AltoViewer.php';
 
$vScale = $_REQUEST['vScale'];
$hScale = $_REQUEST['hScale'];
$dScale = $_REQUEST['dScale'];
$image = $_REQUEST['image'];

$config = parse_ini_file('./config/altoview.ini');

function endsWith($haystack, $needle) {
     return substr($haystack, -strlen($needle))===$needle;
}

function sans_suffix($file) {
     return preg_replace('/\.[^.]+$/','',$file);
}

function listFiles($root, $folder) {
   global $hScale, $vScale, $dScale;
   if ($handle = opendir("$root/$folder")) {
       $blacklist = array('.', '..');
       while (false !== ($file = readdir($handle))) {
           if (!in_array($file, $blacklist)) {
               $path = ($folder == "" ? $file : "$folder/$file");
               if (is_dir("$root/$path")) {
                   listFiles($root, "$path");
               } else {
                   if (endsWith($file, ".png") || endsWith($file, ".jpg")) {
                       $image = sans_suffix($path);
                   ?> <a href="index.php?hScale=<?php echo $hScale; ?>&vScale=<?php echo $vScale; ?>&dScale=<?php echo $dScale; ?>&image=<?php echo $image; ?>"><?php echo $image; ?></a><br/> <?php
                   }
               }
            }
        }
        closedir($handle);
    } else {
        echo "No images available in ";
        echo $config['imageDir'];
    }
     
}

?>

<html>
    <head>
        <link rel="stylesheet" type="text/css" href="css/style.css" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>          
        <?php if ($image == '') { ?>
            <title>ALTO Viewer T2 available images</title>
        <?php } else { 

$altoViewer = new AltoViewer(   $config['altoDir'], 
                                $config['imageDir'], 
                                $image, $vScale, $hScale, $dScale);
$imageSize = $altoViewer->getImageSize();
$strings = $altoViewer->getStrings();
$textLines = $altoViewer->getTextLines();
$textBlocks = $altoViewer->getTextBlocks();
$printSpace = $altoViewer->getPrintSpace();

$scaledHeight = $imageSize[1] * $vScale;
$scaledWidth = $imageSize[0] * $hScale;

?>
            <title>ALTO Viewer T2 - <?php echo $image; ?> - <?php echo $vScale; ?> x <?php echo $hScale; ?> - (<?php echo $imageSize[0]; ?>x<?php echo $imageSize[1]; ?>px)</title>
        <?php } ?>
    </head>
    <body>

        <?php if ($image == '') { ?>
        <h1>Available images</h1>
        <div class="imagelist">

<?php  listFiles($config['imageDir'], ""); ?>

</div>

<?php } else { ?>


<!--
Strings: <?php echo sizeof($strings); ?><br/>
Lines: <?php echo sizeof($textLines); ?><br/>
Blocks: <?php echo sizeof($textBlocks); ?><br/>
-->

<h1>Showing <?php echo $image; ?></h1>

            <?php foreach ($strings as $string) { 
                  $wc = $string->getWC();
                  if ( $wc != '' ) {
                      if ($wc < 0.25) {
                          $wc_style = "wc000to025";
                      } else if ($wc < 0.50) {
                          $wc_style = "wc025to050";
                      } else if ($wc < 0.75) {
                          $wc_style = "wc050to075";
                      } else if ($wc < 1) {
                          $wc_style = "wc075to100";
                      } else {
                          $wc_style = "wc100";
                      }
                  } else {
                      $wc_style = "wcnone";
                  }
                  ?>
            
            <?php } ?>

        <div class="menu">
            <div class="menuBox" id="toggleBox">
                <span>Toggle Layers</span><br />
                <input type="checkbox" id="strings" >Strings</input><br />
                <input type="checkbox" id="lines" >TextLine</input><br />
                <input type="checkbox" checked="checked" id="blocks" >TextBlock</input><br />
                <input type="checkbox" id="printspace" >PrintSpace</input><br />
            </div>
        </div>
        
        <div id="image">
            <img 
                src="images/<?php echo $image; ?>.png" 
                width="<?php echo $scaledWidth; ?>" 
                height="<?php echo $scaledHeight; ?>" />
            <?php foreach ($strings as $string) {
                  $wc = $string->getWC();
                  $cc = $string->getCC();
                  if ( $wc != '' ) {
                      if ($wc < 0.25) {
                          $wc_style = "wc000to025";
                      } else if ($wc < 0.50) {
                          $wc_style = "wc025to050";
                      } else if ($wc < 0.75) {
                          $wc_style = "wc050to075";
                      } else if ($wc < 1) {
                          $wc_style = "wc075to100";
                      } else {
                          $wc_style = "wc100";
                      }
                  } else {
                      $wc_style = "wcnone";
                  }
            ?>
                <div class="highlighter hs <?php echo $wc_style; ?>"
                    title="<?php echo $string->getContent(); echo " (WC=$wc, CC=$cc)"; ?>"
                    style=" left: <?php echo $string->getHPos(); ?>px; 
                            top: <?php echo $string->getVPos(); ?>px; 
                            width: <?php echo $string->getWidth(); ?>px; 
                            height: <?php echo $string->getHeight(); ?>px; 
                            filter: alpha(opacity=50); 
                            z-index: 4" >
                </div>
            <?php } ?>
            <script>
                $("input[id=strings]").click(function () {
                $("div.hs").toggle();
                });    
            </script>
            
            <?php foreach ($textLines as $textLine) { ?>
                <div class="highlighter hl"
                    style=" left: <?php echo $textLine->getHPos(); ?>px; 
                            top: <?php echo $textLine->getVPos(); ?>px; 
                            width: <?php echo $textLine->getWidth(); ?>px; 
                            height: <?php echo $textLine->getHeight(); ?>px; 
                            filter: alpha(opacity=50); 
                            z-index: 3;
                            display: none">
                </div>
            <?php } ?>
            <script>
                $("input[id=lines]").click(function () {
                $("div.hl").toggle();
                });    
            </script>
        
            <?php foreach ($textBlocks as $textBlock) { ?>
                <div class="highlighter hb"
                    style=" left: <?php echo $textBlock->getHPos(); ?>px; 
                            top: <?php echo $textBlock->getVPos(); ?>px; 
                            width: <?php echo $textBlock->getWidth(); ?>px; 
                            height: <?php echo $textBlock->getHeight(); ?>px; 
                            filter: alpha(opacity=50)
                            z-index: 2" >
                </div>
            <?php } ?>
            <script>
                $("input[id=blocks]").click(function () {
                $("div.hb").toggle();
                });    
            </script>
            
            <div class="highlighter ps"
                style=" left: <?php echo $printSpace->getHPos(); ?>px; 
                        top: <?php echo $printSpace->getVPos(); ?>px; 
                        width: <?php echo $printSpace->getWidth(); ?>px; 
                        height: <?php echo $printSpace->getHeight(); ?>px; 
                        filter: alpha(opacity=50);
                        z-index: 1;
                        display: none" >
            </div>
            <script>
                $("input[id=printspace]").click(function () {
                $("div.ps").toggle();
                });    
            </script>
            
                    
        </div>
    </body>
        <?php } ?>
</html>
