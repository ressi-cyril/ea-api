# EA-api
EA-api is your one-stop solution for managing eSports tournaments.  
Built with the power of Symfony and FOSRestBundle, this RESTful API employs a State Pattern for efficient tournament management.

## Installation

1. Clone the Repository: ``$ git clone https://github/ressi-cyril/ea-api.git ``
2. Pull Docker Images: ``$ docker-compose pull``
3. Fire Up the Containers: ``$ docker-compose up -d``
4. Enter the Project Container: ``$ docker exec -it www-ea bash``
5. install Composer Dependencies: : ``$ composer install``
6. Run Database migration: ``$ php bin/console doctrine:migration:migrate``
7. Configure the JWT provider : ``$ php bin/console lexik:jwt:generate-keypair ``

For a detailed API guide, visit http://localhost/api/open/doc.

## Testing
To confirm the API's operational status:
```
$ docker exec -it www-ea bash
$ php bin/console doctrine:fixture:load
$ php bin/phpunit 
 ```

## Technologies Used
- Symfony 6.3: the framework of choice
- PHP 8.1: the scripting standard
- FOSRestBundle: For RESTful API development
- Docker: For containerization and environment consistency
- LexikJWTAuthenticationBundle: For secure JWT-based authentication
- OpenAPI: For comprehensive API documentation

## Contact
For any questions or collaborations, feel free to reach out:
- ressi.cyril@gmail.com
- https://www.linkedin.com/in/cyrilressi/
