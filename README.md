# makaira/connect-oxid-compat

- this library adds backward compatibility for `makaira/connect` module for Oxid versions older then 6.2
- makes symfony dependency injection through `services.yaml` in module directories available
- makes console commands callable through `./vendor/makaira/oxid-connect-compat/bin/console`
- for oxid < 6.0 please install Oxid connect first and add the following lines to your project composer.json to let composer copy the connect module to the modules directory
  ```yaml
    "scripts": {
      "post-update-cmd": "Makaira\\ConnectCompat\\Composer::postUpdate",
      "post-install-cmd": "Makaira\\ConnectCompat\\Composer::postUpdate"
    },
  ```
  add also the following line before `require_once OX_BASE_PATH . 'modules/functions.php';` to your oxid bootstrap.php file
  ```php
    require_once __DIR__ . '/../vendor/autoload.php';
  ```
