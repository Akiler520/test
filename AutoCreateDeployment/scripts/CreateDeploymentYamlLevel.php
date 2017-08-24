<?php
/**
 * add the menu of the deployment in front page
 *
 * User: martin
 * Date: 2017/3/23
 * Time: 16:41
 */

if($argc < 4){
    echo "[Error] Parameter is invalid \r\n";
    echo "usage: php CreateDeploymentYamlLevel.php [main-qa path] [host section] [project type] [project name] \r\n";
    exit;
}

$configFile = $argv[1];
$hostSection = $argv[2];
$projectType = $argv[3];
$projectName = $argv[4];

addFrontConfig($configFile, $hostSection, $projectType, $projectName);

function addFrontConfig($file, $hostSection,$projectType,$projectName){
    if(!is_file($file)){
        echo "[Error] invalid file: {$file} \r\n";
        return false;
    }
    $handle = fopen($file, "r+");

    if(!$handle){
        echo "[Error] can't open the file: {$file} \r\n";
        return false;
    }

    $contents=file_get_contents($file);
    $mainLines=preg_split("/[\r\n]+/s", $contents);
    $newArray = [];
    for ($i=0; $i < count($mainLines) ; $i++) {
        $line=preg_split("/[\r\n]+/s", $mainLines[$i]);
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