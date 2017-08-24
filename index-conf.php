<?php
 
function addFrontConfig($hostSection,$projectType,$projectName){
	$file="main-qa.yaml";
	$handle=fopen($file, "r+");
	$contents=file_get_contents($file);
	$mainLines=preg_split("/\\r\\n/", $contents);
	$newArray = [];
	for ($i=0; $i < count($mainLines) ; $i++) {
		$line=preg_split("/\\r\\n/", $mainLines[$i]);
		$val=trim($line[0],': , ');
		if($val == $hostSection ){

		if( !in_array(str_repeat(' ', 8).$projectType.":",$mainLines) || !in_array(str_repeat(' ', 4).$projectType.":",$mainLines) ){
					$newItem= true;
					for ($k=$i+1; $k < count($mainLines) ; $k++) {
						if(substr_count($mainLines[$k] , ' ') != 4) {
							if($mainLines[$k] == str_repeat(' ', 8).$projectType.":") {
								$newItem= false;
								break;
							}
						}else{
							break;
						}
					}
					if($newItem){
						$newArray[$i]= $mainLines[$i] ;
						$newArray[$i+1]= str_repeat(' ', 8).$projectType.":" ;
						$newArray[$i+2]=  str_repeat(' ', 12).'- "'.$projectName.'"' ;
						continue;
					}
			}
			if( in_array(str_repeat(' ', 4).$hostSection.":",$mainLines) && in_array(str_repeat(' ', 8).$projectType.":",$mainLines) ){
				for ($k=$i+1; $k < count($mainLines) ; $k++) {
					if(substr_count($mainLines[$k] , ' ') != 4){
							if($mainLines[$k] == (str_repeat(' ', 8).$projectType.":") ){
								$newIndex =$k;
							}
							if(str_repeat(' ', 12).'- "'.$projectName.'"' == $mainLines[$k]){
								$newIndex =0;
								break;
							}
					}else{
						break;
					}
				}
				!empty($newIndex) &&  $innerArray [$newIndex] = str_repeat(' ', 12).'- "'.$projectName.'"' ;
			}
			$newArray[]= $mainLines[$i];
			continue;
		}else{

			$dbIndex= (array_search('db:', $mainLines));
			if($dbIndex == $i && !in_array(str_repeat(' ', 4).$hostSection.":",$mainLines)){
				$newArray[$dbIndex]= str_repeat(' ', 4).$hostSection.":" ;
				$newArray[$dbIndex+1]= str_repeat(' ', 8).$projectType.":" ;
				$newArray[$dbIndex+2]=  str_repeat(' ', 12).'- "'.$projectName.'"' ;
				$newArray[]= $mainLines[$i];
				continue;
			}
		}
			$newArray[]= $mainLines[$i];
	}
	if(!empty($innerArray)){
		$innerKey=key($innerArray);
		array_splice($newArray,$innerKey+1 ,0, $innerArray[$innerKey]);
	}
	$handle=fopen($file, "w");
	foreach (array_filter($newArray) as $key => $line) {
		$line = $line."\r\n";
		fwrite($handle, $line);
	}
	fclose($handle);
}
addFrontConfig('chandresh','base','base');

?>