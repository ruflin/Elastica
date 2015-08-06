FROM nginx:1.9
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

# Copy config files
COPY nginx.conf /etc/nginx/
COPY mime.types /etc/nginx/

# Expose standard ports and 2 tests ports
EXPOSE 80 12345 12346
