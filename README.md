BDD Mink features to testing behaviors of **vitringez.com**
------------------------------------------------------------
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/muhasturk/Mink/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/muhasturk/Mink/?branch=master)

install composer

    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer

execute selenium driver:

    java -jar selenium-server*.jar

create new symfony2 project

    composer create-project symfony/framework-standard-edition path/ "2.3.*"

update your **composer**.json file with I gave one

    composor update

move **Features** folder in your Symfony2 bundle

set your parameters in your /app/config/**parameters.yml**

test features:

    bin/behat @YourBundleName/featurename.feature







