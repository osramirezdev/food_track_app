FROM node:20

RUN npm install -g @angular/cli

WORKDIR /alegra-app

COPY package*.json ./
RUN npm install

COPY . .

CMD ng serve --host 0.0.0.0 --port 4200

EXPOSE 4200
