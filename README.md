[![Packagist](https://img.shields.io/packagist/v/eightwire/magento2-module-primer.svg)](https://packagist.org/packages/eightwire/magento2-module-primer) 
<!-- [![Packagist](https://img.shields.io/packagist/dt/eightwire/magento2-module-primer.svg)](https://packagist.org/packages/eightwire/magento2-module-primer) -->
<!-- [![Packagist](https://img.shields.io/packagist/l/eightwire/magento2-module-primer.svg)](https://packagist.org/packages/eightwire/magento2-module-primer) -->

# Magento 2 Cache Primer

A full page cache priming tool for Magento 2

Requests to whitelisted actions are logged to a history table with a higher priority given to pages that are viewed most frequently. 
A console and cron task is provided to initiate the crawler and prime pages in the queue from highest to lowest priority. 
Supports multiple store views and X-Magento-Vary cookies.


![Recordit GIF](./example.gif)


## Usage

```
php bin/magento primer:crawler:run   # Run crawler task
php bin/magento primer:flush         # Flush urls to force a recrawl
```

Provided by [Eight Wire Digital](https://www.8wiredigital.co.nz/)
