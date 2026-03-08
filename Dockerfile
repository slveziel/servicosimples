FROM nginx:alpine

# Configure nginx to listen on port 8080
RUN echo 'server { listen 8080; location / { root /usr/share/nginx/html; index index.html; } }' > /etc/nginx/conf.d/default.conf

COPY public/ /usr/share/nginx/html/

EXPOSE 8080

CMD ["nginx", "-g", "daemon off;"]
