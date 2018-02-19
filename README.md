# Filecredits

Contao module that adds credit support for images and files.

## Installation

```
composer require heimrichhannot/contao-filecredits
```

### Install cron job (contao 4)
```
0 3 * * * /path/to/contao/vendor/heimrichhannot/contao-filecredits/bin/indexer # every day at 03:00 
```

### Install cron job (contao 3)
```
0 3 * * * /path/to/contao/composer/vendor/heimrichhannot/contao-filecredits/bin/indexer # every day at 03:00 
```

## Filecredits 3.x

Filecredits 3 yields an huge performance due to credit index within cron job only. Filecredits makes usage of `executeResize` Hook, which is triggered
every time an image is resized. If you use responsive images this Hook will be triggered not only once, but for every src-set provided.
Filecredit 2.x invoked the hook within every client request, which braked down your website performance, based on number of images per page.
Filecredit 3 now triggers a daily poor mans cron, or you can declare your own cron job within crontab, see Installation. 

## Filecredits 2.x

Filecredits 2.x is a complete redevelopment. If you attached your custom modules to filecredits 1.x, we would not recommend to upgrade.

## Features

- Copyrights can be added directly at the file within the contao file manager
- Page occurrences for images will be added automatically, on rebuild search index, or when the page with the image will be loaded
- A backend module is available to add custom filecredits with multiple page occurrences.
- A copyright field can be added to any DCA as a shortcut to add copyrights directly to a file without having to go to the file manager

## Technical instructions

### Adding a copyright field to a non-tl_files-DCA

If you want to get a shortcut copyright field next to e.g. some image field you can do this by calling the following code e.g. in your DCA:

```
\HeimrichHannot\FileCredit\FileCredit::addCopyrightFieldToDca(<the DCA's table name>, <the name of the copyright field to be created>, <the name of the linked file field which copyright is being synced>);

// example:
\HeimrichHannot\FileCredit\FileCredit::addCopyrightFieldToDca('tl_news', 'detailsCopyright', 'detailsSingleSRC');
```

### Hooks

Name | Arguments | Expected return value | Description
---- | --------- | --------------------- | -----------
{{copyright::*::,}} | 1: file uuid (string) or file path, 2: credits delimiter (default: ,) | string | Return the file credits for a given uuid or path as delimited string.