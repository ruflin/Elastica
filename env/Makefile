#/bin/bash

# This creates the base image locally for local development. In case no local development is done anymore, make sure to remove this image.
all: nginx-image elasticsearch-image elastica-dev-image elastica-image elastica-data
	# elastica image has to be built after elastica-dev image as it depends on it. Otherwise the remote image is fetched.
	
elastica-image:
	docker build -t ruflin/elastica ../

elastica-dev-image:
	docker build -t ruflin/elastica-dev-base elastica/


elasticsearch-image:
	docker build -t ruflin/elasticsearch-elastica elasticsearch/

nginx-image:
	cd nginx
	docker build -t ruflin/nginx-elastica nginx/
	
elastica-data:
	docker build -t ruflin/elastica-data data/

# Removes all local images
clean:
	-docker rmi ruflin/elastica-dev-base
	-docker rmi ruflin/elasticsearch-elastica
	-docker rmi ruflin/nginx-elastica
	-docker rmi ruflin/elastica
	-docker rmi ruflin/elastica-data
	
# Pushs images as latest to the docker registry. This is normally not needed as they are directly fetched and built from Github
push:
	docker push ruflin/elastica-dev-base
	docker push ruflin/elasticsearch-elastica
	docker push ruflin/nginx-elastica
	docker push ruflin/elastica
	
	
destroy:
	docker stop $(shell docker ps -a -q)
	docker rm $(shell docker ps -a -q)
	docker rmi $(shell docker images -q)
