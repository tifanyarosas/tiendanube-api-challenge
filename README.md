## TiendaNube challenge

Ver enunciado [aquí](https://github.com/TiendaNube/tech-challenge/tree/master/nodejs#es-ar-)

Para resolver el ejercicio utilice PHP y Laravel.

### Cómo ejecutarlo?

Una vez clonado el repositorio, correr los siguientes comandos:
```
composer install
php artisan serve
```

Tener en cuenta que debe estar corriendo la Mock Api (ver instrucciones en enunciado).

Los endpoints creados son:

`POST http://127.0.0.1:8000/api/transaction`

`GET http://127.0.0.1:8000/api/payables?startDate=yyy-mm-dd&endDate=yyyy-mm-dd`

(En caso de testearlo usando Postman, agregar a los headers del request `X-Requested-With: XMLHttpRequest`)
### Asunsiones hechas

- No existen merchants diferenciados, es decir todos las transacciones y payables son de un mismo merchant. Para poder soportarlo deberiamos agregar un identificador de merchant a cada transaccion/payable

### Posibles mejoras

- Implementar más tests.
- Poder diferenciar entre payables de diferentes merchants.
- Agregar validaciones faltantes.
- Retornar un mensaje de error más informativo si la creacion de la transaccion falla.
