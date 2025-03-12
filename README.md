Ez egy iskolai szoftver a diákok étkezési igénylésének kezelésére.

# Telepítés Dockerrel fejlesztési célra
1. Konfiguráláshoz hozzunk létre a *config.php* fájl mellé egy *customconfig.php* fájlt (ezt kötelező akkor is megcsinálni, ha nem akarunk semmit sem felülírni)! Ebben a fájlban lévő változók felülírják az eredetieket és ez nincs benne a verziókezelésben. Ha valamit módosítani szeretnénk a *config.php*-hoz képest, akkor másoljuk át azt a változót a *customconfig.php*-ba és írjuk át a megfelelő paramétereket!
2. Adjuk ki a `docker compose up` parancsot!
3. A php-apache konténerben adjuk ki a `composer install` parancsot!

# Telepítés Docker nélkül éles környezetbe
1. Rendszerkövetelmények:
   1. PHP >= 8.1
        - mysqli
        - Sodium
        - cURL
        - gmp
        - OpenSSL
        - Multibyte String
        - GD
        - Zip
        - DOM
   2. MySQL
   3. Composer
2. Klónozzuk ezt a projektet a `git clone` paranccsal!
3. Ezután adjuk ki a program mappájában a `composer install` parancsot!
4. Konfiguráláshoz hozzunk létre a *config.php* fájl mellé egy *customconfig.php* fájlt (ezt kötelező akkor is megcsinálni, ha nem akarunk semmit sem felülírni)! Ebben a fájlban lévő változók felülírják az eredetieket és ez nincs benne a verziókezelésben. Ha valamit módosítani szeretnénk a *config.php*-hoz képest, akkor másoljuk át azt a változót a *customconfig.php*-ba és írjuk át a megfelelő paramétereket!
   1. Állítsuk a *$debug* változó értékét hamisra!
   2. Az *$rp* változóban található értékek a jelkulcsra vonatkozó relying party adatok. A *name* értéke legyen tetszőleges, az *id* értéke legyen a domain név (pl. menza.peldaiskola.hu)!
   3. A *$vapid* változóban az üzenetküldéshez szükséges értékeket kell megadni. A *subject* legyen a weboldalt üzemeltető elérhetősége ilyen formátumban: `mailto:admin@valami.hu`. (Ez az ímél cím a leküldéses értesítéseknél használt átjátszószerverek üzemeltetőinek kell, ha valami baj van, akkor ezen keresztül tudják felvenni a kapcsolatot.) A titkos és nyilvános kulcsot egy ilyen PHP kóddal generálhatunk:
   ```php
   <?php
    use Minishlink\WebPush\VAPID;
    require_once "config.php";
    var_dump(VAPID::createVapidKeys());
    ?>
    ```

    Fontos, hogy ezekről a kulcsokról készüljön biztonsági mentés!
    
   4. Adjuk még meg az adatbázis elérhetőségét a *$dbcred* változóban!
 5. Ezután készen áll a weboldal!