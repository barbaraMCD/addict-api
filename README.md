# HELLO

Welcome to my project, the goal is to track bad habits and help you to get rid of them.

# INSTALLATION

To run this project u can use the following command (from the Makefile):

```
make install
make run
```

# TESTING

```
make fixtures
make test
```


# DOCKER COMPOSE STACK


| service    | port | identifiant | password |
|------------|------|-------------|----------|
| API        | 8000 |             |          |
| POSTGRESQL | 5432 | app         | addict   |
| ADMINER    | 8080 | app         | addict   |


# ROUTING

To see all routes use the following command:

```
make cli
bin/console debug:router
```