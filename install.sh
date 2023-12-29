#!/bin/bash

#Installation des dépendances de base
echo "Mot de passe root nécessaire pour installer les dépendances"
su -c 'apt update
&& apt install -y git zip unzip curl
&& curl -1sLf "https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh" | bash
&& apt install -y symfony-cli
&& chmod u+x composer.phar
&& mv composer.phar /usr/local/bin/composer'

#Installation de Symfony et des dépendances associées
rm -f composer.lock
composer update
symfony console tailwind:init

echo "Mot de passe root nécessaire pour installer le certificat TLS"
su -c 'symfony server:ca:install'

#Configuration de la BDD
echo "Début de la configuration de la BDD"
read -p 'Nom de la base de données (schéma): ' schema
read -p 'Adresse du serveur BDD: ' address
read -p 'Port du serveur BDD: ' port
read -p "Nom de l'utilisateur: " user
read -p 'Mot de passe: ' password
read -p 'Logiciel de base de données (mysql, mariadb, postgresql) : ' software

if [[ $software != "mysql" && $software != "mariadb" && $software != "postgresql" ]]
then
	echo "Logiciel invalide, fin du script"
	exit 1
fi
read -p 'Version de votre BDD: ' version

if [[ $software == "mariadb" ]]
then
sed -i "s/DATABASE_URL=.*/DATABASE_URL=\"mysql:\/\/$user:$password@$address:$port\/$schema?serverVersion=$version-MariaDB\&charset=utf8mb4\"/" .env
fi

if [[ $software == "mysql" ]]
then
sed -i "s/DATABASE_URL=.*/DATABASE_URL=\"mysql:\/\/$user:$password@$address:$port\/$schema?serverVersion=$version\&charset=utf8mb4\"/" .env
fi

if [[ $software == "postgresql" ]]
then
sed -i "s/DATABASE_URL=.*/DATABASE_URL=\"postgresql:\/\/$user:$password@$address:$port\/$schema?serverVersion=$version\&charset=utf8\"/" .env
fi

echo "BDD configurée"

#Fermeture du serveur (par sécurité)
symfony server:stop

#Vérification des mises à jours
composer update

#Installation d'une dépendance
symfony console importmap:install

#Création des tables de la BDD
symfony console doctrine:migrations:migrate

#Installation du framework CSS Tailwind
symfony console tailwind:build

#Lancement du serveur
symfony server:start
