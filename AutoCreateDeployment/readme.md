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
    -P <phpCommandPath>                 path of php commands, used to update main-qa.yaml and the cem->env table, default: /home/commands/auto_create_deploy
    -s <cemService>                     the URL of cem service, default: cem.services.qa1.tff.com
    -N <ConfigPath>                     the conf path of nginx, default: /opt/app/nginx/conf
    -D <deployConfigPath>               the conf path of deploy yaml file, default: /opt/app/deployment/configs/projects/qa
    -M <mainDeployConfigPath>           the conf path of deploy main-qa.yaml file, default: /opt/app/deployment/configs
    -h or --help                        print help (this message) and exit

******************************************

############## auto commands for cem config of qa4 - Laravel ##############

GenerateDeploymentSection.sh -H qa4 -u shuttle.services.qa4.tff.com -g ssh://git@git.tff.bz:1158/TFF/shuttle_product_services.git -t product

GenerateDeploymentSection.sh -H qa4 -u search.services.qa4.tff.com -g ssh://git@git.tff.bz:1158/TFF/search_sphinx_services.git

GenerateDeploymentSection.sh -H qa4 -u productapi.services.qa4.tff.com -g ssh://git@git.tff.bz:1158/TFF/product_api_services.git -t product

GenerateDeploymentSection.sh -H qa4 -u activity.services.qa4.tff.com -g ssh://git@git.tff.bz:1158/TFF/activity_product_services.git -t product

GenerateDeploymentSection.sh -H qa4 -u tour.services.qa4.tff.com -g ssh://git@git.tff.bz:1158/TFF/tour_product_services.git -t product

GenerateDeploymentSection.sh -H qa4 -u allproduct.services.qa4.tff.com -g ssh://git@git.tff.bz:1158/TFF/all_product_services.git -t product

GenerateDeploymentSection.sh -H qa4 -u opsp.services.qa4.tff.com -g  ssh://git@git.tff.bz:1158/TFF/opspsvcs.git

GenerateDeploymentSection.sh -H qa4 -u distribution.services.qa4.tff.com -g ssh://git@git.tff.bz:1158/TFF/distribution_service -t distribution


######### administration ###########

GenerateDeploymentSection.sh -H qa4 -u administration.qa4.tff.com -g ssh://git@git.tff.bz:1158/TFF/administration.git -t admin


############## qa4 -  yiifrontendtff, yiiadmin, yiimode ############

GenerateDeploymentSection.sh -H qa4 -u yiiadmin.qa4.tff.com -g git@github.com:Tours4Fun/yiiadmin.git -r "" -t admin

GenerateDeploymentSection.sh -H qa4 -u yiifrontendtff.qa4.tff.com -g git@github.com:Tours4Fun/yiifrontendtff.git -r "" -t tff

GenerateDeploymentSection.sh -H qa1 -u hotel.services.qa1.tff.com -g git@github.com:Tours4Fun/hotel_services.git -r "" -t product
