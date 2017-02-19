## Configuração do Ambiente de Desenvolvimento

>Instalar Composer: *https://getcomposer.org/download/* 

> mais informações em caso de problemas na instalação: *https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-14-04*

###### Após instalar composer, clonar o diretório, entre no mesmo e execute:


> composer install


##### Versão do PHP: 5.6 
- Mod_Rewrite ativo
- No apache a opção AllowOverride deve etsar configurada com 'AllowOverride All'


##### Versão do PHP 7 (Caso você esteja utilizando): *instalar antes de rodar o 'composer install' no diretório*

>    sudo apt-get -y install apache2 php7.0 php7.0-mysql libapache2-mod-php7.0 curl lynx-cur 
>    && sudo apt-get install php7.0-mbstring -y 
>    && sudo apt-get install php-xml -y 
>    && sudo apt-get install php7.0-gd -y

## DOCKER

> Segue abaixo as configurações para rodar a API utilizando o Docker Container. Todos os comandos devem ser executados
  dentro da pasta *docker/*
> É preciso ter o Docker e o Docker-Compose para utilizar esta feature.

### Build do Container WEB
> docker build -t web . *(Executar este comando apenas caso o arquivo Dockerfile seja modificado ou no inicio da
  configuração)*

### Subindo o container utilizando o Docker-compose
> docker-compose up --build *(Utilizar o --build apenas após o comando acima)*
