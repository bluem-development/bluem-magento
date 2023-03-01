#!/bin/bash

helpFunction()
{
   echo ""
   echo "Usage: $0 -p project"
   echo "\t-p The name of the Docker project (default: bluem-magento2-dev)"
   exit 1 # Exit script after printing help
}

while getopts "p:" opt
do
   case "$opt" in
      p ) parameterP="$OPTARG" ;;
      ? ) helpFunction ;; # Print helpFunction in case parameter is non-existent
   esac
done

# Print helpFunction in case parameters are empty
if [ -z "$parameterP" ]
then
   echo "Some or all of the parameters are empty";
   helpFunction
fi

echo "Changing directory permissions (public folders)..";
docker exec -it $parameterP chmod -R 0777 bitnami/magento/var/ bitnami/magento/pub/ bitnami/magento/generated/

echo "Removing cached, preprocessed views, generated and static files..";
docker exec -it $parameterP rm -rf bitnami/magento/var/cache/* bitnami/magento/var/view_preprocessed/* bitnami/magento/generated/* bitnami/magento/pub/static/*

echo "Do Magento upgrade..";
docker exec -it $parameterP php bitnami/magento/bin/magento setup:upgrade

echo "Do Magento compiling..";
docker exec -it $parameterP php bitnami/magento/bin/magento setup:di:compile

echo "Do Magento static deployment..";
docker exec -it $parameterP php bitnami/magento/bin/magento setup:static-content:deploy -f

echo "Clean Magento cache..";
docker exec -it $parameterP php bitnami/magento/bin/magento cache:clean

echo "Flush Magento cache..";
docker exec -it $parameterP php bitnami/magento/bin/magento cache:flush

echo "Do Magento reindexing..";
docker exec -it $parameterP php bitnami/magento/bin/magento indexer:reindex

echo "Changing directory permissions..";
docker exec -it $parameterP chmod -R 0777 bitnami/magento/var/ bitnami/magento/pub/ bitnami/magento/generated/

echo "Restarting Docker container..";
docker restart $parameterP

echo "Done with all tasks!";
