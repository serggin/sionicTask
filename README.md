# Тестовое задание

## Замечание по сути задания

Требования:

>Если в каком-либо городе нет количества или цены для данного товара, или он отсутствует вообще, в соответствующие поля
 ставится 0. Если в разных файлах у одного и того же товара имеются несовпадающие поля, например, название или вес,
 можно использовать любое из них для записи в БД.

и

>При последующем запуске скрипт должен обновлять данные по товарам, находя соответствие по полю Код (то есть обновлять то,
что уже есть, а не добавлять новые).

несколько противоречат друг другу. Буквально, первое требует не обновлять не ключевые атрибуты, если запись была
создана или обновлена в ТЕКУЩЕМ ЗАПУСКЕ скрипта.
Второе же требует обновлять не ключевые атрибуты, если запись была создана или обновлена в одном из ПРЕДЫДУЩИХ ЗАПУСКОВ скрипта.

Конечно, этим требованием можно удовлетворить, если хранить в каждой записи некоторый идентификатор запуска скрипта
(например, дату+время). Но в таком решении мало практического смысла.

Поэтому я выбираю для руководства второе требование и буду всегда обновлять неключеые атрибуты.

## Структура базы данных

Использовал MySQL.

```
CREATE TABLE `product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(16) NOT NULL,
  `name` varchar(255) NOT NULL,
  `weight` decimal(10,3) unsigned DEFAULT '0.000',
  `usage` varchar(1024) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_code_UNIQUE` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```
```
CREATE TABLE `offer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(16) NOT NULL,
  `city` varchar(32) NOT NULL,
  `quantity` int(11) DEFAULT '0',
  `price` decimal(10,2) unsigned DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `offer_code_city_UNIQUE` (`code`,`city`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
```

## Доступ к базе данных

Имя схемы, пользователь, пароль захардкожены в конструкторе класса PdoHelper. 
Конечно, для коммерческого ПО нужно использовать то или конфигурационное решение.
Но для простоты тестого задания это приемлимо.

## Часть 1 задания

Запуск скрипта осуществляется командой
```
$ php script.php <source-dir>
```
где <source-dir> - путь к директории с исходными xml-файлами.
Папку с исходнымы файлами (data) я разместил в корне проекта задания.
Поэтому в моем случае команда запуска:
```
$ php script.php data
```

## Часть 2 задания

Запускаем тестовый веб-сервер в корне проекта, например:
```angular2
$ php -S localhost:3000
```

и отображаем веб-страницу в броузере:
```
localhost:3000
```

Данных достаточно много, поэтому я выбрал размер страницы в 10 записей.