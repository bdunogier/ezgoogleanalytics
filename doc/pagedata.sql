CREATE TABLE `ezgoogleanalytics_pagedata_total` (
  `node_id` int(10) NOT NULL,
  `url` text NOT NULL,
  `pageviews` int(10) NOT NULL,
  `uniquepageviews` int(10) NOT NULL,
  `entrances` int(10) NOT NULL,
  `exits` int(10) NOT NULL,
  `timeonpage` int(10) NOT NULL,
  `bounces` int(10) NOT NULL,
  `newvisits` int(10) NOT NULL,
  PRIMARY KEY  (`node_id`,`url`(255)),
  KEY `url` (`url`(255)),
  KEY `node_id` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ezgoogleanalytics_pagedata_incremental` (
  `node_id` int(10) NOT NULL,
  `url` text NOT NULL,
  `pageviews` int(10) NOT NULL,
  `uniquepageviews` int(10) NOT NULL,
  `entrances` int(10) NOT NULL,
  `exits` int(10) NOT NULL,
  `timeonpage` int(10) NOT NULL,
  `bounces` int(10) NOT NULL,
  `newvisits` int(10) NOT NULL,
  PRIMARY KEY  (`node_id`,`url`(255)),
  KEY `url` (`url`(255)),
  KEY `node_id` (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;