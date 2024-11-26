FROM node:21

RUN npm install -g @angular/cli

WORKDIR /alegra-app

COPY . .

RUN npm install

EXPOSE 4200

CMD ["ng", "serve", "--host", "0.0.0.0", "--port", "4200"]
