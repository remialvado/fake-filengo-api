# Test

Start with `php bin/console server:run`
On another terminal : 
  - to create a new user : `curl -i -X POST -H'Authorization: Bearer abcd' -d'{"email":"remi.alvado@gmail.com", "firstName": "Rémi", "lastName": "Alvado"}' 'http://localhost:8000/api/v1/account'`
  - to check what the next required info is : `curl -i -X GET  -H'Authorization: Bearer abcd' 'http://localhost:8000/api/v1/next-info/dc8c5d4bedd6594c228e7fc49815e5cd6345dcd4'`
  - to send answer for next info : `curl -X PUT -H'Authorization: Bearer abcd' -d'{"nationality":"française"}' 'http://localhost:8000/api/v1/account/dc8c5d4bedd6594c228e7fc49815e5cd6345dcd4'`
  - to get matching financial services : `curl -i -X GET  -H'Authorization: Bearer abcd' 'http://localhost:8000/api/v1/financial-services/dc8c5d4bedd6594c228e7fc49815e5cd6345dcd4'`
  
Enjoy :)

# Code

/!\ This API is just a fake for test purposes. It does not handle security or communicate with MongoDB the right way. It's just a quick and dirty fake example without any tests.

Basically, the idea is to describe wha t a financial service is using a DSL (required infos, constraints & conditions) and to run this description against a simple matching engine.
I've borrowed some ideas from Hamcrest to help me write the matchers and from Symfony DI component to fluently describe what a financial service is.
Every important piece of code is inside `src/AppBundle`.
 
I didn't do much tests so there might be some issues :)
