#!/bin/bash

repositoryPath=$1
repositoryName=$2
repositoryType=$3
repositoryGit=$4

pathFrontDeploy=$5
pathDeployment=$6

hostSection=$7
URL=$8
cemServer=$9

repositoryPathSection="${repositoryPath}/${hostSection}"
repositoryFilePath="${repositoryPathSection}/${repositoryName}"

#############################################
## clone code repository from git ###########
#############################################
function repositoryClone(){
    if [ ! -x "${repositoryPathSection}" ]; then
        mkdir -p "${repositoryPathSection}"
    fi

    if [ ! -d "${repositoryFilePath}" ]; then
        cd ${repositoryPathSection} && git clone ${repositoryGit} && cd -
    fi

    echo "[Repository] git clone the repository: ${repositoryGit}"
}

#############################################
## create the config file for front page ####
#############################################
function repositoryCreatePathFront(){
    frontProjectConfPath="${pathFrontDeploy}/${hostSection}/${repositoryType}"
    pathDeploymentRepository="${pathDeployment}/${hostSection}/${repositoryName}"
    frontProjectConfFile="${frontProjectConfPath}/${repositoryName}.yaml"

    if [ ! -x "${frontProjectConfPath}" ]; then
        mkdir -p "${frontProjectConfPath}"
    fi

    if [ ! -x "${pathDeploymentRepository}" ]; then
        mkdir -p "${pathDeploymentRepository}"
    fi

    if [ ! -f "${frontProjectConfFile}" ]; then
        touch ${frontProjectConfFile}
    fi

    sh ./scripts/GenerateYamlConfigFile.sh -H ${hostSection} -R ${repositoryName} -D ${pathDeploymentRepository} -u ${URL} -F ${frontProjectConfFile} -s ${cemServer} -p ${repositoryPath}

    echo "[Repository] add path of the repository in front page: ${repositoryPath}, ${pathDeployment}"
}

#############################################
## check the cem file and the env file   ####
#############################################
function repositoryEnvCheck(){
    cemConfFile="${repositoryFilePath}/cem.conf"
    cemTemplateFIle="${repositoryFilePath}/.env.template.cem"

    if [ ! -f "${cemConfFile}" ]; then
        echo "[Error][${cemConfFile}] does not exist! cem.conf and .evn.template.cem are required"
        exit -1;
    fi

    if [ ! -f "${cemTemplateFIle}" ] && [ ${repositoryName} != "yiiadmin" ] && [ ${repositoryName} != "yiifrontendtff" ]; then
        echo "[Error][${cemTemplateFIle}] does not exist! cem.conf and .evn.template.cem are required"
        exit -1;
    fi

    echo "[Repository] check env settings"
}

#############################################
## TODO: check the cem config in database ###
#############################################
function repositoryCemCheck(){
    echo "[Repository] check cem config of database"
}

repositoryClone
repositoryCreatePathFront
repositoryEnvCheck
repositoryCemCheck
