<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */

$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amsorting/most_viewed')}` (
  `id`          int(10)     unsigned NOT NULL,
  `store_id`    smallint(5) unsigned NOT NULL,
  `most_viewed` int(10)     unsigned NOT NULL,
  KEY `most_viewed_idx` (`id`, `store_id`)
) ENGINE=MyISAM;

CREATE TABLE `{$this->getTable('amsorting/bestsellers')}` (
  `id`          int(10)     unsigned NOT NULL,
  `store_id`    smallint(5) unsigned NOT NULL,
  `bestsellers` int(10)     unsigned NOT NULL,
  KEY `bestsellers_idx` (`id`, `store_id`)
) ENGINE=MyISAM;

CREATE TABLE `{$this->getTable('amsorting/wished')}` (
  `id`          int(10)     unsigned NOT NULL,
  `store_id`    smallint(5) unsigned NOT NULL,
  `wished`      int(10)     unsigned NOT NULL,
  KEY `bestsellers_idx` (`id`, `store_id`)
) ENGINE=MyISAM;

");

$this->endSetup(); 