<?php
/**
 * Copyright 2014 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category  Horde
 * @copyright 2014 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @link      http://pear.horde.org/index.php?package=Core
 * @package   Core
 */

/**
 * A Horde_Injector based factory for creating the History object.
 *
 * @category  Horde
 * @copyright 2014 Horde LLC
 * @license   http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @link      http://pear.horde.org/index.php?package=Core
 * @package   Core
 */
class Horde_Core_Factory_History extends Horde_Core_Factory_Injector
{
    /**
     * @return Horde_History
     * @throws Horde_Exception
     */
    public function create(Horde_Injector $injector)
    {
        global $conf, $injector;

        // For BC, default to 'Sql' driver.
        $driver = empty($conf['history']['driver'])
            ? 'Sql'
            : $conf['history']['driver'];
        $params = Horde::getDriverConfig('history', $driver);

        $history = null;

        switch (Horde_String::lower($driver)) {
        case 'nosql':
            $nosql = $injector->getInstance('Horde_Core_Factory_Nosql')->create('horde', 'history');
            if ($nosql instanceof Horde_History_Mongo) {
                $history = new Horde_History_Mongo(
                    $injector->getInstance('Horde_Registry')->getAuth(),
                    array('mongo_db' => $nosql)
                );
            }
            break;

        case 'sql':
            try {
                $history = new Horde_History_Sql(
                    $injector->getInstance('Horde_Registry')->getAuth(),
                    $injector->getInstance('Horde_Core_Factory_Db')->create('horde', 'history')
                );
            } catch (Exception $e) {}
            break;
        }

        if (is_null($history)) {
            $history = new Horde_History_Null();
        } elseif ($cache = $injector->getInstance('Horde_Cache')) {
            $history->setCache($cache);
        }

        return $history;
    }
}
