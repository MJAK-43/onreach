FROM node:22-alpine AS base
WORKDIR /app

FROM base AS dev
COPY package.json package-lock.json* ./
RUN npm ci
COPY . .
EXPOSE 5173
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]

FROM base AS build
COPY package.json package-lock.json* ./
RUN npm ci
COPY . .
ARG VITE_API_URL=http://api.localhost
ENV VITE_API_URL=$VITE_API_URL
RUN npm run build

FROM nginx:alpine AS prod
COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
