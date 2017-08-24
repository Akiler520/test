<?php
/**
 * usage: php updateEnvDeploy.php [host section] [creator] [parent host]
 *
 * User: martin
 * Date: 2017/3/22
 * Time: 10:36
 */

// check the parameters of command
if($argc < 3){
    echo "host section, creator are required\r\n";
    echo "usage: php updateEnvDeploy.php [host section] [creator] [parent host]\r\n";
    exit;
}

$hostName = $argv[1];
$owner = $argv[2];

if(!$hostName || !$owner){
    echo "host section, creator are required\r\n";
    echo "usage: php updateEnvDeploy.php [host section] [ip] [creator] [parent host]\r\n";
    exit;
}

$parentName = "";

if($argc >= 4){
    $parentName = $argv[3];
}

if($hostName == $parentName){
    $parentName = "";
}

$config = include("config/config.php");
$configDB = $config['database'];

// database connection information
$dbHost = $configDB['host'];
$dbUser = $configDB['user'];
$dbPassword = $configDB['password'];
$dbName = $configDB['database'];

// connect database
$mysqlObj = Martin_Mysql::getInstance()->connect($configDB['host'], $configDB['user'], $configDB['password'], $configDB['database']);

// set table
$mysqlObj->setTable($configDB['table']);

// check whether the host is exist in current database
$HostList = $mysqlObj->select("`name`='{$hostName}'");

if(!$HostList){
    // try to add new one
    $hostInfo = [
        'name'  => $hostName,
        'parent_name'   => $parentName,
        'created_by'    => $owner,
        'created_at'    => date("Y-m-d H:i:s"),
    ];
    $retInsert = $mysqlObj->insert($hostInfo);

    if($retInsert){
        echo "[ENV setting] create new host section info cem->env successful\r\n";
    }else{
        echo "[ENV setting] error happened when create new host section info cem->env\r\n";
    }
}else{
    echo "[ENV setting] host section is exist in host table\r\n";
}

class Martin_Mysql{
    /**
     * host address of database
     * @var
     */
    protected $host;

    /**
     * user name to connect the database
     * @var
     */
    protected $user;

    /**
     * password
     * @var
     */
    protected $pwd;

    /**
     * the name of database
     * @var
     */
    protected $dbName;

    /**
     * prefix of table
     * @var
     */
    protected $dbPrefix;

    /**
     * charset of database
     * @var
     */
    protected $charset;

    /**
     * current executing sql string
     * @var
     */
    protected $sql;

    /**
     * the fields of current selected table
     * @var
     */
    protected $fields;

    /**
     * the connect source of database
     * @var
     */
    protected $connectID;

    /**
     * current selected table
     * @var
     */
    protected $tabName;

    /**
     * if cache the fields of table
     * @var bool
     */
    protected $cache = true;

    /**
     * the path of cache file
     * @var string
     */
    protected $cachePath = 'db_cache';

    /**
     * object of current class
     *
     * @var object
     */
    private static $_instance;

    /**
     * debug
     * @var int
     */
    private $debug = true;

    private function __construct(){

    }

    /**
     * instance of the class
     * to avoid create multiple instance of class
     * @return Model|object
     */
    public static function getInstance()
    {
        if(!isset(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }

    public function connect($dbHost, $dbUser, $dbPassword, $dbName = '', $dbPrefix = '', $charSet = 'utf8')
    {
        $this->host = $dbHost;
        $this->user = $dbUser;
        $this->pwd = $dbPassword;
        $this->dbPrefix = $dbPrefix;
        $this->dbName = $dbName;
        $this->charset = $charSet;

        $this->connectID = @mysqli_connect($dbHost, $dbUser, $dbPassword);

        if(!$this->connectID) $this->halt('Can not connect to MySQL server');

        $this->charset = $charSet;
        $this->setCharset();

        if($dbName && !$this->selectDb()) $this->halt('Cannot use database '.$dbName);

        return $this;
    }

    /**
     * the the name of the table current operated
     * @param $tableName
     */
    public function setTable($tableName){
        $this->tabName = $this->dbPrefix.$tableName;
    }

    /**
     * set the name of database
     *
     * @param string $dbName
     * @return bool
     */
    protected function selectDb($dbName = ''){
        if(empty($dbName)){
            $dbName = $this->dbName;
        }

        if(!@mysqli_select_db($this->connectID, $dbName)) return false;

        $this->dbName = $dbName;

        return true;
    }

    /**
     * insert data by array
     *
     * @param $data
     * @return bool|int
     */
    public function insert($data){
        // get keys of new data
        $keys = array_keys($data);

        $this->sql = "INSERT INTO `$this->tabName`(`".implode('`,`', $keys)."`) VALUES('".implode("','", $data)."')";

        return $this->execute($this->sql);
    }

    /**
     * update data
     *
     * @param $data
     * @param $where
     * @return bool|int
     */
    public function update($data, $where){
        $keys = array_keys($data);
        $newKey = array_intersect($keys, $this->fields);

        $valueParam = '';
        $values = array();

        foreach($data as $key => $value){
            if(!in_array($key, $newKey)){
                continue;
            }

            $valueParam .= ", `$key`='$value'";
            $values[] = $value;
        }

        if($where){
            $valueParam = trim($valueParam, ', ');

            $this->sql = "UPDATE `$this->tabName` SET $valueParam WHERE $where";
        }
        else
        {
            $this->sql = "REPLACE INTO `$this->tabName`(`".implode('`,`', $newKey)."`) VALUES('".implode("','", $values)."')";
        }

        return $this->execute($this->sql);

    }

    /**
     * get data
     *
     * @param string $where
     * @param string $fields
     * @param string $order
     * @param string $limit
     * @param int $result_type
     * @return array|bool
     */
    public function select($where='', $fields = '*', $order='', $limit='', $result_type = MYSQLI_ASSOC){
        if(!empty($where)){
            $where=' where '.$where;
        }
        if(!empty($order)){
            $order=' order by '.$order;
        }
        if(!empty($limit)){
            if(is_array($limit)){
                $limit=' limit '.$limit[0].','.$limit[1];
            }else{
                $limit=' limit '.$limit;
            }
        }

        $this->sql = "select $fields from $this->tabName $where $order $limit";

        return $this->query($this->sql);

    }

    /**
     * delete data
     *
     * @param array|string $data
     * @param string $where
     * @return bool|int
     */
    public function delete($data, $where=''){
        //delete from TABLE_NAME where id=;
        //delete from TABLE_NAME where id in();
        //delete from TABLE_NAME  order by  limit;
        //delete from TABLE_NAME where
        if(!empty($where)){
            $where=' where '.$where;

            $this->sql="delete from ".$this->tabName.$where;
        }else{
            if(is_array($data)){
                $data=join(',',$data);
            }
            $fields=$this->fields['_pk'];   // primary key

            $this->sql="delete from ".$this->tabName." where $fields in ($data)";
        }
        return $this->execute($this->sql);
    }

    /**
     * if $this->debug = true, break when error happen
     *
     * @param string $message
     * @param string $sql
     * @param string $cut
     */
    public function halt($message = '', $sql = '', $cut = '')
    {
        if($this->debug)
        {
            echo "MySQL Error:{$this->error()}{$cut}";
            if(!empty($sql)) echo "MySQL Query:{$sql}{$cut}";
            if(!empty($message)) echo "Message:{$message}{$cut}";
            exit;
        }
    }

    /**
     * set the charset of the database
     */
    protected function setCharset(){
        $charSet = $this->charset;

        mysqli_query($this->connectID, "SET $charSet");
    }

    /**
     * execute a sql string and get result data
     *
     * @param $sql
     * @return array|bool
     */
    public function query($sql){
        $rows = array();

        $result = mysqli_query($this->connectID,$sql);

        if($result && mysqli_affected_rows($this->connectID)){
            while($row = mysqli_fetch_assoc($result)){
                $rows[] = $row;
            }
        }else{
            return false;
        }

        return $rows;
    }

    /**
     * only execute the sql string, no data return but boolean
     *
     * @param $sql
     * @return bool|int
     */
    public function execute($sql){
        $result = mysqli_query($this->connectID,$sql);
        if($result && mysqli_affected_rows($this->connectID)){
            return mysqli_affected_rows($this->connectID);
        }else{
            return false;
        }
    }

    /**
     * close the connection of database when instance is destroyed
     */
    public function __destruct(){
        $this->close();
    }

    /**
     * get error message
     *
     * @return string
     */
    public function error()
    {
        return @mysqli_error($this->connectID).'('.intval(@mysqli_errno($this->connectID)).')';
    }

    public function debug($debug){
        $this->debug = $debug;
    }

    /**
     * close the connection of database
     *
     * @return bool
     */
    public function close()
    {
        return mysqli_close($this->connectID);
    }

}


