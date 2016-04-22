"""
Counters
"""

from yoyo import step

__depends__ = {'20160421_01_aWbdT-create-posts-table'}

steps = [
    step(""" 
CREATE TABLE IF NOT EXISTS `counter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Dumping data for table `counter`
--

INSERT INTO `counter` (`id`, `name`, `value`) VALUES
(1, 'posts', 0),
(2, 'views', 0);
""")
]
