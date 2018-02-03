
<?php 

# Thanks to https://gist.github.com/anchetaWern/6150297

#Obtain dirty html
$html = file_get_contents('http://www.sunat.gob.pe/cl-at-ittipcam/tcS01Alias');
 

#disable errorsfrom libxml; Build the Document Object Model and load the HTML to clear errors 
libxml_use_internal_errors(TRUE);
$sunat_dom = new DOMDocument();
$sunat_dom->loadHTML($html);
libxml_clear_errors();


$sunat_xpath = new DOMXPath($sunat_dom);
$sunat_dia_row = $sunat_xpath->query('//td[@class="H3"]');

$sunat=[];

if($sunat_dia_row->length > 0){
	foreach($sunat_dia_row as $dia_row){
		$cv=[];
		$sunat_cv_row = $sunat_xpath->query('//td[@class="tne10"]');
		if($sunat_cv_row->length > 0){
				$cv['compra']=floatval($sunat_cv_row[($dia_row->nodeValue*2)-2]->nodeValue);
				$cv['venta']=floatval($sunat_cv_row[($dia_row->nodeValue*2)-1]->nodeValue);
		}
		$sunat[intval($dia_row->nodeValue)]=$cv;
	}
}
echo "<pre>";
print_r($sunat);
echo "</pre>";
?>
