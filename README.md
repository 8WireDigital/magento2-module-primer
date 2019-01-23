# Magento 2 Cache Primer

A full page cache priming tool for Magento 2

Requests to whitelisted actions will be logged to a primer queue with a higher priority given to pages that are viewed most frequently. 
A console and cron task is provided to initiate the crawler and prime pages in the queue from highest to lowest priority. 
Supports multi store views and X-Magento-Vary cookies 


![Recordit GIF](./example.gif)


## Usage

```
php -f bin/magento primer:crawler:run   # Run crawler task
php -f bin/magento primer:flush         # Flush urls to force a recrawl
```

Provided by [Eight Wire Digital](https://www.8wiredigital.co.nz/)