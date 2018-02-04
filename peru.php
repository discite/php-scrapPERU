
<?php 

# Thanks to https://gist.github.com/anchetaWern/6150297 i was able to write the scrapper part of the code

$data=$_GET;
$today_day=date('d',time());
$today_month=date('m',time());
$today_year=date('Y',time());

#Obtain dirty html
$html = file_get_contents('http://www.sunat.gob.pe/cl-at-ittipcam/tcS01Alias');
header('Content-Type: application/json');
$response=array(
	'status' => 'error',
	'message' => 'error base',
	'data' => array(), 
);
try{
	if(!empty($html)){
		#disable errorsfrom libxml; Build the Document Object Model and load the HTML to clear errors 
		libxml_use_internal_errors(TRUE);
		$sunat_dom = new DOMDocument();
		$sunat_dom->loadHTML($html);
		libxml_clear_errors();

		$sunat=[];
		$sunat_xpath = new DOMXPath($sunat_dom);
		if(isset($data['dia'])){
			if(empty($data['dia'])) throw new Exception("Error: se ingreso informacion vacia");
			$dia=intval($data['dia']);
			$cv=[];
			$sunat_dia_row = $sunat_xpath->query('//td[@class="H3"]');
			$sunat_cv_row = $sunat_xpath->query('//td[@class="tne10"]');
			if($dia>$sunat_dia_row->length) throw new Exception("Error: no se tiene información de la SUNAT de ese día");
			if($sunat_cv_row->length > 0){
					$cv['compra']=floatval($sunat_cv_row[($dia*2)-2]->nodeValue);
					$cv['venta']=floatval($sunat_cv_row[($dia*2)-1]->nodeValue);
			}else throw new Exception("Error: no se encontro informacion de compra y venta");
			$sunat[$dia]=$cv;
			$response['status'] = 'success';
			$response['message'] = 'se obtuvo el dia de la sunat de este mes ('.$dia.' de '.$today_month.' del '.$today_year.')';
			$response['data'] = $sunat;
		}else{
			$sunat_dia_row = $sunat_xpath->query('//td[@class="H3"]');
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
			}else throw new Exception("Error: no se encontro informacion de compra y venta");
			$response['status'] = 'success';
			$response['message'] = 'se obtuvo todos los dias de la sunat de este mes ('.$today_month.' del '.$today_year.')';
			$response['data'] = $sunat;
		}
	}else throw new Exception("Error: no se recivio contenido de TC de la sunat");
}catch (Exception $e) {
	$response=array(
		'status' => 'error',
		'message' => $e->getMessage(),
		'data' => array(), 
	);
}
echo json_encode($response);
?>
