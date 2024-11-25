FROM nginx:alpine

COPY ./hosts /etc/nginx/templates

WORKDIR /var/www/app

EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]