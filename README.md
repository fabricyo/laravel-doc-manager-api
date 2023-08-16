#Meu projeto de teste

- ```git clone https://github.com/fabricyo/laravel-doc-manager-api.git```


- ```docker-compose up -d --build```


- ```docker exec Serve cp .env.example .env```


- ```docker exec Serve php artisan key:generate```


- ```docker exec Serve php artisan migrate```

App URL: http://localhost:8000

Docs: http://localhost:8000/docs

PhpMyAdmin: http://localhost:8081

PhpMyAdmin logar:
 - database:
   - db
 - login:
   - root
 - pass:
   - notSecureChangeMe


At json request, add to headers, in the Accept key, the value application/json
