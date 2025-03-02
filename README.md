Ez egy iskolai szoftver a diákok étkezési igénylésének kezelésére.

# Telepítés Dockerrel
1. Ha szeretnénk, hogy jelkulcs létrehozásakor a jelkulcs neve automatikusan a platform (pl. Windows) legyen, akkor töltsünk egy browscap fájlt és másoljuk a mappába (konténerben a /var/www/html-be). (Ilyen fájlt beszerezhetünk pl. a [browscap.org](https://browscap.org/) oldalról.) Ezt követően hozzunk létre egy tetszőleges nevű (.ini kiterjesztésű) fájlt a *conf/php* könyvtárban! Tartalma legyen:
```
[browscap]
browscap = "/var/www/html/php_browscap.ini"
```
Cseréljük ki az elérési útvonalat az aktuálisra!

2. Ezután adjuk ki a `docker compose up` parancsot!