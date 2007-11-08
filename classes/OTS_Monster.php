<?php

/**#@+
 * @version 0.0.6+SVN
 * @since 0.0.6+SVN
 */

/**
 * @package POT
 * @author Wrzasq <wrzasq@gmail.com>
 * @copyright 2007 (C) by Wrzasq
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public License, Version 3
 */

/**
 * Wrapper for monsters files DOMDocument.
 * 
 * Note: as this class extends DOMDocument class and contains exacly respond XML tree you can work on it as on normal DOM tree.
 * 
 * @package POT
 */
class OTS_Monster extends DOMDocument
{
/**
 * Returns monster name.
 * 
 * @return string Name.
 */
    public function getName()
    {
        return $this->documentElement->getAttribute('name');
    }

/**
 * Returns monster race.
 * 
 * @return string Race.
 */
    public function getRace()
    {
        return $this->documentElement->getAttribute('race');
    }

/**
 * Returns amount of experience for killing this monster.
 * 
 * @return int Experience points.
 */
    public function getExperience()
    {
        return (int) $this->documentElement->getAttribute('experience');
    }

/**
 * Returns monster speed.
 * 
 * @return int Speed.
 */
    public function getSpeed()
    {
        return (int) $this->documentElement->getAttribute('speed');
    }

/**
 * Returns amount of mana required to summon this monster.
 * 
 * @return int|bool Mana required (false if not possible).
 */
    public function getManaCost()
    {
        // check if it is possible to summon this monster
        if( $this->documentElement->hasAttribute('manacost') )
        {
            return (int) $this->documentElement->getAttribute('manacost');
        }
        else
        {
            return false;
        }
    }

/**
 * Returns monster HP.
 * 
 * @return int Hit points.
 */
    public function getHealth()
    {
        return (int) $this->documentElement->getElementsByTagName('health')->item(0)->getAttribute('max');
    }

/**
 * Returns all monster flags (in format flagname => value).
 * 
 * @return array Flags.
 */
    public function getFlags()
    {
        $flags = array();

        // read all flags
        foreach( $this->documentElement->getElementsByTagName('flags')->item(0)->getElementsByTagName('flag') as $flag)
        {
            $flag = $flag->attributes->item(0);

            $flags[$flag->nodeName] = (int) $flag->nodeValue;
        }

        return $flags;
    }

/**
 * Returns specified flag value.
 * 
 * @param string $flag Flag.
 * @return int|bool Flag value (false if not set).
 */
    public function getFlag($flag)
    {
        // searches for flag
        foreach( $this->documentElement->getElementsByTagName('flags')->item(0)->getElementsByTagName('flag') as $flag)
        {
            // found
            if( $flag->hasAttribute($flag) )
            {
                return (int) $flag->getAttribute($flag);
            }
        }

        // not found
        return false;
    }

/**
 * Returns voices that monster can sound.
 * 
 * @return array List of voices.
 */
    public function getVoices()
    {
        $voices = array();

        $element = $this->documentElement->getElementsByTagName('voices')->item(0);

        // checks if it has any voices
        if( isset($element) )
        {
            // loads all voices
            foreach( $element->getElementsByTagName('voice') as $voice)
            {
                $voices[] = $voice->getAttribute('sentence');
            }
        }

        return $voices;
    }

/**
 * Returns all possible loot.
 * 
 * @return array List of item IDs.
 */
    public function getLoot()
    {
        $loot = array();

        $element = $this->documentElement->getElementsByTagName('loot')->item(0);

        // checks if it has any loot
        if( isset($element) )
        {
            // adds all items
            foreach( $element->getElementsByTagName('item') as $item)
            {
                $id = $item->getAttribute('id');

                // avoid redundancy
                if( !in_array($id, $loot) )
                {
                    $loot[] = $id;
                }
            }
        }

        return $loot;
    }

/**
 * Returns all monster immunities.
 * 
 * @return array Immunities.
 */
    public function getImmunities()
    {
        $immunities = array();

        $element = $this->documentElement->getElementsByTagName('immunities')->item(0);

        // checks if it has any immunities
        if( isset($element) )
        {
            // read all immunities
            foreach( $element->getElementsByTagName('immunity') as $immunity)
            {
                $immunity = $immunity->attributes->item(0);

                // checks if immunity is set
                if($immunity->nodeValue > 0)
                {
                    $immunities[] = $immunity->nodeName;
                }
            }
        }

        return $immunities;
    }

/**
 * Checks if monster has given immunity.
 * 
 * @param string $name Immunity to check.
 * @return bool Immunity state.
 */
    public function hasImmunity($name)
    {
        $element = $this->documentElement->getElementsByTagName('immunities')->item(0);

        // if doesn't have any immunities obviously doesn't have this one too
        if( isset($element) )
        {
            // read all immunities
            foreach( $element->getElementsByTagName('immunity') as $immunity)
            {
                // checks if this is what we are searching for
                if( $immunity->hasAttribute($name) )
                {
                    return $immunity->getAttribute($name) > 0;
                }
            }
        }

        return false;
    }

/**
 * Returns monster defense rate.
 * 
 * @return int Defense rate.
 */
    public function getDefense()
    {
        $element = $this->documentElement->getElementsByTagName('defenses')->item(0);

        // checks if defenses element is set
        if( isset($element) )
        {
            return (int) $element->getAttribute('defense');
        }

        return 0;
    }

/**
 * Returns monster armor.
 * 
 * @return int Armor rate.
 */
    public function getArmor()
    {
        $element = $this->documentElement->getElementsByTagName('defenses')->item(0);

        // checks if defenses element is set
        if( isset($element) )
        {
            return (int) $element->getAttribute('armor');
        }

        return 0;
    }

/**
 * Returns list of special defenses.
 * 
 * @return array List of defense effects.
 */
    public function getDefenses()
    {
        $defenses = array();

        $element = $this->documentElement->getElementsByTagName('defenses')->item(0);

        // checks if it has any defenses
        if( isset($element) )
        {
            foreach( $element->getElementsByTagName('defense') as $defense)
            {
                $defenses[] = $defense->getAttribute('name');
            }
        }

        return $defenses;
    }

/**
 * Returns list of monster attacks.
 * 
 * @return array List of attafck effects.
 */
    public function getAttacks()
    {
        $attacks = array();

        $element = $this->documentElement->getElementsByTagName('attacks')->item(0);

        // checks if it has any defenses
        if( isset($element) )
        {
            foreach( $element->getElementsByTagName('attack') as $attack)
            {
                $attacks[] = $attack->getAttribute('name');
            }
        }

        return $attacks;
    }
}

/**#@-*/

?>
