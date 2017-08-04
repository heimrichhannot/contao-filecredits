# Filecredits

Contao module that adds credit support for images and files.

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