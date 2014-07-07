BDD Mink features to testing behaviors of **vitringez.com**
------------------------------------------------------------
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/muhasturk/Mink/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/muhasturk/Mink/?branch=master)

First you have to execute selenium driver:

    java -jar selenium-server*.jar

Update your **composer**.json file:

    composor update

move **Features** folder in your Symfony2 bundle

add "main_mail" and "other_mail" parameters in your **parameters.yml**

Test all features:

    bin/behat @YourBundleName/featurename.feature







