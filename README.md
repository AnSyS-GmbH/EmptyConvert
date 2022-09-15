# EmptyConvert
Möglichkeit leere Werte statt 0-Werte per REST-API zu übertragen

# Installation

composer config repositories.repo-name vcs https://github.com/AnSyS-GmbH/EmptyConvert
composer require  ansys/emptyconvert:dev-main
php bin/magento module:enable AnSyS_EmptyConvert
php bin/magento setup:upgrade
php bin/magento setup:di:compile
