# My laravel api project

This is just a simple laravel api project,
I'm not very used to write microservices, 
so I know that it might have a lot of mistakes here,
so please, you can point them if you want.

```
git clone https://github.com/fabricyo/laravel-doc-manager-api.git
```

```
docker-compose up -d --build
```

```
docker exec Serve cp .env.example .env
```

```
docker exec Serve php artisan key:generate
```

```
docker exec Serve php artisan migrate
```

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

## Document
The document info is based on the column_document relationship
You can use any columns that you want, but, only if it's of the same type of the document

## Tests
If you want, there's some simple tests. 
You can run it with:

```
docker exec Serve php artisan test
```
![tests.png](Prints%2Ftests.png)

## Using Postman
- Add to headers, in the Accept key, the value application/json


Managing the document is a little bit complex, so this some examples

### Creating a document
![create_doc.png](Prints%2Fcreate_doc.png)

### Updating a document
![update_doc.png](Prints%2Fupdate_doc.png)

### Downloading a document
![download_pdf.png](Prints%2Fdownload_pdf.png)
