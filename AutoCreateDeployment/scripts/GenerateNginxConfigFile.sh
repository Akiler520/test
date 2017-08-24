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

    if [ ${repositoryName} == "yiiadmin" ] ; then
        createConfNginxYiiadmin
    elif [ ${repositoryName} == "yiifrontendtff" ]; then
        createConfNginxYiifrontend
    else
        createConfNginxLaravel
    fi

    echo "[Nginx]create config file file new repository: ${URL}, ${config_file}"
}

#############################################
## create config file of nginx for yiiadmin #
#############################################
function createConfNginxYiiadmin(){
    echo "server {" > ${config_file}
    echo "    server_name ${URL};" >> ${config_file}
    echo "    root ${pathRoot}/${repositoryName};" >> ${config_file}
    echo "    error_log ${log_path}/${URL}.error.log;" >> ${config_file}
    echo "    " >> ${config_file}
    echo "    include laravel;" >> ${config_file}
    echo "}" >> ${config_file}
}

################################################
## create config file of nginx for Yiifrontend #
################################################
function createConfNginxYiifrontend(){
    echo "server {" > ${config_file}
    echo "    listen 80;" >> ${config_file}
    echo "    listen 443 ssl;" >> ${config_file}

    echo "    server_name ${URL};" >> ${config_file}
    echo "    root ${pathRoot}/${repositoryName};" >> ${config_file}
    echo "    error_log ${log_path}/${URL}.error.log;" >> ${config_file}

    echo "    " >> ${config_file}

    echo "    location / {" >> ${config_file}
    echo "        index index.php;" >> ${config_file}
    echo "        autoindex on;" >> ${config_file}
    echo "        try_files \$uri \$uri/ /index.php?\$query_string;" >> ${config_file}
    echo "    }" >> ${config_file}

    echo "    " >> ${config_file}

    echo "    location ~ index\.php$ {" >> ${config_file}
    echo "        include fastcgi.conf;" >> ${config_file}
    echo "        fastcgi_param  SERVER_NAME \$host;" >> ${config_file}
    echo "        fastcgi_pass   127.0.0.1:9000;" >> ${config_file}
    echo "        fastcgi_param  FILE_STORAGE_PATH /opt/data;" >> ${config_file}
    echo "        fastcgi_param  FILE_STORAGE_PREFIX f1;" >> ${config_file}
    echo "        include fastcgi_params;" >> ${config_file}
    echo "        fastcgi_index  index.php;" >> ${config_file}
    echo "    }" >> ${config_file}

    echo "    " >> ${config_file}

    echo "    include ssl.inc;" >> ${config_file}
    echo "}" >> ${config_file}

    echo "createConfNginxYiifrontend"
}

####################################################
## create config file of nginx for Laravel project #
####################################################
function createConfNginxLaravel(){
    echo "server {" > ${config_file}

    if [ ${repositoryName} == "administration" ] ; then
        echo "    server_name ${URL} *.${URL};" >> ${config_file}
    else
        echo "    server_name ${URL};" >> ${config_file}
    fi

    echo "    root ${pathRoot};" >> ${config_file}
    echo "    error_log ${log_path}/${URL}.error.log;" >> ${config_file}
    echo "    " >> ${config_file}
    echo "    include laravel;" >> ${config_file}
    echo "}" >> ${config_file}
}

createConfNginx