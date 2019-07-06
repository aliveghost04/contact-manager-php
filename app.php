<?php

// Load all required files
require(__DIR__ . '/config.php');
require(__DIR__ . '/database/Database.php');
require(__DIR__ . '/database/DatabaseConfig.php');
require(__DIR__ . '/router.php');
require(__DIR__ . '/routes.php');
require(__DIR__ . '/controllers/BaseController.php');
require(__DIR__ . '/controllers/ContactController.php');
require(__DIR__ . '/models/BaseModel.php');
require(__DIR__ . '/models/ContactModel.php');
require(__DIR__ . '/repositories/BaseRepository.php');
require(__DIR__ . '/repositories/ContactRepository.php');
require(__DIR__ . '/services/contact/ContactValidator.php');
