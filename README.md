# EasyFarma Despachos

### Endpoints

El plugin provee el endpoint

GET /wp-json/orders/v1/get/{id-orden}

Este plugin debe usarse de forma conjunta con el plugin auth4wp el cual provee el endpoint

POST /wp-json/auth/v1/login

{
    "username": "{usuario}",
    "password": "{password}"
}

Además para enviar imágenes se dispone de

POST /wp-json/ez_files/v1/post