Для nginx попльзователь по умолчанию  - это nobody 
http://nginx.org/en/docs/ngx_core_module.html#user


/tmp/app-test - должна быть доступна для записи на хостовой маашине для пользоввателя из подкоторого запускаается контейнер 

```shell
docker run -it --name=swool-test-app-container \
-p 4300:8080 \
-v /tmp/app-test:/app/var/log:z \
-v /tmp/app-test:/var/log/php-fpm:z \
-v /tmp/app-test:/var/log/nginx:z \
-v /tmp/app-test:/tmp:z \
swool-test-app:latest
```


Запуск экспрта 

bin/console app:meter-event:export
