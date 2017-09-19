#!/bin/bash
URL=$1
pathRoot=$2
repositoryName=$3
pathNginxConf=$4
hostSection=$5
repositoryType=$6
config_file=""
log_path=""

#############################################
## create config file of nginx            ###
#############################################
function createConfNginx(){
    config_path="${pathNginxConf}/${hostSection}.d/${repositoryType}"
    log_path="/opt/app/nginx/logs/${hostSection}"

    if [ ! -x "${config_path}" ]; then
        mkdir -p "${config_path}"
    fi

    if [ ! -x "${log_path}" ]; then
        mkdir -p "${log_path}"
    fi

    config_file="${config_path}/${URL}.conf"

    touch ${config_file}

    createConfOfWebServer

    echo "[Nginx]create config file file new repository: ${URL}, ${config_file}"
}

####################################################
## create config file of nginx for Laravel project #
####################################################
function createConfOfWebServer(){
    ServeName=""
    configFile=./config/nginx/common.conf
    logFile="${log_path}/${URL}.error.log"

    if [ ${repositoryName} == "administration" ] ; then
        ServeName=${URL}\ *.${URL}
    else
        ServeName=${URL}
    fi

    if [ ${repositoryName} == "yiiadmin" ] ; then
        pathRoot=${pathRoot}/${repositoryName}
    fi

    if [ ${repositoryName} == "yiifrontendtff" ] ; then
        pathRoot=${pathRoot}/${repositoryName}
        configFile=./config/nginx/yiifrontend.conf
    fi

    cat ${configFile} > ${config_file}

    sed -i "s#@@URL@@#${ServeName}#g" ${config_file}
    sed -i "s#@@pathRoot@@#${pathRoot}#g" ${config_file}
    sed -i "s#@@logFile@@#${logFile}#g" ${config_file}
}

createConfNginx