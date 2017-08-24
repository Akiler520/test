<?php
$itinerary = '{
	"Itinerary":{
		"DepartureCity":{
			"0":{
				"CityName" : "纽约华盛顿华盛顿华盛顿@@内部显示@@华盛顿华盛顿华盛顿华盛顿华盛顿华盛顿华盛顿华盛顿华盛顿华盛顿",
				"CityName2" : "ccccccccccccccc city 222222222"
			},
			"1":{
				"list"  :{
				    "name1" : "test name1",
				    "name2" : "testtt name2"
				},
				"CityName3" : {
				    "name3" : "华盛顿"
				}
			}
		},
		"ReturnCity" : {
			"0":{
				"CityName" : "纽约"
			}
		}
	}
}';
$itinerary11 = '"Itinerary":{
                    "name"  :{
                        "name1" : "test name1",
                        "name2" : "testtt name2"
                    }
				}';

// 直接循环到每层，获取key链，再到数据库中匹配

$itinerary = json_decode($itinerary, true);
//echo "<pre>";var_dump((current($itinerary['Itinerary'])));exit;


$translateRule['field_from'] = "Itinerary.DepartureCity.0.CityName";
$translateRule['field_to'] = "Itinerary.DepartureCity.CityList.0.CityName";
$translateRule['platform'] = "tmall";
$translateRule['object_type'] = "";     // category,product,product_line,module
$translateRule['object_id'] = "";       // categoryID,productID,productLine,tab name
$translateRule['rule'] = "string:5,30|replace:@@内部显示@@,|prefix:test_";

$FieldTranslateEngine = new FieldTranslate($translateRule);

$link = '';
$count = 0;
$linkData = $itinerary['Itinerary'];

while(is_array($linkData) && !empty($linkData)){
    foreach ($linkData as $key => &$item) {
        if(empty($item)){
            unset($linkData[$key]);
            continue;
        }
//        echo "<pre>-------";var_dump($key);
//        echo "<pre>-------";var_dump($item);
        $linkArr = $FieldTranslateEngine->getLink($linkData, $key, $item, $link);
    }

    echo "<pre>########## COUNT: {$count}";;
//    echo "<pre>########## data: "; var_dump($linkData);
    $count ++;


    if($count > 10){
        break;
    }
}

echo "<pre>"; var_dump($FieldTranslateEngine->getLinkArr());


/*$linkArr = $FieldTranslateEngine->getLink($itinerary, $link);
echo $linkArr;
echo "<pre>"; var_dump($FieldTranslateEngine->getLinkArr());*/
//echo "<pre>";var_dump($itinerary);

class FieldTranslate
{
    private $_platform = null;
    private $_translateRule = [];

    private $_linkArr = [];
    
    public function __construct($translateRule)
    {
        $this->setPlatform($translateRule['platform']);
        $this->_translateRule = $translateRule;
    }

    public function setPlatform($platform){
        $this->_platform = $platform;
    }
    
    public function translate(){

    }

    public function getLinkArr(){
        return $this->_linkArr;
    }

    public function getLink(&$linkData, $key, &$data, $link)
    {
        $arrayKey = array_keys($data);
        $first = current($data);
        $link .= $arrayKey[0] . '.';

        echo "<pre> #######dataaaaa#######";var_dump($data);
        echo "<pre> #######firstttt#######";var_dump($first);
        echo "<pre> #######keyyyyyyy#######";var_dump($arrayKey);
        $firstKey = '';
        is_array($first) && $firstKey = array_keys($first);
        if(empty($first) && is_array($data)){
            unset($data[$arrayKey[0]]);
        }

        if(is_array($first) && !empty($first)){
            echo "<pre> #######unsettt keyyyyyyy#######";var_dump($firstKey);

            unset($data[$arrayKey[0]][$firstKey[0]]);
            echo "<pre> #######uuuuuuet dataaaaa#######";var_dump($data);
            $this->getLink($linkData, $key, $first, $link);
//            $this->getLink($data, $arrayKey[0], $first, $link);
        }else{
//            unset($linkData[$key]);


            echo "<pre> ########notttttt array#######";var_dump($key);
            echo "<pre> ########notttttt array#######";var_dump($linkData);
            $this->_linkArr[] = $key . ".". $link;
            return $link;
        }
    }

    public function __getLink(&$data, $link){
        $is_break = false;
        foreach ($data as $key => $item) {
            if(is_array($item)){
                if($key == '0'){
                    var_dump($link."<br>");
                }
                $link .= $key . ".";
                $link .= $this->getLink($item, $link);
            }else{
                var_dump($link."--<br>");
                $link .= $key . ".";
                $this->_linkArr[] = $link;
                unset($data[$key]);

                $link = "";

                $is_break = true;
            }
        }

        return ($is_break) ? "" : $link;
    }
}

class FieldTranslateEngine
{

}

class FieldTranslateString
{
    private $_value = null;
    private $_rule = null;

    public function setValue($value){
        $this->_value = $value;
    }

    public function setRule($rule){
        $this->_rule = $rule;
    }
}


