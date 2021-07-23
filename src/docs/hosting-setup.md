# Настройка хостинга

### Для удобства создаем переменные

```shell
PHP_PATH=/opt/php/php73/bin
PHP=$PHP_PATH/php
```

### Создаем каталог, в который будет установлен composer и переходим в него:

```shell
mkdir -p bin
cd bin
```

### Скачиваем и устанавливаем composer:

```shell
curl -sS https://getcomposer.org/installer > composer-setup.php
cd ~
$PHP bin/composer-setup.php --install-dir=bin --filename=composer
```

### Создаем файл .profile, чтобы запускать нужную версию php и установленный composer из командной строки по команде php:

```shell
echo "PATH='$PHP_PATH:$PATH'" >> ~/.profile
echo "alias composer='$PHP $HOME/bin/composer'" >> ~/.profile
echo 'source ~/.profile' >> ~/.bashrc
source ~/.profile
```

### Проверяем composer и версию php
```shell
composer
php -v
```