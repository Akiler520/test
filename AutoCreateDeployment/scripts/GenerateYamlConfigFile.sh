#!/bin/bash

function usageMessage(){
    echo "
******************************************
e.g.:
    GenerateYamlConfigFile.sh -R search_sphinx_services -D /opt/app/nginx/html/qa1/search_sphinx_services -u search.services.qa4.tff.com
******************************
Arguments:
    -R <repositoryName>                 required,the name of repository
    -D <pathDeployment>                 required,the name of repository
    -H <hostSection>                    required,the name of current environment
    -u <URL>                            required,the URL of current environment
    -F <yamlFile>                       the name of yaml file
    -p <repositoryPath>                 the path of code repository on server, default: /opt/repos/qa
    -s <cemService>                     the URL of cem service, default: cem.services.qa1.tff.com
******************************************"
}

repositoryPath="/opt/repos/qa"
hostSection="qa1"
cemServer="cem.services.qa1.tff.com"
repositoryName=""
pathDeploymentRepository=""
yamlFile=""
URL=""

while getopts ":R:D:F:H:p:s:u:" opt
do
   case $opt in
        R)
            repositoryName=$OPTARG;;
        D)
            pathDeploymentRepository=$OPTARG;;
        u)
            URL=$OPTARG;;
        F)
            yamlFile=$OPTARG;;
        p)
            repositoryPath=$OPTARG;;
        H)
            hostSection=$OPTARG;;
        s)
            cemServer=$OPTARG;;
        ?)
            usageMessage
            exit 1;;
   esac
done

#####################################
## verify the required parameters ###
#####################################
function verifyRequest(){
    error=0

    if  [ ! -n "$URL" ] ;then
        echo "[Error] URL of service is required"
        error=1
    fi

    if  [ ! -n "$repositoryName" ] ;then
        echo "[Error] path of Git repositoryName is required"
        error=1
    fi

    if  [ ! -n "$pathDeploymentRepository" ] ;then
        echo "[Error] path of deployment  is required"
        error=1
    fi

    if [ ${error} == 1 ]; then
        usageMessage
        exit -1;
    fi
}

### verify the parameters first ###
verifyRequest

if [ ${repositoryName} == "yiiadmin" ] ; then
    configFile=./config/yiiadmin.yaml
elif [ ${repositoryName} == "yiifrontendtff" ]; then
    configFile=./config/yiifrontendtff.yaml
else
    configFile=./config/laravel.yaml
fi

### ./yaml/sphinx_search_services.yaml

if  [ ! -n "$yamlFile" ] ;then
    yamlFile="./yaml/${repositoryName}.yaml"
fi


### generate yaml for deployment
commonFile=./config/common.yaml
cat ${commonFile} > ${yamlFile}
cat ${configFile} >> ${yamlFile}

### replace deployment settings
sed -i "s/@@URL@@/${URL}/g" ${yamlFile}
sed -i "s/@@repositoryName@@/${repositoryName}/g" ${yamlFile}
sed -i "s#@@repositoryPath@@#${repositoryPath}#g" ${yamlFile}
sed -i "s#@@hostSection@@#${hostSection}#g" ${yamlFile}
sed -i "s#@@pathDeploymentRepository@@#${pathDeploymentRepository}#g" ${yamlFile}
sed -i "s/@@cemServer@@/${cemServer}/g" ${yamlFile}