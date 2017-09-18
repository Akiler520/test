#!/bin/bash

function usageMessage(){
    echo "
******************************************
usage:
    1. GenerateDeploymentSection.sh -H qa4 -u activity.services.qa4.tff.com -g ssh://git@git.tff.bz:1158/TFF/activity_product_services.git -t product
    2. GenerateDeploymentSection.sh -H qa4 -u yiiadmin.qa4.tff.com -g git@github.com:Tours4Fun/yiiadmin.git -r "" -t admin
    3. GenerateDeploymentSection.sh -H qa4 -u yiifrontendtff.qa4.tff.com -g git@github.com:Tours4Fun/yiifrontendtff.git -r "" -t tff

******************************************

Arguments:
    -H <hostSection>                    required,the name of current environment
    -u <URL>                            required,the URL of current environment
    -g <repositoryGit>                  required,the URL of code repository of git
    -p <repositoryPath>                 the path of code repository on server, default: /opt/repos/qa
    -d <pathDeployment>                 the path of code in web server, default: /opt/app/nginx/html
    -r <indexPathOfNginx>               the path of index.php in web server, default: public
    -t <repositoryType>                 the type of project,such as product,base,admin,tff,finance,user,order, default:base
    -c <creator>                        creator of env deploy, default:system
    -f <parentHost>                     parent of hostSection, default:qa1
    -s <cemService>                     the URL of cem service, default: cem.services.qa1.tff.com
    -N <ConfigPath>                     the conf path of nginx, default: /opt/app/nginx/conf
    -D <deployConfigPath>               the conf path of deploy yaml file, default: /opt/app/deployment/configs/projects/qa
    -M <mainDeployConfigPath>           the conf path of deploy main-qa.yaml file, default: /opt/app/deployment/configs/main-qa.yaml
    -h or --help                        print help (this message) and exit

******************************************
"
}

hostSection="qa1"
URL=""
repositoryGit=""
repositoryPath="/opt/repos/qa"
repositoryName=""
repositoryType="base"
pathDeployment="/opt/app/nginx/html"
pathRoot="public"
pathNginxConf="/opt/app/nginx/conf"
pathFrontDeploy="/opt/app/deployment/configs/projects/qa"
pathFrontDeployMain="/opt/app/deployment/configs/main-qa.yaml"
creator="system"
parentHost="qa1"
cemServer="cem.services.qa1.tff.com"
basePath=$(cd `dirname $0`; pwd)

while getopts ":H:u:g:p:d:r:t:i:c:f:P:s:N:D:M:" opt
do
   case $opt in
        H)
            hostSection=$OPTARG
            echo "[Params] host: $OPTARG";;
        u)
            URL=$OPTARG
            echo "[Params] URL: $OPTARG";;
        g)
            repositoryGit=$OPTARG
            echo "[Params] git: $OPTARG";;
        p)
            repositoryPath=$OPTARG
            echo "[Params] path: $OPTARG";;
        d)
            pathDeployment=$OPTARG
            echo "[Params] path of deploy: $OPTARG";;
        r)
            pathRoot=$OPTARG
            echo "[Params] path of server root: $OPTARG";;
        t)
            repositoryType=$OPTARG
            echo "[Params] path of server root: $OPTARG";;
        c)
            creator=$OPTARG
            echo "[Params] creator of env deploy: $OPTARG";;
        f)
            parentHost=$OPTARG
            echo "[Params] parentHost of host section: $OPTARG";;
        s)
            cemServer=$OPTARG
            echo "[Params] url of cem server: $OPTARG";;
        N)
            pathNginxConf=$OPTARG
            echo "[Params] the conf path of nginx: $OPTARG";;
        D)
            pathFrontDeploy=$OPTARG
            echo "[Params] the conf path of deploy yaml file: $OPTARG";;
        M)
            pathFrontDeployMain=$OPTARG
            echo "[Params] the conf path of deploy main-qa.yaml file: $OPTARG";;
        ?)
            usageMessage
            exit 1;;
   esac
done

### verify the required parameters ###
function verifyRequest(){
    error=0
    if  [ ! -n "$hostSection" ] ;then
        echo "[Error] the name of environment is required"
        error=1
    fi

    if  [ ! -n "$URL" ] ;then
        echo "[Error] URL of service is required"
        error=1
    fi

    if  [ ! -n "$repositoryGit" ] ;then
        echo "[Error] path of Git repository is required"
        error=1
    fi

    if [ ${error} == 1 ]; then
        usageMessage
        exit -1;
    fi
}

### verify the parameters first ###

verifyRequest

### get the name of code repsoitory ###
function getRepositoryName(){
    repositoryNameArr=(${repositoryGit//\// })
    repositoryNameTMP=""
    
    for repositoryNameStr in ${repositoryNameArr[@]}
    do
        repositoryNameTMP=${repositoryNameStr}
    done
    
    repositoryName=(${repositoryNameTMP//./ })
}

getRepositoryName

############ start to create deploy environments ######################

if  [ -n "$pathRoot" ] ;then
    pathRoot=${pathDeployment}/${hostSection}/${repositoryName}/${pathRoot}
else
    pathRoot=${pathDeployment}/${hostSection}/${repositoryName}
fi

############ start to create config file of nginx ###############
sh ./scripts/GenerateNginxConfigFile.sh ${URL} ${pathRoot} ${repositoryName} ${pathNginxConf} ${hostSection} ${repositoryType}
sh ./scripts/GenerateRepositoryCode.sh ${repositoryPath} ${repositoryName} ${repositoryType} ${repositoryGit} ${pathFrontDeploy} ${pathDeployment} ${hostSection} ${URL} ${cemServer}

### insert main host into cem->env ###
php ${basePath}/scripts/CreateDeploymentSection.php ${hostSection} ${creator} ${parentHost}
php ${basePath}/scripts/CreateDeploymentYamlLevel.php ${pathFrontDeployMain} ${hostSection} ${repositoryType} ${repositoryName}

### restart nginx and deployment service ###
echo "
************************************
[Service] restarting related service ......
"

/etc/init.d/nginx restart && supervisorctl restart deployment

echo "
[success] Generate successful
************************************
"
