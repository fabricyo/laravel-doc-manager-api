# Meu projeto de teste

- ```git clone https://github.com/fabricyo/laravel-doc-manager-api.git```


- ```docker-compose up -d --build```


- ```docker exec Serve cp .env.example .env```


- ```docker exec Serve php artisan key:generate```


- ```docker exec Serve php artisan migrate```

App URL: http://localhost:8000

Docs: http://localhost:8000/docs

PhpMyAdmin: http://localhost:8081

PhpMyAdmin login:
 - database:
   - db
 - login:
   - root
 - pass:
   - notSecureChangeMe


## Using Postman
Add to headers, in the Accept key, the value application/json


Managing the document is a little bit complex, so this some examples

### Creating a document
![create_doc.png](Prints%2Fcreate_doc.png)

### Updating a document
![update_doc.png](Prints%2Fupdate_doc.png)

### Downloading a document
![download_pdf.png](Prints%2Fdownload_pdf.png)
