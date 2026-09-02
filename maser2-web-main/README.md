Plataforma MASER
=============================================
Plataforma web desenvolvida para Maser Transportes.

## Compatibilidade

### Ambiente de desenvolvimento local

| Nome                      | Versão                |
|---------------------------|-----------------------|
| WampServer                | 3.3.7                 |
| Apache                    | 2.4.62.1              |
| PHP                       | 8.4.0                 |
| MySql                     | 8.4.6                 |

### Depedências

| Nome                      | Versão                |
|---------------------------|-----------------------|
| Composer                  | 2.8.4                 |
| Laravel                   | 11.31                 |
| Node.js                   | 12.14.1               |
| - NPM                     | 6.13.6                |
| - Vue                     | 2.9.6                 |

### Ferramentas

| Nome                      | Versão                                  |
|---------------------------|-----------------------------------------|
| Visual Studio Code        | 1.118.0                                 |
| HeidiSql                  | 12.20.0.7320                            |
| Postman                   | 11.88.1                                 |
| Google Chrome             | 147.0.7727.138 (Versão oficial) 64 bits |

## Manual de instalação

### Criar tabelas e campos no Banco de Dados
```
php artisan migrate
```

### Instalar dependências
```
composer install ou composer update
```

```
npm install
```

### Rodar (localhost)
```
php artisan serve
```

```
npm run watch
```

### Compilar aplicação (plataforma web)
Compile a aplicação:
```
npm run prod
```